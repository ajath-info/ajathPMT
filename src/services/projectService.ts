import { supabase, isSupabaseConfigured } from '../lib/supabase';
import {
  Project,
  ProjectMember,
  ProjectMemberRole,
  CreateProjectInput,
  UpdateProjectInput,
  OrgRole,
  OrganizationInvitation,
} from '../types';
import { logActivity, createInvitation } from './organizationService';
import { createNotification } from './notificationService';

const DEFAULT_MOCK_PROJECTS: Project[] = [
  {
    id: 'proj-hii',
    organization_id: 'demo-org-acme',
    team_id: 'team-1',
    name: 'hii',
    slug: 'hii',
    description: '',
    cover_url: 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800&auto=format&fit=crop&q=80',
    status: 'ACTIVE',
    visibility: 'ORGANIZATION',
    start_date: new Date().toISOString().split('T')[0],
    created_by: 'demo-user-owner',
    created_at: new Date().toISOString(),
    progress: 0,
    members_count: 1,
    is_starred: false,
  },
  {
    id: 'proj-hq',
    organization_id: 'demo-org-acme',
    team_id: 'team-1',
    name: 'Ajath Infotech Pvt Ltd HQ',
    slug: 'ajath-infotech-pvt-ltd-hq',
    description: 'Company-wide announcements and stuff everyone needs to know.',
    cover_url: 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&auto=format&fit=crop&q=80',
    status: 'ACTIVE',
    visibility: 'ORGANIZATION',
    start_date: '2026-01-01',
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 30 * 24 * 3600 * 1000).toISOString(),
    progress: 90,
    members_count: 5,
    is_starred: false,
  },
  {
    id: 'proj-rmc',
    organization_id: 'demo-org-acme',
    team_id: 'team-2',
    name: 'Ride My Cars (Edward)',
    slug: 'ride-my-cars-edward',
    description: 'NDC Homes',
    cover_url: 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=800&auto=format&fit=crop&q=80',
    status: 'ACTIVE',
    visibility: 'ORGANIZATION',
    start_date: '2026-02-15',
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 25 * 24 * 3600 * 1000).toISOString(),
    progress: 60,
    members_count: 3,
    is_starred: false,
  },
  {
    id: 'proj-hrms',
    organization_id: 'demo-org-acme',
    team_id: 'team-1',
    name: 'HRMS - Sena Bhawan',
    slug: 'hrms-sena-bhawan',
    description: 'Client',
    cover_url: 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800&auto=format&fit=crop&q=80',
    status: 'ACTIVE',
    visibility: 'ORGANIZATION',
    start_date: '2026-03-01',
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 20 * 24 * 3600 * 1000).toISOString(),
    progress: 45,
    members_count: 2,
    is_starred: false,
  },
  {
    id: 'proj-veggie',
    organization_id: 'demo-org-acme',
    team_id: 'team-2',
    name: 'Veggie- Pro Mobile Apps',
    slug: 'veggie-pro-mobile-apps',
    description: 'Client: Seller and Driver Apps And Admin Panel',
    cover_url: 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=800&auto=format&fit=crop&q=80',
    status: 'ACTIVE',
    visibility: 'ORGANIZATION',
    start_date: '2026-04-10',
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 15 * 24 * 3600 * 1000).toISOString(),
    progress: 70,
    members_count: 1,
    is_starred: false,
  },
  {
    id: 'proj-bipl',
    organization_id: 'demo-org-acme',
    team_id: 'team-3',
    name: 'BIPL APP',
    slug: 'bipl-app',
    description: 'BIPL: Mobile App Developement',
    cover_url: 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800&auto=format&fit=crop&q=80',
    status: 'ACTIVE',
    visibility: 'ORGANIZATION',
    start_date: '2026-05-01',
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 12 * 24 * 3600 * 1000).toISOString(),
    progress: 80,
    members_count: 5,
    is_starred: false,
  },
  {
    id: 'proj-exibine',
    organization_id: 'demo-org-acme',
    team_id: 'team-3',
    name: 'Exibine Mobile App',
    slug: 'exibine-mobile-app',
    description: 'Universal Business Identity App',
    cover_url: 'https://images.unsplash.com/photo-1551650975-87deedd944c3?w=800&auto=format&fit=crop&q=80',
    status: 'ACTIVE',
    visibility: 'ORGANIZATION',
    start_date: '2026-06-01',
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 8 * 24 * 3600 * 1000).toISOString(),
    progress: 35,
    members_count: 3,
    is_starred: false,
  },
  {
    id: 'proj-hihlo',
    organization_id: 'demo-org-acme',
    team_id: 'team-2',
    name: 'HiHlo Social Contact App',
    slug: 'hihlo-social-contact-app',
    description: 'HiHlo',
    cover_url: 'https://images.unsplash.com/photo-1611162617474-5b21e879e113?w=800&auto=format&fit=crop&q=80',
    status: 'ACTIVE',
    visibility: 'ORGANIZATION',
    start_date: '2026-07-01',
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 5 * 24 * 3600 * 1000).toISOString(),
    progress: 55,
    members_count: 2,
    is_starred: false,
  },
  {
    id: 'proj-harsac',
    organization_id: 'demo-org-acme',
    team_id: 'team-1',
    name: 'HARSAC Website Development',
    slug: 'harsac-website-development',
    description: 'The scope of work includes: • Regular website content updates (notices, reports, managing image galleri...',
    cover_url: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&auto=format&fit=crop&q=80',
    status: 'ACTIVE',
    visibility: 'ORGANIZATION',
    start_date: '2026-06-15',
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 7 * 24 * 3600 * 1000).toISOString(),
    progress: 50,
    members_count: 4,
    is_starred: false,
  },
  {
    id: 'proj-archived-legacy',
    organization_id: 'demo-org-acme',
    team_id: 'team-2',
    name: 'Legacy Marketing Portal (Archived)',
    slug: 'legacy-marketing-portal',
    description: 'Archived 2025 promotional campaign and static microsite.',
    cover_url: 'https://images.unsplash.com/photo-1432888498266-38ffec3eaf0a?w=800&auto=format&fit=crop&q=80',
    status: 'ARCHIVED',
    visibility: 'ORGANIZATION',
    start_date: '2025-02-01',
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 365 * 24 * 3600 * 1000).toISOString(),
    progress: 100,
    members_count: 2,
    is_starred: false,
  },
  {
    id: 'proj-trashed-wireframes',
    organization_id: 'demo-org-acme',
    team_id: 'team-3',
    name: 'Z-Old Wireframes Prototype (Trashed)',
    slug: 'z-old-wireframes-prototype',
    description: 'Trashed exploratory wireframe test bed.',
    cover_url: '',
    status: 'ARCHIVED',
    visibility: 'PRIVATE',
    start_date: '2025-08-01',
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 180 * 24 * 3600 * 1000).toISOString(),
    progress: 10,
    members_count: 1,
    is_starred: false,
  },
];

