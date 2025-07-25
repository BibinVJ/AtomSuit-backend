<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function uploadToS3(UploadedFile $file, string $folder = 'uploads'): string
    {
        $path = $file->store($folder, 's3');
        return Storage::disk('s3')->url($path);
    }

    public function deleteFromS3(string $url): void
    {
        $parsed = parse_url($url);
        if (!isset($parsed['path'])) return;

        // remove leading slash
        $path = ltrim($parsed['path'], '/');
        Storage::disk('s3')->delete($path);
    }
}
