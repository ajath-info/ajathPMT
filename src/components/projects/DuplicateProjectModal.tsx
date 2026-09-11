import React, { useState } from 'react';
import { X, Copy, ArrowRight } from 'lucide-react';
import { duplicateProject } from '../../services/projectService';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useToast } from '../../context/ToastContext';
import { Project } from '../../types';

interface DuplicateProjectModalProps {
  isOpen: boolean;
  onClose: () => void;
  sourceProject: Project | null;
  onProjectDuplicated: (project: Project) => void;
}

export const DuplicateProjectModal: React.FC<DuplicateProjectModalProps> = ({
  isOpen,
  onClose,
  sourceProject,
  onProjectDuplicated,
}) => {
  const { currentOrganization } = useOrganization();
  const { user } = useAuth();
  const { addToast } = useToast();

  const [newName, setNewName] = useState(sourceProject ? `${sourceProject.name} (Copy)` : '');
  const [loading, setLoading] = useState(false);

  if (!isOpen || !sourceProject) return null;

  const handleDuplicate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newName.trim() || !currentOrganization) return;

    setLoading(true);
    try {
      const { data, error } = await duplicateProject(
        sourceProject.id,
        newName.trim(),
        currentOrganization.id,
        user?.id
      );

      if (error || !data) {
        addToast(error?.message || 'Failed to duplicate project', 'error');
      } else {
        addToast(`Successfully duplicated project "${data.name}"!`, 'success');
        onProjectDuplicated(data);
        onClose();
      }
    } catch (err: any) {
      addToast(err.message || 'An error occurred', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate-fadeIn">
      <div className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
        {/* Header */}
        <div className="p-6 border-b border-slate-800/80 flex items-center justify-between bg-slate-900/50">
          <div className="flex items-center gap-3">
            <div className="p-2.5 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20">
              <Copy className="w-6 h-6" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-slate-100">Duplicate Project</h2>
              <p className="text-sm text-slate-400">Clone settings and workspace structure from {sourceProject.name}.</p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-2 text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 rounded-lg transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Content Body */}
        <form onSubmit={handleDuplicate} className="p-6 space-y-4">
          <div>
            <label className="block text-xs font-medium text-slate-300 mb-1.5">New Project Name</label>
            <input
              type="text"
              value={newName}
              onChange={(e) => setNewName(e.target.value)}
              placeholder="Enter name for duplicated project..."
              required
              className="w-full px-4 py-2.5 bg-slate-950 border border-slate-700 rounded-lg text-slate-100 text-sm focus:outline-none focus:border-purple-500 transition-colors"
            />
          </div>

          <div className="p-4 rounded-xl bg-slate-950/60 border border-slate-800 text-xs text-slate-400 space-y-1">
            <p className="font-semibold text-slate-300">What gets duplicated?</p>
            <ul className="list-disc list-inside space-y-1 pt-1">
              <li>Project tool settings & active modules</li>
              <li>Project cover image and visibility settings</li>
              <li>Base framework for team collaboration</li>
            </ul>
          </div>

          {/* Footer */}
          <div className="pt-4 flex items-center justify-end gap-3 border-t border-slate-800/80">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-sm font-medium text-slate-400 hover:text-slate-200 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={loading || !newName.trim()}
              className="px-6 py-2.5 rounded-lg bg-purple-600 hover:bg-purple-500 disabled:opacity-50 text-white font-medium text-sm flex items-center gap-2 shadow-lg shadow-purple-600/20 transition-all"
            >
              {loading ? 'Duplicating...' : 'Duplicate Project'}
              <ArrowRight className="w-4 h-4" />
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
