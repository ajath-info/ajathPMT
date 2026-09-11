import React, { useState, useRef, useEffect } from 'react';
import {
  ChevronDown,
  Maximize2,
  Minimize2,
  LogOut,
  Settings,
  User,
  Building2,
  Home,
  X,
  Search,
  Plus,
  MessageSquare,
  Bell,
  CheckSquare,
  Calendar,
  Layers,
  ShieldCheck,
  HelpCircle,
  FolderGit2,
  Sparkles,
  Check,
} from 'lucide-react';
import { useAuth, DEMO_USERS } from '../../context/AuthContext';
import { OrgRole } from '../../types';
import { useOrganization } from '../../context/OrganizationContext';
import { useNavigate, useLocation, Link } from 'react-router-dom';
import { BasecampNavDropdown } from './BasecampNavDropdown';
import { OnboardingCompanyModal } from '../modals/OnboardingCompanyModal';
import { getInitials } from '../../lib/utils';
import { getUserNotifications } from '../../services/notificationService';

export interface BasecampHeaderProps {
  onOpenPings: () => void;
  onOpenHey?: () => void;
  isPingsOpen?: boolean;
  onClosePings?: () => void;
  onOpenSearch?: () => void;
  onOpenProfile?: () => void;
  onOpenSupport?: () => void;
  onOpenDrawer?: (type: 'tasks' | 'events' | 'bookmarks' | 'activity' | 'notes') => void;
}

