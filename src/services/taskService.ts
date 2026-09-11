import { supabase, isSupabaseConfigured } from '../lib/supabase';
import {
  Task,
  TaskComment,
  Label,
  CreateTaskInput,
  UpdateTaskInput,
  TaskStatusType,
  TaskPriorityType,
  Profile,
} from '../types';
import { logActivity } from './organizationService';
import { createNotification } from './notificationService';

let MOCK_TASKS: Task[] = [
  {
    id: 'task-1',
    project_id: 'proj-hq',
    todo_list_id: 'list-hq-1',
    title: 'Audit & finalize Ajath PMT launchpad design tokens',
    description: 'Ensure all buttons, cards, avatars, and bottom dock drawers match the production Ajath PMT specification.',
    status: 'IN_PROGRESS',
    priority: 'HIGH',
    start_date: '2026-09-02',
    due_date: new Date(Date.now() + 1 * 24 * 3600 * 1000).toISOString().split('T')[0],
    position: 0,
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 5 * 24 * 3600 * 1000).toISOString(),
    assignees: [
      {
        id: 'demo-user-owner',
        email: 'shivy@ajath.com',
        full_name: 'Shivy Narain',
        avatar_url: '',
      },
    ],
    comments_count: 2,
    subtasks_completed_count: 1,
    subtasks: [
      { id: 'sub-1', project_id: 'proj-hq', todo_list_id: 'list-hq-1', parent_task_id: 'task-1', title: 'Check 4-column responsive grid layout', status: 'COMPLETED', priority: 'MEDIUM', position: 0, created_at: new Date().toISOString() },
      { id: 'sub-2', project_id: 'proj-hq', todo_list_id: 'list-hq-1', parent_task_id: 'task-1', title: 'Verify CW purple avatar in bottom dock', status: 'NOT_STARTED', priority: 'MEDIUM', position: 1, created_at: new Date().toISOString() },
    ],
    labels: [{ id: 'lbl-1', organization_id: 'demo-org-acme', name: 'Design System', color: '#6366f1' }],
  },
  {
    id: 'task-rmc-1',
    project_id: 'proj-rmc',
    todo_list_id: 'list-rmc-1',
    title: 'Domain Login Details',
    description: 'Provide domain registrar and DNS credentials.',
    status: 'NOT_STARTED',
    priority: 'HIGH',
    position: 0,
    created_by: 'user-e',
    created_at: new Date(Date.now() - 6 * 24 * 3600 * 1000).toISOString(),
    assignees: [
      { id: 'user-e', email: 'edward@ndchomes.com', full_name: 'Edward', avatar_url: '' },
      { id: 'demo-user-owner', email: 'shivy@ajath.com', full_name: 'Shivy Narain', avatar_url: '' },
    ],
    comments_count: 0,
    subtasks_completed_count: 0,
    subtasks: [],
    labels: [],
  },
  {
    id: 'task-rmc-2',
    project_id: 'proj-rmc',
    todo_list_id: 'list-rmc-1',
    title: '1 Email Id With Domain Related',
    description: 'Setup primary transactional email inbox.',
    status: 'NOT_STARTED',
    priority: 'MEDIUM',
    position: 1,
    created_by: 'user-e',
    created_at: new Date(Date.now() - 5 * 24 * 3600 * 1000).toISOString(),
    assignees: [
      { id: 'user-e', email: 'edward@ndchomes.com', full_name: 'Edward', avatar_url: '' },
    ],
    comments_count: 0,
    subtasks_completed_count: 0,
    subtasks: [],
    labels: [],
  },
  {
    id: 'task-rmc-3',
    project_id: 'proj-rmc',
    todo_list_id: 'list-rmc-1',
    title: 'SSL',
    description: 'Install and verify SSL TLS wildcard certificate.',
    status: 'NOT_STARTED',
    priority: 'HIGH',
    position: 2,
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 4 * 24 * 3600 * 1000).toISOString(),
    assignees: [
      { id: 'demo-user-owner', email: 'shivy@ajath.com', full_name: 'Shivy Narain', avatar_url: '' },
    ],
    comments_count: 0,
    subtasks_completed_count: 0,
    subtasks: [],
    labels: [],
  },
  {
    id: 'task-2',
    project_id: 'proj-rmc',
    todo_list_id: 'list-rmc-1',
    title: 'Review Ride My Cars driver app vehicle inspection flow',
    description: 'Walk through the inspection photo upload, damage logs, and client signoff with Edward.',
    status: 'NOT_STARTED',
    priority: 'URGENT',
    start_date: '2026-09-04',
    due_date: new Date(Date.now() + 2 * 24 * 3600 * 1000).toISOString().split('T')[0],
    position: 3,
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 4 * 24 * 3600 * 1000).toISOString(),
    assignees: [
      {
        id: 'demo-user-owner',
        email: 'shivy@ajath.com',
        full_name: 'Shivy Narain',
        avatar_url: '',
      },
      {
        id: 'user-e',
        email: 'edward@ndchomes.com',
        full_name: 'Edward',
        avatar_url: '',
      },
    ],
    comments_count: 1,
    subtasks_completed_count: 0,
    subtasks: [],
    labels: [{ id: 'lbl-2', organization_id: 'demo-org-acme', name: 'Mobile Flow', color: '#10b981' }],
  },
  {
    id: 'task-3',
    project_id: 'proj-hrms',
    todo_list_id: 'list-proj-hrms-default',
    title: 'HRMS Sena Bhawan payroll integration signoff',
    description: 'Verify employee salary matrix, tax deductions, and attendance punch sync.',
    status: 'IN_PROGRESS',
    priority: 'HIGH',
    start_date: '2026-09-05',
    due_date: new Date(Date.now() + 3 * 24 * 3600 * 1000).toISOString().split('T')[0],
    position: 0,
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 3 * 24 * 3600 * 1000).toISOString(),
    assignees: [
      {
        id: 'demo-user-owner',
        email: 'shivy@ajath.com',
        full_name: 'Shivy Narain',
        avatar_url: '',
      },
    ],
    comments_count: 0,
    subtasks_completed_count: 0,
    subtasks: [],
    labels: [{ id: 'lbl-3', organization_id: 'demo-org-acme', name: 'Client Gov', color: '#f59e0b' }],
  },
  {
    id: 'task-4',
    project_id: 'proj-bipl',
    todo_list_id: 'list-bipl-1',
    title: 'BIPL App production staging build verification',
    description: 'Test push notifications, socket reconnection, and offline cache synchronization.',
    status: 'NOT_STARTED',
    priority: 'HIGH',
    start_date: '2026-09-06',
    due_date: new Date(Date.now() + 4 * 24 * 3600 * 1000).toISOString().split('T')[0],
    position: 0,
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 2 * 24 * 3600 * 1000).toISOString(),
    assignees: [
      {
        id: 'demo-user-owner',
        email: 'shivy@ajath.com',
        full_name: 'Shivy Narain',
        avatar_url: '',
      },
      {
        id: 'user-pt',
        email: 'pradeep@ajath.com',
        full_name: 'Pradeep Tiwari',
        avatar_url: '',
      },
    ],
    comments_count: 1,
    subtasks_completed_count: 0,
    subtasks: [],
    labels: [{ id: 'lbl-4', organization_id: 'demo-org-acme', name: 'QA Test', color: '#8b5cf6' }],
  },
  {
    id: 'task-5',
    project_id: 'proj-veggie',
    todo_list_id: 'list-veg-1',
    title: 'Veggie- Pro seller app order dispatch live pass',
    description: 'Verify real-time driver allocation and geo-location tracking for daily orders.',
    status: 'COMPLETED',
    priority: 'MEDIUM',
    completed_at: new Date(Date.now() - 1 * 24 * 3600 * 1000).toISOString(),
    start_date: '2026-09-01',
    due_date: '2026-09-05',
    position: 0,
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 6 * 24 * 3600 * 1000).toISOString(),
    assignees: [
      {
        id: 'demo-user-owner',
        email: 'shivy@ajath.com',
        full_name: 'Shivy Narain',
        avatar_url: '',
      },
    ],
    comments_count: 0,
    subtasks_completed_count: 0,
    subtasks: [],
    labels: [{ id: 'lbl-2', organization_id: 'demo-org-acme', name: 'Mobile Flow', color: '#10b981' }],
  },
];

