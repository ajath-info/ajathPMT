import React, { useState, useRef } from 'react';
import { Send, Paperclip, AtSign, X, FileText, CornerDownRight } from 'lucide-react';
import { Button } from '../common/Button';
import { ChatMessage } from '../../types';

interface ChatInputProps {
  onSendMessage: (text: string, replyToId?: string, attachment?: { url: string; name: string; type: string }) => Promise<void>;
  placeholder?: string;
  members?: Array<{ id: string; name: string; avatar?: string }>;
  replyingTo?: ChatMessage | null;
  onCancelReply?: () => void;
}

export function ChatInput({
  onSendMessage,
  placeholder = 'Type a message...',
  members = [
    { id: 'demo-user-owner', name: 'Alex Vance' },
    { id: 'demo-user-admin', name: 'Sarah Jenkins' },
    { id: 'demo-user-member', name: 'David Chen' },
    { id: 'demo-user-client', name: 'Claire Watson' },
  ],
  replyingTo,
  onCancelReply,
}: ChatInputProps) {
  const [text, setText] = useState('');
  const [sending, setSending] = useState(false);
  const [showMentions, setShowMentions] = useState(false);
  const [mentionQuery, setMentionQuery] = useState('');
  const [attachedFile, setAttachedFile] = useState<{ url: string; name: string; type: string } | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = e.target.value;
    setText(val);

    const lastWord = val.split(' ').pop() || '';
    if (lastWord.startsWith('@')) {
      setShowMentions(true);
      setMentionQuery(lastWord.substring(1).toLowerCase());
    } else {
      setShowMentions(false);
    }
  };

  const handleSelectMention = (memberName: string) => {
    const words = text.split(' ');
    words.pop();
    const newText = [...words, `@${memberName} `].join(' ');
    setText(newText);
    setShowMentions(false);
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
      setAttachedFile({
        url: reader.result as string,
        name: file.name,
        type: file.type,
      });
    };
    reader.readAsDataURL(file);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if ((!text.trim() && !attachedFile) || sending) return;
    setSending(true);
    try {
      await onSendMessage(
        text.trim(),
        replyingTo ? replyingTo.id : undefined,
        attachedFile || undefined
      );
      setText('');
      setAttachedFile(null);
      setShowMentions(false);
      if (onCancelReply) onCancelReply();
    } catch (err) {
      console.error(err);
    } finally {
      setSending(false);
    }
  };

  const filteredMembers = members.filter((m) =>
    m.name.toLowerCase().includes(mentionQuery)
  );

  return (
    <div className="relative space-y-2">
      {/* Replying To Quote Banner */}
      {replyingTo && (
        <div className="flex items-center justify-between px-4 py-2 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 rounded-2xl text-xs text-indigo-700 dark:text-indigo-300 animate-in fade-in slide-in-from-bottom-1">
          <div className="flex items-center gap-2 truncate">
            <CornerDownRight className="w-3.5 h-3.5 text-indigo-500 shrink-0" />
            <span className="font-bold">Replying to {replyingTo.sender?.full_name || 'Member'}:</span>
            <span className="truncate italic opacity-80 max-w-sm">{replyingTo.content}</span>
          </div>
          {onCancelReply && (
            <button
              type="button"
              onClick={onCancelReply}
              className="p-1 text-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-200 rounded-lg"
              title="Cancel reply"
            >
              <X className="w-3.5 h-3.5" />
            </button>
          )}
        </div>
      )}

      {/* Attachment Preview Banner */}
      {attachedFile && (
        <div className="flex items-center justify-between px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-xl text-xs border border-slate-200 dark:border-slate-700">
          <div className="flex items-center gap-2 truncate">
            <FileText className="w-4 h-4 text-emerald-500 shrink-0" />
            <span className="font-semibold truncate text-slate-800 dark:text-slate-200">{attachedFile.name}</span>
          </div>
          <button
            type="button"
            onClick={() => setAttachedFile(null)}
            className="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
          >
            <X className="w-3.5 h-3.5" />
          </button>
        </div>
      )}

      {/* @Mentions Popup Menu */}
      {showMentions && filteredMembers.length > 0 && (
        <div className="absolute bottom-full mb-2 left-0 w-64 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl p-2 z-50 animate-fadeIn">
          <p className="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2 py-1 flex items-center gap-1">
            <AtSign className="w-3 h-3 text-indigo-400" /> Mention Team Member
          </p>
          <div className="space-y-0.5 max-h-40 overflow-y-auto">
            {filteredMembers.map((m) => (
              <button
                key={m.id}
                type="button"
                onClick={() => handleSelectMention(m.name)}
                className="w-full text-left px-2.5 py-1.5 rounded-lg text-xs text-slate-200 hover:bg-indigo-600/20 hover:text-indigo-300 font-medium transition-colors"
              >
                @{m.name}
              </button>
            ))}
          </div>
        </div>
      )}

      <form
        onSubmit={handleSubmit}
        className="p-2.5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl flex items-center gap-2 shadow-xs"
      >
        <input
          type="file"
          ref={fileInputRef}
          onChange={handleFileChange}
          className="hidden"
        />
        <button
          type="button"
          onClick={() => fileInputRef.current?.click()}
          className="p-2 rounded-xl text-slate-400 hover:text-brand-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          title="Attach file or image"
        >
          <Paperclip className="w-4 h-4" />
        </button>

        <input
          type="text"
          value={text}
          onChange={handleInputChange}
          placeholder={placeholder}
          className="flex-1 px-2 py-2 text-xs bg-transparent text-slate-900 dark:text-slate-100 focus:outline-none"
        />
        <Button
          type="submit"
          variant="primary"
          size="sm"
          isLoading={sending}
          disabled={!text.trim() && !attachedFile}
          leftIcon={<Send className="w-3.5 h-3.5" />}
        >
          Send
        </Button>
      </form>
    </div>
  );
}

