<?php

use App\Models\VideoDownload;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Cleanup: Remove old video downloads
|--------------------------------------------------------------------------
|
| Every hour, delete VideoDownload records older than 2 hours along
| with their associated .mp4 files from the private storage disk.
|
*/
Schedule::call(function () {
    $expiredDownloads = VideoDownload::where('created_at', '<', now()->subHours(2))->get();

    foreach ($expiredDownloads as $download) {
        // Delete the physical file if it exists
        if ($download->file_path) {
            $fullPath = $download->file_path;
            if (Storage::disk('local')->exists($fullPath)) {
                Storage::disk('local')->delete($fullPath);
            }
        }

        // Delete the database record
        $download->delete();
    }
})->hourly()->name('cleanup-old-downloads')->withoutOverlapping();
