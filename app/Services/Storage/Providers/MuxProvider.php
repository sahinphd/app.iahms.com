<?php

namespace App\Services\Storage\Providers;

use App\Contracts\StorageProvider;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MuxProvider implements StorageProvider
{
    /**
     * Get Mux API request basic auth credentials.
     */
    protected function getAuth(): array
    {
        return [
            Setting::get('mux_token_id'),
            Setting::get('mux_token_secret')
        ];
    }

    /**
     * Upload a video to Mux.
     */
    public function upload(UploadedFile $file, string $path): string
    {
        $auth = $this->getAuth();
        $signingKeyId = Setting::get('mux_signing_key_id');
        $playbackPolicy = $signingKeyId ? 'signed' : 'public';

        // 1. Create a Mux Direct Upload URL
        $uploadResponse = Http::withBasicAuth($auth[0], $auth[1])
            ->post('https://api.mux.com/video/v1/uploads', [
                'cors_origin' => '*',
                'new_asset_settings' => [
                    'playback_policy' => [$playbackPolicy]
                ]
            ]);

        if (!$uploadResponse->successful()) {
            throw new \Exception("Mux Direct Upload creation failed: " . $uploadResponse->body());
        }

        $uploadData = $uploadResponse->json('data');
        $uploadUrl = $uploadData['url'];
        $uploadId = $uploadData['id'];

        // 2. PUT the video file content directly to Mux upload URL
        $fileStream = fopen($file->getRealPath(), 'r');
        $putResponse = Http::withBody($fileStream, $file->getMimeType())
            ->put($uploadUrl);

        if (!$putResponse->successful()) {
            throw new \Exception("Failed to upload video stream to Mux: " . $putResponse->body());
        }

        // 3. Poll Mux API for Asset creation (up to 15 seconds)
        $assetId = null;
        $playbackId = null;
        $attempts = 0;

        while ($attempts < 15) {
            sleep(1);
            $attempts++;

            $statusResponse = Http::withBasicAuth($auth[0], $auth[1])
                ->get("https://api.mux.com/video/v1/uploads/{$uploadId}");

            if ($statusResponse->successful() && $statusResponse->json('data.status') === 'asset_created') {
                $assetId = $statusResponse->json('data.asset_id');
                break;
            }
        }

        if (!$assetId) {
            throw new \Exception("Mux asset creation timed out on direct upload.");
        }

        // 4. Retrieve Playback ID from Mux Asset details
        $assetResponse = Http::withBasicAuth($auth[0], $auth[1])
            ->get("https://api.mux.com/video/v1/assets/{$assetId}");

        if ($assetResponse->successful()) {
            $playbackIds = $assetResponse->json('data.playback_ids');
            if (!empty($playbackIds)) {
                $playbackId = $playbackIds[0]['id'];
            }
        }

        if (!$playbackId) {
            // Fallback: save asset_id, stream will parse it
            $playbackId = 'pending';
        }

        // Return a structured Mux URI
        return "mux://{$assetId}:{$playbackId}";
    }

    /**
     * Generate secure streaming URL or Mux token.
     */
    public function generateSignedUrl(string $filePath, int $expiryMinutes = 15): string
    {
        // Parse "mux://{asset_id}:{playback_id}"
        $parts = explode(':', str_replace('mux://', '', $filePath));
        $playbackId = $parts[1] ?? null;

        if (!$playbackId || $playbackId === 'pending') {
            // Attempt to fetch dynamically if pending
            $assetId = $parts[0] ?? null;
            if ($assetId) {
                $auth = $this->getAuth();
                $assetResponse = Http::withBasicAuth($auth[0], $auth[1])
                    ->get("https://api.mux.com/video/v1/assets/{$assetId}");
                if ($assetResponse->successful()) {
                    $playbackIds = $assetResponse->json('data.playback_ids');
                    if (!empty($playbackIds)) {
                        $playbackId = $playbackIds[0]['id'];
                    }
                }
            }
        }

        if (!$playbackId || $playbackId === 'pending') {
            return 'https://stream.mux.com/placeholder.m3u8'; // fallback
        }

        $signingKeyId = Setting::get('mux_signing_key_id');
        $privateKeyPem = Setting::get('mux_private_key');

        if ($signingKeyId && $privateKeyPem) {
            // Generate RS256 signed JWT
            $token = $this->generateMuxJwt($playbackId, $signingKeyId, $privateKeyPem, $expiryMinutes);
            return "https://stream.mux.com/{$playbackId}.m3u8?token={$token}";
        }

        // Public video stream fallback
        return "https://stream.mux.com/{$playbackId}.m3u8";
    }

    /**
     * Delete video asset from Mux.
     */
    public function delete(string $filePath): bool
    {
        try {
            $parts = explode(':', str_replace('mux://', '', $filePath));
            $assetId = $parts[0] ?? null;

            if ($assetId) {
                $auth = $this->getAuth();
                $response = Http::withBasicAuth($auth[0], $auth[1])
                    ->delete("https://api.mux.com/video/v1/assets/{$assetId}");

                return $response->successful();
            }
        } catch (\Exception $e) {
            Log::error("Mux delete error: " . $e->getMessage());
        }
        return false;
    }

    /**
     * RS256 JWT Token generator for Mux streaming auth.
     */
    protected function generateMuxJwt(string $playbackId, string $keyId, string $privateKeyPem, int $expiryMinutes): string
    {
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT', 'kid' => $keyId]);
        $payload = json_encode([
            'sub' => $playbackId,
            'aud' => 'v',
            'exp' => time() + ($expiryMinutes * 60)
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
}
