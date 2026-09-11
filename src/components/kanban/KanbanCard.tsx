import React from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import {
  Calendar,
  CheckSquare,
  MessageSquare,
  AlertCircle,
  GripVertical,
  Clock,
  Paperclip,
} from 'lucide-react';
import { Task, TaskPriorityType } from '../../types';

interface KanbanCardProps {
  task: Task;
  onClick: (task: Task) => void;
}

const PRIORITY_CONFIG: Record<
  TaskPriorityType,
  { label: string; bg: string; text: string; border: string }
> = {
  LOW: {
    label: 'Low',
    bg: 'bg-slate-100 dark:bg-slate-800',
    text: 'text-slate-600 dark:text-slate-300',
    border: 'border-slate-200 dark:border-slate-700',
  },
  MEDIUM: {
    label: 'Medium',
    bg: 'bg-blue-50 dark:bg-blue-950/50',
    text: 'text-blue-600 dark:text-blue-400',
    border: 'border-blue-200 dark:border-blue-800',
  },
  HIGH: {
    label: 'High',
    bg: 'bg-amber-50 dark:bg-amber-950/50',
    text: 'text-amber-600 dark:text-amber-400',
    border: 'border-amber-200 dark:border-amber-800',
  },
  URGENT: {
    label: 'Urgent',
    bg: 'bg-rose-50 dark:bg-rose-950/50',
    text: 'text-rose-600 dark:text-rose-400',
    border: 'border-rose-200 dark:border-rose-800',
  },
};

export function KanbanCard({ task, onClick }: KanbanCardProps) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: task.id });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  };

  const isOverdue =
    task.due_date &&
    task.status !== 'COMPLETED' &&
    new Date(task.due_date) < new Date(new Date().setHours(0, 0, 0, 0));

  const priorityStyle = PRIORITY_CONFIG[task.priority] || PRIORITY_CONFIG.MEDIUM;
  const totalSubtasks = task.subtasks?.length || 0;
  const completedSubtasks =
    task.subtasks_completed_count ??
    (task.subtasks?.filter((s) => s.status === 'COMPLETED').length || 0);

  const isBlocked = (task.blocked_by && task.blocked_by.length > 0) || false;

  return (
    <div
      ref={setNodeRef}
      style={style}
      className={`group relative bg-white dark:bg-slate-900 rounded-2xl p-4 border transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md ${
        isDragging
          ? 'opacity-40 border-brand-500 scale-[1.02] z-50 shadow-xl ring-2 ring-brand-500/20'
          : task.status === 'COMPLETED'
          ? 'border-slate-200/60 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 opacity-80'
          : 'border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'
      }`}
      onClick={() => onClick(task)}
    >
      {/* Drag Handle & Priority Badge */}
      <div className="flex items-center justify-between gap-2 mb-2.5">
        <div className="flex items-center gap-1.5 flex-wrap">
          <span
            className={`px-2 py-0.5 rounded-full text-[11px] font-bold border ${priorityStyle.bg} ${priorityStyle.text} ${priorityStyle.border}`}
          >
            {priorityStyle.label}
          </span>

          {isBlocked && (
            <span className="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-400 border border-amber-300 dark:border-amber-800 flex items-center gap-1">
              <AlertCircle className="w-3 h-3" />
              Blocked
            </span>
          )}
        </div>

        <button
          {...attributes}
          {...listeners}
          onClick={(e) => e.stopPropagation()}
          className="p-1 rounded-lg text-slate-300 dark:text-slate-600 opacity-0 group-hover:opacity-100 hover:text-slate-600 dark:hover:text-slate-300 transition-opacity cursor-grab active:cursor-grabbing"
          title="Drag to reorder"
        >
          <GripVertical className="w-4 h-4" />
        </button>
      </div>

      {/* Title & Description */}
      <div className="space-y-1 mb-3">
        <h4
          className={`text-sm font-bold tracking-tight line-clamp-2 ${
            task.status === 'COMPLETED'
              ? 'line-through text-slate-400 dark:text-slate-500'
              : 'text-slate-900 dark:text-slate-100'
          }`}
        >
          {task.title}
        </h4>
        {task.description && (
          <p className="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
            {task.description}
          </p>
        )}
      </div>

      {/* Labels */}
      {task.labels && task.labels.length > 0 && (
        <div className="flex flex-wrap gap-1 mb-3">
          {task.labels.map((lbl) => (
            <span
              key={lbl.id}
              className="px-2 py-0.5 rounded-md text-[10px] font-semibold text-white tracking-wide shadow-2xs"
              style={{ backgroundColor: lbl.color || '#6366f1' }}
            >
              {lbl.name}
            </span>
          ))}
        </div>
      )}

      {/* Footer Info: Due Date, Subtasks, Comments, Assignees */}
      <div className="pt-2.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
        <div className="flex items-center gap-3">
          {/* Due date */}
          {task.due_date && (
            <div
              className={`flex items-center gap-1 font-medium ${
                isOverdue
                  ? 'text-rose-600 dark:text-rose-400 font-bold'
                  : 'text-slate-500 dark:text-slate-400'
              }`}
            >
              <Calendar className="w-3.5 h-3.5" />
              <span>
                {new Date(task.due_date).toLocaleDateString(undefined, {
                  month: 'short',
                  day: 'numeric',
                })}
              </span>
            </div>
          )}

          {/* Subtask count */}
          {totalSubtasks > 0 && (
            <div className="flex items-center gap-1 font-medium text-slate-500 dark:text-slate-400">
              <CheckSquare className="w-3.5 h-3.5 text-slate-400" />
              <span>
                {completedSubtasks}/{totalSubtasks}
              </span>
            </div>
          )}

          {/* Comment count */}
          {(task.comments_count ?? 0) > 0 && (
            <div className="flex items-center gap-1 font-medium text-slate-500 dark:text-slate-400">
              <MessageSquare className="w-3.5 h-3.5 text-slate-400" />
              <span>{task.comments_count}</span>
            </div>
          )}
        </div>

        {/* Assignee Avatars */}
        {task.assignees && task.assignees.length > 0 && (
          <div className="flex items-center -space-x-1.5 overflow-hidden">
            {task.assignees.slice(0, 3).map((u) => (
              <img
                key={u.id}
                src={
                  u.avatar_url ||
                  `https://ui-avatars.com/api/?name=${encodeURIComponent(
                    u.full_name
                  )}&background=6366f1&color=fff`
                }
                alt={u.full_name}
                title={u.full_name}
                className="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-slate-900 object-cover"
              />
            ))}
            {task.assignees.length > 3 && (
              <span className="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 ring-2 ring-white dark:ring-slate-900 text-[10px] font-bold text-slate-600 dark:text-slate-300">
                +{task.assignees.length - 3}
              </span>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
