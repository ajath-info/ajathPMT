import React from 'react';
import { NavLink } from 'react-router-dom';
import {
  Home,
  CheckSquare,
  MessageSquare,
  Calendar,
  Bell,
  FolderKanban,
  Users,
  UserCheck,
  BarChart3,
  Settings,
  ChevronLeft,
  ChevronRight,
  Layers,
  ShieldCheck,
  X,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { OrgSwitcher } from './OrgSwitcher';
import { Badge } from '../common/Badge';
import { cn } from '../../lib/utils';

interface SidebarProps {
  isCollapsed: boolean;
  onToggleCollapse: () => void;
  isMobileOpen: boolean;
  onCloseMobile: () => void;
}

export function Sidebar({ isCollapsed, onToggleCollapse, isMobileOpen, onCloseMobile }: SidebarProps) {
  const { userRole } = useAuth();
  const { members } = useOrganization();

  const navItems = [
    { label: 'Home', path: '/dashboard', icon: Home },
    { label: 'Lineup', path: '/projects', icon: FolderKanban },
    { label: 'Pings', path: '/messages', icon: MessageSquare, badge: '2' },
    { label: 'Hey!', path: '/notifications', icon: Bell, badge: '3' },
    { label: 'My Stuff', path: '/my-tasks', icon: CheckSquare, badge: '4' },
    { label: 'Teams', path: '/teams', icon: Users },
    { label: 'Members', path: '/members', icon: UserCheck, badge: String(members.length || 10) },
    { label: 'Schedule', path: '/calendar', icon: Calendar },
    { label: 'Activity & Reports', path: '/reports', icon: BarChart3 },
    { label: 'Settings', path: '/settings', icon: Settings },
  ];

  const roleColors = {
    OWNER: 'brand',
    ADMIN: 'warning',
    MEMBER: 'neutral',
    CLIENT: 'success',
  } as const;

  return (
    <>
      {/* Drawer backdrop */}
      {isMobileOpen && (
        <div
          className="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm transition-opacity"
          onClick={onCloseMobile}
        />
      )}

      <aside
        className={cn(
          'fixed top-0 bottom-0 left-0 z-50 flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300 ease-in-out shadow-2xl',
          'w-72',
          isMobileOpen ? 'translate-x-0' : '-translate-x-full'
        )}
      >
        {/* Workspace Brand Header */}
        <div className="h-16 flex items-center justify-between px-4 border-b border-slate-100 dark:border-slate-800">
          <div className="flex items-center gap-3 overflow-hidden">
            <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 via-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20 shrink-0">
              <Layers className="w-5 h-5" />
            </div>
            <div className="truncate">
              <h1 className="font-bold text-base tracking-tight text-slate-900 dark:text-slate-100 truncate">
                Ajath PMT
              </h1>
              <p className="text-[10px] font-semibold tracking-wider uppercase text-brand-600 dark:text-brand-400">
                Ajath Workspace
              </p>
            </div>
          </div>

          <button
            onClick={onCloseMobile}
            className="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            title="Close drawer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Multi-Organization Switcher */}
        <OrgSwitcher isCollapsed={isCollapsed} />

        {/* User Role Banner */}
        {!isCollapsed && (
          <div className="px-4 py-2.5 mx-3 my-1 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div className="flex items-center gap-2">
              <ShieldCheck className="w-4 h-4 text-brand-600 dark:text-brand-400" />
              <span className="text-xs font-semibold text-slate-700 dark:text-slate-300">Access Level</span>
            </div>
            <Badge variant={roleColors[userRole]}>{userRole}</Badge>
          </div>
        )}

        {/* Navigation Items */}
        <nav className="flex-1 px-3 py-3 space-y-1 overflow-y-auto custom-scrollbar">
          {navItems.map((item) => (
            <NavLink
              key={item.path}
              to={item.path}
              onClick={onCloseMobile}
              className={({ isActive }) =>
                cn(
                  'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group relative',
                  isActive
                    ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300 font-semibold shadow-xs'
                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-slate-900 dark:hover:text-slate-100'
                )
              }
            >
              <item.icon className="w-5 h-5 shrink-0 transition-transform group-hover:scale-105" />
              {!isCollapsed && <span className="truncate">{item.label}</span>}
              {!isCollapsed && item.badge && (
                <span className="ml-auto px-2 py-0.5 text-xs font-bold rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300">
                  {item.badge}
                </span>
              )}

              {/* Tooltip on collapsed state */}
              {isCollapsed && (
                <div className="absolute left-full ml-3 px-2 py-1 bg-slate-900 text-white text-xs font-medium rounded shadow-md opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-50 whitespace-nowrap">
                  {item.label}
                </div>
              )}
            </NavLink>
          ))}
        </nav>
      </aside>
    </>
  );
}
