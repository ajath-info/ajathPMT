import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { Discussion, DiscussionComment, DiscussionVersion, Profile, OrgRole } from '../types';
import { logActivity } from './organizationService';
import { createNotification } from './notificationService';

let MOCK_DISCUSSIONS: Discussion[] = [
  {
    id: 'disc-1',
    project_id: 'proj-hq',
    author_id: 'demo-user-owner',
    title: 'MOM 08/09/2026',
    content: 'Minutes of Meeting - 08/09/2026.\n1. Aligned team on Ajath PMT workspace launchpad.\n2. Verified project grids, avatar initials, and bottom dock drawers.\n3. Gaurav leading HQ releases; Pankaj overseeing Ride My Cars driver app.',
    category: 'Announcements',
    is_announcement: true,
    is_pinned: true,
    is_locked: false,
    status: 'published',
    created_at: new Date(Date.now() - 2 * 3600 * 1000).toISOString(),
    author: {
      id: 'demo-user-owner',
      email: 'claire.client@partner.com',
      full_name: 'Claire Watson',
      avatar_url: '',
    },
    comments_count: 2,
  },
  {
    id: 'disc-2',
    project_id: 'proj-hq',
    author_id: 'user-gk',
    title: 'Message Board',
    content: 'Welcome to the centralized Ajath Infotech Pvt Ltd HQ Message Board! Pitch ideas, post meeting minutes, and make company-wide announcements here.',
    category: 'General',
    is_announcement: false,
    is_pinned: true,
    is_locked: false,
    status: 'published',
    created_at: new Date(Date.now() - 24 * 3600 * 1000).toISOString(),
    author: {
      id: 'user-gk',
      email: 'gaurav@ajath.com',
      full_name: 'Gaurav Kumar',
      avatar_url: '',
    },
    comments_count: 3,
  },
  {
    id: 'disc-3',
    project_id: 'proj-hq',
    author_id: 'user-pk',
    title: 'MOM 07/09/2026',
    content: 'Minutes of Meeting - 07/09/2026.\n1. Sprint progress review across mobile initiatives.\n2. Ride My Cars client sync completed with Edward.\n3. BIPL staging build scheduled for validation.',
    category: 'Meetings',
    is_announcement: false,
    is_pinned: false,
    is_locked: false,
    status: 'published',
    created_at: new Date(Date.now() - 26 * 3600 * 1000).toISOString(),
    author: {
      id: 'user-pk',
      email: 'pankaj@ajath.com',
      full_name: 'Pankaj Kumar',
      avatar_url: '',
    },
    comments_count: 1,
  },
  {
    id: 'disc-4',
    project_id: 'proj-hq',
    author_id: 'user-rk',
    title: 'Worklog 07/09/2026',
    content: 'Daily engineering worklog: Completed API endpoint migrations and verified real-time event broadcasting on production cluster.',
    category: 'Worklog',
    is_announcement: false,
    is_pinned: false,
    is_locked: false,
    status: 'published',
    created_at: new Date(Date.now() - 28 * 3600 * 1000).toISOString(),
    author: {
      id: 'user-rk',
      email: 'rohit@ajath.com',
      full_name: 'Rohit Kumar',
      avatar_url: '',
    },
    comments_count: 0,
  },
  {
    id: 'disc-rmc-1',
    project_id: 'proj-rmc',
    author_id: 'user-e',
    title: 'Untitled',
    content: '[RIDE MY CARS TERMS AND CONDITION1. Final.docx]',
    category: 'General',
    is_announcement: false,
    is_pinned: false,
    is_locked: false,
    is_client_visible: true,
    status: 'published',
    notified_count: 5,
    attachments: [
      {
        name: 'RIDE MY CARS TERMS AND CONDITION1. Final.docx',
        size: '21.7 KB',
        type: 'docx',
      },
    ],
    reactions: [
      {
        emoji: '🚀',
        count: 1,
        users: ['Edward'],
      },
    ],
    created_at: '2026-08-26T10:00:00.000Z',
    author: {
      id: 'user-e',
      email: 'edward@ndchomes.com',
      full_name: 'Edward',
      avatar_url: '',
    },
    comments_count: 27,
  },
  {
    id: 'disc-rmc-2',
    project_id: 'proj-rmc',
    author_id: 'user-rs',
    title: 'worklog 10-08-2026',
    content: '• Cloned the Ride My Cars project from GitHub. • Completed the project setup and configured the required environment. • Built and installed the APK on an Android device. • Explored and tested the driver module.',
    category: 'Worklog',
    is_announcement: false,
    is_pinned: false,
    is_locked: false,
    is_client_visible: false,
    status: 'published',
    created_at: '2026-08-10T14:30:00.000Z',
    author: {
      id: 'user-rs',
      email: 'rekha@ajath.com',
      full_name: 'Rekha Singh',
      avatar_url: '',
    },
    comments_count: 1,
  },
];

