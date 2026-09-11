import React, { useState, useEffect, useMemo } from 'react';
import { useParams } from 'react-router-dom';
import {
  DndContext,
  DragOverlay,
  closestCorners,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
  DragStartEvent,
  DragOverEvent,
  DragEndEvent,
} from '@dnd-kit/core';
import { arrayMove, sortableKeyboardCoordinates, SortableContext, horizontalListSortingStrategy } from '@dnd-kit/sortable';
import { Layout, Plus, Filter, RefreshCw, Sparkles, CheckSquare } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { KanbanColumn, Task, BoardFilter, Profile, Label, TodoList } from '../../types';
import {
  getKanbanColumns,
  getBoardTasks,
  moveTaskToColumn,
  reorderKanbanColumns,
  reorderTasksInColumn,
  createKanbanColumn,
} from '../../services/kanbanService';
import { getProjectMembers } from '../../services/projectService';
import { getTodoLists } from '../../services/todoService';
import { createTask, getTasks } from '../../services/taskService';
import { can } from '../../lib/permissions';
import { supabase, isSupabaseConfigured } from '../../lib/supabase';

import { KanbanColumnComponent } from '../../components/kanban/KanbanColumnComponent';
import { KanbanCard } from '../../components/kanban/KanbanCard';
import { KanbanFilterBar } from '../../components/kanban/KanbanFilterBar';
import { CreateKanbanColumnModal } from '../../components/kanban/CreateKanbanColumnModal';
import { DeleteKanbanColumnModal } from '../../components/kanban/DeleteKanbanColumnModal';
import { TaskDetailModal } from '../../components/todos/TaskDetailModal';
import { CreateTaskModal } from '../../components/todos/CreateTaskModal';
import { Button } from '../../components/common/Button';
import { Badge } from '../../components/common/Badge';

import { useProject } from '../../context/ProjectContext';

