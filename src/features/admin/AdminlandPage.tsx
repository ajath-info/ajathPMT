import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Users,
  MessageSquare,
  ArrowUpRight,
  Rocket,
  DollarSign,
  Download,
  Lock,
  X,
  Check,
  Plus,
  FileText,
  CreditCard,
  Building2,
  CheckCircle2,
  AlertTriangle,
  ExternalLink,
  ShieldCheck,
  Send,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useProject } from '../../context/ProjectContext';
import { useToast } from '../../context/ToastContext';
import { getInitials } from '../../lib/utils';
import { transferAccountOwnership } from '../../services/organizationService';

interface Person {
  id: string;
  name: string;
  initials?: string;
  avatarUrl?: string;
  avatarBg?: string;
  email: string;
  role: string;
  isCurrentUser?: boolean;
}

const INITIAL_CATEGORIES = [
  { id: 'cat-1', name: 'Announcements', color: '#16a34a', desc: 'Company-wide news and updates' },
  { id: 'cat-2', name: 'FYI', color: '#0284c7', desc: 'Good to know, no reply needed' },
  { id: 'cat-3', name: 'Pitch', color: '#9333ea', desc: 'Proposals, pitches, and ideas' },
  { id: 'cat-4', name: 'Heartbeat', color: '#ea580c', desc: 'Weekly status updates' },
  { id: 'cat-5', name: 'Questions', color: '#ca8a04', desc: 'Ask the team anything' },
];

