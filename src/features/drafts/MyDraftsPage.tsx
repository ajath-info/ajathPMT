import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { FileText, ChevronLeft, ArrowRight, Sparkles, Trash2 } from 'lucide-react';
import { useToast } from '../../context/ToastContext';

export function MyDraftsPage() {
  const navigate = useNavigate();
  const { addToast } = useToast();

  useEffect(() => {
    document.title = 'My drafts';
  }, []);

  const [showDemoDrafts, setShowDemoDrafts] = useState(false);

  const [drafts, setDrafts] = useState([
    {
      id: 'draft-1',
      title: 'Re: Untitled - Pricing model adjustment & agreement',
      snippet: 'Enclosing the updated rider commission breakdown and vehicle intake checklist for review...',
      project: 'Ride My Cars (Edward)',
      tool: 'Message Board',
      path: '/projects/proj-rmc/discussions',
      saved: 'Saved 1 hour ago',
    },
    {
      id: 'draft-2',
      title: 'QA Pass for Vehicle Inspection Flow',
      snippet: 'Verify mobile responsiveness, camera capture upload, and offline sync verification...',
      project: 'Ride My Cars (Edward)',
      tool: 'To-dos',
      path: '/projects/proj-rmc/todos',
      saved: 'Saved 4 hours ago',
    },
  ]);

  const handleDeleteDraft = (id: string, e: React.MouseEvent) => {
    e.stopPropagation();
    setDrafts((prev) => prev.filter((d) => d.id !== id));
    addToast('Draft discarded', 'info');
  };

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-300 pb-16 font-sans">
      {/* Modern SaaS Card Container */}
      <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
        
        {/* Top Navigation */}
        <div className="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
          <button
            onClick={() => navigate('/dashboard')}
            className="text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 flex items-center gap-1.5 transition-colors cursor-pointer"
          >
            <ChevronLeft className="w-4 h-4" /> Return to Dashboard
          </button>

          <span className="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
            {drafts.length} Active {drafts.length === 1 ? 'Draft' : 'Drafts'}
          </span>
        </div>

        {/* Hero Banner with Ambient Glow */}
        <div className="flex items-center gap-4 py-2">
          <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/20 shrink-0">
            <FileText className="w-6 h-6" />
          </div>
          <div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
              Your Saved Drafts
            </h1>
            <p className="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
              Unpublished messages, to-do notes, and working documents across all active projects
            </p>
          </div>
        </div>

        {/* Content Box */}
        {!showDemoDrafts || drafts.length === 0 ? (
          /* Modern Empty State Card */
          <div className="py-12 px-4">
            <div className="max-w-md mx-auto text-center space-y-4 p-8 rounded-3xl border border-dashed border-amber-300 dark:border-amber-800/60 bg-amber-50/40 dark:bg-amber-950/20">
              <div className="w-14 h-14 mx-auto rounded-2xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-inner">
                <FileText className="w-7 h-7 stroke-[1.75]" />
              </div>
              <div className="space-y-1.5">
                <h3 className="text-base font-bold text-slate-900 dark:text-slate-100">
                  No drafts saved just yet
                </h3>
                <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  When you start composing a message or creating content and pause, your work will be safely preserved here.
                </p>
              </div>
              <div className="pt-2">
                <button
                  onClick={() => setShowDemoDrafts(true)}
                  className="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold shadow-md shadow-amber-500/20 cursor-pointer inline-flex items-center gap-1.5 active:scale-95 transition-all"
                >
                  <Sparkles className="w-3.5 h-3.5" /> Preview example saved drafts
                </button>
              </div>
            </div>
          </div>
        ) : (
          /* Interactive Drafts List */
          <div className="space-y-4 pt-2">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
              <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400">
                Saved Working Drafts ({drafts.length})
              </h3>
              <button
                onClick={() => setShowDemoDrafts(false)}
                className="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 underline cursor-pointer"
              >
                Reset to empty state
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {drafts.map((d) => (
                <div
                  key={d.id}
                  className="p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-xs space-y-3 hover:border-amber-400 dark:hover:border-amber-500/80 hover:shadow-md transition-all group flex flex-col justify-between"
                >
                  <div className="space-y-2.5">
                    <div className="flex items-center justify-between">
                      <span className="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 px-2.5 py-0.5 rounded-md">
                        {d.tool}
                      </span>
                      <div className="flex items-center gap-2">
                        <span className="text-[11px] text-slate-400 font-medium">{d.saved}</span>
                        <button
                          onClick={(e) => handleDeleteDraft(d.id, e)}
                          className="p-1 text-slate-400 hover:text-rose-500 dark:hover:text-rose-400 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded"
                          title="Discard draft"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </div>

                    <div>
                      <h4 className="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                        {d.title}
                      </h4>
                      <p className="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">
                        {d.snippet}
                      </p>
                    </div>
                  </div>

                  <div className="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                    <span className="text-slate-500 dark:text-slate-400 font-medium truncate max-w-[180px]">
                      {d.project}
                    </span>
                    <button
                      onClick={() => navigate(d.path)}
                      className="px-3 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-100 dark:hover:bg-amber-900/60 text-amber-800 dark:text-amber-300 font-bold text-xs flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                      Continue drafting <ArrowRight className="w-3.5 h-3.5" />
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
