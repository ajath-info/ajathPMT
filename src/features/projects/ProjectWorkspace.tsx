import React, { useEffect, useState } from 'react';
import { useParams, Routes, Route, useNavigate } from 'react-router-dom';
import { getProjectById, getProjectMembers, getProjectToolSettings } from '../../services/projectService';
import { Project, ProjectMember } from '../../types';
import { ProjectHeader } from '../../components/projects/ProjectHeader';
import { ProjectOverview } from './ProjectOverview';
import { ProjectTodosPage } from '../todos/ProjectTodosPage';
import { ProjectKanbanPage } from '../kanban/ProjectKanbanPage';
import { ProjectDiscussionsPage } from '../discussions/ProjectDiscussionsPage';
import { ProjectDiscussionDetailPage } from '../discussions/ProjectDiscussionDetailPage';
import { ProjectNewMessagePage } from '../discussions/ProjectNewMessagePage';
import { ProjectChatPage } from '../chat/ProjectChatPage';
import { ProjectCalendarPage } from '../calendar/ProjectCalendarPage';
import { ProjectDocsPage } from '../docs/ProjectDocsPage';
import { ProjectFilesPage } from '../files/ProjectFilesPage';
import { ProjectCheckinsPage } from '../checkins/ProjectCheckinsPage';
import { ProjectProgressPage } from '../progress/ProjectProgressPage';
import { ProjectActivityPage } from '../activity/ProjectActivityPage';
import { ProjectReportsPage } from '../reports/ProjectReportsPage';
import { ProjectMembersModal } from '../../components/projects/ProjectMembersModal';
import { CreateProjectModal } from '../../components/projects/CreateProjectModal';
import { ProjectPeopleEditPage } from './ProjectPeopleEditPage';
import { Button } from '../../components/common/Button';
import { Sliders } from 'lucide-react';
import { recordRecentlyVisitedItem } from '../../components/layout/BasecampNavDropdown';

import { useAuth } from '../../context/AuthContext';
import { canAccessProject, canAccessCampfire } from '../../lib/permissions';
import { AccessDeniedView } from '../auth/ProtectedRoute';

function DisabledToolNotice({ toolName, projectId }: { toolName: string; projectId: string }) {
  const navigate = useNavigate();
  return (
    <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-12 text-center max-w-xl mx-auto space-y-4 shadow-sm animate-in fade-in duration-200 my-8">
      <div className="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto">
        <Sliders className="w-6 h-6" />
      </div>
      <h3 className="text-lg font-bold text-slate-900 dark:text-slate-100">{toolName} is Disabled</h3>
      <p className="text-xs text-slate-500 leading-relaxed">
        The <strong>{toolName}</strong> tool has been turned off in this project's settings by an administrator. All existing data is safely preserved in the database.
      </p>
      <div className="pt-2 flex justify-center gap-3">
        <Button variant="outline" size="sm" onClick={() => navigate(`/projects/${projectId}`)}>
          Back to Overview
        </Button>
        <Button variant="secondary" size="sm" onClick={() => navigate(`/projects/${projectId}/settings`)}>
          Project Settings
        </Button>
      </div>
    </div>
  );
}

