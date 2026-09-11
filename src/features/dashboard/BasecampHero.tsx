import React from 'react';
import {
  Plus,
  FolderPlus,
  UserPlus,
  ShieldCheck,
  Palette,
  Search,
  Sparkles,
  Layers,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';

interface BasecampHeroProps {
  onOpenNewProject: () => void;
  onOpenAddFolder: () => void;
  onOpenInvite: () => void;
  onOpenAdminland: () => void;
  onOpenThemeCustomizer: () => void;
  onOpenJumpMenu: () => void;
}

export function BasecampHero({
  onOpenNewProject,
  onOpenAddFolder,
  onOpenInvite,
  onOpenAdminland,
  onOpenThemeCustomizer,
  onOpenJumpMenu,
}: BasecampHeroProps) {
  const { profile, userRole } = useAuth();
  const { currentOrganization } = useOrganization();

  // Current formatted date
  const todayString = new Intl.DateTimeFormat('en-US', {
    weekday: 'long',
    month: 'short',
    day: 'numeric',
  }).format(new Date());

  const userName = profile?.full_name?.split(' ')[0] || 'Team';

  return (
    <div className="pt-4 pb-2 space-y-6 animate-in fade-in duration-300">
      {/* Modern Greeting & Search Top Card */}
      <div className="bg-gradient-to-r from-white via-slate-50 to-white dark:from-slate-900 dark:via-slate-900/80 dark:to-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        {/* Left: Personalized Workspace Greeting */}
        <div className="space-y-1.5">
          <div className="flex items-center gap-2">
            <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/60">
              <span className="w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400 animate-pulse" />
              {todayString}
            </span>
            <span className="text-xs text-slate-400 dark:text-slate-500 font-medium">
              • {currentOrganization?.name || 'Ajath Infotech'}
            </span>
          </div>

          <h1 className="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
            Welcome back, {userName} 👋
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-normal">
            Track projects, communicate with your team, and organize deliverables effortlessly.
          </p>
        </div>

        {/* Right: Quick Jump Search Bar */}
        <div className="w-full md:w-80">
          <button
            onClick={onOpenJumpMenu}
            className="w-full flex items-center justify-between px-4 py-2.5 rounded-2xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400 shadow-xs hover:border-slate-300 dark:hover:border-slate-600 transition-all cursor-pointer text-left group"
          >
            <div className="flex items-center gap-2.5">
              <Search className="w-4 h-4 text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
              <span className="font-medium text-slate-600 dark:text-slate-300">Quick jump or search...</span>
            </div>
            <div className="flex items-center gap-1">
              <kbd className="px-1.5 py-0.5 text-[10px] font-bold bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-slate-500 shadow-2xs">
                ⌘K
              </kbd>
            </div>
          </button>
        </div>
      </div>

      {/* Modern Quick Action Toolbar */}
      <div className="flex flex-wrap items-center justify-between gap-3 pt-1">
        <div className="flex items-center gap-2">
          <div className="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
            <Layers className="w-4 h-4" />
          </div>
          <div>
            <h2 className="text-base font-bold text-slate-900 dark:text-slate-100 leading-tight">
              Workspaces & Projects
            </h2>
            <p className="text-xs text-slate-500 dark:text-slate-400">
              Active team hubs, client projects, and folders
            </p>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex flex-wrap items-center gap-2">
          {/* Primary: New Project (Team members & Admins/Owners only) */}
          {userRole !== 'CLIENT' && (
            <button
              onClick={onOpenNewProject}
              className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-xs hover:shadow-md hover:shadow-blue-500/10 transition-all cursor-pointer active:scale-95"
            >
              <Plus className="w-4 h-4 stroke-[2.5]" />
              <span>New Project</span>
            </button>
          )}

          {/* New Folder (Internal team only) */}
          {userRole !== 'CLIENT' && (
            <button
              onClick={onOpenAddFolder}
              className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 border border-slate-200/80 dark:border-slate-700/80 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-xs transition-all cursor-pointer"
            >
              <FolderPlus className="w-4 h-4 text-amber-500" />
              <span>Add Folder</span>
            </button>
          )}

          {/* Invite (Internal team only) */}
          {userRole !== 'CLIENT' && (
            <button
              onClick={onOpenInvite}
              className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 border border-slate-200/80 dark:border-slate-700/80 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-xs transition-all cursor-pointer"
            >
              <UserPlus className="w-4 h-4 text-emerald-500" />
              <span>Invite People</span>
            </button>
          )}

          {/* Adminland (Owner & Admin only) */}
          {['OWNER', 'ADMIN'].includes(userRole || '') && (
            <button
              onClick={onOpenAdminland}
              className="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 border border-slate-200/80 dark:border-slate-700/80 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-xs transition-all cursor-pointer"
            >
              <ShieldCheck className="w-4 h-4 text-purple-500" />
              <span>Adminland</span>
            </button>
          )}

          {/* Theme / Appearance */}
          <button
            onClick={onOpenThemeCustomizer}
            className="p-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 border border-slate-200/80 dark:border-slate-700/80 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white shadow-xs transition-all cursor-pointer"
            title="Customize workspace theme"
          >
            <Palette className="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  );
}
