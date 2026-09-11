import React, { useState, useEffect } from 'react';
import { Save, ArrowLeft, Archive, Trash2, Clock, History, RotateCcw, X } from 'lucide-react';
import { ProjectDocument } from '../../types';
import {
  updateDocument,
  archiveDocument,
  deleteDocument,
  saveDocumentVersion,
  getDocumentVersions,
  restoreDocumentVersion,
} from '../../services/documentService';
import { Button } from '../common/Button';

interface DocumentEditorProps {
  document: ProjectDocument;
  onBack: () => void;
  userId?: string;
  onSuccess: () => void;
}

export function DocumentEditor({ document: doc, onBack, userId, onSuccess }: DocumentEditorProps) {
  const [title, setTitle] = useState(doc.title);
  const [content, setContent] = useState(doc.content || '');
  const [saving, setSaving] = useState(false);
  const [lastSaved, setLastSaved] = useState<string | null>(null);

  // Versions state
  const [isVersionsDrawerOpen, setIsVersionsDrawerOpen] = useState(false);
  const [versions, setVersions] = useState<any[]>([]);
  const [loadingVersions, setLoadingVersions] = useState(false);

  useEffect(() => {
    setTitle(doc.title);
    setContent(doc.content || '');
  }, [doc]);

  const loadVersions = async () => {
    setLoadingVersions(true);
    const data = await getDocumentVersions(doc.id);
    setVersions(data);
    setLoadingVersions(false);
  };

  const toggleVersionsDrawer = () => {
    if (!isVersionsDrawerOpen) {
      loadVersions();
    }
    setIsVersionsDrawerOpen(!isVersionsDrawerOpen);
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      await saveDocumentVersion(doc.id, title.trim(), content, userId);
      await updateDocument(doc.id, {
        title: title.trim(),
        content: content,
        updated_by: userId,
      });
      setLastSaved(new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
      onSuccess();
      if (isVersionsDrawerOpen) loadVersions();
    } catch (err) {
      console.error('Failed to save document', err);
    } finally {
      setSaving(false);
    }
  };

  const handleRestoreVersion = async (versionId: string) => {
    try {
      const restored = await restoreDocumentVersion(doc.id, versionId);
      setTitle(restored.title);
      setContent(restored.content || '');
      onSuccess();
      loadVersions();
    } catch (err) {
      console.error('Failed to restore version', err);
    }
  };

  const handleArchive = async () => {
    await archiveDocument(doc.id);
    onSuccess();
    onBack();
  };

  const handleDelete = async () => {
    await deleteDocument(doc.id);
    onSuccess();
    onBack();
  };

  return (
    <div className="relative flex gap-6 animate-in fade-in duration-200">
      <div className="flex-1 space-y-6 bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm">
        {/* Action Header */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
          <button
            onClick={onBack}
            className="text-xs font-bold text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 flex items-center gap-1.5"
          >
            <ArrowLeft className="w-4 h-4" />
            Back to Documents List
          </button>

          <div className="flex items-center gap-2">
            {lastSaved && (
              <span className="text-[11px] text-slate-400 flex items-center gap-1">
                <Clock className="w-3 h-3" />
                Saved at {lastSaved}
              </span>
            )}

            <Button
              variant="outline"
              size="sm"
              onClick={toggleVersionsDrawer}
              leftIcon={<History className="w-3.5 h-3.5" />}
            >
              History
            </Button>

            <Button
              variant="outline"
              size="sm"
              onClick={handleArchive}
              leftIcon={<Archive className="w-3.5 h-3.5" />}
            >
              Archive
            </Button>

            <Button
              variant="danger"
              size="sm"
              onClick={handleDelete}
              leftIcon={<Trash2 className="w-3.5 h-3.5" />}
            >
              Delete
            </Button>

            <Button
              variant="primary"
              size="sm"
              isLoading={saving}
              onClick={handleSave}
              leftIcon={<Save className="w-3.5 h-3.5" />}
            >
              Save Changes
            </Button>
          </div>
        </div>

        {/* Editable Title */}
        <input
          type="text"
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          placeholder="Document Title..."
          className="w-full text-2xl font-extrabold text-slate-900 dark:text-slate-100 bg-transparent border-none focus:outline-none tracking-tight"
        />

        {/* Editor Surface */}
        <textarea
          rows={16}
          value={content}
          onChange={(e) => setContent(e.target.value)}
          placeholder="Write collaborative project document in markdown or rich text..."
          className="w-full p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 text-xs font-mono leading-relaxed focus:outline-none focus:ring-2 focus:ring-brand-500/20 resize-none"
        />
      </div>

      {/* Version History Drawer */}
      {isVersionsDrawerOpen && (
        <div className="w-80 bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex flex-col shrink-0 animate-fadeIn">
          <div className="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
            <h4 className="font-bold text-sm text-slate-100 flex items-center gap-2">
              <History className="w-4 h-4 text-indigo-400" /> Version History
            </h4>
            <button
              onClick={() => setIsVersionsDrawerOpen(false)}
              className="text-slate-400 hover:text-slate-200"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          <div className="flex-1 overflow-y-auto space-y-3">
            {loadingVersions ? (
              <p className="text-xs text-slate-500 text-center py-4">Loading versions...</p>
            ) : versions.length === 0 ? (
              <p className="text-xs text-slate-500 text-center py-4">No previous versions saved yet.</p>
            ) : (
              versions.map((ver) => (
                <div
                  key={ver.id}
                  className="p-3 rounded-xl bg-slate-950/60 border border-slate-800 text-xs space-y-2"
                >
                  <div className="flex items-center justify-between">
                    <span className="font-bold text-indigo-400">v{ver.version_number}</span>
                    <span className="text-[10px] text-slate-500">
                      {new Date(ver.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </span>
                  </div>
                  <p className="text-slate-300 font-semibold truncate">{ver.title}</p>
                  <button
                    onClick={() => handleRestoreVersion(ver.id)}
                    className="w-full py-1 bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-400 border border-indigo-500/20 rounded-lg text-[11px] font-medium flex items-center justify-center gap-1.5 transition-colors"
                  >
                    <RotateCcw className="w-3 h-3" />
                    Restore this version
                  </button>
                </div>
              ))
            )}
          </div>
        </div>
      )}
    </div>
  );
}

