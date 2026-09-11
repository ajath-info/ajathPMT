export type OrgRole = 'OWNER' | 'ADMIN' | 'MEMBER' | 'CLIENT';
export type ProjectStatus = 'PLANNING' | 'ACTIVE' | 'ON_HOLD' | 'COMPLETED' | 'ARCHIVED';
export type ProjectVisibility = 'PRIVATE' | 'ORGANIZATION' | 'TEAM';
export type ProjectMemberRole = 'PROJECT_OWNER' | 'PROJECT_MANAGER' | 'PROJECT_MEMBER' | 'PROJECT_CLIENT';

export type TaskStatusType = 'NOT_STARTED' | 'IN_PROGRESS' | 'WAITING' | 'IN_REVIEW' | 'COMPLETED';
export type TaskPriorityType = 'LOW' | 'MEDIUM' | 'HIGH' | 'URGENT';
export type InviteStatus = 'PENDING' | 'ACCEPTED' | 'DECLINED' | 'EXPIRED';

export interface Profile {
  id: string;
  email: string;
  full_name: string;
  avatar_url?: string;
  job_title?: string;
  bio?: string;
  location?: string;
  status?: string;
  timezone?: string;
  created_at?: string;
}

export interface Organization {
  id: string;
  name: string;
  slug: string;
  description?: string;
  logo_url?: string;
  owner_id: string;
  created_at: string;
  updated_at?: string;
  member_count?: number;
}

export interface OrganizationMember {
  id: string;
  organization_id: string;
  user_id: string;
  role: OrgRole;
  joined_at: string;
  profile?: Profile;
}

export interface OrganizationInvitation {
  id: string;
  organization_id: string;
  email: string;
  role: OrgRole;
  invited_by: string;
  token: string;
  status: InviteStatus;
  personal_message?: string;
  team_id?: string;
  project_id?: string;
  project_role?: ProjectMemberRole;
  accepted_at?: string;
  accepted_by?: string;
  expires_at: string;
  created_at: string;
  organization?: Organization;
  inviter?: Profile;
}

export interface Team {
  id: string;
  organization_id: string;
  name: string;
  description?: string;
  avatar_url?: string;
  created_by?: string;
  created_at: string;
  updated_at?: string;
  member_count?: number;
  members?: TeamMember[];
}

export interface TeamMember {
  id: string;
  team_id: string;
  user_id: string;
  created_at: string;
  profile?: Profile;
}

export interface ProjectMember {
  id: string;
  project_id: string;
  user_id: string;
  role: ProjectMemberRole;
  added_by?: string;
  created_at: string;
  profile?: Profile;
}

export interface Project {
  id: string;
  organization_id: string;
  team_id?: string;
  name: string;
  slug: string;
  description?: string;
  cover_url?: string;
  status: ProjectStatus;
  visibility: ProjectVisibility;
  start_date?: string;
  end_date?: string;
  created_by?: string;
  archived_at?: string;
  trashed_at?: string | null;
  created_at: string;
  updated_at?: string;
  deleted_at?: string;
  members?: ProjectMember[];
  team?: Team;
  members_count?: number;
  progress?: number;
  folder_name?: string;
  is_starred?: boolean;
}

export interface CreateProjectInput {
  organization_id: string;
  team_id?: string;
  name: string;
  description?: string;
  cover_url?: string;
  status?: ProjectStatus;
  visibility?: ProjectVisibility;
  start_date?: string;
  end_date?: string;
  created_by?: string;
}

export interface UpdateProjectInput {
  name?: string;
  team_id?: string;
  description?: string;
  cover_url?: string;
  status?: ProjectStatus;
  visibility?: ProjectVisibility;
  start_date?: string;
  end_date?: string;
}

export interface TodoList {
  id: string;
  project_id: string;
  title: string;
  description?: string;
  position: number;
  created_by?: string;
  created_at: string;
  updated_at?: string;
  tasks?: Task[];
}

export interface CreateTodoListInput {
  project_id: string;
  title: string;
  description?: string;
  created_by?: string;
}

