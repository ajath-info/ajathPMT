import React from 'react';
import { CheckCircle2, Circle, Clock, MessageSquare, CheckSquare, AlertTriangle } from 'lucide-react';
import { Task, TaskPriorityType, TaskStatusType } from '../../types';
import { Avatar } from '../common/Avatar';
import { Badge } from '../common/Badge';
import { formatDate } from '../../lib/utils';

interface TaskRowProps {
  task: Task;
  onToggleComplete: (task: Task) => void;
  onClickTask: (task: Task) => void;
}

export function TaskRow({ task, onToggleComplete, onClickTask }: TaskRowProps) {
  const isCompleted = task.status === 'COMPLETED';

  const priorityColors: Record<TaskPriorityType, 'neutral' | 'brand' | 'warning' | 'danger'> = {
    LOW: 'neutral',
    MEDIUM: 'brand',
    HIGH: 'warning',
    URGENT: 'danger',
  };

  const isOverdue =
    !isCompleted &&
    task.due_date &&
    new Date(task.due_date) < new Date(new Date().setHours(0, 0, 0, 0));

  return (
    <div
      onClick={() => onClickTask(task)}
      className={`group p-3 sm:p-4 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-4 ${
        isCompleted
          ? 'bg-slate-50/50 dark:bg-slate-800/20 border-slate-200/60 dark:border-slate-800/60 opacity-75'
          : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-brand-500/50 hover:shadow-md'
      }`}
    >
      <div className="flex items-center gap-3.5 min-w-0 flex-1">
        {/* Completion Checkbox */}
        <button
          type="button"
          onClick={(e) => {
            e.stopPropagation();
            onToggleComplete(task);
          }}
          className={`shrink-0 rounded-lg p-0.5 transition-transform active:scale-95 ${
            isCompleted
              ? 'text-emerald-500'
              : 'text-slate-300 dark:text-slate-700 hover:text-brand-500'
          }`}
        >
          {isCompleted ? (
            <CheckCircle2 className="w-5 h-5 fill-emerald-50 dark:fill-emerald-950/50" />
          ) : (
            <Circle className="w-5 h-5" />
          )}
        </button>

        {/* Title and Metadata */}
        <div className="min-w-0 flex-1">
          <div className="flex items-center gap-2">
            <h4
              className={`text-sm font-semibold truncate transition-colors ${
                isCompleted
                  ? 'line-through text-slate-400 dark:text-slate-500'
                  : 'text-slate-900 dark:text-slate-100 group-hover:text-brand-600 dark:group-hover:text-brand-400'
              }`}
            >
              {task.title}
            </h4>

            {task.labels && task.labels.length > 0 && (
              <div className="hidden sm:flex items-center gap-1">
                {task.labels.map((lbl) => (
                  <span
                    key={lbl.id}
                    className="px-2 py-0.5 text-[10px] font-bold rounded-md text-white"
                    style={{ backgroundColor: lbl.color || '#6366f1' }}
                  >
                    {lbl.name}
                  </span>
                ))}
              </div>
            )}
          </div>

          <div className="flex items-center gap-3 text-xs text-slate-400 mt-1">
            {task.due_date && (
              <span
                className={`flex items-center gap-1 font-medium ${
                  isOverdue ? 'text-rose-500 font-bold' : 'text-slate-500 dark:text-slate-400'
                }`}
              >
                {isOverdue ? <AlertTriangle className="w-3.5 h-3.5" /> : <Clock className="w-3.5 h-3.5" />}
                {formatDate(task.due_date)}
              </span>
            )}

            {task.subtasks && task.subtasks.length > 0 && (
              <span className="flex items-center gap-1 font-semibold text-slate-500">
                <CheckSquare className="w-3.5 h-3.5" />
                {task.subtasks_completed_count || 0}/{task.subtasks.length}
              </span>
            )}

            {task.comments_count ? (
              <span className="flex items-center gap-1 text-slate-500 font-semibold">
                <MessageSquare className="w-3.5 h-3.5" />
                {task.comments_count}
              </span>
            ) : null}
          </div>
        </div>
      </div>

      {/* Priority & Assignees Right Column */}
      <div className="flex items-center gap-3 shrink-0">
        <Badge variant={priorityColors[task.priority]}>{task.priority}</Badge>

        <div className="hidden sm:flex -space-x-1.5 overflow-hidden">
          {task.assignees && task.assignees.length > 0 ? (
            task.assignees.slice(0, 2).map((a) => (
              <Avatar key={a.id} src={a.avatar_url} name={a.full_name} size="xs" />
            ))
          ) : (
            <span className="text-[10px] text-slate-400 italic">Unassigned</span>
          )}
        </div>
      </div>
    </div>
  );
}
