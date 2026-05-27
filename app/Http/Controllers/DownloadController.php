<?php

namespace App\Http\Controllers;

use App\Models\VideoDownload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    /**
     * Serve a completed video file to the browser as a download.
     */
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

        $disk = Storage::disk('local');

        $fullPath = $videoDownload->file_path;

        if (!$disk->exists($fullPath)) {
            Log::error('Download file not found on disk', [
                'id' => $id,
                'file_path' => $fullPath,
                'disk_root' => config('filesystems.disks.local.root'),
                'absolute_path' => storage_path('app/private/' . $fullPath),
                'file_exists_check' => file_exists(storage_path('app/private/' . $fullPath)),
            ]);
            abort(404, 'File not found on disk.');
        }

        $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'mp4';
        
        // Map content types
        $contentTypes = [
            'mp3' => 'audio/mpeg',
            'm4a' => 'audio/mp4',
            'mp4' => 'video/mp4',
        ];
        
        $contentType = $contentTypes[$ext] ?? 'application/octet-stream';

        // Generate descriptive filename using video title
        $slug = \Illuminate\Support\Str::slug($videoDownload->title ?: 'video');
        $filename = ($slug ?: 'video') . '-' . substr($videoDownload->id, 0, 8) . '.' . $ext;

        return response()->streamDownload(function () use ($disk, $fullPath) {
            $stream = $disk->readStream($fullPath);
            fpassthru($stream);
            fclose($stream);
        }, $filename, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
