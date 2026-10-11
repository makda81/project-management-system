<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $manager = User::factory()->create([
            'name' => 'Maya Manager',
            'email' => 'manager@example.com',
        ]);
        $manager->syncRoles('manager');

        $members = collect(['Abel', 'Beza', 'Chala'])->map(function ($name) {
            $user = User::factory()->create([
                'name' => $name,
                'email' => strtolower($name).'@example.com',
            ]);
            $user->syncRoles('member');

            return $user;
        });

        $project = Project::create([
            'owner_id' => $manager->id,
            'name' => 'Internship Portal',
            'description' => 'A small portal for the internship program.',
            'start_date' => today(),
            'due_date' => today()->addDays(30),
        ]);
        $project->members()->attach($members->pluck('id'));

        $samples = [
            ['Design the login page', Task::TODO, 'high', 5],
            ['Set up the database', Task::IN_PROGRESS, 'high', 2],
            ['Write the user guide', Task::TODO, 'low', 20],
            ['Build the dashboard', Task::IN_PROGRESS, 'medium', -2],
            ['Prepare the demo data', Task::FINISHED, 'medium', 1],
        ];

        foreach ($samples as $i => [$title, $status, $priority, $dueIn]) {
            Task::create([
                'project_id' => $project->id,
                'assigned_to' => $members[$i % 3]->id,
                'title' => $title,
                'status' => $status,
                'priority' => $priority,
                'start_date' => today(),
                'due_date' => today()->addDays($dueIn),
                'position' => $i,
            ]);
        }
    }
}
