import React, { useState, useRef, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  ChevronDown,
  Check,
  Lock,
  Users,
  UserPlus,
  Building2,
  Plus,
  Trash2,
  Mail,
  ShieldCheck,
} from 'lucide-react';
import { useProject } from '../../context/ProjectContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { addProjectMember, inviteProjectClient } from '../../services/projectService';
import { getInitials } from '../../lib/utils';

interface TemplateOption {
  id: string;
  name: string;
  title: string;
  description: string;
}

const TEMPLATES: TemplateOption[] = [
  {
    id: 'blank',
    name: 'Blank Project',
    title: '',
    description: '',
  },
  {
    id: 'client',
    name: 'Client Deliverable & Handover',
    title: 'Client Web & Mobile Deliverables',
    description: 'Weekly milestones, sprint scope, design mockups, and client feedback.',
  },
  {
    id: 'mobile',
    name: 'Mobile App Sprint',
    title: 'Mobile App Development',
    description: 'iOS & Android feature roadmap, API contracts, and release testing.',
  },
  {
    id: 'ops',
    name: 'Company Operations & HR',
    title: 'Operations & Engineering Standards',
    description: 'Internal documentation, handbook SOPs, onboarding, and team schedules.',
  },
];

export function CreateProjectPage() {
  const navigate = useNavigate();
  const { createNewProject, refreshProjects } = useProject();
  const { currentOrganization, teams, members: orgMembers } = useOrganization();
  const { profile, userRole } = useAuth();
  const { addToast } = useToast();

  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [selectedTeamId, setSelectedTeamId] = useState<string>('');
  const [selectedMemberUserIds, setSelectedMemberUserIds] = useState<string[]>([]);
  const [newInvites, setNewInvites] = useState<
    Array<{ id: string; email: string; name: string; role: 'colleague' | 'client'; clientCompany: string }>
  >([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isTemplateMenuOpen, setIsTemplateMenuOpen] = useState(false);
  const [selectedTemplateId, setSelectedTemplateId] = useState<string>('blank');

  const titleInputRef = useRef<HTMLInputElement>(null);
  const templateMenuRef = useRef<HTMLDivElement>(null);

  // Auto-focus the project name input
  useEffect(() => {
    titleInputRef.current?.focus();
  }, []);

  // Pre-select all organization members by default for the project
  useEffect(() => {
    if (orgMembers && orgMembers.length > 0) {
      setSelectedMemberUserIds(orgMembers.map((m) => m.user_id));
    }
  }, [orgMembers]);

  // Close template menu on outside click
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (templateMenuRef.current && !templateMenuRef.current.contains(e.target as Node)) {
        setIsTemplateMenuOpen(false);
      }
    };
    if (isTemplateMenuOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [isTemplateMenuOpen]);

  // Clients cannot create projects
  if (userRole === 'CLIENT') {
    return (
      <div className="w-full max-w-4xl mx-auto py-16 px-4 animate-in fade-in duration-300">
        <div className="bg-white dark:bg-slate-900 rounded-2xl p-8 sm:p-12 border border-slate-200/80 dark:border-slate-800 shadow-sm text-center space-y-6">
          <div className="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 mx-auto flex items-center justify-center shadow-inner">
            <Lock className="w-8 h-8" />
          </div>
          <div className="space-y-2">
            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
              <span>Access Restricted</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
              Clients cannot create new projects
            </h1>
            <p className="text-sm text-slate-500 dark:text-slate-400 max-w-lg mx-auto leading-relaxed">
              In Ajath PMT, only team members, administrators, and account owners can create new projects.
            </p>
          </div>
          <div className="pt-2 flex items-center justify-center gap-3">
            <button
              onClick={() => navigate('/dashboard')}
              className="px-5 py-2.5 rounded-lg bg-[#1070e5] hover:bg-[#0c61c7] text-white font-semibold text-xs shadow-sm active:scale-95 transition-all cursor-pointer"
            >
              Return to Dashboard
            </button>
          </div>
        </div>
      </div>
    );
  }

  const handleSelectTemplate = (tpl: TemplateOption) => {
    setSelectedTemplateId(tpl.id);
    setName(tpl.title);
    setDescription(tpl.description);
    setIsTemplateMenuOpen(false);
    titleInputRef.current?.focus();
  };

  const toggleMemberSelection = (userId: string) => {
    setSelectedMemberUserIds((prev) =>
      prev.includes(userId) ? prev.filter((id) => id !== userId) : [...prev, userId]
    );
  };

  const handleSelectAllMembers = () => {
    setSelectedMemberUserIds(orgMembers.map((m) => m.user_id));
  };

  const handleClearMembers = () => {
    if (profile?.id) {
      setSelectedMemberUserIds([profile.id]);
    } else {
      setSelectedMemberUserIds([]);
    }
  };

  const handleAddInviteRow = () => {
    setNewInvites((prev) => [
      ...prev,
      {
        id: `inv-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
        email: '',
        name: '',
        role: 'colleague',
        clientCompany: '',
      },
    ]);
  };

  const handleRemoveInviteRow = (id: string) => {
    setNewInvites((prev) => prev.filter((inv) => inv.id !== id));
  };

  const handleUpdateInvite = (id: string, field: string, value: string) => {
    setNewInvites((prev) =>
      prev.map((inv) => (inv.id === id ? { ...inv, [field]: value } : inv))
    );
  };

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    const trimmedName = name.trim();
    if (!trimmedName) {
      addToast('Please name your project before creating it.', 'error');
      titleInputRef.current?.focus();
      return;
    }

    setIsSubmitting(true);
    try {
      const { data, error } = await createNewProject({
        name: trimmedName,
        description: description.trim() || undefined,
        team_id: selectedTeamId || undefined,
        status: 'ACTIVE',
        visibility: 'ORGANIZATION',
        created_by: profile?.id,
      });

      if (error || !data) {
        addToast(error?.message || 'Failed to create project workspace.', 'error');
        setIsSubmitting(false);
        return;
      }

      // Add selected organization members to the newly created project
      const memberPromises = selectedMemberUserIds
        .filter((userId) => userId !== profile?.id)
        .map((userId) =>
          addProjectMember(data.id, userId, 'PROJECT_MEMBER', profile?.id)
        );
      await Promise.allSettled(memberPromises);

      // Send invitations to new colleagues or clients
      const validInvites = newInvites.filter((inv) => inv.email.trim());
      if (validInvites.length > 0) {
        const invitePromises = validInvites.map((inv) =>
          inviteProjectClient(
            data.id,
            inv.email.trim(),
            inv.name.trim() || inv.email.split('@')[0],
            inv.role,
            inv.role === 'client' ? inv.clientCompany.trim() || undefined : undefined
          )
        );
        await Promise.allSettled(invitePromises);
      }

      await refreshProjects();

      // Initialize empty tool settings matching Basecamp new project state
      const initialEmptyTools = {
        project_id: data.id,
        message_board: false,
        todos: false,
        docs_files: false,
        campfire: false,
        schedule: false,
        checkins: false,
        kanban: false,
        card_table: false,
        doors: false,
      };
      try {
        localStorage.setItem(`proj_tools_${data.id}`, JSON.stringify(initialEmptyTools));
      } catch (e) {}

      addToast(`"${trimmedName}" created successfully!`, 'success');
      // Redirect directly into the new project workspace
      navigate(`/projects/${data.id}`);
    } catch (err: any) {
      addToast(err?.message || 'Unexpected error creating project.', 'error');
      setIsSubmitting(false);
    }
  };

  return (
    <div className="w-full max-w-5xl mx-auto py-6 sm:py-8 px-4 sm:px-6 animate-in fade-in duration-200">
      {/* Basecamp Document Card matching screenshot */}
      <div className="w-full bg-white dark:bg-slate-900 rounded-xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
        {/* Top Header Bar: Use a template */}
        <div
          className="px-6 sm:px-12 py-3.5 flex justify-end border-b border-slate-100 dark:border-slate-800/80 relative"
          ref={templateMenuRef}
        >
          <button
            type="button"
            onClick={() => setIsTemplateMenuOpen(!isTemplateMenuOpen)}
            className="flex items-center gap-1.5 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition-colors cursor-pointer"
          >
            <span>Use a template</span>
            <ChevronDown className="w-3.5 h-3.5 text-slate-500" />
          </button>

          {/* Template Dropdown */}
          {isTemplateMenuOpen && (
            <div className="absolute right-6 sm:right-12 top-10 w-72 bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 py-1.5 z-50 animate-in zoom-in-95 duration-100 text-left">
              <div className="px-3.5 py-2 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                Select a Template
              </div>
              {TEMPLATES.map((tpl) => (
                <button
                  key={tpl.id}
                  type="button"
                  onClick={() => handleSelectTemplate(tpl)}
                  className="w-full px-3.5 py-2.5 text-xs text-left hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-between text-slate-800 dark:text-slate-200 font-medium transition-colors cursor-pointer"
                >
                  <span>{tpl.name}</span>
                  {selectedTemplateId === tpl.id && (
                    <Check className="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                  )}
                </button>
              ))}
            </div>
          )}
        </div>

        {/* Card Main Body */}
        <div className="px-6 sm:px-12 py-8 sm:py-12">
          <form onSubmit={handleCreate}>
            {/* Project Name (Large Bold Title) */}
            <div>
              <input
                ref={titleInputRef}
                type="text"
                value={name}
                onChange={(e) => setName(e.target.value)}
                placeholder="Name the project"
                className="w-full text-3xl sm:text-[38px] font-bold text-slate-900 dark:text-slate-100 placeholder:text-slate-900 placeholder:font-bold dark:placeholder:text-slate-100 focus:outline-none bg-transparent tracking-tight leading-tight"
              />
            </div>

            {/* Optional Description */}
            <div className="mt-2 sm:mt-2.5">
              <textarea
                rows={2}
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                placeholder="Add an optional description"
                className="w-full text-base sm:text-lg text-slate-600 dark:text-slate-300 placeholder:text-slate-500/90 dark:placeholder:text-slate-400 placeholder:font-normal focus:outline-none bg-transparent resize-none leading-relaxed"
              />
            </div>

            {/* 1. Team Selection */}
            {teams && teams.length > 0 && (
              <div className="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-2">
                  <Users className="w-4 h-4 text-indigo-500" />
                  <span>Assign to a Team (Optional)</span>
                </label>
                <p className="text-xs text-slate-500 dark:text-slate-400 mb-2.5">
                  Link this project to a department or functional unit workspace.
                </p>
                <select
                  value={selectedTeamId}
                  onChange={(e) => setSelectedTeamId(e.target.value)}
                  className="w-full sm:w-80 px-3.5 py-2 text-xs font-medium rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                >
                  <option value="">No specific team (Company-wide / Cross-functional)</option>
                  {teams.map((t) => (
                    <option key={t.id} value={t.id}>
                      {t.name}
                    </option>
                  ))}
                </select>
              </div>
            )}

            {/* 2. Organization Members Assignment */}
            <div className="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <Building2 className="w-4 h-4 text-blue-500" />
                    <span>Add People from {currentOrganization?.name || 'Company'}</span>
                  </label>
                  <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Select which colleagues should have access to this project workspace immediately.
                  </p>
                </div>
                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={handleSelectAllMembers}
                    className="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer"
                  >
                    Select All ({orgMembers.length})
                  </button>
                  <span className="text-slate-300 dark:text-slate-600">•</span>
                  <button
                    type="button"
                    onClick={handleClearMembers}
                    className="text-xs font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 cursor-pointer"
                  >
                    Clear
                  </button>
                </div>
              </div>

              {orgMembers.length === 0 ? (
                <p className="text-xs text-slate-400 italic py-2">
                  No other company members found. You can invite colleagues below.
                </p>
              ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-56 overflow-y-auto p-1 border border-slate-100 dark:border-slate-800/80 rounded-2xl bg-slate-50/50 dark:bg-slate-900/40">
                  {orgMembers.map((m) => {
                    const isSelected = selectedMemberUserIds.includes(m.user_id);
                    const isCurrentUser = m.user_id === profile?.id;
                    const name = m.profile?.full_name || 'Member';
                    const initials = getInitials(name);

                    return (
                      <div
                        key={m.user_id}
                        onClick={() => {
                          if (!isCurrentUser) toggleMemberSelection(m.user_id);
                        }}
                        className={`p-2.5 rounded-xl border transition-all flex items-center justify-between gap-2.5 select-none ${
                          isCurrentUser
                            ? 'bg-blue-50/50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-900 cursor-default'
                            : isSelected
                            ? 'bg-blue-50/80 dark:bg-blue-950/40 border-blue-400 dark:border-blue-700 cursor-pointer shadow-2xs'
                            : 'bg-white dark:bg-slate-800/60 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 cursor-pointer'
                        }`}
                      >
                        <div className="flex items-center gap-2.5 min-w-0">
                          <div className="w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0 shadow-2xs">
                            {initials}
                          </div>
                          <div className="min-w-0">
                            <p className="text-xs font-bold text-slate-900 dark:text-white truncate">
                              {name} {isCurrentUser && <span className="text-[10px] text-blue-600 dark:text-blue-400 font-normal">(You)</span>}
                            </p>
                            <p className="text-[10px] text-slate-400 truncate">
                              {m.profile?.email || m.role}
                            </p>
                          </div>
                        </div>
                        <div
                          className={`w-5 h-5 rounded-md flex items-center justify-center transition-colors shrink-0 ${
                            isSelected || isCurrentUser
                              ? 'bg-blue-600 text-white'
                              : 'border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900'
                          }`}
                        >
                          {(isSelected || isCurrentUser) && <Check className="w-3.5 h-3.5 stroke-[3]" />}
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>

            {/* 3. Invite New People directly into this project */}
            <div className="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
              <div className="flex items-center justify-between">
                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <UserPlus className="w-4 h-4 text-emerald-500" />
                    <span>Invite New People or Clients</span>
                  </label>
                  <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Need to include someone not yet in {currentOrganization?.name || 'the company'}? Invite them by email.
                  </p>
                </div>
                <button
                  type="button"
                  onClick={handleAddInviteRow}
                  className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 dark:hover:bg-emerald-900 text-emerald-600 dark:text-emerald-400 text-xs font-bold border border-emerald-200/60 dark:border-emerald-800/60 transition-colors cursor-pointer"
                >
                  <Plus className="w-3.5 h-3.5" />
                  <span>Add Invitee</span>
                </button>
              </div>

              {newInvites.length > 0 && (
                <div className="mt-3.5 space-y-2.5">
                  {newInvites.map((inv) => (
                    <div
                      key={inv.id}
                      className="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 flex flex-col sm:flex-row items-start sm:items-center gap-2.5 animate-in fade-in duration-150"
                    >
                      <div className="relative flex-1 w-full sm:w-auto">
                        <Mail className="w-3.5 h-3.5 text-slate-400 absolute left-3 top-3" />
                        <input
                          type="email"
                          placeholder="Email address (e.g. colleague@acme.com)"
                          value={inv.email}
                          onChange={(e) => handleUpdateInvite(inv.id, 'email', e.target.value)}
                          className="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none"
                        />
                      </div>
                      <input
                        type="text"
                        placeholder="Full Name (optional)"
                        value={inv.name}
                        onChange={(e) => handleUpdateInvite(inv.id, 'name', e.target.value)}
                        className="w-full sm:w-44 px-3 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none"
                      />
                      <select
                        value={inv.role}
                        onChange={(e) => handleUpdateInvite(inv.id, 'role', e.target.value)}
                        className="w-full sm:w-36 px-3 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none font-medium"
                      >
                        <option value="colleague">Colleague (Team)</option>
                        <option value="client">Client Partner</option>
                      </select>
                      {inv.role === 'client' && (
                        <input
                          type="text"
                          placeholder="Client Company"
                          value={inv.clientCompany}
                          onChange={(e) => handleUpdateInvite(inv.id, 'clientCompany', e.target.value)}
                          className="w-full sm:w-36 px-3 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none"
                        />
                      )}
                      <button
                        type="button"
                        onClick={() => handleRemoveInviteRow(inv.id)}
                        className="p-2 text-slate-400 hover:text-rose-500 transition-colors cursor-pointer self-end sm:self-center"
                        title="Remove invitee"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Action Buttons Row */}
            <div className="mt-8 sm:mt-9 flex items-center gap-3.5">
              <button
                type="submit"
                disabled={isSubmitting}
                className="bg-[#1070e5] hover:bg-[#0c61c7] active:bg-[#094ea3] text-white px-5 py-2.5 rounded-md font-semibold text-sm shadow-xs transition-colors cursor-pointer disabled:opacity-50"
              >
                {isSubmitting ? 'Creating...' : 'Create this project'}
              </button>

              <button
                type="button"
                onClick={() => navigate('/dashboard')}
                className="bg-white dark:bg-slate-800 border border-slate-300/90 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-[#1070e5] dark:text-blue-400 hover:border-slate-400 px-4 py-2.5 rounded-md font-semibold text-sm transition-colors cursor-pointer"
              >
                Never mind
              </button>
            </div>

            {/* "What happens next?" Informative Note */}
            <div className="mt-10 sm:mt-12 max-w-xl">
              <p className="text-xs sm:text-[13px] text-slate-700 dark:text-slate-300 leading-relaxed">
                <strong className="text-slate-900 dark:text-slate-100 font-bold mr-1">What happens next?</strong>{' '}
                We'll drop you right on your project so you can get started. You'll be able to invite people and jump into adding messages, documents, etc. from there.
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
