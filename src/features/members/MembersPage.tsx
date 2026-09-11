import React, { useState } from 'react';
import {
  Users,
  UserPlus,
  Search,
  Filter,
  Trash2,
  ShieldCheck,
  Mail,
  Copy,
  Check,
  XCircle,
  MoreVertical,
  Calendar,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { updateMemberRole, removeMember, cancelInvitation } from '../../services/organizationService';
import { Avatar } from '../../components/common/Avatar';
import { Badge } from '../../components/common/Badge';
import { Button } from '../../components/common/Button';
import { RoleGate } from '../../components/common/RoleGate';
import { InviteMemberModal } from '../../components/members/InviteMemberModal';
import { AddMemberModal } from '../../components/members/AddMemberModal';
import { Modal } from '../../components/common/Modal';
import { OrgRole, OrganizationMember, OrganizationInvitation } from '../../types';
import { formatDate } from '../../lib/utils';
import { can } from '../../lib/permissions';

export function MembersPage() {
  const { userRole } = useAuth();
  const { currentOrganization, members, pendingInvitations, refreshOrganizationData } = useOrganization();

  const [searchQuery, setSearchQuery] = useState('');
  const [roleFilter, setRoleFilter] = useState<string>('ALL');
  const [isInviteModalOpen, setIsInviteModalOpen] = useState(false);
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [copiedToken, setCopiedToken] = useState<string | null>(null);

  // Selected member for removal
  const [memberToRemove, setMemberToRemove] = useState<OrganizationMember | null>(null);
  const [isRemoving, setIsRemoving] = useState(false);

  const roleColors: Record<OrgRole, 'brand' | 'warning' | 'neutral' | 'success'> = {
    OWNER: 'brand',
    ADMIN: 'warning',
    MEMBER: 'neutral',
    CLIENT: 'success',
  };

  const filteredMembers = members.filter((m) => {
    const nameMatch = m.profile?.full_name?.toLowerCase().includes(searchQuery.toLowerCase());
    const emailMatch = m.profile?.email?.toLowerCase().includes(searchQuery.toLowerCase());
    const jobMatch = m.profile?.job_title?.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesSearch = nameMatch || emailMatch || jobMatch;

    const matchesRole = roleFilter === 'ALL' || m.role === roleFilter;
    return matchesSearch && matchesRole;
  });

  const handleRoleChange = async (memberId: string, newRole: OrgRole) => {
    await updateMemberRole(memberId, newRole);
    refreshOrganizationData();
  };

  const handleConfirmRemove = async () => {
    if (!memberToRemove) return;
    setIsRemoving(true);
    await removeMember(memberToRemove.id);
    setIsRemoving(false);
    setMemberToRemove(null);
    refreshOrganizationData();
  };

  const handleCancelInvite = async (inviteId: string) => {
    await cancelInvitation(inviteId);
    refreshOrganizationData();
  };

  const handleCopyInviteLink = (token: string) => {
    const link = `${window.location.origin}/invite/${token}`;
    navigator.clipboard.writeText(link);
    setCopiedToken(token);
    setTimeout(() => setCopiedToken(null), 3000);
  };

  const ownerCount = members.filter((m) => m.role === 'OWNER' || m.role === 'ADMIN').length;
  const clientCount = members.filter((m) => m.role === 'CLIENT').length;

  return (
    <div className="space-y-6 animate-in fade-in duration-300 max-w-7xl mx-auto pb-12">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
              {currentOrganization?.name || 'Workspace'}
            </span>
            <span className="text-xs text-slate-500 font-medium">Directory ({members.length} Members)</span>
          </div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Organization Member Directory
          </h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Manage user roles, access levels, pending invitations, and workspace security
          </p>
        </div>

        <RoleGate action="invite_members">
          <div className="flex items-center gap-2 self-start md:self-auto">
            <button
              onClick={() => setIsAddModalOpen(true)}
              className="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold text-xs shadow-md shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer flex items-center gap-2"
              title="Add a person directly as Admin, Client, or Member"
            >
              <UserPlus className="w-4 h-4" />
              <span>+ Add Person</span>
            </button>
            <button
              onClick={() => setIsInviteModalOpen(true)}
              className="px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-xs shadow-xs transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer flex items-center gap-2"
              title="Generate invite link"
            >
              <Mail className="w-4 h-4 text-slate-400" />
              <span>Invite Link</span>
            </button>
          </div>
        </RoleGate>
      </div>

      {/* 5 Colorful KPI Cards */}
      <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between text-xs font-bold text-blue-600 dark:text-blue-400 mb-2">
            <span>Total People</span>
            <Users className="w-4 h-4" />
          </div>
          <p className="text-2xl font-black text-slate-900 dark:text-slate-100">{members.length}</p>
        </div>

        <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between text-xs font-bold text-purple-600 dark:text-purple-400 mb-2">
            <span>Admins & Owners</span>
            <ShieldCheck className="w-4 h-4" />
          </div>
          <p className="text-2xl font-black text-slate-900 dark:text-slate-100">{ownerCount}</p>
        </div>

        <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between text-xs font-bold text-indigo-600 dark:text-indigo-400 mb-2">
            <span>Team Members</span>
            <Users className="w-4 h-4" />
          </div>
          <p className="text-2xl font-black text-slate-900 dark:text-slate-100">{members.filter((m) => m.role === 'MEMBER').length}</p>
        </div>

        <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between text-xs font-bold text-emerald-600 dark:text-emerald-400 mb-2">
            <span>Clients</span>
            <Users className="w-4 h-4" />
          </div>
          <p className="text-2xl font-black text-slate-900 dark:text-slate-100">{clientCount}</p>
        </div>

        <div className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
          <div className="flex items-center justify-between text-xs font-bold text-amber-600 dark:text-amber-400 mb-2">
            <span>Invitations</span>
            <Mail className="w-4 h-4" />
          </div>
          <p className="text-2xl font-black text-slate-900 dark:text-slate-100">{pendingInvitations.length}</p>
        </div>
      </div>

      {/* Filter and Search Bar */}
      <div className="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div className="relative w-full sm:w-80">
          <Search className="absolute left-3.5 top-2.5 w-4 h-4 text-slate-400" />
          <input
            type="text"
            placeholder="Search member name, email, or role..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
          />
        </div>

        <div className="flex items-center gap-3 w-full sm:w-auto">
          <Filter className="w-4 h-4 text-slate-400 shrink-0" />
          <select
            value={roleFilter}
            onChange={(e) => setRoleFilter(e.target.value)}
            className="px-3.5 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none"
          >
            <option value="ALL">All Roles</option>
            <option value="OWNER">OWNER</option>
            <option value="ADMIN">ADMIN</option>
            <option value="MEMBER">MEMBER</option>
            <option value="CLIENT">CLIENT</option>
          </select>
        </div>
      </div>

      {/* Member Directory Table (Desktop) / Cards (Mobile) */}
      <div className="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        {/* Desktop Table View */}
        <div className="hidden md:block overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                <th className="py-3.5 px-6">Member</th>
                <th className="py-3.5 px-6">Job Title</th>
                <th className="py-3.5 px-6">Role</th>
                <th className="py-3.5 px-6">Status</th>
                <th className="py-3.5 px-6">Joined Date</th>
                <th className="py-3.5 px-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
              {filteredMembers.map((member) => (
                <tr key={member.id} className="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                  <td className="py-4 px-6">
                    <div className="flex items-center gap-3">
                      <Avatar src={member.profile?.avatar_url} name={member.profile?.full_name || 'User'} size="md" status="online" />
                      <div>
                        <p className="font-bold text-slate-900 dark:text-slate-100">{member.profile?.full_name}</p>
                        <p className="text-xs text-slate-500 dark:text-slate-400">{member.profile?.email}</p>
                      </div>
                    </div>
                  </td>

                  <td className="py-4 px-6 text-slate-600 dark:text-slate-300 text-xs font-medium">
                    {member.profile?.job_title || 'Team Specialist'}
                  </td>

                  <td className="py-4 px-6">
                    {can(userRole, 'change_member_role') && member.role !== 'OWNER' ? (
                      <select
                        value={member.role}
                        onChange={(e) => handleRoleChange(member.id, e.target.value as OrgRole)}
                        className="px-2.5 py-1 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none"
                      >
                        <option value="MEMBER">MEMBER</option>
                        <option value="ADMIN">ADMIN</option>
                        <option value="CLIENT">CLIENT</option>
                        <option value="OWNER">OWNER</option>
                      </select>
                    ) : (
                      <Badge variant={roleColors[member.role]}>{member.role}</Badge>
                    )}
                  </td>

                  <td className="py-4 px-6">
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                      <span className="w-1.5 h-1.5 rounded-full bg-emerald-500" /> Active
                    </span>
                  </td>

                  <td className="py-4 px-6 text-xs text-slate-500 dark:text-slate-400">
                    {formatDate(member.joined_at)}
                  </td>

                  <td className="py-4 px-6 text-right">
                    {can(userRole, 'remove_member') && member.role !== 'OWNER' && (
                      <button
                        onClick={() => setMemberToRemove(member)}
                        className="p-2 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                        title="Remove member"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Mobile Responsive Card List */}
        <div className="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
          {filteredMembers.map((member) => (
            <div key={member.id} className="p-4 space-y-3">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <Avatar src={member.profile?.avatar_url} name={member.profile?.full_name || 'User'} size="sm" />
                  <div>
                    <p className="font-bold text-sm text-slate-900 dark:text-slate-100">{member.profile?.full_name}</p>
                    <p className="text-xs text-slate-500 dark:text-slate-400">{member.profile?.email}</p>
                  </div>
                </div>
                {can(userRole, 'change_member_role') && member.role !== 'OWNER' ? (
                  <select
                    value={member.role}
                    onChange={(e) => handleRoleChange(member.id, e.target.value as OrgRole)}
                    className="px-2 py-1 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none"
                  >
                    <option value="MEMBER">MEMBER</option>
                    <option value="ADMIN">ADMIN</option>
                    <option value="CLIENT">CLIENT</option>
                    <option value="OWNER">OWNER</option>
                  </select>
                ) : (
                  <Badge variant={roleColors[member.role]}>{member.role}</Badge>
                )}
              </div>

              <div className="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-800">
                <span>{member.profile?.job_title || 'Team Member'}</span>
                {can(userRole, 'remove_member') && member.role !== 'OWNER' && (
                  <button onClick={() => setMemberToRemove(member)} className="text-rose-500 font-semibold">
                    Remove
                  </button>
                )}
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Pending Invitations Section - Restricted to users with invite permissions */}
      {pendingInvitations.length > 0 && can(userRole, 'invite_members') && (
        <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Mail className="w-5 h-5 text-amber-500" /> Pending Workspace Invitations ({pendingInvitations.length})
            </h3>
          </div>

          <div className="divide-y divide-slate-100 dark:divide-slate-800">
            {pendingInvitations.map((inv) => (
              <div key={inv.id} className="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                  <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">{inv.email}</p>
                  <p className="text-xs text-slate-500">Role: <span className="font-bold text-slate-700 dark:text-slate-300">{inv.role}</span> • Sent {formatDate(inv.created_at)}</p>
                </div>

                <div className="flex items-center gap-2">
                  {['OWNER', 'ADMIN'].includes(userRole) && !inv.token.includes('•') && (
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => handleCopyInviteLink(inv.token)}
                      leftIcon={copiedToken === inv.token ? <Check className="w-3.5 h-3.5" /> : <Copy className="w-3.5 h-3.5" />}
                    >
                      {copiedToken === inv.token ? 'Link Copied' : 'Copy Invite Link'}
                    </Button>
                  )}
                  {can(userRole, 'remove_member') && (
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleCancelInvite(inv.id)}
                      leftIcon={<XCircle className="w-3.5 h-3.5 text-rose-500" />}
                    >
                      Cancel
                    </Button>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Remove Confirmation Modal */}
      <Modal
        isOpen={Boolean(memberToRemove)}
        onClose={() => setMemberToRemove(null)}
        title="Remove Member from Workspace"
        description="Are you sure you want to revoke this user's access to the organization?"
      >
        <p className="text-sm text-slate-600 dark:text-slate-400 mb-6">
          Removing <span className="font-bold text-slate-900 dark:text-slate-100">{memberToRemove?.profile?.full_name}</span> will immediately revoke their access to all associated projects, teams, and workspace resources.
        </p>

        <div className="flex justify-end gap-3">
          <Button variant="outline" onClick={() => setMemberToRemove(null)}>
            Cancel
          </Button>
          <Button variant="danger" isLoading={isRemoving} onClick={handleConfirmRemove}>
            Confirm Remove Member
          </Button>
        </div>
      </Modal>

      {/* Invite Modal */}
      <InviteMemberModal isOpen={isInviteModalOpen} onClose={() => setIsInviteModalOpen(false)} />

      {/* Add Member Directly Modal */}
      <AddMemberModal isOpen={isAddModalOpen} onClose={() => setIsAddModalOpen(false)} onSuccess={() => refreshOrganizationData()} />
    </div>
  );
}
