-- WorkSphere Seed Data Script
-- Insert initial demo organizations, projects, and users for local or cloud testing.

-- Insert demo organization
INSERT INTO public.organizations (id, name, slug)
VALUES ('00000000-0000-0000-0000-000000000001', 'Acme Workspace', 'acme-workspace')
ON CONFLICT (id) DO NOTHING;

-- Insert demo project
INSERT INTO public.projects (id, organization_id, name, description, status)
VALUES (
  '00000000-0000-0000-0000-000000000002',
  '00000000-0000-0000-0000-000000000001',
  'WorkSphere Web Platform v1.0',
  'Comprehensive project management & team collaboration hub build.',
  'Active'
) ON CONFLICT (id) DO NOTHING;
