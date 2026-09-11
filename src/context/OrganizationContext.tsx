import React, { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { useAuth } from './AuthContext';
import { Organization, OrganizationMember, Team, OrganizationInvitation, OrgRole } from '../types';
import {
  fetchUserOrganizations,
  createOrganization,
  updateOrganization,
  deleteOrganization,
  fetchOrganizationMembers,
  fetchPendingInvitations,
  fetchUserPendingInvitations,
  acceptInvitation,
  declineInvitation,
  logActivity,
  getSavedMockOrganizations,
} from '../services/organizationService';
import { fetchOrganizationTeams } from '../services/teamService';
import { supabase, isSupabaseConfigured } from '../lib/supabase';

interface OrganizationContextType {
  currentOrganization: Organization | null;
  currentRole: OrgRole;
  organizations: Organization[];
  members: OrganizationMember[];
  teams: Team[];
  pendingInvitations: OrganizationInvitation[];
  userPendingInvitations: OrganizationInvitation[];
  loading: boolean;
  switchOrganization: (orgId: string) => void;
  createNewOrganization: (name: string, slug?: string, description?: string, logoUrl?: string) => Promise<{ data: Organization | null; error: Error | null }>;
  updateCurrentOrganization: (updates: Partial<Organization>) => Promise<{ error: Error | null }>;
  deleteCurrentOrganization: () => Promise<{ error: Error | null }>;
  refreshOrganizationData: () => Promise<void>;
  acceptUserInvitation: (token: string) => Promise<{ error: Error | null; organizationId?: string }>;
  declineUserInvitation: (token: string) => Promise<{ error: Error | null }>;
}

const OrganizationContext = createContext<OrganizationContextType | undefined>(undefined);

export function OrganizationProvider({ children }: { children: React.ReactNode }) {
  const { profile, setUserRole } = useAuth();
  const [organizations, setOrganizations] = useState<Organization[]>([]);
  const [currentOrganization, setCurrentOrganization] = useState<Organization | null>(null);
  const [currentRole, setCurrentRole] = useState<OrgRole>('OWNER');
  const [members, setMembers] = useState<OrganizationMember[]>([]);
  const [teams, setTeams] = useState<Team[]>([]);
  const [pendingInvitations, setPendingInvitations] = useState<OrganizationInvitation[]>([]);
  const [userPendingInvitations, setUserPendingInvitations] = useState<OrganizationInvitation[]>([]);
  const [loading, setLoading] = useState<boolean>(true);

  // Load user organizations
  const loadUserOrganizations = useCallback(async () => {
    if (!profile) {
      setOrganizations([]);
      setCurrentOrganization(null);
      setLoading(false);
      return;
    }

    try {
      const orgList = await fetchUserOrganizations(profile.id);

      // Basecamp Single Company Architecture: One admin can register and manage ONE company workspace
      let activeOrgList = orgList;
      if (profile.id !== 'demo-user-client' && (profile as any).role !== 'CLIENT' && orgList.length > 1) {
        const savedOrgId = localStorage.getItem('worksphere_active_org_id');
        const singleOrg = orgList.find((o) => o.id === savedOrgId) || orgList[0];
        activeOrgList = singleOrg ? [singleOrg] : [];
      }

      setOrganizations(activeOrgList);

      const savedOrgId = localStorage.getItem('worksphere_active_org_id');
      const foundOrg = activeOrgList.find((o) => o.id === savedOrgId) || activeOrgList[0] || null;

      setCurrentOrganization(foundOrg);
      if (foundOrg) {
        localStorage.setItem('worksphere_active_org_id', foundOrg.id);
      } else {
        localStorage.removeItem('worksphere_active_org_id');
      }

      if (profile.email) {
        const userInvites = await fetchUserPendingInvitations(profile.email);
        setUserPendingInvitations(userInvites);
      }
    } catch (err) {
      console.error('Error loading organizations:', err);
    } finally {
      setLoading(false);
    }
  }, [profile]);

  // Load details for current active organization
  const loadActiveOrgDetails = useCallback(async () => {
    if (!currentOrganization) return;

    try {
      const [mList, tList, iList] = await Promise.all([
        fetchOrganizationMembers(currentOrganization.id),
        fetchOrganizationTeams(currentOrganization.id),
        fetchPendingInvitations(currentOrganization.id),
      ]);

      setMembers(mList);
      setTeams(tList);
      setPendingInvitations(iList);

      let role: OrgRole = 'CLIENT';
      if (profile) {
        if (currentOrganization.owner_id === profile.id) {
          role = 'OWNER';
        } else {
          const found = mList.find(
            (m) =>
              m.user_id === profile.id ||
              m.profile?.id === profile.id ||
              (m.profile?.email && profile.email && m.profile.email.toLowerCase() === profile.email.toLowerCase()) ||
              (profile.id === 'demo-user-admin' && (m.role === 'ADMIN' || m.profile?.email?.toLowerCase() === 'alex.admin@worksphere.io')) ||
              (profile.id === 'demo-user-member' && (m.role === 'MEMBER' || m.profile?.email?.toLowerCase() === 'marcus.dev@worksphere.io')) ||
              (profile.id === 'demo-user-client' && (m.role === 'CLIENT' || m.profile?.email?.toLowerCase() === 'edward.client@partner.com'))
          );
          if (found) {
            role = found.role;
          } else {
            role = 'CLIENT';
          }
        }
        setCurrentRole(role);
        if (setUserRole) {
          setUserRole(role);
        }
      }
    } catch (err) {
      console.error('Error loading active org details:', err);
    }
  }, [currentOrganization, profile, setUserRole]);

  useEffect(() => {
    loadUserOrganizations();
  }, [loadUserOrganizations]);

  useEffect(() => {
    if (currentOrganization) {
      loadActiveOrgDetails();
    }
  }, [currentOrganization, loadActiveOrgDetails]);

  // Supabase Realtime channel listener
  useEffect(() => {
    if (!isSupabaseConfigured || !currentOrganization) return;

    const channel = supabase
      .channel(`org-${currentOrganization.id}`)
      .on(
        'postgres_changes',
        { event: '*', schema: 'public', table: 'organization_members', filter: `organization_id=eq.${currentOrganization.id}` },
        () => loadActiveOrgDetails()
      )
      .on(
        'postgres_changes',
        { event: '*', schema: 'public', table: 'organization_invitations', filter: `organization_id=eq.${currentOrganization.id}` },
        () => loadActiveOrgDetails()
      )
      .on(
        'postgres_changes',
        { event: '*', schema: 'public', table: 'teams', filter: `organization_id=eq.${currentOrganization.id}` },
        () => loadActiveOrgDetails()
      )
      .subscribe();

    return () => {
      supabase.removeChannel(channel);
    };
  }, [currentOrganization, loadActiveOrgDetails]);

  const switchOrganization = (orgId: string) => {
    let target = organizations.find((o) => o.id === orgId);
    if (!target) {
      const allSaved = getSavedMockOrganizations();
      target = allSaved.find((o) => o.id === orgId);
    }
    if (target) {
      setCurrentOrganization(target);
      setOrganizations([target]);
      try {
        localStorage.setItem('worksphere_active_org_id', target.id);
      } catch (e) {}
      window.dispatchEvent(new Event('storage'));
    }
  };

  const createNewOrganization = async (name: string, slug?: string, description?: string, logoUrl?: string) => {
    if (!profile) return { data: null, error: new Error('User not authenticated') };

    const generatedSlug = slug || name.toLowerCase().replace(/[^a-z0-9]/g, '-');
    const res = await createOrganization(name, generatedSlug, description || '', profile.id, logoUrl);

    if (res.data) {
      const newOrg = res.data;
      try {
        localStorage.setItem('worksphere_active_org_id', newOrg.id);
      } catch (e) {}
      setCurrentOrganization(newOrg);
      setOrganizations([newOrg]);
      await logActivity(newOrg.id, profile.id, 'created organization', name);
      await loadUserOrganizations();
      // Ensure currentOrganization remains definitively anchored to the newly registered workspace
      setCurrentOrganization(newOrg);
      setOrganizations([newOrg]);
      window.dispatchEvent(new Event('storage'));
    }

    return res;
  };

  const acceptUserInvitation = async (token: string) => {
    if (!profile) return { error: new Error('User not authenticated') };
    const res = await acceptInvitation(token, profile.id);
    if (!res.error) {
      await refreshOrganizationData();
      if (res.organizationId) {
        switchOrganization(res.organizationId);
      }
    }
    return res;
  };

  const declineUserInvitation = async (token: string) => {
    const res = await declineInvitation(token);
    if (!res.error && profile?.email) {
      const userInvites = await fetchUserPendingInvitations(profile.email);
      setUserPendingInvitations(userInvites);
    }
    return res;
  };

  const updateCurrentOrganization = async (updates: Partial<Organization>) => {
    if (!currentOrganization || !profile) return { error: new Error('No organization active') };

    const res = await updateOrganization(currentOrganization.id, updates);
    if (!res.error) {
      setCurrentOrganization({ ...currentOrganization, ...updates });
      setOrganizations((prev) => prev.map((o) => (o.id === currentOrganization.id ? { ...o, ...updates } : o)));
      await logActivity(currentOrganization.id, profile.id, 'updated organization settings', currentOrganization.name);
    }

    return res;
  };

  const deleteCurrentOrganization = async () => {
    if (!currentOrganization || !profile) return { error: new Error('No organization active') };

    const orgId = currentOrganization.id;
    const res = await deleteOrganization(orgId);

    if (!res.error) {
      const remaining = organizations.filter((o) => o.id !== orgId);
      setOrganizations(remaining);
      const nextOrg = remaining[0] || null;
      setCurrentOrganization(nextOrg);
      if (nextOrg) {
        localStorage.setItem('worksphere_active_org_id', nextOrg.id);
      } else {
        localStorage.removeItem('worksphere_active_org_id');
      }
    }

    return res;
  };

  const refreshOrganizationData = async () => {
    await loadUserOrganizations();
    await loadActiveOrgDetails();
  };

  return (
    <OrganizationContext.Provider
      value={{
        currentOrganization,
        currentRole,
        organizations,
        members,
        teams,
        pendingInvitations,
        userPendingInvitations,
        loading,
        switchOrganization,
        createNewOrganization,
        updateCurrentOrganization,
        deleteCurrentOrganization,
        refreshOrganizationData,
        acceptUserInvitation,
        declineUserInvitation,
      }}
    >
      {children}
    </OrganizationContext.Provider>
  );
}

export function useOrganization() {
  const context = useContext(OrganizationContext);
  if (!context) {
    throw new Error('useOrganization must be used within an OrganizationProvider');
  }
  return context;
}
