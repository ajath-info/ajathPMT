import React, { useEffect, useState } from 'react';
import {
  CheckSquare,
  Clock,
  AlertTriangle,
  CheckCircle2,
  Calendar,
  Filter,
  Search,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useProject } from '../../context/ProjectContext';
import { getUserTasks, completeTask, reopenTask } from '../../services/taskService';
import { Task, TaskPriorityType } from '../../types';
import { TaskRow } from '../../components/todos/TaskRow';
import { TaskDetailModal } from '../../components/todos/TaskDetailModal';
import { Badge } from '../../components/common/Badge';

import {
  getUserPersonalNotes,
  updateUserPersonalNotes,
  getUserBookmarks,
  addBookmark,
  deleteBookmark,
  getUserRecentlyViewed,
} from '../../services/personalService';
import { useOrganization } from '../../context/OrganizationContext';
import { Bookmark, FileEdit, History, Plus, Trash2, ExternalLink } from 'lucide-react';

export function MyTasksPage() {
  const { profile, user } = useAuth();
  const { projects } = useProject();
  const { currentOrganization } = useOrganization();

  const [userTasks, setUserTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);

  const [activeTab, setActiveTab] = useState<'TODAY' | 'UPCOMING' | 'OVERDUE' | 'COMPLETED' | 'PRODUCTIVITY'>('TODAY');
  const [projectFilter, setProjectFilter] = useState('ALL');
  const [priorityFilter, setPriorityFilter] = useState('ALL');
  const [searchQuery, setSearchQuery] = useState('');

  const [selectedTaskId, setSelectedTaskId] = useState<string | null>(null);

  // Personal productivity state
  const [personalNote, setPersonalNote] = useState('');
  const [bookmarks, setBookmarks] = useState<any[]>([]);
  const [recentlyViewed, setRecentlyViewed] = useState<any[]>([]);
  const [newBookmarkTitle, setNewBookmarkTitle] = useState('');
  const [newBookmarkUrl, setNewBookmarkUrl] = useState('');
  const [isNoteSaving, setIsNoteSaving] = useState(false);

  const loadUserTasks = async () => {
    if (!profile) return;
    setLoading(true);
    const projIds = projects.map((p) => p.id);
    const data = await getUserTasks(profile.id, currentOrganization?.id, projIds);
    setUserTasks(data);

    if (user) {
      getUserPersonalNotes(user.id).then(setPersonalNote);
      getUserBookmarks(user.id).then(setBookmarks);
      getUserRecentlyViewed(user.id).then(setRecentlyViewed);
    }
    setLoading(false);
  };

  useEffect(() => {
    loadUserTasks();
  }, [profile, user, currentOrganization?.id, projects]);

  const handleSaveNote = async () => {
    if (!user) return;
    setIsNoteSaving(true);
    await updateUserPersonalNotes(user.id, personalNote);
    setIsNoteSaving(false);
  };

  const handleAddBookmark = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!user || !newBookmarkTitle.trim() || !newBookmarkUrl.trim()) return;
    const res = await addBookmark(user.id, newBookmarkTitle.trim(), newBookmarkUrl.trim());
    if (res.data) {
      setBookmarks([res.data, ...bookmarks]);
      setNewBookmarkTitle('');
      setNewBookmarkUrl('');
    }
  };

  const handleDeleteBookmark = async (id: string) => {
    await deleteBookmark(id);
    setBookmarks(bookmarks.filter((b) => b.id !== id));
  };

  const handleToggleComplete = async (task: Task) => {
    if (task.status === 'COMPLETED') {
      await reopenTask(task.id);
    } else {
      await completeTask(task.id);
    }
    loadUserTasks();
  };

  const todayStr = new Date().toISOString().split('T')[0];

  const filteredTasks = userTasks.filter((t) => {
    const matchesSearch =
      t.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
      t.description?.toLowerCase().includes(searchQuery.toLowerCase());

    const matchesProject = projectFilter === 'ALL' || t.project_id === projectFilter;
    const matchesPriority = priorityFilter === 'ALL' || t.priority === priorityFilter;

    if (!matchesSearch || !matchesProject || !matchesPriority) return false;

    const isCompleted = t.status === 'COMPLETED';
    const isOverdue =
      !isCompleted &&
      t.due_date &&
      new Date(t.due_date) < new Date(new Date().setHours(0, 0, 0, 0));

    if (activeTab === 'COMPLETED') return isCompleted;
    if (activeTab === 'OVERDUE') return isOverdue;
    if (activeTab === 'TODAY') return !isCompleted && !isOverdue && t.due_date === todayStr;
    if (activeTab === 'UPCOMING') return !isCompleted && !isOverdue;

    return true;
  });

  return (
    <div className="space-y-8 animate-in fade-in duration-300 max-w-7xl mx-auto pb-12">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
              Personal Productivity Hub
            </span>
            <span className="text-xs text-slate-500 font-medium">{userTasks.length} Total Tasks</span>
          </div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            My Tasks & Productivity Workspace
          </h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Your personal priority stream, private notes, bookmarks, and recently viewed workspace items
          </p>
        </div>
      </div>

      {/* 4 Colorful KPI Cards */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div
          onClick={() => setActiveTab('TODAY')}
          className={`p-4 rounded-2xl border transition-all cursor-pointer select-none ${
            activeTab === 'TODAY'
              ? 'bg-amber-500/10 border-amber-500/40 shadow-sm shadow-amber-500/10 scale-[1.02]'
              : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-amber-400'
          }`}
        >
          <div className="flex items-center justify-between text-xs font-bold text-amber-600 dark:text-amber-400 mb-2">
            <span>Due Today</span>
            <Clock className="w-4 h-4" />
          </div>
          <p className="text-2xl font-black text-slate-900 dark:text-slate-100">
            {userTasks.filter((t) => t.status !== 'COMPLETED' && t.due_date && t.due_date.startsWith(todayStr)).length}
          </p>
        </div>

        <div
          onClick={() => setActiveTab('UPCOMING')}
          className={`p-4 rounded-2xl border transition-all cursor-pointer select-none ${
            activeTab === 'UPCOMING'
              ? 'bg-blue-500/10 border-blue-500/40 shadow-sm shadow-blue-500/10 scale-[1.02]'
              : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-blue-400'
          }`}
        >
          <div className="flex items-center justify-between text-xs font-bold text-blue-600 dark:text-blue-400 mb-2">
            <span>Upcoming</span>
            <Calendar className="w-4 h-4" />
          </div>
          <p className="text-2xl font-black text-slate-900 dark:text-slate-100">
            {userTasks.filter((t) => t.status !== 'COMPLETED' && (!t.due_date || new Date(t.due_date) >= new Date())).length}
          </p>
        </div>

        <div
          onClick={() => setActiveTab('OVERDUE')}
          className={`p-4 rounded-2xl border transition-all cursor-pointer select-none ${
            activeTab === 'OVERDUE'
              ? 'bg-rose-500/10 border-rose-500/40 shadow-sm shadow-rose-500/10 scale-[1.02]'
              : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-rose-400'
          }`}
        >
          <div className="flex items-center justify-between text-xs font-bold text-rose-600 dark:text-rose-400 mb-2">
            <span>Overdue</span>
            <AlertTriangle className="w-4 h-4" />
          </div>
          <p className="text-2xl font-black text-slate-900 dark:text-slate-100">
            {userTasks.filter((t) => t.status !== 'COMPLETED' && t.due_date && new Date(t.due_date) < new Date(new Date().setHours(0, 0, 0, 0))).length}
          </p>
        </div>

        <div
          onClick={() => setActiveTab('COMPLETED')}
          className={`p-4 rounded-2xl border transition-all cursor-pointer select-none ${
            activeTab === 'COMPLETED'
              ? 'bg-emerald-500/10 border-emerald-500/40 shadow-sm shadow-emerald-500/10 scale-[1.02]'
              : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-emerald-400'
          }`}
        >
          <div className="flex items-center justify-between text-xs font-bold text-emerald-600 dark:text-emerald-400 mb-2">
            <span>Completed</span>
            <CheckCircle2 className="w-4 h-4" />
          </div>
          <p className="text-2xl font-black text-slate-900 dark:text-slate-100">
            {userTasks.filter((t) => t.status === 'COMPLETED').length}
          </p>
        </div>
      </div>

      {/* Tabs Bar */}
      <div className="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 text-sm font-semibold overflow-x-auto">
        {[
          { id: 'TODAY', label: 'Due Today', icon: Clock, color: 'text-amber-600', activeBg: 'bg-amber-500 text-white' },
          { id: 'UPCOMING', label: 'Upcoming', icon: Calendar, color: 'text-blue-600', activeBg: 'bg-blue-600 text-white' },
          { id: 'OVERDUE', label: 'Overdue', icon: AlertTriangle, color: 'text-rose-600', activeBg: 'bg-rose-600 text-white' },
          { id: 'COMPLETED', label: 'Completed', icon: CheckCircle2, color: 'text-emerald-600', activeBg: 'bg-emerald-600 text-white' },
          { id: 'PRODUCTIVITY', label: 'Notes & Scratchpad', icon: FileEdit, color: 'text-purple-600', activeBg: 'bg-purple-600 text-white' },
        ].map((tab) => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id as any)}
            className={`px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap ${
              activeTab === tab.id
                ? `${tab.activeBg} shadow-xs`
                : 'border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
            }`}
          >
            <tab.icon className="w-3.5 h-3.5" />
            {tab.label}
          </button>
        ))}
      </div>

      {activeTab === 'PRODUCTIVITY' ? (
        /* Personal Productivity Workspace View */
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Notes Scratchpad */}
          <div className="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div className="flex items-center justify-between">
              <h3 className="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <FileEdit className="w-5 h-5 text-indigo-500" /> Private Notes & Scratchpad
              </h3>
              <button
                onClick={handleSaveNote}
                disabled={isNoteSaving}
                className="px-4 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer"
              >
                {isNoteSaving ? 'Saving...' : 'Save Note'}
              </button>
            </div>
            <textarea
              rows={12}
              value={personalNote}
              onChange={(e) => setPersonalNote(e.target.value)}
              placeholder="Jot down personal reminders, draft thoughts, meeting takeaways..."
              className="w-full p-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:border-indigo-500 font-mono transition-colors"
            />
          </div>

          {/* Bookmarks & Recently Viewed */}
          <div className="space-y-6">
            {/* Bookmarks */}
            <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
              <h3 className="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <Bookmark className="w-5 h-5 text-amber-500" /> Quick Bookmarks
              </h3>

              <form onSubmit={handleAddBookmark} className="space-y-2">
                <input
                  type="text"
                  placeholder="Bookmark Title..."
                  value={newBookmarkTitle}
                  onChange={(e) => setNewBookmarkTitle(e.target.value)}
                  className="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:outline-none focus:border-indigo-500"
                />
                <div className="flex gap-2">
                  <input
                    type="text"
                    placeholder="URL (e.g. /projects/proj-1)..."
                    value={newBookmarkUrl}
                    onChange={(e) => setNewBookmarkUrl(e.target.value)}
                    className="flex-1 px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 text-xs focus:outline-none focus:border-indigo-500"
                  />
                  <button
                    type="submit"
                    className="p-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition-colors cursor-pointer"
                  >
                    <Plus className="w-4 h-4" />
                  </button>
                </div>
              </form>

              <div className="space-y-2 max-h-48 overflow-y-auto">
                {bookmarks.map((b) => (
                  <div
                    key={b.id}
                    className="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-xs"
                  >
                    <a
                      href={b.url}
                      className="font-medium text-slate-800 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 truncate flex items-center gap-1.5"
                    >
                      <ExternalLink className="w-3.5 h-3.5 text-slate-400" />
                      <span className="truncate">{b.title}</span>
                    </a>
                    <button
                      onClick={() => handleDeleteBookmark(b.id)}
                      className="text-slate-400 hover:text-rose-500 p-1 cursor-pointer"
                    >
                      <Trash2 className="w-3.5 h-3.5" />
                    </button>
                  </div>
                ))}
              </div>
            </div>

            {/* Recently Viewed */}
            <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
              <h3 className="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <History className="w-5 h-5 text-emerald-500" /> Recently Viewed
              </h3>
              <div className="space-y-2">
                {recentlyViewed.map((item) => (
                  <a
                    key={item.id}
                    href={item.target_url}
                    className="block p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors text-xs"
                  >
                    <p className="font-semibold text-slate-900 dark:text-slate-200 truncate">{item.title}</p>
                    <p className="text-[10px] text-slate-400 capitalize">{item.item_type}</p>
                  </a>
                ))}
              </div>
            </div>
          </div>
        </div>
      ) : (
        /* Task Stream View */
        <>
          <div className="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div className="relative w-full sm:w-80">
              <Search className="absolute left-3.5 top-2.5 w-4 h-4 text-slate-400" />
              <input
                type="text"
                placeholder="Search my tasks..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none"
              />
            </div>

            <div className="flex items-center gap-3 w-full sm:w-auto">
              <Filter className="w-4 h-4 text-slate-400 shrink-0" />
              <select
                value={projectFilter}
                onChange={(e) => setProjectFilter(e.target.value)}
                className="px-3.5 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none"
              >
                <option value="ALL">All Projects</option>
                {projects.map((p) => (
                  <option key={p.id} value={p.id}>
                    {p.name}
                  </option>
                ))}
              </select>

              <select
                value={priorityFilter}
                onChange={(e) => setPriorityFilter(e.target.value)}
                className="px-3.5 py-2 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none"
              >
                <option value="ALL">All Priorities</option>
                <option value="LOW">LOW</option>
                <option value="MEDIUM">MEDIUM</option>
                <option value="HIGH">HIGH</option>
                <option value="URGENT">URGENT</option>
              </select>
            </div>
          </div>

          {loading ? (
            <div className="py-12 text-center text-xs text-slate-500 font-semibold">
              Loading assigned tasks...
            </div>
          ) : filteredTasks.length === 0 ? (
            <div className="text-center py-16 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-3">
              <CheckSquare className="w-10 h-10 text-slate-400 mx-auto" />
              <h3 className="text-base font-bold text-slate-900 dark:text-slate-100">No Tasks in {activeTab}</h3>
              <p className="text-xs text-slate-500">You are all caught up for this view filter!</p>
            </div>
          ) : (
            <div className="space-y-3">
              {filteredTasks.map((t) => (
                <TaskRow
                  key={t.id}
                  task={t}
                  onToggleComplete={handleToggleComplete}
                  onClickTask={(task) => setSelectedTaskId(task.id)}
                />
              ))}
            </div>
          )}
        </>
      )}

      {/* Detail Modal */}
      <TaskDetailModal
        isOpen={Boolean(selectedTaskId)}
        onClose={() => setSelectedTaskId(null)}
        taskId={selectedTaskId}
        todoLists={[]}
        onSuccess={loadUserTasks}
      />
    </div>
  );
}

