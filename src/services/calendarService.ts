import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { CalendarEvent, CalendarEventAttendee, CalendarEventComment, Task } from '../types';
import { getTasks } from './taskService';

let MOCK_CALENDAR_EVENTS: CalendarEvent[] = [
  {
    id: 'evt-1',
    project_id: 'proj-hq',
    title: 'Sprint Planning & Retrospective',
    description: 'Weekly team alignment meeting in HQ conference room.',
    start_at: new Date(Date.now() + 1 * 24 * 3600 * 1000).toISOString(),
    end_at: new Date(Date.now() + 1 * 24 * 3600 * 1000 + 3600 * 1000).toISOString(),
    all_day: false,
    color: '#3b82f6',
    created_by: 'demo-user-owner',
    created_at: new Date().toISOString(),
  },
  {
    id: 'evt-2',
    project_id: 'proj-rmc',
    title: 'Client Demo: Ride My Cars (Edward)',
    description: 'Review vehicle intake and inspection mobile flow with client.',
    start_at: new Date(Date.now() + 3 * 24 * 3600 * 1000).toISOString(),
    end_at: new Date(Date.now() + 3 * 24 * 3600 * 1000 + 3600 * 1000).toISOString(),
    all_day: false,
    color: '#10b981',
    created_by: 'demo-user-owner',
    created_at: new Date().toISOString(),
  },
  {
    id: 'evt-3',
    project_id: 'proj-bipl',
    title: 'BIPL App Staging Release v1.2',
    description: 'Final regression test pass before client sign-off.',
    start_at: new Date(Date.now() + 5 * 24 * 3600 * 1000).toISOString(),
    end_at: new Date(Date.now() + 5 * 24 * 3600 * 1000).toISOString(),
    all_day: true,
    color: '#f59e0b',
    created_by: 'demo-user-owner',
    created_at: new Date().toISOString(),
  },
];

let MOCK_EVENT_ATTENDEES: CalendarEventAttendee[] = [
  {
    id: 'att-1',
    event_id: 'evt-1',
    user_id: 'demo-user-owner',
    rsvp_status: 'ACCEPTED',
    created_at: new Date().toISOString(),
  },
  {
    id: 'att-2',
    event_id: 'evt-1',
    user_id: 'user-shivansh',
    rsvp_status: 'PENDING',
    created_at: new Date().toISOString(),
  },
];

let MOCK_EVENT_COMMENTS: CalendarEventComment[] = [
  {
    id: 'evt-comm-1',
    event_id: 'evt-1',
    user_id: 'demo-user-owner',
    content: 'Please bring the updated sprint metrics and client feedback notes.',
    created_at: new Date().toISOString(),
  },
];

export async function getCalendarEvents(projectId: string): Promise<CalendarEvent[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('calendar_events')
        .select('*, creator:profiles(*)')
        .eq('project_id', projectId)
        .order('start_at', { ascending: true });

      if (!error && data) return data as CalendarEvent[];
    } catch (e) {
      console.warn('Failed to fetch calendar events via Supabase:', e);
    }
  }

  return MOCK_CALENDAR_EVENTS.filter((e) => e.project_id === projectId);
}

export async function getCalendarEvent(eventId: string): Promise<CalendarEvent | null> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('calendar_events')
        .select('*, creator:profiles(*)')
        .eq('id', eventId)
        .single();

      if (!error && data) return data as CalendarEvent;
    } catch (e) {
      console.warn('Failed to fetch single calendar event:', e);
    }
  }

  const evt = MOCK_CALENDAR_EVENTS.find((e) => e.id === eventId);
  return evt || null;
}

