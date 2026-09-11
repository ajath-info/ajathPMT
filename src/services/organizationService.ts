import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { Organization, OrganizationMember, OrganizationInvitation, OrgRole, ActivityLogItem, Profile, ProjectMemberRole } from '../types';
import { canInviteRole } from '../lib/permissions';

// Mock initial database store for development fallback - Ajath is preserved as an existing customer tenant
export const DEFAULT_MOCK_ORGANIZATIONS: Organization[] = [
  {
    id: 'demo-org-acme',
    name: 'Ajath Infotech Pvt Ltd',
    slug: 'ajath-infotech',
    description: 'Company-wide announcements and stuff everyone needs to know.',
    logo_url: '',
    owner_id: 'demo-user-owner',
    created_at: new Date(Date.now() - 30 * 24 * 3600 * 1000).toISOString(),
    member_count: 10,
  },
  {
    id: 'demo-org-nexus',
    name: 'Nexus Digital Labs',
    slug: 'nexus-labs',
    description: 'Design system agency and client portal.',
    logo_url: '',
    owner_id: 'demo-user-owner',
    created_at: new Date(Date.now() - 15 * 24 * 3600 * 1000).toISOString(),
    member_count: 2,
  },
];

export function getSavedMockOrganizations(): Organization[] {
  try {
    const saved = localStorage.getItem('basecamp_organizations_list_v1');
    if (saved) {
      const parsed: Organization[] = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length > 0) {
        // Guarantee existing Ajath organization is preserved
        const missing = DEFAULT_MOCK_ORGANIZATIONS.filter((d) => !parsed.some((p) => p.id === d.id));
        if (missing.length > 0) {
          const merged = [...parsed, ...missing];
          localStorage.setItem('basecamp_organizations_list_v1', JSON.stringify(merged));
          return merged;
        }
        return parsed;
      }
    }
  } catch (e) {}
  return [...DEFAULT_MOCK_ORGANIZATIONS];
}

export function saveMockOrganizations(orgs: Organization[]) {
  MOCK_ORGANIZATIONS = orgs;
  try {
    localStorage.setItem('basecamp_organizations_list_v1', JSON.stringify(orgs));
  } catch (e) {}
}

let MOCK_ORGANIZATIONS: Organization[] = getSavedMockOrganizations();

let MOCK_MEMBERS: OrganizationMember[] = [
  {
    id: 'mem-1',
    organization_id: 'demo-org-acme',
    user_id: 'demo-user-owner',
    role: 'OWNER',
    joined_at: new Date(Date.now() - 30 * 24 * 3600 * 1000).toISOString(),
    profile: {
      id: 'demo-user-owner',
      email: 'claire.watson@ajath.com',
      full_name: 'Claire Watson',
      avatar_url: '',
      job_title: 'Account Owner',
      timezone: 'Asia/Kolkata (UTC+5:30)',
    },
  },
  {
    id: 'mem-2',
    organization_id: 'demo-org-acme',
    user_id: 'demo-user-admin',
    role: 'ADMIN',
    joined_at: new Date(Date.now() - 25 * 24 * 3600 * 1000).toISOString(),
    profile: {
      id: 'demo-user-admin',
      email: 'sarah.admin@worksphere.io',
      full_name: 'Sarah Jenkins',
      avatar_url: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
      job_title: 'Senior Engineering Lead',
      timezone: 'America/Los_Angeles (UTC-8)',
    },
  },
  {
    id: 'mem-3',
    organization_id: 'demo-org-acme',
    user_id: 'demo-user-member',
    role: 'MEMBER',
    joined_at: new Date(Date.now() - 20 * 24 * 3600 * 1000).toISOString(),
    profile: {
      id: 'demo-user-member',
      email: 'david.member@worksphere.io',
      full_name: 'David Chen',
      avatar_url: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
      job_title: 'UI/UX Designer',
      timezone: 'Europe/London (UTC+0)',
    },
  },
  {
    id: 'mem-ai',
    organization_id: 'demo-org-acme',
    user_id: 'user-ai',
    role: 'ADMIN',
    joined_at: new Date(Date.now() - 20 * 24 * 3600 * 1000).toISOString(),
    profile: {
      id: 'user-ai',
      email: 'hq@ajath.com',
      full_name: 'Ajath Infotech',
      avatar_url: '',
      job_title: 'HQ Team',
      timezone: 'Asia/Kolkata (UTC+5:30)',
    },
  },
  {
    id: 'mem-gk',
    organization_id: 'demo-org-acme',
    user_id: 'user-gk',
    role: 'MEMBER',
    joined_at: new Date(Date.now() - 18 * 24 * 3600 * 1000).toISOString(),
    profile: {
      id: 'user-gk',
      email: 'gaurav@ajath.com',
      full_name: 'Gaurav Kumar',
      avatar_url: '',
      job_title: 'Project Lead',
      timezone: 'Asia/Kolkata (UTC+5:30)',
    },
  },
  {
    id: 'mem-pk',
    organization_id: 'demo-org-acme',
    user_id: 'user-pk',
    role: 'MEMBER',
    joined_at: new Date(Date.now() - 15 * 24 * 3600 * 1000).toISOString(),
    profile: {
      id: 'user-pk',
      email: 'pankaj@ajath.com',
      full_name: 'Pankaj Kumar',
      avatar_url: '',
      job_title: 'Fullstack Dev',
      timezone: 'Asia/Kolkata (UTC+5:30)',
    },
  },
  {
    id: 'mem-rk',
    organization_id: 'demo-org-acme',
    user_id: 'user-rk',
    role: 'MEMBER',
    joined_at: new Date(Date.now() - 12 * 24 * 3600 * 1000).toISOString(),
    profile: {
      id: 'user-rk',
      email: 'rohit@ajath.com',
      full_name: 'Rohit Kumar',
      avatar_url: '',
      job_title: 'Backend Dev',
      timezone: 'Asia/Kolkata (UTC+5:30)',
    },
  },
  {
    id: 'mem-pt',
    organization_id: 'demo-org-acme',
    user_id: 'user-pt',
    role: 'MEMBER',
    joined_at: new Date(Date.now() - 10 * 24 * 3600 * 1000).toISOString(),
    profile: {
      id: 'user-pt',
      email: 'pradeep@ajath.com',
      full_name: 'Pradeep Tiwari',
      avatar_url: '',
      job_title: 'Mobile Engineer',
      timezone: 'Asia/Kolkata (UTC+5:30)',
    },
  },
  {
    id: 'mem-rs',
    organization_id: 'demo-org-acme',
    user_id: 'user-rs',
    role: 'MEMBER',
    joined_at: new Date(Date.now() - 8 * 24 * 3600 * 1000).toISOString(),
    profile: {
      id: 'user-rs',
      email: 'rasgo@ajath.com',
      full_name: 'Rasgo',
      avatar_url: '',
      job_title: 'Operations',
      timezone: 'Asia/Kolkata (UTC+5:30)',
    },
  },
  {
    id: 'mem-4',
    organization_id: 'demo-org-acme',
    user_id: 'demo-user-client',
    role: 'CLIENT',
    joined_at: new Date(Date.now() - 10 * 24 * 3600 * 1000).toISOString(),
    profile: {
      id: 'demo-user-client',
      email: 'edward.client@partner.com',
      full_name: 'Edward (Client Partner)',
      avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
      job_title: 'Client Partner Representative',
      timezone: 'Asia/Tokyo (UTC+9)',
    },
  },
];

