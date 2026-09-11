import React from 'react';
import { Droplet, Sun, Moon, Laptop, Check } from 'lucide-react';
import { Modal } from '../common/Modal';
import { useTheme } from '../../context/ThemeContext';
import { Button } from '../common/Button';

interface ThemeCustomizerModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export function ThemeCustomizerModal({ isOpen, onClose }: ThemeCustomizerModalProps) {
  const { theme, setTheme } = useTheme();

  const themes = [
    { id: 'light', name: 'Ajath Clean (Light)', icon: Sun, desc: 'Signature warm off-white canvas with crisp cards' },
    { id: 'dark', name: 'Ajath Night (Dark)', icon: Moon, desc: 'Sleek dark slate aesthetic for low-light focus' },
    { id: 'system', name: 'Match System Preference', icon: Laptop, desc: 'Automatically adapt to your operating system theme' },
  ];

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="Customize Theme & Appearance" maxWidth="sm">
      <div className="space-y-4 pt-1">
        <p className="text-xs text-slate-500">
          Personalize the look and feel of your Ajath PMT workspace.
        </p>

        <div className="space-y-2">
          {themes.map((t) => {
            const Icon = t.icon;
            const isSelected = theme === t.id;
            return (
              <button
                key={t.id}
                onClick={() => setTheme(t.id as any)}
                className={`w-full p-3 rounded-2xl border text-left flex items-start gap-3 transition-all ${
                  isSelected
                    ? 'border-brand-600 bg-brand-50/50 dark:bg-brand-950/30 text-brand-900 dark:text-brand-100 ring-2 ring-brand-500/20'
                    : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200'
                }`}
              >
                <div
                  className={`w-8 h-8 rounded-xl flex items-center justify-center shrink-0 ${
                    isSelected
                      ? 'bg-brand-600 text-white'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-500'
                  }`}
                >
                  <Icon className="w-4 h-4" />
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center justify-between">
                    <span className="text-xs font-bold">{t.name}</span>
                    {isSelected && <Check className="w-4 h-4 text-brand-600 dark:text-brand-400" />}
                  </div>
                  <p className="text-[11px] text-slate-500 mt-0.5">{t.desc}</p>
                </div>
              </button>
            );
          })}
        </div>

        <div className="pt-2 flex justify-end">
          <Button variant="primary" size="sm" onClick={onClose}>
            Done
          </Button>
        </div>
      </div>
    </Modal>
  );
}
