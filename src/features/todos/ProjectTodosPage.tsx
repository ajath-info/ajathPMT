import React, { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  CheckSquare,
  Plus,
  List,
  ChevronDown,
  ChevronRight,
  Edit,
  Trash2,
  Bookmark,
  MoreHorizontal,
  Calendar as CalendarIcon,
  User as UserIcon,
  X,
  FolderPlus,
  Layers,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { useProject } from '../../context/ProjectContext';
import {
  getTodoLists,
  deleteTodoList,
  createTodoList,
  getTodoGroups,
  createTodoGroup,
  deleteTodoGroup,
} from '../../services/todoService';
import { getTasks, completeTask, reopenTask, createTask } from '../../services/taskService';
import { getProjectById, getProjectMembers } from '../../services/projectService';
import { TodoList, TodoGroup, Task, Project, ProjectMember } from '../../types';
import { CreateTodoListModal } from '../../components/todos/CreateTodoListModal';
import { CreateTaskModal } from '../../components/todos/CreateTaskModal';
import { TaskDetailModal } from '../../components/todos/TaskDetailModal';
import { audioEffects } from '../../lib/audioEffects';

export function ProjectTodosPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const { projects } = useProject();
  const { user, userRole } = useAuth();
  const { addToast } = useToast();
  const navigate = useNavigate();

  const targetProjectId = projectId || projects[0]?.id || 'proj-rmc';

  const [project, setProject] = useState<Project | null>(null);
  const [members, setMembers] = useState<ProjectMember[]>([]);
  const [todoLists, setTodoLists] = useState<TodoList[]>([]);
  const [todoGroups, setTodoGroups] = useState<Record<string, TodoGroup[]>>({});
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);

  // Bookmarking
  const bookmarkKey = `basecamp_bookmark_todos_${targetProjectId}`;
  const [isBookmarked, setIsBookmarked] = useState<boolean>(() => {
    try {
      return localStorage.getItem(bookmarkKey) === 'true';
    } catch {
      return false;
    }
  });
  const [isMoreMenuOpen, setIsMoreMenuOpen] = useState(false);
  const moreMenuRef = useRef<HTMLDivElement>(null);

  // Inline List creation
  const [isCreatingList, setIsCreatingList] = useState(false);
  const [newListName, setNewListName] = useState('');
  const [newListDescription, setNewListDescription] = useState('');

  // Inline Task creation per list ID & optional group ID
  const [activeInlineListId, setActiveInlineListId] = useState<string | null>(null);
  const [activeInlineGroupId, setActiveInlineGroupId] = useState<string | null>(null);
  const [inlineTaskTitle, setInlineTaskTitle] = useState('');
  const [inlineTaskAssignee, setInlineTaskAssignee] = useState('');
  const [inlineTaskDueDate, setInlineTaskDueDate] = useState('');

  // Inline Group creation per list ID
  const [activeAddingGroupId, setActiveAddingGroupId] = useState<string | null>(null);
  const [newGroupTitle, setNewGroupTitle] = useState('');

  // Collapsed state for completed to-dos per list
  const [collapsedCompletedLists, setCollapsedCompletedLists] = useState<Record<string, boolean>>({});

  // Modals
  const [isListModalOpen, setIsListModalOpen] = useState(false);
  const [listToEdit, setListToEdit] = useState<TodoList | null>(null);
  const [isTaskModalOpen, setIsTaskModalOpen] = useState(false);
  const [defaultListId, setDefaultListId] = useState<string | undefined>(undefined);
  const [selectedTaskId, setSelectedTaskId] = useState<string | null>(null);

  const loadData = async () => {
    if (!targetProjectId) return;
    setLoading(true);
    try {
      const [pData, mData, lData, tData] = await Promise.all([
        getProjectById(targetProjectId).catch(() => null),
        getProjectMembers(targetProjectId).catch(() => []),
        getTodoLists(targetProjectId).catch(() => []),
        getTasks(targetProjectId).catch(() => []),
      ]);
      if (pData) setProject(pData);
      setMembers(mData || []);
      setTodoLists(lData);
      setTasks(tData);

      // Load groups for each todo list
      const groupEntries = await Promise.all(
        lData.map(async (list) => {
          const grps = await getTodoGroups(list.id).catch(() => []);
          return [list.id, grps] as const;
        })
      );
      setTodoGroups(Object.fromEntries(groupEntries));
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [targetProjectId]);

  // Click outside listener for more menu
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (moreMenuRef.current && !moreMenuRef.current.contains(e.target as Node)) {
        setIsMoreMenuOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleToggleBookmark = () => {
    setIsBookmarked((prev) => {
      const next = !prev;
      try {
        localStorage.setItem(bookmarkKey, String(next));
      } catch {}
      addToast(next ? 'Bookmarked To-dos' : 'Bookmark removed', 'info');
      return next;
    });
  };

  const handleToggleComplete = async (task: Task) => {
    const nextStatus = task.status === 'COMPLETED' ? 'NOT_STARTED' : 'COMPLETED';
    if (nextStatus === 'COMPLETED') {
      audioEffects.playTaskCheck();
      addToast('To-do completed! 🎉', 'success');
    }
    setTasks((prev) =>
      prev.map((t) => (t.id === task.id ? { ...t, status: nextStatus } : t))
    );
    try {
      if (task.status === 'COMPLETED') {
        await reopenTask(task.id);
      } else {
        await completeTask(task.id);
      }
    } catch {
      loadData();
    }
  };

  const handleDeleteList = async (listId: string) => {
    if (userRole === 'CLIENT') {
      addToast('Client accounts cannot delete to-do lists.', 'error');
      return;
    }
    if (confirm('Delete this to-do list and all its tasks?')) {
      const res = await deleteTodoList(listId, userRole);
      if (res.error) {
        addToast(res.error.message || 'Failed to delete to-do list', 'error');
        return;
      }
      addToast('To-do list deleted', 'info');
      loadData();
    }
  };

  const handleCreateListSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newListName.trim()) return;
    try {
      await createTodoList({
        project_id: targetProjectId,
        title: newListName.trim(),
        description: newListDescription.trim() || undefined,
      });
      addToast('To-do list added', 'success');
      setNewListName('');
      setNewListDescription('');
      setIsCreatingList(false);
      loadData();
    } catch {
      addToast('Failed to create to-do list', 'error');
    }
  };

  const handleCreateGroupSubmit = async (listId: string) => {
    if (!newGroupTitle.trim()) return;
    try {
      await createTodoGroup(listId, newGroupTitle.trim());
      addToast('Group added to to-do list', 'success');
      setNewGroupTitle('');
      setActiveAddingGroupId(null);
      loadData();
    } catch {
      addToast('Failed to create to-do group', 'error');
    }
  };

  const handleDeleteGroup = async (groupId: string) => {
    if (confirm('Delete this group? Tasks in it will be kept and ungrouped.')) {
      try {
        await deleteTodoGroup(groupId);
        addToast('Group deleted (tasks preserved)', 'info');
        loadData();
      } catch {
        addToast('Failed to delete group', 'error');
      }
    }
  };

  const handleCreateInlineTask = async (listId: string, groupId?: string) => {
    if (!inlineTaskTitle.trim()) return;
    try {
      await createTask({
        project_id: targetProjectId,
        todo_list_id: listId,
        todo_group_id: groupId || undefined,
        title: inlineTaskTitle.trim(),
        due_date: inlineTaskDueDate || undefined,
        assignee_ids: inlineTaskAssignee ? [inlineTaskAssignee] : [],
        status: 'NOT_STARTED',
        priority: 'MEDIUM',
      });
      addToast('To-do added', 'success');
      setInlineTaskTitle('');
      setInlineTaskAssignee('');
      setInlineTaskDueDate('');
      setActiveInlineListId(null);
      setActiveInlineGroupId(null);
      loadData();
    } catch {
      addToast('Failed to add to-do', 'error');
    }
  };

  const toggleCompletedListVisibility = (listId: string) => {
    setCollapsedCompletedLists((prev) => ({
      ...prev,
      [listId]: !prev[listId],
    }));
  };

  if (loading) {
    return (
      <div className="w-full max-w-7xl mx-auto space-y-6 pt-4 pb-16">
        <div className="w-full bg-white dark:bg-slate-900 rounded-3xl min-h-[400px] p-12 text-center text-slate-400 font-semibold text-sm animate-pulse border border-slate-200/80 dark:border-slate-800 flex items-center justify-center">
          Loading to-dos...
        </div>
      </div>
    );
  }

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 pb-16">
      <div className="w-full bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
        
        {/* Top Breadcrumb & Action Utility Bar */}
        <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800/80">
          <div className="flex items-center gap-2 text-sm">
            <button
              onClick={() => navigate(`/projects/${targetProjectId}`)}
              className="font-bold text-slate-800 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:underline transition-colors cursor-pointer"
            >
              {project?.name || 'Project'}
            </button>
            <span className="text-slate-300 dark:text-slate-600">/</span>
            <span className="font-extrabold text-slate-900 dark:text-slate-100">To-dos</span>
          </div>

          <div className="flex items-center gap-2 relative" ref={moreMenuRef}>
            <button
              onClick={handleToggleBookmark}
              className={`p-1.5 rounded-lg border transition-colors cursor-pointer flex items-center gap-1.5 text-xs font-semibold ${
                isBookmarked
                  ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 text-amber-600 dark:text-amber-400'
                  : 'border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800'
              }`}
              title={isBookmarked ? 'Bookmarked' : 'Bookmark this page'}
            >
              <Bookmark className={`w-3.5 h-3.5 ${isBookmarked ? 'fill-current' : ''}`} />
              <span className="hidden sm:inline">{isBookmarked ? 'Bookmarked' : 'Bookmark'}</span>
            </button>

            <button
              onClick={() => setIsMoreMenuOpen(!isMoreMenuOpen)}
              className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
              title="More options"
            >
              <MoreHorizontal className="w-4 h-4" />
            </button>

            {isMoreMenuOpen && (
              <div className="absolute right-0 top-9 w-48 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 py-1.5 z-50 animate-in zoom-in-95 duration-100 text-xs">
                <button
                  onClick={() => {
                    setIsMoreMenuOpen(false);
                    setIsCreatingList(true);
                  }}
                  className="w-full px-4 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium cursor-pointer"
                >
                  Add a new list
                </button>
                <button
                  onClick={() => {
                    setIsMoreMenuOpen(false);
                    navigate(`/projects/${targetProjectId}`);
                  }}
                  className="w-full px-4 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium cursor-pointer"
                >
                  Back to project overview
                </button>
              </div>
            )}
          </div>
        </div>

        {/* Main Title: To-dos */}
        <div className="space-y-1 pt-1">
          <h1 className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            To-dos
          </h1>
        </div>

        {/* Action Toolbar */}
        {userRole !== 'CLIENT' && (
          <div className="flex items-center gap-3 pt-1">
            <button
              onClick={() => setIsCreatingList(true)}
              className="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-xs hover:shadow-md transition-all cursor-pointer flex items-center gap-1.5 active:scale-95"
            >
              <Plus className="w-3.5 h-3.5 stroke-[2.5]" />
              <span>New list</span>
            </button>
          </div>
        )}

        {/* Inline Add List Form */}
        {isCreatingList && (
          <form
            onSubmit={handleCreateListSubmit}
            className="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3 animate-in fade-in duration-200 max-w-xl"
          >
            <h4 className="font-bold text-sm text-slate-900 dark:text-slate-100">Add a to-do list</h4>
            <input
              type="text"
              value={newListName}
              onChange={(e) => setNewListName(e.target.value)}
              placeholder="Name this list..."
              autoFocus
              className="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <input
              type="text"
              value={newListDescription}
              onChange={(e) => setNewListDescription(e.target.value)}
              placeholder="Add extra details or notes (optional)..."
              className="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <div className="flex items-center gap-3 pt-1">
              <button
                type="submit"
                className="bg-[#0c66e4] hover:bg-[#0052cc] text-white px-4 py-1.5 rounded-xl font-bold text-xs shadow-xs transition-colors cursor-pointer"
              >
                Add this list
              </button>
              <button
                type="button"
                onClick={() => {
                  setIsCreatingList(false);
                  setNewListName('');
                  setNewListDescription('');
                }}
                className="text-xs font-semibold text-slate-500 hover:text-slate-800 hover:underline cursor-pointer"
              >
                Cancel
              </button>
            </div>
          </form>
        )}

        {/* Lists & Tasks Stack */}
        {todoLists.length === 0 && !isCreatingList ? (
          <div className="text-center py-16 space-y-4">
            <div className="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto">
              <CheckSquare className="w-7 h-7" />
            </div>
            <h3 className="text-base font-bold text-slate-900 dark:text-slate-100">No to-do lists yet</h3>
            <p className="text-xs text-slate-500 max-w-sm mx-auto">
              Break your project into lists of to-dos to keep tasks organized and track progress.
            </p>
            <button
              onClick={() => setIsCreatingList(true)}
              className="bg-[#0c66e4] hover:bg-[#0052cc] text-white px-4 py-2 rounded-xl font-bold text-xs shadow-xs transition-colors cursor-pointer inline-flex items-center gap-1.5"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>Create first to-do list</span>
            </button>
          </div>
        ) : (
          <div className="space-y-10 pt-2">
            {todoLists.map((list) => {
              const listTasks = tasks.filter((t) => t.todo_list_id === list.id);
              const listGroups = todoGroups[list.id] || [];
              const uncompletedTasks = listTasks.filter((t) => t.status !== 'COMPLETED');
              const completedTasks = listTasks.filter((t) => t.status === 'COMPLETED');
              const totalCount = listTasks.length;
              const completedCount = completedTasks.length;
              const percent = totalCount > 0 ? (completedCount / totalCount) * 100 : 0;
              const isAddingTask = activeInlineListId === list.id && !activeInlineGroupId;
              const isAddingGroup = activeAddingGroupId === list.id;
              const isCompletedCollapsed = collapsedCompletedLists[list.id] ?? false;

              // Ungrouped uncompleted tasks
              const ungroupedTasks = uncompletedTasks.filter(
                (t) => !t.todo_group_id || !listGroups.some((g) => g.id === t.todo_group_id)
              );

              return (
                <div key={list.id} className="space-y-4 pb-8 border-b border-slate-100 dark:border-slate-800/80 last:border-0">
                  {/* List Header with Signature Circular Progress */}
                  <div className="flex items-center justify-between group">
                    <div className="flex items-center gap-3">
                      <div
                        className="w-5 h-5 rounded-full border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 overflow-hidden relative"
                        title={`${completedCount} of ${totalCount} completed`}
                      >
                        {totalCount > 0 && (
                          <div
                            className="absolute inset-0 bg-emerald-500 transition-all duration-300"
                            style={{
                              clipPath:
                                percent === 100
                                  ? 'none'
                                  : `polygon(50% 50%, 50% 0, 100% 0, 100% ${percent >= 25 ? '100%' : '0'}, ${
                                      percent >= 50 ? '0 100%' : '50% 50%'
                                    }, ${percent >= 75 ? '0 0' : '50% 50%'})`,
                            }}
                          />
                        )}
                      </div>

                      <h3
                        onClick={() => {
                          setListToEdit(list);
                          setIsListModalOpen(true);
                        }}
                        className="text-xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight hover:text-brand-600 cursor-pointer transition-colors"
                      >
                        {list.title}
                      </h3>

                      <span className="text-xs text-slate-400 font-semibold">
                        ({completedCount} of {totalCount})
                      </span>
                    </div>

                    {/* Actions */}
                    <div className="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                      <button
                        onClick={() => {
                          setListToEdit(list);
                          setIsListModalOpen(true);
                        }}
                        className="p-1 rounded-md text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
                        title="Edit list"
                      >
                        <Edit className="w-3.5 h-3.5" />
                      </button>
                      {userRole !== 'CLIENT' && (
                        <button
                          onClick={() => handleDeleteList(list.id)}
                          className="p-1 rounded-md text-slate-400 hover:text-rose-500"
                          title="Delete list"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      )}
                    </div>
                  </div>

                  {list.description && (
                    <p className="text-xs text-slate-500 pl-8 leading-relaxed">
                      {list.description}
                    </p>
                  )}

                  {/* Tasks & Groups Area */}
                  <div className="pl-8 space-y-4 pt-1">
                    {/* 1. Ungrouped Tasks */}
                    {ungroupedTasks.length > 0 && (
                      <div className="space-y-2">
                        {ungroupedTasks.map((t) => renderTaskRow(t))}
                      </div>
                    )}

                    {/* 2. Basecamp 4 To-Do Groups */}
                    {listGroups.map((grp) => {
                      const groupTasks = uncompletedTasks.filter((t) => t.todo_group_id === grp.id);
                      const isAddingToThisGroup =
                        activeInlineListId === list.id && activeInlineGroupId === grp.id;

                      return (
                        <div
                          key={grp.id}
                          className="bg-slate-50/70 dark:bg-slate-800/40 rounded-2xl p-4 border border-slate-200/60 dark:border-slate-800 space-y-3"
                        >
                          <div className="flex items-center justify-between group/grouphead">
                            <div className="flex items-center gap-2">
                              <Layers className="w-4 h-4 text-blue-500" />
                              <h4 className="font-extrabold text-sm text-slate-800 dark:text-slate-200">
                                {grp.title}
                              </h4>
                              <span className="text-[11px] text-slate-400 font-semibold">
                                ({groupTasks.length})
                              </span>
                            </div>
                            <button
                              onClick={() => handleDeleteGroup(grp.id)}
                              className="opacity-0 group-hover/grouphead:opacity-100 transition-opacity text-slate-400 hover:text-rose-500 text-xs p-1"
                              title="Delete group (ungroups tasks)"
                            >
                              <Trash2 className="w-3.5 h-3.5" />
                            </button>
                          </div>

                          {/* Group Tasks */}
                          <div className="space-y-2 pl-2">
                            {groupTasks.map((t) => renderTaskRow(t))}
                          </div>

                          {/* Inline task creator for this group */}
                          {isAddingToThisGroup ? (
                            renderInlineTaskForm(list.id, grp.id)
                          ) : (
                            <button
                              onClick={() => {
                                setActiveInlineListId(list.id);
                                setActiveInlineGroupId(grp.id);
                                setInlineTaskTitle('');
                              }}
                              className="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 pl-2 pt-1 cursor-pointer"
                            >
                              <Plus className="w-3.5 h-3.5" />
                              <span>Add a to-do to {grp.title}</span>
                            </button>
                          )}
                        </div>
                      );
                    })}

                    {/* Inline Task Form for Ungrouped */}
                    {isAddingTask && renderInlineTaskForm(list.id)}

                    {/* Inline Add Group Form */}
                    {isAddingGroup && (
                      <div className="p-4 rounded-2xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/40 space-y-2.5 max-w-md">
                        <div className="flex items-center gap-2 text-xs font-bold text-slate-800 dark:text-slate-200">
                          <FolderPlus className="w-4 h-4 text-blue-600" />
                          <span>Add a To-do Group</span>
                        </div>
                        <input
                          type="text"
                          value={newGroupTitle}
                          onChange={(e) => setNewGroupTitle(e.target.value)}
                          placeholder="e.g. Phase 1, High-Fi Prototype, Backend API..."
                          autoFocus
                          onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                              e.preventDefault();
                              handleCreateGroupSubmit(list.id);
                            }
                          }}
                          className="w-full px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <div className="flex items-center gap-2">
                          <button
                            type="button"
                            onClick={() => handleCreateGroupSubmit(list.id)}
                            className="px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-bold shadow-xs hover:bg-blue-700 cursor-pointer"
                          >
                            Add this group
                          </button>
                          <button
                            type="button"
                            onClick={() => {
                              setActiveAddingGroupId(null);
                              setNewGroupTitle('');
                            }}
                            className="text-xs text-slate-500 hover:underline cursor-pointer"
                          >
                            Cancel
                          </button>
                        </div>
                      </div>
                    )}

                    {/* Add to-do / Add group toolbar */}
                    {!isAddingTask && (
                      <div className="flex items-center gap-4 pt-1">
                        <button
                          onClick={() => {
                            setActiveInlineListId(list.id);
                            setActiveInlineGroupId(null);
                            setInlineTaskTitle('');
                          }}
                          className="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 cursor-pointer"
                        >
                          <Plus className="w-3.5 h-3.5" />
                          <span>Add a to-do</span>
                        </button>
                        <span className="text-slate-300 dark:text-slate-700">•</span>
                        <button
                          onClick={() => {
                            setActiveAddingGroupId(list.id);
                            setNewGroupTitle('');
                          }}
                          className="text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-300 hover:underline flex items-center gap-1 cursor-pointer"
                        >
                          <FolderPlus className="w-3.5 h-3.5" />
                          <span>Add a group</span>
                        </button>
                      </div>
                    )}

                    {/* Completed Tasks Collapsible Section */}
                    {completedTasks.length > 0 && (
                      <div className="pt-3">
                        <button
                          onClick={() => toggleCompletedListVisibility(list.id)}
                          className="text-xs font-bold text-slate-400 hover:text-slate-600 flex items-center gap-1 cursor-pointer select-none"
                        >
                          {isCompletedCollapsed ? (
                            <ChevronRight className="w-3.5 h-3.5" />
                          ) : (
                            <ChevronDown className="w-3.5 h-3.5" />
                          )}
                          <span>
                            {completedCount} completed {completedCount === 1 ? 'to-do' : 'to-dos'}
                          </span>
                        </button>

                        {!isCompletedCollapsed && (
                          <div className="space-y-1.5 pt-2 pl-2">
                            {completedTasks.map((t) => (
                              <div
                                key={t.id}
                                className="flex items-center gap-2.5 text-xs text-slate-400 line-through py-0.5"
                              >
                                <input
                                  type="checkbox"
                                  checked={true}
                                  onChange={() => handleToggleComplete(t)}
                                  className="w-3.5 h-3.5 rounded-xs border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-0 cursor-pointer"
                                />
                                <span
                                  onClick={() => setSelectedTaskId(t.id)}
                                  className="cursor-pointer hover:text-slate-600 truncate"
                                >
                                  {t.title}
                                </span>
                              </div>
                            ))}
                          </div>
                        )}
                      </div>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>

      {/* Modals */}
      {targetProjectId && (
        <>
          <CreateTodoListModal
            isOpen={isListModalOpen}
            onClose={() => {
              setIsListModalOpen(false);
              setListToEdit(null);
            }}
            projectId={targetProjectId}
            listToEdit={listToEdit}
            onSuccess={loadData}
          />

          <CreateTaskModal
            isOpen={isTaskModalOpen}
            onClose={() => setIsTaskModalOpen(false)}
            projectId={targetProjectId}
            todoLists={todoLists}
            defaultListId={defaultListId}
            onSuccess={loadData}
          />

          <TaskDetailModal
            isOpen={Boolean(selectedTaskId)}
            onClose={() => setSelectedTaskId(null)}
            taskId={selectedTaskId}
            todoLists={todoLists}
            onSuccess={loadData}
          />
        </>
      )}
    </div>
  );

  function renderTaskRow(t: Task) {
    const assigneeName = t.assignees?.[0]?.full_name || '';
    return (
      <div
        key={t.id}
        className="flex items-start gap-2.5 py-1 text-sm group/task hover:bg-slate-50/80 dark:hover:bg-slate-800/40 px-2 rounded-xl transition-colors"
      >
        <input
          type="checkbox"
          checked={false}
          onChange={() => handleToggleComplete(t)}
          className="w-4 h-4 rounded-xs border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-0 cursor-pointer mt-0.5"
        />
        <span
          onClick={() => setSelectedTaskId(t.id)}
          className="font-medium text-slate-800 dark:text-slate-200 hover:text-blue-600 cursor-pointer flex-1"
        >
          {t.title}
        </span>

        {assigneeName && (
          <span className="text-[11px] font-semibold text-slate-500 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full shrink-0">
            {assigneeName}
          </span>
        )}

        {t.due_date && (
          <span className="text-[11px] text-slate-400 shrink-0">
            {new Date(t.due_date).toLocaleDateString([], { month: 'short', day: 'numeric' })}
          </span>
        )}
      </div>
    );
  }

  function renderInlineTaskForm(listId: string, groupId?: string) {
    return (
      <div className="pt-2 pb-3 space-y-3 bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 animate-in fade-in max-w-xl">
        <input
          type="text"
          value={inlineTaskTitle}
          onChange={(e) => setInlineTaskTitle(e.target.value)}
          placeholder="Describe this to-do..."
          autoFocus
          onKeyDown={(e) => {
            if (e.key === 'Enter') {
              e.preventDefault();
              handleCreateInlineTask(listId, groupId);
            }
          }}
          className="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <div className="flex items-center gap-3 flex-wrap text-xs">
          <div className="flex items-center gap-1.5">
            <UserIcon className="w-3.5 h-3.5 text-slate-400" />
            <select
              value={inlineTaskAssignee}
              onChange={(e) => setInlineTaskAssignee(e.target.value)}
              className="px-2 py-1 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200"
            >
              <option value="">Unassigned</option>
              {members.map((m) => (
                <option key={m.id} value={m.user_id}>
                  {m.profile?.full_name || 'Member'}
                </option>
              ))}
            </select>
          </div>

          <div className="flex items-center gap-1.5">
            <CalendarIcon className="w-3.5 h-3.5 text-slate-400" />
            <input
              type="date"
              value={inlineTaskDueDate}
              onChange={(e) => setInlineTaskDueDate(e.target.value)}
              className="px-2 py-1 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200"
            />
          </div>
        </div>

        <div className="flex items-center gap-3 pt-1">
          <button
            type="button"
            onClick={() => handleCreateInlineTask(listId, groupId)}
            className="bg-[#0c66e4] hover:bg-[#0052cc] text-white px-4 py-1.5 rounded-xl font-bold text-xs shadow-xs transition-colors cursor-pointer"
          >
            Add this to-do
          </button>
          <button
            type="button"
            onClick={() => {
              setActiveInlineListId(null);
              setActiveInlineGroupId(null);
              setInlineTaskTitle('');
            }}
            className="text-xs font-semibold text-slate-500 hover:text-slate-800 hover:underline cursor-pointer"
          >
            Cancel
          </button>
        </div>
      </div>
    );
  }
}
