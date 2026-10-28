<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BasecampImporterService;
use App\Models\Organization;
use App\Models\User;

class BasecampImportCommand extends Command
{
    protected $signature = 'basecamp:import 
                            {--zip= : Absolute path to the exported Basecamp .zip file}
                            {--token= : Basecamp 3/4 OAuth Access Token or Personal Access Token}
                            {--account=4135299 : Basecamp Account ID}
                            {--org= : Target Organization ID (default: first)}';

    protected $description = 'Import Basecamp live and archived projects, to-dos, messages, and people from a ZIP file or directly via the API.';

    public function handle(BasecampImporterService $importer)
    {
        $this->info("=== Basecamp Data Importer ===");

        $orgId = $this->option('org') ?: Organization::first()->id;
        $org = Organization::findOrFail($orgId);
        $user = User::first();

        $zipPath = $this->option('zip');
        $token = $this->option('token');
        $accountId = $this->option('account');

        if ($zipPath) {
            if (!file_exists($zipPath)) {
                $this->error("ZIP file not found at: {$zipPath}");
                return 1;
            }

            $this->info("Importing from ZIP archive: {$zipPath} into '{$org->name}'...");
            $stats = $importer->importFromZip($zipPath, $org->id, $user->id);

            $this->info("Import Successful!");
            $this->table(
                ['Metric', 'Imported Count'],
                [
                    ['Projects (Live & Archived)', $stats['projects_imported']],
                    ['To-dos & Tasks', $stats['tasks_imported']],
                    ['Message Board Discussions', $stats['discussions_imported']],
                    ['Docs & Files', $stats['docs_imported']],
                    ['People & Clients', $stats['people_imported']],
                ]
            );
            return 0;
        }

        if ($token) {
            $this->info("Syncing directly from Basecamp API (Account: {$accountId}) into '{$org->name}'...");
            $stats = $importer->importFromApi($token, $accountId, $org->id, $user->id);

            $this->info("API Sync Successful!");
            $this->table(
                ['Metric', 'Imported Count'],
                [
                    ['Projects (Live & Archived)', $stats['projects_imported']],
                    ['To-dos & Tasks', $stats['tasks_imported']],
                    ['Message Board Discussions', $stats['discussions_imported']],
                    ['Docs & Files', $stats['docs_imported']],
                    ['People & Clients', $stats['people_imported']],
                ]
            );
            return 0;
        }

        $this->warn("Please specify either --zip=/path/to/basecamp-export.zip or --token=YOUR_TOKEN");
        return 1;
    }
}
