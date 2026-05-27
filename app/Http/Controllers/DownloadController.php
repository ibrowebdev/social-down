<?php

namespace App\Http\Controllers;

use App\Models\VideoDownload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function serveFile(string $id): StreamedResponse
    {
        $videoDownload = VideoDownload::findOrFail($id);

        if ($videoDownload->status !== 'completed' || !$videoDownload->file_path) {
            Log::warning('Download attempted but file not ready', [
                'id' => $id,
                'status' => $videoDownload->status,
                'file_path' => $videoDownload->file_path,
            ]);
            abort(404, 'File is not ready for download.');
        }

        $disk = Storage::disk(config('filesystems.default'));
        $fullPath = $videoDownload->file_path;

        // If the stored path doesn't exist, try finding the file with any extension
        if (!$disk->exists($fullPath)) {
            // For local disks we can glob, for S3 we would list objects, but usually 
            // if we just uploaded it with the correct extension, this branch shouldn't hit.
            Log::error('Download file not found on disk', [
                'id' => $id,
                'file_path' => $fullPath,
                'disk' => config('filesystems.default'),
            ]);
            abort(404, 'File not found on disk.');
        }

        $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'mp4';

        $contentTypes = [
            'mp3'  => 'audio/mpeg',
            'm4a'  => 'audio/mp4',
            'mp4'  => 'video/mp4',
            'webm' => 'video/webm',
            'mkv'  => 'video/x-matroska',
            'ogg'  => 'audio/ogg',
            'opus' => 'audio/opus',
        ];

        $contentType = $contentTypes[$ext] ?? 'application/octet-stream';

        $slug = \Illuminate\Support\Str::slug($videoDownload->title ?: 'video');
        $filename = ($slug ?: 'video') . '-' . substr($videoDownload->id, 0, 8) . '.' . $ext;

        return $disk->download($fullPath, $filename, [
            'Content-Type' => $contentType,
        ]);
    }
}