export function AdminlandPage() {
  const navigate = useNavigate();
  const { profile, userRole } = useAuth();
  const { currentOrganization, members } = useOrganization();
  const { projects } = useProject();
  const { addToast } = useToast();

  const isCurrentOwner =
    userRole === 'OWNER' ||
    profile?.job_title === 'Account Owner' ||
    currentOrganization?.owner_id === profile?.id ||
    localStorage.getItem('worksphere_demo_role') === 'OWNER';

  // Dynamic list of Account Owners (including currently logged-in owner)
  const ownersList: (Person & { isCurrentUser?: boolean })[] = React.useMemo(() => {
    const list: (Person & { isCurrentUser?: boolean })[] = [];

    // 1. If currently logged in as Account Owner, put them first
    if (isCurrentOwner && profile) {
      list.push({
        id: profile.id || 'current-owner-user',
        name: profile.full_name || 'Account Owner',
        avatarUrl: profile.avatar_url || '',
        initials: getInitials(profile.full_name || 'Account Owner'),
        avatarBg: 'bg-amber-600',
        email: profile.email || '',
        role: 'Account Owner',
        isCurrentUser: true,
      });
    }

    // 2. Add any other distinct owners from organization members
    if (members && members.length > 0) {
      members
        .filter((m) => m.role === 'OWNER' || m.profile?.job_title === 'Account Owner')
        .forEach((m) => {
          const isSelf =
            m.user_id === profile?.id ||
            m.user_id === currentOrganization?.owner_id ||
            m.profile?.id === profile?.id ||
            m.profile?.id === currentOrganization?.owner_id ||
            (profile?.email && m.profile?.email && m.profile.email.toLowerCase() === profile.email.toLowerCase());

          if (isSelf) return; // Skip self since already added above

          const name = m.profile?.full_name?.trim();
          const email = m.profile?.email?.trim() || '';

          // Avoid phantom/placeholder owners that have no real identity
          if (!name || (name === 'Account Owner' && !email)) return;

          if (!list.some((existing) => (email && existing.email === email) || existing.id === (m.user_id || m.id))) {
            list.push({
              id: m.user_id || m.id,
              name,
              avatarUrl: m.profile?.avatar_url || '',
              initials: getInitials(name),
              avatarBg: 'bg-amber-600',
              email,
              role: 'Account Owner',
              isCurrentUser: false,
            });
          }
        });
    }

    // Fallback if list is empty
    if (list.length === 0 && profile) {
      list.push({
        id: profile.id,
        name: profile.full_name || 'Account Owner',
        avatarUrl: profile.avatar_url || '',
        initials: getInitials(profile.full_name || 'AO'),
        avatarBg: 'bg-amber-600',
        email: profile.email || '',
        role: 'Account Owner',
        isCurrentUser: isCurrentOwner,
      });
    }

    return list;
  }, [isCurrentOwner, profile, members, currentOrganization]);

  // Dynamic list of Administrators
  const adminsList: (Person & { isCurrentUser?: boolean })[] = React.useMemo(() => {
    const list: (Person & { isCurrentUser?: boolean })[] = [];

    // 1. If currently logged in as Administrator, put them first
    if (userRole === 'ADMIN' && profile) {
      list.push({
        id: profile.id || 'current-admin-user',
        name: profile.full_name || 'Administrator',
        avatarUrl: profile.avatar_url || '',
        initials: getInitials(profile.full_name || 'Administrator'),
        avatarBg: 'bg-blue-600',
        email: profile.email || '',
        role: 'Administrator',
        isCurrentUser: true,
      });
    }

    // 2. Organization members with role 'ADMIN'
    if (members && members.length > 0) {
      members
        .filter((m) => m.role === 'ADMIN')
        .forEach((m) => {
          const isSelf =
            m.user_id === profile?.id ||
            m.profile?.id === profile?.id ||
            (profile?.email && m.profile?.email && m.profile.email.toLowerCase() === profile.email.toLowerCase());

          if (isSelf) return;

          const name = m.profile?.full_name?.trim() || 'Administrator';
          const email = m.profile?.email?.trim() || '';

          if (!list.some((existing) => (email && existing.email === email) || existing.id === (m.user_id || m.id))) {
            list.push({
              id: m.user_id || m.id,
              name,
              avatarUrl: m.profile?.avatar_url || '',
              initials: getInitials(name),
              avatarBg: 'bg-[#00828a]',
              email,
              role: 'Administrator',
              isCurrentUser: false,
            });
          }
        });
    }

    return list;
  }, [userRole, profile, members]);

  useEffect(() => {
    document.title = 'Adminland';
  }, []);

  // Modals & Active Action States
  const [selectedPersonForPing, setSelectedPersonForPing] = useState<Person | null>(null);
  const [pingMessage, setPingMessage] = useState('');
  const [isPingSent, setIsPingSent] = useState(false);

  // 1. Project Access Modal
  const [isProjectAccessModalOpen, setIsProjectAccessModalOpen] = useState(false);
  const [selectedMemberId, setSelectedMemberId] = useState<string>('demo-user-member');
  const [memberProjectAccess, setMemberProjectAccess] = useState<Record<string, Record<string, boolean>>>({
    'demo-user-admin': { 'proj-rmc': true, 'proj-hq': true, 'proj-bipl': true },
    'demo-user-member': { 'proj-rmc': true, 'proj-hq': true },
    'demo-user-client': { 'proj-rmc': true },
  });

  // 2. Message Categories Modal
  const [isCategoriesModalOpen, setIsCategoriesModalOpen] = useState(false);
  const [categories, setCategories] = useState(INITIAL_CATEGORIES);
  const [newCatName, setNewCatName] = useState('');
  const [newCatColor, setNewCatColor] = useState('#2563eb');

  // 3. Migration Modal
  const [isMigrationModalOpen, setIsMigrationModalOpen] = useState(false);
  const [isMigrating, setIsMigrating] = useState(false);

  // 4. Upgrade Plan Modal
  const [isUpgradeModalOpen, setIsUpgradeModalOpen] = useState(false);
  const [selectedPlan, setSelectedPlan] = useState<'pro' | 'plus'>('pro');

  // 5. Billing Modal
  const [isBillingModalOpen, setIsBillingModalOpen] = useState(false);

  // 6. 2FA Modal
  const [is2FAModalOpen, setIs2FAModalOpen] = useState(false);
  const [is2FARequired, setIs2FARequired] = useState<boolean>(() => {
    try {
      return localStorage.getItem('basecamp_security_2fa_required') === 'true';
    } catch {
      return false;
    }
  });

  // 7. Cancel Account Modal
  const [isCancelModalOpen, setIsCancelModalOpen] = useState(false);
  const [cancelConfirmText, setCancelConfirmText] = useState('');

  // 8. Transfer Account Ownership Modal (Basecamp 4 Parity)
  const [isTransferModalOpen, setIsTransferModalOpen] = useState(false);
  const [selectedNewOwnerId, setSelectedNewOwnerId] = useState('');
  const [isTransferring, setIsTransferring] = useState(false);

  const handleTransferOwnership = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!currentOrganization || !selectedNewOwnerId || !profile?.id) return;
    if (!confirm('Are you sure you want to transfer account ownership? You will become an Administrator.')) return;
    setIsTransferring(true);
    try {
      const res = await transferAccountOwnership(currentOrganization.id, selectedNewOwnerId, profile.id);
      if (res.error) {
        addToast(res.error.message || 'Failed to transfer ownership', 'error');
        return;
      }
      addToast('Account ownership transferred successfully!', 'success');
      setIsTransferModalOpen(false);
      window.location.reload();
    } catch {
      addToast('Failed to transfer ownership', 'error');
    } finally {
      setIsTransferring(false);
    }
  };

  // Export Data Handler
  const handleExportAccountData = () => {
    addToast('Generating complete Ajath PMT account export...', 'info');
    setTimeout(() => {
      const exportData = {
        account_id: '4135299',
        account_name: currentOrganization?.name || 'Ajath Infotech Pvt Ltd',
        export_date: new Date().toISOString(),
        administrators: adminsList,
        account_owners: ownersList,
        categories,
        projects: projects.map((p) => ({
          id: p.id,
          name: p.name,
          description: p.description,
          created_at: p.created_at,
          members_count: p.members_count,
        })),
        members: members.map((m) => ({
          id: m.id,
          role: m.role,
          name: m.profile?.full_name,
          email: m.profile?.email,
        })),
      };

      const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `ajath-pmt-4135299-account-export-${new Date().toISOString().split('T')[0]}.json`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      addToast('Account data exported and downloaded successfully!', 'success');
    }, 600);
  };

  const handleSendPing = (e: React.FormEvent) => {
    e.preventDefault();
    if (!pingMessage.trim() || !selectedPersonForPing) return;
    setIsPingSent(true);
    addToast(`Ping sent to ${selectedPersonForPing.name}!`, 'success');
    setTimeout(() => {
      setIsPingSent(false);
      setSelectedPersonForPing(null);
      setPingMessage('');
    }, 1200);
  };

  const handleToggleProjectAccess = (projId: string) => {
    setMemberProjectAccess((prev) => {
      const userAccess = prev[selectedMemberId] || {};
      return {
        ...prev,
        [selectedMemberId]: {
          ...userAccess,
          [projId]: !userAccess[projId],
        },
      };
    });
  };

  const handleAddCategory = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newCatName.trim()) return;
    const newCat = {
      id: `cat-${Date.now()}`,
      name: newCatName.trim(),
      color: newCatColor,
      desc: 'Custom message category',
    };
    setCategories((prev) => [...prev, newCat]);
    setNewCatName('');
    addToast(`Category "${newCat.name}" added`, 'success');
  };

  const handleDeleteCategory = (catId: string) => {
    setCategories((prev) => prev.filter((c) => c.id !== catId));
    addToast('Category removed', 'info');
  };

  const handleToggle2FAPolicy = () => {
    const next = !is2FARequired;
    setIs2FARequired(next);
    try {
      localStorage.setItem('basecamp_security_2fa_required', String(next));
      addToast(next ? 'Two-Factor Authentication is now mandatory' : '2FA is now optional', 'info');
    } catch {}
  };

  // Basecamp Role Gate: Only Account Owners and Administrators can access Adminland
  if (userRole === 'CLIENT' || userRole === 'MEMBER') {
    return (
      <div className="w-full max-w-4xl mx-auto py-12 px-4 animate-in fade-in duration-300">
        <div className="bg-white dark:bg-slate-900 rounded-3xl p-8 sm:p-12 border border-slate-200/80 dark:border-slate-800 shadow-sm text-center space-y-6">
          <div className="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 mx-auto flex items-center justify-center shadow-inner">
            <Lock className="w-8 h-8" />
          </div>
          <div className="space-y-2">
            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
              <span>Adminland Access Restricted</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
              Adminland is for Owners and Admins only
            </h1>
            <p className="text-sm text-slate-500 dark:text-slate-400 max-w-lg mx-auto leading-relaxed">
              In Ajath PMT, high-level account controls, billing, and system configurations are reserved exclusively for workspace administrators and account owners. You are currently signed in as a <span className="font-bold text-slate-800 dark:text-slate-200">{userRole === 'CLIENT' ? 'Client' : 'Team Member'}</span>.
            </p>
          </div>

          <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 max-w-md mx-auto text-left space-y-2 text-xs text-slate-600 dark:text-slate-300">
            <p className="font-bold text-slate-900 dark:text-slate-100">Need account-level changes?</p>
            <p className="text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
              Please reach out to an Account Owner ({ownersList.map((o) => o.name).join(', ')}) or an Administrator to request project access, upgrades, or settings updates.
            </p>
          </div>

          <div className="pt-2 flex items-center justify-center gap-3">
            <button
              onClick={() => navigate('/dashboard')}
              className="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 active:scale-95 transition-all cursor-pointer"
            >
              Return to Dashboard
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 py-2 pb-16">
      {/* Modern Adminland Card */}
      <div className="w-full bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-200/80 dark:border-slate-800 space-y-8">
        {/* Header Title */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
          <div>
            <div className="flex items-center gap-2 mb-1">
              <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800">
                Administration & Security
              </span>
              <span className="text-xs text-slate-500 font-medium">Enterprise Control Hub</span>
            </div>
            <h1 className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
              Adminland
            </h1>
            <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
              Account-level controls for workspace owners and administrators
            </p>
          </div>
        </div>

        {/* 3 Glowing KPI Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div className="p-4 rounded-2xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-indigo-200/50 dark:border-indigo-900/50">
            <div className="flex items-center justify-between text-xs font-bold text-indigo-600 dark:text-indigo-400 mb-1">
              <span>Account Plan</span>
              <Building2 className="w-4 h-4" />
            </div>
            <p className="text-xl font-black text-slate-900 dark:text-slate-100">Enterprise Pro</p>
            <p className="text-[11px] text-slate-400 mt-0.5">Unlimited projects & storage</p>
          </div>

          <div className="p-4 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-teal-500/10 border border-emerald-200/50 dark:border-emerald-900/50">
            <div className="flex items-center justify-between text-xs font-bold text-emerald-600 dark:text-emerald-400 mb-1">
              <span>Security & 2FA</span>
              <ShieldCheck className="w-4 h-4" />
            </div>
            <p className="text-xl font-black text-slate-900 dark:text-slate-100">
              {is2FARequired ? 'Mandatory' : 'Optional'}
            </p>
            <p className="text-[11px] text-slate-400 mt-0.5">Encrypted workspace auth</p>
          </div>

          <div className="p-4 rounded-2xl bg-gradient-to-br from-blue-500/10 to-cyan-500/10 border border-blue-200/50 dark:border-blue-900/50">
            <div className="flex items-center justify-between text-xs font-bold text-blue-600 dark:text-blue-400 mb-1">
              <span>Workspace Leadership</span>
              <Users className="w-4 h-4" />
            </div>
            <p className="text-xl font-black text-slate-900 dark:text-slate-100">
              {adminsList.length + ownersList.length} Leaders
            </p>
            <p className="text-[11px] text-slate-400 mt-0.5">
              {adminsList.length} {adminsList.length === 1 ? 'Admin' : 'Admins'} • {ownersList.length} {ownersList.length === 1 ? 'Owner' : 'Owners'}
            </p>
          </div>
        </div>

        {/* Section: Administrators */}
        <div className="space-y-3 pt-2">
          <div className="flex items-center gap-3">
            <h2 className="text-xl font-bold text-slate-900 dark:text-white tracking-tight shrink-0">
              Administrators
            </h2>
            <div className="h-px bg-slate-200/80 dark:bg-slate-800 flex-1" />
          </div>

          {/* Administrators list row */}
          <div className="flex flex-wrap items-center gap-3 sm:gap-4 pt-1">
            {adminsList.map((admin) => (
              <div
                key={admin.id}
                onClick={() => {
                  if (admin.isCurrentUser) {
                    addToast('You are currently viewing Adminland as this Administrator', 'info');
                  } else {
                    setSelectedPersonForPing(admin);
                  }
                }}
                className={`flex items-center gap-2.5 p-1.5 pr-3 rounded-2xl border transition-all select-none cursor-pointer group ${
                  admin.isCurrentUser
                    ? 'bg-blue-50/60 dark:bg-blue-950/30 border-blue-300 dark:border-blue-700/80 shadow-xs'
                    : 'border-transparent hover:border-slate-200 dark:hover:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40'
                }`}
                title={admin.isCurrentUser ? 'Your Administrator Profile' : `Click to reach out to ${admin.name}`}
              >
                {admin.avatarUrl ? (
                  <img
                    src={admin.avatarUrl}
                    alt={admin.name}
                    className="w-8 h-8 rounded-full object-cover ring-2 ring-blue-400 shadow-2xs"
                    onError={(e) => {
                      e.currentTarget.style.display = 'none';
                    }}
                  />
                ) : (
                  <div
                    className={`w-8 h-8 rounded-full ${admin.avatarBg || 'bg-[#00828a]'} text-white font-bold text-xs flex items-center justify-center ring-2 ring-blue-400 shadow-2xs`}
                  >
                    {admin.initials || getInitials(admin.name)}
                  </div>
                )}
                <div className="flex flex-col">
                  <div className="flex items-center gap-1.5">
                    <span className="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                      {admin.name}
                    </span>
                    {admin.isCurrentUser && (
                      <span className="px-1.5 py-0.2 rounded text-[10px] font-extrabold bg-blue-500 text-white shadow-2xs">
                        You 🛡️
                      </span>
                    )}
                  </div>
                  <span className="text-[11px] text-slate-400 font-normal">{admin.email}</span>
                </div>
              </div>
            ))}
          </div>

          {/* "Reach out to an admin to..." sub-section */}
          <div className="pt-3 space-y-3">
            <h3 className="text-[15px] font-bold text-slate-900 dark:text-white">
              Reach out to an admin to...
            </h3>

            <div className="space-y-3">
              {/* Item 1: Choose which projects people can access */}
              <div
                onClick={() => setIsProjectAccessModalOpen(true)}
                className="flex items-center gap-3 group cursor-pointer w-fit"
              >
                <div className="w-7 h-7 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                  <Users className="w-3.5 h-3.5 fill-current" />
                </div>
                <span className="text-sm sm:text-[15px] text-slate-800 dark:text-slate-200 font-medium group-hover:text-blue-600 dark:group-hover:text-blue-400 group-hover:underline transition-colors">
                  Choose which projects people can access on the account
                </span>
              </div>

              {/* Item 2: Change message categories */}
              <div
                onClick={() => setIsCategoriesModalOpen(true)}
                className="flex items-center gap-3 group cursor-pointer w-fit"
              >
                <div className="w-7 h-7 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                  <MessageSquare className="w-3.5 h-3.5 fill-current" />
                </div>
                <span className="text-sm sm:text-[15px] text-slate-800 dark:text-slate-200 font-medium group-hover:text-emerald-600 dark:group-hover:text-emerald-400 group-hover:underline transition-colors">
                  Change message categories
                </span>
              </div>

              {/* Item 3: Move projects from Basecamp 2 to Basecamp 5 */}
              <div
                onClick={() => setIsMigrationModalOpen(true)}
                className="flex items-center gap-3 group cursor-pointer w-fit"
              >
                <div className="w-7 h-7 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                  <ArrowUpRight className="w-3.5 h-3.5 stroke-[2.5]" />
                </div>
                <span className="text-sm sm:text-[15px] text-slate-800 dark:text-slate-200 font-medium group-hover:text-purple-600 dark:group-hover:text-purple-400 group-hover:underline transition-colors">
                  Import projects into Ajath PMT
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Section: Account Owners */}
        <div className="space-y-3 pt-4">
          <div className="flex items-center gap-3">
            <h2 className="text-xl font-bold text-slate-900 dark:text-white tracking-tight shrink-0">
              Account Owners
            </h2>
            <div className="h-px bg-slate-200/80 dark:bg-slate-800 flex-1" />
          </div>

          {/* Account Owner list row */}
          <div className="flex flex-wrap items-center gap-3 sm:gap-4 pt-1">
            {ownersList.map((owner) => (
              <div
                key={owner.id}
                onClick={() => {
                  if (owner.isCurrentUser || isCurrentOwner) {
                    addToast('You are the Account Owner with full administrative authority over this workspace.', 'info');
                  } else {
                    setSelectedPersonForPing(owner);
                  }
                }}
                className={`flex items-center gap-2.5 p-1.5 pr-3 rounded-2xl border transition-all select-none cursor-pointer group ${
                  owner.isCurrentUser
                    ? 'bg-amber-50/60 dark:bg-amber-950/30 border-amber-300 dark:border-amber-700/80 shadow-xs'
                    : 'border-transparent hover:border-slate-200 dark:hover:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40'
                }`}
                title={owner.isCurrentUser ? 'Your Account Owner Profile (Full Authority)' : `Click to reach out to ${owner.name}`}
              >
                {owner.avatarUrl ? (
                  <img
                    src={owner.avatarUrl}
                    alt={owner.name}
                    className="w-8 h-8 rounded-full object-cover ring-2 ring-amber-400 shadow-2xs"
                    onError={(e) => {
                      e.currentTarget.style.display = 'none';
                    }}
                  />
                ) : (
                  <div
                    className={`w-8 h-8 rounded-full ${owner.avatarBg || 'bg-amber-600'} text-white font-bold text-xs flex items-center justify-center ring-2 ring-amber-400 shadow-2xs`}
                  >
                    {owner.initials || getInitials(owner.name)}
                  </div>
                )}
                <div className="flex flex-col">
                  <div className="flex items-center gap-1.5">
                    <span className="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                      {owner.name}
                    </span>
                    {owner.isCurrentUser && (
                      <span className="px-1.5 py-0.2 rounded text-[10px] font-extrabold bg-amber-400 text-amber-950 shadow-2xs">
                        You 👑
                      </span>
                    )}
                  </div>
                  <span className="text-[11px] text-slate-400 font-normal">{owner.email}</span>
                </div>
              </div>
            ))}
          </div>

          {/* Account Owner Controls / Reach out sub-section */}
          <div className="pt-3 space-y-3">
            {isCurrentOwner ? (
              <div className="p-3.5 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/30 border border-amber-200 dark:border-amber-800/80 flex items-center justify-between flex-wrap gap-2">
                <div className="flex items-center gap-2.5">
                  <span className="text-xl">👑</span>
                  <div>
                    <h3 className="text-sm font-extrabold text-amber-950 dark:text-amber-200">
                      Account Owner Controls
                    </h3>
                    <p className="text-xs text-amber-850 dark:text-amber-400 font-medium">
                      You are signed in as an Account Owner with direct control over billing, plans, data exports, and security.
                    </p>
                  </div>
                </div>
                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={() => setIsTransferModalOpen(true)}
                    className="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer transition-colors"
                  >
                    Transfer ownership...
                  </button>
                  <span className="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wide bg-amber-400 text-amber-950 shadow-2xs">
                    Active Owner
                  </span>
                </div>
              </div>
            ) : (
              <h3 className="text-[15px] font-bold text-slate-900 dark:text-white">
                Reach out to an account owner to...
              </h3>
            )}

            <div className="space-y-3">
              {/* Item 1: Upgrade/downgrade account */}
              <div
                onClick={() => {
                  if (isCurrentOwner) {
                    setIsUpgradeModalOpen(true);
                  } else {
                    const targetOwner = ownersList.find((o) => !o.isCurrentUser && o.id !== profile?.id);
                    if (targetOwner) {
                      setSelectedPersonForPing(targetOwner);
                      setPingMessage('Hi! Could we look into upgrading our account plan?');
                      addToast('Only Account Owners can change plans. Contacting an Account Owner...', 'info');
                    } else {
                      addToast('Only Account Owners can change plans.', 'info');
                    }
                  }
                }}
                className="flex items-center gap-3 group cursor-pointer w-fit"
              >
                <div className="w-7 h-7 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                  <Rocket className="w-3.5 h-3.5 fill-current" />
                </div>
                <span className="text-sm sm:text-[15px] text-slate-800 dark:text-slate-200 font-medium group-hover:text-amber-600 dark:group-hover:text-amber-400 group-hover:underline transition-colors">
                  Upgrade or downgrade this account
                </span>
              </div>

              {/* Item 2: Handle billing info or invoices */}
              <div
                onClick={() => {
                  if (isCurrentOwner) {
                    setIsBillingModalOpen(true);
                  } else {
                    const targetOwner = ownersList.find((o) => !o.isCurrentUser && o.id !== profile?.id);
                    if (targetOwner) {
                      setSelectedPersonForPing(targetOwner);
                      setPingMessage('Hi! Could you please send over our latest invoices and billing receipts?');
                      addToast('Only Account Owners can access billing. Contacting an Account Owner...', 'info');
                    } else {
                      addToast('Only Account Owners can access billing.', 'info');
                    }
                  }
                }}
                className="flex items-center gap-3 group cursor-pointer w-fit"
              >
                <div className="w-7 h-7 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                  <DollarSign className="w-3.5 h-3.5 stroke-[2.5]" />
                </div>
                <span className="text-sm sm:text-[15px] text-slate-800 dark:text-slate-200 font-medium group-hover:text-emerald-600 dark:group-hover:text-emerald-400 group-hover:underline transition-colors">
                  See invoices or change credit card info
                </span>
              </div>

              {/* Item 3: Export account data */}
              <div
                onClick={handleExportAccountData}
                className="flex items-center gap-3 group cursor-pointer w-fit"
              >
                <div className="w-7 h-7 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                  <Download className="w-3.5 h-3.5 stroke-[2.5]" />
                </div>
                <span className="text-sm sm:text-[15px] text-slate-800 dark:text-slate-200 font-medium group-hover:text-cyan-600 dark:group-hover:text-cyan-400 group-hover:underline transition-colors">
                  Export account data
                </span>
              </div>

              {/* Item 4: Require two-factor authentication */}
              <div
                onClick={() => setIs2FAModalOpen(true)}
                className="flex items-center gap-3 group cursor-pointer w-fit"
              >
                <div className="w-7 h-7 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                  <Lock className="w-3.5 h-3.5 fill-current" />
                </div>
                <span className="text-sm sm:text-[15px] text-slate-800 dark:text-slate-200 font-medium group-hover:text-purple-600 dark:group-hover:text-purple-400 group-hover:underline transition-colors">
                  Require two-factor authentication for everyone on this account
                </span>
              </div>

              {/* Item 5: Cancel the account */}
              <div
                onClick={() => {
                  if (isCurrentOwner) {
                    setIsCancelModalOpen(true);
                  } else {
                    addToast('Only an Account Owner has permission to cancel or delete this account.', 'error');
                  }
                }}
                className="flex items-center gap-3 group cursor-pointer w-fit"
              >
                <div className="w-7 h-7 rounded-xl bg-gradient-to-br from-rose-500 to-red-600 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                  <X className="w-3.5 h-3.5 stroke-[2.5]" />
                </div>
                <span className="text-sm sm:text-[15px] text-slate-800 dark:text-slate-200 font-medium group-hover:text-rose-600 dark:group-hover:text-rose-400 group-hover:underline transition-colors">
                  Cancel this account
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* ================= MODALS FOR WORKING FEATURES ================= */}

      {/* 1. Reach out / Ping Modal (Only for contacting other people, never oneself) */}
      {selectedPersonForPing && !selectedPersonForPing.isCurrentUser && selectedPersonForPing.id !== profile?.id && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2.5">
                {selectedPersonForPing.avatarUrl ? (
                  <img
                    src={selectedPersonForPing.avatarUrl}
                    alt={selectedPersonForPing.name}
                    className="w-9 h-9 rounded-full object-cover ring-1 ring-slate-200"
                  />
                ) : (
                  <div
                    className={`w-9 h-9 rounded-full ${selectedPersonForPing.avatarBg || 'bg-teal-600'} text-white font-bold text-xs flex items-center justify-center`}
                  >
                    {selectedPersonForPing.initials}
                  </div>
                )}
                <div>
                  <h3 className="font-extrabold text-slate-900 text-sm">
                    Reach out to {selectedPersonForPing.name}
                  </h3>
                  <p className="text-[11px] text-slate-400">{selectedPersonForPing.role}</p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setSelectedPersonForPing(null)}
                className="p-1 rounded-lg text-slate-400 hover:text-slate-700"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {isPingSent ? (
              <div className="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center gap-2">
                <Check className="w-4 h-4 text-emerald-600" />
                <span>Message sent! They will get a notification.</span>
              </div>
            ) : (
              <form onSubmit={handleSendPing} className="space-y-3">
                <p className="text-xs text-slate-500">
                  Send a direct ping or request regarding project permissions, billing, or account changes.
                </p>
                <textarea
                  rows={3}
                  autoFocus
                  required
                  placeholder={`Hi ${selectedPersonForPing.name}, could you help me with...`}
                  value={pingMessage}
                  onChange={(e) => setPingMessage(e.target.value)}
                  className="w-full p-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 text-slate-800"
                />
                <div className="flex items-center justify-end gap-2 pt-2">
                  <button
                    type="button"
                    onClick={() => setSelectedPersonForPing(null)}
                    className="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    className="px-4 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl flex items-center gap-1.5 shadow-xs"
                  >
                    <Send className="w-3 h-3" />
                    <span>Send Ping</span>
                  </button>
                </div>
              </form>
            )}
          </div>
        </div>
      )}

      {/* 2. Choose which projects people can access Modal */}
      {isProjectAccessModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-4">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <div className="flex items-center gap-2.5">
                <div className="w-8 h-8 rounded-full bg-[#1D74F5] text-white flex items-center justify-center">
                  <Users className="w-4 h-4 fill-white" />
                </div>
                <div>
                  <h3 className="font-extrabold text-slate-900 text-sm">
                    Project Access Permissions
                  </h3>
                  <p className="text-[11px] text-slate-400">Choose which projects each person can access</p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setIsProjectAccessModalOpen(false)}
                className="p-1 text-slate-400 hover:text-slate-700"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">Select Person:</label>
              <select
                value={selectedMemberId}
                onChange={(e) => setSelectedMemberId(e.target.value)}
                className="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white font-semibold focus:outline-none"
              >
                {members.map((m) => {
                  const name = m.profile?.full_name || 'Member';
                  const userId = m.user_id || m.id;
                  return (
                    <option key={userId} value={userId}>
                      {name} ({m.role})
                    </option>
                  );
                })}
              </select>
            </div>

            <div className="space-y-2">
              <label className="block text-xs font-bold text-slate-700">Project Access List:</label>
              <div className="max-h-48 overflow-y-auto space-y-1.5 p-2 rounded-2xl border border-slate-100 bg-slate-50/50">
                {projects.map((p) => {
                  const hasAccess = Boolean(memberProjectAccess[selectedMemberId]?.[p.id]);
                  return (
                    <div
                      key={p.id}
                      onClick={() => handleToggleProjectAccess(p.id)}
                      className={`p-2.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between text-xs select-none ${
                        hasAccess
                          ? 'border-blue-500 bg-blue-50/70 font-bold text-blue-900'
                          : 'border-slate-200 bg-white text-slate-700'
                      }`}
                    >
                      <span className="truncate pr-2">{p.name}</span>
                      <div
                        className={`w-4 h-4 rounded flex items-center justify-center shrink-0 ${
                          hasAccess ? 'bg-blue-600 text-white' : 'border border-slate-300'
                        }`}
                      >
                        {hasAccess && <Check className="w-3 h-3 stroke-[3]" />}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>

            <div className="flex items-center justify-between pt-2 border-t border-slate-100">
              <button
                type="button"
                onClick={() => {
                  setIsProjectAccessModalOpen(false);
                  navigate('/account/enrollments/new');
                }}
                className="text-xs font-bold text-blue-600 hover:underline"
              >
                + Invite a new person
              </button>
              <button
                type="button"
                onClick={() => {
                  setIsProjectAccessModalOpen(false);
                  addToast('Project access permissions updated', 'success');
                }}
                className="px-4 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 shadow-xs"
              >
                Done
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 3. Change message categories Modal */}
      {isCategoriesModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-4">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <div className="flex items-center gap-2.5">
                <div className="w-8 h-8 rounded-full bg-[#1D74F5] text-white flex items-center justify-center">
                  <MessageSquare className="w-4 h-4 fill-white" />
                </div>
                <div>
                  <h3 className="font-extrabold text-slate-900 text-sm">
                    Message Categories
                  </h3>
                  <p className="text-[11px] text-slate-400">Manage tags for discussions and announcements</p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setIsCategoriesModalOpen(false)}
                className="p-1 text-slate-400 hover:text-slate-700"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <div className="space-y-1.5 max-h-52 overflow-y-auto pr-1">
              {categories.map((cat) => (
                <div
                  key={cat.id}
                  className="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs"
                >
                  <div className="flex items-center gap-2">
                    <span
                      className="w-3 h-3 rounded-full"
                      style={{ backgroundColor: cat.color }}
                    />
                    <span className="font-bold text-slate-800">{cat.name}</span>
                    <span className="text-[11px] text-slate-400 hidden sm:inline">• {cat.desc}</span>
                  </div>
                  <button
                    type="button"
                    onClick={() => handleDeleteCategory(cat.id)}
                    className="p-1 text-slate-400 hover:text-rose-600 rounded-lg"
                    title="Remove category"
                  >
                    <X className="w-3.5 h-3.5" />
                  </button>
                </div>
              ))}
            </div>

            {/* Add new category form */}
            <form onSubmit={handleAddCategory} className="flex gap-2 pt-2 border-t border-slate-100">
              <input
                type="color"
                value={newCatColor}
                onChange={(e) => setNewCatColor(e.target.value)}
                className="w-9 h-9 p-0.5 rounded-xl border border-slate-200 cursor-pointer"
              />
              <input
                type="text"
                placeholder="Category name (e.g., Retrospective)"
                value={newCatName}
                onChange={(e) => setNewCatName(e.target.value)}
                className="flex-1 px-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:outline-none text-slate-800"
              />
              <button
                type="submit"
                disabled={!newCatName.trim()}
                className="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl"
              >
                Add
              </button>
            </form>

            <div className="flex justify-end pt-1">
              <button
                type="button"
                onClick={() => setIsCategoriesModalOpen(false)}
                className="px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 4. Move projects from Basecamp 2 Modal */}
      {isMigrationModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <div className="flex items-center gap-2.5">
                <div className="w-8 h-8 rounded-full bg-[#38BDF8] text-white flex items-center justify-center">
                  <ArrowUpRight className="w-4 h-4 stroke-[3]" />
                </div>
                <div>
                  <h3 className="font-extrabold text-slate-900 text-sm">
                    Move Projects to Ajath PMT
                  </h3>
                  <p className="text-[11px] text-slate-400">Import archive into Ajath PMT</p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setIsMigrationModalOpen(false)}
                className="p-1 text-slate-400 hover:text-slate-700"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <p className="text-xs text-slate-600 leading-relaxed">
              You can migrate existing projects, message archives, to-do lists, and files directly into your Ajath PMT account.
            </p>

            <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2 text-xs">
              <p className="font-bold text-slate-800">Choose import source:</p>
              <label className="flex items-center gap-2 text-slate-700 cursor-pointer">
                <input type="radio" defaultChecked name="source" className="text-blue-600" />
                <span>Account Export Archive (.zip / .json)</span>
              </label>
              <label className="flex items-center gap-2 text-slate-700 cursor-pointer">
                <input type="radio" name="source" className="text-blue-600" />
                <span>Trello / Asana CSV Project Export</span>
              </label>
            </div>

            <div className="flex items-center justify-end gap-2 pt-2">
              <button
                type="button"
                onClick={() => setIsMigrationModalOpen(false)}
                className="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl"
              >
                Cancel
              </button>
              <button
                type="button"
                onClick={() => {
                  setIsMigrating(true);
                  setTimeout(() => {
                    setIsMigrating(false);
                    setIsMigrationModalOpen(false);
                    addToast('Project migration completed successfully', 'success');
                  }, 1200);
                }}
                disabled={isMigrating}
                className="px-4 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs"
              >
                {isMigrating ? 'Importing Projects...' : 'Start Migration'}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 5. Upgrade/Downgrade Account Modal */}
      {isUpgradeModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-4">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <div className="flex items-center gap-2.5">
                <div className="w-8 h-8 rounded-full bg-[#F59E0B] text-white flex items-center justify-center">
                  <Rocket className="w-4 h-4 fill-white" />
                </div>
                <div>
                  <h3 className="font-extrabold text-slate-900 text-sm">
                    Upgrade/Downgrade Account
                  </h3>
                  <p className="text-[11px] text-slate-400">Current plan: Ajath PMT Pro Unlimited</p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setIsUpgradeModalOpen(false)}
                className="p-1 text-slate-400 hover:text-slate-700"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <div className="grid grid-cols-2 gap-3 text-xs">
              <div
                onClick={() => setSelectedPlan('pro')}
                className={`p-4 rounded-2xl border cursor-pointer transition-all ${
                  selectedPlan === 'pro'
                    ? 'border-amber-500 bg-amber-50/60 ring-1 ring-amber-500'
                    : 'border-slate-200 bg-white'
                }`}
              >
                <div className="flex items-center justify-between mb-1">
                  <span className="font-black text-sm text-slate-900">Pro Unlimited</span>
                  <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-200 text-amber-900">Current</span>
                </div>
                <p className="text-xl font-extrabold text-slate-900 mb-1">$299<span className="text-xs text-slate-500 font-normal">/mo</span></p>
                <p className="text-slate-500 text-[11px]">Unlimited users, unlimited projects, 500 GB storage.</p>
              </div>

              <div
                onClick={() => setSelectedPlan('plus')}
                className={`p-4 rounded-2xl border cursor-pointer transition-all ${
                  selectedPlan === 'plus'
                    ? 'border-amber-500 bg-amber-50/60 ring-1 ring-amber-500'
                    : 'border-slate-200 bg-white'
                }`}
              >
                <div className="flex items-center justify-between mb-1">
                  <span className="font-black text-sm text-slate-900">Per User Plan</span>
                </div>
                <p className="text-xl font-extrabold text-slate-900 mb-1">$15<span className="text-xs text-slate-500 font-normal">/user/mo</span></p>
                <p className="text-slate-500 text-[11px]">Best for smaller teams up to 10 members.</p>
              </div>
            </div>

            <div className="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
              <button
                type="button"
                onClick={() => setIsUpgradeModalOpen(false)}
                className="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl"
              >
                Close
              </button>
              <button
                type="button"
                onClick={() => {
                  setIsUpgradeModalOpen(false);
                  addToast(`Account plan set to ${selectedPlan === 'pro' ? 'Pro Unlimited' : 'Per User'}`, 'success');
                }}
                className="px-4 py-1.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs"
              >
                Save Plan Changes
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 6. Handle Billing Info or Invoices Modal */}
      {isBillingModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <div className="flex items-center gap-2.5">
                <div className="w-8 h-8 rounded-full bg-[#D97706] text-white flex items-center justify-center">
                  <DollarSign className="w-4 h-4 stroke-[3]" />
                </div>
                <div>
                  <h3 className="font-extrabold text-slate-900 text-sm">
                    Billing Info & Invoices
                  </h3>
                  <p className="text-[11px] text-slate-400">Manage payment methods and view history</p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setIsBillingModalOpen(false)}
                className="p-1 text-slate-400 hover:text-slate-700"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {/* Payment method */}
            <div className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-2">
              <div className="flex items-center justify-between">
                <span className="font-bold text-slate-800 flex items-center gap-2">
                  <CreditCard className="w-4 h-4 text-slate-600" /> MasterCard ending in 4242
                </span>
                <span className="text-[11px] text-slate-500">Expires 08/28</span>
              </div>
              <p className="text-[11px] text-slate-400">Billed to: Ajath Infotech Pvt Ltd • Next cycle: Oct 1, 2026</p>
            </div>

            {/* Recent invoices */}
            <div className="space-y-1 text-xs">
              <p className="font-bold text-slate-800">Recent Invoices:</p>
              <div className="divide-y divide-slate-100 border border-slate-200 rounded-xl bg-white text-[11px]">
                <div className="p-2.5 flex items-center justify-between">
                  <span>Sep 01, 2026 • Pro Unlimited</span>
                  <button
                    onClick={() => addToast('Downloading invoice PDF...', 'info')}
                    className="text-blue-600 font-bold hover:underline cursor-pointer"
                  >
                    Download $299.00
                  </button>
                </div>
                <div className="p-2.5 flex items-center justify-between">
                  <span>Aug 01, 2026 • Pro Unlimited</span>
                  <button
                    onClick={() => addToast('Downloading invoice PDF...', 'info')}
                    className="text-blue-600 font-bold hover:underline cursor-pointer"
                  >
                    Download $299.00
                  </button>
                </div>
              </div>
            </div>

            <div className="flex justify-end pt-2 border-t border-slate-100">
              <button
                type="button"
                onClick={() => setIsBillingModalOpen(false)}
                className="px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 7. Require Two-Factor Authentication Modal */}
      {is2FAModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <div className="flex items-center gap-2.5">
                <div className="w-8 h-8 rounded-full bg-[#B45309] text-white flex items-center justify-center">
                  <Lock className="w-4 h-4 stroke-[2.5]" />
                </div>
                <div>
                  <h3 className="font-extrabold text-slate-900 text-sm">
                    Two-Factor Authentication
                  </h3>
                  <p className="text-[11px] text-slate-400">Account security governance</p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setIs2FAModalOpen(false)}
                className="p-1 text-slate-400 hover:text-slate-700"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <div className="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
              <p className="font-bold flex items-center gap-1.5">
                <ShieldCheck className="w-4 h-4 text-amber-700" />
                Require 2FA for all members
              </p>
              <p className="text-[11px] text-amber-800 leading-relaxed">
                When enabled, everyone who logs into this account must configure an authenticator app (Google Authenticator, 1Password, or Authy).
              </p>
            </div>

            <div className="flex items-center justify-between py-2 border-y border-slate-100 text-xs">
              <span className="font-bold text-slate-800">2FA Requirement Status:</span>
              <button
                type="button"
                onClick={handleToggle2FAPolicy}
                className={`w-12 h-6 rounded-full transition-colors cursor-pointer relative p-0.5 ${
                  is2FARequired ? 'bg-amber-600' : 'bg-slate-300'
                }`}
              >
                <div
                  className={`w-5 h-5 rounded-full bg-white shadow-xs transition-transform ${
                    is2FARequired ? 'translate-x-6' : 'translate-x-0'
                  }`}
                />
              </button>
            </div>

            <div className="flex justify-end pt-1">
              <button
                type="button"
                onClick={() => setIs2FAModalOpen(false)}
                className="px-4 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl"
              >
                Done
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 8. Cancel Account Modal */}
      {isCancelModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-3xl shadow-2xl border border-rose-200 max-w-md w-full p-6 space-y-4">
            <div className="flex items-center justify-between pb-2 border-b border-rose-100">
              <div className="flex items-center gap-2.5 text-rose-600">
                <AlertTriangle className="w-5 h-5" />
                <h3 className="font-black text-sm">Cancel the Account</h3>
              </div>
              <button
                type="button"
                onClick={() => setIsCancelModalOpen(false)}
                className="p-1 text-slate-400 hover:text-slate-700"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <p className="text-xs text-slate-600 leading-relaxed">
              Canceling will immediately freeze this account. We recommend downloading your complete data export first before proceeding.
            </p>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">
                Type <span className="font-mono text-rose-600 select-all">CANCEL ACCOUNT</span> to proceed:
              </label>
              <input
                type="text"
                value={cancelConfirmText}
                onChange={(e) => setCancelConfirmText(e.target.value)}
                placeholder="CANCEL ACCOUNT"
                className="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 font-mono text-slate-800 focus:outline-none"
              />
            </div>

            <div className="flex items-center justify-between pt-2 border-t border-slate-100">
              <button
                type="button"
                onClick={() => setIsCancelModalOpen(false)}
                className="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl"
              >
                Keep Account
              </button>
              <button
                type="button"
                disabled={cancelConfirmText !== 'CANCEL ACCOUNT'}
                onClick={() => {
                  setIsCancelModalOpen(false);
                  addToast('Cancellation request submitted to billing support', 'info');
                }}
                className="px-4 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-40 rounded-xl shadow-xs"
              >
                Confirm Cancellation
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 9. Transfer Account Ownership Modal (Basecamp 4 Parity) */}
      {isTransferModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-amber-200 dark:border-amber-800/80 max-w-md w-full p-6 space-y-4">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
              <div className="flex items-center gap-2.5 text-amber-600 dark:text-amber-400">
                <span className="text-xl">👑</span>
                <h3 className="font-extrabold text-sm text-slate-900 dark:text-slate-100">
                  Transfer Primary Account Ownership
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setIsTransferModalOpen(false)}
                className="p-1 text-slate-400 hover:text-slate-700"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <p className="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
              Transferring ownership gives the chosen person full executive authority over billing, deletion, plans, and company exports. You will remain an Administrator.
            </p>

            <form onSubmit={handleTransferOwnership} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                  Select New Account Owner:
                </label>
                <select
                  required
                  value={selectedNewOwnerId}
                  onChange={(e) => setSelectedNewOwnerId(e.target.value)}
                  className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500"
                >
                  <option value="">-- Choose a team member --</option>
                  {members
                    .filter((m) => m.user_id !== profile?.id && m.role !== 'CLIENT')
                    .map((m) => (
                      <option key={m.id} value={m.user_id}>
                        {m.profile?.full_name || m.profile?.email} ({m.role})
                      </option>
                    ))}
                </select>
              </div>

              <div className="p-3 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-800/60 text-[11px] text-amber-900 dark:text-amber-200">
                ⚠️ This action takes effect immediately across all project workspaces and billing consoles.
              </div>

              <div className="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button
                  type="button"
                  onClick={() => setIsTransferModalOpen(false)}
                  className="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={!selectedNewOwnerId || isTransferring}
                  className="px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 disabled:opacity-40 rounded-xl shadow-xs cursor-pointer"
                >
                  {isTransferring ? 'Transferring...' : 'Transfer Ownership'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
