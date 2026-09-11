import React, { createContext, useContext, useEffect, useState } from 'react';
import { User, Session } from '@supabase/supabase-js';
import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { Profile, Organization, OrgRole } from '../types';
import { sendPasswordResetEmail } from '../services/emailService';
import { createOrganization } from '../services/organizationService';

export const DEMO_USERS: Record<OrgRole, { profile: Profile; role: OrgRole }> = {
  OWNER: {
    profile: {
      id: 'demo-user-owner',
      email: 'claire.watson@ajath.com',
      full_name: 'Claire Watson',
      avatar_url: '',
      job_title: 'Account Owner',
      bio: 'Leading Ajath Infotech Pvt Ltd.',
      timezone: 'Asia/Kolkata (UTC+5:30)',
      created_at: new Date().toISOString(),
    },
    role: 'OWNER',
  },
  ADMIN: {
    profile: {
      id: 'demo-user-admin',
      email: 'alex.admin@worksphere.io',
      full_name: 'Alex Vance',
      avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
      job_title: 'Senior Operations & Tech Lead',
      bio: 'Managing cloud architecture, sprint delivery, and team workflows.',
      timezone: 'America/New_York (UTC-5)',
      created_at: new Date().toISOString(),
    },
    role: 'ADMIN',
  },
  MEMBER: {
    profile: {
      id: 'demo-user-member',
      email: 'marcus.dev@worksphere.io',
      full_name: 'Marcus Rivera',
      avatar_url: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
      job_title: 'Full-Stack Developer',
      bio: 'Crafting responsive user interfaces and visual systems.',
      timezone: 'America/Los_Angeles (UTC-8)',
      created_at: new Date().toISOString(),
    },
    role: 'MEMBER',
  },
  CLIENT: {
    profile: {
      id: 'demo-user-client',
      email: 'edward.client@partner.com',
      full_name: 'Edward Smith',
      avatar_url: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
      job_title: 'Client Partner Representative',
      bio: 'Reviewing progress updates and project deliverables.',
      timezone: 'Asia/Kolkata (UTC+5:30)',
      created_at: new Date().toISOString(),
    },
    role: 'CLIENT',
  },
};

export const DEMO_ORGANIZATION: Organization = {
  id: 'demo-org-acme',
  name: 'Acme Corporation Workspace',
  slug: 'acme-corp',
  logo_url: '',
  owner_id: DEMO_USERS.OWNER.profile.id,
  created_at: new Date().toISOString(),
};

interface AuthContextType {
  user: User | null;
  profile: Profile | null;
  organization: Organization | null;
  userRole: OrgRole;
  setUserRole: (role: OrgRole) => void;
  session: Session | null;
  loading: boolean;
  isMockMode: boolean;
  signUp: (email: string, password: string, fullName: string, orgName?: string) => Promise<{ error: Error | null }>;
  signIn: (email: string, password: string) => Promise<{ error: Error | null }>;
  signInAsDemo: (role: OrgRole) => void;
  signInAsFreshAdmin: () => void;
  signInAsFreshOwner: () => void;
  signOut: () => Promise<void>;
  resetPassword: (email: string) => Promise<{ error: Error | null }>;
  updatePassword: (password: string) => Promise<{ error: Error | null }>;
  updateProfile: (updates: Partial<Profile>) => Promise<{ error: Error | null }>;
  emailVerificationSent: boolean;
  setEmailVerificationSent: (sent: boolean) => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

function createMockUser(p: Profile): User {
  return {
    id: p.id,
    app_metadata: { provider: 'email' },
    user_metadata: { full_name: p.full_name, avatar_url: p.avatar_url },
    aud: 'authenticated',
    confirmation_sent_at: p.created_at,
    recovery_sent_at: p.created_at,
    email_change_sent_at: p.created_at,
    new_email: '',
    invited_at: p.created_at,
    action_link: '',
    email: p.email,
    phone: '',
    created_at: p.created_at,
    confirmed_at: p.created_at,
    email_confirmed_at: p.created_at,
    phone_confirmed_at: p.created_at,
    last_sign_in_at: p.created_at,
    role: 'authenticated',
    updated_at: p.created_at,
    identities: [],
    is_anonymous: false,
    factors: [],
  } as unknown as User;
}

function getInitialProfile(): Profile {
  try {
    const savedRole = localStorage.getItem('worksphere_demo_role') as OrgRole;
    if (savedRole && DEMO_USERS[savedRole]) {
      return { ...DEMO_USERS[savedRole].profile };
    }
    const saved = localStorage.getItem('basecamp_user_profile');
    if (saved) {
      const p = JSON.parse(saved);
      if (p && p.full_name) {
        return p;
      }
    }
  } catch (e) {}
  return DEMO_USERS.OWNER.profile;
}

function getInitialRole(): OrgRole {
  try {
    const savedRole = localStorage.getItem('worksphere_demo_role') as OrgRole;
    if (savedRole && DEMO_USERS[savedRole]) return savedRole;
  } catch (e) {}
  return 'OWNER';
}

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(() => createMockUser(getInitialProfile()));
  const [session, setSession] = useState<Session | null>(null);
  const [profile, setProfile] = useState<Profile | null>(() => getInitialProfile());
  const [organization, setOrganization] = useState<Organization | null>(null);
  const [userRole, setUserRole] = useState<OrgRole>(() => getInitialRole());
  const [loading, setLoading] = useState<boolean>(true);
  const [isMockMode, setIsMockMode] = useState<boolean>(!isSupabaseConfigured);
  const [emailVerificationSent, setEmailVerificationSent] = useState<boolean>(false);

