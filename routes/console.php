<?php

use App\Jobs\CleanupOldVideosJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

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
Schedule::job(new CleanupOldVideosJob)->hourly()->name('cleanup-old-downloads')->withoutOverlapping();
