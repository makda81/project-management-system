<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /** Only the assigned member can move a task, and never after approval. */
    public function move(User $user, Task $task): bool
    {
        return $user->can('task.move')
            && (int) $task->assigned_to === (int) $user->id
            && $task->status !== Task::APPROVED;
    }

    /** Approve and reject: the manager who owns the task's project. */
    public function review(User $user, Task $task): bool
    {
        return $user->can('task.approve')
            && Project::whereKey($task->project_id)->where('owner_id', $user->id)->exists();
    }
}
