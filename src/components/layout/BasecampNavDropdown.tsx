import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  TrendingUp,
  Calendar as CalendarIcon,
  PieChart,
  Globe,
  MessageSquare,
  Search,
  Building2,
  Plus,
  Check,
  CheckSquare,
  FileText,
  X,
} from 'lucide-react';
import { useProject } from '../../context/ProjectContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useAuth } from '../../context/AuthContext';

export interface RecentVisitedItem {
  id: string;
  title: string;
  subtitle?: string;
  type: 'discussion' | 'project' | 'document' | 'todo' | 'chat';
  path: string;
  visitedAt?: number;
}

const RECENT_STORAGE_KEY_PREFIX = 'basecamp_recently_visited_';

export function getRecentlyVisitedItems(orgId?: string): RecentVisitedItem[] {
  if (!orgId) return [];
  try {
    const raw = localStorage.getItem(`${RECENT_STORAGE_KEY_PREFIX}${orgId}`);
    if (raw) {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed) && parsed.length > 0) return parsed;
    }
  } catch (e) {
    console.error('Failed to parse recently visited items', e);
  }

  // Pre-seeded demo items exclusively for demo-org-acme customer tenant
  if (orgId === 'demo-org-acme') {
    return [
      {
        id: 'recent-1',
        title: 'MOM 08/09/2026',
        subtitle: 'Ajath Infotech Pvt Ltd HQ',
        type: 'discussion',
        path: '/projects/proj-hq/discussions',
      },
      {
        id: 'recent-2',
        title: 'Message Board',
        subtitle: 'Ajath Infotech Pvt Ltd HQ',
        type: 'discussion',
        path: '/projects/proj-hq/discussions',
      },
      {
        id: 'recent-3',
        title: 'Ajath Infotech Pvt Ltd HQ',
        subtitle: '',
        type: 'project',
        path: '/projects/proj-hq',
      },
      {
        id: 'recent-4',
        title: 'MOM 07/09/2026',
        subtitle: 'Ajath Infotech Pvt Ltd HQ',
        type: 'discussion',
        path: '/projects/proj-hq/discussions',
      },
      {
        id: 'recent-5',
        title: 'Worklog 07/09/2026',
        subtitle: 'Ajath Infotech Pvt Ltd HQ',
        type: 'discussion',
        path: '/projects/proj-hq/discussions',
      },
    ];
  }

  // Brand new workspaces or fresh admins start completely clean
  return [];
}

export function recordRecentlyVisitedItem(
  orgId: string,
  item: Omit<RecentVisitedItem, 'visitedAt'>
) {
  if (!orgId) return;
  try {
    const key = `${RECENT_STORAGE_KEY_PREFIX}${orgId}`;
    const raw = localStorage.getItem(key);
    let items: RecentVisitedItem[] = [];
    if (raw) {
      try {
        items = JSON.parse(raw);
        if (!Array.isArray(items)) items = [];
      } catch {
        items = [];
      }
    }
    // Remove if already exists with same path or id
    items = items.filter((i) => i.path !== item.path && i.id !== item.id);
    // Add to beginning
    items.unshift({
      ...item,
      visitedAt: Date.now(),
    });
    // Cap at 10 items
    items = items.slice(0, 10);
    localStorage.setItem(key, JSON.stringify(items));
  } catch (e) {
    console.error('Failed to record recently visited item', e);
  }
}

interface BasecampNavDropdownProps {
  isOpen: boolean;
  onClose: () => void;
  onOpenCreateOrg?: () => void;
}