export interface Task {
  id: string;
  project_id: string;
  todo_list_id: string;
  parent_task_id?: string;
  kanban_column_id?: string;
  kanban_position?: number;
  title: string;
  description?: string;
  status: TaskStatusType;
  priority: TaskPriorityType;
  start_date?: string;
  due_date?: string;
  completed_at?: string;
  position: number;
  created_by?: string;
  archived_at?: string;
  created_at: string;
  updated_at?: string;
  assignees?: Profile[];
  comments_count?: number;
  subtasks?: Task[];
  subtasks_completed_count?: number;
  labels?: Label[];
  dependencies?: Task[];
  blocked_by?: Task[];
  recurrence_pattern?: { frequency: 'DAILY' | 'WEEKLY' | 'MONTHLY' | 'CUSTOM'; interval?: number };
  recurring_parent_id?: string;
  todo_group_id?: string;
  attachments_count?: number;
  watchers_count?: number;
  is_watched?: boolean;
}

export interface CreateTaskInput {
  project_id: string;
  todo_list_id: string;
  parent_task_id?: string;
  kanban_column_id?: string;
  kanban_position?: number;
  title: string;
  description?: string;
  status?: TaskStatusType;
  priority?: TaskPriorityType;
  start_date?: string;
  due_date?: string;
  created_by?: string;
  assignee_ids?: string[];
  label_ids?: string[];
  todo_group_id?: string;
  recurrence_pattern?: { frequency: 'DAILY' | 'WEEKLY' | 'MONTHLY' | 'CUSTOM'; interval?: number };
  recurring_parent_id?: string;
}

export interface UpdateTaskInput {
  title?: string;
  description?: string;
  todo_list_id?: string;
  kanban_column_id?: string;
  kanban_position?: number;
  status?: TaskStatusType;
  priority?: TaskPriorityType;
  start_date?: string;
  due_date?: string;
  position?: number;
  todo_group_id?: string;
  recurrence_pattern?: { frequency: 'DAILY' | 'WEEKLY' | 'MONTHLY' | 'CUSTOM'; interval?: number };
  recurring_parent_id?: string;
}

export interface KanbanColumn {
  id: string;
  project_id: string;
  name: string;
  description?: string;
  position: number;
  color?: string;
  mapped_status?: TaskStatusType;
  is_default?: boolean;
  is_triage?: boolean;
  created_by?: string;
  created_at: string;
  updated_at?: string;
  tasks?: Task[];
}

export interface CreateKanbanColumnInput {
  project_id: string;
  name: string;
  description?: string;
  color?: string;
  mapped_status?: TaskStatusType;
  created_by?: string;
}

export interface UpdateKanbanColumnInput {
  name?: string;
  description?: string;
  color?: string;
  mapped_status?: TaskStatusType;
  position?: number;
}

export interface BoardFilter {
  assignee_id?: string;
  status?: TaskStatusType;
  priority?: TaskPriorityType;
  label_id?: string;
  due_date_filter?: 'today' | 'upcoming' | 'overdue';
  my_tasks_only?: boolean;
  overdue_only?: boolean;
  completed_only?: boolean;
  blocked_only?: boolean;
  search_query?: string;
}

export interface TaskAssignee {
  id: string;
  task_id: string;
  user_id: string;
  assigned_by?: string;
  created_at: string;
  profile?: Profile;
}

export interface TaskComment {
  id: string;
  task_id: string;
  user_id: string;
  parent_comment_id?: string;
  content: string;
  created_at: string;
  updated_at?: string;
  profile?: Profile;
}

export interface Label {
  id: string;
  organization_id: string;
  name: string;
  color: string;
  created_by?: string;
  created_at?: string;
}

export interface TaskDependency {
  id: string;
  task_id: string;
  depends_on_task_id: string;
  created_at: string;
  depends_on_task?: Task;
}

export interface NotificationItem {
  id: string;
  organization_id?: string;
  user_id: string;
  actor_id?: string;
  type?: string;
  title: string;
  message: string;
  link_url?: string;
  entity_type?: string;
  entity_id?: string;
  is_read: boolean;
  aggregate_count?: number;
  queued_for_quiet_hours?: boolean;
  created_at: string;
  actor?: Profile;
}

export interface ActivityLogItem {
  id: string;
  organization_id: string;
  project_id?: string;
  user_id?: string;
  action: string;
  item_title?: string;
  created_at: string;
  user?: Profile;
  profile?: Profile;
}

