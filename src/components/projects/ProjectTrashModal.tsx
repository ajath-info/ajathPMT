import React, { useState } from 'react';
import { Trash2, RotateCcw, FileText, CheckSquare, MessageSquare, AlertCircle } from 'lucide-react';
import { Modal } from '../common/Modal';
import { Button } from '../common/Button';
import { useToast } from '../../context/ToastContext';

interface ProjectTrashModalProps {
  isOpen: boolean;
  onClose: () => void;
  projectId: string;
  projectName: string;
}

interface TrashedItem {
  id: string;
  type: 'todo' | 'message' | 'file';
  title: string;
  deletedBy: string;
  deletedAt: string;
}

const INITIAL_TRASHED: TrashedItem[] = [
  {
    id: 'tr-1',
    type: 'todo',
    title: 'Review legacy API endpoints documentation',
    deletedBy: 'Edward',
    deletedAt: '2 days ago',
  },
  {
    id: 'tr-2',
    type: 'file',
    title: 'Ghana_Pricing_Proposal_v1_draft.pdf',
    deletedBy: 'Shivy Narain',
    deletedAt: '4 days ago',
  },
  {
    id: 'tr-3',
    type: 'message',
    title: 'Brainstorming session notes (Old Draft)',
    deletedBy: 'Sarah Jenkins',
    deletedAt: 'Last week',
  },
];

export function ProjectTrashModal({
  isOpen,
  onClose,
  projectId,
  projectName,
}: ProjectTrashModalProps) {
  const { addToast } = useToast();
  const [trashedItems, setTrashedItems] = useState<TrashedItem[]>(INITIAL_TRASHED);

  const handleRestore = (id: string, title: string) => {
    setTrashedItems((prev) => prev.filter((i) => i.id !== id));
    addToast(`Restored "${title}" to project`, 'success');
  };

  const handleDeletePermanently = (id: string, title: string) => {
    setTrashedItems((prev) => prev.filter((i) => i.id !== id));
    addToast(`Permanently deleted "${title}"`, 'info');
  };

  const handleEmptyTrash = () => {
    setTrashedItems([]);
    addToast('Trash emptied permanently', 'info');
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title={`Trash for ${projectName}`} maxWidth="md">
      <div className="space-y-4 pt-1">
        <div className="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
          <span>Items stay in the trash for up to 30 days before being permanently deleted.</span>
          {trashedItems.length > 0 && (
            <button
              type="button"
              onClick={handleEmptyTrash}
              className="text-rose-500 hover:text-rose-700 font-bold shrink-0 ml-2 hover:underline cursor-pointer"
            >
              Empty trash
            </button>
          )}
        </div>

        <div className="space-y-2 max-h-72 overflow-y-auto pr-1">
          {trashedItems.length === 0 ? (
            <div className="py-12 text-center text-xs text-slate-400">
              <Trash2 className="w-8 h-8 mx-auto mb-2 opacity-40" />
              The trash is empty!
            </div>
          ) : (
            trashedItems.map((item) => (
              <div
                key={item.id}
                className="p-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/60 flex items-center justify-between gap-3 shadow-2xs"
              >
                <div className="flex items-center gap-2.5 min-w-0">
                  <div className="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 shrink-0">
                    {item.type === 'todo' && <CheckSquare className="w-3.5 h-3.5" />}
                    {item.type === 'file' && <FileText className="w-3.5 h-3.5" />}
                    {item.type === 'message' && <MessageSquare className="w-3.5 h-3.5" />}
                  </div>
                  <div className="min-w-0">
                    <span className="font-bold text-xs text-slate-900 dark:text-slate-100 truncate block">
                      {item.title}
                    </span>
                    <span className="text-[10px] text-slate-400 block">
                      Trashed by {item.deletedBy} • {item.deletedAt}
                    </span>
                  </div>
                </div>

                <div className="flex items-center gap-1.5 shrink-0">
                  <button
                    type="button"
                    onClick={() => handleRestore(item.id, item.title)}
                    className="px-2.5 py-1 rounded-lg text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 flex items-center gap-1 cursor-pointer"
                  >
                    <RotateCcw className="w-3 h-3" />
                    <span>Restore</span>
                  </button>
                  <button
                    type="button"
                    onClick={() => handleDeletePermanently(item.id, item.title)}
                    className="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer"
                    title="Delete permanently"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            ))
          )}
        </div>

        <div className="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
          <Button variant="primary" size="sm" onClick={onClose}>
            Done
          </Button>
        </div>
      </div>
    </Modal>
  );
}