let MOCK_COMMENTS: TaskComment[] = [
  {
    id: 'cmt-1',
    task_id: 'task-1',
    user_id: 'demo-user-admin',
    content: 'Checked the Tailwind colors. The dark mode surface contrast looks great on OLED displays.',
    created_at: new Date(Date.now() - 2 * 3600 * 1000).toISOString(),
    profile: {
      id: 'demo-user-admin',
      email: 'sarah.admin@worksphere.io',
      full_name: 'Sarah Jenkins',
      avatar_url: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
    },
  },
];

let MOCK_LABELS: Label[] = [
  { id: 'lbl-1', organization_id: 'demo-org-acme', name: 'Design System', color: '#6366f1' },
  { id: 'lbl-2', organization_id: 'demo-org-acme', name: 'UI Layer', color: '#10b981' },
  { id: 'lbl-3', organization_id: 'demo-org-acme', name: 'Security', color: '#f59e0b' },
  { id: 'lbl-4', organization_id: 'demo-org-acme', name: 'Backend API', color: '#8b5cf6' },
];

function getActiveUserFullName(): string {
  try {
    const saved = localStorage.getItem('basecamp_user_profile');
    if (saved) {
      const p = JSON.parse(saved);
      if (p.full_name) return p.full_name;
    }
    const role = localStorage.getItem('worksphere_demo_role');
    if (role === 'CLIENT') return 'Claire Watson';
  } catch (e) {}
  return 'Claire Watson';
}

