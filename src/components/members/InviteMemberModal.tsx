import React, { useState } from 'react';
import {
  Users,
  Briefcase,
  ShieldCheck,
  Mail,
  User,
  Check,
  Copy,
  Send,
  Link2,
  Folder,
  ArrowRight,
  Sparkles,
} from 'lucide-react';
import { Modal } from '../common/Modal';
import { Button } from '../common/Button';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useProject } from '../../context/ProjectContext';
import { createInvitation } from '../../services/organizationService';
import { addProjectMember } from '../../services/projectService';
import { OrgRole, ProjectMemberRole } from '../../types';

interface InviteMemberModalProps {
  isOpen: boolean;
  onClose: () => void;
  defaultProjectId?: string;
}

type AudienceType = 'colleague' | 'client' | 'contractor';

export function InviteMemberModal({ isOpen, onClose, defaultProjectId }: InviteMemberModalProps) {
  const { profile } = useAuth();
  const { currentOrganization, refreshOrganizationData } = useOrganization();
  const { projects } = useProject();

  const [audienceType, setAudienceType] = useState<AudienceType>('colleague');
  const [fullName, setFullName] = useState('');
  const [email, setEmail] = useState('');
  const [jobTitle, setJobTitle] = useState('');
  const [isAdmin, setIsAdmin] = useState(false);
  const [selectedProjectIds, setSelectedProjectIds] = useState<Set<string>>(() => {
    return defaultProjectId ? new Set([defaultProjectId]) : new Set(['proj-rmc']);
  });
  const [personalMessage, setPersonalMessage] = useState(
    'Welcome to our workspace! We are collaborating here on our active projects.'
  );

  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [generatedInviteLink, setGeneratedInviteLink] = useState<string | null>(null);
  const [invitedCount, setInvitedCount] = useState<number>(1);
  const [isCopied, setIsCopied] = useState(false);

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
    if (!email.trim() || !currentOrganization || !profile) {
      setError('Please provide at least one valid email address.');
      return;
    }

    const emailList = email
      .split(/[\n,;\s]+/)
      .map((em) => em.trim().toLowerCase())
      .filter((em) => em.length > 0 && em.includes('@'));

    if (emailList.length === 0) {
      setError('Please provide at least one valid email address.');
      return;
    }

    setError(null);
    setIsLoading(true);

    let role: OrgRole = 'MEMBER';
    let projectMemberRole: ProjectMemberRole = 'PROJECT_MEMBER';

    if (audienceType === 'colleague') {
      role = isAdmin ? 'ADMIN' : 'MEMBER';
      projectMemberRole = isAdmin ? 'PROJECT_MANAGER' : 'PROJECT_MEMBER';
    } else if (audienceType === 'client') {
      role = 'CLIENT';
      projectMemberRole = 'PROJECT_CLIENT';
    } else if (audienceType === 'contractor') {
      role = 'MEMBER';
      projectMemberRole = 'PROJECT_MEMBER';
    }

    let lastToken: string | null = null;
    let successfulCount = 0;

    for (const singleEmail of emailList) {
      const note = fullName
        ? `Invited: ${fullName}${jobTitle ? ` (${jobTitle})` : ''}. ${personalMessage}`
        : personalMessage;

      const res = await createInvitation(currentOrganization.id, singleEmail, role, profile.id, note);

      if (!res.error && res.data) {
        lastToken = res.data.token;
        successfulCount++;

        // Add member to selected projects
        const tempUserId = `user-inv-${Date.now()}-${Math.random().toString(36).substring(2, 5)}`;
        for (const pId of Array.from(selectedProjectIds)) {
          try {
            await addProjectMember(pId, tempUserId, projectMemberRole, profile.id);
          } catch (err) {
            console.warn('Failed to assign project member:', err);
          }
        }
      }
    }

    setIsLoading(false);
    if (successfulCount > 0 && lastToken) {
      setInvitedCount(successfulCount);
      const inviteUrl = `${window.location.origin}/invite/${lastToken}`;
      setGeneratedInviteLink(inviteUrl);
      refreshOrganizationData();
    } else {
      setError('Failed to send invitations. Please ensure email addresses are valid and not already invited.');
    }
  };

  const handleCopyLink = () => {
    if (!generatedInviteLink) return;
    navigator.clipboard.writeText(generatedInviteLink);
    setIsCopied(true);
    setTimeout(() => setIsCopied(false), 3000);
  };

  const handleReset = () => {
    setFullName('');
    setEmail('');
    setJobTitle('');
    setIsAdmin(false);
    setAudienceType('colleague');
    setSelectedProjectIds(new Set(['proj-rmc']));
    setGeneratedInviteLink(null);
    setError(null);
    onClose();
  };

  return (
    <Modal isOpen={isOpen} onClose={handleReset} title="" maxWidth="lg">
      {generatedInviteLink ? (
        <div className="space-y-6 py-2 animate-in fade-in duration-200">
          <div className="text-center space-y-2">
            <div className="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto shadow-sm border border-emerald-200">
              <Check className="w-7 h-7 stroke-[3]" />
            </div>
            <h2 className="text-2xl font-black text-slate-900 tracking-tight">
              Invitations Ready!
            </h2>
            <p className="text-xs text-slate-500 max-w-sm mx-auto">
              We&apos;ve registered the invitation for <span className="font-bold text-slate-800">{invitedCount} {invitedCount === 1 ? 'person' : 'people'}</span> as{' '}
              <span className="font-bold text-indigo-600">
                {audienceType === 'colleague' ? (isAdmin ? 'Administrator' : 'Team Member') : 'Client Partner'}
              </span>. They have been granted access to {selectedProjectIds.size}{' '}
              {selectedProjectIds.size === 1 ? 'project' : 'projects'}.
            </p>
          </div>

          <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
            <label className="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
              <Link2 className="w-4 h-4 text-blue-600" /> Shareable One-Click Invite Link
            </label>
            <div className="flex gap-2">
              <input
                type="text"
                readOnly
                value={generatedInviteLink}
                className="flex-1 px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white text-slate-800 font-mono select-all focus:outline-none"
              />
              <button
                type="button"
                onClick={handleCopyLink}
                className={`px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer shadow-xs ${
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
              Send this link in Slack, WhatsApp, or an email. They will join instantly.
            </p>
          </div>

          <div className="flex items-center justify-between pt-3 border-t border-slate-100">
            <button
              type="button"
              onClick={() => {
                setGeneratedInviteLink(null);
                setEmail('');
                setFullName('');
              }}
              className="text-xs font-bold text-blue-600 hover:text-blue-700 cursor-pointer"
            >
              + Invite more people
            </button>
            <Button variant="primary" onClick={handleReset}>
              Done
            </Button>
          </div>
        </div>
      ) : (
        <form onSubmit={handleSendInvite} className="space-y-5 pt-1">
          {/* Header */}
          <div className="text-center space-y-1 pb-2 border-b border-slate-100">
            <h2 className="text-2xl font-black text-slate-900 tracking-tight">
              Add people to {currentOrganization?.name || 'Workspace'}
            </h2>
            <p className="text-xs text-slate-500">
              Everyone gets their own login. You choose what each person can see and do.
            </p>
          </div>

          {error && (
            <div className="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-600 animate-in fade-in">
              {error}
            </div>
          )}

          {/* 3 Explicit Role Selection Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-2">
            <button
              type="button"
              onClick={() => {
                setAudienceType('colleague');
                setIsAdmin(false);
              }}
              className={`p-3 rounded-2xl border text-left transition-all cursor-pointer select-none flex flex-col justify-between ${
                audienceType === 'colleague' && !isAdmin
                  ? 'border-blue-600 bg-blue-50/70 shadow-xs ring-1 ring-blue-600'
                  : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'
              }`}
            >
              <div className="flex items-center gap-2">
                <Users className={`w-4 h-4 ${audienceType === 'colleague' && !isAdmin ? 'text-blue-600' : 'text-slate-400'}`} />
                <span className="font-extrabold text-xs text-slate-900">Team Member</span>
              </div>
              <p className="text-[10px] text-slate-500 mt-1 leading-snug">
                Colleague / employee. Participates in assigned teams, campfire, and projects.
              </p>
            </button>

            <button
              type="button"
              onClick={() => {
                setAudienceType('colleague');
                setIsAdmin(true);
              }}
              className={`p-3 rounded-2xl border text-left transition-all cursor-pointer select-none flex flex-col justify-between ${
                audienceType === 'colleague' && isAdmin
                  ? 'border-purple-600 bg-purple-50/70 shadow-xs ring-1 ring-purple-600'
                  : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'
              }`}
            >
              <div className="flex items-center gap-2">
                <ShieldCheck className={`w-4 h-4 ${audienceType === 'colleague' && isAdmin ? 'text-purple-600' : 'text-slate-400'}`} />
                <span className="font-extrabold text-xs text-slate-900">Administrator</span>
              </div>
              <p className="text-[10px] text-slate-500 mt-1 leading-snug">
                Full admin rights. Can invite members, create projects, configure teams and settings.
              </p>
            </button>

            <button
              type="button"
              onClick={() => {
                setAudienceType('client');
                setIsAdmin(false);
              }}
              className={`p-3 rounded-2xl border text-left transition-all cursor-pointer select-none flex flex-col justify-between ${
                audienceType === 'client'
                  ? 'border-emerald-600 bg-emerald-50/70 shadow-xs ring-1 ring-emerald-600'
                  : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'
              }`}
            >
              <div className="flex items-center gap-2">
                <Briefcase className={`w-4 h-4 ${audienceType === 'client' ? 'text-emerald-600' : 'text-slate-400'}`} />
                <span className="font-extrabold text-xs text-slate-900">Client Partner</span>
              </div>
              <p className="text-[10px] text-slate-500 mt-1 leading-snug">
                Outside client or guest. Restricted strictly to assigned projects.
              </p>
            </button>
          </div>

          {/* Recipient Details */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Full Name (Optional)
              </label>
              <div className="relative">
                <User className="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
                <input
                  type="text"
                  placeholder="e.g. Edward Smith"
                  value={fullName}
                  onChange={(e) => setFullName(e.target.value)}
                  className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 text-slate-900 font-medium"
                />
              </div>
            </div>

            <div>
              <div className="flex items-center justify-between mb-1">
                <label className="block text-xs font-bold text-slate-700">
                  Email Address(es) <span className="text-rose-500">*</span>
                </label>
                <span className="text-[10px] text-slate-400">Multiple: separate with commas</span>
              </div>
              <div className="relative">
                <Mail className="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
                <input
                  type="text"
                  required
                  placeholder="e.g. alex@company.com, sarah@company.com"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 text-slate-900 font-medium"
                />
              </div>
            </div>
          </div>

          {/* Job title */}
          <div className="p-3 rounded-2xl bg-slate-50 border border-slate-100">
            <label className="block text-[11px] font-bold text-slate-600 mb-0.5">
              Job Title / Department / Organization
            </label>
            <input
              type="text"
              placeholder="e.g. Senior Frontend Engineer"
              value={jobTitle}
              onChange={(e) => setJobTitle(e.target.value)}
              className="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500/30 text-slate-800"
            />
          </div>

          {/* Which projects should they have access to right away? */}
          <div className="space-y-2 pt-1">
            <div className="flex items-center justify-between">
              <div>
                <label className="block text-xs font-black text-slate-900">
                  Which projects should they have access to right away?
                </label>
                <p className="text-[11px] text-slate-500">
                  They will automatically be added as a project member upon acceptance.
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

            <div className="max-h-40 overflow-y-auto space-y-1.5 p-2 rounded-2xl border border-slate-200 bg-white">
              {projects.map((p) => {
                const isChecked = selectedProjectIds.has(p.id);
                return (
                  <div
                    key={p.id}
                    onClick={() => toggleProject(p.id)}
                    className={`p-2.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between select-none ${
                      isChecked
                        ? 'border-blue-500 bg-blue-50/60'
                        : 'border-slate-100 hover:bg-slate-50'
                    }`}
                  >
                    <div className="min-w-0 pr-2">
                      <p className="font-bold text-xs text-slate-900 truncate">{p.name}</p>
                      {p.description && (
                        <p className="text-[10px] text-slate-500 truncate">{p.description}</p>
                      )}
                    </div>
                    <div
                      className={`w-4 h-4 rounded flex items-center justify-center transition-colors shrink-0 ${
                        isChecked
                          ? 'bg-blue-600 text-white shadow-xs'
                          : 'border border-slate-300'
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
            <label className="block text-xs font-bold text-slate-700 mb-1">
              Personal Welcome Note (Optional)
            </label>
            <textarea
              rows={2}
              value={personalMessage}
              onChange={(e) => setPersonalMessage(e.target.value)}
              placeholder="Write a warm welcome note..."
              className="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 text-slate-800"
            />
          </div>

          {/* Actions */}
          <div className="flex items-center justify-between pt-3 border-t border-slate-100">
            <button
              type="button"
              onClick={handleReset}
              className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={isLoading || !email.trim()}
              className="px-5 py-2.5 text-xs font-bold text-white bg-[#0c66e4] hover:bg-[#0055cc] disabled:opacity-50 rounded-xl flex items-center gap-2 shadow-sm cursor-pointer transition-all"
            >
              <Send className="w-3.5 h-3.5" />
              <span>{isLoading ? 'Creating Invitation...' : 'Send the invitation'}</span>
            </button>
          </div>
        </form>
      )}
    </Modal>
  );
}