function getActiveUserFullName(): string {
  try {
    const saved = localStorage.getItem('basecamp_user_profile');
    if (saved) {
      const p = JSON.parse(saved);
      if (p.full_name) return p.full_name;
    }
  } catch (e) {}
  return 'Claire Watson';
}

export function getSavedMockMembers(): OrganizationMember[] {
  const currentName = getActiveUserFullName();
  let list = MOCK_MEMBERS;
  try {
    const saved = localStorage.getItem('basecamp_org_members_v1');
    if (saved) {
      const parsed: OrganizationMember[] = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length > 0) {
        // Guarantee all default MOCK_MEMBERS are present in storage
        const missing = MOCK_MEMBERS.filter((dm) => !parsed.some((p) => p.user_id === dm.user_id));
        let combined = missing.length > 0 ? [...parsed, ...missing] : parsed;

        // Clean up any auto-injected sample members from user-created workspaces so fresh admin starts clean
        const cleaned = combined.filter((m) => {
          if (m.organization_id === 'demo-org-acme') return true;
          if (
            m.id.startsWith('mem-dev-') ||
            m.id.startsWith('mem-des-') ||
            m.id.startsWith('mem-client-') ||
            (m.id.startsWith('mem-admin-') && m.organization_id !== 'demo-org-acme')
          ) {
            return false;
          }
          return true;
        });

        if (cleaned.length !== combined.length) {
          list = cleaned;
          localStorage.setItem('basecamp_org_members_v1', JSON.stringify(list));
        } else {
          list = combined;
        }
      }
    } else {
      localStorage.setItem('basecamp_org_members_v1', JSON.stringify(MOCK_MEMBERS));
    }
  } catch (e) {}

  return list.map((m) => {
    if (m.user_id === 'demo-user-owner' || m.profile?.id === 'demo-user-owner') {
      const profile: Profile = {
        id: m.profile?.id || 'demo-user-owner',
        email: m.profile?.email || 'claire.watson@ajath.com',
        full_name: currentName,
        avatar_url: m.profile?.avatar_url || '',
        job_title: m.profile?.job_title || 'Account Owner',
        timezone: m.profile?.timezone || 'Asia/Kolkata (UTC+5:30)',
      };
      return {
        ...m,
        profile,
      };
    }
    if (!m.profile) {
      try {
        const savedUser = localStorage.getItem('basecamp_user_profile');
        if (savedUser) {
          const userP = JSON.parse(savedUser);
          if (userP && (userP.id === m.user_id || m.role === 'OWNER')) {
            return {
              ...m,
              profile: userP,
            };
          }
        }
      } catch (e) {}
    }
    return m;
  });
}

export function saveMockMembers(members: OrganizationMember[]) {
  MOCK_MEMBERS = members;
  try {
    localStorage.setItem('basecamp_org_members_v1', JSON.stringify(members));
  } catch (e) {}
}

