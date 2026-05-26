<?php

namespace App\Jobs;

use App\Models\VideoDownload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class FetchVideoMetadata implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $videoDownloadId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $videoDownload = VideoDownload::findOrFail($this->videoDownloadId);

        $videoDownload->update(['status' => 'fetching_metadata']);

        $ytdlpBinary = config('services.ytdlp.binary', 'yt-dlp');
        $cookiesFile = config('services.ytdlp.cookies_file');
        $cookiesBrowser = config('services.ytdlp.cookies_browser');

        // Command: yt-dlp -j --no-playlist <URL>
        $command = [
            $ytdlpBinary,
            '--js-runtimes', 'nodejs,deno',
            '-j',
            '--no-playlist',
            '--playlist-items', '1',
        ];

        // Static cookies.txt file
        if ($cookiesFile && is_file($cookiesFile)) {
            $command[] = '--cookies';
            $command[] = $cookiesFile;
        }
        // Fallback to local browser cookies
        elseif ($cookiesBrowser) {
            $command[] = '--cookies-from-browser';
            $command[] = $cookiesBrowser;
        }

        $command[] = $videoDownload->original_url;

        $process = new Process($command);
        $process->setTimeout(150);

        try {
            $process->run();

            if (!$process->isSuccessful()) {
                $errorOutput = $process->getErrorOutput() ?: $process->getOutput();
                throw new \RuntimeException($errorOutput ?: 'yt-dlp process failed with no output.');
            }

            $output = $process->getOutput();
            $metadata = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to parse metadata JSON output: ' . json_last_error_msg());
            }

            // Extract fields
            $title = $metadata['title'] ?? $metadata['fulltitle'] ?? 'Social Video';
            $thumbnail = $metadata['thumbnail'] ?? null;
            
            // Fallback for thumbnail if not directly present in thumbnail key but exists in thumbnails array
            if (!$thumbnail && !empty($metadata['thumbnails'])) {
                $thumbnails = $metadata['thumbnails'];
                // Get the last thumbnail (usually highest resolution)
                $lastThumb = end($thumbnails);
                $thumbnail = $lastThumb['url'] ?? null;
            }

            $duration = isset($metadata['duration']) ? (int) $metadata['duration'] : null;
            $uploader = $metadata['uploader'] ?? $metadata['channel'] ?? 'Unknown Creator';
            $rawFormats = $metadata['formats'] ?? [];

            // Parse and format options
            $compiledFormats = $this->compileFormatOptions($rawFormats);

            $videoDownload->update([
                'title' => mb_substr($title, 0, 255),
                'thumbnail' => $thumbnail,
                'duration' => $duration,
                'uploader' => mb_substr($uploader, 0, 100),
                'formats' => $compiledFormats,
                'status' => 'metadata_fetched',
            ]);

            Log::info('Video metadata fetched successfully', [
                'id' => $videoDownload->id,
                'title' => $title,
                'formats_count' => count($compiledFormats),
            ]);

        } catch (\Exception $e) {
            $videoDownload->update([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 500),
            ]);

            Log::error('Failed to fetch video metadata', [
                'id' => $videoDownload->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Compile video resolutions and audio options into a clean, pretty structure.
     */
    protected function compileFormatOptions(array $formats): array
    {
        $compiled = [];
        $heights = [];

        // Find the best audio stream size to estimate total combined file size
        $bestAudioSize = 0;
        foreach ($formats as $f) {
            if (($f['vcodec'] ?? 'none') === 'none' && ($f['acodec'] ?? 'none') !== 'none') {
                $size = $f['filesize'] ?? $f['filesize_approx'] ?? 0;
                if ($size > $bestAudioSize) {
                    $bestAudioSize = $size;
                }
            }
        }

        // Extract and sort video resolutions (heights)
        foreach ($formats as $f) {
            $height = $f['height'] ?? null;
            $vcodec = $f['vcodec'] ?? 'none';
            if ($height && $vcodec !== 'none') {
                $heights[] = (int) $height;
            }
        }

        $heights = array_values(array_unique($heights));
        rsort($heights);

        // Standard labels for resolutions
        $supportedQualities = [
            4320 => '8K Ultra HD (4320p)',
            2160 => '4K Ultra HD (2160p)',
            1440 => '2K (1440p)',
            1080 => 'Full HD (1080p)',
            720 => 'HD (720p)',
            480 => '480p',
            360 => '360p',
            240 => '240p',
            144 => '144p',
        ];

        foreach ($heights as $height) {
            $qualityName = $supportedQualities[$height] ?? "{$height}p";

            // Prevent duplicates
            if (collect($compiled)->contains('quality', $qualityName)) {
                continue;
            }

            // Estimate total filesize for this height
            $bestVideoForHeightSize = 0;
            foreach ($formats as $f) {
                if (($f['height'] ?? null) == $height && ($f['vcodec'] ?? 'none') !== 'none') {
                    $size = $f['filesize'] ?? $f['filesize_approx'] ?? 0;
                    if ($size > $bestVideoForHeightSize) {
                        $bestVideoForHeightSize = $size;
                    }
                }
            }

            $estimatedSize = null;
            $totalBytes = $bestVideoForHeightSize + $bestAudioSize;
            if ($totalBytes > 0) {
                $estimatedSize = $this->formatBytes($totalBytes);
            }

            // We download using: bestvideo[height<=HEIGHT]+bestaudio/best[height<=HEIGHT]
            $formatSelector = "bestvideo[height<={$height}]+bestaudio/best[height<={$height}]";

            $compiled[] = [
                'id' => $formatSelector,
                'quality' => $qualityName,
                'ext' => 'mp4',
                'type' => 'video',
                'size' => $estimatedSize ?? 'Unknown size',
            ];
        }

        // Add Audio MP3 Option
        $mp3Size = 'Unknown size';
        if ($bestAudioSize > 0) {
            $mp3Size = $this->formatBytes($bestAudioSize);
        }
        $compiled[] = [
            'id' => 'bestaudio/best',
            'quality' => 'MP3 Audio (High Quality)',
            'ext' => 'mp3',
            'type' => 'audio',
            'size' => $mp3Size,
        ];

        // Add Audio M4A Option
        $m4aSize = 'Unknown size';
        foreach ($formats as $f) {
            if (($f['vcodec'] ?? 'none') === 'none' && ($f['ext'] ?? '') === 'm4a') {
                $size = $f['filesize'] ?? $f['filesize_approx'] ?? 0;
                if ($size > 0) {
                    $m4aSize = $this->formatBytes($size);
                    break;
                }
            }
        }
        $compiled[] = [
            'id' => 'bestaudio[ext=m4a]/best',
            'quality' => 'M4A Audio (Standard)',
            'ext' => 'm4a',
            'type' => 'audio',
            'size' => $m4aSize !== 'Unknown size' ? $m4aSize : $mp3Size,
        ];

        return $compiled;
    }

    /**
     * Format bytes into a human readable string.
     */
    protected function formatBytes(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        $videoDownload = VideoDownload::find($this->videoDownloadId);

        if ($videoDownload) {
            $videoDownload->update([
                'status' => 'failed',
                'error_message' => $exception?->getMessage() ?? 'Failed to fetch video metadata.',
            ]);
        }
    }
}
