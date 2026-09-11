import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Archive, Trash2, AlertTriangle, Check, ArrowRight } from 'lucide-react';
import { Modal } from '../common/Modal';
import { Button } from '../common/Button';
import { useToast } from '../../context/ToastContext';
import { useProject } from '../../context/ProjectContext';

interface ArchiveOrDeleteModalProps {
  isOpen: boolean;
  onClose: () => void;
  projectId: string;
  projectName: string;
}

export function ArchiveOrDeleteModal({
  isOpen,
  onClose,
  projectId,
  projectName,
}: ArchiveOrDeleteModalProps) {
  const navigate = useNavigate();
  const { addToast } = useToast();
  const { deleteExistingProject, archiveExistingProject, refreshProjects } = useProject();
  const [isProcessing, setIsProcessing] = useState(false);

  const handleArchive = async () => {
    setIsProcessing(true);
    await archiveExistingProject(projectId);
    await refreshProjects();
    setIsProcessing(false);
    addToast(`Project "${projectName}" has been archived`, 'info');
    onClose();
    navigate('/projects/directory');
  };

  const handleDelete = async () => {
    setIsProcessing(true);
    await deleteExistingProject(projectId);
    await refreshProjects();
    setIsProcessing(false);
    addToast(`Project "${projectName}" has been moved to the trash`, 'info');
    onClose();
    navigate('/dashboard');
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title={`Archive or delete ${projectName}`} maxWidth="md">
      <div className="space-y-4 pt-1">
        <p className="text-xs text-slate-500 dark:text-slate-400">
          When work on <strong className="text-slate-900 dark:text-slate-100">{projectName}</strong> is finished or cancelled, you can archive or delete it.
        </p>

        <div className="space-y-3 pt-1">
          {/* Option 1: Archive */}
          <div className="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/60 hover:border-slate-300 dark:hover:border-slate-700 transition-all space-y-2">
            <div className="flex items-center gap-2 text-slate-900 dark:text-slate-100 font-extrabold text-sm">
              <Archive className="w-4 h-4 text-amber-500" />
              <span>Archive this project</span>
            </div>
            <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
              Preserves all discussions, to-dos, and files in a read-only state. You can restore it anytime or view it from the project directory.
            </p>
            <div className="pt-1">
              <button
                type="button"
                onClick={handleArchive}
                disabled={isProcessing}
                className="px-4 py-2 text-xs font-bold rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 transition-colors cursor-pointer"
              >
                Archive project
              </button>
            </div>
          </div>

          {/* Option 2: Trash / Delete */}
          <div className="p-4 rounded-2xl border border-rose-100 dark:border-rose-950/60 bg-rose-50/40 dark:bg-rose-950/20 space-y-2">
            <div className="flex items-center gap-2 text-rose-700 dark:text-rose-400 font-extrabold text-sm">
              <Trash2 className="w-4 h-4 text-rose-500" />
              <span>Delete this project</span>
            </div>
            <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
              Moves the project to the trash. It will be held in the trash for up to 30 days before being permanently deleted.
            </p>
            <div className="pt-1">
              <button
                type="button"
                onClick={handleDelete}
                disabled={isProcessing}
                className="px-4 py-2 text-xs font-bold rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-xs transition-colors cursor-pointer"
              >
                Send to trash
              </button>
            </div>
          </div>
        </div>

        <div className="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
          <Button variant="outline" size="sm" onClick={onClose} disabled={isProcessing}>
            Never mind
          </Button>
        </div>
      </div>
    </Modal>
  );
}
