<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Homework is handed in one exercise at a time: which exercise (tab number) a result belongs to. */
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->unsignedTinyInteger('part')->nullable()->after('kind');
        });
    }

    public function down(): void
    {
        Schema::table('results', fn (Blueprint $t) => $t->dropColumn('part'));
    }
};
