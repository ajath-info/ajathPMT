import React from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Sparkles,
  CheckCircle2,
  MessageSquare,
  FileText,
  Clock,
  ArrowRight,
  Activity,
  HelpCircle,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { getInitials } from '../../lib/utils';

interface ActivityItem {
  id: string;
  user: string;
  initials: string;
  avatarBg: string;
  action: string;
  target: string;
  projectName: string;
  projectId: string;
  time: string;
  type: 'todo' | 'message' | 'file' | 'checkin';
}

const MOCK_ACTIVITIES_TODAY: ActivityItem[] = [
  {
    id: 'act-1',
    user: 'Claire Watson',
    initials: 'CW',
    avatarBg: 'bg-indigo-600',
    action: 'completed 2 tasks',
    target: 'Inspection checklist & vehicle documentation',
    projectName: 'Ride My Cars: Pro Mobile Apps',
    projectId: 'proj-rmc',
    time: '2:45 PM',
    type: 'todo',
  },
  {
    id: 'act-2',
    user: 'Edward',
    initials: 'E',
    avatarBg: 'bg-orange-500',
    action: 'posted a proposal',
    target: 'Ghana Price Adjustment Matrix',
    projectName: 'Ride My Cars: Pro Mobile Apps',
    projectId: 'proj-rmc',
    time: '11:15 AM',
    type: 'message',
  },
  {
    id: 'act-3',
    user: 'Sarah Jenkins',
    initials: 'SJ',
    avatarBg: 'bg-purple-600',
    action: 'answered daily check-in',
    target: 'What did you work on today?',
    projectName: 'Ajath Infotech Pvt Ltd',
    projectId: 'proj-hq',
    time: '9:30 AM',
    type: 'checkin',
  },
];

const MOCK_ACTIVITIES_YESTERDAY: ActivityItem[] = [
  {
    id: 'act-4',
    user: 'David Chen',
    initials: 'DC',
    avatarBg: 'bg-blue-600',
    action: 'uploaded document',
    target: 'Database Migration Architecture v2.pdf',
    projectName: 'BIPL Global Dev',
    projectId: 'proj-bipl',
    time: 'Yesterday at 4:20 PM',
    type: 'file',
  },
  {
    id: 'act-5',
    user: 'Edward',
    initials: 'E',
    avatarBg: 'bg-orange-500',
    action: 'commented on update',
    target: 'Spotlight what matters and other improvements',
    projectName: 'Ajath Infotech Pvt Ltd',
    projectId: 'proj-hq',
    time: 'Yesterday at 2:05 PM',
    type: 'message',
  },
];

