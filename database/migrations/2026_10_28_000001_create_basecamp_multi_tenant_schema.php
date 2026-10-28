<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Alter Users table for Profile attributes
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title')->nullable()->after('avatar_url');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('job_title');
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('Asia/Kolkata')->after('bio');
            }
        });

        // 2. Organizations (Companies)
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Organization Members (Multi-tenancy & Roles)
        Schema::create('organization_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['OWNER', 'ADMIN', 'MEMBER', 'CLIENT'])->default('MEMBER');
            $table->string('department')->nullable();
            $table->string('client_company')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
        });

        // 4. Teams
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Team Members
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
        });

        // 6. Projects
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE, ON_HOLD, COMPLETED, ARCHIVED
            $table->string('visibility')->default('ORGANIZATION'); // ORGANIZATION, PRIVATE
            $table->date('start_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 7. Project Members (Project-level access control)
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('PROJECT_MEMBER'); // PROJECT_LEAD, PROJECT_MEMBER, PROJECT_CLIENT
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        // 8. Tasks (Kanban & To-dos)
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('list_name')->default('To-dos');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('TODO'); // TODO, IN_PROGRESS, DONE
            $table->string('priority')->default('MEDIUM'); // LOW, MEDIUM, HIGH, URGENT
            $table->date('due_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        // 9. Discussions (Message Board)
        Schema::create('discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('category')->default('General');
            $table->string('title');
            $table->longText('content');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });

        // 10. Discussion Comments
        Schema::create('discussion_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discussion_id')->constrained('discussions')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
        });

        // 11. Documents
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 12. Chat Messages (Project Campfire & Direct Messages)
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->string('type')->default('project'); // project, direct
            $table->timestamps();
        });

        // 13. Automatic Check-ins
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('question');
            $table->string('frequency')->default('daily');
            $table->string('time')->nullable();
            $table->timestamps();
        });

        // 14. Checkin Answers
        Schema::create('checkin_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkin_id')->constrained('checkins')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('answer');
            $table->timestamps();
        });

        // 15. Organization Invitations
        Schema::create('organization_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('email');
            $table->string('role')->default('MEMBER');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
            $table->string('token')->unique();
            $table->string('status')->default('PENDING'); // PENDING, ACCEPTED, DECLINED, EXPIRED
            $table->text('personal_message')->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_invitations');
        Schema::dropIfExists('checkin_answers');
        Schema::dropIfExists('checkins');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('discussion_comments');
        Schema::dropIfExists('discussions');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('organization_members');
        Schema::dropIfExists('organizations');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_url', 'job_title', 'bio', 'timezone']);
        });
    }
};

