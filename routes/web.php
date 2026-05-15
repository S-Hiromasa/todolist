<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/todos');

Route::middleware('guest')->group(function (): void {
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::resource('teams', TeamController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('teams/{team}/invite', [TeamController::class, 'invite'])->name('teams.invite');
    Route::patch('todos/{todo}/toggle', [TodoController::class, 'toggle'])->name('todos.toggle');
    Route::resource('todos', TodoController::class)->except(['show']);
});
