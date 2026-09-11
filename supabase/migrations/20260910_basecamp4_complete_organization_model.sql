-- WorkSphere / Ajath PMT: Basecamp 4 Complete Organization Model Migration
-- Date: 2026-09-10
-- Purpose: Complete tenant scoping for notifications, file storage RLS policies, and non-destructive index optimization.

-- 1. Tenant-scoped notifications
ALTER TABLE public.notifications ADD COLUMN IF NOT EXISTS organization_id UUID REFERENCES public.organizations(id) ON DELETE CASCADE;

CREATE INDEX IF NOT EXISTS idx_notifications_org_user ON public.notifications(organization_id, user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_user_unread ON public.notifications(user_id, is_read);

-- Backfill existing notifications with organization_id from projects if linked
UPDATE public.notifications n
SET organization_id = p.organization_id
FROM public.projects p
WHERE n.organization_id IS NULL AND n.link_url LIKE '/projects/' || p.id || '%';

-- 2. Storage Object RLS Policies for project-files bucket
-- Ensures users cannot read, upload, or delete files across organization or project boundaries
DO $$
BEGIN
    -- Check if storage schema and objects table exist in Supabase
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'storage' AND table_name = 'objects') THEN
        -- Select policy: Project members can view files in their projects
        DROP POLICY IF EXISTS "Project members can read project files" ON storage.objects;
        CREATE POLICY "Project members can read project files" ON storage.objects FOR SELECT USING (
            bucket_id = 'project-files' AND
            public.is_project_member((split_part(name, '/', 1))::uuid)
        );

        -- Insert policy: Project members can upload files to their projects
        DROP POLICY IF EXISTS "Project members can upload project files" ON storage.objects;
        CREATE POLICY "Project members can upload project files" ON storage.objects FOR INSERT WITH CHECK (
            bucket_id = 'project-files' AND
            public.is_project_member((split_part(name, '/', 1))::uuid)
        );

        -- Delete policy: Project managers and owners can delete project files
        DROP POLICY IF EXISTS "Project leaders can delete project files" ON storage.objects;
        CREATE POLICY "Project leaders can delete project files" ON storage.objects FOR DELETE USING (
            bucket_id = 'project-files' AND
            public.is_project_manager_or_owner((split_part(name, '/', 1))::uuid)
        );
    END IF;
END $$;
