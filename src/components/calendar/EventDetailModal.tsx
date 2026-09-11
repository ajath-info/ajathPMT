import React, { useState, useEffect } from 'react';
import { X, Calendar, Clock, MessageSquare, Users, Check, AlertCircle, Trash2, Download, Send } from 'lucide-react';
import { CalendarEvent, CalendarEventAttendee, CalendarEventComment } from '../../types';
import {
  getEventAttendees,
  respondEventRsvp,
  getEventComments,
  addEventComment,
  deleteCalendarEvent,
  downloadCalendarIcs,
} from '../../services/calendarService';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { Button } from '../common/Button';

interface EventDetailModalProps {
  isOpen: boolean;
  onClose: () => void;
  event: CalendarEvent | null;
  onEventUpdated?: () => void;
  onEventDeleted?: () => void;
}

export function EventDetailModal({
  isOpen,
  onClose,
  event,
  onEventUpdated,
  onEventDeleted,
}: EventDetailModalProps) {
  const { user } = useAuth();
  const { addToast } = useToast();

  const [attendees, setAttendees] = useState<CalendarEventAttendee[]>([]);
  const [comments, setComments] = useState<CalendarEventComment[]>([]);
  const [newComment, setNewComment] = useState('');
  const [isSubmittingComment, setIsSubmittingComment] = useState(false);
  const [myRsvp, setMyRsvp] = useState<'PENDING' | 'ACCEPTED' | 'DECLINED' | 'TENTATIVE'>('PENDING');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!event || !isOpen) return;
    const eventId = event.id;

    let isMounted = true;
    async function loadData() {
      setLoading(true);
      try {
        const [loadedAttendees, loadedComments] = await Promise.all([
          getEventAttendees(eventId),
          getEventComments(eventId),
        ]);
        if (isMounted) {
          setAttendees(loadedAttendees);
          setComments(loadedComments);
          const currentAttendee = loadedAttendees.find((a) => a.user_id === user?.id);
          if (currentAttendee) {
            setMyRsvp(currentAttendee.rsvp_status);
          } else {
            setMyRsvp('PENDING');
          }
        }
      } catch (err) {
        console.error(err);
      } finally {
        if (isMounted) setLoading(false);
      }
    }

    loadData();
    return () => {
      isMounted = false;
    };
  }, [event, isOpen, user?.id]);

  if (!isOpen || !event) return null;

  const handleRsvp = async (status: 'ACCEPTED' | 'DECLINED' | 'TENTATIVE') => {
    if (!user) return;
    try {
      await respondEventRsvp(event.id, user.id, status);
      setMyRsvp(status);
      const updated = await getEventAttendees(event.id);
      setAttendees(updated);
      addToast(`RSVP updated: ${status.toLowerCase()}`, 'success');
      onEventUpdated?.();
    } catch {
      addToast('Failed to update RSVP', 'error');
    }
  };

  const handlePostComment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newComment.trim() || !user) return;

    setIsSubmittingComment(true);
    try {
      const added = await addEventComment(event.id, user.id, newComment.trim());
      setComments((prev) => [...prev, added]);
      setNewComment('');
      addToast('Comment added', 'success');
    } catch {
      addToast('Failed to post comment', 'error');
    } finally {
      setIsSubmittingComment(false);
    }
  };

  const handleDelete = async () => {
    if (window.confirm('Are you sure you want to delete this event?')) {
      try {
        await deleteCalendarEvent(event.id);
        addToast('Event deleted', 'info');
        onEventDeleted?.();
        onClose();
      } catch {
        addToast('Failed to delete event', 'error');
      }
    }
  };

  const startDateFormatted = new Date(event.start_at).toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
  const endDateFormatted = new Date(event.end_at).toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="relative w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col max-h-[90vh]">
        {/* Header */}
        <div className="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0">
          <div className="flex items-center gap-3">
            <div
              className="w-4 h-4 rounded-full shrink-0"
              style={{ backgroundColor: event.color || '#3b82f6' }}
            />
            <div>
              <h3 className="text-lg font-extrabold text-slate-900 dark:text-slate-100 line-clamp-1">
                {event.title}
              </h3>
              <p className="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
                <Calendar className="w-3.5 h-3.5 text-slate-400" />
                <span>
                  {startDateFormatted}
                  {startDateFormatted !== endDateFormatted && ` – ${endDateFormatted}`}
                </span>
                {event.all_day && (
                  <span className="px-1.5 py-0.2 bg-slate-100 dark:bg-slate-800 text-[10px] rounded font-bold text-slate-600 dark:text-slate-300">
                    All-day
                  </span>
                )}
              </p>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <button
              onClick={() => downloadCalendarIcs([event], `${event.title.replace(/\s+/g, '_')}.ics`)}
              className="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
              title="Download iCal (.ics)"
            >
              <Download className="w-4 h-4" />
            </button>
            <button
              onClick={handleDelete}
              className="p-2 rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30"
              title="Delete event"
            >
              <Trash2 className="w-4 h-4" />
            </button>
            <button
              onClick={onClose}
              className="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
            >
              <X className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* Body Content */}
        <div className="p-6 overflow-y-auto space-y-6 flex-1">
          {/* Description */}
          {event.description && (
            <div className="bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">
              <p className="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed">
                {event.description}
              </p>
            </div>
          )}

          {/* RSVP Status / Actions (Basecamp 4 Parity) */}
          <div className="bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40 p-4 rounded-2xl space-y-3">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                <Users className="w-4 h-4 text-blue-600" />
                Will you attend?
              </span>
              <span className="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300">
                Status: {myRsvp}
              </span>
            </div>
            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={() => handleRsvp('ACCEPTED')}
                className={`flex-1 py-1.5 px-3 rounded-xl text-xs font-bold transition-all ${
                  myRsvp === 'ACCEPTED'
                    ? 'bg-emerald-600 text-white shadow-xs'
                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:bg-emerald-50 hover:text-emerald-700'
                }`}
              >
                ✓ Going
              </button>
              <button
                type="button"
                onClick={() => handleRsvp('TENTATIVE')}
                className={`flex-1 py-1.5 px-3 rounded-xl text-xs font-bold transition-all ${
                  myRsvp === 'TENTATIVE'
                    ? 'bg-amber-500 text-white shadow-xs'
                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:bg-amber-50 hover:text-amber-700'
                }`}
              >
                ? Maybe
              </button>
              <button
                type="button"
                onClick={() => handleRsvp('DECLINED')}
                className={`flex-1 py-1.5 px-3 rounded-xl text-xs font-bold transition-all ${
                  myRsvp === 'DECLINED'
                    ? 'bg-red-600 text-white shadow-xs'
                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:bg-red-50 hover:text-red-700'
                }`}
              >
                ✕ Decline
              </button>
            </div>

            {/* Attendees list */}
            {attendees.length > 0 && (
              <div className="pt-2 border-t border-blue-100/60 dark:border-blue-900/30 flex flex-wrap gap-2 items-center">
                <span className="text-[11px] text-slate-500">Attendees ({attendees.length}):</span>
                {attendees.map((att) => (
                  <span
                    key={att.id}
                    className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300"
                  >
                    <span>{att.profile?.full_name || att.user_id.slice(0, 8)}</span>
                    <span
                      className={`w-1.5 h-1.5 rounded-full ${
                        att.rsvp_status === 'ACCEPTED'
                          ? 'bg-emerald-500'
                          : att.rsvp_status === 'DECLINED'
                          ? 'bg-red-500'
                          : 'bg-amber-400'
                      }`}
                    />
                  </span>
                ))}
              </div>
            )}
          </div>

          {/* Event Discussion & Comments */}
          <div className="space-y-3 pt-2">
            <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <MessageSquare className="w-3.5 h-3.5 text-slate-400" />
              Event Discussion ({comments.length})
            </h4>

            {comments.length === 0 ? (
              <p className="text-xs text-slate-400 italic py-2">No comments yet. Start the conversation below.</p>
            ) : (
              <div className="space-y-2.5 max-h-48 overflow-y-auto pr-1">
                {comments.map((c) => (
                  <div
                    key={c.id}
                    className="bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-100 dark:border-slate-800 space-y-1"
                  >
                    <div className="flex items-center justify-between text-[11px]">
                      <span className="font-bold text-slate-800 dark:text-slate-200">
                        {c.profile?.full_name || 'Team Member'}
                      </span>
                      <span className="text-slate-400 text-[10px]">
                        {new Date(c.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                      </span>
                    </div>
                    <p className="text-xs text-slate-600 dark:text-slate-300 whitespace-pre-wrap">
                      {c.content}
                    </p>
                  </div>
                ))}
              </div>
            )}

            {/* Add Comment Input */}
            <form onSubmit={handlePostComment} className="flex gap-2 pt-2">
              <input
                type="text"
                value={newComment}
                onChange={(e) => setNewComment(e.target.value)}
                placeholder="Write a comment or note about this event..."
                className="flex-1 text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
              />
              <button
                type="submit"
                disabled={!newComment.trim() || isSubmittingComment}
                className="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1 cursor-pointer"
              >
                <Send className="w-3.5 h-3.5" />
                <span>Post</span>
              </button>
            </form>
          </div>
        </div>

        {/* Footer */}
        <div className="px-6 py-3 border-t border-slate-100 dark:border-slate-800 flex justify-end bg-slate-50/50 dark:bg-slate-800/30 shrink-0">
          <Button type="button" variant="outline" onClick={onClose} size="sm">
            Close
          </Button>
        </div>
      </div>
    </div>
  );
}
