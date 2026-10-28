<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\PeopleController;
use App\Http\Controllers\Web\AdminlandController;

// Public auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick-login');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes (Session Auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/switch-org/{id}', [DashboardController::class, 'switchOrg'])->name('switch-org');
    Route::post('/organization/logo', [DashboardController::class, 'updateLogo'])->name('organization.logo');

    // Projects
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::post('/projects/{id}/toggle-pin', [ProjectController::class, 'togglePin'])->name('projects.toggle-pin');
    Route::post('/projects/{id}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('/projects/{id}/unarchive', [ProjectController::class, 'unarchive'])->name('projects.unarchive');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');

    // Project Tools
    Route::get('/projects/{id}/tasks', [ProjectController::class, 'tasks'])->name('projects.tasks');
    Route::post('/projects/{id}/tasks', [ProjectController::class, 'storeTask'])->name('projects.tasks.store');
    Route::post('/projects/{id}/tasks/{taskId}/toggle', [ProjectController::class, 'toggleTask'])->name('projects.tasks.toggle');
    Route::delete('/projects/{id}/tasks/{taskId}', [ProjectController::class, 'deleteTask'])->name('projects.tasks.delete');

    Route::get('/projects/{id}/discussions', [ProjectController::class, 'discussions'])->name('projects.discussions');
    Route::post('/projects/{id}/discussions', [ProjectController::class, 'storeDiscussion'])->name('projects.discussions.store');
    Route::get('/projects/{id}/discussions/{discId}', [ProjectController::class, 'showDiscussion'])->name('projects.discussions.show');
    Route::post('/projects/{id}/discussions/{discId}/comments', [ProjectController::class, 'storeComment'])->name('projects.discussions.comment');

    Route::get('/projects/{id}/docs', [ProjectController::class, 'docs'])->name('projects.docs');
    Route::post('/projects/{id}/docs', [ProjectController::class, 'storeDoc'])->name('projects.docs.store');

    Route::get('/projects/{id}/chat', [ProjectController::class, 'chat'])->name('projects.chat');
    Route::post('/projects/{id}/chat', [ProjectController::class, 'storeChat'])->name('projects.chat.store');

    // People & Clients
    Route::get('/people', [PeopleController::class, 'index'])->name('people.index');
    Route::post('/people', [PeopleController::class, 'store'])->name('people.store');
    Route::delete('/people/{userId}', [PeopleController::class, 'destroy'])->name('people.destroy');

    // Adminland & Basecamp Import/Export
    Route::get('/adminland', [AdminlandController::class, 'index'])->name('adminland.index');
    Route::get('/adminland/import', [AdminlandController::class, 'importView'])->name('adminland.import');
    Route::post('/adminland/import/zip', [AdminlandController::class, 'importZip'])->name('adminland.import.zip');
    Route::post('/adminland/import/api', [AdminlandController::class, 'importApi'])->name('adminland.import.api');
    Route::post('/adminland/export', [AdminlandController::class, 'export'])->name('adminland.export');
});
