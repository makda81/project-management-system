<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Order matters: roles first, then users (who get roles),
        // then projects (owned by the manager), then tasks (in projects).
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            ProjectSeeder::class,
            TaskSeeder::class,
        ]);
    }
}
