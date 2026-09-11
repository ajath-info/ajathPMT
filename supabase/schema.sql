-- WorkSphere Supabase Database Schema DDL - Phase 4 Enhanced
-- Execute this SQL in your Supabase SQL Editor to set up tables, RLS policies, and triggers.

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. PROFILES (Extends Supabase auth.users)
CREATE TABLE IF NOT EXISTS public.profiles (
    id UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
    full_name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    avatar_url TEXT,
    job_title TEXT,
    bio TEXT,
    timezone TEXT DEFAULT 'UTC',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 2. ORGANIZATIONS
CREATE TABLE IF NOT EXISTS public.organizations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    description TEXT,
    logo_url TEXT,
    owner_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 3. ORGANIZATION MEMBERS
DO $$ BEGIN
    CREATE TYPE public.org_role AS ENUM ('OWNER', 'ADMIN', 'MEMBER', 'CLIENT');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS public.organization_members (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    organization_id UUID NOT NULL REFERENCES public.organizations(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    role public.org_role DEFAULT 'MEMBER',
    joined_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(organization_id, user_id)
);

-- 4. ORGANIZATION INVITATIONS
DO $$ BEGIN
    CREATE TYPE public.invite_status AS ENUM ('PENDING', 'ACCEPTED', 'DECLINED', 'EXPIRED');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS public.organization_invitations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    organization_id UUID NOT NULL REFERENCES public.organizations(id) ON DELETE CASCADE,
    email TEXT NOT NULL,
    role public.org_role DEFAULT 'MEMBER',
    invited_by UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    token TEXT UNIQUE NOT NULL DEFAULT md5(random()::text || clock_timestamp()::text),
    status public.invite_status DEFAULT 'PENDING',
    personal_message TEXT,
    team_id UUID REFERENCES public.teams(id) ON DELETE SET NULL,
    project_id UUID REFERENCES public.projects(id) ON DELETE CASCADE,
    project_role public.project_member_role DEFAULT 'PROJECT_MEMBER',
    accepted_at TIMESTAMPTZ,
    accepted_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    expires_at TIMESTAMPTZ DEFAULT (NOW() + INTERVAL '7 days'),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 5. TEAMS & TEAM MEMBERS
CREATE TABLE IF NOT EXISTS public.teams (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    organization_id UUID NOT NULL REFERENCES public.organizations(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    description TEXT,
    avatar_url TEXT,
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.team_members (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    team_id UUID NOT NULL REFERENCES public.teams(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(team_id, user_id)
);

-- 6. PROJECTS & PROJECT MEMBERS
DO $$ BEGIN
    CREATE TYPE public.project_status AS ENUM ('PLANNING', 'ACTIVE', 'ON_HOLD', 'COMPLETED', 'ARCHIVED');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE public.project_visibility AS ENUM ('PRIVATE', 'ORGANIZATION', 'TEAM');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS public.projects (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    organization_id UUID NOT NULL REFERENCES public.organizations(id) ON DELETE CASCADE,
    team_id UUID REFERENCES public.teams(id) ON DELETE SET NULL,
    name TEXT NOT NULL,
    slug TEXT NOT NULL,
    description TEXT,
    cover_url TEXT,
    status public.project_status DEFAULT 'PLANNING',
    visibility public.project_visibility DEFAULT 'PRIVATE',
    start_date DATE,
    end_date DATE,
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    archived_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

DO $$ BEGIN
    CREATE TYPE public.project_member_role AS ENUM ('PROJECT_OWNER', 'PROJECT_MANAGER', 'PROJECT_MEMBER', 'PROJECT_CLIENT');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS public.project_members (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    role public.project_member_role DEFAULT 'PROJECT_MEMBER',
    added_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(project_id, user_id)
);

-- 7. TODO LISTS
CREATE TABLE IF NOT EXISTS public.todo_lists (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    description TEXT,
    position INT DEFAULT 0,
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 7b. KANBAN COLUMNS (Phase 5)
CREATE TABLE IF NOT EXISTS public.kanban_columns (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    description TEXT,
    position INT DEFAULT 0,
    color TEXT DEFAULT '#64748b',
    mapped_status public.task_status_type,
    is_default BOOLEAN DEFAULT FALSE,
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 8. TASKS & ENUMS
DO $$ BEGIN
    CREATE TYPE public.task_status_type AS ENUM ('NOT_STARTED', 'IN_PROGRESS', 'WAITING', 'IN_REVIEW', 'COMPLETED');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE public.task_priority_type AS ENUM ('LOW', 'MEDIUM', 'HIGH', 'URGENT');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS public.tasks (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    todo_list_id UUID NOT NULL REFERENCES public.todo_lists(id) ON DELETE CASCADE,
    parent_task_id UUID REFERENCES public.tasks(id) ON DELETE CASCADE, -- Nullable for 1-level subtasks
    kanban_column_id UUID REFERENCES public.kanban_columns(id) ON DELETE SET NULL,
    kanban_position INT DEFAULT 0,
    title TEXT NOT NULL,
    description TEXT,
    status public.task_status_type DEFAULT 'NOT_STARTED',
    priority public.task_priority_type DEFAULT 'MEDIUM',
    start_date DATE,
    due_date DATE,
    completed_at TIMESTAMPTZ,
    position INT DEFAULT 0,
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    archived_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 9. TASK ASSIGNEES
CREATE TABLE IF NOT EXISTS public.task_assignees (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    task_id UUID NOT NULL REFERENCES public.tasks(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    assigned_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(task_id, user_id)
);

-- 10. TASK COMMENTS
CREATE TABLE IF NOT EXISTS public.task_comments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    task_id UUID NOT NULL REFERENCES public.tasks(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    parent_comment_id UUID REFERENCES public.task_comments(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 11. LABELS & TASK LABELS
CREATE TABLE IF NOT EXISTS public.labels (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    organization_id UUID NOT NULL REFERENCES public.organizations(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    color TEXT DEFAULT '#6366f1',
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.task_labels (
    task_id UUID REFERENCES public.tasks(id) ON DELETE CASCADE,
    label_id UUID REFERENCES public.labels(id) ON DELETE CASCADE,
    PRIMARY KEY (task_id, label_id)
);

-- 12. TASK DEPENDENCIES
CREATE TABLE IF NOT EXISTS public.task_dependencies (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    task_id UUID NOT NULL REFERENCES public.tasks(id) ON DELETE CASCADE,
    depends_on_task_id UUID NOT NULL REFERENCES public.tasks(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(task_id, depends_on_task_id),
    CHECK (task_id <> depends_on_task_id)
);

-- 13. NOTIFICATIONS & ACTIVITY LOGS
CREATE TABLE IF NOT EXISTS public.notifications (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    organization_id UUID REFERENCES public.organizations(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    actor_id UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    type TEXT DEFAULT 'INFO',
    title TEXT NOT NULL,
    message TEXT NOT NULL,
    link_url TEXT,
    entity_type TEXT,
    entity_id UUID,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.activity_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    organization_id UUID NOT NULL REFERENCES public.organizations(id) ON DELETE CASCADE,
    project_id UUID REFERENCES public.projects(id) ON DELETE CASCADE,
    user_id UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    action TEXT NOT NULL,
    item_title TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 14. DISCUSSIONS & DISCUSSION COMMENTS (Phase 6)
CREATE TABLE IF NOT EXISTS public.discussions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    author_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    category TEXT DEFAULT 'General',
    is_announcement BOOLEAN DEFAULT FALSE,
    is_pinned BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE,
    is_client_visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.discussion_comments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    discussion_id UUID NOT NULL REFERENCES public.discussions(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    parent_comment_id UUID REFERENCES public.discussion_comments(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 15. REALTIME CHAT & DIRECT MESSAGES (Phase 7)
CREATE TABLE IF NOT EXISTS public.chat_rooms (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID UNIQUE REFERENCES public.projects(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.chat_messages (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    room_id UUID NOT NULL REFERENCES public.chat_rooms(id) ON DELETE CASCADE,
    sender_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    reply_to_id UUID REFERENCES public.chat_messages(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.direct_conversations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    organization_id UUID NOT NULL REFERENCES public.organizations(id) ON DELETE CASCADE,
    user1_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    user2_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(user1_id, user2_id)
);

CREATE TABLE IF NOT EXISTS public.direct_messages (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    conversation_id UUID NOT NULL REFERENCES public.direct_conversations(id) ON DELETE CASCADE,
    sender_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.message_reactions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    message_id UUID NOT NULL REFERENCES public.chat_messages(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    emoji TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(message_id, user_id, emoji)
);

-- 16. CALENDAR EVENTS (Phase 8)
CREATE TABLE IF NOT EXISTS public.calendar_events (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    description TEXT,
    start_at TIMESTAMPTZ NOT NULL,
    end_at TIMESTAMPTZ NOT NULL,
    all_day BOOLEAN DEFAULT FALSE,
    color TEXT DEFAULT '#3b82f6',
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 17. FOLDERS & FILES (Phase 9)
CREATE TABLE IF NOT EXISTS public.folders (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    parent_folder_id UUID REFERENCES public.folders(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.files (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    folder_id UUID REFERENCES public.folders(id) ON DELETE SET NULL,
    uploaded_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    name TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    mime_type TEXT NOT NULL,
    size BIGINT NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 18. DOCUMENTS (Phase 10)
CREATE TABLE IF NOT EXISTS public.documents (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    parent_document_id UUID REFERENCES public.documents(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    content TEXT DEFAULT '',
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    updated_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    is_archived BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 19. CHECK-INS (Phase 11)
CREATE TABLE IF NOT EXISTS public.checkin_questions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    question TEXT NOT NULL,
    schedule TEXT DEFAULT 'DAILY', -- DAILY, WEEKLY
    is_active BOOLEAN DEFAULT TRUE,
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS public.checkin_responses (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    question_id UUID NOT NULL REFERENCES public.checkin_questions(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    response TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- PERFORMANCE INDEXES
CREATE INDEX IF NOT EXISTS idx_tasks_project ON public.tasks(project_id);
CREATE INDEX IF NOT EXISTS idx_tasks_todo_list ON public.tasks(todo_list_id);
CREATE INDEX IF NOT EXISTS idx_tasks_kanban_column ON public.tasks(kanban_column_id);
CREATE INDEX IF NOT EXISTS idx_tasks_status ON public.tasks(status);
CREATE INDEX IF NOT EXISTS idx_tasks_due_date ON public.tasks(due_date);
CREATE INDEX IF NOT EXISTS idx_tasks_created_by ON public.tasks(created_by);
CREATE INDEX IF NOT EXISTS idx_task_assignees_task ON public.task_assignees(task_id);
CREATE INDEX IF NOT EXISTS idx_task_assignees_user ON public.task_assignees(user_id);
CREATE INDEX IF NOT EXISTS idx_task_comments_task ON public.task_comments(task_id);
CREATE INDEX IF NOT EXISTS idx_todo_lists_project ON public.todo_lists(project_id);
CREATE INDEX IF NOT EXISTS idx_kanban_columns_project ON public.kanban_columns(project_id);
CREATE INDEX IF NOT EXISTS idx_discussions_project ON public.discussions(project_id);
CREATE INDEX IF NOT EXISTS idx_chat_messages_room ON public.chat_messages(room_id);
CREATE INDEX IF NOT EXISTS idx_direct_messages_conversation ON public.direct_messages(conversation_id);
CREATE INDEX IF NOT EXISTS idx_calendar_events_project ON public.calendar_events(project_id);
CREATE INDEX IF NOT EXISTS idx_files_project ON public.files(project_id);
CREATE INDEX IF NOT EXISTS idx_documents_project ON public.documents(project_id);
CREATE INDEX IF NOT EXISTS idx_notifications_user ON public.notifications(user_id);

-- ====================================================
-- ROW LEVEL SECURITY (RLS) HELPER FUNCTIONS & POLICIES
-- ====================================================

-- Security Definer helper to check organization membership
CREATE OR REPLACE FUNCTION public.is_org_member(org_id UUID)
RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1 FROM public.organization_members
        WHERE organization_id = org_id AND user_id = auth.uid()
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Security Definer helper to check project membership or organization ownership/admin
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

-- Security Definer helper to check if user is internal company staff (not client)
CREATE OR REPLACE FUNCTION public.is_internal_user()
RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1 FROM public.organization_members
        WHERE user_id = auth.uid() AND role IN ('OWNER', 'ADMIN', 'MEMBER')
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER STABLE;

-- Security Definer helper to check if user is org admin or owner
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

-- Security Definer helper to check if user is project manager, project owner, or org admin
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

-- 1. Enable RLS on core identity & organization tables
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.organizations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.organization_members ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.organization_invitations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.teams ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.team_members ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.projects ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.project_members ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.todo_lists ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.kanban_columns ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.tasks ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.task_assignees ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.task_comments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.labels ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.task_labels ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.task_dependencies ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.activity_logs ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.message_reactions ENABLE ROW LEVEL SECURITY;

-- 2. Enable RLS on communication and project tools
ALTER TABLE public.discussions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.discussion_comments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.chat_rooms ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.chat_messages ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.direct_conversations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.direct_messages ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.calendar_events ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.folders ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.files ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.documents ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.checkin_questions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.checkin_responses ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.notifications ENABLE ROW LEVEL SECURITY;

-- SECURE PROFILES POLICIES
CREATE POLICY "Profiles are viewable by authenticated users" ON public.profiles FOR SELECT USING (auth.uid() IS NOT NULL);
CREATE POLICY "Users can update their own profile" ON public.profiles FOR UPDATE USING (id = auth.uid());
CREATE POLICY "Users can insert their own profile" ON public.profiles FOR INSERT WITH CHECK (id = auth.uid());

-- SECURE ORGANIZATIONS POLICIES
CREATE POLICY "Members view their organizations" ON public.organizations FOR SELECT USING (
    owner_id = auth.uid() OR is_org_member(id)
);
CREATE POLICY "Authenticated users can create organizations" ON public.organizations FOR INSERT WITH CHECK (auth.uid() IS NOT NULL);
CREATE POLICY "Owners and admins update organizations" ON public.organizations FOR UPDATE USING (
    owner_id = auth.uid() OR EXISTS (
        SELECT 1 FROM public.organization_members WHERE organization_id = id AND user_id = auth.uid() AND role IN ('OWNER', 'ADMIN')
    )
);

-- SECURE ORGANIZATION MEMBERS POLICIES
CREATE POLICY "Members view org membership" ON public.organization_members FOR SELECT USING (is_org_member(organization_id));
CREATE POLICY "Admins manage org membership" ON public.organization_members FOR ALL USING (
    EXISTS (SELECT 1 FROM public.organization_members om WHERE om.organization_id = organization_members.organization_id AND om.user_id = auth.uid() AND om.role IN ('OWNER', 'ADMIN'))
);

-- SECURE TEAMS & TEAM MEMBERS (Internal Members Only)
CREATE POLICY "Internal members view teams" ON public.teams FOR SELECT USING (
    is_org_member(organization_id) AND is_internal_user()
);
CREATE POLICY "Admins manage teams" ON public.teams FOR ALL USING (
    is_org_admin(organization_id)
);
CREATE POLICY "Internal members view team members" ON public.team_members FOR SELECT USING (
    EXISTS (SELECT 1 FROM public.teams t WHERE t.id = team_members.team_id AND is_org_member(t.organization_id) AND is_internal_user())
);

-- SECURE PROJECTS & MEMBERS POLICIES (No IDOR, BOLA, or Leakage)
CREATE POLICY "Authorized users view projects" ON public.projects FOR SELECT USING (
    is_org_admin(organization_id)
    OR EXISTS (SELECT 1 FROM public.project_members pm WHERE pm.project_id = id AND pm.user_id = auth.uid())
    OR (visibility = 'ORGANIZATION' AND is_internal_user())
);
CREATE POLICY "Internal members create projects" ON public.projects FOR INSERT WITH CHECK (
    is_org_member(organization_id) AND is_internal_user()
);
CREATE POLICY "Project leaders update projects" ON public.projects FOR UPDATE USING (
    is_project_manager_or_owner(id)
);
CREATE POLICY "Admins delete projects" ON public.projects FOR DELETE USING (
    is_org_admin(organization_id)
);

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

-- SECURE TO-DOS & KANBAN POLICIES
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

CREATE POLICY "Project members view kanban columns" ON public.kanban_columns FOR SELECT USING (
    is_project_member(project_id)
);
CREATE POLICY "Internal members manage kanban columns" ON public.kanban_columns FOR ALL USING (
    is_project_member(project_id) AND is_internal_user()
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

CREATE POLICY "Project members access task assignees" ON public.task_assignees FOR ALL USING (
    EXISTS (SELECT 1 FROM public.tasks t WHERE t.id = task_assignees.task_id AND is_project_member(t.project_id))
);
CREATE POLICY "Project members access task comments" ON public.task_comments FOR ALL USING (
    EXISTS (SELECT 1 FROM public.tasks t WHERE t.id = task_comments.task_id AND is_project_member(t.project_id))
);
CREATE POLICY "Project members access task dependencies" ON public.task_dependencies FOR ALL USING (
    EXISTS (SELECT 1 FROM public.tasks t WHERE t.id = task_dependencies.task_id AND is_project_member(t.project_id))
);

-- SECURE DISCUSSIONS POLICIES (Client Confidentiality Enforced)
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
CREATE POLICY "Project members access discussion comments" ON public.discussion_comments FOR ALL USING (
    EXISTS (SELECT 1 FROM public.discussions d WHERE d.id = discussion_comments.discussion_id AND is_project_member(d.project_id) AND (is_internal_user() OR d.is_client_visible = TRUE))
);

-- SECURE CAMPFIRE CHAT POLICIES (Internal Company Staff Only - Clients Prohibited)
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
CREATE POLICY "Internal members access message reactions" ON public.message_reactions FOR ALL USING (
    EXISTS (
        SELECT 1 FROM public.chat_messages m
        JOIN public.chat_rooms r ON r.id = m.room_id
        WHERE m.id = message_reactions.message_id AND is_project_member(r.project_id) AND is_internal_user()
    )
);

-- SECURE ORGANIZATION INVITATIONS (Privilege Escalation Protected)
CREATE POLICY "Admins view invitations" ON public.organization_invitations FOR SELECT USING (
    is_org_admin(organization_id)
);
CREATE POLICY "Admins and members invite" ON public.organization_invitations FOR INSERT WITH CHECK (
    is_org_admin(organization_id) OR (is_internal_user() AND role NOT IN ('OWNER', 'ADMIN'))
);
CREATE POLICY "Admins delete invitations" ON public.organization_invitations FOR DELETE USING (
    is_org_admin(organization_id)
);

-- SECURE DIRECT MESSAGES (PARTICIPANT-ONLY ACCESS)
CREATE POLICY "Participants view direct conversations" ON public.direct_conversations FOR SELECT USING (
    user1_id = auth.uid() OR user2_id = auth.uid()
);
CREATE POLICY "Participants create direct conversations" ON public.direct_conversations FOR INSERT WITH CHECK (
    user1_id = auth.uid() OR user2_id = auth.uid()
);
CREATE POLICY "Participants update direct conversations" ON public.direct_conversations FOR UPDATE USING (
    user1_id = auth.uid() OR user2_id = auth.uid()
);
CREATE POLICY "Participants delete direct conversations" ON public.direct_conversations FOR DELETE USING (
    user1_id = auth.uid() OR user2_id = auth.uid()
);

CREATE POLICY "Participants view direct messages" ON public.direct_messages FOR SELECT USING (
    EXISTS (
        SELECT 1 FROM public.direct_conversations dc
        WHERE dc.id = direct_messages.conversation_id
        AND (dc.user1_id = auth.uid() OR dc.user2_id = auth.uid())
    )
);
CREATE POLICY "Participants send direct messages" ON public.direct_messages FOR INSERT WITH CHECK (
    sender_id = auth.uid() AND EXISTS (
        SELECT 1 FROM public.direct_conversations dc
        WHERE dc.id = direct_messages.conversation_id
        AND (dc.user1_id = auth.uid() OR dc.user2_id = auth.uid())
    )
);
CREATE POLICY "Senders update their direct messages" ON public.direct_messages FOR UPDATE USING (sender_id = auth.uid());
CREATE POLICY "Senders delete their direct messages" ON public.direct_messages FOR DELETE USING (sender_id = auth.uid());

-- SECURE SCHEDULE, FILES, DOCUMENTS, CHECK-INS & ACTIVITY LOGS
CREATE POLICY "Project members access calendar events" ON public.calendar_events FOR ALL USING (is_project_member(project_id));
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

CREATE POLICY "Project members view documents" ON public.documents FOR SELECT USING (
    is_project_member(project_id)
);
CREATE POLICY "Internal members create documents" ON public.documents FOR INSERT WITH CHECK (
    is_project_member(project_id) AND is_internal_user()
);
CREATE POLICY "Internal members update documents" ON public.documents FOR UPDATE USING (
    is_project_member(project_id) AND is_internal_user()
);
CREATE POLICY "Project leaders delete documents" ON public.documents FOR DELETE USING (
    is_project_manager_or_owner(project_id) OR (created_by = auth.uid() AND is_internal_user())
);
CREATE POLICY "Project members access checkin questions" ON public.checkin_questions FOR ALL USING (is_project_member(project_id));
CREATE POLICY "Project members access checkin responses" ON public.checkin_responses FOR ALL USING (
    EXISTS (SELECT 1 FROM public.checkin_questions q WHERE q.id = checkin_responses.question_id AND is_project_member(q.project_id))
);
CREATE POLICY "Org members view activity logs" ON public.activity_logs FOR SELECT USING (is_org_member(organization_id));
CREATE POLICY "Org members record activity logs" ON public.activity_logs FOR INSERT WITH CHECK (is_org_member(organization_id));
CREATE POLICY "Users view own notifications" ON public.notifications FOR ALL USING (user_id = auth.uid());
CREATE POLICY "Users manage own labels" ON public.labels FOR ALL USING (is_org_member(organization_id));
CREATE POLICY "Users manage own task labels" ON public.task_labels FOR ALL USING (
    EXISTS (SELECT 1 FROM public.tasks t WHERE t.id = task_labels.task_id AND is_project_member(t.project_id))
);

-- 22. PROJECT TEMPLATES
CREATE TABLE IF NOT EXISTS public.project_templates (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name TEXT NOT NULL,
    description TEXT,
    category TEXT DEFAULT 'General',
    structure JSONB NOT NULL DEFAULT '{}'::jsonb,
    is_system BOOLEAN DEFAULT false,
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 23. PROJECT TOOL SETTINGS
CREATE TABLE IF NOT EXISTS public.project_tool_settings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID UNIQUE NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    disabled_tools JSONB NOT NULL DEFAULT '[]'::jsonb,
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 24. TASK ATTACHMENTS
CREATE TABLE IF NOT EXISTS public.task_attachments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    task_id UUID NOT NULL REFERENCES public.tasks(id) ON DELETE CASCADE,
    project_id UUID NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    mime_type TEXT NOT NULL,
    size BIGINT DEFAULT 0,
    uploaded_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 25. TASK WATCHERS
CREATE TABLE IF NOT EXISTS public.task_watchers (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    task_id UUID NOT NULL REFERENCES public.tasks(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(task_id, user_id)
);

-- 26. TASK REMINDERS
CREATE TABLE IF NOT EXISTS public.task_reminders (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    task_id UUID NOT NULL REFERENCES public.tasks(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    reminder_type TEXT NOT NULL DEFAULT 'AT_DUE',
    remind_at TIMESTAMPTZ NOT NULL,
    is_sent BOOLEAN DEFAULT false,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 27. DOCUMENT VERSIONS
CREATE TABLE IF NOT EXISTS public.document_versions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    document_id UUID NOT NULL REFERENCES public.documents(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    version_number INT NOT NULL DEFAULT 1,
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 28. USER BOOKMARKS
CREATE TABLE IF NOT EXISTS public.user_bookmarks (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    item_type TEXT NOT NULL,
    item_id UUID NOT NULL,
    title TEXT NOT NULL,
    url TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(user_id, item_type, item_id)
);

-- 29. USER PERSONAL NOTES
CREATE TABLE IF NOT EXISTS public.user_personal_notes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    title TEXT NOT NULL DEFAULT 'Untitled Note',
    content TEXT DEFAULT '',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 30. USER RECENTLY VIEWED
CREATE TABLE IF NOT EXISTS public.user_recently_viewed (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    item_type TEXT NOT NULL,
    item_id UUID NOT NULL,
    title TEXT NOT NULL,
    url TEXT NOT NULL,
    viewed_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(user_id, item_type, item_id)
);

-- 31. USER ONBOARDING
CREATE TABLE IF NOT EXISTS public.user_onboarding (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID UNIQUE NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    step INT DEFAULT 1,
    is_completed BOOLEAN DEFAULT false,
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ALTERATIONS FOR RECURRENCE & CHAT IMPROVEMENTS
ALTER TABLE public.tasks ADD COLUMN IF NOT EXISTS recurrence_pattern JSONB DEFAULT NULL;
ALTER TABLE public.tasks ADD COLUMN IF NOT EXISTS recurring_parent_id UUID REFERENCES public.tasks(id) ON DELETE SET NULL;
ALTER TABLE public.chat_messages ADD COLUMN IF NOT EXISTS reply_to_id UUID REFERENCES public.chat_messages(id) ON DELETE SET NULL;
ALTER TABLE public.chat_messages ADD COLUMN IF NOT EXISTS attachments JSONB DEFAULT '[]'::jsonb;
ALTER TABLE public.chat_messages ADD COLUMN IF NOT EXISTS is_edited BOOLEAN DEFAULT false;
ALTER TABLE public.chat_messages ADD COLUMN IF NOT EXISTS edited_at TIMESTAMPTZ DEFAULT NULL;

-- NEW RLS POLICIES
ALTER TABLE public.project_templates ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.project_tool_settings ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.task_attachments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.task_watchers ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.task_reminders ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.document_versions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_bookmarks ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_personal_notes ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_recently_viewed ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_onboarding ENABLE ROW LEVEL SECURITY;

-- SECURE PROJECT AUXILIARY POLICIES
CREATE POLICY "Project members access project templates" ON public.project_templates FOR ALL USING (auth.uid() IS NOT NULL);
CREATE POLICY "Project members access tool settings" ON public.project_tool_settings FOR ALL USING (is_project_member(project_id));
CREATE POLICY "Project members access task attachments" ON public.task_attachments FOR ALL USING (is_project_member(project_id));
CREATE POLICY "Project members access task watchers" ON public.task_watchers FOR ALL USING (
    EXISTS (SELECT 1 FROM public.tasks t WHERE t.id = task_watchers.task_id AND is_project_member(t.project_id))
);
CREATE POLICY "Users access own task reminders" ON public.task_reminders FOR ALL USING (user_id = auth.uid());
CREATE POLICY "Project members access document versions" ON public.document_versions FOR ALL USING (
    EXISTS (SELECT 1 FROM public.documents d WHERE d.id = document_versions.document_id AND is_project_member(d.project_id))
);
CREATE POLICY "Users access own bookmarks" ON public.user_bookmarks FOR ALL USING (user_id = auth.uid());
CREATE POLICY "Users access own notes" ON public.user_personal_notes FOR ALL USING (user_id = auth.uid());
CREATE POLICY "Users access own recent history" ON public.user_recently_viewed FOR ALL USING (user_id = auth.uid());
CREATE POLICY "Users access own onboarding" ON public.user_onboarding FOR ALL USING (user_id = auth.uid());

-- 32. BASECAMP HILL CHART SCOPES (Phase 4)
CREATE TABLE IF NOT EXISTS public.hill_chart_scopes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id UUID NOT NULL REFERENCES public.projects(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    color TEXT DEFAULT '#6366f1',
    progress INT NOT NULL DEFAULT 50 CHECK (progress >= 0 AND progress <= 100),
    description TEXT,
    tasks_count INT DEFAULT 0,
    completed_count INT DEFAULT 0,
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

ALTER TABLE public.hill_chart_scopes ENABLE ROW LEVEL SECURITY;
CREATE POLICY "Project members access hill chart scopes" ON public.hill_chart_scopes FOR ALL USING (is_project_member(project_id));

-- 33. USER NOTIFICATION PREFERENCES (Phase 12)
CREATE TABLE IF NOT EXISTS public.user_notification_preferences (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID UNIQUE NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    email_enabled BOOLEAN DEFAULT true,
    push_enabled BOOLEAN DEFAULT false,
    in_app_enabled BOOLEAN DEFAULT true,
    categories JSONB DEFAULT '{"task_assigned":true,"comments":true,"mentions":true,"direct_messages":true,"project_updates":true,"checkins":true,"reminders":true}'::jsonb,
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

ALTER TABLE public.user_notification_preferences ENABLE ROW LEVEL SECURITY;
CREATE POLICY "Users access own notification preferences" ON public.user_notification_preferences FOR ALL USING (user_id = auth.uid());

-- 34. INBOUND EMAIL REPLIES AUDIT (Phase 8)
CREATE TABLE IF NOT EXISTS public.inbound_email_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    message_id TEXT UNIQUE NOT NULL,
    sender_email TEXT NOT NULL,
    recipient_email TEXT NOT NULL,
    thread_type TEXT NOT NULL,
    thread_id TEXT NOT NULL,
    subject TEXT,
    raw_payload JSONB,
    status TEXT DEFAULT 'PROCESSED',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

ALTER TABLE public.inbound_email_logs ENABLE ROW LEVEL SECURITY;
CREATE POLICY "Admins view inbound email logs" ON public.inbound_email_logs FOR SELECT USING (auth.uid() IS NOT NULL);

-- 35. MULTI-TENANT SAAS ARCHITECTURE (2026-09-10)
ALTER TABLE public.organization_invitations ADD COLUMN IF NOT EXISTS team_id UUID REFERENCES public.teams(id) ON DELETE SET NULL;
ALTER TABLE public.organization_invitations ADD COLUMN IF NOT EXISTS project_id UUID REFERENCES public.projects(id) ON DELETE CASCADE;
ALTER TABLE public.organization_invitations ADD COLUMN IF NOT EXISTS project_role public.project_member_role DEFAULT 'PROJECT_MEMBER';
ALTER TABLE public.organization_invitations ADD COLUMN IF NOT EXISTS accepted_at TIMESTAMPTZ DEFAULT NULL;
ALTER TABLE public.organization_invitations ADD COLUMN IF NOT EXISTS accepted_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_org_invitations_token ON public.organization_invitations(token);
CREATE INDEX IF NOT EXISTS idx_org_invitations_email ON public.organization_invitations(email);
CREATE INDEX IF NOT EXISTS idx_org_invitations_org ON public.organization_invitations(organization_id);
CREATE INDEX IF NOT EXISTS idx_org_invitations_status ON public.organization_invitations(status);

-- Integrity Trigger: Enforce team members must belong to team organization
CREATE OR REPLACE FUNCTION public.validate_team_member_organization()
RETURNS TRIGGER AS $$
DECLARE
    team_org_id UUID;
    is_member BOOLEAN;
BEGIN
    SELECT organization_id INTO team_org_id FROM public.teams WHERE id = NEW.team_id;
    
    IF team_org_id IS NULL THEN
        RAISE EXCEPTION 'Team does not exist or has no associated organization.';
    END IF;

    SELECT EXISTS (
        SELECT 1 FROM public.organization_members
        WHERE organization_id = team_org_id AND user_id = NEW.user_id
    ) INTO is_member;

    IF NOT is_member THEN
        RAISE EXCEPTION 'User % is not a member of organization % for team %', NEW.user_id, team_org_id, NEW.team_id;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

DROP TRIGGER IF EXISTS trg_validate_team_member_org ON public.team_members;
CREATE TRIGGER trg_validate_team_member_org
    BEFORE INSERT OR UPDATE ON public.team_members
    FOR EACH ROW
    EXECUTE FUNCTION public.validate_team_member_organization();

-- Integrity Trigger: Enforce project members must belong to project organization (or be PROJECT_CLIENT)
CREATE OR REPLACE FUNCTION public.validate_project_member_organization()
RETURNS TRIGGER AS $$
DECLARE
    proj_org_id UUID;
    is_org_member BOOLEAN;
BEGIN
    SELECT organization_id INTO proj_org_id FROM public.projects WHERE id = NEW.project_id;

    IF proj_org_id IS NULL THEN
        RAISE EXCEPTION 'Project does not exist or has no associated organization.';
    END IF;

    SELECT EXISTS (
        SELECT 1 FROM public.organization_members
        WHERE organization_id = proj_org_id AND user_id = NEW.user_id
    ) INTO is_org_member;

    IF NOT is_org_member AND NEW.role <> 'PROJECT_CLIENT' THEN
        RAISE EXCEPTION 'User % must belong to organization % to be an internal project member.', NEW.user_id, proj_org_id;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

DROP TRIGGER IF EXISTS trg_validate_project_member_org ON public.project_members;
CREATE TRIGGER trg_validate_project_member_org
    BEFORE INSERT OR UPDATE ON public.project_members
    FOR EACH ROW
    EXECUTE FUNCTION public.validate_project_member_organization();

CREATE INDEX IF NOT EXISTS idx_org_members_user ON public.organization_members(user_id);
CREATE INDEX IF NOT EXISTS idx_org_members_org_user ON public.organization_members(organization_id, user_id);
CREATE INDEX IF NOT EXISTS idx_projects_org ON public.projects(organization_id);
CREATE INDEX IF NOT EXISTS idx_teams_org ON public.teams(organization_id);
CREATE INDEX IF NOT EXISTS idx_direct_conv_org ON public.direct_conversations(organization_id);
CREATE INDEX IF NOT EXISTS idx_notifications_org_user ON public.notifications(organization_id, user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_user_unread ON public.notifications(user_id, is_read);

-- Storage Object RLS Policies for project-files bucket
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'storage' AND table_name = 'objects') THEN
        DROP POLICY IF EXISTS "Project members can read project files" ON storage.objects;
        CREATE POLICY "Project members can read project files" ON storage.objects FOR SELECT USING (
            bucket_id = 'project-files' AND
            public.is_project_member((split_part(name, '/', 1))::uuid)
        );

        DROP POLICY IF EXISTS "Project members can upload project files" ON storage.objects;
        CREATE POLICY "Project members can upload project files" ON storage.objects FOR INSERT WITH CHECK (
            bucket_id = 'project-files' AND
            public.is_project_member((split_part(name, '/', 1))::uuid)
        );

        DROP POLICY IF EXISTS "Project leaders can delete project files" ON storage.objects;
        CREATE POLICY "Project leaders can delete project files" ON storage.objects FOR DELETE USING (
            bucket_id = 'project-files' AND
            public.is_project_manager_or_owner((split_part(name, '/', 1))::uuid)
        );
    END IF;
END $$;


