import { OrgRole, ProjectMemberRole, Project, ProjectMember } from '../types';

export type PermissionAction =
  | 'access_adminland'
  | 'manage_billing'
  | 'manage_organization'
  | 'delete_organization'
  | 'manage_members'
  | 'change_member_role'
  | 'remove_member'
  | 'invite_members'
  | 'invite_admin'
  | 'manage_teams'
  | 'view_teams'
  | 'create_team'
  | 'delete_team'
  | 'manage_projects'
  | 'create_project'
  | 'delete_project'
  | 'access_campfire'
  | 'view_internal_items'
  | 'toggle_client_visibility'
  | 'create_task'
  | 'delete_task'
  | 'delete_todo_list'
  | 'edit_assigned_task'
  | 'comment'
  | 'upload_file'
  | 'delete_file'
  | 'delete_folder';

const PERMISSION_MATRIX: Record<PermissionAction, OrgRole[]> = {
  access_adminland: ['OWNER', 'ADMIN'],
  manage_billing: ['OWNER'],
  manage_organization: ['OWNER', 'ADMIN'],
  delete_organization: ['OWNER'],
  manage_members: ['OWNER', 'ADMIN'],
  change_member_role: ['OWNER', 'ADMIN'],
  remove_member: ['OWNER', 'ADMIN'],
  invite_members: ['OWNER', 'ADMIN', 'MEMBER'],
  invite_admin: ['OWNER', 'ADMIN'], // Only Owners and Admins can create ADMIN invites!
  manage_teams: ['OWNER', 'ADMIN'],
  view_teams: ['OWNER', 'ADMIN', 'MEMBER'], // Teams are internal company only!
  create_team: ['OWNER', 'ADMIN'],
  delete_team: ['OWNER', 'ADMIN'],
  manage_projects: ['OWNER', 'ADMIN'],
  create_project: ['OWNER', 'ADMIN', 'MEMBER'], // Clients cannot create projects!
  delete_project: ['OWNER', 'ADMIN'],
  access_campfire: ['OWNER', 'ADMIN', 'MEMBER'], // Campfire is internal only!
  view_internal_items: ['OWNER', 'ADMIN', 'MEMBER'],
  toggle_client_visibility: ['OWNER', 'ADMIN', 'MEMBER'],
  create_task: ['OWNER', 'ADMIN', 'MEMBER'],
  delete_task: ['OWNER', 'ADMIN'],
  delete_todo_list: ['OWNER', 'ADMIN'], // Clients can NEVER delete todo lists!
  edit_assigned_task: ['OWNER', 'ADMIN', 'MEMBER', 'CLIENT'],
  comment: ['OWNER', 'ADMIN', 'MEMBER', 'CLIENT'],
  upload_file: ['OWNER', 'ADMIN', 'MEMBER'],
  delete_file: ['OWNER', 'ADMIN'], // Clients can NEVER delete company files!
  delete_folder: ['OWNER', 'ADMIN'],
};

/**
 * Centralized organization-level permission checker
 */
export function can(role: OrgRole | undefined | null, action: PermissionAction): boolean {
  if (!role) return false;
  const allowedRoles = PERMISSION_MATRIX[action];
  if (!allowedRoles) return false;
  return allowedRoles.includes(role);
}

/**
 * Check if user is an internal company teammate (not a client partner)
 */
export function isInternalUser(role?: OrgRole | null): boolean {
  if (!role) return false;
  return role === 'OWNER' || role === 'ADMIN' || role === 'MEMBER';
}

/**
 * Helper to check role seniority
 */
export function isOwner(role?: OrgRole | null): boolean {
  return role === 'OWNER';
}

export function isAdminOrOwner(role?: OrgRole | null): boolean {
  return role === 'OWNER' || role === 'ADMIN';
}

/**
 * Check if caller role is allowed to invite a specific target role
 * Prevents MEMBER -> ADMIN privilege escalation
 */
export function canInviteRole(callerRole: OrgRole | undefined | null, targetRole: OrgRole): boolean {
  if (!callerRole) return false;
  if (callerRole === 'CLIENT') return false; // Clients cannot invite anyone
  if (targetRole === 'OWNER') return false; // Ownership cannot be granted via simple invite
  if (targetRole === 'ADMIN') {
    return callerRole === 'OWNER' || callerRole === 'ADMIN'; // Only Owners and Admins can invite Admins
  }
  return callerRole === 'OWNER' || callerRole === 'ADMIN' || callerRole === 'MEMBER';
}

export type ProjectPermissionAction =
  | 'manage_settings'
  | 'delete_project'
  | 'manage_members'
  | 'create_task'
  | 'edit_task'
  | 'delete_task'
  | 'delete_todo_list'
  | 'create_doc'
  | 'edit_doc'
  | 'delete_doc'
  | 'upload_file'
  | 'delete_file'
  | 'create_checkin'
  | 'post_discussion'
  | 'send_chat';

export type ProjectRole = 'ADMIN' | 'MEMBER' | 'VIEWER' | 'CLIENT' | ProjectMemberRole;