let MOCK_INVITATIONS: OrganizationInvitation[] = [
  {
    id: 'inv-1',
    organization_id: 'demo-org-acme',
    email: 'marcus.dev@worksphere.io',
    role: 'MEMBER',
    invited_by: 'demo-user-owner',
    token: 'demo-invite-token-123',
    status: 'PENDING',
    personal_message: 'Hey Marcus, join our Ajath PMT team workspace!',
    expires_at: new Date(Date.now() + 7 * 24 * 3600 * 1000).toISOString(),
    created_at: new Date().toISOString(),
  },
];

export async function fetchUserOrganizations(userId: string): Promise<Organization[]> {
  if (!isSupabaseConfigured) {
    const allOrgs = getSavedMockOrganizations();
    const allMembers = getSavedMockMembers();
    const userOrgIds = new Set(
      allMembers
        .filter(
          (m) =>
            m.user_id === userId ||
            m.profile?.id === userId ||
            (userId === 'demo-user-admin' && (m.role === 'ADMIN' || m.profile?.email?.toLowerCase() === 'alex.admin@worksphere.io')) ||
            (userId === 'demo-user-member' && (m.role === 'MEMBER' || m.profile?.email?.toLowerCase() === 'marcus.dev@worksphere.io')) ||
            (userId === 'demo-user-client' && (m.role === 'CLIENT' || m.profile?.email?.toLowerCase() === 'edward.client@partner.com')) ||
            (m.profile?.email && typeof userId === 'string' && userId.includes('@') && m.profile.email.toLowerCase() === userId.toLowerCase())
        )
        .map((m) => m.organization_id)
    );

    // Also include organizations where user is an active project collaborator/client
    try {
      const { getSavedMockProjects, getSavedMockMembers: getProjMembers } = await import('./projectService');
      const allProjects = getSavedMockProjects();
      const allProjectMembers = getProjMembers();
      allProjectMembers
        .filter((pm: any) => pm.user_id === userId)
        .forEach((pm: any) => {
          const proj = allProjects.find((p) => p.id === pm.project_id);
          if (proj) userOrgIds.add(proj.organization_id);
        });
    } catch (e) {}

    // User sees organizations they are an active member of or own
    const userOrgs = allOrgs.filter((o) => userOrgIds.has(o.id) || o.owner_id === userId);

    // Basecamp Architecture Rule: One admin can only register and manage ONE company workspace.
    // If an admin created/belongs to multiple workspaces in storage, restrict strictly to their one registered workspace.
    if (userId !== 'demo-user-client' && userOrgs.length > 1) {
      let activeOrgId: string | null = null;
      try {
        activeOrgId = localStorage.getItem('worksphere_active_org_id');
      } catch (e) {}
      const singleRegisteredOrg = (activeOrgId ? userOrgs.find((o) => o.id === activeOrgId) : null) || userOrgs[0];
      if (singleRegisteredOrg) {
        try {
          localStorage.setItem('worksphere_active_org_id', singleRegisteredOrg.id);
        } catch (e) {}
        return [singleRegisteredOrg];
      }
    }

    return userOrgs;
  }

  const { data: orgMemberData } = await supabase
    .from('organization_members')
    .select('organization_id, organizations(*)')
    .eq('user_id', userId);

  // Also query project_members to include organizations where user is a client/collaborator
  const { data: projMemberData } = await supabase
    .from('project_members')
    .select('project_id, projects(organization_id, organizations(*))')
    .eq('user_id', userId);

  const orgMap = new Map<string, Organization>();
  if (orgMemberData) {
    orgMemberData.forEach((item: any) => {
      if (item.organizations?.id) orgMap.set(item.organizations.id, item.organizations);
    });
  }
  if (projMemberData) {
    projMemberData.forEach((item: any) => {
      const org = item.projects?.organizations;
      if (org?.id) orgMap.set(org.id, org);
    });
  }

  const orgList = Array.from(orgMap.values());
  if (userId !== 'demo-user-client' && orgList.length > 1) {
    let activeOrgId: string | null = null;
    try {
      activeOrgId = localStorage.getItem('worksphere_active_org_id');
    } catch (e) {}
    const singleRegisteredOrg = (activeOrgId ? orgList.find((o) => o.id === activeOrgId) : null) || orgList[0];
    return singleRegisteredOrg ? [singleRegisteredOrg] : [];
  }

  return orgList;
}

