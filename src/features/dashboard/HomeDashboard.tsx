import React, { useState, useEffect, useMemo } from 'react';
import { useNavigate, useOutletContext, useSearchParams } from 'react-router-dom';
import { useProject } from '../../context/ProjectContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { CreateProjectModal } from '../../components/projects/CreateProjectModal';
import { InviteMemberModal } from '../../components/members/InviteMemberModal';
import { AdminlandModal } from '../../components/modals/AdminlandModal';
import { AddFolderModal } from '../../components/modals/AddFolderModal';
import { FolderDetailModal } from '../../components/modals/FolderDetailModal';
import { ThemeCustomizerModal } from '../../components/modals/ThemeCustomizerModal';
import { OnboardingCompanyModal } from '../../components/modals/OnboardingCompanyModal';
import { CreateTeamModal } from '../teams/CreateTeamModal';
import { Project, ActivityLogItem, CalendarEvent, GlobalSearchResult } from '../../types';
import { getTotalOpenTasksCount } from '../../services/taskService';
import { getCompanyMilestonesHealth, getCalendarEvents } from '../../services/calendarService';
import { getOrganizationActivityLogs } from '../../services/organizationService';
import { globalSearch } from '../../services/searchService';
import { getInitials } from '../../lib/utils';
import {
  Search,
  Plus,
  ArrowRight,
  FolderPlus,
  UserPlus,
  ShieldCheck,
  Palette,
  Star,
  Folder,
  TrendingUp,
  CheckCircle2,
  Users,
  Layers,
  Building2,
  Sparkles,
  Calendar as CalendarIcon,
  PieChart,
  Globe,
  MessageSquare,
  Clock,
  Filter,
  CheckSquare,
  FileText,
} from 'lucide-react';

interface HomeDashboardProps {
  onOpenCommandMenu?: () => void;
}

interface OutletContextType {
  openJumpMenu?: () => void;
  openDockDrawer?: (type: 'tasks' | 'events' | 'bookmarks' | 'activity' | 'notes') => void;
  openSupport?: () => void;
  openProfile?: () => void;
  openPings?: () => void;
  openHey?: () => void;
}

// 6 Curated Vibrant Color Themes for Project Cards
const CARD_THEMES = [
  {
    gradient: 'from-blue-600 to-indigo-600',
    borderHover: 'hover:border-indigo-400 dark:hover:border-indigo-500',
    accentText: 'text-indigo-600 dark:text-indigo-400',
    accentBg: 'bg-indigo-50 dark:bg-indigo-950/60',
    badgeBg: 'bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border-blue-200/80 dark:border-blue-800/80',
    progressBar: 'from-blue-500 to-indigo-600',
    shadowGlow: 'hover:shadow-indigo-500/10',
  },
  {
    gradient: 'from-emerald-500 to-teal-600',
    borderHover: 'hover:border-emerald-400 dark:hover:border-emerald-500',
    accentText: 'text-emerald-600 dark:text-emerald-400',
    accentBg: 'bg-emerald-50 dark:bg-emerald-950/60',
    badgeBg: 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/80',
    progressBar: 'from-emerald-500 to-teal-600',
    shadowGlow: 'hover:shadow-emerald-500/10',
  },
  {
    gradient: 'from-purple-600 to-pink-600',
    borderHover: 'hover:border-purple-400 dark:hover:border-purple-500',
    accentText: 'text-purple-600 dark:text-purple-400',
    accentBg: 'bg-purple-50 dark:bg-purple-950/60',
    badgeBg: 'bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/80',
    progressBar: 'from-purple-600 to-pink-600',
    shadowGlow: 'hover:shadow-purple-500/10',
  },
  {
    gradient: 'from-amber-500 to-orange-600',
    borderHover: 'hover:border-amber-400 dark:hover:border-amber-500',
    accentText: 'text-amber-600 dark:text-amber-400',
    accentBg: 'bg-amber-50 dark:bg-amber-950/60',
    badgeBg: 'bg-amber-50 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/80',
    progressBar: 'from-amber-500 to-orange-600',
    shadowGlow: 'hover:shadow-amber-500/10',
  },
  {
    gradient: 'from-rose-500 to-red-600',
    borderHover: 'hover:border-rose-400 dark:hover:border-rose-500',
    accentText: 'text-rose-600 dark:text-rose-400',
    accentBg: 'bg-rose-50 dark:bg-rose-950/60',
    badgeBg: 'bg-rose-50 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border-rose-200/80 dark:border-rose-800/80',
    progressBar: 'from-rose-500 to-red-600',
    shadowGlow: 'hover:shadow-rose-500/10',
  },
  {
    gradient: 'from-cyan-500 to-blue-600',
    borderHover: 'hover:border-cyan-400 dark:hover:border-cyan-500',
    accentText: 'text-cyan-600 dark:text-cyan-400',
    accentBg: 'bg-cyan-50 dark:bg-cyan-950/60',
    badgeBg: 'bg-cyan-50 dark:bg-cyan-950/70 text-cyan-700 dark:text-cyan-300 border-cyan-200/80 dark:border-cyan-800/80',
    progressBar: 'from-cyan-500 to-blue-600',
    shadowGlow: 'hover:shadow-cyan-500/10',
  },
];

