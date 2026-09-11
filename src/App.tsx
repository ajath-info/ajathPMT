import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from './context/AuthContext';
import { ThemeProvider } from './context/ThemeContext';
import { OrganizationProvider } from './context/OrganizationContext';
import { ProjectProvider } from './context/ProjectContext';
import { ErrorBoundary } from './components/common/ErrorBoundary';

import { AppLayout } from './components/layout/AppLayout';
import { ProtectedRoute, RequireOrgRole, RequireInternalUser } from './features/auth/ProtectedRoute';

import { LoginPage } from './features/auth/LoginPage';
import { SignUpPage } from './features/auth/SignUpPage';
import { ForgotPasswordPage } from './features/auth/ForgotPasswordPage';
import { ResetPasswordPage } from './features/auth/ResetPasswordPage';
import { ProfilePage } from './features/auth/ProfilePage';
import { HomeDashboard } from './features/dashboard/HomeDashboard';
import { MembersPage } from './features/members/MembersPage';
import { TeamsPage } from './features/teams/TeamsPage';
import { TeamDetailsPage } from './features/teams/TeamDetailsPage';
import { OrgSettingsPage } from './features/settings/OrgSettingsPage';
import { AcceptInvitePage } from './features/invitations/AcceptInvitePage';
import { ProjectsPage } from './features/projects/ProjectsPage';
import { CreateProjectPage } from './features/projects/CreateProjectPage';
import { ProjectWorkspace } from './features/projects/ProjectWorkspace';
import { MyTasksPage } from './features/tasks/MyTasksPage';
import { DirectMessagesPage } from './features/chat/DirectMessagesPage';
import { ProjectCalendarPage } from './features/calendar/ProjectCalendarPage';
import { NotificationsPage } from './features/notifications/NotificationsPage';
import { ProjectDiscussionsPage } from './features/discussions/ProjectDiscussionsPage';
import { ProjectDiscussionDetailPage } from './features/discussions/ProjectDiscussionDetailPage';
import { ProjectNewMessagePage } from './features/discussions/ProjectNewMessagePage';
import { ProjectDocsPage } from './features/docs/ProjectDocsPage';
import { ProjectReportsPage } from './features/reports/ProjectReportsPage';
import { ProjectActivityPage } from './features/activity/ProjectActivityPage';
import { ProjectSettingsPage } from './features/projects/ProjectSettingsPage';
import { UserSettingsPage } from './features/settings/UserSettingsPage';
import { AdminlandPage } from './features/admin/AdminlandPage';
import { InvitePeoplePage } from './features/members/InvitePeoplePage';
import {
  EmployeeEnrollmentPage,
  VendorEnrollmentPage,
  ClientEnrollmentPage,
} from './features/members/EnrollmentSetupPage';
import { ProjectsDirectoryPage } from './features/projects/ProjectsDirectoryPage';
import { MyBoostsPage } from './features/boosts/MyBoostsPage';
import { MyDraftsPage } from './features/drafts/MyDraftsPage';
import { EverythingPage } from './features/everything/EverythingPage';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 1,
    },
  },
});

import { ToastProvider } from './context/ToastContext';
import { startBackgroundWorker } from './services/backgroundWorker';