export function getSavedMockProjects(): Project[] {
  try {
    const saved = localStorage.getItem('basecamp_projects_list_v1');
    if (saved) {
      let parsed: Project[] = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length > 0) {
        // Guarantee 'hii' is present
        if (!parsed.some((p) => p.name.toLowerCase() === 'hii' || p.id === 'proj-hii')) {
          parsed.unshift(DEFAULT_MOCK_PROJECTS[0]);
          localStorage.setItem('basecamp_projects_list_v1', JSON.stringify(parsed));
        }
        // Purge any removed 'hlo' project from user's storage
        if (parsed.some((p) => p.name.toLowerCase() === 'hlo' || p.slug === 'hlo' || p.id === 'hlo')) {
          parsed = parsed.filter((p) => p.name.toLowerCase() !== 'hlo' && p.slug !== 'hlo' && p.id !== 'hlo');
          localStorage.setItem('basecamp_projects_list_v1', JSON.stringify(parsed));
        }
        return parsed;
      }
    }
  } catch (e) {}
  return [...DEFAULT_MOCK_PROJECTS];
}

export function saveMockProjects(projects: Project[]) {
  MOCK_PROJECTS = projects;
  try {
    localStorage.setItem('basecamp_projects_list_v1', JSON.stringify(projects));
  } catch (e) {}
}

let MOCK_PROJECTS: Project[] = getSavedMockProjects();