export function BasecampNavDropdown({ isOpen, onClose, onOpenCreateOrg }: BasecampNavDropdownProps) {
  const navigate = useNavigate();
  const { userRole } = useAuth();
  const { projects } = useProject();
  const { currentOrganization, organizations, switchOrganization } = useOrganization();
  const [searchTerm, setSearchTerm] = useState('');
  const [recentItems, setRecentItems] = useState<RecentVisitedItem[]>([]);
  const inputRef = useRef<HTMLInputElement>(null);
  const dropdownRef = useRef<HTMLDivElement>(null);

  // Focus input and load tenant-scoped recently visited items
  useEffect(() => {
    if (isOpen) {
      setTimeout(() => inputRef.current?.focus(), 50);
      setRecentItems(getRecentlyVisitedItems(currentOrganization?.id));
    } else {
      setSearchTerm('');
    }
  }, [isOpen, currentOrganization?.id]);

  // Click outside listener
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(e.target as Node)) {
        onClose();
      }
    };
    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [isOpen, onClose]);

  // Escape key listener
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        onClose();
      }
    };
    if (isOpen) {
      window.addEventListener('keydown', handleKeyDown);
    }
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [isOpen, onClose]);

  const filteredProjects = projects.filter(
    (p) =>
      p.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (p.description && p.description.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  const filteredRecent = recentItems.filter(
    (item) =>
      item.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (item.subtitle && item.subtitle.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  if (!isOpen) return null;

  return (
    <div
      ref={dropdownRef}
      className="absolute left-1/2 -translate-x-1/2 mt-2 w-[460px] max-w-[95vw] bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/90 dark:border-slate-800 p-4 z-50 animate-in zoom-in-95 duration-150 text-left select-none"
    >
      {/* Header bar with title and close button */}
      <div className="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 dark:border-slate-800">
        <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Jump / Quick Switch</span>
        <button
          type="button"
          onClick={onClose}
          className="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          title="Close"
        >
          <X className="w-3.5 h-3.5" />
        </button>
      </div>

      {/* 1. Top Quick Action Tiles: Activity, Calendar, Reports, Everything */}
      <div className="grid grid-cols-4 gap-2 pb-3">
        {/* Activity */}
        <button
          onClick={() => {
            onClose();
            navigate('/activity');
          }}
          className="flex flex-col items-center justify-center p-3 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-all text-slate-700 dark:text-slate-200 group"
        >
          <TrendingUp className="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white group-hover:scale-110 transition-transform mb-1.5" />
          <span className="text-xs font-bold tracking-tight">Activity</span>
        </button>

        {/* Calendar */}
        <button
          onClick={() => {
            onClose();
            navigate('/calendar');
          }}
          className="flex flex-col items-center justify-center p-3 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-all text-slate-700 dark:text-slate-200 group"
        >
          <CalendarIcon className="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white group-hover:scale-110 transition-transform mb-1.5" />
          <span className="text-xs font-bold tracking-tight">Calendar</span>
        </button>

        {/* Reports (Active highlighted styling matching screenshot) */}
        <button
          onClick={() => {
            onClose();
            navigate('/reports');
          }}
          className="flex flex-col items-center justify-center p-3 rounded-2xl bg-blue-50/80 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60 transition-all group shadow-xs"
        >
          <PieChart className="w-5 h-5 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform mb-1.5" />
          <span className="text-xs font-extrabold tracking-tight">Reports</span>
        </button>

        {/* Everything */}
        <button
          onClick={() => {
            onClose();
            navigate('/everything');
          }}
          className="flex flex-col items-center justify-center p-3 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-all text-slate-700 dark:text-slate-200 group"
        >
          <Globe className="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white group-hover:scale-110 transition-transform mb-1.5" />
          <span className="text-xs font-bold tracking-tight">Everything</span>
        </button>
      </div>

      {/* 2. Search Input */}
      <div className="pb-3">
        <div className="relative">
          <input
            ref={inputRef}
            type="text"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            placeholder="Search or jump to a project, person, or recent page"
            className="w-full px-3.5 py-2 text-xs rounded-xl border border-blue-500 ring-2 ring-blue-500/20 focus:outline-none placeholder-slate-400 font-medium text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 transition-all"
          />
        </div>
      </div>

      {/* 3. Scrollable List of Recently Visited & Projects */}
      <div className="max-h-[380px] overflow-y-auto space-y-4 custom-scrollbar pr-1">
        {/* Section: Organizations / Workspaces */}
        <div className="space-y-1 pb-2 border-b border-slate-100 dark:border-slate-800">
          <div className="flex items-center justify-between px-1 pb-1">
            <h4 className="text-xs font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
              <Building2 className="w-3.5 h-3.5 text-indigo-500" />
              <span>{organizations.length <= 1 ? 'Company Workspace' : `Organizations (${organizations.length})`}</span>
            </h4>
            {onOpenCreateOrg && organizations.length === 0 && (
              <button
                type="button"
                onClick={() => {
                  onClose();
                  onOpenCreateOrg();
                }}
                className="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 flex items-center gap-1 cursor-pointer"
              >
                <Plus className="w-3 h-3 stroke-[3]" />
                <span>+ Create Company</span>
              </button>
            )}
          </div>
          {organizations.length === 1 && (
            <p className="text-[10px] text-slate-400 font-medium px-1 pb-1">
              One admin manages one registered company workspace.
            </p>
          )}
          <div className="space-y-1">
            {organizations.map((org) => {
              const isActive = currentOrganization?.id === org.id;
              return (
                <div
                  key={org.id}
                  onClick={() => {
                    switchOrganization(org.id);
                    onClose();
                  }}
                  className={`flex items-center justify-between px-2.5 py-1.5 rounded-xl transition-all text-xs ${
                    isActive
                      ? 'bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/80 dark:border-indigo-800 text-indigo-900 dark:text-indigo-100 font-bold shadow-2xs'
                      : 'hover:bg-slate-100/80 dark:hover:bg-slate-800/60 text-slate-700 dark:text-slate-300 font-medium cursor-pointer'
                  }`}
                >
                  <div className="flex items-center gap-2 min-w-0">
                    <div className="w-5 h-5 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0">
                      {org.name.charAt(0).toUpperCase()}
                    </div>
                    <div className="min-w-0">
                      <span className="truncate block font-bold">{org.name}</span>
                      {isActive && (
                        <span className="text-[10px] text-slate-400 dark:text-slate-500 font-normal">
                          Active Workspace
                        </span>
                      )}
                    </div>
                  </div>
                  {isActive ? (
                    <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-indigo-600 text-white">
                      Active
                    </span>
                  ) : (
                    <span className="text-[10px] text-slate-400 hover:text-indigo-600">Switch</span>
                  )}
                </div>
              );
            })}
          </div>
        </div>

        {/* Section: Recently Visited */}
        {filteredRecent.length > 0 && (
          <div className="space-y-1">
            <h4 className="text-xs font-extrabold text-slate-900 dark:text-slate-100 px-1 pb-1">
              Recently visited
            </h4>
            <div className="space-y-0.5">
              {filteredRecent.map((item, idx) => (
                <div
                  key={item.id}
                  onClick={() => {
                    onClose();
                    navigate(item.path);
                  }}
                  className={`flex items-center gap-2.5 px-2.5 py-2 rounded-xl cursor-pointer transition-colors text-xs ${
                    idx === 0
                      ? 'bg-blue-50/80 dark:bg-blue-950/40 text-blue-900 dark:text-blue-100'
                      : 'hover:bg-slate-100/80 dark:hover:bg-slate-800/60 text-slate-800 dark:text-slate-200'
                  }`}
                >
                  {/* Icon */}
                  {item.type === 'discussion' ? (
                    <div className="w-5 h-5 rounded-full bg-blue-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                      <MessageSquare className="w-3 h-3 fill-current" />
                    </div>
                  ) : item.type === 'todo' ? (
                    <div className="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                      <CheckSquare className="w-3 h-3" />
                    </div>
                  ) : item.type === 'document' ? (
                    <div className="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                      <FileText className="w-3 h-3" />
                    </div>
                  ) : (
                    <div className="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0 shadow-xs">
                      <svg
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        className="w-3 h-3"
                      >
                        <path d="m8 3 4 8 5-5 5 15H2L8 3z" />
                      </svg>
                    </div>
                  )}

                  {/* Title & Subtitle */}
                  <div className="truncate flex items-center gap-1.5 min-w-0">
                    <span className="font-bold truncate text-slate-900 dark:text-slate-100">
                      {item.title}
                    </span>
                    {item.subtitle && (
                      <span className="text-slate-400 font-normal truncate">
                        • {item.subtitle}
                      </span>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Section: Projects */}
        {filteredProjects.length > 0 && (
          <div className="space-y-1 pt-1">
            <div className="flex items-center gap-1.5 px-1 pb-1">
              <h4 className="text-xs font-extrabold text-slate-900 dark:text-slate-100">
                Projects
              </h4>
              <button
                onClick={() => {
                  onClose();
                  navigate('/projects');
                }}
                className="text-xs text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-medium"
              >
                — See all
              </button>
            </div>

            <div className="space-y-0.5">
              {filteredProjects.map((p) => {
                let descriptionSuffix = '';
                if (p.description) {
                  if (p.name.includes('Ajath Infotech')) {
                    descriptionSuffix = `- ${p.description}`;
                  } else {
                    descriptionSuffix = `- for ${p.description}`;
                  }
                }

                return (
                  <div
                    key={p.id}
                    onClick={() => {
                      onClose();
                      navigate(`/projects/${p.id}`);
                    }}
                    className="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-slate-100/80 dark:hover:bg-slate-800/60 cursor-pointer transition-colors text-xs group"
                  >
                    {/* Project Mountain Icon Badge */}
                    <div className="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-200 flex items-center justify-center shrink-0 shadow-xs">
                      <svg
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        className="w-3 h-3"
                      >
                        <path d="m8 3 4 8 5-5 5 15H2L8 3z" />
                      </svg>
                    </div>

                    {/* Project Name and Subtitle */}
                    <div className="truncate flex items-center gap-1.5 min-w-0">
                      <span className="font-bold truncate text-slate-900 dark:text-slate-100 group-hover:text-brand-600 transition-colors">
                        {p.name}
                      </span>
                      {descriptionSuffix && (
                        <span className="text-slate-400 font-normal truncate">
                          {descriptionSuffix}
                        </span>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {/* Empty state if nothing matches search or brand new workspace */}
        {filteredRecent.length === 0 && filteredProjects.length === 0 && (
          <div className="py-8 text-center text-xs text-slate-400">
            {searchTerm ? `No projects or pages matching "${searchTerm}"` : 'No recently visited pages yet'}
          </div>
        )}
      </div>
    </div>
  );
}
