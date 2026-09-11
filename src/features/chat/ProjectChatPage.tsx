import React, { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  MessageSquare,
  Users,
  Sparkles,
  Bookmark,
  MoreHorizontal,
  Send,
  Paperclip,
  Smile,
  Volume2,
  VolumeX,
  Lock,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { useProject } from '../../context/ProjectContext';
import { ChatMessage, Project, ProjectMember } from '../../types';
import {
  getOrCreateProjectChatRoom,
  getProjectChatMessages,
  sendChatMessage,
  subscribeToRoomMessages,
} from '../../services/chatService';
import { getProjectById, getProjectMembers } from '../../services/projectService';
import { ChatMessageBubble } from '../../components/chat/ChatMessageBubble';

export function ProjectChatPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const { projects } = useProject();
  const { user, profile, userRole } = useAuth();
  const { addToast } = useToast();
  const navigate = useNavigate();

  const targetProjectId = projectId || projects[0]?.id || 'proj-rmc';

  const [project, setProject] = useState<Project | null>(null);
  const [members, setMembers] = useState<ProjectMember[]>([]);
  const [roomId, setRoomId] = useState<string | null>(null);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [replyingTo, setReplyingTo] = useState<ChatMessage | null>(null);
  const [loading, setLoading] = useState(true);
  const [inputText, setInputText] = useState('');
  const [soundEnabled, setSoundEnabled] = useState(true);

  // Bookmarking
  const bookmarkKey = `basecamp_bookmark_chat_${targetProjectId}`;
  const [isBookmarked, setIsBookmarked] = useState<boolean>(() => {
    try {
      return localStorage.getItem(bookmarkKey) === 'true';
    } catch {
      return false;
    }
  });
  const [isMoreMenuOpen, setIsMoreMenuOpen] = useState(false);
  const moreMenuRef = useRef<HTMLDivElement>(null);
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  const loadData = async () => {
    if (!targetProjectId) return;
    setLoading(true);
    try {
      const [pData, mData, rId] = await Promise.all([
        getProjectById(targetProjectId).catch(() => null),
        getProjectMembers(targetProjectId).catch(() => []),
        getOrCreateProjectChatRoom(targetProjectId).catch(() => null),
      ]);
      if (pData) setProject(pData);
      setMembers(mData || []);
      setRoomId(rId);
      if (rId) {
        const msgs = await getProjectChatMessages(rId).catch(() => []);
        setMessages(msgs);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
      setTimeout(scrollToBottom, 100);
    }
  };

  useEffect(() => {
    loadData();
  }, [targetProjectId]);

  // Realtime Subscription
  useEffect(() => {
    if (!roomId) return;
    const unsubscribe = subscribeToRoomMessages(roomId, (incomingMsg) => {
      setMessages((prev) => {
        if (prev.some((m) => m.id === incomingMsg.id)) return prev;
        return [...prev, incomingMsg];
      });
      setTimeout(scrollToBottom, 60);
    });
    return () => {
      unsubscribe();
    };
  }, [roomId]);

  // Click outside listener for more menu
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (moreMenuRef.current && !moreMenuRef.current.contains(e.target as Node)) {
        setIsMoreMenuOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleToggleBookmark = () => {
    setIsBookmarked((prev) => {
      const next = !prev;
      try {
        localStorage.setItem(bookmarkKey, String(next));
      } catch {}
      addToast(next ? 'Bookmarked Campfire' : 'Bookmark removed', 'info');
      return next;
    });
  };

  const handleSendMessage = async (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    if (!roomId || !user || !inputText.trim()) return;
    const content = inputText.trim();
    setInputText('');

    try {
      const newMsg = await sendChatMessage(roomId, user.id, content, replyingTo?.id);
      setMessages((prev) => {
        if (prev.some((m) => m.id === newMsg.id)) return prev;
        return [...prev, newMsg];
      });
      setReplyingTo(null);
      setTimeout(scrollToBottom, 50);
    } catch (err) {
      addToast('Failed to send message', 'error');
    }
  };

  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file || !roomId || !user) return;
    const reader = new FileReader();
    reader.onload = async (event) => {
      const url = event.target?.result as string;
      try {
        const newMsg = await sendChatMessage(roomId, user.id, `Shared a file: ${file.name}`, undefined, {
          url,
          name: file.name,
          type: file.type,
        });
        setMessages((prev) => [...prev, newMsg]);
        setTimeout(scrollToBottom, 50);
      } catch (err) {
        addToast('Failed to upload file', 'error');
      }
    };
    reader.readAsDataURL(file);
  };

  // Basecamp Role Gate: Campfire chat is strictly internal team only
  if (userRole === 'CLIENT') {
    return (
      <div className="w-full max-w-4xl mx-auto py-12 px-4 animate-in fade-in duration-300">
        <div className="bg-white dark:bg-slate-900 rounded-3xl p-8 sm:p-12 border border-slate-200/80 dark:border-slate-800 shadow-sm text-center space-y-6">
          <div className="w-16 h-16 rounded-2xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 mx-auto flex items-center justify-center shadow-inner">
            <Lock className="w-8 h-8" />
          </div>
          <div className="space-y-2">
            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
              <span>Internal Team Chat</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
              Campfire is private to the team
            </h1>
            <p className="text-sm text-slate-500 dark:text-slate-400 max-w-lg mx-auto leading-relaxed">
              In Ajath PMT, Campfire chat rooms are casual spaces reserved for internal company teammates. Client stakeholders do not have access to internal Campfire chat.
            </p>
          </div>

          <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-800 max-w-md mx-auto text-left space-y-2 text-xs text-slate-600 dark:text-slate-300">
            <p className="font-bold text-slate-900 dark:text-slate-100">Want to communicate with the team?</p>
            <p className="text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
              Please post on the <span className="font-semibold text-blue-600 dark:text-blue-400">Message Board</span> or leave comments on shared to-dos and documents.
            </p>
          </div>

          <div className="pt-2 flex items-center justify-center gap-3">
            <button
              onClick={() => navigate(`/projects/${targetProjectId}`)}
              className="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 active:scale-95 transition-all cursor-pointer"
            >
              Return to Project Overview
            </button>
          </div>
        </div>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="w-full max-w-7xl mx-auto pt-4 pb-16">
        <div className="w-full bg-white dark:bg-slate-900 rounded-3xl min-h-[500px] p-12 text-center text-slate-400 font-semibold text-sm animate-pulse border border-slate-200/80 dark:border-slate-800">
          Connecting to Campfire...
        </div>
      </div>
    );
  }

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 pb-16">
      {/* Modern Campfire Card */}
      <div className="w-full bg-white dark:bg-slate-900 rounded-3xl min-h-[calc(100vh-12rem)] p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
        
        {/* Top Section: Breadcrumb + Header */}
        <div>
          {/* 1. Top Breadcrumb & Action Utility Bar */}
          <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800/80">
            {/* Breadcrumb Hierarchy */}
            <div className="flex items-center gap-2 text-sm">
              <button
                onClick={() => navigate(`/projects/${targetProjectId}`)}
                className="font-bold text-slate-800 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:underline transition-colors cursor-pointer"
              >
                {project?.name || 'Project'}
              </button>
              <span className="text-slate-300 dark:text-slate-600">/</span>
              <span className="font-extrabold text-slate-900 dark:text-slate-100">Campfire</span>
            </div>

            {/* Right Utility Bar */}
            <div className="flex items-center gap-2 relative" ref={moreMenuRef}>
              <button
                onClick={() => setSoundEnabled(!soundEnabled)}
                className="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer"
                title={soundEnabled ? 'Mute sounds' : 'Enable sounds'}
              >
                {soundEnabled ? <Volume2 className="w-3.5 h-3.5" /> : <VolumeX className="w-3.5 h-3.5" />}
              </button>

              <button
                onClick={handleToggleBookmark}
                className={`p-1.5 rounded-lg border transition-colors cursor-pointer flex items-center gap-1.5 text-xs font-semibold ${
                  isBookmarked
                    ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 text-amber-600 dark:text-amber-400'
                    : 'border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800'
                }`}
                title={isBookmarked ? 'Bookmarked' : 'Bookmark this page'}
              >
                <Bookmark className={`w-3.5 h-3.5 ${isBookmarked ? 'fill-current' : ''}`} />
                <span className="hidden sm:inline">{isBookmarked ? 'Bookmarked' : 'Bookmark'}</span>
              </button>

              <button
                onClick={() => setIsMoreMenuOpen(!isMoreMenuOpen)}
                className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                title="More options"
              >
                <MoreHorizontal className="w-4 h-4" />
              </button>

              {isMoreMenuOpen && (
                <div className="absolute right-0 top-9 w-48 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 py-1.5 z-50 animate-in zoom-in-95 duration-100 text-xs">
                  <button
                    onClick={() => {
                      setIsMoreMenuOpen(false);
                      navigate(`/projects/${targetProjectId}`);
                    }}
                    className="w-full px-4 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium cursor-pointer"
                  >
                    Back to project overview
                  </button>
                </div>
              )}
            </div>
          </div>

          {/* 2. Main Title & Description matching Basecamp */}
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-3 pb-4">
            <div>
              <h1 className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                Campfire
              </h1>
              <p className="text-xs text-slate-400 dark:text-slate-500 mt-1">
                A casual space to chat with the team, ask quick questions, and share updates.
              </p>
            </div>

            {/* Active Members Pile */}
            <div className="flex items-center gap-1.5">
              <span className="text-[11px] font-semibold text-slate-400 mr-1">In campfire:</span>
              <div className="flex -space-x-1.5 overflow-hidden">
                {members.slice(0, 5).map((m) => {
                  const name = m.profile?.full_name || 'Member';
                  const initials = name
                    .split(' ')
                    .map((n) => n[0])
                    .join('')
                    .toUpperCase()
                    .slice(0, 2);
                  return (
                    <div
                      key={m.id}
                      title={name}
                      className="w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-[9px] flex items-center justify-center ring-1 ring-white dark:ring-slate-900 select-none"
                    >
                      {initials}
                    </div>
                  );
                })}
              </div>
            </div>
          </div>
        </div>

        {/* 3. Messages Stream */}
        <div className="flex-1 my-4 overflow-y-auto space-y-4 max-h-[500px] custom-scrollbar p-2">
          {messages.length === 0 ? (
            <div className="text-center py-20 space-y-2">
              <div className="w-12 h-12 rounded-2xl bg-orange-50 dark:bg-orange-950/40 text-orange-500 flex items-center justify-center mx-auto">
                <MessageSquare className="w-6 h-6" />
              </div>
              <p className="font-bold text-sm text-slate-800 dark:text-slate-200">The Campfire is quiet</p>
              <p className="text-xs text-slate-400 max-w-xs mx-auto">
                Break the ice! Say hello or drop a quick work question to the group.
              </p>
            </div>
          ) : (
            messages.map((m) => (
              <ChatMessageBubble
                key={m.id}
                message={m}
                isSelf={m.sender_id === user?.id}
                onReply={(msg) => setReplyingTo(msg)}
                onRefresh={loadData}
              />
            ))
          )}
          <div ref={messagesEndRef} />
        </div>

        {/* 4. Bottom Input Dock Bar matching Basecamp */}
        <div className="pt-3 border-t border-slate-100 dark:border-slate-800">
          <input
            type="file"
            ref={fileInputRef}
            onChange={handleFileUpload}
            className="hidden"
          />

          {replyingTo && (
            <div className="mb-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
              <span className="text-slate-600 dark:text-slate-300 truncate">
                Replying to <strong>{replyingTo.sender?.full_name || 'Member'}</strong>: {replyingTo.content}
              </span>
              <button
                onClick={() => setReplyingTo(null)}
                className="text-slate-400 hover:text-slate-600 font-bold"
              >
                ✕
              </button>
            </div>
          )}

          <form onSubmit={handleSendMessage} className="flex items-center gap-2">
            <button
              type="button"
              onClick={() => fileInputRef.current?.click()}
              className="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer shrink-0"
              title="Attach a file"
            >
              <Paperclip className="w-4 h-4" />
            </button>

            <input
              type="text"
              value={inputText}
              onChange={(e) => setInputText(e.target.value)}
              placeholder="Chat with the group or leave a note..."
              className="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-slate-400"
            />

            <button
              type="submit"
              disabled={!inputText.trim()}
              className="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:opacity-40 disabled:cursor-not-allowed text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md shadow-purple-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer flex items-center gap-1.5 shrink-0"
            >
              <Send className="w-3.5 h-3.5" />
              <span className="hidden sm:inline">Send</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}