  useEffect(() => {
    // Check saved local mock auth state
    const customProfile = getInitialProfile();
    const savedDemoRole = localStorage.getItem('worksphere_demo_role') as OrgRole;

    if (!isSupabaseConfigured || savedDemoRole || customProfile) {
      const activeRole = savedDemoRole || 'CLIENT';
      setProfile(customProfile);
      setUser(createMockUser(customProfile));
      setUserRole(activeRole);
      setIsMockMode(true);
      setLoading(false);
      return;
    }

    // Real Supabase Auth listener
    supabase.auth.getSession().then(({ data: { session } }) => {
      setSession(session);
      setUser(session?.user ?? null);
      if (session?.user) {
        fetchProfile(session.user.id);
      } else {
        setLoading(false);
      }
    });

    const { data: { subscription } } = supabase.auth.onAuthStateChange(async (_event, session) => {
      setSession(session);
      setUser(session?.user ?? null);
      if (session?.user) {
        await fetchProfile(session.user.id);
      } else {
        setProfile(null);
        setLoading(false);
      }
    });

    return () => {
      subscription.unsubscribe();
    };
  }, []);

  const fetchProfile = async (userId: string) => {
    try {
      const { data, error } = await supabase
        .from('profiles')
        .select('*')
        .eq('id', userId)
        .single();

      // Look up user's organization membership role
      const { data: memberData } = await supabase
        .from('organization_members')
        .select('role')
        .eq('user_id', userId)
        .maybeSingle();

      const { data: userData } = await supabase.auth.getUser();
      const metaRole = userData.user?.user_metadata?.role as OrgRole;
      const userEmail = userData.user?.email?.toLowerCase() || '';

      const resolvedRole: OrgRole = memberData?.role || metaRole || (
        userEmail.includes('client') || userEmail.includes('edward') ? 'CLIENT' :
        userEmail.includes('admin') ? 'ADMIN' :
        userEmail.includes('owner') ? 'OWNER' : 'MEMBER'
      );

      setUserRole(resolvedRole);
      localStorage.setItem('worksphere_demo_role', resolvedRole);

      if (data && !error) {
        setProfile(data as Profile);
      } else {
        // Fallback profile from metadata if profile row isn't created yet
        if (userData.user) {
          setProfile({
            id: userData.user.id,
            email: userData.user.email || '',
            full_name: userData.user.user_metadata?.full_name || (resolvedRole === 'CLIENT' ? 'Edward (Client Partner)' : 'Team Member'),
            avatar_url: userData.user.user_metadata?.avatar_url || '',
            job_title: userData.user.user_metadata?.job_title || (resolvedRole === 'CLIENT' ? 'Client Partner' : 'Workspace Member'),
            bio: '',
            timezone: 'UTC',
          });
        }
      }
    } catch (err) {
      console.error('Error fetching profile:', err);
    } finally {
      setLoading(false);
    }
  };

