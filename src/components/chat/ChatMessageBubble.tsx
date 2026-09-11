import React, { useState } from 'react';
import { ChatMessage, MessageReaction } from '../../types';
import { Avatar } from '../common/Avatar';
import { Edit2, Trash2, Check, X, FileText, Reply, CornerDownRight, Rocket, Smile } from 'lucide-react';
import { editChatMessage, deleteChatMessage, addMessageReaction } from '../../services/chatService';
import { useAuth } from '../../context/AuthContext';

interface ChatMessageBubbleProps {
  message: ChatMessage;
  isSelf: boolean;
  onRefresh?: () => void;
  onReply?: (message: ChatMessage) => void;
}

export function ChatMessageBubble({ message, isSelf, onRefresh, onReply }: ChatMessageBubbleProps) {
  const { user } = useAuth();
  const [isEditing, setIsEditing] = useState(false);
  const [editText, setEditText] = useState(message.content);
  const [loading, setLoading] = useState(false);
  const [localReactions, setLocalReactions] = useState<MessageReaction[]>(message.reactions || []);
  const [showEmojiPicker, setShowEmojiPicker] = useState(false);

  const boostEmojis = ['👏', '🚀', '❤️', '🔥', '👍', '🎉', '💡'];

  const handleSaveEdit = async () => {
    if (!editText.trim()) return;
    setLoading(true);
    await editChatMessage(message.id, editText.trim());
    setIsEditing(false);
    setLoading(false);
    if (onRefresh) onRefresh();
  };

  const handleDelete = async () => {
    if (confirm('Delete this message?')) {
      await deleteChatMessage(message.id);
      if (onRefresh) onRefresh();
    }
  };

  const handleBoost = async (emoji: string) => {
    if (!user) return;
    const newReact = await addMessageReaction(message.id, user.id, emoji);
    setLocalReactions((prev) => [...prev, newReact]);
    setShowEmojiPicker(false);
    if (onRefresh) onRefresh();
  };

  // Group reactions by emoji
  const reactionGroups = localReactions.reduce((acc: Record<string, number>, r) => {
    acc[r.emoji] = (acc[r.emoji] || 0) + 1;
    return acc;
  }, {});

  return (
    <div className={`flex items-start gap-3 group ${isSelf ? 'flex-row-reverse' : ''}`}>
      <Avatar
        src={message.sender?.avatar_url}
        name={message.sender?.full_name || 'Member'}
        size="sm"
      />
      <div className={`space-y-1 max-w-md ${isSelf ? 'items-end text-right' : ''}`}>
        <div className="flex items-center gap-2 px-1">
          <span className="font-bold text-xs text-slate-900 dark:text-slate-100">
            {message.sender?.full_name}
          </span>
          <span className="text-[10px] text-slate-400">
            {new Date(message.created_at).toLocaleTimeString([], {
              hour: '2-digit',
              minute: '2-digit',
            })}
          </span>
          {message.is_edited && (
            <span className="text-[9px] text-slate-400 italic">(edited)</span>
          )}

          <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity ml-1">
            {onReply && (
              <button
                onClick={() => onReply(message)}
                className="text-slate-400 hover:text-brand-500 p-0.5 rounded"
                title="Reply to message"
              >
                <Reply className="w-3 h-3" />
              </button>
            )}
            {isSelf && !isEditing && (
              <>
                <button
                  onClick={() => setIsEditing(true)}
                  className="text-slate-400 hover:text-indigo-400 p-0.5"
                  title="Edit message"
                >
                  <Edit2 className="w-3 h-3" />
                </button>
                <button
                  onClick={handleDelete}
                  className="text-slate-400 hover:text-rose-400 p-0.5"
                  title="Delete message"
                >
                  <Trash2 className="w-3 h-3" />
                </button>
              </>
            )}
          </div>
        </div>

        {/* Quoted Message */}
        {message.reply_to && (
          <div className={`flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] bg-slate-100 dark:bg-slate-800/80 border-l-2 border-brand-500 text-slate-600 dark:text-slate-300 mb-1 ${isSelf ? 'justify-end' : ''}`}>
            <CornerDownRight className="w-3 h-3 text-brand-500 shrink-0" />
            <span className="font-semibold text-brand-600 dark:text-brand-400">
              {message.reply_to.sender?.full_name || 'Member'}:
            </span>
            <span className="truncate max-w-[200px]">{message.reply_to.content}</span>
          </div>
        )}

        {isEditing ? (
          <div className="flex items-center gap-2 mt-1">
            <input
              type="text"
              value={editText}
              onChange={(e) => setEditText(e.target.value)}
              className="px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-indigo-500"
            />
            <button
              onClick={handleSaveEdit}
              disabled={loading}
              className="p-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-500"
            >
              <Check className="w-3.5 h-3.5" />
            </button>
            <button
              onClick={() => setIsEditing(false)}
              className="p-1.5 bg-slate-800 text-slate-300 rounded-lg hover:bg-slate-700"
            >
              <X className="w-3.5 h-3.5" />
            </button>
          </div>
        ) : (
          <div
            className={`p-3.5 rounded-2xl text-xs leading-relaxed shadow-2xs space-y-2 ${
              isSelf
                ? 'bg-brand-600 text-white rounded-tr-none'
                : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-tl-none border border-slate-200/80 dark:border-slate-700'
            }`}
          >
            <div>{message.content}</div>

            {/* Attached file or image */}
            {message.attachment_url && (
              <div className="pt-2 border-t border-white/20 dark:border-slate-700/60">
                {message.attachment_type?.startsWith('image/') || message.attachment_url.match(/\.(jpeg|jpg|gif|png|webp)/i) ? (
                  <img
                    src={message.attachment_url}
                    alt={message.attachment_name || 'Attachment'}
                    className="max-h-48 rounded-xl object-cover border border-white/10"
                  />
                ) : (
                  <a
                    href={message.attachment_url}
                    target="_blank"
                    rel="noreferrer"
                    className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold hover:underline ${
                      isSelf ? 'bg-brand-700 text-white' : 'bg-slate-100 dark:bg-slate-700 text-brand-600 dark:text-brand-300'
                    }`}
                  >
                    <FileText className="w-3.5 h-3.5" />
                    <span>{message.attachment_name || 'View Attachment'}</span>
                  </a>
                )}
              </div>
            )}
          </div>
        )}

        {/* Basecamp Boosts (Cheers & Reactions) */}
        <div className={`flex items-center gap-1.5 flex-wrap pt-1 ${isSelf ? 'justify-end' : 'justify-start'}`}>
          {Object.entries(reactionGroups).map(([emoji, count]) => (
            <button
              key={emoji}
              onClick={() => handleBoost(emoji)}
              className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 transition-transform active:scale-95 shadow-2xs"
            >
              <span>{emoji}</span>
              <span className="text-[10px] font-bold text-slate-500 dark:text-slate-400">{count}</span>
            </button>
          ))}

          {/* Add Boost Button */}
          <div className="relative">
            <button
              onClick={() => setShowEmojiPicker(!showEmojiPicker)}
              className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
              title="Boost"
            >
              <Rocket className="w-3 h-3 text-amber-500" />
              <span>Boost</span>
            </button>

            {showEmojiPicker && (
              <div
                className={`absolute bottom-full mb-1 z-50 p-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl flex items-center gap-1 animate-in zoom-in-95 ${
                  isSelf ? 'right-0' : 'left-0'
                }`}
                onMouseLeave={() => setShowEmojiPicker(false)}
              >
                {boostEmojis.map((em) => (
                  <button
                    key={em}
                    onClick={() => handleBoost(em)}
                    className="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl text-base hover:scale-125 transition-transform"
                  >
                    {em}
                  </button>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