export default function App() {
  React.useEffect(() => {
    const stopWorker = startBackgroundWorker(60_000);
    return () => {
      stopWorker();
    };
  }, []);
  return (
    <ErrorBoundary>
      <QueryClientProvider client={queryClient}>
        <ThemeProvider>
          <AuthProvider>
            <OrganizationProvider>
              <ProjectProvider>
                <ToastProvider>
                  <BrowserRouter>
                  <Routes>
                    {/* Public Auth Routes */}
                    <Route path="/login" element={<LoginPage />} />
                    <Route path="/signup" element={<SignUpPage />} />
                    <Route path="/forgot-password" element={<ForgotPasswordPage />} />
                    <Route path="/reset-password" element={<ResetPasswordPage />} />
                    <Route path="/invite/:token" element={<AcceptInvitePage />} />

                    {/* Protected Workspace Routes */}
                    <Route
                      path="/"
                      element={
                        <ProtectedRoute>
                          <AppLayout />
                        </ProtectedRoute>
                      }
                    >
                      <Route index element={<Navigate to="/dashboard" replace />} />
                      <Route path="dashboard" element={<HomeDashboard />} />
                      <Route path="profile" element={<ProfilePage />} />
                      <Route path="members" element={<MembersPage />} />
                      <Route
                        path="teams"
                        element={
                          <RequireInternalUser>
                            <TeamsPage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path="teams/:teamId"
                        element={
                          <RequireInternalUser>
                            <TeamDetailsPage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path="settings/organization"
                        element={
                          <RequireOrgRole allowedRoles={['OWNER', 'ADMIN']}>
                            <OrgSettingsPage />
                          </RequireOrgRole>
                        }
                      />

                      {/* Project Routes */}
                      <Route path="projects" element={<ProjectsDirectoryPage />} />
                      <Route path="projects/directory" element={<ProjectsDirectoryPage />} />
                      <Route path=":orgId/projects/directory" element={<ProjectsDirectoryPage />} />
                      <Route path="projects/lineup" element={<ProjectsPage />} />
                      <Route
                        path="projects/new"
                        element={
                          <RequireInternalUser>
                            <CreateProjectPage />
                          </RequireInternalUser>
                        }
                      />
                      <Route path="projects/:projectId/settings" element={<ProjectSettingsPage />} />
                      <Route path="projects/:projectId/*" element={<ProjectWorkspace />} />
                      <Route path=":orgId/projects/:projectId/*" element={<ProjectWorkspace />} />

                      {/* Personal Task Center */}
                      <Route path="my-tasks" element={<MyTasksPage />} />

                      <Route path="messages" element={<DirectMessagesPage />} />
                      <Route path="calendar" element={<ProjectCalendarPage />} />
                      <Route path=":orgId/calendar" element={<ProjectCalendarPage />} />
                      <Route path="activity" element={<ProjectActivityPage />} />
                      <Route path=":orgId/activity" element={<ProjectActivityPage />} />
                      <Route path="notifications" element={<NotificationsPage />} />
                      <Route path="discussions" element={<ProjectDiscussionsPage />} />
                      <Route path="messages/new" element={<ProjectNewMessagePage />} />
                      <Route path="discussions/new" element={<ProjectNewMessagePage />} />
                      <Route path="discussions/:discussionId" element={<ProjectDiscussionDetailPage />} />
                      <Route path="messages/:messageId" element={<ProjectDiscussionDetailPage />} />
                      <Route path="docs" element={<ProjectDocsPage />} />
                      <Route path="reports" element={<ProjectReportsPage />} />
                      <Route path=":orgId/reports" element={<ProjectReportsPage />} />
                      <Route path="everything" element={<EverythingPage />} />
                      <Route path=":orgId/everything" element={<EverythingPage />} />

                      {/* Adminland & Invite People Routes */}
                      <Route
                        path="adminland"
                        element={
                          <RequireOrgRole allowedRoles={['OWNER', 'ADMIN']}>
                            <AdminlandPage />
                          </RequireOrgRole>
                        }
                      />
                      <Route
                        path="admin"
                        element={
                          <RequireOrgRole allowedRoles={['OWNER', 'ADMIN']}>
                            <AdminlandPage />
                          </RequireOrgRole>
                        }
                      />
                      <Route
                        path="account"
                        element={
                          <RequireOrgRole allowedRoles={['OWNER', 'ADMIN']}>
                            <AdminlandPage />
                          </RequireOrgRole>
                        }
                      />
                      <Route
                        path=":orgId/account"
                        element={
                          <RequireOrgRole allowedRoles={['OWNER', 'ADMIN']}>
                            <AdminlandPage />
                          </RequireOrgRole>
                        }
                      />
                      <Route
                        path="account/enrollments/new"
                        element={
                          <RequireInternalUser>
                            <InvitePeoplePage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path=":orgId/account/enrollments/new"
                        element={
                          <RequireInternalUser>
                            <InvitePeoplePage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path="account/enrollments/employees/new"
                        element={
                          <RequireInternalUser>
                            <EmployeeEnrollmentPage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path=":orgId/account/enrollments/employees/new"
                        element={
                          <RequireInternalUser>
                            <EmployeeEnrollmentPage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path="enrollments/employees/new"
                        element={
                          <RequireInternalUser>
                            <EmployeeEnrollmentPage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path="account/enrollments/vendors/new"
                        element={
                          <RequireInternalUser>
                            <VendorEnrollmentPage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path=":orgId/account/enrollments/vendors/new"
                        element={
                          <RequireInternalUser>
                            <VendorEnrollmentPage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path="enrollments/vendors/new"
                        element={
                          <RequireInternalUser>
                            <VendorEnrollmentPage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path="account/enrollments/clients/new"
                        element={
                          <RequireInternalUser>
                            <ClientEnrollmentPage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path=":orgId/account/enrollments/clients/new"
                        element={
                          <RequireInternalUser>
                            <ClientEnrollmentPage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path="enrollments/clients/new"
                        element={
                          <RequireInternalUser>
                            <ClientEnrollmentPage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path="enrollments/new"
                        element={
                          <RequireInternalUser>
                            <InvitePeoplePage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path="enrollments"
                        element={
                          <RequireInternalUser>
                            <InvitePeoplePage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path="invite-people"
                        element={
                          <RequireInternalUser>
                            <InvitePeoplePage />
                          </RequireInternalUser>
                        }
                      />
                      <Route
                        path="invite"
                        element={
                          <RequireInternalUser>
                            <InvitePeoplePage />
                          </RequireInternalUser>
                        }
                      />

                      <Route path="settings" element={<UserSettingsPage />} />
                      <Route path="my/settings" element={<UserSettingsPage />} />
                      <Route path=":orgId/my/settings" element={<UserSettingsPage />} />
                      <Route path="my/boosts" element={<MyBoostsPage />} />
                      <Route path="boosts" element={<MyBoostsPage />} />
                      <Route path=":orgId/my/boosts" element={<MyBoostsPage />} />
                      <Route path="my/drafts" element={<MyDraftsPage />} />
                      <Route path="drafts" element={<MyDraftsPage />} />
                      <Route path=":orgId/my/drafts" element={<MyDraftsPage />} />
                    </Route>

                    {/* Catch-all Fallback Route */}
                    <Route path="*" element={<Navigate to="/dashboard" replace />} />
                  </Routes>
                </BrowserRouter>
              </ToastProvider>
            </ProjectProvider>
          </OrganizationProvider>
          </AuthProvider>
        </ThemeProvider>
      </QueryClientProvider>
    </ErrorBoundary>
  );
}