export async function createCalendarEvent(input: {
  project_id: string;
  title: string;
  description?: string;
  start_at: string;
  end_at: string;
  all_day?: boolean;
  color?: string;
  created_by?: string;
}): Promise<CalendarEvent> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('calendar_events')
        .insert([
          {
            project_id: input.project_id,
            title: input.title.trim(),
            description: input.description?.trim() || null,
            start_at: input.start_at,
            end_at: input.end_at,
            all_day: Boolean(input.all_day),
            color: input.color || '#3b82f6',
            created_by: input.created_by || null,
          },
        ])
        .select('*, creator:profiles(*)')
        .single();

      if (!error && data) return data as CalendarEvent;
    } catch (e) {
      console.warn('Failed to create calendar event via Supabase:', e);
    }
  }

  const newEvent: CalendarEvent = {
    id: `evt-${Date.now()}`,
    project_id: input.project_id,
    title: input.title.trim(),
    description: input.description?.trim(),
    start_at: input.start_at,
    end_at: input.end_at,
    all_day: Boolean(input.all_day),
    color: input.color || '#3b82f6',
    created_by: input.created_by,
    created_at: new Date().toISOString(),
  };
  MOCK_CALENDAR_EVENTS.push(newEvent);
  return newEvent;
}

export async function updateCalendarEvent(
  id: string,
  input: Partial<{
    title: string;
    description: string;
    start_at: string;
    end_at: string;
    all_day: boolean;
    color: string;
  }>
): Promise<CalendarEvent | null> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('calendar_events')
        .update(input)
        .eq('id', id)
        .select('*, creator:profiles(*)')
        .single();

      if (!error && data) return data as CalendarEvent;
    } catch (e) {
      console.warn('Failed to update calendar event via Supabase:', e);
    }
  }

  const index = MOCK_CALENDAR_EVENTS.findIndex((e) => e.id === id);
  if (index !== -1) {
    MOCK_CALENDAR_EVENTS[index] = {
      ...MOCK_CALENDAR_EVENTS[index],
      ...input,
    };
    return MOCK_CALENDAR_EVENTS[index];
  }
  return null;
}

export async function deleteCalendarEvent(id: string): Promise<void> {
  if (isSupabaseConfigured) {
    await supabase.from('calendar_events').delete().eq('id', id);
  }
  MOCK_CALENDAR_EVENTS = MOCK_CALENDAR_EVENTS.filter((e) => e.id !== id);
}

/**
 * Event Attendees & RSVP
 */
export async function getEventAttendees(eventId: string): Promise<CalendarEventAttendee[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('calendar_event_attendees')
        .select('*, profile:profiles(*)')
        .eq('event_id', eventId);

      if (!error && data) return data as CalendarEventAttendee[];
    } catch (e) {
      console.warn('Failed to fetch event attendees via Supabase:', e);
    }
  }

  return MOCK_EVENT_ATTENDEES.filter((a) => a.event_id === eventId);
}

export async function setEventAttendees(eventId: string, userIds: string[]): Promise<CalendarEventAttendee[]> {
  if (isSupabaseConfigured) {
    try {
      // Upsert attendees
      const payload = userIds.map((userId) => ({
        event_id: eventId,
        user_id: userId,
        rsvp_status: 'PENDING',
      }));
      const { data, error } = await supabase
        .from('calendar_event_attendees')
        .upsert(payload, { onConflict: 'event_id,user_id' })
        .select('*, profile:profiles(*)');

      if (!error && data) return data as CalendarEventAttendee[];
    } catch (e) {
      console.warn('Failed to upsert attendees via Supabase:', e);
    }
  }

  const newAttendees: CalendarEventAttendee[] = userIds.map((userId, idx) => ({
    id: `att-${Date.now()}-${idx}`,
    event_id: eventId,
    user_id: userId,
    rsvp_status: 'PENDING',
    created_at: new Date().toISOString(),
  }));

  MOCK_EVENT_ATTENDEES = [
    ...MOCK_EVENT_ATTENDEES.filter((a) => a.event_id !== eventId),
    ...newAttendees,
  ];
  return newAttendees;
}

