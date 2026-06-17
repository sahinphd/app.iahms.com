<?php

namespace App\Services\Storage\Providers;

use App\Contracts\StorageProvider;
use App\Models\Setting;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\UploadedFile;

class GcsProvider implements StorageProvider
{
    /**
     * Get the Google Cloud Storage SDK client.
     */
    protected function getClient()
    {
        $projectId = Setting::get('gcp_project_id');
        $keyFileJson = Setting::get('gcp_key_file');

        $config = [];
        if ($projectId) {
            $config['projectId'] = $projectId;
        }

        if ($keyFileJson) {
            $keyFileData = json_decode($keyFileJson, true);
            if (is_array($keyFileData)) {
                $config['keyFile'] = $keyFileData;
            }
        }

        return new StorageClient($config);
    }

    /**
     * Upload a file to GCP bucket.
     */
    public function upload(UploadedFile $file, string $path): string
    {
        $client = $this->getClient();
        $bucketName = Setting::get('gcp_bucket');
        $bucket = $client->bucket($bucketName);

        $filename = time() . '_' . $file->getClientOriginalName();
        $fullPath = rtrim($path, '/') . '/' . $filename;

        $bucket->upload(
            fopen($file->getRealPath(), 'r'),
            [
                'name' => $fullPath
            ]
        );

        return $fullPath;
    }

    /**
     * Generate a GCP secure signed URL.
     */
    public function generateSignedUrl(string $filePath, int $expiryMinutes = 15): string
    {
        $client = $this->getClient();
        $bucketName = Setting::get('gcp_bucket');
        $bucket = $client->bucket($bucketName);
        $object = $bucket->object($filePath);

        return $object->signedUrl(
            now()->addMinutes($expiryMinutes),
            [
                'version' => 'v4',
            ]
        );
    }

    /**
     * Generate a signed upload configuration for uploading directly to GCP bucket.
     */
    public function generateSignedUploadUrl(string $path, string $contentType, int $expiryMinutes = 15): array
    {
        $client = $this->getClient();
        $bucketName = Setting::get('gcp_bucket');
        $bucket = $client->bucket($bucketName);

        $filename = time() . '_' . basename($path);
        $fullPath = rtrim(dirname($path), '/') . '/' . $filename;

        $object = $bucket->object($fullPath);
        $url = $object->signedUrl(
            now()->addMinutes($expiryMinutes),
            [
                'method' => 'PUT',
                'contentType' => $contentType,
                'version' => 'v4',
            ]
        );

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
     * Delete an object from GCP bucket.
     */
    public function delete(string $filePath): bool
    {
        try {
            $client = $this->getClient();
            $bucketName = Setting::get('gcp_bucket');
            $bucket = $client->bucket($bucketName);
            $object = $bucket->object($filePath);

            if ($object->exists()) {
                $object->delete();
                return true;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("GCS delete error: " . $e->getMessage());
        }
        return false;
    }
}
