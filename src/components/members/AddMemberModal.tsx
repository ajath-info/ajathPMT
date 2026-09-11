import React, { useState } from 'react';
import {
  UserPlus,
  ShieldCheck,
  Briefcase,
  Users,
  Sparkles,
  Check,
  Mail,
  User,
  CheckCircle2,
  Building2,
  Lock,
} from 'lucide-react';
import { Modal } from '../common/Modal';
import { Button } from '../common/Button';
import { useOrganization } from '../../context/OrganizationContext';
import { useToast } from '../../context/ToastContext';
import { addOrganizationMember, ensureOrganizationPeople } from '../../services/organizationService';
import { OrgRole } from '../../types';

interface AddMemberModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess?: () => void;
}

const SAMPLE_PRESETS = [
  {
    name: 'Alex Vance',
    email: 'alex.admin@worksphere.io',
    role: 'ADMIN' as OrgRole,
    jobTitle: 'Senior Operations & Tech Lead',
    avatarUrl: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
    description: 'System Administrator with workspace oversight and project management permissions.',
  },
  {
    name: 'Marcus Rivera',
    email: 'marcus.dev@worksphere.io',
    role: 'MEMBER' as OrgRole,
    jobTitle: 'Full-Stack Developer',
    avatarUrl: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
    description: 'Internal team colleague collaborating on code, tasks, and discussions.',
  },
  {
    name: 'Elena Rostova',
    email: 'elena.design@worksphere.io',
    role: 'MEMBER' as OrgRole,
    jobTitle: 'Senior Product Designer',
    avatarUrl: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
    description: 'Internal designer driving UX deliverables and sprint reviews.',
  },
  {
    name: 'Edward Smith',
    email: 'edward.client@partner.com',
    role: 'CLIENT' as OrgRole,
    jobTitle: 'Client Partner Representative',
    avatarUrl: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
    description: 'External client partner. Scoped exclusively to assigned projects with client boundaries.',
  },
];

