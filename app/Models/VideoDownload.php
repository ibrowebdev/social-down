<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VideoDownload extends Model
{
    use HasUuids;

    protected $fillable = [
        'original_url',
        'title',
        'thumbnail',
        'duration',
        'uploader',
        'formats',
        'selected_format',
        'selected_quality',
        'status',
        'file_path',
        'error_message',
    ];

    protected $casts = [
        'formats' => 'array',
    ];
}