function normalizeTaskAssignees(task: Task): Task {
  const currentName = getActiveUserFullName();
  return {
    ...task,
    assignees: task.assignees?.map((a) => {
      if (
        a.id === 'demo-user-owner' ||
        a.id === 'demo-user-client' ||
        a.full_name === 'Shiv Narayan' ||
        a.full_name === 'Shivy Narain' ||
        (a.full_name && a.full_name.toLowerCase().includes('shiv'))
      ) {
        return { ...a, full_name: currentName, email: 'claire.client@partner.com' };
      }
      return a;
    }),
  };
}

export async function getTasks(projectId: string): Promise<Task[]> {
  if (!isSupabaseConfigured) {
    return MOCK_TASKS.filter((t) => t.project_id === projectId && !t.parent_task_id).map(normalizeTaskAssignees);
  }

  const { data, error } = await supabase
    .from('tasks')
    .select('*, assignees:task_assignees(*, profile:profiles(*)), comments:task_comments(*), subtasks:tasks!parent_task_id(*)')
    .eq('project_id', projectId)
    .is('parent_task_id', null)
    .order('position', { ascending: true });

  if (error || !data) return MOCK_TASKS.filter((t) => t.project_id === projectId && !t.parent_task_id).map(normalizeTaskAssignees);
  return data.map((t) => ({
    ...t,
    assignees: t.assignees?.map((a: any) => a.profile).filter(Boolean),
    comments_count: t.comments?.length || 0,
    subtasks_completed_count: t.subtasks?.filter((s: any) => s.status === 'COMPLETED').length || 0,
  })) as Task[];
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

export async function getUserTasks(
  userId: string,
  organizationId?: string,
  projectIds?: string[]
): Promise<Task[]> {
  const targetProjectIds = await resolveProjectIds(organizationId, projectIds);

  if (!isSupabaseConfigured) {
    return MOCK_TASKS.filter((t) => {
      if (targetProjectIds !== null && !targetProjectIds.has(t.project_id)) {
        return false;
      }
      return t.assignees?.some(
        (a) =>
          a.id === userId ||
          (userId === 'demo-user-owner' && a.id === 'demo-user-client') ||
          (userId === 'demo-user-client' && a.id === 'demo-user-owner')
      );
    }).map(normalizeTaskAssignees);
  }

  let query = supabase
    .from('task_assignees')
    .select('task:tasks(*, projects!inner(organization_id), assignees:task_assignees(*, profile:profiles(*)))')
    .eq('user_id', userId);

  if (targetProjectIds !== null) {
    if (targetProjectIds.size === 0) return [];
    query = query.in('task.project_id', Array.from(targetProjectIds));
  } else if (organizationId) {
    query = query.eq('task.projects.organization_id', organizationId);
  }

  const { data, error } = await query;

  if (error || !data) {
    return MOCK_TASKS.filter((t) => {
      if (targetProjectIds !== null && !targetProjectIds.has(t.project_id)) {
        return false;
      }
      return true;
    }).map(normalizeTaskAssignees);
  }
  return data.map((item: any) => item.task).filter(Boolean);
}

export async function getTotalOpenTasksCount(
  organizationId?: string,
  projectIds?: string[]
): Promise<number> {
  const targetProjectIds = await resolveProjectIds(organizationId, projectIds);

  if (!isSupabaseConfigured) {
    return MOCK_TASKS.filter((t) => {
      if (t.status === 'COMPLETED' || t.parent_task_id) return false;
      if (targetProjectIds !== null) {
        return targetProjectIds.has(t.project_id);
      }
      return true;
    }).length;
  }

  try {
    let query = supabase
      .from('tasks')
      .select('*, projects!inner(organization_id)', { count: 'exact', head: true })
      .neq('status', 'COMPLETED')
      .is('parent_task_id', null);

    if (targetProjectIds !== null) {
      if (targetProjectIds.size === 0) return 0;
      query = query.in('project_id', Array.from(targetProjectIds));
    } else if (organizationId) {
      query = query.eq('projects.organization_id', organizationId);
    }

    const { count, error } = await query;
    if (!error && count !== null) return count;
  } catch (e) {
    console.warn('Failed to fetch total open tasks count:', e);
  }

  return MOCK_TASKS.filter((t) => {
    if (t.status === 'COMPLETED' || t.parent_task_id) return false;
    if (targetProjectIds !== null) {
      return targetProjectIds.has(t.project_id);
    }
    return true;
  }).length;
}

export async function getUserAssignedByTasks(
  userId: string,
  organizationId?: string,
  projectIds?: string[]
): Promise<Task[]> {
  const targetProjectIds = await resolveProjectIds(organizationId, projectIds);

  if (!isSupabaseConfigured) {
    return MOCK_TASKS.filter((t) => {
      if (targetProjectIds !== null && !targetProjectIds.has(t.project_id)) {
        return false;
      }
      return (
        t.created_by === userId ||
        (userId === 'demo-user-owner' && (t.created_by === 'demo-user-client' || t.created_by === 'user-e')) ||
        t.assignees?.some((a) => a.id !== userId)
      );
    }).map(normalizeTaskAssignees);
  }

  let query = supabase
    .from('tasks')
    .select('*, projects!inner(organization_id), assignees:task_assignees(*, profile:profiles(*))')
    .eq('created_by', userId);

  if (targetProjectIds !== null) {
    if (targetProjectIds.size === 0) return [];
    query = query.in('project_id', Array.from(targetProjectIds));
  } else if (organizationId) {
    query = query.eq('projects.organization_id', organizationId);
  }

  const { data, error } = await query;

  if (error || !data) {
    return MOCK_TASKS.filter((t) => {
      if (targetProjectIds !== null && !targetProjectIds.has(t.project_id)) {
        return false;
      }
      return t.created_by === userId;
    }).map(normalizeTaskAssignees);
  }
  return data.map((t) => ({
    ...t,
    assignees: t.assignees?.map((a: any) => a.profile).filter(Boolean),
  })) as Task[];
}

export async function getTaskById(taskId: string): Promise<Task | null> {
  if (!isSupabaseConfigured) {
    const found = MOCK_TASKS.find((t) => t.id === taskId) || MOCK_TASKS[0];
    return found ? normalizeTaskAssignees(found) : null;
  }

  const { data, error } = await supabase
    .from('tasks')
    .select('*, assignees:task_assignees(*, profile:profiles(*)), comments:task_comments(*, profile:profiles(*)), subtasks:tasks!parent_task_id(*)')
    .eq('id', taskId)
    .single();

  if (error || !data) return null;
  return {
    ...data,
    assignees: data.assignees?.map((a: any) => a.profile).filter(Boolean),
    comments_count: data.comments?.length || 0,
    subtasks_completed_count: data.subtasks?.filter((s: any) => s.status === 'COMPLETED').length || 0,
  } as Task;
}

export function calculateNextRecurringDate(
  currentDateStr: string | undefined,
  frequency: 'DAILY' | 'WEEKLY' | 'MONTHLY' | 'CUSTOM',
  interval = 1
): string {
  const base = currentDateStr ? new Date(currentDateStr) : new Date();
  const next = new Date(base);
  if (isNaN(next.getTime())) {
    return new Date(Date.now() + 24 * 3600 * 1000).toISOString().split('T')[0];
  }

  const step = interval > 0 ? interval : 1;
  switch (frequency) {
    case 'DAILY':
      next.setDate(next.getDate() + step);
      break;
    case 'WEEKLY':
      next.setDate(next.getDate() + 7 * step);
      break;
    case 'MONTHLY':
      next.setMonth(next.getMonth() + step);
      break;
    case 'CUSTOM':
      next.setDate(next.getDate() + step);
      break;
  }
  return next.toISOString().split('T')[0];
}

export async function createTask(input: CreateTaskInput): Promise<{ data: Task | null; error: Error | null }> {
  if (!isSupabaseConfigured) {
    const newTask: Task = {
      id: `task-${Date.now()}`,
      project_id: input.project_id,
      todo_list_id: input.todo_list_id,
      parent_task_id: input.parent_task_id,
      kanban_column_id: input.kanban_column_id,
      kanban_position: input.kanban_position ?? 0,
      title: input.title,
      description: input.description,
      status: input.status || 'NOT_STARTED',
      priority: input.priority || 'MEDIUM',
      start_date: input.start_date,
      due_date: input.due_date,
      position: MOCK_TASKS.length,
      created_by: input.created_by,
      created_at: new Date().toISOString(),
      recurrence_pattern: input.recurrence_pattern,
      recurring_parent_id: input.recurring_parent_id,
      assignees: input.assignee_ids?.map((uid) => ({
        id: uid,
        email: uid.includes('sarah') ? 'sarah.admin@worksphere.io' : 'alex.owner@worksphere.io',
        full_name: uid.includes('sarah') ? 'Sarah Jenkins' : 'Alex Vance',
        avatar_url: uid.includes('sarah')
          ? 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80'
          : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
      })) || [],
      comments_count: 0,
      subtasks_completed_count: 0,
      subtasks: [],
    };
    MOCK_TASKS.push(newTask);

    // Trigger assignment notifications
    if (input.assignee_ids && input.assignee_ids.length > 0) {
      for (const uid of input.assignee_ids) {
        if (uid !== input.created_by) {
          await createNotification({
            user_id: uid,
            type: 'TASK_ASSIGNED',
            title: 'Task Assigned',
            body: `You were assigned to: ${input.title}`,
            link_url: `/projects/${input.project_id}/tasks`,
          });
        }
      }
    }

    await logActivity(
      '',
      input.created_by || 'demo-user-owner',
      'created task',
      input.title,
      input.project_id
    );

    return { data: newTask, error: null };
  }

  const { data, error } = await supabase
    .from('tasks')
    .insert([
      {
        project_id: input.project_id,
        todo_list_id: input.todo_list_id,
        parent_task_id: input.parent_task_id,
        kanban_column_id: input.kanban_column_id || null,
        kanban_position: input.kanban_position ?? 0,
        title: input.title,
        description: input.description,
        status: input.status || 'NOT_STARTED',
        priority: input.priority || 'MEDIUM',
        start_date: input.start_date,
        due_date: input.due_date,
        created_by: input.created_by,
        recurrence_pattern: input.recurrence_pattern,
        recurring_parent_id: input.recurring_parent_id,
      },
    ])
    .select()
    .single();

  if (error) return { data: null, error: error as Error };

  // Add assignees if provided
  if (input.assignee_ids && input.assignee_ids.length > 0) {
    const assigneeInserts = input.assignee_ids.map((uid) => ({
      task_id: data.id,
      user_id: uid,
      assigned_by: input.created_by,
    }));
    await supabase.from('task_assignees').insert(assigneeInserts);

    for (const uid of input.assignee_ids) {
      if (uid !== input.created_by) {
        await createNotification({
          user_id: uid,
          type: 'TASK_ASSIGNED',
          title: 'Task Assigned',
          body: `You were assigned to: ${input.title}`,
          link_url: `/projects/${input.project_id}/tasks`,
        });
      }
    }
  }

  await logActivity(
    '',
    input.created_by || 'demo-user-owner',
    'created task',
    input.title,
    input.project_id
  );

  return { data: data as Task, error: null };
}

export async function updateTask(taskId: string, updates: Partial<Task>): Promise<Task> {
  if (!isSupabaseConfigured) {
    let targetTask: Task | null = null;
    MOCK_TASKS = MOCK_TASKS.map((t) => {
      if (t.id === taskId) {
        targetTask = { ...t, ...updates, updated_at: new Date().toISOString() };
        return targetTask;
      }
      return t;
    });
    return targetTask || ({ id: taskId, ...updates } as Task);
  }

  const { data, error } = await supabase
    .from('tasks')
    .update({ ...updates, updated_at: new Date().toISOString() })
    .eq('id', taskId)
    .select()
    .single();

  if (error || !data) {
    throw error || new Error('Failed to update task');
  }

  return data as Task;
}

export async function completeTask(taskId: string): Promise<{ error: Error | null }> {
  const completedAt = new Date().toISOString();
  let existingTask: Task | null = null;

  if (!isSupabaseConfigured) {
    existingTask = MOCK_TASKS.find((t) => t.id === taskId) || null;
    MOCK_TASKS = MOCK_TASKS.map((t) =>
      t.id === taskId ? { ...t, status: 'COMPLETED', completed_at: completedAt, updated_at: completedAt } : t
    );
  } else {
    existingTask = await getTaskById(taskId);
    const { error } = await supabase
      .from('tasks')
      .update({ status: 'COMPLETED', completed_at: completedAt, updated_at: completedAt })
      .eq('id', taskId);

    if (error) return { error: error as Error | null };
  }

  if (existingTask) {
    // 1. Recurrence handling: Generate next recurring task instance
    if (existingTask.recurrence_pattern && existingTask.recurrence_pattern.frequency) {
      const nextDueDate = calculateNextRecurringDate(
        existingTask.due_date,
        existingTask.recurrence_pattern.frequency,
        existingTask.recurrence_pattern.interval
      );
      const nextStartDate = existingTask.start_date
        ? calculateNextRecurringDate(
            existingTask.start_date,
            existingTask.recurrence_pattern.frequency,
            existingTask.recurrence_pattern.interval
          )
        : undefined;

      await createTask({
        project_id: existingTask.project_id,
        todo_list_id: existingTask.todo_list_id,
        kanban_column_id: existingTask.kanban_column_id,
        title: existingTask.title,
        description: existingTask.description,
        priority: existingTask.priority,
        status: 'NOT_STARTED',
        start_date: nextStartDate,
        due_date: nextDueDate,
        created_by: existingTask.created_by,
        assignee_ids: existingTask.assignees?.map((a) => a.id),
        recurrence_pattern: existingTask.recurrence_pattern,
        recurring_parent_id: existingTask.recurring_parent_id || existingTask.id,
      });
    }

    // 2. Notifications: Notify watchers & creator
    const watchers = await getTaskWatchers(taskId);
    const notifyRecipients = new Set<string>();
    if (existingTask.created_by) notifyRecipients.add(existingTask.created_by);
    watchers.forEach((w: any) => {
      if (w.user_id) notifyRecipients.add(w.user_id);
    });

    for (const uid of notifyRecipients) {
      await createNotification({
        user_id: uid,
        type: 'TASK_COMPLETED',
        title: 'Task Completed',
        body: `"${existingTask.title}" was marked completed.`,
        link_url: `/projects/${existingTask.project_id}/tasks`,
      });
    }

    // 3. Activity Logging
    await logActivity(
      '',
      existingTask.created_by || 'demo-user-owner',
      'completed task',
      existingTask.title,
      existingTask.project_id
    );
  }

  return { error: null };
}

export async function reopenTask(taskId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    MOCK_TASKS = MOCK_TASKS.map((t) =>
      t.id === taskId ? { ...t, status: 'IN_PROGRESS', completed_at: undefined } : t
    );
    return { error: null };
  }

  const { error } = await supabase
    .from('tasks')
    .update({ status: 'IN_PROGRESS', completed_at: null, updated_at: new Date().toISOString() })
    .eq('id', taskId);

  return { error: error as Error | null };
}

