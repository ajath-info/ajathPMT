import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { BarChart3, CheckSquare, Clock, AlertTriangle, Activity, Sparkles, TrendingUp } from 'lucide-react';
import { Task } from '../../types';
import { getTasks } from '../../services/taskService';
import { Badge } from '../../components/common/Badge';
import { HillChart, HillScope } from '../../components/projects/HillChart';
import { useProject } from '../../context/ProjectContext';

export function ProjectProgressPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const { projects } = useProject();

  const [selectedProjectId, setSelectedProjectId] = useState<string>('');
  const targetProjectId = projectId || selectedProjectId || projects[0]?.id || '';

  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadData() {
      if (!targetProjectId) {
        setTasks([]);
        setLoading(false);
        return;
      }
      setLoading(true);
      try {
        const tList = await getTasks(targetProjectId);
        setTasks(tList);
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    }
    loadData();
  }, [targetProjectId]);

  const totalTasks = tasks.length;
  const completedTasks = tasks.filter((t) => t.status === 'COMPLETED').length;
  const inProgressTasks = tasks.filter((t) => t.status === 'IN_PROGRESS').length;
  const inReviewTasks = tasks.filter((t) => t.status === 'IN_REVIEW').length;
  const waitingTasks = tasks.filter((t) => t.status === 'WAITING').length;
  const notStartedTasks = tasks.filter((t) => t.status === 'NOT_STARTED').length;

  const overdueTasks = tasks.filter(
    (t) =>
      t.due_date &&
      t.status !== 'COMPLETED' &&
      new Date(t.due_date) < new Date(new Date().setHours(0, 0, 0, 0))
  );

  const completionPercentage =
    totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;

  // WorkSphere Original 5-stage progress calculation
  const getProgressStage = (pct: number) => {
    if (pct >= 100) return { stage: '100% Completed', desc: 'All deliverables finished and verified.' };
    if (pct >= 75) return { stage: '75% Executing', desc: 'Active development and QA in progress.' };
    if (pct >= 50) return { stage: '50% Solution Ready', desc: 'Architecture planned and initial tasks moving.' };
    if (pct >= 25) return { stage: '25% Understanding', desc: 'Requirements gathered and backlog defined.' };
    return { stage: '0% Exploring', desc: 'Initial project setup phase.' };
  };

  const currentStage = getProgressStage(completionPercentage);

  if (loading) {
    return (
      <div className="py-16 text-center text-slate-500 font-semibold text-sm animate-pulse">
        Calculating real project progress metrics...
      </div>
    );
  }

  return (
    <div className="space-y-8 animate-in fade-in duration-300 max-w-7xl mx-auto">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <Badge variant="brand">{completionPercentage}% Completed</Badge>
          </div>
          <h1 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Ajath PMT Hill Chart & Progress Tracker
          </h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Visual Hill Chart (Figuring things out vs. Making it happen), milestone progress, and velocity health
          </p>
        </div>
      </div>

      {/* Basecamp Hill Chart */}
      <HillChart />

      {/* 5-Stage Milestone Tracker */}
      <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Sparkles className="w-5 h-5 text-brand-600" />
            <h3 className="font-extrabold text-base text-slate-900 dark:text-slate-100">
              WorkSphere 5-Stage Milestone: {currentStage.stage}
            </h3>
          </div>
          <span className="text-xs text-slate-500">{currentStage.desc}</span>
        </div>

        <div className="w-full bg-slate-100 dark:bg-slate-800 h-4 rounded-full overflow-hidden p-0.5">
          <div
            className="bg-brand-600 h-full rounded-full transition-all duration-700"
            style={{ width: `${completionPercentage}%` }}
          />
        </div>

        <div className="grid grid-cols-5 text-center text-[11px] font-bold text-slate-500 pt-1">
          <span className={completionPercentage >= 0 ? 'text-brand-600 font-extrabold' : ''}>0% Exploring</span>
          <span className={completionPercentage >= 25 ? 'text-brand-600 font-extrabold' : ''}>25% Understanding</span>
          <span className={completionPercentage >= 50 ? 'text-brand-600 font-extrabold' : ''}>50% Solution Ready</span>
          <span className={completionPercentage >= 75 ? 'text-brand-600 font-extrabold' : ''}>75% Executing</span>
          <span className={completionPercentage >= 100 ? 'text-brand-600 font-extrabold' : ''}>100% Completed</span>
        </div>
      </div>

      {/* Metrics Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2">
          <span className="text-xs font-bold uppercase tracking-wider text-slate-400">Total Tasks</span>
          <p className="text-2xl font-extrabold text-slate-900 dark:text-slate-100">{totalTasks}</p>
          <p className="text-[11px] text-slate-500">{completedTasks} completed • {totalTasks - completedTasks} open</p>
        </div>

        <div className="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2">
          <span className="text-xs font-bold uppercase tracking-wider text-slate-400">In Progress</span>
          <p className="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{inProgressTasks}</p>
          <p className="text-[11px] text-slate-500">{inReviewTasks} in review</p>
        </div>

        <div className="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2">
          <span className="text-xs font-bold uppercase tracking-wider text-slate-400">Overdue Tasks</span>
          <p className="text-2xl font-extrabold text-rose-600 dark:text-rose-400">{overdueTasks.length}</p>
          <p className="text-[11px] text-slate-500">Requires deadline extension</p>
        </div>

        <div className="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2">
          <span className="text-xs font-bold uppercase tracking-wider text-slate-400">Project Health</span>
          <Badge variant={overdueTasks.length === 0 ? 'success' : 'warning'}>
            {overdueTasks.length === 0 ? 'HEALTHY' : 'NEEDS ATTENTION'}
          </Badge>
          <p className="text-[11px] text-slate-500">Calculated from task completion rates</p>
        </div>
      </div>

      {/* Task Status Distribution Breakdown */}
      <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
        <h3 className="font-extrabold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <TrendingUp className="w-5 h-5 text-indigo-500" /> Task Status Breakdown
        </h3>

        <div className="grid grid-cols-1 md:grid-cols-5 gap-3">
          <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 space-y-1">
            <span className="text-xs font-bold text-slate-500">Not Started</span>
            <p className="text-lg font-extrabold text-slate-800 dark:text-slate-200">{notStartedTasks}</p>
          </div>
          <div className="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-900/40 space-y-1">
            <span className="text-xs font-bold text-amber-700 dark:text-amber-300">In Progress</span>
            <p className="text-lg font-extrabold text-amber-700 dark:text-amber-400">{inProgressTasks}</p>
          </div>
          <div className="p-4 rounded-2xl bg-purple-50/50 dark:bg-purple-950/30 border border-purple-200/60 dark:border-purple-900/40 space-y-1">
            <span className="text-xs font-bold text-purple-700 dark:text-purple-300">In Review</span>
            <p className="text-lg font-extrabold text-purple-700 dark:text-purple-400">{inReviewTasks}</p>
          </div>
          <div className="p-4 rounded-2xl bg-blue-50/50 dark:bg-blue-950/30 border border-blue-200/60 dark:border-blue-900/40 space-y-1">
            <span className="text-xs font-bold text-blue-700 dark:text-blue-300">Waiting</span>
            <p className="text-lg font-extrabold text-blue-700 dark:text-blue-400">{waitingTasks}</p>
          </div>
          <div className="p-4 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/40 space-y-1">
            <span className="text-xs font-bold text-emerald-700 dark:text-emerald-300">Completed</span>
            <p className="text-lg font-extrabold text-emerald-700 dark:text-emerald-400">{completedTasks}</p>
          </div>
        </div>
      </div>
    </div>
  );
}
