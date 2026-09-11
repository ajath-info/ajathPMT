import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Users, Plus, Search, ArrowRight, Trash2, Edit, Calendar, FolderKanban } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { deleteTeam } from '../../services/teamService';
import { Button } from '../../components/common/Button';
import { Badge } from '../../components/common/Badge';
import { RoleGate } from '../../components/common/RoleGate';
import { CreateTeamModal } from './CreateTeamModal';
import { Modal } from '../../components/common/Modal';
import { Team } from '../../types';
import { formatDate } from '../../lib/utils';
import { can } from '../../lib/permissions';

export function TeamsPage() {
  const { userRole } = useAuth();
  const { currentOrganization, teams, refreshOrganizationData } = useOrganization();
  const navigate = useNavigate();

  const [searchQuery, setSearchQuery] = useState('');
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [teamToEdit, setTeamToEdit] = useState<Team | null>(null);

  const [teamToDelete, setTeamToDelete] = useState<Team | null>(null);
  const [isDeleting, setIsDeleting] = useState(false);

  const filteredTeams = teams.filter(
    (t) =>
      t.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      t.description?.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const handleConfirmDelete = async () => {
    if (!teamToDelete) return;
    setIsDeleting(true);
    await deleteTeam(teamToDelete.id);
    setIsDeleting(false);
    setTeamToDelete(null);
    refreshOrganizationData();
  };

  return (
    <div className="space-y-8 animate-in fade-in duration-300 max-w-7xl mx-auto">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <Badge variant="brand">{currentOrganization?.name || 'Workspace'}</Badge>
            <span className="text-xs text-slate-500 font-medium">{teams.length} Active Teams</span>
          </div>
          <h1 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Organization Teams
          </h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Functional groups, cross-departmental units, and project team structures
          </p>
        </div>

        <RoleGate action="create_team">
          <Button
            variant="primary"
            leftIcon={<Plus className="w-4 h-4" />}
            onClick={() => {
              setTeamToEdit(null);
              setIsCreateModalOpen(true);
            }}
          >
            Create Team
          </Button>
        </RoleGate>
      </div>

      {/* Search Input */}
      <div className="flex items-center justify-between bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div className="relative w-full sm:w-80">
          <Search className="absolute left-3.5 top-2.5 w-4 h-4 text-slate-400" />
          <input
            type="text"
            placeholder="Search teams by name or description..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
          />
        </div>
      </div>

      {/* Team Cards Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {filteredTeams.map((team) => (
          <div
            key={team.id}
            className="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-brand-500/50 transition-all flex flex-col justify-between space-y-4 group"
          >
            <div className="space-y-3">
              <div className="flex items-start justify-between gap-3">
                <div className="w-12 h-12 rounded-2xl overflow-hidden bg-gradient-to-tr from-brand-600 to-indigo-600 shrink-0 shadow-sm">
                  {team.avatar_url ? (
                    <img src={team.avatar_url} alt={team.name} className="w-full h-full object-cover" />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center text-white font-bold text-base">
                      {team.name.substring(0, 2).toUpperCase()}
                    </div>
                  )}
                </div>

                {can(userRole, 'manage_teams') && (
                  <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                      onClick={() => {
                        setTeamToEdit(team);
                        setIsCreateModalOpen(true);
                      }}
                      className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                      title="Edit team"
                    >
                      <Edit className="w-4 h-4" />
                    </button>
                    <button
                      onClick={() => setTeamToDelete(team)}
                      className="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40"
                      title="Delete team"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                )}
              </div>

              <div>
                <h3 className="font-extrabold text-base text-slate-900 dark:text-slate-100 tracking-tight group-hover:text-brand-600 transition-colors">
                  {team.name}
                </h3>
                <p className="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mt-1">
                  {team.description || 'No description specified for this team unit.'}
                </p>
              </div>
            </div>

            <div className="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
              <span className="font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-1">
                <Users className="w-3.5 h-3.5 text-brand-500" /> {team.member_count || 3} Members
              </span>

              <Button
                variant="ghost"
                size="sm"
                onClick={() => navigate(`/teams/${team.id}`)}
                rightIcon={<ArrowRight className="w-3.5 h-3.5" />}
              >
                View Details
              </Button>
            </div>
          </div>
        ))}
      </div>

      {/* Delete Team Modal */}
      <Modal
        isOpen={Boolean(teamToDelete)}
        onClose={() => setTeamToDelete(null)}
        title="Delete Team"
        description="Are you sure you want to permanently delete this team?"
      >
        <p className="text-sm text-slate-600 dark:text-slate-400 mb-6">
          Deleting <span className="font-bold text-slate-900 dark:text-slate-100">{teamToDelete?.name}</span> will unassign all team members. Project references will remain intact.
        </p>

        <div className="flex justify-end gap-3">
          <Button variant="outline" onClick={() => setTeamToDelete(null)}>
            Cancel
          </Button>
          <Button variant="danger" isLoading={isDeleting} onClick={handleConfirmDelete}>
            Confirm Delete Team
          </Button>
        </div>
      </Modal>

      {/* Create / Edit Team Modal */}
      <CreateTeamModal
        isOpen={isCreateModalOpen}
        onClose={() => {
          setIsCreateModalOpen(false);
          setTeamToEdit(null);
        }}
        teamToEdit={teamToEdit}
      />
    </div>
  );
}
