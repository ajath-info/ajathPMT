import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { TodoList, TodoGroup, CreateTodoListInput, OrgRole } from '../types';
import { updateTask } from './taskService';

let MOCK_TODO_LISTS: TodoList[] = [
  {
    id: 'list-hq-1',
    project_id: 'proj-hq',
    title: 'Company Operations & HR',
    description: 'General company policies, announcements, and team onboarding.',
    position: 0,
    created_at: new Date(Date.now() - 10 * 24 * 3600 * 1000).toISOString(),
  },
  {
    id: 'list-hq-2',
    project_id: 'proj-hq',
    title: 'Infrastructure & Engineering Standards',
    description: 'DevOps, CI/CD, cloud architecture, and security reviews.',
    position: 1,
    created_at: new Date(Date.now() - 8 * 24 * 3600 * 1000).toISOString(),
  },
  {
    id: 'list-rmc-1',
    project_id: 'proj-rmc',
    title: 'Dependency Checklist',
    description: 'Domain credentials, email mapping, and SSL certificates.',
    position: 0,
    created_at: new Date(Date.now() - 7 * 24 * 3600 * 1000).toISOString(),
  },
  {
    id: 'list-rmc-2',
    project_id: 'proj-rmc',
    title: 'Vehicle Inspection Checklist',
    description: 'Photo upload, damages checklist, and inspection signoff.',
    position: 1,
    created_at: new Date(Date.now() - 5 * 24 * 3600 * 1000).toISOString(),
  },
  {
    id: 'list-bipl-1',
    project_id: 'proj-bipl',
    title: 'Mobile App Development Sprint',
    description: 'Feature implementation, QA test cases, and release builds.',
    position: 0,
    created_at: new Date(Date.now() - 6 * 24 * 3600 * 1000).toISOString(),
  },
  {
    id: 'list-veg-1',
    project_id: 'proj-veggie',
    title: 'Seller & Driver App Releases',
    description: 'Catalog management, order dispatch, and live route tracking.',
    position: 0,
    created_at: new Date(Date.now() - 4 * 24 * 3600 * 1000).toISOString(),
  },
];

let MOCK_TODO_GROUPS: TodoGroup[] = [
  {
    id: 'grp-1',
    todo_list_id: 'list-hq-1',
    title: 'Phase 1: Foundation & Specs',
    position: 0,
    created_at: new Date(Date.now() - 9 * 24 * 3600 * 1000).toISOString(),
  },
  {
    id: 'grp-2',
    todo_list_id: 'list-hq-1',
    title: 'Phase 2: Execution & Validation',
    position: 1,
    created_at: new Date(Date.now() - 8 * 24 * 3600 * 1000).toISOString(),
  },
];

export async function getTodoLists(projectId: string): Promise<TodoList[]> {
  if (!isSupabaseConfigured) {
    let lists = MOCK_TODO_LISTS.filter((l) => l.project_id === projectId);
    if (lists.length === 0) {
      const defaultList: TodoList = {
        id: `list-${projectId}-default`,
        project_id: projectId,
        title: 'General To-Dos',
        description: 'Action items and deliverables for this project workspace.',
        position: 0,
        created_at: new Date().toISOString(),
      };
      MOCK_TODO_LISTS.push(defaultList);
      lists = [defaultList];
    }
    return lists;
  }

  const { data, error } = await supabase
    .from('todo_lists')
    .select('*')
    .eq('project_id', projectId)
    .order('position', { ascending: true });

  if (error || !data) return MOCK_TODO_LISTS.filter((l) => l.project_id === projectId);
  return data as TodoList[];
}

export async function createTodoList(input: CreateTodoListInput): Promise<{ data: TodoList | null; error: Error | null }> {
  if (!isSupabaseConfigured) {
    const newList: TodoList = {
      id: `list-${Date.now()}`,
      project_id: input.project_id,
      title: input.title,
      description: input.description,
      position: MOCK_TODO_LISTS.length,
      created_by: input.created_by,
      created_at: new Date().toISOString(),
    };
    MOCK_TODO_LISTS.push(newList);
    return { data: newList, error: null };
  }

  const { data, error } = await supabase
    .from('todo_lists')
    .insert([
      {
        project_id: input.project_id,
        title: input.title,
        description: input.description,
        created_by: input.created_by,
      },
    ])
    .select()
    .single();

  return { data: data as TodoList, error: error as Error | null };
}

