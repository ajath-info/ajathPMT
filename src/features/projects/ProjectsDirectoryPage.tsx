import React, { useState, useEffect, useMemo } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useProject } from '../../context/ProjectContext';
import { useToast } from '../../context/ToastContext';
import { Search, X, Check, ArrowLeft, Archive, Trash2, RotateCcw, FolderPlus } from 'lucide-react';
import { Project } from '../../types';
import {
  getTrashedProjects,
  restoreTrashedProject,
} from '../../services/projectService';

export function ProjectsDirectoryPage() {
  const navigate = useNavigate();
  const { projects, refreshProjects } = useProject();
  const { addToast } = useToast();

  useEffect(() => {
    document.title = 'Projects';
    return () => {
      document.title = 'Ajath PMT';
    };
  }, []);

  const [activeTab, setActiveTab] = useState<'active' | 'archived' | 'trash'>('active');
  const [searchQuery, setSearchQuery] = useState('');
  const [trashedProjects, setTrashedProjects] = useState<Project[]>([]);
  const [loadingTrash, setLoadingTrash] = useState(false);

  const loadTrash = async () => {
    setLoadingTrash(true);
    try {
      const data = await getTrashedProjects();
      setTrashedProjects(data);
    } catch (e) {
      console.warn('Failed to load trashed projects:', e);
    } finally {
      setLoadingTrash(false);
    }
  };

  useEffect(() => {
    if (activeTab === 'trash') {
      loadTrash();
    }
  }, [activeTab]);

  const handleRestore = async (projectId: string, projectName: string) => {
    try {
      const res = await restoreTrashedProject(projectId);
      if (res.error) {
        addToast(res.error.message || 'Failed to restore project', 'error');
        return;
      }
      addToast(`"${projectName}" restored successfully!`, 'success');
      await loadTrash();
      await refreshProjects();
    } catch {
      addToast('Error restoring project', 'error');
    }
  };

  // Filter projects by active tab and search query
  const filteredProjects = useMemo(() => {
    let sourceList: Project[] = [];
    if (activeTab === 'active') {
      sourceList = projects.filter((p) => p.status !== 'ARCHIVED' && !p.trashed_at);
    } else if (activeTab === 'archived') {
      sourceList = projects.filter((p) => p.status === 'ARCHIVED' && !p.trashed_at);
    } else {
      sourceList = trashedProjects;
    }

    if (!searchQuery.trim()) return sourceList;
    const q = searchQuery.toLowerCase();
    return sourceList.filter(
      (p) => p.name.toLowerCase().includes(q) || p.description?.toLowerCase().includes(q)
    );
  }, [projects, trashedProjects, activeTab, searchQuery]);

  // Group filtered projects alphabetically
  const groupedProjects = useMemo(() => {
    const sorted = [...filteredProjects].sort((a, b) =>
      a.name.localeCompare(b.name, undefined, { sensitivity: 'base' })
    );

    const groups: { [letter: string]: Project[] } = {};

    sorted.forEach((project) => {
      const firstChar = project.name.trim().charAt(0).toUpperCase();
      const letter = /[A-Z]/.test(firstChar) ? firstChar : '#';
      if (!groups[letter]) {
        groups[letter] = [];
      }
      groups[letter].push(project);
    });

    return groups;
  }, [filteredProjects]);

  const sortedLetters = useMemo(() => {
    return Object.keys(groupedProjects).sort((a, b) => {
      if (a === '#') return 1;
      if (b === '#') return -1;
      return a.localeCompare(b);
    });
  }, [groupedProjects]);

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 py-2 pb-16">
      {/* Modern Directory Card */}
      <div className="w-full bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-200/80 dark:border-slate-800 space-y-6">
        
        {/* Card Header Title */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-100 dark:border-slate-800">
          <div>
            <h1 className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-slate-50 tracking-tight leading-tight">
              All Projects
            </h1>
            <p className="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
              Alphabetical directory of company workspaces, client portals, and project hubs
            </p>
          </div>

          <button
            onClick={() => navigate('/projects/new')}
            className="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold text-xs shadow-md shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer flex items-center gap-1.5 self-start sm:self-auto"
          >
            <span>+ New Project</span>
          </button>
        </div>

        {/* Tab Selection: Active / Archived / Trash (Basecamp 4 Parity) */}
        <div className="flex flex-wrap items-center justify-between gap-4 pb-2">
          {/* Tabs */}
          <div className="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
            <button
              onClick={() => setActiveTab('active')}
              className={`px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                activeTab === 'active'
                  ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
              }`}
            >
              Active ({projects.filter((p) => p.status !== 'ARCHIVED' && !p.trashed_at).length})
            </button>
            <button
              onClick={() => setActiveTab('archived')}
              className={`px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                activeTab === 'archived'
                  ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
              }`}
            >
              Archived ({projects.filter((p) => p.status === 'ARCHIVED' && !p.trashed_at).length})
            </button>
            <button
              onClick={() => setActiveTab('trash')}
              className={`px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 ${
                activeTab === 'trash'
                  ? 'bg-white dark:bg-slate-900 text-rose-600 shadow-2xs'
                  : 'text-slate-600 dark:text-slate-400 hover:text-rose-600'
              }`}
            >
              <Trash2 className="w-3.5 h-3.5" />
              <span>Trash (30-day retention)</span>
            </button>
          </div>

          {/* Search Input */}
          <div className="relative">
            <input
              type="text"
              placeholder="Find a project..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-60 sm:w-72 px-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium"
            />
            {searchQuery && (
              <button
                onClick={() => setSearchQuery('')}
                className="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
              >
                <X className="w-3.5 h-3.5" />
              </button>
            )}
          </div>
        </div>

        {/* Quick Letter Navigation Chips */}
        {sortedLetters.length > 1 && (
          <div className="flex items-center gap-1.5 flex-wrap pt-1 pb-2">
            <span className="text-[11px] font-bold text-slate-400 uppercase mr-1">Jump to:</span>
            {sortedLetters.map((l) => (
              <a
                key={l}
                href={`#letter-${l}`}
                className="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors"
              >
                {l}
              </a>
            ))}
          </div>
        )}

        {/* Alphabetical A-Z Project Directory */}
        <div className="pt-2">
          {sortedLetters.length > 0 ? (
            <div className="divide-y divide-slate-100 dark:divide-slate-800/80">
              {sortedLetters.map((letter) => (
                <div
                  key={letter}
                  id={`letter-${letter}`}
                  className="flex items-start py-4 sm:py-5 first:pt-2"
                >
                  {/* Left Column: Letter Indicator */}
                  <div className="w-10 sm:w-14 shrink-0 pt-0.5 select-none">
                    <span className="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-500/15 to-indigo-500/20 text-blue-600 dark:text-blue-400 font-black text-sm flex items-center justify-center border border-blue-200/50 dark:border-blue-900/50 shadow-2xs">
                      {letter}
                    </span>
                  </div>

                  {/* Right Column: Projects starting with this letter */}
                  <div className="space-y-3 flex-1 min-w-0 pr-2">
                    {groupedProjects[letter].map((project) => {
                      const isTrashed = Boolean(project.trashed_at);

                      return (
                        <div
                          key={project.id}
                          className="text-[14px] sm:text-[15px] leading-relaxed flex flex-wrap items-center justify-between gap-2"
                        >
                          <div className="flex items-baseline gap-1.5 flex-wrap">
                            {isTrashed ? (
                              <span className="font-bold text-slate-500 line-through">
                                {project.name}
                              </span>
                            ) : (
                              <Link
                                to={`/projects/${project.id}`}
                                className="font-bold text-slate-900 dark:text-slate-100 hover:text-blue-600 dark:hover:text-blue-400 hover:underline transition-colors"
                              >
                                {project.name}
                              </Link>
                            )}

                            {project.description && (
                              <span className="text-slate-500 dark:text-slate-400 font-normal">
                                - {project.description}
                              </span>
                            )}

                            {project.status === 'ARCHIVED' && !isTrashed && (
                              <span className="inline-flex items-center gap-1 ml-1.5 px-2 py-0.2 rounded-md bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/80 text-[10px] font-bold">
                                <Archive className="w-3 h-3" /> Archived
                              </span>
                            )}

                            {isTrashed && (
                              <span className="inline-flex items-center gap-1 ml-1.5 px-2 py-0.2 rounded-md bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60 text-[10px] font-bold">
                                Trashed • Retained for 30 days
                              </span>
                            )}
                          </div>

                          {/* Restore action for trashed items */}
                          {isTrashed && (
                            <button
                              type="button"
                              onClick={() => handleRestore(project.id, project.name)}
                              className="px-3 py-1 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold rounded-xl border border-emerald-200 dark:border-emerald-800 transition-colors flex items-center gap-1 cursor-pointer"
                            >
                              <RotateCcw className="w-3.5 h-3.5" />
                              <span>Restore project</span>
                            </button>
                          )}
                        </div>
                      );
                    })}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="py-12 text-center space-y-3">
              <p className="text-sm text-slate-500 dark:text-slate-400">
                {activeTab === 'trash'
                  ? 'Trash is empty. Trashed projects are retained here for 30 days before permanent purging.'
                  : searchQuery
                  ? `No projects found matching "${searchQuery}"`
                  : `No ${activeTab} projects available.`}
              </p>
              {searchQuery && (
                <button
                  type="button"
                  onClick={() => setSearchQuery('')}
                  className="text-xs font-bold text-blue-600 hover:underline cursor-pointer"
                >
                  Clear search filter
                </button>
              )}
            </div>
          )}
        </div>

      </div>
    </div>
  );
}
