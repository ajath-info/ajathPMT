import React, { useState, useEffect } from 'react';
import { Bell, CheckCheck, Filter, ArrowRight } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { NotificationItem } from '../../types';
import {
  getUserNotifications,
  markNotificationAsRead,
  markAllNotificationsAsRead,
} from '../../services/notificationService';
import { Avatar } from '../../components/common/Avatar';
import { Button } from '../../components/common/Button';
import { Badge } from '../../components/common/Badge';

export function NotificationsPage() {
  const { user } = useAuth();
  const { currentOrganization } = useOrganization();
  const navigate = useNavigate();

  const [notifications, setNotifications] = useState<NotificationItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [filterMode, setFilterMode] = useState<'ALL' | 'UNREAD'>('ALL');

  const loadData = async () => {
    if (!user) return;
    setLoading(true);
    try {
      const list = await getUserNotifications(user.id, currentOrganization?.id);
      setNotifications(list);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [user?.id, currentOrganization?.id]);

  const handleMarkRead = async (id: string, linkUrl?: string) => {
    await markNotificationAsRead(id);
    loadData();
    if (linkUrl) {
      navigate(linkUrl);
    }
  };

  const handleMarkAllRead = async () => {
    if (!user) return;
    await markAllNotificationsAsRead(user.id, currentOrganization?.id);
    loadData();
  };

  const filteredNotifs = notifications.filter((n) => {
    if (filterMode === 'UNREAD') return !n.is_read;
    return true;
  });

  const unreadCount = notifications.filter((n) => !n.is_read).length;

  if (loading) {
    return (
      <div className="py-16 text-center text-slate-500 font-semibold text-sm animate-pulse">
        Loading notifications center...
      </div>
    );
  }

  return (
    <div className="space-y-8 animate-in fade-in duration-300 max-w-5xl mx-auto">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <Badge variant="brand">{unreadCount} Unread Alerts</Badge>
          </div>
          <h1 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
            <Bell className="w-6 h-6 text-brand-600" />
            Notification Hub
          </h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Task assignments, mentions, discussion replies, and project alerts
          </p>
        </div>

        <div className="flex items-center gap-2.5">
          <Button
            variant="outline"
            size="sm"
            onClick={handleMarkAllRead}
            disabled={unreadCount === 0}
            leftIcon={<CheckCheck className="w-4 h-4" />}
          >
            Mark All as Read
          </Button>
        </div>
      </div>

      {/* Filter Toolbar */}
      <div className="flex items-center gap-2 bg-white dark:bg-slate-900 p-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 w-fit">
        <button
          onClick={() => setFilterMode('ALL')}
          className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
            filterMode === 'ALL'
              ? 'bg-brand-50 dark:bg-brand-950/80 text-brand-600'
              : 'text-slate-500 hover:text-slate-900'
          }`}
        >
          All Notifications ({notifications.length})
        </button>
        <button
          onClick={() => setFilterMode('UNREAD')}
          className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
            filterMode === 'UNREAD'
              ? 'bg-brand-50 dark:bg-brand-950/80 text-brand-600'
              : 'text-slate-500 hover:text-slate-900'
          }`}
        >
          Unread ({unreadCount})
        </button>
      </div>

      {/* Notifications List */}
      {filteredNotifs.length === 0 ? (
        <div className="text-center py-16 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-2">
          <Bell className="w-8 h-8 text-slate-400 mx-auto" />
          <p className="text-xs font-semibold text-slate-500">No notifications found.</p>
        </div>
      ) : (
        <div className="space-y-3">
          {filteredNotifs.map((item) => (
            <div
              key={item.id}
              onClick={() => handleMarkRead(item.id, item.link_url)}
              className={`p-4 rounded-3xl border transition-all cursor-pointer flex items-start justify-between gap-4 ${
                !item.is_read
                  ? 'bg-brand-50/40 dark:bg-brand-950/40 border-brand-200 dark:border-brand-800 shadow-xs'
                  : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 opacity-80'
              }`}
            >
              <div className="flex items-start gap-3">
                <Avatar
                  src={item.actor?.avatar_url}
                  name={item.actor?.full_name || 'System'}
                  size="sm"
                />
                <div className="space-y-1">
                  <div className="flex items-center gap-2">
                    <span className="font-bold text-xs text-slate-900 dark:text-slate-100">
                      {item.title}
                    </span>
                    {!item.is_read && (
                      <span className="w-2 h-2 rounded-full bg-brand-500 shrink-0" />
                    )}
                  </div>
                  <p className="text-xs text-slate-600 dark:text-slate-300">{item.message}</p>
                  <span className="text-[10px] text-slate-400 block pt-0.5">
                    {new Date(item.created_at).toLocaleString()}
                  </span>
                </div>
              </div>

              {item.link_url && (
                <ArrowRight className="w-4 h-4 text-slate-400 shrink-0 self-center" />
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
