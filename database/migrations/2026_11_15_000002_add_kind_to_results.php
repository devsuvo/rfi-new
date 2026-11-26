<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** kind = "class" (work done in class) or "homework"; late = homework handed in after its deadline. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->string('kind', 20)->default('class')->after('test_slug');
            $table->boolean('late')->default(false)->after('kind');
        });
    }

    public function down(): void
    {
        Schema::table('results', fn (Blueprint $t) => $t->dropColumn(['kind', 'late']));
    }
};
