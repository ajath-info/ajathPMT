import React, { useState } from 'react';
import { X, Sparkles, CheckCircle2, ArrowRight, ArrowLeft, Layout, CheckSquare, MessageSquare, Shield, Rocket } from 'lucide-react';
import { completeUserOnboarding } from '../../services/onboardingService';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';

interface OnboardingWizardModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export const OnboardingWizardModal: React.FC<OnboardingWizardModalProps> = ({
  isOpen,
  onClose,
}) => {
  const { user } = useAuth();
  const { addToast } = useToast();
  const [step, setStep] = useState(1);
  const [loading, setLoading] = useState(false);

  if (!isOpen) return null;

  const handleComplete = async () => {
    if (!user) return;
    setLoading(true);
    try {
      await completeUserOnboarding(user.id);
      addToast('Onboarding complete! Welcome to Ajath PMT.', 'success');
      onClose();
    } catch (e: any) {
      addToast('Failed to complete onboarding', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md animate-fadeIn">
      <div className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col">
        {/* Header */}
        <div className="p-6 border-b border-slate-800/80 flex items-center justify-between bg-slate-900/50">
          <div className="flex items-center gap-3">
            <div className="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
              <Rocket className="w-6 h-6" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-slate-100">Welcome to Ajath PMT</h2>
              <p className="text-xs text-slate-400">Step {step} of 3 — Quick Setup & Feature Tour</p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-2 text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 rounded-lg transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Wizard Body */}
        <div className="p-8 flex-1 overflow-y-auto">
          {step === 1 && (
            <div className="space-y-6 animate-fadeIn">
              <div className="text-center max-w-md mx-auto space-y-3">
                <div className="w-16 h-16 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 text-indigo-400 flex items-center justify-center mx-auto text-2xl font-bold">
                  ⚡
                </div>
                <h3 className="text-2xl font-bold text-slate-100">Modern Project Collaboration</h3>
                <p className="text-sm text-slate-400">
                  Ajath PMT provides a complete suite of project tools inspired by modern high-productivity team platforms. Everything your team needs in one workspace.
                </p>
              </div>

              <div className="grid grid-cols-2 gap-3 pt-4">
                <div className="p-4 rounded-xl bg-slate-950/50 border border-slate-800/80 space-y-1">
                  <MessageSquare className="w-5 h-5 text-indigo-400" />
                  <p className="font-semibold text-sm text-slate-200">Message Board & Chat</p>
                  <p className="text-xs text-slate-400">Pitch ideas, post updates, and chat live in project Campfire.</p>
                </div>
                <div className="p-4 rounded-xl bg-slate-950/50 border border-slate-800/80 space-y-1">
                  <CheckSquare className="w-5 h-5 text-emerald-400" />
                  <p className="font-semibold text-sm text-slate-200">To-dos & Kanban</p>
                  <p className="text-xs text-slate-400">Manage tasks with sublists, due dates, recurring rules & boards.</p>
                </div>
              </div>
            </div>
          )}

          {step === 2 && (
            <div className="space-y-6 animate-fadeIn">
              <h3 className="text-xl font-bold text-slate-100">Key Features at a Glance</h3>
              <div className="space-y-3">
                <div className="flex items-start gap-3 p-3.5 rounded-xl bg-slate-950/40 border border-slate-800/80">
                  <CheckCircle2 className="w-5 h-5 text-indigo-400 shrink-0 mt-0.5" />
                  <div>
                    <p className="font-semibold text-sm text-slate-200">Document Version History</p>
                    <p className="text-xs text-slate-400">Track and restore previous document versions seamlessly.</p>
                  </div>
                </div>

                <div className="flex items-start gap-3 p-3.5 rounded-xl bg-slate-950/40 border border-slate-800/80">
                  <CheckCircle2 className="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" />
                  <div>
                    <p className="font-semibold text-sm text-slate-200">Automated Daily Check-ins</p>
                    <p className="text-xs text-slate-400">Automatic recurring questions to keep team members in sync.</p>
                  </div>
                </div>

                <div className="flex items-start gap-3 p-3.5 rounded-xl bg-slate-950/40 border border-slate-800/80">
                  <CheckCircle2 className="w-5 h-5 text-amber-400 shrink-0 mt-0.5" />
                  <div>
                    <p className="font-semibold text-sm text-slate-200">Personal Productivity Suite</p>
                    <p className="text-xs text-slate-400">Personal scratchpad notes, quick bookmarks, and recently viewed history.</p>
                  </div>
                </div>
              </div>
            </div>
          )}

          {step === 3 && (
            <div className="text-center max-w-md mx-auto space-y-6 animate-fadeIn py-4">
              <div className="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto">
                <Sparkles className="w-8 h-8" />
              </div>
              <div>
                <h3 className="text-2xl font-bold text-slate-100">You're All Set!</h3>
                <p className="text-sm text-slate-400 mt-2">
                  Jump right into your active projects or create a new project workspace using templates.
                </p>
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="p-6 border-t border-slate-800/80 bg-slate-900/50 flex items-center justify-between">
          {step > 1 ? (
            <button
              onClick={() => setStep(step - 1)}
              className="px-4 py-2 text-sm font-medium text-slate-400 hover:text-slate-200 flex items-center gap-1.5 transition-colors"
            >
              <ArrowLeft className="w-4 h-4" />
              Back
            </button>
          ) : <div />}

          {step < 3 ? (
            <button
              onClick={() => setStep(step + 1)}
              className="px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm flex items-center gap-2 shadow-lg shadow-indigo-600/20 transition-all"
            >
              Next
              <ArrowRight className="w-4 h-4" />
            </button>
          ) : (
            <button
              onClick={handleComplete}
              disabled={loading}
              className="px-6 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm flex items-center gap-2 shadow-lg shadow-emerald-600/20 transition-all"
            >
              {loading ? 'Finishing...' : 'Get Started'}
              <CheckCircle2 className="w-4 h-4" />
            </button>
          )}
        </div>
      </div>
    </div>
  );
};
