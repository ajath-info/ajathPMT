@extends('layouts.app')

@section('title', "Who's on " . $project->name . ' - Basecamp')

@section('content')
<style>
.bc-dotted-input {
    background-color: transparent !important;
    border-top: 0 !important;
    border-left: 0 !important;
    border-right: 0 !important;
    border-bottom: 1px dotted #8c969e !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}
.bc-dotted-input:focus {
    border-bottom: 1px solid #60a5fa !important;
}
html.bc-theme-dark .bc-visibility-pill {
    background-color: #334e68 !important;
    color: #e6eaed !important;
}
html.bc-theme-dark .bc-client-switch-on {
    background-color: #2e6f56 !important;
    color: #86efac !important;
}
</style>
<div x-data="{
        activeTab: '{{ $initialTab ?? 'team' }}',
        accessMode: '{{ $savedAccessMode ?? 'invite_only' }}',
        clientsEnabled: {{ isset($savedClientsEnabled) ? ($savedClientsEnabled ? 'true' : 'false') : 'true' }},
        addClientsModal: false,
        clientSearchQuery: '',
        searchQuery: '',
        dropdownOpen: false,
        companyPickerOpen: false,
        selectedCompany: 'Ajath Infotech Pvt Ltd',
        checkedUserIds: [],
        sentLinkId: null,
        openNotifyId: null,
        toastMessage: '',
        toastType: 'success',
        newPersonFormOpen: false,
        newPersonName: '',
        newPersonEmail: '',
        newPersonCompany: 'Ajath Infotech Pvt Ltd',
        newPersonTitle: 'Team Member',
        newPersonIsClient: false,
        editingEmailMemberId: null,
        editingEmailValue: '',
        editingCompanyValue: 'Ajath Infotech Pvt Ltd',
        oooActive: localStorage.getItem('bc_ooo_active') !== '0',
        platformUsers: {{ Js::from($platformUsers) }},
        members: ({{ Js::from($projectMembers) }} || []).map(m => Object.assign({ notifications: true, isPending: Boolean(m.isPending || m.roleBadge === 'Pending') }, m)),
        companies: {{ Js::from($companiesList) }},

        showToast(msg, type = 'success') {
            this.toastMessage = msg;
            this.toastType = type;
            setTimeout(() => {
                if (this.toastMessage === msg) {
                    this.toastMessage = '';
                }
            }, 5000);
        },

        switchTab(t) {
            this.activeTab = t;
            const targetUrl = t === 'clients'
                ? '{{ route('projects.client_people', $project->id) }}'
                : '{{ route('projects.people', $project->id) }}';
            try { history.replaceState(null, '', targetUrl); } catch (e) {}
        },

        get teamMembers() {
            return this.members
                .filter(m => !m.isClient)
                .slice()
                .sort((a, b) => a.name.localeCompare(b.name));
        },

        get clientMembers() {
            return this.members
                .filter(m => Boolean(m.isClient))
                .slice()
                .sort((a, b) => a.name.localeCompare(b.name));
        },

        get availablePlatformUsers() {
            const q = (this.searchQuery || '').trim().toLowerCase();
            const existingIds = new Set(this.members.map(m => m.id));
            return this.platformUsers.filter(u => {
                if (existingIds.has(u.id)) return false;
                if (!q) return true;
                return (
                    (u.name || '').toLowerCase().includes(q) ||
                    (u.email || '').toLowerCase().includes(q) ||
                    (u.company || '').toLowerCase().includes(q) ||
                    (u.jobTitle || '').toLowerCase().includes(q)
                );
            });
        },

        get availableClientCandidates() {
            const q = (this.clientSearchQuery || '').trim().toLowerCase();
            const existingIds = new Set(this.members.map(m => m.id));
            return this.platformUsers.filter(u => {
                if (existingIds.has(u.id)) return false;
                if (!q) return true;
                return (
                    (u.name || '').toLowerCase().includes(q) ||
                    (u.email || '').toLowerCase().includes(q) ||
                    (u.company || '').toLowerCase().includes(q) ||
                    (u.jobTitle || '').toLowerCase().includes(q)
                );
            });
        },

        toggleMemberNotification(id) {
            this.openNotifyId = (this.openNotifyId === id ? null : id);
            if (this.openNotifyId === id) {
                this.$nextTick(() => {
                    const pop = document.getElementById('notify-pop-' + id);
                    if (pop) pop.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                });
            }
        },

        setMemberNotification(id, enabled) {
            this.members = this.members.map(item => {
                if (item.id === id) {
                    return Object.assign({}, item, { notifications: Boolean(enabled) });
                }
                return item;
            });
            this.openNotifyId = null;
            this.syncBackend('update_notifications', id, false);
        },

        addPlatformUser(u, asClient = false) {
            if (!u) return;
            const exists = this.members.some(m => m.id === u.id);
            if (!exists) {
                const isCli = asClient || this.activeTab === 'clients' || Boolean(u.isClient);
                const clone = Object.assign({}, u, {
                    isClient: isCli,
                    roleBadge: isCli ? 'Client' : (u.roleBadge || ''),
                    jobTitle: isCli && !(u.jobTitle || '').includes(' at ')
                        ? ((u.jobTitle || 'Project Owner') + ' at ' + (u.company || 'NDC Homes'))
                        : (u.jobTitle || 'Team Member'),
                    company: u.company || (isCli ? 'NDC Homes' : 'Ajath Infotech Pvt Ltd'),
                    notifications: true
                });
                this.members = [...this.members, clone];
                this.syncBackend('add', clone.id, clone.isClient, clone);
                this.showToast(clone.name + ' added to this project.');
            }
            this.searchQuery = '';
            this.clientSearchQuery = '';
            this.dropdownOpen = false;
            this.addClientsModal = false;
        },

        openNewPersonForm(rawInput = '', asClient = false) {
            const q = (rawInput || '').trim();
            this.newPersonIsClient = Boolean(asClient || this.activeTab === 'clients');
            if (q.includes('@')) {
                const namePart = q.split('@')[0].replace(/[._-]/g, ' ');
                this.newPersonName = namePart.replace(/\b\w/g, l => l.toUpperCase());
                this.newPersonEmail = q;
            } else {
                this.newPersonName = q.replace(/\b\w/g, l => l.toUpperCase());
                this.newPersonEmail = q ? (q.toLowerCase().replace(/[^a-z0-9]+/g, '.') + '@ajath.com') : '';
            }
            this.newPersonCompany = this.newPersonIsClient ? 'NDC Homes' : 'Ajath Infotech Pvt Ltd';
            this.newPersonTitle = this.newPersonIsClient ? 'Project Owner' : 'Team Member';
            this.newPersonFormOpen = true;
            this.dropdownOpen = false;
            this.companyPickerOpen = false;
        },

        addTypedPerson(asClient = false) {
            const raw = asClient ? this.clientSearchQuery : this.searchQuery;
            const q = (raw || '').trim();
            if (!q) return;
            const list = asClient ? this.availableClientCandidates : this.availablePlatformUsers;
            const match = list.find(u =>
                (u.name || '').toLowerCase() === q.toLowerCase() ||
                (u.email || '').toLowerCase() === q.toLowerCase()
            );
            if (match) {
                this.addPlatformUser(match, asClient);
                return;
            }
            if (q.includes('@')) {
                const namePart = q.split('@')[0].replace(/[._-]/g, ' ');
                this.newPersonName = namePart.replace(/\b\w/g, l => l.toUpperCase());
                this.newPersonEmail = q;
                this.newPersonIsClient = Boolean(asClient || this.activeTab === 'clients');
                this.newPersonCompany = this.newPersonIsClient ? 'NDC Homes' : 'Outside collaborator';
                this.newPersonTitle = this.newPersonIsClient ? 'Project Owner' : 'Team Member';
                this.submitNewPersonInvite();
                return;
            }
            this.openNewPersonForm(q, asClient);
        },

        submitNewPersonInvite() {
            const name = (this.newPersonName || '').trim();
            const email = (this.newPersonEmail || '').trim();
            const company = (this.newPersonCompany || 'Ajath Infotech Pvt Ltd').trim() || 'Ajath Infotech Pvt Ltd';
            const title = (this.newPersonTitle || 'Team Member').trim() || 'Team Member';
            const isCli = Boolean(this.newPersonIsClient);

            if (!name) {
                this.showToast('Please enter the person\'s name.', 'error');
                return;
            }
            if (!email || !email.includes('@')) {
                this.showToast('Please enter a valid email address so we can send the invitation email.', 'error');
                return;
            }

            const parts = name.split(/\s+/);
            const initials = ((parts[0] ? parts[0][0] : 'U') + (parts[1] ? parts[1][0] : '')).toUpperCase();
            const formattedJobTitle = isCli && !title.includes(' at ')
                ? (title + ' at ' + company)
                : title;

            const newMember = {
                id: Date.now(),
                name: name,
                email: email,
                initials: initials,
                bg: isCli ? 'bg-[#c026d3]' : 'bg-[#0d9488]',
                roleBadge: isCli ? 'Client' : 'Pending',
                isPending: true,
                jobTitle: formattedJobTitle,
                avatar: null,
                company: company,
                isClient: isCli,
                isSelf: false,
                notifications: true
            };

            const existingIdx = this.members.findIndex(m =>
                (m.email && m.email.toLowerCase() === email.toLowerCase()) ||
                (m.name && m.name.toLowerCase() === name.toLowerCase())
            );
            if (existingIdx !== -1) {
                this.members[existingIdx] = Object.assign({}, this.members[existingIdx], newMember);
                this.members = [...this.members];
            } else {
                this.members = [...this.members, newMember];
            }

            if (!this.platformUsers.some(u => (u.email || '').toLowerCase() === email.toLowerCase())) {
                this.platformUsers = [...this.platformUsers, newMember];
            }

            // Also add to the selected company in `companies` (e.g. Ajath Infotech Pvt Ltd)
            let foundComp = false;
            this.companies = this.companies.map(c => {
                if (c.name.toLowerCase() === company.toLowerCase()) {
                    foundComp = true;
                    const people = Array.isArray(c.people) ? [...c.people] : [];
                    if (!people.some(p => (p.name || '').toLowerCase() === name.toLowerCase())) {
                        people.push(newMember);
                    }
                    return Object.assign({}, c, { people });
                }
                return c;
            });
            if (!foundComp) {
                this.companies = [...this.companies, { name: company, people: [newMember] }];
            }

            this.syncBackend('invite_new', newMember.id, isCli, newMember);
            this.showToast('Invitation email sent to ' + email + ' • Added to ' + company + ' (Pending)');
            this.newPersonFormOpen = false;
            this.searchQuery = '';
            this.clientSearchQuery = '';
            this.addClientsModal = false;
        },

        sendLoginLink(m) {
            if (!m) return;
            this.sentLinkId = m.id;
            setTimeout(() => { if (this.sentLinkId === m.id) this.sentLinkId = null; }, 3000);

            this.members = this.members.map(item => {
                if (item.id === m.id) {
                    const updatedBadge = (item.roleBadge === 'Owner' || item.roleBadge === 'Admin' || item.roleBadge === 'Client')
                        ? item.roleBadge
                        : 'Pending';
                    return Object.assign({}, item, {
                        isPending: true,
                        roleBadge: updatedBadge,
                        company: item.company || 'Ajath Infotech Pvt Ltd'
                    });
                }
                return item;
            });

            const updatedMember = this.members.find(item => item.id === m.id) || m;
            this.syncBackend('send_login_link', updatedMember.id, Boolean(updatedMember.isClient), updatedMember);
            this.showToast('Invitation & login link email sent to ' + (updatedMember.email || updatedMember.name) + ' (Pending)');
        },

        openEditMemberInvite(m) {
            this.editingEmailMemberId = m.id;
            this.editingEmailValue = m.email || (m.name.toLowerCase().replace(/[^a-z0-9]+/g, '.') + '@ajath.com');
            this.editingCompanyValue = m.company || 'Ajath Infotech Pvt Ltd';
        },

        saveMemberInviteAndSend(m) {
            const email = (this.editingEmailValue || '').trim();
            const company = (this.editingCompanyValue || 'Ajath Infotech Pvt Ltd').trim();
            if (!email || !email.includes('@')) {
                this.showToast('Please enter a valid email address.', 'error');
                return;
            }
            this.members = this.members.map(item => {
                if (item.id === m.id) {
                    const updatedBadge = (item.roleBadge === 'Owner' || item.roleBadge === 'Admin' || item.roleBadge === 'Client')
                        ? item.roleBadge
                        : 'Pending';
                    return Object.assign({}, item, {
                        email: email,
                        company: company,
                        isPending: true,
                        roleBadge: updatedBadge
                    });
                }
                return item;
            });

            const updated = this.members.find(item => item.id === m.id);
            // Ensure they are listed under the selected company (e.g. Ajath Infotech Pvt Ltd)
            this.companies = this.companies.map(c => {
                if (c.name.toLowerCase() === company.toLowerCase()) {
                    const people = Array.isArray(c.people) ? [...c.people] : [];
                    if (!people.some(p => (p.name || '').toLowerCase() === updated.name.toLowerCase())) {
                        people.push(updated);
                    }
                    return Object.assign({}, c, { people });
                }
                return c;
            });

            this.editingEmailMemberId = null;
            this.syncBackend('invite_new', updated.id, Boolean(updated.isClient), updated);
            this.showToast('Invitation email sent to ' + email + ' • Added to ' + company + ' (Pending)');
        },

        toggleCompanyCheckbox(comp) {
            const ids = (comp.people || []).map(p => p.id);
            const allChecked = ids.length > 0 && ids.every(id => this.checkedUserIds.includes(id));
            if (allChecked) {
                this.checkedUserIds = this.checkedUserIds.filter(id => !ids.includes(id));
            } else {
                ids.forEach(id => {
                    if (!this.checkedUserIds.includes(id)) {
                        this.checkedUserIds.push(id);
                    }
                });
            }
        },

        addCheckedCompanyPeople() {
            const chosen = this.platformUsers.filter(u => this.checkedUserIds.includes(u.id));
            let nextMembers = [...this.members];
            chosen.forEach(u => {
                if (!nextMembers.some(m => m.id === u.id)) {
                    const clone = Object.assign({}, u, { notifications: true });
                    nextMembers.push(clone);
                }
            });
            this.members = nextMembers;
            this.syncBackend('add_bulk', 0, false);
            this.showToast(chosen.length + ' ' + (chosen.length === 1 ? 'person' : 'people') + ' added to this project.');
            this.checkedUserIds = [];
            this.companyPickerOpen = false;
        },

        removeMember(id) {
            this.members = this.members.filter(m => m.id !== id);
            this.syncBackend('remove', id, false);
        },

        syncBackend(action, userId, isClient, targetMember = null) {
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
            fetch('{{ route('projects.people.update', $project->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    action: action,
                    user_id: userId,
                    is_client: isClient,
                    target_member: targetMember,
                    members: this.members,
                    access_mode: this.accessMode,
                    clients_enabled: this.clientsEnabled
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.member && targetMember) {
                    this.members = this.members.map(item => {
                        if (item.id === targetMember.id || (item.email && data.member.email && item.email.toLowerCase() === data.member.email.toLowerCase())) {
                            return Object.assign({}, item, data.member);
                        }
                        return item;
                    });
                }
                if (data && data.redirect_url && action === 'invite_new') {
                    window.location.href = data.redirect_url;
                }
            })
            .catch(() => {});
        }
    }"
    class="max-w-[920px] mx-auto pb-20 font-sans text-[#1d2d35]">

    <!-- Basecamp Sheet Container (Exact Match to media_1793292903620_a436496c.jpg & media_1793292952761_79276e0c.jpg) -->
    <div class="bc-sheet bg-white rounded-[10px] border border-[#e5e2dc] shadow-[0_1px_4px_rgba(0,0,0,0.04)] min-h-[calc(100vh-84px)] overflow-visible">

        <!-- Top Breadcrumb Bar: Project Name -->
        <div class="px-6 py-2.5 border-b border-[#ece9e2] flex items-center text-[13.5px]">
            <a href="{{ route('projects.show', $project->id) }}" class="font-bold text-[#1d2d35] hover:underline">
                {{ $project->name }}
            </a>
        </div>

        <!-- Main Sheet Content -->
        <div class="px-8 sm:px-14 pt-8 pb-40">

            <!-- Notification Banner (Email Sent / Company Added) -->
            <div x-show="toastMessage"
                 x-cloak
                 x-transition
                 class="mb-5 px-4 py-3 rounded-[8px] border flex items-center justify-between gap-3 text-[13.5px] font-semibold shadow-2xs"
                 :class="toastType === 'error'
                     ? 'bg-[#fef2f2] border-[#fecaca] text-[#991b1b]'
                     : 'bg-[#ecfdf5] border-[#a7f3d0] text-[#065f46]'">
                <div class="flex items-center gap-2">
                    <svg x-show="toastType !== 'error'" class="w-4 h-4 text-[#10b981] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-text="toastMessage"></span>
                </div>
                <button type="button" @click="toastMessage = ''" class="text-xs opacity-70 hover:opacity-100 cursor-pointer">✕</button>
            </div>

            <!-- Title: Who’s on this project? -->
            <h1 class="text-[28px] sm:text-[32px] font-extrabold text-[#1d2d35] tracking-[-0.025em] leading-[1.15] mb-6">
                Who&rsquo;s on this project?
            </h1>

            <!-- Tabs: Team | Clients -->
            <div class="border-b border-[#ece9e2] flex items-center gap-1 text-[13.5px]">
                <button type="button"
                        @click="switchTab('team')"
                        class="px-3.5 py-2 rounded-t-[6px] transition-colors cursor-pointer -mb-px"
                        :class="activeTab === 'team'
                            ? 'border border-b-0 border-[#ece9e2] bg-white font-bold text-[#1d2d35]'
                            : 'text-[#6d6a65] hover:text-[#1d2d35] font-medium'">
                    Team
                </button>
                <button type="button"
                        @click="switchTab('clients')"
                        class="px-3.5 py-2 rounded-t-[6px] transition-colors cursor-pointer -mb-px"
                        :class="activeTab === 'clients'
                            ? 'border border-b-0 border-[#ece9e2] bg-white font-bold text-[#1d2d35]'
                            : 'text-[#6d6a65] hover:text-[#1d2d35] font-medium'">
                    Clients
                </button>
            </div>

            <!-- =============================================================== -->
            <!-- TAB 1: TEAM (media_1793292903620_a436496c.jpg)                  -->
            <!-- =============================================================== -->
            <div x-show="activeTab === 'team'">
                <!-- Invite-only / All-access Banner -->
                <div class="mt-6 mb-8 px-5 py-3.5 rounded-[8px] bg-[#f6f5f1] border border-[#ece9e2] text-[13.5px] leading-relaxed text-[#1d2d35]">
                    <template x-if="accessMode === 'invite_only'">
                        <div>
                            <strong class="font-bold">This project is Invite-only.</strong>
                            Only the people listed below have access. If you&rsquo;d like everyone in your Basecamp account to be able to access the project,
                            <button type="button" @click="accessMode = 'all_access'; syncBackend('settings', 0, false)" class="text-[#1b6ac9] underline hover:opacity-80 cursor-pointer">switch to All-access</button>
                            instead.
                        </div>
                    </template>
                    <template x-if="accessMode === 'all_access'">
                        <div>
                            <strong class="font-bold">This project is All-access.</strong>
                            Everyone in your Basecamp account can access this project. If you&rsquo;d like only the people listed below to have access,
                            <button type="button" @click="accessMode = 'invite_only'; syncBackend('settings', 0, false)" class="text-[#1b6ac9] underline hover:opacity-80 cursor-pointer">switch to Invite-only</button>
                            instead.
                        </div>
                    </template>
                </div>

                <!-- Two-Column Grid: Left Current Members | Right Invite More People -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">

                    <!-- LEFT COLUMN: PEOPLE CURRENTLY ON THIS PROJECT -->
                    <div class="md:border-r border-[#ece9e2] md:pr-6 space-y-5">
                        <template x-for="m in teamMembers" :key="m.id">
                            <div class="flex items-start gap-3.5">
                                <!-- Avatar (Photo or Initials Circle, with OUT badge on self) -->
                                <div class="relative w-[42px] h-[42px] rounded-full overflow-hidden flex-shrink-0 mt-0.5 shadow-2xs">
                                    <template x-if="m.avatar">
                                        <img :src="m.avatar" :alt="m.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!m.avatar">
                                        <span class="w-full h-full flex items-center justify-center text-white font-bold text-[15px] select-none"
                                              :class="m.bg || 'bg-[#00897b]'"
                                              x-text="m.initials"></span>
                                    </template>
                                    <div x-show="m.isSelf && oooActive"
                                         class="absolute bottom-0 inset-x-0 h-[38%] bg-[#facc15] flex items-center justify-center">
                                        <span class="text-[8.5px] font-black text-[#1d1c1b] leading-none tracking-tight">OUT</span>
                                    </div>
                                </div>

                                <!-- Member Info -->
                                <div class="min-w-0 flex-1 leading-snug relative">
                                    <div class="flex items-center flex-wrap gap-1.5">
                                        <span class="text-[15.5px] font-bold text-[#1d2d35]" x-text="m.name"></span>
                                        <template x-if="m.roleBadge === 'Owner'">
                                            <span class="px-2.5 py-0.5 rounded-full bg-[#e0669e] text-white text-[11px] font-bold leading-none">
                                                Owner
                                            </span>
                                        </template>
                                        <template x-if="m.roleBadge === 'Admin'">
                                            <span class="px-2.5 py-0.5 rounded-full bg-[#109888] text-white text-[11px] font-bold leading-none">
                                                Admin
                                            </span>
                                        </template>
                                        <template x-if="m.isPending || m.roleBadge === 'Pending'">
                                            <span class="px-2.5 py-0.5 rounded-full bg-[#facc15] text-[#1d2d35] text-[11px] font-bold leading-none">
                                                Pending
                                            </span>
                                        </template>
                                    </div>

                                    <div class="text-[13.5px] text-[#1d2d35]" x-text="(m.jobTitle || 'Team Member') + (m.company && m.company !== 'Ajath Infotech Pvt Ltd' ? ' • ' + m.company : '')"></div>

                                    <!-- Action Links Row -->
                                    <div class="text-[12px] text-[#6d6a65] mt-0.5 flex flex-wrap items-center gap-1 relative" @click.outside="if (openNotifyId === m.id) openNotifyId = null">
                                        <button type="button"
                                                @click="removeMember(m.id)"
                                                class="underline hover:text-[#1d2d35] cursor-pointer"
                                                x-text="m.isSelf ? 'Remove yourself' : 'Remove'"></button>

                                        <span x-show="!m.isSelf">&bull;</span>
                                        <button type="button"
                                                x-show="!m.isSelf"
                                                @click="sendLoginLink(m)"
                                                class="underline hover:text-[#1d2d35] cursor-pointer"
                                                x-text="sentLinkId === m.id ? 'Invitation email sent!' : 'Send a link to log in'"></button>

                                        <span>&bull;</span>
                                        <button type="button"
                                                @click="toggleMemberNotification(m.id)"
                                                class="underline hover:text-[#1d2d35] cursor-pointer"
                                                :class="!m.notifications ? 'text-[#8c8881]' : 'text-[#6d6a65]'"
                                                x-text="m.notifications ? 'Notifications on' : 'Notifications off'"></button>

                                        <template x-if="!m.isSelf && (m.isPending || m.roleBadge === 'Pending' || !m.roleBadge)">
                                            <span>
                                                <span>&bull;</span>
                                                <button type="button"
                                                        @click="openEditMemberInvite(m)"
                                                        class="underline text-[#1b6ac9] hover:opacity-80 cursor-pointer ml-0.5">
                                                    Add to company / Edit email
                                                </button>
                                            </span>
                                        </template>

                                        <!-- Inline Editor for Email & Company (e.g. Ajath Infotech Pvt Ltd) -->
                                        <div x-show="editingEmailMemberId === m.id"
                                             x-cloak
                                             class="w-full mt-2 p-3 rounded-[8px] border border-[#d8d4cc] bg-[#f9f8f6] text-[#1d2d35] space-y-2 shadow-xs">
                                            <div class="text-[12px] font-bold text-[#1d2d35]">Send invitation &amp; assign company for <span x-text="m.name"></span></div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-[#6d6a65] mb-0.5">Email address</label>
                                                <input type="email"
                                                       x-model="editingEmailValue"
                                                       placeholder="name@company.com"
                                                       class="w-full px-2.5 py-1.5 rounded-[5px] border border-[#d4d0c8] bg-white text-[12.5px] text-[#1d2d35] focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-[#6d6a65] mb-0.5">Company</label>
                                                <select x-model="editingCompanyValue"
                                                        class="w-full px-2.5 py-1.5 rounded-[5px] border border-[#d4d0c8] bg-white text-[12.5px] text-[#1d2d35] focus:outline-none">
                                                    <option value="Ajath Infotech Pvt Ltd">Ajath Infotech Pvt Ltd</option>
                                                    <template x-for="comp in companies" :key="'opt-' + comp.name">
                                                        <option :value="comp.name" x-show="comp.name !== 'Ajath Infotech Pvt Ltd'" x-text="comp.name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="flex items-center justify-end gap-2 pt-1">
                                                <button type="button"
                                                        @click="editingEmailMemberId = null"
                                                        class="px-2.5 py-1 rounded-[5px] border border-[#d4d0c8] text-[12px] text-[#1d2d35] cursor-pointer">
                                                    Cancel
                                                </button>
                                                <button type="button"
                                                        @click="saveMemberInviteAndSend(m)"
                                                        class="px-3 py-1 rounded-[5px] bg-[#2da562] hover:bg-[#258c52] text-white font-semibold text-[12px] cursor-pointer">
                                                    Save &amp; Send Email
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Inline Basecamp Notification Settings Popover for Member -->
                                        <div :id="'notify-pop-' + m.id"
                                             x-show="openNotifyId === m.id"
                                             x-cloak
                                             class="absolute left-0 top-[calc(100%+6px)] w-[275px] rounded-[8px] border border-[#e5e2dc] bg-[#f6f5f1] shadow-[0_10px_28px_rgba(0,0,0,0.22)] p-3 z-50 text-left select-none">
                                            <button type="button"
                                                    @click="setMemberNotification(m.id, true)"
                                                    class="w-full flex items-start gap-2 text-left rounded-[6px] p-1.5 hover:bg-[#ece9e2]/60 transition-colors cursor-pointer">
                                                <span class="w-4 pt-0.5 flex-shrink-0 text-[#1d2d35]">
                                                    <svg x-show="m.notifications" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-[13px] font-bold text-[#1d2d35] leading-snug" x-text="'Yes, notify ' + m.name"></div>
                                                    <div class="text-[12px] text-[#6d6a65] leading-[1.35] mt-0.5">
                                                        Loop them in by default when new things are posted to this project.
                                                    </div>
                                                </div>
                                            </button>

                                            <button type="button"
                                                    @click="setMemberNotification(m.id, false)"
                                                    class="w-full flex items-start gap-2 text-left rounded-[6px] p-1.5 mt-1.5 hover:bg-[#ece9e2]/60 transition-colors cursor-pointer">
                                                <span class="w-4 pt-0.5 flex-shrink-0 text-[#1d2d35]">
                                                    <svg x-show="!m.notifications" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-[13px] font-bold text-[#1d2d35] leading-snug" x-text="'Only notify ' + m.name + ' when...'"></div>
                                                    <div class="text-[12px] text-[#6d6a65] leading-[1.35] mt-0.5">
                                                        ...someone replies to a thread they&rsquo;re participating in, or @mentions them directly.
                                                    </div>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- RIGHT COLUMN: INVITE MORE PEOPLE (PLATFORM USERS / COMPANY) -->
                    <div class="md:pl-2">
                        <h2 class="text-[17.5px] font-bold text-[#1d2d35] mb-3">
                            Invite more people to this project
                        </h2>

                        <!-- Autocomplete Input for Platform Users -->
                        <div class="relative" @click.outside="dropdownOpen = false">
                            <input type="text"
                                   x-model="searchQuery"
                                   @focus="dropdownOpen = true; companyPickerOpen = false"
                                   @input="dropdownOpen = true"
                                   @keydown.enter.prevent="addTypedPerson(false)"
                                   placeholder="Type a name or email address..."
                                   class="bc-dotted-input w-full pb-1.5 text-[14.5px] text-[#1d2d35] placeholder-[#6d6a65] focus:outline-none">

                            <!-- Dropdown of Users on the Platform -->
                            <div x-show="dropdownOpen"
                                 x-cloak
                                 class="absolute left-0 right-0 top-[calc(100%+6px)] z-30 max-h-[290px] overflow-y-auto rounded-[8px] bg-white border border-[#d8d4cc] shadow-[0_10px_28px_rgba(0,0,0,0.22)] py-1.5">

                                <div class="px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-[#6d6a65] border-b border-[#ece9e2]">
                                    People on the platform
                                </div>

                                <template x-for="u in availablePlatformUsers" :key="u.id">
                                    <button type="button"
                                            @click="addPlatformUser(u, false)"
                                            class="w-full px-3 py-2 flex items-center justify-between gap-2.5 text-left hover:bg-[#f6f4f0] transition-colors cursor-pointer">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <template x-if="u.avatar">
                                                <img :src="u.avatar" :alt="u.name" class="w-7 h-7 rounded-full object-cover flex-shrink-0">
                                            </template>
                                            <template x-if="!u.avatar">
                                                <span class="w-7 h-7 rounded-full text-white font-bold text-[11px] flex items-center justify-center flex-shrink-0 select-none"
                                                      :class="u.bg || 'bg-[#00897b]'"
                                                      x-text="u.initials"></span>
                                            </template>
                                            <div class="min-w-0">
                                                <div class="text-[13.5px] font-bold text-[#1d2d35] truncate" x-text="u.name"></div>
                                                <div class="text-[11.5px] text-[#6d6a65] truncate" x-text="(u.jobTitle || 'Team Member') + ' • ' + (u.company || 'Ajath Infotech Pvt Ltd')"></div>
                                            </div>
                                        </div>
                                        <span class="text-[11.5px] font-semibold text-[#60a5fa] flex-shrink-0">+ Add</span>
                                    </button>
                                </template>

                                <!-- Option to invite a new email/name if typed -->
                                <div x-show="searchQuery.trim().length > 0" class="border-t border-[#ece9e2] mt-1 pt-1 px-2">
                                    <button type="button"
                                            @click="addTypedPerson(false)"
                                            class="w-full px-2.5 py-2 rounded-[6px] text-left text-[13px] font-semibold text-[#1b6ac9] hover:bg-[#f6f4f0] cursor-pointer">
                                        + Send email invite to &ldquo;<span x-text="searchQuery.trim()"></span>&rdquo;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Send email invites button when typing a new email/name -->
                        <div x-show="searchQuery.trim().length > 0" x-cloak class="mt-3">
                            <button type="button"
                                    @click="addTypedPerson(false)"
                                    class="px-4 py-1.5 rounded-[6px] bg-[#198744] hover:bg-[#146c36] text-white font-semibold text-[13.5px] transition-colors cursor-pointer">
                                Send email invites
                            </button>
                        </div>

                        <!-- "Or pick people from a company..." + "Invite a new person to Ajath Infotech Pvt Ltd" Links -->
                        <div class="mt-3.5 flex flex-wrap items-center gap-x-3 gap-y-1.5">
                            <button type="button"
                                    @click="companyPickerOpen = !companyPickerOpen; dropdownOpen = false; newPersonFormOpen = false"
                                    class="text-[13.5px] text-[#1b6ac9] underline hover:opacity-80 cursor-pointer">
                                Or pick people from a company...
                            </button>
                            <span class="text-[#8c8881] text-[12px]">&bull;</span>
                            <button type="button"
                                    @click="openNewPersonForm(searchQuery, false)"
                                    class="text-[13.5px] text-[#1b6ac9] underline hover:opacity-80 cursor-pointer">
                                + Add new person to Ajath Infotech Pvt Ltd...
                            </button>
                        </div>

                        <!-- Inline Basecamp "Add a new person to company & project" Form -->
                        <div x-show="newPersonFormOpen"
                             x-cloak
                             class="mt-3 rounded-[8px] border border-[#d8d4cc] bg-[#f9f8f6] p-4 space-y-3 shadow-sm">
                            <div class="flex items-center justify-between border-b border-[#ece9e2] pb-2">
                                <span class="text-[13.5px] font-bold text-[#1d2d35]">Invite a new person &amp; send email</span>
                                <button type="button" @click="newPersonFormOpen = false" class="text-[12px] text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">✕</button>
                            </div>

                            <div class="space-y-2.5">
                                <div>
                                    <label class="block text-[12px] font-bold text-[#1d2d35] mb-1">Full Name</label>
                                    <input type="text"
                                           x-model="newPersonName"
                                           placeholder="e.g. Shachish Sneh"
                                           class="w-full px-3 py-1.5 rounded-[6px] border border-[#d4d0c8] bg-white text-[13.5px] text-[#1d2d35] focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-[12px] font-bold text-[#1d2d35] mb-1">Email Address (invitation link will be sent here)</label>
                                    <input type="email"
                                           x-model="newPersonEmail"
                                           placeholder="name@ajath.com"
                                           class="w-full px-3 py-1.5 rounded-[6px] border border-[#d4d0c8] bg-white text-[13.5px] text-[#1d2d35] focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-[12px] font-bold text-[#1d2d35] mb-1">Company</label>
                                    <select x-model="newPersonCompany"
                                            class="w-full px-3 py-1.5 rounded-[6px] border border-[#d4d0c8] bg-white text-[13.5px] text-[#1d2d35] focus:outline-none">
                                        <option value="Ajath Infotech Pvt Ltd">Ajath Infotech Pvt Ltd (Our Company)</option>
                                        <template x-for="comp in companies" :key="'newcomp-' + comp.name">
                                            <option :value="comp.name" x-show="comp.name !== 'Ajath Infotech Pvt Ltd'" x-text="comp.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[12px] font-bold text-[#1d2d35] mb-1">Title / Role</label>
                                    <input type="text"
                                           x-model="newPersonTitle"
                                           placeholder="Team Member"
                                           class="w-full px-3 py-1.5 rounded-[6px] border border-[#d4d0c8] bg-white text-[13.5px] text-[#1d2d35] focus:outline-none">
                                </div>
                            </div>

                            <div class="pt-1 flex items-center justify-end gap-2">
                                <button type="button"
                                        @click="newPersonFormOpen = false"
                                        class="px-3 py-1.5 rounded-[6px] border border-[#d6d2c9] text-[12.5px] text-[#1d2d35] cursor-pointer">
                                    Cancel
                                </button>
                                <button type="button"
                                        @click="submitNewPersonInvite()"
                                        class="px-3.5 py-1.5 rounded-[6px] bg-[#2da562] hover:bg-[#258c52] text-white font-semibold text-[12.5px] cursor-pointer">
                                    Add to project &amp; send email
                                </button>
                            </div>
                        </div>

                        <!-- Expandable Company & Platform Users Picker -->
                        <div x-show="companyPickerOpen"
                             x-cloak
                             class="mt-3 rounded-[8px] border border-[#d8d4cc] bg-[#f9f8f6] p-4 space-y-3 shadow-sm">
                            <div class="flex items-center justify-between border-b border-[#ece9e2] pb-2">
                                <span class="text-[13px] font-bold text-[#1d2d35]">Pick people by company</span>
                                <button type="button" @click="companyPickerOpen = false" class="text-[12px] text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">✕</button>
                            </div>

                            <div class="max-h-[260px] overflow-y-auto space-y-3 pr-1">
                                <template x-for="comp in companies" :key="comp.name">
                                    <div class="rounded-[6px] bg-white border border-[#ece9e2] p-2.5">
                                        <label class="flex items-center justify-between gap-2 cursor-pointer font-bold text-[13px] text-[#1d2d35]">
                                            <span class="flex items-center gap-2">
                                                <input type="checkbox"
                                                       @change="toggleCompanyCheckbox(comp)"
                                                       :checked="(comp.people || []).length > 0 && (comp.people || []).every(p => checkedUserIds.includes(p.id))"
                                                       class="rounded border-[#8c8881]">
                                                <span x-text="comp.name"></span>
                                            </span>
                                            <span class="text-[11px] font-normal text-[#6d6a65]" x-text="(comp.people || []).length + ' ' + ((comp.people || []).length === 1 ? 'person' : 'people')"></span>
                                        </label>

                                        <div x-show="(comp.people || []).length > 0" class="mt-2 pl-5 space-y-1.5 border-t border-[#f2efe9] pt-2">
                                            <template x-for="p in comp.people" :key="p.id">
                                                <label class="flex items-center justify-between gap-2 text-[12.5px] text-[#1d2d35] cursor-pointer">
                                                    <span class="flex items-center gap-2 min-w-0">
                                                        <input type="checkbox" :value="p.id" x-model.number="checkedUserIds" class="rounded border-[#8c8881]">
                                                        <span class="truncate font-medium" x-text="p.name"></span>
                                                        <span class="text-[11px] text-[#6d6a65] truncate" x-text="'(' + (p.jobTitle || 'Member') + ')'"></span>
                                                    </span>
                                                    <span x-show="members.some(m => m.id === p.id)" class="text-[10.5px] text-[#2da562] font-semibold flex-shrink-0">On project</span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="pt-1 flex items-center justify-end gap-2">
                                <button type="button"
                                        @click="companyPickerOpen = false"
                                        class="px-3 py-1.5 rounded-[6px] border border-[#d6d2c9] text-[12.5px] text-[#1d2d35] cursor-pointer">
                                    Cancel
                                </button>
                                <button type="button"
                                        @click="addCheckedCompanyPeople()"
                                        class="px-3.5 py-1.5 rounded-[6px] bg-[#1b6ac9] hover:bg-[#1557a8] text-white font-semibold text-[12.5px] cursor-pointer">
                                    Add selected to project
                                </button>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <!-- =============================================================== -->
            <!-- TAB 2: CLIENTS (Exact Match to media_1793292952761_79276e0c.jpg)-->
            <!-- =============================================================== -->
            <div x-show="activeTab === 'clients'" x-cloak>
                <!-- Work with clients Box -->
                <div class="mt-6 mb-7 px-6 py-5 rounded-[10px] bg-[#f6f5f1] border border-[#ece9e2] text-[#1d2d35]">
                    <div class="flex items-center gap-2.5 mb-2.5">
                        <button type="button"
                                @click="clientsEnabled = !clientsEnabled; syncBackend('settings', 0, false)"
                                class="inline-flex items-center gap-1.5 px-2 py-1 rounded-[5px] text-[11.5px] font-bold transition-colors cursor-pointer select-none"
                                :class="clientsEnabled ? 'bc-client-switch-on bg-[#198744] text-white' : 'bg-[#6d6a65] text-white'">
                            <span x-show="clientsEnabled">On</span>
                            <span class="w-[7px] h-[15px] rounded-[2px] bg-[#152026]/80 inline-block"></span>
                            <span x-show="!clientsEnabled" x-cloak>Off</span>
                        </button>
                        <span class="text-[15.5px] font-bold text-[#1d2d35]">Work with clients</span>
                    </div>

                    <p class="text-[13.5px] text-[#1d2d35] leading-relaxed">
                        Choose what clients see by clicking the
                        <span class="bc-visibility-pill inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#dbeafe] text-[#1e3a5f] text-[12px] font-medium mx-0.5 align-baseline">
                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Visibility</span>
                        </span>
                        link on the page you want to share, like a message, to-do list, or file.
                    </p>

                    <p class="text-[13.5px] text-[#1d2d35] leading-relaxed mt-1">
                        <strong class="font-bold">Finished with clients on this project?</strong>
                        Remove them below, then flip the switch to off.
                    </p>
                </div>

                <!-- Client Company Section Header + "Add clients" Button -->
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-[17px] font-bold text-[#1d2d35]">NDC Homes</h2>
                    <button type="button"
                            @click="addClientsModal = !addClientsModal"
                            class="px-4 py-1.5 rounded-full bg-[#1b6ac9] hover:bg-[#1557a8] text-white text-[13.5px] font-semibold transition-colors cursor-pointer">
                        Add clients
                    </button>
                </div>

                <!-- Expandable "Add clients" Picker (Platform Users & Companies) -->
                <div x-show="addClientsModal"
                     x-cloak
                     class="mb-6 rounded-[8px] border border-[#d8d4cc] bg-[#f9f8f6] p-4 max-w-[460px] shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[13.5px] font-bold text-[#1d2d35]">Add clients from platform or company</span>
                        <button type="button" @click="addClientsModal = false" class="text-[12px] text-[#6d6a65] hover:text-[#1d2d35] cursor-pointer">✕</button>
                    </div>
                    <input type="text"
                           x-model="clientSearchQuery"
                           @keydown.enter.prevent="addTypedPerson(true)"
                           placeholder="Type a client name, company, or email..."
                           class="w-full px-3 py-1.5 rounded-[6px] border border-[#d4d0c8] bg-white text-[13.5px] text-[#1d2d35] mb-2 focus:outline-none">
                    <div class="max-h-[210px] overflow-y-auto divide-y divide-[#ece9e2]">
                        <template x-for="u in availableClientCandidates" :key="'cli-' + u.id">
                            <button type="button"
                                    @click="addPlatformUser(u, true)"
                                    class="w-full py-2 px-2 flex items-center justify-between gap-2 text-left hover:bg-[#f6f4f0] rounded-[5px] cursor-pointer">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-7 h-7 rounded-full text-white font-bold text-[11px] flex items-center justify-center flex-shrink-0"
                                          :class="u.bg || 'bg-[#c026d3]'"
                                          x-text="u.initials"></span>
                                    <div class="min-w-0">
                                        <div class="text-[13px] font-bold text-[#1d2d35] truncate" x-text="u.name"></div>
                                        <div class="text-[11px] text-[#6d6a65] truncate" x-text="(u.jobTitle || 'Client') + ' • ' + (u.company || 'NDC Homes')"></div>
                                    </div>
                                </div>
                                <span class="text-[11.5px] font-semibold text-[#60a5fa] flex-shrink-0">+ Add</span>
                            </button>
                        </template>
                    </div>
                    <div x-show="clientSearchQuery.trim().length > 0" class="border-t border-[#ece9e2] mt-2 pt-2">
                        <button type="button"
                                @click="addTypedPerson(true)"
                                class="w-full px-2.5 py-2 rounded-[6px] text-left text-[13px] font-semibold text-[#60a5fa] hover:bg-[#f6f4f0] cursor-pointer">
                            + Invite &ldquo;<span x-text="clientSearchQuery.trim()"></span>&rdquo; as a client
                        </button>
                    </div>
                </div>

                <!-- Client Entries List -->
                <div class="space-y-5">
                    <template x-for="c in clientMembers" :key="c.id">
                        <div class="flex items-start gap-3.5">
                            <!-- Client Avatar (Matches media_1793435235267_c172778d.jpg) -->
                            <div class="relative w-[42px] h-[42px] rounded-full overflow-hidden flex-shrink-0 mt-0.5 shadow-2xs">
                                <template x-if="c.avatar">
                                    <img :src="c.avatar" :alt="c.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!c.avatar">
                                    <span class="w-full h-full flex items-center justify-center text-white font-bold text-[16px] select-none"
                                          :class="c.bg || 'bg-[#e0522b]'"
                                          x-text="c.initials"></span>
                                </template>
                            </div>

                            <!-- Client Info -->
                            <div class="min-w-0 flex-1 leading-snug">
                                <div class="flex items-center flex-wrap gap-1.5">
                                    <span class="text-[15.5px] font-bold text-[#1d2d35]" x-text="c.name"></span>
                                    <span class="px-2.5 py-0.5 rounded-full bg-[#f9d976] text-[#1d2d35] text-[11px] font-bold leading-none">
                                        Client
                                    </span>
                                    <template x-if="c.isPending || c.roleBadge === 'Pending'">
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#facc15] text-[#1d2d35] text-[11px] font-bold leading-none">
                                            Pending
                                        </span>
                                    </template>
                                </div>

                                <div class="text-[13.5px] text-[#1d2d35]" x-text="c.jobTitle || 'Project Owner at NDC Homes'"></div>

                                <div class="text-[12px] text-[#6d6a65] mt-0.5 flex flex-wrap items-center gap-1">
                                    <button type="button"
                                            @click="removeMember(c.id)"
                                            class="underline hover:text-[#1d2d35] cursor-pointer">Remove</button>
                                    <span>&bull;</span>
                                    <button type="button"
                                            @click="sendLoginLink(c)"
                                            class="underline hover:text-[#1d2d35] cursor-pointer"
                                            x-text="sentLinkId === c.id ? 'Invitation email sent!' : 'Send a link to log in'"></button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Bottom TIP Row -->
                <div class="mt-8 flex items-center gap-2 text-[12.5px] text-[#6d6a65]">
                    <span class="px-1.5 py-0.5 rounded-[4px] bg-[#e5e2dc] text-[#1d2d35] text-[10px] font-bold uppercase tracking-wider leading-none">
                        TIP
                    </span>
                    <span>
                        <a href="{{ route('enrollments.clients.create') }}" class="underline hover:text-[#1d2d35]">Invite new clients</a>
                        to your Basecamp account before adding them to the project.
                    </span>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
