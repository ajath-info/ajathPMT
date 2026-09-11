-- WorkSphere / Ajath PMT: Multi-Tenant SaaS Architecture Migration
-- Date: 2026-09-10
-- Purpose: Additive, non-destructive migration converting system to a true multi-tenant SaaS model.
-- Organizations are customer tenants. Ajath is preserved as one tenant.
-- Cross-tenant isolation is enforced at DB, relationship, constraint, and RLS levels.

-- 1. ENHANCE ORGANIZATION INVITATIONS FOR ROBUST EMPLOYEE & CLIENT FLOWS
ALTER TABLE public.organization_invitations ADD COLUMN IF NOT EXISTS team_id UUID REFERENCES public.teams(id) ON DELETE SET NULL;
ALTER TABLE public.organization_invitations ADD COLUMN IF NOT EXISTS project_id UUID REFERENCES public.projects(id) ON DELETE CASCADE;
ALTER TABLE public.organization_invitations ADD COLUMN IF NOT EXISTS project_role public.project_member_role DEFAULT 'PROJECT_MEMBER';
ALTER TABLE public.organization_invitations ADD COLUMN IF NOT EXISTS accepted_at TIMESTAMPTZ DEFAULT NULL;
ALTER TABLE public.organization_invitations ADD COLUMN IF NOT EXISTS accepted_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_org_invitations_token ON public.organization_invitations(token);
CREATE INDEX IF NOT EXISTS idx_org_invitations_email ON public.organization_invitations(email);
CREATE INDEX IF NOT EXISTS idx_org_invitations_org ON public.organization_invitations(organization_id);
CREATE INDEX IF NOT EXISTS idx_org_invitations_status ON public.organization_invitations(status);

-- 2. INTEGRITY TRIGGER: ENFORCE TEAM MEMBERS MUST BELONG TO TEAM ORGANIZATION
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

-- 3. INTEGRITY TRIGGER: ENFORCE PROJECT MEMBERS MUST BELONG TO PROJECT ORGANIZATION (OR BE PROJECT_CLIENT)
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

    -- If not an organization member, only allow if they are designated as a PROJECT_CLIENT with explicit client invitation
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

-- 4. RLS POLICIES FOR INVITATIONS: ALLOW INVITEES TO VIEW AND ACCEPT THEIR INVITATIONS
DO $$ BEGIN
    CREATE POLICY "Invitees view their own pending invitations" ON public.organization_invitations
        FOR SELECT USING (
            auth.uid() IS NOT NULL AND (
                is_org_admin(organization_id)
                OR email = (SELECT email FROM public.profiles WHERE id = auth.uid())
            )
        );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE POLICY "Invitees update their invitation status" ON public.organization_invitations
        FOR UPDATE USING (
            auth.uid() IS NOT NULL AND (
                is_org_admin(organization_id)
                OR email = (SELECT email FROM public.profiles WHERE id = auth.uid())
            )
        );
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

-- 5. RLS POLICY FOR ORGANIZATIONS CREATION & MEMBERSHIP
-- Make sure any authenticated user can create a new organization
DROP POLICY IF EXISTS "Authenticated users can create organizations" ON public.organizations;
CREATE POLICY "Authenticated users can create organizations" ON public.organizations
    FOR INSERT WITH CHECK (
        auth.uid() IS NOT NULL AND owner_id = auth.uid()
    );

-- Creator automatically inserts themselves as OWNER in organization_members
DROP POLICY IF EXISTS "Owners insert initial org membership" ON public.organization_members;
CREATE POLICY "Owners insert initial org membership" ON public.organization_members
    FOR INSERT WITH CHECK (
        user_id = auth.uid() OR is_org_admin(organization_id)
    );

-- Performance indexes for tenant queries
CREATE INDEX IF NOT EXISTS idx_org_members_user ON public.organization_members(user_id);
CREATE INDEX IF NOT EXISTS idx_org_members_org_user ON public.organization_members(organization_id, user_id);
CREATE INDEX IF NOT EXISTS idx_projects_org ON public.projects(organization_id);
CREATE INDEX IF NOT EXISTS idx_teams_org ON public.teams(organization_id);
CREATE INDEX IF NOT EXISTS idx_direct_conv_org ON public.direct_conversations(organization_id);
