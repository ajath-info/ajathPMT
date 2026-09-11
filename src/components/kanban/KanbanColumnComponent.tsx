import React, { useState } from 'react';
import { useSortable, SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import {
  MoreVertical,
  Plus,
  Edit2,
  Trash2,
  GripHorizontal,
  Check,
  X,
} from 'lucide-react';
import { KanbanColumn, Task } from '../../types';
import { KanbanCard } from './KanbanCard';

interface KanbanColumnComponentProps {
  column: KanbanColumn;
  tasks: Task[];
  onTaskClick: (task: Task) => void;
  onEditColumn: (column: KanbanColumn) => void;
  onDeleteColumn: (column: KanbanColumn) => void;
  onQuickAddTask: (columnId: string, title: string) => Promise<void>;
  canManageBoard: boolean;
}

export function KanbanColumnComponent({
  column,
  tasks,
  onTaskClick,
  onEditColumn,
  onDeleteColumn,
  onQuickAddTask,
  canManageBoard,
}: KanbanColumnComponentProps) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({
    id: column.id,
    data: { type: 'Column', column },
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  };

  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isQuickAdding, setIsQuickAdding] = useState(false);
  const [quickTitle, setQuickTitle] = useState('');
  const [addingLoading, setAddingLoading] = useState(false);

  const handleQuickSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!quickTitle.trim()) return;
    setAddingLoading(true);
    try {
      await onQuickAddTask(column.id, quickTitle.trim());
      setQuickTitle('');
      setIsQuickAdding(false);
    } catch (err) {
      console.error(err);
    } finally {
      setAddingLoading(false);
    }
  };

  const taskIds = tasks.map((t) => t.id);

  return (
    <div
      ref={setNodeRef}
      style={style}
      className={`w-80 shrink-0 flex flex-col max-h-full bg-slate-100/80 dark:bg-slate-900/60 rounded-3xl border border-slate-200/70 dark:border-slate-800/80 shadow-xs transition-shadow ${
        isDragging ? 'opacity-40 border-brand-500 scale-[0.98]' : ''
      }`}
    >
      {/* Column Header */}
      <div className="p-4 flex items-center justify-between gap-2 border-b border-slate-200/60 dark:border-slate-800/60">
        <div className="flex items-center gap-2.5 min-w-0">
          <div
            {...attributes}
            {...listeners}
            className="cursor-grab active:cursor-grabbing text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 p-0.5"
            title="Drag column"
          >
            <GripHorizontal className="w-4 h-4" />
          </div>

          <div
            className="w-3 h-3 rounded-full shrink-0"
            style={{ backgroundColor: column.color || '#64748b' }}
          />

          <h3 className="font-extrabold text-sm text-slate-900 dark:text-slate-100 truncate tracking-tight">
            {column.name}
          </h3>

          <span className="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-200/80 dark:bg-slate-800 text-slate-700 dark:text-slate-300 shrink-0">
            {tasks.length}
          </span>
        </div>

        {/* Column Actions Dropdown */}
        {canManageBoard && (
          <div className="relative">
            <button
              onClick={() => setIsMenuOpen(!isMenuOpen)}
              className="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800"
            >
              <MoreVertical className="w-4 h-4" />
            </button>

            {isMenuOpen && (
              <>
                <div
                  className="fixed inset-0 z-10"
                  onClick={() => setIsMenuOpen(false)}
                />
                <div className="absolute right-0 mt-1 w-44 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-20 animate-in fade-in zoom-in-95 duration-150">
                  <button
                    onClick={() => {
                      setIsMenuOpen(false);
                      onEditColumn(column);
                    }}
                    className="w-full text-left px-3.5 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center gap-2"
                  >
                    <Edit2 className="w-3.5 h-3.5 text-slate-400" />
                    Edit Column
                  </button>

                  <button
                    onClick={() => {
                      setIsMenuOpen(false);
                      onDeleteColumn(column);
                    }}
                    className="w-full text-left px-3.5 py-2 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center gap-2"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                    Delete Column
                  </button>
                </div>
              </>
            )}
          </div>
        )}
      </div>

      {/* Column Body: Task Cards List */}
      <div className="p-3 flex-1 overflow-y-auto min-h-[160px] max-h-[calc(100vh-280px)] space-y-3">
        <SortableContext items={taskIds} strategy={verticalListSortingStrategy}>
          {tasks.length === 0 ? (
            <div className="h-32 flex flex-col items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center">
              <p className="text-xs font-medium text-slate-400 dark:text-slate-500">
                No tasks in this column
              </p>
            </div>
          ) : (
            tasks.map((task) => (
              <KanbanCard key={task.id} task={task} onClick={onTaskClick} />
            ))
          )}
        </SortableContext>
      </div>

      {/* Column Footer: Quick Add Task */}
      <div className="p-3 border-t border-slate-200/60 dark:border-slate-800/60">
        {isQuickAdding ? (
          <form onSubmit={handleQuickSubmit} className="space-y-2">
            <input
              type="text"
              autoFocus
              value={quickTitle}
              onChange={(e) => setQuickTitle(e.target.value)}
              placeholder="Task title..."
              className="w-full px-3 py-1.5 text-xs rounded-xl border border-brand-500 dark:border-brand-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
            />
            <div className="flex items-center justify-end gap-1.5">
              <button
                type="button"
                onClick={() => setIsQuickAdding(false)}
                className="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
              >
                <X className="w-3.5 h-3.5" />
              </button>
              <button
                type="submit"
                disabled={addingLoading || !quickTitle.trim()}
                className="px-2.5 py-1 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-xs font-bold flex items-center gap-1 disabled:opacity-50"
              >
                <Check className="w-3.5 h-3.5" />
                Add
              </button>
            </div>
          </form>
        ) : (
          <button
            onClick={() => setIsQuickAdding(true)}
            className="w-full py-2 px-3 rounded-xl border border-transparent hover:border-slate-200 dark:hover:border-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-white dark:hover:bg-slate-900 transition-colors flex items-center justify-center gap-1.5"
          >
            <Plus className="w-4 h-4" />
            Add Task
          </button>
        )}
      </div>
    </div>
  );
}
