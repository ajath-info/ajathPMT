import React, { useState, useEffect } from 'react';
import { List, FileText } from 'lucide-react';
import { Modal } from '../common/Modal';
import { Input } from '../common/Input';
import { Button } from '../common/Button';
import { useAuth } from '../../context/AuthContext';
import { createTodoList, updateTodoList } from '../../services/todoService';
import { TodoList } from '../../types';

interface CreateTodoListModalProps {
  isOpen: boolean;
  onClose: () => void;
  projectId: string;
  listToEdit?: TodoList | null;
  onSuccess: () => void;
}

export function CreateTodoListModal({
  isOpen,
  onClose,
  projectId,
  listToEdit,
  onSuccess,
}: CreateTodoListModalProps) {
  const { profile } = useAuth();
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (listToEdit) {
      setTitle(listToEdit.title);
      setDescription(listToEdit.description || '');
    } else {
      setTitle('');
      setDescription('');
    }
  }, [listToEdit, isOpen]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!title.trim()) {
      setError('To-Do List title is required.');
      return;
    }

    setError(null);
    setIsLoading(true);

    if (listToEdit) {
      const { error: err } = await updateTodoList(listToEdit.id, { title, description });
      setIsLoading(false);
      if (err) {
        setError(err.message || 'Failed to update list.');
      } else {
        onSuccess();
        onClose();
      }
    } else {
      const { error: err } = await createTodoList({
        project_id: projectId,
        title,
        description,
        created_by: profile?.id,
      });
      setIsLoading(false);
      if (err) {
        setError(err.message || 'Failed to create list.');
      } else {
        onSuccess();
        onClose();
      }
    }
  };

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title={listToEdit ? 'Edit To-Do List' : 'Create New To-Do List'}
      description="Organize tasks into categorized milestone lists for your project workspace."
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        {error && (
          <div className="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-xs font-medium text-rose-600 dark:text-rose-400">
            {error}
          </div>
        )}

        <Input
          label="To-Do List Title *"
          placeholder="e.g. Frontend Core Components"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          leftIcon={<List className="w-4 h-4" />}
          required
        />

        <div>
          <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            Description (Optional)
          </label>
          <textarea
            rows={2}
            placeholder="Brief scope of tasks in this list..."
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            className="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
          />
        </div>

        <div className="flex justify-end gap-3 pt-2">
          <Button variant="outline" type="button" onClick={onClose}>
            Cancel
          </Button>
          <Button variant="primary" type="submit" isLoading={isLoading}>
            {listToEdit ? 'Save List Changes' : 'Create To-Do List'}
          </Button>
        </div>
      </form>
    </Modal>
  );
}