let MOCK_PROJECT_MEMBERS: ProjectMember[] = [
  // Hii: CW
  {
    id: 'pm-hii-owner',
    project_id: 'proj-hii',
    user_id: 'demo-user-owner',
    role: 'PROJECT_OWNER',
    created_at: new Date().toISOString(),
    profile: { id: 'demo-user-owner', email: 'claire.client@partner.com', full_name: 'Claire Watson', avatar_url: '', job_title: 'Account Owner' },
  },
  // HQ: AI, GK, PK, RK, CW
  {
    id: 'pm-1',
    project_id: 'proj-hq',
    user_id: 'user-ai',
    role: 'PROJECT_OWNER',
    created_at: new Date().toISOString(),
    profile: { id: 'user-ai', email: 'hq@ajath.com', full_name: 'Ajath Infotech', avatar_url: '', job_title: 'HQ Team' },
  },
  {
    id: 'pm-2',
    project_id: 'proj-hq',
    user_id: 'user-gk',
    role: 'PROJECT_MANAGER',
    created_at: new Date().toISOString(),
    profile: { id: 'user-gk', email: 'gaurav@ajath.com', full_name: 'Gaurav Kumar', avatar_url: '', job_title: 'Project Lead' },
  },
  {
    id: 'pm-3',
    project_id: 'proj-hq',
    user_id: 'user-pk',
    role: 'PROJECT_MEMBER',
    created_at: new Date().toISOString(),
    profile: { id: 'user-pk', email: 'pankaj@ajath.com', full_name: 'Pankaj Kumar', avatar_url: '', job_title: 'Fullstack Dev' },
  },
  {
    id: 'pm-4',
    project_id: 'proj-hq',
    user_id: 'user-rk',
    role: 'PROJECT_MEMBER',
    created_at: new Date().toISOString(),
    profile: { id: 'user-rk', email: 'rohit@ajath.com', full_name: 'Rohit Kumar', avatar_url: '', job_title: 'Backend Dev' },
  },
  {
    id: 'pm-demo-member',
    project_id: 'proj-hq',
    user_id: 'demo-user-member',
    role: 'PROJECT_MEMBER',
    created_at: new Date().toISOString(),
    profile: { id: 'demo-user-member', email: 'david.member@worksphere.io', full_name: 'David Chen', avatar_url: '', job_title: 'UI/UX Designer' },
  },
  {
    id: 'pm-5',
    project_id: 'proj-hq',
    user_id: 'demo-user-owner',
    role: 'PROJECT_OWNER',
    created_at: new Date().toISOString(),
    profile: { id: 'demo-user-owner', email: 'claire.client@partner.com', full_name: 'Claire Watson', avatar_url: '', job_title: 'Account Owner' },
  },

  // Ride My Cars: E, PK, CW
  {
    id: 'pm-demo-client',
    project_id: 'proj-rmc',
    user_id: 'demo-user-client',
    role: 'PROJECT_CLIENT',
    created_at: new Date().toISOString(),
    profile: { id: 'demo-user-client', email: 'edward.client@partner.com', full_name: 'Edward (Client Partner)', avatar_url: '', job_title: 'Client Partner Representative' },
  },
  {
    id: 'pm-6',
    project_id: 'proj-rmc',
    user_id: 'user-e',
    role: 'PROJECT_CLIENT',
    created_at: new Date().toISOString(),
    profile: { id: 'user-e', email: 'edward@ndchomes.com', full_name: 'Edward', avatar_url: '', job_title: 'Client Partner' },
  },
  {
    id: 'pm-7',
    project_id: 'proj-rmc',
    user_id: 'user-pk',
    role: 'PROJECT_MANAGER',
    created_at: new Date().toISOString(),
    profile: { id: 'user-pk', email: 'pankaj@ajath.com', full_name: 'Pankaj Kumar', avatar_url: '', job_title: 'Mobile Lead' },
  },
  {
    id: 'pm-8',
    project_id: 'proj-rmc',
    user_id: 'demo-user-owner',
    role: 'PROJECT_OWNER',
    created_at: new Date().toISOString(),
    profile: { id: 'demo-user-owner', email: 'claire.client@partner.com', full_name: 'Claire Watson', avatar_url: '', job_title: 'Account Owner' },
  },

  // HRMS: B, CW
  {
    id: 'pm-9',
    project_id: 'proj-hrms',
    user_id: 'user-b',
    role: 'PROJECT_CLIENT',
    created_at: new Date().toISOString(),
    profile: { id: 'user-b', email: 'bhawan@senabhawan.gov', full_name: 'Bhawan Representative', avatar_url: '', job_title: 'Client Rep' },
  },
  {
    id: 'pm-10',
    project_id: 'proj-hrms',
    user_id: 'demo-user-owner',
    role: 'PROJECT_OWNER',
    created_at: new Date().toISOString(),
    profile: { id: 'demo-user-owner', email: 'claire.client@partner.com', full_name: 'Claire Watson', avatar_url: '', job_title: 'Account Owner' },
  },

  // Veggie: CW
  {
    id: 'pm-11',
    project_id: 'proj-veggie',
    user_id: 'demo-user-owner',
    role: 'PROJECT_OWNER',
    created_at: new Date().toISOString(),
    profile: { id: 'demo-user-owner', email: 'claire.client@partner.com', full_name: 'Claire Watson', avatar_url: '', job_title: 'Account Owner' },
  },

  // BIPL: BGD, PT, RASGO, PK, CW
  {
    id: 'pm-12',
    project_id: 'proj-bipl',
    user_id: 'user-bgd',
    role: 'PROJECT_MEMBER',
    created_at: new Date().toISOString(),
    profile: { id: 'user-bgd', email: 'bgd@bipl.com', full_name: 'BIPL Global Dev', avatar_url: '', job_title: 'Lead Architect' },
  },
  {
    id: 'pm-13',
    project_id: 'proj-bipl',
    user_id: 'user-pt',
    role: 'PROJECT_MEMBER',
    created_at: new Date().toISOString(),
    profile: { id: 'user-pt', email: 'pradeep@ajath.com', full_name: 'Pradeep Tiwari', avatar_url: '', job_title: 'App Engineer' },
  },
  {
    id: 'pm-14',
    project_id: 'proj-bipl',
    user_id: 'user-rasgo',
    role: 'PROJECT_MEMBER',
    created_at: new Date().toISOString(),
    profile: { id: 'user-rasgo', email: 'rasgo@bipl.com', full_name: 'Rasgo', avatar_url: '', job_title: 'QA Engineer' },
  },
  {
    id: 'pm-15',
    project_id: 'proj-bipl',
    user_id: 'user-pk',
    role: 'PROJECT_MANAGER',
    created_at: new Date().toISOString(),
    profile: { id: 'user-pk', email: 'pankaj@ajath.com', full_name: 'Pankaj Kumar', avatar_url: '', job_title: 'Tech Lead' },
  },
  {
    id: 'pm-16',
    project_id: 'proj-bipl',
    user_id: 'demo-user-owner',
    role: 'PROJECT_OWNER',
    created_at: new Date().toISOString(),
    profile: { id: 'demo-user-owner', email: 'claire.client@partner.com', full_name: 'Claire Watson', avatar_url: '', job_title: 'Account Owner' },
  },

  // Exibine: TL, PK, CW
  {
    id: 'pm-17',
    project_id: 'proj-exibine',
    user_id: 'user-tl',
    role: 'PROJECT_MEMBER',
    created_at: new Date().toISOString(),
    profile: { id: 'user-tl', email: 'tl@exibine.com', full_name: 'Tech Lead', avatar_url: '', job_title: 'System Architect' },
  },
  {
    id: 'pm-18',
    project_id: 'proj-exibine',
    user_id: 'user-pk',
    role: 'PROJECT_MANAGER',
    created_at: new Date().toISOString(),
    profile: { id: 'user-pk', email: 'pankaj@ajath.com', full_name: 'Pankaj Kumar', avatar_url: '', job_title: 'App Lead' },
  },
  {
    id: 'pm-19',
    project_id: 'proj-exibine',
    user_id: 'demo-user-owner',
    role: 'PROJECT_OWNER',
    created_at: new Date().toISOString(),
    profile: { id: 'demo-user-owner', email: 'claire.client@partner.com', full_name: 'Claire Watson', avatar_url: '', job_title: 'Account Owner' },
  },

  // HiHlo: AR, CW
  {
    id: 'pm-20',
    project_id: 'proj-hihlo',
    user_id: 'user-ar',
    role: 'PROJECT_MEMBER',
    created_at: new Date().toISOString(),
    profile: { id: 'user-ar', email: 'amit@ajath.com', full_name: 'Amit Roy', avatar_url: '', job_title: 'Mobile Engineer' },
  },
  {
    id: 'pm-21',
    project_id: 'proj-hihlo',
    user_id: 'demo-user-owner',
    role: 'PROJECT_OWNER',
    created_at: new Date().toISOString(),
    profile: { id: 'demo-user-owner', email: 'claire.client@partner.com', full_name: 'Claire Watson', avatar_url: '', job_title: 'Account Owner' },
  },
];

