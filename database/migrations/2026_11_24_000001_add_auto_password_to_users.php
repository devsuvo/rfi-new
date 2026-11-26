<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/** Remembers whether a user's password is still the auto one (= email before "@"), so lists can copy it without slow hash checks. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('auto_password')->default(false);
        });
        foreach (DB::table('users')->where('email', 'like', '%@hz.com')->get(['id', 'email', 'password']) as $u) {
            if (Hash::check(strstr($u->email, '@', true), $u->password)) {
                DB::table('users')->where('id', $u->id)->update(['auto_password' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('auto_password'));
    }
};