export function BasecampLatestActivityFeed() {
  const navigate = useNavigate();
  const { profile } = useAuth();
  const currentUserName = profile?.full_name || 'Claire Watson';
  const currentUserInitials = getInitials(currentUserName);

  const todayActivities = MOCK_ACTIVITIES_TODAY.map((item) => {
    if (item.id === 'act-1' || item.user === 'Shivy Narain' || item.initials === 'SN') {
      return {
        ...item,
        user: currentUserName,
        initials: currentUserInitials,
      };
    }
    return item;
  });

  const renderIcon = (type: string) => {
    switch (type) {
      case 'todo':
        return <CheckCircle2 className="w-4 h-4 text-emerald-600 dark:text-emerald-400" />;
      case 'message':
        return <MessageSquare className="w-4 h-4 text-blue-600 dark:text-blue-400" />;
      case 'file':
        return <FileText className="w-4 h-4 text-amber-600 dark:text-amber-400" />;
      case 'checkin':
        return <HelpCircle className="w-4 h-4 text-purple-600 dark:text-purple-400" />;
      default:
        return <Clock className="w-4 h-4 text-slate-400" />;
    }
  };

  return (
    <div className="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs space-y-6 animate-in fade-in duration-200">
      {/* Feed Header */}
      <div className="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-4">
        <div className="flex items-center gap-3">
          <div className="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-200/60 dark:border-blue-800/60">
            <Activity className="w-4 h-4" />
          </div>
          <div>
            <h2 className="text-base font-bold text-slate-900 dark:text-white leading-tight">
              Live Workspace Activity
            </h2>
            <p className="text-xs text-slate-500 dark:text-slate-400">
              Recent updates, submissions, and check-ins
            </p>
          </div>
        </div>

        <button
          onClick={() => navigate('/activity')}
          className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/50 transition-colors cursor-pointer"
        >
          <span>View All Activity</span>
          <ArrowRight className="w-3.5 h-3.5" />
        </button>
      </div>

      {/* Group: Today */}
      <div className="space-y-3">
        <div className="flex items-center gap-2">
          <span className="text-[11px] font-bold text-slate-400 dark:text-slate-500 tracking-wider uppercase">
            Today
          </span>
          <div className="h-px bg-slate-100 dark:bg-slate-800 flex-1" />
        </div>

        <div className="divide-y divide-slate-100 dark:divide-slate-800/60">
          {todayActivities.map((item) => (
            <div
              key={item.id}
              onClick={() => navigate(`/projects/${item.projectId}`)}
              className="flex items-start gap-3.5 py-3 px-2 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer group"
            >
              {/* Left: User Avatar */}
              <div
                className={`w-8 h-8 rounded-xl ${item.avatarBg} text-white font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5 shadow-xs`}
              >
                {item.initials}
              </div>

              {/* Middle: Content */}
              <div className="min-w-0 flex-1 text-xs">
                <p className="text-slate-700 dark:text-slate-300 leading-snug">
                  <span className="font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                    {item.user}
                  </span>{' '}
                  <span className="text-slate-500 dark:text-slate-400">{item.action}:</span>{' '}
                  <span className="font-semibold text-slate-900 dark:text-slate-100">
                    {item.target}
                  </span>
                </p>
                <div className="flex items-center gap-2 pt-1 text-[11px] text-slate-400">
                  <span className="font-medium text-slate-600 dark:text-slate-400">
                    {item.projectName}
                  </span>
                  <span>•</span>
                  <span>{item.time}</span>
                </div>
              </div>

              {/* Right: Icon Badge */}
              <div className="p-2 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 shrink-0 border border-slate-200/50 dark:border-slate-700/50 group-hover:scale-105 transition-transform">
                {renderIcon(item.type)}
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Group: Yesterday */}
      <div className="space-y-3 pt-1">
        <div className="flex items-center gap-2">
          <span className="text-[11px] font-bold text-slate-400 dark:text-slate-500 tracking-wider uppercase">
            Yesterday
          </span>
          <div className="h-px bg-slate-100 dark:bg-slate-800 flex-1" />
        </div>

        <div className="divide-y divide-slate-100 dark:divide-slate-800/60">
          {MOCK_ACTIVITIES_YESTERDAY.map((item) => (
            <div
              key={item.id}
              onClick={() => navigate(`/projects/${item.projectId}`)}
              className="flex items-start gap-3.5 py-3 px-2 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer group"
            >
              <div
                className={`w-8 h-8 rounded-xl ${item.avatarBg} text-white font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5 shadow-xs`}
              >
                {item.initials}
              </div>

              <div className="min-w-0 flex-1 text-xs">
                <p className="text-slate-700 dark:text-slate-300 leading-snug">
                  <span className="font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                    {item.user}
                  </span>{' '}
                  <span className="text-slate-500 dark:text-slate-400">{item.action}:</span>{' '}
                  <span className="font-semibold text-slate-900 dark:text-slate-100">
                    {item.target}
                  </span>
                </p>
                <div className="flex items-center gap-2 pt-1 text-[11px] text-slate-400">
                  <span className="font-medium text-slate-600 dark:text-slate-400">
                    {item.projectName}
                  </span>
                  <span>•</span>
                  <span>{item.time}</span>
                </div>
              </div>

              <div className="p-2 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 shrink-0 border border-slate-200/50 dark:border-slate-700/50 group-hover:scale-105 transition-transform">
                {renderIcon(item.type)}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
