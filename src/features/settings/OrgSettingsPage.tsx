import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Building,
  Users,
  ShieldAlert,
  Save,
  Trash2,
  CheckCircle2,
  ArrowRight,
  ShieldCheck,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { Button } from '../../components/common/Button';
import { Input } from '../../components/common/Input';
import { Badge } from '../../components/common/Badge';
import { Modal } from '../../components/common/Modal';
import { isOwner } from '../../lib/permissions';
import { AccessDeniedView } from '../auth/ProtectedRoute';

export function OrgSettingsPage() {
  const { userRole } = useAuth();
  const { currentOrganization, updateCurrentOrganization, deleteCurrentOrganization } = useOrganization();
  const navigate = useNavigate();

  const [name, setName] = useState(currentOrganization?.name || '');
  const [description, setDescription] = useState(currentOrganization?.description || '');
  const [logoUrl, setLogoUrl] = useState(currentOrganization?.logo_url || '');

  const [isLoading, setIsLoading] = useState(false);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);

  // Delete modal
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);

  useEffect(() => {
    if (currentOrganization) {
      setName(currentOrganization.name);
      setDescription(currentOrganization.description || '');
      setLogoUrl(currentOrganization.logo_url || '');
    }
  }, [currentOrganization]);

  if (!['OWNER', 'ADMIN'].includes(userRole)) {
    return (
      <AccessDeniedView
        title="Admin Privilege Required"
        message="Only Organization Owners and Administrators can modify workspace configuration and settings."
        backLink="/dashboard"
        backLabel="Return to Dashboard"
      />
    );
  }

  const handleSaveGeneral = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!['OWNER', 'ADMIN'].includes(userRole)) {
      return;
    }
    setIsLoading(true);
    setSuccessMessage(null);

    const res = await updateCurrentOrganization({ name, description, logo_url: logoUrl });
    setIsLoading(false);

    if (!res.error) {
      setSuccessMessage('Organization settings updated successfully!');
      setTimeout(() => setSuccessMessage(null), 4000);
    }
  };

  const handleDeleteOrganization = async () => {
    setIsDeleting(true);
    const res = await deleteCurrentOrganization();
    setIsDeleting(false);

    if (!res.error) {
      navigate('/dashboard');
    }
  };

  return (
    <div className="space-y-8 animate-in fade-in duration-300 max-w-4xl mx-auto">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
          <h1 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Organization Settings
          </h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Manage workspace identity, member privileges, team structures, and security policy
          </p>
        </div>
        <Badge variant="brand" size="md">Role: {userRole}</Badge>
      </div>

      {successMessage && (
        <div className="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-sm font-semibold text-emerald-700 dark:text-emerald-300 flex items-center gap-3">
          <CheckCircle2 className="w-5 h-5 shrink-0" />
          {successMessage}
        </div>
      )}

      {/* General Settings */}
      <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <h3 className="font-bold text-base text-slate-900 dark:text-slate-100 border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-2">
          <Building className="w-5 h-5 text-brand-600" /> General Workspace Info
        </h3>

        <form onSubmit={handleSaveGeneral} className="space-y-4">
          <Input
            label="Organization Name"
            value={name}
            onChange={(e) => setName(e.target.value)}
            leftIcon={<Building className="w-4 h-4" />}
            required
          />

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Description
            </label>
            <textarea
              rows={3}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
            />
          </div>

          <Input
            label="Logo URL (Optional)"
            placeholder="https://example.com/logo.png"
            value={logoUrl}
            onChange={(e) => setLogoUrl(e.target.value)}
          />

          <div className="flex justify-end pt-2">
            <Button variant="primary" type="submit" isLoading={isLoading} leftIcon={<Save className="w-4 h-4" />}>
              Save Workspace Settings
            </Button>
          </div>
        </form>
      </div>

      {/* Management Shortcuts */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
          <h4 className="font-bold text-slate-900 dark:text-slate-100 text-base">Members Management</h4>
          <p className="text-xs text-slate-500">Manage invitations, user roles, and access controls.</p>
          <Button variant="outline" size="sm" onClick={() => navigate('/members')} rightIcon={<ArrowRight className="w-4 h-4" />}>
            Go to Members Directory
          </Button>
        </div>

        <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
          <h4 className="font-bold text-slate-900 dark:text-slate-100 text-base">Teams Structure</h4>
          <p className="text-xs text-slate-500">Create departments, project units, and assign team leaders.</p>
          <Button variant="outline" size="sm" onClick={() => navigate('/teams')} rightIcon={<ArrowRight className="w-4 h-4" />}>
            Go to Teams Directory
          </Button>
        </div>
      </div>

      {/* Danger Zone (OWNER only) */}
      {isOwner(userRole) && (
        <div className="bg-rose-50/50 dark:bg-rose-950/20 rounded-3xl p-6 sm:p-8 border border-rose-200 dark:border-rose-900/50 space-y-4">
          <h3 className="font-bold text-base text-rose-700 dark:text-rose-400 flex items-center gap-2">
            <ShieldAlert className="w-5 h-5" /> Danger Zone (Owner Only)
          </h3>
          <p className="text-xs text-slate-600 dark:text-slate-400">
            Deleting this organization will permanently remove all member access, teams, project task boards, and data records.
          </p>

          <Button
            variant="danger"
            size="sm"
            leftIcon={<Trash2 className="w-4 h-4" />}
            onClick={() => setIsDeleteModalOpen(true)}
          >
            Delete Organization
          </Button>
        </div>
      )}

      {/* Delete Organization Modal */}
      <Modal
        isOpen={isDeleteModalOpen}
        onClose={() => setIsDeleteModalOpen(false)}
        title="Permanently Delete Organization"
        description="This action cannot be undone."
      >
        <p className="text-sm text-slate-600 dark:text-slate-400 mb-6">
          Are you sure you want to delete <span className="font-bold text-slate-900 dark:text-slate-100">{currentOrganization?.name}</span>? All data and member access will be permanently erased.
        </p>

        <div className="flex justify-end gap-3">
          <Button variant="outline" onClick={() => setIsDeleteModalOpen(false)}>
            Cancel
          </Button>
          <Button variant="danger" isLoading={isDeleting} onClick={handleDeleteOrganization}>
            Confirm Permanent Deletion
          </Button>
        </div>
      </Modal>
    </div>
  );
}
