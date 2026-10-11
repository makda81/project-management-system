<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $projects = Project::visibleTo($request->user())
            ->withCount('tasks')
            ->latest()
            ->get();

        return Inertia::render('projects/Index', ['projects' => $projects]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = $request->user()->ownedProjects()->create($request->validated());

        return redirect()->route('projects.show', $project);
    }

    public function show(Request $request, Project $project): Response
    {
        abort_unless(
            Project::visibleTo($request->user())->whereKey($project->id)->exists(),
            403
        );

        $project->load(['members:id,name,email', 'tasks']);

        return Inertia::render('projects/Show', ['project' => $project]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return back();
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index');
    }
}