export function AddMemberModal({ isOpen, onClose, onSuccess }: AddMemberModalProps) {
  const { currentOrganization, refreshOrganizationData } = useOrganization();
  const { addToast } = useToast();

  const [fullName, setFullName] = useState('');
  const [email, setEmail] = useState('');
  const [role, setRole] = useState<OrgRole>('MEMBER');
  const [jobTitle, setJobTitle] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSeedingAll, setIsSeedingAll] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleRoleSelect = (newRole: OrgRole) => {
    setRole(newRole);
    if (!jobTitle) {
      if (newRole === 'ADMIN') setJobTitle('Project & Operations Lead');
      else if (newRole === 'CLIENT') setJobTitle('Client Partner Representative');
      else setJobTitle('Software Engineer');
    }
  };

  const handleApplyPreset = (preset: typeof SAMPLE_PRESETS[0]) => {
    setFullName(preset.name);
    setEmail(preset.email);
    setRole(preset.role);
    setJobTitle(preset.jobTitle);
    setError(null);
  };

  const handleAddPerson = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!currentOrganization) return;
    if (!fullName.trim() || !email.trim()) {
      setError('Please provide a full name and a valid email address.');
      return;
    }

    setIsSubmitting(true);
    setError(null);

    try {
      const res = await addOrganizationMember(currentOrganization.id, {
        fullName: fullName.trim(),
        email: email.trim(),
        role,
        jobTitle: jobTitle.trim() || (role === 'ADMIN' ? 'Administrator' : role === 'CLIENT' ? 'Client Partner' : 'Team Member'),
      });

      if (res.error) {
        setError(res.error.message);
        setIsSubmitting(false);
        return;
      }

      addToast(`${fullName.trim()} has been added as ${role} to ${currentOrganization.name}`, 'success');
      await refreshOrganizationData();
      if (onSuccess) onSuccess();
      onClose();
      // Reset fields
      setFullName('');
      setEmail('');
      setJobTitle('');
      setRole('MEMBER');
    } catch (err: any) {
      setError(err.message || 'Failed to add person to organization.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleSeedAllSamplePeople = async () => {
    if (!currentOrganization) return;
    setIsSeedingAll(true);
    setError(null);

    try {
      await ensureOrganizationPeople(currentOrganization.id);
      addToast(`Added sample Admin (Alex), Members (Marcus, Elena), and Client (Edward) to ${currentOrganization.name}!`, 'success');
      await refreshOrganizationData();
      if (onSuccess) onSuccess();
      onClose();
    } catch (err: any) {
      setError(err.message || 'Failed to add sample people.');
    } finally {
      setIsSeedingAll(false);
    }
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="Add Person to Organization" maxWidth="xl">
      <div className="space-y-6">
        {/* Workspace indicator */}
        <div className="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 text-xs">
          <div className="flex items-center gap-2">
            <Building2 className="w-4 h-4 text-blue-600 dark:text-blue-400" />
            <span className="text-slate-500 dark:text-slate-400">Target Workspace:</span>
            <span className="font-bold text-slate-800 dark:text-slate-200">{currentOrganization?.name || 'Active Company'}</span>
          </div>
          <button
            type="button"
            onClick={handleSeedAllSamplePeople}
            disabled={isSeedingAll}
            className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold shadow-xs hover:shadow-md transition-all cursor-pointer active:scale-95 text-[11px]"
            title="Populate Admin, Client, and Members in one click"
          >
            <Sparkles className="w-3.5 h-3.5" />
            <span>{isSeedingAll ? 'Adding...' : 'Add All Sample Roles'}</span>
          </button>
        </div>

        {/* Quick Presets */}
        <div>
          <div className="flex items-center justify-between mb-2">
            <label className="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
              Quick Pick Presets (Admin, Client, Member)
            </label>
          </div>
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-2">
            {SAMPLE_PRESETS.map((preset) => (
              <button
                key={preset.email}
                type="button"
                onClick={() => handleApplyPreset(preset)}
                className={`p-2.5 rounded-xl border text-left transition-all cursor-pointer group ${
                  email === preset.email
                    ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/40 ring-2 ring-blue-500/20'
                    : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-700'
                }`}
              >
                <div className="flex items-center justify-between mb-1">
                  <span className="font-bold text-xs text-slate-900 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate">
                    {preset.name}
                  </span>
                  <span
                    className={`text-[9px] font-black px-1.5 py-0.5 rounded-md uppercase tracking-wider ${
                      preset.role === 'ADMIN'
                        ? 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300'
                        : preset.role === 'CLIENT'
                        ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300'
                        : 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300'
                    }`}
                  >
                    {preset.role}
                  </span>
                </div>
                <p className="text-[10px] text-slate-400 truncate">{preset.jobTitle}</p>
              </button>
            ))}
          </div>
        </div>

        {/* Manual Form */}
        <form onSubmit={handleAddPerson} className="space-y-4">
          {error && (
            <div className="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2">
              <Lock className="w-4 h-4 shrink-0" />
              <span>{error}</span>
            </div>
          )}

          {/* Role selector cards */}
          <div>
            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
              Assign Role in Organization <span className="text-rose-500">*</span>
            </label>
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
              {/* ADMIN */}
              <button
                type="button"
                onClick={() => handleRoleSelect('ADMIN')}
                className={`p-3.5 rounded-2xl border text-left transition-all cursor-pointer relative ${
                  role === 'ADMIN'
                    ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/40 ring-2 ring-amber-500/20 shadow-xs'
                    : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-300'
                }`}
              >
                <div className="flex items-center justify-between mb-1.5">
                  <div className="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <ShieldCheck className="w-4 h-4" />
                  </div>
                  {role === 'ADMIN' && <CheckCircle2 className="w-4 h-4 text-amber-600 dark:text-amber-400" />}
                </div>
                <h4 className="font-bold text-xs text-slate-900 dark:text-slate-100">Administrator</h4>
                <p className="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                  Full project oversight, member management & Adminland settings.
                </p>
              </button>

              {/* MEMBER */}
              <button
                type="button"
                onClick={() => handleRoleSelect('MEMBER')}
                className={`p-3.5 rounded-2xl border text-left transition-all cursor-pointer relative ${
                  role === 'MEMBER'
                    ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-950/40 ring-2 ring-blue-500/20 shadow-xs'
                    : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-300'
                }`}
              >
                <div className="flex items-center justify-between mb-1.5">
                  <div className="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <Briefcase className="w-4 h-4" />
                  </div>
                  {role === 'MEMBER' && <CheckCircle2 className="w-4 h-4 text-blue-600 dark:text-blue-400" />}
                </div>
                <h4 className="font-bold text-xs text-slate-900 dark:text-slate-100">Team Member</h4>
                <p className="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                  Internal colleague participating in tasks, teams, documents & campfire.
                </p>
              </button>

              {/* CLIENT */}
              <button
                type="button"
                onClick={() => handleRoleSelect('CLIENT')}
                className={`p-3.5 rounded-2xl border text-left transition-all cursor-pointer relative ${
                  role === 'CLIENT'
                    ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 ring-2 ring-emerald-500/20 shadow-xs'
                    : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-300'
                }`}
              >
                <div className="flex items-center justify-between mb-1.5">
                  <div className="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <Users className="w-4 h-4" />
                  </div>
                  {role === 'CLIENT' && <CheckCircle2 className="w-4 h-4 text-emerald-600 dark:text-emerald-400" />}
                </div>
                <h4 className="font-bold text-xs text-slate-900 dark:text-slate-100">Client Partner</h4>
                <p className="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                  External client. Access scoped exclusively to assigned projects.
                </p>
              </button>
            </div>
          </div>

          {/* Full Name & Email */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Full Name <span className="text-rose-500">*</span>
              </label>
              <div className="relative">
                <User className="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                <input
                  type="text"
                  required
                  value={fullName}
                  onChange={(e) => setFullName(e.target.value)}
                  placeholder="e.g. Alex Vance"
                  className="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Email Address <span className="text-rose-500">*</span>
              </label>
              <div className="relative">
                <Mail className="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                <input
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="e.g. alex.admin@worksphere.io"
                  className="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                />
              </div>
            </div>
          </div>

          {/* Job Title */}
          <div>
            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              Job Title / Designation
            </label>
            <input
              type="text"
              value={jobTitle}
              onChange={(e) => setJobTitle(e.target.value)}
              placeholder="e.g. Senior Operations & Tech Lead"
              className="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
            />
          </div>

          {/* Actions */}
          <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors cursor-pointer"
            >
              Cancel
            </button>
            <Button
              type="submit"
              variant="primary"
              isLoading={isSubmitting}
              className="px-5 py-2 text-xs font-bold rounded-xl flex items-center gap-2 cursor-pointer shadow-md shadow-blue-500/20"
            >
              <UserPlus className="w-4 h-4" />
              <span>Add Person to Workspace</span>
            </Button>
          </div>
        </form>
      </div>
    </Modal>
  );
}
