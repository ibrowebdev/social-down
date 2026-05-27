<?php

namespace App\Jobs;

use App\Models\VideoDownload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class CleanupOldVideosJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $expiredDownloads = VideoDownload::where('created_at', '<', now()->subHours(2))->get();

        foreach ($expiredDownloads as $download) {
            // Delete the physical file if it exists
            if ($download->file_path) {
                $fullPath = $download->file_path;
                $disk = Storage::disk(config('filesystems.default'));
                if ($disk->exists($fullPath)) {
                    $disk->delete($fullPath);
                }
            }

            // Delete the database record
            $download->delete();
        }
    }
}