export interface Discussion {
  id: string;
  project_id: string;
  author_id: string;
  title: string;
  content: string;
  category?: string;
  is_announcement?: boolean;
  is_pinned?: boolean;
  is_locked?: boolean;
  is_client_visible?: boolean;
  status?: 'PUBLISHED' | 'SCHEDULED' | 'DRAFT' | 'published' | 'scheduled' | 'draft';
  scheduled_at?: string;
  scheduled_publish_at?: string | null;
  notified_count?: number;
  attachments?: Array<{
    name: string;
    size: string;
    type: string;
    url?: string;
  }>;
  reactions?: Array<{
    emoji: string;
    count: number;
    users?: string[];
  }>;
  created_at: string;
  updated_at?: string;
  author?: Profile;
  comments_count?: number;
  versions?: DiscussionVersion[];
}

export interface DiscussionComment {
  id: string;
  discussion_id: string;
  user_id: string;
  parent_comment_id?: string;
  content: string;
  role?: string;
  is_client?: boolean;
  reactions?: Array<{
    emoji: string;
    count: number;
  }>;
  attachments?: Array<{
    name: string;
    size: string;
    type: string;
    url?: string;
  }>;
  created_at: string;
  updated_at?: string;
  profile?: Profile;
}

export interface ChatMessage {
  id: string;
  room_id: string;
  sender_id: string;
  content: string;
  reply_to_id?: string;
  reply_to?: ChatMessage;
  attachment_url?: string;
  attachment_name?: string;
  attachment_type?: string;
  is_edited?: boolean;
  edited_at?: string;
  created_at: string;
  updated_at?: string;
  sender?: Profile;
  reactions?: MessageReaction[];
}

export interface DirectConversation {
  id: string;
  organization_id: string;
  user1_id?: string;
  user2_id?: string;
  title?: string;
  is_group?: boolean;
  created_at: string;
  user1?: Profile;
  user2?: Profile;
  other_user?: Profile;
  participants?: DirectConversationParticipant[];
  last_message?: DirectMessage;
  unread_count?: number;
}

export interface DirectMessage {
  id: string;
  conversation_id: string;
  sender_id: string;
  content: string;
  is_read?: boolean;
  created_at: string;
  sender?: Profile;
}

export interface MessageReaction {
  id: string;
  message_id: string;
  user_id: string;
  emoji: string;
  created_at: string;
}

export interface CalendarEvent {
  id: string;
  project_id: string;
  title: string;
  description?: string;
  location?: string;
  start_at: string;
  end_at: string;
  all_day?: boolean;
  color?: string;
  recurrence_rule?: {
    frequency: 'DAILY' | 'WEEKLY' | 'MONTHLY' | 'YEARLY';
    interval?: number;
    until?: string;
  };
  parent_event_id?: string;
  created_by?: string;
  created_at: string;
  creator?: Profile;
  attendees?: CalendarEventAttendee[];
  comments?: CalendarEventComment[];
  attachments?: CalendarEventAttachment[];
}

export interface Folder {
  id: string;
  project_id: string;
  parent_folder_id?: string;
  name: string;
  items_count?: number;
  created_by?: string;
  created_at: string;
}

export interface ProjectFile {
  id: string;
  project_id: string;
  folder_id?: string;
  uploaded_by?: string;
  name: string;
  storage_path: string;
  file_url?: string;
  mime_type: string;
  size: number;
  description?: string;
  created_at: string;
  uploader?: Profile;
  comments_count?: number;
  versions_count?: number;
  versions?: FileVersion[];
}


export interface ProjectDocument {
  id: string;
  project_id: string;
  parent_document_id?: string;
  title: string;
  content: string;
  created_by?: string;
  updated_by?: string;
  is_archived?: boolean;
  created_at: string;
  updated_at?: string;
  creator?: Profile;
  editor?: Profile;
}

export interface CheckinQuestion {
  id: string;
  project_id: string;
  question: string;
  schedule?: string;
  is_active?: boolean;
  created_by?: string;
  created_at: string;
  creator?: Profile;
  responses_count?: number;
}

export interface CheckinResponse {
  id: string;
  question_id: string;
  user_id: string;
  response: string;
  created_at: string;
  user?: Profile;
}

export interface GlobalSearchResult {
  id: string;
  type: 'project' | 'task' | 'discussion' | 'document' | 'file' | 'member' | 'event' | 'comment' | 'list';
  title: string;
  subtitle?: string;
  url: string;
  badge?: string;
}

