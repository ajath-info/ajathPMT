import React, { useState, useEffect } from 'react';
import {
  CheckSquare,
  CheckCircle2,
  Circle,
  Clock,
  MessageSquare,
  Plus,
  Trash2,
  Save,
  Send,
  AlertTriangle,
  MoveRight,
  ShieldCheck,
  UserPlus,
} from 'lucide-react';
import { Modal } from '../common/Modal';
import { Input } from '../common/Input';
import { Button } from '../common/Button';
import { Avatar } from '../common/Avatar';
import { Badge } from '../common/Badge';
import { useAuth } from '../../context/AuthContext';
import {
  getTaskById,
  updateTask,
  completeTask,
  reopenTask,
  deleteTask,
  createSubtask,
  getTaskComments,
  addTaskComment,
  deleteTaskComment,
  moveTask,
  getTaskAttachments,
  addTaskAttachment,
  deleteTaskAttachment,
  getTaskWatchers,
  toggleTaskWatcher,
  getTaskReminders,
  createTaskReminder,
  deleteTaskReminder,
  getTaskDependencies,
  addTaskDependency,
  removeTaskDependency,
  getTasks,
} from '../../services/taskService';
import { Eye, Bell, Paperclip, FileText, X, ExternalLink, Link2 } from 'lucide-react';
import { useToast } from '../../context/ToastContext';
import { Task, TaskComment, TodoList, TaskStatusType, TaskPriorityType } from '../../types';
import { formatDate, formatTimeAgo } from '../../lib/utils';

export interface TaskDetailModalProps {
  isOpen: boolean;
  onClose: () => void;
  taskId: string | null;
  todoLists: TodoList[];
  onSuccess: () => void;
}