export async function deleteTask(taskId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    MOCK_TASKS = MOCK_TASKS.filter((t) => t.id !== taskId);
    return { error: null };
  }

  const { error } = await supabase.from('tasks').delete().eq('id', taskId);
  return { error: error as Error | null };
}

export async function moveTask(taskId: string, targetTodoListId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    MOCK_TASKS = MOCK_TASKS.map((t) => (t.id === taskId ? { ...t, todo_list_id: targetTodoListId } : t));
    return { error: null };
  }

  const { error } = await supabase
    .from('tasks')
    .update({ todo_list_id: targetTodoListId, updated_at: new Date().toISOString() })
    .eq('id', taskId);

  return { error: error as Error | null };
}

// Subtasks
export async function createSubtask(
  parentTaskId: string,
  projectId: string,
  todoListId: string,
  title: string,
  createdBy?: string
): Promise<{ data: Task | null; error: Error | null }> {
  return createTask({
    project_id: projectId,
    todo_list_id: todoListId,
    parent_task_id: parentTaskId,
    title,
    created_by: createdBy,
  });
}

// Task Comments
export async function getTaskComments(taskId: string): Promise<TaskComment[]> {
  if (!isSupabaseConfigured) {
    return MOCK_COMMENTS.filter((c) => c.task_id === taskId);
  }

  const { data, error } = await supabase
    .from('task_comments')
    .select('*, profile:profiles(*)')
    .eq('task_id', taskId)
    .order('created_at', { ascending: true });

  if (error || !data) return MOCK_COMMENTS.filter((c) => c.task_id === taskId);
  return data as TaskComment[];
}

