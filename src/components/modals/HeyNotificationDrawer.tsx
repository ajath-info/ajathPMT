import React, { useState, useEffect } from 'react';
import { X, Bell, CheckCircle2, MessageSquare, ArrowRight, Sparkles } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import {
  getUserNotifications,
  markAllNotificationsAsRead,
  markNotificationAsRead,
} from '../../services/notificationService';
import { NotificationItem } from '../../types';

interface HeyNotificationDrawerProps {
  isOpen: boolean;
  onClose: () => void;
}

export function HeyNotificationDrawer({ isOpen, onClose }: HeyNotificationDrawerProps) {
  const navigate = useNavigate();
  const { user } = useAuth();
  const { currentOrganization } = useOrganization();

  const [notifications, setNotifications] = useState<NotificationItem[]>([]);
  const [loading, setLoading] = useState(false);

  const loadNotifications = async () => {
    if (!user) return;
    setLoading(true);
    try {
      const list = await getUserNotifications(user.id, currentOrganization?.id);
      setNotifications(list);
    } catch (err) {
      console.error('Failed to load notifications in Hey drawer', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (isOpen) {
      loadNotifications();
    }
  }, [isOpen, user?.id, currentOrganization?.id]);

  if (!isOpen) return null;

  const handleMarkAllRead = async () => {
    if (!user) return;
    await markAllNotificationsAsRead(user.id, currentOrganization?.id);
    setNotifications((prev) => prev.map((n) => ({ ...n, is_read: true })));
  };

  const handleItemClick = async (item: NotificationItem) => {
    if (!item.is_read) {
      await markNotificationAsRead(item.id);
    }
    onClose();
    if (item.link_url) {
      navigate(item.link_url);
    } else {
      navigate('/notifications');
    }
  };

  const unreadCount = notifications.filter((n) => !n.is_read).length;

  return (
    <div className="fixed inset-0 z-50 overflow-hidden animate-in fade-in duration-200">
      <div className="absolute inset-0 bg-slate-900/40 backdrop-blur-xs" onClick={onClose} />
      <div className="absolute inset-y-0 right-0 max-w-full flex pl-10">
        <div className="w-screen max-w-md bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col">
          {/* Header */}
          <div className="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 flex items-center justify-center font-bold">
                <Bell className="w-4 h-4" />
              </div>
              <div>
                <h2 className="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                  Hey!
                  {unreadCount > 0 && (
                    <span className="text-[10px] bg-rose-500 text-white font-black px-1.5 py-0.2 rounded-full">
                      {unreadCount} new
                    </span>
                  )}
                </h2>
                <p className="text-[11px] text-slate-400">
                  {currentOrganization?.name || 'Workspace'} alerts & updates
                </p>
              </div>
            </div>
            <div className="flex items-center gap-1">
              {unreadCount > 0 && (
                <button
                  onClick={handleMarkAllRead}
                  className="text-[11px] font-bold text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 px-2 py-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                >
                  Mark read
                </button>
              )}
              <button
                onClick={onClose}
                className="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>
          </div>

          {/* List */}
          <div className="flex-1 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
            {loading ? (
              <div className="py-12 text-center text-xs text-slate-400 animate-pulse">
                Loading notifications...
              </div>
            ) : notifications.length === 0 ? (
              <div className="py-16 text-center px-4 space-y-2">
                <Sparkles className="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto" />
                <p className="text-sm font-semibold text-slate-600 dark:text-slate-300">
                  You're all caught up!
                </p>
                <p className="text-xs text-slate-400">
                  No notifications in {currentOrganization?.name || 'this workspace'}.
                </p>
              </div>
            ) : (
              notifications.map((item) => (
                <div
                  key={item.id}
                  onClick={() => handleItemClick(item)}
                  className={`p-4 cursor-pointer transition-colors flex items-start gap-3 group ${
                    !item.is_read
                      ? 'bg-amber-50/40 dark:bg-amber-950/15 hover:bg-amber-50/70 dark:hover:bg-amber-950/30'
                      : 'hover:bg-slate-50 dark:hover:bg-slate-800/40'
                  }`}
                >
                  <div className="mt-1">
                    {!item.is_read ? (
                      <span className="w-2.5 h-2.5 rounded-full bg-rose-500 block ring-4 ring-rose-100 dark:ring-rose-950/50" />
                    ) : (
                      <span className="w-2.5 h-2.5 rounded-full bg-slate-300 dark:bg-slate-700 block" />
                    )}
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-xs font-bold text-slate-900 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                      {item.title}
                    </p>
                    <p className="text-xs text-slate-600 dark:text-slate-400 mt-1 line-clamp-2">
                      {item.message}
                    </p>
                    <span className="text-[10px] text-slate-400 block mt-1.5 font-medium">
                      {new Date(item.created_at).toLocaleDateString(undefined, {
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                      })}
                    </span>
                  </div>
                </div>
              ))
            )}
          </div>

          {/* Footer */}
          <div className="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/80 flex items-center justify-between">
            <span className="text-xs text-slate-400">All notifications</span>
            <button
              onClick={() => {
                onClose();
                navigate('/notifications');
              }}
              className="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 cursor-pointer"
            >
              Notifications Center <ArrowRight className="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
