<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Creates the first admin account (change the password after logging in). */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@hz.com'],
            ['name' => 'admin', 'password' => '10102020', 'role' => 'admin']
        );
    }
}
