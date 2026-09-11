import React, { useState, useEffect } from 'react';
import {
  Sliders,
  MessageSquare,
  CheckSquare,
  FileText,
  MessageCircle,
  Calendar,
  HelpCircle,
  Check,
  X,
  Columns,
  ExternalLink,
} from 'lucide-react';
import { Modal } from '../common/Modal';
import { Button } from '../common/Button';
import { useToast } from '../../context/ToastContext';
import { getProjectToolSettings, updateProjectToolSettings } from '../../services/projectService';

interface CustomizeToolsModalProps {
  isOpen: boolean;
  onClose: () => void;
  projectId: string;
  projectName: string;
  onSettingsUpdated?: (settings: any) => void;
}

export function CustomizeToolsModal({
  isOpen,
  onClose,
  projectId,
  projectName,
  onSettingsUpdated,
}: CustomizeToolsModalProps) {
  const { addToast } = useToast();

  const [tools, setTools] = useState({
    message_board: true,
    todos: true,
    docs_files: true,
    campfire: true,
    schedule: true,
    checkins: true,
    card_table: true,
    doors: true,
  });

  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    if (isOpen && projectId) {
      getProjectToolSettings(projectId).then((data) => {
        if (data) {
          setTools({
            message_board: data.message_board ?? true,
            todos: data.todos ?? true,
            docs_files: data.docs_files ?? true,
            campfire: data.campfire ?? true,
            schedule: data.schedule ?? true,
            checkins: data.checkins ?? true,
            card_table: data.card_table ?? true,
            doors: data.doors ?? true,
          });
        }
      });
    }
  }, [isOpen, projectId]);

  const toggleTool = (toolKey: keyof typeof tools) => {
    setTools((prev) => ({
      ...prev,
      [toolKey]: !prev[toolKey],
    }));
  };

  const handleSave = async () => {
    setIsSaving(true);
    await updateProjectToolSettings(projectId, tools);
    setIsSaving(false);
    onSettingsUpdated?.(tools);
    addToast(`Tools updated for ${projectName}`, 'success');
    onClose();
  };

  const toolConfigs = [
    {
      key: 'message_board' as const,
      name: 'Message Board',
      desc: 'Post announcements, pitch ideas, and get team feedback.',
      icon: MessageSquare,
      color: 'text-red-500 bg-red-50 dark:bg-red-950/40',
    },
    {
      key: 'todos' as const,
      name: 'To-dos',
      desc: 'Make lists of work that needs to get done, assign tasks, and set due dates.',
      icon: CheckSquare,
      color: 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/40',
    },
    {
      key: 'docs_files' as const,
      name: 'Docs & Files',
      desc: 'Share documents, images, spreadsheets, and organize them into folders.',
      icon: FileText,
      color: 'text-amber-500 bg-amber-50 dark:bg-amber-950/40',
    },
    {
      key: 'campfire' as const,
      name: 'Campfire',
      desc: 'Chat casually with the group, ask quick questions, and share fun stuff.',
      icon: MessageCircle,
      color: 'text-orange-500 bg-orange-50 dark:bg-orange-950/40',
    },
    {
      key: 'schedule' as const,
      name: 'Schedule',
      desc: 'Set milestones, track deadlines, and sync with your Google or Outlook calendar.',
      icon: Calendar,
      color: 'text-blue-500 bg-blue-50 dark:bg-blue-950/40',
    },
    {
      key: 'checkins' as const,
      name: 'Automatic Check-ins',
      desc: 'Ask recurring questions to keep everyone aligned without holding a meeting.',
      icon: HelpCircle,
      color: 'text-purple-500 bg-purple-50 dark:bg-purple-950/40',
    },
    {
      key: 'card_table' as const,
      name: 'Card Table',
      desc: 'Visual Kanban workflow columns to track tasks across stages.',
      icon: Columns,
      color: 'text-indigo-500 bg-indigo-50 dark:bg-indigo-950/40',
    },
    {
      key: 'doors' as const,
      name: 'Doors',
      desc: 'Direct links to external tools like Figma, GitHub, Notion, or Google Drive.',
      icon: ExternalLink,
      color: 'text-teal-500 bg-teal-50 dark:bg-teal-950/40',
    },
  ];

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="Customize tools" maxWidth="md">
      <div className="space-y-4 pt-1">
        <p className="text-xs text-slate-500 dark:text-slate-400">
          Turn on the tools you need for <strong className="text-slate-800 dark:text-slate-200">{projectName}</strong>, and turn off any you don't use.
        </p>

        <div className="space-y-2 max-h-[380px] overflow-y-auto pr-1">
          {toolConfigs.map((tool) => {
            const isEnabled = tools[tool.key];
            const Icon = tool.icon;

            return (
              <div
                key={tool.key}
                onClick={() => toggleTool(tool.key)}
                className={`p-3 rounded-2xl border transition-all cursor-pointer flex items-center justify-between select-none ${
                  isEnabled
                    ? 'border-blue-500 bg-blue-50/40 dark:bg-blue-950/30'
                    : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40 opacity-70'
                }`}
              >
                <div className="flex items-center gap-3 min-w-0 pr-3">
                  <div className={`w-9 h-9 rounded-xl flex items-center justify-center shrink-0 ${tool.color}`}>
                    <Icon className="w-4 h-4" />
                  </div>
                  <div className="min-w-0">
                    <span className="font-bold text-xs text-slate-900 dark:text-slate-100 block truncate">
                      {tool.name}
                    </span>
                    <span className="text-[11px] text-slate-500 dark:text-slate-400 block line-clamp-1">
                      {tool.desc}
                    </span>
                  </div>
                </div>

                {/* Switch Toggle */}
                <div
                  className={`w-10 h-6 rounded-full transition-colors p-0.5 shrink-0 flex items-center ${
                    isEnabled ? 'bg-[#0c66e4] justify-end' : 'bg-slate-300 dark:bg-slate-700 justify-start'
                  }`}
                >
                  <div className="w-5 h-5 rounded-full bg-white shadow-xs" />
                </div>
              </div>
            );
          })}
        </div>

        <div className="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <Button variant="outline" size="sm" onClick={onClose} disabled={isSaving}>
            Cancel
          </Button>
          <Button variant="primary" size="sm" onClick={handleSave} disabled={isSaving}>
            {isSaving ? 'Saving...' : 'Save changes'}
          </Button>
        </div>
      </div>
    </Modal>
  );
}
