<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->after('email');
            $table->string('phone')->nullable()->after('role');
        });

        // Correct answers for one test page (reading class).
        Schema::create('answer_keys', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('test_slug');
            $table->string('test_name');
            $table->json('answers'); // {"p1_q1": "A", "p1_q7": "£50 deposit / deposit of £50", ...}
            $table->timestamps();
            $table->unique(['module', 'test_slug']);
        });

        // One student submission.
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module');
            $table->string('test_slug');
            $table->json('answers');
            $table->unsignedSmallInteger('score')->nullable();
            $table->unsignedSmallInteger('total')->nullable();
            $table->decimal('band', 3, 1)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });

        Schema::create('promo_popups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('image_url')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Which tests a student may open.
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module');
            $table->string('test_slug');
            $table->timestamps();
            $table->unique(['user_id', 'module', 'test_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('promo_popups');
        Schema::dropIfExists('results');
        Schema::dropIfExists('answer_keys');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'phone']));
    }
};
