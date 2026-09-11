import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { NotificationItem, UserNotificationPreferences } from '../types';
export type { UserNotificationPreferences };

let MOCK_NOTIFICATIONS: NotificationItem[] = [
  {
    id: 'notif-1',
    user_id: 'demo-user-owner',
    actor_id: 'demo-user-admin',
    type: 'TASK_ASSIGNED',
    title: 'New Task Assignment',
    message: 'Sarah Jenkins assigned you to "Audit & refine Tailwind CSS color system tokens"',
    link_url: '/projects/proj-1/todos',
    entity_type: 'TASK',
    entity_id: 'task-1',
    is_read: false,
    created_at: new Date(Date.now() - 2 * 3600 * 1000).toISOString(),
    actor: {
      id: 'demo-user-admin',
      email: 'sarah.admin@worksphere.io',
      full_name: 'Sarah Jenkins',
      avatar_url: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
    },
  },
  {
    id: 'notif-2',
    user_id: 'demo-user-owner',
    actor_id: 'demo-user-admin',
    type: 'DISCUSSION_POSTED',
    title: 'New Discussion Announcement',
    message: 'Sarah Jenkins posted in "UI Design System & HSL Token Standards"',
    link_url: '/projects/proj-1/discussions',
    entity_type: 'DISCUSSION',
    entity_id: 'disc-2',
    is_read: true,
    created_at: new Date(Date.now() - 1 * 24 * 3600 * 1000).toISOString(),
    actor: {
      id: 'demo-user-admin',
      email: 'sarah.admin@worksphere.io',
      full_name: 'Sarah Jenkins',
      avatar_url: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
    },
  },
];

export async function getUserNotifications(userId: string, orgId?: string): Promise<NotificationItem[]> {
  if (isSupabaseConfigured) {
    try {
      let query = supabase
        .from('notifications')
        .select('*, actor:profiles!actor_id(*)')
        .eq('user_id', userId);

      if (orgId) {
        query = query.eq('organization_id', orgId);
      }

      const { data, error } = await query.order('created_at', { ascending: false });

      if (!error && data) return data as NotificationItem[];
    } catch (e) {
      console.warn('Failed to fetch notifications via Supabase:', e);
    }
  }

  return MOCK_NOTIFICATIONS.filter(
    (n) => n.user_id === userId && (!orgId || !n.organization_id || n.organization_id === orgId)
  );
}

export async function markNotificationAsRead(id: string): Promise<void> {
  if (isSupabaseConfigured) {
    await supabase.from('notifications').update({ is_read: true }).eq('id', id);
  }
  const n = MOCK_NOTIFICATIONS.find((item) => item.id === id);
  if (n) n.is_read = true;
}

export async function markAllNotificationsAsRead(userId: string, orgId?: string): Promise<void> {
  if (isSupabaseConfigured) {
    let query = supabase.from('notifications').update({ is_read: true }).eq('user_id', userId);
    if (orgId) query = query.eq('organization_id', orgId);
    await query;
  }
  MOCK_NOTIFICATIONS.forEach((n) => {
    if (n.user_id === userId && (!orgId || !n.organization_id || n.organization_id === orgId)) {
      n.is_read = true;
    }
  });
}

/**
 * Basecamp "Work Can Wait": Evaluates whether recipient is currently in their designated quiet hours
 */
