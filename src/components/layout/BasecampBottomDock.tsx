import React, { useState, useRef, useEffect } from 'react';
import {
  HelpCircle,
  User,
  FileText,
  Rocket,
  Settings,
  LogOut,
  CheckSquare,
  Calendar,
  Bookmark,
  Activity,
  StickyNote,
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { DockDrawerType } from '../modals/DockDrawers';
import { getInitials } from '../../lib/utils';

interface BasecampBottomDockProps {
  activeDrawer?: DockDrawerType;
  onOpenDrawer: (type: DockDrawerType) => void;
  onOpenSupport: () => void;
  onOpenProfile: () => void;
}

export function BasecampBottomDock({
  activeDrawer,
  onOpenDrawer,
  onOpenSupport,
  onOpenProfile,
}: BasecampBottomDockProps) {
  const { profile, signOut } = useAuth();
  const navigate = useNavigate();
  const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);
  const userMenuRef = useRef<HTMLDivElement>(null);

  // Close popup menu when clicking outside
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (userMenuRef.current && !userMenuRef.current.contains(e.target as Node)) {
        setIsUserMenuOpen(false);
      }
    };
    if (isUserMenuOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isUserMenuOpen]);

  const quickItems = [
    { type: 'tasks' as const, label: 'Tasks', icon: CheckSquare },
    { type: 'events' as const, label: 'Schedule', icon: Calendar },
    { type: 'bookmarks' as const, label: 'Saved', icon: Bookmark },
    { type: 'activity' as const, label: 'Activity', icon: Activity },
    { type: 'notes' as const, label: 'Scratchpad', icon: StickyNote },
  ];

  return (
    <aside
      aria-label="Quick Actions"
      className="fixed bottom-4 left-1/2 -translate-x-1/2 z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 rounded-2xl px-2 py-1.5 shadow-xl shadow-slate-900/10 flex items-center gap-1 sm:gap-2 transition-all"
    >
      {/* Mini Profile Trigger */}
      <div className="relative" ref={userMenuRef}>
        <button
          onClick={() => setIsUserMenuOpen(!isUserMenuOpen)}
          className="w-7 h-7 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 hover:opacity-90 text-white font-bold text-[11px] flex items-center justify-center shadow-xs transition-all cursor-pointer active:scale-95 overflow-hidden"
          title={profile?.full_name || 'My Account'}
        >
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
        </button>

        {/* Quick Menu */}
        {isUserMenuOpen && (
          <div className="absolute bottom-10 left-0 w-52 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200/80 dark:border-slate-800 py-2 px-1 z-50 animate-in fade-in zoom-in-95 duration-150">
            <div className="px-3 py-1.5 border-b border-slate-100 dark:border-slate-800 mb-1">
              <span className="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                My Workspace
              </span>
            </div>
            <button
              onClick={() => {
                setIsUserMenuOpen(false);
                onOpenProfile();
              }}
              className="w-full flex items-center gap-2.5 px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors font-medium text-left"
            >
              <User className="w-3.5 h-3.5 text-slate-400" />
              <span>Profile Settings</span>
            </button>
            <button
              onClick={() => {
                setIsUserMenuOpen(false);
                navigate('/my/drafts');
              }}
              className="w-full flex items-center gap-2.5 px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors font-medium text-left"
            >
              <FileText className="w-3.5 h-3.5 text-slate-400" />
              <span>Drafts</span>
            </button>
            <button
              onClick={() => {
                setIsUserMenuOpen(false);
                navigate('/my/boosts');
              }}
              className="w-full flex items-center gap-2.5 px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors font-medium text-left"
            >
              <Rocket className="w-3.5 h-3.5 text-slate-400" />
              <span>Boosts & Cheers</span>
            </button>
            <button
              onClick={() => {
                setIsUserMenuOpen(false);
                signOut();
                navigate('/login');
              }}
              className="w-full flex items-center gap-2.5 px-3 py-1.5 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors font-semibold text-left border-t border-slate-100 dark:border-slate-800 mt-1"
            >
              <LogOut className="w-3.5 h-3.5" />
              <span>Sign out</span>
            </button>
          </div>
        )}
      </div>

      <div className="w-[1px] h-4 bg-slate-200 dark:bg-slate-800" />

      {/* Modern Compact Quick Drawer Items */}
      <nav className="flex items-center gap-1">
        {quickItems.map((item) => {
          const Icon = item.icon;
          const isActive = activeDrawer === item.type;
          return (
            <button
              key={item.type}
              onClick={() => onOpenDrawer(isActive ? null : item.type)}
              className={`flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer ${
                isActive
                  ? 'bg-blue-600 text-white shadow-xs'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-slate-800/80'
              }`}
              title={`Open ${item.label}`}
            >
              <Icon className={`w-3.5 h-3.5 ${isActive ? 'text-white' : 'text-slate-400'}`} />
              <span className="hidden sm:inline">{item.label}</span>
            </button>
          );
        })}
      </nav>

      <div className="w-[1px] h-4 bg-slate-200 dark:bg-slate-800" />

      {/* Support Icon Button */}
      <button
        onClick={onOpenSupport}
        className="p-1.5 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        title="Help & Documentation"
      >
        <HelpCircle className="w-4 h-4" />
      </button>
    </aside>
  );
}