export async function respondEventRsvp(
  eventId: string,
  userId: string,
  rsvpStatus: 'PENDING' | 'ACCEPTED' | 'DECLINED' | 'TENTATIVE'
): Promise<CalendarEventAttendee | null> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('calendar_event_attendees')
        .upsert(
          {
            event_id: eventId,
            user_id: userId,
            rsvp_status: rsvpStatus,
          },
          { onConflict: 'event_id,user_id' }
        )
        .select('*, profile:profiles(*)')
        .single();

      if (!error && data) return data as CalendarEventAttendee;
    } catch (e) {
      console.warn('Failed to update RSVP via Supabase:', e);
    }
  }

  const existing = MOCK_EVENT_ATTENDEES.find((a) => a.event_id === eventId && a.user_id === userId);
  if (existing) {
    existing.rsvp_status = rsvpStatus;
    return existing;
  }

  const created: CalendarEventAttendee = {
    id: `att-${Date.now()}`,
    event_id: eventId,
    user_id: userId,
    rsvp_status: rsvpStatus,
    created_at: new Date().toISOString(),
  };
  MOCK_EVENT_ATTENDEES.push(created);
  return created;
}

/**
 * Event Comments
 */
export async function getEventComments(eventId: string): Promise<CalendarEventComment[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('calendar_event_comments')
        .select('*, profile:profiles(*)')
        .eq('event_id', eventId)
        .order('created_at', { ascending: true });

      if (!error && data) return data as CalendarEventComment[];
    } catch (e) {
      console.warn('Failed to fetch event comments via Supabase:', e);
    }
  }

  return MOCK_EVENT_COMMENTS.filter((c) => c.event_id === eventId);
}

export async function addEventComment(
  eventId: string,
  userId: string,
  content: string
): Promise<CalendarEventComment> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('calendar_event_comments')
        .insert([
          {
            event_id: eventId,
            user_id: userId,
            content: content.trim(),
          },
        ])
        .select('*, profile:profiles(*)')
        .single();

      if (!error && data) return data as CalendarEventComment;
    } catch (e) {
      console.warn('Failed to add event comment via Supabase:', e);
    }
  }

  const newComment: CalendarEventComment = {
    id: `evt-comm-${Date.now()}`,
    event_id: eventId,
    user_id: userId,
    content: content.trim(),
    created_at: new Date().toISOString(),
  };
  MOCK_EVENT_COMMENTS.push(newComment);
  return newComment;
}

/**
 * RFC 5545 iCalendar (.ics) Generator for Basecamp 4 Parity
 */
export function generateCalendarIcs(events: CalendarEvent[], projectName: string = 'WorkSphere Project'): string {
  const formatUtc = (isoStr: string) => {
    const d = new Date(isoStr);
    return d.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '');
  };

  const formatDateOnly = (isoStr: string) => {
    const d = new Date(isoStr);
    return d.toISOString().slice(0, 10).replace(/-/g, '');
  };

  let ics = [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'PRODID:-//Ajath PMT//Calendar//EN',
    'CALSCALE:GREGORIAN',
    'METHOD:PUBLISH',
    `X-WR-CALNAME:${projectName.replace(/\r?\n/g, ' ')}`,
    'X-WR-TIMEZONE:UTC',
  ];

  for (const evt of events) {
    ics.push('BEGIN:VEVENT');
    ics.push(`UID:${evt.id}@worksphere.app`);
    ics.push(`DTSTAMP:${formatUtc(new Date().toISOString())}`);

    if (evt.all_day) {
      ics.push(`DTSTART;VALUE=DATE:${formatDateOnly(evt.start_at)}`);
      ics.push(`DTEND;VALUE=DATE:${formatDateOnly(evt.end_at)}`);
    } else {
      ics.push(`DTSTART:${formatUtc(evt.start_at)}`);
      ics.push(`DTEND:${formatUtc(evt.end_at)}`);
    }

    ics.push(`SUMMARY:${(evt.title || '').replace(/\r?\n/g, ' ')}`);
    if (evt.description) {
      ics.push(`DESCRIPTION:${evt.description.replace(/\r?\n/g, '\\n')}`);
    }
    ics.push('STATUS:CONFIRMED');
    ics.push('END:VEVENT');
  }

  ics.push('END:VCALENDAR');
  return ics.join('\r\n');
}

