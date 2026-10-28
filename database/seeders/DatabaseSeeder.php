<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        // 1. Create Organization
        $org = Organization::firstOrCreate(
            ['slug' => 'ajath-infotech'],
            [
                'name' => 'Ajath Infotech Pvt Ltd',
                'description' => 'Enterprise mobile and web development agency.',
            ]
        );

        // 2. Create Users with realistic avatars & initials matching screenshot
        $usersData = [
            [
                'email' => 'claire.watson@ajath.com',
                'name' => 'Claire Watson (SN)',
                'job_title' => 'Managing Director',
                'role' => 'OWNER',
                'avatar_color' => 'bg-purple-600',
                'initials' => 'SN',
            ],
            [
                'email' => 'marcus.dev@worksphere.io',
                'name' => 'Marcus Rivera (PK)',
                'job_title' => 'Technical Lead',
                'role' => 'ADMIN',
                'avatar_color' => 'bg-teal-500',
                'initials' => 'PK',
            ],
            [
                'email' => 'edward.client@partner.com',
                'name' => 'Edward Smith (E)',
                'job_title' => 'Client Representative',
                'role' => 'CLIENT',
                'client_company' => 'NDC Homes / Ride My Cars',
                'avatar_color' => 'bg-orange-500',
                'initials' => 'E',
            ],
            [
                'email' => 'deepak.tiwari@ajath.com',
                'name' => 'Deepak Tiwari (DT)',
                'job_title' => 'Mobile Engineer',
                'role' => 'MEMBER',
                'avatar_color' => 'bg-emerald-600',
                'initials' => 'DT',
            ],
            [
                'email' => 'rohit.verma@ajath.com',
                'name' => 'Rohit Verma (RO)',
                'job_title' => 'Full-Stack Developer',
                'role' => 'MEMBER',
                'avatar_color' => 'bg-blue-600',
                'initials' => 'RO',
            ],
            [
                'email' => 'gaurav.kumar@ajath.com',
                'name' => 'Gaurav Kumar (GK)',
                'job_title' => 'Flutter Developer',
                'role' => 'MEMBER',
                'avatar_color' => 'bg-fuchsia-600',
                'initials' => 'GK',
            ],
            [
                'email' => 'priya.sharma@ajath.com',
                'name' => 'Priya Sharma (PI)',
                'job_title' => 'UI/UX Designer',
                'role' => 'MEMBER',
                'avatar_color' => 'bg-pink-500',
                'initials' => 'PI',
            ],
            [
                'email' => 'rakesh.kumar@ajath.com',
                'name' => 'Rakesh Kumar (RK)',
                'job_title' => 'QA Specialist',
                'role' => 'MEMBER',
                'avatar_color' => 'bg-indigo-600',
                'initials' => 'RK',
            ],
            [
                'email' => 'rahul.sharma@ajath.com',
                'name' => 'Rahul Sharma (RS)',
                'job_title' => 'Backend Engineer',
                'role' => 'MEMBER',
                'avatar_color' => 'bg-rose-500',
                'initials' => 'RS',
            ],
            [
                'email' => 'ayush.verma@ajath.com',
                'name' => 'Ayush Verma (AY)',
                'job_title' => 'Developer',
                'role' => 'MEMBER',
                'avatar_color' => 'bg-violet-600',
                'initials' => 'AY',
            ],
            [
                'email' => 'isha.yadav@ajath.com',
                'name' => 'Isha Yadav (IY)',
                'job_title' => 'Product Designer',
                'role' => 'MEMBER',
                'avatar_color' => 'bg-cyan-600',
                'initials' => 'IY',
            ],
        ];

        $users = [];
        foreach ($usersData as $ud) {
            $user = User::firstOrCreate(
                ['email' => $ud['email']],
                [
                    'name' => $ud['name'],
                    'password' => Hash::make('password123'),
                    'job_title' => $ud['job_title'],
                    'avatar_url' => 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($ud['initials']) . '&backgroundColor=' . substr($ud['avatar_color'], 3),
                    'timezone' => 'Asia/Kolkata',
                ]
            );

            $orgMemberData = [
                'role' => $ud['role'],
                'department' => $ud['role'] === 'CLIENT' ? null : 'Engineering',
                'client_company' => $ud['client_company'] ?? null,
            ];

            OrganizationMember::updateOrCreate(
                ['organization_id' => $org->id, 'user_id' => $user->id],
                $orgMemberData
            );

            $users[$ud['initials']] = $user;
        }

        // Set organization owner
        $org->owner_id = $users['SN']->id;
        $org->save();

        // 3. Exact projects from the attached Basecamp Screenshot!
        $projectsConfig = [
            [
                'name' => 'Adhyatmah Bharat Version 2.0',
                'subtitle' => 'Client',
                'badge' => null,
                'color' => 'white',
                'is_pinned' => true,
                'members' => ['DT', 'PK', 'RO', 'SN'],
                'desc' => 'Adhyatmah Bharat mobile applications, backend services, and content platform.',
            ],
            [
                'name' => 'Veggie- Pro Mobile Apps',
                'subtitle' => 'Client: Seller and Driver Apps And Admin Panel',
                'badge' => null,
                'color' => 'yellow',
                'is_pinned' => true,
                'members' => ['SN', 'PK'],
                'desc' => 'Vegetable vendor seller app, logistics driver tracking, and multi-tenant admin console.',
            ],
            [
                'name' => 'HARSAC Website Development',
                'subtitle' => 'Client: The scope of work includes: Regular website content updates...',
                'badge' => 'All-access',
                'color' => 'amber',
                'is_pinned' => true,
                'members' => ['SN', 'PI', 'PK'],
                'desc' => 'Government space applications center portal, geo-spatial viewer, and departmental updates.',
            ],
            [
                'name' => 'Ride My Cars (Edward)',
                'subtitle' => 'NDC Homes',
                'badge' => null,
                'color' => 'peach',
                'is_pinned' => true,
                'members' => ['E', 'GK', 'PI', 'PK', 'RO', 'SN'],
                'desc' => 'Fleet management, luxury car rental reservation portal, and client live tracker.',
            ],
            [
                'name' => 'Exibine Mobile App',
                'subtitle' => 'Universal Business Identity App',
                'badge' => null,
                'color' => 'rose',
                'is_pinned' => true,
                'members' => ['GK', 'PI', 'PK', 'SN'],
                'desc' => 'Digital identity NFC & QR card business networking application.',
            ],
            [
                'name' => 'HiHlo Social Contact App',
                'subtitle' => 'HiHlo',
                'badge' => null,
                'color' => 'purple',
                'is_pinned' => true,
                'members' => ['RO', 'PK', 'SN'],
                'desc' => 'Next-gen social address book with auto-syncing contact cards and messaging.',
            ],
            [
                'name' => 'Three Parcel App',
                'subtitle' => 'Client',
                'badge' => null,
                'color' => 'sky',
                'is_pinned' => true,
                'members' => ['RS', 'PK', 'SN'],
                'desc' => 'Courier aggregator, doorstep pickup booking, and live tracking system.',
            ],
            [
                'name' => 'Ajath Infotech Pvt Ltd HQ',
                'subtitle' => 'Company-wide announcements and stuff everyone needs to know.',
                'badge' => null,
                'color' => 'mint',
                'is_pinned' => false,
                'members' => ['SN', 'PK', 'GK', 'PI', 'RO', 'RK', 'RS', 'AY', 'IY'],
                'desc' => 'Ajath Infotech company home, culture, all-hands meeting updates, and HR info.',
            ],
            [
                'name' => 'Gurudwara App',
                'subtitle' => 'Client',
                'badge' => null,
                'color' => 'rose',
                'is_pinned' => false,
                'members' => ['SN', 'PI'],
                'desc' => 'Community religious portal, live stream, daily hukamnama, and langar seva bookings.',
            ],
            [
                'name' => 'HRMS - Sena Bhawan',
                'subtitle' => 'Client',
                'badge' => null,
                'color' => 'slate',
                'is_pinned' => false,
                'members' => ['PK', 'RO', 'SN'],
                'desc' => 'Government personnel management, leave workflow, roster management, and payroll.',
            ],
            [
                'name' => 'Adhyatmah Bharat - eCommerce App and...',
                'subtitle' => 'Adhyatmah Bharat: eCommerce App and Website',
                'badge' => null,
                'color' => 'amber',
                'is_pinned' => false,
                'members' => ['DT', 'RO', 'SN'],
                'desc' => 'Spiritual merchandise marketplace, pooja booking, and live inventory sync.',
            ],
            [
                'name' => 'ClubGo',
                'subtitle' => 'ClubGo',
                'badge' => null,
                'color' => 'white',
                'is_pinned' => false,
                'members' => ['AY', 'IY', 'PK'],
                'desc' => 'Nightlife passes, guest list RSVP, and club event ticket booking application.',
            ],
            [
                'name' => 'Ajath Management - 2026',
                'subtitle' => 'Management and quarterly planning',
                'badge' => null,
                'color' => 'slate',
                'is_pinned' => false,
                'members' => ['SN', 'PK'],
                'desc' => 'Executive planning, annual targets, hiring roadmaps, and client acquisitions.',
            ],
            [
                'name' => 'BIPL APP',
                'subtitle' => 'BIPL: Mobile App Developement',
                'badge' => null,
                'color' => 'yellow',
                'is_pinned' => false,
                'members' => ['GK', 'PK', 'SN'],
                'desc' => 'Bharat Infratel field inspection and tower maintenance audit application.',
            ],
            [
                'name' => 'Tender',
                'subtitle' => 'Government and Corporate Bids',
                'badge' => null,
                'color' => 'peach',
                'is_pinned' => false,
                'members' => ['PK', 'SN'],
                'desc' => 'Public procurement documentation, RFP compliance checks, and bid submissions.',
            ],
            [
                'name' => 'Gradmo (Ed.Tech) Mobile Apps',
                'subtitle' => 'Client',
                'badge' => null,
                'color' => 'purple',
                'is_pinned' => false,
                'members' => ['SN', 'PI'],
                'desc' => 'Interactive virtual classroom, mock test series, and adaptive learning portal.',
            ],
            [
                'name' => 'Prettify',
                'subtitle' => 'Prettify: Prettify Mobile App',
                'badge' => null,
                'color' => 'mint',
                'is_pinned' => false,
                'members' => ['RO', 'SN'],
                'desc' => 'Salon booking on demand, at-home beauty treatments, and stylist scheduling.',
            ],
        ];

        foreach ($projectsConfig as $pConfig) {
            $slug = Str::slug($pConfig['name']);
            $proj = Project::updateOrCreate(
                ['organization_id' => $org->id, 'slug' => $slug],
                [
                    'name' => $pConfig['name'],
                    'client_subtitle' => $pConfig['subtitle'],
                    'badge_text' => $pConfig['badge'],
                    'color_theme' => $pConfig['color'],
                    'is_pinned' => $pConfig['is_pinned'],
                    'description' => $pConfig['desc'],
                    'status' => 'ACTIVE',
                    'visibility' => 'ORGANIZATION',
                    'created_by' => $users['SN']->id,
                ]
            );

            // Assign members
            foreach ($pConfig['members'] as $init) {
                if (isset($users[$init])) {
                    $u = $users[$init];
                    $role = $u->email === 'edward.client@partner.com' ? 'PROJECT_CLIENT' : ($init === 'SN' ? 'PROJECT_LEAD' : 'PROJECT_MEMBER');
                    ProjectMember::firstOrCreate([
                        'project_id' => $proj->id,
                        'user_id' => $u->id,
                    ], ['role' => $role]);
                }
            }

            // Seed sample to-dos and discussions in Ride My Cars & HQ
            if ($slug === 'ride-my-cars-edward' || $slug === 'ajath-infotech-pvt-ltd-hq') {
                Task::firstOrCreate([
                    'project_id' => $proj->id,
                    'title' => 'Setup production deployment pipeline',
                ], [
                    'priority' => 'HIGH',
                    'status' => 'DONE',
                    'due_date' => now()->addDays(2),
                    'assigned_to' => $users['PK']->id,
                    'created_by' => $users['SN']->id,
                ]);

                Task::firstOrCreate([
                    'project_id' => $proj->id,
                    'title' => 'Client Final Sign-off on Milestone 1',
                ], [
                    'priority' => 'URGENT',
                    'status' => 'TODO',
                    'due_date' => now()->addDays(5),
                    'assigned_to' => $users['E']->id ?? $users['SN']->id,
                    'created_by' => $users['SN']->id,
                ]);

                Discussion::firstOrCreate([
                    'project_id' => $proj->id,
                    'title' => 'Welcome to ' . $proj->name . ' Workspace!',
                ], [
                    'category' => 'Announcement',
                    'content' => "Hello team! Welcome to {$proj->name}. Please review current sprint to-dos and docs.",
                    'author_id' => $users['SN']->id,
                ]);

                ChatMessage::firstOrCreate([
                    'project_id' => $proj->id,
                    'content' => 'Welcome to the Campfire chat!',
                ], [
                    'organization_id' => $org->id,
                    'sender_id' => $users['SN']->id,
                    'type' => 'project',
                ]);
            }
        }
    }
}
