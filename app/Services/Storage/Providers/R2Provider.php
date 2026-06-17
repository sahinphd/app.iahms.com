<?php

namespace App\Services\Storage\Providers;

use App\Contracts\StorageProvider;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class R2Provider implements StorageProvider
{
    /**
     * Dynamically register and get the Cloudflare R2 storage disk.
     */
    protected function getDisk()
    {
        $endpoint = Setting::get('r2_endpoint');
        $bucket = Setting::get('r2_bucket');
        $key = Setting::get('r2_access_key_id');
        $secret = Setting::get('r2_secret_access_key');
        $region = Setting::get('r2_region', 'auto');

        config(['filesystems.disks.r2_dynamic' => [
            'driver' => 's3',
            'key' => $key,
            'secret' => $secret,
            'region' => $region,
            'bucket' => $bucket,
            'endpoint' => $endpoint,
            'use_path_style_endpoint' => true,
        ]]);

        return Storage::disk('r2_dynamic');
    }

    /**
     * Upload a file to Cloudflare R2 bucket.
     */
    public function upload(UploadedFile $file, string $path): string
    {
        $disk = $this->getDisk();
        $filename = time() . '_' . $file->getClientOriginalName();
        $fullPath = rtrim($path, '/') . '/' . $filename;
        
        $disk->putFileAs($path, $file, $filename);

        return $fullPath;
    }

    /**
     * Generate an S3/R2 pre-signed URL.
     */
    public function generateSignedUrl(string $filePath, int $expiryMinutes = 15): string
    {
        $disk = $this->getDisk();
        return $disk->temporaryUrl($filePath, now()->addMinutes($expiryMinutes));
    }

    /**
     * Generate a signed upload configuration for R2 storage upload.
     */
    public function generateSignedUploadUrl(string $path, string $contentType, int $expiryMinutes = 15): array
    {
        $disk = $this->getDisk();
        $filename = time() . '_' . basename($path);
        $fullPath = rtrim(dirname($path), '/') . '/' . $filename;

        // AWS/S3 compatible temporary upload URL
        $url = $disk->temporaryUploadUrl($fullPath, now()->addMinutes($expiryMinutes));

        return [
            'upload_url' => $url,
            'file_path' => $fullPath,
            'method' => 'PUT',
            'headers' => [
                'Content-Type' => $contentType,
            ]
        ];
    }

    /**
     * Delete an object from R2.
     */
    public function delete(string $filePath): bool
    {
        try {
            $disk = $this->getDisk();
            if ($disk->exists($filePath)) {
                return $disk->delete($filePath);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Cloudflare R2 delete error: " . $e->getMessage());
        }
        return false;
    }
}
