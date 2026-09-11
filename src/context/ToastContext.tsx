import React, { createContext, useContext, useState, useCallback } from 'react';
import { CheckCircle2, AlertCircle, Info, X } from 'lucide-react';

export interface ToastItem {
  id: string;
  title: string;
  description?: string;
  type: 'success' | 'error' | 'info';
  onUndo?: () => void;
  duration?: number;
}

interface ToastContextType {
  addToast: (toastOrTitle: Omit<ToastItem, 'id'> | string, type?: 'success' | 'error' | 'info') => void;
  removeToast: (id: string) => void;
}

const ToastContext = createContext<ToastContextType | undefined>(undefined);

export function ToastProvider({ children }: { children: React.ReactNode }) {
  const [toasts, setToasts] = useState<ToastItem[]>([]);

  const removeToast = useCallback((id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  const addToast = useCallback(
    (toastOrTitle: Omit<ToastItem, 'id'> | string, type: 'success' | 'error' | 'info' = 'info') => {
      const id = `toast-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`;
      const toastInput: Omit<ToastItem, 'id'> =
        typeof toastOrTitle === 'string'
          ? { title: toastOrTitle, type }
          : toastOrTitle;

      const newToast: ToastItem = { ...toastInput, id };
      setToasts((prev) => [...prev, newToast]);

      const duration = toastInput.duration || (toastInput.onUndo ? 7000 : 4000);
      setTimeout(() => {
        removeToast(id);
      }, duration);
    },
    [removeToast]
  );


  return (
    <ToastContext.Provider value={{ addToast, removeToast }}>
      {children}
      {/* Toast Render Overlay */}
      <div className="fixed bottom-5 right-5 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none">
        {toasts.map((toast) => (
          <div
            key={toast.id}
            className={`pointer-events-auto p-4 rounded-2xl shadow-xl border flex items-start gap-3 transition-all duration-300 animate-in slide-in-from-bottom-5 ${
              toast.type === 'success'
                ? 'bg-slate-900 text-white border-slate-800'
                : toast.type === 'error'
                ? 'bg-rose-950 text-rose-100 border-rose-800'
                : 'bg-slate-900 text-white border-slate-800'
            }`}
          >
            {toast.type === 'success' && <CheckCircle2 className="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" />}
            {toast.type === 'error' && <AlertCircle className="w-5 h-5 text-rose-400 shrink-0 mt-0.5" />}
            {toast.type === 'info' && <Info className="w-5 h-5 text-brand-400 shrink-0 mt-0.5" />}

            <div className="flex-1 min-w-0">
              <p className="text-xs font-bold leading-snug">{toast.title}</p>
              {toast.description && <p className="text-[11px] opacity-80 mt-0.5 line-clamp-2">{toast.description}</p>}
            </div>

            {toast.onUndo && (
              <button
                onClick={() => {
                  toast.onUndo?.();
                  removeToast(toast.id);
                }}
                className="px-2 py-0.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-[10px] font-extrabold uppercase tracking-wider shrink-0"
              >
                Undo
              </button>
            )}

            <button
              onClick={() => removeToast(toast.id)}
              className="text-slate-400 hover:text-white transition-colors"
            >
              <X className="w-4 h-4" />
            </button>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}

export function useToast() {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error('useToast must be used within ToastProvider');
  }
  return context;
}
