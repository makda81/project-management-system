<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Three projects owned by the manager, each with its own members.
     * Day offsets are relative to today so the demo never looks stale.
     */
    public function run(): void
    {
        $manager = User::where('email', 'manager@example.com')->firstOrFail();

        $projects = [
            [
                'name' => 'Internship Portal',
                'description' => 'A small portal for the internship program.',
                'start' => -3,
                'due' => 25,
                'members' => ['abel', 'beza', 'chala'],
            ],
            [
                'name' => 'Mobile App Prototype',
                'description' => 'Design and test a mobile prototype for new interns.',
                'start' => 2,
                'due' => 40,
                'members' => ['abel', 'dawit', 'eden'],
            ],
            [
                'name' => 'Data Dashboard',
                'description' => 'Collect weekly progress numbers and show them on a dashboard.',
                'start' => -12,
                'due' => 18,
                'members' => ['beza', 'chala', 'fikir'],
            ],
        ];

        foreach ($projects as $data) {
            $project = Project::where('name', $data['name'])
                ->where('owner_id', $manager->id)
                ->first() ?? new Project;

            $project->forceFill([
                'name' => $data['name'],
                'description' => $data['description'],
                'start_date' => now()->addDays($data['start'])->toDateString(),
                'due_date' => now()->addDays($data['due'])->toDateString(),
                'owner_id' => $manager->id,
            ])->save();

            $memberIds = User::whereIn(
                'email',
                array_map(fn (string $n) => $n.'@example.com', $data['members'])
            )->pluck('id');

            $project->members()->syncWithoutDetaching($memberIds);
        }
    }
}
