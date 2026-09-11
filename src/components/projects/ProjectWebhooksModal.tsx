import React, { useState } from 'react';
import { Webhook, Plus, Trash2, Check, ExternalLink, Send } from 'lucide-react';
import { Modal } from '../common/Modal';
import { Button } from '../common/Button';
import { useToast } from '../../context/ToastContext';

interface ProjectWebhooksModalProps {
  isOpen: boolean;
  onClose: () => void;
  projectId: string;
  projectName: string;
}

interface WebhookConfig {
  id: string;
  url: string;
  name: string;
  events: string[];
}

export function ProjectWebhooksModal({
  isOpen,
  onClose,
  projectId,
  projectName,
}: ProjectWebhooksModalProps) {
  const { addToast } = useToast();

  const [webhooks, setWebhooks] = useState<WebhookConfig[]>([
    {
      id: 'wh-1',
      name: 'Slack Alerts Integration',
      url: 'https://hooks.slack.com/services/T000/B000/XXXXX',
      events: ['Todos', 'Messages', 'Docs'],
    },
  ]);

  const [newUrl, setNewUrl] = useState('');
  const [newName, setNewName] = useState('');
  const [isAdding, setIsAdding] = useState(false);

  const handleAddWebhook = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newUrl.trim()) return;

    const newHook: WebhookConfig = {
      id: `wh-${Date.now()}`,
      name: newName.trim() || 'Custom Webhook',
      url: newUrl.trim(),
      events: ['Todos', 'Messages', 'Docs', 'Campfire'],
    };

    setWebhooks((prev) => [...prev, newHook]);
    setNewUrl('');
    setNewName('');
    setIsAdding(false);
    addToast('Webhook added successfully!', 'success');
  };

  const handleTestWebhook = (wh: WebhookConfig) => {
    addToast(`Test payload dispatched to ${wh.name} (200 OK)`, 'info');
  };

  const handleDeleteWebhook = (id: string) => {
    setWebhooks((prev) => prev.filter((w) => w.id !== id));
    addToast('Webhook removed', 'info');
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title={`Webhooks for ${projectName}`} maxWidth="md">
      <div className="space-y-4 pt-1">
        <p className="text-xs text-slate-500 dark:text-slate-400">
          Ajath PMT can send an HTTP POST payload to your server or chatbot whenever activity happens in this project.
        </p>

        {/* Existing Webhooks */}
        <div className="space-y-2.5 max-h-60 overflow-y-auto pr-1">
          {webhooks.map((wh) => (
            <div
              key={wh.id}
              className="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/60 space-y-2 shadow-2xs"
            >
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full bg-emerald-500" />
                  <span className="font-bold text-xs text-slate-900 dark:text-slate-100">
                    {wh.name}
                  </span>
                </div>
                <div className="flex items-center gap-1.5">
                  <button
                    type="button"
                    onClick={() => handleTestWebhook(wh)}
                    className="px-2 py-0.5 text-[11px] font-semibold text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40 rounded-md cursor-pointer"
                  >
                    Send test
                  </button>
                  <button
                    type="button"
                    onClick={() => handleDeleteWebhook(wh.id)}
                    className="p-1 text-slate-400 hover:text-rose-600 cursor-pointer"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>

              <div className="text-[11px] font-mono text-slate-500 truncate bg-slate-50 dark:bg-slate-900 px-2 py-1 rounded-lg">
                {wh.url}
              </div>
            </div>
          ))}
        </div>

        {/* Add New Webhook Form */}
        {!isAdding ? (
          <button
            type="button"
            onClick={() => setIsAdding(true)}
            className="w-full py-2.5 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 text-xs font-bold text-blue-600 hover:bg-blue-50/50 dark:hover:bg-slate-800 flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Add a new webhook</span>
          </button>
        ) : (
          <form onSubmit={handleAddWebhook} className="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
            <div className="text-xs font-bold text-slate-900 dark:text-slate-100">
              New Webhook Endpoint
            </div>
            <div>
              <input
                type="text"
                placeholder="Name (e.g. Discord Bot, Zapier Sync)"
                value={newName}
                onChange={(e) => setNewName(e.target.value)}
                className="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"
              />
            </div>
            <div>
              <input
                type="url"
                required
                placeholder="Payload URL (https://...)"
                value={newUrl}
                onChange={(e) => setNewUrl(e.target.value)}
                className="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono"
              />
            </div>
            <div className="flex items-center justify-end gap-2 pt-1">
              <Button variant="outline" size="sm" type="button" onClick={() => setIsAdding(false)}>
                Cancel
              </Button>
              <Button variant="primary" size="sm" type="submit">
                Save Webhook
              </Button>
            </div>
          </form>
        )}

        <div className="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
          <Button variant="primary" size="sm" onClick={onClose}>
            Done
          </Button>
        </div>
      </div>
    </Modal>
  );
}
