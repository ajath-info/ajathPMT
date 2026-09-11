import React from 'react';
import { Pin, Lock, Megaphone, MessageSquare, Calendar } from 'lucide-react';
import { Discussion } from '../../types';
import { Avatar } from '../common/Avatar';
import { Badge } from '../common/Badge';

interface DiscussionCardProps {
  discussion: Discussion;
  onClick: (discussion: Discussion) => void;
}

export function DiscussionCard({ discussion, onClick }: DiscussionCardProps) {
  return (
    <div
      onClick={() => onClick(discussion)}
      className="group bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer space-y-4"
    >
      <div className="flex items-center justify-between gap-3">
        <div className="flex items-center gap-2 flex-wrap">
          {discussion.is_pinned && (
            <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center gap-1">
              <Pin className="w-3 h-3 fill-current" />
              Pinned
            </span>
          )}

          {discussion.is_announcement && (
            <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-50 dark:bg-brand-950/80 text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-800 flex items-center gap-1">
              <Megaphone className="w-3 h-3" />
              Announcement
            </span>
          )}

          <Badge variant="neutral" size="sm">
            {discussion.category || 'General'}
          </Badge>
        </div>

        {discussion.is_locked && (
          <span title="Discussion locked">
            <Lock className="w-4 h-4 text-slate-400" />
          </span>
        )}
      </div>

      <div className="space-y-1.5">
        <h3 className="font-extrabold text-base text-slate-900 dark:text-slate-100 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors line-clamp-1">
          {discussion.title}
        </h3>
        <p className="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
          {discussion.content}
        </p>
      </div>

      <div className="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2 text-xs text-slate-500">
        <div className="flex items-center gap-2.5">
          <Avatar
            src={discussion.author?.avatar_url}
            name={discussion.author?.full_name || 'Author'}
            size="xs"
          />
          <span className="font-bold text-slate-700 dark:text-slate-300">
            {discussion.author?.full_name}
          </span>
          <span>•</span>
          <span className="flex items-center gap-1 text-[11px]">
            <Calendar className="w-3 h-3 text-slate-400" />
            {new Date(discussion.created_at).toLocaleDateString(undefined, {
              month: 'short',
              day: 'numeric',
            })}
          </span>
        </div>

        <div className="flex items-center gap-1 font-semibold text-slate-600 dark:text-slate-400">
          <MessageSquare className="w-4 h-4 text-slate-400" />
          <span>{discussion.comments_count || 0}</span>
        </div>
      </div>
    </div>
  );
}
