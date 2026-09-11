import React, { useState, useEffect, useRef, useMemo } from 'react';
import {
  X,
  Check,
  Bookmark,
  Calendar as CalendarIcon,
  Image as ImageIcon,
  Paperclip,
  Bold,
  Italic,
  Strikethrough,
  Highlighter,
  Link2,
  Quote,
  Code,
  List,
  ListOrdered,
  Table as TableIcon,
  Minus,
  Undo,
  Redo,
  ChevronDown,
  Megaphone,
  CheckSquare,
  Search,
  ExternalLink,
  Trash2,
  Star,
  TrendingUp,
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { Task, CalendarEvent, ActivityLogItem } from '../../types';
import {
  getUserTasks,
  getUserAssignedByTasks,
  completeTask,
  reopenTask,
} from '../../services/taskService';
import { getOrganizationActivityLogs } from '../../services/organizationService';
import { getCalendarEvents } from '../../services/calendarService';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useProject } from '../../context/ProjectContext';
import { getInitials } from '../../lib/utils';

export type DockDrawerType =
  | 'tasks'
  | 'events'
  | 'bookmarks'
  | 'activity'
  | 'notes'
  | 'drafts'
  | 'boosts'
  | null;

interface DockDrawersProps {
  activeDrawer: DockDrawerType;
  onClose: () => void;
}

interface BookmarkItem {
  id: string;
  key: string;
  type: 'project' | 'message_board' | 'todos' | 'docs' | 'chat';
  title: string;
  subtitle: string;
  link: string;
}

// Default initial Basecamp activity matching user's screenshot
const BASECAMP_DEFAULT_ACTIVITIES = [
  {
    id: 'act-1',
    dateGroup: 'TODAY, TUESDAY, SEPTEMBER 8',
    time: '10:31am',
    projectId: 'proj-hq',
    project: 'Ajath Infotech Pvt Ltd HQ',
    author: 'Claire Watson',
    action: 'posted a message:',
    title: 'MOM 08/09/2026',
    link: '/projects/proj-hq/discussions/disc-1',
    snippet:
      'Hi All, Please find below the Minutes of the Meeting (MOM) discussion on Date: 08/09/2026 Time: 10:10 AM To 10:30 AM Attendees: Shachish, Gaurav. Agenda: Discuss on Todays Work Meeting Summary: Complete the Ajath PMT project features according to specifications.',
  },
  {
    id: 'act-2',
    dateGroup: 'YESTERDAY, MONDAY, SEPTEMBER 7',
    time: '7:02pm',
    projectId: 'proj-hq',
    project: 'Ajath Infotech Pvt Ltd HQ',
    author: 'Claire Watson',
    action: 'posted a message:',
    title: 'Worklog 07/09/2026',
    link: '/projects/proj-hq/discussions/disc-4',
    snippet:
      'Today I worked on Ajath PMT project with all functionality according to the reports and UI of the project ready for production.',
  },
  {
    id: 'act-3',
    dateGroup: 'YESTERDAY, MONDAY, SEPTEMBER 7',
    time: '10:34am',
    projectId: 'proj-hq',
    project: 'Ajath Infotech Pvt Ltd HQ',
    author: 'Claire Watson',
    action: 'posted a message:',
    title: 'MOM 07/09/2026',
    link: '/projects/proj-hq/discussions/disc-3',
    snippet:
      'Attendees: Shachish, Gaurav. Agenda: Project review and Ajath PMT milestones for production delivery.',
  },
];

