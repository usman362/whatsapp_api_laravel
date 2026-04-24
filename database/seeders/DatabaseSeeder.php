<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = 'admin@timhr.es';

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = $user->name ?: 'Admin';

        if (! $user->exists) {
            $user->password = Hash::make('password');
        }

        $user->save();
    }
}
