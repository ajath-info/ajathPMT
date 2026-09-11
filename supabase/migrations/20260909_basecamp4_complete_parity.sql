-- WorkSphere / Ajath PMT: Complete Basecamp 4 Functional Parity Migration
-- Date: 2026-09-09
-- Purpose: Additive, non-destructive migration introducing multi-person group pings,
-- to-do groups, calendar attendees/comments/recurrence, file comments/versions,
-- discussion versions/scheduling, notification quiet hours/aggregation, project soft-trash, and card table triage.

-- 1. MULTI-PERSON GROUP PINGS / DIRECT CONVERSATIONS
ALTER TABLE public.direct_conversations ADD COLUMN IF NOT EXISTS title TEXT;
ALTER TABLE public.direct_conversations ADD COLUMN IF NOT EXISTS is_group BOOLEAN DEFAULT FALSE;

CREATE TABLE IF NOT EXISTS public.direct_conversation_participants (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    conversation_id UUID NOT NULL REFERENCES public.direct_conversations(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(conversation_id, user_id)
);

CREATE INDEX IF NOT EXISTS idx_dm_part_conv ON public.direct_conversation_participants(conversation_id);
CREATE INDEX IF NOT EXISTS idx_dm_part_user ON public.direct_conversation_participants(user_id);

ALTER TABLE public.direct_conversation_participants ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN
    CREATE POLICY "Participants view conversation participants" ON public.direct_conversation_participants
        FOR SELECT USING (
            user_id = auth.uid() OR EXISTS (
                SELECT 1 FROM public.direct_conversation_participants p
                WHERE p.conversation_id = direct_conversation_participants.conversation_id AND p.user_id = auth.uid()
            ) OR EXISTS (
                SELECT 1 FROM public.direct_conversations dc
                WHERE dc.id = direct_conversation_participants.conversation_id AND (dc.user1_id = auth.uid() OR dc.user2_id = auth.uid())
            )
        );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE POLICY "Participants manage conversation participants" ON public.direct_conversation_participants
        FOR ALL USING (
            user_id = auth.uid() OR EXISTS (
                SELECT 1 FROM public.direct_conversation_participants p
                WHERE p.conversation_id = direct_conversation_participants.conversation_id AND p.user_id = auth.uid()
            ) OR EXISTS (
                SELECT 1 FROM public.direct_conversations dc
                WHERE dc.id = direct_conversation_participants.conversation_id AND (dc.user1_id = auth.uid() OR dc.user2_id = auth.uid())
            )
        );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

-- 2. TO-DO GROUPS
CREATE TABLE IF NOT EXISTS public.todo_groups (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    todo_list_id UUID NOT NULL REFERENCES public.todo_lists(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    position INT DEFAULT 0,
    created_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_todo_groups_list ON public.todo_groups(todo_list_id);

ALTER TABLE public.tasks ADD COLUMN IF NOT EXISTS todo_group_id UUID REFERENCES public.todo_groups(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_tasks_todo_group ON public.tasks(todo_group_id);

ALTER TABLE public.todo_groups ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN
    CREATE POLICY "Project members access todo groups" ON public.todo_groups
        FOR ALL USING (
            EXISTS (
                SELECT 1 FROM public.todo_lists l
                WHERE l.id = todo_groups.todo_list_id AND is_project_member(l.project_id)
            )
        );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

-- 3. CALENDAR ENHANCEMENTS: ATTENDEES, COMMENTS, ATTACHMENTS & RECURRENCE
ALTER TABLE public.calendar_events ADD COLUMN IF NOT EXISTS location TEXT;
ALTER TABLE public.calendar_events ADD COLUMN IF NOT EXISTS recurrence_rule JSONB DEFAULT NULL;
ALTER TABLE public.calendar_events ADD COLUMN IF NOT EXISTS parent_event_id UUID REFERENCES public.calendar_events(id) ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS public.calendar_event_attendees (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    event_id UUID NOT NULL REFERENCES public.calendar_events(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    rsvp_status TEXT DEFAULT 'PENDING',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(event_id, user_id)
);

CREATE INDEX IF NOT EXISTS idx_cal_att_event ON public.calendar_event_attendees(event_id);
CREATE INDEX IF NOT EXISTS idx_cal_att_user ON public.calendar_event_attendees(user_id);

ALTER TABLE public.calendar_event_attendees ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN
    CREATE POLICY "Project members access event attendees" ON public.calendar_event_attendees
        FOR ALL USING (
            EXISTS (
                SELECT 1 FROM public.calendar_events e
                WHERE e.id = calendar_event_attendees.event_id AND is_project_member(e.project_id)
            )
        );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS public.calendar_event_comments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    event_id UUID NOT NULL REFERENCES public.calendar_events(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_cal_com_event ON public.calendar_event_comments(event_id);

ALTER TABLE public.calendar_event_comments ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN
    CREATE POLICY "Project members access event comments" ON public.calendar_event_comments
        FOR ALL USING (
            EXISTS (
                SELECT 1 FROM public.calendar_events e
                WHERE e.id = calendar_event_comments.event_id AND is_project_member(e.project_id)
            )
        );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS public.calendar_event_attachments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    event_id UUID NOT NULL REFERENCES public.calendar_events(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    storage_path TEXT NOT NULL,
    mime_type TEXT,
    size BIGINT DEFAULT 0,
    uploaded_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_cal_att_file ON public.calendar_event_attachments(event_id);

ALTER TABLE public.calendar_event_attachments ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN
    CREATE POLICY "Project members access event attachments" ON public.calendar_event_attachments
        FOR ALL USING (
            EXISTS (
                SELECT 1 FROM public.calendar_events e
                WHERE e.id = calendar_event_attachments.event_id AND is_project_member(e.project_id)
            )
        );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

-- 4. FILE COMMENTS & VERSION REPLACEMENT
CREATE TABLE IF NOT EXISTS public.file_comments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    file_id UUID NOT NULL REFERENCES public.files(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_file_com_file ON public.file_comments(file_id);

ALTER TABLE public.file_comments ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN
    CREATE POLICY "Project members access file comments" ON public.file_comments
        FOR ALL USING (
            EXISTS (
                SELECT 1 FROM public.files f
                WHERE f.id = file_comments.file_id AND is_project_member(f.project_id)
            )
        );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

CREATE TABLE IF NOT EXISTS public.file_versions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    file_id UUID NOT NULL REFERENCES public.files(id) ON DELETE CASCADE,
    version_number INT NOT NULL DEFAULT 1,
    storage_path TEXT NOT NULL,
    size BIGINT NOT NULL,
    mime_type TEXT,
    description TEXT,
    uploaded_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_file_ver_file ON public.file_versions(file_id);

ALTER TABLE public.file_versions ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN
    CREATE POLICY "Project members access file versions" ON public.file_versions
        FOR ALL USING (
            EXISTS (
                SELECT 1 FROM public.files f
                WHERE f.id = file_versions.file_id AND is_project_member(f.project_id)
            )
        );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

-- 5. DISCUSSION VERSION HISTORY & SCHEDULED PUBLISHING
ALTER TABLE public.discussions ADD COLUMN IF NOT EXISTS status TEXT DEFAULT 'PUBLISHED';
ALTER TABLE public.discussions ADD COLUMN IF NOT EXISTS scheduled_at TIMESTAMPTZ DEFAULT NULL;

CREATE TABLE IF NOT EXISTS public.discussion_versions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    discussion_id UUID NOT NULL REFERENCES public.discussions(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    version_number INT NOT NULL DEFAULT 1,
    edited_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_disc_ver_disc ON public.discussion_versions(discussion_id);

ALTER TABLE public.discussion_versions ENABLE ROW LEVEL SECURITY;

DO $$ BEGIN
    CREATE POLICY "Project members view discussion versions" ON public.discussion_versions
        FOR SELECT USING (
            EXISTS (
                SELECT 1 FROM public.discussions d
                WHERE d.id = discussion_versions.discussion_id AND is_project_member(d.project_id)
                AND (is_internal_user() OR d.is_client_visible = TRUE)
            )
        );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

-- 6. NOTIFICATION QUIET HOURS & AGGREGATION
ALTER TABLE public.user_notification_preferences ADD COLUMN IF NOT EXISTS work_days JSONB DEFAULT '[1,2,3,4,5]'::jsonb;
ALTER TABLE public.user_notification_preferences ADD COLUMN IF NOT EXISTS work_hours_start TEXT DEFAULT '09:00';
ALTER TABLE public.user_notification_preferences ADD COLUMN IF NOT EXISTS work_hours_end TEXT DEFAULT '18:00';
ALTER TABLE public.user_notification_preferences ADD COLUMN IF NOT EXISTS quiet_hours_enabled BOOLEAN DEFAULT FALSE;
ALTER TABLE public.user_notification_preferences ADD COLUMN IF NOT EXISTS timezone TEXT DEFAULT 'UTC';

ALTER TABLE public.notifications ADD COLUMN IF NOT EXISTS aggregate_count INT DEFAULT 1;
ALTER TABLE public.notifications ADD COLUMN IF NOT EXISTS queued_for_quiet_hours BOOLEAN DEFAULT FALSE;

-- 7. PROJECT SOFT TRASH & 30-DAY RETENTION
ALTER TABLE public.projects ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMPTZ DEFAULT NULL;
CREATE INDEX IF NOT EXISTS idx_projects_deleted ON public.projects(deleted_at);

-- 8. CARD TABLE TRIAGE
ALTER TABLE public.kanban_columns ADD COLUMN IF NOT EXISTS is_triage BOOLEAN DEFAULT FALSE;
