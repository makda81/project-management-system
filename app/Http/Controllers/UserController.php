<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manager-only: list every user and add new ones.
 * Both routes are protected by the "can:member.manage" middleware (see routes/web.php).
 */
class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first() ?? 'member',
                'created_at' => $user->created_at?->toDateString(),
            ])
            ->values();

        return Inertia::render('users/Index', [
            'users' => $users,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = new User;
        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            // The manager vouches for this account, so it can log in right away.
            'email_verified_at' => now(),
        ])->save();

        $user->assignRole($data['role']);

        return redirect()->route('users.index');
    }
}
