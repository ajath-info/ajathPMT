import React, { useState, useEffect } from 'react';
import { X, Layout, Sparkles } from 'lucide-react';
import { KanbanColumn, TaskStatusType } from '../../types';
import { createKanbanColumn, updateKanbanColumn } from '../../services/kanbanService';
import { Button } from '../common/Button';

interface CreateKanbanColumnModalProps {
  isOpen: boolean;
  onClose: () => void;
  projectId: string;
  columnToEdit?: KanbanColumn | null;
  onSuccess: () => void;
}

const COLOR_OPTIONS = [
  { label: 'Slate', hex: '#64748b' },
  { label: 'Blue', hex: '#3b82f6' },
  { label: 'Amber', hex: '#f59e0b' },
  { label: 'Purple', hex: '#8b5cf6' },
  { label: 'Emerald', hex: '#10b981' },
  { label: 'Rose', hex: '#f43f5e' },
  { label: 'Indigo', hex: '#6366f1' },
  { label: 'Cyan', hex: '#06b6d4' },
];

const STATUS_OPTIONS: { label: string; value: TaskStatusType | '' }[] = [
  { label: 'None (No automatic status update)', value: '' },
  { label: 'Not Started', value: 'NOT_STARTED' },
  { label: 'In Progress', value: 'IN_PROGRESS' },
  { label: 'Waiting', value: 'WAITING' },
  { label: 'In Review', value: 'IN_REVIEW' },
  { label: 'Completed (Done)', value: 'COMPLETED' },
];

export function CreateKanbanColumnModal({
  isOpen,
  onClose,
  projectId,
  columnToEdit,
  onSuccess,
}: CreateKanbanColumnModalProps) {
  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [color, setColor] = useState('#3b82f6');
  const [mappedStatus, setMappedStatus] = useState<TaskStatusType | ''>('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (columnToEdit) {
      setName(columnToEdit.name);
      setDescription(columnToEdit.description || '');
      setColor(columnToEdit.color || '#3b82f6');
      setMappedStatus(columnToEdit.mapped_status || '');
    } else {
      setName('');
      setDescription('');
      setColor('#3b82f6');
      setMappedStatus('');
    }
    setError(null);
  }, [columnToEdit, isOpen]);

  if (!isOpen) return null;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim()) {
      setError('Column name is required.');
      return;
    }

    setSubmitting(true);
    setError(null);
    try {
      if (columnToEdit) {
        await updateKanbanColumn(columnToEdit.id, {
          name: name.trim(),
          description: description.trim() || undefined,
          color,
          mapped_status: mappedStatus ? (mappedStatus as TaskStatusType) : undefined,
        });
      } else {
        await createKanbanColumn({
          project_id: projectId,
          name: name.trim(),
          description: description.trim() || undefined,
          color,
          mapped_status: mappedStatus ? (mappedStatus as TaskStatusType) : undefined,
        });
      }
      onSuccess();
      onClose();
    } catch (err: any) {
      setError(err.message || 'Failed to save column');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        {/* Header */}
        <div className="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="p-2.5 rounded-2xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400">
              <Layout className="w-5 h-5" />
            </div>
            <div>
              <h3 className="text-lg font-extrabold text-slate-900 dark:text-slate-100">
                {columnToEdit ? 'Edit Kanban Column' : 'Create Kanban Column'}
              </h3>
              <p className="text-xs text-slate-500">
                Configure workflow column, status mapping, and visual accent
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Form Body */}
        <form onSubmit={handleSubmit} className="p-6 space-y-5">
          {error && (
            <div className="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-xs font-semibold text-rose-600 dark:text-rose-400">
              {error}
            </div>
          )}

          <div className="space-y-1.5">
            <label className="text-xs font-bold text-slate-700 dark:text-slate-300">
              Column Name *
            </label>
            <input
              type="text"
              required
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="e.g. Quality Assurance, Ready for Release"
              className="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
            />
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-bold text-slate-700 dark:text-slate-300">
              Description (Optional)
            </label>
            <textarea
              rows={2}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              placeholder="Brief overview of what belongs in this stage..."
              className="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 resize-none"
            />
          </div>

          {/* Mapped Task Status */}
          <div className="space-y-1.5">
            <label className="text-xs font-bold text-slate-700 dark:text-slate-300">
              Mapped Task Status
            </label>
            <p className="text-[11px] text-slate-500">
              Moving cards into this column will automatically update the task's status.
            </p>
            <select
              value={mappedStatus}
              onChange={(e) => setMappedStatus(e.target.value as TaskStatusType | '')}
              className="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
            >
              {STATUS_OPTIONS.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </select>
          </div>

          {/* Color Selection */}
          <div className="space-y-2">
            <label className="text-xs font-bold text-slate-700 dark:text-slate-300">
              Visual Accent Color
            </label>
            <div className="flex items-center gap-2 flex-wrap">
              {COLOR_OPTIONS.map((c) => (
                <button
                  key={c.hex}
                  type="button"
                  onClick={() => setColor(c.hex)}
                  className={`w-8 h-8 rounded-full transition-transform border-2 ${
                    color === c.hex
                      ? 'scale-110 border-slate-900 dark:border-white ring-2 ring-brand-500/30'
                      : 'border-transparent hover:scale-105'
                  }`}
                  style={{ backgroundColor: c.hex }}
                  title={c.label}
                />
              ))}
            </div>
          </div>

          {/* Footer Buttons */}
          <div className="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
            <Button type="button" variant="outline" onClick={onClose}>
              Cancel
            </Button>
            <Button type="submit" variant="primary" isLoading={submitting}>
              {columnToEdit ? 'Save Changes' : 'Create Column'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}
