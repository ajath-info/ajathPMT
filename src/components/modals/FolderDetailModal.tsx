import React, { useState, useEffect } from 'react';
import { Folder, Trash2, Check, Edit2, X } from 'lucide-react';
import { Modal } from '../common/Modal';
import { Button } from '../common/Button';
import { useProject } from '../../context/ProjectContext';

interface FolderDetailModalProps {
  isOpen: boolean;
  onClose: () => void;
  folderName: string | null;
  onDeleteFolder: (folderName: string) => void;
  onRenameFolder?: (oldName: string, newName: string) => void;
}

export function FolderDetailModal({
  isOpen,
  onClose,
  folderName,
  onDeleteFolder,
  onRenameFolder,
}: FolderDetailModalProps) {
  const { projects } = useProject();

  // Load project IDs assigned to this folder from localStorage
  const [assignedProjectIds, setAssignedProjectIds] = useState<Set<string>>(new Set());
  const [isEditingName, setIsEditingName] = useState(false);
  const [newName, setNewName] = useState('');

  useEffect(() => {
    if (folderName) {
      setNewName(folderName);
      setIsEditingName(false);
      try {
        const stored = localStorage.getItem(`basecamp_folder_${folderName}`);
        if (stored) {
          setAssignedProjectIds(new Set(JSON.parse(stored)));
        } else {
          setAssignedProjectIds(new Set());
        }
      } catch (e) {
        setAssignedProjectIds(new Set());
      }
    }
  }, [folderName, isOpen]);

  const handleSaveRename = (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    if (!folderName || !newName.trim() || newName.trim() === folderName) {
      setIsEditingName(false);
      return;
    }
    onRenameFolder?.(folderName, newName.trim());
    setIsEditingName(false);
  };

  const toggleProject = (projectId: string) => {
    if (!folderName) return;
    setAssignedProjectIds((prev) => {
      const next = new Set(prev);
      if (next.has(projectId)) {
        next.delete(projectId);
      } else {
        next.add(projectId);
      }
      try {
        localStorage.setItem(`basecamp_folder_${folderName}`, JSON.stringify(Array.from(next)));
      } catch (e) {}
      return next;
    });
  };

  if (!folderName) return null;

  const folderProjects = projects.filter((p) => assignedProjectIds.has(p.id));

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="" maxWidth="md">
      <div className="space-y-5 pt-1">
        {/* Header */}
        <div className="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
          <div className="flex items-center gap-3 min-w-0 flex-1">
            {/* Folder badge with rainbow ring */}
            <div className="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center relative shrink-0 shadow-xs">
              <Folder className="w-5 h-5 text-slate-700 dark:text-slate-300" />
              <div className="absolute -bottom-1 -right-1">
                <svg className="w-3.5 h-3.5" viewBox="0 0 20 20" fill="none">
                  <defs>
                    <linearGradient id="modalRainbowRing" x1="0" y1="0" x2="20" y2="20" gradientUnits="userSpaceOnUse">
                      <stop offset="0%" stopColor="#38bdf8" />
                      <stop offset="30%" stopColor="#c084fc" />
                      <stop offset="65%" stopColor="#f472b6" />
                      <stop offset="100%" stopColor="#34d399" />
                    </linearGradient>
                  </defs>
                  <circle cx="10" cy="10" r="7" stroke="url(#modalRainbowRing)" strokeWidth="3" />
                </svg>
              </div>
            </div>

            <div className="min-w-0 flex-1 pr-2">
              {isEditingName ? (
                <form onSubmit={handleSaveRename} className="flex items-center gap-1.5">
                  <input
                    type="text"
                    value={newName}
                    onChange={(e) => setNewName(e.target.value)}
                    autoFocus
                    className="px-2.5 py-1 text-sm font-bold rounded-lg border border-blue-500 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-100 w-full"
                  />
                  <button
                    type="submit"
                    className="p-1 rounded-md bg-blue-600 text-white hover:bg-blue-700 cursor-pointer"
                  >
                    <Check className="w-4 h-4" />
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      setNewName(folderName);
                      setIsEditingName(false);
                    }}
                    className="p-1 rounded-md text-slate-400 hover:text-slate-600 cursor-pointer"
                  >
                    <X className="w-4 h-4" />
                  </button>
                </form>
              ) : (
                <div className="flex items-center gap-2 group">
                  <h2 className="text-lg font-extrabold text-slate-900 dark:text-slate-100 truncate">
                    {folderName}
                  </h2>
                  <button
                    type="button"
                    onClick={() => setIsEditingName(true)}
                    title="Rename folder"
                    className="p-1 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                  >
                    <Edit2 className="w-3.5 h-3.5" />
                  </button>
                </div>
              )}
              <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                {folderProjects.length} {folderProjects.length === 1 ? 'project' : 'projects'} in this folder
              </p>
            </div>
          </div>

          <button
            onClick={() => {
              onDeleteFolder(folderName);
              onClose();
            }}
            title="Delete this folder"
            className="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors cursor-pointer shrink-0"
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>

        {/* Instructions */}
        <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
          Check off the projects you want organized inside this folder:
        </p>

        {/* Project Selection Checklist */}
        <div className="max-h-60 overflow-y-auto space-y-1.5 custom-scrollbar pr-1">
          {projects.map((p) => {
            const isAssigned = assignedProjectIds.has(p.id);
            return (
              <div
                key={p.id}
                onClick={() => toggleProject(p.id)}
                className={`p-3 rounded-2xl border transition-all cursor-pointer flex items-center justify-between group select-none ${
                  isAssigned
                    ? 'border-blue-500 bg-blue-50/70 dark:bg-blue-950/30'
                    : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40 bg-white dark:bg-slate-900'
                }`}
              >
                <div className="min-w-0 pr-2">
                  <p className="font-bold text-xs text-slate-900 dark:text-slate-100 truncate">
                    {p.name}
                  </p>
                  {p.description && (
                    <p className="text-[10px] text-slate-400 truncate mt-0.5">
                      {p.description}
                    </p>
                  )}
                </div>

                <div
                  className={`w-5 h-5 rounded-lg flex items-center justify-center transition-colors shrink-0 ${
                    isAssigned
                      ? 'bg-blue-600 text-white shadow-xs'
                      : 'border border-slate-300 dark:border-slate-600 group-hover:border-slate-400'
                  }`}
                >
                  {isAssigned && <Check className="w-3.5 h-3.5 stroke-[3]" />}
                </div>
              </div>
            );
          })}
        </div>

        {/* Footer */}
        <div className="pt-2 flex items-center justify-between border-t border-slate-100 dark:border-slate-800">
          <button
            type="button"
            onClick={() => {
              onDeleteFolder(folderName);
              onClose();
            }}
            className="text-xs font-semibold text-rose-500 hover:text-rose-700 cursor-pointer"
          >
            Delete folder
          </button>
          <Button variant="primary" size="sm" onClick={onClose}>
            Done
          </Button>
        </div>
      </div>
    </Modal>
  );
}
