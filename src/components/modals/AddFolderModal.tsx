import React, { useState } from 'react';
import { FolderPlus, Folder, Check } from 'lucide-react';
import { Modal } from '../common/Modal';
import { Input } from '../common/Input';
import { Button } from '../common/Button';
import { useProject } from '../../context/ProjectContext';

interface AddFolderModalProps {
  isOpen: boolean;
  onClose: () => void;
  onCreateFolder: (folderName: string, initialProjectIds?: string[]) => void;
}

export function AddFolderModal({ isOpen, onClose, onCreateFolder }: AddFolderModalProps) {
  const { projects } = useProject();
  const [folderName, setFolderName] = useState('');
  const [selectedProjectIds, setSelectedProjectIds] = useState<Set<string>>(new Set());
  const [error, setError] = useState<string | null>(null);

  const toggleProject = (projectId: string) => {
    setSelectedProjectIds((prev) => {
      const next = new Set(prev);
      if (next.has(projectId)) {
        next.delete(projectId);
      } else {
        next.add(projectId);
      }
      return next;
    });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!folderName.trim()) {
      setError('Please enter a folder name.');
      return;
    }
    onCreateFolder(folderName.trim(), Array.from(selectedProjectIds));
    setFolderName('');
    setSelectedProjectIds(new Set());
    setError(null);
    onClose();
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="Add a Folder" maxWidth="md">
      <form onSubmit={handleSubmit} className="space-y-4 pt-1">
        <div className="flex items-center gap-3 p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200 border border-amber-200/60 dark:border-amber-800/60">
          <Folder className="w-5 h-5 text-amber-600 shrink-0" />
          <p className="text-xs">
            Folders keep projects neatly categorized together on your home launchpad.
          </p>
        </div>

        <Input
          label="Folder Name"
          placeholder="e.g., Client Work, Internal Apps, Q3 Launches"
          value={folderName}
          onChange={(e) => {
            setFolderName(e.target.value);
            if (error) setError(null);
          }}
          error={error || undefined}
          autoFocus
        />

        {/* Project Selection Checklist */}
        {projects.length > 0 && (
          <div className="space-y-2 pt-1">
            <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300">
              Put these projects in this folder right away (optional):
            </label>
            <div className="max-h-48 overflow-y-auto space-y-1.5 pr-1 border border-slate-200 dark:border-slate-800 rounded-2xl p-2 bg-slate-50/50 dark:bg-slate-900/50">
              {projects.map((p) => {
                const isChecked = selectedProjectIds.has(p.id);
                return (
                  <div
                    key={p.id}
                    onClick={() => toggleProject(p.id)}
                    className={`p-2.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between text-xs select-none ${
                      isChecked
                        ? 'border-amber-400 bg-amber-50/70 dark:bg-amber-950/30'
                        : 'border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50'
                    }`}
                  >
                    <span className="font-bold text-slate-800 dark:text-slate-200 truncate pr-2">
                      {p.name}
                    </span>
                    <div
                      className={`w-4 h-4 rounded flex items-center justify-center transition-colors shrink-0 ${
                        isChecked
                          ? 'bg-amber-500 text-white shadow-xs'
                          : 'border border-slate-300 dark:border-slate-600'
                      }`}
                    >
                      {isChecked && <Check className="w-3 h-3 stroke-[3]" />}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        <div className="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
          <Button variant="outline" type="button" onClick={onClose}>
            Cancel
          </Button>
          <Button
            variant="primary"
            type="submit"
            leftIcon={<FolderPlus className="w-4 h-4" />}
          >
            Create Folder
          </Button>
        </div>
      </form>
    </Modal>
  );
}
