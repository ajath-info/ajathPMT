import React, { useState, useEffect } from 'react';
import { X, Sparkles, Check, ArrowRight, Layers, CheckSquare, Kanban } from 'lucide-react';
import { getProjectTemplates, instantiateProjectFromTemplate, ProjectTemplate } from '../../services/templateService';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useToast } from '../../context/ToastContext';
import { Project } from '../../types';

interface ProjectTemplateModalProps {
  isOpen: boolean;
  onClose: () => void;
  onProjectCreated: (project: Project) => void;
}

export const ProjectTemplateModal: React.FC<ProjectTemplateModalProps> = ({
  isOpen,
  onClose,
  onProjectCreated,
}) => {
  const { currentOrganization } = useOrganization();
  const { user } = useAuth();
  const { addToast } = useToast();
  const [templates, setTemplates] = useState<ProjectTemplate[]>([]);
  const [selectedTemplate, setSelectedTemplate] = useState<ProjectTemplate | null>(null);
  const [projectName, setProjectName] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (isOpen) {
      getProjectTemplates().then((tpls) => {
        setTemplates(tpls);
        if (tpls.length > 0) {
          setSelectedTemplate(tpls[0]);
          setProjectName(`${tpls[0].name} Workspace`);
        }
      });
    }
  }, [isOpen]);

  if (!isOpen) return null;

  const handleSelectTemplate = (tpl: ProjectTemplate) => {
    setSelectedTemplate(tpl);
    setProjectName(`${tpl.name} Workspace`);
  };

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedTemplate || !projectName.trim() || !currentOrganization) return;

    setLoading(true);
    try {
      const { data, error } = await instantiateProjectFromTemplate(
        selectedTemplate.id,
        projectName.trim(),
        currentOrganization.id,
        user?.id
      );

      if (error || !data) {
        addToast(error?.message || 'Failed to create project from template', 'error');
      } else {
        addToast(`Successfully created project "${data.name}" from template!`, 'success');
        onProjectCreated(data);
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
      <div className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-4xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
        {/* Header */}
        <div className="p-6 border-b border-slate-800/80 flex items-center justify-between bg-slate-900/50">
          <div className="flex items-center gap-3">
            <div className="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
              <Sparkles className="w-6 h-6" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-slate-100">Create Project from Template</h2>
              <p className="text-sm text-slate-400">Launch a pre-configured project workspace with tasks and boards instantly.</p>
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
        <form onSubmit={handleCreate} className="flex-1 overflow-y-auto p-6 space-y-6">
          {/* Template Grid */}
          <div>
            <label className="block text-sm font-medium text-slate-300 mb-3">Choose a Project Blueprint</label>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {templates.map((tpl) => {
                const isSelected = selectedTemplate?.id === tpl.id;
                const taskCount = tpl.structure.todo_lists?.reduce((acc: number, l: any) => acc + (l.tasks?.length || 0), 0) || 0;
                const columnCount = tpl.structure.kanban_columns?.length || 0;
                return (
                  <div
                    key={tpl.id}
                    onClick={() => handleSelectTemplate(tpl)}
                    className={`relative p-5 rounded-xl border transition-all cursor-pointer flex flex-col justify-between ${
                      isSelected
                        ? 'border-indigo-500 bg-indigo-500/10 shadow-lg shadow-indigo-500/10'
                        : 'border-slate-800 bg-slate-900/40 hover:border-slate-700 hover:bg-slate-800/40'
                    }`}
                  >
                    {isSelected && (
                      <div className="absolute top-3 right-3 w-5 h-5 rounded-full bg-indigo-500 flex items-center justify-center text-white">
                        <Check className="w-3.5 h-3.5 stroke-[3]" />
                      </div>
                    )}
                    <div>
                      <div className="text-2xl mb-2">🚀</div>
                      <h3 className="font-semibold text-slate-100 text-base mb-1">{tpl.name}</h3>
                      <p className="text-xs text-slate-400 leading-relaxed mb-4">{tpl.description}</p>
                    </div>

                    <div className="space-y-1.5 text-xs text-slate-400 border-t border-slate-800/80 pt-3">
                      <div className="flex items-center gap-1.5">
                        <CheckSquare className="w-3.5 h-3.5 text-indigo-400" />
                        <span>{taskCount} starter tasks</span>
                      </div>
                      <div className="flex items-center gap-1.5">
                        <Kanban className="w-3.5 h-3.5 text-emerald-400" />
                        <span>{columnCount} Kanban columns</span>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>


          {/* Project Details */}
          {selectedTemplate && (
            <div className="p-5 rounded-xl bg-slate-950/40 border border-slate-800/80 space-y-4">
              <h4 className="text-sm font-semibold text-slate-200">Configure Project Details</h4>
              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1.5">Project Name</label>
                <input
                  type="text"
                  value={projectName}
                  onChange={(e) => setProjectName(e.target.value)}
                  placeholder="e.g. Q4 Marketing Campaign"
                  required
                  className="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg text-slate-100 text-sm focus:outline-none focus:border-indigo-500 transition-colors"
                />
              </div>
            </div>
          )}
        </form>

        {/* Footer */}
        <div className="p-6 border-t border-slate-800/80 bg-slate-900/50 flex items-center justify-end gap-3">
          <button
            type="button"
            onClick={onClose}
            className="px-4 py-2 text-sm font-medium text-slate-400 hover:text-slate-200 transition-colors"
          >
            Cancel
          </button>
          <button
            type="button"
            onClick={handleCreate}
            disabled={loading || !projectName.trim()}
            className="px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-medium text-sm flex items-center gap-2 shadow-lg shadow-indigo-600/20 transition-all"
          >
            {loading ? 'Creating Project...' : 'Create Project'}
            <ArrowRight className="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  );
};
