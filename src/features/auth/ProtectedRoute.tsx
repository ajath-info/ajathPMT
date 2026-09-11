import React from 'react';
import { Navigate, useLocation, Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { Layers, ShieldAlert, ArrowLeft } from 'lucide-react';
import { OrgRole } from '../../types';
import { isInternalUser } from '../../lib/permissions';

export function AccessDeniedView({
  title = 'Access Denied',
  message = "You don't have permission to view or access this page. Please contact your organization administrator if you believe this is an error.",
  backLink = '/dashboard',
  backLabel = 'Back to Home Dashboard',
}: {
  title?: string;
  message?: string;
  backLink?: string;
  backLabel?: string;
}) {
  return (
    <div className="min-h-[70vh] flex items-center justify-center p-6 bg-slate-50 dark:bg-slate-950">
      <div className="max-w-md w-full text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-xl">
        <div className="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 flex items-center justify-center mx-auto mb-5 text-amber-600 dark:text-amber-400">
          <ShieldAlert className="w-8 h-8" />
        </div>
        <h2 className="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mb-2">
          {title}
        </h2>
        <p className="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
          {message}
        </p>
        <Link
          to={backLink}
          className="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-semibold hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors shadow-sm"
        >
          <ArrowLeft className="w-4 h-4" />
          {backLabel}
        </Link>
      </div>
    </div>
  );
}

export function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { profile, loading } = useAuth();
  const location = useLocation();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-slate-950">
        <div className="text-center space-y-3">
          <div className="w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center animate-bounce mx-auto shadow-lg shadow-brand-500/30">
            <Layers className="w-6 h-6" />
          </div>
          <p className="text-sm font-semibold text-slate-600 dark:text-slate-400">Loading WorkSphere Workspace...</p>
        </div>
      </div>
    );
  }

  if (!profile) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  return <>{children}</>;
}

export function RequireOrgRole({
  allowedRoles,
  children,
}: {
  allowedRoles: OrgRole[];
  children: React.ReactNode;
}) {
  const { userRole, loading } = useAuth();

  if (loading) return null;

  if (!allowedRoles.includes(userRole)) {
    return (
      <AccessDeniedView
        title="Admin Privilege Required"
        message="This area is restricted to Organization Owners and Administrators. Your current role does not grant administrative privileges."
      />
    );
  }

  return <>{children}</>;
}

export function RequireInternalUser({
  children,
}: {
  children: React.ReactNode;
}) {
  const { userRole, loading } = useAuth();

  if (loading) return null;

  if (!isInternalUser(userRole)) {
    return (
      <AccessDeniedView
        title="Internal Staff Only"
        message="This section is restricted to internal team members. Client accounts are limited to their assigned projects."
      />
    );
  }

  return <>{children}</>;
}
