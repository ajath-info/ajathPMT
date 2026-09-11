import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Calendar, ChevronDown, ChevronUp, ArrowRight, Clock } from 'lucide-react';
import { Project } from '../../types';

interface BasecampLineupStripProps {
  projects: Project[];
}

const AVATAR_COLORS: Record<string, string> = {
  AI: 'bg-teal-500',
  GK: 'bg-pink-500',
  PK: 'bg-cyan-500',
  RK: 'bg-rose-500',
  SN: 'bg-purple-600',
  CW: 'bg-purple-600',
  E: 'bg-orange-500',
  B: 'bg-amber-500',
};

function getInitials(name?: string): string {
  if (!name) return 'CW';
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

export function BasecampLineupStrip({ projects }: BasecampLineupStripProps) {
  const navigate = useNavigate();
  const [isOpen, setIsOpen] = useState<boolean>(() => {
    try {
      return localStorage.getItem('basecamp_home_lineup_open') !== 'false';
    } catch {
      return true;
    }
  });

  const toggleOpen = () => {
    const next = !isOpen;
    setIsOpen(next);
    try {
      localStorage.setItem('basecamp_home_lineup_open', String(next));
    } catch {}
  };

  // Select top active projects with dates or meaningful mock ranges
  const activeProjects = projects.slice(0, 5);

  const months = ['Aug', 'Sep', 'Oct', 'Nov'];

  return (
    <div className="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xs transition-all space-y-4 animate-in fade-in duration-200">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center shadow-2xs">
            <Calendar className="w-4 h-4 stroke-[2.5]" />
          </div>
          <div>
            <h2 className="text-base font-extrabold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
              <span>The Lineup</span>
              <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                {activeProjects.length} scheduled
              </span>
            </h2>
            <p className="text-xs text-slate-400 dark:text-slate-500 font-normal">
              Visual project timeline across the next 12 weeks
            </p>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => navigate('/projects?view=lineup')}
            className="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline hidden sm:flex items-center gap-1"
          >
            <span>Full Lineup view</span>
            <ArrowRight className="w-3.5 h-3.5" />
          </button>
          <button
            onClick={toggleOpen}
            className="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            title={isOpen ? 'Collapse Lineup' : 'Expand Lineup'}
          >
            {isOpen ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}
          </button>
        </div>
      </div>

      {/* Timeline Body */}
      {isOpen && (
        <div className="space-y-4 pt-1">
          {/* Calendar Months Ruler */}
          <div className="relative border-b border-slate-200/80 dark:border-slate-800 pb-2">
            <div className="grid grid-cols-4 text-center text-xs font-bold text-slate-400 dark:text-slate-500 select-none">
              {months.map((m, idx) => (
                <div key={m} className={`relative ${idx === 1 ? 'text-amber-600 font-extrabold' : ''}`}>
                  <span>{m} 2026</span>
                  {idx === 1 && (
                    <span className="ml-1.5 px-1.5 py-0.2 rounded text-[9px] bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                      Now
                    </span>
                  )}
                </div>
              ))}
            </div>
            {/* Today indicator line */}
            <div className="absolute left-[36%] -bottom-px w-2 h-2 rounded-full bg-amber-500 -translate-x-1" />
          </div>

          {/* Project Timeline Bars */}
          <div className="space-y-2.5">
            {activeProjects.map((p, idx) => {
              // Calculate styling offset or widths based on project index
              const offsets = [
                { left: '10%', width: '45%', color: 'bg-emerald-500/15 border-emerald-400/60 text-emerald-950 dark:text-emerald-200' },
                { left: '25%', width: '60%', color: 'bg-blue-500/15 border-blue-400/60 text-blue-950 dark:text-blue-200' },
                { left: '5%', width: '75%', color: 'bg-purple-500/15 border-purple-400/60 text-purple-950 dark:text-purple-200' },
                { left: '35%', width: '50%', color: 'bg-amber-500/15 border-amber-400/60 text-amber-950 dark:text-amber-200' },
                { left: '50%', width: '40%', color: 'bg-rose-500/15 border-rose-400/60 text-rose-950 dark:text-rose-200' },
              ];
              const config = offsets[idx % offsets.length];

              const dateRange = p.start_date && p.end_date
                ? `${new Date(p.start_date).toLocaleDateString([], { month: 'short', day: 'numeric' })} – ${new Date(p.end_date).toLocaleDateString([], { month: 'short', day: 'numeric' })}`
                : idx === 0
                ? 'Aug 15 – Sep 28'
                : idx === 1
                ? 'Sep 1 – Oct 31'
                : idx === 2
                ? 'Aug 1 – Oct 15'
                : 'Sep 10 – Nov 20';

              return (
                <div
                  key={p.id}
                  onClick={() => navigate(`/projects/${p.id}`)}
                  className="relative h-10 w-full bg-slate-50 dark:bg-slate-800/40 rounded-xl overflow-hidden cursor-pointer group select-none hover:bg-slate-100/80 transition-all flex items-center"
                >
                  {/* Spanning project bar */}
                  <div
                    style={{ left: config.left, width: config.width }}
                    className={`absolute h-8 rounded-lg border ${config.color} px-3 flex items-center justify-between shadow-2xs group-hover:shadow-xs transition-all group-hover:scale-[1.01]`}
                  >
                    <div className="flex items-center gap-2 min-w-0 pr-2">
                      <span className="font-extrabold text-xs truncate">
                        {p.name}
                      </span>
                      <span className="text-[10px] opacity-75 hidden sm:inline truncate">
                        • {dateRange}
                      </span>
                    </div>

                    <div className="flex items-center gap-1.5 shrink-0">
                      <div className="w-5 h-5 rounded-full bg-slate-800 text-white font-bold text-[9px] flex items-center justify-center ring-1 ring-white">
                        {getInitials(p.name)}
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
}
