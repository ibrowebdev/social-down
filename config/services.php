<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ytdlp' => [
        'binary' => strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? env('YTDLP_PATH', 'yt-dlp') // Local Windows uses your WinGet path from .env
            : base_path('bin/yt-dlp'),    // Live Laravel Cloud automatically uses the bundled Linux binary

        'ffmpeg_dir' => strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? env('FFMPEG_DIR')           // Local Windows uses your local FFmpeg directory
            : null,                       // On Laravel Cloud, FFmpeg is typically global (or handled via buildpacks)

        'cookies_browser' => env('YTDLP_COOKIES_BROWSER'),
        'cookies_file' => strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? env('YTDLP_COOKIES_FILE')   // Local Windows absolute path
            : base_path('cookies.txt'),    // Cloud uses cookies.txt committed at project root
    ],
];
