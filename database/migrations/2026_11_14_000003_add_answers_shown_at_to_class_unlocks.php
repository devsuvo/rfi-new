<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** When set, the teacher is showing this class's correct answers to the batch. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_unlocks', function (Blueprint $table) {
            $table->timestamp('answers_shown_at')->nullable()->after('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('class_unlocks', fn (Blueprint $t) => $t->dropColumn('answers_shown_at'));
    }
};
