@extends('layouts.app')

@section('title', 'Import & Export - Basecamp')

@section('content')
<div x-data="{ tab: 'zip' }" class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="space-y-1">
        <a href="{{ route('adminland.index') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Adminland
        </a>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 flex items-center gap-2.5">
            <i data-lucide="arrow-down-to-dot" class="w-7 h-7 text-amber-500"></i>
            <span>Import & Export Basecamp Data</span>
        </h1>
        <p class="text-xs text-slate-500">Easily import all live and archived data from your Basecamp account into {{ $org->name }}.</p>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200">
        <button @click="tab = 'zip'" :class="tab === 'zip' ? 'border-amber-500 text-amber-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'" class="px-5 py-3 border-b-2 text-xs flex items-center gap-2 transition-all">
            <i data-lucide="file-archive" class="w-4 h-4"></i>
            <span>Method 1: Upload Basecamp ZIP</span>
        </button>

        <button @click="tab = 'api'" :class="tab === 'api' ? 'border-amber-500 text-amber-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'" class="px-5 py-3 border-b-2 text-xs flex items-center gap-2 transition-all">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            <span>Method 2: Direct API Sync (Account #4135299)</span>
        </button>

        <button @click="tab = 'export'" :class="tab === 'export' ? 'border-amber-500 text-amber-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'" class="px-5 py-3 border-b-2 text-xs flex items-center gap-2 transition-all">
            <i data-lucide="download" class="w-4 h-4"></i>
            <span>Export Backup (.zip)</span>
        </button>
    </div>

    <!-- Tab 1: ZIP Upload -->
    <div x-show="tab === 'zip'" class="space-y-6">
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-6">
            <div class="space-y-2">
                <h2 class="text-base font-black text-slate-900">Upload your Basecamp Export File</h2>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Basecamp allows you to export all your company's data into a single downloadable ZIP archive. Once uploaded here, the importer will parse every live project, archived project, to-do list, task, message board discussion, document, and team member.
                </p>
            </div>

            <!-- How to get export from Basecamp -->
            <div class="p-4 bg-amber-50/60 rounded-2xl border border-amber-200/60 space-y-2 text-xs text-amber-900">
                <p class="font-bold flex items-center gap-1.5">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600"></i>
                    <span>How to get the ZIP export file from Basecamp:</span>
                </p>
                <ol class="list-decimal list-inside space-y-1 pl-1 text-[11px] text-amber-800 font-medium">
                    <li>Log into your Basecamp account at <a href="https://app.basecamp.com/4135299/" target="_blank" class="underline font-bold">https://app.basecamp.com/4135299/</a>.</li>
                    <li>Click on <strong>Adminland</strong> on the home dashboard.</li>
                    <li>Under the <strong>Export</strong> section, click <strong>"Export your data"</strong>.</li>
                    <li>Basecamp will generate your archive and email you a download link for the <strong>.zip</strong> file.</li>
                    <li>Upload that <strong>.zip</strong> file below.</li>
                </ol>
            </div>

            <!-- Upload Form -->
            <form action="{{ route('adminland.import.zip') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div class="border-2 border-dashed border-slate-300 hover:border-amber-400 rounded-2xl p-8 text-center bg-slate-50/50 hover:bg-amber-50/20 transition-all cursor-pointer">
                    <input type="file" name="zip_file" accept=".zip" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-white hover:file:bg-amber-600 file:cursor-pointer cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-2">Upload the full Basecamp export ZIP archive (up to 100MB)</p>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-[11px] text-slate-400">All data will be safely mapped to {{ $org->name }}</span>
                    <button type="submit" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-2xl text-xs shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Start Basecamp ZIP Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab 2: API Direct Sync -->
    <div x-show="tab === 'api'" x-cloak class="space-y-6">
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-6">
            <div class="space-y-2">
                <h2 class="text-base font-black text-slate-900">Direct Basecamp API Sync</h2>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Connect directly to Basecamp 3/4 via REST API to automatically crawl and import all active projects, archived projects, to-dos, discussions, and documents.
                </p>
            </div>

            <form action="{{ route('adminland.import.api') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Basecamp Account ID *</label>
                    <input type="text" name="account_id" value="4135299" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <p class="text-[10px] text-slate-400 mt-1">From your URL: https://app.basecamp.com/<strong>4135299</strong>/</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Basecamp OAuth / Access Token *</label>
                    <input type="password" name="api_token" required placeholder="Bearer token or Personal Access Token" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <p class="text-[10px] text-slate-400 mt-1">Generated at <a href="https://launchpad.37signals.com/integrations" target="_blank" class="text-amber-600 underline font-bold">37signals Launchpad Integrations</a></p>
                </div>

                <div class="flex items-center justify-end pt-3 border-t border-slate-100">
                    <button type="submit" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-2xl text-xs shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>Connect & Sync All Data (Live & Archived)</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab 3: Export Backup -->
    <div x-show="tab === 'export'" x-cloak class="space-y-6">
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-6 text-center">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-3xl flex items-center justify-center mx-auto">
                <i data-lucide="download" class="w-7 h-7"></i>
            </div>
            <div class="max-w-md mx-auto space-y-2">
                <h2 class="text-lg font-black text-slate-900">Download Complete Workspace Archive</h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Export all your organization's projects (both active and archived), to-dos, message boards, comments, docs, and team members into a single structured ZIP archive.
                </p>
            </div>

            <form action="{{ route('adminland.export') }}" method="POST">
                @csrf
                <button type="submit" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl text-xs shadow-md transition-all flex items-center gap-2 mx-auto">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Generate & Download ZIP Archive</span>
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
