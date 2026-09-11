import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Megaphone, FileText, CheckSquare, MessageSquare } from 'lucide-react';

export function EverythingPage() {
  const navigate = useNavigate();

  // Browser tab title matching screenshot: Everything 🍕
  useEffect(() => {
    document.title = 'Everything 🍕';
    return () => {
      document.title = 'Ajath PMT';
    };
  }, []);

  const everythingTiles = [
    {
      id: 'messages',
      title: 'All messages',
      description: 'See all messages across your projects',
      icon: Megaphone,
      iconBg: 'bg-[#1b75bb]',
      path: '/projects',
      onClick: () => navigate('/discussions'),
    },
    {
      id: 'docs',
      title: 'All docs & files',
      description: 'Browse all docs and files across your projects',
      icon: FileText,
      iconBg: 'bg-[#ea580c]',
      path: '/docs',
      onClick: () => navigate('/docs'),
    },
    {
      id: 'tasks',
      title: 'All tasks',
      description: 'See all to-dos and cards across your projects',
      icon: CheckSquare,
      iconBg: 'bg-[#16a34a]',
      path: '/my-tasks',
      onClick: () => navigate('/my-tasks'),
    },
    {
      id: 'comments',
      title: 'All comments',
      description: 'See all comments across your projects',
      icon: MessageSquare,
      iconBg: 'bg-[#db2777]',
      path: '/activity',
      onClick: () => navigate('/activity'),
    },
  ];

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 py-2 pb-16">
      {/* Centered Document Card */}
      <div className="w-full bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-200/80 dark:border-slate-800 space-y-8">
        
        {/* Card Header */}
        <div className="text-center space-y-2">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800 mb-1">
            Omni-Workspace Search
          </div>
          <h1 className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
            Everything
          </h1>
          <p className="text-sm sm:text-base text-slate-500 dark:text-slate-400 font-normal max-w-xl mx-auto">
            Cross-project collections of tasks, messages, uploads, and discussions from all your workspaces
          </p>
        </div>

        {/* 4 Cards Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pt-2">
          {everythingTiles.map((tile) => {
            const Icon = tile.icon;
            return (
              <div
                key={tile.id}
                onClick={tile.onClick}
                className="bg-slate-50/70 dark:bg-slate-850 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-7 flex flex-col items-center text-center shadow-xs hover:shadow-xl hover:border-purple-300 dark:hover:border-purple-700 hover:-translate-y-1 transition-all duration-200 cursor-pointer group select-none min-h-[220px] justify-center space-y-3"
              >
                <div
                  className={`w-14 h-14 rounded-2xl ${tile.iconBg} text-white flex items-center justify-center shadow-md group-hover:scale-105 transition-transform`}
                >
                  <Icon className="w-7 h-7" />
                </div>
                <div className="space-y-1">
                  <h3 className="font-extrabold text-base text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                    {tile.title}
                  </h3>
                  <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-normal">
                    {tile.description}
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