export function DockDrawers({ activeDrawer, onClose }: DockDrawersProps) {
  const navigate = useNavigate();
  const { profile } = useAuth();
  const { currentOrganization } = useOrganization();
  const { projects } = useProject();

  // --------------------------------------------------------------------------
  // Tasks Drawer State
  // --------------------------------------------------------------------------
  const [tasks, setTasks] = useState<Task[]>([]);
  const [tasksTab, setTasksTab] = useState<'mine' | 'assigned'>('mine');
  const [emailingNoticeDismissed, setEmailingNoticeDismissed] = useState<boolean>(() => {
    try {
      return localStorage.getItem('basecamp_tasks_email_dismissed') === 'true';
    } catch {
      return false;
    }
  });
  const [tasksFilter, setTasksFilter] = useState('');

  // --------------------------------------------------------------------------
  // Events Drawer State
  // --------------------------------------------------------------------------
  const [events, setEvents] = useState<CalendarEvent[]>([]);
  const [eventsFilter, setEventsFilter] = useState('');

  // --------------------------------------------------------------------------
  // Bookmarks Drawer State
  // --------------------------------------------------------------------------
  const [bookmarks, setBookmarks] = useState<BookmarkItem[]>([]);

  // --------------------------------------------------------------------------
  // Activity Drawer State
  // --------------------------------------------------------------------------
  const [activityFilter, setActivityFilter] = useState('');
  const [dynamicActivities, setDynamicActivities] = useState<any[]>([]);

  const activityItems = useMemo(() => {
    const isDemoOrg = !currentOrganization?.id || currentOrganization.id === 'demo-org-acme';
    const isDemoOwner = profile?.id === 'demo-user-owner';

    // Fallback ONLY for the demo account inside demo-org-acme when no real logs exist yet
    if (isDemoOrg && isDemoOwner && dynamicActivities.length === 0) {
      return BASECAMP_DEFAULT_ACTIVITIES;
    }

    // For all newly created companies or fresh accounts: strictly dynamic activities
    // If none exist, returns [] (0 activities)
    if (!dynamicActivities || dynamicActivities.length === 0) {
      return [];
    }

    return dynamicActivities
      .filter((l) => {
        // If it's My Activity, filter by current user id/email or show their activity
        if (profile?.id && l.user_id && currentOrganization?.id !== 'demo-org-acme') {
          return l.user_id === profile.id;
        }
        return true;
      })
      .map((l) => {
        const d = new Date(l.created_at);
        const now = new Date();
        const isToday = d.toDateString() === now.toDateString();
        const yesterday = new Date(now);
        yesterday.setDate(now.getDate() - 1);
        const isYesterday = d.toDateString() === yesterday.toDateString();

        const dateGroup = isToday
          ? `TODAY, ${d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }).toUpperCase()}`
          : isYesterday
          ? `YESTERDAY, ${d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }).toUpperCase()}`
          : d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }).toUpperCase();

        const time = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }).toLowerCase();
        const proj = l.project_id ? projects.find((p) => p.id === l.project_id) : undefined;
        const projectName = proj?.name || currentOrganization?.name || 'Workspace';
        const authorName = l.user?.full_name || l.profile?.full_name || (l.user_id === profile?.id ? profile?.full_name : 'Team member') || 'Team member';

        return {
          id: l.id,
          dateGroup,
          time,
          projectId: l.project_id || '',
          project: projectName,
          author: authorName,
          action: l.action.startsWith('posted') || l.action.startsWith('created') || l.action.startsWith('updated') || l.action.startsWith('verified') ? l.action : `performed ${l.action}`,
          title: l.item_title || '',
          link: l.project_id ? `/projects/${l.project_id}` : '/dashboard',
          snippet: l.item_title ? `${l.action} "${l.item_title}" in ${projectName}` : `${l.action} in ${projectName}`,
        };
      });
  }, [currentOrganization?.id, currentOrganization?.name, dynamicActivities, profile?.id, profile?.full_name, projects]);

  const filteredActivityItems = useMemo(() => {
    if (!activityFilter.trim()) return activityItems;
    const q = activityFilter.toLowerCase();
    return activityItems.filter(
      (a) =>
        a.snippet.toLowerCase().includes(q) ||
        a.title.toLowerCase().includes(q) ||
        a.project.toLowerCase().includes(q) ||
        a.author.toLowerCase().includes(q)
    );
  }, [activityItems, activityFilter]);

  const uniqueDateGroups = useMemo(() => {
    const set = new Set<string>();
    filteredActivityItems.forEach((a) => set.add(a.dateGroup));
    return Array.from(set);
  }, [filteredActivityItems]);

  // --------------------------------------------------------------------------
  // Notes WYSIWYG Editor State
  // --------------------------------------------------------------------------
  const notesEditorRef = useRef<HTMLDivElement>(null);
  const [notesLoaded, setNotesLoaded] = useState(false);
  const savedRangeRef = useRef<Range | null>(null);
  const [activeFormats, setActiveFormats] = useState<{
    bold: boolean;
    italic: boolean;
    strikeThrough: boolean;
    h3: boolean;
    quote: boolean;
    code: boolean;
    unorderedList: boolean;
    orderedList: boolean;
  }>({
    bold: false,
    italic: false,
    strikeThrough: false,
    h3: false,
    quote: false,
    code: false,
    unorderedList: false,
    orderedList: false,
  });

  // --------------------------------------------------------------------------
  // Data Loaders
  // --------------------------------------------------------------------------
  const loadTasks = async () => {
    const userId = profile?.id || 'demo-user-owner';
    const projIds = projects.map((p) => p.id);
    try {
      if (tasksTab === 'mine') {
        const list = await getUserTasks(userId, currentOrganization?.id, projIds);
        setTasks(list);
      } else {
        const list = await getUserAssignedByTasks(userId, currentOrganization?.id, projIds);
        setTasks(list);
      }
    } catch (e) {
      console.error('Failed to load tasks', e);
    }
  };

  const loadBookmarks = () => {
    const collected: BookmarkItem[] = [];

    // 1. Starred projects
    try {
      const savedProjects = localStorage.getItem('basecamp_starred_projects');
      if (savedProjects) {
        const starredIds = new Set<string>(JSON.parse(savedProjects));
        projects
          .filter((p) => starredIds.has(p.id))
          .forEach((p) => {
            collected.push({
              id: `starred-${p.id}`,
              key: `project_${p.id}`,
              type: 'project',
              title: p.name,
              subtitle: p.description || 'Project workspace',
              link: `/projects/${p.id}`,
            });
          });
      }
    } catch {}

    // 2. Tool-level bookmarks
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (!key) continue;

        if (key.startsWith('basecamp_bookmark_mb_')) {
          const pId = key.replace('basecamp_bookmark_mb_', '');
          const p = projects.find((x) => x.id === pId);
          if (localStorage.getItem(key) === 'true') {
            collected.push({
              id: key,
              key,
              type: 'message_board',
              title: `${p?.name || 'Project'} — Message Board`,
              subtitle: 'Discussions & announcements',
              link: `/projects/${pId}/discussions`,
            });
          }
        } else if (key.startsWith('basecamp_bookmark_todos_')) {
          const pId = key.replace('basecamp_bookmark_todos_', '');
          const p = projects.find((x) => x.id === pId);
          if (localStorage.getItem(key) === 'true') {
            collected.push({
              id: key,
              key,
              type: 'todos',
              title: `${p?.name || 'Project'} — To-dos`,
              subtitle: 'Lists, task tracking, and assignments',
              link: `/projects/${pId}/todos`,
            });
          }
        } else if (key.startsWith('basecamp_bookmark_docs_')) {
          const pId = key.replace('basecamp_bookmark_docs_', '');
          const p = projects.find((x) => x.id === pId);
          if (localStorage.getItem(key) === 'true') {
            collected.push({
              id: key,
              key,
              type: 'docs',
              title: `${p?.name || 'Project'} — Docs & Files`,
              subtitle: 'File vaults, folders, and documents',
              link: `/projects/${pId}/docs`,
            });
          }
        } else if (key.startsWith('basecamp_bookmark_chat_')) {
          const pId = key.replace('basecamp_bookmark_chat_', '');
          const p = projects.find((x) => x.id === pId);
          if (localStorage.getItem(key) === 'true') {
            collected.push({
              id: key,
              key,
              type: 'chat',
              title: `${p?.name || 'Project'} — Campfire`,
              subtitle: 'Casual real-time team chat',
              link: `/projects/${pId}/chat`,
            });
          }
        }
      }
    } catch {}

    setBookmarks(collected);
  };

  useEffect(() => {
    if (activeDrawer === 'tasks') {
      loadTasks();
    } else if (activeDrawer === 'events') {
      Promise.all(projects.map((p) => getCalendarEvents(p.id).catch(() => [])))
        .then((results) => {
          const all = results.flat();
          // Fallback events only if demo org and empty
          if (all.length === 0 && (!currentOrganization?.id || currentOrganization.id === 'demo-org-acme')) {
            all.push(
              {
                id: 'evt-1',
                project_id: 'proj-hq',
                title: 'Sprint Planning & Retrospective',
                description: 'Weekly team alignment meeting in HQ conference room.',
                start_at: '2026-09-10T10:00:00.000Z',
                end_at: '2026-09-10T11:00:00.000Z',
                all_day: false,
                color: '#3b82f6',
                created_at: new Date().toISOString(),
              },
              {
                id: 'evt-2',
                project_id: 'proj-rmc',
                title: 'Client Demo: Ride My Cars (Edward)',
                description: 'Review vehicle intake and inspection mobile flow with client.',
                start_at: '2026-09-12T14:30:00.000Z',
                end_at: '2026-09-12T15:30:00.000Z',
                all_day: false,
                color: '#10b981',
                created_at: new Date().toISOString(),
              },
              {
                id: 'evt-3',
                project_id: 'proj-bipl',
                title: 'BIPL App Staging Release v1.2',
                description: 'Final regression test pass before client sign-off.',
                start_at: '2026-09-14T09:00:00.000Z',
                end_at: '2026-09-14T18:00:00.000Z',
                all_day: true,
                color: '#f59e0b',
                created_at: new Date().toISOString(),
              }
            );
          }
          all.sort((a, b) => new Date(a.start_at).getTime() - new Date(b.start_at).getTime());
          setEvents(all);
        })
        .catch(() => {});
    } else if (activeDrawer === 'bookmarks') {
      loadBookmarks();
    } else if (activeDrawer === 'activity') {
      getOrganizationActivityLogs(currentOrganization?.id || 'demo-org-acme')
        .then((logs) => setDynamicActivities(logs))
        .catch(() => {});
    }
  }, [activeDrawer, tasksTab, profile?.id, projects, currentOrganization?.id]);

  // Load notes content into editor when opening notes drawer
  useEffect(() => {
    if (activeDrawer === 'notes' && notesEditorRef.current && !notesLoaded) {
      const saved = localStorage.getItem('basecamp_personal_notes') || '';
      // Parse markdown to HTML if legacy text contains asterisks or tildes
      let initialHtml = saved;
      if (
        saved.includes('**') ||
        saved.includes('~~') ||
        saved.startsWith('#') ||
        saved.includes('\n-')
      ) {
        initialHtml = saved
          .replace(/\*\*\*\*\*(.*?)\*\*\*\*\*/g, '<b><i>$1</i></b>')
          .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
          .replace(/\*(.*?)\*/g, '<i>$1</i>')
          .replace(/~~(.*?)~~/g, '<s>$1</s>')
          .replace(/==(.*?)==/g, '<mark style="background:#fde047; padding:1px 4px; border-radius:3px;">$1</mark>')
          .replace(/^### (.*$)/gim, '<h3>$1</h3>')
          .replace(/^\- (.*$)/gim, '<ul><li>$1</li></ul>')
          .replace(/\n/g, '<br>');
      }
      notesEditorRef.current.innerHTML = initialHtml;
      setNotesLoaded(true);
    }
    if (activeDrawer !== 'notes') {
      setNotesLoaded(false);
    }
  }, [activeDrawer, notesLoaded]);

  // --------------------------------------------------------------------------
  // Task Handlers
  // --------------------------------------------------------------------------
  const handleToggleTask = async (task: Task) => {
    if (task.status === 'COMPLETED') {
      await reopenTask(task.id);
    } else {
      await completeTask(task.id);
    }
    await loadTasks();
  };

  const handleDismissEmailNotice = () => {
    setEmailingNoticeDismissed(true);
    try {
      localStorage.setItem('basecamp_tasks_email_dismissed', 'true');
    } catch {}
  };

  // --------------------------------------------------------------------------
  // Bookmark Handlers
  // --------------------------------------------------------------------------
  const handleRemoveBookmark = (item: BookmarkItem) => {
    if (item.key.startsWith('project_')) {
      const pId = item.key.replace('project_', '');
      try {
        const savedProjects = localStorage.getItem('basecamp_starred_projects');
        if (savedProjects) {
          const starredIds = new Set<string>(JSON.parse(savedProjects));
          starredIds.delete(pId);
          localStorage.setItem('basecamp_starred_projects', JSON.stringify(Array.from(starredIds)));
        }
      } catch {}
    } else {
      try {
        localStorage.removeItem(item.key);
      } catch {}
    }
    loadBookmarks();
  };

  const handleAddSampleBookmark = (projectId: string) => {
    try {
      const savedProjects = localStorage.getItem('basecamp_starred_projects');
      const starredIds = savedProjects ? new Set<string>(JSON.parse(savedProjects)) : new Set<string>();
      starredIds.add(projectId);
      localStorage.setItem('basecamp_starred_projects', JSON.stringify(Array.from(starredIds)));
      loadBookmarks();
    } catch {}
  };

  // --------------------------------------------------------------------------
  // Notes WYSIWYG Selection Preservation & Format Handlers
  // --------------------------------------------------------------------------
  const saveSelection = () => {
    try {
      const sel = window.getSelection();
      if (sel && sel.rangeCount > 0 && notesEditorRef.current) {
        const range = sel.getRangeAt(0);
        if (
          notesEditorRef.current.contains(range.commonAncestorContainer) ||
          notesEditorRef.current === range.commonAncestorContainer
        ) {
          savedRangeRef.current = range.cloneRange();
        }
      }
    } catch (e) {
      console.warn('saveSelection error:', e);
    }
  };

  const restoreSelection = () => {
    try {
      const editor = notesEditorRef.current;
      if (!editor) return;

      if (savedRangeRef.current) {
        const sel = window.getSelection();
        if (sel) {
          sel.removeAllRanges();
          sel.addRange(savedRangeRef.current);
        }
      } else {
        editor.focus();
      }
    } catch (e) {
      console.warn('restoreSelection error:', e);
    }
  };

  const updateActiveFormats = () => {
    saveSelection();
    try {
      const block = (document.queryCommandValue('formatBlock') || '').toLowerCase();
      setActiveFormats({
        bold: document.queryCommandState('bold'),
        italic: document.queryCommandState('italic'),
        strikeThrough: document.queryCommandState('strikeThrough'),
        h3: block.includes('h3'),
        quote: block.includes('blockquote'),
        code: block.includes('pre'),
        unorderedList: document.queryCommandState('insertUnorderedList'),
        orderedList: document.queryCommandState('insertOrderedList'),
      });
    } catch {}
  };

  // Keep selection and active format indicator in sync while notes drawer is open
  useEffect(() => {
    if (activeDrawer !== 'notes') return;

    const handleSelectionChange = () => {
      const sel = window.getSelection();
      if (!sel || sel.rangeCount === 0 || !notesEditorRef.current) return;
      try {
        const range = sel.getRangeAt(0);
        if (
          notesEditorRef.current.contains(range.commonAncestorContainer) ||
          notesEditorRef.current === range.commonAncestorContainer
        ) {
          savedRangeRef.current = range.cloneRange();
          const block = (document.queryCommandValue('formatBlock') || '').toLowerCase();
          setActiveFormats({
            bold: document.queryCommandState('bold'),
            italic: document.queryCommandState('italic'),
            strikeThrough: document.queryCommandState('strikeThrough'),
            h3: block.includes('h3'),
            quote: block.includes('blockquote'),
            code: block.includes('pre'),
            unorderedList: document.queryCommandState('insertUnorderedList'),
            orderedList: document.queryCommandState('insertOrderedList'),
          });
        }
      } catch {}
    };

    document.addEventListener('selectionchange', handleSelectionChange);
    return () => {
      document.removeEventListener('selectionchange', handleSelectionChange);
    };
  }, [activeDrawer]);

  const execEditorCmd = (command: string, value: string | undefined = undefined) => {
    const editor = notesEditorRef.current;
    if (!editor) return;
    restoreSelection();
    editor.focus();

    try {
      if (command === 'formatBlock') {
        const currentBlock = (document.queryCommandValue('formatBlock') || '').toLowerCase();
        const target = (value || '').replace(/[<>]/g, '').toLowerCase();
        if (currentBlock === target) {
          document.execCommand('formatBlock', false, '<p>');
        } else {
          const ok = document.execCommand('formatBlock', false, value);
          if (!ok && value) {
            document.execCommand('formatBlock', false, target);
          }
        }
      } else if (command === 'hiliteColor') {
        let ok = false;
        try {
          ok = document.execCommand('hiliteColor', false, value);
        } catch {}
        if (!ok) {
          try {
            ok = document.execCommand('backColor', false, value);
          } catch {}
        }
        if (!ok) {
          const sel = window.getSelection();
          if (sel && sel.rangeCount > 0 && !sel.isCollapsed) {
            const range = sel.getRangeAt(0);
            const mark = document.createElement('mark');
            mark.style.backgroundColor = value || '#fef08a';
            mark.appendChild(range.extractContents());
            range.insertNode(mark);
          }
        }
      } else {
        document.execCommand(command, false, value);
      }
    } catch (e) {
      console.warn('execCommand failed:', e);
    }

    saveSelection();
    handleNotesInput();
    updateActiveFormats();
  };

  const toggleHeading = () => {
    restoreSelection();
    try {
      const currentBlock = (document.queryCommandValue('formatBlock') || '').toLowerCase();
      if (currentBlock === 'h3' || currentBlock === '<h3>') {
        document.execCommand('formatBlock', false, '<p>');
      } else {
        const ok = document.execCommand('formatBlock', false, '<h3>');
        if (!ok) {
          document.execCommand('formatBlock', false, 'H3');
        }
      }
    } catch (e) {
      console.warn('toggleHeading error:', e);
    }
    saveSelection();
    handleNotesInput();
    updateActiveFormats();
  };

  const toggleQuote = () => {
    restoreSelection();
    try {
      const currentBlock = (document.queryCommandValue('formatBlock') || '').toLowerCase();
      if (currentBlock === 'blockquote' || currentBlock === '<blockquote>') {
        document.execCommand('formatBlock', false, '<p>');
      } else {
        const ok = document.execCommand('formatBlock', false, '<blockquote>');
        if (!ok) {
          document.execCommand('formatBlock', false, 'BLOCKQUOTE');
        }
      }
    } catch (e) {
      console.warn('toggleQuote error:', e);
    }
    saveSelection();
    handleNotesInput();
    updateActiveFormats();
  };

  const toggleCode = () => {
    restoreSelection();
    const sel = window.getSelection();
    if (sel && sel.rangeCount > 0 && !sel.isCollapsed && sel.toString().length > 0) {
      const range = sel.getRangeAt(0);
      const parent = range.commonAncestorContainer.parentElement;
      if (parent && parent.tagName === 'CODE') {
        const textNode = document.createTextNode(parent.textContent || '');
        parent.parentNode?.replaceChild(textNode, parent);
      } else {
        const codeEl = document.createElement('code');
        codeEl.className = 'notes-inline-code';
        codeEl.appendChild(range.extractContents());
        range.insertNode(codeEl);
      }
    } else {
      const currentBlock = (document.queryCommandValue('formatBlock') || '').toLowerCase();
      if (currentBlock === 'pre' || currentBlock === '<pre>') {
        document.execCommand('formatBlock', false, '<p>');
      } else {
        document.execCommand('formatBlock', false, '<pre>');
      }
    }
    saveSelection();
    handleNotesInput();
    updateActiveFormats();
  };

  const toggleHighlight = () => {
    restoreSelection();
    const sel = window.getSelection();
    if (!sel || sel.rangeCount === 0 || sel.isCollapsed) {
      try {
        document.execCommand('hiliteColor', false, '#fef08a');
      } catch {
        document.execCommand('backColor', false, '#fef08a');
      }
      return;
    }
    const range = sel.getRangeAt(0);
    const parent = range.commonAncestorContainer.parentElement;
    if (parent && (parent.tagName === 'MARK' || (parent.style && parent.style.backgroundColor))) {
      try {
        document.execCommand('hiliteColor', false, 'transparent');
      } catch {
        document.execCommand('backColor', false, 'transparent');
      }
    } else {
      let ok = false;
      try {
        ok = document.execCommand('hiliteColor', false, '#fef08a');
      } catch {}
      if (!ok) {
        try {
          ok = document.execCommand('backColor', false, '#fef08a');
        } catch {}
      }
      if (!ok) {
        const mark = document.createElement('mark');
        mark.style.backgroundColor = '#fef08a';
        mark.appendChild(range.extractContents());
        range.insertNode(mark);
      }
    }
    saveSelection();
    handleNotesInput();
    updateActiveFormats();
  };

  const handleNotesInput = () => {
    if (!notesEditorRef.current) return;
    const html = notesEditorRef.current.innerHTML;
    try {
      localStorage.setItem('basecamp_personal_notes', html);
    } catch {}
  };

  const handleInsertLink = () => {
    saveSelection();
    const url = prompt('Enter website link URL (e.g. https://example.com):', 'https://');
    if (url && url.trim() && url !== 'https://') {
      const cleanUrl = url.trim().startsWith('http://') || url.trim().startsWith('https://') || url.trim().startsWith('mailto:')
        ? url.trim()
        : `https://${url.trim()}`;
      restoreSelection();
      const sel = window.getSelection();
      if (!sel || sel.isCollapsed || !sel.toString().trim()) {
        execEditorCmd('insertHTML', `<a href="${cleanUrl}" target="_blank" rel="noopener noreferrer">${cleanUrl}</a>&nbsp;`);
      } else {
        execEditorCmd('createLink', cleanUrl);
      }
    }
  };

  const handleInsertImage = () => {
    saveSelection();
    const url = prompt(
      'Enter public image URL:',
      'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&auto=format&fit=crop&q=80'
    );
    if (url && url.trim()) {
      restoreSelection();
      const imgHtml = `<img src="${url.trim()}" alt="Note attachment" style="max-width:100%; border-radius:8px; margin:8px 0; display:block;" /><p><br></p>`;
      execEditorCmd('insertHTML', imgHtml);
    }
  };

  const handleInsertAttachment = () => {
    saveSelection();
    const name = prompt('Enter attachment label:', 'Project Scope Brief.pdf');
    if (name && name.trim()) {
      restoreSelection();
      const pill = `<span contenteditable="false" style="display:inline-flex; align-items:center; gap:6px; background:#f1f5f9; border:1px solid #cbd5e1; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:600; margin:4px 2px; user-select:all; cursor:pointer;" title="Attachment: ${name.trim()}">📎 ${name.trim()}</span>&nbsp;`;
      execEditorCmd('insertHTML', pill);
    }
  };

  const handleInsertTable = () => {
    saveSelection();
    restoreSelection();
    const tableHtml = `
      <table style="width:100%; border-collapse:collapse; margin:12px 0; font-size:13px; border:1px solid #cbd5e1;">
        <thead>
          <tr style="background:#f8fafc; border-bottom:2px solid #cbd5e1;">
            <th style="border:1px solid #cbd5e1; padding:8px 12px; text-align:left; font-weight:600;">Item</th>
            <th style="border:1px solid #cbd5e1; padding:8px 12px; text-align:left; font-weight:600;">Description</th>
            <th style="border:1px solid #cbd5e1; padding:8px 12px; text-align:left; font-weight:600;">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="border:1px solid #cbd5e1; padding:8px 12px;">Task 1</td>
            <td style="border:1px solid #cbd5e1; padding:8px 12px;">Setup initial project scope</td>
            <td style="border:1px solid #cbd5e1; padding:8px 12px; color:#16a34a; font-weight:600;">Done</td>
          </tr>
          <tr>
            <td style="border:1px solid #cbd5e1; padding:8px 12px;">Task 2</td>
            <td style="border:1px solid #cbd5e1; padding:8px 12px;">Review milestones with team</td>
            <td style="border:1px solid #cbd5e1; padding:8px 12px; color:#2563eb; font-weight:600;">In Progress</td>
          </tr>
        </tbody>
      </table>
      <p><br></p>
    `;
    execEditorCmd('insertHTML', tableHtml);
  };

  if (!activeDrawer) return null;

  // Filtered Task List
  const filteredTasks = tasks.filter((t) => {
    if (!tasksFilter.trim()) return true;
    const q = tasksFilter.toLowerCase();
    return (
      t.title.toLowerCase().includes(q) ||
      (t.description && t.description.toLowerCase().includes(q))
    );
  });

  // Filtered Events List
  const filteredEvents = events.filter((ev) => {
    if (!eventsFilter.trim()) return true;
    const q = eventsFilter.toLowerCase();
    return (
      ev.title.toLowerCase().includes(q) ||
      (ev.description && ev.description.toLowerCase().includes(q))
    );
  });

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto pt-10 pb-14 px-2 sm:px-6 flex justify-center items-start animate-in fade-in duration-150">
      {/* Dimmed backdrop */}
      <div
        className="fixed inset-0 bg-black/30 dark:bg-black/50 backdrop-blur-2xs transition-opacity"
        onClick={onClose}
      />

      {/* Main Modal Card Container matching Basecamp Screenshots */}
      <div className="relative w-full max-w-4xl bg-white dark:bg-slate-900 rounded-3xl min-h-[580px] p-6 sm:p-10 border border-slate-200/90 dark:border-slate-800 shadow-2xl z-50 text-slate-900 dark:text-slate-100 flex flex-col justify-start my-auto">
        {/* ============================================================= */}
        {/* 1. MY TASKS (Screenshot 1)                                    */}
        {/* ============================================================= */}
        {activeDrawer === 'tasks' && (
          <div className="space-y-6">
            {/* Header: Title + Email Notice + Close X */}
            <div className="flex items-center justify-between">
              <h1 className="text-3xl sm:text-[34px] font-extrabold text-slate-900 dark:text-white tracking-tight">
                My Tasks
              </h1>

              <div className="flex items-center gap-3">
                {!emailingNoticeDismissed && (
                  <div className="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1 rounded-full border border-slate-200 dark:border-slate-700">
                    <Check className="w-3.5 h-3.5 text-emerald-600 stroke-[3]" />
                    <span>Emailing tasks every Monday</span>
                    <button
                      onClick={handleDismissEmailNotice}
                      className="hover:text-slate-700 dark:hover:text-slate-200 cursor-pointer ml-1"
                      title="Dismiss notice"
                    >
                      <X className="w-3 h-3" />
                    </button>
                  </div>
                )}
                <button
                  onClick={onClose}
                  className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                  title="Close"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>
            </div>

            {/* Sub-bar: Pill tabs ('My tasks' vs 'Stuff I've assigned') + Filter */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
              <div className="flex items-center gap-2">
                <button
                  onClick={() => setTasksTab('mine')}
                  className={`px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all cursor-pointer ${
                    tasksTab === 'mine'
                      ? 'bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white shadow-2xs'
                      : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                  }`}
                >
                  My tasks
                </button>
                <button
                  onClick={() => setTasksTab('assigned')}
                  className={`px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all cursor-pointer ${
                    tasksTab === 'assigned'
                      ? 'bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white shadow-2xs'
                      : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                  }`}
                >
                  Stuff I've assigned
                </button>
              </div>

              <div>
                <input
                  type="text"
                  value={tasksFilter}
                  onChange={(e) => setTasksFilter(e.target.value)}
                  placeholder="Filter..."
                  className="w-48 text-xs px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                />
              </div>
            </div>

            {/* Tasks List matching Screenshot 1 */}
            {filteredTasks.length === 0 ? (
              <div className="border border-dashed border-amber-300 dark:border-amber-700/80 rounded-2xl p-8 bg-amber-50/30 dark:bg-amber-950/10 max-w-lg mx-auto my-12 text-center space-y-2 select-none">
                <CheckSquare className="w-8 h-8 text-amber-500 mx-auto" />
                <h4 className="font-bold text-slate-900 dark:text-white text-sm">
                  {tasksFilter ? 'No tasks match your filter' : 'No tasks in this view'}
                </h4>
                <p className="text-xs text-slate-500 dark:text-slate-400">
                  {tasksTab === 'mine'
                    ? 'All tasks assigned to you have been completed or none assigned yet.'
                    : 'Tasks you assign to team members will appear here.'}
                </p>
              </div>
            ) : (
              <div className="space-y-3 pt-2">
                {filteredTasks.map((t) => {
                  const isDone = t.status === 'COMPLETED';
                  return (
                    <div
                      key={t.id}
                      className={`p-4 sm:p-5 rounded-2xl border transition-all flex items-start gap-4 group cursor-pointer ${
                        isDone
                          ? 'border-slate-200/70 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-850/50'
                          : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-blue-400 dark:hover:border-blue-500 shadow-2xs'
                      }`}
                    >
                      {/* Checkbox */}
                      <button
                        type="button"
                        onClick={(e) => {
                          e.stopPropagation();
                          handleToggleTask(t);
                        }}
                        className={`w-5 h-5 rounded-md border-2 flex items-center justify-center shrink-0 mt-0.5 transition-colors cursor-pointer ${
                          isDone
                            ? 'bg-emerald-500 border-emerald-500 text-white'
                            : 'border-slate-300 hover:border-emerald-500 bg-white dark:bg-slate-800'
                        }`}
                      >
                        {isDone && <Check className="w-3.5 h-3.5 stroke-[3]" />}
                      </button>

                      {/* Content (Clicking navigates to Project To-Dos) */}
                      <div
                        className="flex-1 min-w-0"
                        onClick={() => {
                          onClose();
                          navigate(`/projects/${t.project_id}/todos`);
                        }}
                      >
                        <h4
                          className={`text-sm font-bold tracking-tight transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 ${
                            isDone
                              ? 'line-through text-slate-400 dark:text-slate-500'
                              : 'text-slate-900 dark:text-slate-100'
                          }`}
                        >
                          {t.title}
                        </h4>
                        {t.description && (
                          <p
                            className={`text-xs mt-1 leading-relaxed ${
                              isDone
                                ? 'text-slate-400 dark:text-slate-500 line-through'
                                : 'text-slate-500 dark:text-slate-400'
                            }`}
                          >
                            {t.description}
                          </p>
                        )}
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        )}

        {/* ============================================================= */}
        {/* 2. MY EVENTS (Screenshot 2)                                   */}
        {/* ============================================================= */}
        {activeDrawer === 'events' && (
          <div className="space-y-6">
            {/* Header: Title + Close X */}
            <div className="flex items-start justify-between">
              <h1 className="text-3xl sm:text-[34px] font-extrabold text-slate-900 dark:text-white tracking-tight">
                My Events
              </h1>
              <button
                onClick={onClose}
                className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                title="Close"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Filter Input */}
            <div>
              <input
                type="text"
                value={eventsFilter}
                onChange={(e) => setEventsFilter(e.target.value)}
                placeholder="Filter..."
                className="w-48 text-xs px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
              />
            </div>

            {/* Month Header & Calendar Block */}
            <div className="space-y-4 pt-1">
              <div className="flex items-center gap-3">
                <h3 className="font-extrabold text-slate-900 dark:text-white text-base">
                  September 2026
                </h3>
                <div className="h-px bg-slate-200 dark:bg-slate-800 flex-1" />
              </div>

              {/* Today's Row matching Screenshot 2 */}
              <div
                onClick={() => {
                  onClose();
                  navigate('/calendar');
                }}
                className="flex items-center gap-4 cursor-pointer group"
              >
                {/* Left Calendar Date Block */}
                <div className="w-14 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden text-center shadow-2xs shrink-0 select-none pb-1 group-hover:border-blue-400 transition-colors">
                  <div className="bg-[#48535a] text-white text-[10px] font-bold py-0.5 uppercase tracking-wider">
                    Sep
                  </div>
                  <div className="text-xl font-extrabold text-slate-900 dark:text-white leading-tight mt-0.5">
                    8
                  </div>
                  <div className="text-[9px] font-bold text-slate-500 uppercase tracking-tight">
                    TUE
                  </div>
                  <div className="mx-1 mt-0.5 bg-[#283238] text-white text-[8px] font-black rounded uppercase py-0.2">
                    TODAY
                  </div>
                </div>

                {/* Right Soft Yellow / Cream Highlight Banner */}
                <div className="bg-[#FAF7E8] dark:bg-amber-950/20 border border-[#F2EAC4] dark:border-amber-900/40 rounded-xl p-4 flex-1 flex items-center shadow-2xs group-hover:border-amber-400 transition-colors">
                  <span className="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    No events today
                  </span>
                </div>
              </div>

              {/* Upcoming events & deadlines matching Screenshot 2 */}
              {filteredEvents.length > 0 && (
                <div className="space-y-2.5 pt-4">
                  <p className="text-xs text-slate-500 font-medium">Upcoming events & deadlines:</p>
                  {filteredEvents.map((ev) => (
                    <div
                      key={ev.id}
                      onClick={() => {
                        onClose();
                        navigate(ev.project_id ? `/projects/${ev.project_id}/calendar` : '/calendar');
                      }}
                      className="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 hover:border-blue-400 dark:hover:border-blue-500 transition-all flex items-center justify-between cursor-pointer group"
                    >
                      <div>
                        <p className="text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                          {ev.title}
                        </p>
                        <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                          {new Date(ev.start_at).toLocaleDateString(undefined, {
                            month: 'short',
                            day: 'numeric',
                          })}
                        </p>
                      </div>
                      <span className="text-xs text-blue-600 dark:text-blue-400 font-bold group-hover:underline flex items-center gap-1">
                        <span>Calendar</span>
                        <ExternalLink className="w-3.5 h-3.5" />
                      </span>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        )}

        {/* ============================================================= */}
        {/* 3. MY BOOKMARKS (Screenshot 3)                                */}
        {/* ============================================================= */}
        {activeDrawer === 'bookmarks' && (
          <div className="space-y-6">
            {/* Header: Title + Close X */}
            <div className="flex items-start justify-between">
              <h1 className="text-3xl sm:text-[34px] font-extrabold text-slate-900 dark:text-white tracking-tight">
                My Bookmarks
              </h1>
              <button
                onClick={onClose}
                className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                title="Close"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Bookmarks List or Empty State matching Screenshot 3 */}
            {bookmarks.length === 0 ? (
              <div className="bg-[#FAF6EE] dark:bg-amber-950/20 rounded-2xl p-12 sm:p-20 flex flex-col items-center justify-center text-center mt-4 select-none space-y-4">
                <Bookmark className="w-10 h-10 text-[#d4c3a3] stroke-1" />
                <div className="space-y-1">
                  <p className="text-slate-600 dark:text-slate-300 font-bold text-sm sm:text-base">
                    You haven’t bookmarked anything yet
                  </p>
                  <p className="text-xs text-slate-400 max-w-sm">
                    Bookmark your favorite projects, message boards, or to-dos to jump back in with one click.
                  </p>
                </div>
                {/* Quick 1-Click Bookmark Action to test */}
                <button
                  type="button"
                  onClick={() => handleAddSampleBookmark('proj-rmc')}
                  className="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white dark:bg-slate-800 border border-amber-300 dark:border-amber-800 text-xs font-bold text-amber-900 dark:text-amber-200 hover:bg-amber-50 shadow-xs cursor-pointer active:scale-95 transition-all"
                >
                  <Star className="w-3.5 h-3.5 fill-amber-400 text-amber-500" />
                  <span>Bookmark "Ride My Cars (Edward)"</span>
                </button>
              </div>
            ) : (
              <div className="space-y-3 pt-2">
                <p className="text-xs text-slate-500 font-medium">Your bookmarked workspaces & tools:</p>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  {bookmarks.map((b) => (
                    <div
                      key={b.id}
                      onClick={() => {
                        onClose();
                        navigate(b.link);
                      }}
                      className="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 hover:border-blue-400 dark:hover:border-blue-500 transition-all cursor-pointer flex items-center justify-between group shadow-2xs"
                    >
                      <div className="min-w-0 flex-1 pr-3">
                        <h4 className="text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate">
                          {b.title}
                        </h4>
                        <p className="text-xs text-slate-400 truncate mt-0.5">{b.subtitle}</p>
                      </div>
                      <button
                        type="button"
                        onClick={(e) => {
                          e.stopPropagation();
                          handleRemoveBookmark(b);
                        }}
                        className="p-1 text-amber-500 hover:text-red-500 transition-colors cursor-pointer shrink-0"
                        title="Remove bookmark"
                      >
                        <Bookmark className="w-4 h-4 fill-amber-400" />
                      </button>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>
        )}

        {/* ============================================================= */}
        {/* 4. MY ACTIVITY (Screenshot 4)                                 */}
        {/* ============================================================= */}
        {activeDrawer === 'activity' && (
          <div className="space-y-6">
            {/* Header: Title + Close X */}
            <div className="flex items-start justify-between">
              <h1 className="text-3xl sm:text-[34px] font-extrabold text-slate-900 dark:text-white tracking-tight">
                My Activity
              </h1>
              <button
                onClick={onClose}
                className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                title="Close"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Filter Input matching Screenshot 4 */}
            <div>
              <input
                type="text"
                value={activityFilter}
                onChange={(e) => setActivityFilter(e.target.value)}
                placeholder="Filter..."
                className="w-48 text-xs px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
              />
            </div>

            {/* Grouped Activity Stream */}
            <div className="space-y-6 pt-1">
              {activityItems.length === 0 ? (
                <div className="p-12 text-center rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 space-y-3">
                  <div className="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <TrendingUp className="w-6 h-6" />
                  </div>
                  <h4 className="font-extrabold text-base text-slate-800 dark:text-slate-200">No activity yet</h4>
                  <p className="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    You haven't performed any activity yet in {currentOrganization?.name || 'this workspace'}. Activity will appear here as you create projects, post discussions, complete to-dos, or share files.
                  </p>
                </div>
              ) : filteredActivityItems.length === 0 ? (
                <div className="p-8 text-center text-xs text-slate-400">
                  No activity matching "{activityFilter}"
                </div>
              ) : (
                uniqueDateGroups.map((groupDate) => {
                  const groupItems = filteredActivityItems.filter((a) => a.dateGroup === groupDate);
                  if (groupItems.length === 0) return null;

                  return (
                    <div key={groupDate} className="space-y-3">
                      {/* Dark pill date header */}
                      <div className="flex items-center gap-3">
                        <span className="bg-[#283238] text-white text-[11px] font-extrabold uppercase px-2.5 py-1 rounded-md tracking-wider">
                          {groupDate}
                        </span>
                        <div className="h-px bg-slate-200 dark:bg-slate-800 flex-1" />
                      </div>

                      {/* Timeline items */}
                      <div className="space-y-5 pl-3 relative border-l-2 border-slate-100 dark:border-slate-800 ml-4">
                        {groupItems.map((item) => (
                          <div key={item.id} className="relative pl-6 space-y-1 group">
                            {/* Left dot icon */}
                            <div className="absolute -left-[17px] top-1 w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-xs">
                              <Megaphone className="w-3 h-3" />
                            </div>

                            {/* Time & Project line (CLICKABLE) */}
                            <div className="flex items-center gap-2 text-xs">
                              <span className="text-slate-400">{item.time}</span>
                              <div className="w-5 h-5 rounded-full bg-[#872ec4] text-white font-bold text-[9px] flex items-center justify-center shrink-0">
                                {getInitials(item.author)}
                              </div>
                              {item.projectId ? (
                                <button
                                  type="button"
                                  onClick={() => {
                                    onClose();
                                    navigate(`/projects/${item.projectId}`);
                                  }}
                                  className="font-bold text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:underline cursor-pointer"
                                >
                                  {item.project}
                                </button>
                              ) : (
                                <span className="font-bold text-slate-700 dark:text-slate-300">
                                  {item.project}
                                </span>
                              )}
                            </div>

                            {/* Headline with CLICKABLE Title */}
                            <div className="text-sm font-bold text-slate-900 dark:text-white">
                              <span>
                                {item.author} {item.action}{' '}
                              </span>
                              {item.title && (
                                <button
                                  type="button"
                                  onClick={() => {
                                    onClose();
                                    navigate(item.link);
                                  }}
                                  className="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer font-extrabold"
                                >
                                  {item.title}
                                </button>
                              )}
                            </div>

                            {/* Snippet preview */}
                            {item.snippet && (
                              <p className="text-xs text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl">
                                {item.snippet}
                              </p>
                            )}
                          </div>
                        ))}
                      </div>
                    </div>
                  );
                })
              )}
            </div>
          </div>
        )}

        {/* ============================================================= */}
        {/* 5. MY NOTES WYSIWYG EDITOR (Screenshot 5)                     */}
        {/* ============================================================= */}
        {activeDrawer === 'notes' && (
          <div className="space-y-4 flex-1 flex flex-col">
            {/* Header: Title + Close X */}
            <div className="flex items-start justify-between">
              <h1 className="text-3xl sm:text-[34px] font-extrabold text-slate-900 dark:text-white tracking-tight">
                My Notes
              </h1>
              <button
                onClick={onClose}
                className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                title="Close"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Interactive WYSIWYG Toolbar */}
            <div
              onMouseDown={(e) => {
                // Prevent toolbar clicks from stealing focus or dropping selection
                e.preventDefault();
              }}
              className="flex items-center justify-between border-y border-slate-200/80 dark:border-slate-800 py-2 -mx-6 sm:-mx-10 px-6 sm:px-10 text-slate-600 dark:text-slate-400 text-xs overflow-x-auto select-none gap-2"
            >
              <div className="flex items-center gap-1 sm:gap-1.5 shrink-0 flex-wrap">
                {/* Image */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={handleInsertImage}
                  className="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded text-slate-600 dark:text-slate-300 cursor-pointer transition-colors"
                  title="Insert image"
                >
                  <ImageIcon className="w-4 h-4" />
                </button>

                {/* Attachment */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={handleInsertAttachment}
                  className="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded text-slate-600 dark:text-slate-300 cursor-pointer transition-colors"
                  title="Attach file"
                >
                  <Paperclip className="w-4 h-4" />
                </button>

                <div className="h-4 w-px bg-slate-200 dark:bg-slate-700 mx-1" />

                {/* Bold */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => execEditorCmd('bold')}
                  className={`p-1.5 rounded cursor-pointer transition-colors ${
                    activeFormats.bold
                      ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 font-black'
                      : 'hover:bg-slate-100 dark:hover:bg-slate-800 font-bold text-slate-700 dark:text-slate-200'
                  }`}
                  title="Bold (Ctrl+B)"
                >
                  <Bold className="w-4 h-4" />
                </button>

                {/* Italic */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => execEditorCmd('italic')}
                  className={`p-1.5 rounded italic cursor-pointer transition-colors ${
                    activeFormats.italic
                      ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'
                      : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200'
                  }`}
                  title="Italic (Ctrl+I)"
                >
                  <Italic className="w-4 h-4" />
                </button>

                {/* Strikethrough */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => execEditorCmd('strikeThrough')}
                  className={`p-1.5 rounded cursor-pointer transition-colors ${
                    activeFormats.strikeThrough
                      ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'
                      : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200'
                  }`}
                  title="Strikethrough"
                >
                  <Strikethrough className="w-4 h-4" />
                </button>

                {/* Heading (Tt) */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={toggleHeading}
                  className={`px-1.5 py-1 rounded font-bold flex items-center gap-0.5 cursor-pointer transition-colors ${
                    activeFormats.h3
                      ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'
                      : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200'
                  }`}
                  title="Heading / Title"
                >
                  <span className="text-xs font-black">Tt</span>
                  <ChevronDown className="w-3 h-3 text-slate-400" />
                </button>

                {/* Highlighter */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={toggleHighlight}
                  className="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded text-amber-500 hover:text-amber-600 cursor-pointer transition-colors"
                  title="Highlight"
                >
                  <Highlighter className="w-4 h-4" />
                </button>

                {/* Link */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={handleInsertLink}
                  className="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded text-slate-600 dark:text-slate-300 cursor-pointer transition-colors"
                  title="Insert Link"
                >
                  <Link2 className="w-4 h-4" />
                </button>

                {/* Quote */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={toggleQuote}
                  className={`p-1.5 rounded cursor-pointer transition-colors ${
                    activeFormats.quote
                      ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'
                      : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300'
                  }`}
                  title="Quote"
                >
                  <Quote className="w-4 h-4" />
                </button>

                {/* Code */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={toggleCode}
                  className={`p-1.5 rounded cursor-pointer transition-colors ${
                    activeFormats.code
                      ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'
                      : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300'
                  }`}
                  title="Code snippet or block"
                >
                  <Code className="w-4 h-4" />
                </button>

                {/* Bulleted List */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => execEditorCmd('insertUnorderedList')}
                  className={`p-1.5 rounded cursor-pointer transition-colors ${
                    activeFormats.unorderedList
                      ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'
                      : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300'
                  }`}
                  title="Bulleted List"
                >
                  <List className="w-4 h-4" />
                </button>

                {/* Numbered List */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => execEditorCmd('insertOrderedList')}
                  className={`p-1.5 rounded cursor-pointer transition-colors ${
                    activeFormats.orderedList
                      ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'
                      : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300'
                  }`}
                  title="Numbered List"
                >
                  <ListOrdered className="w-4 h-4" />
                </button>

                {/* Table */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={handleInsertTable}
                  className="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded text-slate-600 dark:text-slate-300 cursor-pointer transition-colors"
                  title="Insert Table"
                >
                  <TableIcon className="w-4 h-4" />
                </button>

                {/* Divider Line */}
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => execEditorCmd('insertHorizontalRule')}
                  className="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded text-slate-600 dark:text-slate-300 cursor-pointer transition-colors"
                  title="Horizontal divider"
                >
                  <Minus className="w-4 h-4" />
                </button>
              </div>

              {/* Undo & Redo */}
              <div className="flex items-center gap-1 shrink-0">
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => execEditorCmd('undo')}
                  className="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 cursor-pointer transition-colors"
                  title="Undo (Ctrl+Z)"
                >
                  <Undo className="w-3.5 h-3.5" />
                </button>
                <button
                  type="button"
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => execEditorCmd('redo')}
                  className="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 cursor-pointer transition-colors"
                  title="Redo (Ctrl+Y)"
                >
                  <Redo className="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            {/* WYSIWYG ContentEditable Surface */}
            <div className="pt-2 flex-1 flex flex-col">
              <div
                ref={notesEditorRef}
                contentEditable={true}
                suppressContentEditableWarning={true}
                onInput={handleNotesInput}
                onKeyUp={updateActiveFormats}
                onMouseUp={updateActiveFormats}
                onSelect={updateActiveFormats}
                onBlur={saveSelection}
                className="notes-wysiwyg-editor w-full flex-1 min-h-[350px] text-slate-900 dark:text-slate-100 bg-transparent text-base leading-relaxed border-none focus:outline-none p-2 font-sans overflow-y-auto custom-scrollbar"
                style={{ outline: 'none' }}
                data-placeholder="Type your personal notes here... Highlight text and click any toolbar button above to format."
              />
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
