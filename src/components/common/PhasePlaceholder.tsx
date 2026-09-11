import React from 'react';
import { Layers, ArrowRight, CheckCircle2 } from 'lucide-react';
import { Button } from './Button';
import { Badge } from './Badge';

interface PhasePlaceholderProps {
  title: string;
  phase: number;
  description: string;
  features: string[];
}

export function PhasePlaceholder({ title, phase, description, features }: PhasePlaceholderProps) {
  return (
    <div className="max-w-4xl mx-auto space-y-6 animate-in fade-in duration-300">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div className="flex items-center gap-2 mb-2">
            <Badge variant="brand">Phase {phase} Module</Badge>
            <span className="text-xs text-slate-500 font-medium">WorkSphere Architecture</span>
          </div>
          <h1 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">{title}</h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">{description}</p>
        </div>
      </div>

      <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div className="flex items-center gap-3 p-4 rounded-2xl bg-brand-50/70 dark:bg-brand-950/40 border border-brand-200 dark:border-brand-800 text-brand-800 dark:text-brand-200">
          <Layers className="w-6 h-6 shrink-0 text-brand-600 dark:text-brand-400" />
          <p className="text-xs sm:text-sm font-medium">
            Phase 1 Foundation is fully operational! The database schema, auth guards, layout shell, dark mode, and profile engines are ready for Phase {phase}.
          </p>
        </div>

        <div>
          <h3 className="font-bold text-sm text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-3">
            Features Planned for Phase {phase}:
          </h3>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            {features.map((feat, idx) => (
              <div key={idx} className="flex items-center gap-2.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300">
                <CheckCircle2 className="w-4 h-4 text-brand-500 shrink-0" />
                {feat}
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
