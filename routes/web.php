<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\PeopleController;
use App\Http\Controllers\Web\AdminlandController;
use App\Http\Controllers\Web\MyStuffController;

// Public auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/firebase-google', [AuthController::class, 'firebaseGoogleLogin'])->name('auth.firebase.google');
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick-login');
Route::get('/clear-subpath-cookies', function (\Illuminate\Http\Request $request) {
    $ret = $request->query('redirect', '/');
    $html = '<!DOCTYPE html><html><head><script>document.cookie = "humans_21909=1; path=/"; window.location.replace(' . json_encode('/quick-login/manjot?redirect=' . $ret) . ');</script></head><body></body></html>';
    return response($html, 200)
        ->header('Content-Type', 'text/html; charset=UTF-8')
        ->header('Clear-Site-Data', '"cookies"');
});

Route::get('/forgot_password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword']);
Route::post('/forgot_password', [AuthController::class, 'sendResetLink'])->name('password.email');

Route::get('/pricing', [AuthController::class, 'showPricing'])->name('pricing');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public invitation acceptance / join routes
Route::get('/join/{user}', [PeopleController::class, 'showJoinForm'])->name('people.join');
Route::post('/join/{user}', [PeopleController::class, 'completeJoin'])->name('people.join.submit');
Route::get('/join/{user}/decline', [PeopleController::class, 'declineJoin'])->name('people.join.decline');