let MOCK_DISCUSSION_VERSIONS: DiscussionVersion[] = [
  {
    id: 'dver-1',
    discussion_id: 'disc-rmc-1',
    version_number: 1,
    title: 'Ride My Cars Terms and Condition (Initial Draft)',
    content: '[Draft document for client review v0.8]',
    edited_by: 'user-e',
    created_at: '2026-08-25T14:00:00.000Z',
  },
];

const RMC_NAMES = [
  { name: 'Edward', role: 'Project Owner', isClient: true, email: 'edward@ndchomes.com' },
  { name: 'Shivy Narain', role: 'Director', isClient: false, email: 'shivy@ajath.com' },
  { name: 'Pankaj Kumar', role: 'Team Lead', isClient: false, email: 'pankaj@ajath.com' },
  { name: 'Gaurav Kumar', role: 'Dev Lead', isClient: false, email: 'gaurav@ajath.com' },
  { name: 'Rekha Singh', role: 'QA Engineer', isClient: false, email: 'rekha@ajath.com' },
  { name: 'Shachish Sneh', role: 'Project Manager', isClient: false, email: 'shachish@ajath.com' },
];

const RMC_SAMPLE_MESSAGES = [
  "Uploaded the revised draft of Nigeria vehicle terms and partner rider agreement.",
  "Verified all payment escrow clauses with counsel.",
  "Added driver onboarding verification requirements per vehicle inspection protocol.",
  "Confirmed SLA guarantees for roadside emergency assistance integration.",
  "Driver commission tier model updated in accordance with annexure B.",
  "Data retention policies audited under Nigeria Data Protection Act requirements.",
  "Customer in-app chat privacy terms harmonized with Firebase security rules.",
  "Staging environment build #48 ready with terms acceptance checkbox.",
  "Confirmed tokenized credit card authorization flows with Paystack integration.",
  "Notification templates dispatched to beta driver testing cluster.",
  "Client side error logging hooked up to Sentry monitoring console.",
  "Fleet management enterprise onboarding terms reviewed with corporate partners.",
  "Escrow disbursement validation logic tested against sandbox accounts.",
  "Weekly audit log retention period adjusted to 180 calendar days.",
  "Driver app APK v1.0.4 distributed via internal test track.",
  "Legal review from counsel completed with zero blockers.",
  "Ready for sign-off on final terms and conditions document."
];

let MOCK_DISCUSSION_COMMENTS: DiscussionComment[] = [
  {
    id: 'dcmt-1',
    discussion_id: 'disc-1',
    user_id: 'demo-user-admin',
    content: 'Sounds clear! Ready to roll out Phase 6 message board.',
    created_at: new Date(Date.now() - 2 * 24 * 3600 * 1000).toISOString(),
    profile: {
      id: 'demo-user-admin',
      email: 'sarah.admin@worksphere.io',
      full_name: 'Sarah Jenkins',
      avatar_url: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
    },
  },
  ...RMC_SAMPLE_MESSAGES.map((msg, i) => {
    const person = RMC_NAMES[i % RMC_NAMES.length];
    return {
      id: `dcmt-rmc-${i + 1}`,
      discussion_id: 'disc-rmc-1',
      user_id: `user-${person.name.toLowerCase().replace(/\s+/g, '-')}`,
      role: person.role,
      is_client: person.isClient,
      content: msg,
      created_at: new Date(new Date('2026-08-26T10:15:00Z').getTime() + i * 1800000).toISOString(),
      profile: {
        id: `user-${person.name.toLowerCase().replace(/\s+/g, '-')}`,
        email: person.email,
        full_name: person.name,
        avatar_url: '',
      },
    };
  }),
  {
    id: 'dcmt-rmc-27',
    discussion_id: 'disc-rmc-1',
    user_id: 'user-e',
    role: 'Project Owner',
    is_client: true,
    content: 'Also please ensure the vehicle inspection terms match Nigerian transport safety guidelines section 12. Attached docx has highlighted paragraphs for clause 8.',
    created_at: '2026-08-26T14:45:00.000Z',
    profile: {
      id: 'user-e',
      email: 'edward@ndchomes.com',
      full_name: 'Edward',
      avatar_url: '',
    },
  },
];

