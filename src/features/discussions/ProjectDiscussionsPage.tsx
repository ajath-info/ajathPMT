import React, { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Bookmark,
  MoreHorizontal,
  Plus,
  ChevronDown,
  Search,
  MessageSquare,
  Check,
  ArrowLeft,
  Eye,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useProject } from '../../context/ProjectContext';
import { Discussion } from '../../types';
import { getDiscussions } from '../../services/discussionService';
import { getProjectById } from '../../services/projectService';
import { CreateDiscussionModal } from '../../components/discussions/CreateDiscussionModal';
import { DiscussionDetailModal } from '../../components/discussions/DiscussionDetailModal';

// Avatar color palette matching Basecamp
const AVATAR_COLORS: Record<string, string> = {
  AI: 'bg-teal-500',
  GK: 'bg-pink-500',
  PK: 'bg-cyan-500',
  RK: 'bg-rose-500',
  SN: 'bg-purple-600',
  CW: 'bg-purple-600',
  SS: 'bg-blue-600',
  E: 'bg-orange-500',
  RS: 'bg-teal-600',
  B: 'bg-amber-500',
  PT: 'bg-slate-500',
  TL: 'bg-indigo-600',
  AR: 'bg-emerald-600',
};

function getInitials(fullName?: string): string {
  if (!fullName) return 'CW';
  if (fullName === 'Claire Watson') return 'CW';
  if (fullName === 'Edward') return 'E';
  if (fullName === 'Shachish Sneh') return 'SS';
  if (fullName === 'Gaurav Kumar') return 'GK';
  if (fullName === 'Pankaj Kumar') return 'PK';
  if (fullName === 'Rohit Kumar') return 'RK';
  if (fullName === 'Rekha Singh' || fullName === 'Rasgo') return 'RS';
  if (fullName === 'Pradeep Tiwari') return 'PT';
  const parts = fullName.trim().split(' ');
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function getAvatarColor(initials: string): string {
  if (AVATAR_COLORS[initials]) return AVATAR_COLORS[initials];
  return 'bg-blue-500';
}

export function ProjectDiscussionsPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const navigate = useNavigate();
  const { projects } = useProject();
  const { profile, userRole } = useAuth();

  const targetProjectId = projectId || 'proj-rmc';
  const [project, setProject] = useState<any>(null);

  const [discussions, setDiscussions] = useState<Discussion[]>([]);
  const [loading, setLoading] = useState(true);

  // Filters & Sorting
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string>('ALL');
  const [sortBy, setSortBy] = useState<'newest' | 'oldest' | 'comments'>('newest');

  // Menus
  const [isCategoryMenuOpen, setIsCategoryMenuOpen] = useState(false);
  const [isSortMenuOpen, setIsSortMenuOpen] = useState(false);
  const [isMoreMenuOpen, setIsMoreMenuOpen] = useState(false);
  const [isBookmarked, setIsBookmarked] = useState<boolean>(() => {
    try {
      const saved = localStorage.getItem(`basecamp_bookmark_mb_${targetProjectId}`);
      return saved === 'true';
    } catch {
      return false;
    }
  });

  // Modals
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [selectedDiscussionId, setSelectedDiscussionId] = useState<string | null>(null);

  const categoryMenuRef = useRef<HTMLDivElement>(null);
  const sortMenuRef = useRef<HTMLDivElement>(null);
  const moreMenuRef = useRef<HTMLDivElement>(null);

  // Load project details
  useEffect(() => {
    if (targetProjectId) {
      getProjectById(targetProjectId).then(setProject).catch(() => {});
    }
  }, [targetProjectId]);

  const loadData = async () => {
    setLoading(true);
    try {
      const list = await getDiscussions(targetProjectId);
      setDiscussions(list);
    } catch (err) {
      console.error('Failed to load discussions', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [targetProjectId]);

  // Click outside listeners for menus
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (categoryMenuRef.current && !categoryMenuRef.current.contains(e.target as Node)) {
        setIsCategoryMenuMenuOpen(false);
      }
      if (sortMenuRef.current && !sortMenuRef.current.contains(e.target as Node)) {
        setIsSortMenuOpen(false);
      }
      if (moreMenuRef.current && !moreMenuRef.current.contains(e.target as Node)) {
        setIsMoreMenuOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const setIsCategoryMenuMenuOpen = setIsCategoryMenuOpen;

  const handleToggleBookmark = () => {
    setIsBookmarked((prev) => {
      const next = !prev;
      try {
        localStorage.setItem(`basecamp_bookmark_mb_${targetProjectId}`, String(next));
      } catch {}
      return next;
    });
  };

  const isDiscussionVisibleToClient = (d: Discussion) => {
    if (d.is_client_visible === false) return false;
    if (d.category === 'Worklog') return false;
    const authorEmail = d.author?.email?.toLowerCase() || '';
    const authorName = d.author?.full_name?.toLowerCase() || '';
    if (d.author_id === profile?.id || (profile?.email && authorEmail === profile?.email?.toLowerCase())) return true;
    if (authorEmail.includes('client') || authorEmail.includes('edward') || authorName.includes('edward')) return true;
    return d.is_client_visible === true;
  };

  const categories = userRole === 'CLIENT'
    ? ['ALL', 'General', 'Meetings', 'Announcements']
    : ['ALL', 'General', 'Worklog', 'Meetings', 'Announcements'];

  // Filtering & Sorting
  const filteredDiscussions = discussions
    .filter((d) => {
      // Basecamp Role Gate: Clients can only see client-approved discussions
      if (userRole === 'CLIENT' && !isDiscussionVisibleToClient(d)) return false;
      if (selectedCategory !== 'ALL' && d.category !== selectedCategory) return false;
      if (!searchQuery.trim()) return true;
      const q = searchQuery.toLowerCase();
      return (
        d.title.toLowerCase().includes(q) ||
        d.content.toLowerCase().includes(q) ||
        d.author?.full_name.toLowerCase().includes(q)
      );
    })
    .sort((a, b) => {
      if (sortBy === 'comments') {
        return (b.comments_count || 0) - (a.comments_count || 0);
      }
      if (sortBy === 'oldest') {
        return new Date(a.created_at).getTime() - new Date(b.created_at).getTime();
      }
      return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
    });

  const selectedDiscussion = discussions.find((d) => d.id === selectedDiscussionId);

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 pb-16">
      {/* Modern Discussions Card */}
      <div className="w-full bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
        {/* 1. Top Utility Bar: Project Link on left | Bookmark & More on right */}
        <div className="flex items-center justify-between pb-2">
          {/* Project Link on left */}
          <button
            onClick={() => navigate(`/projects/${targetProjectId}`)}
            className="font-bold text-sm text-slate-800 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 hover:underline transition-colors cursor-pointer"
          >
            {project?.name || 'Ride My Cars (Edward)'}
          </button>

          {/* Bookmark & More on right */}
          <div className="flex items-center gap-2 relative">
            <button
              onClick={handleToggleBookmark}
              className={`flex items-center gap-1.5 text-xs font-semibold py-1 px-2.5 rounded-lg transition-colors cursor-pointer ${
                isBookmarked
                  ? 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800'
              }`}
            >
              <Bookmark
                className={`w-3.5 h-3.5 ${
                  isBookmarked ? 'fill-amber-400 text-amber-400' : 'text-slate-400'
                }`}
              />
              <span>{isBookmarked ? 'Bookmarked' : 'Bookmark'}</span>
            </button>

            <div ref={moreMenuRef} className="relative">
              <button
                onClick={() => setIsMoreMenuOpen(!isMoreMenuOpen)}
                className="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                title="Options"
              >
                <MoreHorizontal className="w-4 h-4" />
              </button>

              {isMoreMenuOpen && (
                <div className="absolute right-0 top-8 w-48 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 py-1.5 z-50 text-xs animate-in zoom-in-95 duration-100">
                  <button
                    onClick={() => {
                      setIsMoreMenuOpen(false);
                      setIsCreateModalOpen(true);
                    }}
                    className="w-full px-3.5 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium"
                  >
                    Post new message
                  </button>
                  <button
                    onClick={() => {
                      setIsMoreMenuOpen(false);
                      navigate(`/projects/${targetProjectId}/settings`);
                    }}
                    className="w-full px-3.5 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium"
                  >
                    Change tool settings
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* 2. Main Title: Message Board */}
        <div>
          <h1 className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Message Board
          </h1>
        </div>

        {/* 3. Filter & Action Toolbar */}
        <div className="flex flex-wrap items-center gap-2.5 pt-1">
          {/* + New message Button */}
          <button
            onClick={() => navigate(`/projects/${targetProjectId}/messages/new`)}
            className="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-md shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer flex items-center gap-1.5"
          >
            <Plus className="w-3.5 h-3.5 stroke-[3]" />
            <span>New message</span>
          </button>

          {/* Quick Category Filter Pills */}
          <div className="flex items-center gap-1.5 flex-wrap">
            <button
              onClick={() => {
                setSelectedCategory('ALL');
                setSearchQuery('');
              }}
              className={`px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer ${
                selectedCategory === 'ALL'
                  ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-900 dark:border-white shadow-xs'
                  : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
              }`}
            >
              All messages
            </button>
            <button
              onClick={() => setSelectedCategory('Announcements')}
              className={`px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer ${
                selectedCategory === 'Announcements'
                  ? 'bg-emerald-500 text-white border-emerald-500 shadow-xs shadow-emerald-500/30'
                  : 'border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 bg-emerald-50/60 dark:bg-emerald-950/30 hover:bg-emerald-100/60'
              }`}
            >
              📢 Announcements
            </button>
            {userRole !== 'CLIENT' && (
              <button
                onClick={() => setSelectedCategory('Worklog')}
                className={`px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer ${
                  selectedCategory === 'Worklog'
                    ? 'bg-purple-600 text-white border-purple-600 shadow-xs shadow-purple-500/30'
                    : 'border-purple-200 dark:border-purple-900/40 text-purple-700 dark:text-purple-400 bg-purple-50/60 dark:bg-purple-950/30 hover:bg-purple-100/60'
                }`}
              >
                ⚡ Worklog
              </button>
            )}
            <button
              onClick={() => setSelectedCategory('Meetings')}
              className={`px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer ${
                selectedCategory === 'Meetings'
                  ? 'bg-amber-500 text-white border-amber-500 shadow-xs shadow-amber-500/30'
                  : 'border-amber-200 dark:border-amber-900/40 text-amber-700 dark:text-amber-400 bg-amber-50/60 dark:bg-amber-950/30 hover:bg-amber-100/60'
              }`}
            >
              🗓 Meetings
            </button>
          </div>

          {/* Categories ⌄ Dropdown */}
          <div className="relative" ref={categoryMenuRef}>
            <button
              onClick={() => setIsCategoryMenuOpen(!isCategoryMenuOpen)}
              className="px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-1 transition-colors cursor-pointer"
            >
              <span>{selectedCategory === 'ALL' ? 'More categories' : selectedCategory}</span>
              <ChevronDown className="w-3.5 h-3.5 text-slate-400" />
            </button>

            {isCategoryMenuOpen && (
              <div className="absolute left-0 top-9 w-44 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 py-1.5 z-50 text-xs animate-in zoom-in-95 duration-100">
                {categories.map((cat) => (
                  <button
                    key={cat}
                    onClick={() => {
                      setSelectedCategory(cat);
                      setIsCategoryMenuOpen(false);
                    }}
                    className="w-full px-3.5 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-between text-slate-700 dark:text-slate-300 font-medium"
                  >
                    <span>{cat === 'ALL' ? 'All Categories' : cat}</span>
                    {selectedCategory === cat && <Check className="w-3.5 h-3.5 text-blue-600" />}
                  </button>
                ))}
              </div>
            )}
          </div>

          {/* Newest post ⌄ Sort Dropdown */}
          <div className="relative" ref={sortMenuRef}>
            <button
              onClick={() => setIsSortMenuOpen(!isSortMenuOpen)}
              className="px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-1 transition-colors cursor-pointer"
            >
              <span>
                {sortBy === 'newest'
                  ? 'Newest post'
                  : sortBy === 'oldest'
                  ? 'Oldest post'
                  : 'Most comments'}
              </span>
              <ChevronDown className="w-3.5 h-3.5 text-slate-400" />
            </button>

            {isSortMenuOpen && (
              <div className="absolute left-0 top-9 w-40 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 py-1.5 z-50 text-xs animate-in zoom-in-95 duration-100">
                <button
                  onClick={() => {
                    setSortBy('newest');
                    setIsSortMenuOpen(false);
                  }}
                  className="w-full px-3.5 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium"
                >
                  Newest post
                </button>
                <button
                  onClick={() => {
                    setSortBy('oldest');
                    setIsSortMenuOpen(false);
                  }}
                  className="w-full px-3.5 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium"
                >
                  Oldest post
                </button>
                <button
                  onClick={() => {
                    setSortBy('comments');
                    setIsSortMenuOpen(false);
                  }}
                  className="w-full px-3.5 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium"
                >
                  Most comments
                </button>
              </div>
            )}
          </div>

          {/* Search Filter Input */}
          <div className="relative ml-auto">
            <input
              type="text"
              placeholder="Filter..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-36 sm:w-48 px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 font-medium transition-all"
            />
          </div>
        </div>

        {/* 4. Stream of Discussions matching screenshot */}
        <div className="pt-2 divide-y divide-slate-100 dark:divide-slate-800/80">
          {filteredDiscussions.length === 0 ? (
            <div className="py-16 text-center text-xs text-slate-400 space-y-2">
              <MessageSquare className="w-8 h-8 text-slate-300 mx-auto" />
              <p className="font-bold text-slate-700 dark:text-slate-300">No messages found</p>
              <p>Post an announcement or start a discussion with your team.</p>
            </div>
          ) : (
            filteredDiscussions.map((d) => {
              const authorName = d.author?.full_name || 'Edward';
              const initials = getInitials(authorName);
              const color = getAvatarColor(initials);
              const dateStr = d.created_at
                ? new Date(d.created_at).toLocaleDateString([], { month: 'short', day: 'numeric' })
                : 'Aug 26';

              // For Internal Team: determine whether the client can see this discussion
              const isItemClientVisible = isDiscussionVisibleToClient(d);

              return (
                <div
                  key={d.id}
                  onClick={() => navigate(`/projects/${targetProjectId}/messages/${d.id}`)}
                  className="py-5 flex items-start gap-4 hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors cursor-pointer rounded-2xl px-2 sm:px-3 group select-none"
                >
                  {/* Avatar Icon */}
                  <div
                    className={`w-9 h-9 sm:w-10 sm:h-10 rounded-full ${color} text-white font-black text-xs flex items-center justify-center shrink-0 shadow-xs select-none mt-0.5`}
                  >
                    {initials}
                  </div>

                  {/* Content Body */}
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 flex-wrap">
                      <h3 className="font-extrabold text-[15px] sm:text-base text-slate-900 dark:text-slate-100 group-hover:text-blue-600 transition-colors underline-offset-2 group-hover:underline">
                        {d.title || 'Untitled'}
                      </h3>

                      {/* Internal Staff Visibility Badge */}
                      {userRole !== 'CLIENT' && (
                        isItemClientVisible ? (
                          <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100/90 text-amber-900 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            <span className="text-xs">👁</span>
                            <span>The client can see this</span>
                          </span>
                        ) : (
                          <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            <span className="text-xs">🔒</span>
                            <span>The client cannot see this</span>
                          </span>
                        )
                      )}
                    </div>

                    {/* Metadata line: Author • Date • Snippet */}
                    <div className="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                      <span className="font-medium text-slate-800 dark:text-slate-200">
                        {authorName}
                      </span>
                      <span className="mx-1.5">•</span>
                      <span>{dateStr}</span>
                      {d.content && (
                        <>
                          <span className="mx-1.5">•</span>
                          <span className="text-slate-600 dark:text-slate-400 line-clamp-1 sm:line-clamp-2 inline">
                            {d.content}
                          </span>
                        </>
                      )}
                    </div>
                  </div>

                  {/* Right: Comments Count Blue Pill Badge */}
                  <div className="shrink-0 pt-1">
                    <span className="min-w-6 h-6 px-1.5 rounded-full bg-[#0c66e4] text-white font-black text-xs flex items-center justify-center shadow-xs">
                      {d.comments_count || 0}
                    </span>
                  </div>
                </div>
              );
            })
          )}
        </div>
      </div>

      {/* Modals */}
      <CreateDiscussionModal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
        projectId={targetProjectId}
        authorId={profile?.id}
        onSuccess={loadData}
      />

      <DiscussionDetailModal
        isOpen={Boolean(selectedDiscussionId)}
        onClose={() => setSelectedDiscussionId(null)}
        discussionId={selectedDiscussionId}
        discussionItem={discussions.find((d) => d.id === selectedDiscussionId) || null}
        currentUserId={profile?.id}
        onSuccess={loadData}
      />
    </div>
  );
}
