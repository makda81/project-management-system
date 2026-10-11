<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

Route::middleware('auth')->group(function () {
    // projects
    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('projects', [ProjectController::class, 'store'])
        ->middleware('can:project.create')->name('projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('projects/{project}', [ProjectController::class, 'update'])
        ->middleware('can:project.update')->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])
        ->middleware('can:project.delete')->name('projects.destroy');

    // members
    Route::post('projects/{project}/members', [ProjectMemberController::class, 'store'])
        ->middleware('can:member.manage')->name('projects.members.store');
    Route::delete('projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])
        ->middleware('can:member.manage')->name('projects.members.destroy');

    // tasks
    Route::post('projects/{project}/tasks', [TaskController::class, 'store'])
        ->middleware('can:task.create')->name('tasks.store');
    Route::put('tasks/{task}', [TaskController::class, 'update'])
        ->middleware('can:task.update')->name('tasks.update');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])
        ->middleware('can:task.delete')->name('tasks.destroy');
});
