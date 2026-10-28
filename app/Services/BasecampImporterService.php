<?php

namespace App\Services;

use ZipArchive;
use Illuminate\Support\Facades\Http;
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

class BasecampImporterService
{
    /**
     * Import Basecamp data from an exported ZIP archive.
     */
    public function importFromZip(string $zipFilePath, int $organizationId, int $currentUserId): array
    {
        $stats = [
            'projects_imported' => 0,
            'tasks_imported' => 0,
            'discussions_imported' => 0,
            'docs_imported' => 0,
            'people_imported' => 0,
        ];

        $extractPath = storage_path('app/temp/bc_extract_' . Str::random(8));
        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0777, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) !== true) {
            throw new \Exception("Unable to open the uploaded ZIP file.");
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // 1. Scan for people.json
        $peopleFile = $this->findFileRecursive($extractPath, 'people.json');
        $userMap = []; // basecamp_id => user_id

        if ($peopleFile && file_exists($peopleFile)) {
            $peopleData = json_decode(file_get_contents($peopleFile), true) ?? [];
            foreach ($peopleData as $p) {
                $email = $p['email_address'] ?? ($p['email'] ?? null);
                if (!$email) continue;

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $p['name'] ?? $email,
                        'job_title' => $p['title'] ?? 'Team Member',
                        'avatar_url' => $p['avatar_url'] ?? ('https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($p['name'] ?? 'User')),
                        'password' => Hash::make('Basecamp2026!'),
                        'basecamp_id' => (string)($p['id'] ?? ''),
                    ]
                );

                OrganizationMember::firstOrCreate(
                    ['organization_id' => $organizationId, 'user_id' => $user->id],
                    [
                        'role' => ($p['admin'] ?? false) ? 'ADMIN' : (($p['client'] ?? false) ? 'CLIENT' : 'MEMBER'),
                        'client_company' => $p['company_name'] ?? null,
                    ]
                );

                $userMap[$p['id'] ?? $email] = $user->id;
                $stats['people_imported']++;
            }
        }

        // 2. Scan for project folders or project.json files
        $projectFiles = $this->findFilesRecursive($extractPath, 'project.json');
        if (empty($projectFiles)) {
            // Also check for projects.json
            $projectsListFile = $this->findFileRecursive($extractPath, 'projects.json');
            if ($projectsListFile && file_exists($projectsListFile)) {
                $pList = json_decode(file_get_contents($projectsListFile), true) ?? [];
                foreach ($pList as $projItem) {
                    $this->importSingleProjectData($projItem, $organizationId, $currentUserId, $userMap, $stats);
                }
            }
        } else {
            foreach ($projectFiles as $pJsonFile) {
                $pData = json_decode(file_get_contents($pJsonFile), true) ?? [];
                $proj = $this->importSingleProjectData($pData, $organizationId, $currentUserId, $userMap, $stats);

                if ($proj) {
                    // Look for todos, messages, documents in the same folder
                    $folder = dirname($pJsonFile);
                    $this->importProjectSubfolders($folder, $proj, $currentUserId, $userMap, $stats);
                }
            }
        }

        // Clean up extracted files
        $this->deleteDirectoryRecursive($extractPath);

        return $stats;
    }

    /**
     * Import Basecamp data directly via Basecamp 3/4 REST API.
     */
    public function importFromApi(string $apiToken, string $accountId, int $organizationId, int $currentUserId): array
    {
        $stats = [
            'projects_imported' => 0,
            'tasks_imported' => 0,
            'discussions_imported' => 0,
            'docs_imported' => 0,
            'people_imported' => 0,
        ];

        $baseUrl = "https://3.basecampapi.com/{$accountId}";
        $headers = [
            'Authorization' => "Bearer {$apiToken}",
            'User-Agent' => 'Ajath Basecamp Sync (workspace.ajath.com)',
            'Accept' => 'application/json',
        ];

        // 1. Fetch People
        $peopleRes = Http::withHeaders($headers)->get("{$baseUrl}/people.json");
        $userMap = [];

        if ($peopleRes->successful()) {
            foreach ($peopleRes->json() as $p) {
                $email = $p['email_address'] ?? null;
                if (!$email) continue;

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $p['name'] ?? 'Basecamp User',
                        'job_title' => $p['title'] ?? 'Team Member',
                        'avatar_url' => $p['avatar_url'] ?? null,
                        'password' => Hash::make('Basecamp2026!'),
                        'basecamp_id' => (string)$p['id'],
                    ]
                );

                OrganizationMember::firstOrCreate(
                    ['organization_id' => $organizationId, 'user_id' => $user->id],
                    [
                        'role' => ($p['admin'] ?? false) ? 'ADMIN' : (($p['client'] ?? false) ? 'CLIENT' : 'MEMBER'),
                        'client_company' => $p['company']['name'] ?? null,
                    ]
                );

                $userMap[$p['id']] = $user->id;
                $stats['people_imported']++;
            }
        }

        // 2. Fetch Active Projects
        $activeProjects = Http::withHeaders($headers)->get("{$baseUrl}/projects.json")->json() ?? [];
        // 3. Fetch Archived Projects
        $archivedProjects = Http::withHeaders($headers)->get("{$baseUrl}/projects.json?status=archived")->json() ?? [];

        $allProjects = array_merge(
            array_map(fn($p) => array_merge($p, ['_is_archived' => false]), $activeProjects),
            array_map(fn($p) => array_merge($p, ['_is_archived' => true]), $archivedProjects)
        );

        foreach ($allProjects as $pData) {
            $isArchived = $pData['_is_archived'] ?? false;
            $slug = Str::slug($pData['name']) . '-' . Str::random(4);

            $proj = Project::updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'basecamp_id' => (string)$pData['id'],
                ],
                [
                    'name' => $pData['name'],
                    'slug' => $slug,
                    'description' => $pData['description'] ?? null,
                    'client_subtitle' => ($pData['bookmarked'] ?? false) ? 'Starred' : null,
                    'is_pinned' => (bool)($pData['bookmarked'] ?? false),
                    'status' => $isArchived ? 'ARCHIVED' : 'ACTIVE',
                    'created_by' => $currentUserId,
                ]
            );

            ProjectMember::firstOrCreate([
                'project_id' => $proj->id,
                'user_id' => $currentUserId,
            ], ['role' => 'PROJECT_LEAD']);

            $stats['projects_imported']++;

            // Inspect Dock Tools
            if (!empty($pData['dock'])) {
                foreach ($pData['dock'] as $tool) {
                    $toolName = $tool['name'] ?? '';
                    $toolUrl = $tool['url'] ?? '';

                    // A. Message Board
                    if ($toolName === 'message_board' && $toolUrl) {
                        $messagesRes = Http::withHeaders($headers)->get("{$toolUrl}/messages.json");
                        if ($messagesRes->successful()) {
                            foreach ($messagesRes->json() as $msg) {
                                $authorId = $userMap[$msg['creator']['id'] ?? 0] ?? $currentUserId;
                                $disc = Discussion::updateOrCreate(
                                    [
                                        'project_id' => $proj->id,
                                        'basecamp_id' => (string)$msg['id'],
                                    ],
                                    [
                                        'title' => $msg['subject'] ?? 'Untitled Message',
                                        'category' => $msg['category']['name'] ?? 'General',
                                        'content' => strip_tags($msg['content'] ?? ''),
                                        'author_id' => $authorId,
                                    ]
                                );
                                $stats['discussions_imported']++;

                                // Fetch Comments for message
                                if (!empty($msg['comments_url'])) {
                                    $comments = Http::withHeaders($headers)->get($msg['comments_url'])->json() ?? [];
                                    foreach ($comments as $c) {
                                        DiscussionComment::firstOrCreate(
                                            [
                                                'discussion_id' => $disc->id,
                                                'content' => strip_tags($c['content'] ?? ''),
                                                'author_id' => $userMap[$c['creator']['id'] ?? 0] ?? $currentUserId,
                                            ]
                                        );
                                    }
                                }
                            }
                        }
                    }

                    // B. To-dos (Todoset)
                    if ($toolName === 'todoset' && $toolUrl) {
                        $todolistsRes = Http::withHeaders($headers)->get("{$toolUrl}/todolists.json");
                        if ($todolistsRes->successful()) {
                            foreach ($todolistsRes->json() as $tlist) {
                                $todosUrl = $tlist['todos_url'] ?? '';
                                if ($todosUrl) {
                                    $todosRes = Http::withHeaders($headers)->get($todosUrl);
                                    if ($todosRes->successful()) {
                                        foreach ($todosRes->json() as $td) {
                                            $assigneeId = null;
                                            if (!empty($td['assignees'][0]['id'])) {
                                                $assigneeId = $userMap[$td['assignees'][0]['id']] ?? null;
                                            }

                                            Task::updateOrCreate(
                                                [
                                                    'project_id' => $proj->id,
                                                    'basecamp_id' => (string)$td['id'],
                                                ],
                                                [
                                                    'title' => $td['content'] ?? 'Untitled To-do',
                                                    'description' => strip_tags($td['description'] ?? ''),
                                                    'status' => ($td['completed'] ?? false) ? 'DONE' : 'TODO',
                                                    'due_date' => $td['due_on'] ?? null,
                                                    'priority' => 'MEDIUM',
                                                    'assigned_to' => $assigneeId,
                                                    'created_by' => $userMap[$td['creator']['id'] ?? 0] ?? $currentUserId,
                                                ]
                                            );
                                            $stats['tasks_imported']++;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // C. Docs & Files (Vault)
                    if ($toolName === 'vault' && $toolUrl) {
                        $docsRes = Http::withHeaders($headers)->get("{$toolUrl}/documents.json");
                        if ($docsRes->successful()) {
                            foreach ($docsRes->json() as $doc) {
                                Document::updateOrCreate(
                                    [
                                        'project_id' => $proj->id,
                                        'basecamp_id' => (string)$doc['id'],
                                    ],
                                    [
                                        'title' => $doc['title'] ?? 'Untitled Document',
                                        'content' => strip_tags($doc['content'] ?? ''),
                                        'author_id' => $userMap[$doc['creator']['id'] ?? 0] ?? $currentUserId,
                                    ]
                                );
                                $stats['docs_imported']++;
                            }
                        }
                    }

                    // D. Campfire (Chat)
                    if ($toolName === 'chat' && $toolUrl) {
                        $linesRes = Http::withHeaders($headers)->get("{$toolUrl}/lines.json");
                        if ($linesRes->successful()) {
                            foreach ($linesRes->json() as $line) {
                                ChatMessage::create([
                                    'project_id' => $proj->id,
                                    'organization_id' => $organizationId,
                                    'sender_id' => $userMap[$line['creator']['id'] ?? 0] ?? $currentUserId,
                                    'content' => strip_tags($line['content'] ?? ''),
                                    'type' => 'project',
                                ]);
                            }
                        }
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Export all organization data into a Basecamp-compatible ZIP archive.
     */
    public function exportWorkspaceZip(int $organizationId): string
    {
        $org = Organization::findOrFail($organizationId);
        $exportDir = storage_path('app/exports');
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0777, true);
        }

        $filename = 'basecamp_export_' . Str::slug($org->name) . '_' . date('Y-m-d_His') . '.zip';
        $zipPath = $exportDir . '/' . $filename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create zip archive: {$zipPath}");
        }

        // 1. Organization Metadata
        $orgMeta = [
            'organization_id' => $org->id,
            'name' => $org->name,
            'exported_at' => now()->toIso8601String(),
            'platform' => 'Ajath PMT Basecamp Edition',
        ];
        $zip->addFromString('workspace.json', json_encode($orgMeta, JSON_PRETTY_PRINT));

        // 2. People
        $members = OrganizationMember::with('user')->where('organization_id', $org->id)->get();
        $people = [];
        foreach ($members as $m) {
            $people[] = [
                'id' => $m->user->id,
                'name' => $m->user->name,
                'email' => $m->user->email,
                'role' => $m->role,
                'job_title' => $m->user->job_title,
                'department' => $m->department,
                'client_company' => $m->client_company,
                'avatar_url' => $m->user->avatar_url,
            ];
        }
        $zip->addFromString('people.json', json_encode($people, JSON_PRETTY_PRINT));

        // 3. Projects (Both Active & Archived)
        $projects = Project::with(['tasks.assignee', 'discussions.comments.author', 'documents', 'chatMessages.sender', 'members'])
            ->where('organization_id', $org->id)
            ->get();

        foreach ($projects as $p) {
            $pFolder = 'projects/' . Str::slug($p->name) . '_' . $p->id;

            $pData = [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'is_pinned' => (bool)$p->is_pinned,
                'client_subtitle' => $p->client_subtitle,
                'badge_text' => $p->badge_text,
                'color_theme' => $p->color_theme,
                'description' => $p->description,
                'created_at' => $p->created_at->toIso8601String(),
                'members' => $p->members->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'email' => $m->email,
                    'role' => $m->pivot->role,
                ])->toArray(),
            ];
            $zip->addFromString("{$pFolder}/project.json", json_encode($pData, JSON_PRETTY_PRINT));

            // To-dos
            $todos = [];
            foreach ($p->tasks as $t) {
                $todos[] = [
                    'id' => $t->id,
                    'title' => $t->title,
                    'description' => $t->description,
                    'status' => $t->status,
                    'priority' => $t->priority,
                    'due_date' => $t->due_date ? $t->due_date->toDateString() : null,
                    'assigned_to' => $t->assignee ? $t->assignee->name : null,
                ];
            }
            $zip->addFromString("{$pFolder}/todos.json", json_encode($todos, JSON_PRETTY_PRINT));

            // Discussions
            $discs = [];
            foreach ($p->discussions as $d) {
                $comments = [];
                foreach ($d->comments as $c) {
                    $comments[] = [
                        'author' => $c->author ? $c->author->name : 'Unknown',
                        'content' => $c->content,
                        'created_at' => $c->created_at->toIso8601String(),
                    ];
                }
                $discs[] = [
                    'title' => $d->title,
                    'category' => $d->category,
                    'content' => $d->content,
                    'comments' => $comments,
                ];
            }
            $zip->addFromString("{$pFolder}/discussions.json", json_encode($discs, JSON_PRETTY_PRINT));

            // Documents
            $docs = [];
            foreach ($p->documents as $doc) {
                $docs[] = [
                    'title' => $doc->title,
                    'content' => $doc->content,
                ];
            }
            $zip->addFromString("{$pFolder}/documents.json", json_encode($docs, JSON_PRETTY_PRINT));

            // Campfire Messages
            $chats = [];
            foreach ($p->chatMessages as $msg) {
                $chats[] = [
                    'sender' => $msg->sender ? $msg->sender->name : 'User',
                    'content' => $msg->content,
                    'created_at' => $msg->created_at->toIso8601String(),
                ];
            }
            $zip->addFromString("{$pFolder}/campfire.json", json_encode($chats, JSON_PRETTY_PRINT));
        }

        $zip->close();
        return $zipPath;
    }

    private function importSingleProjectData(array $pData, int $organizationId, int $currentUserId, array $userMap, array &$stats): ?Project
    {
        $name = $pData['name'] ?? ($pData['title'] ?? null);
        if (!$name) return null;

        $slug = Str::slug($name) . '-' . Str::random(4);
        $status = (isset($pData['status']) && strtoupper($pData['status']) === 'ARCHIVED') || ($pData['archived'] ?? false)
            ? 'ARCHIVED'
            : 'ACTIVE';

        $proj = Project::updateOrCreate(
            [
                'organization_id' => $organizationId,
                'name' => $name,
            ],
            [
                'slug' => $slug,
                'description' => $pData['description'] ?? null,
                'client_subtitle' => $pData['client_subtitle'] ?? ($pData['subtitle'] ?? null),
                'color_theme' => $pData['color_theme'] ?? ($pData['color'] ?? 'white'),
                'is_pinned' => (bool)($pData['is_pinned'] ?? ($pData['bookmarked'] ?? false)),
                'status' => $status,
                'created_by' => $currentUserId,
                'basecamp_id' => (string)($pData['id'] ?? ''),
            ]
        );

        ProjectMember::firstOrCreate([
            'project_id' => $proj->id,
            'user_id' => $currentUserId,
        ], ['role' => 'PROJECT_LEAD']);

        $stats['projects_imported']++;
        return $proj;
    }

    private function importProjectSubfolders(string $folder, Project $proj, int $currentUserId, array $userMap, array &$stats): void
    {
        // 1. To-dos
        $todosFile = $folder . '/todos.json';
        if (file_exists($todosFile)) {
            $todos = json_decode(file_get_contents($todosFile), true) ?? [];
            foreach ($todos as $t) {
                Task::create([
                    'project_id' => $proj->id,
                    'title' => $t['title'] ?? ($t['content'] ?? 'To-do'),
                    'description' => $t['description'] ?? null,
                    'status' => (isset($t['status']) && $t['status'] === 'DONE') || ($t['completed'] ?? false) ? 'DONE' : 'TODO',
                    'priority' => $t['priority'] ?? 'MEDIUM',
                    'due_date' => $t['due_date'] ?? null,
                    'created_by' => $currentUserId,
                ]);
                $stats['tasks_imported']++;
            }
        }

        // 2. Discussions
        $discFile = $folder . '/discussions.json';
        if (file_exists($discFile)) {
            $discs = json_decode(file_get_contents($discFile), true) ?? [];
            foreach ($discs as $d) {
                $disc = Discussion::create([
                    'project_id' => $proj->id,
                    'title' => $d['title'] ?? 'Discussion',
                    'category' => $d['category'] ?? 'General',
                    'content' => $d['content'] ?? '',
                    'author_id' => $currentUserId,
                ]);
                $stats['discussions_imported']++;

                if (!empty($d['comments'])) {
                    foreach ($d['comments'] as $c) {
                        DiscussionComment::create([
                            'discussion_id' => $disc->id,
                            'content' => $c['content'] ?? '',
                            'author_id' => $currentUserId,
                        ]);
                    }
                }
            }
        }

        // 3. Documents
        $docFile = $folder . '/documents.json';
        if (file_exists($docFile)) {
            $docs = json_decode(file_get_contents($docFile), true) ?? [];
            foreach ($docs as $doc) {
                Document::create([
                    'project_id' => $proj->id,
                    'title' => $doc['title'] ?? 'Document',
                    'content' => $doc['content'] ?? '',
                    'author_id' => $currentUserId,
                ]);
                $stats['docs_imported']++;
            }
        }

        // 4. Campfire
        $chatFile = $folder . '/campfire.json';
        if (file_exists($chatFile)) {
            $chats = json_decode(file_get_contents($chatFile), true) ?? [];
            foreach ($chats as $msg) {
                ChatMessage::create([
                    'project_id' => $proj->id,
                    'organization_id' => $proj->organization_id,
                    'sender_id' => $currentUserId,
                    'content' => $msg['content'] ?? '',
                    'type' => 'project',
                ]);
            }
        }
    }

    private function findFileRecursive(string $dir, string $filename): ?string
    {
        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($it);

        foreach ($ri as $file) {
            if ($file->getFilename() === $filename) {
                return $file->getPathname();
            }
        }
        return null;
    }

    private function findFilesRecursive(string $dir, string $filename): array
    {
        $files = [];
        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($it);

        foreach ($ri as $file) {
            if ($file->getFilename() === $filename) {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    private function deleteDirectoryRecursive(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->deleteDirectoryRecursive($path) : unlink($path);
        }
        rmdir($dir);
    }
}