export async function getDiscussions(projectId: string, userRole?: OrgRole): Promise<Discussion[]> {
  let list: Discussion[] = [];
  if (isSupabaseConfigured) {
    try {
      let query = supabase
        .from('discussions')
        .select('*, author:profiles(*), comments:discussion_comments(count)')
        .eq('project_id', projectId);

      if (userRole === 'CLIENT') {
        query = query.eq('is_client_visible', true).neq('status', 'scheduled');
      }

      const { data, error } = await query
        .order('is_pinned', { ascending: false })
        .order('created_at', { ascending: false });

      if (!error && data) {
        list = data.map((d: any) => ({
          ...d,
          comments_count: d.comments?.[0]?.count || 0,
        })) as Discussion[];
      }
    } catch (e) {
      console.warn('Failed to fetch discussions via Supabase:', e);
    }
  }

  if (list.length === 0) {
    list = MOCK_DISCUSSIONS.filter((d) => d.project_id === projectId).sort((a, b) => {
      if (a.is_pinned !== b.is_pinned) return a.is_pinned ? -1 : 1;
      return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
    });
  }

  // Basecamp confidentiality & scheduled visibility rules
  if (userRole === 'CLIENT') {
    list = list.filter((d) => d.is_client_visible !== false);
    list = list.filter((d) => d.status !== 'scheduled');
  }

  return list;
}

export async function getDiscussionById(id: string, userRole?: OrgRole): Promise<Discussion | null> {
  let item: Discussion | null = null;
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('discussions')
        .select('*, author:profiles(*), comments:discussion_comments(count)')
        .eq('id', id)
        .single();

      if (!error && data) {
        item = {
          ...data,
          comments_count: data.comments?.[0]?.count || 0,
        } as Discussion;
      }
    } catch (e) {
      console.warn('Failed to fetch discussion by id via Supabase:', e);
    }
  }

  if (!item) {
    item = MOCK_DISCUSSIONS.find((d) => d.id === id) || null;
  }

  if (item && userRole === 'CLIENT' && (item.is_client_visible === false || item.status === 'scheduled')) {
    return null;
  }

  return item;
}

export async function createDiscussion(input: {
  project_id: string;
  author_id: string;
  title: string;
  content: string;
  category?: string;
  is_announcement?: boolean;
  is_pinned?: boolean;
  is_client_visible?: boolean;
  notified_count?: number;
  author?: Profile;
  attachments?: Array<{ name: string; size: string; type: string; url?: string }>;
  status?: 'published' | 'draft' | 'scheduled';
  scheduled_publish_at?: string;
}): Promise<Discussion> {
  const isScheduled = Boolean(
    input.scheduled_publish_at && new Date(input.scheduled_publish_at) > new Date()
  );
  const status = input.status || (isScheduled ? 'scheduled' : 'published');

  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('discussions')
        .insert([
          {
            project_id: input.project_id,
            author_id: input.author_id,
            title: input.title.trim(),
            content: input.content.trim(),
            category: input.category || 'General',
            is_announcement: Boolean(input.is_announcement),
            is_pinned: Boolean(input.is_pinned),
            is_client_visible: input.is_client_visible !== false,
            status,
            scheduled_publish_at: input.scheduled_publish_at || null,
          },
        ])
        .select('*, author:profiles(*)')
        .single();

      if (!error && data) {
        await logActivity(
          '',
          input.author_id,
          status === 'scheduled' ? 'Scheduled Discussion' : 'Created Discussion',
          data.title,
          input.project_id
        );
        return data as Discussion;
      }
    } catch (e) {
      console.warn('Failed to create discussion via Supabase:', e);
    }
  }

  const newDiscussion: Discussion = {
    id: `disc-${Date.now()}`,
    project_id: input.project_id,
    author_id: input.author_id,
    title: input.title.trim(),
    content: input.content.trim(),
    category: input.category || 'General',
    is_announcement: Boolean(input.is_announcement),
    is_pinned: Boolean(input.is_pinned),
    is_locked: false,
    is_client_visible: input.is_client_visible !== false,
    notified_count: input.notified_count || 5,
    attachments: input.attachments,
    status,
    scheduled_publish_at: input.scheduled_publish_at,
    created_at: new Date().toISOString(),
    comments_count: 0,
    author: input.author || {
      id: input.author_id,
      email: 'claire.client@partner.com',
      full_name: 'Claire Watson',
      avatar_url: '',
    },
  };
  MOCK_DISCUSSIONS.unshift(newDiscussion);
  await logActivity(
    '',
    input.author_id,
    status === 'scheduled' ? 'Scheduled Discussion' : 'Created Discussion',
    newDiscussion.title,
    input.project_id
  );
  return newDiscussion;
}