export async function createOrganization(
  name: string,
  slug: string,
  description: string,
  ownerId: string,
  logoUrl?: string
): Promise<{ data: Organization | null; error: Error | null }> {
  if (!isSupabaseConfigured) {
    const newOrg: Organization = {
      id: `org-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
      name,
      slug: slug || name.toLowerCase().replace(/[^a-z0-9]/g, '-'),
      description: description || '',
      logo_url: logoUrl || '',
      owner_id: ownerId,
      created_at: new Date().toISOString(),
      member_count: 1,
    };
    let currentOrgs = getSavedMockOrganizations();

    // One admin/owner can only register ONE company: replace any previous company owned by this owner
    // Also if creating as fresh owner/admin, replace any leftover non-demo custom workspace
    const replacedOrgIds = currentOrgs
      .filter((o) => (o.owner_id === ownerId || (typeof ownerId === 'string' && ownerId.startsWith('fresh-'))) && o.id !== 'demo-org-acme')
      .map((o) => o.id);

    currentOrgs = currentOrgs.filter((o) => o.owner_id !== ownerId);
    currentOrgs = currentOrgs.filter((o) => !replacedOrgIds.includes(o.id));
    currentOrgs.unshift(newOrg);
    saveMockOrganizations(currentOrgs);
    try {
      localStorage.setItem('worksphere_active_org_id', newOrg.id);
    } catch (e) {}

    // Clean up any projects in localStorage belonging to the replaced company workspaces
    // This ensures a newly registered company starts with completely clean 0 projects!
    try {
      const savedProjs = localStorage.getItem('basecamp_projects_list_v1');
      if (savedProjs) {
        let parsed = JSON.parse(savedProjs);
        if (Array.isArray(parsed)) {
          parsed = parsed.filter(
            (p: any) => !replacedOrgIds.includes(p.organization_id) && p.organization_id !== newOrg.id
          );
          localStorage.setItem('basecamp_projects_list_v1', JSON.stringify(parsed));
        }
      }
    } catch (e) {}

    const all = getSavedMockMembers();
    let currentProfile: Profile | undefined = undefined;
    try {
      const savedUser = localStorage.getItem('basecamp_user_profile');
      if (savedUser) {
        currentProfile = JSON.parse(savedUser);
      }
    } catch (e) {}

    all.push({
      id: `mem-${Date.now()}`,
      organization_id: newOrg.id,
      user_id: ownerId,
      role: 'OWNER',
      joined_at: new Date().toISOString(),
      profile: currentProfile || {
        id: ownerId,
        email: 'owner@company.com',
        full_name: getActiveUserFullName() || 'Account Owner',
        job_title: 'Account Owner',
      },
    });

    const samplePeople = getSampleOrganizationMembers(newOrg.id);
    // User Requirement: When logged in as fresh admin, there should not be any members pre-seeded. All setup done by admin.
    const isCleanAdminWorkspace = true;
    if (!isCleanAdminWorkspace) {
      all.push(...samplePeople);
      newOrg.member_count = 1 + samplePeople.length;
    } else {
      newOrg.member_count = 1;
    }
    saveMockMembers(all);
    saveMockOrganizations(currentOrgs);
    return { data: newOrg, error: null };
  }

  const { data, error } = await supabase
    .from('organizations')
    .insert([{ name, slug, description, logo_url: logoUrl, owner_id: ownerId }])
    .select()
    .single();

  if (error) return { data: null, error: error as Error };

  // Add owner to members table
  await supabase.from('organization_members').insert([
    { organization_id: data.id, user_id: ownerId, role: 'OWNER' }
  ]);

  return { data, error: null };
}

export async function updateOrganization(
  orgId: string,
  updates: Partial<Organization>
): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const currentOrgs = getSavedMockOrganizations().map((o) => (o.id === orgId ? { ...o, ...updates } : o));
    saveMockOrganizations(currentOrgs);
    return { error: null };
  }

  const { error } = await supabase
    .from('organizations')
    .update({ ...updates, updated_at: new Date().toISOString() })
    .eq('id', orgId);

  return { error: error as Error | null };
}

export async function deleteOrganization(orgId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const currentOrgs = getSavedMockOrganizations().filter((o) => o.id !== orgId);
    saveMockOrganizations(currentOrgs);
    return { error: null };
  }

  const { error } = await supabase.from('organizations').delete().eq('id', orgId);
  return { error: error as Error | null };
}

export function getSampleOrganizationMembers(orgId: string): OrganizationMember[] {
  return [
    {
      id: `mem-admin-${orgId}`,
      organization_id: orgId,
      user_id: `demo-user-admin`,
      role: 'ADMIN',
      joined_at: new Date(Date.now() - 14 * 24 * 3600 * 1000).toISOString(),
      profile: {
        id: `demo-user-admin`,
        email: 'alex.admin@worksphere.io',
        full_name: 'Alex Vance',
        avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
        job_title: 'Senior Operations & Tech Lead',
        timezone: 'America/New_York (UTC-5)',
      },
    },
    {
      id: `mem-dev-${orgId}`,
      organization_id: orgId,
      user_id: `demo-user-member`,
      role: 'MEMBER',
      joined_at: new Date(Date.now() - 10 * 24 * 3600 * 1000).toISOString(),
      profile: {
        id: `demo-user-member`,
        email: 'marcus.dev@worksphere.io',
        full_name: 'Marcus Rivera',
        avatar_url: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
        job_title: 'Full-Stack Developer',
        timezone: 'America/Los_Angeles (UTC-8)',
      },
    },
    {
      id: `mem-design-${orgId}`,
      organization_id: orgId,
      user_id: `user-design-${orgId}`,
      role: 'MEMBER',
      joined_at: new Date(Date.now() - 7 * 24 * 3600 * 1000).toISOString(),
      profile: {
        id: `user-design-${orgId}`,
        email: 'elena.design@worksphere.io',
        full_name: 'Elena Rostova',
        avatar_url: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
        job_title: 'Senior Product Designer',
        timezone: 'Europe/London (UTC+0)',
      },
    },
    {
      id: `mem-client-${orgId}`,
      organization_id: orgId,
      user_id: `demo-user-client`,
      role: 'CLIENT',
      joined_at: new Date(Date.now() - 5 * 24 * 3600 * 1000).toISOString(),
      profile: {
        id: `demo-user-client`,
        email: 'edward.client@partner.com',
        full_name: 'Edward Smith',
        avatar_url: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
        job_title: 'Client Partner Representative',
        timezone: 'Asia/Kolkata (UTC+5:30)',
      },
    },
  ];
}

export async function ensureOrganizationPeople(orgId: string): Promise<OrganizationMember[]> {
  if (!isSupabaseConfigured) {
    let all = getSavedMockMembers();
    let orgMembers = all.filter((m) => m.organization_id === orgId);

    // Fresh admin workspaces start with NO pre-seeded members. All setup done by admin.
    const isAutoSeedRequested = localStorage.getItem(`worksphere_seed_${orgId}`) === 'true';
    if (orgId && orgId !== 'demo-org-acme' && !isAutoSeedRequested) {
      return orgMembers;
    }

    const hasAdmin = orgMembers.some((m) => m.role === 'ADMIN');
    const hasMember = orgMembers.some((m) => m.role === 'MEMBER');
    const hasClient = orgMembers.some((m) => m.role === 'CLIENT');

      if (!hasAdmin || !hasMember || !hasClient) {
        const samplePeople = getSampleOrganizationMembers(orgId);
        const toAdd = samplePeople.filter((p) => {
          if (p.role === 'ADMIN' && hasAdmin) return false;
          if (p.role === 'CLIENT' && hasClient) return false;
          return !orgMembers.some(
            (existing) =>
              existing.user_id === p.user_id ||
              existing.profile?.email?.toLowerCase() === p.profile?.email?.toLowerCase()
          );
        });

        if (toAdd.length > 0) {
          all = [...all, ...toAdd];
          saveMockMembers(all);
          orgMembers = all.filter((m) => m.organization_id === orgId);
        }
      }
    return orgMembers;
  }

  const { data, error } = await supabase
    .from('organization_members')
    .select('*, profile:profiles(*)')
    .eq('organization_id', orgId);

  if (error || !data) return [];
  return data as OrganizationMember[];
}

export async function addOrganizationMember(
  orgId: string,
  member: {
    fullName: string;
    email: string;
    role: OrgRole;
    jobTitle?: string;
    avatarUrl?: string;
    timezone?: string;
  }
): Promise<{ data: OrganizationMember | null; error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockMembers();
    const cleanEmail = member.email.trim().toLowerCase();
    const existing = all.find((m) => m.organization_id === orgId && m.profile?.email?.toLowerCase() === cleanEmail);
    if (existing) {
      return { data: existing, error: null };
    }

    const newUserId = `user-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`;
    const newMember: OrganizationMember = {
      id: `mem-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
      organization_id: orgId,
      user_id: newUserId,
      role: member.role,
      joined_at: new Date().toISOString(),
      profile: {
        id: newUserId,
        email: cleanEmail,
        full_name: member.fullName.trim(),
        avatar_url: member.avatarUrl || '',
        job_title:
          member.jobTitle ||
          (member.role === 'ADMIN'
            ? 'Administrator'
            : member.role === 'CLIENT'
            ? 'Client Partner'
            : 'Team Member'),
        timezone: member.timezone || 'Asia/Kolkata (UTC+5:30)',
      },
    };

    all.push(newMember);
    saveMockMembers(all);
    return { data: newMember, error: null };
  }

  const { data: profileData, error: profileErr } = await supabase
    .from('profiles')
    .insert([
      {
        email: member.email.trim().toLowerCase(),
        full_name: member.fullName.trim(),
        job_title: member.jobTitle,
        avatar_url: member.avatarUrl,
      },
    ])
    .select()
    .single();

  if (profileErr || !profileData) {
    return { data: null, error: profileErr as Error };
  }

  const { data: memData, error: memErr } = await supabase
    .from('organization_members')
    .insert([
      {
        organization_id: orgId,
        user_id: profileData.id,
        role: member.role,
      },
    ])
    .select('*, profile:profiles(*)')
    .single();

  if (memErr) return { data: null, error: memErr as Error };
  return { data: memData as OrganizationMember, error: null };
}

