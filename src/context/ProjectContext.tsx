import React, { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { useOrganization } from './OrganizationContext';
import { useAuth } from './AuthContext';
import { Project, ProjectStatus, CreateProjectInput, UpdateProjectInput } from '../types';
import {
  getProjects,
  getProjectById,
  createProject as apiCreateProject,
  updateProject as apiUpdateProject,
  archiveProject as apiArchiveProject,
  restoreProject as apiRestoreProject,
  deleteProject as apiDeleteProject,
} from '../services/projectService';
import { supabase, isSupabaseConfigured } from '../lib/supabase';

interface ProjectContextType {
  projects: Project[];
  loading: boolean;
  searchQuery: string;
  setSearchQuery: (query: string) => void;
  statusFilter: ProjectStatus | 'ALL';
  setStatusFilter: (status: ProjectStatus | 'ALL') => void;
  teamFilter: string;
  setTeamFilter: (teamId: string) => void;
  createNewProject: (input: Omit<CreateProjectInput, 'organization_id'>) => Promise<{ data: Project | null; error: Error | null }>;
  updateExistingProject: (projectId: string, updates: UpdateProjectInput) => Promise<{ error: Error | null }>;
  archiveExistingProject: (projectId: string) => Promise<{ error: Error | null }>;
  restoreExistingProject: (projectId: string) => Promise<{ error: Error | null }>;
  deleteExistingProject: (projectId: string) => Promise<{ error: Error | null }>;
  refreshProjects: () => Promise<void>;
}

const ProjectContext = createContext<ProjectContextType | undefined>(undefined);

export function ProjectProvider({ children }: { children: React.ReactNode }) {
  const { currentOrganization } = useOrganization();
  const { profile, userRole } = useAuth();
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState<boolean>(true);

  const [searchQuery, setSearchQuery] = useState('');
  const [statusFilter, setStatusFilter] = useState<ProjectStatus | 'ALL'>('ALL');
  const [teamFilter, setTeamFilter] = useState<string>('ALL');

  const loadProjects = useCallback(async () => {
    if (!currentOrganization) {
      setProjects([]);
      setLoading(false);
      return;
    }

    setLoading(true);
    try {
      const data = await getProjects(currentOrganization.id, profile?.id, userRole);
      setProjects(data);
    } catch (err) {
      console.error('Error fetching projects:', err);
    } finally {
      setLoading(false);
    }
  }, [currentOrganization, profile?.id, userRole]);

  useEffect(() => {
    loadProjects();
  }, [loadProjects]);

  // Supabase Realtime channel listener for projects
  useEffect(() => {
    if (!isSupabaseConfigured || !currentOrganization) return;

    const channel = supabase
      .channel(`projects-${currentOrganization.id}`)
      .on(
        'postgres_changes',
        { event: '*', schema: 'public', table: 'projects', filter: `organization_id=eq.${currentOrganization.id}` },
        () => loadProjects()
      )
      .subscribe();

    return () => {
      supabase.removeChannel(channel);
    };
  }, [currentOrganization, loadProjects]);

  const createNewProject = async (input: Omit<CreateProjectInput, 'organization_id'>) => {
    if (!currentOrganization) return { data: null, error: new Error('No active organization') };
    if (userRole === 'CLIENT') {
      return { data: null, error: new Error('Unauthorized: Client accounts cannot create projects.') };
    }

    const res = await apiCreateProject({
      ...input,
      organization_id: currentOrganization.id,
    });

    if (!res.error) {
      await loadProjects();
    }

    return res;
  };

  const updateExistingProject = async (projectId: string, updates: UpdateProjectInput) => {
    const res = await apiUpdateProject(projectId, updates);
    if (!res.error) {
      await loadProjects();
    }
    return res;
  };

  const archiveExistingProject = async (projectId: string) => {
    const res = await apiArchiveProject(projectId);
    if (!res.error) {
      setProjects((prev) =>
        prev.map((p) =>
          p.id === projectId || p.slug === projectId || p.name.toLowerCase() === projectId.toLowerCase()
            ? { ...p, status: 'ARCHIVED' as const, archived_at: new Date().toISOString() }
            : p
        )
      );
      await loadProjects();
    }
    return res;
  };

  const restoreExistingProject = async (projectId: string) => {
    const res = await apiRestoreProject(projectId);
    if (!res.error) {
      setProjects((prev) =>
        prev.map((p) =>
          p.id === projectId || p.slug === projectId || p.name.toLowerCase() === projectId.toLowerCase()
            ? { ...p, status: 'ACTIVE' as const, archived_at: undefined }
            : p
        )
      );
      await loadProjects();
    }
    return res;
  };

  const deleteExistingProject = async (projectId: string) => {
    const res = await apiDeleteProject(projectId);
    if (!res.error) {
      setProjects((prev) =>
        prev.filter(
          (p) =>
            p.id !== projectId &&
            p.slug !== projectId &&
            p.name.toLowerCase() !== projectId.toLowerCase()
        )
      );
      await loadProjects();
    }
    return res;
  };

  return (
    <ProjectContext.Provider
      value={{
        projects,
        loading,
        searchQuery,
        setSearchQuery,
        statusFilter,
        setStatusFilter,
        teamFilter,
        setTeamFilter,
        createNewProject,
        updateExistingProject,
        archiveExistingProject,
        restoreExistingProject,
        deleteExistingProject,
        refreshProjects: loadProjects,
      }}
    >
      {children}
    </ProjectContext.Provider>
  );
}

export function useProject() {
  const context = useContext(ProjectContext);
  if (!context) {
    throw new Error('useProject must be used within a ProjectProvider');
  }
  return context;
}