  const signUp = async (email: string, password: string, fullName: string, orgName?: string) => {
    if (!isSupabaseConfigured) {
      // Local fallback signup simulation
      const newProfile: Profile = {
        id: `user-${Date.now()}`,
        email,
        full_name: fullName,
        job_title: 'Organization Owner',
        avatar_url: '',
        bio: 'Welcome to WorkSphere!',
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC',
        created_at: new Date().toISOString(),
      };
      setProfile(newProfile);
      setUserRole('OWNER');
      if (orgName) {
        const createdOrg = await createOrganization(
          orgName,
          orgName.toLowerCase().replace(/[^a-z0-9]/g, '-'),
          'Company-wide announcements and workspace.',
          newProfile.id
        );
        if (createdOrg.data) {
          setOrganization(createdOrg.data);
          localStorage.setItem('worksphere_active_org_id', createdOrg.data.id);
        }
      }
      localStorage.setItem('worksphere_demo_role', 'OWNER');
      setEmailVerificationSent(true);
      return { error: null };
    }

    const { data, error } = await supabase.auth.signUp({
      email,
      password,
      options: {
        data: {
          full_name: fullName,
          org_name: orgName || 'My Workspace',
        },
      },
    });

    if (!error && data.user) {
      setEmailVerificationSent(true);
    }
    return { error: error as Error | null };
  };

  const signIn = async (email: string, password: string) => {
    const clean = email.trim().toLowerCase();
    if (clean.includes('fresh') || clean.includes('clean')) {
      if (clean.includes('owner')) {
        signInAsFreshOwner();
      } else {
        signInAsFreshAdmin();
      }
      return { error: null };
    }

    let detectedRole: OrgRole = 'MEMBER';
    if (clean.includes('client') || clean.includes('edward') || clean === DEMO_USERS.CLIENT.profile.email.toLowerCase()) {
      detectedRole = 'CLIENT';
    } else if (
      clean.includes('admin') ||
      clean.includes('alex') ||
      clean === DEMO_USERS.ADMIN.profile.email.toLowerCase() ||
      clean === 'sarah.admin@worksphere.io'
    ) {
      detectedRole = 'ADMIN';
    } else if (clean.includes('owner') || clean === DEMO_USERS.OWNER.profile.email.toLowerCase()) {
      detectedRole = 'OWNER';
    }

    if (!isSupabaseConfigured) {
      // Local fallback sign in simulation
      localStorage.setItem('worksphere_demo_role', detectedRole);
      const activeProfile = DEMO_USERS[detectedRole].profile;
      try {
        localStorage.setItem('basecamp_user_profile', JSON.stringify(activeProfile));
        window.dispatchEvent(new Event('storage'));
        window.dispatchEvent(new CustomEvent('basecamp_profile_updated', { detail: activeProfile }));
      } catch (e) {}
      setProfile(activeProfile);
      setUser(createMockUser(activeProfile));
      setUserRole(detectedRole);
      setIsMockMode(true);
      return { error: null };
    }

    const { error, data } = await supabase.auth.signInWithPassword({
      email,
      password,
    });
    if (!error && data?.user) {
      await fetchProfile(data.user.id);
    }
    return { error: error as Error | null };
  };

  const signInAsFreshOwner = () => {
    localStorage.setItem('worksphere_demo_role', 'OWNER');
    const freshId = `fresh-owner-${Date.now().toString(36)}`;
    const freshProfile: Profile = {
      id: freshId,
      email: 'fresh.owner@worksphere.io',
      full_name: 'Fresh Account Owner',
      avatar_url: '',
      job_title: 'Account Owner',
      bio: 'Owner ready to register and lead our company workspace.',
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC',
      created_at: new Date().toISOString(),
    };
    try {
      localStorage.setItem('basecamp_user_profile', JSON.stringify(freshProfile));
      localStorage.removeItem('worksphere_active_org_id');
      window.dispatchEvent(new Event('storage'));
      window.dispatchEvent(new CustomEvent('basecamp_profile_updated', { detail: freshProfile }));
    } catch (e) {}
    setProfile(freshProfile);
    setUser(createMockUser(freshProfile));
    setUserRole('OWNER');
    setIsMockMode(true);
    setLoading(false);
  };

