import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { Team, TeamMember, Profile } from '../types';
import { fetchOrganizationMembers } from './organizationService';

let MOCK_TEAMS: Team[] = [
  {
    id: 'team-1',
    organization_id: 'demo-org-acme',
    name: 'Frontend Engineering',
    description: 'React, TypeScript, and UI system operations.',
    avatar_url: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=150&auto=format&fit=crop&q=80',
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 20 * 24 * 3600 * 1000).toISOString(),
    member_count: 3,
  },
  {
    id: 'team-2',
    organization_id: 'demo-org-acme',
    name: 'Product Design & UX',
    description: 'User research, design system, and prototyping.',
    avatar_url: 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?w=150&auto=format&fit=crop&q=80',
    created_by: 'demo-user-admin',
    created_at: new Date(Date.now() - 15 * 24 * 3600 * 1000).toISOString(),
    member_count: 2,
  },
  {
    id: 'team-3',
    organization_id: 'demo-org-acme',
    name: 'Infrastructure & Security',
    description: 'Database schema, Supabase RLS, and DevOps.',
    avatar_url: 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=150&auto=format&fit=crop&q=80',
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 10 * 24 * 3600 * 1000).toISOString(),
    member_count: 2,
  },
];

let MOCK_TEAM_MEMBERS: TeamMember[] = [
  {
    id: 'tm-1',
    team_id: 'team-1',
    user_id: 'demo-user-owner',
    created_at: new Date().toISOString(),
    profile: {
      id: 'demo-user-owner',
      email: 'alex.owner@worksphere.io',
      full_name: 'Alex Vance',
      avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
      job_title: 'Product Director',
    },
  },
  {
    id: 'tm-2',
    team_id: 'team-1',
    user_id: 'demo-user-admin',
    created_at: new Date().toISOString(),
    profile: {
      id: 'demo-user-admin',
      email: 'sarah.admin@worksphere.io',
      full_name: 'Sarah Jenkins',
      avatar_url: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
      job_title: 'Engineering Lead',
    },
  },
  {
    id: 'tm-3',
    team_id: 'team-1',
    user_id: 'demo-user-member',
    created_at: new Date().toISOString(),
    profile: {
      id: 'demo-user-member',
      email: 'david.member@worksphere.io',
      full_name: 'David Chen',
      avatar_url: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
      job_title: 'UI/UX Designer',
    },
  },
];

export function getSavedMockTeams(): Team[] {
  try {
    const saved = localStorage.getItem('basecamp_teams_v1');
    if (saved) {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length > 0) return parsed;
    }
  } catch (e) {}
  return MOCK_TEAMS;
}

export function saveMockTeams(teams: Team[]) {
  MOCK_TEAMS = teams;
  try {
    localStorage.setItem('basecamp_teams_v1', JSON.stringify(teams));
  } catch (e) {}
}

export function getSavedMockTeamMembers(): TeamMember[] {
  try {
    const saved = localStorage.getItem('basecamp_team_members_v1');
    if (saved) {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed)) return parsed;
    }
  } catch (e) {}
  return MOCK_TEAM_MEMBERS;
}

export function saveMockTeamMembers(members: TeamMember[]) {
  MOCK_TEAM_MEMBERS = members;
  try {
    localStorage.setItem('basecamp_team_members_v1', JSON.stringify(members));
  } catch (e) {}
}

export async function fetchOrganizationTeams(orgId: string): Promise<Team[]> {
  if (!orgId) return [];

  if (!isSupabaseConfigured) {
    return getSavedMockTeams().filter((t) => t.organization_id === orgId);
  }

  const { data, error } = await supabase
    .from('teams')
    .select('*')
    .eq('organization_id', orgId);

  if (error || !data) return getSavedMockTeams().filter((t) => t.organization_id === orgId);
  return data as Team[];
}

export async function fetchTeamById(teamId: string): Promise<Team | null> {
  if (!isSupabaseConfigured) {
    return getSavedMockTeams().find((t) => t.id === teamId) || null;
  }

  const { data, error } = await supabase
    .from('teams')
    .select('*')
    .eq('id', teamId)
    .single();

  if (error || !data) return getSavedMockTeams().find((t) => t.id === teamId) || null;
  return data as Team;
}

