import React from 'react';
import { useAuth } from '../../context/AuthContext';
import { can, canProject, PermissionAction, ProjectPermissionAction, ProjectRole } from '../../lib/permissions';

interface RoleGateProps {
  action?: PermissionAction;
  projectAction?: ProjectPermissionAction;
  projectRole?: ProjectRole | string;
  children: React.ReactNode;
  fallback?: React.ReactNode;
}

export function RoleGate({ action, projectAction, projectRole, children, fallback = null }: RoleGateProps) {
  const { userRole } = useAuth();

  if (projectAction) {
    const effectiveRole = projectRole || userRole;
    if (canProject(effectiveRole, projectAction)) {
      return <>{children}</>;
    }
    return <>{fallback}</>;
  }

  if (action && can(userRole, action)) {
    return <>{children}</>;
  }

  return <>{fallback}</>;
}