export async function fetchOrganizationMembers(orgId: string): Promise<OrganizationMember[]> {
  if (!isSupabaseConfigured) {
    let all = getSavedMockMembers();
    let orgMembers = all.filter((m) => m.organization_id === orgId);

    // Fresh admin workspaces start with NO pre-seeded members. All setup done by admin.
    const isAutoSeedRequested = localStorage.getItem(`worksphere_seed_${orgId}`) === 'true';
    if (orgId && orgId !== 'demo-org-acme' && !isAutoSeedRequested) {
      return orgMembers;
    }

    if (orgId && orgId !== 'demo-org-acme') {
      const hasAdmin = orgMembers.some((m) => m.role === 'ADMIN');
      const hasMember = orgMembers.some((m) => m.role === 'MEMBER');
      const hasClient = orgMembers.some((m) => m.role === 'CLIENT');

      if (!hasAdmin || !hasMember || !hasClient) {
        return ensureOrganizationPeople(orgId);
      }
    }

    return orgMembers;
  }

  const { data, error } = await supabase
    .from('organization_members')
    .select('*, profile:profiles(*)')
    .eq('organization_id', orgId);

  if (error || !data) return [];
  return data as OrganizationMember[];
}

export async function updateMemberRole(memberId: string, role: OrgRole): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockMembers().map((m) =>
      m.id === memberId || m.user_id === memberId ? { ...m, role } : m
    );
    saveMockMembers(all);
    return { error: null };
  }

  const { error } = await supabase
    .from('organization_members')
    .update({ role })
    .eq('id', memberId);

  return { error: error as Error | null };
}

