<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveTaskRequest;
use App\Http\Requests\RejectTaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TaskWorkflowController extends Controller
{
    public function move(MoveTaskRequest $request, Task $task): RedirectResponse
    {
        Gate::authorize('move', $task);

        $status = (string) $request->validated('status');

        // default: drop the card at the bottom of the target column
        $position = $request->filled('position')
            ? $request->integer('position')
            : (int) Task::where('project_id', $task->project_id)
                ->where('status', $status)
                ->max('position') + 1;

        $task->forceFill([
            'status' => $status,
            'position' => $position,
            'finished_at' => $status === Task::FINISHED ? now() : null,
        ])->save();

        return back();
    }

    public function approve(Task $task): RedirectResponse
    {
        Gate::authorize('review', $task);
        $this->requireFinished($task);

        $task->forceFill([
            'status' => Task::APPROVED,
            'approved_at' => now(),
            'reviewed_by' => Auth::id(),
            'review_comment' => null,
        ])->save();

        return back();
    }

    public function reject(RejectTaskRequest $request, Task $task): RedirectResponse
    {
        Gate::authorize('review', $task);
        $this->requireFinished($task);

        $task->forceFill([
            'status' => Task::IN_PROGRESS,
            'finished_at' => null,
            'reviewed_by' => Auth::id(),
            'review_comment' => (string) $request->validated('comment'),
        ])->save();

        return back();
    }

    private function requireFinished(Task $task): void
    {
        if ($task->status !== Task::FINISHED) {
            throw ValidationException::withMessages([
                'status' => 'Only finished tasks can be approved or rejected.',
            ]);
        }
    }
}
