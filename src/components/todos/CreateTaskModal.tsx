import React, { useState, useEffect } from 'react';
import { CheckSquare, Calendar, Users, AlertCircle } from 'lucide-react';
import { Modal } from '../common/Modal';
import { Input } from '../common/Input';
import { Button } from '../common/Button';
import { useAuth } from '../../context/AuthContext';
import { getProjectMembers } from '../../services/projectService';
import { createTask } from '../../services/taskService';
import { TodoList, TaskStatusType, TaskPriorityType, ProjectMember } from '../../types';

interface CreateTaskModalProps {
  isOpen: boolean;
  onClose: () => void;
  projectId: string;
  todoLists: TodoList[];
  defaultListId?: string;
  onSuccess: () => void;
}

export function CreateTaskModal({
  isOpen,
  onClose,
  projectId,
  todoLists,
  defaultListId,
  onSuccess,
}: CreateTaskModalProps) {
  const { profile } = useAuth();
  const [todoListId, setTodoListId] = useState(defaultListId || (todoLists[0]?.id || ''));
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [status, setStatus] = useState<TaskStatusType>('NOT_STARTED');
  const [priority, setPriority] = useState<TaskPriorityType>('MEDIUM');
  const [startDate, setStartDate] = useState('');
  const [dueDate, setDueDate] = useState('');
  const [recurrenceFreq, setRecurrenceFreq] = useState<'NONE' | 'DAILY' | 'WEEKLY' | 'MONTHLY' | 'CUSTOM'>('NONE');
  const [recurrenceInterval, setRecurrenceInterval] = useState<number>(1);
  const [selectedAssignees, setSelectedAssignees] = useState<string[]>([]);
  const [projectMembers, setProjectMembers] = useState<ProjectMember[]>([]);

  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (isOpen) {
      if (defaultListId) setTodoListId(defaultListId);
      else if (todoLists.length > 0) setTodoListId(todoLists[0].id);

      getProjectMembers(projectId).then(setProjectMembers);
    }
  }, [isOpen, projectId, defaultListId, todoLists]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!title.trim() || !todoListId) {
      setError('Task title and To-Do list selection are required.');
      return;
    }

    if (startDate && dueDate && new Date(dueDate) < new Date(startDate)) {
      setError('Due date cannot be before start date.');
      return;
    }

    setError(null);
    setIsLoading(true);

    const { error: err } = await createTask({
      project_id: projectId,
      todo_list_id: todoListId,
      title,
      description,
      status,
      priority,
      start_date: startDate || undefined,
      due_date: dueDate || undefined,
      created_by: profile?.id,
      assignee_ids: selectedAssignees,
      recurrence_pattern:
        recurrenceFreq !== 'NONE'
          ? {
              frequency: recurrenceFreq,
              interval: recurrenceFreq === 'CUSTOM' ? recurrenceInterval : 1,
            }
          : undefined,
    });

    setIsLoading(false);

    if (err) {
      setError(err.message || 'Failed to create task.');
    } else {
      setTitle('');
      setDescription('');
      setSelectedAssignees([]);
      onSuccess();
      onClose();
    }
  };

  const toggleAssignee = (userId: string) => {
    if (selectedAssignees.includes(userId)) {
      setSelectedAssignees(selectedAssignees.filter((id) => id !== userId));
    } else {
      setSelectedAssignees([...selectedAssignees, userId]);
    }
  };

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title="Create New Task"
      description="Add a task item with assignees, priorities, and deadlines."
      maxWidth="lg"
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        {error && (
          <div className="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-xs font-medium text-rose-600 dark:text-rose-400">
            {error}
          </div>
        )}

        <Input
          label="Task Title *"
          placeholder="e.g. Implement Supabase Realtime client listener"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          leftIcon={<CheckSquare className="w-4 h-4" />}
          required
        />

        <div>
          <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            To-Do List Category *
          </label>
          <select
            value={todoListId}
            onChange={(e) => setTodoListId(e.target.value)}
            className="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
            required
          >
            {todoLists.map((l) => (
              <option key={l.id} value={l.id}>
                {l.title}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            Task Description
          </label>
          <textarea
            rows={3}
            placeholder="Detailed instructions, requirements, or links..."
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            className="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
          />
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Status Pipeline
            </label>
            <select
              value={status}
              onChange={(e) => setStatus(e.target.value as TaskStatusType)}
              className="w-full px-3 py-2 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none"
            >
              <option value="NOT_STARTED">NOT_STARTED</option>
              <option value="IN_PROGRESS">IN_PROGRESS</option>
              <option value="WAITING">WAITING</option>
              <option value="IN_REVIEW">IN_REVIEW</option>
              <option value="COMPLETED">COMPLETED</option>
            </select>
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Priority Level
            </label>
            <select
              value={priority}
              onChange={(e) => setPriority(e.target.value as TaskPriorityType)}
              className="w-full px-3 py-2 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none"
            >
              <option value="LOW">LOW</option>
              <option value="MEDIUM">MEDIUM</option>
              <option value="HIGH">HIGH</option>
              <option value="URGENT">URGENT</option>
            </select>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Input
            label="Start Date"
            type="date"
            value={startDate}
            onChange={(e) => setStartDate(e.target.value)}
          />
          <Input
            label="Due Date (Deadline)"
            type="date"
            value={dueDate}
            onChange={(e) => setDueDate(e.target.value)}
          />
        </div>

        {/* Recurrence Rule */}
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-800">
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Recurrence Schedule
            </label>
            <select
              value={recurrenceFreq}
              onChange={(e) => setRecurrenceFreq(e.target.value as any)}
              className="w-full px-3 py-2 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none"
            >
              <option value="NONE">Does not repeat</option>
              <option value="DAILY">Daily (Repeats every day)</option>
              <option value="WEEKLY">Weekly (Repeats every 7 days)</option>
              <option value="MONTHLY">Monthly (Repeats every month)</option>
              <option value="CUSTOM">Custom Interval (Days)</option>
            </select>
          </div>
          {recurrenceFreq === 'CUSTOM' && (
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Repeat Every (Days)
              </label>
              <input
                type="number"
                min="1"
                max="365"
                value={recurrenceInterval}
                onChange={(e) => setRecurrenceInterval(parseInt(e.target.value) || 1)}
                className="w-full px-3 py-2 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none"
              />
            </div>
          )}
        </div>

        {/* Assignees Selector */}
        <div>
          <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5 flex items-center gap-1">
            <Users className="w-3.5 h-3.5" /> Select Assignees (Project Members Only)
          </label>
          <div className="flex flex-wrap gap-2 max-h-32 overflow-y-auto custom-scrollbar p-2 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-800/40">
            {projectMembers.map((pm) => {
              const isSelected = selectedAssignees.includes(pm.user_id);
              return (
                <button
                  key={pm.id}
                  type="button"
                  onClick={() => toggleAssignee(pm.user_id)}
                  className={`px-3 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-2 border transition-all ${
                    isSelected
                      ? 'bg-brand-600 text-white border-brand-600 shadow-xs'
                      : 'bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100'
                  }`}
                >
                  {pm.profile?.full_name}
                </button>
              );
            })}
          </div>
        </div>

        <div className="flex justify-end gap-3 pt-2">
          <Button variant="outline" type="button" onClick={onClose}>
            Cancel
          </Button>
          <Button variant="primary" type="submit" isLoading={isLoading}>
            Create Task
          </Button>
        </div>
      </form>
    </Modal>
  );
}
