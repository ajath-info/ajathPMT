import React from 'react';
import { useNavigate, useLocation, Link } from 'react-router-dom';
import {
  ArrowLeft,
  Users,
  Settings,
  LayoutDashboard,
  CheckSquare,
  Columns,
  MessageSquare,
  Folder,
  Calendar,
  MessageCircle,
  Activity,
  UserPlus,
} from 'lucide-react';
import { Project } from '../../types';
import { Avatar } from '../common/Avatar';
import { useAuth } from '../../context/AuthContext';

interface ProjectHeaderProps {
  project: Project;
  toolSettings?: any;
  onOpenMembersModal: () => void;
  onOpenEditModal: () => void;
}

export function ProjectHeader({ project, onOpenMembersModal, onOpenEditModal }: ProjectHeaderProps) {
  const navigate = useNavigate();
  const location = useLocation();
  const { userRole } = useAuth();

  const isOverview =
    location.pathname === `/projects/${project.id}` || location.pathname === `/projects/${project.id}/`;

  const tabs = [
    { label: 'Overview', path: `/projects/${project.id}`, icon: LayoutDashboard },
    { label: 'To-Dos', path: `/projects/${project.id}/todos`, icon: CheckSquare },
    { label: 'Card Table', path: `/projects/${project.id}/board`, icon: Columns, internalOnly: true },
    { label: 'Discussions', path: `/projects/${project.id}/discussions`, icon: MessageSquare },
    { label: 'Docs & Files', path: `/projects/${project.id}/docs`, icon: Folder },
    { label: 'Calendar', path: `/projects/${project.id}/calendar`, icon: Calendar },
    { label: 'Chat', path: `/projects/${project.id}/chat`, icon: MessageCircle, internalOnly: true },
    { label: 'Activity', path: `/projects/${project.id}/activity`, icon: Activity },
  ];

  const visibleTabs = tabs.filter((t) => {
    if (userRole === 'CLIENT' && t.internalOnly) return false;
    return true;
  });

  const isTabActive = (tabPath: string) => {
    if (tabPath === `/projects/${project.id}`) {
      return isOverview;
    }
    return location.pathname.startsWith(tabPath);
  };

  return (
    <div className="bg-white dark:bg-slate-900 border-b border-slate-200/80 dark:border-slate-800 -mx-2 sm:-mx-4 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3 mb-6 transition-all">
      <div className="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
        {/* Left: Back Link, Title & Status */}
        <div className="flex items-center gap-3">
          <button
            onClick={() => navigate('/projects/directory')}
            className="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            title="Back to all projects"
          >
            <ArrowLeft className="w-4 h-4" />
          </button>

          <div className="space-y-0.5">
            <div className="flex items-center gap-2">
              <h1 className="font-extrabold text-lg sm:text-xl text-slate-900 dark:text-white tracking-tight leading-tight">
                {project.name}
              </h1>
              <span className="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60">
                {project.status || 'Active'}
              </span>
            </div>
            {project.description && (
              <p className="text-xs text-slate-500 dark:text-slate-400 truncate max-w-md">
                {project.description}
              </p>
            )}
          </div>
        </div>

        {/* Right: Team Members & Settings */}
        <div className="flex items-center gap-2.5 self-end md:self-auto">
          {/* Members Avatars Stack */}
          {userRole !== 'CLIENT' ? (
            <button
              onClick={onOpenMembersModal}
              className="flex items-center gap-2 px-2.5 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer group border border-slate-200/60 dark:border-slate-700/60"
              title="Manage project team"
            >
              <div className="flex -space-x-1.5 overflow-hidden">
                {project.members &&
                  project.members.slice(0, 3).map((pm) => (
                    <Avatar
                      key={pm.id}
                      src={pm.profile?.avatar_url}
                      name={pm.profile?.full_name || 'Member'}
                      size="xs"
                    />
                  ))}
              </div>
              <span className="text-xs font-semibold text-slate-600 dark:text-slate-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                {project.members?.length || 1} People
              </span>
              <UserPlus className="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400" />
            </button>
          ) : (
            <div
              className="flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 select-none"
              title="Project team members"
            >
              <div className="flex -space-x-1.5 overflow-hidden">
                {project.members &&
                  project.members.slice(0, 3).map((pm) => (
                    <Avatar
                      key={pm.id}
                      src={pm.profile?.avatar_url}
                      name={pm.profile?.full_name || 'Member'}
                      size="xs"
                    />
                  ))}
              </div>
              <span className="text-xs font-semibold text-slate-600 dark:text-slate-300">
                {project.members?.length || 1} People
              </span>
            </div>
          )}

          {/* Settings Button (Owner & Admin only) */}
          {['OWNER', 'ADMIN'].includes(userRole) && (
            <button
              onClick={() => navigate(`/projects/${project.id}/settings`)}
              className="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 transition-colors cursor-pointer"
              title="Project settings"
            >
              <Settings className="w-4 h-4" />
            </button>
          )}
        </div>
      </div>

      {/* Tabs Navigation Bar */}
      <div className="max-w-7xl mx-auto flex items-center gap-1 overflow-x-auto pt-3 mt-2 border-t border-slate-100 dark:border-slate-800/80 custom-scrollbar">
        {visibleTabs.map((tab) => {
          const Icon = tab.icon;
          const active = isTabActive(tab.path);
          return (
            <Link
              key={tab.path}
              to={tab.path}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap ${
                active
                  ? 'bg-blue-600 text-white shadow-xs'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/80'
              }`}
            >
              <Icon className={`w-3.5 h-3.5 ${active ? 'text-white' : 'text-slate-400'}`} />
              <span>{tab.label}</span>
            </Link>
          );
        })}
      </div>
    </div>
  );
}