export async function transferAccountOwnership(
  orgId: string,
  newOwnerUserId: string,
  currentOwnerUserId: string
): Promise<{ error: Error | null }> {
  if (isSupabaseConfigured) {
    try {
      await supabase.from('organizations').update({ owner_id: newOwnerUserId }).eq('id', orgId);
      await supabase.from('organization_members').update({ role: 'ADMIN' }).eq('organization_id', orgId).eq('user_id', currentOwnerUserId);
      await supabase.from('organization_members').update({ role: 'OWNER' }).eq('organization_id', orgId).eq('user_id', newOwnerUserId);
      await logActivity(orgId, currentOwnerUserId, 'Transferred Account Ownership', `Transferred account ownership to user ${newOwnerUserId}`);
      return { error: null };
    } catch (e) {
      return { error: e as Error };
    }
  }

  const all = getSavedMockMembers().map((m) => {
    if (m.user_id === newOwnerUserId || m.id === newOwnerUserId) {
      return { ...m, role: 'OWNER' as OrgRole };
    }
    if (m.user_id === currentOwnerUserId || m.id === currentOwnerUserId) {
      return { ...m, role: 'ADMIN' as OrgRole };
    }
    return m;
  });
  saveMockMembers(all);
  const org = MOCK_ORGANIZATIONS.find((o) => o.id === orgId);
  if (org) {
    org.owner_id = newOwnerUserId;
  }
  await logActivity(orgId, currentOwnerUserId, 'Transferred Account Ownership', `Transferred account ownership to user ${newOwnerUserId}`);
  return { error: null };
}

export async function removeMember(memberId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockMembers().filter((m) => m.id !== memberId && m.user_id !== memberId);
    saveMockMembers(all);
    return { error: null };
  }

  const { error } = await supabase.from('organization_members').delete().eq('id', memberId);
  return { error: error as Error | null };
}