// Document preview / download fallback route so /uploads/docs/* never 404s
Route::get('/uploads/docs/{filename}', function (string $filename) {
    $decoded = rawurldecode($filename);
    $candidates = [
        base_path('uploads/docs/' . $decoded),
        public_path('uploads/docs/' . $decoded),
        base_path('uploads/docs/1791962446.pdf'),
        public_path('uploads/docs/1791962446.pdf'),
    ];
    foreach ($candidates as $path) {
        if (is_file($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mimeMap = [
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'amr' => 'audio/amr',
                'm4a' => 'audio/mp4',
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'mp4' => 'video/mp4',
                'webm' => 'video/webm',
            ];
            $mime = $mimeMap[$ext] ?? 'application/octet-stream';
            return response()->file($path, [
                'Content-Type' => $mime,
            ]);
        }
    }
    abort(404);
})->where('filename', '.*');

// Protected routes (Session Auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/switch-org/{id}', [DashboardController::class, 'switchOrg'])->name('switch-org');
    Route::post('/organization/logo', [DashboardController::class, 'updateLogo'])->name('organization.logo');

    // Projects
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/directory', [ProjectController::class, 'index'])->name('projects.directory');
    Route::get('/projects/new', [ProjectController::class, 'create'])->name('projects.create');
    Route::get('/projects/create', [ProjectController::class, 'create']);
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::post('/projects/{id}/toggle-pin', [ProjectController::class, 'togglePin'])->name('projects.toggle-pin');
    Route::post('/projects/{id}/color', [ProjectController::class, 'updateColor'])->name('projects.color');
    Route::post('/projects/{id}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('/projects/{id}/unarchive', [ProjectController::class, 'unarchive'])->name('projects.unarchive');
    Route::get('/projects/{id}/status', [ProjectController::class, 'status'])->name('projects.status');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::post('/projects/{id}/update', [ProjectController::class, 'update']);

    // Project Tools
    Route::get('/projects/{id}/tasks', [ProjectController::class, 'tasks'])->name('projects.tasks');
    Route::post('/projects/{id}/tasks', [ProjectController::class, 'storeTask'])->name('projects.tasks.store');
    Route::get('/projects/{id}/tasks/{taskId}', [ProjectController::class, 'showTask'])->name('projects.tasks.show');
    Route::match(['put', 'patch', 'post'], '/projects/{id}/tasks/{taskId}', [ProjectController::class, 'updateTask'])->name('projects.tasks.update');
    Route::post('/projects/{id}/tasks/{taskId}/comments', [ProjectController::class, 'storeTaskComment'])->name('projects.tasks.comments.store');
    Route::match(['get', 'post', 'put', 'patch'], '/projects/{id}/tasks/{taskId}/toggle', [ProjectController::class, 'toggleTask'])->name('projects.tasks.toggle');
    Route::delete('/projects/{id}/tasks/{taskId}', [ProjectController::class, 'deleteTask'])->name('projects.tasks.delete');

    Route::get('/projects/{id}/discussions', [ProjectController::class, 'discussions'])->name('projects.discussions');
    Route::get('/projects/{id}/discussions/new', [ProjectController::class, 'discussions'])->name('projects.discussions.new');
    Route::get('/projects/{id}/messages/new', [ProjectController::class, 'discussions'])->name('projects.messages.new');
    Route::post('/projects/{id}/discussions', [ProjectController::class, 'storeDiscussion'])->name('projects.discussions.store');
    Route::get('/projects/{id}/discussions/{discId}', [ProjectController::class, 'showDiscussion'])->name('projects.discussions.show');
    Route::post('/projects/{id}/discussions/{discId}/comments', [ProjectController::class, 'storeComment'])->name('projects.discussions.comment');

    Route::get('/projects/{id}/docs', [ProjectController::class, 'docs'])->name('projects.docs');
    Route::post('/projects/{id}/docs', [ProjectController::class, 'storeDoc'])->name('projects.docs.store');

    Route::get('/projects/{id}/chat', [ProjectController::class, 'chat'])->name('projects.chat');
    Route::get('/projects/{id}/campfire', [ProjectController::class, 'chat'])->name('projects.campfire');
    Route::post('/projects/{id}/chat', [ProjectController::class, 'storeChat'])->name('projects.chat.store');
    Route::get('/projects/{id}/email-forwards', [ProjectController::class, 'discussions'])->name('projects.email-forwards');

    Route::get('/projects/{id}/schedule', [ProjectController::class, 'schedule'])->name('projects.schedule');
    Route::get('/projects/{id}/calendar', [ProjectController::class, 'schedule'])->name('projects.calendar');
    Route::get('/projects/{id}/kanban', [ProjectController::class, 'kanban'])->name('projects.kanban');
    Route::get('/projects/{id}/card_tables', [ProjectController::class, 'kanban']);
    Route::get('/projects/{id}/gauge/needles', [ProjectController::class, 'gauge'])->name('projects.gauge');
    Route::get('/projects/{id}/gauge/needles/new', [ProjectController::class, 'gauge'])->name('projects.gauge.new');
    Route::post('/projects/{id}/gauge/needles', [ProjectController::class, 'storeGaugeNeedle'])->name('projects.gauge.store');
    Route::get('/projects/{id}/settings', [ProjectController::class, 'show'])->name('projects.settings');
    Route::get('/projects/{id}/people/users/edit', [ProjectController::class, 'people'])->name('projects.people');
    Route::get('/projects/{id}/people/client_users/edit', [ProjectController::class, 'people'])->name('projects.client_people');
    Route::get('/projects/{id}/people', [ProjectController::class, 'people']);
    Route::post('/projects/{id}/people', [ProjectController::class, 'updatePeople'])->name('projects.people.update');
    Route::get('/projects/{id}/invitations/enrollees/edit', [ProjectController::class, 'editInvitationEnrollees'])->name('projects.invitations.enrollees.edit');
    Route::post('/projects/{id}/invitations/enrollees', [ProjectController::class, 'updateInvitationEnrollees'])->name('projects.invitations.enrollees.update');
    Route::get('/projects/{id}/checkins', [ProjectController::class, 'checkins'])->name('projects.checkins');
    Route::get('/projects/{id}/checkins/questions/new', [ProjectController::class, 'checkins'])->name('projects.checkins.questions.new');
    Route::post('/projects/{id}/checkins/questions', [ProjectController::class, 'storeCheckinQuestion'])->name('projects.checkins.questions.store');
    Route::post('/projects/{id}/checkins', [ProjectController::class, 'storeCheckinAnswer'])->name('projects.checkins.store');

    // People & Clients
    Route::get('/people', [PeopleController::class, 'index'])->name('people.index');
    Route::get('/account/people', [PeopleController::class, 'index']);
    Route::post('/people', [PeopleController::class, 'store'])->name('people.store');
    Route::delete('/people/{userId}', [PeopleController::class, 'destroy'])->name('people.destroy');
    Route::get('/account/enrollments/new', [PeopleController::class, 'createEnrollment'])->name('enrollments.create');
    Route::get('/account/enrollments/employees/new', [PeopleController::class, 'createEmployeeEnrollment'])->name('enrollments.employees.create');
    Route::get('/account/enrollments/collaborators/new', [PeopleController::class, 'createCollaboratorEnrollment'])->name('enrollments.collaborators.create');
    Route::get('/account/enrollments/clients/new', [PeopleController::class, 'createClientEnrollment'])->name('enrollments.clients.create');
    Route::get('/account/enrollments/accesses/new', [PeopleController::class, 'createEnrollmentAccesses'])->name('enrollments.accesses.create');
    Route::post('/account/enrollments/accesses', [PeopleController::class, 'storeEnrollmentAccesses'])->name('enrollments.accesses.store');
    Route::get('/account/enrollments/{type}/completed', [PeopleController::class, 'enrollmentCompleted'])->name('enrollments.completed');
    Route::post('/account/enrollments', [PeopleController::class, 'storeEnrollments'])->name('enrollments.store');

    // My Stuff & Personal Dock (Matches Basecamp 4 UI)
    Route::get('/my/profile', [MyStuffController::class, 'profile'])->name('my.profile');
    Route::post('/my/profile', [MyStuffController::class, 'updateProfile'])->name('my.profile.update');
    Route::get('/my/drafts', [MyStuffController::class, 'drafts'])->name('my.drafts');
    Route::post('/my/drafts', [MyStuffController::class, 'saveDraft'])->name('my.drafts.save');
    Route::delete('/my/drafts/{id}', [MyStuffController::class, 'deleteDraft'])->name('my.drafts.delete');
    Route::get('/my/boosts', [MyStuffController::class, 'boosts'])->name('my.boosts');
    Route::post('/my/boosts', [MyStuffController::class, 'giveBoost'])->name('my.boosts.give');
    Route::get('/my/settings', [MyStuffController::class, 'settings'])->name('my.settings');
    Route::get('/settings', [MyStuffController::class, 'settings'])->name('settings.index');
    Route::post('/my/settings', [MyStuffController::class, 'updateSettings'])->name('my.settings.update');
    Route::get('/my/notifications/settings/edit', [MyStuffController::class, 'notificationSettings'])->name('my.notifications.settings');

    Route::get('/my/tasks', [MyStuffController::class, 'tasks'])->name('my.tasks');
    Route::get('/my/events', [MyStuffController::class, 'events'])->name('my.events');
    Route::get('/my/bookmarks', [MyStuffController::class, 'bookmarks'])->name('my.bookmarks');
    Route::post('/my/bookmarks/toggle', [MyStuffController::class, 'toggleBookmark'])->name('my.bookmarks.toggle');
    Route::get('/my/activity', [MyStuffController::class, 'activity'])->name('my.activity');
    Route::get('/activity', [MyStuffController::class, 'activity'])->name('activity.index');
    Route::get('/my/notes', [MyStuffController::class, 'notes'])->name('my.notes');
    Route::post('/my/notes', [MyStuffController::class, 'saveNotes'])->name('my.notes.save');
    Route::get('/pings', [MyStuffController::class, 'pings'])->name('my.pings');
    Route::get('/pings/{userId}', [MyStuffController::class, 'showPing'])->name('my.pings.show');
    Route::post('/pings/{userId}', [MyStuffController::class, 'sendPing'])->name('my.pings.send');
    Route::get('/support', [MyStuffController::class, 'support'])->name('my.support');
    Route::post('/support/agent', [MyStuffController::class, 'aiSupportAgent'])->name('my.support.agent');
    Route::get('/apps', [MyStuffController::class, 'apps'])->name('my.apps');
    Route::get('/mobile', [MyStuffController::class, 'apps']);
    Route::get('/my/apps', [MyStuffController::class, 'apps']);
    Route::get('/identity/login_option', [MyStuffController::class, 'loginOption'])->name('my.login_option');
    Route::get('/my/security', [MyStuffController::class, 'loginOption']);

    // Adminland & Basecamp Import/Export
    Route::get('/adminland', [AdminlandController::class, 'index'])->name('adminland.index');
    Route::get('/account', [AdminlandController::class, 'index']);
    Route::get('/account/administrators', [AdminlandController::class, 'administrators'])->name('adminland.administrators');
    Route::post('/account/administrators', [AdminlandController::class, 'grantAdministrator'])->name('adminland.administrators.grant');
    Route::delete('/account/administrators/{userId}', [AdminlandController::class, 'revokeAdministrator'])->name('adminland.administrators.revoke');
    Route::get('/account/user_groups', [AdminlandController::class, 'userGroups'])->name('adminland.groups');
    Route::get('/account/user_groups/new', [AdminlandController::class, 'createUserGroup'])->name('adminland.groups.create');
    Route::get('/account/companies', [AdminlandController::class, 'companies'])->name('adminland.companies');
    Route::get('/account/custom_tool_names', [AdminlandController::class, 'customToolNames'])->name('adminland.tools');
    Route::get('/account/categories', [AdminlandController::class, 'categories'])->name('adminland.categories');
    Route::get('/account/voice_notes', [AdminlandController::class, 'voiceNotes'])->name('adminland.voice_notes');
    Route::get('/account/person_merges', [AdminlandController::class, 'personMerges'])->name('adminland.person_merges');
    Route::get('/account/todos/mass_reassignments/new', [MyStuffController::class, 'tasks'])->name('adminland.mass_reassignments');
    Route::get('/account/retention', [AdminlandController::class, 'retention'])->name('adminland.retention');
    Route::get('/templates', [AdminlandController::class, 'templates'])->name('templates.index');
    Route::get('/templates/new', [AdminlandController::class, 'templates'])->name('templates.create');
    Route::get('/templates/{id}', [AdminlandController::class, 'showTemplate'])->name('templates.show');
    Route::get('/template_library/todolists', [AdminlandController::class, 'templates'])->name('templates.todolists');
    Route::get('/template_library/todolists/new', [AdminlandController::class, 'templates'])->name('templates.todolists.create');
    Route::get('/template_library/card_tables', [AdminlandController::class, 'templates'])->name('templates.card_tables');
    Route::get('/template_library/card_tables/new', [AdminlandController::class, 'templates'])->name('templates.card_tables.create');
    Route::get('/adminland/import', [AdminlandController::class, 'importView'])->name('adminland.import');
    Route::post('/adminland/import/zip', [AdminlandController::class, 'importZip'])->name('adminland.import.zip');
    Route::post('/adminland/import/api', [AdminlandController::class, 'importApi'])->name('adminland.import.api');
    Route::post('/adminland/export', [AdminlandController::class, 'export'])->name('adminland.export');
});
