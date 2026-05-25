<?php

use App\Jobs\FetchVideoMetadata;
use App\Jobs\ProcessVideoDownload;
use App\Models\VideoDownload;
use App\Livewire\Downloader;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('it dispatches metadata fetch job on url submission', function () {
    Queue::fake();

    Livewire::test(Downloader::class)
        ->set('url', 'https://www.youtube.com/watch?v=aqz-KE-bpKQ')
        ->call('submit')
        ->assertSet('status', 'fetching_metadata')
        ->assertSet('shouldPoll', true);

    Queue::assertPushed(FetchVideoMetadata::class);
});

test('it dispatches download job when quality is selected', function () {
    Queue::fake();

    $videoDownload = VideoDownload::create([
        'original_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
        'status' => 'metadata_fetched',
        'title' => 'Big Buck Bunny',
        'formats' => [
            [
                'id' => 'bestvideo[height<=1080]+bestaudio/best[height<=1080]',
                'quality' => '1080p (Full HD)',
                'ext' => 'mp4',
                'type' => 'video',
                'size' => '45 MB',
            ]
        ],
    ]);

    Livewire::test(Downloader::class)
        ->set('downloadId', $videoDownload->id)
        ->set('status', 'metadata_fetched')
        ->call('startDownload', 'bestvideo[height<=1080]+bestaudio/best[height<=1080]', '1080p (Full HD)')
        ->assertSet('status', 'processing')
        ->assertSet('shouldPoll', true);

    Queue::assertPushed(ProcessVideoDownload::class);
});
