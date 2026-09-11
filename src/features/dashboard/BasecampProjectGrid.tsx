import React from 'react';
import { useNavigate } from 'react-router-dom';
import { Star, Folder, ArrowRight, LayoutGrid, CheckCircle2 } from 'lucide-react';
import { Project } from '../../types';
import { useAuth } from '../../context/AuthContext';

interface BasecampProjectGridProps {
  projects: Project[];
  onToggleStar: (projectId: string, e: React.MouseEvent) => void;
  starredProjectIds: Set<string>;
  onSeeAllProjects: () => void;
  isExpanded?: boolean;
  folders?: string[];
  onOpenFolder?: (folderName: string) => void;
}

const AVATAR_COLORS: Record<string, string> = {
  AI: 'bg-teal-500',
  GK: 'bg-pink-500',
  PK: 'bg-cyan-500',
  RK: 'bg-rose-500',
  SN: 'bg-purple-600',
  CW: 'bg-indigo-600',
  E: 'bg-orange-500',
  B: 'bg-amber-500',
  BGD: 'bg-red-500',
  PT: 'bg-slate-500',
  RASGO: 'bg-emerald-600',
  TL: 'bg-indigo-600',
  AR: 'bg-teal-600',
};

function getInitials(fullName?: string): string {
  if (!fullName) return 'CW';
  if (fullName === 'Claire Watson') return 'CW';
  if (fullName === 'Ajath Infotech') return 'AI';
  if (fullName === 'Gaurav Kumar') return 'GK';
  if (fullName === 'Pankaj Kumar') return 'PK';
  if (fullName === 'Rohit Kumar') return 'RK';
  if (fullName === 'Edward') return 'E';
  if (fullName.includes('Bhawan')) return 'B';
  if (fullName === 'BIPL Global Dev') return 'BGD';
  if (fullName === 'Pradeep Tiwari') return 'PT';
  if (fullName === 'Rasgo') return 'RASGO';
  if (fullName === 'Tech Lead') return 'TL';
  if (fullName === 'Amit Roy') return 'AR';

  const parts = fullName.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function getAvatarColor(initials: string): string {
  if (AVATAR_COLORS[initials]) return AVATAR_COLORS[initials];
  const defaults = ['bg-indigo-500', 'bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-purple-500'];
  let hash = 0;
  for (let i = 0; i < initials.length; i++) {
    hash = initials.charCodeAt(i) + ((hash << 5) - hash);
  }
  return defaults[Math.abs(hash) % defaults.length];
}

export function BasecampProjectGrid({
  projects,
  onToggleStar,
  starredProjectIds,
  onSeeAllProjects,
  isExpanded = false,
  folders = [],
  onOpenFolder,
}: BasecampProjectGridProps) {
  const navigate = useNavigate();
  const { profile } = useAuth();
  const currentUserName = profile?.full_name || 'Claire Watson';
  const currentUserInitials = getInitials(currentUserName);

  return (
    <div className="space-y-6 animate-in fade-in duration-300">
      {/* Modern 4-Column Responsive Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
        {/* Modern Folder Cards */}
        {folders.map((folderName, idx) => {
          let assignedCount = 0;
          try {
            const raw = localStorage.getItem(`basecamp_folder_${folderName}`);
            if (raw) assignedCount = JSON.parse(raw).length;
          } catch (e) {}

          return (
            <div
              key={`folder-${idx}`}
              onClick={() => onOpenFolder?.(folderName)}
              className="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-md hover:border-blue-400 dark:hover:border-blue-600 hover:-translate-y-0.5 transition-all duration-200 cursor-pointer flex flex-col justify-between min-h-[185px] group select-none relative"
            >
              <div className="flex items-center justify-between">
                <div className="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 flex items-center justify-center border border-amber-200/60 dark:border-amber-800/40 group-hover:scale-105 transition-transform">
                  <Folder className="w-4 h-4 fill-amber-400/30" />
                </div>
                <span className="text-[11px] font-bold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                  {assignedCount} {assignedCount === 1 ? 'project' : 'projects'}
                </span>
              </div>

              <div className="space-y-1 my-auto pt-2">
                <h3 className="font-bold text-[15px] text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors leading-snug">
                  {folderName}
                </h3>
                <p className="text-xs text-slate-400 dark:text-slate-500">
                  Grouped workspace collection
                </p>
              </div>

              <div className="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                <span className="font-medium text-[11px]">View folder items</span>
                <ArrowRight className="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
              </div>
            </div>
          );
        })}

        {/* Modern Project Cards */}
        {projects.map((project, idx) => {
          const isStarred = starredProjectIds.has(project.id);
          const members = project.members || [];
          // Calculate a realistic completion progress based on project index
          const progressPercent = Math.min(95, Math.max(30, ((idx + 2) * 19) % 100));

          return (
            <div
              key={project.id}
              onClick={() => navigate(`/projects/${project.id}`)}
              className="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-lg hover:shadow-blue-500/5 hover:border-blue-400 dark:hover:border-blue-600 hover:-translate-y-0.5 transition-all duration-200 cursor-pointer flex flex-col justify-between min-h-[185px] group select-none relative"
            >
              {/* Card Header: Category & Star */}
              <div className="space-y-2">
                <div className="flex items-center justify-between gap-2">
                  <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">
                    {project.status || 'Active'}
                  </span>

                  {/* Star Toggle */}
                  <button
                    onClick={(e) => onToggleStar(project.id, e)}
                    title={isStarred ? 'Unstar project' : 'Star project'}
                    className="p-1 -mr-1 -mt-1 text-slate-300 hover:text-amber-400 transition-colors shrink-0 cursor-pointer"
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

                {/* Project Title */}
                <h3 className="font-bold text-[15px] leading-snug text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                  {project.name}
                </h3>

                {/* Description */}
                {project.description && (
                  <p className="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                    {project.description}
                  </p>
                )}
              </div>

              {/* Card Footer: Progress & Avatar Stack */}
              <div className="pt-4 space-y-2.5">
                {/* Modern Progress Bar */}
                <div className="space-y-1">
                  <div className="flex items-center justify-between text-[10px] text-slate-400 font-medium">
                    <span>Progress</span>
                    <span>{progressPercent}%</span>
                  </div>
                  <div className="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div
                      className="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full transition-all duration-500"
                      style={{ width: `${progressPercent}%` }}
                    />
                  </div>
                </div>

                {/* Avatar Stack */}
                <div className="flex items-center justify-between pt-1">
                  <div className="flex -space-x-1.5 overflow-hidden py-0.5">
                    {members.slice(0, 5).map((m, mIdx) => {
                      const isCurrentUser =
                        m.user_id === 'demo-user-owner' ||
                        m.user_id === 'demo-user-client' ||
                        m.user_id === profile?.id ||
                        m.profile?.full_name === 'Shiv Narayan' ||
                        m.profile?.full_name === 'Shivy Narain';

                      const fullName = isCurrentUser ? currentUserName : m.profile?.full_name || 'Member';
                      const initials = isCurrentUser ? currentUserInitials : getInitials(fullName);
                      const colorClass = getAvatarColor(initials);

                      return (
                        <div
                          key={m.id || mIdx}
                          title={fullName}
                          className={`w-6 h-6 rounded-full ${colorClass} text-white font-bold text-[9px] tracking-tight flex items-center justify-center ring-2 ring-white dark:ring-slate-900 shadow-xs select-none`}
                        >
                          {initials}
                        </div>
                      );
                    })}
                    {members.length > 5 && (
                      <div className="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-[9px] flex items-center justify-center ring-2 ring-white dark:ring-slate-900">
                        +{members.length - 5}
                      </div>
                    )}
                  </div>

                  <span className="text-[11px] font-semibold text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors flex items-center gap-1">
                    Open <ArrowRight className="w-3 h-3 group-hover:translate-x-0.5 transition-transform" />
                  </span>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Modern Centered "See all projects" Action */}
      <div className="flex justify-center pt-3 pb-4">
        <button
          onClick={onSeeAllProjects}
          className="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 transition-all shadow-xs hover:shadow-sm cursor-pointer"
        >
          <LayoutGrid className="w-3.5 h-3.5 text-slate-500" />
          <span>{isExpanded ? 'Show Fewer Projects' : `See All Projects (${projects.length})`}</span>
        </button>
      </div>
    </div>
  );
}