  const signInAsFreshAdmin = () => {
    localStorage.setItem('worksphere_demo_role', 'ADMIN');
    const freshId = `fresh-admin-${Date.now().toString(36)}`;
    const freshProfile: Profile = {
      id: freshId,
      email: 'fresh.admin@worksphere.io',
      full_name: 'Fresh Administrator',
      avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
      job_title: 'Workspace Administrator',
      bio: 'Administrator ready to create and manage our company workspace.',
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC',
      created_at: new Date().toISOString(),
    };
    try {
      localStorage.setItem('basecamp_user_profile', JSON.stringify(freshProfile));
      localStorage.removeItem('worksphere_active_org_id');
      window.dispatchEvent(new Event('storage'));
      window.dispatchEvent(new CustomEvent('basecamp_profile_updated', { detail: freshProfile }));
    } catch (e) {}
    setProfile(freshProfile);
    setUser(createMockUser(freshProfile));
    setUserRole('ADMIN');
    setIsMockMode(true);
    setLoading(false);
  };

  const signInAsDemo = (role: OrgRole) => {
    localStorage.setItem('worksphere_demo_role', role);
    const activeProfile = DEMO_USERS[role]?.profile || DEMO_USERS.OWNER.profile;
    try {
      localStorage.setItem('basecamp_user_profile', JSON.stringify(activeProfile));
      window.dispatchEvent(new Event('storage'));
      window.dispatchEvent(new CustomEvent('basecamp_profile_updated', { detail: activeProfile }));
    } catch (e) {}
    setProfile(activeProfile);
    setUser(createMockUser(activeProfile));
    setUserRole(role);
    setIsMockMode(true);
    setLoading(false);
  };

  const signOut = async () => {
    localStorage.removeItem('worksphere_demo_role');
    localStorage.removeItem('worksphere_active_org_id');
    localStorage.removeItem('basecamp_user_profile');
    if (isSupabaseConfigured) {
      await supabase.auth.signOut();
    }
    setUser(null);
    setSession(null);
    setProfile(null);
  };

  const resetPassword = async (email: string) => {
    const resetUrl = `${window.location.origin}/reset-password`;
    await sendPasswordResetEmail(email, resetUrl);

    if (!isSupabaseConfigured) {
      return { error: null };
    }
    const { error } = await supabase.auth.resetPasswordForEmail(email, {
      redirectTo: resetUrl,
    });
    return { error: error as Error | null };
  };

  const updatePassword = async (password: string) => {
    if (!isSupabaseConfigured) {
      return { error: null };
    }
    const { error } = await supabase.auth.updateUser({ password });
    return { error: error as Error | null };
  };

  const updateProfile = async (updates: Partial<Profile>) => {
    if (!profile) return { error: new Error('No profile loaded') };

    const updated = { ...profile, ...updates };
    setProfile(updated);
    setUser(createMockUser(updated));
    try {
      localStorage.setItem('basecamp_user_profile', JSON.stringify(updated));
      window.dispatchEvent(new Event('storage'));
      window.dispatchEvent(new CustomEvent('basecamp_profile_updated', { detail: updated }));
    } catch (e) {}

    if (isSupabaseConfigured && user) {
      const { error } = await supabase
        .from('profiles')
        .upsert({ id: user.id, ...updates, updated_at: new Date().toISOString() });
      return { error: error as Error | null };
    }

    return { error: null };
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        profile,
        organization,
        userRole,
        setUserRole,
        session,
        loading,
        isMockMode,
        signUp,
        signIn,
        signInAsDemo,
        signInAsFreshAdmin,
        signInAsFreshOwner,
        signOut,
        resetPassword,
        updatePassword,
        updateProfile,
        emailVerificationSent,
        setEmailVerificationSent,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
