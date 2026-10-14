<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * One manager and six members. Every password is "password".
     */
    public function run(): void
    {
        $people = [
            ['Maya Manager', 'manager@example.com', 'manager'],
            ['Abel', 'abel@example.com', 'member'],
            ['Beza', 'beza@example.com', 'member'],
            ['Chala', 'chala@example.com', 'member'],
            ['Dawit', 'dawit@example.com', 'member'],
            ['Eden', 'eden@example.com', 'member'],
            ['Fikir', 'fikir@example.com', 'member'],
        ];

        foreach ($people as [$name, $email, $role]) {
            $user = User::firstOrNew(['email' => $email]);

            $user->forceFill([
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ])->save();

            $user->syncRoles($role);
        }
    }
}
