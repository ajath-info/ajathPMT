import React from 'react';
import { HelpCircle, BookOpen, Keyboard, MessageSquare, LifeBuoy, ExternalLink } from 'lucide-react';
import { Modal } from '../common/Modal';
import { Button } from '../common/Button';

interface SupportModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export function SupportModal({ isOpen, onClose }: SupportModalProps) {
  const shortcuts = [
    { keys: ['Shift', 'J'], action: 'Jump to a project or person' },
    { keys: ['Ctrl', 'K'], action: 'Global Search' },
    { keys: ['Esc'], action: 'Close modal or active window' },
  ];

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="Ajath PMT Help & Support" maxWidth="md">
      <div className="space-y-5 pt-1">
        <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-100 border border-emerald-200 dark:border-emerald-800">
          <LifeBuoy className="w-6 h-6 text-emerald-600 shrink-0" />
          <div className="text-xs">
            <p className="font-bold">Need assistance with Ajath PMT?</p>
            <p className="text-slate-600 dark:text-slate-300">
              We're here to help you get the most out of your team's workspace.
            </p>
          </div>
        </div>

        {/* Shortcuts Cheat Sheet */}
        <div className="space-y-2">
          <h4 className="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
            <Keyboard className="w-3.5 h-3.5" /> Keyboard Shortcuts
          </h4>
          <div className="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3 divide-y divide-slate-100 dark:divide-slate-800">
            {shortcuts.map((sc, idx) => (
              <div key={idx} className="flex items-center justify-between py-2 text-xs">
                <span className="text-slate-600 dark:text-slate-400 font-medium">{sc.action}</span>
                <div className="flex items-center gap-1">
                  {sc.keys.map((k) => (
                    <kbd
                      key={k}
                      className="px-2 py-0.5 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs text-[10px] font-bold text-slate-700 dark:text-slate-300"
                    >
                      {k}
                    </kbd>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Guides & Resources */}
        <div className="grid grid-cols-2 gap-3">
          <a
            href="https://ajath.com/help"
            target="_blank"
            rel="noopener noreferrer"
            className="p-3 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-brand-500 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300 group"
          >
            <span className="flex items-center gap-2">
              <BookOpen className="w-4 h-4 text-brand-600" />
              Guides & Docs
            </span>
            <ExternalLink className="w-3.5 h-3.5 text-slate-400 group-hover:text-brand-600" />
          </a>

          <a
            href="mailto:support@ajath.com"
            className="p-3 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-brand-500 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300 group"
          >
            <span className="flex items-center gap-2">
              <MessageSquare className="w-4 h-4 text-emerald-600" />
              Contact Support
            </span>
            <ExternalLink className="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-600" />
          </a>
        </div>

        <div className="pt-2 flex justify-end">
          <Button variant="outline" size="sm" onClick={onClose}>
            Close
          </Button>
        </div>
      </div>
    </Modal>
  );
}