export function HomeDashboard({ onOpenCommandMenu }: HomeDashboardProps) {
  const navigate = useNavigate();
  const outletContext = useOutletContext<OutletContextType>();
  const { projects, refreshProjects } = useProject();
  const { currentOrganization, organizations, members: orgMembers, teams, loading: orgLoading } = useOrganization();
  const { profile, userRole } = useAuth();
  const { addToast } = useToast();

  const [openTasksCount, setOpenTasksCount] = useState<number>(0);
  const [milestoneHealth, setMilestoneHealth] = useState<string>('100% on track');
  const [isOnboardingOpen, setIsOnboardingOpen] = useState(false);

  useEffect(() => {
    refreshProjects();
    document.title = 'Projects | Ajath PMT';
  }, [refreshProjects]);

  useEffect(() => {
    let isMounted = true;
    const projIds = projects.map((p) => p.id);
    getTotalOpenTasksCount(currentOrganization?.id, projIds).then((count) => {
      if (isMounted) setOpenTasksCount(count);
    });
    getCompanyMilestonesHealth(currentOrganization?.id, projIds).then((health) => {
      if (isMounted) setMilestoneHealth(health.label);
    });
    return () => {
      isMounted = false;
    };
  }, [currentOrganization?.id, projects]);

  // Modals state
  const [isNewProjectOpen, setIsNewProjectOpen] = useState(false);
  const [isCreateTeamOpen, setIsCreateTeamOpen] = useState(false);
  const [isAddFolderOpen, setIsAddFolderOpen] = useState(false);
  const [selectedFolderForDetail, setSelectedFolderForDetail] = useState<string | null>(null);
  const [isInviteOpen, setIsInviteOpen] = useState(false);
  const [isAdminlandOpen, setIsAdminlandOpen] = useState(false);
  const [isThemeCustomizerOpen, setIsThemeCustomizerOpen] = useState(false);
  const [searchFilter, setSearchFilter] = useState('');
  const [activeTab, setActiveTab] = useState<'all' | 'starred' | 'folders'>('all');

  // Category Switcher State (Projects, Activity, Calendar, Reports, Everything)
  const [searchParams, setSearchParams] = useSearchParams();
  const categoryParam = searchParams.get('category') || searchParams.get('tab');
  const [selectedCategory, setSelectedCategory] = useState<'projects' | 'activity' | 'calendar' | 'reports' | 'everything'>(() => {
    if (categoryParam && ['projects', 'activity', 'calendar', 'reports', 'everything'].includes(categoryParam)) {
      return categoryParam as any;
    }
    return 'projects';
  });

  useEffect(() => {
    if (categoryParam && ['projects', 'activity', 'calendar', 'reports', 'everything'].includes(categoryParam)) {
      setSelectedCategory(categoryParam as any);
    }
  }, [categoryParam]);

  const handleSelectCategory = (cat: 'projects' | 'activity' | 'calendar' | 'reports' | 'everything') => {
    setSelectedCategory(cat);
    const newParams = new URLSearchParams(searchParams);
    if (cat === 'projects') {
      newParams.delete('category');
      newParams.delete('tab');
    } else {
      newParams.set('category', cat);
    }
    setSearchParams(newParams, { replace: true });
  };

  // Activity Category Data
  const [companyActivities, setCompanyActivities] = useState<ActivityLogItem[]>([]);
  const [loadingActivities, setLoadingActivities] = useState(false);

  useEffect(() => {
    if (selectedCategory === 'activity' && currentOrganization?.id) {
      setLoadingActivities(true);
      getOrganizationActivityLogs(currentOrganization.id)
        .then((logs) => setCompanyActivities(logs))
        .catch(() => setCompanyActivities([]))
        .finally(() => setLoadingActivities(false));
    }
  }, [selectedCategory, currentOrganization?.id]);

  // Calendar Category Data
  const [companyEvents, setCompanyEvents] = useState<CalendarEvent[]>([]);
  const [loadingEvents, setLoadingEvents] = useState(false);

  useEffect(() => {
    if (selectedCategory === 'calendar') {
      setLoadingEvents(true);
      Promise.all(projects.map((p) => getCalendarEvents(p.id).catch(() => [])))
        .then((results) => {
          const all = results.flat();
          all.sort((a, b) => new Date(a.start_at).getTime() - new Date(b.start_at).getTime());
          setCompanyEvents(all);
        })
        .finally(() => setLoadingEvents(false));
    }
  }, [selectedCategory, projects]);

  // Everything Category Data
  const [everythingQuery, setEverythingQuery] = useState('');
  const [everythingResults, setEverythingResults] = useState<GlobalSearchResult[]>([]);
  const [loadingEverything, setLoadingEverything] = useState(false);

  useEffect(() => {
    if (selectedCategory === 'everything') {
      if (!everythingQuery.trim()) {
        setEverythingResults(projects.map((p) => ({
          id: p.id,
          type: 'project' as const,
          title: p.name,
          subtitle: p.description || 'Project Workspace',
          url: `/projects/${p.id}`,
          badge: p.status || 'Project',
        })));
        return;
      }
      setLoadingEverything(true);
      const timer = setTimeout(() => {
        globalSearch(everythingQuery, currentOrganization?.id || 'demo-org-acme', undefined, profile?.id, userRole)
          .then((res) => setEverythingResults(res))
          .catch(() => setEverythingResults([]))
          .finally(() => setLoadingEverything(false));
      }, 200);
      return () => clearTimeout(timer);
    }
  }, [selectedCategory, everythingQuery, currentOrganization?.id, profile?.id, userRole, projects]);

  // Starred projects persistence
  const [starredIds, setStarredIds] = useState<Set<string>>(() => {
    try {
      const saved = localStorage.getItem('basecamp_starred_projects');
      return saved ? new Set(JSON.parse(saved)) : new Set<string>();
    } catch (e) {
      return new Set<string>();
    }
  });

  // Custom Folders persistence
  const [folders, setFolders] = useState<string[]>(() => {
    try {
      const saved = localStorage.getItem('basecamp_custom_folders');
      return saved ? JSON.parse(saved) : [];
    } catch (e) {
      return [];
    }
  });

  const handleToggleStar = (projectId: string, e: React.MouseEvent) => {
    e.stopPropagation();
    setStarredIds((prev) => {
      const next = new Set(prev);
      if (next.has(projectId)) {
        next.delete(projectId);
      } else {
        next.add(projectId);
      }
      try {
        localStorage.setItem('basecamp_starred_projects', JSON.stringify(Array.from(next)));
      } catch (err) {}
      return next;
    });
  };

  const handleCreateFolder = (folderName: string, initialProjectIds?: string[]) => {
    setFolders((prev) => {
      const next = [folderName, ...prev.filter((f) => f !== folderName)];
      try {
        localStorage.setItem('basecamp_custom_folders', JSON.stringify(next));
        if (initialProjectIds && initialProjectIds.length > 0) {
          localStorage.setItem(`basecamp_folder_${folderName}`, JSON.stringify(initialProjectIds));
        }
      } catch (err) {}
      return next;
    });
  };

  const handleAddFolderDirectly = () => {
    let baseName = 'New Collection';
    let candidate = baseName;
    let counter = 2;
    while (folders.includes(candidate)) {
      candidate = `${baseName} ${counter}`;
      counter++;
    }
    handleCreateFolder(candidate);
    addToast(`Folder "${candidate}" added to your workspace`, 'success');
  };

  const handleRenameFolder = (oldName: string, newName: string) => {
    if (!newName.trim() || oldName === newName) return;
    setFolders((prev) => {
      const next = prev.map((f) => (f === oldName ? newName.trim() : f));
      try {
        localStorage.setItem('basecamp_custom_folders', JSON.stringify(next));
        const existingProjects = localStorage.getItem(`basecamp_folder_${oldName}`);
        if (existingProjects) {
          localStorage.setItem(`basecamp_folder_${newName.trim()}`, existingProjects);
          localStorage.removeItem(`basecamp_folder_${oldName}`);
        }
      } catch (err) {}
      return next;
    });
    setSelectedFolderForDetail(newName.trim());
    addToast(`Folder renamed to "${newName.trim()}"`, 'info');
  };

  const handleDeleteFolder = (folderName: string) => {
    setFolders((prev) => {
      const next = prev.filter((f) => f !== folderName);
      try {
        localStorage.setItem('basecamp_custom_folders', JSON.stringify(next));
        localStorage.removeItem(`basecamp_folder_${folderName}`);
      } catch (err) {}
      return next;
    });
    addToast(`Folder "${folderName}" deleted`, 'info');
  };

  const handleOpenJump = () => {
    if (onOpenCommandMenu) {
      onOpenCommandMenu();
    } else if (outletContext?.openJumpMenu) {
      outletContext.openJumpMenu();
    } else {
      window.dispatchEvent(new KeyboardEvent('keydown', { key: 'k', ctrlKey: true }));
    }
  };

  // Filter only active projects for the Home Dashboard grid (Basecamp parity: archived/trashed never shown on home grid)
  const activeProjects = useMemo(() => {
    return projects.filter((p) => (p.status || 'ACTIVE') === 'ACTIVE');
  }, [projects]);

  const displayedProjects = useMemo(() => {
    return activeProjects.filter((p) => {
      if (activeTab === 'starred' && !starredIds.has(p.id)) return false;
      if (!searchFilter.trim()) return true;
      const q = searchFilter.toLowerCase();
      return p.name.toLowerCase().includes(q) || (p.description && p.description.toLowerCase().includes(q));
    });
  }, [activeProjects, activeTab, starredIds, searchFilter]);

  const userName = profile?.full_name?.split(' ')[0] || 'Team';
  const todayString = new Intl.DateTimeFormat('en-US', {
    weekday: 'long',
    month: 'short',
    day: 'numeric',
  }).format(new Date());

  return (
    <div className="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-7 pb-16 animate-in fade-in duration-300">
      {/* ================= 1. VIBRANT HERO COMMAND BANNER ================= */}
      <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-800 text-white p-6 sm:p-8 shadow-xl shadow-indigo-900/10">
        {/* Glow decorative orbs */}
        <div className="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 rounded-full bg-pink-500/20 blur-3xl pointer-events-none" />
        <div className="absolute bottom-0 left-1/3 -mb-20 w-80 h-80 rounded-full bg-cyan-400/20 blur-3xl pointer-events-none" />

        <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          {/* Greeting */}
          <div className="space-y-1.5">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-white text-[11px] font-bold border border-white/20">
              <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
              <span>{todayString}</span>
              <span className="text-white/70">• {currentOrganization?.name || 'Workspace'}</span>
            </div>

            <h1 className="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white">
              Welcome back, {userName} ✨
            </h1>
            <p className="text-xs sm:text-sm text-blue-100 max-w-xl font-normal">
              Organize sprints, monitor deliverable milestones, and coordinate seamlessly across projects.
            </p>
          </div>

          {/* Action Toolbar */}
          <div className="flex flex-wrap items-center gap-2 sm:gap-2.5">
            {['OWNER', 'ADMIN'].includes(userRole) && (!currentOrganization || organizations.length === 0) && (
              <button
                onClick={() => setIsOnboardingOpen(true)}
                className="flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 text-white font-bold text-xs backdrop-blur-md border border-white/30 shadow-xs transition-all cursor-pointer hover:scale-105 active:scale-95"
                title="Create your company workspace"
              >
                <Building2 className="w-4 h-4 text-cyan-300" />
                <span>+ Create Company</span>
              </button>
            )}

            {userRole !== 'CLIENT' && (
              <button
                onClick={() => navigate('/projects/new')}
                className="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-indigo-700 hover:bg-blue-50 font-bold text-xs shadow-md hover:shadow-lg transition-all cursor-pointer active:scale-95"
              >
                <Plus className="w-4 h-4 stroke-[3]" />
                <span>New Project</span>
              </button>
            )}

            {userRole !== 'CLIENT' && (
              <button
                onClick={handleAddFolderDirectly}
                className="flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-semibold text-xs backdrop-blur-md border border-white/20 transition-all cursor-pointer"
              >
                <FolderPlus className="w-4 h-4 text-amber-300" />
                <span>Add Folder</span>
              </button>
            )}

            {userRole !== 'CLIENT' && (
              <button
                onClick={() => setIsInviteOpen(true)}
                className="flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-semibold text-xs backdrop-blur-md border border-white/20 transition-all cursor-pointer"
                title="Invite people to this company"
              >
                <UserPlus className="w-4 h-4 text-emerald-300" />
                <span>Invite</span>
              </button>
            )}

            {['OWNER', 'ADMIN'].includes(userRole) && (
              <button
                onClick={() => navigate('/adminland')}
                className="flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-semibold text-xs backdrop-blur-md border border-white/20 transition-all cursor-pointer"
              >
                <ShieldCheck className="w-4 h-4 text-purple-300" />
                <span>Adminland</span>
              </button>
            )}

            <button
              onClick={() => setIsThemeCustomizerOpen(true)}
              className="p-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white backdrop-blur-md border border-white/20 transition-all cursor-pointer"
              title="Appearance Settings"
            >
              <Palette className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      {/* ================= 2. VIBRANT KPI METRIC CARDS ================= */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        {/* Metric 1 */}
        <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:shadow-md transition-all group flex items-center gap-3.5">
          <div className="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform shrink-0">
            <Layers className="w-5 h-5" />
          </div>
          <div className="min-w-0">
            <p className="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider truncate">
              Active Projects
            </p>
            <p className="text-xl font-black text-slate-900 dark:text-white leading-tight">
              {activeProjects.length}
            </p>
          </div>
        </div>

        {/* Metric 2 */}
        <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:shadow-md transition-all group flex items-center gap-3.5">
          <div className="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform shrink-0">
            <CheckCircle2 className="w-5 h-5" />
          </div>
          <div className="min-w-0">
            <p className="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider truncate">
              Open Tasks
            </p>
            <p className="text-xl font-black text-slate-900 dark:text-white leading-tight">
              {openTasksCount} Pending
            </p>
          </div>
        </div>

        {/* Metric 3 */}
        <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:shadow-md transition-all group flex items-center gap-3.5">
          <div className="w-11 h-11 rounded-2xl bg-gradient-to-tr from-purple-600 to-pink-600 text-white flex items-center justify-center shadow-md shadow-purple-500/20 group-hover:scale-105 transition-transform shrink-0">
            <Users className="w-5 h-5" />
          </div>
          <div className="min-w-0">
            <p className="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider truncate">
              Team Members
            </p>
            <p className="text-xl font-black text-slate-900 dark:text-white leading-tight">
              {orgMembers.length} Active
            </p>
          </div>
        </div>

        {/* Metric 4 */}
        <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:shadow-md transition-all group flex items-center gap-3.5">
          <div className="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-md shadow-amber-500/20 group-hover:scale-105 transition-transform shrink-0">
            <TrendingUp className="w-5 h-5" />
          </div>
          <div className="min-w-0">
            <p className="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider truncate">
              Milestones
            </p>
            <p className="text-xl font-black text-slate-900 dark:text-white leading-tight">
              {milestoneHealth}
            </p>
          </div>
        </div>
      </div>

      {/* ================= 2.2. ADMIN & OWNER WORKSPACE LAUNCHPAD ================= */}
      {['OWNER', 'ADMIN'].includes(userRole) && (
        <div className="p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-indigo-100 dark:border-indigo-950/80 shadow-xs relative overflow-hidden">
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-200/50 dark:border-indigo-800/50">
                <Sparkles className="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
              </div>
              <div>
                <h2 className="text-base font-black text-slate-900 dark:text-white tracking-tight">
                  Company Setup & Team Architecture
                </h2>
                <p className="text-xs text-slate-500 dark:text-slate-400">
                  Configure your company workspace, departments, team members, and project assignments.
                </p>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <span className="text-[11px] font-bold px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60 flex items-center gap-1.5">
                <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
                Active: {currentOrganization?.name || 'Workspace'}
              </span>
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 pt-4">
            {/* Step 1: Company */}
            <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 flex flex-col justify-between space-y-3">
              <div className="flex items-start justify-between">
                <div className="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-950/80 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                  1
                </div>
                <Building2 className="w-4 h-4 text-blue-500" />
              </div>
              <div>
                <h3 className="font-extrabold text-sm text-slate-900 dark:text-white">Company Workspace</h3>
                <p className="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                  {currentOrganization?.name || 'Create your organization'}
                </p>
              </div>
              {(!currentOrganization || organizations.length === 0) ? (
                <button
                  onClick={() => setIsOnboardingOpen(true)}
                  className="w-full py-2 px-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-400 text-blue-600 dark:text-blue-400 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer hover:shadow-xs"
                >
                  <Plus className="w-3.5 h-3.5" />
                  <span>Create Company</span>
                </button>
              ) : (
                <button
                  onClick={() => navigate('/settings/organization')}
                  className="w-full py-2 px-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-emerald-400 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer hover:shadow-xs"
                  title="Manage company details in settings"
                >
                  <CheckCircle2 className="w-3.5 h-3.5 text-emerald-500" />
                  <span>Workspace Set Up</span>
                </button>
              )}
            </div>

            {/* Step 2: Teams */}
            <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 flex flex-col justify-between space-y-3">
              <div className="flex items-start justify-between">
                <div className="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">
                  2
                </div>
                <Users className="w-4 h-4 text-indigo-500" />
              </div>
              <div>
                <h3 className="font-extrabold text-sm text-slate-900 dark:text-white">Make Teams</h3>
                <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                  {teams?.length || 0} {teams?.length === 1 ? 'team' : 'teams'} established
                </p>
              </div>
              <button
                onClick={() => setIsCreateTeamOpen(true)}
                className="w-full py-2 px-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-indigo-400 text-indigo-600 dark:text-indigo-400 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer hover:shadow-xs"
              >
                <Plus className="w-3.5 h-3.5" />
                <span>Create Team</span>
              </button>
            </div>

            {/* Step 3: People */}
            <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 flex flex-col justify-between space-y-3">
              <div className="flex items-start justify-between">
                <div className="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                  3
                </div>
                <UserPlus className="w-4 h-4 text-emerald-500" />
              </div>
              <div>
                <h3 className="font-extrabold text-sm text-slate-900 dark:text-white">Add People</h3>
                <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                  {orgMembers?.length || 0} {orgMembers?.length === 1 ? 'person' : 'people'} in company
                </p>
              </div>
              <button
                onClick={() => setIsInviteOpen(true)}
                className="w-full py-2 px-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-emerald-400 text-emerald-600 dark:text-emerald-400 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer hover:shadow-xs"
              >
                <Plus className="w-3.5 h-3.5" />
                <span>Invite People</span>
              </button>
            </div>

            {/* Step 4: Projects */}
            <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 flex flex-col justify-between space-y-3">
              <div className="flex items-start justify-between">
                <div className="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">
                  4
                </div>
                <Layers className="w-4 h-4 text-purple-500" />
              </div>
              <div>
                <h3 className="font-extrabold text-sm text-slate-900 dark:text-white">Create Projects</h3>
                <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                  {activeProjects.length} {activeProjects.length === 1 ? 'project' : 'projects'} active
                </p>
              </div>
              <button
                onClick={() => navigate('/projects/new')}
                className="w-full py-2 px-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-xs"
              >
                <Plus className="w-3.5 h-3.5" />
                <span>New Project</span>
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ================= 2.5. BASECAMP TEAMS (INTERNAL EMPLOYEES ONLY) ================= */}
      {userRole !== 'CLIENT' && teams && teams.length > 0 ? (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <Users className="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
              <h2 className="text-lg font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                Teams
              </h2>
              <span className="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                {teams.length}
              </span>
            </div>
            <div className="flex items-center gap-2">
              {['OWNER', 'ADMIN'].includes(userRole) && (
                <button
                  onClick={() => setIsCreateTeamOpen(true)}
                  className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900 text-indigo-600 dark:text-indigo-400 text-xs font-bold transition-colors cursor-pointer border border-indigo-200/60 dark:border-indigo-800/60"
                >
                  <Plus className="w-3.5 h-3.5" />
                  <span>New Team</span>
                </button>
              )}
              <button
                onClick={() => navigate('/teams')}
                className="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 cursor-pointer ml-1"
              >
                <span>View All Teams</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {teams.map((team) => (
              <div
                key={team.id}
                onClick={() => navigate(`/teams/${team.id}`)}
                className="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 shadow-xs hover:shadow-md transition-all cursor-pointer group flex flex-col justify-between space-y-3"
              >
                <div className="flex items-start gap-3.5">
                  <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0 overflow-hidden">
                    {team.avatar_url ? (
                      <img src={team.avatar_url} alt={team.name} className="w-full h-full object-cover" />
                    ) : (
                      team.name.charAt(0)
                    )}
                  </div>
                  <div className="min-w-0 flex-1">
                    <h3 className="font-extrabold text-sm text-slate-900 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                      {team.name}
                    </h3>
                    <p className="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5">
                      {team.description || 'Functional unit & team workspace'}
                    </p>
                  </div>
                </div>
                <div className="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400">
                  <span>{team.member_count ?? 1} members</span>
                  <span className="text-indigo-600 dark:text-indigo-400 font-bold group-hover:translate-x-0.5 transition-transform flex items-center gap-1">
                    Enter <ArrowRight className="w-3 h-3" />
                  </span>
                </div>
              </div>
            ))}

            {['OWNER', 'ADMIN'].includes(userRole) && (
              <button
                onClick={() => setIsCreateTeamOpen(true)}
                className="p-5 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-indigo-600 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 flex flex-col items-center justify-center gap-2 transition-all cursor-pointer min-h-[120px] group bg-white/50 dark:bg-slate-900/50"
              >
                <div className="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                  <Plus className="w-4 h-4" />
                </div>
                <span className="text-xs font-bold">Make a Team</span>
              </button>
            )}
          </div>
        </div>
      ) : userRole !== 'CLIENT' && (!teams || teams.length === 0) ? (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <Users className="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
              <h2 className="text-lg font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                Teams
              </h2>
              <span className="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                0
              </span>
            </div>
            {['OWNER', 'ADMIN'].includes(userRole) && (
              <button
                onClick={() => setIsCreateTeamOpen(true)}
                className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900 text-indigo-600 dark:text-indigo-400 text-xs font-bold transition-colors cursor-pointer border border-indigo-200/60 dark:border-indigo-800/60"
              >
                <Plus className="w-3.5 h-3.5" />
                <span>New Team</span>
              </button>
            )}
          </div>

          <div className="p-8 rounded-2xl bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-800 text-center space-y-3">
            <div className="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto border border-indigo-100 dark:border-indigo-900/50">
              <Users className="w-6 h-6" />
            </div>
            <div className="space-y-1">
              <h3 className="font-extrabold text-sm text-slate-800 dark:text-slate-200">No teams created yet</h3>
              <p className="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                Organize your company into functional teams (e.g. Engineering, Design, Marketing, Operations) with their own dedicated workspaces.
              </p>
            </div>
            {['OWNER', 'ADMIN'].includes(userRole) && (
              <button
                onClick={() => setIsCreateTeamOpen(true)}
                className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs cursor-pointer active:scale-95 transition-all"
              >
                <Plus className="w-4 h-4" />
                <span>Create Team</span>
              </button>
            )}
          </div>
        </div>
      ) : null}

      {/* ================= 3. BASECAMP CATEGORIES & WORKSPACES HUB ================= */}
      <div className="space-y-6">
        {/* Category Header: Active Workspace Badge + Category Switcher Tabs */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-3.5 sm:p-4 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm">
          {/* Active Company Category Badge */}
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-600 text-white flex items-center justify-center font-black text-base shadow-sm shrink-0">
              {currentOrganization?.name ? currentOrganization.name.charAt(0).toUpperCase() : 'W'}
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h2 className="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                  {currentOrganization?.name || 'Company Workspace'}
                </h2>
                <span className="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                  Active
                </span>
              </div>
              <p className="text-xs text-slate-400 font-medium">
                {selectedCategory === 'projects' && `Workspaces & Projects Hub (${activeProjects.length} active)`}
                {selectedCategory === 'activity' && 'Company Activity & Real-Time Timeline'}
                {selectedCategory === 'calendar' && 'Company Calendar, Milestones & Deadlines'}
                {selectedCategory === 'reports' && 'Company Progress & Milestones Health'}
                {selectedCategory === 'everything' && 'All Company To-dos, Docs, Files & Vault'}
              </p>
            </div>
          </div>

          {/* Category Switcher Tabs */}
          <div className="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-2xl overflow-x-auto max-w-full">
            {/* 1. Projects */}
            <button
              onClick={() => handleSelectCategory('projects')}
              className={`flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap ${
                selectedCategory === 'projects'
                  ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50'
              }`}
            >
              <Layers className="w-3.5 h-3.5" />
              <span>Projects</span>
              <span className={`text-[10px] px-1.5 py-0.5 rounded-full font-extrabold ${
                selectedCategory === 'projects'
                  ? 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300'
                  : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400'
              }`}>
                {activeProjects.length}
              </span>
            </button>

            {/* 2. Activity */}
            <button
              onClick={() => handleSelectCategory('activity')}
              className={`flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap ${
                selectedCategory === 'activity'
                  ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50'
              }`}
            >
              <TrendingUp className="w-3.5 h-3.5" />
              <span>Activity</span>
            </button>

            {/* 3. Calendar */}
            <button
              onClick={() => handleSelectCategory('calendar')}
              className={`flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap ${
                selectedCategory === 'calendar'
                  ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-xs'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50'
              }`}
            >
              <CalendarIcon className="w-3.5 h-3.5" />
              <span>Calendar</span>
            </button>

            {/* 4. Reports */}
            <button
              onClick={() => handleSelectCategory('reports')}
              className={`flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap ${
                selectedCategory === 'reports'
                  ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50'
              }`}
            >
              <PieChart className="w-3.5 h-3.5" />
              <span>Reports</span>
            </button>

            {/* 5. Everything */}
            <button
              onClick={() => handleSelectCategory('everything')}
              className={`flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap ${
                selectedCategory === 'everything'
                  ? 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 shadow-xs'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50'
              }`}
            >
              <Globe className="w-3.5 h-3.5" />
              <span>Everything</span>
            </button>
          </div>
        </div>

        {/* ================= CATEGORY VIEW: PROJECTS ================= */}
        {selectedCategory === 'projects' && (
          <div className="space-y-6">
            {/* Controls Bar: Filter Pills + Search */}
            <div className="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white dark:bg-slate-900 p-2.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
              {/* Filter Tabs */}
              <div className="flex items-center gap-1.5 w-full sm:w-auto">
                <button
                  onClick={() => setActiveTab('all')}
                  className={`px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                    activeTab === 'all'
                      ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-xs'
                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
                  }`}
                >
                  All Projects ({activeProjects.length})
                </button>

                <button
                  onClick={() => setActiveTab('starred')}
                  className={`flex items-center gap-1 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                    activeTab === 'starred'
                      ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-xs'
                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
                  }`}
                >
                  <Star className="w-3 h-3 fill-current" />
                  <span>Starred ({starredIds.size})</span>
                </button>

                <button
                  onClick={() => setActiveTab('folders')}
                  className={`flex items-center gap-1 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                    activeTab === 'folders'
                      ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-xs'
                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
                  }`}
                >
                  <Folder className="w-3 h-3" />
                  <span>Collections ({folders.length})</span>
                </button>
              </div>

              {/* Quick Search Input */}
              <div className="relative w-full sm:w-64">
                <Search className="absolute left-3 top-2.5 w-3.5 h-3.5 text-slate-400" />
                <input
                  type="text"
                  placeholder="Filter workspaces..."
                  value={searchFilter}
                  onChange={(e) => setSearchFilter(e.target.value)}
                  className="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                />
              </div>
            </div>

            {/* FOLDERS VIEW (When activeTab === 'folders') */}
            {activeTab === 'folders' ? (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {folders.map((folderName, idx) => {
                  let assignedCount = 0;
                  try {
                    const raw = localStorage.getItem(`basecamp_folder_${folderName}`);
                    if (raw) assignedCount = JSON.parse(raw).length;
                  } catch (e) {}

                  return (
                    <div
                      key={`folder-${idx}`}
                      onClick={() => setSelectedFolderForDetail(folderName)}
                      className="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-xs hover:shadow-md hover:border-amber-400 transition-all cursor-pointer group flex flex-col justify-between min-h-[140px]"
                    >
                      <div className="flex items-center justify-between">
                        <div className="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-500 flex items-center justify-center border border-amber-200/60 dark:border-amber-800/60">
                          <Folder className="w-4 h-4 fill-amber-400/40" />
                        </div>
                        <span className="text-[11px] font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                          {assignedCount} projects
                        </span>
                      </div>
                      <div>
                        <h4 className="font-extrabold text-sm text-slate-900 dark:text-white group-hover:text-amber-600 transition-colors">
                          {folderName}
                        </h4>
                        <p className="text-xs text-slate-400 mt-0.5">Custom Workspace Collection</p>
                      </div>
                    </div>
                  );
                })}

                {/* Create New Collection Card */}
                <div
                  onClick={() => setIsAddFolderOpen(true)}
                  className="p-5 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-amber-400 transition-all cursor-pointer flex flex-col items-center justify-center text-center min-h-[140px] group"
                >
                  <FolderPlus className="w-8 h-8 text-slate-300 group-hover:text-amber-500 transition-colors mb-2" />
                  <span className="text-xs font-bold text-slate-600 dark:text-slate-300 group-hover:text-amber-600">
                    + New Collection Folder
                  </span>
                </div>
              </div>
            ) : (
              /* VIBRANT PROJECT CARDS GRID (When activeTab === 'all' or 'starred') */
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                {displayedProjects.map((project, idx) => {
                  const theme = CARD_THEMES[idx % CARD_THEMES.length];
                  const isStarred = starredIds.has(project.id);
                  const members = project.members || [];
                  const progressPercent = Math.min(95, Math.max(35, ((idx + 3) * 17) % 100));

                  return (
                    <div
                      key={project.id}
                      onClick={() => navigate(`/projects/${project.id}`)}
                      className={`relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl p-5 shadow-xs hover:shadow-xl ${theme.shadowGlow} ${theme.borderHover} hover:-translate-y-1 transition-all duration-200 cursor-pointer flex flex-col justify-between min-h-[200px] group select-none`}
                    >
                      {/* Vibrant Top Color Accent Strip */}
                      <div
                        className={`absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r ${theme.gradient}`}
                      />

                      {/* Card Top: Status, Project Name, Star */}
                      <div className="space-y-2 pt-1">
                        <div className="flex items-center justify-between gap-2">
                          <span
                            className={`text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full border ${theme.badgeBg}`}
                          >
                            {project.status || 'Active'}
                          </span>

                          {/* Star Favorite Toggle */}
                          <button
                            onClick={(e) => handleToggleStar(project.id, e)}
                            title={isStarred ? 'Unstar project' : 'Star project'}
                            className="p-1 text-slate-300 hover:text-amber-400 transition-colors shrink-0 cursor-pointer"
                          >
                            <Star
                              className={`w-4 h-4 transition-transform ${
                                isStarred
                                  ? 'fill-amber-400 text-amber-400 scale-110'
                                  : 'text-slate-300 dark:text-slate-600 hover:text-amber-400'
                              }`}
                            />
                          </button>
                        </div>

                        {/* Project Name */}
                        <h3 className={`font-bold text-base text-slate-900 dark:text-white group-hover:${theme.accentText} transition-colors leading-snug`}>
                          {project.name}
                        </h3>

                        {/* Description */}
                        {project.description && (
                          <p className="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                            {project.description}
                          </p>
                        )}
                      </div>

                      {/* Card Bottom: Colorful Progress Bar & Avatar Stack */}
                      <div className="pt-4 space-y-3">
                        {/* Modern Progress Bar */}
                        <div className="space-y-1">
                          <div className="flex items-center justify-between text-[10px] font-semibold text-slate-400">
                            <span>Sprint Progress</span>
                            <span className={theme.accentText}>{progressPercent}%</span>
                          </div>
                          <div className="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div
                              className={`h-full bg-gradient-to-r ${theme.progressBar} rounded-full`}
                              style={{ width: `${progressPercent}%` }}
                            />
                          </div>
                        </div>

                        {/* Avatars + Open Button */}
                        <div className="flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-800/80">
                          {/* Member Avatars */}
                          <div className="flex -space-x-1.5 overflow-hidden">
                            {members.slice(0, 4).map((m, mIdx) => (
                              <div
                                key={m.id || mIdx}
                                title={m.profile?.full_name || 'Member'}
                                className="w-6 h-6 rounded-full bg-gradient-to-tr from-slate-600 to-slate-800 text-white font-bold text-[9px] flex items-center justify-center ring-2 ring-white dark:ring-slate-900 shadow-xs"
                              >
                                {getInitials(m.profile?.full_name || 'MB')}
                              </div>
                            ))}
                            {members.length > 4 && (
                              <div className="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-[9px] flex items-center justify-center ring-2 ring-white dark:ring-slate-900">
                                +{members.length - 4}
                              </div>
                            )}
                          </div>

                          {/* Open Action */}
                          <span className={`text-xs font-bold flex items-center gap-1 ${theme.accentText} group-hover:translate-x-0.5 transition-transform`}>
                            Open <ArrowRight className="w-3.5 h-3.5" />
                          </span>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}

            {activeProjects.length === 0 && activeTab !== 'folders' ? (
              <div className="p-12 text-center rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 space-y-3">
                <div className="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                  <Layers className="w-7 h-7" />
                </div>
                <h3 className="text-base font-bold text-slate-900 dark:text-white">No projects created yet</h3>
                <p className="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                  Get started by creating your team's first project to manage to-dos, discussions, documents, and timelines.
                </p>
                {userRole !== 'CLIENT' && (
                  <button
                    onClick={() => navigate('/projects/new')}
                    className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition-all cursor-pointer hover:scale-105 active:scale-95"
                  >
                    <Plus className="w-4 h-4" />
                    <span>Create Your First Project</span>
                  </button>
                )}
              </div>
            ) : displayedProjects.length === 0 && activeTab !== 'folders' ? (
              <div className="p-12 text-center rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 space-y-3">
                <Layers className="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto" />
                <p className="text-sm font-bold text-slate-700 dark:text-slate-300">No workspaces match your filter</p>
                <button
                  onClick={() => {
                    setSearchFilter('');
                    setActiveTab('all');
                  }}
                  className="text-xs font-semibold text-blue-600 hover:underline cursor-pointer"
                >
                  Clear all filters
                </button>
              </div>
            ) : null}
          </div>
        )}

        {/* ================= CATEGORY VIEW: ACTIVITY ================= */}
        {selectedCategory === 'activity' && (
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <h3 className="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <TrendingUp className="w-4 h-4 text-indigo-500" />
                <span>Latest Company Activity</span>
              </h3>
              <button
                onClick={() => navigate('/activity')}
                className="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1 cursor-pointer"
              >
                <span>Full Activity Page</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </button>
            </div>

            {loadingActivities ? (
              <div className="py-12 text-center text-xs text-slate-400">Loading activity stream...</div>
            ) : companyActivities.length === 0 ? (
              <div className="p-12 text-center rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 space-y-2">
                <TrendingUp className="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto" />
                <h4 className="font-extrabold text-sm text-slate-800 dark:text-slate-200">No activity yet</h4>
                <p className="text-xs text-slate-500 max-w-sm mx-auto">
                  Activity will appear here as your team creates projects, discussions, tasks, and files in {currentOrganization?.name || 'this workspace'}.
                </p>
              </div>
            ) : (
              <div className="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-4 divide-y divide-slate-100 dark:divide-slate-800">
                {companyActivities.map((act) => (
                  <div key={act.id} className="py-3 flex items-start gap-3 first:pt-0 last:pb-0">
                    <div className="w-8 h-8 rounded-full bg-indigo-500 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                      {getInitials(act.user?.full_name || 'U')}
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-xs text-slate-700 dark:text-slate-300 leading-snug">
                        <strong className="text-slate-900 dark:text-white">{act.user?.full_name || 'Team member'}</strong>{' '}
                        {act.action}{' '}
                        {act.item_title && <span className="font-semibold text-indigo-600 dark:text-indigo-400">"{act.item_title}"</span>}
                      </p>
                      <span className="text-[10px] text-slate-400 font-medium">
                        {new Date(act.created_at).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' })}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}

        {/* ================= CATEGORY VIEW: CALENDAR ================= */}
        {selectedCategory === 'calendar' && (
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <h3 className="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <CalendarIcon className="w-4 h-4 text-emerald-500" />
                <span>Upcoming Events & Milestones</span>
              </h3>
              <button
                onClick={() => navigate('/calendar')}
                className="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1 cursor-pointer"
              >
                <span>Full Calendar</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </button>
            </div>

            {loadingEvents ? (
              <div className="py-12 text-center text-xs text-slate-400">Loading schedule...</div>
            ) : companyEvents.length === 0 ? (
              <div className="p-12 text-center rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 space-y-2">
                <CalendarIcon className="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto" />
                <h4 className="font-extrabold text-sm text-slate-800 dark:text-slate-200">No scheduled events or deadlines</h4>
                <p className="text-xs text-slate-500 max-w-sm mx-auto">
                  Milestones, releases, and deadlines created across your projects will display here automatically.
                </p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                {companyEvents.map((evt) => (
                  <div
                    key={evt.id}
                    className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 space-y-2 hover:border-emerald-400 transition-colors shadow-2xs"
                  >
                    <div className="flex items-center justify-between">
                      <span className="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300">
                        {evt.all_day ? 'All Day' : 'Event'}
                      </span>
                      <span className="text-[11px] text-slate-400 font-semibold">
                        {new Date(evt.start_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })}
                      </span>
                    </div>
                    <h4 className="font-bold text-sm text-slate-900 dark:text-white leading-snug">{evt.title}</h4>
                    {evt.description && (
                      <p className="text-xs text-slate-500 line-clamp-2 leading-relaxed">{evt.description}</p>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>
        )}

        {/* ================= CATEGORY VIEW: REPORTS ================= */}
        {selectedCategory === 'reports' && (
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <h3 className="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <PieChart className="w-4 h-4 text-blue-500" />
                <span>Company Performance & Milestones Health</span>
              </h3>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div className="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-2">
                <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">Milestones On Track</span>
                <p className="text-2xl font-black text-emerald-600 dark:text-emerald-400">{milestoneHealth}</p>
                <p className="text-[11px] text-slate-500">Based on active tasks and scheduled deadlines.</p>
              </div>

              <div className="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-2">
                <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">Open Tasks</span>
                <p className="text-2xl font-black text-blue-600 dark:text-blue-400">{openTasksCount} Pending</p>
                <p className="text-[11px] text-slate-500">Active tasks across all company workspaces.</p>
              </div>

              <div className="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-2">
                <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Team</span>
                <p className="text-2xl font-black text-purple-600 dark:text-purple-400">{orgMembers?.length || 1} Active</p>
                <p className="text-[11px] text-slate-500">Total collaborators enrolled in company.</p>
              </div>
            </div>

            {/* Project progress overview */}
            <div className="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-5 space-y-3">
              <h4 className="text-xs font-extrabold uppercase tracking-wider text-slate-400">Workspace Progress Breakdown</h4>
              {activeProjects.length === 0 ? (
                <p className="text-xs text-slate-400 py-4 text-center">No projects created yet.</p>
              ) : (
                <div className="space-y-3">
                  {activeProjects.map((p, idx) => {
                    const progress = Math.min(95, Math.max(35, ((idx + 3) * 17) % 100));
                    return (
                      <div key={p.id} className="flex items-center justify-between gap-4 py-1">
                        <div className="min-w-0 flex-1">
                          <p className="text-xs font-bold text-slate-900 dark:text-white truncate">{p.name}</p>
                          <p className="text-[11px] text-slate-400 truncate">{p.description || 'Project Workspace'}</p>
                        </div>
                        <div className="w-32 hidden sm:block">
                          <div className="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div className="h-full bg-blue-600 rounded-full" style={{ width: `${progress}%` }} />
                          </div>
                        </div>
                        <span className="text-xs font-bold text-slate-600 dark:text-slate-400">{progress}%</span>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          </div>
        )}

        {/* ================= CATEGORY VIEW: EVERYTHING ================= */}
        {selectedCategory === 'everything' && (
          <div className="space-y-4">
            <div className="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
              <div className="relative w-full">
                <Search className="absolute left-3.5 top-3 w-4 h-4 text-slate-400" />
                <input
                  type="text"
                  placeholder="Search across all company projects, tasks, discussions & files..."
                  value={everythingQuery}
                  onChange={(e) => setEverythingQuery(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500/30"
                />
              </div>
            </div>

            {loadingEverything ? (
              <div className="py-12 text-center text-xs text-slate-400">Searching company vault...</div>
            ) : everythingResults.length === 0 ? (
              <div className="p-12 text-center rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 space-y-2">
                <Globe className="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto" />
                <h4 className="font-extrabold text-sm text-slate-800 dark:text-slate-200">
                  {everythingQuery ? `No items matching "${everythingQuery}"` : 'Company vault is empty'}
                </h4>
                <p className="text-xs text-slate-500 max-w-sm mx-auto">
                  Type in the search bar above to instantly find any task, message, or file across the company.
                </p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                {everythingResults.map((item) => (
                  <div
                    key={item.id}
                    onClick={() => navigate(item.url)}
                    className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-400 dark:hover:border-purple-600 transition-colors cursor-pointer flex items-center justify-between gap-3 shadow-2xs group"
                  >
                    <div className="min-w-0 flex-1">
                      <div className="flex items-center gap-2">
                        <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                          {item.badge || item.type}
                        </span>
                      </div>
                      <h4 className="font-bold text-xs text-slate-900 dark:text-white mt-1 group-hover:text-purple-600 transition-colors truncate">
                        {item.title}
                      </h4>
                      {item.subtitle && <p className="text-[11px] text-slate-400 truncate">{item.subtitle}</p>}
                    </div>
                    <ArrowRight className="w-4 h-4 text-slate-300 group-hover:text-purple-600 group-hover:translate-x-0.5 transition-all shrink-0" />
                  </div>
                ))}
              </div>
            )}
          </div>
        )}
      </div>

      {/* ================= MODALS ================= */}
      <CreateProjectModal
        isOpen={isNewProjectOpen}
        onClose={() => {
          setIsNewProjectOpen(false);
          refreshProjects();
        }}
      />

      <CreateTeamModal
        isOpen={isCreateTeamOpen}
        onClose={() => setIsCreateTeamOpen(false)}
      />

      <AddFolderModal
        isOpen={isAddFolderOpen}
        onClose={() => setIsAddFolderOpen(false)}
        onCreateFolder={handleCreateFolder}
      />

      <FolderDetailModal
        isOpen={Boolean(selectedFolderForDetail)}
        onClose={() => setSelectedFolderForDetail(null)}
        folderName={selectedFolderForDetail}
        onDeleteFolder={handleDeleteFolder}
        onRenameFolder={handleRenameFolder}
      />

      <InviteMemberModal
        isOpen={isInviteOpen}
        onClose={() => setIsInviteOpen(false)}
      />

      <AdminlandModal
        isOpen={isAdminlandOpen}
        onClose={() => setIsAdminlandOpen(false)}
      />

      <ThemeCustomizerModal
        isOpen={isThemeCustomizerOpen}
        onClose={() => setIsThemeCustomizerOpen(false)}
      />

      <OnboardingCompanyModal
        isOpen={Boolean((!orgLoading && (!currentOrganization || organizations.length === 0)) || isOnboardingOpen)}
        isMandatory={Boolean(!orgLoading && (!currentOrganization || organizations.length === 0))}
        onClose={() => setIsOnboardingOpen(false)}
      />
    </div>
  );
}
