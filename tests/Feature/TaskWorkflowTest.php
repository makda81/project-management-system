<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private User $member;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        $this->member = User::factory()->create();
        $this->member->assignRole('member');

        $this->project = Project::create([
            'owner_id' => $this->manager->id,
            'name' => 'Test project',
            'description' => 'For tests',
            'start_date' => today(),
            'due_date' => today()->addDays(10),
        ]);
        $this->project->members()->attach($this->member->id);
    }

    private function makeTask(string $status = Task::TODO, ?User $assignee = null): Task
    {
        return Task::create([
            'project_id' => $this->project->id,
            'assigned_to' => ($assignee ?? $this->member)->id,
            'title' => 'Sample task',
            'status' => $status,
            'priority' => 'medium',
            'start_date' => today(),
            'due_date' => today()->addDays(3),
            'position' => 0,
        ]);
    }

    public function test_member_can_move_own_task_and_finished_at_is_set(): void
    {
        $task = $this->makeTask(Task::IN_PROGRESS);

        $this->actingAs($this->member)
            ->patch(route('tasks.move', $task), ['status' => Task::FINISHED])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame(Task::FINISHED, $task->status);
        $this->assertNotNull($task->finished_at);
    }

    public function test_member_cannot_move_someone_elses_task(): void
    {
        $other = User::factory()->create();
        $other->assignRole('member');
        $task = $this->makeTask(Task::TODO, $other);

        $this->actingAs($this->member)
            ->patch(route('tasks.move', $task), ['status' => Task::IN_PROGRESS])
            ->assertForbidden();
    }

    public function test_manager_cannot_move_tasks(): void
    {
        $task = $this->makeTask();

        $this->actingAs($this->manager)
            ->patch(route('tasks.move', $task), ['status' => Task::IN_PROGRESS])
            ->assertForbidden();
    }

    public function test_approved_task_cannot_be_moved(): void
    {
        $task = $this->makeTask(Task::APPROVED);

        $this->actingAs($this->member)
            ->patch(route('tasks.move', $task), ['status' => Task::TODO])
            ->assertForbidden();
    }

    public function test_manager_can_approve_a_finished_task(): void
    {
        $task = $this->makeTask(Task::FINISHED);

        $this->actingAs($this->manager)
            ->post(route('tasks.approve', $task))
            ->assertRedirect();

        $task->refresh();
        $this->assertSame(Task::APPROVED, $task->status);
        $this->assertNotNull($task->approved_at);
        $this->assertSame($this->manager->id, (int) $task->reviewed_by);
    }

    public function test_cannot_approve_an_unfinished_task(): void
    {
        $task = $this->makeTask(Task::IN_PROGRESS);

        $this->actingAs($this->manager)
            ->post(route('tasks.approve', $task))
            ->assertSessionHasErrors('status');

        $this->assertSame(Task::IN_PROGRESS, $task->fresh()->status);
    }

    public function test_member_cannot_approve(): void
    {
        $task = $this->makeTask(Task::FINISHED);

        $this->actingAs($this->member)
            ->post(route('tasks.approve', $task))
            ->assertForbidden();
    }

    public function test_reject_requires_a_comment(): void
    {
        $task = $this->makeTask(Task::FINISHED);

        $this->actingAs($this->manager)
            ->post(route('tasks.reject', $task), [])
            ->assertSessionHasErrors('comment');
    }

    public function test_reject_sends_task_back_with_the_comment(): void
    {
        $task = $this->makeTask(Task::FINISHED);

        $this->actingAs($this->manager)
            ->post(route('tasks.reject', $task), ['comment' => 'Please add tests'])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame(Task::IN_PROGRESS, $task->status);
        $this->assertSame('Please add tests', $task->review_comment);
        $this->assertNull($task->finished_at);
    }
}