export async function getProjects(orgId?: string, userId?: string, userRole?: OrgRole): Promise<Project[]> {
  if (!orgId) return [];

  let projects: Project[] = [];
  if (!isSupabaseConfigured) {
    const all = getSavedMockProjects();
    const membersAll = getSavedMockMembers();
    projects = all
      .filter((p) => !p.trashed_at)
      .filter((p) => p.organization_id === orgId)
      .map((p) => {
        const members = membersAll.filter((pm) => pm.project_id === p.id);
        return { ...p, members, members_count: members.length };
      });
  } else {
    const { data, error } = await supabase
      .from('projects')
      .select('*, members:project_members(*, profile:profiles(*)), team:teams(*)')
      .eq('organization_id', orgId)
      .is('trashed_at', null)
      .order('created_at', { ascending: false });

    if (error || !data) {
      const all = getSavedMockProjects();
      const membersAll = getSavedMockMembers();
      projects = all
        .filter((p) => !p.trashed_at)
        .filter((p) => p.organization_id === orgId)
        .map((p) => {
          const members = membersAll.filter((pm) => pm.project_id === p.id);
          return { ...p, members, members_count: members.length };
        });
    } else {
      projects = data.map((p) => ({
        ...p,
        members_count: p.members?.length || 0,
      })) as Project[];
    }
  }

  // Security Filtering based on Basecamp role
  if (userRole === 'CLIENT') {
    return projects.filter((p) =>
      userId ? p.members?.some((m) => m.user_id === userId) : false
    );
  }

  if (userRole === 'MEMBER') {
    return projects.filter((p) =>
      p.visibility === 'ORGANIZATION' || (userId && p.members?.some((m) => m.user_id === userId))
    );
  }

  return projects;
}

/**
 * Basecamp 4 Project Trash & 30-Day Retention
 */
export async function trashProject(projectId: string, userRole?: OrgRole): Promise<{ error: Error | null }> {
  if (userRole === 'CLIENT') {
    return { error: new Error('Unauthorized: Clients cannot trash projects.') };
  }

  const trashedAt = new Date().toISOString();

  if (!isSupabaseConfigured) {
    const all = getSavedMockProjects().map((p) =>
      p.id === projectId ? { ...p, trashed_at: trashedAt, status: 'ARCHIVED' as const } : p
    );
    saveMockProjects(all);
    return { error: null };
  }

  const { error } = await supabase
    .from('projects')
    .update({ trashed_at: trashedAt, status: 'ARCHIVED' })
    .eq('id', projectId);

  return { error: error as Error | null };
}

export async function restoreTrashedProject(projectId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockProjects().map((p) =>
      p.id === projectId ? { ...p, trashed_at: undefined, status: 'ACTIVE' as const } : p
    );
    saveMockProjects(all);
    return { error: null };
  }

  const { error } = await supabase
    .from('projects')
    .update({ trashed_at: null, status: 'ACTIVE' })
    .eq('id', projectId);

  return { error: error as Error | null };
}

export async function getTrashedProjects(orgId?: string): Promise<Project[]> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockProjects();
    return all.filter((p) => Boolean(p.trashed_at));
  }

  const { data, error } = await supabase
    .from('projects')
    .select('*, members:project_members(*, profile:profiles(*)), team:teams(*)')
    .not('trashed_at', 'is', null)
    .order('trashed_at', { ascending: false });

  if (error || !data) {
    return getSavedMockProjects().filter((p) => Boolean(p.trashed_at));
  }

  return data as Project[];
}

