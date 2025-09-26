<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Str;

class FileHelper
{

    static function uploadFileToS3($file, string $upload_path)
    {

        $path = $file->store($upload_path, 's3');

        $setVisibility = Storage::setVisibility($path, 'public');

        // return ['sdsd' => $setVisibility];

        $url = Storage::url($path);

        return $url;
        
    }

    /**
     * Store an uploaded file into the given disk and path and return a public URL.
     * disk: 'public' or 's3' (default: 'public')
     */
    public static function storeUploadedFile($file, string $uploadPath, string $disk = 'public'): string
    {
        $path = $file->store($uploadPath, $disk);

        // ensure visibility when using public disk
        if ($disk === 'public') {
            Storage::disk('public')->setVisibility($path, 'public');
        } else {
            Storage::setVisibility($path, 'public');
        }

        return Storage::url($path);
    }

    /**
     * Download a remote file (image) and store it in the given storage path on the public disk.
     * Returns the public URL on success or null on failure.
     */
    public static function downloadRemoteFileToStorage(string $url, string $storagePath): ?string
    {
        try {
            $contents = @file_get_contents($url);
            if (!$contents) return null;

            Storage::disk('public')->put($storagePath, $contents);
            Storage::disk('public')->setVisibility($storagePath, 'public');

            return Storage::url($storagePath);
        } catch (\Throwable $e) {
            return null;
        }
    }

    static function generateZip(array $fileUrls, string $fileName, string $storagePath): array
    {
        // Create a new ZIP archive
        $zip = new ZipArchive;

        // generate zip file name
        $fileName = $fileName . '.zip';

        // get storage path
        $zipFilePath = storage_path($storagePath . $fileName);

        // open ZipArchive and check if it is open
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return ['error' => 'Failed to create ZIP archive.'];
        }

        // Download each file from S3 and add it to the ZIP archive
        foreach ($fileUrls as $url) {
            // Extract the filename from the URL
            $filename = Str::random(3) . basename($url);

            // Download the file from the URL
            $fileContents = file_get_contents($url);

            // Add the file to the ZIP archive
            $zip->addFromString($filename, $fileContents);
        }

        // Close the ZIP archive
        $zip->close();

        // rerun
        return [
            'zip_file_path' => $zipFilePath,
            'zip_file_name' => $fileName
        ];
    }
}
