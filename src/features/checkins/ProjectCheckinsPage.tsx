import React, { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  HelpCircle,
  Plus,
  Send,
  Clock,
  User,
  CheckCircle2,
  Bookmark,
  MoreHorizontal,
  ChevronLeft,
  BellRing,
  Printer,
  Share2,
  Sparkles,
  ToggleLeft,
  ToggleRight,
  Trash2,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { useProject } from '../../context/ProjectContext';
import { CheckinQuestion, CheckinResponse, Project } from '../../types';
import {
  getCheckinQuestions,
  getCheckinResponses,
  submitCheckinResponse,
  runScheduledCheckins,
  toggleCheckinQuestion,
  deleteCheckinQuestion,
} from '../../services/checkinService';
import { getProjectById } from '../../services/projectService';
import { CreateCheckinModal } from '../../components/checkins/CreateCheckinModal';
import { Avatar } from '../../components/common/Avatar';
import { Badge } from '../../components/common/Badge';

export function ProjectCheckinsPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const { projects } = useProject();
  const { user, profile } = useAuth();
  const { addToast } = useToast();
  const navigate = useNavigate();

  const targetProjectId = projectId || projects[0]?.id || 'proj-rmc';

  const [project, setProject] = useState<Project | null>(null);
  const [questions, setQuestions] = useState<CheckinQuestion[]>([]);
  const [activeQuestionId, setActiveQuestionId] = useState<string | null>(null);
  const [responses, setResponses] = useState<CheckinResponse[]>([]);
  const [loading, setLoading] = useState(true);
  const [newResponse, setNewResponse] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [runningRunner, setRunningRunner] = useState(false);

  // Bookmarking
  const bookmarkKey = `basecamp_bookmark_checkins_${targetProjectId}`;
  const [isBookmarked, setIsBookmarked] = useState<boolean>(() => {
    try {
      return localStorage.getItem(bookmarkKey) === 'true';
    } catch {
      return false;
    }
  });
  const [isMoreMenuOpen, setIsMoreMenuOpen] = useState(false);
  const moreMenuRef = useRef<HTMLDivElement>(null);

  const [isModalOpen, setIsModalOpen] = useState(false);

  const loadData = async () => {
    if (!targetProjectId) {
      setQuestions([]);
      setLoading(false);
      return;
    }
    setLoading(true);
    try {
      const [pData, qList] = await Promise.all([
        getProjectById(targetProjectId).catch(() => null),
        getCheckinQuestions(targetProjectId).catch(() => []),
      ]);
      if (pData) setProject(pData);
      setQuestions(qList);
      if (qList.length > 0 && !activeQuestionId) {
        setActiveQuestionId(qList[0].id);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [targetProjectId]);

  useEffect(() => {
    async function loadResponses() {
      if (!activeQuestionId) return;
      const rList = await getCheckinResponses(activeQuestionId);
      setResponses(rList);
    }
    loadResponses();
  }, [activeQuestionId]);

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
      addToast(next ? 'Bookmarked Check-ins' : 'Bookmark removed', 'info');
      return next;
    });
  };

  const handleSubmitResponse = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeQuestionId || !user || !newResponse.trim()) return;
    setSubmitting(true);
    try {
      await submitCheckinResponse(activeQuestionId, user.id, newResponse.trim());
      setNewResponse('');
      const rList = await getCheckinResponses(activeQuestionId);
      setResponses(rList);
      addToast('Check-in answer posted!', 'success');
      loadData();
    } catch (err) {
      console.error(err);
      addToast('Failed to post answer', 'error');
    } finally {
      setSubmitting(false);
    }
  };

  const handleToggleActive = async (q: CheckinQuestion, e: React.MouseEvent) => {
    e.stopPropagation();
    const nextState = !q.is_active;
    try {
      await toggleCheckinQuestion(q.id, nextState);
      setQuestions((prev) =>
        prev.map((item) => (item.id === q.id ? { ...item, is_active: nextState } : item))
      );
      addToast(nextState ? 'Question activated' : 'Question paused', 'info');
    } catch {
      addToast('Failed to update question', 'error');
    }
  };

  const handleDeleteQuestion = async (qId: string, e: React.MouseEvent) => {
    e.stopPropagation();
    if (confirm('Delete this check-in question and all its answers?')) {
      await deleteCheckinQuestion(qId);
      addToast('Check-in question deleted', 'info');
      if (activeQuestionId === qId) {
        setActiveQuestionId(null);
      }
      loadData();
    }
  };

  const handleRunRunner = async () => {
    if (!targetProjectId) return;
    setRunningRunner(true);
    try {
      const res = await runScheduledCheckins(targetProjectId);
      if (res.notifiedCount > 0) {
        addToast(`Dispatched ${res.notifiedCount} check-in prompt notifications to team members!`, 'success');
      } else {
        addToast('All check-in prompts have already been dispatched for today.', 'info');
      }
    } catch (e) {
      addToast('Failed to trigger scheduled check-ins', 'error');
    } finally {
      setRunningRunner(false);
    }
  };

  const activeQuestion = questions.find((q) => q.id === activeQuestionId);

  if (loading) {
    return (
      <div className="w-full max-w-7xl mx-auto pt-4 pb-16">
        <div className="w-full bg-white dark:bg-slate-900 rounded-3xl min-h-[500px] p-12 text-center text-slate-400 font-semibold text-sm animate-pulse border border-slate-200/80 dark:border-slate-800">
          Loading automatic check-in prompts...
        </div>
      </div>
    );
  }

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 pb-16">
      {/* Modern Check-ins Card */}
      <div className="w-full bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
        
        {/* 1. Top Breadcrumb & Action Utility Bar */}
        <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800/80">
          {/* Breadcrumb Hierarchy matching Basecamp */}
          <div className="flex items-center gap-2 text-sm">
            <button
              onClick={() => navigate(`/projects/${targetProjectId}`)}
              className="font-bold text-slate-800 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:underline transition-colors cursor-pointer flex items-center gap-1.5"
            >
              <ChevronLeft className="w-4 h-4 text-slate-400" />
              {project?.name || 'Project'}
            </button>
            <span className="text-slate-300 dark:text-slate-600 font-normal">/</span>
            <span className="font-extrabold text-slate-900 dark:text-white">Automatic Check-ins</span>
          </div>

          {/* Right utility: Bookmark & More options */}
          <div className="flex items-center gap-2">
            <button
              onClick={handleToggleBookmark}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border transition-all cursor-pointer ${
                isBookmarked
                  ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300'
                  : 'bg-transparent border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
              }`}
              title={isBookmarked ? 'Remove bookmark' : 'Bookmark this tool'}
            >
              <Bookmark className={`w-3.5 h-3.5 ${isBookmarked ? 'fill-amber-500 text-amber-500' : ''}`} />
              <span>{isBookmarked ? 'Bookmarked' : 'Bookmark'}</span>
            </button>

            {/* More Options dropdown */}
            <div className="relative" ref={moreMenuRef}>
              <button
                onClick={() => setIsMoreMenuOpen(!isMoreMenuOpen)}
                className="p-1.5 rounded-full text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                title="More options"
              >
                <MoreHorizontal className="w-4 h-4" />
              </button>

              {isMoreMenuOpen && (
                <div className="absolute right-0 mt-1 w-52 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 py-1.5 z-30 text-xs animate-in fade-in zoom-in-95 duration-150">
                  <button
                    onClick={() => {
                      window.print();
                      setIsMoreMenuOpen(false);
                    }}
                    className="w-full px-3.5 py-2 text-left text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 flex items-center gap-2 cursor-pointer font-medium"
                  >
                    <Printer className="w-3.5 h-3.5 text-slate-400" />
                    Print check-in log
                  </button>
                  <button
                    onClick={() => {
                      navigator.clipboard.writeText(window.location.href);
                      addToast('Check-ins link copied to clipboard', 'success');
                      setIsMoreMenuOpen(false);
                    }}
                    className="w-full px-3.5 py-2 text-left text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 flex items-center gap-2 cursor-pointer font-medium"
                  >
                    <Share2 className="w-3.5 h-3.5 text-slate-400" />
                    Copy link to check-ins
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* 2. Tool Title & Actions Banner */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
          <div>
            <h1 className="text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
              <span>Automatic Check-ins</span>
            </h1>
            <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
              Ask your team recurring questions automatically and collect async updates
            </p>
          </div>

          <div className="flex items-center gap-2.5 flex-wrap">
            {/* Run Prompts Button */}
            <button
              onClick={handleRunRunner}
              disabled={runningRunner}
              className="px-3.5 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <BellRing className="w-3.5 h-3.5 text-indigo-500" />
              <span>{runningRunner ? 'Sending...' : 'Trigger Prompts'}</span>
            </button>

            {/* Vibrant Add Question Button */}
            <button
              onClick={() => setIsModalOpen(true)}
              className="bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-bold text-xs sm:text-sm px-4 py-2 rounded-xl shadow-md shadow-rose-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center gap-1.5 cursor-pointer"
            >
              <Plus className="w-4 h-4" />
              <span>New check-in question</span>
            </button>
          </div>
        </div>

        {/* 3. Basecamp Check-ins Layout */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
          {/* Left Column: Questions List */}
          <div className="space-y-3">
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">
              Check-in Questions ({questions.length})
            </h3>
            {questions.length === 0 ? (
              <div className="p-6 text-center text-xs text-slate-400 border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl">
                No check-in questions yet. Click "New check-in question" above to create one.
              </div>
            ) : (
              questions.map((q) => (
                <div
                  key={q.id}
                  onClick={() => setActiveQuestionId(q.id)}
                  className={`p-4 rounded-2xl border transition-all cursor-pointer space-y-2 group ${
                    activeQuestionId === q.id
                      ? 'bg-blue-50/70 dark:bg-blue-950/40 border-[#0c66e4]/40 shadow-xs'
                      : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'
                  }`}
                >
                  <div className="flex items-center justify-between">
                    <span className="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                      {q.schedule}
                    </span>
                    <div className="flex items-center gap-1.5">
                      <span className="text-[10px] text-slate-400 font-semibold">
                        {q.responses_count || 0} answers
                      </span>
                      <button
                        onClick={(e) => handleToggleActive(q, e)}
                        className="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 ml-1"
                        title={q.is_active ? 'Pause question' : 'Activate question'}
                      >
                        {q.is_active ? (
                          <ToggleRight className="w-4 h-4 text-emerald-500" />
                        ) : (
                          <ToggleLeft className="w-4 h-4 text-slate-400" />
                        )}
                      </button>
                      <button
                        onClick={(e) => handleDeleteQuestion(q.id, e)}
                        className="text-slate-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"
                        title="Delete question"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                  <p className="font-bold text-xs text-slate-900 dark:text-slate-100 line-clamp-2">
                    {q.question}
                  </p>
                </div>
              ))
            )}
          </div>

          {/* Right Column: Answers Stream & Response Input */}
          <div className="md:col-span-2 space-y-6">
            {activeQuestion ? (
              <div className="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-6">
                <div className="space-y-2 pb-4 border-b border-slate-100 dark:border-slate-800">
                  <div className="flex items-center gap-2">
                    <span className="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-[#0c66e4] dark:text-blue-300">
                      {activeQuestion.schedule} CHECK-IN
                    </span>
                    {!activeQuestion.is_active && (
                      <span className="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">
                        Paused
                      </span>
                    )}
                  </div>
                  <h2 className="text-xl font-extrabold text-slate-900 dark:text-slate-100 leading-snug">
                    {activeQuestion.question}
                  </h2>
                </div>

                {/* Submit Answer Form */}
                <form onSubmit={handleSubmitResponse} className="space-y-3 bg-slate-50/70 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                  <div className="flex items-center gap-2">
                    <Avatar
                      src={profile?.avatar_url || user?.user_metadata?.avatar_url}
                      name={profile?.full_name || user?.user_metadata?.full_name || 'You'}
                      size="xs"
                    />
                    <label className="text-xs font-bold text-slate-800 dark:text-slate-200">
                      Your response today
                    </label>
                  </div>
                  <textarea
                    rows={3}
                    value={newResponse}
                    onChange={(e) => setNewResponse(e.target.value)}
                    placeholder="Write your check-in response for the team..."
                    className="w-full p-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0c66e4]/20 resize-none"
                  />
                  <div className="flex justify-end">
                    <button
                      type="submit"
                      disabled={submitting || !newResponse.trim()}
                      className="bg-[#0c66e4] hover:bg-[#0052cc] disabled:opacity-50 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer active:scale-98"
                    >
                      <Send className="w-3.5 h-3.5" />
                      <span>{submitting ? 'Posting...' : 'Post my answer'}</span>
                    </button>
                  </div>
                </form>

                {/* Responses Feed */}
                <div className="space-y-4 pt-2">
                  <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Team Responses ({responses.length})
                  </h4>

                  {responses.length === 0 ? (
                    <div className="text-center py-10 text-slate-400 text-xs italic">
                      No responses yet for this check-in. Be the first to answer above!
                    </div>
                  ) : (
                    responses.map((resp) => (
                      <div
                        key={resp.id}
                        className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 space-y-2.5 shadow-2xs"
                      >
                        <div className="flex items-center justify-between">
                          <div className="flex items-center gap-2.5">
                            <Avatar src={resp.user?.avatar_url} name={resp.user?.full_name || 'User'} size="sm" />
                            <div>
                              <div className="font-bold text-xs text-slate-900 dark:text-slate-100">
                                {resp.user?.full_name || 'Team Member'}
                              </div>
                              <div className="text-[10px] text-slate-400">
                                {new Date(resp.created_at).toLocaleDateString(undefined, {
                                  month: 'short',
                                  day: 'numeric',
                                })}{' '}
                                at{' '}
                                {new Date(resp.created_at).toLocaleTimeString([], {
                                  hour: '2-digit',
                                  minute: '2-digit',
                                })}
                              </div>
                            </div>
                          </div>
                        </div>
                        <p className="text-xs text-slate-700 dark:text-slate-300 leading-relaxed pl-10 whitespace-pre-line">
                          {resp.response}
                        </p>
                      </div>
                    ))
                  )}
                </div>
              </div>
            ) : (
              <div className="py-20 text-center text-slate-400 text-xs font-semibold bg-slate-50/50 dark:bg-slate-800/30 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                Select or create a check-in question on the left.
              </div>
            )}
          </div>
        </div>

        {/* Modal */}
        {targetProjectId && (
          <CreateCheckinModal
            isOpen={isModalOpen}
            onClose={() => setIsModalOpen(false)}
            projectId={targetProjectId}
            userId={user?.id}
            onSuccess={loadData}
          />
        )}
      </div>
    </div>
  );
}
