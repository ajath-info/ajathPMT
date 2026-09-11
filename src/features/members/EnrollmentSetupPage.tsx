import React, { useState, useEffect, useRef } from 'react';
import { Link, useNavigate, useParams, useLocation } from 'react-router-dom';
import {
  Plus,
  Trash2,
  Check,
  Copy,
  ArrowLeft,
  ChevronDown,
  Building2,
  FolderGit2,
  Mail,
  User,
  Briefcase,
  ExternalLink,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useProject } from '../../context/ProjectContext';
import { useToast } from '../../context/ToastContext';
import { createInvitation } from '../../services/organizationService';
import { addProjectMember } from '../../services/projectService';
import { OrgRole, ProjectMemberRole } from '../../types';

export type EnrollmentType = 'employees' | 'vendors' | 'clients';

interface PersonEntry {
  id: string;
  fullName: string;
  email: string;
  jobTitle: string;
  company: string;
}

const DEFAULT_VENDOR_COMPANIES = [
  'NDC Homes',
  'Sena Bhawan',
  'HiHlo',
  'Veggie Pro',
  'BIPL',
  'Freelance / Independent',
  'Design Studio Co',
];

interface EnrollmentSetupPageProps {
  type?: EnrollmentType;
}

export function EnrollmentSetupPage({ type: propType }: EnrollmentSetupPageProps) {
  const navigate = useNavigate();
  const location = useLocation();
  const { orgId } = useParams<{ orgId?: string }>();
  const { profile, userRole } = useAuth();
  const { currentOrganization, refreshOrganizationData } = useOrganization();
  const { projects } = useProject();
  const { addToast } = useToast();

  // Determine enrollment type: from prop or auto-detect from current path
  const enrollmentType: EnrollmentType =
    propType ||
    (location.pathname.includes('/employees')
      ? 'employees'
      : location.pathname.includes('/vendors')
      ? 'vendors'
      : 'clients');

  const orgName = currentOrganization?.name || 'Our Company';

  // Set browser title
  useEffect(() => {
    document.title = 'Invite someone to your account... Ajath PMT';
    return () => {
      document.title = 'Ajath PMT Workspace';
    };
  }, []);

  // Multi-person list
  const [people, setPeople] = useState<PersonEntry[]>([
    {
      id: 'person-1',
      fullName: '',
      email: '',
      jobTitle: '',
      company: enrollmentType === 'employees' ? orgName : '',
    },
  ]);

  // Keep company synced with orgName if employees
  useEffect(() => {
    if (enrollmentType === 'employees') {
      setPeople((prev) =>
        prev.map((p) => ({
          ...p,
          company: orgName,
        }))
      );
    }
  }, [enrollmentType, orgName]);

  // Expandable personal note
  const [showPersonalNote, setShowPersonalNote] = useState(false);
  const [personalNote, setPersonalNote] = useState('');

  // Expandable project access selector
  const [showProjectSelector, setShowProjectSelector] = useState(false);
  const [selectedProjectIds, setSelectedProjectIds] = useState<Set<string>>(
    () => new Set(projects.slice(0, 3).map((p) => p.id))
  );

  // Focus ref on the first input
  const firstInputRef = useRef<HTMLInputElement>(null);
  useEffect(() => {
    firstInputRef.current?.focus();
  }, [enrollmentType]);

  // State
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [submittedInvites, setSubmittedInvites] = useState<
    Array<{ name: string; email: string; company: string; link: string; token: string }>
  >([]);
  const [copiedToken, setCopiedToken] = useState<string | null>(null);
  const [activeDropdownIndex, setActiveDropdownIndex] = useState<number | null>(null);

  // Add row
  const handleAddRow = () => {
    const lastCompany =
      enrollmentType === 'employees'
        ? orgName
        : people[people.length - 1]?.company || '';

    setPeople((prev) => [
      ...prev,
      {
        id: `person-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
        fullName: '',
        email: '',
        jobTitle: '',
        company: lastCompany,
      },
    ]);
  };

  // Remove row
  const handleRemoveRow = (id: string) => {
    if (people.length <= 1) return;
    setPeople((prev) => prev.filter((p) => p.id !== id));
  };

  // Update row
  const handleUpdateRow = (id: string, field: keyof PersonEntry, value: string) => {
    setPeople((prev) =>
      prev.map((p) => (p.id === id ? { ...p, [field]: value } : p))
    );
  };

  // Project toggle
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

  // Submit invitations
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    const validPeople = people.filter((p) => p.email.trim().length > 0);
    if (validPeople.length === 0) {
      setError('Please provide at least one valid email address.');
      return;
    }

    for (const p of validPeople) {
      if (!p.email.includes('@') || !p.email.includes('.')) {
        setError(`Please enter a valid email address for "${p.fullName || p.email}".`);
        return;
      }
    }

    if (!currentOrganization || !profile) {
      setError('Organization context not loaded. Please refresh the page.');
      return;
    }

    setError(null);
    setIsLoading(true);

    const role: OrgRole = enrollmentType === 'clients' ? 'CLIENT' : 'MEMBER';
    const projectMemberRole: ProjectMemberRole =
      enrollmentType === 'clients' ? 'PROJECT_CLIENT' : 'PROJECT_MEMBER';

    const results: Array<{ name: string; email: string; company: string; link: string; token: string }> = [];

    for (const p of validPeople) {
      const fullNote = [
        p.fullName ? `Name: ${p.fullName}` : null,
        p.jobTitle ? `Title: ${p.jobTitle}` : null,
        p.company ? `Company: ${p.company}` : null,
        personalNote.trim() ? `Personal Note: ${personalNote.trim()}` : null,
      ]
        .filter(Boolean)
        .join(' | ');

      const firstProjId = selectedProjectIds.size > 0 ? Array.from(selectedProjectIds)[0] : undefined;
      const res = await createInvitation(
        currentOrganization.id,
        p.email.trim(),
        role,
        profile.id,
        fullNote || `${enrollmentType.slice(0, -1)} invitation`,
        userRole,
        {
          projectId: firstProjId,
          projectRole: projectMemberRole,
        }
      );

      if (res.data) {
        const inviteUrl = `${window.location.origin}/invite/${res.data.token}`;
        results.push({
          name: p.fullName.trim() || p.email.trim(),
          email: p.email.trim(),
          company: p.company.trim(),
          link: inviteUrl,
          token: res.data.token,
        });

        // Grant access to selected projects
        const tempUserId = `user-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`;
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

    if (results.length > 0) {
      setSubmittedInvites(results);
      refreshOrganizationData();
      addToast(
        results.length === 1
          ? 'Invitation sent successfully!'
          : `${results.length} invitations sent successfully!`,
        'success'
      );
    } else {
      setError('Failed to send invitations. Please try again.');
    }
  };

  const handleCopyLink = (link: string, token: string) => {
    navigator.clipboard.writeText(link);
    setCopiedToken(token);
    addToast('Invitation link copied to clipboard!', 'info');
    setTimeout(() => {
      setCopiedToken((prev) => (prev === token ? null : prev));
    }, 3000);
  };

  const handleReset = () => {
    setPeople([
      {
        id: `person-${Date.now()}`,
        fullName: '',
        email: '',
        jobTitle: '',
        company: enrollmentType === 'employees' ? orgName : '',
      },
    ]);
    setSubmittedInvites([]);
    setPersonalNote('');
    setError(null);
  };

  // Dynamic titles and subtitles matching the screenshots
  const headingText =
    enrollmentType === 'employees'
      ? "Set up your coworker's account"
      : enrollmentType === 'vendors'
      ? 'Set up your contractor, vendor, etc.'
      : "Set up your client's account";

  const subtitleText =
    enrollmentType === 'employees'
      ? `Full-time, part-time, or a volunteer at ${orgName}? They can create projects, add people to projects, and become administrators.`
      : enrollmentType === 'vendors'
      ? `People outside ${orgName} can collaborate on projects with you, but they won't be able to create projects, invite people to the account, add people to projects, or be admins.`
      : "Clients can access projects you've created, but they can't create their own, invite or add new people, or become admins. You can hide parts of projects from them so they can't see work in progress.";

  return (
    <div className="w-full flex justify-center py-8 sm:py-14 px-4 sm:px-6">
      {/* Centered White Card matching authentic Basecamp screenshots */}
      <div className="w-full max-w-[620px] bg-white dark:bg-slate-900 rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.04)] border border-slate-200/80 dark:border-slate-800 p-8 sm:p-12 md:p-14 animate-in fade-in duration-200">
        
        {/* Navigation Breadcrumb / Back Link */}
        <div className="mb-4">
          <Link
            to={orgId ? `/${orgId}/account/enrollments/new` : '/account/enrollments/new'}
            className="inline-flex items-center gap-1.5 text-[13px] font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-colors"
          >
            <ArrowLeft className="w-3.5 h-3.5" />
            <span>‹ Who are you inviting?</span>
          </Link>
        </div>

        {submittedInvites.length === 0 ? (
          /* ================= ACTIVE ENROLLMENT FORM ================= */
          <div>
            {/* Heading & Subtitle exactly matching Basecamp */}
            <h1 className="text-[32px] sm:text-[34px] font-extrabold text-slate-900 dark:text-slate-50 tracking-tight leading-tight mb-2">
              {headingText}
            </h1>
            <p className="text-[15px] text-slate-600 dark:text-slate-400 font-normal leading-normal mb-8">
              {subtitleText}
            </p>

            {error && (
              <div className="mb-6 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-600 animate-in fade-in">
                {error}
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Person Rows List */}
              <div className="space-y-6">
                {people.map((person, index) => (
                  <div key={person.id} className="relative group transition-all">
                    {people.length > 1 && (
                      <div className="flex items-center justify-between pb-2">
                        <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">
                          Person #{index + 1}
                        </span>
                        <button
                          type="button"
                          onClick={() => handleRemoveRow(person.id)}
                          className="text-xs text-rose-500 hover:text-rose-700 flex items-center gap-1 font-semibold cursor-pointer"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                          <span>Remove</span>
                        </button>
                      </div>
                    )}

                    {/* 2x2 Input Grid */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                      {/* Row 1, Col 1: Full name */}
                      <div>
                        <input
                          ref={index === 0 ? firstInputRef : undefined}
                          type="text"
                          placeholder="Full name"
                          value={person.fullName}
                          onChange={(e) =>
                            handleUpdateRow(person.id, 'fullName', e.target.value)
                          }
                          className="w-full px-3.5 py-2.5 text-[15px] rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/40 transition-all font-normal"
                        />
                      </div>

                      {/* Row 1, Col 2: Email address */}
                      <div>
                        <input
                          type="email"
                          placeholder="Email address"
                          value={person.email}
                          onChange={(e) =>
                            handleUpdateRow(person.id, 'email', e.target.value)
                          }
                          className="w-full px-3.5 py-2.5 text-[15px] rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/40 transition-all font-normal"
                        />
                      </div>

                      {/* Row 2, Col 1: Job title (optional) */}
                      <div>
                        <input
                          type="text"
                          placeholder="Job title (optional)"
                          value={person.jobTitle}
                          onChange={(e) =>
                            handleUpdateRow(person.id, 'jobTitle', e.target.value)
                          }
                          className="w-full px-3.5 py-2.5 text-[15px] rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/40 transition-all font-normal"
                        />
                      </div>

                      {/* Row 2, Col 2: Company/organization field */}
                      {enrollmentType === 'employees' ? (
                        /* Coworker fixed company/org box matching Image 1 */
                        <div className="w-full px-3.5 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 flex flex-col justify-center select-none shadow-2xs">
                          <span className="text-[10px] text-slate-400 dark:text-slate-400 font-medium leading-tight">
                            Company/organization
                          </span>
                          <span className="text-[14px] text-slate-800 dark:text-slate-200 font-normal leading-tight truncate">
                            {orgName}
                          </span>
                        </div>
                      ) : (
                        /* Contractor/Vendor & Client selector matching Image 2 */
                        <div className="relative">
                          <div className="relative flex items-center">
                            <input
                              type="text"
                              placeholder="Type company/org name..."
                              value={person.company}
                              onChange={(e) =>
                                handleUpdateRow(person.id, 'company', e.target.value)
                              }
                              onFocus={() => setActiveDropdownIndex(index)}
                              className="w-full pl-3.5 pr-8 py-2.5 text-[15px] rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/40 transition-all font-normal"
                            />
                            <button
                              type="button"
                              onClick={() =>
                                setActiveDropdownIndex(
                                  activeDropdownIndex === index ? null : index
                                )
                              }
                              className="absolute right-2.5 p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                            >
                              <ChevronDown className="w-4 h-4" />
                            </button>
                          </div>

                          {/* Quick Suggestions Dropdown */}
                          {activeDropdownIndex === index && (
                            <div className="absolute top-full left-0 right-0 mt-1 z-30 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg py-1.5 max-h-48 overflow-y-auto animate-in fade-in">
                              <div className="px-3 py-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                Suggested companies/orgs
                              </div>
                              {DEFAULT_VENDOR_COMPANIES.map((companyName) => (
                                <button
                                  key={companyName}
                                  type="button"
                                  onClick={() => {
                                    handleUpdateRow(person.id, 'company', companyName);
                                    setActiveDropdownIndex(null);
                                  }}
                                  className="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-700 flex items-center justify-between cursor-pointer"
                                >
                                  <span>{companyName}</span>
                                  {person.company === companyName && (
                                    <Check className="w-3.5 h-3.5 text-blue-600" />
                                  )}
                                </button>
                              ))}
                            </div>
                          )}
                        </div>
                      )}
                    </div>
                  </div>
                ))}
              </div>

              {/* Square Plus Button matching screenshots */}
              <div>
                <button
                  type="button"
                  onClick={handleAddRow}
                  title="Add another person"
                  className="w-10 h-10 rounded-xl border border-slate-300 dark:border-slate-700 hover:border-slate-400 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 flex items-center justify-center text-[#0c66e4] hover:text-[#0055cc] shadow-2xs transition-all cursor-pointer group"
                >
                  <Plus className="w-5 h-5 stroke-[2.5] group-hover:scale-110 transition-transform" />
                </button>
              </div>

              {/* Expandable Personal Note Link */}
              <div className="pt-1">
                {!showPersonalNote ? (
                  <button
                    type="button"
                    onClick={() => setShowPersonalNote(true)}
                    className="text-[14px] text-[#0c66e4] hover:text-[#0055cc] hover:underline cursor-pointer block font-normal text-left"
                  >
                    Add a personal note to the invitation email
                  </button>
                ) : (
                  <div className="space-y-1.5 animate-in fade-in">
                    <div className="flex items-center justify-between">
                      <label className="text-xs font-bold text-slate-700 dark:text-slate-300">
                        Personal note in the invitation email
                      </label>
                      <button
                        type="button"
                        onClick={() => setShowPersonalNote(false)}
                        className="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                      >
                        Hide note
                      </button>
                    </div>
                    <textarea
                      rows={3}
                      value={personalNote}
                      onChange={(e) => setPersonalNote(e.target.value)}
                      placeholder="e.g. Welcome! We look forward to collaborating together on our projects."
                      className="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/40"
                    />
                  </div>
                )}
              </div>

              {/* Optional Collapsible Project Access Assignment */}
              <div className="pt-1">
                {!showProjectSelector ? (
                  <button
                    type="button"
                    onClick={() => setShowProjectSelector(true)}
                    className="text-[13px] text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:underline cursor-pointer block text-left"
                  >
                    Choose specific projects to grant right away ({selectedProjectIds.size} selected) ▾
                  </button>
                ) : (
                  <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3 animate-in fade-in">
                    <div className="flex items-center justify-between">
                      <div>
                        <span className="text-xs font-bold text-slate-900 dark:text-slate-100 block">
                          Project Access
                        </span>
                        <span className="text-[11px] text-slate-500 dark:text-slate-400">
                          Select which projects will be accessible upon joining.
                        </span>
                      </div>
                      <div className="flex items-center gap-2 text-[11px] font-bold">
                        <button
                          type="button"
                          onClick={handleSelectAllProjects}
                          className="text-blue-600 hover:underline cursor-pointer"
                        >
                          All
                        </button>
                        <span className="text-slate-300">•</span>
                        <button
                          type="button"
                          onClick={handleClearProjects}
                          className="text-slate-500 hover:underline cursor-pointer"
                        >
                          None
                        </button>
                        <span className="text-slate-300">•</span>
                        <button
                          type="button"
                          onClick={() => setShowProjectSelector(false)}
                          className="text-slate-400 hover:text-slate-600 cursor-pointer"
                        >
                          Done
                        </button>
                      </div>
                    </div>

                    <div className="max-h-40 overflow-y-auto space-y-1.5 pr-1">
                      {projects.map((p) => {
                        const isChecked = selectedProjectIds.has(p.id);
                        return (
                          <div
                            key={p.id}
                            onClick={() => toggleProject(p.id)}
                            className={`p-2 rounded-lg border text-xs cursor-pointer flex items-center justify-between transition-colors ${
                              isChecked
                                ? 'bg-blue-50/70 border-blue-400 text-blue-900 dark:bg-blue-950/40 dark:border-blue-700 dark:text-blue-100'
                                : 'bg-white border-slate-200 hover:bg-slate-100/60 text-slate-700 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200'
                            }`}
                          >
                            <span className="font-semibold truncate pr-2">{p.name}</span>
                            <div
                              className={`w-4 h-4 rounded flex items-center justify-center shrink-0 ${
                                isChecked
                                  ? 'bg-blue-600 text-white'
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
                )}
              </div>

              {/* Blue Primary Button matching Basecamp */}
              <div className="pt-2">
                <button
                  type="submit"
                  disabled={isLoading}
                  className="inline-flex items-center justify-center px-6 py-2.5 sm:py-3 text-[14px] font-bold text-white bg-[#0c66e4] hover:bg-[#0055cc] active:bg-[#0047b3] disabled:opacity-50 rounded-xl shadow-xs transition-colors cursor-pointer"
                >
                  {isLoading ? 'Emailing invitations...' : 'Email invitation now...'}
                </button>
              </div>
            </form>
          </div>
        ) : (
          /* ================= SUCCESS CONFIRMATION STATE ================= */
          <div className="space-y-6 animate-in fade-in duration-200">
            <div className="text-center space-y-2">
              <div className="w-14 h-14 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center mx-auto border border-emerald-200 dark:border-emerald-800 shadow-2xs">
                <Check className="w-7 h-7 stroke-[3]" />
              </div>
              <h2 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                {submittedInvites.length === 1
                  ? 'Invitation Sent!'
                  : `${submittedInvites.length} Invitations Sent!`}
              </h2>
              <p className="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                Invitations have been registered. The invitees can use the direct link below to join immediately.
              </p>
            </div>

            {/* Generated Links for Instant Sharing */}
            <div className="space-y-3">
              <div className="text-xs font-bold text-slate-700 dark:text-slate-300">
                Shareable Direct Invitation Links
              </div>
              {submittedInvites.map((inv) => {
                const isCopied = copiedToken === inv.token;
                return (
                  <div
                    key={inv.token}
                    className="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2"
                  >
                    <div className="min-w-0">
                      <div className="text-xs font-bold text-slate-900 dark:text-slate-100 truncate">
                        {inv.name} ({inv.email})
                      </div>
                      {inv.company && (
                        <div className="text-[11px] text-slate-500 dark:text-slate-400">
                          {inv.company}
                        </div>
                      )}
                      <div className="text-[11px] font-mono text-blue-600 dark:text-blue-400 truncate mt-0.5">
                        {inv.link}
                      </div>
                    </div>
                    <button
                      type="button"
                      onClick={() => handleCopyLink(inv.link, inv.token)}
                      className={`px-3 py-1.5 rounded-lg text-xs font-bold shrink-0 flex items-center justify-center gap-1.5 transition-colors cursor-pointer shadow-2xs ${
                        isCopied
                          ? 'bg-emerald-600 text-white'
                          : 'bg-[#0c66e4] hover:bg-[#0055cc] text-white'
                      }`}
                    >
                      {isCopied ? <Check className="w-3.5 h-3.5" /> : <Copy className="w-3.5 h-3.5" />}
                      <span>{isCopied ? 'Copied' : 'Copy link'}</span>
                    </button>
                  </div>
                );
              })}
            </div>

            {/* Bottom Actions */}
            <div className="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
              <button
                type="button"
                onClick={handleReset}
                className="text-xs font-bold text-[#0c66e4] hover:underline cursor-pointer"
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

// Named convenience components for clean route element declarations
export const EmployeeEnrollmentPage = () => <EnrollmentSetupPage type="employees" />;
export const VendorEnrollmentPage = () => <EnrollmentSetupPage type="vendors" />;
export const ClientEnrollmentPage = () => <EnrollmentSetupPage type="clients" />;
