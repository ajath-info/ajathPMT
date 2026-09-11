import React from 'react';
import {
  Search,
  Filter,
  User,
  Tag,
  AlertCircle,
  Clock,
  CheckCircle2,
  XCircle,
} from 'lucide-react';
import { BoardFilter, Profile, Label, TaskPriorityType, TaskStatusType } from '../../types';

interface KanbanFilterBarProps {
  filters: BoardFilter;
  onChangeFilters: (newFilters: BoardFilter) => void;
  members: Profile[];
  labels: Label[];
  currentUserId?: string;
}

export function KanbanFilterBar({
  filters,
  onChangeFilters,
  members,
  labels,
  currentUserId,
}: KanbanFilterBarProps) {
  const hasActiveFilters =
    Boolean(filters.search_query) ||
    Boolean(filters.assignee_id) ||
    Boolean(filters.status) ||
    Boolean(filters.priority) ||
    Boolean(filters.label_id) ||
    Boolean(filters.my_tasks_only) ||
    Boolean(filters.overdue_only) ||
    Boolean(filters.completed_only) ||
    Boolean(filters.blocked_only);

  const resetFilters = () => {
    onChangeFilters({});
  };

  return (
    <div className="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-3">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-3">
        {/* Search input */}
        <div className="relative flex-1 min-w-[220px]">
          <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            type="text"
            value={filters.search_query || ''}
            onChange={(e) =>
              onChangeFilters({ ...filters, search_query: e.target.value })
            }
            placeholder="Search cards by title or description..."
            className="w-full pl-10 pr-4 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
          />
          {filters.search_query && (
            <button
              onClick={() => onChangeFilters({ ...filters, search_query: '' })}
              className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
            >
              <XCircle className="w-4 h-4" />
            </button>
          )}
        </div>

        {/* Dropdowns */}
        <div className="flex items-center gap-2 flex-wrap">
          {/* Assignee Filter */}
          <select
            value={filters.assignee_id || ''}
            onChange={(e) =>
              onChangeFilters({
                ...filters,
                assignee_id: e.target.value || undefined,
              })
            }
            className="px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
          >
            <option value="">All Assignees</option>
            {members.map((m) => (
              <option key={m.id} value={m.id}>
                {m.full_name}
              </option>
            ))}
          </select>

          {/* Priority Filter */}
          <select
            value={filters.priority || ''}
            onChange={(e) =>
              onChangeFilters({
                ...filters,
                priority: (e.target.value as TaskPriorityType) || undefined,
              })
            }
            className="px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
          >
            <option value="">All Priorities</option>
            <option value="LOW">Low</option>
            <option value="MEDIUM">Medium</option>
            <option value="HIGH">High</option>
            <option value="URGENT">Urgent</option>
          </select>

          {/* Label Filter */}
          {labels.length > 0 && (
            <select
              value={filters.label_id || ''}
              onChange={(e) =>
                onChangeFilters({
                  ...filters,
                  label_id: e.target.value || undefined,
                })
              }
              className="px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
            >
              <option value="">All Labels</option>
              {labels.map((l) => (
                <option key={l.id} value={l.id}>
                  {l.name}
                </option>
              ))}
            </select>
          )}

          {hasActiveFilters && (
            <button
              onClick={resetFilters}
              className="px-3 py-2 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors"
            >
              Clear Filters
            </button>
          )}
        </div>
      </div>

      {/* Quick Filter Toggle Pills */}
      <div className="flex items-center gap-2 flex-wrap pt-1">
        {currentUserId && (
          <button
            onClick={() =>
              onChangeFilters({
                ...filters,
                my_tasks_only: !filters.my_tasks_only,
              })
            }
            className={`px-3 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors border ${
              filters.my_tasks_only
                ? 'bg-brand-50 dark:bg-brand-950/80 text-brand-700 dark:text-brand-300 border-brand-300 dark:border-brand-800'
                : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900'
            }`}
          >
            <User className="w-3.5 h-3.5" />
            My Tasks
          </button>
        )}

        <button
          onClick={() =>
            onChangeFilters({
              ...filters,
              overdue_only: !filters.overdue_only,
            })
          }
          className={`px-3 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors border ${
            filters.overdue_only
              ? 'bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border-rose-300 dark:border-rose-800'
              : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900'
          }`}
        >
          <Clock className="w-3.5 h-3.5" />
          Overdue
        </button>

        <button
          onClick={() =>
            onChangeFilters({
              ...filters,
              blocked_only: !filters.blocked_only,
            })
          }
          className={`px-3 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors border ${
            filters.blocked_only
              ? 'bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border-amber-300 dark:border-amber-800'
              : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900'
          }`}
        >
          <AlertCircle className="w-3.5 h-3.5" />
          Blocked
        </button>

        <button
          onClick={() =>
            onChangeFilters({
              ...filters,
              completed_only: !filters.completed_only,
            })
          }
          className={`px-3 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors border ${
            filters.completed_only
              ? 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800'
              : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900'
          }`}
        >
          <CheckCircle2 className="w-3.5 h-3.5" />
          Completed
        </button>
      </div>
    </div>
  );
}
