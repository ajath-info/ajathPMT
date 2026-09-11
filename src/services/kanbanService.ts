import { supabase, isSupabaseConfigured } from '../lib/supabase';
import {
  KanbanColumn,
  CreateKanbanColumnInput,
  UpdateKanbanColumnInput,
  Task,
  TaskStatusType,
} from '../types';
import { logActivity } from './organizationService';
import { getTasks, updateTask } from './taskService';

// Fallback in-memory store for default columns per project
let MOCK_KANBAN_COLUMNS: KanbanColumn[] = [
  {
    id: 'col-backlog',
    project_id: 'proj-1',
    name: 'Backlog',
    description: 'Ideas and unprioritized features',
    position: 0,
    color: '#64748b',
    mapped_status: 'NOT_STARTED',
    is_default: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'col-todo',
    project_id: 'proj-1',
    name: 'To Do',
    description: 'Tasks ready to be worked on',
    position: 1,
    color: '#3b82f6',
    mapped_status: 'NOT_STARTED',
    is_default: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'col-in-progress',
    project_id: 'proj-1',
    name: 'In Progress',
    description: 'Tasks currently actively being developed',
    position: 2,
    color: '#f59e0b',
    mapped_status: 'IN_PROGRESS',
    is_default: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'col-review',
    project_id: 'proj-1',
    name: 'Review',
    description: 'Code review or client signoff',
    position: 3,
    color: '#8b5cf6',
    mapped_status: 'IN_REVIEW',
    is_default: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'col-done',
    project_id: 'proj-1',
    name: 'Done',
    description: 'Completed deliverables',
    position: 4,
    color: '#10b981',
    mapped_status: 'COMPLETED',
    is_default: true,
    created_at: new Date().toISOString(),
  },
];

const DEFAULT_COLUMN_TEMPLATES = [
  { name: 'Triage', description: 'Incoming cards waiting to be reviewed & prioritized', color: '#6366f1', mapped_status: 'NOT_STARTED' as TaskStatusType, is_triage: true },
  { name: 'To Do', description: 'Tasks ready to be worked on', color: '#3b82f6', mapped_status: 'NOT_STARTED' as TaskStatusType, is_triage: false },
  { name: 'In Progress', description: 'Tasks currently actively being developed', color: '#f59e0b', mapped_status: 'IN_PROGRESS' as TaskStatusType, is_triage: false },
  { name: 'Review', description: 'Code review or client signoff', color: '#8b5cf6', mapped_status: 'IN_REVIEW' as TaskStatusType, is_triage: false },
  { name: 'Done', description: 'Completed deliverables', color: '#10b981', mapped_status: 'COMPLETED' as TaskStatusType, is_triage: false },
];

/**
 * Initialize default workflow columns for a project if none exist
 */
export async function initializeDefaultKanbanColumns(projectId: string): Promise<KanbanColumn[]> {
  if (isSupabaseConfigured) {
    try {
      const { data: existing } = await supabase
        .from('kanban_columns')
        .select('*')
        .eq('project_id', projectId);

      if (existing && existing.length > 0) {
        return existing as KanbanColumn[];
      }

      const rowsToInsert = DEFAULT_COLUMN_TEMPLATES.map((tmpl, idx) => ({
        project_id: projectId,
        name: tmpl.name,
        description: tmpl.description,
        position: idx,
        color: tmpl.color,
        mapped_status: tmpl.mapped_status,
        is_default: true,
      }));

      const { data: created, error } = await supabase
        .from('kanban_columns')
        .insert(rowsToInsert)
        .select();

      if (error) {
        console.warn('Error seeding default columns via Supabase:', error);
      } else if (created) {
        return created as KanbanColumn[];
      }
    } catch (e) {
      console.warn('Supabase default columns error:', e);
    }
  }

  // Fallback in-memory initialization
  const projCols = MOCK_KANBAN_COLUMNS.filter((c) => c.project_id === projectId);
  if (projCols.length === 0) {
    const createdCols: KanbanColumn[] = DEFAULT_COLUMN_TEMPLATES.map((tmpl, idx) => ({
      id: `col-${projectId}-${idx}-${Date.now()}`,
      project_id: projectId,
      name: tmpl.name,
      description: tmpl.description,
      position: idx,
      color: tmpl.color,
      mapped_status: tmpl.mapped_status,
      is_default: true,
      created_at: new Date().toISOString(),
    }));
    MOCK_KANBAN_COLUMNS.push(...createdCols);
    return createdCols;
  }
  return projCols;
}

