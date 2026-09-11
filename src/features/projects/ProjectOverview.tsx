import React, { useEffect, useState, useRef } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import {
  Users,
  Bell,
  MoreHorizontal,
  FileText,
  Folder,
  MessageCircle,
  TrendingUp,
  Settings,
  Archive,
  Trash2,
  Calendar as CalendarIcon,
  HelpCircle,
  CheckCircle2,
  ExternalLink,
  Link2,
  Image as ImageIcon,
  Columns,
  Plus,
  Megaphone,
  Check,
  UserPlus,
} from 'lucide-react';
import { useToast } from '../../context/ToastContext';
import {
  Project,
  ProjectMember,
  Task,
  Discussion,
  CalendarEvent,
  CheckinQuestion,
  ChatMessage,
  Folder as FolderType,
  ProjectFile,
  TodoList,
} from '../../types';
import { HillChart, HillScope } from '../../components/projects/HillChart';
import { CustomizeToolsModal } from '../../components/projects/CustomizeToolsModal';
import { ProjectTrashModal } from '../../components/projects/ProjectTrashModal';
import { ProjectWebhooksModal } from '../../components/projects/ProjectWebhooksModal';
import { ArchiveOrDeleteModal } from '../../components/projects/ArchiveOrDeleteModal';
import { LeaveProjectModal } from '../../components/projects/LeaveProjectModal';
import { getTasks } from '../../services/taskService';
import { getTodoLists } from '../../services/todoService';
import { getDiscussions } from '../../services/discussionService';
import { getCalendarEvents } from '../../services/calendarService';
import { getCheckinQuestions } from '../../services/checkinService';
import { getOrCreateProjectChatRoom, getProjectChatMessages } from '../../services/chatService';
import { getProjectFilesAndFolders } from '../../services/fileService';
import { getProjectHillScopes, updateHillScopePosition } from '../../services/hillChartService';
import { updateProject, getProjectToolSettings, updateProjectToolSettings } from '../../services/projectService';
import { useAuth } from '../../context/AuthContext';
import { canManageProjectSettings, canAccessCampfire } from '../../lib/permissions';

interface ProjectOverviewProps {
  project: Project;
  members: ProjectMember[];
  toolSettings?: any;
  onOpenMembersModal: () => void;
  initialEditingSettings?: boolean;
  onProjectUpdated?: (updated: Project) => void;
  onToolsUpdated?: (updatedTools: any) => void;
}

// Avatar color palette matching Basecamp
const AVATAR_COLORS: Record<string, string> = {
  AI: 'bg-teal-500',
  GK: 'bg-pink-500',
  PK: 'bg-cyan-500',
  RK: 'bg-rose-500',
  SN: 'bg-purple-600',
  CW: 'bg-purple-600',
  E: 'bg-orange-500',
  RS: 'bg-teal-600',
  B: 'bg-amber-500',
  BGD: 'bg-red-500',
  PT: 'bg-slate-500',
  TL: 'bg-indigo-600',
  AR: 'bg-emerald-600',
};

function getInitials(fullName?: string): string {
  if (!fullName) return 'CW';
  if (fullName === 'Claire Watson') return 'CW';
  if (fullName === 'Edward') return 'E';
  if (fullName === 'Gaurav Kumar') return 'GK';
  if (fullName === 'Pankaj Kumar') return 'PK';
  if (fullName === 'Rohit Kumar') return 'RK';
  if (fullName === 'Rasgo') return 'RS';
  if (fullName === 'Pradeep Tiwari') return 'PT';
  if (fullName === 'Ajath Infotech') return 'AI';
  const parts = fullName.trim().split(/\s+/);
  if (parts.length >= 2) {
    return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
  }
  return fullName.slice(0, 2).toUpperCase();
}

function getAvatarColor(initials: string): string {
  if (AVATAR_COLORS[initials]) return AVATAR_COLORS[initials];
  return 'bg-blue-500';
}

export const AVAILABLE_TOOLS = [
  {
    key: 'message_board',
    name: 'Message Board',
    description: 'Broadcast announcements & updates',
    icon: Megaphone,
  },
  {
    key: 'todos',
    name: 'To-dos',
    description: 'Make lists, assign tasks, get stuff done',
    icon: Check,
  },
  {
    key: 'docs_files',
    name: 'Docs & Files',
    description: 'Store docs, files, PDFs, images, etc.',
    icon: Folder,
  },
  {
    key: 'schedule',
    name: 'Calendar',
    description: 'Add events, milestones, deadlines, etc.',
    icon: CalendarIcon,
  },
  {
    key: 'campfire',
    name: 'Chat',
    description: 'Add a chat room to the project',
    icon: MessageCircle,
  },
  {
    key: 'card_table',
    name: 'Card Table',
    description: 'Track work visually on cards & columns',
    icon: Columns,
  },
  {
    key: 'doors',
    name: 'Doors',
    description: 'Links to tools outside Ajath PMT',
    icon: ExternalLink,
  },
  {
    key: 'checkins',
    name: 'Automatic Check-ins',
    description: 'Ask regular questions to keep everyone aligned',
    icon: HelpCircle,
  },
];