export async function updateDiscussion(
  id: string,
  updates: Partial<Discussion>,
  editorId?: string
): Promise<Discussion> {
  const current = MOCK_DISCUSSIONS.find((d) => d.id === id);

  // If content or title changed, archive revision version in Basecamp 4 revision history
  if (current && (updates.title || updates.content)) {
    const existingVersions = await getDiscussionVersions(id);
    const nextVerNum = existingVersions.length + 1;

    if (isSupabaseConfigured) {
      try {
        await supabase.from('discussion_versions').insert([
          {
            discussion_id: id,
            version_number: nextVerNum,
            title: current.title,
            content: current.content,
            edited_by: editorId || current.author_id,
          },
        ]);
      } catch (e) {
        console.warn('Failed to record discussion version via Supabase:', e);
      }
    }

    const archived: DiscussionVersion = {
      id: `dver-${Date.now()}`,
      discussion_id: id,
      version_number: nextVerNum,
      title: current.title,
      content: current.content,
      edited_by: editorId || current.author_id,
      created_at: new Date().toISOString(),
    };
    MOCK_DISCUSSION_VERSIONS.unshift(archived);
  }

  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('discussions')
        .update({ ...updates, updated_at: new Date().toISOString() })
        .eq('id', id)
        .select('*, author:profiles(*)')
        .single();

      if (!error && data) return data as Discussion;
    } catch (e) {
      console.warn('Error updating discussion via Supabase:', e);
    }
  }

  const idx = MOCK_DISCUSSIONS.findIndex((d) => d.id === id);
  if (idx !== -1) {
    MOCK_DISCUSSIONS[idx] = {
      ...MOCK_DISCUSSIONS[idx],
      ...updates,
      updated_at: new Date().toISOString(),
    };
    return MOCK_DISCUSSIONS[idx];
  }
  throw new Error('Discussion not found');
}

export async function getDiscussionVersions(discussionId: string): Promise<DiscussionVersion[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('discussion_versions')
        .select('*, editor:profiles(*)')
        .eq('discussion_id', discussionId)
        .order('version_number', { ascending: false });

      if (!error && data) return data as DiscussionVersion[];
    } catch (e) {
      console.warn('Failed to fetch discussion versions via Supabase:', e);
    }
  }

  return MOCK_DISCUSSION_VERSIONS.filter((v) => v.discussion_id === discussionId).sort(
    (a, b) => b.version_number - a.version_number
  );
}

/**
 * Autonomous worker helper to publish scheduled discussions when their publish time arrives
 */
export async function publishScheduledDiscussions(): Promise<number> {
  const now = new Date().toISOString();
  let publishedCount = 0;

  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('discussions')
        .update({ status: 'published' })
        .eq('status', 'scheduled')
        .lte('scheduled_publish_at', now)
        .select('*');

      if (!error && data) {
        publishedCount = data.length;
      }
    } catch (e) {
      console.warn('Failed publishing scheduled discussions via Supabase:', e);
    }
  }

  // Mock
  for (const disc of MOCK_DISCUSSIONS) {
    if (
      disc.status === 'scheduled' &&
      disc.scheduled_publish_at &&
      new Date(disc.scheduled_publish_at) <= new Date()
    ) {
      disc.status = 'published';
      publishedCount++;
    }
  }

  return publishedCount;
}

