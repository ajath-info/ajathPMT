import React, { useState } from 'react';
import { AlertTriangle, X } from 'lucide-react';
import { KanbanColumn, Task } from '../../types';
import { deleteKanbanColumn } from '../../services/kanbanService';
import { Button } from '../common/Button';

interface DeleteKanbanColumnModalProps {
  isOpen: boolean;
  onClose: () => void;
  column: KanbanColumn | null;
  allColumns: KanbanColumn[];
  columnTasks: Task[];
  onSuccess: () => void;
}

export function DeleteKanbanColumnModal({
  isOpen,
  onClose,
  column,
  allColumns,
  columnTasks,
  onSuccess,
}: DeleteKanbanColumnModalProps) {
  const [targetColumnId, setTargetColumnId] = useState<string>('');
  const [submitting, setSubmitting] = useState(false);

  if (!isOpen || !column) return null;

  const otherColumns = allColumns.filter((c) => c.id !== column.id);
  const defaultTarget = otherColumns[0]?.id || '';

  const handleDelete = async () => {
    setSubmitting(true);
    try {
      await deleteKanbanColumn(
        column.id,
        columnTasks.length > 0 ? targetColumnId || defaultTarget : undefined
      );
      onSuccess();
      onClose();
    } catch (err) {
      console.error('Failed to delete column', err);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-5">
        <div className="flex items-center justify-between">
          <div className="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
            <AlertTriangle className="w-6 h-6" />
          </div>
          <button
            onClick={onClose}
            className="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        <div>
          <h3 className="text-lg font-extrabold text-slate-900 dark:text-slate-100">
            Delete Column "{column.name}"?
          </h3>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
            This action will remove the workflow column from your Kanban board.
          </p>
        </div>

        {columnTasks.length > 0 && (
          <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
            <p className="text-xs font-semibold text-slate-700 dark:text-slate-300">
              This column currently contains {columnTasks.length} task(s). Move them to:
            </p>

            {otherColumns.length > 0 ? (
              <select
                value={targetColumnId || defaultTarget}
                onChange={(e) => setTargetColumnId(e.target.value)}
                className="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
              >
                {otherColumns.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.name}
                  </option>
                ))}
              </select>
            ) : (
              <p className="text-xs text-rose-500 font-semibold">
                No other column available. Delete will unassign tasks from columns.
              </p>
            )}
          </div>
        )}

        <div className="flex items-center justify-end gap-3 pt-2">
          <Button variant="outline" onClick={onClose}>
            Cancel
          </Button>
          <Button
            variant="danger"
            isLoading={submitting}
            onClick={handleDelete}
          >
            Delete Column
          </Button>
        </div>
      </div>
    </div>
  );
}