export async function addTaskComment(
  taskId: string,
  userId: string,
  content: string
): Promise<{ data: TaskComment | null; error: Error | null }> {
  let createdComment: TaskComment | null = null;
  let resError: Error | null = null;

  if (!isSupabaseConfigured) {
    createdComment = {
      id: `cmt-${Date.now()}`,
      task_id: taskId,
      user_id: userId,
      content,
      created_at: new Date().toISOString(),
      profile: {
        id: userId,
        email: userId.includes('sarah') ? 'sarah.admin@worksphere.io' : 'alex.owner@worksphere.io',
        full_name: userId.includes('sarah') ? 'Sarah Jenkins' : 'Alex Vance',
      },
    };
    MOCK_COMMENTS.push(createdComment);
  } else {
    const { data, error } = await supabase
      .from('task_comments')
      .insert([{ task_id: taskId, user_id: userId, content }])
      .select('*, profile:profiles(*)')
      .single();

    createdComment = data as TaskComment;
    resError = error as Error | null;
  }

  if (createdComment) {
    const targetTask = !isSupabaseConfigured
      ? MOCK_TASKS.find((t) => t.id === taskId)
      : await getTaskById(taskId);

    if (targetTask) {
      const recipients = new Set<string>();
      if (targetTask.created_by && targetTask.created_by !== userId) {
        recipients.add(targetTask.created_by);
      }
      targetTask.assignees?.forEach((a) => {
        if (a.id && a.id !== userId) recipients.add(a.id);
      });

      for (const recId of recipients) {
        await createNotification({
          user_id: recId,
          type: 'TASK_COMMENTED',
          title: 'New Task Comment',
          body: `Comment on "${targetTask.title}": ${content.slice(0, 60)}`,
          link_url: `/projects/${targetTask.project_id}/tasks`,
        });
      }

      await logActivity(
        '',
        userId,
        'commented on task',
        targetTask.title,
        targetTask.project_id
      );
    }
  }

  return { data: createdComment, error: resError };
}