const PROJECT_PERMISSION_MATRIX: Record<ProjectPermissionAction, string[]> = {
  manage_settings: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'ADMIN', 'OWNER'],
  delete_project: ['PROJECT_OWNER', 'ADMIN', 'OWNER'],
  manage_members: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'ADMIN', 'OWNER'],
  create_task: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'PROJECT_MEMBER', 'ADMIN', 'MEMBER', 'OWNER'],
  edit_task: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'PROJECT_MEMBER', 'ADMIN', 'MEMBER', 'OWNER'],
  delete_task: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'ADMIN', 'OWNER'],
  delete_todo_list: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'ADMIN', 'OWNER'],
  create_doc: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'PROJECT_MEMBER', 'ADMIN', 'MEMBER', 'OWNER'],
  edit_doc: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'PROJECT_MEMBER', 'ADMIN', 'MEMBER', 'OWNER'],
  delete_doc: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'ADMIN', 'OWNER'],
  upload_file: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'PROJECT_MEMBER', 'ADMIN', 'MEMBER', 'OWNER'],
  delete_file: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'ADMIN', 'OWNER'],
  create_checkin: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'ADMIN', 'MEMBER', 'OWNER'],
  post_discussion: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'PROJECT_MEMBER', 'PROJECT_CLIENT', 'ADMIN', 'MEMBER', 'CLIENT', 'OWNER'],
  send_chat: ['PROJECT_OWNER', 'PROJECT_MANAGER', 'PROJECT_MEMBER', 'ADMIN', 'MEMBER', 'OWNER'], // Chat is strictly internal team only
};

export function canProject(
  role: ProjectRole | string | undefined | null,
  action: ProjectPermissionAction
): boolean {
  if (!role) return false;
  if (role === 'OWNER') return true;
  const normalizedRole = role.toUpperCase();
  const allowed = PROJECT_PERMISSION_MATRIX[action];
  if (!allowed) return false;
  return allowed.includes(normalizedRole);
}

/**
 * Mandatory project-level authorization check.
 * Verifies if user has legitimate access to view or interact with a project.
 */
export function canAccessProject(
  userRole?: OrgRole | null,
  userId?: string | null,
  project?: Project | null,
  members?: ProjectMember[] | null
): boolean {
  if (!userRole || !userId || !project) return false;

  // 1. Account Owner & Workspace Admins have access to all projects in their organization
  if (userRole === 'OWNER' || userRole === 'ADMIN') {
    return true;
  }

  // 2. If user is explicitly in project members roster
  if (members && members.some((m) => m.user_id === userId)) {
    return true;
  }

  // 3. For company members (MEMBER): can access if project visibility is ORGANIZATION
  if (userRole === 'MEMBER' && project.visibility === 'ORGANIZATION') {
    return true;
  }

  // 4. CLIENTs can ONLY access projects to which they are explicitly assigned in members
  return false;
}

/**
 * Check if user can manage project configuration/settings
 * Eliminates URL parameter ?settings=true bypasses
 */
export function canManageProjectSettings(
  userRole?: OrgRole | null,
  projectRole?: ProjectMemberRole | string | null
): boolean {
  if (userRole === 'OWNER' || userRole === 'ADMIN') return true;
  if (!projectRole) return false;
  const norm = projectRole.toUpperCase();
  return norm === 'PROJECT_OWNER' || norm === 'PROJECT_MANAGER';
}

/**
 * Check if user can delete todo list
 */
export function canDeleteTodoList(
  userRole?: OrgRole | null,
  projectRole?: ProjectMemberRole | string | null
): boolean {
  if (userRole === 'CLIENT') return false; // Clients NEVER delete lists
  if (userRole === 'OWNER' || userRole === 'ADMIN') return true;
  if (!projectRole) return false;
  const norm = projectRole.toUpperCase();
  return norm === 'PROJECT_OWNER' || norm === 'PROJECT_MANAGER';
}

/**
 * Check if user can delete files or folders
 */
export function canDeleteFileOrFolder(
  userRole?: OrgRole | null,
  projectRole?: ProjectMemberRole | string | null
): boolean {
  if (userRole === 'CLIENT') return false; // Clients NEVER delete company files/folders
  if (userRole === 'OWNER' || userRole === 'ADMIN') return true;
  if (!projectRole) return false;
  const norm = projectRole.toUpperCase();
  return norm === 'PROJECT_OWNER' || norm === 'PROJECT_MANAGER' || norm === 'PROJECT_MEMBER';
}

/**
 * Check if user can access Campfire chat
 */
export function canAccessCampfire(userRole?: OrgRole | null): boolean {
  return isInternalUser(userRole);
}

/**
 * Check if user can view a specific discussion based on client visibility
 */
export function canAccessDiscussion(
  userRole?: OrgRole | null,
  isClientVisible: boolean = true
): boolean {
  if (isInternalUser(userRole)) return true;
  // If user is client, item MUST be client-visible
  return isClientVisible === true;
}
