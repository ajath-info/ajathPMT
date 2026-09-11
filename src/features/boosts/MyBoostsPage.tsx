import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Rocket, CheckCircle2, ChevronLeft, Sparkles, ArrowRight } from 'lucide-react';
import { useToast } from '../../context/ToastContext';

function StatusBadge() {
  return (
    <div className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
      <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" />
      <span>Notifying you of new boosts every 3 hours</span>
    </div>
  );
}

export function MyBoostsPage() {
  const navigate = useNavigate();
  const { addToast } = useToast();

  useEffect(() => {
    document.title = 'My Boosts';
  }, []);

  // State allowing demo toggling between empty state and received boosts
  const [showDemoBoosts, setShowDemoBoosts] = useState(false);

  const demoBoosts = [
    {
      id: 'boost-1',
      emoji: '🚀',
      author: 'Edward',
      action: 'boosted your message',
      item: 'COST MATRIX FOR RIDE MY CARS.docx [GHANA PRICE ADJUSTMENT]',
      project: 'Ride My Cars (Edward)',
      path: '/projects/proj-rmc/discussions',
      time: '4:19 AM',
    },
    {
      id: 'boost-2',
      emoji: '👏',
      author: 'Sarah Jenkins',
      action: 'clapped for your to-do',
      item: 'Dependency Checklist: Domain login & Server setup',
      project: 'Ride My Cars (Edward)',
      path: '/projects/proj-rmc/todos',
      time: 'Yesterday',
    },
    {
      id: 'boost-3',
      emoji: '❤️',
      author: 'Manjot',
      action: 'loved your check-in answer',
      item: 'What did you accomplish today and priorities for tomorrow?',
      project: 'Ride My Cars (Edward)',
      path: '/projects/proj-rmc/checkins',
      time: 'Sep 7',
    },
  ];

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-300 pb-16 font-sans">
      {/* Modern SaaS Card Container */}
      <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
        
        {/* Top Navigation & Status */}
        <div className="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800 flex-wrap gap-2">
          <button
            onClick={() => navigate('/dashboard')}
            className="text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 flex items-center gap-1.5 transition-colors cursor-pointer"
          >
            <ChevronLeft className="w-4 h-4" /> Return to Dashboard
          </button>

          <StatusBadge />
        </div>

        {/* Hero Header with Vibrant Gradient Accent */}
        <div className="flex items-center gap-4 py-2">
          <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-fuchsia-500 to-pink-500 flex items-center justify-center text-white shadow-lg shadow-fuchsia-500/20 shrink-0">
            <Rocket className="w-6 h-6" />
          </div>
          <div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
              <span>Your Teammate Boosts</span>
              <span className="text-2xl">🚀</span>
            </h1>
            <p className="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
              Little notes of cheer, kudos, and encouragement from collaborators across your projects
            </p>
          </div>
        </div>

        {/* Content Box */}
        {!showDemoBoosts ? (
          /* Modern Empty State */
          <div className="py-12 px-4">
            <div className="max-w-md mx-auto text-center space-y-4 p-8 rounded-3xl border border-dashed border-fuchsia-300 dark:border-fuchsia-800/60 bg-fuchsia-50/40 dark:bg-fuchsia-950/20">
              <div className="w-14 h-14 mx-auto rounded-2xl bg-fuchsia-100 dark:bg-fuchsia-900/40 text-fuchsia-600 dark:text-fuchsia-400 flex items-center justify-center shadow-inner">
                <Rocket className="w-7 h-7 stroke-[1.75]" />
              </div>
              <div className="space-y-1.5">
                <h3 className="text-base font-bold text-slate-900 dark:text-slate-100">
                  No boosts received just yet
                </h3>
                <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  When you share updates, complete tasks, or participate in discussions, your teammates can send boosts to celebrate your wins!
                </p>
              </div>
              <div className="pt-2">
                <button
                  onClick={() => setShowDemoBoosts(true)}
                  className="px-4 py-2 rounded-xl bg-gradient-to-r from-fuchsia-600 to-pink-600 hover:from-fuchsia-700 hover:to-pink-700 text-white text-xs font-bold shadow-md shadow-fuchsia-500/20 cursor-pointer inline-flex items-center gap-1.5 active:scale-95 transition-all"
                >
                  <Sparkles className="w-3.5 h-3.5" /> Preview example received boosts
                </button>
              </div>
            </div>
          </div>
        ) : (
          /* Interactive Boosts Feed Grid */
          <div className="space-y-4 pt-2">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
              <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400">
                Recent Boosts from Collaborators ({demoBoosts.length})
              </h3>
              <button
                onClick={() => setShowDemoBoosts(false)}
                className="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 underline cursor-pointer"
              >
                Reset to empty state
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {demoBoosts.map((b) => (
                <div
                  key={b.id}
                  className="p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-xs space-y-3 hover:border-fuchsia-400 dark:hover:border-fuchsia-500/80 hover:shadow-md transition-all group flex flex-col justify-between"
                >
                  <div className="space-y-3">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <span className="text-2xl p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">{b.emoji}</span>
                        <div>
                          <p className="text-xs font-bold text-slate-900 dark:text-slate-100">
                            {b.author}
                          </p>
                          <p className="text-[11px] text-slate-500 dark:text-slate-400">
                            {b.action}
                          </p>
                        </div>
                      </div>
                      <span className="text-[11px] text-slate-400 font-medium">{b.time}</span>
                    </div>

                    <div className="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-300 italic font-medium leading-relaxed">
                      "{b.item}"
                    </div>
                  </div>

                  <div className="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                    <span className="text-slate-500 dark:text-slate-400 font-medium truncate max-w-[150px]">
                      {b.project}
                    </span>
                    <button
                      onClick={() => navigate(b.path)}
                      className="px-3 py-1.5 rounded-lg bg-fuchsia-50 dark:bg-fuchsia-950/50 hover:bg-fuchsia-100 dark:hover:bg-fuchsia-900/60 text-fuchsia-800 dark:text-fuchsia-300 font-bold text-xs flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                      View in project <ArrowRight className="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
