<?php

namespace App\Services\Storage\Providers;

use App\Contracts\StorageProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class LocalProvider implements StorageProvider
{
    /**
     * Upload a file to the local public disk.
     */
    public function upload(UploadedFile $file, string $path): string
    {
        return $file->store($path, 'public');
    }

    /**
     * Generate a secure temporary signed route URL for local streaming/viewing.
     */
    public function generateSignedUrl(string $filePath, int $expiryMinutes = 15): string
    {
        // Generate a secure temporary signed route URL
        return URL::temporarySignedRoute(
            'local.storage.serve',
            now()->addMinutes($expiryMinutes),
            ['path' => $filePath]
        );
    }

    /**
     * Delete a file from the local public disk.
     */
    public function delete(string $filePath): bool
    {
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->delete($filePath);
        }
        return false;
    }
}