export async function purgeExpiredTrashedProjects(retentionDays: number = 30): Promise<number> {
  const cutoff = new Date(Date.now() - retentionDays * 24 * 3600 * 1000).toISOString();
  let count = 0;

  if (isSupabaseConfigured) {
    try {
      const { data } = await supabase.from('projects').delete().lte('trashed_at', cutoff).select('id');
      if (data) count = data.length;
    } catch (e) {
      console.warn('Failed to purge expired trashed projects:', e);
    }
  }

  const all = getSavedMockProjects();
  const kept = all.filter((p) => {
    if (p.trashed_at && new Date(p.trashed_at) <= new Date(cutoff)) {
      count++;
      return false;
    }
    return true;
  });
  saveMockProjects(kept);
  return count;
}

export async function getProjectById(projectId: string): Promise<Project | null> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockProjects();
    const found = all.find((p) => p.id === projectId);
    if (!found) return null;
    const membersAll = getSavedMockMembers();
    const members = membersAll.filter((pm) => pm.project_id === projectId);
    return { ...found, members, members_count: members.length };
  }

  const { data, error } = await supabase
    .from('projects')
    .select('*, members:project_members(*, profile:profiles(*)), team:teams(*)')
    .eq('id', projectId)
    .single();

  if (error || !data) return null;
  return { ...data, members_count: data.members?.length || 0 } as Project;
}