/**
 * Fetch all Kanban columns for a project ordered by position
 */
export async function getKanbanColumns(projectId: string): Promise<KanbanColumn[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('kanban_columns')
        .select('*')
        .eq('project_id', projectId)
        .order('position', { ascending: true });

      if (!error && data) {
        if (data.length === 0) {
          return await initializeDefaultKanbanColumns(projectId);
        }
        return data as KanbanColumn[];
      }
    } catch (e) {
      console.warn('Failed to fetch kanban columns via Supabase:', e);
    }
  }

  const projCols = MOCK_KANBAN_COLUMNS.filter((c) => c.project_id === projectId).sort(
    (a, b) => a.position - b.position
  );
  if (projCols.length === 0) {
    return await initializeDefaultKanbanColumns(projectId);
  }
  return projCols;
}

/**
 * Create a new Kanban column
 */
export async function createKanbanColumn(input: CreateKanbanColumnInput): Promise<KanbanColumn> {
  const existingCols = await getKanbanColumns(input.project_id);
  const nextPos = existingCols.length;

  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('kanban_columns')
        .insert([
          {
            project_id: input.project_id,
            name: input.name.trim(),
            description: input.description?.trim() || null,
            color: input.color || '#64748b',
            mapped_status: input.mapped_status || null,
            position: nextPos,
            created_by: input.created_by || null,
          },
        ])
        .select()
        .single();

      if (!error && data) {
        await logActivity(
          '',
          input.created_by || 'demo-user-owner',
          'Kanban Column Created',
          data.name,
          input.project_id
        );
        return data as KanbanColumn;
      }
    } catch (e) {
      console.warn('Error creating column via Supabase:', e);
    }
  }

  const newCol: KanbanColumn = {
    id: `col-${Date.now()}`,
    project_id: input.project_id,
    name: input.name.trim(),
    description: input.description?.trim(),
    position: nextPos,
    color: input.color || '#64748b',
    mapped_status: input.mapped_status,
    created_by: input.created_by,
    created_at: new Date().toISOString(),
  };
  MOCK_KANBAN_COLUMNS.push(newCol);
  await logActivity(
    '',
    input.created_by || 'demo-user-owner',
    'Kanban Column Created',
    newCol.name,
    input.project_id
  );
  return newCol;
}

/**
 * Update an existing Kanban column
 */
export async function updateKanbanColumn(
  columnId: string,
  input: UpdateKanbanColumnInput
): Promise<KanbanColumn> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('kanban_columns')
        .update({
          ...(input.name !== undefined && { name: input.name.trim() }),
          ...(input.description !== undefined && { description: input.description.trim() || null }),
          ...(input.color !== undefined && { color: input.color }),
          ...(input.mapped_status !== undefined && { mapped_status: input.mapped_status }),
          ...(input.position !== undefined && { position: input.position }),
          updated_at: new Date().toISOString(),
        })
        .eq('id', columnId)
        .select()
        .single();

      if (!error && data) {
        return data as KanbanColumn;
      }
    } catch (e) {
      console.warn('Error updating column via Supabase:', e);
    }
  }

  const idx = MOCK_KANBAN_COLUMNS.findIndex((c) => c.id === columnId);
  if (idx !== -1) {
    MOCK_KANBAN_COLUMNS[idx] = {
      ...MOCK_KANBAN_COLUMNS[idx],
      ...input,
      updated_at: new Date().toISOString(),
    };
    return MOCK_KANBAN_COLUMNS[idx];
  }
  throw new Error('Kanban column not found');
}

/**
 * Delete a column, optionally migrating tasks to another target column
 */
export async function deleteKanbanColumn(
  columnId: string,
  targetColumnIdForTasks?: string
): Promise<void> {
  // First update tasks if target column specified
  if (targetColumnIdForTasks) {
    if (isSupabaseConfigured) {
      await supabase
        .from('tasks')
        .update({ kanban_column_id: targetColumnIdForTasks })
        .eq('kanban_column_id', columnId);
    }
  }

  if (isSupabaseConfigured) {
    try {
      const { error } = await supabase.from('kanban_columns').delete().eq('id', columnId);
      if (error) console.warn('Supabase delete column error:', error);
    } catch (e) {
      console.warn('Error deleting column:', e);
    }
  }

  MOCK_KANBAN_COLUMNS = MOCK_KANBAN_COLUMNS.filter((c) => c.id !== columnId);
}

