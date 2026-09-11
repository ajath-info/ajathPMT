import React, { useState, useEffect, useRef } from 'react';
import {
  X,
  FileText,
  Download,
  Upload,
  History,
  MessageSquare,
  Send,
  Trash2,
  Clock,
  User,
  CheckCircle2,
} from 'lucide-react';
import { ProjectFile, FileComment, FileVersion } from '../../types';
import {
  getFileComments,
  addFileComment,
  getFileVersions,
  replaceFileVersion,
  deleteFile,
} from '../../services/fileService';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { Button } from '../common/Button';

interface FileDetailModalProps {
  isOpen: boolean;
  onClose: () => void;
  file: ProjectFile | null;
  onFileUpdated?: () => void;
  onFileDeleted?: () => void;
}

export function FileDetailModal({
  isOpen,
  onClose,
  file,
  onFileUpdated,
  onFileDeleted,
}: FileDetailModalProps) {
  const { user, userRole } = useAuth();
  const { addToast } = useToast();

  const [comments, setComments] = useState<FileComment[]>([]);
  const [versions, setVersions] = useState<FileVersion[]>([]);
  const [newComment, setNewComment] = useState('');
  const [isSubmittingComment, setIsSubmittingComment] = useState(false);
  const [isReplacing, setIsReplacing] = useState(false);
  const [loading, setLoading] = useState(false);

  const fileInputRef = useRef<HTMLInputElement>(null);

  const loadData = async () => {
    if (!file || !isOpen) return;
    setLoading(true);
    try {
      const [commList, verList] = await Promise.all([
        getFileComments(file.id),
        getFileVersions(file.id),
      ]);
      setComments(commList);
      setVersions(verList);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [file?.id, isOpen]);

  if (!isOpen || !file) return null;

  const handlePostComment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newComment.trim() || !user) return;

    setIsSubmittingComment(true);
    try {
      const added = await addFileComment(file.id, user.id, newComment.trim());
      setComments((prev) => [...prev, added]);
      setNewComment('');
      addToast('Comment posted', 'success');
    } catch {
      addToast('Failed to post comment', 'error');
    } finally {
      setIsSubmittingComment(false);
    }
  };

  const handleFileReplacement = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const selected = e.target.files?.[0];
    if (!selected) return;

    setIsReplacing(true);
    try {
      await replaceFileVersion(file.id, selected, user?.id);
      addToast('New version uploaded successfully!', 'success');
      await loadData();
      onFileUpdated?.();
    } catch {
      addToast('Failed to replace version', 'error');
    } finally {
      setIsReplacing(false);
      if (fileInputRef.current) fileInputRef.current.value = '';
    }
  };

  const handleDelete = async () => {
    if (confirm('Are you sure you want to delete this file and all its versions?')) {
      try {
        await deleteFile(file.id, userRole);
        addToast('File deleted', 'info');
        onFileDeleted?.();
        onClose();
      } catch (err: any) {
        addToast(err.message || 'Failed to delete file', 'error');
      }
    }
  };

  const formatBytes = (bytes?: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col max-h-[90vh]">
        {/* Header */}
        <div className="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0">
          <div className="flex items-center gap-3">
            <div className="p-2.5 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
              <FileText className="w-5 h-5" />
            </div>
            <div>
              <h3 className="text-base font-extrabold text-slate-900 dark:text-slate-100 line-clamp-1">
                {file.name}
              </h3>
              <p className="text-xs text-slate-500 flex items-center gap-2">
                <span>{formatBytes(file.size)}</span>
                <span>•</span>
                <span>Uploaded {new Date(file.created_at).toLocaleDateString()}</span>
              </p>
            </div>
          </div>
          <div className="flex items-center gap-2">
            {userRole !== 'CLIENT' && (
              <button
                onClick={handleDelete}
                className="p-2 rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30"
                title="Delete file"
              >
                <Trash2 className="w-4 h-4" />
              </button>
            )}
            <button
              onClick={onClose}
              className="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
            >
              <X className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* Body */}
        <div className="p-6 overflow-y-auto space-y-6 flex-1">
          {/* File Card & Replacement Action */}
          <div className="bg-slate-50 dark:bg-slate-850 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4">
            <div>
              <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Current File</span>
              <p className="font-bold text-sm text-slate-900 dark:text-slate-100 mt-0.5">{file.name}</p>
              <p className="text-xs text-slate-500">
                {file.uploader?.full_name ? `By ${file.uploader.full_name}` : 'Internal Upload'} •{' '}
                {formatBytes(file.size)}
              </p>
            </div>

            <div className="flex items-center gap-2">
              <input
                ref={fileInputRef}
                type="file"
                className="hidden"
                onChange={handleFileReplacement}
              />
              <button
                onClick={() => fileInputRef.current?.click()}
                disabled={isReplacing}
                className="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer"
              >
                <Upload className="w-3.5 h-3.5 text-blue-600" />
                <span>{isReplacing ? 'Uploading...' : 'Replace with a new version'}</span>
              </button>

              <a
                href={file.storage_path}
                download={file.name}
                className="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 shadow-xs cursor-pointer"
              >
                <Download className="w-3.5 h-3.5" />
                <span>Download</span>
              </a>
            </div>
          </div>

          {/* Version History (Basecamp 4 Parity) */}
          <div className="space-y-2">
            <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
              <History className="w-4 h-4 text-purple-600" />
              Version History ({versions.length + 1})
            </h4>

            <div className="border border-slate-200/80 dark:border-slate-800 rounded-2xl divide-y divide-slate-100 dark:divide-slate-800 overflow-hidden bg-white dark:bg-slate-900">
              {/* Latest Version */}
              <div className="p-3 flex items-center justify-between text-xs bg-emerald-50/50 dark:bg-emerald-950/20">
                <div className="flex items-center gap-2">
                  <CheckCircle2 className="w-4 h-4 text-emerald-600" />
                  <span className="font-extrabold text-emerald-900 dark:text-emerald-200">
                    v{versions.length + 1} (Current Version)
                  </span>
                  <span className="text-slate-400">• {file.name}</span>
                </div>
                <span className="text-slate-500 font-medium">{formatBytes(file.size)}</span>
              </div>

              {/* Archived Versions */}
              {versions.map((ver) => (
                <div key={ver.id} className="p-3 flex items-center justify-between text-xs">
                  <div className="flex items-center gap-2">
                    <span className="font-bold text-slate-600 dark:text-slate-400">
                      v{ver.version_number}
                    </span>
                    <span className="text-slate-700 dark:text-slate-300">{ver.name}</span>
                    <span className="text-[10px] text-slate-400">
                      ({new Date(ver.created_at).toLocaleDateString()})
                    </span>
                  </div>
                  <span className="text-slate-400">{formatBytes(ver.size)}</span>
                </div>
              ))}
            </div>
          </div>

          {/* File Comments & Discussion (Basecamp 4 Parity) */}
          <div className="space-y-3 pt-2">
            <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
              <MessageSquare className="w-4 h-4 text-blue-600" />
              Discussion on this file ({comments.length})
            </h4>

            {comments.length === 0 ? (
              <p className="text-xs text-slate-400 italic py-2">No comments on this file yet. Start the discussion below.</p>
            ) : (
              <div className="space-y-2.5 max-h-48 overflow-y-auto pr-1">
                {comments.map((c) => (
                  <div
                    key={c.id}
                    className="bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-100 dark:border-slate-800 space-y-1"
                  >
                    <div className="flex items-center justify-between text-[11px]">
                      <span className="font-bold text-slate-800 dark:text-slate-200">
                        {c.profile?.full_name || 'Team Member'}
                      </span>
                      <span className="text-slate-400 text-[10px]">
                        {new Date(c.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                      </span>
                    </div>
                    <p className="text-xs text-slate-600 dark:text-slate-300 whitespace-pre-wrap">
                      {c.content}
                    </p>
                  </div>
                ))}
              </div>
            )}

            {/* Comment input form */}
            <form onSubmit={handlePostComment} className="flex gap-2 pt-2">
              <input
                type="text"
                value={newComment}
                onChange={(e) => setNewComment(e.target.value)}
                placeholder="Write a comment about this file..."
                className="flex-1 text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
              />
              <button
                type="submit"
                disabled={!newComment.trim() || isSubmittingComment}
                className="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1 cursor-pointer"
              >
                <Send className="w-3.5 h-3.5" />
                <span>Post</span>
              </button>
            </form>
          </div>
        </div>

        {/* Footer */}
        <div className="px-6 py-3 border-t border-slate-100 dark:border-slate-800 flex justify-end bg-slate-50/50 dark:bg-slate-800/30 shrink-0">
          <Button type="button" variant="outline" onClick={onClose} size="sm">
            Close
          </Button>
        </div>
      </div>
    </div>
  );
}
