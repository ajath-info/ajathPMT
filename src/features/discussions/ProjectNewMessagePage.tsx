import React, { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import {
  Bold,
  Italic,
  Strikethrough,
  Link2,
  List,
  ListOrdered,
  Quote,
  Code,
  Paperclip,
  X,
  File as FileIcon,
  ChevronDown,
  Eye,
  Lock,
  Send,
  Check,
  Users,
  UserPlus,
} from 'lucide-react';
import { createDiscussion } from '../../services/discussionService';
import { getProjectMembers } from '../../services/projectService';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { useProject } from '../../context/ProjectContext';

interface AttachedFileItem {
  id: string;
  name: string;
  size: string;
  type: string;
  url?: string;
  file?: File;
}

interface NotifyPerson {
  id: string;
  name: string;
  role: string;
  client: boolean;
  col: string;
  isCurrentUser?: boolean;
}

const CATEGORIES = [
  'General',
  'Announcements',
  'FYI',
  'Heartbeat',
  'Pitch',
  'Question',
  'Design',
  'Technical',
];

function formatBytes(bytes: number): string {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

function getInitials(name: string): string {
  if (!name) return 'U';
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

export function ProjectNewMessagePage() {
  const { projectId } = useParams<{ projectId: string }>();
  const navigate = useNavigate();
  const { profile, userRole } = useAuth();
  const { addToast } = useToast();
  const { projects } = useProject();

  const targetProjectId = projectId || 'proj-rmc';
  const project = projects.find((p) => p.id === targetProjectId) || null;

  // Form states
  const [title, setTitle] = useState('');
  const [category, setCategory] = useState('General');
  const [isCategoryMenuOpen, setIsCategoryMenuOpen] = useState(false);
  const [isClientVisible, setIsClientVisible] = useState(true);
  const [isAnnouncement, setIsAnnouncement] = useState(false);
  const [publishOption, setPublishOption] = useState<'now' | 'scheduled'>('now');
  const [scheduledDate, setScheduledDate] = useState(
    new Date(Date.now() + 24 * 3600 * 1000).toISOString().split('T')[0]
  );
  const [scheduledTime, setScheduledTime] = useState('09:00');

  // Notifications audience checklist - dynamically scoped to this project's real members
  const [notifyPeople, setNotifyPeople] = useState<NotifyPerson[]>([]);
  const [loadingPeople, setLoadingPeople] = useState(true);
  const [selectedRecipientIds, setSelectedRecipientIds] = useState<string[]>([]);

  useEffect(() => {
    let isMounted = true;
    async function loadMembers() {
      if (!targetProjectId) return;
      setLoadingPeople(true);
      try {
        const membersData = await getProjectMembers(targetProjectId);
        if (!isMounted) return;

        if (membersData && membersData.length > 0) {
          const colors = ['bg-[#ea580c]', 'bg-[#9333ea]', 'bg-[#0d9488]', 'bg-[#16a34a]', 'bg-blue-600', 'bg-indigo-600', 'bg-amber-600'];
          const mapped: NotifyPerson[] = membersData.map((m, idx) => {
            const isClient = m.role === 'PROJECT_CLIENT';
            const fullName = m.profile?.full_name || 'Team Member';
            const roleName = isClient
              ? 'Client'
              : m.profile?.job_title || (m.role === 'PROJECT_OWNER' ? 'Project Lead' : 'Team Member');
            return {
              id: m.user_id || m.id,
              name: fullName,
              role: roleName,
              client: isClient,
              col: colors[idx % colors.length],
              isCurrentUser: m.user_id === profile?.id,
            };
          });

          setNotifyPeople(mapped);
          setSelectedRecipientIds(mapped.map((p) => p.id));
        } else {
          // If no members have been assigned to this project yet:
          // Show only current user (creator)
          if (profile) {
            const currentUserPerson: NotifyPerson = {
              id: profile.id,
              name: profile.full_name || 'You',
              role: userRole === 'OWNER' ? 'Account Owner' : userRole === 'ADMIN' ? 'Administrator' : 'Project Creator',
              client: userRole === 'CLIENT',
              col: 'bg-indigo-600',
              isCurrentUser: true,
            };
            setNotifyPeople([currentUserPerson]);
            setSelectedRecipientIds([currentUserPerson.id]);
          } else {
            setNotifyPeople([]);
            setSelectedRecipientIds([]);
          }
        }
      } catch (err) {
        console.error('Failed to load project members for notification', err);
        if (isMounted && profile) {
          setNotifyPeople([{
            id: profile.id,
            name: profile.full_name || 'You',
            role: 'Project Creator',
            client: false,
            col: 'bg-indigo-600',
            isCurrentUser: true,
          }]);
          setSelectedRecipientIds([profile.id]);
        }
      } finally {
        if (isMounted) setLoadingPeople(false);
      }
    }

    loadMembers();
    return () => {
      isMounted = false;
    };
  }, [targetProjectId, profile?.id, userRole]);

  // WYSIWYG Editor State
  const editorRef = useRef<HTMLDivElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [isEditorEmpty, setIsEditorEmpty] = useState(true);
  const [attachedFiles, setAttachedFiles] = useState<AttachedFileItem[]>([]);
  const [isDraggingOver, setIsDraggingOver] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  // Editor command execution
  const executeCommand = (cmd: string, val: string | undefined = undefined) => {
    if (editorRef.current) {
      editorRef.current.focus();
    }
    document.execCommand(cmd, false, val);
    handleEditorInput();
  };

  const handleLinkCommand = () => {
    const url = prompt('Enter link URL (e.g. https://example.com):');
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

  // Recipients toggles
  const handleToggleRecipient = (id: string) => {
    setSelectedRecipientIds((prev) =>
      prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
    );
  };

  const handleSelectAllRecipients = () => {
    setSelectedRecipientIds(notifyPeople.map((p) => p.id));
  };

  const handleSelectNoRecipients = () => {
    setSelectedRecipientIds([]);
  };

  // Submit new message
  const handlePostMessage = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!title.trim()) {
      addToast('Please type a title for your message', 'error');
      return;
    }

    const htmlContent = editorRef.current?.innerHTML.trim() || '';
    const textContent = editorRef.current?.innerText.trim() || '';

    if (!textContent && attachedFiles.length === 0) {
      addToast('Please write message content or attach a file', 'error');
      return;
    }

    setSubmitting(true);
    try {
      const currentUserName = profile?.full_name || 'Shivy Narain';
      const currentUserEmail = profile?.email || 'shivy@ajath.com';
      const userId = profile?.id || 'demo-user-owner';

      const attachmentsPayload = attachedFiles.map((f) => ({
        name: f.name,
        size: f.size,
        type: f.type,
        url: f.url,
      }));

      const isScheduled = publishOption === 'scheduled';
      const scheduledPublishAt = isScheduled
        ? new Date(`${scheduledDate}T${scheduledTime}`).toISOString()
        : undefined;

      const newDisc = await createDiscussion({
        project_id: targetProjectId,
        author_id: userId,
        title: title.trim(),
        content: htmlContent || textContent,
        category,
        is_announcement: isAnnouncement,
        is_pinned: false,
        is_client_visible: userRole === 'CLIENT' ? true : isClientVisible,
        notified_count: selectedRecipientIds.length,
        attachments: attachmentsPayload,
        status: isScheduled ? 'scheduled' : 'published',
        scheduled_publish_at: scheduledPublishAt,
        author: {
          id: userId,
          email: currentUserEmail,
          full_name: currentUserName,
          avatar_url: profile?.avatar_url || '',
        },
      });

      addToast(
        isScheduled
          ? `Message scheduled for ${new Date(scheduledPublishAt!).toLocaleString()}`
          : 'Message posted to the board',
        'success'
      );
      navigate(`/projects/${targetProjectId}/discussions`);
    } catch (err) {
      console.error(err);
      addToast('Failed to post message', 'error');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 py-2 pb-16">
      <input
        type="file"
        ref={fileInputRef}
        onChange={handleFileChange}
        multiple
        className="hidden"
        accept="*/*"
      />

      {/* Modern Editor Card */}
      <div className="w-full max-w-4xl mx-auto bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-12 shadow-sm border border-slate-200/80 dark:border-slate-800 space-y-6">
          {/* Top Breadcrumb Navigation */}
          <div className="flex items-center justify-between pb-6 border-b border-slate-100/90 text-sm">
            <div className="flex items-center gap-2 font-sans flex-wrap">
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
              <span className="text-slate-400 font-bold">‹</span>
              <span className="text-slate-400 font-medium">New message</span>
            </div>

            <button
              type="button"
              onClick={() => navigate(`/projects/${targetProjectId}/discussions`)}
              className="text-slate-400 hover:text-slate-700 p-1 rounded-lg transition-colors cursor-pointer"
              title="Cancel"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          <form onSubmit={handlePostMessage} className="pt-8 space-y-6">
            {/* Category Selector Pill */}
            <div className="relative inline-block">
              <button
                type="button"
                onClick={() => setIsCategoryMenuOpen(!isCategoryMenuOpen)}
                className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-slate-200 hover:border-slate-300 bg-slate-50 text-slate-700 font-semibold text-xs transition-colors cursor-pointer"
              >
                <span>Category:</span>
                <span className="text-blue-600 font-bold">{category}</span>
                <ChevronDown className="w-3.5 h-3.5 text-slate-400" />
              </button>

              {isCategoryMenuOpen && (
                <div className="absolute left-0 mt-1 w-48 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 z-40 text-xs font-medium animate-in fade-in zoom-in-95">
                  {CATEGORIES.map((cat) => (
                    <button
                      key={cat}
                      type="button"
                      onClick={() => {
                        setCategory(cat);
                        setIsCategoryMenuOpen(false);
                      }}
                      className="w-full px-3.5 py-2 text-left hover:bg-slate-50 flex items-center justify-between text-slate-700"
                    >
                      <span>{cat}</span>
                      {category === cat && <Check className="w-3.5 h-3.5 text-blue-600" />}
                    </button>
                  ))}
                </div>
              )}
            </div>

            {/* Title: Big, Bold, Clean Basecamp Title Input */}
            <div>
              <input
                type="text"
                autoFocus
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                placeholder="Type a title..."
                className="w-full text-3xl sm:text-5xl font-extrabold text-slate-900 placeholder:text-slate-300 border-none outline-none tracking-tight focus:ring-0 px-0"
              />
            </div>

            {/* Rich Content-Editable Message Canvas */}
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
              {/* Text Area Canvas */}
              <div className="relative min-h-[220px] p-4 sm:p-6">
                {isEditorEmpty && (
                  <div className="absolute top-4 sm:top-6 left-4 sm:left-6 text-base text-slate-400 pointer-events-none select-none">
                    Write your message...
                  </div>
                )}
                <div
                  ref={editorRef}
                  contentEditable
                  onInput={handleEditorInput}
                  className="w-full min-h-[190px] text-base text-slate-900 focus:outline-none leading-relaxed font-sans"
                  style={{ outline: 'none' }}
                />
              </div>

              {/* Attached Files Preview */}
              {attachedFiles.length > 0 && (
                <div className="px-4 sm:px-6 pb-4 flex flex-wrap gap-2.5 border-t border-slate-100 pt-3 bg-slate-50/50">
                  {attachedFiles.map((file) => (
                    <div
                      key={file.id}
                      className="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs shadow-2xs"
                    >
                      <FileIcon className="w-4 h-4 text-blue-600 shrink-0" />
                      <span className="font-semibold text-slate-800 truncate max-w-[180px]">
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
              <div className="px-4 py-2.5 bg-slate-50/80 border-t border-slate-200 flex items-center justify-between text-slate-600 text-xs flex-wrap gap-2">
                <div className="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                  <button
                    type="button"
                    onClick={() => executeCommand('bold')}
                    className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                    title="Bold"
                  >
                    <Bold className="w-4 h-4 font-black" />
                  </button>

                  <button
                    type="button"
                    onClick={() => executeCommand('italic')}
                    className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                    title="Italic"
                  >
                    <Italic className="w-4 h-4" />
                  </button>

                  <button
                    type="button"
                    onClick={() => executeCommand('strikeThrough')}
                    className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                    title="Strikethrough"
                  >
                    <Strikethrough className="w-4 h-4" />
                  </button>

                  <span className="text-slate-300">|</span>

                  <button
                    type="button"
                    onClick={handleLinkCommand}
                    className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                    title="Link"
                  >
                    <Link2 className="w-4 h-4" />
                  </button>

                  <button
                    type="button"
                    onClick={() => executeCommand('insertUnorderedList')}
                    className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                    title="Bullet List"
                  >
                    <List className="w-4 h-4" />
                  </button>

                  <button
                    type="button"
                    onClick={() => executeCommand('insertOrderedList')}
                    className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                    title="Numbered List"
                  >
                    <ListOrdered className="w-4 h-4" />
                  </button>

                  <button
                    type="button"
                    onClick={() => executeCommand('formatBlock', 'blockquote')}
                    className="p-1.5 rounded hover:bg-slate-200 hover:text-slate-900 transition-colors cursor-pointer"
                    title="Quote"
                  >
                    <Quote className="w-4 h-4" />
                  </button>

                  <span className="text-slate-300">|</span>

                  <button
                    type="button"
                    onClick={handleTriggerFileInput}
                    className="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg hover:bg-slate-200 hover:text-slate-900 font-semibold transition-colors cursor-pointer"
                    title="Attach files"
                  >
                    <Paperclip className="w-4 h-4 text-slate-500" />
                    <span>Add files</span>
                    {attachedFiles.length > 0 && (
                      <span className="px-1.5 py-0.2 rounded-full bg-blue-600 text-white text-[10px] font-bold">
                        {attachedFiles.length}
                      </span>
                    )}
                  </button>
                </div>

                <div className="text-[11px] text-slate-400 select-none">
                  Drag & drop files supported
                </div>
              </div>
            </div>

            {/* Client Visibility Option (Internal staff only) */}
            {userRole !== 'CLIENT' && (
              <div className="p-4 sm:p-5 rounded-2xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 space-y-2">
                <div className="flex items-center justify-between">
                  <span className="text-xs font-black uppercase tracking-wider text-amber-950 dark:text-amber-200">
                    Client Visibility
                  </span>
                  <span className="px-2 py-0.5 rounded text-[11px] font-bold bg-[#fef08a] text-yellow-950 border border-yellow-300">
                    {isClientVisible ? '👁 The client can see this' : '🔒 The client cannot see this (Internal only)'}
                  </span>
                </div>
                <div className="flex items-center gap-6 pt-1">
                  <label className="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-800 dark:text-slate-200">
                    <input
                      type="radio"
                      name="clientVisibility"
                      checked={isClientVisible}
                      onChange={() => setIsClientVisible(true)}
                      className="text-amber-600 focus:ring-amber-500"
                    />
                    <span>The client can see this message</span>
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-800 dark:text-slate-200">
                    <input
                      type="radio"
                      name="clientVisibility"
                      checked={!isClientVisible}
                      onChange={() => setIsClientVisible(false)}
                      className="text-amber-600 focus:ring-amber-500"
                    />
                    <span>Internal only (The client cannot see this)</span>
                  </label>
                </div>
              </div>
            )}

            {/* Notification Audience Selection */}
            <div className="pt-2 space-y-3">
              <div className="flex items-center justify-between">
                <h4 className="text-xs font-extrabold uppercase tracking-wider text-slate-700">
                  When done, who should be notified?
                </h4>
                <div className="flex items-center gap-3 text-xs">
                  <button
                    type="button"
                    onClick={handleSelectAllRecipients}
                    className="text-blue-600 hover:underline font-semibold cursor-pointer"
                  >
                    Select all
                  </button>
                  <span className="text-slate-300">•</span>
                  <button
                    type="button"
                    onClick={handleSelectNoRecipients}
                    className="text-slate-500 hover:underline cursor-pointer"
                  >
                    Select none
                  </button>
                </div>
              </div>

              {loadingPeople ? (
                <div className="py-6 text-center text-xs text-slate-400">Loading project members...</div>
              ) : notifyPeople.length === 0 ? (
                <div className="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-dashed border-slate-200 dark:border-slate-800 text-center space-y-2">
                  <p className="text-xs font-semibold text-slate-600 dark:text-slate-400">
                    No people have been added to this project yet.
                  </p>
                  {userRole !== 'CLIENT' && (
                    <Link
                      to={`/projects/${targetProjectId}/people`}
                      className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors"
                    >
                      <UserPlus className="w-3.5 h-3.5" />
                      <span>+ Add People to Project</span>
                    </Link>
                  )}
                </div>
              ) : (
                <>
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                    {notifyPeople.map((person) => {
                      const isChecked = selectedRecipientIds.includes(person.id);
                      return (
                        <label
                          key={person.id}
                          onClick={() => handleToggleRecipient(person.id)}
                          className={`flex items-center gap-3 p-2.5 rounded-xl border cursor-pointer select-none transition-all ${
                            isChecked
                              ? 'bg-blue-50/60 border-blue-300 text-slate-900 shadow-2xs'
                              : 'bg-white border-slate-200 hover:bg-slate-50 text-slate-600'
                          }`}
                        >
                          <input
                            type="checkbox"
                            checked={isChecked}
                            onChange={() => {}}
                            className="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                          />
                          <div
                            className={`w-7 h-7 rounded-full ${person.col} text-white font-bold text-[10px] flex items-center justify-center shrink-0`}
                          >
                            {getInitials(person.name)}
                          </div>
                          <div className="min-w-0 flex-1">
                            <p className="font-bold text-xs truncate">
                              {person.name}
                              {person.isCurrentUser && (
                                <span className="ml-1 text-[10px] font-normal text-slate-400">(You)</span>
                              )}
                            </p>
                            <p className="text-[10px] text-slate-400">{person.role}</p>
                          </div>
                          {person.client && (
                            <span className="px-1.5 py-0.2 rounded text-[9px] font-bold bg-[#fef08a] text-yellow-950">
                              Client
                            </span>
                          )}
                        </label>
                      );
                    })}
                  </div>

                  {notifyPeople.length === 1 && notifyPeople[0].isCurrentUser && (
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 mt-2">
                      <div className="flex items-center gap-2.5 text-xs text-amber-800 dark:text-amber-300 font-medium">
                        <Users className="w-4 h-4 text-amber-600 shrink-0" />
                        <span>Currently you have not added any other person to this project.</span>
                      </div>
                      {userRole !== 'CLIENT' && (
                        <Link
                          to={`/projects/${targetProjectId}/people`}
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition-colors shrink-0"
                        >
                          <UserPlus className="w-3.5 h-3.5" />
                          <span>+ Add People to Project</span>
                        </Link>
                      )}
                    </div>
                  )}
                </>
              )}
            </div>

            {/* When should this be posted? (Basecamp 4 Scheduling) */}
            <div className="pt-2 space-y-3 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800">
              <h4 className="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                When should this be posted?
              </h4>
              <div className="flex items-center gap-6 text-xs font-semibold">
                <label className="flex items-center gap-2 cursor-pointer text-slate-800 dark:text-slate-200">
                  <input
                    type="radio"
                    name="publishOption"
                    checked={publishOption === 'now'}
                    onChange={() => setPublishOption('now')}
                    className="text-blue-600 focus:ring-0"
                  />
                  <span>Post now</span>
                </label>
                <label className="flex items-center gap-2 cursor-pointer text-slate-800 dark:text-slate-200">
                  <input
                    type="radio"
                    name="publishOption"
                    checked={publishOption === 'scheduled'}
                    onChange={() => setPublishOption('scheduled')}
                    className="text-blue-600 focus:ring-0"
                  />
                  <span>Schedule to post later...</span>
                </label>
              </div>

              {publishOption === 'scheduled' && (
                <div className="pt-2 flex items-center gap-3 flex-wrap animate-in fade-in text-xs">
                  <div className="flex items-center gap-1.5">
                    <span className="text-slate-500 font-medium">Date:</span>
                    <input
                      type="date"
                      value={scheduledDate}
                      onChange={(e) => setScheduledDate(e.target.value)}
                      className="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-semibold"
                    />
                  </div>
                  <div className="flex items-center gap-1.5">
                    <span className="text-slate-500 font-medium">Time:</span>
                    <input
                      type="time"
                      value={scheduledTime}
                      onChange={(e) => setScheduledTime(e.target.value)}
                      className="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-semibold"
                    />
                  </div>
                </div>
              )}
            </div>

            {/* Bottom Action Buttons */}
            <div className="pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center gap-4 flex-wrap">
              <button
                type="submit"
                disabled={submitting || !title.trim()}
                className="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm shadow-md shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2 cursor-pointer"
              >
                <Send className="w-4 h-4" />
                <span>{submitting ? 'Posting...' : 'Post this message'}</span>
              </button>

              <button
                type="button"
                onClick={() => {
                  addToast('Draft saved', 'info');
                  navigate(`/projects/${targetProjectId}/discussions`);
                }}
                className="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs transition-colors cursor-pointer"
              >
                Save as a draft
              </button>

              <button
                type="button"
                onClick={() => navigate(`/projects/${targetProjectId}/discussions`)}
                className="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:underline cursor-pointer"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
  );
}
