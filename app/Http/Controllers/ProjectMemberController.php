<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectMemberRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ProjectMemberController extends Controller
{
    public function store(StoreProjectMemberRequest $request, Project $project): RedirectResponse
    {
        // syncWithoutDetaching = add if missing, never create a duplicate
        $project->members()->syncWithoutDetaching([$request->integer('user_id')]);

        return back();
    }

    public function destroy(Project $project, User $user): RedirectResponse
    {
        $project->members()->detach($user->id);

        return back();
    }
}