export async function createInvitation(
  orgId: string,
  email: string,
  role: OrgRole,
  invitedBy: string,
  personalMessage?: string,
  inviterRole?: OrgRole,
  options?: {
    teamId?: string;
    projectId?: string;
    projectRole?: ProjectMemberRole;
  }
): Promise<{ data: OrganizationInvitation | null; error: Error | null }> {
  // Prevent privilege escalation
  if (inviterRole && !canInviteRole(inviterRole, role)) {
    return {
      data: null,
      error: new Error(`Unauthorized: Role '${inviterRole}' cannot invite users with role '${role}'. Privilege escalation prohibited.`),
    };
  }

  const cleanEmail = email.trim().toLowerCase();

  // Prevent duplicate active pending invitations in the same organization
  if (!isSupabaseConfigured) {
    const existing = MOCK_INVITATIONS.find(
      (i) => i.organization_id === orgId && i.email.toLowerCase() === cleanEmail && i.status === 'PENDING'
    );
    if (existing) {
      return {
        data: null,
        error: new Error(`An active pending invitation for '${cleanEmail}' already exists in this organization.`),
      };
    }
  } else {
    const { data: existing } = await supabase
      .from('organization_invitations')
      .select('id')
      .eq('organization_id', orgId)
      .eq('email', cleanEmail)
      .eq('status', 'PENDING')
      .maybeSingle();

    if (existing) {
      return {
        data: null,
        error: new Error(`An active pending invitation for '${cleanEmail}' already exists in this organization.`),
      };
    }
  }

  const token = `inv-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;

  if (!isSupabaseConfigured) {
    const newInv: OrganizationInvitation = {
      id: `inv-${Date.now()}`,
      organization_id: orgId,
      email: cleanEmail,
      role,
      invited_by: invitedBy,
      token,
      status: 'PENDING',
      personal_message: personalMessage,
      team_id: options?.teamId,
      project_id: options?.projectId,
      project_role: options?.projectRole,
      expires_at: new Date(Date.now() + 7 * 24 * 3600 * 1000).toISOString(),
      created_at: new Date().toISOString(),
    };
    MOCK_INVITATIONS.unshift(newInv);
    return { data: newInv, error: null };
  }

  const { data, error } = await supabase
    .from('organization_invitations')
    .insert([
      {
        organization_id: orgId,
        email: email.trim().toLowerCase(),
        role,
        invited_by: invitedBy,
        token,
        personal_message: personalMessage,
        team_id: options?.teamId || null,
        project_id: options?.projectId || null,
        project_role: options?.projectRole || 'PROJECT_MEMBER',
      },
    ])
    .select()
    .single();

  return { data: data as OrganizationInvitation, error: error as Error | null };
}

export async function fetchPendingInvitations(orgId: string, requesterRole?: OrgRole): Promise<OrganizationInvitation[]> {
  if (requesterRole === 'CLIENT') {
    return []; // Clients never see pending invitations
  }

  let invitations: OrganizationInvitation[] = [];
  if (!isSupabaseConfigured) {
    invitations = MOCK_INVITATIONS.filter((i) => i.organization_id === orgId && i.status === 'PENDING');
  } else {
    const { data, error } = await supabase
      .from('organization_invitations')
      .select('*')
      .eq('organization_id', orgId)
      .eq('status', 'PENDING');

    if (!error && data) {
      invitations = data as OrganizationInvitation[];
    }
  }

  // Redact raw tokens for non-administrators to prevent token interception
  if (requesterRole && !['OWNER', 'ADMIN'].includes(requesterRole)) {
    return invitations.map((i) => ({ ...i, token: '••••••••••••••••' }));
  }

  return invitations;
}

export async function fetchUserPendingInvitations(email: string): Promise<OrganizationInvitation[]> {
  const cleanEmail = email.trim().toLowerCase();
  if (!cleanEmail) return [];

  if (!isSupabaseConfigured) {
    const allOrgs = getSavedMockOrganizations();
    return MOCK_INVITATIONS.filter(
      (i) => i.email.toLowerCase() === cleanEmail && i.status === 'PENDING'
    ).map((inv) => {
      const org = allOrgs.find((o) => o.id === inv.organization_id);
      return { ...inv, organization: org };
    });
  }

  const { data, error } = await supabase
    .from('organization_invitations')
    .select('*, organization:organizations(*)')
    .eq('email', cleanEmail)
    .eq('status', 'PENDING');

  if (error || !data) return [];
  return data as OrganizationInvitation[];
}

export async function cancelInvitation(inviteId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    MOCK_INVITATIONS = MOCK_INVITATIONS.filter((i) => i.id !== inviteId);
    return { error: null };
  }

  const { error } = await supabase.from('organization_invitations').delete().eq('id', inviteId);
  return { error: error as Error | null };
}

export async function declineInvitation(token: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const inv = MOCK_INVITATIONS.find((i) => i.token === token);
    if (inv) {
      inv.status = 'DECLINED';
    }
    return { error: null };
  }

  const { error } = await supabase
    .from('organization_invitations')
    .update({ status: 'DECLINED', updated_at: new Date().toISOString() })
    .eq('token', token);

  return { error: error as Error | null };
}

export async function fetchInvitationByToken(token: string): Promise<OrganizationInvitation | null> {
  if (!isSupabaseConfigured) {
    const inv = MOCK_INVITATIONS.find((i) => i.token === token);
    if (inv) {
      const allOrgs = getSavedMockOrganizations();
      const org = allOrgs.find((o) => o.id === inv.organization_id) || null;
      return { ...inv, organization: org || undefined };
    }
    return null;
  }

  const { data, error } = await supabase
    .from('organization_invitations')
    .select('*, organization:organizations(*)')
    .eq('token', token)
    .single();

  if (error || !data) return null;
  return data as OrganizationInvitation;
}

export async function acceptInvitation(token: string, userId: string): Promise<{ error: Error | null; organizationId?: string }> {
  if (!isSupabaseConfigured) {
    const inv = MOCK_INVITATIONS.find((i) => i.token === token);
    if (!inv) return { error: new Error('Invitation not found or invalid token.') };
    if (inv.status === 'ACCEPTED') return { error: new Error('Invitation has already been accepted.') };
    if (inv.status === 'EXPIRED' || (inv.expires_at && new Date(inv.expires_at) < new Date())) {
      return { error: new Error('Invitation has expired.') };
    }

    inv.status = 'ACCEPTED';
    inv.accepted_at = new Date().toISOString();
    inv.accepted_by = userId;

    // Client isolation: Clients NEVER become internal organization employees
    if (inv.role !== 'CLIENT') {
      const all = getSavedMockMembers();
      if (!all.some(m => m.organization_id === inv.organization_id && (m.user_id === userId || m.profile?.id === userId))) {
        all.push({
          id: `mem-${Date.now()}`,
          organization_id: inv.organization_id,
          user_id: userId,
          role: inv.role,
          joined_at: new Date().toISOString(),
        });
        saveMockMembers(all);
      }

      if (inv.team_id) {
        try {
          const { addTeamMember } = await import('./teamService');
          await addTeamMember(inv.team_id, userId);
        } catch (e) {}
      }
    }

    if (inv.project_id) {
      try {
        const { addProjectMember } = await import('./projectService');
        const pRole = inv.project_role || (inv.role === 'CLIENT' ? 'PROJECT_CLIENT' : 'PROJECT_MEMBER');
        await addProjectMember(inv.project_id, userId, pRole, inv.invited_by);
      } catch (e) {}
    }

    return { error: null, organizationId: inv.organization_id };
  }

  // Supabase Mode
  const { data: inv, error: fetchErr } = await supabase
    .from('organization_invitations')
    .select('*')
    .eq('token', token)
    .single();

  if (fetchErr || !inv) return { error: new Error('Invalid or expired invitation token.') };
  if (inv.status === 'ACCEPTED') return { error: new Error('Invitation has already been accepted.') };
  if (inv.status === 'EXPIRED' || (inv.expires_at && new Date(inv.expires_at) < new Date())) {
    return { error: new Error('Invitation has expired.') };
  }

  await supabase.from('organization_invitations').update({
    status: 'ACCEPTED',
    accepted_at: new Date().toISOString(),
    accepted_by: userId,
  }).eq('token', token);

  // Client isolation: Clients NEVER become internal organization employees
  if (inv.role !== 'CLIENT') {
    await supabase.from('organization_members').upsert([
      { organization_id: inv.organization_id, user_id: userId, role: inv.role }
    ], { onConflict: 'organization_id,user_id' });

    if (inv.team_id) {
      await supabase.from('team_members').upsert([
        { team_id: inv.team_id, user_id: userId }
      ], { onConflict: 'team_id,user_id' });
    }
  }

  if (inv.project_id) {
    const pRole = inv.project_role || (inv.role === 'CLIENT' ? 'PROJECT_CLIENT' : 'PROJECT_MEMBER');
    await supabase.from('project_members').upsert([
      { project_id: inv.project_id, user_id: userId, role: pRole, added_by: inv.invited_by }
    ], { onConflict: 'project_id,user_id' });
  }

  return { error: null, organizationId: inv.organization_id };
}

let MOCK_ACTIVITY_LOGS: ActivityLogItem[] = [
  {
    id: 'act-1',
    organization_id: 'demo-org-acme',
    project_id: 'proj-hq',
    user_id: 'demo-user-owner',
    action: 'posted announcement',
    item_title: 'MOM 08/09/2026',
    created_at: new Date(Date.now() - 2 * 3600 * 1000).toISOString(),
    user: {
      id: 'demo-user-owner',
      email: 'claire.client@partner.com',
      full_name: 'Claire Watson',
      avatar_url: '',
    },
  },
  {
    id: 'act-2',
    organization_id: 'demo-org-acme',
    project_id: 'proj-rmc',
    user_id: 'user-e',
    action: 'updated to-do checklist in',
    item_title: 'Ride My Cars (Edward)',
    created_at: new Date(Date.now() - 4 * 3600 * 1000).toISOString(),
    user: {
      id: 'user-e',
      email: 'edward@ndchomes.com',
      full_name: 'Edward',
      avatar_url: '',
    },
  },
  {
    id: 'act-3',
    organization_id: 'demo-org-acme',
    project_id: 'proj-bipl',
    user_id: 'demo-user-owner',
    action: 'verified build milestone for',
    item_title: 'BIPL APP',
    created_at: new Date(Date.now() - 12 * 3600 * 1000).toISOString(),
    user: {
      id: 'demo-user-owner',
      email: 'claire.client@partner.com',
      full_name: 'Claire Watson',
      avatar_url: '',
    },
  },
];

export async function logActivity(
  orgId: string,
  userId: string,
  action: string,
  itemTitle?: string,
  projectId?: string
): Promise<void> {
  let resolvedOrgId = orgId;
  if (!resolvedOrgId && projectId) {
    try {
      const { getProjectById } = await import('./projectService');
      const proj = await getProjectById(projectId);
      if (proj?.organization_id) resolvedOrgId = proj.organization_id;
    } catch (e) {}
  }
  if (!resolvedOrgId) return;

  if (!isSupabaseConfigured) {
    MOCK_ACTIVITY_LOGS.unshift({
      id: `act-${Date.now()}`,
      organization_id: resolvedOrgId,
      project_id: projectId,
      user_id: userId,
      action,
      item_title: itemTitle,
      created_at: new Date().toISOString(),
      user: {
        id: userId,
        email: 'user@workspace.io',
        full_name: userId.includes('sarah') ? 'Sarah Jenkins' : 'Alex Vance',
      },
    });
    return;
  }
  await supabase.from('activity_logs').insert([
    { organization_id: resolvedOrgId, user_id: userId, action, item_title: itemTitle, project_id: projectId }
  ]);
}

export async function getOrganizationActivityLogs(orgId: string): Promise<ActivityLogItem[]> {
  if (!isSupabaseConfigured) return MOCK_ACTIVITY_LOGS.filter((l) => l.organization_id === orgId);
  const { data, error } = await supabase
    .from('activity_logs')
    .select('*, profile:profiles(*)')
    .eq('organization_id', orgId)
    .order('created_at', { ascending: false })
    .limit(50);

  if (error || !data) return MOCK_ACTIVITY_LOGS.filter((l) => l.organization_id === orgId);
  return data as ActivityLogItem[];
}

export async function getProjectActivityLogs(projectId: string): Promise<ActivityLogItem[]> {
  if (!isSupabaseConfigured) return MOCK_ACTIVITY_LOGS.filter((l) => l.project_id === projectId);
  const { data, error } = await supabase
    .from('activity_logs')
    .select('*, profile:profiles(*)')
    .eq('project_id', projectId)
    .order('created_at', { ascending: false })
    .limit(50);

  if (error || !data) return MOCK_ACTIVITY_LOGS.filter((l) => l.project_id === projectId);
  return data as ActivityLogItem[];
}