export async function deleteTaskComment(commentId: string): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    MOCK_COMMENTS = MOCK_COMMENTS.filter((c) => c.id !== commentId);
    return { error: null };
  }

  const { error } = await supabase.from('task_comments').delete().eq('id', commentId);
  return { error: error as Error | null };
}

// Labels
export async function getOrganizationLabels(orgId: string): Promise<Label[]> {
  if (!isSupabaseConfigured) {
    return MOCK_LABELS.filter((l) => l.organization_id === orgId);
  }

  const { data, error } = await supabase
    .from('labels')
    .select('*')
    .eq('organization_id', orgId);

  if (error || !data) return MOCK_LABELS;
  return data as Label[];
}

// Task Attachments, Watchers, Reminders Mock Store
let MOCK_TASK_ATTACHMENTS: any[] = [];
let MOCK_TASK_WATCHERS: any[] = [];
let MOCK_TASK_REMINDERS: any[] = [];

export async function getTaskAttachments(taskId: string) {
  if (!isSupabaseConfigured) {
    return MOCK_TASK_ATTACHMENTS.filter((a) => a.task_id === taskId);
  }
  const { data, error } = await supabase
    .from('task_attachments')
    .select('*, profile:profiles(*)')
    .eq('task_id', taskId);
  if (error || !data) return MOCK_TASK_ATTACHMENTS.filter((a) => a.task_id === taskId);
  return data;
}

