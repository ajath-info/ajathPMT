-- WorkSphere / Ajath PMT: Security and RLS Hardening Migration
-- Date: 2026-09-09
-- Purpose: Implement least-privilege RLS, object-level authorization, client isolation, and prevent privilege escalation.

-- 1. Add client visibility flag to discussions if not present
ALTER TABLE public.discussions ADD COLUMN IF NOT EXISTS is_client_visible BOOLEAN DEFAULT TRUE;
CREATE INDEX IF NOT EXISTS idx_discussions_client_vis ON public.discussions(project_id, is_client_visible);

-- 2. Security Definer Helper Functions

-- Check if authenticated user is an internal company teammate (OWNER, ADMIN, or MEMBER)
CREATE OR REPLACE FUNCTION public.is_internal_user()
RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1 FROM public.organization_members
        WHERE user_id = auth.uid() AND role IN ('OWNER', 'ADMIN', 'MEMBER')
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER STABLE;

-- Check if authenticated user is an organization admin or owner
CREATE OR REPLACE FUNCTION public.is_org_admin(org_id UUID)
RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1 FROM public.organizations o
        WHERE o.id = org_id AND o.owner_id = auth.uid()
    ) OR EXISTS (
        SELECT 1 FROM public.organization_members om
        WHERE om.organization_id = org_id AND om.user_id = auth.uid() AND om.role IN ('OWNER', 'ADMIN')
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER STABLE;

-- Check if authenticated user is a member of the project
CREATE OR REPLACE FUNCTION public.is_project_member(proj_id UUID)
RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1 FROM public.project_members
        WHERE project_id = proj_id AND user_id = auth.uid()
    ) OR EXISTS (
        SELECT 1 FROM public.projects p
        JOIN public.organization_members om ON om.organization_id = p.organization_id
        WHERE p.id = proj_id AND om.user_id = auth.uid() AND om.role IN ('OWNER', 'ADMIN')
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER STABLE;

-- Check if authenticated user is Project Owner or Project Manager
CREATE OR REPLACE FUNCTION public.is_project_manager_or_owner(proj_id UUID)
RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1 FROM public.project_members
        WHERE project_id = proj_id AND user_id = auth.uid() AND role IN ('PROJECT_OWNER', 'PROJECT_MANAGER')
    ) OR EXISTS (
        SELECT 1 FROM public.projects p
        JOIN public.organization_members om ON om.organization_id = p.organization_id
        WHERE p.id = proj_id AND om.user_id = auth.uid() AND om.role IN ('OWNER', 'ADMIN')
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER STABLE;

-- ====================================================
-- 3. DROP OVERLY BROAD "FOR ALL" POLICIES
-- ====================================================

DROP POLICY IF EXISTS "Org members view projects" ON public.projects;
DROP POLICY IF EXISTS "Org members create projects" ON public.projects;
DROP POLICY IF EXISTS "Project managers update projects" ON public.projects;
DROP POLICY IF EXISTS "Project admins delete projects" ON public.projects;

DROP POLICY IF EXISTS "Members view project members" ON public.project_members;
DROP POLICY IF EXISTS "Admins manage project members" ON public.project_members;

DROP POLICY IF EXISTS "Project members access todo lists" ON public.todo_lists;
DROP POLICY IF EXISTS "Project members access tasks" ON public.tasks;

DROP POLICY IF EXISTS "Project members view discussions" ON public.discussions;
DROP POLICY IF EXISTS "Project members create discussions" ON public.discussions;
DROP POLICY IF EXISTS "Authors and admins update discussions" ON public.discussions;
DROP POLICY IF EXISTS "Authors and admins delete discussions" ON public.discussions;

DROP POLICY IF EXISTS "Project members access chat rooms" ON public.chat_rooms;
DROP POLICY IF EXISTS "Project members access chat messages" ON public.chat_messages;

DROP POLICY IF EXISTS "Project members access files" ON public.files;
DROP POLICY IF EXISTS "Project members access folders" ON public.folders;
DROP POLICY IF EXISTS "Project members access documents" ON public.documents;

DROP POLICY IF EXISTS "Org members view teams" ON public.teams;
DROP POLICY IF EXISTS "Org admins manage teams" ON public.teams;
DROP POLICY IF EXISTS "Org members view team members" ON public.team_members;

-- ====================================================
-- 4. LEAST-PRIVILEGE RLS POLICIES
-- ====================================================

-- 4A. PROJECTS
-- SELECT: Only assigned project members, org admins/owners, or company members if project is organization-visible
CREATE POLICY "Authorized users view projects" ON public.projects FOR SELECT USING (
    is_org_admin(organization_id)
    OR EXISTS (SELECT 1 FROM public.project_members pm WHERE pm.project_id = id AND pm.user_id = auth.uid())
    OR (visibility = 'ORGANIZATION' AND is_internal_user())
);

-- INSERT: Clients cannot create projects
CREATE POLICY "Internal members create projects" ON public.projects FOR INSERT WITH CHECK (
    is_org_member(organization_id) AND is_internal_user()
);

-- UPDATE: Org Admins/Owners or Project Managers/Owners
CREATE POLICY "Project leaders update projects" ON public.projects FOR UPDATE USING (
    is_project_manager_or_owner(id)
);

-- DELETE: Strictly Org Admins/Owners
CREATE POLICY "Admins delete projects" ON public.projects FOR DELETE USING (
    is_org_admin(organization_id)
);

-- 4B. PROJECT MEMBERS
CREATE POLICY "Members view project membership" ON public.project_members FOR SELECT USING (
    is_project_member(project_id)
);
CREATE POLICY "Project leaders manage members" ON public.project_members FOR INSERT WITH CHECK (
    is_project_manager_or_owner(project_id)
);
CREATE POLICY "Project leaders update members" ON public.project_members FOR UPDATE USING (
    is_project_manager_or_owner(project_id)
);
CREATE POLICY "Project leaders remove members" ON public.project_members FOR DELETE USING (
    is_project_manager_or_owner(project_id)
);

-- 4C. DISCUSSIONS (Client Confidentiality Enforced at DB Level)
CREATE POLICY "Project members view discussions" ON public.discussions FOR SELECT USING (
    is_project_member(project_id) AND (is_internal_user() OR is_client_visible = TRUE)
);
CREATE POLICY "Project members create discussions" ON public.discussions FOR INSERT WITH CHECK (
    is_project_member(project_id)
);
CREATE POLICY "Authors and managers update discussions" ON public.discussions FOR UPDATE USING (
    author_id = auth.uid() OR is_project_manager_or_owner(project_id)
);
CREATE POLICY "Authors and managers delete discussions" ON public.discussions FOR DELETE USING (
    author_id = auth.uid() OR is_project_manager_or_owner(project_id)
);

-- 4D. CAMPFIRE CHAT (Strictly Internal Company Teammates Only)
CREATE POLICY "Internal members access chat rooms" ON public.chat_rooms FOR SELECT USING (
    is_project_member(project_id) AND is_internal_user()
);
CREATE POLICY "Internal members view chat messages" ON public.chat_messages FOR SELECT USING (
    EXISTS (SELECT 1 FROM public.chat_rooms r WHERE r.id = chat_messages.room_id AND is_project_member(r.project_id) AND is_internal_user())
);
CREATE POLICY "Internal members send chat messages" ON public.chat_messages FOR INSERT WITH CHECK (
    sender_id = auth.uid() AND EXISTS (SELECT 1 FROM public.chat_rooms r WHERE r.id = chat_messages.room_id AND is_project_member(r.project_id) AND is_internal_user())
);
CREATE POLICY "Senders and admins delete chat messages" ON public.chat_messages FOR DELETE USING (
    sender_id = auth.uid() OR EXISTS (SELECT 1 FROM public.chat_rooms r WHERE r.id = chat_messages.room_id AND is_project_manager_or_owner(r.project_id))
);

-- 4E. TODO LISTS & TASKS
CREATE POLICY "Project members view todo lists" ON public.todo_lists FOR SELECT USING (
    is_project_member(project_id)
);
CREATE POLICY "Internal members create todo lists" ON public.todo_lists FOR INSERT WITH CHECK (
    is_project_member(project_id) AND is_internal_user()
);
CREATE POLICY "Internal members update todo lists" ON public.todo_lists FOR UPDATE USING (
    is_project_member(project_id) AND is_internal_user()
);
CREATE POLICY "Project leaders delete todo lists" ON public.todo_lists FOR DELETE USING (
    is_project_manager_or_owner(project_id)
);

CREATE POLICY "Project members view tasks" ON public.tasks FOR SELECT USING (
    is_project_member(project_id)
);
CREATE POLICY "Project members create tasks" ON public.tasks FOR INSERT WITH CHECK (
    is_project_member(project_id)
);
CREATE POLICY "Project members update tasks" ON public.tasks FOR UPDATE USING (
    is_project_member(project_id)
);
CREATE POLICY "Project leaders delete tasks" ON public.tasks FOR DELETE USING (
    is_project_manager_or_owner(project_id) OR (created_by = auth.uid() AND is_internal_user())
);

-- 4F. FILES & FOLDERS
CREATE POLICY "Project members view files" ON public.files FOR SELECT USING (
    is_project_member(project_id)
);
CREATE POLICY "Project members upload files" ON public.files FOR INSERT WITH CHECK (
    is_project_member(project_id)
);
CREATE POLICY "Internal members update files" ON public.files FOR UPDATE USING (
    is_project_member(project_id) AND is_internal_user()
);
CREATE POLICY "Project leaders delete files" ON public.files FOR DELETE USING (
    is_project_manager_or_owner(project_id) OR (uploaded_by = auth.uid() AND is_internal_user())
);

CREATE POLICY "Project members view folders" ON public.folders FOR SELECT USING (
    is_project_member(project_id)
);
CREATE POLICY "Internal members create folders" ON public.folders FOR INSERT WITH CHECK (
    is_project_member(project_id) AND is_internal_user()
);
CREATE POLICY "Internal members update folders" ON public.folders FOR UPDATE USING (
    is_project_member(project_id) AND is_internal_user()
);
CREATE POLICY "Project leaders delete folders" ON public.folders FOR DELETE USING (
    is_project_manager_or_owner(project_id)
);

-- 4G. TEAMS (Internal Company Only)
CREATE POLICY "Internal members view teams" ON public.teams FOR SELECT USING (
    is_org_member(organization_id) AND is_internal_user()
);
CREATE POLICY "Admins manage teams" ON public.teams FOR ALL USING (
    is_org_admin(organization_id)
);
CREATE POLICY "Internal members view team members" ON public.team_members FOR SELECT USING (
    EXISTS (SELECT 1 FROM public.teams t WHERE t.id = team_members.team_id AND is_org_member(t.organization_id) AND is_internal_user())
);

-- 4H. INVITATIONS (Prevent Privilege Escalation)
DROP POLICY IF EXISTS "Admins view invitations" ON public.organization_invitations;
CREATE POLICY "Admins view invitations" ON public.organization_invitations FOR SELECT USING (
    is_org_admin(organization_id)
);
CREATE POLICY "Admins manage invitations" ON public.organization_invitations FOR INSERT WITH CHECK (
    is_org_admin(organization_id) OR (is_internal_user() AND role NOT IN ('OWNER', 'ADMIN'))
);
CREATE POLICY "Admins delete invitations" ON public.organization_invitations FOR DELETE USING (
    is_org_admin(organization_id)
);
