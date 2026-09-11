import React, { useState, useMemo, useRef, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  MessageSquare,
  Bell,
  Check,
  X,
  Plus,
  Send,
  VolumeX,
  Search,
  User,
} from 'lucide-react';
import { useToast } from '../../context/ToastContext';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import {
  getUserNotifications,
  markAllNotificationsAsRead,
  markNotificationAsRead,
  createNotification,
} from '../../services/notificationService';
import { getInitials } from '../../lib/utils';

interface PingsDrawerProps {
  isOpen: boolean;
  onClose: () => void;
}

interface NewForItem {
  id: string;
  avatarType: 'edward' | 'checkmark';
  avatarInitial?: string;
  avatarColor?: string;
  badgeType: 'message' | 'alert' | 'send' | 'announcement';
  title: string;
  snippet?: string;
  meta: string;
  unread: boolean;
  targetPath: string;
}

interface PingContact {
  id: string;
  name: string;
  initials: string;
  color: string;
  img?: string;
}

export function PingsDrawer({ isOpen, onClose }: PingsDrawerProps) {
  const navigate = useNavigate();
  const { addToast } = useToast();
  const { user, profile } = useAuth();
  const { members, currentOrganization } = useOrganization();
  const chatInputRef = useRef<HTMLInputElement>(null);

  const [isPingActive, setIsPingActive] = useState(false);
  const [selectedRecipient, setSelectedRecipient] = useState<string>('');
  const [selectedRecipientId, setSelectedRecipientId] = useState<string>('');
  const [privateChatMessage, setPrivateChatMessage] = useState('');
  const [filterText, setFilterText] = useState('');
  const [isSnoozed, setIsSnoozed] = useState(false);
  const [items, setItems] = useState<NewForItem[]>([]);

  // Compute contacts dynamically from currentOrganization members
  const pingContacts: PingContact[] = useMemo(() => {
    const list = members.filter((m) => m.user_id !== user?.id);
    const colors = ['bg-indigo-600', 'bg-blue-600', 'bg-purple-600', 'bg-teal-600', 'bg-rose-600'];
    return list.map((m, idx) => ({
      id: m.user_id,
      name: m.profile?.full_name || m.profile?.email || 'Teammate',
      initials: getInitials(m.profile?.full_name || m.profile?.email || 'T'),
      color: colors[idx % colors.length],
      img: m.profile?.avatar_url,
    }));
  }, [members, user?.id]);

  useEffect(() => {
    if (!selectedRecipient && pingContacts.length > 0) {
      setSelectedRecipient(pingContacts[0].name);
      setSelectedRecipientId(pingContacts[0].id);
    }
  }, [pingContacts, selectedRecipient]);

  // Load tenant notifications for "New for you"
  useEffect(() => {
    if (!isOpen || !user) return;
    getUserNotifications(user.id, currentOrganization?.id)
      .then((notifs) => {
        const mapped: NewForItem[] = notifs.map((n) => ({
          id: n.id,
          avatarType: 'checkmark',
          badgeType: n.type === 'CHAT_MENTION' || n.type === 'DISCUSSION_REPLY' ? 'message' : 'alert',
          title: n.title,
          snippet: n.message,
          meta: `${new Date(n.created_at).toLocaleDateString(undefined, {
            month: 'short',
            day: 'numeric',
          })} · ${currentOrganization?.name || 'Workspace'}`,
          unread: !n.is_read,
          targetPath: n.link_url || '/notifications',
        }));
        setItems(mapped);
      })
      .catch((err) => console.error('Failed to load notifications in PingsDrawer', err));
  }, [isOpen, user?.id, currentOrganization?.id]);

  const filteredItems = useMemo(() => {
    if (!filterText.trim()) return items;
    const q = filterText.toLowerCase();
    return items.filter(
      (item) =>
        item.title.toLowerCase().includes(q) ||
        (item.snippet && item.snippet.toLowerCase().includes(q)) ||
        item.meta.toLowerCase().includes(q)
    );
  }, [items, filterText]);

  const handleMarkAllRead = async () => {
    if (user?.id) {
      await markAllNotificationsAsRead(user.id, currentOrganization?.id);
    }
    setItems((prev) => prev.map((item) => ({ ...item, unread: false })));
    addToast('All notifications marked as read', 'info');
  };

  const handleItemClick = async (item: NewForItem) => {
    if (item.unread) {
      await markNotificationAsRead(item.id);
    }
    setItems((prev) =>
      prev.map((i) => (i.id === item.id ? { ...i, unread: false } : i))
    );
    onClose();
    navigate(item.targetPath);
  };

  const handleTogglePingButton = () => {
    if (isPingActive) {
      setIsPingActive(false);
      setPrivateChatMessage('');
      addToast('Ping compose closed', 'info');
    } else {
      setIsPingActive(true);
      if (!selectedRecipient && pingContacts.length > 0) {
        setSelectedRecipient(pingContacts[0].name);
        setSelectedRecipientId(pingContacts[0].id);
      }
      setTimeout(() => chatInputRef.current?.focus(), 50);
    }
  };

  const handleSelectContact = (contact: PingContact) => {
    setSelectedRecipient(contact.name);
    setSelectedRecipientId(contact.id);
    setIsPingActive(true);
    setTimeout(() => chatInputRef.current?.focus(), 50);
  };

  const handleSendPing = () => {
    const recipient = selectedRecipient || 'Teammate';
    if (!privateChatMessage.trim()) {
      addToast(`Please enter a message to ping ${recipient}`, 'error');
      chatInputRef.current?.focus();
      return;
    }
    const message = privateChatMessage.trim();
    setPrivateChatMessage('');
    addToast(`Ping sent to ${recipient}: "${message}"`, 'success');

    if (selectedRecipientId) {
      createNotification({
        user_id: selectedRecipientId,
        actor_id: user?.id,
        type: 'CHAT_MENTION',
        title: `Ping from ${profile?.full_name || 'Teammate'}`,
        message: message,
        link_url: '/messages',
        organization_id: currentOrganization?.id,
      }).catch((err) => console.error('Error creating ping notification:', err));
    }

    // Add to top of items as an interactive ping entry
    const newItem: NewForItem = {
      id: `ping-${Date.now()}`,
      avatarType: 'edward',
      avatarInitial: recipient.charAt(0),
      avatarColor: 'bg-blue-600',
      badgeType: 'message',
      title: `Ping to ${recipient}`,
      snippet: message,
      meta: `Just now · ${profile?.full_name || 'You'} · Private Ping`,
      unread: false,
      targetPath: '/messages',
    };
    setItems((prev) => [newItem, ...prev]);
  };

  const toggleSnooze = () => {
    setIsSnoozed((prev) => {
      const next = !prev;
      addToast(
        next ? 'Shhh... Notifications snoozed for 2 hours' : 'Notifications un-snoozed',
        'info'
      );
      return next;
    });
  };

  if (!isOpen) return null;

  return (
    <>
      {/* Invisible backdrop to allow clicking outside to dismiss without dimming dashboard */}
      <div
        className="fixed inset-0 z-40 bg-transparent"
        onClick={onClose}
      />

      {/* Slide-over Right Panel matching Basecamp screenshot */}
      <aside className="fixed top-14 right-0 bottom-12 z-50 w-full sm:w-[380px] bg-white dark:bg-slate-900 border-l border-slate-200/90 dark:border-slate-800 shadow-xl flex flex-col animate-in slide-in-from-right-4 duration-200 select-none">
        
        {/* Scrollable Body */}
        <div className="flex-1 overflow-y-auto px-5 py-4 space-y-6">
          
          {/* ================= 1. PING SOMEONE SECTION ================= */}
          <section className="space-y-3">
            <div className="flex items-center justify-between">
              <h3 className="text-[14px] font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                Ping someone
              </h3>
              <button
                type="button"
                onClick={() => {
                  onClose();
                  navigate('/messages');
                }}
                className="text-[12px] font-normal text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:underline cursor-pointer"
              >
                View all Pings
              </button>
            </div>

            {/* Horizontal Contact Avatars */}
            <div className="flex items-center gap-3.5 pt-0.5 overflow-x-auto pb-1">
              {/* Interactive "Ping" Action Button */}
              <div className="flex flex-col items-center shrink-0">
                <button
                  type="button"
                  onClick={handleTogglePingButton}
                  title={isPingActive ? 'Cancel ping compose' : 'Start a new ping'}
                  className={`w-10 h-10 rounded-full border transition-all flex items-center justify-center shadow-2xs cursor-pointer ${
                    isPingActive
                      ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-950/40 text-[#0c66e4] dark:text-blue-400 hover:bg-blue-100/70'
                      : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:border-blue-400 hover:text-blue-600'
                  }`}
                >
                  {isPingActive ? (
                    <X className="w-4 h-4 stroke-[2.5]" />
                  ) : (
                    <Plus className="w-4 h-4 stroke-[2.5]" />
                  )}
                </button>
                <span className="text-[11px] text-slate-700 dark:text-slate-300 font-medium text-center mt-1">
                  Ping
                </span>
              </div>

              {/* Teammates List */}
              {pingContacts.length === 0 ? (
                <div className="text-xs text-slate-400 italic py-1">
                  Invite teammates to start pinging
                </div>
              ) : (
                pingContacts.map((contact) => {
                  const isSelected = selectedRecipientId === contact.id && isPingActive;
                  return (
                    <div
                      key={contact.id}
                      onClick={() => handleSelectContact(contact)}
                      className="flex flex-col items-center shrink-0 cursor-pointer group"
                      title={`Ping ${contact.name}`}
                    >
                      <div
                        className={`w-10 h-10 rounded-full overflow-hidden shadow-xs transition-all ${
                          isSelected
                            ? 'ring-2 ring-blue-500 ring-offset-2 dark:ring-offset-slate-900 scale-105'
                            : 'ring-1 ring-transparent hover:ring-blue-300'
                        }`}
                      >
                        {contact.img ? (
                          <img
                            src={contact.img}
                            alt={contact.name}
                            className="w-full h-full object-cover group-hover:scale-105 transition-transform"
                            onError={(e) => {
                              const target = e.currentTarget;
                              target.style.display = 'none';
                              if (target.parentElement) {
                                target.parentElement.className = `w-10 h-10 rounded-full ${contact.color} text-white font-bold text-xs flex items-center justify-center`;
                                target.parentElement.innerText = contact.initials;
                              }
                            }}
                          />
                        ) : (
                          <div
                            className={`w-full h-full ${contact.color} text-white font-bold text-xs flex items-center justify-center`}
                          >
                            {contact.initials}
                          </div>
                        )}
                      </div>
                      <span
                        className={`text-[11px] font-medium text-center mt-1 truncate max-w-[50px] transition-colors ${
                          isSelected
                            ? 'text-blue-600 dark:text-blue-400 font-bold'
                            : 'text-slate-700 dark:text-slate-300 group-hover:text-blue-600'
                        }`}
                      >
                        {contact.name}
                      </span>
                    </div>
                  );
                })
              )}
            </div>

            {/* Inline Private Chat Input & "Ping 'em" Button (Active when isPingActive is true) */}
            {isPingActive && (
              <div className="flex items-center gap-2 pt-1 animate-in fade-in slide-in-from-top-1 duration-150">
                <input
                  ref={chatInputRef}
                  type="text"
                  value={privateChatMessage}
                  onChange={(e) => setPrivateChatMessage(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') handleSendPing();
                  }}
                  placeholder={
                    selectedRecipient
                      ? `Start a private chat with ${selectedRecipient}...`
                      : 'Start a private chat...'
                  }
                  className="flex-1 px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs"
                />
                <button
                  type="button"
                  onClick={handleSendPing}
                  className="px-3.5 py-2 bg-[#1b75bb] hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-2xs transition-colors cursor-pointer shrink-0"
                >
                  Ping &apos;em
                </button>
              </div>
            )}
          </section>

          {/* ================= 2. NEW FOR YOU SECTION ================= */}
          <section className="space-y-2.5">
            <div className="flex items-center justify-between">
              <h3 className="text-[14px] font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                New for you
              </h3>
              <button
                type="button"
                onClick={handleMarkAllRead}
                className="text-[12px] font-normal text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:underline cursor-pointer"
              >
                Mark all read
              </button>
            </div>

            {/* Notifications / Pings List */}
            <div className="divide-y divide-slate-100 dark:divide-slate-800/80">
              {filteredItems.map((item) => (
                <div
                  key={item.id}
                  onClick={() => handleItemClick(item)}
                  className="py-3 px-1 hover:bg-slate-50 dark:hover:bg-slate-800/50 rounded-xl cursor-pointer transition-colors flex items-start gap-3 group select-none"
                >
                  {/* Avatar with Top-Right Badge Overlay matching screenshot */}
                  <div className="relative shrink-0 mt-0.5">
                    {item.avatarType === 'edward' ? (
                      <div className="w-8 h-8 rounded-full bg-[#ff6f3c] text-white font-bold text-xs flex items-center justify-center shadow-xs">
                        E
                      </div>
                    ) : (
                      <div className="w-8 h-8 rounded-full bg-[#22c55e] text-white flex items-center justify-center shadow-xs">
                        <Check className="w-4 h-4 stroke-[3]" />
                      </div>
                    )}

                    {/* Badge Overlay in Top-Right matching screenshot */}
                    {item.badgeType === 'message' && (
                      <div className="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-[#0284c7] ring-1.5 ring-white dark:ring-slate-900 flex items-center justify-center text-white shadow-2xs">
                        <MessageSquare className="w-2.5 h-2.5 fill-white" />
                      </div>
                    )}
                    {item.badgeType === 'alert' && (
                      <div className="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-[#ea580c] ring-1.5 ring-white dark:ring-slate-900 flex items-center justify-center text-white shadow-2xs">
                        <Bell className="w-2.5 h-2.5 fill-white" />
                      </div>
                    )}
                    {item.badgeType === 'send' && (
                      <div className="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-[#8b5cf6] ring-1.5 ring-white dark:ring-slate-900 flex items-center justify-center text-white shadow-2xs">
                        <Send className="w-2.5 h-2.5 fill-white" />
                      </div>
                    )}
                    {item.badgeType === 'announcement' && (
                      <div className="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-[#8b5cf6] ring-1.5 ring-white dark:ring-slate-900 flex items-center justify-center text-white shadow-2xs">
                        <MessageSquare className="w-2.5 h-2.5 fill-white" />
                      </div>
                    )}
                  </div>

                  {/* Title & Preview & Metadata */}
                  <div className="flex-1 min-w-0 pr-1">
                    <h4 className="text-[13px] font-bold text-slate-900 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors leading-snug">
                      {item.title}
                    </h4>

                    {item.snippet && (
                      <p className="text-[12px] text-slate-600 dark:text-slate-400 font-normal leading-normal mt-0.5 line-clamp-2">
                        {item.snippet}
                      </p>
                    )}

                    <span className="text-[11px] text-slate-400 dark:text-slate-500 block mt-1 font-normal truncate">
                      {item.meta}
                    </span>
                  </div>

                  {/* Red Unread Indicator Dot on far right */}
                  <div className="pt-1.5 shrink-0">
                    {item.unread && (
                      <span className="w-2 h-2 rounded-full bg-[#E03A1A] block shadow-2xs" />
                    )}
                  </div>
                </div>
              ))}

              {filteredItems.length === 0 && (
                <div className="py-8 text-center text-xs text-slate-400">
                  No matching notifications or pings
                </div>
              )}
            </div>
          </section>

        </div>

        {/* ================= 3. BOTTOM FOOTER BAR ================= */}
        <div className="p-3 border-t border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between gap-3">
          {/* Filter Input */}
          <div className="flex-1">
            <input
              type="text"
              placeholder="Filter..."
              value={filterText}
              onChange={(e) => setFilterText(e.target.value)}
              className="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-slate-400"
            />
          </div>

          {/* Shhh... Snooze Button */}
          <button
            type="button"
            onClick={toggleSnooze}
            title={isSnoozed ? 'Un-snooze notifications' : 'Snooze notifications (Shhh...)'}
            className="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white cursor-pointer select-none shrink-0"
          >
            {isSnoozed ? (
              <VolumeX className="w-3.5 h-3.5 text-amber-600" />
            ) : (
              <Bell className="w-3.5 h-3.5 text-slate-500" />
            )}
            <span>Shhh...</span>
          </button>
        </div>

      </aside>
    </>
  );
}
