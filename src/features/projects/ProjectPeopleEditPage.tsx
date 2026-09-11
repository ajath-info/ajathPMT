import React, { useState, useEffect, useMemo } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import {
  Users,
  Search,
  Check,
  Building2,
  X,
  AlertCircle,
  Shield,
  Briefcase,
  UserPlus,
  ChevronDown,
  Sparkles,
  Mail,
  Send,
  Lock,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useToast } from '../../context/ToastContext';
import {
  getProjectById,
  getProjectMembers,
  addProjectMember,
  removeProjectMember,
  inviteProjectClient,
} from '../../services/projectService';
import { Project, ProjectMember, OrganizationMember } from '../../types';

// Avatar colors matching Basecamp palette
const AVATAR_COLORS: Record<string, string> = {
  SN: 'bg-[#872ec4]',
  AI: 'bg-teal-600',
  GK: 'bg-pink-600',
  PK: 'bg-cyan-600',
  RK: 'bg-rose-600',
  E: 'bg-orange-500',
  RS: 'bg-teal-600',
  B: 'bg-amber-500',
  PT: 'bg-slate-600',
  SJ: 'bg-indigo-600',
  DC: 'bg-emerald-600',
  CW: 'bg-fuchsia-600',
};

function getInitials(fullName?: string): string {
  if (!fullName) return 'CW';
  const trimmed = fullName.trim();
  if (trimmed === 'Edward') return 'E';
  if (trimmed === 'Rasgo') return 'RS';
  const parts = trimmed.split(/\s+/);
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function getAvatarColor(initials: string): string {
  if (AVATAR_COLORS[initials]) return AVATAR_COLORS[initials];
  return 'bg-[#872ec4]';
}

interface ProjectPeopleEditPageProps {
  project?: Project | null;
  onProjectUpdated?: () => void;
}

export function ProjectPeopleEditPage({ project: propProject, onProjectUpdated }: ProjectPeopleEditPageProps) {
  const { projectId } = useParams<{ projectId: string }>();
  const navigate = useNavigate();
  const { profile, userRole } = useAuth();
  const { currentOrganization, members: orgMembers } = useOrganization();
  const { addToast } = useToast();

  const [project, setProject] = useState<Project | null>(propProject || null);
  const [members, setMembers] = useState<ProjectMember[]>([]);
  const [loading, setLoading] = useState(!propProject);
  const [activeTab, setActiveTab] = useState<'team' | 'clients'>('team');

  // Basecamp Gate: Clients cannot manage project access or members
  if (userRole === 'CLIENT') {
    return (
      <div className="max-w-xl mx-auto py-20 text-center space-y-4">
        <div className="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto border border-amber-200 dark:border-amber-800">
          <Shield className="w-7 h-7" />
        </div>
        <h2 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100">Access Restricted</h2>
        <p className="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
          Client stakeholders do not have permission to manage internal team members or modify project access.
        </p>
        <button
          onClick={() => navigate(`/projects/${projectId || ''}`)}
          className="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs cursor-pointer shadow-xs transition-colors"
        >
          Return to Project Overview
        </button>
      </div>
    );
  }

  // Access mode: 'invite-only' (default) vs 'all-access'
  const [accessMode, setAccessMode] = useState<'invite-only' | 'all-access'>(() => {
    if (!projectId) return 'invite-only';
    try {
      const saved = localStorage.getItem(`proj_access_mode_${projectId}`);
      if (saved === 'all-access') return 'all-access';
    } catch (e) {}
    return 'invite-only';
  });

  // Notifications toggle map per memberId
  const [memberNotifs, setMemberNotifs] = useState<Record<string, boolean>>(() => {
    if (!projectId) return {};
    try {
      const saved = localStorage.getItem(`proj_notifs_${projectId}`);
      if (saved) return JSON.parse(saved);
    } catch (e) {}
    return {};
  });

  // Right column invite search & selection
  const [searchQuery, setSearchQuery] = useState('');
  const [isCompanyDropdownOpen, setIsCompanyDropdownOpen] = useState(true);
  const [isAddingMember, setIsAddingMember] = useState(false);

  // Clients feature toggle state and form
  const [clientAccessEnabled, setClientAccessEnabled] = useState(false);
  const [newClientEmail, setNewClientEmail] = useState('');
  const [newClientName, setNewClientName] = useState('');
  const [newClientMessage, setNewClientMessage] = useState('');
  const [isSubmittingClient, setIsSubmittingClient] = useState(false);

  const clientMembers = useMemo(() => {
    return members.filter((m) => m.role === 'PROJECT_CLIENT');
  }, [members]);

  const handleInviteClient = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!project?.id || !newClientEmail.trim()) return;

    setIsSubmittingClient(true);
    try {
      const res = await inviteProjectClient(
        project.id,
        newClientEmail.trim(),
        newClientName.trim() || undefined,
        newClientMessage.trim() || undefined,
        profile?.id
      );

      if (res.error) {
        addToast({
          title: 'Invitation Error',
          description: res.error.message,
          type: 'error',
        });
      } else {
        addToast({
          title: 'Client Invited',
          description: `Invited ${newClientName.trim() || newClientEmail.trim()} as a project client partner.`,
          type: 'success',
        });
        setNewClientEmail('');
        setNewClientName('');
        setNewClientMessage('');
        await loadData();
        if (onProjectUpdated) onProjectUpdated();
      }
    } catch (err: any) {
      addToast({
        title: 'Error',
        description: err?.message || 'Failed to invite client.',
        type: 'error',
      });
    } finally {
      setIsSubmittingClient(false);
    }
  };

  // Load project & members
  const loadData = async () => {
    const id = projectId || propProject?.id;
    if (!id) return;
    setLoading(true);
    try {
      const [projData, membersData] = await Promise.all([
        getProjectById(id),
        getProjectMembers(id),
      ]);
      setProject(projData);

      // If project has no members yet, ensure current user / owner is present
      if (membersData.length === 0) {
        const defaultOwner: ProjectMember = {
          id: `pm-owner-${id}`,
          project_id: id,
          user_id: profile?.id || 'demo-user-owner',
          role: 'PROJECT_OWNER',
          created_at: new Date().toISOString(),
          profile: {
            id: profile?.id || 'demo-user-owner',
            email: profile?.email || 'shivy@ajath.com',
            full_name: profile?.full_name || 'Shiv Narayan',
            avatar_url: profile?.avatar_url || '',
            job_title: profile?.job_title || 'Founder & CEO',
          },
        };
        setMembers([defaultOwner]);
      } else {
        setMembers(membersData);
      }
    } catch (err) {
      console.error('Failed to load project people data:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [projectId]);

  // Set page tab title matching screenshot: People on the project (<projectName>)
  useEffect(() => {
    const projectName = project?.name || propProject?.name || 'project';
    document.title = `People on the project (${projectName})`;
    return () => {
      document.title = 'Ajath PMT';
    };
  }, [project?.name, propProject?.name]);

  // Toggle invite-only vs all-access
  const handleToggleAccessMode = () => {
    const nextMode = accessMode === 'invite-only' ? 'all-access' : 'invite-only';
    setAccessMode(nextMode);
    if (project?.id) {
      try {
        localStorage.setItem(`proj_access_mode_${project.id}`, nextMode);
      } catch (e) {}
    }
    addToast({
      title: nextMode === 'all-access' ? 'Switched to All Access' : 'Switched to Invite-only',
      description:
        nextMode === 'all-access'
          ? 'Everyone in your account can now access this project.'
          : 'Access is now restricted to the invited team members.',
      type: 'info',
    });
  };

  // Toggle notifications for a member
  const handleToggleNotifications = (memberId: string) => {
    const currentState = memberNotifs[memberId] !== false; // defaults to true
    const nextMap = { ...memberNotifs, [memberId]: !currentState };
    setMemberNotifs(nextMap);
    if (project?.id) {
      try {
        localStorage.setItem(`proj_notifs_${project.id}`, JSON.stringify(nextMap));
      } catch (e) {}
    }
    addToast({
      title: !currentState ? 'Notifications turned on' : 'Notifications paused',
      description: !currentState
        ? 'You will receive email & app alerts for activity in this project.'
        : 'Notifications paused for this project.',
      type: 'info',
    });
  };

  // Remove member or remove yourself
  const handleRemove = async (member: ProjectMember, isSelf: boolean) => {
    const isTargetOwner =
      member.user_id === currentOrganization?.owner_id ||
      member.user_id === 'demo-user-owner' ||
      member.profile?.id === 'demo-user-owner' ||
      orgMembers.some(
        (om) => (om.user_id === member.user_id || om.profile?.id === member.user_id) && om.role === 'OWNER'
      );

    if (!isSelf && isTargetOwner) {
      addToast({
        title: 'Action Forbidden',
        description: 'Administrators cannot remove the Account Owner from a project.',
        type: 'error',
      });
      return;
    }

    const confirmMessage = isSelf
      ? `Are you sure you want to remove yourself from "${project?.name}"?`
      : `Remove ${member.profile?.full_name || 'this member'} from this project?`;

    if (!window.confirm(confirmMessage)) return;

    if (project?.id) {
      const res = await removeProjectMember(project.id, member.id, profile?.id, userRole);
      if (res.error) {
        addToast({
          title: 'Action Forbidden',
          description: res.error.message,
          type: 'error',
        });
        return;
      }
      addToast({
        title: isSelf ? 'You have left this project' : 'Member removed',
        description: `${member.profile?.full_name || 'Member'} has been removed from this project.`,
        type: 'success',
      });
      loadData();
      if (onProjectUpdated) onProjectUpdated();
      if (isSelf) {
        navigate('/projects');
      }
    }
  };

  // Available org members not yet on this project
  const availableOrgMembers = useMemo(() => {
    const existingUserIds = new Set(members.map((m) => m.user_id));
    return orgMembers.filter((om) => !existingUserIds.has(om.user_id));
  }, [orgMembers, members]);

  // Filtered members based on right column search query
  const searchResults = useMemo(() => {
    if (!searchQuery.trim()) return [];
    const q = searchQuery.toLowerCase();
    return availableOrgMembers.filter(
      (om) =>
        om.profile?.full_name?.toLowerCase().includes(q) ||
        om.profile?.email?.toLowerCase().includes(q)
    );
  }, [availableOrgMembers, searchQuery]);

  // Add an individual member
  const handleAddUser = async (userId: string, userName: string) => {
    if (!project?.id || isAddingMember) return;
    setIsAddingMember(true);
    try {
      await addProjectMember(project.id, userId, 'PROJECT_MEMBER', profile?.id);
      addToast({
        title: 'Member added',
        description: `${userName} was added to ${project.name}.`,
        type: 'success',
      });
      setSearchQuery('');
      await loadData();
      if (onProjectUpdated) onProjectUpdated();
    } catch (err) {
      console.error(err);
      addToast({
        title: 'Error',
        description: 'Failed to add member to this project.',
        type: 'error',
      });
    } finally {
      setIsAddingMember(false);
    }
  };

  // Add everyone from company
  const handleAddAllFromCompany = async () => {
    if (!project?.id || isAddingMember || availableOrgMembers.length === 0) return;
    setIsAddingMember(true);
    try {
      for (const om of availableOrgMembers) {
        await addProjectMember(project.id, om.user_id, 'PROJECT_MEMBER', profile?.id);
      }
      addToast({
        title: 'All members added',
        description: `Added ${availableOrgMembers.length} team members from ${currentOrganization?.name || 'company'} to this project.`,
        type: 'success',
      });
      await loadData();
      if (onProjectUpdated) onProjectUpdated();
    } catch (err) {
      console.error(err);
    } finally {
      setIsAddingMember(false);
    }
  };

  const companyName = currentOrganization?.name || 'Our Company';
  const projectName = project?.name || propProject?.name || 'hello';

  if (loading && !project) {
    return (
      <div className="w-full max-w-7xl mx-auto space-y-6 pt-4 pb-16">
        <div className="w-full bg-white dark:bg-slate-900 rounded-3xl min-h-[400px] p-12 text-center text-slate-400 font-semibold text-sm animate-pulse border border-slate-200/80 dark:border-slate-800 flex items-center justify-center">
          Loading team members...
        </div>
      </div>
    );
  }

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 py-2 pb-16">
      {/* Modern Team Membership Card */}
      <div className="w-full max-w-5xl mx-auto bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
        
        {/* Top Breadcrumb: Project Name */}
        <div>
          <button
            onClick={() => navigate(`/projects/${project?.id}`)}
            className="text-sm font-bold text-slate-900 dark:text-slate-100 hover:underline cursor-pointer transition-colors"
            title={`Back to ${projectName}`}
          >
            {projectName}
          </button>
          <div className="border-b border-slate-200/80 dark:border-slate-800 my-4 -mx-6 sm:-mx-12" />
        </div>

        {/* Heading */}
        <div>
          <h1 className="text-3xl sm:text-[34px] font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
            Who’s on this project?
          </h1>
        </div>

        {/* Tabs: Team | Clients */}
        <div>
          <div className="flex items-center gap-1 border-b border-slate-200/80 dark:border-slate-700">
            <button
              onClick={() => setActiveTab('team')}
              className={`px-5 py-2.5 text-sm font-semibold transition-all -mb-px rounded-t-lg cursor-pointer ${
                activeTab === 'team'
                  ? 'border border-slate-200/90 dark:border-slate-700 border-b-white dark:border-b-slate-900 bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                  : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
              }`}
            >
              Team
            </button>
            <button
              onClick={() => setActiveTab('clients')}
              className={`px-5 py-2.5 text-sm font-semibold transition-all -mb-px rounded-t-lg cursor-pointer ${
                activeTab === 'clients'
                  ? 'border border-slate-200/90 dark:border-slate-700 border-b-white dark:border-b-slate-900 bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                  : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
              }`}
            >
              Clients
            </button>
          </div>
        </div>

        {activeTab === 'team' ? (
          <>
            {/* Banner: Invite-only vs All-access notice */}
            <div className="bg-[#f8f9fa] dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 rounded-xl p-4 sm:p-5 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
              {accessMode === 'invite-only' ? (
                <p>
                  <span className="font-bold text-slate-900 dark:text-slate-100">
                    This project is Invite-only.
                  </span>{' '}
                  Only the people listed below have access. If you'd like everyone in your Ajath PMT
                  account to be able to access the project,{' '}
                  <button
                    onClick={handleToggleAccessMode}
                    className="text-[#1b75bb] hover:underline font-normal cursor-pointer"
                  >
                    switch to All access
                  </button>{' '}
                  instead.
                </p>
              ) : (
                <p>
                  <span className="font-bold text-slate-900 dark:text-slate-100">
                    This project is All-access.
                  </span>{' '}
                  Everyone in your Ajath PMT account can access and see this project. If you'd prefer
                  to restrict access to only the people listed below,{' '}
                  <button
                    onClick={handleToggleAccessMode}
                    className="text-[#1b75bb] hover:underline font-normal cursor-pointer"
                  >
                    switch to Invite-only
                  </button>{' '}
                  instead.
                </p>
              )}
            </div>

            {/* 2-Column Layout */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-14 pt-2 items-start">
              {/* Left Column: People on this project */}
              <div className="space-y-6">
                {members.map((m) => {
                  const fullName = m.profile?.full_name || 'Shiv Narayan';
                  const initials = getInitials(fullName);
                  const color = getAvatarColor(initials);
                  const isSelf = m.user_id === profile?.id || m.profile?.email === profile?.email;
                  const notifOn = memberNotifs[m.id] !== false;

                  const isTargetOwner =
                    m.user_id === currentOrganization?.owner_id ||
                    m.user_id === 'demo-user-owner' ||
                    m.profile?.id === 'demo-user-owner' ||
                    orgMembers.some(
                      (om) => (om.user_id === m.user_id || om.profile?.id === m.user_id) && om.role === 'OWNER'
                    );

                  return (
                    <div key={m.id} className="flex items-center gap-3.5 group">
                      {/* Avatar Icon */}
                      <div
                        className={`w-11 h-11 rounded-full ${color} text-white font-bold text-sm tracking-tight flex items-center justify-center shrink-0 shadow-xs select-none`}
                        title={fullName}
                      >
                        {initials}
                      </div>

                      {/* Name & Links */}
                      <div className="space-y-0.5">
                        <div className="flex items-center gap-2">
                          <span className="font-bold text-slate-900 dark:text-slate-100 text-[15px] leading-snug">
                            {fullName}
                          </span>
                          {isTargetOwner && (
                            <span className="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60">
                              Account Owner
                            </span>
                          )}
                        </div>
                        <div className="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                          {isSelf ? (
                            <button
                              onClick={() => handleRemove(m, true)}
                              className="hover:text-red-600 hover:underline transition-colors cursor-pointer text-slate-500 dark:text-slate-400"
                            >
                              Remove yourself
                            </button>
                          ) : isTargetOwner ? (
                            <span className="text-slate-400 font-normal italic select-none" title="Account Owner cannot be removed by administrators">
                              Owner cannot be removed
                            </span>
                          ) : (
                            <button
                              onClick={() => handleRemove(m, false)}
                              className="hover:text-red-600 hover:underline transition-colors cursor-pointer text-slate-500 dark:text-slate-400"
                            >
                              Remove from project
                            </button>
                          )}
                          <span className="text-slate-400 select-none">•</span>
                          <button
                            onClick={() => handleToggleNotifications(m.id)}
                            className="hover:text-slate-900 dark:hover:text-slate-200 hover:underline transition-colors cursor-pointer text-slate-500 dark:text-slate-400"
                          >
                            {notifOn ? 'Notifications on' : 'Notifications paused'}
                          </button>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>

              {/* Right Column: Invite more people to this project */}
              <div className="space-y-4">
                <h2 className="text-base font-bold text-slate-900 dark:text-slate-100">
                  Invite more people to this project
                </h2>

                {/* Search / Email Input */}
                <div className="relative">
                  <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    placeholder="Type a name or email address..."
                    className="w-full px-3.5 py-2.5 text-sm border border-slate-300 dark:border-slate-700 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400"
                  />

                  {/* Autocomplete dropdown if query is entered */}
                  {searchQuery.trim().length > 0 && (
                    <div className="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg z-20 overflow-hidden max-h-60 overflow-y-auto">
                      {searchResults.length > 0 ? (
                        searchResults.map((om) => {
                          const name = om.profile?.full_name || om.profile?.email || 'Member';
                          const initials = getInitials(name);
                          return (
                            <div
                              key={om.id}
                              onClick={() => handleAddUser(om.user_id, name)}
                              className="flex items-center justify-between px-3.5 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-750 cursor-pointer transition-colors border-b border-slate-100 dark:border-slate-750 last:border-0"
                            >
                              <div className="flex items-center gap-2.5">
                                <div
                                  className={`w-7 h-7 rounded-full ${getAvatarColor(
                                    initials
                                  )} text-white font-bold text-xs flex items-center justify-center`}
                                >
                                  {initials}
                                </div>
                                <div>
                                  <div className="text-xs font-bold text-slate-900 dark:text-white">
                                    {name}
                                  </div>
                                  <div className="text-[11px] text-slate-500">
                                    {om.profile?.email}
                                  </div>
                                </div>
                              </div>
                              <span className="text-xs font-semibold text-blue-600 hover:underline">
                                Add to project
                              </span>
                            </div>
                          );
                        })
                      ) : (
                        <div className="p-3 text-xs text-slate-500 text-center">
                          No matching team members found. Press enter to invite by email.
                        </div>
                      )}
                    </div>
                  )}
                </div>

                {/* Link: Or pick people from a company... */}
                <div className="pt-1">
                  <button
                    onClick={() => setIsCompanyDropdownOpen(!isCompanyDropdownOpen)}
                    className="text-[#1b75bb] hover:underline cursor-pointer text-sm font-normal block"
                  >
                    Or pick people from a company...
                  </button>

                  {/* Company Dropdown / List matching screenshot */}
                  {isCompanyDropdownOpen && (
                    <div className="mt-2 border border-slate-300 dark:border-slate-700 rounded-md overflow-hidden bg-white dark:bg-slate-800 shadow-2xs text-sm">
                      <div className="px-3.5 py-2 text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-850 border-b border-slate-200 dark:border-slate-700 select-none">
                        Or pick people from a company...
                      </div>

                      {/* Highlighted active company option */}
                      <div className="bg-[#666666] text-white px-3.5 py-2 font-medium flex items-center justify-between cursor-pointer select-none">
                        <span>{companyName}</span>
                        {availableOrgMembers.length > 0 && (
                          <span className="text-xs bg-white/20 px-2 py-0.5 rounded-full">
                            {availableOrgMembers.length} available
                          </span>
                        )}
                      </div>

                      {/* Sub-list of available colleagues to pick from */}
                      <div className="p-3 bg-white dark:bg-slate-850 space-y-2 border-t border-slate-200 dark:border-slate-750">
                        {availableOrgMembers.length > 0 ? (
                          <>
                            <div className="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                              <span className="text-xs text-slate-500">
                                Click to add colleagues to this project:
                              </span>
                              <button
                                onClick={handleAddAllFromCompany}
                                disabled={isAddingMember}
                                className="text-xs font-semibold text-blue-600 hover:underline cursor-pointer disabled:opacity-50"
                              >
                                Add everyone
                              </button>
                            </div>

                            <div className="max-h-48 overflow-y-auto space-y-1.5 pr-1">
                              {availableOrgMembers.map((om) => {
                                const name = om.profile?.full_name || om.profile?.email || 'Member';
                                const initials = getInitials(name);
                                return (
                                  <div
                                    key={om.id}
                                    onClick={() => handleAddUser(om.user_id, name)}
                                    className="flex items-center justify-between p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition-colors group"
                                  >
                                    <div className="flex items-center gap-2">
                                      <div
                                        className={`w-6 h-6 rounded-full ${getAvatarColor(
                                          initials
                                        )} text-white font-bold text-[10px] flex items-center justify-center shrink-0`}
                                      >
                                        {initials}
                                      </div>
                                      <span className="text-xs font-medium text-slate-800 dark:text-slate-200 group-hover:text-blue-600">
                                        {name}
                                      </span>
                                    </div>
                                    <span className="text-xs text-blue-600 font-semibold opacity-0 group-hover:opacity-100 transition-opacity">
                                      + Add
                                    </span>
                                  </div>
                                );
                              })}
                            </div>
                          </>
                        ) : (
                          <div className="text-xs text-slate-500 py-1 text-center">
                            Everyone in {companyName} is already on this project.
                          </div>
                        )}
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </>
        ) : (
          /* Clients Tab View */
          <div className="space-y-6 animate-in fade-in duration-200">
            {!clientAccessEnabled && clientMembers.length === 0 ? (
              <div className="py-12 text-center max-w-lg mx-auto space-y-4">
                <div className="w-16 h-16 rounded-3xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto border border-blue-200/80 dark:border-blue-800/60 shadow-xs">
                  <Briefcase className="w-8 h-8" />
                </div>
                <h2 className="text-2xl font-black text-slate-900 dark:text-white">
                  Clients on this project
                </h2>
                <p className="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                  Keep your clients in the loop without showing them everything. When client access is
                  turned on, you can share specific to-dos, messages, and files while keeping internal
                  discussions and Campfire chat private.
                </p>
                <div className="pt-2">
                  <button
                    onClick={() => {
                      setClientAccessEnabled(true);
                      addToast({
                        title: 'Client access enabled',
                        description: 'You can now invite client partners to collaborate on this project.',
                        type: 'info',
                      });
                    }}
                    className="px-5 py-2.5 text-xs font-bold rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-md hover:shadow-lg transition-all cursor-pointer"
                  >
                    Turn on client access for this project
                  </button>
                </div>
              </div>
            ) : (
              <div className="space-y-6">
                {/* Client Isolation & Boundary Notice Banner */}
                <div className="flex items-start gap-3 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60">
                  <Lock className="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                  <div className="space-y-1 text-xs text-amber-900 dark:text-amber-200">
                    <p className="font-bold">Client Privacy & Tenant Boundary Enforced</p>
                    <p className="text-amber-700 dark:text-amber-300 leading-relaxed text-[11px]">
                      Client stakeholders only see to-dos, milestones, and announcements explicitly marked as client-visible in this project.
                      Clients are strictly blocked from internal team Campfire chat, private discussions, other teams, and unrelated customer projects.
                    </p>
                  </div>
                </div>

                {/* 2-Column Grid: Left (Current Clients) | Right (Invite Client Form) */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 pt-2">
                  {/* Left Column: Active Clients on Project */}
                  <div className="space-y-4">
                    <div className="flex items-center justify-between pb-1 border-b border-slate-200/80 dark:border-slate-800">
                      <h3 className="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <Briefcase className="w-4 h-4 text-blue-600" />
                        <span>Client Stakeholders ({clientMembers.length})</span>
                      </h3>
                      <span className="text-[11px] font-semibold text-slate-500">
                        {companyName} External Partners
                      </span>
                    </div>

                    {clientMembers.length === 0 ? (
                      <div className="p-8 text-center rounded-2xl bg-slate-50 dark:bg-slate-850/60 border border-dashed border-slate-200 dark:border-slate-800 text-slate-400 text-xs">
                        No client partners have been added to this project yet. Use the invite form on the right to send an invitation.
                      </div>
                    ) : (
                      <div className="space-y-2.5">
                        {clientMembers.map((cm) => {
                          const cProfile = cm.profile || { full_name: 'Client Partner', email: '' };
                          const initials = getInitials(cProfile.full_name);
                          return (
                            <div
                              key={cm.id}
                              className="flex items-center justify-between p-3 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200/90 dark:border-slate-800 shadow-2xs hover:shadow-xs transition-all"
                            >
                              <div className="flex items-center gap-3 min-w-0">
                                <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                                  {initials}
                                </div>
                                <div className="min-w-0">
                                  <p className="text-xs font-bold text-slate-900 dark:text-white truncate">
                                    {cProfile.full_name}
                                  </p>
                                  <p className="text-[11px] text-slate-500 truncate">
                                    {cProfile.email || 'Client Stakeholder'}
                                  </p>
                                </div>
                              </div>

                              <div className="flex items-center gap-2">
                                <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/80">
                                  Client Partner
                                </span>
                                <button
                                  type="button"
                                  onClick={() => handleRemove(cm, false)}
                                  className="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors"
                                  title="Remove client from project"
                                >
                                  <X className="w-4 h-4" />
                                </button>
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    )}
                  </div>

                  {/* Right Column: Invite Client Form */}
                  <div className="space-y-4">
                    <div className="pb-1 border-b border-slate-200/80 dark:border-slate-800">
                      <h3 className="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <Mail className="w-4 h-4 text-indigo-600" />
                        <span>Invite a Client Partner</span>
                      </h3>
                      <p className="text-[11px] text-slate-500">
                        Send an email invite directly into this project workspace.
                      </p>
                    </div>

                    <form onSubmit={handleInviteClient} className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-850/80 border border-slate-200/80 dark:border-slate-800 space-y-3.5">
                      <div>
                        <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                          Client Email Address <span className="text-rose-500">*</span>
                        </label>
                        <input
                          type="email"
                          required
                          placeholder="client@partnercompany.com"
                          value={newClientEmail}
                          onChange={(e) => setNewClientEmail(e.target.value)}
                          className="w-full px-3.5 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                      </div>

                      <div>
                        <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                          Client Full Name (Optional)
                        </label>
                        <input
                          type="text"
                          placeholder="e.g. Edward Watson"
                          value={newClientName}
                          onChange={(e) => setNewClientName(e.target.value)}
                          className="w-full px-3.5 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                      </div>

                      <div>
                        <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                          Personal Message / Note (Optional)
                        </label>
                        <textarea
                          rows={2}
                          placeholder="Hey, we are inviting you to view progress updates and deliverables on this project..."
                          value={newClientMessage}
                          onChange={(e) => setNewClientMessage(e.target.value)}
                          className="w-full px-3.5 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                      </div>

                      <button
                        type="submit"
                        disabled={isSubmittingClient || !newClientEmail.trim()}
                        className="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-xs hover:shadow-md transition-all disabled:opacity-50 cursor-pointer"
                      >
                        <Send className="w-3.5 h-3.5" />
                        <span>{isSubmittingClient ? 'Sending Client Invitation...' : 'Send Client Invitation'}</span>
                      </button>
                    </form>

                    <div className="pt-2 text-center">
                      <button
                        type="button"
                        onClick={() => {
                          setClientAccessEnabled(false);
                          addToast({
                            title: 'Client access paused',
                            description: 'Client access has been turned off for this project.',
                            type: 'info',
                          });
                        }}
                        className="text-[11px] text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 font-medium cursor-pointer"
                      >
                        Turn off client access for this project
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
