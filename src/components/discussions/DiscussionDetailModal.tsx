import React, { useState, useEffect } from 'react';
import { X, Send, Pin, Lock, Trash2, Edit, MessageSquare, Calendar } from 'lucide-react';
import { Discussion, DiscussionComment } from '../../types';
import {
  getDiscussionComments,
  addDiscussionComment,
  updateDiscussion,
  deleteDiscussion,
} from '../../services/discussionService';
import { Avatar } from '../common/Avatar';
import { Button } from '../common/Button';
import { useAuth } from '../../context/AuthContext';

interface DiscussionDetailModalProps {
  isOpen: boolean;
  onClose: () => void;
  discussionId: string | null;
  discussionItem?: Discussion | null;
  currentUserId?: string;
  onSuccess: () => void;
}

export function DiscussionDetailModal({
  isOpen,
  onClose,
  discussionId,
  discussionItem,
  currentUserId,
  onSuccess,
}: DiscussionDetailModalProps) {
  const { userRole } = useAuth();
  const [comments, setComments] = useState<DiscussionComment[]>([]);
  const [loading, setLoading] = useState(true);
  const [newComment, setNewComment] = useState('');
  const [submittingComment, setSubmittingComment] = useState(false);

  const loadData = async () => {
    if (!discussionId) return;
    setLoading(true);
    try {
      const cList = await getDiscussionComments(discussionId);
      setComments(cList);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (isOpen && discussionId) {
      loadData();
    }
  }, [isOpen, discussionId]);

  if (!isOpen || !discussionId) return null;

  const handleAddComment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newComment.trim() || !currentUserId) return;
    setSubmittingComment(true);
    try {
      await addDiscussionComment(discussionId, currentUserId, newComment.trim());
      setNewComment('');
      loadData();
      onSuccess();
    } catch (err) {
      console.error('Failed to post comment', err);
    } finally {
      setSubmittingComment(false);
    }
  };

  const authorName = discussionItem?.author?.full_name || 'Edward';

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="relative w-full max-w-3xl max-h-[90vh] bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden">
        {/* Header */}
        <div className="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="p-2.5 rounded-2xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400">
              <MessageSquare className="w-5 h-5" />
            </div>
            <div>
              <h3 className="text-lg font-extrabold text-slate-900 dark:text-slate-100">
                Discussion Thread
              </h3>
              <p className="text-xs text-slate-500">
                Asynchronous project communication and member feedback
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Content Body */}
        <div className="p-6 flex-1 overflow-y-auto space-y-6">
          {/* Discussion Post */}
          <div className="space-y-4 pb-6 border-b border-slate-100 dark:border-slate-800">
            <div className="flex items-center gap-2">
              <h2 className="text-xl font-extrabold text-slate-900 dark:text-slate-100">
                {discussionItem?.title || 'Discussion Topic'}
              </h2>
              {userRole !== 'CLIENT' && (
                discussionItem?.is_client_visible !== false ? (
                  <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 dark:bg-amber-950/70 dark:text-amber-300">
                    <span>👁</span>
                    <span>The client can see this</span>
                  </span>
                ) : (
                  <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    <span>🔒</span>
                    <span>The client cannot see this</span>
                  </span>
                )
              )}
            </div>

            <div className="flex items-center gap-3">
              <Avatar src={discussionItem?.author?.avatar_url} name={authorName} size="sm" />
              <div>
                <p className="text-xs font-bold text-slate-900 dark:text-slate-100">
                  {authorName}
                </p>
                <p className="text-[10px] text-slate-400">
                  {discussionItem?.created_at
                    ? new Date(discussionItem.created_at).toLocaleDateString([], {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric',
                      })
                    : 'Aug 26, 2026'}
                </p>
              </div>
            </div>

            {discussionItem?.content && (
              <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 text-sm text-slate-800 dark:text-slate-200 leading-relaxed font-sans whitespace-pre-wrap">
                {discussionItem.content}
              </div>
            )}
          </div>

          {/* Comments Feed */}
          <div className="space-y-4">
            <h4 className="font-extrabold text-sm text-slate-900 dark:text-slate-100">
              Comments ({comments.length})
            </h4>

            {comments.length === 0 ? (
              <div className="py-8 text-center text-xs text-slate-400 italic">
                No comments yet. Start the conversation below.
              </div>
            ) : (
              comments.map((c) => (
                <div
                  key={c.id}
                  className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 flex items-start gap-3"
                >
                  <Avatar src={c.profile?.avatar_url} name={c.profile?.full_name || 'User'} size="xs" />
                  <div className="flex-1 space-y-1">
                    <div className="flex items-center justify-between">
                      <span className="font-bold text-xs text-slate-900 dark:text-slate-100">
                        {c.profile?.full_name}
                      </span>
                      <span className="text-[10px] text-slate-400">
                        {new Date(c.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                      </span>
                    </div>
                    <p className="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
                      {c.content}
                    </p>
                  </div>
                </div>
              ))
            )}
          </div>
        </div>

        {/* Add Comment Input Footer */}
        <form onSubmit={handleAddComment} className="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 flex items-center gap-2">
          <input
            type="text"
            value={newComment}
            onChange={(e) => setNewComment(e.target.value)}
            placeholder="Write a comment reply..."
            className="flex-1 px-4 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
          />
          <Button
            type="submit"
            variant="primary"
            size="sm"
            isLoading={submittingComment}
            disabled={!newComment.trim()}
            leftIcon={<Send className="w-3.5 h-3.5" />}
          >
            Reply
          </Button>
        </form>
      </div>
    </div>
  );
}