export async function addTaskAttachment(input: {
  task_id: string;
  file_name: string;
  file_url: string;
  file_size?: number;
  file_type?: string;
  uploaded_by?: string;
}) {
  if (!isSupabaseConfigured) {
    const newAtt = {
      id: `att-${Date.now()}`,
      ...input,
      created_at: new Date().toISOString(),
    };
    MOCK_TASK_ATTACHMENTS.push(newAtt);
    return { data: newAtt, error: null };
  }
  const { data, error } = await supabase
    .from('task_attachments')
    .insert([input])
    .select('*, profile:profiles(*)')
    .single();
  return { data, error };
}

export async function deleteTaskAttachment(attachmentId: string) {
  if (!isSupabaseConfigured) {
    MOCK_TASK_ATTACHMENTS = MOCK_TASK_ATTACHMENTS.filter((a) => a.id !== attachmentId);
    return { error: null };
  }
  const { error } = await supabase.from('task_attachments').delete().eq('id', attachmentId);
  return { error };
}

export async function getTaskWatchers(taskId: string) {
  if (!isSupabaseConfigured) {
    return MOCK_TASK_WATCHERS.filter((w) => w.task_id === taskId);
  }
  const { data, error } = await supabase
    .from('task_watchers')
    .select('*, profile:profiles(*)')
    .eq('task_id', taskId);
  if (error || !data) return MOCK_TASK_WATCHERS.filter((w) => w.task_id === taskId);
  return data;
}

export async function toggleTaskWatcher(taskId: string, userId: string) {
  if (!isSupabaseConfigured) {
    const existingIndex = MOCK_TASK_WATCHERS.findIndex((w) => w.task_id === taskId && w.user_id === userId);
    if (existingIndex >= 0) {
      MOCK_TASK_WATCHERS.splice(existingIndex, 1);
      return { watching: false, error: null };
    } else {
      MOCK_TASK_WATCHERS.push({ id: `tw-${Date.now()}`, task_id: taskId, user_id: userId, created_at: new Date().toISOString() });
      return { watching: true, error: null };
    }
  }

  const { data: existing } = await supabase
    .from('task_watchers')
    .select('id')
    .eq('task_id', taskId)
    .eq('user_id', userId)
    .maybeSingle();

  if (existing) {
    await supabase.from('task_watchers').delete().eq('id', existing.id);
    return { watching: false, error: null };
  } else {
    await supabase.from('task_watchers').insert([{ task_id: taskId, user_id: userId }]);
    return { watching: true, error: null };
  }
}

export async function getTaskReminders(taskId: string) {
  if (!isSupabaseConfigured) {
    return MOCK_TASK_REMINDERS.filter((r) => r.task_id === taskId);
  }
  const { data, error } = await supabase
    .from('task_reminders')
    .select('*')
    .eq('task_id', taskId);
  if (error || !data) return MOCK_TASK_REMINDERS.filter((r) => r.task_id === taskId);
  return data;
}

export async function createTaskReminder(taskId: string, userId: string, remindAt: string) {
  if (!isSupabaseConfigured) {
    const newRem = {
      id: `rem-${Date.now()}`,
      task_id: taskId,
      user_id: userId,
      remind_at: remindAt,
      is_sent: false,
      created_at: new Date().toISOString(),
    };
    MOCK_TASK_REMINDERS.push(newRem);
    return { data: newRem, error: null };
  }
  const { data, error } = await supabase
    .from('task_reminders')
    .insert([{ task_id: taskId, user_id: userId, remind_at: remindAt }])
    .select()
    .single();
  return { data, error };
}

