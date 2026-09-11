import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import {
  Check,
  Copy,
  Send,
  Link2,
  ArrowLeft,
  User,
  Mail,
  Briefcase,
  Users,
  ShieldCheck,
  Sparkles,
  ExternalLink,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useProject } from '../../context/ProjectContext';
import { useToast } from '../../context/ToastContext';
import { createInvitation } from '../../services/organizationService';
import { addProjectMember } from '../../services/projectService';
import { OrgRole, ProjectMemberRole } from '../../types';

type AudienceType = 'colleague' | 'contractor' | 'client';

export function InvitePeoplePage() {
  const navigate = useNavigate();
  const { orgId } = useParams<{ orgId?: string }>();
  const { profile, userRole } = useAuth();
  const { currentOrganization, refreshOrganizationData } = useOrganization();
  const { projects } = useProject();
  const { addToast } = useToast();

  // Set browser title
  useEffect(() => {
    document.title = 'Invite someone to your account... Ajath PMT';
    return () => {
      document.title = 'Ajath PMT Workspace';
    };
  }, []);

  // Flow step: 1 = "Who are you inviting?", 2 = "Enter their name...", 3 = Success
  const [step, setStep] = useState<1 | 2 | 3>(1);

  // Basecamp Gate: Clients cannot invite users
  if (userRole === 'CLIENT') {
    return (
      <div className="max-w-xl mx-auto py-20 text-center space-y-4">
        <div className="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto border border-amber-200 dark:border-amber-800">
          <Users className="w-7 h-7" />
        </div>
        <h2 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100">Access Restricted</h2>
        <p className="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
          Client partners do not have permission to invite new people to the workspace.
        </p>
        <button
          onClick={() => navigate('/dashboard')}
          className="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs cursor-pointer shadow-xs transition-colors"
        >
          Return to Dashboard
        </button>
      </div>
    );
  }

  // Audience type selected in step 1
  const [audienceType, setAudienceType] = useState<AudienceType>('colleague');

  // Step 2 Form fields
  const [fullName, setFullName] = useState('');
  const [email, setEmail] = useState('');
  const [jobTitle, setJobTitle] = useState('');
  const [isAdmin, setIsAdmin] = useState(false);
  const [selectedProjectIds, setSelectedProjectIds] = useState<Set<string>>(
    () => new Set(['proj-rmc'])
  );
  const [personalMessage, setPersonalMessage] = useState(
    'Welcome to our workspace! We look forward to collaborating together on our active projects.'
  );

  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [generatedInviteLink, setGeneratedInviteLink] = useState<string | null>(null);
  const [isCopied, setIsCopied] = useState(false);

  const orgName = currentOrganization?.name || 'Our Company';

  const toggleProject = (projectId: string) => {
    setSelectedProjectIds((prev) => {
      const next = new Set(prev);
      if (next.has(projectId)) {
        next.delete(projectId);
      } else {
        next.add(projectId);
      }
      return next;
    });
  };

  const handleSelectAllProjects = () => {
    setSelectedProjectIds(new Set(projects.map((p) => p.id)));
  };

  const handleClearProjects = () => {
    setSelectedProjectIds(new Set());
  };

  const handleSendInvite = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email.trim()) {
      setError('Please provide a valid recipient email address.');
      return;
    }

    if (!currentOrganization || !profile) {
      setError('Missing organization context. Please refresh and try again.');
      return;
    }

    setError(null);
    setIsLoading(true);

    let role: OrgRole = 'MEMBER';
    let projectMemberRole: ProjectMemberRole = 'PROJECT_MEMBER';

    if (audienceType === 'colleague') {
      const canMakeAdmin = isAdmin && ['OWNER', 'ADMIN'].includes(userRole);
      role = canMakeAdmin ? 'ADMIN' : 'MEMBER';
      projectMemberRole = canMakeAdmin ? 'PROJECT_MANAGER' : 'PROJECT_MEMBER';
    } else if (audienceType === 'client') {
      role = 'CLIENT';
      projectMemberRole = 'PROJECT_CLIENT';
    } else if (audienceType === 'contractor') {
      role = 'MEMBER';
      projectMemberRole = 'PROJECT_MEMBER';
    }

    const note = fullName
      ? `Invited: ${fullName}${jobTitle ? ` (${jobTitle})` : ''}. ${personalMessage}`
      : personalMessage;

    const firstProjId = selectedProjectIds.size > 0 ? Array.from(selectedProjectIds)[0] : undefined;
    const res = await createInvitation(
      currentOrganization.id,
      email.trim(),
      role,
      profile.id,
      note,
      userRole,
      {
        projectId: firstProjId,
        projectRole: projectMemberRole,
      }
    );

    if (res.error) {
      setIsLoading(false);
      setError(res.error.message || 'Failed to send organization invitation.');
      return;
    }

    // Add member to selected projects
    const tempUserId = `user-inv-${Date.now()}`;
    for (const pId of Array.from(selectedProjectIds)) {
      try {
        await addProjectMember(pId, tempUserId, projectMemberRole, profile.id);
      } catch (err) {
        console.warn('Failed to assign project member:', err);
      }
    }

    setIsLoading(false);
    if (res.data) {
      const inviteUrl = `${window.location.origin}/invite/${res.data.token}`;
      setGeneratedInviteLink(inviteUrl);
      refreshOrganizationData();
      addToast('Invitation sent successfully!', 'success');
      setStep(3);
    }
  };

  const handleGenerateInstantLink = async () => {
    if (!email.trim()) {
      setEmail('collaborator@example.com');
    }
    if (!currentOrganization || !profile) return;

    setIsLoading(true);
    const mockEmail = email.trim() || `invite-${Date.now()}@example.com`;
    const res = await createInvitation(
      currentOrganization.id,
      mockEmail,
      audienceType === 'client' ? 'CLIENT' : 'MEMBER',
      profile.id,
      'One-click direct enrollment',
      userRole
    );

    setIsLoading(false);
    if (res.data) {
      const inviteUrl = `${window.location.origin}/invite/${res.data.token}`;
      setGeneratedInviteLink(inviteUrl);
      refreshOrganizationData();
      addToast('Direct invite link generated', 'info');
      setStep(3);
    }
  };

  const handleCopyLink = () => {
    if (!generatedInviteLink) return;
    navigator.clipboard.writeText(generatedInviteLink);
    setIsCopied(true);
    addToast('Invite link copied to clipboard', 'info');
    setTimeout(() => setIsCopied(false), 3000);
  };

  const handleResetForAnother = () => {
    setFullName('');
    setEmail('');
    setJobTitle('');
    setIsAdmin(false);
    setGeneratedInviteLink(null);
    setError(null);
    setStep(1);
  };

  return (
    <div className="w-full flex justify-center py-6 sm:py-12 px-3 sm:px-6">
      {/* Centered White Basecamp Card matching screenshot */}
      <div className="w-full max-w-[620px] bg-white dark:bg-slate-900 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.04)] border border-slate-200/70 dark:border-slate-800 p-8 sm:p-12 md:p-14 animate-in fade-in duration-200">
        
        {/* ================= STEP 1: "Who are you inviting?" ================= */}
        {step === 1 && (
          <div className="space-y-8">
            {/* Title & Subtitle matching screenshot */}
            <div className="space-y-2">
              <h1 className="text-[32px] sm:text-[34px] font-extrabold text-slate-900 dark:text-slate-50 tracking-tight leading-tight">
                Who are you inviting?
              </h1>
              <p className="text-[15px] text-slate-600 dark:text-slate-400 font-normal leading-normal">
                First you’ll invite them to the account. Then you can add them to projects.
              </p>
            </div>

            {/* 3 Signature Basecamp Radio Choices */}
            <div className="space-y-6 pt-2">
              {/* Radio Option 1: Company Employee */}
              <label
                onClick={() => setAudienceType('colleague')}
                className="flex items-start gap-3.5 cursor-pointer group select-none"
              >
                {/* Custom Basecamp Radio Button */}
                <div className="mt-1 shrink-0">
                  <div
                    className={`w-5 h-5 rounded-full flex items-center justify-center transition-all ${
                      audienceType === 'colleague'
                        ? 'border-[2.5px] border-slate-900 dark:border-white bg-white dark:bg-slate-900'
                        : 'border-[1.5px] border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 group-hover:border-slate-400'
                    }`}
                  >
                    {audienceType === 'colleague' && (
                      <div className="w-2.5 h-2.5 rounded-full bg-slate-900 dark:bg-white" />
                    )}
                  </div>
                </div>

                <div>
                  <div className="font-bold text-[16px] text-slate-900 dark:text-slate-100 leading-snug">
                    Someone who works at {orgName}
                  </div>
                  <div className="text-[14px] text-slate-600 dark:text-slate-400 leading-relaxed mt-1 font-normal">
                    Full-time, part-time, or a volunteer at {orgName}? They can create projects, add people to projects, and become administrators.
                  </div>
                </div>
              </label>

              {/* Radio Option 2: Outside Collaborator */}
              <label
                onClick={() => setAudienceType('contractor')}
                className="flex items-start gap-3.5 cursor-pointer group select-none"
              >
                {/* Custom Basecamp Radio Button */}
                <div className="mt-1 shrink-0">
                  <div
                    className={`w-5 h-5 rounded-full flex items-center justify-center transition-all ${
                      audienceType === 'contractor'
                        ? 'border-[2.5px] border-slate-900 dark:border-white bg-white dark:bg-slate-900'
                        : 'border-[1.5px] border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 group-hover:border-slate-400'
                    }`}
                  >
                    {audienceType === 'contractor' && (
                      <div className="w-2.5 h-2.5 rounded-full bg-slate-900 dark:bg-white" />
                    )}
                  </div>
                </div>

                <div>
                  <div className="font-bold text-[16px] text-slate-900 dark:text-slate-100 leading-snug">
                    An outside collaborator, partner, contractor, guest, etc.
                  </div>
                  <div className="text-[14px] text-slate-600 dark:text-slate-400 leading-relaxed mt-1 font-normal">
                    They don’t work at {orgName} but can collaborate on projects with you.
                  </div>
                </div>
              </label>

              {/* Radio Option 3: Client */}
              <label
                onClick={() => setAudienceType('client')}
                className="flex items-start gap-3.5 cursor-pointer group select-none"
              >
                {/* Custom Basecamp Radio Button */}
                <div className="mt-1 shrink-0">
                  <div
                    className={`w-5 h-5 rounded-full flex items-center justify-center transition-all ${
                      audienceType === 'client'
                        ? 'border-[2.5px] border-slate-900 dark:border-white bg-white dark:bg-slate-900'
                        : 'border-[1.5px] border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 group-hover:border-slate-400'
                    }`}
                  >
                    {audienceType === 'client' && (
                      <div className="w-2.5 h-2.5 rounded-full bg-slate-900 dark:bg-white" />
                    )}
                  </div>
                </div>

                <div>
                  <div className="font-bold text-[16px] text-slate-900 dark:text-slate-100 leading-snug">
                    A client you’re doing work for
                  </div>
                  <div className="text-[14px] text-slate-600 dark:text-slate-400 leading-relaxed mt-1 font-normal">
                    Clients can access projects you’ve created, but <strong className="font-bold text-slate-900 dark:text-slate-100">they can’t</strong> create their own, invite or add new people, or become admins. You can hide parts of projects from them so they can’t see work in progress.
                  </div>
                </div>
              </label>
            </div>

            {/* Blue Action Button matching screenshot */}
            <div className="pt-4">
              <button
                type="button"
                onClick={() => {
                  if (audienceType === 'colleague') {
                    navigate(orgId ? `/${orgId}/account/enrollments/employees/new` : '/account/enrollments/employees/new');
                  } else if (audienceType === 'contractor') {
                    navigate(orgId ? `/${orgId}/account/enrollments/vendors/new` : '/account/enrollments/vendors/new');
                  } else if (audienceType === 'client') {
                    navigate(orgId ? `/${orgId}/account/enrollments/clients/new` : '/account/enrollments/clients/new');
                  }
                }}
                className="inline-flex items-center justify-center px-6 py-2.5 sm:py-3 text-[14px] font-bold text-white bg-[#0c66e4] hover:bg-[#0055cc] active:bg-[#0047b3] rounded-xl shadow-xs transition-colors cursor-pointer"
              >
                Next, enter their name...
              </button>
            </div>
          </div>
        )}

        {/* ================= STEP 2: "Enter their name and details" ================= */}
        {step === 2 && (
          <form onSubmit={handleSendInvite} className="space-y-6">
            {/* Back link to Step 1 */}
            <div>
              <button
                type="button"
                onClick={() => setStep(1)}
                className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white cursor-pointer transition-colors"
              >
                <ArrowLeft className="w-3.5 h-3.5" />
                <span>‹ Who are you inviting?</span>
              </button>
            </div>

            {/* Dynamic Step 2 Title */}
            <div className="space-y-1 pb-1">
              <h2 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                {audienceType === 'colleague' && `Add someone who works at ${orgName}`}
                {audienceType === 'contractor' && 'Add an outside collaborator, partner, contractor, etc.'}
                {audienceType === 'client' && 'Add a client you’re doing work for'}
              </h2>
              <p className="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                {audienceType === 'colleague' && 'They’ll get an invitation to join your workspace on Ajath PMT.'}
                {audienceType === 'contractor' && 'They will only have access to the specific projects you choose.'}
                {audienceType === 'client' && 'Clients can follow discussions, milestones, and deliverables you share.'}
              </p>
            </div>

            {error && (
              <div className="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-600 animate-in fade-in">
                {error}
              </div>
            )}

            {/* Name & Email Fields */}
            <div className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Name
                </label>
                <div className="relative">
                  <User className="absolute left-3.5 top-2.5 w-4 h-4 text-slate-400" />
                  <input
                    type="text"
                    placeholder="e.g. Edward Smith"
                    value={fullName}
                    onChange={(e) => setFullName(e.target.value)}
                    className="w-full pl-10 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/30 text-slate-900 dark:text-slate-100 font-medium"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Email address <span className="text-rose-500">*</span>
                </label>
                <div className="relative">
                  <Mail className="absolute left-3.5 top-2.5 w-4 h-4 text-slate-400" />
                  <input
                    type="email"
                    required
                    placeholder="e.g. edward@ridemycars.com"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full pl-10 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/30 text-slate-900 dark:text-slate-100 font-medium"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Job title (optional)
                </label>
                <input
                  type="text"
                  placeholder="e.g. Lead Designer"
                  value={jobTitle}
                  onChange={(e) => setJobTitle(e.target.value)}
                  className="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/30 text-slate-900 dark:text-slate-100 font-medium"
                />
              </div>
            </div>

            {/* Administrator Privileges for Employees - Only Org Owners & Admins can assign Admin */}
            {audienceType === 'colleague' && ['OWNER', 'ADMIN'].includes(userRole) && (
              <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700">
                <label className="flex items-start gap-3 cursor-pointer select-none">
                  <input
                    type="checkbox"
                    checked={isAdmin}
                    onChange={(e) => setIsAdmin(e.target.checked)}
                    className="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4"
                  />
                  <div>
                    <span className="block text-xs font-bold text-slate-900 dark:text-slate-100">
                      Can they also be an administrator?
                    </span>
                    <span className="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-normal">
                      Yes, make this person an administrator. Administrators can invite new people, delete projects, and change account settings.
                    </span>
                  </div>
                </label>
              </div>
            )}

            {/* Project Assignment Checklist */}
            <div className="space-y-2 pt-1">
              <div className="flex items-center justify-between">
                <div>
                  <label className="block text-xs font-black text-slate-900 dark:text-slate-100">
                    Which projects should they have access to right away?
                  </label>
                  <p className="text-[11px] text-slate-500 dark:text-slate-400">
                    They will automatically be added to these projects upon accepting.
                  </p>
                </div>
                <div className="flex items-center gap-2 text-[11px] font-bold">
                  <button
                    type="button"
                    onClick={handleSelectAllProjects}
                    className="text-blue-600 hover:underline cursor-pointer"
                  >
                    Select all
                  </button>
                  <span className="text-slate-300">•</span>
                  <button
                    type="button"
                    onClick={handleClearProjects}
                    className="text-slate-500 hover:underline cursor-pointer"
                  >
                    Clear
                  </button>
                </div>
              </div>

              <div className="max-h-44 overflow-y-auto space-y-1.5 p-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40">
                {projects.map((p) => {
                  const isChecked = selectedProjectIds.has(p.id);
                  return (
                    <div
                      key={p.id}
                      onClick={() => toggleProject(p.id)}
                      className={`p-2.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between select-none ${
                        isChecked
                          ? 'border-blue-500 bg-blue-50/70 dark:bg-blue-950/40'
                          : 'border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50'
                      }`}
                    >
                      <div className="min-w-0 pr-3">
                        <p className="font-bold text-xs text-slate-900 dark:text-slate-100 truncate">
                          {p.name}
                        </p>
                        {p.description && (
                          <p className="text-[10px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                            {p.description}
                          </p>
                        )}
                      </div>
                      <div
                        className={`w-4 h-4 rounded flex items-center justify-center transition-colors shrink-0 ${
                          isChecked
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'border border-slate-300 dark:border-slate-600'
                        }`}
                      >
                        {isChecked && <Check className="w-3 h-3 stroke-[3]" />}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>

            {/* Personal Welcome Note */}
            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Personal welcome note (optional)
              </label>
              <textarea
                rows={2}
                value={personalMessage}
                onChange={(e) => setPersonalMessage(e.target.value)}
                placeholder="Write a warm welcome note..."
                className="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/30 text-slate-900 dark:text-slate-100"
              />
            </div>

            {/* Action Buttons */}
            <div className="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
              <button
                type="button"
                onClick={() => setStep(1)}
                className="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl cursor-pointer"
              >
                ‹ Back
              </button>

              <div className="flex items-center gap-2 w-full sm:w-auto justify-end">
                <button
                  type="button"
                  onClick={handleGenerateInstantLink}
                  disabled={isLoading}
                  className="px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer transition-colors"
                >
                  Get shareable link
                </button>

                <button
                  type="submit"
                  disabled={isLoading || !email.trim()}
                  className="px-6 py-2.5 text-xs font-bold text-white bg-[#0c66e4] hover:bg-[#0055cc] disabled:opacity-50 rounded-xl flex items-center gap-2 shadow-xs cursor-pointer transition-all"
                >
                  <Send className="w-3.5 h-3.5" />
                  <span>{isLoading ? 'Creating invitation...' : 'Send the invitation'}</span>
                </button>
              </div>
            </div>
          </form>
        )}

        {/* ================= STEP 3: "Invitation Created!" ================= */}
        {step === 3 && (
          <div className="space-y-6 py-2 animate-in fade-in duration-200">
            <div className="text-center space-y-2">
              <div className="w-16 h-16 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-3xl flex items-center justify-center mx-auto shadow-sm border border-emerald-200 dark:border-emerald-800">
                <Check className="w-8 h-8 stroke-[3]" />
              </div>
              <h2 className="text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                Invitation Created!
              </h2>
              <p className="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                An invitation was registered for <span className="font-bold text-slate-800 dark:text-slate-200">{email || 'your new team member'}</span>.
                They have been granted access to {selectedProjectIds.size}{' '}
                {selectedProjectIds.size === 1 ? 'project' : 'projects'}.
              </p>
            </div>

            {generatedInviteLink && (
              <div className="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                  <Link2 className="w-4 h-4 text-blue-600" /> Shareable One-Click Invite Link
                </label>
                <div className="flex gap-2">
                  <input
                    type="text"
                    readOnly
                    value={generatedInviteLink}
                    className="flex-1 px-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-mono select-all focus:outline-none"
                  />
                  <button
                    type="button"
                    onClick={handleCopyLink}
                    className={`px-5 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer shadow-xs ${
                      isCopied
                        ? 'bg-emerald-600 text-white'
                        : 'bg-[#0c66e4] hover:bg-[#0055cc] text-white'
                    }`}
                  >
                    {isCopied ? <Check className="w-4 h-4" /> : <Copy className="w-4 h-4" />}
                    <span>{isCopied ? 'Copied!' : 'Copy Link'}</span>
                  </button>
                </div>
                <p className="text-[11px] text-slate-400">
                  Send this link via Slack, Teams, WhatsApp, or an email. The recipient can open it to join immediately.
                </p>
              </div>
            )}

            <div className="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
              <button
                type="button"
                onClick={handleResetForAnother}
                className="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer"
              >
                + Invite another person
              </button>
              <button
                type="button"
                onClick={() => navigate('/dashboard')}
                className="px-5 py-2 text-xs font-bold text-white bg-[#0c66e4] hover:bg-[#0055cc] rounded-xl cursor-pointer"
              >
                Back to Dashboard
              </button>
            </div>
          </div>
        )}

      </div>
    </div>
  );
}
