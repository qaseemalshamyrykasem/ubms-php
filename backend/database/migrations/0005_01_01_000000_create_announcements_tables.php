<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Announcements
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // author (rep)
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('body');
            $table->enum('type', [
                'holiday', 'assignment', 'lecture', 'schedule',
                'general', 'urgent', 'emergency', 'meeting', 'important'
            ])->default('general');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('send_telegram')->default(false);
            $table->boolean('telegram_sent')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['batch_id', 'is_published', 'published_at']);
            $table->index(['batch_id', 'type']);
            $table->index('scheduled_at');
            $table->index('is_pinned');
        });

        // Announcement attachments
        Schema::create('announcement_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('file_type', 50);
            $table->bigInteger('file_size');
            $table->timestamps();
            $table->index('announcement_id');
        });

        // Announcement read tracking
        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->timestamps();
            $table->unique(['announcement_id', 'user_id']);
        });

        // Announcement templates (rep time-savers)
        Schema::create('announcement_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->longText('body');
            $table->enum('type', [
                'holiday', 'assignment', 'lecture', 'schedule',
                'general', 'urgent', 'emergency', 'meeting', 'important'
            ])->default('general');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_templates');
        Schema::dropIfExists('announcement_reads');
        Schema::dropIfExists('announcement_attachments');
        Schema::dropIfExists('announcements');
    }
};
