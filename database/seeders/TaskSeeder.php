<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Tasks in every column of the board, so each screen has something to show:
     * To do, In progress, Finished (waiting for review), Approved, one task
     * that was rejected with a comment, and one overdue task.
     */
    public function run(): void
    {
        $manager = User::where('email', 'manager@example.com')->firstOrFail();

        // [title, description, assignee, priority, status, start offset, due offset, reject comment]
        $plan = [
            'Internship Portal' => [
                ['Design the login page', 'Sketch and build a clean login screen.', 'abel', 'high', Task::TODO, 0, 7, null],
                ['Write the user guide', 'Explain how interns use the board.', 'chala', 'low', Task::TODO, 1, 18, null],
                ['Set up the database', 'Create tables and demo data.', 'beza', 'high', Task::IN_PROGRESS, -2, 3, null],
                ['Build the dashboard', 'Show project progress on the home page.', 'abel', 'medium', Task::IN_PROGRESS, -6, -2, null],
                ['Prepare the demo data', 'Add realistic users, projects and tasks.', 'beza', 'medium', Task::FINISHED, -4, 1, null],
                ['Create the project plan', 'List milestones and owners.', 'chala', 'medium', Task::APPROVED, -3, 0, null],
                ['Add form validation', 'Show clear errors on every form.', 'chala', 'high', Task::IN_PROGRESS, -3, 4, 'Please also validate the due date and add a test.'],
            ],
            'Mobile App Prototype' => [
                ['Collect user needs', 'Interview three interns about their daily work.', 'abel', 'medium', Task::TODO, 2, 9, null],
                ['Draw the screens', 'Make low-fidelity sketches of five screens.', 'dawit', 'high', Task::TODO, 3, 12, null],
                ['Choose the color palette', 'Pick colors that work in light and dark mode.', 'eden', 'low', Task::IN_PROGRESS, 2, 8, null],
                ['Build a clickable mockup', 'Link the sketches into a simple prototype.', 'dawit', 'medium', Task::FINISHED, 2, 10, null],
            ],
            'Data Dashboard' => [
                ['Define the weekly numbers', 'Agree on what to measure each week.', 'beza', 'high', Task::APPROVED, -11, -6, null],
                ['Write the import script', 'Load numbers from a spreadsheet.', 'fikir', 'medium', Task::IN_PROGRESS, -8, 2, null],
                ['Draw the first chart', 'Show weekly progress as a line chart.', 'chala', 'medium', Task::FINISHED, -6, 4, null],
                ['Review the numbers with the team', 'Check the first results together.', 'fikir', 'low', Task::TODO, -1, 14, null],
            ],
        ];

        foreach ($plan as $projectName => $tasks) {
            $project = Project::where('name', $projectName)
                ->where('owner_id', $manager->id)
                ->firstOrFail();

            $positions = [];

            foreach ($tasks as [$title, $description, $assignee, $priority, $status, $start, $due, $comment]) {
                $positions[$status] = ($positions[$status] ?? -1) + 1;

                $task = Task::where('project_id', $project->id)
                    ->where('title', $title)
                    ->first() ?? new Task;

                $assigneeId = User::where('email', $assignee . '@example.com')->value('id');
                $isReviewed = $status === Task::APPROVED || $comment !== null;

                $task->forceFill([
                    'project_id' => $project->id,
                    'title' => $title,
                    'description' => $description,
                    'assigned_to' => $assigneeId,
                    'priority' => $priority,
                    'status' => $status,
                    'position' => $positions[$status],
                    'start_date' => now()->addDays($start)->toDateString(),
                    'due_date' => now()->addDays($due)->toDateString(),
                    'finished_at' => in_array($status, [Task::FINISHED, Task::APPROVED], true) ? now()->subDay() : null,
                    'approved_at' => $status === Task::APPROVED ? now() : null,
                    'reviewed_by' => $isReviewed ? $manager->id : null,
                    'review_comment' => $comment,
                ])->save();
            }
        }
    }
}
