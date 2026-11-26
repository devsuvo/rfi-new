<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Three roles (admin / teacher / student), student batches,
 * teacher ↔ batch assignment and per-batch class locks.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('batch')->nullable()->after('phone');        // students only, stored lower-case
            $table->timestamp('access_until')->nullable()->after('batch'); // students: 7 months from registration
        });

        // Which batches a teacher looks after.
        Schema::create('teacher_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('batch');
            $table->timestamps();
            $table->unique(['teacher_id', 'batch']);
        });

        // A class opened for a batch. No row = locked.
        Schema::create('class_unlocks', function (Blueprint $table) {
            $table->id();
            $table->string('batch');
            $table->string('module');
            $table->string('test_slug');
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['batch', 'module', 'test_slug']);
        });

        Schema::table('results', function (Blueprint $table) {
            $table->foreignId('marked_by')->nullable()->after('feedback')->constrained('users')->nullOnDelete();
            $table->timestamp('feedback_sent_at')->nullable()->after('marked_by'); // student sees band + feedback only after this
        });

        // Per-student test assignment is replaced by batch locks.
        Schema::dropIfExists('assignments');
    }

    public function down(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module');
            $table->string('test_slug');
            $table->timestamps();
            $table->unique(['user_id', 'module', 'test_slug']);
        });
        Schema::table('results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marked_by');
            $table->dropColumn('feedback_sent_at');
        });
        Schema::dropIfExists('class_unlocks');
        Schema::dropIfExists('teacher_batches');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['batch', 'access_until']));
    }
};
