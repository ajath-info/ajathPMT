import React, { useState } from 'react';
import { X, Upload, FileText, FolderPlus } from 'lucide-react';
import { uploadFileRecord, createFolder } from '../../services/fileService';
import { Button } from '../common/Button';

interface FileUploadModalProps {
  isOpen: boolean;
  onClose: () => void;
  projectId: string;
  currentFolderId?: string | null;
  userId?: string;
  onSuccess: () => void;
}

export function FileUploadModal({
  isOpen,
  onClose,
  projectId,
  currentFolderId,
  userId,
  onSuccess,
}: FileUploadModalProps) {
  const [mode, setMode] = useState<'FILE' | 'FOLDER'>('FILE');
  const [folderName, setFolderName] = useState('');
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [description, setDescription] = useState('');
  const [submitting, setSubmitting] = useState(false);

  if (!isOpen) return null;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      if (mode === 'FOLDER') {
        if (!folderName.trim()) return;
        await createFolder(projectId, folderName.trim(), currentFolderId, userId);
      } else {
        if (!selectedFile) return;
        await uploadFileRecord(projectId, currentFolderId || null, userId, selectedFile, description);
      }
      onSuccess();
      onClose();
    } catch (err) {
      console.error(err);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div className="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="p-2.5 rounded-2xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400">
              <Upload className="w-5 h-5" />
            </div>
            <div>
              <h3 className="text-lg font-extrabold text-slate-900 dark:text-slate-100">
                {mode === 'FILE' ? 'Upload Project File' : 'Create New Folder'}
              </h3>
              <p className="text-xs text-slate-500">Store and share project documents & assets</p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Mode Switcher */}
        <div className="p-4 border-b border-slate-100 dark:border-slate-800 flex gap-2">
          <button
            type="button"
            onClick={() => setMode('FILE')}
            className={`flex-1 py-2 text-xs font-bold rounded-xl border flex items-center justify-center gap-1.5 ${
              mode === 'FILE'
                ? 'bg-brand-50 dark:bg-brand-950/80 text-brand-600 border-brand-300 dark:border-brand-800'
                : 'border-slate-200 dark:border-slate-800 text-slate-500'
            }`}
          >
            <FileText className="w-4 h-4" />
            Upload File
          </button>
          <button
            type="button"
            onClick={() => setMode('FOLDER')}
            className={`flex-1 py-2 text-xs font-bold rounded-xl border flex items-center justify-center gap-1.5 ${
              mode === 'FOLDER'
                ? 'bg-brand-50 dark:bg-brand-950/80 text-brand-600 border-brand-300 dark:border-brand-800'
                : 'border-slate-200 dark:border-slate-800 text-slate-500'
            }`}
          >
            <FolderPlus className="w-4 h-4" />
            New Folder
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          {mode === 'FOLDER' ? (
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-slate-700 dark:text-slate-300">
                Folder Name *
              </label>
              <input
                type="text"
                required
                value={folderName}
                onChange={(e) => setFolderName(e.target.value)}
                placeholder="e.g. Design Specifications, API Docs"
                className="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100"
              />
            </div>
          ) : (
            <>
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-slate-700 dark:text-slate-300">
                  Select File *
                </label>
                <input
                  type="file"
                  required
                  onChange={(e) => setSelectedFile(e.target.files?.[0] || null)}
                  className="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100"
                />
              </div>

              <div className="space-y-1.5">
                <label className="text-xs font-bold text-slate-700 dark:text-slate-300">
                  File Description (Optional)
                </label>
                <textarea
                  rows={2}
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  placeholder="Notes about this file version..."
                  className="w-full px-4 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 resize-none"
                />
              </div>
            </>
          )}

          <div className="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
            <Button type="button" variant="outline" onClick={onClose}>
              Cancel
            </Button>
            <Button type="submit" variant="primary" isLoading={submitting}>
              {mode === 'FILE' ? 'Upload File' : 'Create Folder'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}