export function isUserInQuietHours(prefs: UserNotificationPreferences): boolean {
  if (!prefs.quiet_hours_enabled) return false;

  try {
    const now = new Date();
    const userTimeStr = prefs.timezone
      ? now.toLocaleTimeString('en-US', { timeZone: prefs.timezone, hour12: false, hour: '2-digit', minute: '2-digit' })
      : `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

    const currentDay = now.getDay();
    const workDays = prefs.work_days || [1, 2, 3, 4, 5];
    if (!workDays.includes(currentDay)) {
      return true; // Weekend / non-workday is quiet
    }

    const start = prefs.work_hours_start || '09:00';
    const end = prefs.work_hours_end || '18:00';

    if (start <= end) {
      return userTimeStr < start || userTimeStr >= end;
    } else {
      return userTimeStr < start && userTimeStr >= end;
    }
  } catch {
    return false;
  }
}

export async function createNotification(input: {
  organization_id?: string;
  user_id: string;
  actor_id?: string;
  type?: string;
  title: string;
  message?: string;
  body?: string;
  link_url?: string;
  entity_type?: string;
  entity_id?: string;
  bypass_quiet_hours?: boolean;
}): Promise<NotificationItem> {
  const contentMsg = input.message || input.body || '';

  let resolvedOrgId = input.organization_id;
  if (!resolvedOrgId && input.link_url && input.link_url.startsWith('/projects/')) {
    const parts = input.link_url.split('/');
    const projId = parts[2];
    if (projId) {
      try {
        const { getProjectById } = await import('./projectService');
        const proj = await getProjectById(projId);
        if (proj?.organization_id) resolvedOrgId = proj.organization_id;
      } catch (e) {}
    }
  }

  // 1. Check User Notification Preferences and Quiet Hours
  const prefs = await getUserNotificationPreferences(input.user_id);
  const inQuietHours = !input.bypass_quiet_hours && isUserInQuietHours(prefs);

  // 2. Intelligent Notification Aggregation (Basecamp bundling)
  if (input.entity_type && input.entity_id) {
    if (!isSupabaseConfigured) {
      const existing = MOCK_NOTIFICATIONS.find(
        (n) =>
          n.user_id === input.user_id &&
          n.entity_type === input.entity_type &&
          n.entity_id === input.entity_id &&
          !n.is_read &&
          (!resolvedOrgId || n.organization_id === resolvedOrgId)
      );

      if (existing) {
        const count = (existing.aggregate_count || 1) + 1;
        existing.aggregate_count = count;
        existing.title = `${input.title} (${count} updates)`;
        existing.message = contentMsg;
        existing.created_at = new Date().toISOString();
        return existing;
      }
    } else {
      try {
        let query = supabase
          .from('notifications')
          .select('*')
          .eq('user_id', input.user_id)
          .eq('entity_type', input.entity_type)
          .eq('entity_id', input.entity_id)
          .eq('is_read', false);

        if (resolvedOrgId) {
          query = query.eq('organization_id', resolvedOrgId);
        }

        const { data: existingList } = await query.limit(1);

        if (existingList && existingList.length > 0) {
          const existing = existingList[0];
          const newCount = (existing.aggregate_count || 1) + 1;
          const { data: updated } = await supabase
            .from('notifications')
            .update({
              aggregate_count: newCount,
              title: `${input.title} (${newCount} updates)`,
              message: contentMsg,
              created_at: new Date().toISOString(),
            })
            .eq('id', existing.id)
            .select('*, actor:profiles!actor_id(*)')
            .single();

          if (updated) return updated as NotificationItem;
        }
      } catch (e) {
        console.warn('Aggregation check error via Supabase:', e);
      }
    }
  }

  // 3. Create fresh notification
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('notifications')
        .insert([
          {
            organization_id: resolvedOrgId || null,
            user_id: input.user_id,
            actor_id: input.actor_id || null,
            type: input.type || 'INFO',
            title: input.title,
            message: contentMsg,
            link_url: input.link_url || null,
            entity_type: input.entity_type || null,
            entity_id: input.entity_id || null,
            is_read: false,
            aggregate_count: 1,
            queued_for_quiet_hours: inQuietHours,
          },
        ])
        .select('*, actor:profiles!actor_id(*)')
        .single();

      if (!error && data) return data as NotificationItem;
    } catch (e) {
      console.warn('Failed to create notification via Supabase:', e);
    }
  }

  const newNotif: NotificationItem = {
    id: `notif-${Date.now()}`,
    organization_id: resolvedOrgId,
    user_id: input.user_id,
    actor_id: input.actor_id,
    type: input.type || 'INFO',
    title: input.title,
    message: contentMsg,
    link_url: input.link_url,
    entity_type: input.entity_type,
    entity_id: input.entity_id,
    is_read: false,
    aggregate_count: 1,
    queued_for_quiet_hours: inQuietHours,
    created_at: new Date().toISOString(),
  };
  MOCK_NOTIFICATIONS.unshift(newNotif);
  return newNotif;
}

const DEFAULT_PREFERENCES: Omit<UserNotificationPreferences, 'user_id'> = {
  email_notifications: true,
  push_notifications: true,
  notify_task_assigned: true,
  notify_discussion_replies: true,
  notify_chat_mentions: true,
  notify_due_dates: true,
  work_days: [1, 2, 3, 4, 5],
  work_hours_start: '09:00',
  work_hours_end: '18:00',
  quiet_hours_enabled: false,
  timezone: 'UTC',
};

export async function getUserNotificationPreferences(userId: string): Promise<UserNotificationPreferences> {
  const cacheKey = `worksphere_notification_prefs_${userId}`;
  const cached = localStorage.getItem(cacheKey);

  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('user_notification_preferences')
        .select('*')
        .eq('user_id', userId)
        .maybeSingle();

      if (!error && data) {
        localStorage.setItem(cacheKey, JSON.stringify(data));
        return data as UserNotificationPreferences;
      }
    } catch (e) {
      console.warn('Failed to fetch user notification preferences via Supabase:', e);
    }
  }

  if (cached) {
    try {
      return JSON.parse(cached);
    } catch {
      // fallback
    }
  }

  return {
    user_id: userId,
    ...DEFAULT_PREFERENCES,
  };
}

export async function updateUserNotificationPreferences(
  userId: string,
  prefs: Partial<UserNotificationPreferences>
): Promise<UserNotificationPreferences> {
  const cacheKey = `worksphere_notification_prefs_${userId}`;
  const existing = await getUserNotificationPreferences(userId);
  const updated: UserNotificationPreferences = {
    ...existing,
    ...prefs,
    user_id: userId,
  };

  localStorage.setItem(cacheKey, JSON.stringify(updated));

  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('user_notification_preferences')
        .upsert({
          user_id: userId,
          email_notifications: updated.email_notifications,
          push_notifications: updated.push_notifications,
          notify_task_assigned: updated.notify_task_assigned,
          notify_discussion_replies: updated.notify_discussion_replies,
          notify_chat_mentions: updated.notify_chat_mentions,
          notify_due_dates: updated.notify_due_dates,
          work_days: updated.work_days || [1, 2, 3, 4, 5],
          work_hours_start: updated.work_hours_start || '09:00',
          work_hours_end: updated.work_hours_end || '18:00',
          quiet_hours_enabled: Boolean(updated.quiet_hours_enabled),
          timezone: updated.timezone || 'UTC',
          updated_at: new Date().toISOString(),
        })
        .select('*')
        .single();

      if (!error && data) return data as UserNotificationPreferences;
    } catch (e) {
      console.warn('Failed to update notification preferences via Supabase:', e);
    }
  }

  return updated;
}


