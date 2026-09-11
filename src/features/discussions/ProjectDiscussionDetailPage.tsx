import React, { useState, useEffect, useRef, useMemo } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import {
  Bookmark,
  MoreHorizontal,
  Send,
  Eye,
  Lock,
  ArrowUp,
  Smile,
  Paperclip,
  Check,
  Share2,
  Trash2,
  Edit,
  FileText,
  Bold,
  Italic,
  Strikethrough,
  Link2,
  List,
  ListOrdered,
  Quote,
  Code,
  X,
  File as FileIcon,
  Download,
} from 'lucide-react';
import {
  getDiscussionById,
  getDiscussionComments,
  getDiscussionVersions,
  addDiscussionComment,
  toggleReaction,
  toggleClientVisibility,
} from '../../services/discussionService';
import { getProjectById } from '../../services/projectService';
import { Discussion, DiscussionComment, DiscussionVersion, Project } from '../../types';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { recordRecentlyVisitedItem } from '../../components/layout/BasecampNavDropdown';

interface AttachedFileItem {
  id: string;
  name: string;
  size: string;
  type: string;
  url?: string;
  file?: File;
}

const AVATAR_COLORS: Record<string, string> = {
  E: 'bg-[#ea580c]',
  RS: 'bg-[#0d9488]',
  SN: 'bg-[#9333ea]',
  PK: 'bg-[#16a34a]',
  GK: 'bg-[#2563eb]',
  RK: 'bg-[#d97706]',
  CW: 'bg-[#9333ea]',
  AV: 'bg-[#2563eb]',
};

