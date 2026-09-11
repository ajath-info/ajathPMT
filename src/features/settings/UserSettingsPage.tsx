import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Wrench,
  LayoutTemplate,
  User,
  Bell,
  Settings,
  Clock,
  Smartphone,
  HelpCircle,
  LogOut,
  Laptop,
  Check,
  X,
  Plus,
  Shield,
  Calendar,
  Sparkles,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { BasecampProfileModal } from '../../components/modals/BasecampProfileModal';
import { ThemeCustomizerModal } from '../../components/modals/ThemeCustomizerModal';
import { SupportModal } from '../../components/modals/SupportModal';
import { AdminlandModal } from '../../components/modals/AdminlandModal';
import { Modal } from '../../components/common/Modal';
import { Button } from '../../components/common/Button';
import {
  getUserNotificationPreferences,
  updateUserNotificationPreferences,
  UserNotificationPreferences,
} from '../../services/notificationService';

export function UserSettingsPage() {
  const { user, profile, signOut, userRole } = useAuth();
  const { addToast } = useToast();
  const navigate = useNavigate();

  const currentUserId = user?.id || profile?.id || 'demo-user-owner';

  // Modal States for the 9 Settings Actions
  const [isProfileModalOpen, setIsProfileModalOpen] = useState(false);
  const [isThemeModalOpen, setIsThemeModalOpen] = useState(false);
  const [isSupportModalOpen, setIsSupportModalOpen] = useState(false);
  const [isAdminlandModalOpen, setIsAdminlandModalOpen] = useState(false);
  const [isTemplatesModalOpen, setIsTemplatesModalOpen] = useState(false);
  const [isNotificationsModalOpen, setIsNotificationsModalOpen] = useState(false);
  const [isOutOfOfficeModalOpen, setIsOutOfOfficeModalOpen] = useState(false);
  const [isDevicesModalOpen, setIsDevicesModalOpen] = useState(false);

  // Out of Office State
  const [oooEnabled, setOooEnabled] = useState(() => {
    return localStorage.getItem('basecamp_ooo_enabled') === 'true';
  });
  const [oooNote, setOooNote] = useState(() => {
    return localStorage.getItem('basecamp_ooo_note') || "I'm currently away on annual leave with limited access to messages.";
  });
  const [oooReturnDate, setOooReturnDate] = useState(() => {
    return localStorage.getItem('basecamp_ooo_date') || '2026-09-15';
  });

  // Notification Preferences
  const [preferences, setPreferences] = useState<UserNotificationPreferences>({
    user_id: currentUserId,
    email_notifications: true,
    push_notifications: true,
    notify_task_assigned: true,
    notify_discussion_replies: true,
    notify_chat_mentions: true,
    notify_due_dates: true,
  });

  useEffect(() => {
    async function loadPrefs() {
      if (!currentUserId) return;
      try {
        const loaded = await getUserNotificationPreferences(currentUserId);
        setPreferences(loaded);
      } catch {}
    }
    loadPrefs();
  }, [currentUserId]);

  const handleTogglePreference = async (key: keyof Omit<UserNotificationPreferences, 'user_id'>) => {
    const updated = { ...preferences, [key]: !preferences[key] };
    setPreferences(updated);
    try {
      await updateUserNotificationPreferences(currentUserId, updated);
      addToast('Notification settings updated', 'success');
    } catch {
      addToast('Failed to save preference', 'error');
    }
  };

  const handleSaveOutOfOffice = (e: React.FormEvent) => {
    e.preventDefault();
    localStorage.setItem('basecamp_ooo_enabled', String(oooEnabled));
    localStorage.setItem('basecamp_ooo_note', oooNote);
    localStorage.setItem('basecamp_ooo_date', oooReturnDate);
    addToast(oooEnabled ? 'Out of Office responder activated!' : 'Out of Office responder turned off', 'info');
    setIsOutOfOfficeModalOpen(false);
  };

  return (
    <div className="w-full min-h-[calc(100vh-8rem)] flex flex-col items-center justify-start py-6 sm:py-10 px-4 animate-in fade-in duration-200">
      
      {/* 1. Page Heading matching Basecamp screenshot */}
      <h1 className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight text-center mb-8">
        Account & Settings
      </h1>

      {/* 2. Main Settings Card */}
      <div className="w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
        {/* Card Title */}
        <div className="py-4 text-center border-b border-slate-100 dark:border-slate-800">
          <h2 className="text-base font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Settings
          </h2>
        </div>

        {/* 9 Settings Rows matching screenshot exactly */}
        <div className="divide-y divide-slate-100 dark:divide-slate-800/80">
          
          {/* Row 1: Adminland (Owner & Admin only) */}
          {['OWNER', 'ADMIN'].includes(userRole) && (
            <button
              onClick={() => setIsAdminlandModalOpen(true)}
              className="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors text-left group cursor-pointer"
            >
              <div className="flex items-center gap-3.5">
                <Wrench className="w-4 h-4 text-slate-700 dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white shrink-0" />
                <span className="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-950 dark:group-hover:text-white">
                  Adminland
                </span>
              </div>
            </button>
          )}

          {/* Row 2: Templates */}
          <button
            onClick={() => setIsTemplatesModalOpen(true)}
            className="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors text-left group cursor-pointer"
          >
            <div className="flex items-center gap-3.5">
              <LayoutTemplate className="w-4 h-4 text-slate-700 dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white shrink-0" />
              <span className="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-950 dark:group-hover:text-white">
                Templates
              </span>
            </div>
          </button>

          {/* Row 3: Profile, password, 2FA */}
          <button
            onClick={() => setIsProfileModalOpen(true)}
            className="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors text-left group cursor-pointer"
          >
            <div className="flex items-center gap-3.5">
              <User className="w-4 h-4 text-slate-700 dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white shrink-0" />
              <span className="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-950 dark:group-hover:text-white">
                Profile, password, 2FA
              </span>
            </div>
          </button>

          {/* Row 4: Notification settings */}
          <button
            onClick={() => setIsNotificationsModalOpen(true)}
            className="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors text-left group cursor-pointer"
          >
            <div className="flex items-center gap-3.5">
              <Bell className="w-4 h-4 text-slate-700 dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white shrink-0" />
              <span className="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-950 dark:group-hover:text-white">
                Notification settings
              </span>
            </div>
          </button>

          {/* Row 5: Preferences */}
          <button
            onClick={() => setIsThemeModalOpen(true)}
            className="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors text-left group cursor-pointer"
          >
            <div className="flex items-center gap-3.5">
              <Settings className="w-4 h-4 text-slate-700 dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white shrink-0" />
              <span className="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-950 dark:group-hover:text-white">
                Preferences
              </span>
            </div>
          </button>

          {/* Row 6: Out of Office */}
          <button
            onClick={() => setIsOutOfOfficeModalOpen(true)}
            className="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors text-left group cursor-pointer"
          >
            <div className="flex items-center gap-3.5">
              <Clock className="w-4 h-4 text-slate-700 dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white shrink-0" />
              <span className="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-950 dark:group-hover:text-white">
                Out of Office
              </span>
            </div>
            {oooEnabled && (
              <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                Active
              </span>
            )}
          </button>

          {/* Row 7: My devices */}
          <button
            onClick={() => setIsDevicesModalOpen(true)}
            className="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors text-left group cursor-pointer"
          >
            <div className="flex items-center gap-3.5">
              <Smartphone className="w-4 h-4 text-slate-700 dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white shrink-0" />
              <span className="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-950 dark:group-hover:text-white">
                My devices
              </span>
            </div>
          </button>

          {/* Row 8: Help */}
          <button
            onClick={() => setIsSupportModalOpen(true)}
            className="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors text-left group cursor-pointer"
          >
            <div className="flex items-center gap-3.5">
              <HelpCircle className="w-4 h-4 text-slate-700 dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white shrink-0" />
              <span className="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-950 dark:group-hover:text-white">
                Help
              </span>
            </div>
            <span className="text-[11px] font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700 shadow-2xs">
              ?
            </span>
          </button>

          {/* Row 9: Log out */}
          <button
            onClick={() => {
              signOut();
              navigate('/login');
            }}
            className="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors text-left group cursor-pointer"
          >
            <div className="flex items-center gap-3.5">
              <LogOut className="w-4 h-4 text-slate-700 dark:text-slate-300 group-hover:text-slate-950 dark:group-hover:text-white shrink-0" />
              <span className="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-950 dark:group-hover:text-white">
                Log out
              </span>
            </div>
            <span className="text-[11px] font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700 shadow-2xs">
              :M
            </span>
          </button>
        </div>
      </div>

      {/* 3. "Get the Basecamp apps" Bottom Card */}
      <div className="w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-xs mt-6 p-6 text-center space-y-3">
        <h3 className="text-base font-extrabold text-slate-900 dark:text-slate-100">
          Get the Ajath PMT apps
        </h3>
        <p className="text-xs text-slate-500 dark:text-slate-400">
          Mobile apps for iPhone, iPad, & Android
        </p>

        <div className="pt-2 flex flex-wrap items-center justify-center gap-3">
          <a
            href="https://apps.apple.com"
            target="_blank"
            rel="noreferrer"
            className="px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors flex items-center gap-2 shadow-2xs"
          >
            <Smartphone className="w-4 h-4" />
            <span>Apple iOS App</span>
          </a>
          <a
            href="https://play.google.com"
            target="_blank"
            rel="noreferrer"
            className="px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors flex items-center gap-2 shadow-2xs"
          >
            <Smartphone className="w-4 h-4" />
            <span>Google Android App</span>
          </a>
          <button
            onClick={() => addToast('Desktop app installer will be available shortly', 'info')}
            className="px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors flex items-center gap-2 shadow-2xs cursor-pointer"
          >
            <Laptop className="w-4 h-4" />
            <span>Mac & Windows Desktop</span>
          </button>
        </div>
      </div>

      {/* Modals Wired to the 9 Rows */}

      {/* 1. Adminland Modal */}
      <AdminlandModal
        isOpen={isAdminlandModalOpen}
        onClose={() => setIsAdminlandModalOpen(false)}
      />

      {/* 2. Templates Modal */}
      <Modal
        isOpen={isTemplatesModalOpen}
        onClose={() => setIsTemplatesModalOpen(false)}
        title="Project Templates"
        maxWidth="lg"
      >
        <div className="space-y-4 pt-1">
          <p className="text-xs text-slate-500">
            Pre-configured project structures with to-do checklists, doc templates, and campfire rooms ready to spin up in one click.
          </p>

          <div className="space-y-2.5">
            <div className="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-slate-900 dark:text-slate-100">
                  Client Deliverable & Mobile App Template
                </span>
                <span className="text-[10px] uppercase font-bold text-brand-600 bg-brand-50 dark:bg-brand-950/60 px-2 py-0.5 rounded-full">
                  Recommended
                </span>
              </div>
              <p className="text-[11px] text-slate-500">
                Preloaded with Dependency Checklist, Inspection Flow specs, Sprint Planning events, and async check-ins.
              </p>
              <div className="pt-2 flex justify-end">
                <Button
                  size="sm"
                  variant="primary"
                  onClick={() => {
                    setIsTemplatesModalOpen(false);
                    navigate('/projects/new');
                  }}
                >
                  Use this template
                </Button>
              </div>
            </div>

            <div className="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-slate-900 dark:text-slate-100">
                  Internal Engineering & Sprint Template
                </span>
              </div>
              <p className="text-[11px] text-slate-500">
                Includes Card Table Kanban board, Hill Chart progress tracker, and daily standup prompts.
              </p>
              <div className="pt-2 flex justify-end">
                <Button
                  size="sm"
                  variant="outline"
                  onClick={() => {
                    setIsTemplatesModalOpen(false);
                    navigate('/projects/new');
                  }}
                >
                  Use this template
                </Button>
              </div>
            </div>
          </div>
        </div>
      </Modal>

      {/* 3. Basecamp Profile Modal */}
      <BasecampProfileModal
        isOpen={isProfileModalOpen}
        onClose={() => setIsProfileModalOpen(false)}
      />

      {/* 4. Notification Settings Modal */}
      <Modal
        isOpen={isNotificationsModalOpen}
        onClose={() => setIsNotificationsModalOpen(false)}
        title="Notification Settings"
        maxWidth="md"
      >
        <div className="space-y-4 pt-1">
          <p className="text-xs text-slate-500">
            Control when, where, and how you receive alerts and pings from team members.
          </p>

          <div className="space-y-3">
            <div className="flex items-center justify-between p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
              <div>
                <p className="text-xs font-bold text-slate-900 dark:text-slate-100">Email Notifications</p>
                <p className="text-[11px] text-slate-500">Receive transactional emails and replies</p>
              </div>
              <button
                type="button"
                onClick={() => handleTogglePreference('email_notifications')}
                className={`w-10 h-5 rounded-full p-0.5 transition-colors cursor-pointer ${
                  preferences.email_notifications ? 'bg-[#0c66e4]' : 'bg-slate-300 dark:bg-slate-700'
                }`}
              >
                <div
                  className={`w-4 h-4 rounded-full bg-white transition-transform ${
                    preferences.email_notifications ? 'translate-x-5' : 'translate-x-0'
                  }`}
                />
              </button>
            </div>

            <div className="flex items-center justify-between p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
              <div>
                <p className="text-xs font-bold text-slate-900 dark:text-slate-100">Desktop Web Push Alerts</p>
                <p className="text-[11px] text-slate-500">Alerts in your browser even when minimized</p>
              </div>
              <button
                type="button"
                onClick={() => handleTogglePreference('push_notifications')}
                className={`w-10 h-5 rounded-full p-0.5 transition-colors cursor-pointer ${
                  preferences.push_notifications ? 'bg-[#0c66e4]' : 'bg-slate-300 dark:bg-slate-700'
                }`}
              >
                <div
                  className={`w-4 h-4 rounded-full bg-white transition-transform ${
                    preferences.push_notifications ? 'translate-x-5' : 'translate-x-0'
                  }`}
                />
              </button>
            </div>

            <div className="flex items-center justify-between p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
              <div>
                <p className="text-xs font-bold text-slate-900 dark:text-slate-100">To-Do Assignments</p>
                <p className="text-[11px] text-slate-500">Notify me when someone assigns me a task</p>
              </div>
              <button
                type="button"
                onClick={() => handleTogglePreference('notify_task_assigned')}
                className={`w-10 h-5 rounded-full p-0.5 transition-colors cursor-pointer ${
                  preferences.notify_task_assigned ? 'bg-[#0c66e4]' : 'bg-slate-300 dark:bg-slate-700'
                }`}
              >
                <div
                  className={`w-4 h-4 rounded-full bg-white transition-transform ${
                    preferences.notify_task_assigned ? 'translate-x-5' : 'translate-x-0'
                  }`}
                />
              </button>
            </div>

            <div className="flex items-center justify-between p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
              <div>
                <p className="text-xs font-bold text-slate-900 dark:text-slate-100">Message Replies</p>
                <p className="text-[11px] text-slate-500">Notify me when someone comments on my posts</p>
              </div>
              <button
                type="button"
                onClick={() => handleTogglePreference('notify_discussion_replies')}
                className={`w-10 h-5 rounded-full p-0.5 transition-colors cursor-pointer ${
                  preferences.notify_discussion_replies ? 'bg-[#0c66e4]' : 'bg-slate-300 dark:bg-slate-700'
                }`}
              >
                <div
                  className={`w-4 h-4 rounded-full bg-white transition-transform ${
                    preferences.notify_discussion_replies ? 'translate-x-5' : 'translate-x-0'
                  }`}
                />
              </button>
            </div>
          </div>
        </div>
      </Modal>

      {/* 5. Preferences (Theme & Appearance) Modal */}
      <ThemeCustomizerModal
        isOpen={isThemeModalOpen}
        onClose={() => setIsThemeModalOpen(false)}
      />

      {/* 6. Out of Office Modal */}
      <Modal
        isOpen={isOutOfOfficeModalOpen}
        onClose={() => setIsOutOfOfficeModalOpen(false)}
        title="Out of Office Responder"
        maxWidth="md"
      >
        <form onSubmit={handleSaveOutOfOffice} className="space-y-4 pt-1">
          <p className="text-xs text-slate-500">
            Automatically inform team members who mention or ping you that you are away.
          </p>

          <div className="flex items-center justify-between p-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
            <div>
              <p className="text-xs font-bold text-slate-900 dark:text-slate-100">Enable Out of Office Status</p>
              <p className="text-[11px] text-slate-500">Shows a palm tree 🌴 next to your avatar</p>
            </div>
            <button
              type="button"
              onClick={() => setOooEnabled(!oooEnabled)}
              className={`w-10 h-5 rounded-full p-0.5 transition-colors cursor-pointer ${
                oooEnabled ? 'bg-[#0c66e4]' : 'bg-slate-300 dark:bg-slate-700'
              }`}
            >
              <div
                className={`w-4 h-4 rounded-full bg-white transition-transform ${
                  oooEnabled ? 'translate-x-5' : 'translate-x-0'
                }`}
              />
            </button>
          </div>

          <div className="space-y-1">
            <label className="text-xs font-bold text-slate-800 dark:text-slate-200">Return Date</label>
            <input
              type="date"
              value={oooReturnDate}
              onChange={(e) => setOooReturnDate(e.target.value)}
              className="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100"
            />
          </div>

          <div className="space-y-1">
            <label className="text-xs font-bold text-slate-800 dark:text-slate-200">Away Note</label>
            <textarea
              rows={3}
              value={oooNote}
              onChange={(e) => setOooNote(e.target.value)}
              placeholder="e.g. I am out of office until next Monday..."
              className="w-full p-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 resize-none"
            />
          </div>

          <div className="pt-2 flex justify-end gap-2">
            <Button type="button" variant="outline" size="sm" onClick={() => setIsOutOfOfficeModalOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" variant="primary" size="sm">
              Save Out of Office
            </Button>
          </div>
        </form>
      </Modal>

      {/* 7. My Devices Modal */}
      <Modal
        isOpen={isDevicesModalOpen}
        onClose={() => setIsDevicesModalOpen(false)}
        title="My Logged-in Devices"
        maxWidth="md"
      >
        <div className="space-y-4 pt-1">
          <p className="text-xs text-slate-500">
            These browsers and mobile devices are currently authorized and logged into your account.
          </p>

          <div className="space-y-2.5">
            <div className="p-3 rounded-2xl border border-emerald-300 dark:border-emerald-800/80 bg-emerald-50/50 dark:bg-emerald-950/30 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <Laptop className="w-5 h-5 text-emerald-600" />
                <div>
                  <p className="text-xs font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    Windows PC • Chrome Browser
                    <span className="text-[10px] bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-bold px-1.5 py-0.2 rounded-full">
                      This device
                    </span>
                  </p>
                  <p className="text-[10px] text-slate-500">New Delhi, India • Active right now</p>
                </div>
              </div>
            </div>

            <div className="p-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <Smartphone className="w-5 h-5 text-slate-500" />
                <div>
                  <p className="text-xs font-bold text-slate-900 dark:text-slate-100">
                    iPhone 15 Pro • Ajath PMT Mobile App
                  </p>
                  <p className="text-[10px] text-slate-500">New Delhi, India • Active 11 hours ago</p>
                </div>
              </div>
              <button
                onClick={() => addToast('Session revoked', 'info')}
                className="text-[11px] font-bold text-rose-600 hover:underline cursor-pointer"
              >
                Sign out
              </button>
            </div>
          </div>

          <div className="pt-2 flex justify-between items-center text-xs">
            <button
              onClick={() => {
                addToast('Signed out of all other devices', 'success');
                setIsDevicesModalOpen(false);
              }}
              className="text-xs font-bold text-rose-600 hover:underline cursor-pointer"
            >
              Sign out of all other devices
            </button>
            <Button size="sm" variant="outline" onClick={() => setIsDevicesModalOpen(false)}>
              Done
            </Button>
          </div>
        </div>
      </Modal>

      {/* 8. Support / Help Modal */}
      <SupportModal
        isOpen={isSupportModalOpen}
        onClose={() => setIsSupportModalOpen(false)}
      />
    </div>
  );
}