export async function deleteDiscussion(id: string): Promise<void> {
  if (isSupabaseConfigured) {
    await supabase.from('discussions').delete().eq('id', id);
  }
  MOCK_DISCUSSIONS = MOCK_DISCUSSIONS.filter((d) => d.id !== id);
}

export async function getDiscussionComments(discussionId: string): Promise<DiscussionComment[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('discussion_comments')
        .select('*, profile:profiles(*)')
        .eq('discussion_id', discussionId)
        .order('created_at', { ascending: true });

      if (!error && data) return data as DiscussionComment[];
    } catch (e) {
      console.warn('Failed to fetch discussion comments via Supabase:', e);
    }
  }

  return MOCK_DISCUSSION_COMMENTS.filter((c) => c.discussion_id === discussionId);
}

export async function addDiscussionComment(
  discussionId: string,
  userId: string,
  content: string,
  parentCommentId?: string,
  userProfile?: Partial<Profile> & { role?: string; is_client?: boolean },
  attachments?: Array<{ name: string; size: string; type: string; url?: string }>
): Promise<DiscussionComment> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('discussion_comments')
        .insert([
          {
            discussion_id: discussionId,
            user_id: userId,
            content: content.trim(),
            parent_comment_id: parentCommentId || null,
          },
        ])
        .select('*, profile:profiles(*)')
        .single();

      if (!error && data) return data as DiscussionComment;
    } catch (e) {
      console.warn('Failed to add comment via Supabase:', e);
    }
  }

  const newComment: DiscussionComment = {
    id: `dcmt-${Date.now()}`,
    discussion_id: discussionId,
    user_id: userId,
    parent_comment_id: parentCommentId,
    content: content.trim(),
    role: userProfile?.role || 'Team Member',
    is_client: userProfile?.is_client ?? false,
    attachments: attachments && attachments.length > 0 ? attachments : undefined,
    created_at: new Date().toISOString(),
    profile: {
      id: userId,
      email: userProfile?.email || 'claire.client@partner.com',
      full_name: userProfile?.full_name || 'Claire Watson',
      avatar_url: userProfile?.avatar_url || '',
    },
  };
  MOCK_DISCUSSION_COMMENTS.push(newComment);

  const disc = MOCK_DISCUSSIONS.find((d) => d.id === discussionId);
  if (disc) {
    disc.comments_count = (disc.comments_count || 0) + 1;
  }

  return newComment;
}

export async function toggleReaction(
  discussionId: string,
  emoji: string,
  userName = 'Current User'
): Promise<Discussion> {
  const disc = MOCK_DISCUSSIONS.find((d) => d.id === discussionId);
  if (!disc) throw new Error('Discussion not found');

  const reactions = disc.reactions ? [...disc.reactions] : [];
  const existingIdx = reactions.findIndex((r) => r.emoji === emoji);

  if (existingIdx !== -1) {
    const existing = reactions[existingIdx];
    const users = existing.users || [];
    const userIdx = users.indexOf(userName);
    if (userIdx !== -1) {
      users.splice(userIdx, 1);
      existing.count = Math.max(0, existing.count - 1);
    } else {
      users.push(userName);
      existing.count += 1;
    }
    if (existing.count === 0) {
      reactions.splice(existingIdx, 1);
    } else {
      reactions[existingIdx] = { ...existing, users };
    }
  } else {
    reactions.push({ emoji, count: 1, users: [userName] });
  }

  disc.reactions = reactions;
  return { ...disc };
}

export async function toggleClientVisibility(
  discussionId: string,
  isVisible: boolean
): Promise<Discussion> {
  const disc = MOCK_DISCUSSIONS.find((d) => d.id === discussionId);
  if (!disc) throw new Error('Discussion not found');
  disc.is_client_visible = isVisible;
  return { ...disc };
}
