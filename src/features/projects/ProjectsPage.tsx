import React, { useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import {
  FolderPlus,
  Plus,
  Search,
  Filter,
  Grid,
  List,
  Calendar,
  Users,
  Archive,
  RotateCcw,
  Trash2,
  Edit,
  ArrowRight,
  ShieldCheck,
  Sparkles,
  Star,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useProject } from '../../context/ProjectContext';
import { Avatar } from '../../components/common/Avatar';
import { Badge } from '../../components/common/Badge';
import { Button } from '../../components/common/Button';
import { RoleGate } from '../../components/common/RoleGate';
import { CreateProjectModal } from '../../components/projects/CreateProjectModal';
import { Modal } from '../../components/common/Modal';
import { Project, ProjectStatus } from '../../types';
import { formatDate } from '../../lib/utils';
import { can } from '../../lib/permissions';
import { ProjectTemplateModal } from '../../components/projects/ProjectTemplateModal';

const AVATAR_COLORS: Record<string, string> = {
  AI: 'bg-teal-500',
  GK: 'bg-pink-500',
  PK: 'bg-cyan-500',
  RK: 'bg-rose-500',
  SN: 'bg-purple-600',
  CW: 'bg-purple-600',
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
  if (parts.length >= 2) {
    return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
  }
  return fullName.slice(0, 2).toUpperCase();
}

function getAvatarColor(initials: string): string {
  if (AVATAR_COLORS[initials]) return AVATAR_COLORS[initials];
  return 'bg-blue-500';
}

export function ProjectsPage() {
  const { userRole, profile } = useAuth();
  const { currentOrganization, teams } = useOrganization();
  const {
    projects,
    loading,
    searchQuery,
    setSearchQuery,
    statusFilter,
    setStatusFilter,
    teamFilter,
    setTeamFilter,
    archiveExistingProject,
    restoreExistingProject,
    deleteExistingProject,
    refreshProjects,
  } = useProject();

  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  const [viewMode, setViewMode] = useState<'grid' | 'list' | 'lineup'>(
    searchParams.get('view') === 'lineup' ? 'lineup' : 'grid'
  );

  React.useEffect(() => {
    if (searchParams.get('view') === 'archived') {
      setStatusFilter('ARCHIVED');
    } else if (searchParams.get('status')) {
      setStatusFilter(searchParams.get('status') as any);
    }
  }, [searchParams, setStatusFilter]);

  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isTemplateModalOpen, setIsTemplateModalOpen] = useState(false);
  const [projectToEdit, setProjectToEdit] = useState<Project | null>(null);

  // Delete modal
  const [projectToDelete, setProjectToDelete] = useState<Project | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);

  // Starred projects persistence
  const [starredIds, setStarredIds] = useState<Set<string>>(() => {
    try {
      const saved = localStorage.getItem('basecamp_starred_projects');
      return saved ? new Set(JSON.parse(saved)) : new Set<string>();
    } catch (e) {
      return new Set<string>();
    }
  });

  const handleToggleStar = (projectId: string, e: React.MouseEvent) => {
    e.stopPropagation();
    setStarredIds((prev) => {
      const next = new Set(prev);
      if (next.has(projectId)) next.delete(projectId);
      else next.add(projectId);
      try {
        localStorage.setItem('basecamp_starred_projects', JSON.stringify(Array.from(next)));
      } catch (err) {}
      return next;
    });
  };

  const statusColors: Record<ProjectStatus, 'brand' | 'success' | 'warning' | 'neutral' | 'danger'> = {
    PLANNING: 'brand',
    ACTIVE: 'success',
    ON_HOLD: 'warning',
    COMPLETED: 'neutral',
    ARCHIVED: 'danger',
  };

  const filteredProjects = projects.filter((p) => {
    const matchesSearch =
      p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.description?.toLowerCase().includes(searchQuery.toLowerCase());

    const matchesStatus = statusFilter === 'ALL' || p.status === statusFilter;
    const matchesTeam = teamFilter === 'ALL' || p.team_id === teamFilter;

    return matchesSearch && matchesStatus && matchesTeam;
  });

  const handleConfirmDelete = async () => {
    if (!projectToDelete) return;
    setIsDeleting(true);
    await deleteExistingProject(projectToDelete.id);
    setIsDeleting(false);
    setProjectToDelete(null);
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-300 max-w-7xl mx-auto">
      {/* Basecamp Projects Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80 dark:border-slate-800">
        <div>
          <h1 className="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
            Projects
          </h1>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Everything your company is working on across all teams ({filteredProjects.length})
          </p>
        </div>

        <RoleGate action="create_project">
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              size="sm"
              leftIcon={<Sparkles className="w-4 h-4 text-indigo-400" />}
              onClick={() => setIsTemplateModalOpen(true)}
              className="rounded-full text-xs"
            >
              Use Template
            </Button>
            <Button
              variant="primary"
              size="sm"
              leftIcon={<Plus className="w-4 h-4" />}
              onClick={() => {
                setProjectToEdit(null);
                setIsCreateModalOpen(true);
              }}
              className="rounded-full text-xs shadow-xs"
            >
              Make a new project
            </Button>
          </div>
        </RoleGate>
      </div>


      {/* Filter and Search Controls */}
      <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div className="flex flex-col sm:flex-row items-center gap-3 flex-1">
          <div className="relative w-full sm:w-72">
            <Search className="absolute left-3.5 top-2.5 w-4 h-4 text-slate-400" />
            <input
              type="text"
              placeholder="Search projects..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
            />
          </div>

          <div className="flex items-center gap-2 w-full sm:w-auto">
            <Filter className="w-4 h-4 text-slate-400 shrink-0" />
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value as any)}
              className="px-3 py-2 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none"
            >
              <option value="ALL">All Statuses</option>
              <option value="ACTIVE">ACTIVE</option>
              <option value="PLANNING">PLANNING</option>
              <option value="ON_HOLD">ON_HOLD</option>
              <option value="COMPLETED">COMPLETED</option>
              <option value="ARCHIVED">ARCHIVED</option>
            </select>

            <select
              value={teamFilter}
              onChange={(e) => setTeamFilter(e.target.value)}
              className="px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none"
            >
              <option value="ALL">All Teams</option>
              {teams.map((t) => (
                <option key={t.id} value={t.id}>
                  {t.name}
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* View Toggle */}
        <div className="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl shrink-0 self-end lg:self-auto">
          <button
            onClick={() => setViewMode('grid')}
            className={`p-1.5 rounded-lg transition-colors ${
              viewMode === 'grid' ? 'bg-white dark:bg-slate-900 text-brand-600 shadow-xs font-bold' : 'text-slate-500'
            }`}
            title="Grid View"
          >
            <Grid className="w-4 h-4" />
          </button>
          <button
            onClick={() => setViewMode('list')}
            className={`p-1.5 rounded-lg transition-colors ${
              viewMode === 'list' ? 'bg-white dark:bg-slate-900 text-brand-600 shadow-xs font-bold' : 'text-slate-500'
            }`}
            title="List View"
          >
            <List className="w-4 h-4" />
          </button>
          <button
            onClick={() => setViewMode('lineup')}
            className={`px-2 py-1 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 ${
              viewMode === 'lineup' ? 'bg-white dark:bg-slate-900 text-brand-600 shadow-xs font-bold' : 'text-slate-500'
            }`}
            title="Ajath PMT Lineup Timeline"
          >
            <Calendar className="w-3.5 h-3.5" />
            <span className="hidden sm:inline">Lineup</span>
          </button>
        </div>
      </div>

      {/* Projects Grid / List */}
      {filteredProjects.length === 0 ? (
        <div className="text-center py-16 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div className="w-14 h-14 rounded-2xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center mx-auto">
            <FolderPlus className="w-7 h-7" />
          </div>
          <h3 className="text-lg font-bold text-slate-900 dark:text-slate-100">No Projects Found</h3>
          <p className="text-sm text-slate-500 max-w-sm mx-auto">
            {searchQuery || statusFilter !== 'ALL'
              ? 'No projects matched your active filters. Try clearing your search.'
              : 'Get started by creating your first organization project workspace.'}
          </p>
          <RoleGate action="create_project">
            <Button variant="primary" leftIcon={<Plus className="w-4 h-4" />} onClick={() => setIsCreateModalOpen(true)}>
              Create Project
            </Button>
          </RoleGate>
        </div>
      ) : viewMode === 'grid' ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {filteredProjects.map((project) => {
            const isStarred = starredIds.has(project.id);
            const members = project.members || [];

            return (
              <div
                key={project.id}
                onClick={() => navigate(`/projects/${project.id}`)}
                className="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-150 cursor-pointer flex flex-col justify-between min-h-[175px] group select-none relative"
              >
                {/* Top: Title, Subtitle, Star & Actions */}
                <div className="space-y-1.5">
                  <div className="flex items-start justify-between gap-2">
                    <h3 className="font-extrabold text-[15px] leading-snug text-slate-900 dark:text-slate-100 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                      {project.name}
                    </h3>

                    <div className="flex items-center gap-1 -mr-1.5 -mt-1">
                      {/* Star Button */}
                      <button
                        onClick={(e) => handleToggleStar(project.id, e)}
                        title={isStarred ? 'Unstar project' : 'Star project'}
                        className="p-1 text-slate-300 hover:text-amber-400 transition-colors shrink-0"
                      >
                        <Star
                          className={`w-4 h-4 transition-transform ${
                            isStarred
                              ? 'fill-amber-400 text-amber-400 scale-110'
                              : 'text-slate-300 dark:text-slate-600 hover:text-amber-400'
                          }`}
                        />
                      </button>

                      {/* Management on hover */}
                      {can(userRole, 'manage_projects') && (
                        <button
                          onClick={(e) => {
                            e.stopPropagation();
                            setProjectToEdit(project);
                            setIsCreateModalOpen(true);
                          }}
                          className="p-1 text-slate-300 hover:text-slate-600 dark:hover:text-slate-200 opacity-0 group-hover:opacity-100 transition-opacity"
                          title="Edit project"
                        >
                          <Edit className="w-3.5 h-3.5" />
                        </button>
                      )}
                    </div>
                  </div>

                  {project.description && (
                    <p className="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed font-normal">
                      {project.description}
                    </p>
                  )}
                </div>

                {/* Bottom: Overlapping circular avatar badges */}
                <div className="pt-4 flex items-center justify-between">
                  <div className="flex -space-x-1.5 overflow-hidden py-0.5">
                    {members.map((m, idx) => {
                      const isCurrentUser =
                        m.user_id === 'demo-user-owner' ||
                        m.user_id === 'demo-user-client' ||
                        m.user_id === profile?.id ||
                        m.profile?.full_name === 'Shiv Narayan' ||
                        m.profile?.full_name === 'Shivy Narain';

                      const fullName = isCurrentUser ? (profile?.full_name || 'Claire Watson') : (m.profile?.full_name || 'Member');
                      const initials = getInitials(fullName);
                      const colorClass = getAvatarColor(initials);

                      return (
                        <div
                          key={m.id || idx}
                          title={fullName}
                          className={`w-6 h-6 rounded-full ${colorClass} text-white font-black text-[9px] tracking-tight flex items-center justify-center ring-2 ring-white dark:ring-slate-900 shadow-xs select-none`}
                        >
                          {initials}
                        </div>
                      );
                    })}
                  </div>

                  <span className="text-[11px] font-bold text-slate-400 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                    Open →
                  </span>
                </div>
              </div>
            );
          })}
        </div>
      ) : viewMode === 'list' ? (
        /* List View */
        <div className="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
          {filteredProjects.map((project) => (
            <div
              key={project.id}
              onClick={() => navigate(`/projects/${project.id}`)}
              className="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors cursor-pointer"
            >
              <div className="flex items-center gap-4 min-w-0">
                <div className="w-12 h-12 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 overflow-hidden shrink-0">
                  {project.cover_url ? (
                    <img src={project.cover_url} alt={project.name} className="w-full h-full object-cover" />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center text-white font-bold text-sm">
                      {project.name.substring(0, 2).toUpperCase()}
                    </div>
                  )}
                </div>
                <div className="min-w-0">
                  <div className="flex items-center gap-2">
                    <h3 className="font-bold text-sm text-slate-900 dark:text-slate-100 truncate">{project.name}</h3>
                    <Badge variant={statusColors[project.status]}>{project.status}</Badge>
                  </div>
                  <p className="text-xs text-slate-500 truncate mt-0.5">{project.description}</p>
                </div>
              </div>

              <div className="flex items-center gap-4 shrink-0">
                <span className="text-xs text-slate-500 hidden md:block">
                  {project.start_date ? formatDate(project.start_date) : 'No date'}
                </span>
                <Button variant="outline" size="sm" rightIcon={<ArrowRight className="w-3.5 h-3.5" />}>
                  Open Workspace
                </Button>
              </div>
            </div>
          ))}
        </div>
      ) : (
        /* Basecamp Lineup Timeline View */
        <div className="space-y-4">
          <div className="p-4 rounded-3xl bg-amber-500/10 border border-amber-500/20 flex items-center gap-3 text-xs text-amber-800 dark:text-amber-200">
            <Calendar className="w-5 h-5 text-amber-600 shrink-0" />
            <div>
              <p className="font-bold text-sm">Ajath PMT Project Lineup</p>
              <p>Visual timeline of active initiatives, start & end durations, progress, and upcoming milestones.</p>
            </div>
          </div>

          <div className="space-y-3">
            {filteredProjects.map((project) => (
              <div
                key={project.id}
                onClick={() => navigate(`/projects/${project.id}`)}
                className="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/90 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-brand-500/50 transition-all cursor-pointer flex flex-col md:flex-row md:items-center justify-between gap-4 group"
              >
                <div className="space-y-1.5 min-w-0 md:w-1/3">
                  <div className="flex items-center gap-2">
                    <Badge variant={statusColors[project.status]} size="sm">
                      {project.status}
                    </Badge>
                    <span className="text-[11px] text-slate-400 font-semibold">{project.visibility}</span>
                  </div>
                  <h3 className="font-extrabold text-base text-slate-900 dark:text-slate-100 group-hover:text-brand-600 transition-colors truncate">
                    {project.name}
                  </h3>
                  <p className="text-xs text-slate-500 dark:text-slate-400 truncate">
                    {project.description || 'Active Ajath PMT initiative'}
                  </p>
                </div>

                {/* Duration Lineup Bar */}
                <div className="flex-1 space-y-1 px-0 md:px-6">
                  <div className="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-semibold">
                    <span>{project.start_date ? formatDate(project.start_date) : 'Flexible'}</span>
                    <span className="text-[10px] font-bold text-brand-600 uppercase tracking-wider">{project.progress || 0}% Complete</span>
                    <span>{project.end_date ? formatDate(project.end_date) : 'No Deadline'}</span>
                  </div>
                  <div className="w-full bg-slate-100 dark:bg-slate-800 h-3 rounded-full overflow-hidden p-0.5">
                    <div
                      className="bg-gradient-to-r from-brand-600 via-indigo-600 to-emerald-500 h-full rounded-full transition-all duration-500"
                      style={{ width: `${project.progress || 0}%` }}
                    />
                  </div>
                </div>

                <div className="flex items-center gap-3 shrink-0">
                  <Button variant="outline" size="sm" rightIcon={<ArrowRight className="w-3.5 h-3.5" />}>
                    Open Workspace
                  </Button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Delete Confirmation Modal */}
      <Modal
        isOpen={Boolean(projectToDelete)}
        onClose={() => setProjectToDelete(null)}
        title="Delete Project Workspace"
        description="Are you sure you want to permanently delete this project?"
      >
        <p className="text-sm text-slate-600 dark:text-slate-400 mb-6">
          Deleting <span className="font-bold text-slate-900 dark:text-slate-100">{projectToDelete?.name}</span> will permanently remove all associated task boards, discussions, files, and member data.
        </p>

        <div className="flex justify-end gap-3">
          <Button variant="outline" onClick={() => setProjectToDelete(null)}>
            Cancel
          </Button>
          <Button variant="danger" isLoading={isDeleting} onClick={handleConfirmDelete}>
            Confirm Permanent Delete
          </Button>
        </div>
      </Modal>

      {/* Create / Edit Project Modal */}
      <CreateProjectModal
        isOpen={isCreateModalOpen}
        onClose={() => {
          setIsCreateModalOpen(false);
          setProjectToEdit(null);
        }}
        projectToEdit={projectToEdit}
      />

      {/* Project Template Modal */}
      <ProjectTemplateModal
        isOpen={isTemplateModalOpen}
        onClose={() => setIsTemplateModalOpen(false)}
        onProjectCreated={(newProj) => {
          refreshProjects();
          navigate(`/projects/${newProj.id}`);
        }}
      />
    </div>
  );
}

