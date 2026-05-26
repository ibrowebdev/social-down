<?php

namespace App\Helpers;

class ErrorSanitizer
{
    /**
     * Convert raw yt-dlp / system error messages into user-friendly text.
     * The raw error is still logged for debugging — only the user-facing
     * message stored in the database is sanitized here.
     */
    public static function forUser(string $rawError): string
    {
        $lower = mb_strtolower($rawError);

        // ── Video unavailable / removed / private ────────────────────
        if (str_contains($lower, 'video unavailable')
            || str_contains($lower, 'this video is unavailable')
            || str_contains($lower, 'this video has been removed')
            || str_contains($lower, 'video is no longer available')
        ) {
            return 'This video is unavailable or has been removed by the uploader.';
        }

        // ── Private / members-only content ───────────────────────────
        if (str_contains($lower, 'private video')
            || str_contains($lower, 'sign in to confirm')
            || str_contains($lower, 'members only')
            || str_contains($lower, 'join this channel')
        ) {
            return 'This video is private or restricted to members only. We cannot download it.';
        }

        // ── Age-restricted ───────────────────────────────────────────
        if (str_contains($lower, 'age-restricted')
            || str_contains($lower, 'age restricted')
            || str_contains($lower, 'confirm your age')
        ) {
            return 'This video is age-restricted and cannot be downloaded without authentication.';
        }

        // ── Geo-blocked ──────────────────────────────────────────────
        if (str_contains($lower, 'not available in your country')
            || str_contains($lower, 'geo restriction')
            || str_contains($lower, 'geo-restricted')
            || str_contains($lower, 'blocked in your')
        ) {
            return 'This video is not available in the server\'s region due to geographic restrictions.';
        }

        // ── Live stream ──────────────────────────────────────────────
        if (str_contains($lower, 'live event')
            || str_contains($lower, 'is live')
            || str_contains($lower, 'live stream')
        ) {
            return 'Live streams cannot be downloaded. Please try again after the stream has ended.';
        }

        // ── Copyright / DMCA takedown ────────────────────────────────
        if (str_contains($lower, 'copyright')
            || str_contains($lower, 'dmca')
            || str_contains($lower, 'blocked on copyright')
        ) {
            return 'This video has been taken down due to a copyright claim and cannot be downloaded.';
        }

        // ── Unsupported URL / platform ───────────────────────────────
        if (str_contains($lower, 'unsupported url')
            || str_contains($lower, 'no suitable')
            || str_contains($lower, 'is not a valid url')
        ) {
            return 'The URL you entered is not supported. Please check the link and try again.';
        }

        // ── No video formats found ──────────────────────────────────
        if (str_contains($lower, 'requested format')
            || str_contains($lower, 'no video formats')
            || str_contains($lower, 'formats may be missing')
        ) {
            return 'The requested quality is not available for this video. Please try a different format.';
        }

        // ── Network / connection errors ──────────────────────────────
        if (str_contains($lower, 'unable to download')
            || str_contains($lower, 'connection reset')
            || str_contains($lower, 'timed out')
            || str_contains($lower, 'network')
            || str_contains($lower, 'urlopen error')
            || str_contains($lower, 'http error')
        ) {
            return 'A network error occurred while fetching the video. Please try again in a moment.';
        }

        // ── Rate limiting / too many requests ────────────────────────
        if (str_contains($lower, '429')
            || str_contains($lower, 'too many requests')
            || str_contains($lower, 'rate limit')
        ) {
            return 'The platform is temporarily limiting our requests. Please wait a minute and try again.';
        }

        // ── Login required ───────────────────────────────────────────
        if (str_contains($lower, 'login required')
            || str_contains($lower, 'cookies')
            || str_contains($lower, 'sign in')
        ) {
            return 'This content requires authentication and cannot be downloaded at this time.';
        }

        // ── JS runtime (yt-dlp internal) ─────────────────────────────
        if (str_contains($lower, 'javascript runtime')
            || str_contains($lower, 'js runtime')
            || str_contains($lower, 'js-runtimes')
            || str_contains($lower, 'deno')
        ) {
            return 'A temporary server configuration issue occurred. Our team has been notified. Please try again shortly.';
        }

        // ── FFmpeg errors ────────────────────────────────────────────
        if (str_contains($lower, 'ffmpeg')
            || str_contains($lower, 'ffprobe')
            || str_contains($lower, 'muxing')
        ) {
            return 'An error occurred while processing the video file. Please try a different quality or format.';
        }

        // ── Generic fallback ─────────────────────────────────────────
        return 'Something went wrong while processing your request. Please try again or use a different link.';
    }
}
