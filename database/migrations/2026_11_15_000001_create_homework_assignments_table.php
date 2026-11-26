<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Homework a teacher has set for one batch: the "Homework …" tabs of a class sheet.
 * No row = the homework is not assigned, so students of that batch do not see it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('batch');
            $table->string('module');
            $table->string('test_slug');
            $table->timestamp('due_at')->nullable();
            $table->string('note', 500)->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['batch', 'module', 'test_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_assignments');
    }
};
