<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A teacher's own time limit for one part of a test, for one batch.
 * part = "1", "2", … (exercise tab number) or "all" (one timer for the whole test).
 * No row = the default time (2 minutes per question, or 60 for a full test).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_times', function (Blueprint $table) {
            $table->id();
            $table->string('batch');
            $table->string('module');
            $table->string('test_slug');
            $table->string('part', 10);
            $table->unsignedSmallInteger('minutes');
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['batch', 'module', 'test_slug', 'part']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_times');
    }
};
