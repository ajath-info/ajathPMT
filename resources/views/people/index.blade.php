@extends('layouts.app')

@section('title', 'People & Clients - ' . $org->name)

@section('content')
<div x-data="{ tab: 'employees', addModalOpen: false, addType: 'MEMBER' }" class="max-w-6xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-700 flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Home
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <i data-lucide="users" class="w-7 h-7 text-indigo-600"></i>
                <span>Everyone at {{ $org->name }}</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Manage team members, roles, client logins, and project access permissions.</p>
        </div>

        @if(in_array($userRole, ['OWNER', 'ADMIN']))
        <button @click="addModalOpen = true" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl text-xs flex items-center justify-center gap-2 shadow-sm transition-all hover:shadow-indigo-500/20">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Add Team Member or Client</span>
        </button>
        @endif
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200">
        <button @click="tab = 'employees'" :class="tab === 'employees' ? 'border-indigo-600 text-indigo-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'" class="px-5 py-3 border-b-2 text-xs flex items-center gap-2 transition-all">
            <i data-lucide="briefcase" class="w-4 h-4"></i>
            <span>Our Team & Employees</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="tab === 'employees' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-600'">
                {{ $employees->count() }}
            </span>
        </button>

        <button @click="tab = 'clients'" :class="tab === 'clients' ? 'border-indigo-600 text-indigo-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'" class="px-5 py-3 border-b-2 text-xs flex items-center gap-2 transition-all">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            <span>Clients & External Partners</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="tab === 'clients' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-600'">
                {{ $clients->count() }}
            </span>
        </button>
    </div>

    <!-- Tab 1: Employees -->
    <div x-show="tab === 'employees'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($employees as $mem)
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm flex flex-col justify-between space-y-4 hover:border-slate-300 transition-all">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $mem->user->avatar_url ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($mem->user->name) }}" class="w-12 h-12 rounded-2xl border border-slate-200 flex-shrink-0">
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-900 truncate">{{ $mem->user->name }}</h3>
                                <p class="text-xs text-slate-500 truncate">{{ $mem->user->job_title ?? 'Team Member' }}</p>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded text-[10px] font-black tracking-wider uppercase {{ $mem->role === 'OWNER' ? 'bg-purple-100 text-purple-700' : ($mem->role === 'ADMIN' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                            {{ $mem->role }}
                        </span>
                    </div>

                    <div class="space-y-1 text-xs text-slate-600">
                        <div class="flex items-center gap-1.5 text-slate-500">
                            <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                            <span class="truncate">{{ $mem->user->email }}</span>
                        </div>
                        @if($mem->department)
                        <div class="flex items-center gap-1.5 text-slate-500">
                            <i data-lucide="building" class="w-3.5 h-3.5"></i>
                            <span>{{ $mem->department }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Assigned Projects -->
                    <div class="pt-2 border-t border-slate-100 space-y-1.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Project Access</p>
                        @if($mem->role === 'OWNER' || $mem->role === 'ADMIN')
                        <span class="inline-block px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold">
                            &bull; All Organization Projects (Admin Access)
                        </span>
                        @else
                            @php
                                $assigned = $mem->user->projects ?? collect();
                            @endphp
                            @if($assigned->isEmpty())
                                <p class="text-[11px] text-slate-400 italic">No projects assigned yet</p>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    @foreach($assigned as $p)
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[10px] font-semibold truncate max-w-[200px]">
                                        {{ $p->name }}
                                    </span>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                @if(in_array($userRole, ['OWNER', 'ADMIN']) && $mem->role !== 'OWNER' && $mem->user_id !== $user->id)
                <div class="pt-2 border-t border-slate-100 flex justify-end">
                    <form action="{{ route('people.destroy', $mem->user_id) }}" method="POST" onsubmit="return confirm('Remove {{ $mem->user->name }} from {{ $org->name }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-700 flex items-center gap-1">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Remove</span>
                        </button>
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-slate-400 space-y-2">
                <i data-lucide="users" class="w-8 h-8 mx-auto opacity-50"></i>
                <p class="text-xs">No employees enrolled yet.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Tab 2: Clients -->
    <div x-show="tab === 'clients'" x-cloak class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($clients as $client)
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm flex flex-col justify-between space-y-4 hover:border-slate-300 transition-all">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $client->user->avatar_url ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($client->user->name) }}" class="w-12 h-12 rounded-2xl border border-slate-200 flex-shrink-0">
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-900 truncate">{{ $client->user->name }}</h3>
                                <p class="text-xs text-emerald-600 font-semibold truncate">{{ $client->client_company ?? 'Client Partner' }}</p>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded text-[10px] font-black tracking-wider uppercase bg-emerald-100 text-emerald-800">
                            CLIENT
                        </span>
                    </div>

                    <div class="space-y-1 text-xs text-slate-600">
                        <div class="flex items-center gap-1.5 text-slate-500">
                            <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                            <span class="truncate">{{ $client->user->email }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-slate-500">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                            <span>Can only see assigned client projects</span>
                        </div>
                    </div>

                    <!-- Assigned Projects -->
                    <div class="pt-2 border-t border-slate-100 space-y-1.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Client Has Access To</p>
                        @php
                            $assigned = $client->user->projects ?? collect();
                        @endphp
                        @if($assigned->isEmpty())
                            <p class="text-[11px] text-amber-600 italic">No projects assigned yet (cannot see any projects)</p>
                        @else
                            <div class="flex flex-wrap gap-1">
                                @foreach($assigned as $p)
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 rounded text-[10px] font-semibold truncate max-w-[200px]">
                                    <i data-lucide="folder" class="w-3 h-3 inline"></i> {{ $p->name }}
                                </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                @if(in_array($userRole, ['OWNER', 'ADMIN']))
                <div class="pt-2 border-t border-slate-100 flex justify-end">
                    <form action="{{ route('people.destroy', $client->user_id) }}" method="POST" onsubmit="return confirm('Remove client {{ $client->user->name }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-700 flex items-center gap-1">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Remove Client</span>
                        </button>
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-slate-400 space-y-2">
                <i data-lucide="shield-check" class="w-8 h-8 mx-auto opacity-50"></i>
                <p class="text-xs">No clients added yet. Click "Add Team Member or Client" to give a client access to specific projects.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Add Person Modal -->
    <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div @click="addModalOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-slate-200">
                <form action="{{ route('people.store') }}" method="POST">
                    @csrf
                    <div class="p-6 sm:p-8 space-y-5">
                        
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div>
                                <h3 class="text-lg font-black text-slate-900">Add Person to {{ $org->name }}</h3>
                                <p class="text-xs text-slate-500">Create login credentials and configure project access.</p>
                            </div>
                            <button type="button" @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <!-- Type Selector (Employee vs Client) -->
                        <div class="grid grid-cols-2 gap-3 p-1 bg-slate-100 rounded-2xl">
                            <button type="button" @click="addType = 'MEMBER'" :class="addType !== 'CLIENT' ? 'bg-white shadow text-slate-900 font-black' : 'text-slate-600 font-semibold'" class="py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5">
                                <i data-lucide="briefcase" class="w-4 h-4"></i>
                                <span>Team Member / Employee</span>
                            </button>
                            <button type="button" @click="addType = 'CLIENT'" :class="addType === 'CLIENT' ? 'bg-white shadow text-slate-900 font-black' : 'text-slate-600 font-semibold'" class="py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                                <span>Client / Customer</span>
                            </button>
                        </div>

                        <!-- Fields -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                                <input type="text" name="name" required placeholder="e.g. Sarah Connor" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Email (Login username) *</label>
                                <input type="email" name="email" required placeholder="sarah@example.com" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Password *</label>
                                <input type="password" name="password" required placeholder="Min 6 characters" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Role selection if employee -->
                            <div x-show="addType !== 'CLIENT'">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Organization Role *</label>
                                <select name="role" :disabled="addType === 'CLIENT'" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="MEMBER">Member (Standard Team)</option>
                                    <option value="ADMIN">Admin (Can manage all projects & users)</option>
                                </select>
                            </div>

                            <!-- Hidden role if client -->
                            <template x-if="addType === 'CLIENT'">
                                <input type="hidden" name="role" value="CLIENT">
                            </template>

                            <div x-show="addType !== 'CLIENT'">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Job Title</label>
                                <input type="text" name="job_title" placeholder="e.g. Senior Frontend Dev" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div x-show="addType !== 'CLIENT'">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Department</label>
                                <input type="text" name="department" placeholder="e.g. Engineering, Marketing" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div x-show="addType === 'CLIENT'" class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Client Company / Brand *</label>
                                <input type="text" name="client_company" placeholder="e.g. Acme Corp / Ride My Cars Ltd" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>

                        <!-- Project Access Checkboxes -->
                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-700">
                                Assign Project Access
                                <span class="text-slate-400 font-normal" x-text="addType === 'CLIENT' ? '(Clients will only see selected projects)' : '(Employees will have access to these projects)'"></span>
                            </label>
                            
                            @if($allProjects->isEmpty())
                            <p class="text-xs text-slate-400 italic">No projects created yet. You can assign projects later.</p>
                            @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 bg-slate-50 rounded-2xl border border-slate-200">
                                @foreach($allProjects as $proj)
                                <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200/80 cursor-pointer hover:border-indigo-300 transition-colors">
                                    <input type="checkbox" name="project_ids[]" value="{{ $proj->id }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs font-bold text-slate-800 truncate">{{ $proj->name }}</span>
                                </label>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                            <button type="button" @click="addModalOpen = false" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition-colors shadow-sm">
                                Create Account & Send Access
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