export async function updateTodoList(
  todoListId: string,
  updates: Partial<TodoList>
): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    MOCK_TODO_LISTS = MOCK_TODO_LISTS.map((l) => (l.id === todoListId ? { ...l, ...updates } : l));
    return { error: null };
  }

  const { error } = await supabase
    .from('todo_lists')
    .update({ ...updates, updated_at: new Date().toISOString() })
    .eq('id', todoListId);

  return { error: error as Error | null };
}

export async function deleteTodoList(todoListId: string, userRole?: OrgRole): Promise<{ error: Error | null }> {
  if (userRole === 'CLIENT') {
    return { error: new Error('Unauthorized: Client accounts cannot delete to-do lists.') };
  }

  if (!isSupabaseConfigured) {
    MOCK_TODO_LISTS = MOCK_TODO_LISTS.filter((l) => l.id !== todoListId);
    return { error: null };
  }

  const { error } = await supabase.from('todo_lists').delete().eq('id', todoListId);
  return { error: error as Error | null };
}

export async function reorderTodoLists(
  projectId: string,
  orderedIds: string[]
): Promise<{ error: Error | null }> {
  if (!isSupabaseConfigured) {
    MOCK_TODO_LISTS = MOCK_TODO_LISTS.map((l) => {
      const idx = orderedIds.indexOf(l.id);
      return idx !== -1 ? { ...l, position: idx } : l;
    }).sort((a, b) => a.position - b.position);
    return { error: null };
  }

  const updates = orderedIds.map((id, index) =>
    supabase.from('todo_lists').update({ position: index }).eq('id', id)
  );
  await Promise.all(updates);
  return { error: null };
}

/**
 * Basecamp 4 To-Do Groups (Grouping within lists)
 */
export async function getTodoGroups(todoListId: string): Promise<TodoGroup[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('todo_groups')
        .select('*')
        .eq('todo_list_id', todoListId)
        .order('position', { ascending: true });

      if (!error && data) return data as TodoGroup[];
    } catch (e) {
      console.warn('Failed to fetch todo groups via Supabase:', e);
    }
  }

  return MOCK_TODO_GROUPS.filter((g) => g.todo_list_id === todoListId).sort((a, b) => a.position - b.position);
}

export async function createTodoGroup(todoListId: string, title: string): Promise<TodoGroup> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('todo_groups')
        .insert([
          {
            todo_list_id: todoListId,
            title: title.trim(),
            position: 99,
          },
        ])
        .select('*')
        .single();

      if (!error && data) return data as TodoGroup;
    } catch (e) {
      console.warn('Failed to create todo group via Supabase:', e);
    }
  }

  const newGroup: TodoGroup = {
    id: `grp-${Date.now()}`,
    todo_list_id: todoListId,
    title: title.trim(),
    position: MOCK_TODO_GROUPS.filter((g) => g.todo_list_id === todoListId).length,
    created_at: new Date().toISOString(),
  };
  MOCK_TODO_GROUPS.push(newGroup);
  return newGroup;
}

export async function updateTodoGroup(groupId: string, title: string): Promise<TodoGroup | null> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('todo_groups')
        .update({ title: title.trim(), updated_at: new Date().toISOString() })
        .eq('id', groupId)
        .select('*')
        .single();

      if (!error && data) return data as TodoGroup;
    } catch (e) {
      console.warn('Failed to update todo group via Supabase:', e);
    }
  }

  const target = MOCK_TODO_GROUPS.find((g) => g.id === groupId);
  if (target) {
    target.title = title.trim();
    target.updated_at = new Date().toISOString();
    return target;
  }
  return null;
}

/**
 * In Basecamp 4, deleting a group does NOT delete the tasks; it ungroups them (sets todo_group_id = null)
 */
export async function deleteTodoGroup(groupId: string): Promise<void> {
  if (isSupabaseConfigured) {
    try {
      // First ungroup tasks
      await supabase.from('tasks').update({ todo_group_id: null }).eq('todo_group_id', groupId);
      // Then delete group
      await supabase.from('todo_groups').delete().eq('id', groupId);
      return;
    } catch (e) {
      console.warn('Failed to delete todo group via Supabase:', e);
    }
  }

  MOCK_TODO_GROUPS = MOCK_TODO_GROUPS.filter((g) => g.id !== groupId);
}

export async function assignTaskToGroup(taskId: string, groupId: string | null): Promise<void> {
  await updateTask(taskId, { todo_group_id: groupId || undefined });
}