/**
 * Reorder column positions
 */
export async function reorderKanbanColumns(
  projectId: string,
  orderedColumnIds: string[]
): Promise<void> {
  if (isSupabaseConfigured) {
    try {
      const updates = orderedColumnIds.map((id, index) =>
        supabase.from('kanban_columns').update({ position: index }).eq('id', id)
      );
      await Promise.all(updates);
    } catch (e) {
      console.warn('Supabase reorder columns error:', e);
    }
  }

  orderedColumnIds.forEach((id, index) => {
    const col = MOCK_KANBAN_COLUMNS.find((c) => c.id === id);
    if (col) {
      col.position = index;
    }
  });
}

/**
 * Fetch all tasks for project and automatically assign default kanban_column_id if unassigned
 */
export async function getBoardTasks(projectId: string): Promise<Task[]> {
  const columns = await getKanbanColumns(projectId);
  const tasks = await getTasks(projectId);

  // Auto-map tasks without kanban_column_id to matching columns based on status or first column
  const mappedTasks = tasks.map((t, idx) => {
    if (!t.kanban_column_id) {
      // Find column matching mapped_status
      const matchingCol =
        columns.find((c) => c.mapped_status === t.status) || columns[0];
      return {
        ...t,
        kanban_column_id: matchingCol?.id || columns[0]?.id,
        kanban_position: t.position ?? idx,
      };
    }
    return t;
  });

  return mappedTasks;
}

/**
 * Move task to a target column and position, updating task status if column maps to a status
 */
export async function moveTaskToColumn(
  taskId: string,
  targetColumnId: string,
  newPosition: number,
  targetColumnMappedStatus?: TaskStatusType
): Promise<Task> {
  const updatePayload: Partial<Task> = {
    kanban_column_id: targetColumnId,
    kanban_position: newPosition,
  };

  if (targetColumnMappedStatus) {
    updatePayload.status = targetColumnMappedStatus;
    if (targetColumnMappedStatus === 'COMPLETED') {
      updatePayload.completed_at = new Date().toISOString();
    } else {
      updatePayload.completed_at = undefined;
    }
  }

  const updatedTask = await updateTask(taskId, updatePayload as any);

  // Log activity
  if (updatedTask) {
    const colName = (await getKanbanColumns(updatedTask.project_id)).find(
      (c) => c.id === targetColumnId
    )?.name;
    await logActivity(
      '',
      updatedTask.created_by || 'demo-user-owner',
      'Moved Task Card',
      `Moved "${updatedTask.title}" to ${colName || 'column'}`,
      updatedTask.project_id
    );
  }

  return updatedTask;
}

/**
 * Reorder tasks within a specific column
 */
export async function reorderTasksInColumn(
  columnId: string,
  orderedTaskIds: string[]
): Promise<void> {
  if (isSupabaseConfigured) {
    try {
      const updates = orderedTaskIds.map((id, index) =>
        supabase
          .from('tasks')
          .update({ kanban_position: index, kanban_column_id: columnId })
          .eq('id', id)
      );
      await Promise.all(updates);
    } catch (e) {
      console.warn('Error reordering tasks via Supabase:', e);
    }
  }

  orderedTaskIds.forEach((id, index) => {
    updateTask(id, { kanban_column_id: columnId, kanban_position: index } as any);
  });
}

/**
 * Basecamp 4 Card Table Triage
 */
export async function getTriageColumn(projectId: string): Promise<KanbanColumn | null> {
  const cols = await getKanbanColumns(projectId);
  const triage = cols.find((c) => c.is_triage || c.name.toLowerCase() === 'triage');
  return triage || null;
}

export async function moveCardToTriage(taskId: string, projectId: string): Promise<Task> {
  let triageCol = await getTriageColumn(projectId);
  if (!triageCol) {
    const cols = await initializeDefaultKanbanColumns(projectId);
    triageCol = cols.find((c) => c.is_triage) || cols[0];
  }
  return updateTask(taskId, {
    kanban_column_id: triageCol.id,
    is_triage: true,
  } as any);
}
