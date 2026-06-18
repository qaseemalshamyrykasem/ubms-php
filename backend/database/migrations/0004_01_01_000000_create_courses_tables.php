<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Courses
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('name_ar');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->integer('credit_hours')->default(3);
            $table->string('instructor_name')->nullable();
            $table->string('instructor_email')->nullable();
            $table->string('instructor_phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('department_id');
            $table->index('code');
        });

        // Course-Batch (many-to-many with level)
        Schema::create('batch_course', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->string('semester')->nullable();
            $table->timestamps();
            $table->unique(['batch_id', 'course_id']);
        });

        // Course files / resources
        Schema::create('course_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->string('file_type', 50);
            $table->bigInteger('file_size');
            $table->text('description')->nullable();
            $table->enum('type', ['syllabus', 'lecture', 'reference', 'assignment', 'other'])->default('lecture');
            $table->timestamps();
            $table->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_files');
        Schema::dropIfExists('batch_course');
        Schema::dropIfExists('courses');
    }
};
