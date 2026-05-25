<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('video_downloads', function (Blueprint $table) {
            $table->string('title')->nullable()->after('original_url');
            $table->text('thumbnail')->nullable()->after('title');
            $table->integer('duration')->nullable()->after('thumbnail');
            $table->string('uploader')->nullable()->after('duration');
            $table->json('formats')->nullable()->after('uploader');
            $table->string('selected_format')->nullable()->after('formats');
            $table->string('selected_quality')->nullable()->after('selected_format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_downloads', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'thumbnail',
                'duration',
                'uploader',
                'formats',
                'selected_format',
                'selected_quality',
            ]);
        });
    }
};
