<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\DiscussionController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ChatController;

// Public auth routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

// Protected routes (Sanctum authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Organizations
    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::post('/organizations', [OrganizationController::class, 'store']);
    Route::get('/organizations/{id}', [OrganizationController::class, 'show']);
    Route::get('/organizations/{id}/members', [OrganizationController::class, 'members']);
    Route::post('/organizations/{id}/members', [OrganizationController::class, 'addMember']);
    Route::delete('/organizations/{id}/members/{userId}', [OrganizationController::class, 'removeMember']);

    // Projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    Route::post('/projects/{id}/members', [ProjectController::class, 'addMember']);
    Route::delete('/projects/{id}/members/{userId}', [ProjectController::class, 'removeMember']);

    // Tasks
    Route::get('/projects/{projectId}/tasks', [TaskController::class, 'index']);
    Route::post('/projects/{projectId}/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    // Discussions
    Route::get('/projects/{projectId}/discussions', [DiscussionController::class, 'index']);
    Route::post('/projects/{projectId}/discussions', [DiscussionController::class, 'store']);
    Route::get('/discussions/{id}', [DiscussionController::class, 'show']);
    Route::post('/discussions/{id}/comments', [DiscussionController::class, 'addComment']);

    // Documents
    Route::get('/projects/{projectId}/docs', [DocumentController::class, 'index']);
    Route::post('/projects/{projectId}/docs', [DocumentController::class, 'store']);
    Route::get('/docs/{id}', [DocumentController::class, 'show']);

    // Chat
    Route::get('/chat/messages', [ChatController::class, 'index']);
    Route::post('/chat/messages', [ChatController::class, 'store']);
});
