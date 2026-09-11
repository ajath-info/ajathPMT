import React, { useState, useEffect } from 'react';
import { Users, FileText, Image as ImageIcon } from 'lucide-react';
import { Modal } from '../../components/common/Modal';
import { Input } from '../../components/common/Input';
import { Button } from '../../components/common/Button';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { createTeam, updateTeam } from '../../services/teamService';
import { Team } from '../../types';

interface CreateTeamModalProps {
  isOpen: boolean;
  onClose: () => void;
  teamToEdit?: Team | null;
}

export function CreateTeamModal({ isOpen, onClose, teamToEdit }: CreateTeamModalProps) {
  const { profile } = useAuth();
  const { currentOrganization, members: orgMembers, refreshOrganizationData } = useOrganization();

  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [avatarUrl, setAvatarUrl] = useState('');
  const [selectedMemberIds, setSelectedMemberIds] = useState<string[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (teamToEdit) {
      setName(teamToEdit.name);
      setDescription(teamToEdit.description || '');
      setAvatarUrl(teamToEdit.avatar_url || '');
    } else {
      setName('');
      setDescription('');
      setAvatarUrl('');
    }
  }, [teamToEdit, isOpen]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim() || !currentOrganization) {
      setError('Team name is required.');
      return;
    }

    setError(null);
    setIsLoading(true);

    if (teamToEdit) {
      const { error: err } = await updateTeam(teamToEdit.id, { name, description, avatar_url: avatarUrl });
      setIsLoading(false);
      if (err) {
        setError(err.message || 'Failed to update team.');
      } else {
        refreshOrganizationData();
        onClose();
      }
    } else {
      const { error: err } = await createTeam(
        currentOrganization.id,
        name,
        description,
        avatarUrl,
        profile?.id,
        selectedMemberIds
      );
      setIsLoading(false);
      if (err) {
        setError(err.message || 'Failed to create team.');
      } else {
        refreshOrganizationData();
        onClose();
      }
    }
  };

  const sampleTeamAvatars = [
    'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=150&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1531403009284-440f080d1e12?w=150&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=150&auto=format&fit=crop&q=80',
  ];

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title={teamToEdit ? 'Edit Team Details' : 'Create New Team'}
      description="Organize workspace members into functional departments or project units."
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        {error && (
          <div className="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-xs font-medium text-rose-600 dark:text-rose-400">
            {error}
          </div>
        )}

        <Input
          label="Team Name"
          placeholder="Frontend Engineering"
          value={name}
          onChange={(e) => setName(e.target.value)}
          leftIcon={<Users className="w-4 h-4" />}
          required
        />

        <div>
          <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            Description
          </label>
          <textarea
            rows={2}
            placeholder="Focus area, responsibilities, and team goals..."
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            className="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
          />
        </div>

        <div>
          <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            Preset Team Cover Photo
          </label>
          <div className="flex gap-3 pt-1">
            {sampleTeamAvatars.map((url, idx) => (
              <button
                key={idx}
                type="button"
                onClick={() => setAvatarUrl(url)}
                className={`rounded-xl overflow-hidden border-2 transition-all ${
                  avatarUrl === url ? 'border-brand-500 scale-105 shadow-md' : 'border-transparent opacity-70 hover:opacity-100'
                }`}
              >
                <img src={url} alt="preset team" className="w-16 h-12 object-cover" />
              </button>
            ))}
          </div>
        </div>

        {!teamToEdit && (
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
              Assign Initial Members ({currentOrganization?.name})
            </label>
            <p className="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
              Select members of this organization to include in this team:
            </p>
            <div className="max-h-40 overflow-y-auto space-y-1.5 border border-slate-200 dark:border-slate-800 rounded-xl p-2 bg-slate-50 dark:bg-slate-900/50">
              {orgMembers.filter((m) => m.user_id !== profile?.id).length === 0 ? (
                <p className="text-xs text-slate-400 p-2">
                  No other organization members found. You will be the initial team member.
                </p>
              ) : (
                orgMembers
                  .filter((m) => m.user_id !== profile?.id)
                  .map((m) => {
                    const mUser = m.profile || { full_name: 'Member', email: '' };
                    const isChecked = selectedMemberIds.includes(m.user_id);
                    return (
                      <label
                        key={m.id}
                        className="flex items-center gap-2.5 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer text-xs"
                      >
                        <input
                          type="checkbox"
                          checked={isChecked}
                          onChange={(e) => {
                            if (e.target.checked) {
                              setSelectedMemberIds((prev) => [...prev, m.user_id]);
                            } else {
                              setSelectedMemberIds((prev) => prev.filter((id) => id !== m.user_id));
                            }
                          }}
                          className="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5"
                        />
                        <div className="min-w-0 flex-1">
                          <span className="font-semibold text-slate-900 dark:text-white truncate block">
                            {mUser.full_name}
                          </span>
                          {mUser.email && (
                            <span className="text-[10px] text-slate-400 truncate block">
                              {mUser.email} • {m.role}
                            </span>
                          )}
                        </div>
                      </label>
                    );
                  })
              )}
            </div>
          </div>
        )}

        <div className="flex justify-end gap-3 pt-2">
          <Button variant="outline" type="button" onClick={onClose}>
            Cancel
          </Button>
          <Button variant="primary" type="submit" isLoading={isLoading}>
            {teamToEdit ? 'Save Team Changes' : 'Create Team'}
          </Button>
        </div>
      </form>
    </Modal>
  );
}