export function BasecampHeader({
  onOpenPings,
  onOpenHey,
  isPingsOpen,
  onClosePings,
  onOpenSearch,
  onOpenProfile,
  onOpenSupport,
  onOpenDrawer,
}: BasecampHeaderProps) {
  const { profile, signOut, userRole, signInAsDemo, signInAsFreshAdmin, signInAsFreshOwner } = useAuth();
  const { currentOrganization, organizations } = useOrganization();
  const navigate = useNavigate();
  const location = useLocation();

  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [isCreateOrgModalOpen, setIsCreateOrgModalOpen] = useState(false);
  const [isFullscreen, setIsFullscreen] = useState(false);
  const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);
  const [isToolsMenuOpen, setIsToolsMenuOpen] = useState(false);
  const [unreadCount, setUnreadCount] = useState(0);
  const userMenuRef = useRef<HTMLDivElement>(null);
  const toolsMenuRef = useRef<HTMLDivElement>(null);

  // Ensure dropdown is always closed whenever navigating to any page (projects, etc.)
  useEffect(() => {
    setIsDropdownOpen(false);
  }, [location.pathname]);

  useEffect(() => {
    if (!profile?.id) return;
    let isMounted = true;
    getUserNotifications(profile.id, currentOrganization?.id)
      .then((list) => {
        if (isMounted) {
          setUnreadCount(list.filter((n) => !n.is_read).length);
        }
      })
      .catch(() => {});
    return () => {
      isMounted = false;
    };
  }, [profile?.id, currentOrganization?.id]);

  // Close user & tools menu on outside click
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (userMenuRef.current && !userMenuRef.current.contains(e.target as Node)) {
        setIsUserMenuOpen(false);
      }
      if (toolsMenuRef.current && !toolsMenuRef.current.contains(e.target as Node)) {
        setIsToolsMenuOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen().catch(() => {});
      setIsFullscreen(true);
    } else {
      if (document.exitFullscreen) {
        document.exitFullscreen().catch(() => {});
        setIsFullscreen(false);
      }
    }
  };

  const isNavActive = (path: string) => {
    if (path === '/dashboard') {
      return location.pathname === '/' || location.pathname === '/dashboard';
    }
    return location.pathname.startsWith(path);
  };

  const navLinks = [
    { label: 'Dashboard', path: '/dashboard', icon: Home },
    { label: 'My Tasks', path: '/my-tasks', icon: CheckSquare },
    { label: 'Calendar', path: '/calendar', icon: Calendar },
    { label: 'Activity', path: '/activity', icon: Layers },
  ];

  return (
    <header className="sticky top-0 z-40 h-16 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800/80 px-4 sm:px-6 flex items-center justify-between transition-all">
      {/* ================= LEFT: Brand & Workspace Switcher ================= */}
      <div className="flex items-center gap-3 sm:gap-4">
        {/* Modern Geometric Logo */}
        <Link
          to="/dashboard"
          className="flex items-center gap-2.5 group focus:outline-none"
          title="Ajath PMT Dashboard"
        >
          <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-sm shadow-indigo-500/20 group-hover:shadow-indigo-500/30 group-hover:scale-105 transition-all">
            <svg
              className="w-5 h-5 text-white"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2.5"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <polygon points="12 2 2 7 12 12 22 7 12 2" />
              <polyline points="2 17 12 22 22 17" />
              <polyline points="2 12 12 17 22 12" />
            </svg>
          </div>

          <div className="hidden sm:flex flex-col text-left">
            <span className="font-extrabold text-[15px] tracking-tight text-slate-900 dark:text-white leading-tight flex items-center gap-1.5">
              Ajath PMT
              <span className="text-[10px] font-semibold uppercase tracking-wider bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded-md border border-blue-200/60 dark:border-blue-800/60">
                PRO
              </span>
            </span>
            <span className="text-[11px] font-medium text-slate-500 dark:text-slate-400 truncate max-w-[140px]">
              {currentOrganization?.name || 'Workspace'}
            </span>
          </div>
        </Link>

        {/* Quick Workspace / Recent Items Dropdown (Right tick section - always available on Home Screen) */}
        <div className="relative">
          <button
            onClick={() => setIsDropdownOpen(!isDropdownOpen)}
            className="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer border border-slate-200/70 dark:border-slate-700/60"
            title="Switch context or recent jump"
          >
            <Building2 className="w-3.5 h-3.5 text-slate-400" />
            <span className="hidden md:inline font-semibold truncate max-w-[110px]">
              {currentOrganization?.name || 'Jump to'}
            </span>
            <ChevronDown className="w-3 h-3 text-slate-400" />
          </button>

          <BasecampNavDropdown
            isOpen={isDropdownOpen}
            onClose={() => setIsDropdownOpen(false)}
            onOpenCreateOrg={() => setIsCreateOrgModalOpen(true)}
          />
        </div>
      </div>

      {/* ================= CENTER: Modern Navigation Tabs & Search ================= */}
      <div className="hidden lg:flex items-center gap-1 bg-slate-100/80 dark:bg-slate-800/60 p-1 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
        {navLinks.map((tab) => {
          const Icon = tab.icon;
          const active = isNavActive(tab.path);
          return (
            <Link
              key={tab.path}
              to={tab.path}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all ${
                active
                  ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs border border-slate-200/50 dark:border-slate-700/50'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-white/50 dark:hover:bg-slate-700/50'
              }`}
            >
              <Icon className={`w-3.5 h-3.5 ${active ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400'}`} />
              <span>{tab.label}</span>
            </Link>
          );
        })}
      </div>

      {/* ================= RIGHT: Quick Search, Actions, Triggers & Profile ================= */}
      <div className="flex items-center gap-2 sm:gap-2.5">
        {/* Quick Global Search Trigger Button */}
        <button
          onClick={onOpenSearch}
          className="hidden sm:flex items-center gap-2 pl-3 pr-2.5 py-1.5 rounded-xl bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-200/70 dark:hover:bg-slate-700/80 border border-slate-200/80 dark:border-slate-700/80 text-xs text-slate-500 dark:text-slate-400 transition-all cursor-pointer group"
          title="Search or Jump anywhere (Ctrl+K)"
        >
          <Search className="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-200 transition-colors" />
          <span className="font-normal">Search...</span>
          <kbd className="px-1.5 py-0.5 text-[10px] font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-slate-500 shadow-2xs">
            ⌘K
          </kbd>
        </button>

        {/* Create Project Button (Internal team only) */}
        {userRole !== 'CLIENT' && (
          <button
            onClick={() => navigate('/projects/new')}
            className="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs hover:shadow-sm transition-all cursor-pointer active:scale-95"
            title="Create New Project"
          >
            <Plus className="w-3.5 h-3.5 stroke-[2.5]" />
            <span>New Project</span>
          </button>
        )}

        {/* Pings / Messages Drawer Trigger */}
        <button
          onClick={isPingsOpen ? onClosePings : onOpenPings}
          className={`relative p-2 rounded-xl border transition-all cursor-pointer ${
            isPingsOpen
              ? 'bg-blue-50 dark:bg-blue-950/60 border-blue-300 dark:border-blue-700 text-blue-600 dark:text-blue-400 shadow-xs'
              : 'bg-white dark:bg-slate-800 border-slate-200/80 dark:border-slate-700/80 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60 hover:text-slate-900 dark:hover:text-white'
          }`}
          title="Direct Messages & Pings"
        >
          <MessageSquare className="w-4 h-4" />
          <span className="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900" />
        </button>

        {/* Notifications / Hey! Drawer Trigger */}
        <button
          onClick={onOpenHey || onOpenPings}
          className="relative p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60 hover:text-slate-900 dark:hover:text-white transition-all cursor-pointer"
          title="Notifications & Updates"
        >
          <Bell className="w-4 h-4" />
          {unreadCount > 0 && (
            <span className="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-blue-600 ring-2 ring-white dark:ring-slate-900" />
          )}
        </button>

        {/* Quick Drawers / Tools Dropdown */}
        <div className="relative" ref={toolsMenuRef}>
          <button
            onClick={() => setIsToolsMenuOpen(!isToolsMenuOpen)}
            className={`p-2 rounded-xl border transition-all cursor-pointer ${
              isToolsMenuOpen
                ? 'bg-purple-50 dark:bg-purple-950/60 border-purple-300 dark:border-purple-700 text-purple-600 dark:text-purple-400'
                : 'bg-white dark:bg-slate-800 border-slate-200/80 dark:border-slate-700/80 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60'
            }`}
            title="Workspace Drawers & Quick Tools"
          >
            <Sparkles className="w-4 h-4 text-purple-500" />
          </button>

          {isToolsMenuOpen && (
            <div className="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200/80 dark:border-slate-800 p-2 z-50 animate-in fade-in zoom-in-95 duration-150">
              <div className="px-3 py-1.5 border-b border-slate-100 dark:border-slate-800 mb-1">
                <span className="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                  Quick Drawers
                </span>
              </div>
              <button
                onClick={() => {
                  setIsToolsMenuOpen(false);
                  onOpenDrawer?.('tasks');
                }}
                className="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-xl transition-colors text-left"
              >
                <div className="w-2 h-2 rounded-full bg-emerald-500" />
                <span>My Tasks Drawer</span>
              </button>
              <button
                onClick={() => {
                  setIsToolsMenuOpen(false);
                  onOpenDrawer?.('events');
                }}
                className="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-950/40 rounded-xl transition-colors text-left"
              >
                <div className="w-2 h-2 rounded-full bg-blue-500" />
                <span>My Schedule</span>
              </button>
              <button
                onClick={() => {
                  setIsToolsMenuOpen(false);
                  onOpenDrawer?.('bookmarks');
                }}
                className="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-xl transition-colors text-left"
              >
                <div className="w-2 h-2 rounded-full bg-amber-500" />
                <span>Saved Bookmarks</span>
              </button>
              <button
                onClick={() => {
                  setIsToolsMenuOpen(false);
                  onOpenDrawer?.('notes');
                }}
                className="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-pink-50 dark:hover:bg-pink-950/40 rounded-xl transition-colors text-left"
              >
                <div className="w-2 h-2 rounded-full bg-pink-500" />
                <span>Scratchpad / Notes</span>
              </button>
              <button
                onClick={() => {
                  setIsToolsMenuOpen(false);
                  onOpenDrawer?.('activity');
                }}
                className="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-purple-50 dark:hover:bg-purple-950/40 rounded-xl transition-colors text-left"
              >
                <div className="w-2 h-2 rounded-full bg-purple-500" />
                <span>Activity Stream</span>
              </button>
            </div>
          )}
        </div>

        {/* Fullscreen Button */}
        <button
          onClick={toggleFullscreen}
          className="hidden xl:flex p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
          title={isFullscreen ? 'Exit Full Screen' : 'Full Screen'}
        >
          {isFullscreen ? <Minimize2 className="w-4 h-4" /> : <Maximize2 className="w-4 h-4" />}
        </button>


        {/* ================= User Profile Menu ================= */}
        <div className="relative pl-1" ref={userMenuRef}>
          <button
            onClick={() => setIsUserMenuOpen(!isUserMenuOpen)}
            className="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer focus:outline-none"
            title="User menu"
          >
            <div className="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-bold text-xs flex items-center justify-center shadow-xs overflow-hidden">
              {profile?.avatar_url ? (
                <img
                  src={profile.avatar_url}
                  alt={profile.full_name || 'User'}
                  className="w-full h-full object-cover"
                  onError={(e) => {
                    e.currentTarget.style.display = 'none';
                  }}
                />
              ) : (
                getInitials(profile?.full_name || 'User')
              )}
            </div>
            <ChevronDown className="w-3.5 h-3.5 text-slate-400 hidden sm:block" />
          </button>

          {/* Sleek Modern Profile Dropdown */}
          {isUserMenuOpen && (
            <div className="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200/80 dark:border-slate-800 p-2 z-50 animate-in fade-in zoom-in-95 duration-150">
              {/* Header Info */}
              <div className="px-3 py-2.5 border-b border-slate-100 dark:border-slate-800">
                <p className="text-xs font-bold text-slate-900 dark:text-white truncate">
                  {profile?.full_name || 'User'}
                </p>
                <p className="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                  {profile?.email || 'user@example.com'}
                </p>
                <div className="mt-2 flex items-center justify-between gap-1 flex-wrap">
                  <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-[10px] font-semibold text-slate-700 dark:text-slate-300">
                    <ShieldCheck className="w-3 h-3 text-blue-500" />
                    <span>{currentOrganization?.name || 'Workspace'}</span>
                  </span>
                  <span className="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wide bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    {userRole === 'OWNER'
                      ? 'Account Owner'
                      : userRole === 'ADMIN'
                      ? 'Administrator'
                      : userRole === 'MEMBER'
                      ? 'Member'
                      : 'Client'}
                  </span>
                </div>
              </div>

              {/* Navigation Items */}
              <div className="py-1 space-y-0.5">
                <button
                  onClick={() => {
                    setIsUserMenuOpen(false);
                    if (onOpenProfile) onOpenProfile();
                    else navigate('/profile');
                  }}
                  className="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors cursor-pointer text-left"
                >
                  <User className="w-3.5 h-3.5 text-slate-400" />
                  <span>My Profile</span>
                </button>

                {['OWNER', 'ADMIN'].includes(userRole) && (
                  <button
                    onClick={() => {
                      setIsUserMenuOpen(false);
                      navigate('/adminland');
                    }}
                    className="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors cursor-pointer text-left"
                  >
                    <ShieldCheck className="w-3.5 h-3.5 text-slate-400" />
                    <span>Adminland & Team</span>
                  </button>
                )}

                {['OWNER', 'ADMIN'].includes(userRole) && (
                  <button
                    onClick={() => {
                      setIsUserMenuOpen(false);
                      navigate('/settings/organization');
                    }}
                    className="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors cursor-pointer text-left"
                  >
                    <Settings className="w-3.5 h-3.5 text-slate-400" />
                    <span>Workspace Settings</span>
                  </button>
                )}

                <button
                  onClick={() => {
                    setIsUserMenuOpen(false);
                    if (onOpenSupport) onOpenSupport();
                  }}
                  className="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors cursor-pointer text-left"
                >
                  <HelpCircle className="w-3.5 h-3.5 text-slate-400" />
                  <span>Help & Support</span>
                </button>
              </div>

              {/* Switch Persona Demo Section */}
              <div className="py-1.5 border-t border-slate-100 dark:border-slate-800">
                <p className="px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center justify-between">
                  <span>Switch Role</span>
                  <span className="text-[9px] text-blue-600 dark:text-blue-400 lowercase font-normal">1-click demo</span>
                </p>
                <div className="space-y-0.5 px-1">
                  {[
                    { role: 'OWNER' as OrgRole, label: 'Account Owner', name: 'Sarah Jenkins', icon: '👑' },
                    { role: 'ADMIN' as OrgRole, label: 'Administrator', name: 'Alex Vance', icon: '🛡️' },
                    { role: 'MEMBER' as OrgRole, label: 'Team Member', name: 'Marcus Rivera', icon: '💻' },
                    { role: 'CLIENT' as OrgRole, label: 'Client Partner', name: 'Edward Smith', icon: '🤝' },
                  ].map((p) => {
                    const active = userRole === p.role;
                    return (
                      <button
                        key={p.role}
                        onClick={() => {
                          setIsUserMenuOpen(false);
                          signInAsDemo(p.role);
                        }}
                        className={`w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs transition-colors cursor-pointer text-left ${
                          active
                            ? 'bg-blue-50 dark:bg-blue-950/60 font-bold text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60'
                            : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300'
                        }`}
                      >
                        <div className="flex items-center gap-2 truncate">
                          <span>{p.icon}</span>
                          <div className="truncate">
                            <p className="text-xs leading-tight truncate font-semibold">{p.label}</p>
                            <p className="text-[10px] text-slate-400 font-normal truncate">{p.name}</p>
                          </div>
                        </div>
                        {active && <Check className="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 shrink-0" />}
                      </button>
                    );
                  })}

                  <button
                    onClick={() => {
                      setIsUserMenuOpen(false);
                      signInAsFreshOwner();
                    }}
                    className="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 transition-colors cursor-pointer text-left mt-0.5"
                  >
                    <div className="flex items-center gap-2 truncate">
                      <span>👑</span>
                      <div className="truncate">
                        <p className="text-xs leading-tight truncate font-semibold">Fresh Owner</p>
                        <p className="text-[10px] text-emerald-600/80 font-normal truncate">Add Company from Scratch</p>
                      </div>
                    </div>
                    <Plus className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                  </button>

                  <button
                    onClick={() => {
                      setIsUserMenuOpen(false);
                      signInAsFreshAdmin();
                    }}
                    className="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-xs hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 transition-colors cursor-pointer text-left mt-0.5"
                  >
                    <div className="flex items-center gap-2 truncate">
                      <span>✨</span>
                      <div className="truncate">
                        <p className="text-xs leading-tight truncate font-semibold">Fresh Admin</p>
                        <p className="text-[10px] text-emerald-600/80 font-normal truncate">Add Company from Scratch</p>
                      </div>
                    </div>
                    <Plus className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                  </button>
                </div>
              </div>

              {/* Add Company Action for Admins/Owners (only if 0 companies exist) */}
              {['OWNER', 'ADMIN'].includes(userRole) && organizations.length === 0 && (
                <div className="pt-1 border-t border-slate-100 dark:border-slate-800">
                  <button
                    onClick={() => {
                      setIsUserMenuOpen(false);
                      setIsCreateOrgModalOpen(true);
                    }}
                    className="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-xl transition-colors cursor-pointer text-left"
                  >
                    <Building2 className="w-3.5 h-3.5" />
                    <span>+ Create Company Workspace</span>
                  </button>
                </div>
              )}

              {/* Sign Out Footer */}
              <div className="pt-1 border-t border-slate-100 dark:border-slate-800">
                <button
                  onClick={() => {
                    setIsUserMenuOpen(false);
                    signOut();
                  }}
                  className="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors cursor-pointer text-left"
                >
                  <LogOut className="w-3.5 h-3.5" />
                  <span>Sign Out</span>
                </button>
              </div>
            </div>
          )}
        </div>
      </div>

      <OnboardingCompanyModal
        isOpen={isCreateOrgModalOpen}
        onClose={() => setIsCreateOrgModalOpen(false)}
      />
    </header>
  );
}