export async function createTeam(
  orgId: string,
  name: string,
  description: string,
  avatarUrl?: string,
  createdBy?: string,
  initialMemberUserIds: string[] = []
): Promise<{ data: Team | null; error: Error | null }> {
  if (!orgId) {
    return { data: null, error: new Error('Organization ID is required to create a team.') };
  }

  if (!isSupabaseConfigured) {
    const newTeam: Team = {
      id: `team-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
      organization_id: orgId,
      name,
      description,
      avatar_url: avatarUrl || 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=150&auto=format&fit=crop&q=80',
      created_by: createdBy,
      created_at: new Date().toISOString(),
      member_count: Math.max(1, (createdBy ? 1 : 0) + initialMemberUserIds.length),
    };
    const allTeams = getSavedMockTeams();
    allTeams.unshift(newTeam);
    saveMockTeams(allTeams);
    
    const allTeamMembers = getSavedMockTeamMembers();
    const userIdsToAdd = new Set<string>();
    if (createdBy) userIdsToAdd.add(createdBy);
    initialMemberUserIds.forEach((uid) => userIdsToAdd.add(uid));

    for (const uid of Array.from(userIdsToAdd)) {
      allTeamMembers.push({
        id: `tm-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
        team_id: newTeam.id,
        user_id: uid,
        created_at: new Date().toISOString(),
      });
    }
    saveMockTeamMembers(allTeamMembers);

    return { data: newTeam, error: null };
  }

  const { data, error } = await supabase
    .from('teams')
    .insert([
      {
        organization_id: orgId,
        name,
        description,
        avatar_url: avatarUrl,
        created_by: createdBy,
      },
    ])
    .select()
    .single();

  if (error) return { data: null, error: error as Error };

  const userIdsToAdd = new Set<string>();
  if (createdBy) userIdsToAdd.add(createdBy);
  initialMemberUserIds.forEach((uid) => userIdsToAdd.add(uid));

  if (userIdsToAdd.size > 0) {
    const inserts = Array.from(userIdsToAdd).map((uid) => ({
      team_id: data.id,
      user_id: uid,
    }));
    await supabase.from('team_members').insert(inserts);
  }

  return { data: data as Team, error: null };
}

export async function updateTeam(teamId: string, updates: Partial<Team>): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockTeams().map((t) => (t.id === teamId ? { ...t, ...updates } : t));
    saveMockTeams(all);
    return { error: null };
  }

  const { error } = await supabase
    .from('teams')
    .update({ ...updates, updated_at: new Date().toISOString() })
    .eq('id', teamId);

  return { error: error as Error | null };
}

export async function deleteTeam(teamId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockTeams().filter((t) => t.id !== teamId);
    saveMockTeams(all);
    return { error: null };
  }

  const { error } = await supabase.from('teams').delete().eq('id', teamId);
  return { error: error as Error | null };
}

export async function fetchTeamMembers(teamId: string): Promise<TeamMember[]> {
  if (!isSupabaseConfigured) {
    return getSavedMockTeamMembers().filter((tm) => tm.team_id === teamId);
  }

  const { data, error } = await supabase
    .from('team_members')
    .select('*, profile:profiles(*)')
    .eq('team_id', teamId);

  if (error || !data) return getSavedMockTeamMembers().filter((tm) => tm.team_id === teamId);
  return data as TeamMember[];
}

export async function addTeamMember(teamId: string, userId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const team = getSavedMockTeams().find((t) => t.id === teamId);
    if (!team) {
      return { error: new Error('Team not found') };
    }
    const orgMembers = await fetchOrganizationMembers(team.organization_id);
    const isMember = orgMembers.some((m) => m.user_id === userId);
    if (!isMember) {
      return { error: new Error('Cannot add a user to a team who is not a member of the organization.') };
    }

    const allMembers = getSavedMockTeamMembers();
    if (!allMembers.some((tm) => tm.team_id === teamId && tm.user_id === userId)) {
      allMembers.push({
        id: `tm-${Date.now()}`,
        team_id: teamId,
        user_id: userId,
        created_at: new Date().toISOString(),
      });
      saveMockTeamMembers(allMembers);
    }
    return { error: null };
  }

  const { data: teamData, error: teamErr } = await supabase
    .from('teams')
    .select('organization_id')
    .eq('id', teamId)
    .single();

  if (teamErr || !teamData) {
    return { error: teamErr ? (teamErr as Error) : new Error('Team not found') };
  }

  const { data: orgMember, error: memErr } = await supabase
    .from('organization_members')
    .select('id')
    .eq('organization_id', teamData.organization_id)
    .eq('user_id', userId)
    .maybeSingle();

  if (memErr) {
    return { error: memErr as Error };
  }
  if (!orgMember) {
    return { error: new Error('Cannot add a user to a team who is not a member of the organization.') };
  }

  const { error } = await supabase.from('team_members').insert([{ team_id: teamId, user_id: userId }]);
  return { error: error as Error | null };
}

export async function removeTeamMember(teamId: string, userId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    const all = getSavedMockTeamMembers().filter((tm) => !(tm.team_id === teamId && tm.user_id === userId));
    saveMockTeamMembers(all);
    return { error: null };
  }

  const { error } = await supabase
    .from('team_members')
    .delete()
    .eq('team_id', teamId)
    .eq('user_id', userId);

  return { error: error as Error | null };
}
