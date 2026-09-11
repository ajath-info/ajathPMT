import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  ChevronsUpDown,
  Building,
  Plus,
  Settings,
  Check,
  Sparkles,
} from 'lucide-react';
import { useOrganization } from '../../context/OrganizationContext';
import { Modal } from '../common/Modal';
import { Input } from '../common/Input';
import { Button } from '../common/Button';

interface OrgSwitcherProps {
  isCollapsed?: boolean;
}

export function OrgSwitcher({ isCollapsed = false }: OrgSwitcherProps) {
  const { currentOrganization, organizations, switchOrganization, createNewOrganization } = useOrganization();
  const [isOpen, setIsOpen] = useState(false);
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [newOrgName, setNewOrgName] = useState('');
  const [newOrgDescription, setNewOrgDescription] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const navigate = useNavigate();

  const handleCreateOrg = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newOrgName.trim()) {
      setError('Organization name is required.');
      return;
    }

    setError(null);
    setIsLoading(true);

    const { data, error: err } = await createNewOrganization(newOrgName, undefined, newOrgDescription);
    setIsLoading(false);

    if (err) {
      setError(err.message || 'Failed to create organization.');
    } else {
      setNewOrgName('');
      setNewOrgDescription('');
      setIsCreateModalOpen(false);
      setIsOpen(false);
    }
  };

  return (
    <>
      <div className="relative px-3 my-2">
        <button
          onClick={() => setIsOpen(!isOpen)}
          className={`w-full flex items-center gap-3 p-2 rounded-xl text-left border transition-all ${
            isOpen
              ? 'bg-brand-50/80 dark:bg-brand-950/60 border-brand-300 dark:border-brand-800 ring-2 ring-brand-500/20'
              : 'bg-slate-50/80 dark:bg-slate-800/60 border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800'
          }`}
        >
          <div className="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs shrink-0">
            {currentOrganization?.logo_url ? (
              <img src={currentOrganization.logo_url} alt={currentOrganization.name} className="w-full h-full rounded-lg object-cover" />
            ) : (
              currentOrganization?.name.substring(0, 2).toUpperCase() || 'WS'
            )}
          </div>

          {!isCollapsed && (
            <>
              <div className="min-w-0 flex-1">
                <p className="text-xs font-bold text-slate-900 dark:text-slate-100 truncate">
                  {currentOrganization?.name || 'Workspace'}
                </p>
                <p className="text-[10px] text-slate-500 dark:text-slate-400 truncate">
                  {currentOrganization?.member_count || 1} members
                </p>
              </div>
              <ChevronsUpDown className="w-4 h-4 text-slate-400 shrink-0" />
            </>
          )}
        </button>

        {/* Dropdown Menu */}
        {isOpen && (
          <div
            className="absolute left-3 right-3 top-full mt-2 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 py-2 z-50 animate-in zoom-in-95 duration-150"
            onMouseLeave={() => setIsOpen(false)}
          >
            <div className="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
              {organizations.length <= 1 ? 'Company Workspace' : `Workspaces (${organizations.length})`}
            </div>
            {organizations.length === 1 && (
              <p className="px-3 pb-1 text-[10px] text-slate-400 font-medium">
                One admin manages one registered company workspace.
              </p>
            )}

            <div className="max-h-56 overflow-y-auto space-y-1 px-1 custom-scrollbar">
              {organizations.map((org) => {
                const isActive = currentOrganization?.id === org.id;
                return (
                  <button
                    key={org.id}
                    onClick={() => {
                      switchOrganization(org.id);
                      setIsOpen(false);
                    }}
                    className={`w-full flex items-center justify-between p-2 rounded-xl text-left transition-colors text-xs ${
                      isActive
                        ? 'bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 font-bold'
                        : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
                    }`}
                  >
                    <div className="flex items-center gap-2.5 min-w-0">
                      <div className="w-6 h-6 rounded-md bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-[10px] shrink-0">
                        {org.name.substring(0, 2).toUpperCase()}
                      </div>
                      <span className="truncate">{org.name}</span>
                    </div>
                    {isActive && <Check className="w-4 h-4 text-brand-600 shrink-0" />}
                  </button>
                );
              })}
            </div>

            <div className="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800 px-1 space-y-1">
              {organizations.length === 0 && (
                <button
                  onClick={() => {
                    setIsOpen(false);
                    setIsCreateModalOpen(true);
                  }}
                  className="w-full flex items-center gap-2 px-3 py-2 text-xs font-semibold text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-950/50 rounded-xl transition-colors"
                >
                  <Plus className="w-4 h-4" />
                  Create Company
                </button>
              )}
              <button
                onClick={() => {
                  setIsOpen(false);
                  navigate('/settings/organization');
                }}
                className="w-full flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors"
              >
                <Settings className="w-4 h-4" />
                Organization Settings
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Create Organization Modal */}
      <Modal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
        title="Create New Organization"
        description="Set up a multi-tenant workspace for your company or team."
      >
        {error && (
          <div className="p-3 mb-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-xs font-medium text-rose-600 dark:text-rose-400">
            {error}
          </div>
        )}

        <form onSubmit={handleCreateOrg} className="space-y-4">
          <Input
            label="Organization Name"
            placeholder="Acme Digital Hub"
            value={newOrgName}
            onChange={(e) => setNewOrgName(e.target.value)}
            leftIcon={<Building className="w-4 h-4" />}
            required
          />

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Description (Optional)
            </label>
            <textarea
              rows={2}
              placeholder="Brief summary of workspace goals..."
              value={newOrgDescription}
              onChange={(e) => setNewOrgDescription(e.target.value)}
              className="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
            />
          </div>

          <div className="flex justify-end gap-3 pt-2">
            <Button variant="outline" type="button" onClick={() => setIsCreateModalOpen(false)}>
              Cancel
            </Button>
            <Button variant="primary" type="submit" isLoading={isLoading}>
              Create Organization
            </Button>
          </div>
        </form>
      </Modal>
    </>
  );
}