export function TaskDetailModal({
  isOpen,
  onClose,
  taskId,
  todoLists,
  onSuccess,
}: TaskDetailModalProps) {
  const { profile, user } = useAuth();
  const { addToast } = useToast();
  const [task, setTask] = useState<Task | null>(null);
  const [comments, setComments] = useState<TaskComment[]>([]);
  const [loading, setLoading] = useState(true);

  // Advanced features state
  const [attachments, setAttachments] = useState<any[]>([]);
  const [watchers, setWatchers] = useState<any[]>([]);
  const [reminders, setReminders] = useState<any[]>([]);
  const [isWatching, setIsWatching] = useState(false);
  const [remindAtInput, setRemindAtInput] = useState('');
  const [attFileName, setAttFileName] = useState('');
  const [attFileUrl, setAttFileUrl] = useState('');

  // Dependencies state
  const [dependencies, setDependencies] = useState<{ blocked_by: Task[]; blocking: Task[] }>({
    blocked_by: [],
    blocking: [],
  });
  const [allProjectTasks, setAllProjectTasks] = useState<Task[]>([]);
  const [selectedDepTaskId, setSelectedDepTaskId] = useState('');

  // Form states
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [status, setStatus] = useState<TaskStatusType>('NOT_STARTED');
  const [priority, setPriority] = useState<TaskPriorityType>('MEDIUM');
  const [dueDate, setDueDate] = useState('');
  const [moveListId, setMoveListId] = useState('');
  const [recurrenceFreq, setRecurrenceFreq] = useState<'NONE' | 'DAILY' | 'WEEKLY' | 'MONTHLY' | 'CUSTOM'>('NONE');
  const [recurrenceInterval, setRecurrenceInterval] = useState<number>(1);

  // Subtask input
  const [newSubtaskTitle, setNewSubtaskTitle] = useState('');

  // Comment input
  const [newCommentContent, setNewCommentContent] = useState('');

  const [isSaving, setIsSaving] = useState(false);

  const loadTask = async () => {
    if (!taskId) return;
    setLoading(true);
    const tData = await getTaskById(taskId);
    const cData = await getTaskComments(taskId);
    const attData = await getTaskAttachments(taskId);
    const watchData = await getTaskWatchers(taskId);
    const remData = await getTaskReminders(taskId);
    const depData = await getTaskDependencies(taskId);

    setTask(tData);
    setComments(cData);
    setAttachments(attData);
    setWatchers(watchData);
    setReminders(remData);
    setDependencies(depData);

    if (user) {
      setIsWatching(watchData.some((w: any) => w.user_id === user.id));
    }

    if (tData) {
      setTitle(tData.title);
      setDescription(tData.description || '');
      setStatus(tData.status);
      setPriority(tData.priority);
      setDueDate(tData.due_date || '');
      setMoveListId(tData.todo_list_id);
      if (tData.recurrence_pattern) {
        setRecurrenceFreq(tData.recurrence_pattern.frequency || 'NONE');
        setRecurrenceInterval(tData.recurrence_pattern.interval || 1);
      } else {
        setRecurrenceFreq('NONE');
        setRecurrenceInterval(1);
      }

      if (tData.project_id) {
        const pTasks = await getTasks(tData.project_id);
        setAllProjectTasks(pTasks.filter((t) => t.id !== taskId));
      }
    }
    setLoading(false);
  };

  useEffect(() => {
    if (isOpen && taskId) {
      loadTask();
    }
  }, [isOpen, taskId]);

  const handleToggleWatch = async () => {
    if (!taskId || !user) return;
    const res = await toggleTaskWatcher(taskId, user.id);
    setIsWatching(res.watching);
    const updatedWatchers = await getTaskWatchers(taskId);
    setWatchers(updatedWatchers);
  };

  const handleAddReminder = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!taskId || !user || !remindAtInput) return;
    await createTaskReminder(taskId, user.id, remindAtInput);
    setRemindAtInput('');
    const updated = await getTaskReminders(taskId);
    setReminders(updated);
  };

  const handleDeleteReminder = async (remId: string) => {
    if (!taskId) return;
    await deleteTaskReminder(remId);
    setReminders(reminders.filter((r) => r.id !== remId));
  };

  const handleAddAttachment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!taskId || !attFileName.trim() || !attFileUrl.trim()) return;
    await addTaskAttachment({
      task_id: taskId,
      file_name: attFileName.trim(),
      file_url: attFileUrl.trim(),
      uploaded_by: user?.id,
    });
    setAttFileName('');
    setAttFileUrl('');
    const updated = await getTaskAttachments(taskId);
    setAttachments(updated);
  };

  const handleDeleteAttachment = async (attId: string) => {
    await deleteTaskAttachment(attId);
    setAttachments(attachments.filter((a) => a.id !== attId));
  };

  const handleSaveChanges = async () => {
    if (!task) return;
    setIsSaving(true);
    await updateTask(task.id, {
      title,
      description,
      status,
      priority,
      due_date: dueDate || undefined,
      todo_list_id: moveListId !== task.todo_list_id ? moveListId : undefined,
      recurrence_pattern:
        recurrenceFreq !== 'NONE'
          ? {
              frequency: recurrenceFreq,
              interval: recurrenceFreq === 'CUSTOM' ? recurrenceInterval : 1,
            }
          : undefined,
    });
    setIsSaving(false);
    onSuccess();
    loadTask();
  };

  const handleToggleComplete = async () => {
    if (!task) return;
    if (task.status === 'COMPLETED') {
      await reopenTask(task.id);
    } else {
      const unfinished = dependencies.blocked_by.filter((b) => b.status !== 'COMPLETED');
      if (unfinished.length > 0) {
        const names = unfinished.map((u) => `"${u.title}"`).join(', ');
        if (!confirm(`Warning: This task is waiting on unfinished dependencies: ${names}. Mark as completed anyway?`)) {
          return;
        }
      }
      await completeTask(task.id);
    }
    onSuccess();
    loadTask();
  };

  const handleAddDependency = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!taskId || !selectedDepTaskId) return;
    const res = await addTaskDependency(taskId, selectedDepTaskId);
    if (res.error) {
      addToast(res.error.message, 'error');
    } else {
      addToast('Dependency added', 'success');
      setSelectedDepTaskId('');
      const updated = await getTaskDependencies(taskId);
      setDependencies(updated);
    }
  };

  const handleRemoveDependency = async (depId: string) => {
    if (!taskId) return;
    const res = await removeTaskDependency(taskId, depId);
    if (res.error) {
      addToast(res.error.message, 'error');
    } else {
      addToast('Dependency removed', 'info');
      const updated = await getTaskDependencies(taskId);
      setDependencies(updated);
    }
  };

  const handleAddSubtask = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!task || !newSubtaskTitle.trim()) return;

    await createSubtask(task.id, task.project_id, task.todo_list_id, newSubtaskTitle, profile?.id);
    setNewSubtaskTitle('');
    loadTask();
  };

  const handleAddComment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!task || !profile || !newCommentContent.trim()) return;

    await addTaskComment(task.id, profile.id, newCommentContent);
    setNewCommentContent('');
    const updated = await getTaskComments(task.id);
    setComments(updated);
  };

  const handleDeleteComment = async (commentId: string) => {
    if (!task) return;
    await deleteTaskComment(commentId);
    const updated = await getTaskComments(task.id);
    setComments(updated);
  };

  const handleDeleteTask = async () => {
    if (!task) return;
    await deleteTask(task.id);
    onSuccess();
    onClose();
  };

  if (!isOpen || !taskId) return null;

  return (
    <Modal isOpen={isOpen} onClose={onClose} maxWidth="xl">
      {loading || !task ? (
        <div className="py-12 text-center text-xs text-slate-500 font-semibold">
          Loading task workspace details...
        </div>
      ) : (
        <div className="space-y-6 max-h-[80vh] overflow-y-auto custom-scrollbar pr-1">
          {/* Header Action Bar */}
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={handleToggleComplete}
                className={`p-1 rounded-xl transition-all ${
                  task.status === 'COMPLETED' ? 'text-emerald-500' : 'text-slate-400 hover:text-brand-500'
                }`}
              >
                {task.status === 'COMPLETED' ? (
                  <CheckCircle2 className="w-6 h-6 fill-emerald-50 dark:fill-emerald-950/50" />
                ) : (
                  <Circle className="w-6 h-6" />
                )}
              </button>
              <Badge variant={task.status === 'COMPLETED' ? 'success' : 'brand'}>{task.status}</Badge>
              <Badge variant={task.priority === 'URGENT' ? 'danger' : 'warning'}>{task.priority}</Badge>

              {/* Watcher Toggle */}
              <button
                onClick={handleToggleWatch}
                className={`px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors border ${
                  isWatching
                    ? 'bg-indigo-500/10 border-indigo-500/30 text-indigo-400'
                    : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-slate-200'
                }`}
                title={isWatching ? 'Stop watching task' : 'Watch this task for updates'}
              >
                <Eye className="w-3.5 h-3.5" />
                <span>{watchers.length} Watchers</span>
              </button>

              {task.recurrence_pattern && (
                <span className="px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center gap-1.5 bg-violet-500/10 border border-violet-500/30 text-violet-400">
                  <Clock className="w-3.5 h-3.5" />
                  <span>Repeats {task.recurrence_pattern.frequency.toLowerCase()}</span>
                </span>
              )}
            </div>

            <div className="flex items-center gap-2">
              <Button size="sm" variant="danger" leftIcon={<Trash2 className="w-3.5 h-3.5" />} onClick={handleDeleteTask}>
                Delete Task
              </Button>
              <Button size="sm" variant="primary" isLoading={isSaving} leftIcon={<Save className="w-3.5 h-3.5" />} onClick={handleSaveChanges}>
                Save Changes
              </Button>
            </div>
          </div>

          {/* Title and Description */}
          <div className="space-y-3">
            <input
              type="text"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              className="w-full font-extrabold text-xl bg-transparent border-none focus:outline-none focus:ring-1 focus:ring-brand-500/30 rounded-lg p-1 text-slate-900 dark:text-slate-100"
            />

            <div>
              <label className="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                Detailed Description
              </label>
              <textarea
                rows={3}
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                placeholder="Add task details, links, or instructions..."
                className="w-full p-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
              />
            </div>
          </div>

          {/* Controls Grid */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 p-4 rounded-2xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 text-xs">
            <div>
              <span className="font-bold text-slate-500 block mb-1">Move to List</span>
              <select
                value={moveListId}
                onChange={(e) => setMoveListId(e.target.value)}
                className="w-full p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-semibold text-slate-800 dark:text-slate-200"
              >
                {todoLists.map((l: TodoList) => (
                  <option key={l.id} value={l.id}>
                    {l.title}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <span className="font-bold text-slate-500 block mb-1">Status</span>
              <select
                value={status}
                onChange={(e) => setStatus(e.target.value as TaskStatusType)}
                className="w-full p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-bold text-slate-800 dark:text-slate-200"
              >
                <option value="NOT_STARTED">NOT_STARTED</option>
                <option value="IN_PROGRESS">IN_PROGRESS</option>
                <option value="WAITING">WAITING</option>
                <option value="IN_REVIEW">IN_REVIEW</option>
                <option value="COMPLETED">COMPLETED</option>
              </select>
            </div>

            <div>
              <span className="font-bold text-slate-500 block mb-1">Priority</span>
              <select
                value={priority}
                onChange={(e) => setPriority(e.target.value as TaskPriorityType)}
                className="w-full p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-bold text-slate-800 dark:text-slate-200"
              >
                <option value="LOW">LOW</option>
                <option value="MEDIUM">MEDIUM</option>
                <option value="HIGH">HIGH</option>
                <option value="URGENT">URGENT</option>
              </select>
            </div>

            <div>
              <span className="font-bold text-slate-500 block mb-1">Due Date</span>
              <input
                type="date"
                value={dueDate}
                onChange={(e) => setDueDate(e.target.value)}
                className="w-full p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 font-semibold text-slate-800 dark:text-slate-200"
              />
            </div>
          </div>

          {/* Recurrence Rule Controls */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-800/40 text-xs">
            <div>
              <label className="font-bold text-indigo-700 dark:text-indigo-400 block mb-1 flex items-center gap-1.5">
                <Clock className="w-3.5 h-3.5" /> Recurrence Automation
              </label>
              <select
                value={recurrenceFreq}
                onChange={(e) => setRecurrenceFreq(e.target.value as any)}
                className="w-full p-1.5 rounded-lg border border-indigo-200 dark:border-indigo-800 bg-white dark:bg-slate-900 font-semibold text-slate-800 dark:text-slate-200"
              >
                <option value="NONE">Does not repeat</option>
                <option value="DAILY">Daily (Repeats every day)</option>
                <option value="WEEKLY">Weekly (Repeats every 7 days)</option>
                <option value="MONTHLY">Monthly (Repeats every month)</option>
                <option value="CUSTOM">Custom Interval</option>
              </select>
            </div>
            {recurrenceFreq === 'CUSTOM' && (
              <div>
                <label className="font-bold text-indigo-700 dark:text-indigo-400 block mb-1">
                  Interval (Days)
                </label>
                <input
                  type="number"
                  min="1"
                  max="365"
                  value={recurrenceInterval}
                  onChange={(e) => setRecurrenceInterval(parseInt(e.target.value) || 1)}
                  className="w-full p-1.5 rounded-lg border border-indigo-200 dark:border-indigo-800 bg-white dark:bg-slate-900 font-semibold text-slate-800 dark:text-slate-200"
                />
              </div>
            )}
          </div>

          {/* Task Attachments Section */}
          <div className="space-y-3 pt-2">
            <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
              <Paperclip className="w-4 h-4 text-emerald-400" /> Task Attachments ({attachments.length})
            </h4>

            <form onSubmit={handleAddAttachment} className="flex gap-2">
              <input
                type="text"
                placeholder="Attachment Name..."
                value={attFileName}
                onChange={(e) => setAttFileName(e.target.value)}
                className="flex-1 px-3 py-1.5 text-xs rounded-xl border border-slate-800 bg-slate-900 text-slate-100"
              />
              <input
                type="text"
                placeholder="File URL..."
                value={attFileUrl}
                onChange={(e) => setAttFileUrl(e.target.value)}
                className="flex-1 px-3 py-1.5 text-xs rounded-xl border border-slate-800 bg-slate-900 text-slate-100"
              />
              <Button size="sm" variant="secondary" type="submit" disabled={!attFileName || !attFileUrl}>
                Attach
              </Button>
            </form>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
              {attachments.map((att) => (
                <div key={att.id} className="p-2.5 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between text-xs">
                  <a href={att.file_url} target="_blank" rel="noreferrer" className="font-semibold text-slate-200 hover:text-indigo-400 truncate flex items-center gap-1.5">
                    <FileText className="w-3.5 h-3.5 text-slate-500" />
                    <span className="truncate">{att.file_name}</span>
                  </a>
                  <button onClick={() => handleDeleteAttachment(att.id)} className="text-slate-500 hover:text-rose-400 p-1">
                    <X className="w-3.5 h-3.5" />
                  </button>
                </div>
              ))}
            </div>
          </div>

          {/* Task Reminders Section */}
          <div className="space-y-3 pt-2">
            <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
              <Bell className="w-4 h-4 text-amber-400" /> Set Reminder ({reminders.length})
            </h4>

            <form onSubmit={handleAddReminder} className="flex gap-2">
              <input
                type="datetime-local"
                value={remindAtInput}
                onChange={(e) => setRemindAtInput(e.target.value)}
                className="px-3 py-1.5 text-xs rounded-xl border border-slate-800 bg-slate-900 text-slate-100"
              />
              <Button size="sm" variant="secondary" type="submit" disabled={!remindAtInput}>
                Add Reminder
              </Button>
            </form>

            <div className="space-y-1.5">
              {reminders.map((rem) => (
                <div key={rem.id} className="flex items-center justify-between p-2 rounded-xl bg-slate-900 border border-slate-800 text-xs">
                  <span className="text-slate-300 font-medium">Remind at: {new Date(rem.remind_at).toLocaleString()}</span>
                  <button onClick={() => handleDeleteReminder(rem.id)} className="text-slate-500 hover:text-rose-400 p-1">
                    <X className="w-3.5 h-3.5" />
                  </button>
                </div>
              ))}
            </div>
          </div>

          {/* Task Dependencies & Blockers Section */}
          <div className="space-y-3 pt-2">
            <div className="flex items-center justify-between">
              <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <Link2 className="w-4 h-4 text-amber-500" /> Dependencies & Blockers
              </h4>
              {dependencies.blocked_by.some((t) => t.status !== 'COMPLETED') && (
                <span className="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                  <AlertTriangle className="w-3 h-3" /> Waiting on dependencies
                </span>
              )}
            </div>

            {/* Add Dependency Form */}
            <form onSubmit={handleAddDependency} className="flex gap-2">
              <select
                value={selectedDepTaskId}
                onChange={(e) => setSelectedDepTaskId(e.target.value)}
                className="flex-1 px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"
              >
                <option value="">Select a task that must be completed first...</option>
                {allProjectTasks
                  .filter((t) => !dependencies.blocked_by.some((b) => b.id === t.id))
                  .map((t) => (
                    <option key={t.id} value={t.id}>
                      {t.title} ({t.status})
                    </option>
                  ))}
              </select>
              <Button size="sm" variant="secondary" type="submit" disabled={!selectedDepTaskId}>
                Add Blocker
              </Button>
            </form>

            {/* Blocked By List */}
            {dependencies.blocked_by.length > 0 && (
              <div className="space-y-1.5">
                <span className="text-[11px] font-semibold text-slate-500">Must be finished first (Blocked by):</span>
                <div className="space-y-1.5">
                  {dependencies.blocked_by.map((dep) => (
                    <div
                      key={dep.id}
                      className={`p-2 rounded-xl border flex items-center justify-between text-xs ${
                        dep.status === 'COMPLETED'
                          ? 'bg-emerald-500/5 border-emerald-500/20 text-emerald-600 dark:text-emerald-400'
                          : 'bg-amber-500/5 border-amber-500/20 text-amber-600 dark:text-amber-400'
                      }`}
                    >
                      <div className="flex items-center gap-2 truncate">
                        {dep.status === 'COMPLETED' ? (
                          <CheckCircle2 className="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                        ) : (
                          <Circle className="w-3.5 h-3.5 text-amber-500 shrink-0" />
                        )}
                        <span className="font-semibold truncate">{dep.title}</span>
                        <span className="text-[10px] px-1.5 py-0.5 rounded bg-black/10 uppercase tracking-wider font-bold">
                          {dep.status}
                        </span>
                      </div>
                      <button
                        type="button"
                        onClick={() => handleRemoveDependency(dep.id)}
                        className="text-slate-400 hover:text-rose-500 p-1 transition-colors"
                        title="Remove dependency"
                      >
                        <X className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Blocking List */}
            {dependencies.blocking.length > 0 && (
              <div className="space-y-1.5 pt-1">
                <span className="text-[11px] font-semibold text-slate-500">Waiting on this task (Blocking):</span>
                <div className="space-y-1.5">
                  {dependencies.blocking.map((dep) => (
                    <div
                      key={dep.id}
                      className="p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400"
                    >
                      <div className="flex items-center gap-2 truncate">
                        <MoveRight className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                        <span className="font-semibold truncate">{dep.title}</span>
                        <span className="text-[10px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/5 uppercase tracking-wider font-bold">
                          {dep.status}
                        </span>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>

          {/* Subtasks Checklist */}
          <div className="space-y-3 pt-2">
            <div className="flex items-center justify-between">
              <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <CheckSquare className="w-4 h-4 text-brand-500" /> Subtasks Checklist
              </h4>
              <span className="text-xs font-semibold text-slate-500">
                {task.subtasks_completed_count || 0} / {task.subtasks?.length || 0} completed
              </span>
            </div>

            {/* Subtask input */}
            <form onSubmit={handleAddSubtask} className="flex gap-2">
              <input
                type="text"
                placeholder="Add subtask item..."
                value={newSubtaskTitle}
                onChange={(e) => setNewSubtaskTitle(e.target.value)}
                className="flex-1 px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"
              />
              <Button size="sm" variant="secondary" type="submit" disabled={!newSubtaskTitle.trim()}>
                Add Subtask
              </Button>
            </form>

            <div className="space-y-1.5">
              {task.subtasks?.map((sub: Task) => (
                <div key={sub.id} className="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 text-xs">
                  <span className={`flex-1 font-semibold ${sub.status === 'COMPLETED' ? 'line-through text-slate-400' : ''}`}>
                    {sub.title}
                  </span>
                </div>
              ))}
            </div>

          </div>

          {/* Comment Thread */}
          <div className="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
            <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
              <MessageSquare className="w-4 h-4 text-violet-500" /> Discussion ({comments.length})
            </h4>

            {/* Comment Input */}
            <form onSubmit={handleAddComment} className="flex gap-2">
              <Avatar src={profile?.avatar_url} name={profile?.full_name || 'User'} size="sm" />
              <input
                type="text"
                placeholder="Write a comment or mention a member..."
                value={newCommentContent}
                onChange={(e) => setNewCommentContent(e.target.value)}
                className="flex-1 px-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none"
              />
              <Button size="sm" variant="primary" type="submit" disabled={!newCommentContent.trim()} leftIcon={<Send className="w-3.5 h-3.5" />}>
                Post
              </Button>
            </form>

            <div className="space-y-3">
              {comments.map((c) => (
                <div key={c.id} className="p-3 rounded-2xl bg-slate-50/70 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 space-y-1">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <Avatar src={c.profile?.avatar_url} name={c.profile?.full_name || 'Member'} size="xs" />
                      <span className="text-xs font-bold text-slate-900 dark:text-slate-100">{c.profile?.full_name}</span>
                      <span className="text-[10px] text-slate-400">{formatTimeAgo(c.created_at)}</span>
                    </div>
                    {c.user_id === profile?.id && (
                      <button onClick={() => handleDeleteComment(c.id)} className="text-slate-400 hover:text-rose-500">
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    )}
                  </div>
                  <p className="text-xs text-slate-700 dark:text-slate-300 pl-7">{c.content}</p>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </Modal>
  );
}

