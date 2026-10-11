<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $data = $request->validated();
        $this->assertAssigneeIsMember($project, $data['assigned_to'] ?? null);

        // new tasks go to the bottom of the To do column
        $position = (int) $project->tasks()->where('status', Task::TODO)->max('position') + 1;

        $project->tasks()->create($data + [
            'status' => Task::TODO,
            'position' => $position,
        ]);

        return back();
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $data = $request->validated();
        $project = Project::findOrFail($task->project_id);
        $this->assertAssigneeIsMember($project, $data['assigned_to'] ?? null);

        $task->update($data);

        return back();
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return back();
    }

    private function assertAssigneeIsMember(Project $project, ?int $userId): void
    {
        if ($userId !== null && ! $project->members()->whereKey($userId)->exists()) {
            throw ValidationException::withMessages([
                'assigned_to' => 'That person is not a member of this project.',
            ]);
        }
    }
}
