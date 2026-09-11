import React, { useState } from 'react';
import {
  KeyRound,
  Building2,
  Users,
  Download,
  Trash2,
  Shield,
  Layers,
} from 'lucide-react';
import { Modal } from '../common/Modal';
import { Button } from '../common/Button';
import { useOrganization } from '../../context/OrganizationContext';
import { useNavigate } from 'react-router-dom';

interface AdminlandModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export function AdminlandModal({ isOpen, onClose }: AdminlandModalProps) {
  const { currentOrganization, members, teams } = useOrganization();
  const navigate = useNavigate();

  const handleExportData = () => {
    const dataStr = JSON.stringify(
      {
        organization: currentOrganization,
        membersCount: members.length,
        teamsCount: teams.length,
        exportDate: new Date().toISOString(),
      },
      null,
      2
    );
    const blob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `ajath-pmt-export-${currentOrganization?.slug || 'workspace'}.json`;
    a.click();
    URL.revokeObjectURL(url);
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="" maxWidth="xl">
      <div className="space-y-6 pt-1">
        {/* Header */}
        <div className="flex items-center gap-3 pb-4 border-b border-slate-200 dark:border-slate-800">
          <div className="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center font-black text-xl shadow-xs">
            <KeyRound className="w-6 h-6" />
          </div>
          <div>
            <h2 className="text-xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
              Adminland
              <span className="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                Account Owner & Admins Only
              </span>
            </h2>
            <p className="text-xs text-slate-500">
              High-level account management for {currentOrganization?.name || 'Ajath Infotech Pvt Ltd'}.
            </p>
          </div>
        </div>

        {/* Admin Tiles Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* Tile 1: Organization Settings */}
          <div
            onClick={() => {
              onClose();
              navigate('/settings/organization');
            }}
            className="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-brand-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-all cursor-pointer group"
          >
            <div className="flex items-start gap-3">
              <div className="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 flex items-center justify-center shrink-0">
                <Building2 className="w-4 h-4" />
              </div>
              <div className="space-y-1">
                <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-brand-600 transition-colors">
                  Account Name & Logo
                </h3>
                <p className="text-xs text-slate-500">
                  Update official company name, branding logo, and domain settings.
                </p>
              </div>
            </div>
          </div>

          {/* Tile 2: People & Permissions */}
          <div
            onClick={() => {
              onClose();
              navigate('/members');
            }}
            className="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-brand-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-all cursor-pointer group"
          >
            <div className="flex items-start gap-3">
              <div className="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 flex items-center justify-center shrink-0">
                <Users className="w-4 h-4" />
              </div>
              <div className="space-y-1">
                <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-brand-600 transition-colors">
                  Manage Everyone ({members.length})
                </h3>
                <p className="text-xs text-slate-500">
                  Add administrators, invite new employees or clients, and revoke access.
                </p>
              </div>
            </div>
          </div>

          {/* Tile 3: Teams */}
          <div
            onClick={() => {
              onClose();
              navigate('/teams');
            }}
            className="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-brand-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-all cursor-pointer group"
          >
            <div className="flex items-start gap-3">
              <div className="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 flex items-center justify-center shrink-0">
                <Layers className="w-4 h-4" />
              </div>
              <div className="space-y-1">
                <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-brand-600 transition-colors">
                  Functional Teams ({teams.length})
                </h3>
                <p className="text-xs text-slate-500">
                  Organize departments, squads, and member rosters.
                </p>
              </div>
            </div>
          </div>

          {/* Tile 4: Export Data */}
          <div
            onClick={handleExportData}
            className="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-brand-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-all cursor-pointer group"
          >
            <div className="flex items-start gap-3">
              <div className="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 flex items-center justify-center shrink-0">
                <Download className="w-4 h-4" />
              </div>
              <div className="space-y-1">
                <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-brand-600 transition-colors">
                  Export Everything
                </h3>
                <p className="text-xs text-slate-500">
                  Download a complete backup archive of all projects, to-dos, and discussions.
                </p>
              </div>
            </div>
          </div>

          {/* Tile 5: Trash & Archive */}
          <div
            onClick={() => {
              onClose();
              navigate('/projects?view=archived');
            }}
            className="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-brand-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-all cursor-pointer group"
          >
            <div className="flex items-start gap-3">
              <div className="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 flex items-center justify-center shrink-0">
                <Trash2 className="w-4 h-4" />
              </div>
              <div className="space-y-1">
                <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-brand-600 transition-colors">
                  The Trash & Archives
                </h3>
                <p className="text-xs text-slate-500">
                  Review discarded projects and deleted items, or restore them.
                </p>
              </div>
            </div>
          </div>

          {/* Tile 6: Security & Audits */}
          <div
            onClick={() => {
              onClose();
              navigate('/reports');
            }}
            className="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-brand-500 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-all cursor-pointer group"
          >
            <div className="flex items-start gap-3">
              <div className="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 flex items-center justify-center shrink-0">
                <Shield className="w-4 h-4" />
              </div>
              <div className="space-y-1">
                <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-brand-600 transition-colors">
                  Audit Logs & Activity
                </h3>
                <p className="text-xs text-slate-500">
                  Track who did what, when, and examine login history.
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* Footer info */}
        <div className="pt-2 flex items-center justify-between text-xs text-slate-500">
          <button
            type="button"
            onClick={() => {
              onClose();
              navigate('/adminland');
            }}
            className="font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1 cursor-pointer"
          >
            <span>Open Full Adminland Page</span>
            <span>→</span>
          </button>
          <Button variant="outline" size="sm" onClick={onClose}>
            Done
          </Button>
        </div>
      </div>
    </Modal>
  );
}
