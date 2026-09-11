import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Users,
  FolderKanban,
  Activity,
  Plus,
  Trash2,
  ArrowLeft,
  Calendar,
  Check,
  ShieldCheck,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useProject } from '../../context/ProjectContext';
import { fetchTeamById, fetchTeamMembers, addTeamMember, removeTeamMember } from '../../services/teamService';
import { getOrganizationActivityLogs } from '../../services/organizationService';
import { Team, TeamMember, OrganizationMember, ActivityLogItem } from '../../types';
import { Avatar } from '../../components/common/Avatar';
import { Badge } from '../../components/common/Badge';
import { Button } from '../../components/common/Button';
import { Modal } from '../../components/common/Modal';
import { can } from '../../lib/permissions';

export function TeamDetailsPage() {
  const { teamId } = useParams<{ teamId: string }>();
  const { userRole } = useAuth();
  const { members: orgMembers, currentOrganization } = useOrganization();
  const { projects } = useProject();
  const navigate = useNavigate();

  const [team, setTeam] = useState<Team | null>(null);
  const [teamMembers, setTeamMembers] = useState<TeamMember[]>([]);
  const [activityLogs, setActivityLogs] = useState<ActivityLogItem[]>([]);
  const [activeTab, setActiveTab] = useState<'overview' | 'members' | 'projects' | 'activity'>('overview');
  const [loading, setLoading] = useState(true);

  // Add Member Modal
  const [isAddMemberOpen, setIsAddMemberOpen] = useState(false);
  const [selectedUserId, setSelectedUserId] = useState<string>('');

  const teamProjects = projects.filter((p) => p.team_id === teamId || p.organization_id === team?.organization_id);

  const loadTeamData = async () => {
    if (!teamId) return;
    try {
      const [tData, tmList, actList] = await Promise.all([
        fetchTeamById(teamId),
        fetchTeamMembers(teamId),
        currentOrganization?.id ? getOrganizationActivityLogs(currentOrganization.id) : Promise.resolve([]),
      ]);
      setTeam(tData);
      setTeamMembers(tmList);
      setActivityLogs(actList);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadTeamData();
  }, [teamId]);

  const handleAddMember = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!teamId || !selectedUserId) return;
    await addTeamMember(teamId, selectedUserId);
    setIsAddMemberOpen(false);
    setSelectedUserId('');
    loadTeamData();
  };

  const handleRemoveMember = async (userId: string) => {
    if (!teamId) return;
    await removeTeamMember(teamId, userId);
    loadTeamData();
  };

  // Available organization members not yet in this team
  const availableOrgMembers = orgMembers.filter(
    (om) => !teamMembers.some((tm) => tm.user_id === om.user_id)
  );

  if (loading) {
    return (
      <div className="py-12 text-center text-slate-500 font-medium">
        Loading team workspace...
      </div>
    );
  }

  if (!team) {
    return (
      <div className="py-12 text-center space-y-4">
        <p className="text-rose-500 font-bold">Team not found.</p>
        <Button variant="outline" onClick={() => navigate('/teams')}>
          Back to Teams Directory
        </Button>
      </div>
    );
  }

  return (
    <div className="space-y-8 animate-in fade-in duration-300 max-w-7xl mx-auto">
      {/* Back Button */}
      <button
        onClick={() => navigate('/teams')}
        className="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 transition-colors"
      >
        <ArrowLeft className="w-4 h-4" /> Back to Teams Directory
      </button>

      {/* Team Header Banner */}
      <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div className="flex items-start gap-5">
          <div className="w-16 h-16 rounded-2xl overflow-hidden bg-gradient-to-tr from-brand-600 to-indigo-600 shrink-0 shadow-md">
            {team.avatar_url ? (
              <img src={team.avatar_url} alt={team.name} className="w-full h-full object-cover" />
            ) : (
              <div className="w-full h-full flex items-center justify-center text-white font-extrabold text-xl">
                {team.name.substring(0, 2).toUpperCase()}
              </div>
            )}
          </div>

          <div className="space-y-1">
            <div className="flex items-center gap-2">
              <h1 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                {team.name}
              </h1>
              <Badge variant="brand">{teamMembers.length} Members</Badge>
            </div>
            <p className="text-sm text-slate-500 dark:text-slate-400">
              {team.description || 'Department team workspace'}
            </p>
          </div>
        </div>

        {can(userRole, 'manage_teams') && (
          <Button
            variant="primary"
            leftIcon={<Plus className="w-4 h-4" />}
            onClick={() => setIsAddMemberOpen(true)}
          >
            Add Team Member
          </Button>
        )}
      </div>

      {/* Navigation Tabs */}
      <div className="flex border-b border-slate-200 dark:border-slate-800 gap-8 text-sm font-semibold">
        {[
          { id: 'overview', label: 'Overview', icon: Users },
          { id: 'members', label: `Members (${teamMembers.length})`, icon: Users },
          { id: 'projects', label: `Projects (${teamProjects.length})`, icon: FolderKanban },
          { id: 'activity', label: 'Activity', icon: Activity },
        ].map((tab) => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id as any)}
            className={`pb-3 flex items-center gap-2 transition-colors border-b-2 -mb-px cursor-pointer ${
              activeTab === tab.id
                ? 'border-brand-600 text-brand-600 dark:text-brand-400'
                : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-slate-100'
            }`}
          >
            <tab.icon className="w-4 h-4" />
            {tab.label}
          </button>
        ))}
      </div>

      {/* Tab Content */}
      {activeTab === 'overview' && (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div className="lg:col-span-2 space-y-6">
            <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
              <h3 className="font-bold text-base text-slate-900 dark:text-slate-100">Team Mission & Scope</h3>
              <p className="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                {team.description || 'This team drives core product execution, cross-functional collaboration, and technical deliverables for the organization.'}
              </p>
            </div>
          </div>

          <div className="space-y-6">
            <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
              <h3 className="font-bold text-base text-slate-900 dark:text-slate-100">Team Roster</h3>
              <div className="space-y-3">
                {teamMembers.map((tm) => (
                  <div key={tm.id} className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <Avatar src={tm.profile?.avatar_url} name={tm.profile?.full_name || 'Member'} size="xs" />
                      <span className="text-xs font-bold text-slate-900 dark:text-slate-100">{tm.profile?.full_name}</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}

      {activeTab === 'members' && (
        <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
          <div className="flex items-center justify-between">
            <h3 className="font-bold text-base text-slate-900 dark:text-slate-100">Assigned Team Members</h3>
            {can(userRole, 'manage_teams') && (
              <Button size="sm" variant="outline" leftIcon={<Plus className="w-4 h-4" />} onClick={() => setIsAddMemberOpen(true)}>
                Add Member
              </Button>
            )}
          </div>

          <div className="divide-y divide-slate-100 dark:divide-slate-800">
            {teamMembers.map((tm) => (
              <div key={tm.id} className="py-3 flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <Avatar src={tm.profile?.avatar_url} name={tm.profile?.full_name || 'User'} size="sm" />
                  <div>
                    <p className="font-bold text-sm text-slate-900 dark:text-slate-100">{tm.profile?.full_name}</p>
                    <p className="text-xs text-slate-500">{tm.profile?.email}</p>
                  </div>
                </div>

                {can(userRole, 'manage_teams') && (
                  <button
                    onClick={() => handleRemoveMember(tm.user_id)}
                    className="text-xs text-rose-500 font-semibold hover:underline cursor-pointer"
                  >
                    Remove from Team
                  </button>
                )}
              </div>
            ))}
          </div>
        </div>
      )}

      {activeTab === 'projects' && (
        <div className="space-y-6">
          <div className="flex items-center justify-between">
            <h3 className="font-bold text-base text-slate-900 dark:text-slate-100">
              Team Projects ({teamProjects.length})
            </h3>
            {userRole !== 'CLIENT' && (
              <Button
                size="sm"
                variant="primary"
                leftIcon={<Plus className="w-4 h-4" />}
                onClick={() => navigate('/projects/new')}
              >
                New Project
              </Button>
            )}
          </div>

          {teamProjects.length === 0 ? (
            <div className="text-center py-16 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-3">
              <FolderKanban className="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto" />
              <p className="text-sm font-bold text-slate-700 dark:text-slate-300">
                No projects assigned to this team yet
              </p>
              <p className="text-xs text-slate-500 max-w-sm mx-auto">
                Create a project and link it to this team to start collaborating on deliverables.
              </p>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {teamProjects.map((project) => (
                <div
                  key={project.id}
                  onClick={() => navigate(`/projects/${project.id}`)}
                  className="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-brand-500/50 transition-all cursor-pointer flex flex-col justify-between space-y-4 group"
                >
                  <div className="space-y-2">
                    <div className="flex items-center justify-between">
                      <span className="px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wide bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 rounded-lg">
                        {project.status || 'ACTIVE'}
                      </span>
                    </div>
                    <h4 className="font-bold text-base text-slate-900 dark:text-slate-100 group-hover:text-brand-600 transition-colors">
                      {project.name}
                    </h4>
                    {project.description && (
                      <p className="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">
                        {project.description}
                      </p>
                    )}
                  </div>
                  <div className="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400">
                    <span>Open Project</span>
                    <span className="text-brand-600 font-bold group-hover:translate-x-1 transition-transform">→</span>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {activeTab === 'activity' && (
        <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
          <h3 className="font-bold text-base text-slate-900 dark:text-slate-100">
            Recent Team Activity
          </h3>
          {activityLogs.length === 0 ? (
            <div className="text-center py-12 text-xs text-slate-400">
              No recent activity recorded for this workspace.
            </div>
          ) : (
            <div className="divide-y divide-slate-100 dark:divide-slate-800 space-y-3">
              {activityLogs.slice(0, 15).map((log) => (
                <div key={log.id} className="pt-3 flex items-start gap-3">
                  <div className="w-8 h-8 rounded-full bg-brand-50 dark:bg-brand-950/50 text-brand-600 flex items-center justify-center font-bold text-xs shrink-0">
                    <Activity className="w-4 h-4" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-xs font-semibold text-slate-900 dark:text-slate-100">
                      {log.action}
                    </p>
                    {log.item_title && (
                      <p className="text-[11px] text-slate-500 mt-0.5">
                        {log.item_title}
                      </p>
                    )}
                    <span className="text-[10px] text-slate-400 block mt-1">
                      {new Date(log.created_at).toLocaleString()}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {/* Add Team Member Modal */}
      <Modal
        isOpen={isAddMemberOpen}
        onClose={() => setIsAddMemberOpen(false)}
        title="Add Member to Team"
        description="Select an organization member to add to this team."
      >
        <form onSubmit={handleAddMember} className="space-y-4">
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
              Select Member
            </label>
            <select
              value={selectedUserId}
              onChange={(e) => setSelectedUserId(e.target.value)}
              className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
              required
            >
              <option value="">Select a member...</option>
              {availableOrgMembers.map((om) => (
                <option key={om.user_id} value={om.user_id}>
                  {om.profile?.full_name} ({om.profile?.email})
                </option>
              ))}
            </select>
          </div>

          <div className="flex justify-end gap-3 pt-2">
            <Button variant="outline" type="button" onClick={() => setIsAddMemberOpen(false)}>
              Cancel
            </Button>
            <Button variant="primary" type="submit" disabled={!selectedUserId}>
              Add to Team
            </Button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
