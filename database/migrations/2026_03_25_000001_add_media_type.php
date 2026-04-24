<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->enum('type', ['image', 'video'])->default('image')->after('alt');
            $table->string('mime_type')->nullable()->after('type');
            $table->unsignedInteger('duration')->nullable()->after('mime_type');
            $table->string('thumbnail_path')->nullable()->after('duration');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['type', 'mime_type', 'duration', 'thumbnail_path']);
        });
    }
};
