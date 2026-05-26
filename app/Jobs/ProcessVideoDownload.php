<?php

namespace App\Jobs;

use App\Helpers\ErrorSanitizer;
use App\Models\VideoDownload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ProcessVideoDownload implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 660;

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

        $videoDownload->update(['status' => 'processing']);

        $outputDir = storage_path('app/private/downloads');

        // Ensure the downloads directory exists
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $outputTemplate = $outputDir . DIRECTORY_SEPARATOR . $videoDownload->id . '.%(ext)s';

        $ytdlpBinary = config('services.ytdlp.binary', 'yt-dlp');
        $ffmpegDir = config('services.ytdlp.ffmpeg_dir');
        $cookiesBrowser = config('services.ytdlp.cookies_browser');
        $cookiesFile = config('services.ytdlp.cookies_file');

        $format = $videoDownload->selected_format ?? 'bestvideo+bestaudio/best';
        $quality = strtolower($videoDownload->selected_quality ?? '');
        $isAudio = str_contains($quality, 'audio') || str_contains($quality, 'mp3') || str_contains($quality, 'm4a');

        $command = [
            $ytdlpBinary,
            '--js-runtimes', 'node,deno',
            '-f',
            $format,
        ];

        if ($isAudio) {
            $audioFormat = str_contains($quality, 'm4a') ? 'm4a' : 'mp3';
            $command[] = '--extract-audio';
            $command[] = '--audio-format';
            $command[] = $audioFormat;
        } else {
            $command[] = '--merge-output-format';
            $command[] = 'mp4';
        }

        $command[] = '-o';
        $command[] = $outputTemplate;
        $command[] = '--no-playlist';
        $command[] = '--no-overwrites';

        // Point yt-dlp to ffmpeg if a custom path is configured
        if ($ffmpegDir) {
            $command[] = '--ffmpeg-location';
            $command[] = $ffmpegDir;
        }

        // Use a static cookies.txt file if configured (most reliable)
        if ($cookiesFile && is_file($cookiesFile)) {
            $command[] = '--cookies';
            $command[] = $cookiesFile;
        }
        // Fallback to local browser cookies (can fail if Chrome/Edge locks its DB while open)
        elseif ($cookiesBrowser) {
            $command[] = '--cookies-from-browser';
            $command[] = $cookiesBrowser;
        }

        $command[] = $videoDownload->original_url;

        $process = new Process($command);

        $process->setTimeout(600);

        try {
            $process->run();

            if ($process->isSuccessful()) {
                $ext = $isAudio ? (str_contains($quality, 'm4a') ? 'm4a' : 'mp3') : 'mp4';
                $filePath = 'downloads/' . $videoDownload->id . '.' . $ext;

                $videoDownload->update([
                    'status' => 'completed',
                    'file_path' => $filePath,
                ]);

                Log::info('Media download completed', [
                    'id' => $videoDownload->id,
                    'path' => $filePath,
                ]);
            } else {
                $errorOutput = $process->getErrorOutput() ?: $process->getOutput();

                $videoDownload->update([
                    'status' => 'failed',
                    'error_message' => ErrorSanitizer::forUser($errorOutput),
                ]);

                Log::error('Video download failed', [
                    'id' => $videoDownload->id,
                    'error' => $errorOutput,
                ]);
            }
        } catch (\Exception $e) {
            $videoDownload->update([
                'status' => 'failed',
                'error_message' => ErrorSanitizer::forUser($e->getMessage()),
            ]);

            Log::error('Video download exception', [
                'id' => $videoDownload->id,
                'error' => $e->getMessage(),
            ]);
        }
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
                'error_message' => ErrorSanitizer::forUser($exception?->getMessage() ?? ''),
            ]);
        }
    }
}
