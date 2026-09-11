import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  AlignLeft,
  Activity,
  TrendingUp,
  Clock,
  Bell,
  ClipboardList,
  CheckCircle,
  User,
  Briefcase,
} from 'lucide-react';

export function ProjectReportsPage() {
  const navigate = useNavigate();

  // Browser Tab Title matching Basecamp: Reports
  useEffect(() => {
    document.title = 'Reports';
    return () => {
      document.title = 'Ajath PMT';
    };
  }, []);

  const reportCards = [
    {
      id: 'lineup',
      title: 'Lineup',
      description: 'Plot projects on a timeline for a snapshot of what’s in play',
      icon: AlignLeft,
      iconBg: 'bg-[#db2777]', // Pink
      onClick: () => navigate('/projects/lineup'),
    },
    {
      id: 'mission-control',
      title: 'Mission Control',
      description: 'See how the needle is moving on your projects',
      icon: Activity,
      iconBg: 'bg-[#2563eb]', // Blue
      onClick: () => navigate('/projects/lineup'),
    },
    {
      id: 'hilltop',
      title: 'Hilltop View',
      description: 'See how things are moving on your hill charts',
      icon: TrendingUp,
      iconBg: 'bg-[#84cc16]', // Lime/green
      onClick: () => navigate('/projects/lineup'),
    },
    {
      id: 'upcoming',
      title: 'Upcoming tasks',
      description: 'See all to-dos and cards with upcoming due dates',
      icon: Clock,
      iconBg: 'bg-[#059669]', // Emerald
      onClick: () => navigate('/my-tasks'),
    },
    {
      id: 'overdue',
      title: 'Overdue to-dos',
      description: 'See what’s running late across projects',
      icon: Bell,
      iconBg: 'bg-[#ef4444]', // Coral/red
      onClick: () => navigate('/my-tasks'),
    },
    {
      id: 'unassigned',
      title: 'Unassigned tasks',
      description: 'Find unassigned to-dos and cards',
      icon: ClipboardList,
      iconBg: 'bg-[#f59e0b]', // Amber
      onClick: () => navigate('/my-tasks'),
    },
    {
      id: 'completed',
      title: 'Tasks added/completed',
      description: 'A daily log of new and completed tasks',
      icon: CheckCircle,
      iconBg: 'bg-[#10b981]', // Mint
      onClick: () => navigate('/activity'),
    },
    {
      id: 'someone-tasks',
      title: 'Someone’s tasks',
      description: 'Track assigned work & responsibilities',
      icon: User,
      iconBg: 'bg-[#f97316]', // Orange
      onClick: () => navigate('/my-tasks'),
    },
    {
      id: 'someone-activity',
      title: 'Someone’s activity',
      description: 'See what someone’s been up to',
      icon: Briefcase,
      iconBg: 'bg-[#6366f1]', // Indigo
      onClick: () => navigate('/activity'),
    },
  ];

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 py-2 pb-16">
      {/* Modern Reports Card */}
      <div className="w-full bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-200/80 dark:border-slate-800 space-y-8">
        
        {/* Header */}
        <div className="text-center space-y-2">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 mb-1">
            Workspace Intelligence
          </div>
          <h1 className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
            Choose a Report
          </h1>
          <p className="text-sm sm:text-base text-slate-500 dark:text-slate-400 font-normal max-w-xl mx-auto">
            Get an instant high-level view across all your active projects, team assignments, and delivery milestones
          </p>
        </div>

        {/* 3x3 Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 pt-2">
          {reportCards.map((card) => {
            const Icon = card.icon;
            return (
              <div
                key={card.id}
                onClick={card.onClick}
                className="bg-slate-50/70 dark:bg-slate-850 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 flex flex-col items-center text-center shadow-xs hover:shadow-xl hover:border-blue-300 dark:hover:border-blue-700 hover:-translate-y-1 transition-all duration-200 cursor-pointer group select-none min-h-[200px] justify-center space-y-3"
              >
                <div
                  className={`w-14 h-14 rounded-2xl ${card.iconBg} text-white flex items-center justify-center shadow-md group-hover:scale-105 transition-transform`}
                >
                  <Icon className="w-6 h-6 stroke-[2.5]" />
                </div>
                <div className="space-y-1">
                  <h3 className="font-extrabold text-[15px] text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors leading-snug">
                    {card.title}
                  </h3>
                  <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-normal">
                    {card.description}
                  </p>
                </div>
              </div>
            );
          })}
        </div>

      </div>
    </div>
  );
}