export interface ProjectTemplate {
  id: string;
  name: string;
  description?: string;
  category?: string;
  structure: {
    todo_lists?: { title: string; tasks?: string[] }[];
    kanban_columns?: { name: string; color?: string; mapped_status?: TaskStatusType }[];
    labels?: { name: string; color?: string }[];
    disabled_tools?: string[];
  };
  is_system?: boolean;
  created_by?: string;
  created_at: string;
}

export interface ProjectToolSettings {
  id: string;
  project_id: string;
  disabled_tools: string[];
  updated_at?: string;
}

export interface TaskAttachment {
  id: string;
  task_id: string;
  project_id: string;
  name: string;
  storage_path: string;
  mime_type: string;
  size: number;
  uploaded_by?: string;
  created_at: string;
  uploader?: Profile;
}

export interface TaskWatcher {
  id: string;
  task_id: string;
  user_id: string;
  created_at: string;
  user?: Profile;
}

export interface TaskReminder {
  id: string;
  task_id: string;
  user_id: string;
  reminder_type: 'AT_DUE' | '1_HOUR_BEFORE' | '1_DAY_BEFORE' | '2_DAYS_BEFORE';
  remind_at: string;
  is_sent?: boolean;
  created_at: string;
}

export interface DocumentVersion {
  id: string;
  document_id: string;
  title: string;
  content: string;
  version_number: number;
  created_by?: string;
  created_at: string;
  creator?: Profile;
}

export interface UserBookmark {
  id: string;
  user_id: string;
  item_type: 'task' | 'discussion' | 'document' | 'file' | 'project';
  item_id: string;
  title: string;
  url: string;
  created_at: string;
}

export interface PersonalNote {
  id: string;
  user_id: string;
  title: string;
  content: string;
  created_at: string;
  updated_at?: string;
}

export interface RecentlyViewedItem {
  id: string;
  user_id: string;
  item_type: 'task' | 'discussion' | 'document' | 'file' | 'project';
  item_id: string;
  title: string;
  url: string;
  viewed_at: string;
}

export interface UserOnboardingState {
  id: string;
  user_id: string;
  step: number;
  is_completed: boolean;
  updated_at?: string;
}

export interface DirectConversationParticipant {
  id: string;
  conversation_id: string;
  user_id: string;
  joined_at?: string;
  created_at?: string;
  profile?: Profile;
}

export interface TodoGroup {
  id: string;
  todo_list_id: string;
  title: string;
  position: number;
  created_by?: string;
  created_at: string;
  updated_at?: string;
  tasks?: Task[];
}

export interface CreateTodoGroupInput {
  todo_list_id: string;
  title: string;
  position?: number;
  created_by?: string;
}

export interface CalendarEventAttendee {
  id: string;
  event_id: string;
  user_id: string;
  rsvp_status: 'PENDING' | 'ACCEPTED' | 'DECLINED' | 'TENTATIVE';
  created_at: string;
  profile?: Profile;
}

export interface CalendarEventComment {
  id: string;
  event_id: string;
  user_id: string;
  content: string;
  created_at: string;
  updated_at?: string;
  profile?: Profile;
}

export interface CalendarEventAttachment {
  id: string;
  event_id: string;
  name: string;
  storage_path: string;
  mime_type?: string;
  size?: number;
  uploaded_by?: string;
  created_at: string;
  uploader?: Profile;
}

export interface FileComment {
  id: string;
  file_id: string;
  user_id: string;
  content: string;
  created_at: string;
  updated_at?: string;
  profile?: Profile;
}

export interface FileVersion {
  id: string;
  file_id: string;
  name?: string;
  version_number: number;
  storage_path: string;
  size: number;
  mime_type?: string;
  description?: string;
  uploaded_by?: string;
  created_at: string;
  uploader?: Profile;
}

export interface DiscussionVersion {
  id: string;
  discussion_id: string;
  title: string;
  content: string;
  version_number: number;
  edited_by?: string;
  created_at: string;
  editor?: Profile;
}

export interface UserNotificationPreferences {
  user_id: string;
  email_notifications: boolean;
  push_notifications: boolean;
  notify_task_assigned: boolean;
  notify_discussion_replies: boolean;
  notify_chat_mentions: boolean;
  notify_due_dates: boolean;
  work_days?: number[]; // e.g. [1, 2, 3, 4, 5]
  work_hours_start?: string; // e.g. "09:00"
  work_hours_end?: string; // e.g. "18:00"
  quiet_hours_enabled?: boolean;
  timezone?: string;
}
