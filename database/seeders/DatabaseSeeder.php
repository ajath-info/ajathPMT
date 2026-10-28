<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\Discussion;
use App\Models\DiscussionComment;
use App\Models\Document;
use App\Models\ChatMessage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users
        $owner = User::firstOrCreate(
            ['email' => 'claire.watson@ajath.com'],
            [
                'name' => 'Claire Watson',
                'password' => Hash::make('password123'),
                'job_title' => 'Account Owner',
                'bio' => 'Leading Ajath Infotech Pvt Ltd.',
                'avatar_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150',
                'timezone' => 'Asia/Kolkata',
            ]
        );

        $member = User::firstOrCreate(
            ['email' => 'marcus.dev@worksphere.io'],
            [
                'name' => 'Marcus Rivera',
                'password' => Hash::make('password123'),
                'job_title' => 'Full-Stack Developer',
                'bio' => 'Building scalable web systems & APIs.',
                'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150',
                'timezone' => 'Asia/Kolkata',
            ]
        );

        $client = User::firstOrCreate(
            ['email' => 'edward.client@partner.com'],
            [
                'name' => 'Edward Smith',
                'password' => Hash::make('password123'),
                'job_title' => 'Client Partner Representative',
                'bio' => 'Client stakeholder reviewing progress and deliverables.',
                'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150',
                'timezone' => 'Asia/Kolkata',
            ]
        );

        // 2. Create Company / Organization
        $org = Organization::firstOrCreate(
            ['slug' => 'ajath-infotech'],
            [
                'name' => 'Ajath Infotech Pvt Ltd',
                'description' => 'Enterprise mobile and web development agency.',
                'owner_id' => $owner->id,
            ]
        );

        // 3. Organization Memberships
        OrganizationMember::firstOrCreate(
            ['organization_id' => $org->id, 'user_id' => $owner->id],
            ['role' => 'OWNER', 'department' => 'Executive']
        );

        OrganizationMember::firstOrCreate(
            ['organization_id' => $org->id, 'user_id' => $member->id],
            ['role' => 'MEMBER', 'department' => 'Engineering']
        );

        OrganizationMember::firstOrCreate(
            ['organization_id' => $org->id, 'user_id' => $client->id],
            ['role' => 'CLIENT', 'client_company' => 'Ride My Cars LLC']
        );

        // 4. Create Projects
        // Project 1: Company HQ (Internal)
        $hq = Project::firstOrCreate(
            ['organization_id' => $org->id, 'slug' => 'ajath-infotech-pvt-ltd-hq'],
            [
                'name' => 'Ajath Infotech Pvt Ltd HQ',
                'description' => 'Company-wide announcements and team workspace.',
                'status' => 'ACTIVE',
                'visibility' => 'ORGANIZATION',
                'cover_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800',
                'start_date' => '2026-01-01',
                'created_by' => $owner->id,
            ]
        );
        ProjectMember::firstOrCreate(['project_id' => $hq->id, 'user_id' => $owner->id], ['role' => 'PROJECT_LEAD']);
        ProjectMember::firstOrCreate(['project_id' => $hq->id, 'user_id' => $member->id], ['role' => 'PROJECT_MEMBER']);

        // Project 2: Client Project (Assigned to Client Edward)
        $clientProj = Project::firstOrCreate(
            ['organization_id' => $org->id, 'slug' => 'ride-my-cars-edward'],
            [
                'name' => 'Ride My Cars (Edward)',
                'description' => 'Mobile booking app & fleet tracking platform.',
                'status' => 'ACTIVE',
                'visibility' => 'ORGANIZATION',
                'cover_url' => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=800',
                'start_date' => '2026-02-15',
                'created_by' => $owner->id,
            ]
        );
        ProjectMember::firstOrCreate(['project_id' => $clientProj->id, 'user_id' => $owner->id], ['role' => 'PROJECT_LEAD']);
        ProjectMember::firstOrCreate(['project_id' => $clientProj->id, 'user_id' => $member->id], ['role' => 'PROJECT_MEMBER']);
        ProjectMember::firstOrCreate(['project_id' => $clientProj->id, 'user_id' => $client->id], ['role' => 'PROJECT_CLIENT']);

        // Project 3: Internal Web Platform (Employee only, Client cannot see this!)
        $internalProj = Project::firstOrCreate(
            ['organization_id' => $org->id, 'slug' => 'internal-core-infra'],
            [
                'name' => 'Internal Cloud Infrastructure',
                'description' => 'Server automation, CI/CD pipelines, and internal tools.',
                'status' => 'ACTIVE',
                'visibility' => 'ORGANIZATION',
                'cover_url' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800',
                'start_date' => '2026-03-01',
                'created_by' => $owner->id,
            ]
        );
        ProjectMember::firstOrCreate(['project_id' => $internalProj->id, 'user_id' => $owner->id], ['role' => 'PROJECT_LEAD']);
        ProjectMember::firstOrCreate(['project_id' => $internalProj->id, 'user_id' => $member->id], ['role' => 'PROJECT_MEMBER']);
        // Note: $client is purposefully NOT a member of $internalProj!

        // 5. Tasks for Client Project
        Task::firstOrCreate(
            ['project_id' => $clientProj->id, 'title' => 'Setup production deployment pipeline'],
            [
                'list_name' => 'Sprint 1',
                'description' => 'Configure automated webhooks and cPanel document root synchronization.',
                'status' => 'DONE',
                'priority' => 'HIGH',
                'due_date' => '2026-11-01',
                'assigned_to' => $member->id,
                'created_by' => $owner->id,
            ]
        );

        Task::firstOrCreate(
            ['project_id' => $clientProj->id, 'title' => 'Implement Client Portal and Project Permissions'],
            [
                'list_name' => 'Sprint 1',
                'description' => 'Strict project isolation so clients only see their own assigned workspace.',
                'status' => 'IN_PROGRESS',
                'priority' => 'URGENT',
                'due_date' => '2026-11-05',
                'assigned_to' => $member->id,
                'created_by' => $owner->id,
            ]
        );

        Task::firstOrCreate(
            ['project_id' => $clientProj->id, 'title' => 'Client Final Sign-off on Milestone 1'],
            [
                'list_name' => 'Review',
                'description' => 'Review deliverable with Edward Smith and collect feedback.',
                'status' => 'TODO',
                'priority' => 'MEDIUM',
                'due_date' => '2026-11-10',
                'assigned_to' => $client->id,
                'created_by' => $owner->id,
            ]
        );

        // 6. Discussions
        $disc = Discussion::firstOrCreate(
            ['project_id' => $clientProj->id, 'title' => 'Welcome to Ride My Cars Workspace!'],
            [
                'category' => 'Announcements',
                'content' => 'Hello everyone, welcome to the project workspace. We will share all sprint updates, tasks, and deliverables here.',
                'author_id' => $owner->id,
                'is_pinned' => true,
            ]
        );

        DiscussionComment::firstOrCreate(
            ['discussion_id' => $disc->id, 'content' => 'Thanks Claire! Glad to have the team working on this.'],
            ['author_id' => $client->id]
        );

        // 7. Docs
        Document::firstOrCreate(
            ['project_id' => $clientProj->id, 'title' => 'Project Scope & Roadmap'],
            [
                'content' => "# Ride My Cars Roadmap\n\n1. Phase 1: MVP Architecture\n2. Phase 2: Booking Engine\n3. Phase 3: Client Dashboard",
                'author_id' => $owner->id,
            ]
        );

        // 8. Campfire Chat Message
        ChatMessage::firstOrCreate(
            ['project_id' => $clientProj->id, 'content' => 'Welcome to the project campfire! Feel free to drop quick notes here.'],
            [
                'organization_id' => $org->id,
                'sender_id' => $owner->id,
                'type' => 'project',
            ]
        );
    }
}
