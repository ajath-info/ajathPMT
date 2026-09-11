import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getProjectById, getProjectMembers } from '../../services/projectService';
import { Project, ProjectMember } from '../../types';
import { ProjectOverview } from './ProjectOverview';
import { ProjectMembersModal } from '../../components/projects/ProjectMembersModal';
import { useAuth } from '../../context/AuthContext';
import { canManageProjectSettings, canAccessProject } from '../../lib/permissions';
import { AccessDeniedView } from '../auth/ProtectedRoute';
import { Shield } from 'lucide-react';

export function ProjectSettingsPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const navigate = useNavigate();
  const { profile, userRole } = useAuth();
  const [project, setProject] = useState<Project | null>(null);
  const [members, setMembers] = useState<ProjectMember[]>([]);
  const [loading, setLoading] = useState(true);
  const [isMembersModalOpen, setIsMembersModalOpen] = useState(false);

  useEffect(() => {
    if (!projectId) return;
    setLoading(true);
    Promise.all([
      getProjectById(projectId),
      getProjectMembers(projectId),
    ])
      .then(([p, m]) => {
        setProject(p);
        setMembers(m || []);
      })
      .catch((err) => {
        console.error('Failed to load project settings data', err);
      })
      .finally(() => {
        setLoading(false);
      });
  }, [projectId]);

  if (loading) {
    return (
      <div className="py-20 text-center text-slate-500 font-semibold text-sm animate-pulse">
        Loading project settings...
      </div>
    );
  }

  if (!project) {
    return (
      <div className="py-20 text-center space-y-4">
        <p className="text-rose-500 font-bold">Project not found.</p>
        <button
          onClick={() => navigate('/projects')}
          className="text-blue-600 hover:underline font-semibold text-sm cursor-pointer"
        >
          Return to Projects
        </button>
      </div>
    );
  }

  // Object-Level Authorization Check
  if (!canAccessProject(userRole, profile?.id, project, members)) {
    return (
      <AccessDeniedView
        title="Project Access Restricted"
        message="You do not have permission to view or manage this project."
        backLink="/dashboard"
        backLabel="Return to Dashboard"
      />
    );
  }

  const currentUserProjectRole = members.find((m) => m.user_id === profile?.id)?.role;
  if (!canManageProjectSettings(userRole, currentUserProjectRole)) {
    return (
      <div className="max-w-xl mx-auto py-20 text-center space-y-4">
        <div className="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto border border-amber-200 dark:border-amber-800">
          <Shield className="w-7 h-7" />
        </div>
        <h2 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100">Project Settings Restricted</h2>
        <p className="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
          Only Account Owners, Administrators, and Project Managers can modify project configuration and dates.
        </p>
        <button
          onClick={() => navigate(`/projects/${projectId || ''}`)}
          className="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs cursor-pointer shadow-xs transition-colors"
        >
          Return to Project Overview
        </button>
      </div>
    );
  }

  return (
    <div className="w-full">
      <ProjectOverview
        project={project}
        members={members}
        initialEditingSettings={true}
        onOpenMembersModal={() => navigate(`/projects/${project.id}/people/users/edit`)}
      />

      <ProjectMembersModal
        isOpen={isMembersModalOpen}
        onClose={() => setIsMembersModalOpen(false)}
        projectId={project.id}
        projectName={project.name}
      />
    </div>
  );
}
