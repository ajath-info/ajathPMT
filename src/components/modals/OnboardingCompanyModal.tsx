import React, { useState } from 'react';
import { useOrganization } from '../../context/OrganizationContext';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import {
  Building2,
  Sparkles,
  ShieldCheck,
  CheckCircle2,
  Mail,
  Globe,
  X,
  Loader2,
} from 'lucide-react';

interface OnboardingCompanyModalProps {
  isOpen: boolean;
  onClose?: () => void;
  isMandatory?: boolean;
}

const PRESET_LOGOS = [
  'https://images.unsplash.com/photo-1572021335469-31706a17aaef?w=150&auto=format&fit=crop&q=80',
  'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=150&auto=format&fit=crop&q=80',
  'https://images.unsplash.com/photo-1551434678-e076c223a692?w=150&auto=format&fit=crop&q=80',
  'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=150&auto=format&fit=crop&q=80',
];

export function OnboardingCompanyModal({
  isOpen,
  onClose,
  isMandatory = false,
}: OnboardingCompanyModalProps) {
  const {
    createNewOrganization,
    userPendingInvitations,
    acceptUserInvitation,
    declineUserInvitation,
    organizations,
  } = useOrganization();
  const { profile } = useAuth();
  const { addToast } = useToast();

  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [slug, setSlug] = useState('');
  const [logoUrl, setLogoUrl] = useState('');
  const [loading, setLoading] = useState(false);
  const [invitationLoading, setInvitationLoading] = useState<string | null>(null);

  if (!isOpen || (organizations && organizations.length > 0 && !isMandatory)) return null;

  const handleNameChange = (val: string) => {
    setName(val);
    setSlug(val.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-'));
  };

  const handleCreateCompany = async (e: React.FormEvent) => {
    e.preventDefault();
    if (organizations && organizations.length > 0) {
      addToast('Your company workspace has already been created. One admin manages one company.', 'info');
      if (onClose) onClose();
      return;
    }
    if (!name.trim()) {
      addToast('Please enter a company name', 'error');
      return;
    }

    setLoading(true);
    try {
      const res = await createNewOrganization(
        name.trim(),
        slug.trim(),
        description.trim(),
        logoUrl.trim() || undefined
      );

      if (res.error) {
        addToast(res.error.message || 'Failed to create organization', 'error');
      } else {
        addToast(`Welcome to ${name.trim()}! Workspace created successfully.`, 'success');
        if (onClose) onClose();
      }
    } catch (err: any) {
      addToast(err?.message || 'Error creating organization', 'error');
    } finally {
      setLoading(false);
    }
  };

  const handleAcceptInvite = async (token: string, orgName?: string) => {
    setInvitationLoading(token);
    try {
      const res = await acceptUserInvitation(token);
      if (res.error) {
        addToast(res.error.message || 'Failed to accept invitation', 'error');
      } else {
        addToast(`Joined ${orgName || 'organization'} successfully!`, 'success');
        if (onClose) onClose();
      }
    } catch (err: any) {
      addToast(err?.message || 'Error accepting invitation', 'error');
    } finally {
      setInvitationLoading(null);
    }
  };

  const handleDeclineInvite = async (token: string) => {
    setInvitationLoading(token);
    try {
      const res = await declineUserInvitation(token);
      if (res.error) {
        addToast(res.error.message || 'Failed to decline invitation', 'error');
      } else {
        addToast('Invitation declined', 'info');
      }
    } catch (err: any) {
      addToast(err?.message || 'Error declining invitation', 'error');
    } finally {
      setInvitationLoading(null);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm overflow-y-auto animate-in fade-in duration-200">
      <div className="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden my-8">
        {/* Header Ribbon */}
        <div className="relative px-6 py-6 sm:px-8 bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-800 text-white">
          <div className="absolute top-0 right-0 -mr-16 -mt-16 w-56 h-56 rounded-full bg-pink-500/20 blur-2xl pointer-events-none" />
          <div className="relative z-10 flex items-start justify-between">
            <div className="space-y-1">
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-white text-[11px] font-bold border border-white/20">
                <Building2 className="w-3.5 h-3.5 text-amber-300" />
                <span>Ajath PMT Organization Setup</span>
              </div>
              <h2 className="text-2xl font-black tracking-tight text-white pt-1">
                {isMandatory ? 'Set Up Your Organization' : 'Create a New Organization'}
              </h2>
              <p className="text-xs sm:text-sm text-blue-100">
                Every company has its own independent space for projects, teams, campfire chats, and client collaboration.
              </p>
            </div>

            {!isMandatory && onClose && (
              <button
                onClick={onClose}
                className="p-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white transition-colors cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            )}
          </div>
        </div>

        <div className="p-6 sm:p-8 space-y-6 max-h-[80vh] overflow-y-auto">
          {/* PENDING INVITATIONS BANNER (If any exist for the user) */}
          {userPendingInvitations && userPendingInvitations.length > 0 && (
            <div className="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 space-y-3">
              <div className="flex items-center gap-2 text-amber-800 dark:text-amber-200 font-bold text-sm">
                <Mail className="w-4 h-4 text-amber-600" />
                <span>You have {userPendingInvitations.length} pending invitation{userPendingInvitations.length > 1 ? 's' : ''}!</span>
              </div>
              <p className="text-xs text-amber-700 dark:text-amber-300">
                An administrator invited you to join their workspace. You can accept to enter their company directly, or create your own organization below.
              </p>

              <div className="space-y-2 pt-1">
                {userPendingInvitations.map((inv) => (
                  <div
                    key={inv.id}
                    className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-white dark:bg-slate-800/90 rounded-xl border border-amber-200/80 dark:border-amber-800/40 shadow-xs"
                  >
                    <div>
                      <p className="text-xs font-bold text-slate-900 dark:text-white">
                        {inv.organization?.name || 'Company Workspace'}
                      </p>
                      <p className="text-[11px] text-slate-500">
                        Role: <span className="font-semibold text-indigo-600 dark:text-indigo-400">{inv.role}</span>
                        {inv.expires_at && (
                          <span className="ml-2 text-slate-400">
                            Expires {new Date(inv.expires_at).toLocaleDateString()}
                          </span>
                        )}
                      </p>
                    </div>

                    <div className="flex items-center gap-2">
                      <button
                        type="button"
                        onClick={() => handleAcceptInvite(inv.token, inv.organization?.name)}
                        disabled={invitationLoading === inv.token}
                        className="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs disabled:opacity-50 cursor-pointer"
                      >
                        {invitationLoading === inv.token ? (
                          <Loader2 className="w-3.5 h-3.5 animate-spin" />
                        ) : (
                          <CheckCircle2 className="w-3.5 h-3.5" />
                        )}
                        <span>Accept & Join</span>
                      </button>

                      <button
                        type="button"
                        onClick={() => handleDeclineInvite(inv.token)}
                        disabled={invitationLoading === inv.token}
                        className="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-medium transition-all disabled:opacity-50 cursor-pointer"
                      >
                        Decline
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* CREATE COMPANY FORM */}
          <form onSubmit={handleCreateCompany} className="space-y-5">
            <div>
              <label className="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                Company / Organization Name <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                required
                placeholder="e.g. Acme Corp, Global Tech, or Horizon Studio"
                value={name}
                onChange={(e) => handleNameChange(e.target.value)}
                className="w-full px-4 py-3 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
              />
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                  Workspace Slug / Identifier
                </label>
                <div className="relative">
                  <Globe className="absolute left-3.5 top-3.5 w-4 h-4 text-slate-400" />
                  <input
                    type="text"
                    placeholder="acme-corp"
                    value={slug}
                    onChange={(e) => setSlug(e.target.value)}
                    className="w-full pl-10 pr-4 py-3 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs font-mono"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                  Company Logo URL (Optional)
                </label>
                <input
                  type="url"
                  placeholder="https://example.com/logo.png"
                  value={logoUrl}
                  onChange={(e) => setLogoUrl(e.target.value)}
                  className="w-full px-4 py-3 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs"
                />
              </div>
            </div>

            {/* Preset Logos */}
            <div>
              <p className="text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-2">
                Or pick a preset avatar:
              </p>
              <div className="flex items-center gap-3">
                {PRESET_LOGOS.map((url, i) => (
                  <button
                    key={i}
                    type="button"
                    onClick={() => setLogoUrl(url)}
                    className={`w-10 h-10 rounded-xl overflow-hidden border-2 transition-all cursor-pointer ${
                      logoUrl === url
                        ? 'border-indigo-600 scale-105 shadow-md ring-2 ring-indigo-500/30'
                        : 'border-slate-200 dark:border-slate-700 opacity-70 hover:opacity-100'
                    }`}
                  >
                    <img src={url} alt={`Preset ${i}`} className="w-full h-full object-cover" />
                  </button>
                ))}
                {logoUrl && (
                  <button
                    type="button"
                    onClick={() => setLogoUrl('')}
                    className="text-[11px] text-slate-500 hover:text-rose-500 font-medium ml-2"
                  >
                    Clear
                  </button>
                )}
              </div>
            </div>

            <div>
              <label className="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                Description / Purpose
              </label>
              <textarea
                rows={2}
                placeholder="What does your company or team do? (e.g. Design agency, SaaS platform, Mobile engineering...)"
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                className="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
            </div>

            {/* Ownership guarantee note */}
            <div className="flex items-start gap-3 p-3.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/60">
              <ShieldCheck className="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0 mt-0.5" />
              <div className="text-xs text-indigo-900 dark:text-indigo-200 space-y-0.5">
                <p className="font-bold">You will be the OWNER of this workspace</p>
                <p className="text-indigo-700 dark:text-indigo-300 text-[11px]">
                  You will have full administrative authority to invite employees, manage roles, create teams and projects, and configure billing or security.
                </p>
              </div>
            </div>

            {/* Form Actions */}
            <div className="pt-2 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
              {!isMandatory && onClose && (
                <button
                  type="button"
                  onClick={onClose}
                  className="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                >
                  Cancel
                </button>
              )}

              <button
                type="submit"
                disabled={loading || !name.trim()}
                className="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md hover:shadow-lg transition-all disabled:opacity-50 cursor-pointer"
              >
                {loading ? (
                  <Loader2 className="w-4 h-4 animate-spin" />
                ) : (
                  <Sparkles className="w-4 h-4 text-amber-300" />
                )}
                <span>{loading ? 'Creating Workspace...' : 'Create Company Workspace'}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