export function downloadCalendarIcs(events: CalendarEvent[], filename: string = 'schedule.ics'): void {
  const content = generateCalendarIcs(events);
  const blob = new Blob([content], { type: 'text/calendar;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

/**
 * Combine custom calendar events AND task deadlines into a single schedule list
 */
export async function getProjectScheduleItems(projectId: string): Promise<{
  events: CalendarEvent[];
  tasks: Task[];
}> {
  const [events, tasks] = await Promise.all([
    getCalendarEvents(projectId),
    getTasks(projectId),
  ]);

  return { events, tasks };
}

async function resolveProjectIds(
  organizationId?: string,
  projectIds?: string[]
): Promise<Set<string> | null> {
  if (projectIds && projectIds.length > 0) {
    return new Set(projectIds);
  }
  if (organizationId) {
    try {
      const { getSavedMockProjects } = await import('./projectService');
      const allProjects = getSavedMockProjects();
      const orgProjects = allProjects.filter((p) => p.organization_id === organizationId);
      return new Set(orgProjects.map((p) => p.id));
    } catch (e) {}
  }
  return null;
}

export async function getCompanyMilestonesHealth(
  organizationId?: string,
  projectIds?: string[]
): Promise<{ onTrackPercent: number; label: string; totalMilestones: number; overdueCount: number }> {
  const targetProjectIds = await resolveProjectIds(organizationId, projectIds);

  if (targetProjectIds !== null && targetProjectIds.size === 0) {
    return { onTrackPercent: 100, label: '100% on track', totalMilestones: 0, overdueCount: 0 };
  }

  let events: CalendarEvent[] = [];
  if (isSupabaseConfigured) {
    try {
      let query = supabase.from('calendar_events').select('*');
      if (targetProjectIds !== null) {
        query = query.in('project_id', Array.from(targetProjectIds));
      }
      const { data, error } = await query;
      if (!error && data) events = data as CalendarEvent[];
    } catch (e) {
      console.warn('Failed to query calendar events for milestones health:', e);
    }
  } else {
    events = MOCK_CALENDAR_EVENTS.filter((e) => {
      if (targetProjectIds !== null) return targetProjectIds.has(e.project_id);
      return true;
    });
  }

  // Also query tasks with due dates
  let tasks: Task[] = [];
  try {
    const { getTasks } = await import('./taskService');
    if (targetProjectIds !== null) {
      for (const pid of Array.from(targetProjectIds)) {
        const pTasks = await getTasks(pid);
        tasks.push(...pTasks);
      }
    }
  } catch (e) {}

  const now = Date.now();
  let totalCount = 0;
  let overdueCount = 0;

  // Process calendar events
  for (const evt of events) {
    totalCount++;
    const endTime = new Date(evt.end_at).getTime();
    if (!isNaN(endTime) && endTime < now) {
      overdueCount++;
    }
  }

  // Process dated tasks
  for (const t of tasks) {
    if (t.due_date && t.status !== 'COMPLETED') {
      totalCount++;
      const dueTime = new Date(t.due_date).getTime() + 24 * 3600 * 1000 - 1; // End of due date
      if (!isNaN(dueTime) && dueTime < now) {
        overdueCount++;
      }
    }
  }

  if (totalCount === 0) {
    return { onTrackPercent: 100, label: '100% on track', totalMilestones: 0, overdueCount: 0 };
  }

  const onTrackCount = Math.max(0, totalCount - overdueCount);
  const percent = Math.round((onTrackCount / totalCount) * 100);
  return {
    onTrackPercent: percent,
    label: `${percent}% on track`,
    totalMilestones: totalCount,
    overdueCount,
  };
}
