<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface StorageProvider
{
    /**
     * Upload a file to the provider.
     *
     * @param UploadedFile $file
     * @param string $path Destination folder path
     * @return string Relative path or provider-specific ID/identifier
     */
    public function upload(UploadedFile $file, string $path): string;

    /**
     * Generate a secure temporary signed URL for playback or download.
     *
     * @param string $filePath File path or provider identifier
     * @param int $expiryMinutes Time to live in minutes
     * @return string
     */
    public function generateSignedUrl(string $filePath, int $expiryMinutes = 15): string;

    /**
     * Delete a file or asset from the provider.
     *
     * @param string $filePath File path or provider identifier
     * @return bool
     */
    public function delete(string $filePath): bool;
}