function getInitials(name: string): string {
  if (!name) return 'U';
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function getAvatarColor(initials: string): string {
  if (AVATAR_COLORS[initials]) return AVATAR_COLORS[initials];
  if (initials.startsWith('E')) return 'bg-[#ea580c]';
  if (initials.startsWith('R')) return 'bg-[#0d9488]';
  if (initials.startsWith('S')) return 'bg-[#9333ea]';
  if (initials.startsWith('P')) return 'bg-[#16a34a]';
  if (initials.startsWith('C')) return 'bg-[#9333ea]';
  return 'bg-[#2563eb]';
}

function formatBytes(bytes: number): string {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

// Safely render rich text formatted as HTML or Markdown
function renderFormattedContent(raw: string): string {
  if (!raw) return '';

  // If already contains HTML markup (from WYSIWYG editor)
  if (/<(p|strong|em|u|s|del|blockquote|pre|code|ul|ol|li|a|br)\b/i.test(raw)) {
    return raw;
  }

  // Convert markdown to HTML
  let out = raw
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

  // Bold: **text** or __text__
  out = out.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
  out = out.replace(/__(.*?)__/g, '<strong>$1</strong>');

  // Italic: *text* or _text_
  out = out.replace(/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');
  out = out.replace(/(?<!_)_(?!_)(.*?)(?<!_)_(?!_)/g, '<em>$1</em>');

  // Strikethrough: ~~text~~
  out = out.replace(/~~(.*?)~~/g, '<del class="line-through">$1</del>');

  // Inline code: `code`
  out = out.replace(
    /`([^`]+)`/g,
    '<code class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 text-xs font-mono">$1</code>'
  );

  // Line breaks
  out = out.replace(/\n/g, '<br />');

  return out;
}

export function ProjectDiscussionDetailPage() {
  const { projectId, messageId, discussionId } = useParams<{
    projectId: string;
    messageId?: string;
    discussionId?: string;
  }>();
  const targetId = messageId || discussionId || 'disc-rmc-1';
  const targetProjectId = projectId || 'proj-rmc';

  const navigate = useNavigate();
  const { profile, userRole } = useAuth();
  const { addToast } = useToast();

  const [project, setProject] = useState<Project | null>(null);
  const [discussion, setDiscussion] = useState<Discussion | null>(null);
  const [comments, setComments] = useState<DiscussionComment[]>([]);
  const [loading, setLoading] = useState(true);

  // Visibility & Menus
  const [showAllComments, setShowAllComments] = useState(false);
  const [isMoreMenuOpen, setIsMoreMenuOpen] = useState(false);
  const [isVisibilityMenuOpen, setIsVisibilityMenuOpen] = useState(false);
  const [isNotifiedModalOpen, setIsNotifiedModalOpen] = useState(false);
  const [isEmojiPickerOpen, setIsEmojiPickerOpen] = useState(false);
  const [isHistoryModalOpen, setIsHistoryModalOpen] = useState(false);
  const [versions, setVersions] = useState<DiscussionVersion[]>([]);

  // Bookmarking
  const bookmarkKey = `basecamp_bookmark_msg_${targetId}`;
  const [isBookmarked, setIsBookmarked] = useState<boolean>(() => {
    try {
      return localStorage.getItem(bookmarkKey) === 'true';
    } catch {
      return false;
    }
  });

  // Rich Comment Editor State
  const editorRef = useRef<HTMLDivElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [isEditorEmpty, setIsEditorEmpty] = useState(true);
  const [attachedFiles, setAttachedFiles] = useState<AttachedFileItem[]>([]);
  const [isDraggingOver, setIsDraggingOver] = useState(false);
  const [submittingComment, setSubmittingComment] = useState(false);
  const [notifyPeopleChecked, setNotifyPeopleChecked] = useState(true);

  const moreMenuRef = useRef<HTMLDivElement>(null);
  const visibilityMenuRef = useRef<HTMLDivElement>(null);
  const emojiPickerRef = useRef<HTMLDivElement>(null);

  const notifiedPeople = useMemo(() => {
    if (project?.members && project.members.length > 0) {
      const colors = ['bg-[#ea580c]', 'bg-[#9333ea]', 'bg-[#0d9488]', 'bg-[#16a34a]', 'bg-blue-600', 'bg-indigo-600', 'bg-amber-600'];
      return project.members.map((m, idx) => ({
        id: m.user_id || m.id,
        name: m.profile?.full_name || 'Team Member',
        role: m.role === 'PROJECT_CLIENT'
          ? 'Client'
          : m.profile?.job_title || (m.role === 'PROJECT_OWNER' ? 'Project Owner' : 'Team Member'),
        client: m.role === 'PROJECT_CLIENT',
        col: colors[idx % colors.length],
      }));
    }
    const authorName = discussion?.author?.full_name || profile?.full_name || 'Project Author';
    return [
      {
        id: discussion?.author_id || profile?.id || 'author',
        name: authorName,
        role: 'Author',
        client: false,
        col: 'bg-indigo-600',
      },
    ];
  }, [project?.members, discussion, profile]);

  const loadData = async () => {
    setLoading(true);
    try {
      const [projData, discData, commData, verData] = await Promise.all([
        getProjectById(targetProjectId),
        getDiscussionById(targetId),
        getDiscussionComments(targetId),
        getDiscussionVersions(targetId),
      ]);
      setProject(projData);
      setDiscussion(discData);
      setComments(commData);
      setVersions(verData || []);

      // Dynamically record recently visited discussion
      if (discData && projData?.organization_id) {
        recordRecentlyVisitedItem(projData.organization_id, {
          id: `disc-${discData.id}`,
          title: discData.title,
          subtitle: projData.name || '',
          type: 'discussion',
          path: `/projects/${targetProjectId}/discussions/${discData.id}`,
        });
      }
    } catch (err) {
      console.error('Failed to load discussion details', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [targetId, targetProjectId]);

  // Click outside listener
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (moreMenuRef.current && !moreMenuRef.current.contains(e.target as Node)) {
        setIsMoreMenuOpen(false);
      }
      if (visibilityMenuRef.current && !visibilityMenuRef.current.contains(e.target as Node)) {
        setIsVisibilityMenuOpen(false);
      }
      if (emojiPickerRef.current && !emojiPickerRef.current.contains(e.target as Node)) {
        setIsEmojiPickerOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleBookmarkToggle = () => {
    const next = !isBookmarked;
    setIsBookmarked(next);
    try {
      localStorage.setItem(bookmarkKey, String(next));
      addToast(next ? 'Bookmarked this message' : 'Bookmark removed', 'info');
    } catch {
      // ignore
    }
  };

  const handleToggleVisibility = async (val: boolean) => {
    if (!discussion) return;
    try {
      const updated = await toggleClientVisibility(discussion.id, val);
      setDiscussion(updated);
      setIsVisibilityMenuOpen(false);
      addToast(
        val
          ? 'The client can now see this message'
          : 'This message is now internal only (hidden from client)',
        'success'
      );
    } catch (err) {
      console.error(err);
    }
  };

  const handleReact = async (emoji: string) => {
    if (!discussion) return;
    try {
      const userName = profile?.full_name || 'Shivy Narain';
      const updated = await toggleReaction(discussion.id, emoji, userName);
      setDiscussion(updated);
      setIsEmojiPickerOpen(false);
    } catch (err) {
      console.error(err);
    }
  };

  const handleDownloadAttachment = (filename: string, fileUrl?: string) => {
    if (fileUrl) {
      const a = document.createElement('a');
      a.href = fileUrl;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      addToast(`Downloading ${filename}...`, 'success');
      return;
    }

    const sampleDocxContent = `RIDE MY CARS (EDWARD) - TERMS AND CONDITIONS\n\n1. SCOPE AND DEFINITIONS\nRide My Cars operates as an on-demand mobility platform connecting independent drivers with verified riders.\n\n2. VEHICLE COMPLIANCE & SAFETY\nAll vehicles must pass quarterly mechanical inspections matching transport safety guidelines section 12.\n\n3. CANCELLATION AND REFUND POLICY\nPassenger cancellations exceeding 5 minutes from driver dispatch incur a standard cancellation fee.\n\n4. PAYMENT PROCESSING & ESCROW\nFares are settled securely via integrated payment gateways with automated driver commission disbursement.\n\n5. CONFIDENTIALITY & DATA PROTECTION\nIn compliance with Nigeria Data Protection Act requirements, passenger and driver locations are encrypted at rest.`;
    const blob = new Blob([sampleDocxContent], {
      type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    addToast(`Downloading ${filename}...`, 'success');
  };

  // WYSIWYG Editor Commands
  const executeCommand = (cmd: string, val: string | undefined = undefined) => {
    if (editorRef.current) {
      editorRef.current.focus();
    }
    document.execCommand(cmd, false, val);
    handleEditorInput();
  };

  const handleLinkCommand = () => {
    const url = prompt('Enter web link URL (e.g. https://example.com):');
    if (url) {
      executeCommand('createLink', url);
    }
  };

  const handleEditorInput = () => {
    if (!editorRef.current) return;
    const text = editorRef.current.innerText.trim();
    setIsEditorEmpty(text.length === 0);
  };

  // File Upload Handlers
  const handleTriggerFileInput = () => {
    fileInputRef.current?.click();
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    if (!files.length) return;
    processFiles(files);
    e.target.value = '';
  };

  const processFiles = (files: File[]) => {
    const newItems: AttachedFileItem[] = files.map((file) => ({
      id: `file-${Date.now()}-${Math.random().toString(36).substring(2, 6)}`,
      name: file.name,
      size: formatBytes(file.size),
      type: file.type || 'document',
      url: URL.createObjectURL(file),
      file,
    }));
    setAttachedFiles((prev) => [...prev, ...newItems]);
    addToast(`Attached ${files.length} file(s)`, 'info');
  };

  const handleRemoveAttachedFile = (fileId: string) => {
    setAttachedFiles((prev) => prev.filter((f) => f.id !== fileId));
  };

  // Drag and Drop
  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDraggingOver(true);
  };

  const handleDragLeave = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDraggingOver(false);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDraggingOver(false);
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
      processFiles(Array.from(e.dataTransfer.files));
    }
  };

  // Submit Comment
  const handleAddComment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editorRef.current || !discussion) return;

    const htmlContent = editorRef.current.innerHTML.trim();
    const textContent = editorRef.current.innerText.trim();

    // Check if there is either text or attached files
    if (!textContent && attachedFiles.length === 0) {
      addToast('Please write a message or attach a file', 'info');
      return;
    }

    setSubmittingComment(true);
    try {
      const currentUserName = profile?.full_name || 'Shivy Narain';
      const currentUserRole =
        profile?.job_title || (userRole === 'CLIENT' ? 'Client Partner' : 'Project Lead');
      const isClientUser = userRole === 'CLIENT' || currentUserName.toLowerCase().includes('claire');
      const userId = profile?.id || 'demo-user-owner';

      // Map attachments
      const attachmentsPayload = attachedFiles.map((f) => ({
        name: f.name,
        size: f.size,
        type: f.type,
        url: f.url,
      }));

      await addDiscussionComment(
        discussion.id,
        userId,
        htmlContent || textContent,
        undefined,
        {
          full_name: currentUserName,
          email: profile?.email || 'shivy@ajath.com',
          role: currentUserRole,
          is_client: isClientUser,
          avatar_url: profile?.avatar_url || '',
        },
        attachmentsPayload
      );

      // Reset editor and attachments
      editorRef.current.innerHTML = '';
      setIsEditorEmpty(true);
      setAttachedFiles([]);

      const updatedComments = await getDiscussionComments(discussion.id);
      setComments(updatedComments);
      addToast('Comment posted', 'success');
    } catch (err) {
      console.error(err);
      addToast('Failed to post comment', 'error');
    } finally {
      setSubmittingComment(false);
    }
  };

  if (loading) {
    return (
      <div className="w-full max-w-7xl mx-auto space-y-6 pt-4 pb-16">
        <div className="w-full bg-white dark:bg-slate-900 rounded-3xl min-h-[400px] p-12 text-center text-slate-400 font-semibold text-sm animate-pulse border border-slate-200/80 dark:border-slate-800 flex items-center justify-center">
          Loading discussion...
        </div>
      </div>
    );
  }

  const authorName = discussion?.author?.full_name || 'Edward';
  const authorInitials = getInitials(authorName);
  const authorColor = getAvatarColor(authorInitials);
  const isClient = authorName.toLowerCase().includes('edward') || discussion?.author_id === 'user-e';
  const isClientVisible =
    discussion?.is_client_visible === true ||
    (discussion?.is_client_visible !== false &&
      discussion?.category !== 'Worklog' &&
      (isClient || (profile?.email && discussion?.author?.email === profile?.email)));

  const currentUserName = profile?.full_name || 'Shivy Narain';
  const currentUserInitials = getInitials(currentUserName);
  const currentUserColor = getAvatarColor(currentUserInitials);

  // Split comments for "See previous comments"
  const visibleComments =
    showAllComments || comments.length <= 2 ? comments : comments.slice(comments.length - 2);
  const hiddenCount = comments.length - visibleComments.length;

  // Basecamp Role Gate: If discussion is internal only, clients cannot access it
  if (userRole === 'CLIENT' && !isClientVisible) {
    return (
      <div className="w-full max-w-4xl mx-auto py-12 px-4 animate-in fade-in duration-300">
        <div className="bg-white dark:bg-slate-900 rounded-3xl p-8 sm:p-12 border border-slate-200/80 dark:border-slate-800 shadow-sm text-center space-y-6">
          <div className="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 mx-auto flex items-center justify-center shadow-inner">
            <Lock className="w-8 h-8" />
          </div>
          <div className="space-y-2">
            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
              <span>Internal Discussion Only</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
              The client cannot see this message
            </h1>
            <p className="text-sm text-slate-500 dark:text-slate-400 max-w-lg mx-auto leading-relaxed">
              In Ajath PMT, items marked "The client cannot see this" or "Internal Only" are restricted to company team members and are hidden from client partners.
            </p>
          </div>
          <div className="pt-2 flex items-center justify-center gap-3">
            <button
              onClick={() => navigate(`/projects/${targetProjectId}/discussions`)}
              className="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 active:scale-95 transition-all cursor-pointer"
            >
              Return to Message Board
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-300 pb-16 font-sans">
      <div className="relative">
        {/* Client Visibility Tab on the top edge (Internal staff only) */}
        {userRole !== 'CLIENT' && (
          <div
            ref={visibilityMenuRef}
            className="absolute right-4 sm:right-8 -top-3 z-30 flex items-center"
          >
            <div
              onClick={() => setIsVisibilityMenuOpen(!isVisibilityMenuOpen)}
              className={`shadow-md rounded-xl border select-none px-3 py-1.5 flex items-center gap-1.5 cursor-pointer hover:scale-105 transition-transform ${
                isClientVisible
                  ? 'bg-amber-400 border-amber-500 text-amber-950'
                  : 'bg-slate-200 border-slate-300 text-slate-700 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300'
              }`}
              title="Click to toggle client visibility"
            >
              <span className="text-xs">{isClientVisible ? '👁' : '🔒'}</span>
              <span className="text-xs font-bold tracking-tight">
                {isClientVisible ? 'The client can see this' : 'The client cannot see this (Internal only)'}{' '}
                <span className="underline font-extrabold ml-1">Change</span>
              </span>
            </div>

          {/* Visibility Popover Dropdown (internal team only) */}
          {isVisibilityMenuOpen && (
            <div className="absolute right-0 top-10 w-72 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-4 z-40 text-left animate-in fade-in zoom-in-95">
              <h4 className="font-extrabold text-sm text-slate-900 mb-1">Client Visibility</h4>
              <p className="text-xs text-slate-500 mb-4 leading-relaxed">
                Control whether client team members can read this discussion topic and participate in
                replies.
              </p>
              <div className="space-y-2">
                <button
                  type="button"
                  onClick={() => handleToggleVisibility(true)}
                  className={`w-full flex items-start gap-3 p-2.5 rounded-xl text-left border transition-all ${
                    isClientVisible
                      ? 'bg-amber-50/70 border-amber-300 text-amber-950 font-bold'
                      : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-700'
                  }`}
                >
                  <Eye className="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
                  <div className="text-xs">
                    <p className="font-bold">The client can see this</p>
                    <p className="text-[11px] text-slate-500 font-normal">
                      Visible to Edward and NDC Homes client stakeholders
                    </p>
                  </div>
                  {isClientVisible && <Check className="w-4 h-4 text-amber-700 ml-auto shrink-0" />}
                </button>

                <button
                  type="button"
                  onClick={() => handleToggleVisibility(false)}
                  className={`w-full flex items-start gap-3 p-2.5 rounded-xl text-left border transition-all ${
                    !isClientVisible
                      ? 'bg-slate-100 border-slate-300 text-slate-900 font-bold'
                      : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-700'
                  }`}
                >
                  <Lock className="w-4 h-4 text-slate-500 shrink-0 mt-0.5" />
                  <div className="text-xs">
                    <p className="font-bold">Internal only</p>
                    <p className="text-[11px] text-slate-500 font-normal">
                      Only internal company members can view this message
                    </p>
                  </div>
                  {!isClientVisible && <Check className="w-4 h-4 text-slate-700 ml-auto shrink-0" />}
                </button>
              </div>
            </div>
          )}
        </div>
      )}

        {/* Modern Document Card Container */}
        <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-12 shadow-sm border border-slate-200/80 dark:border-slate-800 relative">
          {/* Card Top Utility Bar */}
          <div className="flex items-center justify-between pb-6 border-b border-slate-100/90 text-sm">
            {/* Left: Breadcrumbs */}
            <div className="flex items-center gap-2 text-sm font-sans flex-wrap">
              <Link
                to={`/projects/${targetProjectId}`}
                className="font-extrabold text-slate-900 hover:underline transition-colors"
              >
                {project?.name || 'Ride My Cars (Edward)'}
              </Link>
              <span className="text-slate-400 font-bold">‹</span>
              <Link
                to={`/projects/${targetProjectId}/discussions`}
                className="text-slate-500 hover:text-slate-900 font-medium hover:underline transition-colors"
              >
                Message Board
              </Link>
            </div>

            {/* Right: Bookmark & More menu */}
            <div className="flex items-center gap-3">
              <button
                type="button"
                onClick={handleBookmarkToggle}
                className={`inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg border transition-all cursor-pointer ${
                  isBookmarked
                    ? 'bg-amber-50 text-amber-800 border-amber-300 shadow-xs'
                    : 'text-slate-600 hover:text-slate-900 border-transparent hover:border-slate-200 hover:bg-slate-50'
                }`}
              >
                <Bookmark className={`w-3.5 h-3.5 ${isBookmarked ? 'fill-amber-600 text-amber-600' : ''}`} />
                <span>{isBookmarked ? 'Bookmarked' : 'Bookmark'}</span>
              </button>

              <div className="relative" ref={moreMenuRef}>
                <button
                  type="button"
                  onClick={() => setIsMoreMenuOpen(!isMoreMenuOpen)}
                  className="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer"
                >
                  <MoreHorizontal className="w-4 h-4" />
                </button>

                {isMoreMenuOpen && (
                  <div className="absolute right-0 mt-1 w-52 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-40 text-xs font-medium animate-in fade-in">
                    <button
                      onClick={() => {
                        setIsMoreMenuOpen(false);
                        addToast('Edit mode opened', 'info');
                      }}
                      className="w-full px-3.5 py-2 text-left text-slate-700 hover:bg-slate-50 flex items-center gap-2"
                    >
                      <Edit className="w-3.5 h-3.5 text-slate-400" />
                      <span>Edit this message</span>
                    </button>
                    <button
                      onClick={() => {
                        setIsMoreMenuOpen(false);
                        navigator.clipboard.writeText(window.location.href);
                        addToast('Link copied to clipboard', 'success');
                      }}
                      className="w-full px-3.5 py-2 text-left text-slate-700 hover:bg-slate-50 flex items-center gap-2"
                    >
                      <Share2 className="w-3.5 h-3.5 text-slate-400" />
                      <span>Copy link to clipboard</span>
                    </button>
                    <div className="border-t border-slate-100 my-1" />
                    <button
                      onClick={() => {
                        setIsMoreMenuOpen(false);
                        if (confirm('Are you sure you want to delete this message?')) {
                          navigate(`/projects/${targetProjectId}/discussions`);
                          addToast('Message deleted', 'info');
                        }
                      }}
                      className="w-full px-3.5 py-2 text-left text-rose-600 hover:bg-rose-50 flex items-center gap-2"
                    >
                      <Trash2 className="w-3.5 h-3.5" />
                      <span>Trash this message...</span>
                    </button>
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Message Header */}
          <div className="pt-8 pb-6">
            <h1 className="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
              {discussion?.title || 'Untitled'}
            </h1>

            <div className="mt-3 flex items-center gap-2 text-sm text-slate-600 flex-wrap">
              <span className="font-bold text-slate-900">{authorName}</span>
              {isClient && (
                <span className="px-1.5 py-0.5 rounded text-[11px] font-bold bg-[#fef08a] text-yellow-950 border border-yellow-300/60">
                  Client
                </span>
              )}
              <span>on Aug 26</span>
              <span className="text-slate-400">•</span>
              <button
                type="button"
                onClick={() => setIsNotifiedModalOpen(true)}
                className="text-slate-500 hover:text-slate-800 hover:underline cursor-pointer"
              >
                Notified 5 people
              </button>
              {versions.length > 0 && (
                <>
                  <span className="text-slate-400">•</span>
                  <button
                    type="button"
                    onClick={() => setIsHistoryModalOpen(true)}
                    className="text-blue-600 hover:underline cursor-pointer font-semibold"
                  >
                    (edited • {versions.length} {versions.length === 1 ? 'revision' : 'revisions'})
                  </button>
                </>
              )}
            </div>
          </div>

          {/* Attachment Box Section */}
          <div className="mt-4 flex items-start gap-4">
            {/* Orange Author Circle Avatar 'E' */}
            <div
              className={`w-12 h-12 rounded-full ${authorColor} text-white font-black text-xl flex items-center justify-center shrink-0 shadow-xs`}
            >
              {authorInitials[0] || 'E'}
            </div>

            {/* Document Card */}
            <div className="flex-1 space-y-3">
              <div className="bg-[#f8f9fa] hover:bg-[#f1f3f5] transition-colors border border-slate-200/80 rounded-2xl p-4 sm:p-5 flex items-center gap-4 shadow-xs">
                {/* File Icon: Blue Document with Dog-Ear and DOCX Label */}
                <div className="w-11 h-14 bg-[#e0f2fe] border border-blue-200 rounded-md relative flex flex-col justify-end p-1 shrink-0 shadow-xs">
                  {/* Folded dog-ear corner */}
                  <div className="absolute top-0 right-0 w-3 h-3 bg-blue-100 border-l border-b border-blue-300 rounded-bl-xs" />
                  <FileText className="w-5 h-5 text-blue-500 mx-auto mb-1 opacity-70" />
                  <span className="bg-[#0284c7] text-white text-[9px] font-black rounded-xs text-center py-0.5 uppercase tracking-wider leading-none">
                    DOCX
                  </span>
                </div>

                {/* File Info */}
                <div className="flex-1 min-w-0">
                  <h3
                    onClick={() =>
                      handleDownloadAttachment('RIDE MY CARS TERMS AND CONDITION1. Final.docx')
                    }
                    className="font-bold text-slate-900 text-sm sm:text-base truncate hover:underline cursor-pointer"
                  >
                    RIDE MY CARS TERMS AND CONDITION1. Final.docx
                  </h3>
                  <p className="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                    <span>21.7 KB</span>
                    <span>•</span>
                    <button
                      type="button"
                      onClick={() =>
                        handleDownloadAttachment('RIDE MY CARS TERMS AND CONDITION1. Final.docx')
                      }
                      className="text-blue-600 hover:text-blue-800 font-semibold hover:underline cursor-pointer"
                    >
                      Download
                    </button>
                  </p>
                </div>
              </div>

              {/* Emoji Reactions / Boosts */}
              <div className="flex items-center gap-2 pt-1 relative" ref={emojiPickerRef}>
                {discussion?.reactions && discussion.reactions.length > 0 ? (
                  discussion.reactions.map((r) => (
                    <button
                      key={r.emoji}
                      type="button"
                      onClick={() => handleReact(r.emoji)}
                      className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-transform active:scale-95 cursor-pointer"
                    >
                      <span>{r.emoji}</span>
                      {r.count > 1 && <span>{r.count}</span>}
                    </button>
                  ))
                ) : (
                  <button
                    type="button"
                    onClick={() => handleReact('🚀')}
                    className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-transform active:scale-95 cursor-pointer"
                  >
                    <span>🚀</span>
                  </button>
                )}

                {/* Add Boost Button */}
                <button
                  type="button"
                  onClick={() => setIsEmojiPickerOpen(!isEmojiPickerOpen)}
                  className="p-1.5 rounded-full border border-slate-200 hover:border-slate-300 bg-white text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors cursor-pointer"
                  title="Boost this message"
                >
                  <Smile className="w-3.5 h-3.5" />
                </button>

                {isEmojiPickerOpen && (
                  <div className="absolute left-16 top-0 bg-white border border-slate-200 rounded-full shadow-lg p-1.5 flex items-center gap-1 z-30 animate-in fade-in zoom-in-95">
                    {['🚀', '❤️', '👍', '🎉', '🔥', '👏', '🙌'].map((emoji) => (
                      <button
                        key={emoji}
                        type="button"
                        onClick={() => handleReact(emoji)}
                        className="w-7 h-7 rounded-full hover:bg-slate-100 flex items-center justify-center text-sm transition-transform hover:scale-125 cursor-pointer"
                      >
                        {emoji}
                      </button>
                    ))}
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Previous Comments Section */}
          <div className="mt-14 space-y-6">
            {comments.length > 2 && !showAllComments && (
              <button
                type="button"
                onClick={() => setShowAllComments(true)}
                className="w-full py-3 rounded-xl bg-[#faf5ea] hover:bg-[#f5ebd3] border border-[#faecd2] text-slate-700 font-semibold text-sm flex items-center justify-center gap-2 transition-colors cursor-pointer shadow-xs"
              >
                <ArrowUp className="w-4 h-4 text-amber-700" />
                <span>See previous comments ({hiddenCount > 0 ? hiddenCount : 'all'})</span>
              </button>
            )}

            {showAllComments && comments.length > 2 && (
              <button
                type="button"
                onClick={() => setShowAllComments(false)}
                className="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer"
              >
                <span>Collapse to recent comments</span>
              </button>
            )}

            {/* Comments Stream */}
            <div className="space-y-8 pt-4">
              {visibleComments.map((c, index) => {
                const cAuthorName = c.profile?.full_name || 'Edward';
                const cInitials = getInitials(cAuthorName);
                const cColor = getAvatarColor(cInitials);
                const cIsClient = c.is_client || cAuthorName.toLowerCase().includes('edward');

                return (
                  <div key={c.id || index} className="flex items-start gap-4">
                    {/* Avatar circle */}
                    <div
                      className={`w-10 h-10 rounded-full ${cColor} text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-xs`}
                    >
                      {cInitials}
                    </div>

                    {/* Comment Content Box */}
                    <div className="flex-1 space-y-1">
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2 text-sm flex-wrap">
                          <span className="font-extrabold text-slate-900">
                            {cAuthorName}
                            {c.role ? `, ${c.role}` : ''}
                          </span>
                          {cIsClient && (
                            <span className="px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#fef08a] text-yellow-950 border border-yellow-300/60">
                              Client
                            </span>
                          )}
                          <span className="text-slate-400 text-xs">•</span>
                          <span className="text-slate-400 text-xs">Aug 26</span>
                        </div>

                        <button
                          type="button"
                          className="text-slate-400 hover:text-slate-700 p-1 rounded-md transition-colors cursor-pointer"
                        >
                          <MoreHorizontal className="w-4 h-4" />
                        </button>
                      </div>

                      {/* Rendered Comment Content */}
                      <div
                        className="text-sm text-slate-800 leading-relaxed font-sans pt-1"
                        dangerouslySetInnerHTML={{ __html: renderFormattedContent(c.content) }}
                      />

                      {/* Render Attached Files if any */}
                      {c.attachments && c.attachments.length > 0 && (
                        <div className="mt-3 flex flex-wrap gap-2.5">
                          {c.attachments.map((att, attIdx) => (
                            <div
                              key={attIdx}
                              className="flex items-center gap-2.5 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs shadow-2xs hover:bg-slate-100 transition-colors"
                            >
                              <FileIcon className="w-4 h-4 text-blue-600 shrink-0" />
                              <div className="min-w-0 max-w-[200px]">
                                <p className="font-bold text-slate-800 truncate">{att.name}</p>
                                <p className="text-[10px] text-slate-500">{att.size}</p>
                              </div>
                              <button
                                type="button"
                                onClick={() => handleDownloadAttachment(att.name, att.url)}
                                className="p-1 rounded-md hover:bg-slate-200 text-slate-600 hover:text-blue-600 transition-colors"
                                title={`Download ${att.name}`}
                              >
                                <Download className="w-3.5 h-3.5" />
                              </button>
                            </div>
                          ))}
                        </div>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          {/* Add a Comment Editor / Form at Bottom */}
          <div className="mt-14 pt-8 border-t border-slate-200/90">
            <div className="flex items-start gap-4">
              {/* Logged-in user circle avatar with authentic initials & color */}
              <div
                className={`w-10 h-10 rounded-full ${currentUserColor} text-white font-extrabold text-sm flex items-center justify-center shrink-0 shadow-xs`}
              >
                {currentUserInitials}
              </div>

              {/* Basecamp Rich Editor & Reply Form */}
              <form onSubmit={handleAddComment} className="flex-1 space-y-3">
                <input
                  type="file"
                  ref={fileInputRef}
                  onChange={handleFileChange}
                  multiple
                  className="hidden"
                  accept="*/*"
                />

                <div
                  onDragOver={handleDragOver}
                  onDragLeave={handleDragLeave}
                  onDrop={handleDrop}
                  className={`border rounded-2xl bg-white shadow-2xs overflow-hidden transition-all ${
                    isDraggingOver
                      ? 'border-blue-500 bg-blue-50/20 ring-2 ring-blue-500/20'
                      : 'border-slate-300 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20'
                  }`}
                >
                  {/* WYSIWYG Content-Editable Area */}
                  <div className="relative min-h-[120px] p-4">
                    {isEditorEmpty && (
                      <div className="absolute top-4 left-4 text-sm text-slate-400 pointer-events-none select-none">
                        Write a comment...
                      </div>
                    )}
                    <div
                      ref={editorRef}
                      contentEditable
                      onInput={handleEditorInput}
                      className="w-full min-h-[90px] text-sm text-slate-900 focus:outline-none leading-relaxed font-sans"
                      style={{ outline: 'none' }}
                    />
                  </div>

                  {/* Attached Files Preview Inside Editor */}
                  {attachedFiles.length > 0 && (
                    <div className="px-4 pb-3 flex flex-wrap gap-2 border-t border-slate-100 pt-2.5 bg-slate-50/40">
                      {attachedFiles.map((file) => (
                        <div
                          key={file.id}
                          className="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs shadow-2xs group"
                        >
                          <FileIcon className="w-3.5 h-3.5 text-blue-600 shrink-0" />
                          <span className="font-semibold text-slate-800 truncate max-w-[150px]">
                            {file.name}
                          </span>
                          <span className="text-[10px] text-slate-400">{file.size}</span>
                          <button
                            type="button"
                            onClick={() => handleRemoveAttachedFile(file.id)}
                            className="text-slate-400 hover:text-rose-600 p-0.5 rounded-full hover:bg-slate-100 transition-colors"
                            title="Remove attachment"
                          >
                            <X className="w-3.5 h-3.5" />
                          </button>
                        </div>
                      ))}
                    </div>
                  )}

                  {/* Basecamp Rich Formatting Toolbar */}
                  <div className="px-4 py-2 bg-slate-50/80 border-t border-slate-200 flex items-center justify-between text-slate-600 text-xs flex-wrap gap-2">
                    <div className="flex items-center gap-1 sm:gap-2 flex-wrap">
                      {/* Bold (B) */}
                      <button
                        type="button"
                        onClick={() => executeCommand('bold')}
                        className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                        title="Bold (Ctrl+B)"
                      >
                        <Bold className="w-3.5 h-3.5 font-black" />
                      </button>

                      {/* Italic (I) */}
                      <button
                        type="button"
                        onClick={() => executeCommand('italic')}
                        className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                        title="Italic (Ctrl+I)"
                      >
                        <Italic className="w-3.5 h-3.5" />
                      </button>

                      {/* Strikethrough (S) */}
                      <button
                        type="button"
                        onClick={() => executeCommand('strikeThrough')}
                        className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                        title="Strikethrough"
                      >
                        <Strikethrough className="w-3.5 h-3.5" />
                      </button>

                      <span className="text-slate-300">|</span>

                      {/* Link */}
                      <button
                        type="button"
                        onClick={handleLinkCommand}
                        className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                        title="Add Link"
                      >
                        <Link2 className="w-3.5 h-3.5" />
                      </button>

                      {/* Bulleted List */}
                      <button
                        type="button"
                        onClick={() => executeCommand('insertUnorderedList')}
                        className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                        title="Bulleted List"
                      >
                        <List className="w-3.5 h-3.5" />
                      </button>

                      {/* Numbered List */}
                      <button
                        type="button"
                        onClick={() => executeCommand('insertOrderedList')}
                        className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                        title="Numbered List"
                      >
                        <ListOrdered className="w-3.5 h-3.5" />
                      </button>

                      {/* Blockquote */}
                      <button
                        type="button"
                        onClick={() => executeCommand('formatBlock', 'blockquote')}
                        className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                        title="Blockquote"
                      >
                        <Quote className="w-3.5 h-3.5" />
                      </button>

                      <span className="text-slate-300">|</span>

                      {/* Add files button */}
                      <button
                        type="button"
                        onClick={handleTriggerFileInput}
                        className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg hover:bg-slate-200 hover:text-slate-900 font-medium transition-colors cursor-pointer"
                        title="Attach documents, images or zip files"
                      >
                        <Paperclip className="w-3.5 h-3.5 text-slate-500" />
                        <span>Add files</span>
                        {attachedFiles.length > 0 && (
                          <span className="px-1.5 py-0.2 rounded-full bg-blue-600 text-white text-[10px] font-bold">
                            {attachedFiles.length}
                          </span>
                        )}
                      </button>
                    </div>

                    <div className="flex items-center gap-1 text-[11px] text-slate-400 select-none">
                      Drag & drop files supported
                    </div>
                  </div>
                </div>

                {/* Bottom Action Row */}
                <div className="flex items-center justify-between pt-1 flex-wrap gap-3">
                  <div className="flex items-center gap-2">
                    <button
                      type="submit"
                      disabled={(isEditorEmpty && attachedFiles.length === 0) || submittingComment}
                      className="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm shadow-md shadow-blue-500/20 active:scale-95 transition-all flex items-center gap-2 cursor-pointer"
                    >
                      <Send className="w-3.5 h-3.5" />
                      <span>{submittingComment ? 'Posting...' : 'Add this comment'}</span>
                    </button>
                  </div>

                  {/* Notification indicator */}
                  <label className="flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                    <input
                      type="checkbox"
                      checked={notifyPeopleChecked}
                      onChange={(e) => setNotifyPeopleChecked(e.target.checked)}
                      className="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span>When done, notify 5 people</span>
                  </label>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      {/* Notified People Modal */}
      {isNotifiedModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-sm w-full p-6 space-y-4">
            <h3 className="font-extrabold text-slate-900 text-base">Notified on this message</h3>
            <p className="text-xs text-slate-500 leading-relaxed">
              {notifiedPeople.length === 1
                ? '1 team member was notified when this message was posted:'
                : `These ${notifiedPeople.length} team members received an email and in-app ping when this message was posted:`}
            </p>
            <div className="space-y-2.5">
              {notifiedPeople.map((m) => (
                <div key={m.id || m.name} className="flex items-center justify-between text-xs py-1">
                  <div className="flex items-center gap-2.5">
                    <div
                      className={`w-7 h-7 rounded-full ${m.col} text-white font-bold flex items-center justify-center text-[10px]`}
                    >
                      {getInitials(m.name)}
                    </div>
                    <div>
                      <p className="font-bold text-slate-900">{m.name}</p>
                      <p className="text-[10px] text-slate-400">{m.role}</p>
                    </div>
                  </div>
                  {m.client && (
                    <span className="px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#fef08a] text-yellow-950">
                      Client
                    </span>
                  )}
                </div>
              ))}
            </div>
            <div className="pt-2 flex justify-end">
              <button
                type="button"
                onClick={() => setIsNotifiedModalOpen(false)}
                className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Revision History Modal (Basecamp 4 Parity) */}
      {isHistoryModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-xl w-full p-6 space-y-4 max-h-[85vh] flex flex-col overflow-hidden">
            <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 shrink-0">
              <div>
                <h3 className="font-extrabold text-slate-900 dark:text-slate-100 text-base">
                  Revision History
                </h3>
                <p className="text-xs text-slate-500">
                  Past versions of this message before edits
                </p>
              </div>
              <button
                type="button"
                onClick={() => setIsHistoryModalOpen(false)}
                className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="overflow-y-auto space-y-4 flex-1 pr-1">
              {/* Current Version */}
              <div className="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40 space-y-2">
                <div className="flex items-center justify-between text-xs">
                  <span className="font-extrabold text-emerald-900 dark:text-emerald-200">
                    Current Version (Latest)
                  </span>
                  <span className="text-slate-400">
                    {discussion?.updated_at ? new Date(discussion.updated_at).toLocaleString() : 'Now'}
                  </span>
                </div>
                <h4 className="font-bold text-sm text-slate-900 dark:text-slate-100">
                  {discussion?.title}
                </h4>
              </div>

              {/* Past Versions */}
              {versions.map((ver) => (
                <div
                  key={ver.id}
                  className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2"
                >
                  <div className="flex items-center justify-between text-xs">
                    <span className="font-bold text-slate-600 dark:text-slate-400">
                      Revision v{ver.version_number}
                    </span>
                    <span className="text-slate-400">
                      {new Date(ver.created_at).toLocaleString()}
                    </span>
                  </div>
                  <h4 className="font-bold text-sm text-slate-900 dark:text-slate-100">
                    {ver.title}
                  </h4>
                  <div className="text-xs text-slate-600 dark:text-slate-300 whitespace-pre-wrap pt-1 border-t border-slate-200/50 dark:border-slate-700/50">
                    {ver.content}
                  </div>
                </div>
              ))}
            </div>

            <div className="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end shrink-0">
              <button
                type="button"
                onClick={() => setIsHistoryModalOpen(false)}
                className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
              >
                Done
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