export function ProjectWorkspace() {
  const { projectId } = useParams<{ projectId: string }>();
  const navigate = useNavigate();
  const { profile, userRole } = useAuth();

  const [project, setProject] = useState<Project | null>(null);
  const [members, setMembers] = useState<ProjectMember[]>([]);
  const [toolSettings, setToolSettings] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [isUnauthorized, setIsUnauthorized] = useState(false);

  // Modals
  const [isMembersModalOpen, setIsMembersModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);

  const loadProjectData = async () => {
    if (!projectId) return;
    setLoading(true);
    setIsUnauthorized(false);
    try {
      const [pData, mList, tSettings] = await Promise.all([
        getProjectById(projectId),
        getProjectMembers(projectId),
        getProjectToolSettings(projectId),
      ]);

      if (!pData) {
        setProject(null);
        return;
      }

      // Enforce BOLA / IDOR Project Access Control
      const hasAccess = canAccessProject(userRole, profile?.id, pData, mList || []);
      if (!hasAccess) {
        setIsUnauthorized(true);
        setProject(null);
        return;
      }

      setProject(pData);
      setMembers(mList || []);
      setToolSettings(tSettings);

      // Dynamically record recently visited project for active workspace
      if (pData.organization_id) {
        recordRecentlyVisitedItem(pData.organization_id, {
          id: `proj-${pData.id}`,
          title: pData.name,
          subtitle: pData.description || '',
          type: 'project',
          path: `/projects/${pData.id}`,
        });
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadProjectData();
  }, [projectId, userRole, profile?.id]);

  if (loading) {
    return (
      <div className="py-16 text-center text-slate-500 font-semibold text-sm">
        Loading project workspace...
      </div>
    );
  }

  if (isUnauthorized) {
    return (
      <AccessDeniedView
        title="Project Access Restricted"
        message="You do not have permission to view or participate in this project. Only assigned team members, assigned clients, and organization administrators may access this workspace."
        backLink="/dashboard"
        backLabel="Return to Dashboard"
      />
    );
  }

  if (!project) {
    return (
      <div className="py-16 text-center space-y-4">
        <p className="text-rose-500 font-bold">Project workspace not found.</p>
        <Button variant="outline" onClick={() => navigate('/projects')}>
          Return to Projects Directory
        </Button>
      </div>
    );
  }

  return (
    <div className="space-y-8 animate-in fade-in duration-300 max-w-7xl mx-auto">
      {/* Workspace Header & Tool Navigation Bar */}
      <ProjectHeader
        project={project}
        toolSettings={toolSettings}
        onOpenMembersModal={() => navigate(`/projects/${project.id}/people/users/edit`)}
        onOpenEditModal={() => setIsEditModalOpen(true)}
      />

      {/* Sub-tool Outlet / Routes */}
      <Routes>
        <Route
          index
          element={
            <ProjectOverview
              project={project}
              members={members}
              toolSettings={toolSettings}
              onOpenMembersModal={() => navigate(`/projects/${project.id}/people/users/edit`)}
              onToolsUpdated={(updated) => setToolSettings(updated)}
            />
          }
        />
        <Route
          path="people/users/edit"
          element={
            userRole === 'CLIENT' ? (
              <AccessDeniedView
                title="People Management Restricted"
                message="Client accounts cannot modify project membership or access member management."
                backLink={`/projects/${project.id}`}
                backLabel="Return to Project Overview"
              />
            ) : (
              <ProjectPeopleEditPage
                project={project}
                onProjectUpdated={loadProjectData}
              />
            )
          }
        />
        <Route
          path="people"
          element={
            userRole === 'CLIENT' ? (
              <AccessDeniedView
                title="People Management Restricted"
                message="Client accounts cannot modify project membership or access member management."
                backLink={`/projects/${project.id}`}
                backLabel="Return to Project Overview"
              />
            ) : (
              <ProjectPeopleEditPage
                project={project}
                onProjectUpdated={loadProjectData}
              />
            )
          }
        />
        <Route
          path="people/*"
          element={
            userRole === 'CLIENT' ? (
              <AccessDeniedView
                title="People Management Restricted"
                message="Client accounts cannot modify project membership or access member management."
                backLink={`/projects/${project.id}`}
                backLabel="Return to Project Overview"
              />
            ) : (
              <ProjectPeopleEditPage
                project={project}
                onProjectUpdated={loadProjectData}
              />
            )
          }
        />
        <Route
          path="todos"
          element={
            toolSettings?.todos !== false ? (
              <ProjectTodosPage />
            ) : (
              <DisabledToolNotice toolName="To-Dos" projectId={project.id} />
            )
          }
        />
        <Route
          path="board"
          element={
            toolSettings?.kanban !== false ? (
              <ProjectKanbanPage />
            ) : (
              <DisabledToolNotice toolName="Card Board" projectId={project.id} />
            )
          }
        />
        <Route
          path="discussions"
          element={
            toolSettings?.message_board !== false ? (
              <ProjectDiscussionsPage />
            ) : (
              <DisabledToolNotice toolName="Discussions" projectId={project.id} />
            )
          }
        />
        <Route path="messages/new" element={<ProjectNewMessagePage />} />
        <Route path="discussions/new" element={<ProjectNewMessagePage />} />
        <Route path="discussions/:discussionId" element={<ProjectDiscussionDetailPage />} />
        <Route path="messages/:messageId" element={<ProjectDiscussionDetailPage />} />
        <Route
          path="chat"
          element={
            !canAccessCampfire(userRole) ? (
              <AccessDeniedView
                title="Campfire Restricted"
                message="Campfire team chat is reserved for internal company staff only. Client accounts cannot access real-time internal team discussions."
                backLink={`/projects/${project.id}`}
                backLabel="Return to Project Overview"
              />
            ) : toolSettings?.campfire !== false ? (
              <ProjectChatPage />
            ) : (
              <DisabledToolNotice toolName="Team Chat" projectId={project.id} />
            )
          }
        />
        <Route
          path="calendar"
          element={
            toolSettings?.schedule !== false ? (
              <ProjectCalendarPage />
            ) : (
              <DisabledToolNotice toolName="Calendar" projectId={project.id} />
            )
          }
        />
        <Route
          path="docs"
          element={
            toolSettings?.docs_files !== false ? (
              <ProjectDocsPage />
            ) : (
              <DisabledToolNotice toolName="Documents" projectId={project.id} />
            )
          }
        />
        <Route path="docs/:folderId" element={<ProjectDocsPage />} />
        <Route
          path="files"
          element={
            toolSettings?.docs_files !== false ? (
              <ProjectFilesPage />
            ) : (
              <DisabledToolNotice toolName="Files" projectId={project.id} />
            )
          }
        />
        <Route path="files/:folderId" element={<ProjectFilesPage />} />
        <Route path="vaults" element={<ProjectDocsPage />} />
        <Route path="vaults/:folderId" element={<ProjectDocsPage />} />
        <Route
          path="checkins"
          element={
            toolSettings?.checkins !== false ? (
              <ProjectCheckinsPage />
            ) : (
              <DisabledToolNotice toolName="Check-ins" projectId={project.id} />
            )
          }
        />
        <Route path="progress" element={<ProjectProgressPage />} />
        <Route path="activity" element={<ProjectActivityPage />} />
        <Route path="reports" element={<ProjectReportsPage />} />
        <Route
          path="people"
          element={
            <ProjectPeopleEditPage
              project={project}
              onProjectUpdated={loadProjectData}
            />
          }
        />
      </Routes>

      {/* Modals */}
      <ProjectMembersModal
        isOpen={isMembersModalOpen}
        onClose={() => {
          setIsMembersModalOpen(false);
          loadProjectData();
        }}
        projectId={project.id}
        projectName={project.name}
      />

      <CreateProjectModal
        isOpen={isEditModalOpen}
        onClose={() => {
          setIsEditModalOpen(false);
          loadProjectData();
        }}
        projectToEdit={project}
      />
    </div>
  );
}
