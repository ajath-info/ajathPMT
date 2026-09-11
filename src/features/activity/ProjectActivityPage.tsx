import React, { useState, useEffect, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Check,
  Megaphone,
  MessageSquare,
  FileText,
  ChevronDown,
  File,
} from 'lucide-react';
import { useProject } from '../../context/ProjectContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useAuth } from '../../context/AuthContext';
import { getOrganizationActivityLogs } from '../../services/organizationService';
import { getInitials } from '../../lib/utils';

interface ActivityItem {
  id: string;
  time: string;
  dateGroup: string;
  projectId: string;
  projectName: string;
  authorName: string;
  authorInitials: string;
  authorColor: string;
  actionText: string;
  targetTitle: string;
  targetLink?: string;
  snippet: string;
  hasAttachment?: boolean;
  attachmentType?: string;
  type: 'message' | 'comment' | 'task';
}

const DEFAULT_LATEST_ACTIVITIES: ActivityItem[] = [
  {
    id: 'act-1',
    dateGroup: 'TODAY, TUESDAY, SEPTEMBER 8',
    time: '10:31am',
    projectId: 'proj-hq',
    projectName: 'Ajath Infotech Pvt Ltd HQ',
    authorName: 'Claire W.',
    authorInitials: 'CW',
    authorColor: 'bg-[#872ec4]',
    actionText: 'posted a message:',
    targetTitle: 'MOM 08/09/2026',
    targetLink: '/projects/proj-hq/discussions',
    snippet:
      'Hi All, Please find below the Minutes of the Meeting (MOM) discussion on Date: 08/09/2026 Time: 10:10 AM To 10:30 AM Attendees: Shachish, Gaurav. Agenda: Discuss on Todays Work Meeting Summary: Complete the Ajath PMT project features according to specifications.',
    type: 'message',
  },
  {
    id: 'act-2',
    dateGroup: 'TODAY, TUESDAY, SEPTEMBER 8',
    time: '4:19am',
    projectId: 'proj-rmc',
    projectName: 'Ride My Cars (Edward)',
    authorName: 'Edward',
    authorInitials: 'E',
    authorColor: 'bg-[#ea580c]',
    actionText: 'commented on',
    targetTitle: 'Untitled',
    targetLink: '/projects/proj-rmc/docs',
    snippet: '[COST MATRIX FOR RIDE MY CARS.docx] GHANA PRICE ADJUSTMENT',
    hasAttachment: true,
    attachmentType: 'DOCX',
    type: 'comment',
  },
  {
    id: 'act-3',
    dateGroup: 'YESTERDAY, MONDAY, SEPTEMBER 7',
    time: '7:02pm',
    projectId: 'proj-hq',
    projectName: 'Ajath Infotech Pvt Ltd HQ',
    authorName: 'Claire W.',
    authorInitials: 'CW',
    authorColor: 'bg-[#872ec4]',
    actionText: 'posted a message:',
    targetTitle: 'Worklog 07/09/2026',
    targetLink: '/projects/proj-hq/discussions',
    snippet:
      'Today I worked on Ajath PMT project with all functionality according to the reports and UI of the project ready for production.',
    type: 'message',
  },
  {
    id: 'act-4',
    dateGroup: 'YESTERDAY, MONDAY, SEPTEMBER 7',
    time: '10:34am',
    projectId: 'proj-hq',
    projectName: 'Ajath Infotech Pvt Ltd HQ',
    authorName: 'Claire W.',
    authorInitials: 'CW',
    authorColor: 'bg-[#872ec4]',
    actionText: 'posted a message:',
    targetTitle: 'MOM 07/09/2026',
    targetLink: '/projects/proj-hq/discussions',
    snippet:
      'Attendees: Shachish, Gaurav. Agenda: Project review and Ajath PMT milestones for production delivery.',
    type: 'message',
  },
];

