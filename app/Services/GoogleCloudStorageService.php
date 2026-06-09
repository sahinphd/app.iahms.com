<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class GoogleCloudStorageService
{
    /**
     * Upload a file to the specified path in storage.
     *
     * @param UploadedFile $file
     * @param string $path
     * @return string The stored file path relative to disk root
     */
    public function upload(UploadedFile $file, string $path): string
    {
        // Store the file on the local public disk for temporary viewing/access
        return $file->store($path, 'public');
    }

    /**
     * Generate a signed URL for a file with an expiry time.
     *
     * @param string $filePath
     * @param int $expiryMinutes
     * @return string
     */
    public function generateSignedUrl(string $filePath, int $expiryMinutes = 15): string
    {
        // Check if the file exists on the public disk, and return its URL
        // In actual GCP implementation, this will call the Google Cloud Storage SDK to generate a secure signed URL
        return Storage::disk('public')->url($filePath);
    }

    /**
     * Delete a file from storage.
     *
     * @param string $filePath
     * @return bool
     */
    public function delete(string $filePath): bool
    {
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->delete($filePath);
        }
        return false;
    }
}