export async function deleteTaskReminder(reminderId: string) {
  if (!isSupabaseConfigured) {
    MOCK_TASK_REMINDERS = MOCK_TASK_REMINDERS.filter((r) => r.id !== reminderId);
    return { error: null };
  }
  const { error } = await supabase.from('task_reminders').delete().eq('id', reminderId);
  return { error };
}

// Task Dependencies
export interface TaskDependencyRecord {
  id: string;
  task_id: string;
  depends_on_task_id: string;
  created_at: string;
}

let MOCK_TASK_DEPENDENCIES: TaskDependencyRecord[] = [
  { id: 'dep-1', task_id: 'task-3', depends_on_task_id: 'task-1', created_at: new Date().toISOString() },
];

export async function getTaskDependencies(taskId: string): Promise<{ blocked_by: Task[]; blocking: Task[] }> {
  if (!isSupabaseConfigured) {
    const blockedByIds = MOCK_TASK_DEPENDENCIES.filter((d) => d.task_id === taskId).map((d) => d.depends_on_task_id);
    const blockingIds = MOCK_TASK_DEPENDENCIES.filter((d) => d.depends_on_task_id === taskId).map((d) => d.task_id);

    const blocked_by = MOCK_TASKS.filter((t) => blockedByIds.includes(t.id));
    const blocking = MOCK_TASKS.filter((t) => blockingIds.includes(t.id));
    return { blocked_by, blocking };
  }

  try {
    const [blockedByRes, blockingRes] = await Promise.all([
      supabase.from('task_dependencies').select('depends_on_task:tasks!depends_on_task_id(*)').eq('task_id', taskId),
      supabase.from('task_dependencies').select('task:tasks!task_id(*)').eq('depends_on_task_id', taskId),
    ]);

    const blocked_by = (blockedByRes.data?.map((r: any) => r.depends_on_task).filter(Boolean) || []) as Task[];
    const blocking = (blockingRes.data?.map((r: any) => r.task).filter(Boolean) || []) as Task[];
    return { blocked_by, blocking };
  } catch (e) {
    console.warn('Failed to get task dependencies via Supabase:', e);
    return { blocked_by: [], blocking: [] };
  }
}

export async function addTaskDependency(
  taskId: string,
  dependsOnTaskId: string
): Promise<{ error: Error | null }> {
  if (taskId === dependsOnTaskId) {
    return { error: new Error('A task cannot depend on itself.') };
  }

  // Circular dependency cycle detection: check if dependsOnTaskId already depends on taskId
  const visited = new Set<string>();
  async function hasPath(fromId: string, toId: string): Promise<boolean> {
    if (fromId === toId) return true;
    if (visited.has(fromId)) return false;
    visited.add(fromId);

    let nextDeps: string[] = [];
    if (!isSupabaseConfigured) {
      nextDeps = MOCK_TASK_DEPENDENCIES.filter((d) => d.task_id === fromId).map((d) => d.depends_on_task_id);
    } else {
      const { data } = await supabase.from('task_dependencies').select('depends_on_task_id').eq('task_id', fromId);
      nextDeps = data?.map((d: any) => d.depends_on_task_id) || [];
    }

    for (const nextId of nextDeps) {
      if (await hasPath(nextId, toId)) return true;
    }
    return false;
  }

  const causesCycle = await hasPath(dependsOnTaskId, taskId);
  if (causesCycle) {
    return { error: new Error('Cannot add dependency: this would create a circular dependency loop.') };
  }

  if (!isSupabaseConfigured) {
    const exists = MOCK_TASK_DEPENDENCIES.some(
      (d) => d.task_id === taskId && d.depends_on_task_id === dependsOnTaskId
    );
    if (!exists) {
      MOCK_TASK_DEPENDENCIES.push({
        id: `dep-${Date.now()}`,
        task_id: taskId,
        depends_on_task_id: dependsOnTaskId,
        created_at: new Date().toISOString(),
      });
    }
    return { error: null };
  }

  const { error } = await supabase.from('task_dependencies').insert([
    { task_id: taskId, depends_on_task_id: dependsOnTaskId }
  ]);
  return { error: error as Error | null };
}

export async function removeTaskDependency(
  taskId: string,
  dependsOnTaskId: string
): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    MOCK_TASK_DEPENDENCIES = MOCK_TASK_DEPENDENCIES.filter(
      (d) => !(d.task_id === taskId && d.depends_on_task_id === dependsOnTaskId)
    );
    return { error: null };
  }

  const { error } = await supabase
    .from('task_dependencies')
    .delete()
    .eq('task_id', taskId)
    .eq('depends_on_task_id', dependsOnTaskId);

  return { error: error as Error | null };
}

