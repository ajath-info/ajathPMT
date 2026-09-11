import React, { useState, useEffect } from 'react';
import { Users, UserPlus, Trash2, ShieldCheck, Check, AlertCircle } from 'lucide-react';
import { Modal } from '../common/Modal';
import { Button } from '../common/Button';
import { Avatar } from '../common/Avatar';
import { Badge } from '../common/Badge';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import {
  getProjectMembers,
  addProjectMember,
  removeProjectMember,
  updateProjectMemberRole,
} from '../../services/projectService';
import { ProjectMember, ProjectMemberRole, OrganizationMember } from '../../types';

interface ProjectMembersModalProps {
  isOpen: boolean;
  onClose: () => void;
  projectId: string;
  projectName: string;
}

export function ProjectMembersModal({ isOpen, onClose, projectId, projectName }: ProjectMembersModalProps) {
  const { profile } = useAuth();
  const { members: orgMembers } = useOrganization();

  const [projectMembers, setProjectMembers] = useState<ProjectMember[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedUserId, setSelectedUserId] = useState<string>('');
  const [selectedRole, setSelectedRole] = useState<ProjectMemberRole>('PROJECT_MEMBER');
  const [error, setError] = useState<string | null>(null);

  const loadMembers = async () => {
    if (!projectId) return;
    setLoading(true);
    const data = await getProjectMembers(projectId);
    setProjectMembers(data);
    setLoading(false);
  };

  useEffect(() => {
    if (isOpen) {
      loadMembers();
    }
  }, [isOpen, projectId]);

  const handleAddMember = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedUserId) return;

    setError(null);
    const res = await addProjectMember(projectId, selectedUserId, selectedRole, profile?.id);
    if (res.error) {
      setError(res.error.message || 'Failed to add project member.');
    } else {
      setSelectedUserId('');
      loadMembers();
    }
  };

  const handleRoleChange = async (memberId: string, role: ProjectMemberRole) => {
    await updateProjectMemberRole(projectId, memberId, role);
    loadMembers();
  };

  const handleRemoveMember = async (memberId: string, role: ProjectMemberRole) => {
    const ownerCount = projectMembers.filter((pm) => pm.role === 'PROJECT_OWNER').length;
    if (role === 'PROJECT_OWNER' && ownerCount <= 1) {
      setError('Cannot remove the last Project Owner.');
      return;
    }

    setError(null);
    await removeProjectMember(projectId, memberId);
    loadMembers();
  };

  // Org members not yet in this project
  const availableOrgMembers = orgMembers.filter(
    (om) => !projectMembers.some((pm) => pm.user_id === om.user_id)
  );

  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title={`Manage Members - ${projectName}`}
      description="Assign collaborators and set role permissions for this project workspace."
      maxWidth="lg"
    >
      <div className="space-y-6">
        {error && (
          <div className="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-xs font-medium text-rose-600 dark:text-rose-400 flex items-center gap-2">
            <AlertCircle className="w-4 h-4 shrink-0" /> {error}
          </div>
        )}

        {/* Add Member Form */}
        <form onSubmit={handleAddMember} className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800 space-y-3">
          <span className="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
            <UserPlus className="w-4 h-4 text-brand-500" /> Add Organization Member
          </span>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <select
              value={selectedUserId}
              onChange={(e) => setSelectedUserId(e.target.value)}
              className="sm:col-span-2 px-3 py-2 text-xs font-medium rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none"
              required
            >
              <option value="">Select an organization member...</option>
              {availableOrgMembers.map((om) => (
                <option key={om.user_id} value={om.user_id}>
                  {om.profile?.full_name} ({om.profile?.email})
                </option>
              ))}
            </select>

            <select
              value={selectedRole}
              onChange={(e) => setSelectedRole(e.target.value as ProjectMemberRole)}
              className="px-3 py-2 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none"
            >
              <option value="PROJECT_MEMBER">PROJECT_MEMBER</option>
              <option value="PROJECT_MANAGER">PROJECT_MANAGER</option>
              <option value="PROJECT_OWNER">PROJECT_OWNER</option>
              <option value="PROJECT_CLIENT">PROJECT_CLIENT</option>
            </select>
          </div>

          <div className="flex justify-end pt-1">
            <Button size="sm" variant="primary" type="submit" disabled={!selectedUserId}>
              Add to Project
            </Button>
          </div>
        </form>

        {/* Member Roster List */}
        <div className="space-y-3">
          <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400">
            Project Members ({projectMembers.length})
          </h4>

          {loading ? (
            <div className="py-6 text-center text-xs text-slate-400">Loading members...</div>
          ) : (
            <div className="divide-y divide-slate-100 dark:divide-slate-800 max-h-72 overflow-y-auto custom-scrollbar pr-1">
              {projectMembers.map((pm) => (
                <div key={pm.id} className="py-3 flex items-center justify-between gap-3">
                  <div className="flex items-center gap-3">
                    <Avatar src={pm.profile?.avatar_url} name={pm.profile?.full_name || 'Member'} size="sm" />
                    <div>
                      <p className="font-bold text-xs text-slate-900 dark:text-slate-100">{pm.profile?.full_name}</p>
                      <p className="text-[11px] text-slate-400">{pm.profile?.email}</p>
                    </div>
                  </div>

                  <div className="flex items-center gap-2">
                    <select
                      value={pm.role}
                      onChange={(e) => handleRoleChange(pm.id, e.target.value as ProjectMemberRole)}
                      className="px-2 py-1 text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none"
                    >
                      <option value="PROJECT_OWNER">PROJECT_OWNER</option>
                      <option value="PROJECT_MANAGER">PROJECT_MANAGER</option>
                      <option value="PROJECT_MEMBER">PROJECT_MEMBER</option>
                      <option value="PROJECT_CLIENT">PROJECT_CLIENT</option>
                    </select>

                    <button
                      onClick={() => handleRemoveMember(pm.id, pm.role)}
                      className="p-1 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40"
                      title="Remove member"
                    >
                      <Trash2 className="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </Modal>
  );
}
