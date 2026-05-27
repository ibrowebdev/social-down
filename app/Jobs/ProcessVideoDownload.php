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

    public int $timeout = 660;

    public function __construct(
        public string $videoDownloadId
    ) {}

    public function handle(): void
    {
        $videoDownload = VideoDownload::findOrFail($this->videoDownloadId);
        $videoDownload->update(['status' => 'processing']);

        $outputDir = storage_path('app/private/downloads');

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

        $jsRuntimes = $this->detectJsRuntimes();

        $command = [
            $ytdlpBinary,
            '-f', $format,
        ];

        if ($jsRuntimes) {
            $command[] = '--js-runtimes';
            $command[] = $jsRuntimes;
        }

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

        if ($ffmpegDir) {
            $command[] = '--ffmpeg-location';
            $command[] = $ffmpegDir;
        }

        if ($cookiesFile && is_file($cookiesFile)) {
            $command[] = '--cookies';
            $command[] = $cookiesFile;
        } elseif ($cookiesBrowser) {
            $command[] = '--cookies-from-browser';
            $command[] = $cookiesBrowser;
        }

        // ✅ FIX 1: User-Agent
        $command[] = '--user-agent';
        $command[] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';
        $command[] = '--add-header';
        $command[] = 'Accept-Language:en-US,en;q=0.9';

        // ✅ FIX 2: Client fallback loop
        $strategies = [
            ['--extractor-args', 'youtube:player_client=web'],
            ['--extractor-args', 'youtube:player_client=android'],
            ['--extractor-args', 'youtube:player_client=ios'],
        ];

        $process = null;

        foreach ($strategies as $strategy) {
            $fullCommand = array_merge($command, $strategy, [$videoDownload->original_url]);
            $process = new Process($fullCommand);
            $process->setTimeout(600);
            $process->run();

            if ($process->isSuccessful()) {
                break;
            }
        }

        try {
            if ($process->isSuccessful()) {
                // Find the actual file yt-dlp created (extension may differ from expected)
                $actualFiles = glob($outputDir . DIRECTORY_SEPARATOR . $videoDownload->id . '.*');

                if (empty($actualFiles)) {
                    throw new \RuntimeException('yt-dlp reported success but no output file was found.');
                }

                // Use the first matching file (there should only be one)
                $actualFile = $actualFiles[0];
                $actualExt = pathinfo($actualFile, PATHINFO_EXTENSION);
                $filePath = 'downloads/' . $videoDownload->id . '.' . $actualExt;

                $videoDownload->update([
                    'status'    => 'completed',
                    'file_path' => $filePath,
                ]);

                Log::info('Media download completed', [
                    'id'   => $videoDownload->id,
                    'path' => $filePath,
                ]);
            } else {
                $errorOutput = $process->getErrorOutput() ?: $process->getOutput();

                $videoDownload->update([
                    'status'        => 'failed',
                    'error_message' => ErrorSanitizer::forUser($errorOutput),
                ]);

                Log::error('Video download failed', [
                    'id'    => $videoDownload->id,
                    'error' => $errorOutput,
                ]);
            }
        } catch (\Exception $e) {
            $videoDownload->update([
                'status'        => 'failed',
                'error_message' => ErrorSanitizer::forUser($e->getMessage()),
            ]);

            Log::error('Video download exception', [
                'id'    => $videoDownload->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $videoDownload = VideoDownload::find($this->videoDownloadId);

        if ($videoDownload) {
            $videoDownload->update([
                'status'        => 'failed',
                'error_message' => ErrorSanitizer::forUser($exception?->getMessage() ?? ''),
            ]);
        }
    }

    protected function detectJsRuntimes(): ?string
    {
        $runtimes = [];

        $nodePaths = [
            '/usr/local/bin/node',
            '/usr/bin/node',
            '/usr/local/nodejs/bin/node',
            '/opt/node/bin/node',
        ];

        foreach ($nodePaths as $path) {
            if (is_file($path) && is_executable($path)) {
                $runtimes[] = "node:{$path}";
                break;
            }
        }

        if (empty($runtimes)) {
            $which = trim((string) shell_exec('which node 2>/dev/null'));
            if ($which && is_executable($which)) {
                $runtimes[] = "node:{$which}";
            }
        }

        foreach (['/usr/local/bin/deno', '/usr/bin/deno'] as $path) {
            if (is_file($path) && is_executable($path)) {
                $runtimes[] = "deno:{$path}";
                break;
            }
        }

        foreach (['/usr/local/bin/bun', '/usr/bin/bun'] as $path) {
            if (is_file($path) && is_executable($path)) {
                $runtimes[] = "bun:{$path}";
                break;
            }
        }

        return empty($runtimes) ? null : implode(',', $runtimes);
    }
}