export function ProjectActivityPage() {
  const navigate = useNavigate();
  const { projects } = useProject();
  const { profile } = useAuth();
  const { currentOrganization } = useOrganization();
  const [dbActivities, setDbActivities] = useState<any[]>([]);
  const [loadingActivities, setLoadingActivities] = useState(false);

  useEffect(() => {
    if (!currentOrganization?.id || currentOrganization.id === 'demo-org-acme') {
      return;
    }
    setLoadingActivities(true);
    getOrganizationActivityLogs(currentOrganization.id)
      .then((logs) => setDbActivities(logs))
      .catch(() => setDbActivities([]))
      .finally(() => setLoadingActivities(false));
  }, [currentOrganization?.id]);

  const dynamicActivities = useMemo(() => {
    // If inside a custom or newly created workspace, strictly use real dynamic logs
    if (currentOrganization?.id && currentOrganization.id !== 'demo-org-acme') {
      return dbActivities.map((l) => {
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
          time,
          dateGroup,
          projectId: l.project_id || '',
          projectName,
          authorName,
          authorInitials: getInitials(authorName),
          authorColor: 'bg-indigo-600',
          actionText: l.action.startsWith('posted') || l.action.startsWith('created') || l.action.startsWith('updated') ? l.action : `performed ${l.action}`,
          targetTitle: l.item_title || '',
          targetLink: l.project_id ? `/projects/${l.project_id}` : '/dashboard',
          snippet: l.item_title ? `${l.action} "${l.item_title}" in ${projectName}` : `${l.action} in ${projectName}`,
          type: 'message' as const,
        };
      });
    }

    // Demo organization fallback for demo showcase
    return DEFAULT_LATEST_ACTIVITIES;
  }, [currentOrganization?.id, currentOrganization?.name, dbActivities, profile?.id, profile?.full_name, projects]);

  // Browser Tab Title matching Screenshot: Latest Activity
  useEffect(() => {
    document.title = 'Latest Activity';
    return () => {
      document.title = 'Ajath PMT';
    };
  }, []);

  const [activeTab, setActiveTab] = useState<'timeline' | 'wrapup'>('timeline');
  const [selectedProject, setSelectedProject] = useState<string>('all');
  const [selectedPerson, setSelectedPerson] = useState<string>('everyone');
  const [searchQuery, setSearchQuery] = useState('');

  // Group activities
  const filteredActivities = useMemo(() => {
    return dynamicActivities.filter((act) => {
      if (selectedProject !== 'all' && act.projectId !== selectedProject) return false;
      if (selectedPerson !== 'everyone' && act.authorName !== selectedPerson) return false;
      if (searchQuery.trim()) {
        const q = searchQuery.toLowerCase();
        return (
          act.snippet.toLowerCase().includes(q) ||
          act.targetTitle.toLowerCase().includes(q) ||
          act.projectName.toLowerCase().includes(q)
        );
      }
      return true;
    });
  }, [dynamicActivities, selectedProject, selectedPerson, searchQuery]);

  const dateGroups = useMemo(() => {
    const groups: { [date: string]: ActivityItem[] } = {};
    filteredActivities.forEach((act) => {
      if (!groups[act.dateGroup]) groups[act.dateGroup] = [];
      groups[act.dateGroup].push(act);
    });
    return groups;
  }, [filteredActivities]);

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 pb-16">
      {/* Modern Latest Activity Card */}
      <div className="w-full bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
        
        {/* Top Right Notice */}
        <div className="flex items-center justify-between">
          <div />
          <div className="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 select-none bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 px-3 py-1 rounded-full font-semibold border border-emerald-200 dark:border-emerald-800">
            <Check className="w-3.5 h-3.5 text-emerald-600 stroke-[3]" />
            <span>Emailing a daily summary</span>
          </div>
        </div>

        {/* Heading */}
        <div>
          <h1 className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight leading-tight">
            Latest Activity
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            Real-time feed of workspace communications, milestones, check-ins, and task updates
          </p>
        </div>

        {/* Sub-header Filter Bar */}
        <div className="flex flex-wrap items-center justify-between gap-3 pt-1 border-b border-slate-100 dark:border-slate-800 pb-4">
          <div className="flex items-center gap-2 flex-wrap">
            {/* Timeline / Wrap-up tabs */}
            <div className="flex items-center gap-1.5">
              <button
                onClick={() => setActiveTab('timeline')}
                className={`px-3.5 py-1.5 text-xs font-bold rounded-xl transition-all cursor-pointer ${
                  activeTab === 'timeline'
                    ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-sm shadow-blue-500/20'
                    : 'border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
                }`}
              >
                Timeline
              </button>
              <button
                onClick={() => setActiveTab('wrapup')}
                className={`px-3.5 py-1.5 text-xs font-bold rounded-xl transition-all cursor-pointer ${
                  activeTab === 'wrapup'
                    ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-sm shadow-blue-500/20'
                    : 'border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
                }`}
              >
                Wrap-up
              </button>
            </div>

            <div className="h-4 w-px bg-slate-200 dark:bg-slate-700 mx-1 hidden sm:block" />

            {/* Showing: All projects dropdown */}
            <div className="flex items-center gap-1 text-xs text-slate-600 dark:text-slate-300 font-medium">
              <span>Showing</span>
              <select
                value={selectedProject}
                onChange={(e) => setSelectedProject(e.target.value)}
                className="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-md px-2 py-1 text-xs font-bold text-slate-800 dark:text-slate-200 focus:outline-none cursor-pointer"
              >
                <option value="all">All projects</option>
                {projects.map((p) => (
                  <option key={p.id} value={p.id}>
                    {p.name}
                  </option>
                ))}
              </select>
            </div>

            {/* by: Everyone dropdown */}
            <div className="flex items-center gap-1 text-xs text-slate-600 dark:text-slate-300 font-medium">
              <span>by</span>
              <select
                value={selectedPerson}
                onChange={(e) => setSelectedPerson(e.target.value)}
                className="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-md px-2 py-1 text-xs font-bold text-slate-800 dark:text-slate-200 focus:outline-none cursor-pointer"
              >
                <option value="everyone">Everyone</option>
                <option value="Shiv N.">Shiv Narayan</option>
                <option value="Edward">Edward</option>
                <option value="Gaurav Kumar">Gaurav Kumar</option>
              </select>
            </div>
          </div>

          {/* Search Filter Input */}
          <div>
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Filter..."
              className="w-48 text-xs px-3 py-1.5 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-500"
            />
          </div>
        </div>

        {/* Timeline Content */}
        <div className="space-y-8 pt-2">
          {Object.keys(dateGroups).length === 0 ? (
            <div className="p-12 text-center rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 space-y-3">
              <div className="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <Megaphone className="w-6 h-6" />
              </div>
              <h4 className="font-extrabold text-base text-slate-800 dark:text-slate-200">No activity yet</h4>
              <p className="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                No activity has been recorded in {currentOrganization?.name || 'this workspace'} yet. Discussions, tasks, and file updates will automatically be tracked here.
              </p>
            </div>
          ) : (
            Object.entries(dateGroups).map(([dateLabel, items]) => {
              // Count unique active people
              const uniqueAuthors = Array.from(
                new Set(items.map((i) => ({ initials: i.authorInitials, color: i.authorColor })))
              );

              return (
                <div key={dateLabel} className="space-y-4">
                  {/* Date Header matching Screenshot */}
                  <div className="flex items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                      <span className="bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-[11px] font-extrabold uppercase px-3 py-1 rounded-xl tracking-wider shadow-xs">
                        {dateLabel}
                      </span>
                      <div className="h-px bg-slate-200 dark:bg-slate-800 w-24 sm:w-64" />
                    </div>

                    {/* Active People avatars pill */}
                    <div className="flex items-center gap-1.5 text-xs text-slate-500 shrink-0">
                      <span className="hidden sm:inline">
                        {uniqueAuthors.length} {uniqueAuthors.length === 1 ? 'person' : 'people'} active today
                      </span>
                      <div className="flex -space-x-1.5 overflow-hidden">
                        {uniqueAuthors.map((author, idx) => (
                          <div
                            key={idx}
                            className={`w-5 h-5 rounded-full ${author.color} text-white font-bold text-[9px] flex items-center justify-center ring-1 ring-white dark:ring-slate-900 select-none`}
                          >
                            {author.initials}
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>

                  {/* Vertical Timeline items */}
                  <div className="space-y-5 pl-2 relative border-l-2 border-slate-100 dark:border-slate-800 ml-4">
                    {items.map((item) => (
                      <div key={item.id} className="relative pl-7 space-y-1 group">
                        {/* Left icon bubble */}
                        <div
                          className={`absolute -left-[17px] top-1 w-6 h-6 rounded-full ${
                            item.type === 'message'
                              ? 'bg-gradient-to-br from-blue-500 to-indigo-600'
                              : 'bg-gradient-to-br from-amber-500 to-orange-600'
                          } text-white flex items-center justify-center shadow-xs`}
                        >
                          {item.type === 'message' ? (
                            <Megaphone className="w-3 h-3" />
                          ) : (
                            <MessageSquare className="w-3 h-3" />
                          )}
                        </div>

                        {/* Time, Avatar, Project line */}
                        <div className="flex items-center gap-2 text-xs">
                          <span className="text-slate-400">{item.time}</span>
                          <div
                            className={`w-5 h-5 rounded-full ${item.authorColor} text-white font-bold text-[9px] flex items-center justify-center shrink-0`}
                          >
                            {item.authorInitials}
                          </div>
                          <button
                            onClick={() => navigate(`/projects/${item.projectId}`)}
                            className="font-semibold text-slate-600 dark:text-slate-400 hover:underline cursor-pointer"
                          >
                            {item.projectName}
                          </button>
                        </div>

                        {/* Headline with blue target link */}
                        <div className="text-sm font-bold text-slate-900 dark:text-white">
                          <span>{item.authorName} {item.actionText} </span>
                          <button
                            onClick={() => item.targetLink && navigate(item.targetLink)}
                            className="text-blue-600 hover:underline cursor-pointer font-bold"
                          >
                            {item.targetTitle}
                          </button>
                        </div>

                        {/* Snippet */}
                        <p className="text-xs text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl font-normal">
                          {item.snippet}
                        </p>

                        {/* Attachment Preview badge if present matching Screenshot 4 */}
                        {item.hasAttachment && (
                          <div className="pt-1.5 flex items-center">
                            <div className="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex items-center gap-2 shadow-2xs hover:border-blue-300 cursor-pointer transition-colors">
                              <div className="w-8 h-8 rounded bg-sky-100 text-sky-600 flex flex-col items-center justify-center font-bold text-[9px] shadow-2xs">
                                <FileText className="w-3.5 h-3.5 mb-0.5" />
                                <span>{item.attachmentType || 'DOCX'}</span>
                              </div>
                              <div className="text-left">
                                <span className="text-[11px] font-bold text-slate-800 dark:text-slate-200 block">
                                  COST MATRIX FOR RIDE MY CARS.docx
                                </span>
                                <span className="text-[10px] text-slate-400">Microsoft Word document</span>
                              </div>
                            </div>
                          </div>
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
    </div>
  );
}
