<?php

namespace App\Livewire;

use App\Jobs\FetchVideoMetadata;
use App\Jobs\ProcessVideoDownload;
use App\Models\VideoDownload;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Downloader extends Component
{
    #[Validate('required|url')]
    public string $url = '';

    public ?string $downloadId = null;
    public string $status = 'idle'; // idle, fetching_metadata, metadata_fetched, processing, completed, failed
    public ?string $errorMessage = null;
    public bool $shouldPoll = false;

    // Fetched metadata
    public ?string $title = null;
    public ?string $thumbnail = null;
    public ?int $duration = null;
    public ?string $uploader = null;
    public array $formats = [];

    /**
     * Submit the URL to fetch metadata.
     */
    public function submit(): void
    {
        $this->validate();

        try {
            // Reset state for new downloads
            $this->status = 'fetching_metadata';
            $this->errorMessage = null;
            $this->shouldPoll = true;
            $this->title = null;
            $this->thumbnail = null;
            $this->duration = null;
            $this->uploader = null;
            $this->formats = [];

            $videoDownload = VideoDownload::create([
                'original_url' => $this->url,
                'status' => 'pending',
            ]);

            $this->downloadId = $videoDownload->id;

            FetchVideoMetadata::dispatch($videoDownload->id);
        } catch (\Exception $e) {
            $this->status = 'failed';
            $this->errorMessage = 'Failed to start analysis: ' . $e->getMessage();
            $this->shouldPoll = false;
        }
    }

    /**
     * Poll the download status.
     */
    public function checkStatus(): void
    {
        if (!$this->downloadId) {
            return;
        }

        $videoDownload = VideoDownload::find($this->downloadId);

        if (!$videoDownload) {
            $this->status = 'failed';
            $this->errorMessage = 'Record not found.';
            $this->shouldPoll = false;
            return;
        }

        $dbStatus = $videoDownload->status;

        // If we are waiting for metadata
        if ($this->status === 'fetching_metadata') {
            if ($dbStatus === 'metadata_fetched') {
                $this->title = $videoDownload->title;
                $this->thumbnail = $videoDownload->thumbnail;
                $this->duration = $videoDownload->duration;
                $this->uploader = $videoDownload->uploader;
                $this->formats = $videoDownload->formats ?? [];
                
                $this->status = 'metadata_fetched';
                $this->shouldPoll = false;
            } elseif ($dbStatus === 'failed') {
                $this->status = 'failed';
                $this->errorMessage = $videoDownload->error_message ?? 'Failed to analyze video URL.';
                $this->shouldPoll = false;
            }
        }
        // If we are downloading the selected format
        elseif ($this->status === 'processing') {
            if ($dbStatus === 'completed') {
                $this->status = 'completed';
                $this->shouldPoll = false;
                $this->dispatch('trigger-download', url: route('download.serve', $this->downloadId));
            } elseif ($dbStatus === 'failed') {
                $this->status = 'failed';
                $this->errorMessage = $videoDownload->error_message ?? 'Download failed.';
                $this->shouldPoll = false;
            }
        }
    }

    /**
     * Start downloading a specific format.
     */
    public function startDownload(string $formatId, string $qualityName): void
    {
        if (!$this->downloadId) {
            return;
        }

        $videoDownload = VideoDownload::find($this->downloadId);

        if (!$videoDownload) {
            $this->status = 'failed';
            $this->errorMessage = 'Download record not found.';
            $this->shouldPoll = false;
            return;
        }

        try {
            $this->status = 'processing';
            $this->shouldPoll = true;

            $videoDownload->update([
                'selected_format' => $formatId,
                'selected_quality' => $qualityName,
                'status' => 'processing',
                'error_message' => null, // clear previous errors
            ]);

            ProcessVideoDownload::dispatch($this->downloadId);
        } catch (\Exception $e) {
            $this->status = 'failed';
            $this->errorMessage = 'Failed to start download: ' . $e->getMessage();
            $this->shouldPoll = false;
        }
    }

    /**
     * Format duration into a pretty human-readable string.
     */
    public function getFormattedDuration(): string
    {
        if (!$this->duration) {
            return '';
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Reset everything for a new download.
     */
    public function resetDownloader(): void
    {
        $this->reset([
            'url', 'downloadId', 'status', 'errorMessage', 'shouldPoll',
            'title', 'thumbnail', 'duration', 'uploader', 'formats'
        ]);
    }

    public function render()
    {
        return view('livewire.downloader');
    }
}