export function ProjectOverview({
  project,
  members,
  toolSettings,
  onOpenMembersModal,
  initialEditingSettings = false,
  onProjectUpdated,
  onToolsUpdated,
}: ProjectOverviewProps) {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { profile, userRole } = useAuth();
  const currentUserInitials = getInitials(profile?.full_name || 'Claire Watson');

  const [currentProject, setCurrentProject] = useState<Project>(project);
  useEffect(() => {
    setCurrentProject(project);
    if (project?.name) {
      document.title = project.name;
    }
  }, [project]);

  // Real-time tool state matching Basecamp
  const [currentTools, setCurrentTools] = useState<Record<string, boolean>>(() => {
    return {
      message_board: !!toolSettings?.message_board,
      todos: !!toolSettings?.todos,
      docs_files: !!toolSettings?.docs_files,
      campfire: !!toolSettings?.campfire,
      schedule: !!toolSettings?.schedule,
      checkins: !!toolSettings?.checkins,
      card_table: !!(toolSettings?.card_table || toolSettings?.kanban),
      doors: !!toolSettings?.doors,
    };
  });

  useEffect(() => {
    if (toolSettings) {
      setCurrentTools({
        message_board: !!toolSettings.message_board,
        todos: !!toolSettings.todos,
        docs_files: !!toolSettings.docs_files,
        campfire: !!toolSettings.campfire,
        schedule: !!toolSettings.schedule,
        checkins: !!toolSettings.checkins,
        card_table: !!(toolSettings.card_table || toolSettings.kanban),
        doors: !!toolSettings.doors,
      });
    } else if (project.id) {
      getProjectToolSettings(project.id).then((data) => {
        if (data) {
          setCurrentTools({
            message_board: !!data.message_board,
            todos: !!data.todos,
            docs_files: !!data.docs_files,
            campfire: !!data.campfire,
            schedule: !!data.schedule,
            checkins: !!data.checkins,
            card_table: !!(data.card_table || data.kanban),
            doors: !!data.doors,
          });
        }
      });
    }
  }, [toolSettings, project.id]);

  const isToolEnabled = (key: string) => {
    if (key === 'card_table') return !!(currentTools?.card_table || currentTools?.kanban);
    return !!currentTools?.[key];
  };

  const enabledToolCount = AVAILABLE_TOOLS.filter((t) => isToolEnabled(t.key)).length;
  const unaddedTools = AVAILABLE_TOOLS.filter((t) => !isToolEnabled(t.key));

  const currentUserProjectRole = members?.find((m) => m.user_id === profile?.id)?.role;
  const canManageSettings = canManageProjectSettings(userRole, currentUserProjectRole);
  const canCampfire = canAccessCampfire(userRole);

  const handleAddTool = async (toolKey: string, toolName: string) => {
    if (!canManageSettings) {
      addToast('Only project managers and administrators can customize tools.', 'error');
      return;
    }
    const updated = {
      ...currentTools,
      [toolKey]: true,
      ...(toolKey === 'card_table' ? { kanban: true } : {}),
    };
    setCurrentTools(updated);
    try {
      localStorage.setItem(`proj_tools_${currentProject.id}`, JSON.stringify(updated));
    } catch (e) {}
    await updateProjectToolSettings(currentProject.id, updated);
    onToolsUpdated?.(updated);
    addToast(`Added "${toolName}" to ${currentProject.name}`, 'success');
  };

  const [isEditingSettings, setIsEditingSettings] = useState(
    canManageSettings && (initialEditingSettings || searchParams.get('settings') === 'true')
  );

  // Project Settings form state matching Basecamp screenshot
  const [editName, setEditName] = useState(project.name);
  const [editDescription, setEditDescription] = useState(project.description || '');
  const [editLogo, setEditLogo] = useState(project.cover_url || '');
  const [startDate, setStartDate] = useState(project.start_date || '');
  const [endDate, setEndDate] = useState(project.end_date || '');
  const [isPickingDates, setIsPickingDates] = useState(false);
  const [indicateProgress, setIndicateProgress] = useState<boolean>(() => {
    try {
      const saved = localStorage.getItem(`proj_gauge_${project.id}`);
      return saved ? JSON.parse(saved) : true;
    } catch (e) {
      return true;
    }
  });
  const [showProgressHelp, setShowProgressHelp] = useState(false);
  const [isSavingSettings, setIsSavingSettings] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    setEditName(currentProject.name);
    setEditDescription(currentProject.description || '');
    setEditLogo(currentProject.cover_url || '');
    setStartDate(currentProject.start_date || '');
    setEndDate(currentProject.end_date || '');
  }, [currentProject]);

  const [todoLists, setTodoLists] = useState<TodoList[]>([]);
  const [tasks, setTasks] = useState<Task[]>([]);
  const [discussions, setDiscussions] = useState<Discussion[]>([]);
  const [events, setEvents] = useState<CalendarEvent[]>([]);
  const [checkins, setCheckins] = useState<CheckinQuestion[]>([]);
  const [chatMessages, setChatMessages] = useState<ChatMessage[]>([]);
  const [folders, setFolders] = useState<FolderType[]>([]);
  const [files, setFiles] = useState<ProjectFile[]>([]);
  const [hillScopes, setHillScopes] = useState<HillScope[]>([]);
  const [loading, setLoading] = useState(true);

  const { addToast } = useToast();

  // Utility bar toggles & action modals
  const [isNotificationsOn, setIsNotificationsOn] = useState(() => {
    try {
      const saved = localStorage.getItem(`basecamp_project_notif_${project.id}`);
      return saved ? JSON.parse(saved) : true;
    } catch (e) {
      return true;
    }
  });
  const [isActionsOpen, setIsActionsOpen] = useState(false);
  const [isHillChartOpen, setIsHillChartOpen] = useState(false);
  const actionsMenuRef = useRef<HTMLDivElement>(null);

  // Modals from the Actions Popover
  const [isCustomizeToolsOpen, setIsCustomizeToolsOpen] = useState(false);
  const [isTrashOpen, setIsTrashOpen] = useState(false);
  const [isWebhooksOpen, setIsWebhooksOpen] = useState(false);
  const [isArchiveDeleteOpen, setIsArchiveDeleteOpen] = useState(false);
  const [isLeaveProjectOpen, setIsLeaveProjectOpen] = useState(false);

  const handleToggleNotifications = () => {
    setIsNotificationsOn((prev: boolean) => {
      const next = !prev;
      try {
        localStorage.setItem(`basecamp_project_notif_${currentProject.id}`, JSON.stringify(next));
      } catch (e) {}
      addToast(
        next ? `Notifications turned on for ${currentProject.name}` : `Notifications paused for ${currentProject.name}`,
        'info'
      );
      return next;
    });
  };

  const handleSaveAsTemplate = () => {
    try {
      const existing = localStorage.getItem('basecamp_custom_templates');
      const templates = existing ? JSON.parse(existing) : [];
      templates.push({
        id: `tpl-${Date.now()}`,
        name: `${currentProject.name} Template`,
        description: currentProject.description || 'Template copied from project',
        saved_at: new Date().toISOString(),
      });
      localStorage.setItem('basecamp_custom_templates', JSON.stringify(templates));
    } catch (e) {}
    addToast(`"${currentProject.name}" saved as a project template!`, 'success');
  };

  const handleCopyLink = () => {
    navigator.clipboard.writeText(window.location.href);
    addToast('Project link copied to clipboard!', 'info');
  };

  const handleSaveSettings = async (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    if (!canManageSettings) {
      addToast('Only project managers and administrators can modify project settings.', 'error');
      return;
    }
    if (!editName.trim()) {
      addToast('Please enter a project name', 'error');
      return;
    }
    setIsSavingSettings(true);
    try {
      const updates = {
        name: editName.trim(),
        description: editDescription.trim(),
        cover_url: editLogo,
        start_date: startDate || undefined,
        end_date: endDate || undefined,
      };
      const res = await updateProject(currentProject.id, updates);
      if (res.error) {
        addToast(res.error.message || 'Failed to update project settings', 'error');
      } else {
        const updatedProj = { ...currentProject, ...updates };
        setCurrentProject(updatedProj);
        try {
          localStorage.setItem(`proj_gauge_${currentProject.id}`, JSON.stringify(indicateProgress));
        } catch (e) {}
        addToast('Project settings saved!', 'success');
        setIsEditingSettings(false);
        if (onProjectUpdated) {
          onProjectUpdated(updatedProj);
        }
        if (initialEditingSettings) {
          navigate(`/projects/${currentProject.id}`);
        }
      }
    } catch (err: any) {
      addToast('Failed to update project settings', 'error');
    } finally {
      setIsSavingSettings(false);
    }
  };

  const handleNeverMind = () => {
    setEditName(currentProject.name);
    setEditDescription(currentProject.description || '');
    setEditLogo(currentProject.cover_url || '');
    setStartDate(currentProject.start_date || '');
    setEndDate(currentProject.end_date || '');
    setIsEditingSettings(false);
    setIsPickingDates(false);
    if (initialEditingSettings) {
      navigate(`/projects/${currentProject.id}`);
    }
  };

  const handleImageFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (event) => {
        if (event.target?.result) {
          setEditLogo(event.target.result as string);
        }
      };
      reader.readAsDataURL(file);
    }
  };

  useEffect(() => {
    async function loadData() {
      if (!project.id) return;
      setLoading(true);
      try {
        const [tLists, tList, dList, eList, cList, hScopes, fData] = await Promise.all([
          getTodoLists(project.id).catch(() => []),
          getTasks(project.id).catch(() => []),
          getDiscussions(project.id).catch(() => []),
          getCalendarEvents(project.id).catch(() => []),
          getCheckinQuestions(project.id).catch(() => []),
          getProjectHillScopes(project.id).catch(() => []),
          getProjectFilesAndFolders(project.id).catch(() => ({ folders: [], files: [] })),
        ]);
        setTodoLists(tLists);
        setTasks(tList);
        // Ensure clients only receive discussions marked client visible
        const clientSafeDiscussions = userRole === 'CLIENT' ? dList.filter((d: any) => d.is_client_visible !== false) : dList;
        setDiscussions(clientSafeDiscussions);
        setEvents(eList);
        setCheckins(cList);
        if (hScopes && hScopes.length > 0) {
          setHillScopes(hScopes as HillScope[]);
        }
        setFolders(fData.folders || []);
        setFiles(fData.files || []);

        // Fetch Campfire chat messages (strictly internal team members only)
        if (canCampfire) {
          const roomId = await getOrCreateProjectChatRoom(project.id).catch(() => null);
          if (roomId) {
            const messages = await getProjectChatMessages(roomId).catch(() => []);
            setChatMessages(messages);
          }
        }
      } catch (err) {
        console.error('Failed to load project overview data', err);
      } finally {
        setLoading(false);
      }
    }
    loadData();
  }, [project.id]);

  // Close actions menu on outside click
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (actionsMenuRef.current && !actionsMenuRef.current.contains(e.target as Node)) {
        setIsActionsOpen(false);
      }
    };
    if (isActionsOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [isActionsOpen]);

  const handleUpdateScopePosition = async (scopeId: string, newPos: number) => {
    setHillScopes((prev) =>
      prev.map((s) => (s.id === scopeId ? { ...s, progress: newPos } : s))
    );
    await updateHillScopePosition(project.id, scopeId, newPos);
  };

  // Primary list for to-dos card
  const primaryTodoList = todoLists[0];
  const primaryTasks = primaryTodoList
    ? tasks.filter((t) => t.todo_list_id === primaryTodoList.id)
    : tasks.slice(0, 5);

  const completedCount = primaryTasks.filter((t) => t.status === 'COMPLETED').length;
  const totalCount = primaryTasks.length;
  const percentComplete = totalCount > 0 ? (completedCount / totalCount) * 100 : 33;

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 pb-16 animate-in fade-in duration-200">
      {/* Modern Project Workspace Card */}
      <div className="w-full bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
        {/* 1. Top Utility Bar: Avatars pile | Move the needle | Notifications | Actions */}
        <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800/80">
          {/* Left: People Stack OR Invite People button */}
          {members.length > 1 ? (
            <div className="flex items-center gap-3">
              {/* Avatars Cluster */}
              {userRole !== 'CLIENT' ? (
                <div className="flex items-center gap-1.5">
                  <button
                    onClick={onOpenMembersModal}
                    className="flex items-center gap-1.5 p-1 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer group"
                    title="Quick add or remove project members"
                  >
                    <div className="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300">
                      <Users className="w-3.5 h-3.5" />
                    </div>
                    <div className="flex -space-x-1.5 overflow-hidden py-0.5">
                      {members.slice(0, 5).map((m) => {
                        const name = m.profile?.full_name || 'Member';
                        const initials = getInitials(name);
                        const color = getAvatarColor(initials);
                        return (
                          <div
                            key={m.id}
                            title={name}
                            className={`w-5 h-5 rounded-full ${color} text-white font-black text-[8px] tracking-tight flex items-center justify-center ring-1 ring-white dark:ring-slate-900 select-none`}
                          >
                            {initials}
                          </div>
                        );
                      })}
                    </div>
                  </button>

                  <button
                    onClick={() => navigate(`/projects/${currentProject.id}/people`)}
                    className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition-colors cursor-pointer"
                    title="Open Ajath PMT People & Client Access manager"
                  >
                    <UserPlus className="w-3.5 h-3.5" />
                    <span>+ Add / Invite</span>
                  </button>
                </div>
              ) : (
                <div
                  className="flex items-center gap-1.5 p-1 rounded-full select-none"
                  title="Project team members"
                >
                  <div className="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300">
                    <Users className="w-3.5 h-3.5" />
                  </div>
                  <div className="flex -space-x-1.5 overflow-hidden py-0.5">
                    {members.slice(0, 5).map((m) => {
                      const name = m.profile?.full_name || 'Member';
                      const initials = getInitials(name);
                      const color = getAvatarColor(initials);
                      return (
                        <div
                          key={m.id}
                          title={name}
                          className={`w-5 h-5 rounded-full ${color} text-white font-black text-[8px] tracking-tight flex items-center justify-center ring-1 ring-white dark:ring-slate-900 select-none`}
                        >
                          {initials}
                        </div>
                      );
                    })}
                  </div>
                </div>
              )}

              {/* Vertical separator */}
              <div className="h-4 w-px bg-slate-200 dark:bg-slate-700" />

              {/* Move the needle (Hill Chart toggle) */}
              <button
                onClick={() => setIsHillChartOpen(!isHillChartOpen)}
                className="flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-slate-200 transition-colors cursor-pointer py-1 px-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800"
                title="Toggle Hill Chart & Project Lineup"
              >
                <svg viewBox="0 0 24 24" fill="none" className="w-4 h-4 text-slate-500" stroke="currentColor" strokeWidth="2">
                  <path d="M3 18h18M5 14l4-8 6 6 4-4" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
                <span>Move the needle</span>
              </button>
            </div>
          ) : (
            <div className="flex items-center gap-2">
              {userRole !== 'CLIENT' ? (
                <div className="flex items-center gap-2">
                  <button
                    onClick={onOpenMembersModal}
                    className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 text-xs font-semibold text-slate-700 dark:text-slate-200 transition-colors shadow-2xs cursor-pointer select-none"
                    title="Add company members to this project"
                  >
                    <div className="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-300">
                      <Users className="w-3 h-3" />
                    </div>
                    <span>Add members</span>
                  </button>
                  <button
                    onClick={() => navigate(`/projects/${currentProject.id}/people`)}
                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900 text-xs font-bold text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60 transition-colors cursor-pointer"
                    title="Invite people and manage access"
                  >
                    <UserPlus className="w-3.5 h-3.5" />
                    <span>Invite people</span>
                  </button>
                </div>
              ) : (
                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold">
                  <Users className="w-3.5 h-3.5 text-slate-400" />
                  <span>Project Team</span>
                </span>
              )}
            </div>
          )}

          {/* Right: Notifications on + Options dropdown */}
          <div className="flex items-center gap-2 relative" ref={actionsMenuRef}>
            <button
              onClick={handleToggleNotifications}
              className="flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1 rounded-full transition-colors cursor-pointer"
              title="Notification preferences"
            >
              <Bell className="w-3.5 h-3.5 text-slate-500" />
              <span>{isNotificationsOn ? 'Notifications on' : 'Notifications paused'}</span>
            </button>

            <button
              onClick={() => setIsActionsOpen(!isActionsOpen)}
              className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
              title="More project actions"
            >
              <MoreHorizontal className="w-4 h-4" />
            </button>

            {/* Actions Popover matching Basecamp screenshot */}
            {isActionsOpen && (
              <div className="absolute right-0 top-9 w-60 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 py-1.5 z-50 animate-in zoom-in-95 duration-100 text-[13px]">
                {/* 1. Project settings (Project managers & Admins only) */}
                {canManageSettings && (
                  <button
                    onClick={() => {
                      setIsActionsOpen(false);
                      setIsEditingSettings(true);
                    }}
                    className="w-full px-4 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2.5 text-slate-700 dark:text-slate-200 font-medium cursor-pointer"
                  >
                    <Settings className="w-4 h-4 text-slate-500 shrink-0" />
                    <span>Project settings</span>
                  </button>
                )}

                {/* 1.5. Manage People & Invites (Team members & Admins) */}
                {userRole !== 'CLIENT' && (
                  <button
                    onClick={() => {
                      setIsActionsOpen(false);
                      navigate(`/projects/${currentProject.id}/people`);
                    }}
                    className="w-full px-4 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2.5 text-slate-700 dark:text-slate-200 font-medium cursor-pointer"
                  >
                    <Users className="w-4 h-4 text-slate-500 shrink-0" />
                    <span>Manage People & Invites</span>
                  </button>
                )}

                {/* 2. Customize tools (Project managers & Admins only) */}
                {canManageSettings && (
                  <button
                    onClick={() => {
                      setIsActionsOpen(false);
                      setIsCustomizeToolsOpen(true);
                    }}
                    className="w-full px-4 py-2 pl-10.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium cursor-pointer"
                  >
                    Customize tools
                  </button>
                )}

                {/* 3. Save as a project template (Owner & Admin only) */}
                {['OWNER', 'ADMIN'].includes(userRole) && (
                  <button
                    onClick={() => {
                      setIsActionsOpen(false);
                      handleSaveAsTemplate();
                    }}
                    className="w-full px-4 py-2 pl-10.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium cursor-pointer"
                  >
                    Save as a project template
                  </button>
                )}

                {/* 4. Copy link (Available to all) */}
                <button
                  onClick={() => {
                    setIsActionsOpen(false);
                    handleCopyLink();
                  }}
                  className="w-full px-4 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2.5 text-slate-700 dark:text-slate-200 font-medium cursor-pointer"
                >
                  <Link2 className="w-4 h-4 text-slate-500 shrink-0" />
                  <span>Copy link</span>
                </button>

                {/* Divider */}
                <div className="my-1.5 border-t border-slate-100 dark:border-slate-800" />

                {/* 5. Leave this project (Members and Clients) */}
                {userRole !== 'OWNER' && (
                  <button
                    onClick={() => {
                      setIsActionsOpen(false);
                      setIsLeaveProjectOpen(true);
                    }}
                    className="w-full px-4 py-2 pl-10.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium cursor-pointer"
                  >
                    Leave this project
                  </button>
                )}

                {/* 6. See items in the trash (Internal team only, hidden for Clients) */}
                {userRole !== 'CLIENT' && (
                  <button
                    onClick={() => {
                      setIsActionsOpen(false);
                      setIsTrashOpen(true);
                    }}
                    className="w-full px-4 py-2 pl-10.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium cursor-pointer"
                  >
                    See items in the trash
                  </button>
                )}

                {/* 7. Set up webhooks (Owner & Admin only) */}
                {['OWNER', 'ADMIN'].includes(userRole) && (
                  <button
                    onClick={() => {
                      setIsActionsOpen(false);
                      setIsWebhooksOpen(true);
                    }}
                    className="w-full px-4 py-2 pl-10.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium cursor-pointer"
                  >
                    Set up webhooks
                  </button>
                )}

                {/* 8. Archive or delete (Project managers & Admins only) */}
                {canManageSettings && (
                  <button
                    onClick={() => {
                      setIsActionsOpen(false);
                      setIsArchiveDeleteOpen(true);
                    }}
                    className="w-full px-4 py-2 pl-10.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-rose-600 dark:text-rose-400 font-medium cursor-pointer"
                  >
                    Archive or delete
                  </button>
                )}
              </div>
            )}
          </div>
        </div>

        {isEditingSettings ? (
          /* Project Settings Edit Form matching user's screenshot */
          <div className="pt-2 sm:pt-4 max-w-3xl space-y-6 animate-in fade-in duration-200">
            {/* Logo Box + Title Input + Description Input */}
            <div className="flex items-start gap-4 sm:gap-6">
              {/* 1. Logo / Image box on left matching screenshot */}
              <div className="relative shrink-0">
                <input
                  type="file"
                  ref={fileInputRef}
                  accept="image/*"
                  onChange={handleImageFileChange}
                  className="hidden"
                />
                <div
                  onClick={() => fileInputRef.current?.click()}
                  className="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 flex items-center justify-center text-slate-400 hover:border-slate-300 dark:hover:border-slate-600 transition-colors cursor-pointer shadow-2xs group relative overflow-hidden"
                  title="Upload project logo"
                >
                  {editLogo ? (
                    <div className="relative w-full h-full group">
                      <img
                        src={editLogo}
                        alt="Project logo"
                        className="w-full h-full object-cover rounded-2xl"
                      />
                      <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white text-[10px] font-bold">
                        Change
                      </div>
                    </div>
                  ) : (
                    <ImageIcon className="w-7 h-7 sm:w-8 sm:h-8 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" />
                  )}
                </div>
              </div>

              {/* 2. Title & Description inputs */}
              <div className="flex-1 min-w-0 space-y-1">
                <input
                  type="text"
                  value={editName}
                  onChange={(e) => setEditName(e.target.value)}
                  placeholder="Project name"
                  autoFocus
                  className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight w-full border-none outline-none focus:outline-none focus:ring-0 p-0 bg-transparent placeholder:text-slate-300 dark:placeholder:text-slate-600"
                />
                <input
                  type="text"
                  value={editDescription}
                  onChange={(e) => setEditDescription(e.target.value)}
                  placeholder="Add an optional description"
                  className="text-sm sm:text-base text-slate-500 dark:text-slate-400 w-full border-none outline-none focus:outline-none focus:ring-0 p-0 bg-transparent placeholder:text-slate-400 dark:placeholder:text-slate-600 mt-1"
                />
              </div>
            </div>

            {/* 3. Settings controls matching screenshot layout */}
            <div className="space-y-4 pt-6 sm:pt-8">
              {/* Starts/Ends Row */}
              <div className="flex items-center">
                <div className="w-32 sm:w-40 text-right pr-4 font-bold text-slate-900 dark:text-slate-100 text-sm shrink-0 select-none">
                  Starts/Ends
                </div>
                <div className="flex items-center gap-2">
                  {!isPickingDates && !startDate && !endDate ? (
                    <button
                      type="button"
                      onClick={() => setIsPickingDates(true)}
                      className="text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 font-medium cursor-pointer transition-colors"
                    >
                      Select dates...
                    </button>
                  ) : isPickingDates ? (
                    <div className="flex items-center gap-2 flex-wrap">
                      <input
                        type="date"
                        value={startDate}
                        onChange={(e) => setStartDate(e.target.value)}
                        className="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500"
                      />
                      <span className="text-xs text-slate-400">to</span>
                      <input
                        type="date"
                        value={endDate}
                        onChange={(e) => setEndDate(e.target.value)}
                        className="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500"
                      />
                      <button
                        type="button"
                        onClick={() => setIsPickingDates(false)}
                        className="text-xs text-blue-600 hover:underline font-semibold cursor-pointer ml-1"
                      >
                        Done
                      </button>
                      {(startDate || endDate) && (
                        <button
                          type="button"
                          onClick={() => {
                            setStartDate('');
                            setEndDate('');
                            setIsPickingDates(false);
                          }}
                          className="text-xs text-rose-500 hover:underline font-medium cursor-pointer"
                        >
                          Clear
                        </button>
                      )}
                    </div>
                  ) : (
                    <div className="flex items-center gap-2">
                      <span className="text-sm font-semibold text-slate-700 dark:text-slate-300">
                        {startDate
                          ? new Date(startDate).toLocaleDateString('en-US', {
                              month: 'short',
                              day: 'numeric',
                              year: 'numeric',
                            })
                          : 'Anytime'}{' '}
                        –{' '}
                        {endDate
                          ? new Date(endDate).toLocaleDateString('en-US', {
                              month: 'short',
                              day: 'numeric',
                              year: 'numeric',
                            })
                          : 'Ongoing'}
                      </span>
                      <button
                        type="button"
                        onClick={() => setIsPickingDates(true)}
                        className="text-xs text-blue-600 hover:underline font-semibold cursor-pointer"
                      >
                        Change
                      </button>
                    </div>
                  )}
                </div>
              </div>

              {/* Indicate progress Row */}
              <div className="flex items-center">
                <div className="w-32 sm:w-40 text-right pr-4 font-bold text-slate-900 dark:text-slate-100 text-sm shrink-0 select-none">
                  Indicate progress
                </div>
                <div className="flex items-center">
                  {/* Authentic Basecamp green toggle switch */}
                  <button
                    type="button"
                    onClick={() => setIndicateProgress(!indicateProgress)}
                    className={`w-[58px] h-7 rounded-full transition-colors flex items-center px-1 cursor-pointer select-none ${
                      indicateProgress ? 'bg-[#28A745]' : 'bg-slate-300 dark:bg-slate-700'
                    }`}
                    aria-label="Toggle progress gauge"
                  >
                    {indicateProgress ? (
                      <>
                        <span className="text-[11px] font-bold text-white pl-1.5 leading-none">On</span>
                        <div className="w-5 h-5 rounded-full bg-white shadow-xs ml-auto" />
                      </>
                    ) : (
                      <>
                        <div className="w-5 h-5 rounded-full bg-white shadow-xs mr-auto" />
                        <span className="text-[11px] font-bold text-slate-600 dark:text-slate-300 pr-1.5 leading-none">
                          Off
                        </span>
                      </>
                    )}
                  </button>

                  <span
                    onClick={() => setIndicateProgress(!indicateProgress)}
                    className="text-sm font-medium text-slate-800 dark:text-slate-200 ml-3 cursor-pointer select-none"
                  >
                    Show a gauge to indicate progress & status
                  </span>

                  {/* Help icon */}
                  <div className="relative inline-block">
                    <button
                      type="button"
                      onClick={() => setShowProgressHelp(!showProgressHelp)}
                      className="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[11px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 cursor-pointer ml-2 transition-colors select-none"
                      title="Learn more about progress indicator"
                    >
                      <span>?</span>
                      <span className="text-[9px]">▶</span>
                    </button>
                    {showProgressHelp && (
                      <div className="absolute left-0 top-7 w-72 p-3 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300 z-50 animate-in fade-in">
                        <p className="font-semibold text-slate-900 dark:text-slate-100 mb-1">
                          Move the needle (Hill Chart)
                        </p>
                        <p className="leading-relaxed">
                          The progress gauge displays a visual Hill Chart at the top of the project to show whether work is in the "figuring things out" phase or "getting it done" phase.
                        </p>
                      </div>
                    )}
                  </div>
                </div>
              </div>

              {/* Action Buttons Row */}
              <div className="flex items-center pt-3 pl-32 sm:pl-40">
                <button
                  type="button"
                  onClick={handleSaveSettings}
                  disabled={isSavingSettings}
                  className="bg-[#1d63dd] hover:bg-[#1852b7] active:bg-[#15469d] text-white font-bold px-6 py-2 rounded-xl text-sm shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                >
                  {isSavingSettings ? 'Saving...' : 'Save'}
                </button>
                <button
                  type="button"
                  onClick={handleNeverMind}
                  className="text-[#1d63dd] hover:underline font-medium text-sm ml-4 cursor-pointer"
                >
                  Never mind
                </button>
              </div>
            </div>
          </div>
        ) : (
          <>
            {/* Client Partner Banner */}
            {userRole === 'CLIENT' && (
              <div className="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800 flex items-center justify-between text-xs text-amber-900 dark:text-amber-200 shadow-xs">
                <div className="flex items-center gap-2.5">
                  <span className="text-base select-none">🤝</span>
                  <span className="font-semibold">
                    <strong className="font-bold">Client Partner View:</strong> You have collaborative access to client-approved discussions, to-dos, and files. Team-only items and internal Campfire chat are hidden.
                  </span>
                </div>
                <span className="shrink-0 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-200 dark:bg-amber-900 text-amber-800 dark:text-amber-200">
                  Client View
                </span>
              </div>
            )}

            {/* 2. Vibrant Project Command Banner */}
            <div className="p-6 sm:p-7 rounded-3xl bg-gradient-to-r from-blue-50 via-indigo-50/50 to-purple-50 dark:from-slate-800/80 dark:via-slate-850 dark:to-slate-800/80 border border-blue-100 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div className="space-y-1.5">
                <div className="flex items-center gap-2">
                  <span className="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-blue-600 text-white shadow-xs">
                    Workspace
                  </span>
                  {currentProject.description && (
                    <span className="text-xs font-semibold text-slate-500 dark:text-slate-400">
                      {currentProject.description.split('•')[0]}
                    </span>
                  )}
                </div>

                <h1 className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                  {currentProject.name}
                </h1>

                <div className="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 pt-0.5">
                  <div className="w-5 h-5 rounded-full bg-orange-500 text-white font-bold text-[9px] flex items-center justify-center shrink-0">
                    E
                  </div>
                  <span>Active recently • </span>
                  <button
                    onClick={() => navigate(`/projects/${currentProject.id}/activity`)}
                    className="text-blue-600 dark:text-blue-400 hover:underline font-bold"
                  >
                    View activity feed
                  </button>
                </div>
              </div>

              {/* Quick Hill chart toggle and customize tools buttons */}
              <div className="flex items-center gap-2">
                <button
                  onClick={() => setIsHillChartOpen(!isHillChartOpen)}
                  className={`flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                    isHillChartOpen
                      ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-xs'
                      : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:bg-slate-50'
                  }`}
                >
                  <TrendingUp className="w-3.5 h-3.5" />
                  <span>Hill Chart</span>
                </button>

                {['OWNER', 'ADMIN'].includes(userRole) && (
                  <button
                    onClick={() => setIsCustomizeToolsOpen(true)}
                    className="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-xs font-bold transition-all cursor-pointer"
                  >
                    <Settings className="w-3.5 h-3.5 text-slate-400" />
                    <span>Tools</span>
                  </button>
                )}
              </div>
            </div>

            {/* If 0 tools are enabled (brand new project like Basecamp screenshot), show ADD A TOOL menu */}
            {enabledToolCount === 0 ? (
              ['OWNER', 'ADMIN'].includes(userRole) ? (
              <div className="w-full max-w-[370px] pt-4 space-y-2 animate-in fade-in duration-200">
                <div className="text-[12px] font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400 select-none">
                  ADD A TOOL
                </div>

                {/* Scrollable list matching user's screenshot */}
                <div className="w-full max-h-[350px] overflow-y-auto pr-1 space-y-0.5 custom-scrollbar">
                  {AVAILABLE_TOOLS.map((tool) => {
                    const ToolIcon = tool.icon;
                    return (
                      <div
                        key={tool.key}
                        onClick={() => handleAddTool(tool.key, tool.name)}
                        className="flex items-center gap-3.5 p-2 sm:p-2.5 rounded-xl hover:bg-slate-100/90 dark:hover:bg-slate-800 transition-colors cursor-pointer group select-none"
                      >
                        {/* Dark circular badge with white icon */}
                        <div className="w-9 h-9 rounded-full bg-[#20272F] dark:bg-slate-800 text-white flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                          <ToolIcon className="w-4 h-4 text-white stroke-[2.2]" />
                        </div>

                        {/* Tool title & description */}
                        <div className="min-w-0 flex-1">
                          <div className="font-bold text-slate-900 dark:text-slate-100 text-sm leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            {tool.name}
                          </div>
                          <div className="text-xs text-slate-500 dark:text-slate-400 leading-snug mt-0.5 truncate">
                            {tool.description}
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
              ) : (
                <div className="py-12 text-center text-slate-400 text-sm">
                  No tools have been enabled for this project yet.
                </div>
              )
            ) : (
              <>
                {/* Optional Expandable Hill Chart Section */}
                {isHillChartOpen && (
                  <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 animate-in fade-in duration-200">
                    <HillChart scopes={hillScopes} onUpdateScopePosition={handleUpdateScopePosition} />
                  </div>
                )}

                {/* 3. Modern Vibrant Tool Hub Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pt-2">
                  {/* TOOL 1: Message Board */}
                  {currentTools.message_board && (
                    <div className="flex flex-col space-y-2">
                      <div className="flex items-center justify-between pb-1">
                        <div className="flex items-center gap-2">
                          <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-xs">
                            <Megaphone className="w-4 h-4" />
                          </div>
                          <h3 className="text-slate-900 dark:text-white font-bold text-base tracking-tight">
                            Message Board
                          </h3>
                        </div>
                        <button
                          onClick={(e) => {
                            e.stopPropagation();
                            navigate(`/projects/${currentProject.id}/messages/new`);
                          }}
                          className="px-3 py-1 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 hover:bg-blue-100 font-bold text-xs transition-colors cursor-pointer border border-blue-200/60 dark:border-blue-800/60"
                        >
                          New message
                        </button>
                      </div>

                      <div
                        onClick={() => navigate(`/projects/${currentProject.id}/discussions`)}
                        className="relative overflow-hidden border border-slate-200/90 dark:border-slate-800 rounded-3xl p-5 bg-white dark:bg-slate-900 shadow-xs hover:shadow-xl hover:border-blue-400 dark:hover:border-blue-600 hover:-translate-y-1 transition-all duration-200 cursor-pointer min-h-[350px] max-h-[420px] overflow-y-auto space-y-4 custom-scrollbar group select-none"
                      >
                        <div className="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-blue-600 to-indigo-600" />
                        {(() => {
                          const visibleDiscussions = discussions.filter((d) => {
                            if (userRole === 'CLIENT') {
                              if (d.is_client_visible === false) return false;
                              if (d.category === 'Worklog') return false;
                              const aEmail = d.author?.email?.toLowerCase() || '';
                              const aName = d.author?.full_name?.toLowerCase() || '';
                              if (d.author_id === profile?.id || (profile?.email && aEmail === profile?.email?.toLowerCase())) return true;
                              if (aEmail.includes('client') || aEmail.includes('edward') || aName.includes('edward')) return true;
                              return d.is_client_visible === true;
                            }
                            return true;
                          });

                          if (visibleDiscussions.length === 0) {
                            return (
                              <div className="text-center py-12 text-xs text-slate-400">
                                {userRole === 'CLIENT' ? 'No client messages posted yet.' : 'No messages posted yet. Pitch your first idea!'}
                              </div>
                            );
                          }

                          return visibleDiscussions.map((d, idx) => {
                            const authorName = d.author?.full_name || 'Member';
                            const initials = getInitials(authorName);
                            const color = getAvatarColor(initials);
                            const dateStr = d.created_at
                              ? new Date(d.created_at).toLocaleDateString([], { month: 'short', day: 'numeric' })
                              : 'Aug 26';

                            return (
                              <div
                                key={d.id}
                                className="flex items-start gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800/80 last:border-0 last:pb-0"
                              >
                                <div
                                  className={`w-6 h-6 rounded-full ${color} text-white font-black text-[8px] flex items-center justify-center shrink-0 select-none mt-0.5`}
                                >
                                  {initials}
                                </div>

                                <div className="min-w-0 flex-1">
                                  <div className="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                                    <span className="font-bold text-slate-800 dark:text-slate-200 truncate">
                                      {authorName}
                                    </span>
                                    <span>{dateStr}</span>
                                  </div>

                                  <p className="font-bold text-xs text-slate-900 dark:text-slate-100 mt-0.5 truncate group-hover:text-blue-600 transition-colors">
                                    {d.title || 'Untitled'}
                                  </p>

                                  {d.content && (
                                    <p className="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1 mt-0.5">
                                      {d.content}
                                    </p>
                                  )}
                                </div>
                              </div>
                            );
                          });
                        })()}
                      </div>
                    </div>
                  )}

                  {/* TOOL 2: To-dos */}
                  {currentTools.todos && (
                    <div className="flex flex-col space-y-2">
                      <div className="flex items-center justify-between pb-1">
                        <div className="flex items-center gap-2">
                          <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-xs">
                            <Check className="w-4 h-4 stroke-[3]" />
                          </div>
                          <h3 className="text-slate-900 dark:text-white font-bold text-base tracking-tight">
                            To-dos & Tasks
                          </h3>
                        </div>
                        <button
                          onClick={() => navigate(`/projects/${currentProject.id}/todos`)}
                          className="px-3 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 font-bold text-xs transition-colors cursor-pointer border border-emerald-200/60 dark:border-emerald-800/60"
                        >
                          Open list
                        </button>
                      </div>

                      <div
                        onClick={() => navigate(`/projects/${currentProject.id}/todos`)}
                        className="relative overflow-hidden border border-slate-200/90 dark:border-slate-800 rounded-3xl p-5 bg-white dark:bg-slate-900 shadow-xs hover:shadow-xl hover:border-emerald-400 dark:hover:border-emerald-600 hover:-translate-y-1 transition-all duration-200 cursor-pointer min-h-[350px] max-h-[420px] overflow-y-auto space-y-4 custom-scrollbar group select-none"
                      >
                        <div className="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-emerald-500 to-teal-600" />
                        {/* List Header with SVG Pie Chart Progress */}
                        <div className="flex items-center gap-2 pb-1 border-b border-slate-100 dark:border-slate-800">
                          <div className="w-4 h-4 rounded-full border border-emerald-500 flex items-center justify-center shrink-0 overflow-hidden relative">
                            <div
                              className="absolute inset-0 bg-emerald-500"
                              style={{ clipPath: 'polygon(50% 50%, 50% 0, 100% 0, 100% 100%, 0 100%, 0 50%)' }}
                            />
                          </div>
                          <h4 className="font-bold text-xs text-slate-900 dark:text-slate-100 truncate">
                            {primaryTodoList?.title || 'Dependency Checklist'}
                          </h4>
                        </div>

                        {/* Checklist items with square checkboxes */}
                        <div className="space-y-2.5">
                          {primaryTasks.length === 0 ? (
                            <div className="text-xs text-slate-400 italic py-4">No tasks in this list yet.</div>
                          ) : (
                            primaryTasks.map((t) => (
                              <div
                                key={t.id}
                                className="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300"
                              >
                                <div className="w-3.5 h-3.5 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 shrink-0 mt-0.5 flex items-center justify-center">
                                  {t.status === 'COMPLETED' && (
                                    <div className="w-2 h-2 rounded-xs bg-emerald-500" />
                                  )}
                                </div>
                                <span
                                  className={`truncate ${
                                    t.status === 'COMPLETED'
                                      ? 'line-through text-slate-400 font-normal'
                                      : 'font-medium text-slate-800 dark:text-slate-200'
                                  }`}
                                >
                                  {t.title}
                                </span>
                              </div>
                            ))
                          )}
                        </div>
                      </div>
                    </div>
                  )}

                  {/* TOOL 3: Docs & Files */}
                  {currentTools.docs_files && (
                    <div className="flex flex-col space-y-2">
                      <div className="flex items-center justify-between pb-1">
                        <div className="flex items-center gap-2">
                          <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-xs">
                            <Folder className="w-4 h-4 fill-amber-400/40" />
                          </div>
                          <h3 className="text-slate-900 dark:text-white font-bold text-base tracking-tight">
                            Docs & Files
                          </h3>
                        </div>
                        <button
                          onClick={() => navigate(`/projects/${currentProject.id}/docs`)}
                          className="px-3 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 hover:bg-amber-100 font-bold text-xs transition-colors cursor-pointer border border-amber-200/60 dark:border-amber-800/60"
                        >
                          View files
                        </button>
                      </div>

                      <div
                        onClick={() => navigate(`/projects/${currentProject.id}/docs`)}
                        className="relative overflow-hidden border border-slate-200/90 dark:border-slate-800 rounded-3xl p-5 bg-white dark:bg-slate-900 shadow-xs hover:shadow-xl hover:border-amber-400 dark:hover:border-amber-600 hover:-translate-y-1 transition-all duration-200 cursor-pointer min-h-[350px] max-h-[420px] overflow-y-auto space-y-3 custom-scrollbar group select-none"
                      >
                        <div className="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-amber-500 to-orange-600" />
                        {/* Files */}
                        {files.map((file) => (
                          <div key={file.id} className="flex items-start gap-2.5 py-1">
                            <div className="w-5 h-5 flex items-center justify-center shrink-0 mt-0.5 text-slate-400">
                              <FileText className="w-4 h-4" />
                            </div>
                            <div className="min-w-0 flex-1">
                              <p className="font-bold text-xs text-slate-900 dark:text-slate-100 truncate group-hover:text-amber-600 transition-colors">
                                {file.name}
                              </p>
                              <p className="text-[10px] text-slate-400 truncate mt-0.5">
                                {file.description || `${file.uploader?.full_name || 'Nirdesh Verma'} • Aug 12, 2025 • 572 KB`}
                              </p>
                            </div>
                          </div>
                        ))}

                        {/* Folders */}
                        {folders.map((folder, idx) => (
                          <div key={folder.id} className="flex items-start gap-2.5 py-1">
                            <div className="w-5 h-5 flex items-center justify-center shrink-0 mt-0.5 text-amber-500">
                              <Folder className="w-4 h-4 fill-amber-400/40" />
                            </div>
                            <div className="min-w-0 flex-1">
                              <p className="font-bold text-xs text-slate-900 dark:text-slate-100 truncate group-hover:text-amber-600 transition-colors">
                                {folder.name}
                              </p>
                              <p className="text-[10px] text-slate-400 truncate mt-0.5">
                                {idx === 1 ? '2 items' : idx === 2 ? '5 items' : '0 items'}
                              </p>
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}

                  {/* TOOL 4: Campfire (Chat) */}
                  {currentTools.campfire && (
                    <div className="flex flex-col space-y-2">
                      <div className="flex items-center justify-between pb-1">
                        <div className="flex items-center gap-2">
                          <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-purple-600 to-pink-600 text-white flex items-center justify-center shadow-xs">
                            <MessageCircle className="w-4 h-4" />
                          </div>
                          <h3 className="text-slate-900 dark:text-white font-bold text-base tracking-tight">
                            Campfire Chat
                          </h3>
                        </div>
                        {userRole !== 'CLIENT' ? (
                          <button
                            onClick={() => navigate(`/projects/${currentProject.id}/chat`)}
                            className="px-3 py-1 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 hover:bg-purple-100 font-bold text-xs transition-colors cursor-pointer border border-purple-200/60 dark:border-purple-800/60"
                          >
                            Open chat
                          </button>
                        ) : (
                          <span className="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-[11px] border border-slate-200 dark:border-slate-700">
                            🔒 Internal Only
                          </span>
                        )}
                      </div>

                      {userRole !== 'CLIENT' ? (
                        <div
                          onClick={() => navigate(`/projects/${currentProject.id}/chat`)}
                          className="relative overflow-hidden border border-slate-200/90 dark:border-slate-800 rounded-3xl p-5 bg-white dark:bg-slate-900 shadow-xs hover:shadow-xl hover:border-purple-400 dark:hover:border-purple-600 hover:-translate-y-1 transition-all duration-200 cursor-pointer min-h-[220px] max-h-[280px] overflow-y-auto flex flex-col justify-between group select-none"
                        >
                          <div className="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-purple-600 to-pink-600" />
                          <div className="space-y-3">
                            {chatMessages.length === 0 ? (
                              <div className="space-y-2">
                                <div className="flex items-start gap-2 text-xs">
                                  <div className="w-5 h-5 rounded-full bg-orange-500 text-white font-bold text-[8px] flex items-center justify-center shrink-0">
                                    E
                                  </div>
                                  <div className="bg-slate-50 dark:bg-slate-800 p-2 rounded-xl rounded-tl-none text-[11px] text-slate-700 dark:text-slate-300">
                                    Hey team, how is the inspection checklist looking?
                                  </div>
                                </div>
                                <div className="flex items-start gap-2 text-xs">
                                  <div className="w-5 h-5 rounded-full bg-purple-600 text-white font-bold text-[8px] flex items-center justify-center shrink-0">
                                    {currentUserInitials}
                                  </div>
                                  <div className="bg-slate-50 dark:bg-slate-800 p-2 rounded-xl rounded-tl-none text-[11px] text-slate-700 dark:text-slate-300">
                                    All checklist items verified. Ready for staging deployment!
                                  </div>
                                </div>
                              </div>
                            ) : (
                              chatMessages.slice(-3).map((m) => (
                                <div key={m.id} className="flex items-start gap-2 text-xs">
                                  <div className="w-5 h-5 rounded-full bg-indigo-500 text-white font-bold text-[8px] flex items-center justify-center shrink-0">
                                    {getInitials(m.sender?.full_name)}
                                  </div>
                                  <div className="bg-slate-50 dark:bg-slate-800 p-2 rounded-xl rounded-tl-none text-[11px] text-slate-700 dark:text-slate-300 truncate">
                                    {m.content}
                                  </div>
                                </div>
                              ))
                            )}
                          </div>

                          <div className="pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 italic">
                            Chat with the group or leave a note...
                          </div>
                        </div>
                      ) : (
                        <div
                          onClick={() => addToast('Campfire is private to the internal team. Clients do not have access.', 'info')}
                          className="relative overflow-hidden border border-slate-200/90 dark:border-slate-800 rounded-3xl p-6 bg-slate-50/70 dark:bg-slate-850/50 shadow-xs min-h-[220px] max-h-[280px] flex flex-col items-center justify-center text-center space-y-2 select-none cursor-pointer"
                        >
                          <div className="w-10 h-10 rounded-2xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <MessageCircle className="w-5 h-5" />
                          </div>
                          <span className="font-bold text-sm text-slate-800 dark:text-slate-200">
                            Team-Only Campfire
                          </span>
                          <p className="text-xs text-slate-500 dark:text-slate-400 max-w-[220px] leading-relaxed">
                            Campfire is private to the internal team. Client partners communicate via the Message Board and To-dos.
                          </p>
                        </div>
                      )}
                    </div>
                  )}

                  {/* TOOL 5: Schedule (Calendar) */}
                  {currentTools.schedule && (
                    <div className="flex flex-col space-y-2">
                      <div className="flex items-center justify-between pb-1">
                        <div className="flex items-center gap-2">
                          <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 text-white flex items-center justify-center shadow-xs">
                            <CalendarIcon className="w-4 h-4" />
                          </div>
                          <h3 className="text-slate-900 dark:text-white font-bold text-base tracking-tight">
                            Schedule & Deadlines
                          </h3>
                        </div>
                        <button
                          onClick={() => navigate(`/projects/${currentProject.id}/calendar`)}
                          className="px-3 py-1 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 hover:bg-cyan-100 font-bold text-xs transition-colors cursor-pointer border border-cyan-200/60 dark:border-cyan-800/60"
                        >
                          View calendar
                        </button>
                      </div>

                      <div
                        onClick={() => navigate(`/projects/${currentProject.id}/calendar`)}
                        className="relative overflow-hidden border border-slate-200/90 dark:border-slate-800 rounded-3xl p-5 bg-white dark:bg-slate-900 shadow-xs hover:shadow-xl hover:border-cyan-400 dark:hover:border-cyan-600 hover:-translate-y-1 transition-all duration-200 cursor-pointer min-h-[220px] max-h-[280px] overflow-y-auto space-y-2.5 custom-scrollbar group select-none"
                      >
                        <div className="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-cyan-500 to-blue-600" />
                        {events.length === 0 ? (
                          <div className="space-y-2">
                            <div className="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
                              <div>
                                <p className="font-bold text-xs text-slate-900 dark:text-slate-100">
                                  Ride My Cars Client Demo
                                </p>
                                <p className="text-[10px] text-slate-400">Friday • 3:00 PM</p>
                              </div>
                              <span className="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950 text-emerald-600">
                                Demo
                              </span>
                            </div>
                            <div className="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
                              <div>
                                <p className="font-bold text-xs text-slate-900 dark:text-slate-100">
                                  Sprint 4 Code Freeze
                                </p>
                                <p className="text-[10px] text-slate-400">Next Monday • 6:00 PM</p>
                              </div>
                              <span className="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950 text-amber-600">
                                Release
                              </span>
                            </div>
                          </div>
                        ) : (
                          events.map((evt) => (
                            <div
                              key={evt.id}
                              className="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between"
                            >
                              <div>
                                <p className="font-bold text-xs text-slate-900 dark:text-slate-100 truncate">
                                  {evt.title}
                                </p>
                                <p className="text-[10px] text-slate-400">
                                  {new Date(evt.start_at).toLocaleDateString([], {
                                    weekday: 'short',
                                    month: 'short',
                                    day: 'numeric',
                                  })}
                                </p>
                              </div>
                              <CalendarIcon className="w-3.5 h-3.5 text-blue-500" />
                            </div>
                          ))
                        )}
                      </div>
                    </div>
                  )}

                  {/* TOOL 6: Automatic Check-ins */}
                  {currentTools.checkins && (
                    <div className="flex flex-col space-y-2">
                      <div className="flex items-center justify-between pb-1">
                        <div className="flex items-center gap-2">
                          <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-rose-500 to-red-600 text-white flex items-center justify-center shadow-xs">
                            <HelpCircle className="w-4 h-4" />
                          </div>
                          <h3 className="text-slate-900 dark:text-white font-bold text-base tracking-tight">
                            Automatic Check-ins
                          </h3>
                        </div>
                        <button
                          onClick={() => navigate(`/projects/${currentProject.id}/checkins`)}
                          className="px-3 py-1 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 hover:bg-rose-100 font-bold text-xs transition-colors cursor-pointer border border-rose-200/60 dark:border-rose-800/60"
                        >
                          View answers
                        </button>
                      </div>

                      <div
                        onClick={() => navigate(`/projects/${currentProject.id}/checkins`)}
                        className="relative overflow-hidden border border-slate-200/90 dark:border-slate-800 rounded-3xl p-5 bg-white dark:bg-slate-900 shadow-xs hover:shadow-xl hover:border-rose-400 dark:hover:border-rose-600 hover:-translate-y-1 transition-all duration-200 cursor-pointer min-h-[220px] max-h-[280px] overflow-y-auto space-y-3 custom-scrollbar group select-none flex flex-col justify-between"
                      >
                        <div className="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-rose-500 to-red-600" />
                        <div className="space-y-1.5">
                          <p className="font-bold text-xs text-slate-900 dark:text-slate-100 group-hover:text-rose-600 transition-colors">
                            {checkins[0]?.question || 'What did you work on today?'}
                          </p>
                          <p className="text-[11px] text-slate-400 leading-relaxed">
                            Asked every weekday at 5:00 PM
                          </p>
                        </div>

                        <div className="pt-2 flex items-center justify-between border-t border-slate-100 dark:border-slate-800">
                          <div className="flex -space-x-1.5 overflow-hidden">
                            <div className="w-5 h-5 rounded-full bg-orange-500 text-white font-bold text-[8px] flex items-center justify-center ring-1 ring-white dark:ring-slate-900">
                              E
                            </div>
                            <div className="w-5 h-5 rounded-full bg-purple-600 text-white font-bold text-[8px] flex items-center justify-center ring-1 ring-white dark:ring-slate-900">
                              {currentUserInitials}
                            </div>
                          </div>
                          <span className="text-[10px] font-semibold text-slate-400">2 answers today</span>
                        </div>
                      </div>
                    </div>
                  )}

                  {/* TOOL 7: Card Table (Kanban) */}
                  {(currentTools.card_table || currentTools.kanban) && (
                    <div className="flex flex-col space-y-2">
                      <div className="flex items-center justify-between pb-1">
                        <div className="flex items-center gap-2">
                          <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center shadow-xs">
                            <Columns className="w-4 h-4" />
                          </div>
                          <h3 className="text-slate-900 dark:text-white font-bold text-base tracking-tight">
                            Card Table
                          </h3>
                        </div>
                        <button
                          onClick={() => navigate(`/projects/${currentProject.id}/board`)}
                          className="px-3 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 font-bold text-xs transition-colors cursor-pointer border border-indigo-200/60 dark:border-indigo-800/60"
                        >
                          Open Board
                        </button>
                      </div>

                      <div
                        onClick={() => navigate(`/projects/${currentProject.id}/board`)}
                        className="relative overflow-hidden border border-slate-200/90 dark:border-slate-800 rounded-3xl p-5 bg-white dark:bg-slate-900 shadow-xs hover:shadow-xl hover:border-indigo-400 dark:hover:border-indigo-600 hover:-translate-y-1 transition-all duration-200 cursor-pointer min-h-[220px] max-h-[280px] overflow-y-auto space-y-3 custom-scrollbar group select-none flex flex-col justify-between"
                      >
                        <div className="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-indigo-600 to-purple-600" />
                        <div className="space-y-2">
                          <div className="flex items-center gap-2">
                            <div className="flex-1 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                              <span className="text-[10px] font-bold uppercase text-slate-400 block">Triage</span>
                              <span className="text-xs font-extrabold text-slate-800 dark:text-slate-200">2 cards</span>
                            </div>
                            <div className="flex-1 p-2 rounded-xl bg-blue-50/60 dark:blue-950/30 border border-blue-100 dark:border-blue-900/40">
                              <span className="text-[10px] font-bold uppercase text-blue-500 block">In Progress</span>
                              <span className="text-xs font-extrabold text-blue-900 dark:text-blue-300">4 cards</span>
                            </div>
                            <div className="flex-1 p-2 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40">
                              <span className="text-[10px] font-bold uppercase text-emerald-500 block">Done</span>
                              <span className="text-xs font-extrabold text-emerald-900 dark:text-emerald-300">9 cards</span>
                            </div>
                          </div>

                          <div className="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 text-xs">
                            <span className="font-semibold text-slate-800 dark:text-slate-200">Production Hotfix #412</span>
                            <div className="flex items-center justify-between text-[10px] text-slate-400 pt-1">
                              <span>Assigned to CW</span>
                              <span className="text-indigo-600 font-bold">Review</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  )}

                  {/* TOOL 8: Doors (External Links) */}
                  {currentTools.doors && (
                    <div className="flex flex-col space-y-2">
                      <div className="flex items-center justify-between">
                        <h3 className="text-slate-900 dark:text-slate-100 font-extrabold text-[17px] tracking-tight">
                          Doors
                        </h3>
                        <span className="text-[11px] font-bold text-slate-400">External Links</span>
                      </div>

                      <div
                        className="border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 bg-white dark:bg-slate-900 shadow-xs hover:border-slate-300 dark:hover:border-slate-700 transition-all min-h-[220px] max-h-[280px] overflow-y-auto space-y-2.5 custom-scrollbar group select-none flex flex-col justify-between"
                      >
                        <div className="space-y-2">
                          <a
                            href="https://figma.com"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/60 flex items-center justify-between text-xs transition-colors group/link"
                          >
                            <div className="flex items-center gap-2">
                              <span className="w-5 h-5 rounded-lg bg-[#a259ff]/15 text-[#a259ff] flex items-center justify-center font-bold text-[10px]">
                                F
                              </span>
                              <span className="font-bold text-slate-800 dark:text-slate-200 group-hover/link:text-blue-600 transition-colors">
                                Figma UI Prototypes
                              </span>
                            </div>
                            <ExternalLink className="w-3.5 h-3.5 text-slate-400" />
                          </a>

                          <a
                            href="https://github.com"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/60 flex items-center justify-between text-xs transition-colors group/link"
                          >
                            <div className="flex items-center gap-2">
                              <span className="w-5 h-5 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-[10px]">
                                GH
                              </span>
                              <span className="font-bold text-slate-800 dark:text-slate-200 group-hover/link:text-blue-600 transition-colors">
                                GitHub Source Repository
                              </span>
                            </div>
                            <ExternalLink className="w-3.5 h-3.5 text-slate-400" />
                          </a>

                          <a
                            href="https://drive.google.com"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/60 flex items-center justify-between text-xs transition-colors group/link"
                          >
                            <div className="flex items-center gap-2">
                              <span className="w-5 h-5 rounded-lg bg-emerald-500/15 text-emerald-600 flex items-center justify-center font-bold text-[10px]">
                                GD
                              </span>
                              <span className="font-bold text-slate-800 dark:text-slate-200 group-hover/link:text-blue-600 transition-colors">
                                Shared Drive Assets Folder
                              </span>
                            </div>
                            <ExternalLink className="w-3.5 h-3.5 text-slate-400" />
                          </a>
                        </div>

                        <div className="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center gap-1">
                          <span>Direct access to external assets & tooling</span>
                        </div>
                      </div>
                    </div>
                  )}

                  {/* Add more tools card if unadded tools remain (Owner & Admin only) */}
                  {unaddedTools.length > 0 && ['OWNER', 'ADMIN'].includes(userRole) && (
                    <div className="flex flex-col space-y-2">
                      <div className="flex items-center justify-between">
                        <h3 className="text-slate-500 dark:text-slate-400 font-bold text-[15px] tracking-tight">
                          Set up tools
                        </h3>
                      </div>
                      <div
                        onClick={() => setIsCustomizeToolsOpen(true)}
                        className="border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl p-6 bg-slate-50/40 dark:bg-slate-900/40 hover:bg-slate-100/60 dark:hover:bg-slate-800/60 transition-all cursor-pointer min-h-[220px] flex flex-col items-center justify-center text-center space-y-3 group select-none"
                      >
                        <div className="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center group-hover:scale-110 transition-transform">
                          <Plus className="w-5 h-5 text-slate-500 dark:text-slate-400" />
                        </div>
                        <div>
                          <span className="text-sm font-bold text-slate-800 dark:text-slate-200">
                            Add another tool
                          </span>
                          <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                            {unaddedTools.length} tools available to set up
                          </p>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              </>
            )}
          </>
        )}
      </div>

      {/* Action Modals from ••• Menu matching Basecamp */}
      <CustomizeToolsModal
        isOpen={isCustomizeToolsOpen}
        onClose={() => setIsCustomizeToolsOpen(false)}
        projectId={currentProject.id}
        projectName={currentProject.name}
        onSettingsUpdated={(updated) => {
          setCurrentTools(updated);
          onToolsUpdated?.(updated);
        }}
      />

      <ProjectTrashModal
        isOpen={isTrashOpen}
        onClose={() => setIsTrashOpen(false)}
        projectId={currentProject.id}
        projectName={currentProject.name}
      />

      <ProjectWebhooksModal
        isOpen={isWebhooksOpen}
        onClose={() => setIsWebhooksOpen(false)}
        projectId={currentProject.id}
        projectName={currentProject.name}
      />

      <ArchiveOrDeleteModal
        isOpen={isArchiveDeleteOpen}
        onClose={() => setIsArchiveDeleteOpen(false)}
        projectId={currentProject.id}
        projectName={currentProject.name}
      />

      <LeaveProjectModal
        isOpen={isLeaveProjectOpen}
        onClose={() => setIsLeaveProjectOpen(false)}
        projectId={currentProject.id}
        projectName={currentProject.name}
      />
    </div>
  );
}