export async function createProject(input: CreateProjectInput): Promise<{ data: Project | null; error: Error | null }> {
  if (!input.organization_id) {
    return { data: null, error: new Error('Organization context is required to create a project.') };
  }
  const slug = input.name.toLowerCase().replace(/[^a-z0-9]/g, '-') + '-' + Math.random().toString(36).substring(2, 6);

  if (!isSupabaseConfigured) {
    const newProj: Project = {
      id: `proj-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
      organization_id: input.organization_id,
      team_id: input.team_id,
      name: input.name,
      slug,
      description: input.description,
      cover_url: input.cover_url || 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800&auto=format&fit=crop&q=80',
      status: input.status || 'ACTIVE',
      visibility: input.visibility || 'ORGANIZATION',
      start_date: input.start_date || new Date().toISOString().split('T')[0],
      end_date: input.end_date,
      created_by: input.created_by || 'demo-user-owner',
      created_at: new Date().toISOString(),
      progress: 0,
      members_count: 1,
    };
    const all = getSavedMockProjects();
    all.unshift(newProj);
    saveMockProjects(all);

    if (input.created_by) {
      const activeName = getActiveUserFullName();
      MOCK_PROJECT_MEMBERS.push({
        id: `pm-${Date.now()}`,
        project_id: newProj.id,
        user_id: input.created_by,
        role: 'PROJECT_OWNER',
        created_at: new Date().toISOString(),
        profile: {
          id: input.created_by,
          email: 'claire.client@partner.com',
          full_name: activeName,
          avatar_url: '',
          job_title: 'Account Owner',
        },
      });
      saveMockMembers(MOCK_PROJECT_MEMBERS);
    }
    const initialEmptyTools = {
      project_id: newProj.id,
      message_board: false,
      todos: false,
      docs_files: false,
      campfire: false,
      schedule: false,
      checkins: false,
      kanban: false,
      card_table: false,
      doors: false,
    };
    MOCK_TOOL_SETTINGS[newProj.id] = initialEmptyTools;
    try {
      localStorage.setItem(`proj_tools_${newProj.id}`, JSON.stringify(initialEmptyTools));
    } catch (e) {}

    return { data: newProj, error: null };
  }

  const { data, error } = await supabase
    .from('projects')
    .insert([
      {
        organization_id: input.organization_id,
        team_id: input.team_id || null,
        name: input.name,
        slug,
        description: input.description,
        cover_url: input.cover_url,
        status: input.status || 'PLANNING',
        visibility: input.visibility || 'PRIVATE',
        start_date: input.start_date,
        end_date: input.end_date,
        created_by: input.created_by,
      },
    ])
    .select()
    .single();

  if (error) return { data: null, error: error as Error };

  // Set initial empty tools for newly created project
  if (data?.id) {
    const initialEmptyTools = {
      project_id: data.id,
      message_board: false,
      todos: false,
      docs_files: false,
      campfire: false,
      schedule: false,
      checkins: false,
      kanban: false,
      card_table: false,
      doors: false,
    };
    try {
      localStorage.setItem(`proj_tools_${data.id}`, JSON.stringify(initialEmptyTools));
    } catch (e) {}
    await updateProjectToolSettings(data.id, initialEmptyTools);
  }

  // Add creator as PROJECT_OWNER
  if (input.created_by) {
    await supabase.from('project_members').insert([
      { project_id: data.id, user_id: input.created_by, role: 'PROJECT_OWNER', added_by: input.created_by },
    ]);
  }

  if (input.created_by) {
    await logActivity(input.organization_id, input.created_by, 'created project', input.name);
  }

  return { data: data as Project, error: null };
}

export async function updateProject(projectId: string, updates: UpdateProjectInput): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockProjects().map((p) => (p.id === projectId ? { ...p, ...updates } : p));
    saveMockProjects(all);
    return { error: null };
  }

  const { error } = await supabase
    .from('projects')
    .update({ ...updates, updated_at: new Date().toISOString() })
    .eq('id', projectId);

  return { error: error as Error | null };
}

export async function archiveProject(projectId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockProjects().map((p) =>
      p.id === projectId || p.slug === projectId || p.name.toLowerCase() === projectId.toLowerCase()
        ? { ...p, status: 'ARCHIVED' as const, archived_at: new Date().toISOString() }
        : p
    );
    saveMockProjects(all);
    return { error: null };
  }

  const { error } = await supabase
    .from('projects')
    .update({ status: 'ARCHIVED', archived_at: new Date().toISOString() })
    .eq('id', projectId);

  return { error: error as Error | null };
}

export async function restoreProject(projectId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockProjects().map((p) =>
      p.id === projectId || p.slug === projectId || p.name.toLowerCase() === projectId.toLowerCase()
        ? { ...p, status: 'ACTIVE' as const, archived_at: undefined }
        : p
    );
    saveMockProjects(all);
    return { error: null };
  }

  const { error } = await supabase
    .from('projects')
    .update({ status: 'ACTIVE', archived_at: null })
    .eq('id', projectId);

  return { error: error as Error | null };
}

export async function deleteProject(projectId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockProjects().filter(
      (p) => p.id !== projectId && p.slug !== projectId && p.name.toLowerCase() !== projectId.toLowerCase()
    );
    saveMockProjects(all);

    // Clean up associated member relations
    try {
      const allMembers = getSavedMockMembers().filter((pm) => pm.project_id !== projectId);
      saveMockMembers(allMembers);
    } catch (e) {}

    // Clean up associated local storage keys
    try {
      const starred = localStorage.getItem('basecamp_starred_projects');
      if (starred) {
        const set = new Set(JSON.parse(starred));
        set.delete(projectId);
        localStorage.setItem('basecamp_starred_projects', JSON.stringify(Array.from(set)));
      }
      localStorage.removeItem(`basecamp_project_tools_${projectId}`);
      localStorage.removeItem(`proj_tools_${projectId}`);
      localStorage.removeItem(`project_tools_${projectId}`);
      localStorage.removeItem(`basecamp_gauge_settings_${projectId}`);
    } catch (e) {}

    return { error: null };
  }

  const { error } = await supabase.from('projects').delete().eq('id', projectId);
  return { error: error as Error | null };
}

// Profiles dictionary for mock member additions
const MOCK_PROFILES_LOOKUP: Record<string, any> = {
  'demo-user-owner': { id: 'demo-user-owner', full_name: 'Claire Watson', email: 'claire.watson@ajath.com', avatar_url: '', job_title: 'Account Owner' },
  'demo-user-admin': { id: 'demo-user-admin', full_name: 'Sarah Jenkins', email: 'sarah.admin@worksphere.io', avatar_url: '', job_title: 'Admin' },
  'demo-user-member': { id: 'demo-user-member', full_name: 'David Chen', email: 'david.member@worksphere.io', avatar_url: '', job_title: 'Designer' },
  'demo-user-client': { id: 'demo-user-client', full_name: 'Edward (Client Partner)', email: 'edward.client@partner.com', avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80', job_title: 'Client Partner' },
  'user-ai': { id: 'user-ai', full_name: 'Ajath Infotech', email: 'hq@ajath.com', avatar_url: '', job_title: 'HQ Team' },
  'user-gk': { id: 'user-gk', full_name: 'Gaurav Kumar', email: 'gaurav@ajath.com', avatar_url: '', job_title: 'Project Lead' },
  'user-pk': { id: 'user-pk', full_name: 'Pankaj Kumar', email: 'pankaj@ajath.com', avatar_url: '', job_title: 'Fullstack Dev' },
  'user-rk': { id: 'user-rk', full_name: 'Rohit Kumar', email: 'rohit@ajath.com', avatar_url: '', job_title: 'Backend Dev' },
  'user-pt': { id: 'user-pt', full_name: 'Pradeep Tiwari', email: 'pradeep@ajath.com', avatar_url: '', job_title: 'Mobile Engineer' },
  'user-rs': { id: 'user-rs', full_name: 'Rasgo', email: 'rasgo@ajath.com', avatar_url: '', job_title: 'Operations' },
  'user-e': { id: 'user-e', full_name: 'Edward', email: 'edward@ndchomes.com', avatar_url: '', job_title: 'Client Partner' },
};

export function getActiveUserFullName(): string {
  try {
    const saved = localStorage.getItem('basecamp_user_profile');
    if (saved) {
      const p = JSON.parse(saved);
      if (p.full_name) return p.full_name;
    }
    const role = localStorage.getItem('worksphere_demo_role');
    if (role === 'CLIENT') return 'Claire Watson';
    if (role === 'ADMIN') return 'Sarah Jenkins';
    if (role === 'MEMBER') return 'David Chen';
  } catch (e) {}
  return 'Claire Watson';
}

export function getSavedMockMembers(): ProjectMember[] {
  const currentName = getActiveUserFullName();
  let list = MOCK_PROJECT_MEMBERS;
  try {
    const saved = localStorage.getItem('basecamp_project_members_v1');
    if (saved) {
      list = JSON.parse(saved);
    }
  } catch (e) {}

  return list.map((pm) => {
    if (
      pm.user_id === 'demo-user-owner' ||
      pm.user_id === 'demo-user-client' ||
      pm.profile?.id === 'demo-user-owner' ||
      pm.profile?.id === 'demo-user-client' ||
      pm.profile?.full_name === 'Shiv Narayan' ||
      pm.profile?.full_name === 'Shivy Narain' ||
      (pm.profile?.full_name && pm.profile.full_name.toLowerCase().includes('shiv')) ||
      (pm.profile?.full_name && pm.profile.full_name.toLowerCase().includes('narayan'))
    ) {
      return {
        ...pm,
        profile: {
          ...pm.profile,
          id: pm.user_id,
          full_name: currentName,
          email: 'claire.client@partner.com',
          job_title: pm.profile?.job_title || 'Owner',
        },
      };
    }
    return pm;
  });
}

function saveMockMembers(members: ProjectMember[]) {
  MOCK_PROJECT_MEMBERS = members;
  try {
    localStorage.setItem('basecamp_project_members_v1', JSON.stringify(members));
  } catch (e) {}
}

export async function getProjectMembers(projectId: string): Promise<ProjectMember[]> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockMembers();
    return all.filter((pm) => pm.project_id === projectId);
  }

  const { data, error } = await supabase
    .from('project_members')
    .select('*, profile:profiles(*)')
    .eq('project_id', projectId);

  if (error || !data) {
    const all = getSavedMockMembers();
    return all.filter((pm) => pm.project_id === projectId);
  }
  return data as ProjectMember[];
}

export async function addProjectMember(
  projectId: string,
  userId: string,
  role: ProjectMemberRole = 'PROJECT_MEMBER',
  addedBy?: string
): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockMembers();
    const existing = all.find((pm) => pm.project_id === projectId && pm.user_id === userId);
    if (!existing) {
      const profile = MOCK_PROFILES_LOOKUP[userId] || {
        id: userId,
        full_name: userId.replace('user-', '').replace('-', ' '),
        email: `${userId}@company.com`,
        avatar_url: '',
        job_title: 'Collaborator',
      };

      const newMember: ProjectMember = {
        id: `pm-${Date.now()}-${Math.random().toString(36).substr(2, 4)}`,
        project_id: projectId,
        user_id: userId,
        role,
        added_by: addedBy,
        created_at: new Date().toISOString(),
        profile,
      };
      all.push(newMember);
      saveMockMembers(all);
    }
  } else {
    const { error } = await supabase
      .from('project_members')
      .insert([{ project_id: projectId, user_id: userId, role, added_by: addedBy }]);

    if (error) return { error: error as Error };
  }

  // Generate notification for the member (Basecamp requirement #12)
  try {
    const project = await getProjectById(projectId);
    if (project && userId !== addedBy) {
      await createNotification({
        user_id: userId,
        actor_id: addedBy,
        type: 'PROJECT_MEMBER_ADDED',
        title: `Added to project "${project.name}"`,
        message: `You were added to project "${project.name}" with role ${role}.`,
        link_url: `/projects/${projectId}`,
        entity_type: 'PROJECT',
        entity_id: projectId,
      });
    }
  } catch (e) {}

  return { error: null };
}

export async function inviteProjectClient(
  projectId: string,
  email: string,
  clientName?: string,
  personalMessage?: string,
  inviterUserId?: string
): Promise<{ data: any; error: Error | null }> {
  const project = await getProjectById(projectId);
  if (!project) return { data: null, error: new Error('Project not found') };

  const { createInvitation } = await import('./organizationService');
  const res = await createInvitation(
    project.organization_id,
    email,
    'CLIENT',
    inviterUserId || 'demo-user-owner',
    personalMessage
      ? `Client invitation for "${project.name}" (${clientName || email}): ${personalMessage}`
      : `Client invitation for "${project.name}" (${clientName || email})`,
    'ADMIN',
    {
      projectId: project.id,
      projectRole: 'PROJECT_CLIENT',
    }
  );

  if (!isSupabaseConfigured && !res.error) {
    const clientId = `client-${Date.now()}`;
    const clientProfile = {
      id: clientId,
      email: email.toLowerCase(),
      full_name: clientName || email.split('@')[0],
      avatar_url: '',
      job_title: 'Client Partner',
    };
    const all = getSavedMockMembers();
    const existing = all.find(
      (pm) =>
        pm.project_id === projectId &&
        (pm.user_id === clientId || pm.profile?.email === email.toLowerCase())
    );
    if (!existing) {
      all.push({
        id: `pm-client-${Date.now()}`,
        project_id: projectId,
        user_id: clientId,
        role: 'PROJECT_CLIENT',
        added_by: inviterUserId || 'demo-user-owner',
        created_at: new Date().toISOString(),
        profile: clientProfile,
      });
      saveMockMembers(all);
    }
  }

  return res;
}

export async function removeProjectMember(
  projectId: string,
  memberId: string,
  requesterUserId?: string,
  requesterRole?: OrgRole
): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockMembers();
    const target = all.find(
      (pm) => pm.project_id === projectId && (pm.id === memberId || pm.user_id === memberId || pm.profile?.id === memberId)
    );

    // Basecamp rule: Admins cannot remove the Account Owner from any project
    const isTargetOwner =
      target?.user_id === 'demo-user-owner' ||
      target?.profile?.id === 'demo-user-owner' ||
      target?.role === 'PROJECT_OWNER';

    const isSelf = requesterUserId && target && (target.user_id === requesterUserId || target.profile?.id === requesterUserId);

    if (isTargetOwner && requesterRole !== 'OWNER' && !isSelf) {
      return { error: new Error('Action forbidden: Administrators cannot remove the Account Owner from a project.') };
    }

    const filtered = all.filter(
      (pm) => !(pm.project_id === projectId && (pm.id === memberId || pm.user_id === memberId || pm.profile?.id === memberId))
    );
    saveMockMembers(filtered);
    return { error: null };
  }

  // Check in Supabase if configured
  if (requesterRole && requesterRole !== 'OWNER') {
    const { data: member } = await supabase
      .from('project_members')
      .select('user_id, role, project:projects(organization_id, organizations(owner_id))')
      .match({ project_id: projectId, user_id: memberId })
      .single();

    const isOwner = (member as any)?.project?.organizations?.owner_id === member?.user_id;
    if (isOwner && requesterUserId !== member?.user_id) {
      return { error: new Error('Action forbidden: Administrators cannot remove the Account Owner from a project.') };
    }
  }

  const { error } = await supabase.from('project_members').delete().match({ project_id: projectId, user_id: memberId });
  return { error: error as Error | null };
}

export async function updateProjectMemberRole(
  projectId: string,
  memberId: string,
  role: ProjectMemberRole
): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    MOCK_PROJECT_MEMBERS = MOCK_PROJECT_MEMBERS.map((pm) => (pm.id === memberId ? { ...pm, role } : pm));
    return { error: null };
  }

  const { error } = await supabase.from('project_members').update({ role }).eq('id', memberId);
  return { error: error as Error | null };
}

// Project Tool Settings
let MOCK_TOOL_SETTINGS: Record<string, any> = {};

export async function getProjectToolSettings(projectId: string) {
  try {
    const saved = localStorage.getItem(`proj_tools_${projectId}`);
    if (saved) {
      return JSON.parse(saved);
    }
  } catch (e) {}

  if (!isSupabaseConfigured) {
    if (MOCK_TOOL_SETTINGS[projectId]) {
      return MOCK_TOOL_SETTINGS[projectId];
    }
  }

  // Determine if it's one of the default pre-seeded demo projects
  const isDefaultDemoProject = ['proj-hq', 'proj-rmc', 'proj-hrms', 'proj-veggie', 'proj-bipl', 'proj-hihlo', 'proj-harsac'].includes(projectId);

  const defaultSettings = {
    project_id: projectId,
    message_board: isDefaultDemoProject,
    todos: isDefaultDemoProject,
    docs_files: isDefaultDemoProject,
    campfire: isDefaultDemoProject,
    schedule: isDefaultDemoProject,
    checkins: isDefaultDemoProject,
    kanban: isDefaultDemoProject,
    card_table: isDefaultDemoProject,
    doors: isDefaultDemoProject,
  };

  if (!isSupabaseConfigured) {
    MOCK_TOOL_SETTINGS[projectId] = defaultSettings;
    return defaultSettings;
  }

  const { data, error } = await supabase
    .from('project_tool_settings')
    .select('*')
    .eq('project_id', projectId)
    .single();

  if (error || !data) return defaultSettings;
  return data;
}

export async function updateProjectToolSettings(projectId: string, settings: Partial<{
  message_board: boolean;
  todos: boolean;
  docs_files: boolean;
  campfire: boolean;
  schedule: boolean;
  checkins: boolean;
  kanban: boolean;
  card_table: boolean;
  doors: boolean;
}>): Promise<{ error: Error | null }> {
  // Normalize card_table and kanban
  const normalized = { ...settings };
  if ('card_table' in normalized && !('kanban' in normalized)) {
    normalized.kanban = normalized.card_table;
  }
  if ('kanban' in normalized && !('card_table' in normalized)) {
    normalized.card_table = normalized.kanban;
  }

  const existing = MOCK_TOOL_SETTINGS[projectId] || {};
  const merged = {
    project_id: projectId,
    message_board: false,
    todos: false,
    docs_files: false,
    campfire: false,
    schedule: false,
    checkins: false,
    kanban: false,
    card_table: false,
    doors: false,
    ...existing,
    ...normalized,
  };

  MOCK_TOOL_SETTINGS[projectId] = merged;
  try {
    localStorage.setItem(`proj_tools_${projectId}`, JSON.stringify(merged));
  } catch (e) {}

  if (!isSupabaseConfigured) {
    return { error: null };
  }

  const { error } = await supabase
    .from('project_tool_settings')
    .upsert({ project_id: projectId, ...normalized }, { onConflict: 'project_id' });

  return { error: error as Error | null };
}

export async function duplicateProject(
  sourceProjectId: string,
  newName: string,
  organizationId: string,
  createdBy?: string
): Promise<{ data: Project | null; error: Error | null }> {
  const source = await getProjectById(sourceProjectId);
  if (!source) return { data: null, error: new Error('Source project not found') };

  const created = await createProject({
    organization_id: organizationId,
    name: newName,
    description: `Duplicated from ${source.name}. ${source.description || ''}`,
    cover_url: source.cover_url,
    status: 'ACTIVE',
    visibility: source.visibility,
    created_by: createdBy,
  });

  if (created.error || !created.data) return created;

  const newProjId = created.data.id;
  const toolSettings = await getProjectToolSettings(sourceProjectId);
  await updateProjectToolSettings(newProjId, toolSettings);

  return created;
}


