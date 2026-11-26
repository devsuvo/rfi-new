<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Each student / teacher belongs to one course: Reading for IELTS or Writing for IELTS. Admins see both. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('course', 20)->nullable()->after('role');
        });
        // everyone already registered here was a Reading for IELTS user
        DB::table('users')->whereIn('role', ['student', 'teacher'])->update(['course' => 'reading']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('course');
        });
    }
};