export function ProjectKanbanPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const { projects } = useProject();
  const { user, userRole } = useAuth();

  const [selectedProjectId, setSelectedProjectId] = useState<string>('');
  const targetProjectId = projectId || selectedProjectId || projects[0]?.id || '';

  const [columns, setColumns] = useState<KanbanColumn[]>([]);
  const [tasks, setTasks] = useState<Task[]>([]);
  const [members, setMembers] = useState<Profile[]>([]);
  const [todoLists, setTodoLists] = useState<TodoList[]>([]);
  const [loading, setLoading] = useState(true);

  // Filters & Search
  const [filters, setFilters] = useState<BoardFilter>({});

  // Active Drag state
  const [activeColumn, setActiveColumn] = useState<KanbanColumn | null>(null);
  const [activeTask, setActiveTask] = useState<Task | null>(null);

  // Modals
  const [isColumnModalOpen, setIsColumnModalOpen] = useState(false);
  const [columnToEdit, setColumnToEdit] = useState<KanbanColumn | null>(null);

  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
  const [columnToDelete, setColumnToDelete] = useState<KanbanColumn | null>(null);

  const [selectedTaskId, setSelectedTaskId] = useState<string | null>(null);
  const [isCreateTaskModalOpen, setIsCreateTaskModalOpen] = useState(false);

  const canManageBoard = can(userRole, 'create_task');

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 5,
      },
    }),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    })
  );

  const loadBoardData = async () => {
    if (!targetProjectId) {
      setColumns([]);
      setTasks([]);
      setLoading(false);
      return;
    }
    setLoading(true);
    try {
      const [colsData, tasksData, membersData, listsData] = await Promise.all([
        getKanbanColumns(targetProjectId),
        getBoardTasks(targetProjectId),
        getProjectMembers(targetProjectId),
        getTodoLists(targetProjectId),
      ]);
      setColumns(colsData);
      setTasks(tasksData);
      setMembers(membersData.map((m) => m.profile).filter(Boolean) as Profile[]);
      setTodoLists(listsData);
    } catch (err) {
      console.error('Failed to load kanban data:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadBoardData();
  }, [targetProjectId]);

  // Realtime Subscriptions
  useEffect(() => {
    if (!targetProjectId || !isSupabaseConfigured) return;

    const columnSub = supabase
      .channel(`kanban-columns-${projectId}`)
      .on(
        'postgres_changes',
        { event: '*', schema: 'public', table: 'kanban_columns', filter: `project_id=eq.${projectId}` },
        () => loadBoardData()
      )
      .subscribe();

    const taskSub = supabase
      .channel(`kanban-tasks-${projectId}`)
      .on(
        'postgres_changes',
        { event: '*', schema: 'public', table: 'tasks', filter: `project_id=eq.${projectId}` },
        () => loadBoardData()
      )
      .subscribe();

    return () => {
      supabase.removeChannel(columnSub);
      supabase.removeChannel(taskSub);
    };
  }, [projectId]);

  // Quick Task Add from Column
  const handleQuickAddTask = async (columnId: string, title: string) => {
    if (!projectId) return;
    const col = columns.find((c) => c.id === columnId);
    const defaultList = todoLists[0];

    const { error } = await createTask({
      project_id: projectId,
      todo_list_id: defaultList?.id || `list-${projectId}`,
      title,
      status: col?.mapped_status || 'NOT_STARTED',
      kanban_column_id: columnId,
      created_by: user?.id,
    });

    if (!error) {
      loadBoardData();
    }
  };

  // Drag and Drop Handlers
  const handleDragStart = (event: DragStartEvent) => {
    const { active } = event;
    const activeId = active.id as string;

    const col = columns.find((c) => c.id === activeId);
    if (col) {
      setActiveColumn(col);
      return;
    }

    const t = tasks.find((item) => item.id === activeId);
    if (t) {
      setActiveTask(t);
    }
  };

  const handleDragOver = (event: DragOverEvent) => {
    const { active, over } = event;
    if (!over) return;

    const activeId = active.id as string;
    const overId = over.id as string;

    if (activeId === overId) return;

    const isActiveTask = tasks.some((t) => t.id === activeId);
    const isOverTask = tasks.some((t) => t.id === overId);
    const isOverColumn = columns.some((c) => c.id === overId);

    if (!isActiveTask) return;

    // Moving a Task over another Task in a different column
    if (isActiveTask && isOverTask) {
      setTasks((prev) => {
        const activeIdx = prev.findIndex((t) => t.id === activeId);
        const overIdx = prev.findIndex((t) => t.id === overId);

        if (prev[activeIdx].kanban_column_id !== prev[overIdx].kanban_column_id) {
          const updated = [...prev];
          updated[activeIdx] = {
            ...updated[activeIdx],
            kanban_column_id: prev[overIdx].kanban_column_id,
          };
          return arrayMove(updated, activeIdx, overIdx);
        }
        return arrayMove(prev, activeIdx, overIdx);
      });
    }

    // Moving a Task over an empty Column container
    if (isActiveTask && isOverColumn) {
      setTasks((prev) => {
        const activeIdx = prev.findIndex((t) => t.id === activeId);
        if (prev[activeIdx].kanban_column_id !== overId) {
          const updated = [...prev];
          updated[activeIdx] = {
            ...updated[activeIdx],
            kanban_column_id: overId,
          };
          return updated;
        }
        return prev;
      });
    }
  };

  const handleDragEnd = async (event: DragEndEvent) => {
    const { active, over } = event;
    setActiveColumn(null);
    setActiveTask(null);

    if (!over || !projectId) return;

    const activeId = active.id as string;
    const overId = over.id as string;

    // Column Reordering
    const isColumn = columns.some((c) => c.id === activeId);
    if (isColumn) {
      if (activeId !== overId) {
        const oldIndex = columns.findIndex((c) => c.id === activeId);
        const newIndex = columns.findIndex((c) => c.id === overId);
        const newColumns = arrayMove(columns, oldIndex, newIndex);
        setColumns(newColumns);
        await reorderKanbanColumns(
          projectId,
          newColumns.map((c) => c.id)
        );
      }
      return;
    }

    // Task Moving / Reordering
    const currentTask = tasks.find((t) => t.id === activeId);
    if (!currentTask) return;

    const targetColumnId = currentTask.kanban_column_id;
    if (!targetColumnId) return;

    const targetCol = columns.find((c) => c.id === targetColumnId);

    // Persist position & column move
    try {
      await moveTaskToColumn(
        currentTask.id,
        targetColumnId,
        currentTask.kanban_position ?? 0,
        targetCol?.mapped_status
      );

      const colTaskIds = tasks
        .filter((t) => t.kanban_column_id === targetColumnId)
        .map((t) => t.id);

      await reorderTasksInColumn(targetColumnId, colTaskIds);
    } catch (err) {
      console.error('Failed to persist drag drop:', err);
      loadBoardData();
    }
  };

  // Filter Tasks
  const filteredTasks = useMemo(() => {
    return tasks.filter((t) => {
      // Search query match
      if (filters.search_query) {
        const q = filters.search_query.toLowerCase();
        const matchTitle = t.title.toLowerCase().includes(q);
        const matchDesc = t.description?.toLowerCase().includes(q) || false;
        if (!matchTitle && !matchDesc) return false;
      }

      // Assignee match
      if (filters.assignee_id) {
        if (!t.assignees?.some((a) => a.id === filters.assignee_id)) return false;
      }

      // My tasks only
      if (filters.my_tasks_only && user) {
        if (!t.assignees?.some((a) => a.id === user.id)) return false;
      }

      // Priority match
      if (filters.priority) {
        if (t.priority !== filters.priority) return false;
      }

      // Label match
      if (filters.label_id) {
        if (!t.labels?.some((l) => l.id === filters.label_id)) return false;
      }

      // Overdue match
      if (filters.overdue_only) {
        const isOverdue =
          t.due_date &&
          t.status !== 'COMPLETED' &&
          new Date(t.due_date) < new Date(new Date().setHours(0, 0, 0, 0));
        if (!isOverdue) return false;
      }

      // Blocked match
      if (filters.blocked_only) {
        if (!t.blocked_by || t.blocked_by.length === 0) return false;
      }

      // Completed match
      if (filters.completed_only) {
        if (t.status !== 'COMPLETED') return false;
      }

      return true;
    });
  }, [tasks, filters, user]);

  const columnIds = columns.map((c) => c.id);

  if (loading) {
    return (
      <div className="py-16 text-center text-slate-500 font-semibold text-sm animate-pulse">
        Loading visual card board...
      </div>
    );
  }

  return (
    <div className="space-y-6 animate-in fade-in duration-300 max-w-7xl mx-auto">
      {/* Header Bar */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <Badge variant="brand">{columns.length} Columns</Badge>
            <span className="text-xs text-slate-500 font-medium">{tasks.length} Total Cards</span>
          </div>
          <h1 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
            Card Board
          </h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Visualize project workflow, manage drag-and-drop cards, and track status stages
          </p>
        </div>

        <div className="flex items-center gap-2.5">
          <Button
            variant="outline"
            size="sm"
            leftIcon={<RefreshCw className="w-3.5 h-3.5" />}
            onClick={loadBoardData}
          >
            Refresh
          </Button>

          {canManageBoard && (
            <>
              <Button
                variant="outline"
                leftIcon={<Layout className="w-4 h-4" />}
                onClick={() => {
                  setColumnToEdit(null);
                  setIsColumnModalOpen(true);
                }}
              >
                Add Column
              </Button>

              <Button
                variant="primary"
                leftIcon={<Plus className="w-4 h-4" />}
                onClick={() => setIsCreateTaskModalOpen(true)}
              >
                New Task
              </Button>
            </>
          )}
        </div>
      </div>

      {/* Filter Bar */}
      <KanbanFilterBar
        filters={filters}
        onChangeFilters={setFilters}
        members={members}
        labels={[]}
        currentUserId={user?.id}
      />

      {/* Kanban Board Container (Horizontal Scroll) */}
      <DndContext
        sensors={sensors}
        collisionDetection={closestCorners}
        onDragStart={handleDragStart}
        onDragOver={handleDragOver}
        onDragEnd={handleDragEnd}
      >
        <div className="overflow-x-auto pb-6 pt-2">
          <div className="flex gap-5 min-h-[520px] items-start min-w-max">
            <SortableContext items={columnIds} strategy={horizontalListSortingStrategy}>
              {columns.map((column) => {
                const columnTasks = filteredTasks
                  .filter((t) => t.kanban_column_id === column.id)
                  .sort((a, b) => (a.kanban_position ?? 0) - (b.kanban_position ?? 0));

                return (
                  <KanbanColumnComponent
                    key={column.id}
                    column={column}
                    tasks={columnTasks}
                    onTaskClick={(t) => setSelectedTaskId(t.id)}
                    onEditColumn={(c) => {
                      setColumnToEdit(c);
                      setIsColumnModalOpen(true);
                    }}
                    onDeleteColumn={(c) => {
                      setColumnToDelete(c);
                      setIsDeleteModalOpen(true);
                    }}
                    onQuickAddTask={handleQuickAddTask}
                    canManageBoard={canManageBoard}
                  />
                );
              })}
            </SortableContext>
          </div>
        </div>

        {/* Drag Overlay for smooth animation */}
        <DragOverlay>
          {activeColumn ? (
            <KanbanColumnComponent
              column={activeColumn}
              tasks={filteredTasks.filter((t) => t.kanban_column_id === activeColumn.id)}
              onTaskClick={() => {}}
              onEditColumn={() => {}}
              onDeleteColumn={() => {}}
              onQuickAddTask={async () => {}}
              canManageBoard={false}
            />
          ) : activeTask ? (
            <KanbanCard task={activeTask} onClick={() => {}} />
          ) : null}
        </DragOverlay>
      </DndContext>

      {/* Modals */}
      {projectId && (
        <>
          <CreateKanbanColumnModal
            isOpen={isColumnModalOpen}
            onClose={() => {
              setIsColumnModalOpen(false);
              setColumnToEdit(null);
            }}
            projectId={projectId}
            columnToEdit={columnToEdit}
            onSuccess={loadBoardData}
          />

          <DeleteKanbanColumnModal
            isOpen={isDeleteModalOpen}
            onClose={() => {
              setIsDeleteModalOpen(false);
              setColumnToDelete(null);
            }}
            column={columnToDelete}
            allColumns={columns}
            columnTasks={
              columnToDelete
                ? tasks.filter((t) => t.kanban_column_id === columnToDelete.id)
                : []
            }
            onSuccess={loadBoardData}
          />

          <CreateTaskModal
            isOpen={isCreateTaskModalOpen}
            onClose={() => setIsCreateTaskModalOpen(false)}
            projectId={projectId}
            todoLists={todoLists}
            defaultListId={todoLists[0]?.id}
            onSuccess={loadBoardData}
          />

          <TaskDetailModal
            isOpen={Boolean(selectedTaskId)}
            onClose={() => setSelectedTaskId(null)}
            taskId={selectedTaskId}
            todoLists={todoLists}
            onSuccess={loadBoardData}
          />
        </>
      )}
    </div>
  );
}
