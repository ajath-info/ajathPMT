import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
  Search,
  Plus,
  Bell,
  Sun,
  Moon,
  Laptop,
  Menu,
  User as UserIcon,
  LogOut,
  FolderPlus,
  CheckSquare,
  MessageSquare,
  Calendar,
  UserPlus,
  ChevronDown,
  Sparkles,
  Layers,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import { Avatar } from '../common/Avatar';
import { Button } from '../common/Button';
import { OrgRole } from '../../types';

import { NavLink } from 'react-router-dom';

interface HeaderProps {
  onOpenMobileMenu: () => void;
  onOpenCommandMenu: () => void;
}

export function Header({ onOpenMobileMenu, onOpenCommandMenu }: HeaderProps) {
  const { profile, userRole, signInAsDemo, signOut, isMockMode } = useAuth();
  const { theme, setTheme, isDark } = useTheme();
  const navigate = useNavigate();

  const [isQuickCreateOpen, setIsQuickCreateOpen] = useState(false);
  const [isProfileMenuOpen, setIsProfileMenuOpen] = useState(false);
  const [isNotificationsOpen, setIsNotificationsOpen] = useState(false);
  const [isRoleMenuOpen, setIsRoleMenuOpen] = useState(false);

  const mockNotifications = [
    { id: '1', title: 'New task assigned', message: 'Sarah assigned you "Design System Audits"', time: '10m ago' },
    { id: '2', title: 'Comment on Discussion', message: 'David replied to "Q4 Product Strategy"', time: '1h ago' },
    { id: '3', title: 'Project Milestone', message: 'Mobile API Integration marked 75% complete', time: '3h ago' },
  ];

  const basecampNavPills = [
    { label: 'Home', path: '/dashboard' },
    { label: 'Lineup', path: '/projects?view=lineup' },
    { label: 'Pings', path: '/messages', badge: '2', badgeColor: 'bg-brand-600' },
    { label: 'Hey!', path: '/notifications', badge: '3', badgeColor: 'bg-amber-500' },
    { label: 'Activity', path: '/reports' },
    { label: 'My Stuff', path: '/my-tasks' },
  ];

  return (
    <header className="sticky top-0 z-30 h-16 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-3 sm:px-4 flex items-center justify-between gap-2 sm:gap-4">
      {/* Left section: Drawer Toggle, Brand Logo & Quick Search Trigger */}
      <div className="flex items-center gap-2 sm:gap-3">
        <button
          onClick={onOpenMobileMenu}
          title="Open Navigation Menu"
          className="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors flex items-center gap-1.5 text-xs font-semibold"
        >
          <Menu className="w-5 h-5 text-slate-700 dark:text-slate-200" />
          <span className="hidden sm:inline font-bold">Menu</span>
        </button>

        <Link
          to="/dashboard"
          className="flex items-center gap-2 group font-black text-slate-800 dark:text-slate-100 text-sm sm:text-base tracking-tight hover:opacity-90 transition-opacity"
        >
          <div className="w-7 h-7 rounded-lg bg-gradient-to-tr from-brand-600 to-amber-500 flex items-center justify-center text-white shadow-sm group-hover:scale-105 transition-transform">
            <Layers className="w-4 h-4" />
          </div>
          <span className="hidden md:inline font-bold">Ajath PMT</span>
        </Link>

        <button
          onClick={onOpenCommandMenu}
          className="hidden xl:flex items-center gap-2 px-3 py-1.5 text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800/70 hover:bg-slate-200/70 dark:hover:bg-slate-800 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 rounded-xl transition-all shadow-inner group ml-1"
        >
          <Search className="w-3.5 h-3.5 text-slate-400 group-hover:text-brand-500 transition-colors" />
          <span className="truncate text-slate-500 dark:text-slate-400">Find...</span>
          <kbd className="inline-flex items-center gap-0.5 ml-1 px-1.5 py-0.5 text-[9px] font-semibold text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded shadow-xs">
            Ctrl K
          </kbd>
        </button>
      </div>

      {/* Center section: Basecamp Signature Top Navigation Pills */}
      <nav className="hidden md:flex items-center gap-1 bg-slate-100/90 dark:bg-slate-800/70 p-1 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-inner">
        {basecampNavPills.map((item) => (
          <NavLink
            key={item.path}
            to={item.path}
            className={({ isActive }) =>
              `px-3 py-1.5 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 ${
                isActive
                  ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-white/50 dark:hover:bg-slate-700/40'
              }`
            }
          >
            <span>{item.label}</span>
            {item.badge && (
              <span
                className={`px-1.5 py-0.2 text-[10px] font-extrabold rounded-full text-white ${item.badgeColor}`}
              >
                {item.badge}
              </span>
            )}
          </NavLink>
        ))}

        <button
          onClick={onOpenCommandMenu}
          className="px-2.5 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-white/50 dark:hover:bg-slate-700/40 rounded-xl transition-all flex items-center gap-1"
        >
          <Search className="w-3 h-3 text-slate-400" />
          <span>Find</span>
        </button>
      </nav>

      {/* Right Action Tools */}
      <div className="flex items-center gap-2 sm:gap-3">
        {/* Quick Create Dropdown */}
        <div className="relative">
          <Button
            variant="primary"
            size="sm"
            leftIcon={<Plus className="w-4 h-4" />}
            onClick={() => setIsQuickCreateOpen(!isQuickCreateOpen)}
            className="hidden sm:inline-flex"
          >
            Create
          </Button>
          <button
            onClick={() => setIsQuickCreateOpen(!isQuickCreateOpen)}
            className="sm:hidden p-2 rounded-xl bg-brand-600 text-white"
          >
            <Plus className="w-5 h-5" />
          </button>

          {isQuickCreateOpen && (
            <div
              className="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 py-2 z-50 animate-in zoom-in-95 duration-150"
              onMouseLeave={() => setIsQuickCreateOpen(false)}
            >
              <div className="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Quick Actions
              </div>
              <button
                onClick={() => { setIsQuickCreateOpen(false); navigate('/projects?create=true'); }}
                className="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
              >
                <FolderPlus className="w-4 h-4 text-brand-500" />
                Create Project
              </button>
              <button
                onClick={() => { setIsQuickCreateOpen(false); navigate('/my-tasks?create=true'); }}
                className="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
              >
                <CheckSquare className="w-4 h-4 text-emerald-500" />
                Create Task
              </button>
              <button
                onClick={() => { setIsQuickCreateOpen(false); navigate('/discussions?create=true'); }}
                className="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
              >
                <MessageSquare className="w-4 h-4 text-violet-500" />
                Start Discussion
              </button>
              <button
                onClick={() => { setIsQuickCreateOpen(false); navigate('/calendar?create=true'); }}
                className="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
              >
                <Calendar className="w-4 h-4 text-amber-500" />
                Schedule Event
              </button>
              <button
                onClick={() => { setIsQuickCreateOpen(false); navigate('/invite-people'); }}
                className="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors border-t border-slate-100 dark:border-slate-800 mt-1 pt-2"
              >
                <UserPlus className="w-4 h-4 text-indigo-500" />
                Invite Member
              </button>
            </div>
          )}
        </div>

        {/* Notifications Dropdown */}
        <div className="relative">
          <button
            onClick={() => setIsNotificationsOpen(!isNotificationsOpen)}
            className="relative p-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            title="Notifications"
          >
            <Bell className="w-5 h-5" />
            <span className="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-brand-600 ring-2 ring-white dark:ring-slate-900" />
          </button>

          {isNotificationsOpen && (
            <div
              className="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 py-2 z-50 animate-in zoom-in-95 duration-150"
              onMouseLeave={() => setIsNotificationsOpen(false)}
            >
              <div className="flex items-center justify-between px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                <span className="font-bold text-sm text-slate-900 dark:text-slate-100">Notifications</span>
                <Link to="/notifications" onClick={() => setIsNotificationsOpen(false)} className="text-xs text-brand-600 dark:text-brand-400 hover:underline">
                  Open Hub
                </Link>
              </div>
              <div className="max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                {mockNotifications.map((notif) => (
                  <div key={notif.id} className="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <p className="text-xs font-semibold text-slate-900 dark:text-slate-100">{notif.title}</p>
                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{notif.message}</p>
                    <span className="text-[10px] text-slate-400 mt-1 block">{notif.time}</span>
                  </div>
                ))}
              </div>
              <div className="p-2 text-center border-t border-slate-100 dark:border-slate-800">
                <Link
                  to="/notifications"
                  onClick={() => setIsNotificationsOpen(false)}
                  className="text-xs font-medium text-brand-600 dark:text-brand-400 hover:underline"
                >
                  View all notifications
                </Link>
              </div>
            </div>
          )}
        </div>

        {/* Theme Toggle */}
        <button
          onClick={() => {
            if (theme === 'light') setTheme('dark');
            else if (theme === 'dark') setTheme('system');
            else setTheme('light');
          }}
          className="p-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          title={`Theme: ${theme}`}
        >
          {theme === 'light' && <Sun className="w-5 h-5 text-amber-500" />}
          {theme === 'dark' && <Moon className="w-5 h-5 text-indigo-400" />}
          {theme === 'system' && <Laptop className="w-5 h-5 text-slate-500" />}
        </button>

        {/* Profile Avatar & Menu */}
        <div className="relative">
          <button
            onClick={() => setIsProfileMenuOpen(!isProfileMenuOpen)}
            className="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          >
            <Avatar
              src={profile?.avatar_url}
              name={profile?.full_name || 'User'}
              size="sm"
              status="online"
            />
            <ChevronDown className="w-3.5 h-3.5 text-slate-400 hidden sm:block" />
          </button>

          {isProfileMenuOpen && (
            <div
              className="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 py-2 z-50 animate-in zoom-in-95 duration-150"
              onMouseLeave={() => setIsProfileMenuOpen(false)}
            >
              {/* User Header */}
              <div className="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                <p className="font-bold text-sm text-slate-900 dark:text-slate-100">{profile?.full_name}</p>
                <p className="text-xs text-slate-500 dark:text-slate-400 truncate">{profile?.email}</p>
                <p className="text-[11px] font-semibold text-brand-600 dark:text-brand-400 mt-1">{profile?.job_title}</p>
              </div>

              {/* Demo Role Switcher Section */}
              <div className="px-3 py-2 border-b border-slate-100 dark:border-slate-800">
                <button
                  onClick={() => setIsRoleMenuOpen(!isRoleMenuOpen)}
                  className="w-full flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 py-1"
                >
                  <span className="flex items-center gap-1.5">
                    <Sparkles className="w-3.5 h-3.5 text-amber-500" /> Switch Demo Role ({userRole})
                  </span>
                  <ChevronDown className="w-3 h-3" />
                </button>
                {isRoleMenuOpen && (
                  <div className="mt-1 space-y-1 bg-slate-50 dark:bg-slate-800/60 p-1.5 rounded-xl">
                    {(['OWNER', 'ADMIN', 'MEMBER', 'CLIENT'] as OrgRole[]).map((r) => (
                      <button
                        key={r}
                        onClick={() => {
                          signInAsDemo(r);
                          setIsRoleMenuOpen(false);
                          setIsProfileMenuOpen(false);
                        }}
                        className={`w-full text-left px-2.5 py-1 text-xs rounded-lg font-medium transition-colors ${
                          userRole === r
                            ? 'bg-brand-600 text-white font-bold'
                            : 'text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'
                        }`}
                      >
                        {r} Persona
                      </button>
                    ))}
                  </div>
                )}
              </div>

              {/* Links */}
              <div className="py-1">
                <Link
                  to="/profile"
                  onClick={() => setIsProfileMenuOpen(false)}
                  className="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                >
                  <UserIcon className="w-4 h-4 text-slate-400" />
                  Your Profile & Settings
                </Link>
              </div>

              <div className="pt-1 border-t border-slate-100 dark:border-slate-800">
                <button
                  onClick={() => {
                    setIsProfileMenuOpen(false);
                    signOut();
                    navigate('/login');
                  }}
                  className="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                >
                  <LogOut className="w-4 h-4" />
                  Sign Out
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
