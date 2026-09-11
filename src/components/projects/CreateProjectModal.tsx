import React, { useState, useEffect } from 'react';
import { FolderPlus, Image as ImageIcon, Calendar, Users, Eye, ShieldCheck } from 'lucide-react';
import { Modal } from '../common/Modal';
import { Input } from '../common/Input';
import { Button } from '../common/Button';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useProject } from '../../context/ProjectContext';
import { Project, ProjectStatus, ProjectVisibility } from '../../types';

interface CreateProjectModalProps {
  isOpen: boolean;
  onClose: () => void;
  projectToEdit?: Project | null;
}

export function CreateProjectModal({ isOpen, onClose, projectToEdit }: CreateProjectModalProps) {
  const { profile } = useAuth();
  const { teams } = useOrganization();
  const { createNewProject, updateExistingProject } = useProject();

  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [teamId, setTeamId] = useState<string>('');
  const [status, setStatus] = useState<ProjectStatus>('PLANNING');
  const [visibility, setVisibility] = useState<ProjectVisibility>('PRIVATE');
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [coverUrl, setCoverUrl] = useState('');

  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const sampleCovers = [
    'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1542744094-3a3172720177?w=800&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=800&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&auto=format&fit=crop&q=80',
  ];

  useEffect(() => {
    if (projectToEdit) {
      setName(projectToEdit.name);
      setDescription(projectToEdit.description || '');
      setTeamId(projectToEdit.team_id || '');
      setStatus(projectToEdit.status);
      setVisibility(projectToEdit.visibility);
      setStartDate(projectToEdit.start_date || '');
      setEndDate(projectToEdit.end_date || '');
      setCoverUrl(projectToEdit.cover_url || '');
    } else {
      setName('');
      setDescription('');
      setTeamId('');
      setStatus('PLANNING');
      setVisibility('PRIVATE');
      setStartDate('');
      setEndDate('');
      setCoverUrl(sampleCovers[0]);
    }
  }, [projectToEdit, isOpen]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim()) {
      setError('Project name is required.');
      return;
    }
    if (name.trim().length < 3) {
      setError('Project name must be at least 3 characters.');
      return;
    }
    if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
      setError('Project End Date cannot be before Start Date.');
      return;
    }

    setError(null);
    setIsLoading(true);

    if (projectToEdit) {
      const { error: err } = await updateExistingProject(projectToEdit.id, {
        name,
        description,
        team_id: teamId || undefined,
        status,
        visibility,
        start_date: startDate || undefined,
        end_date: endDate || undefined,
        cover_url: coverUrl,
      });
      setIsLoading(false);
      if (err) {
        setError(err.message || 'Failed to update project.');
      } else {
        onClose();
      }
    } else {
      const { error: err } = await createNewProject({
        name,
        description,
        team_id: teamId || undefined,
        status,
        visibility,
        start_date: startDate || undefined,
        end_date: endDate || undefined,
        cover_url: coverUrl,
        created_by: profile?.id,
      });
      setIsLoading(false);
      if (err) {
        setError(err.message || 'Failed to create project.');
      } else {
        onClose();
      }
    }
  };

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title={projectToEdit ? 'Edit Project Settings' : 'Create New Project'}
      description="Set up a workspace for your team deliverables, tasks, and discussions."
      maxWidth="lg"
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        {error && (
          <div className="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-xs font-medium text-rose-600 dark:text-rose-400">
            {error}
          </div>
        )}

        <Input
          label="Project Name *"
          placeholder="Ajath PMT Web Platform v1.0"
          value={name}
          onChange={(e) => setName(e.target.value)}
          leftIcon={<FolderPlus className="w-4 h-4" />}
          required
        />

        <div>
          <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            Project Description
          </label>
          <textarea
            rows={3}
            placeholder="High level overview of deliverables, goals, and milestones..."
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            className="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
          />
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Assigned Team (Optional)
            </label>
            <select
              value={teamId}
              onChange={(e) => setTeamId(e.target.value)}
              className="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none"
            >
              <option value="">No Team Assigned</option>
              {teams.map((t) => (
                <option key={t.id} value={t.id}>
                  {t.name}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Project Status
            </label>
            <select
              value={status}
              onChange={(e) => setStatus(e.target.value as ProjectStatus)}
              className="w-full px-3 py-2 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none"
            >
              <option value="PLANNING">PLANNING</option>
              <option value="ACTIVE">ACTIVE</option>
              <option value="ON_HOLD">ON_HOLD</option>
              <option value="COMPLETED">COMPLETED</option>
              <option value="ARCHIVED">ARCHIVED</option>
            </select>
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Visibility Scope
            </label>
            <select
              value={visibility}
              onChange={(e) => setVisibility(e.target.value as ProjectVisibility)}
              className="w-full px-3 py-2 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none"
            >
              <option value="PRIVATE">PRIVATE (Members only)</option>
              <option value="ORGANIZATION">ORGANIZATION (Entire org)</option>
              <option value="TEAM">TEAM (Assigned team)</option>
            </select>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Input
            label="Start Date"
            type="date"
            value={startDate}
            onChange={(e) => setStartDate(e.target.value)}
          />
          <Input
            label="End Date (Deadline)"
            type="date"
            value={endDate}
            onChange={(e) => setEndDate(e.target.value)}
          />
        </div>

        <div>
          <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            Preset Cover Image
          </label>
          <div className="grid grid-cols-4 gap-2">
            {sampleCovers.map((url, idx) => (
              <button
                key={idx}
                type="button"
                onClick={() => setCoverUrl(url)}
                className={`rounded-xl overflow-hidden border-2 transition-all h-14 ${
                  coverUrl === url ? 'border-brand-500 scale-105 shadow-md ring-2 ring-brand-500/20' : 'border-transparent opacity-70 hover:opacity-100'
                }`}
              >
                <img src={url} alt="cover" className="w-full h-full object-cover" />
              </button>
            ))}
          </div>
        </div>

        <div className="flex justify-end gap-3 pt-3">
          <Button variant="outline" type="button" onClick={onClose}>
            Cancel
          </Button>
          <Button variant="primary" type="submit" isLoading={isLoading}>
            {projectToEdit ? 'Save Project Settings' : 'Create Project Workspace'}
          </Button>
        </div>
      </form>
    </Modal>
  );
}
