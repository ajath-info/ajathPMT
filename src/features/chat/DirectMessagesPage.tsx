import React, { useState, useEffect } from 'react';
import { MessageSquare, Search, User, Send, Plus, Users, X, Check } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { DirectConversation, DirectMessage, OrganizationMember } from '../../types';
import {
  getDirectConversations,
  getOrCreateDirectConversation,
  createGroupDirectConversation,
  getDirectMessages,
  sendDirectMessage,
  subscribeToDirectMessages,
} from '../../services/chatService';
import { fetchOrganizationMembers } from '../../services/organizationService';
import { Avatar } from '../../components/common/Avatar';
import { ChatMessageBubble } from '../../components/chat/ChatMessageBubble';
import { ChatInput } from '../../components/chat/ChatInput';
import { Button } from '../../components/common/Button';

const DEFAULT_MEMBERS: OrganizationMember[] = [
  {
    id: 'mem-1',
    organization_id: 'demo-org-acme',
    user_id: 'demo-user-owner',
    role: 'OWNER',
    joined_at: new Date().toISOString(),
    profile: {
      id: 'demo-user-owner',
      email: 'alex.owner@worksphere.io',
      full_name: 'Alex Vance',
      avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
      job_title: 'Product Director & Founder',
      timezone: 'America/New_York (UTC-5)',
    },
  },
  {
    id: 'mem-2',
    organization_id: 'demo-org-acme',
    user_id: 'demo-user-admin',
    role: 'ADMIN',
    joined_at: new Date().toISOString(),
    profile: {
      id: 'demo-user-admin',
      email: 'sarah.admin@worksphere.io',
      full_name: 'Sarah Jenkins',
      avatar_url: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
      job_title: 'Senior Engineering Lead',
      timezone: 'America/Los_Angeles (UTC-8)',
    },
  },
  {
    id: 'mem-3',
    organization_id: 'demo-org-acme',
    user_id: 'demo-user-member',
    role: 'MEMBER',
    joined_at: new Date().toISOString(),
    profile: {
      id: 'demo-user-member',
      email: 'david.member@worksphere.io',
      full_name: 'David Chen',
      avatar_url: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
      job_title: 'UI/UX Designer',
      timezone: 'Europe/London (UTC+0)',
    },
  },
  {
    id: 'mem-4',
    organization_id: 'demo-org-acme',
    user_id: 'demo-user-client',
    role: 'CLIENT',
    joined_at: new Date().toISOString(),
    profile: {
      id: 'demo-user-client',
      email: 'claire.client@partner.com',
      full_name: 'Claire Watson',
      avatar_url: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&auto=format&fit=crop&q=80',
      job_title: 'Client Partner Representative',
      timezone: 'Asia/Tokyo (UTC+9)',
    },
  },
];

export function DirectMessagesPage() {
  const { user, profile } = useAuth();
  const { currentOrganization } = useOrganization();
  const currentUserId = user?.id || profile?.id || 'demo-user-owner';

  const [conversations, setConversations] = useState<DirectConversation[]>([]);
  const [activeConv, setActiveConv] = useState<DirectConversation | null>(null);
  const [messages, setMessages] = useState<DirectMessage[]>([]);
  const [members, setMembers] = useState<OrganizationMember[]>([]);
  const [loading, setLoading] = useState(true);
  const [memberSearch, setMemberSearch] = useState('');

  // New Ping / Group Ping Modal State
  const [isGroupModalOpen, setIsGroupModalOpen] = useState(false);
  const [selectedMemberIds, setSelectedMemberIds] = useState<string[]>([]);
  const [groupTitle, setGroupTitle] = useState('');
  const [isCreatingGroup, setIsCreatingGroup] = useState(false);

  const loadData = async () => {
    if (!currentOrganization || !currentUserId) return;
    setLoading(true);
    try {
      const [convList, memList] = await Promise.all([
        getDirectConversations(currentOrganization.id, currentUserId),
        fetchOrganizationMembers(currentOrganization.id),
      ]);
      setConversations(convList);

      const availableMembers = memList.length > 0 ? memList : DEFAULT_MEMBERS;
      setMembers(availableMembers);

      if (convList.length > 0) {
        setActiveConv(convList[0]);
      } else {
        const otherMember = availableMembers.find((m) => m.user_id !== currentUserId);
        if (otherMember) {
          const conv = await getOrCreateDirectConversation(
            currentOrganization.id,
            currentUserId,
            otherMember.user_id
          );
          setActiveConv(conv);
          setConversations([conv]);
        }
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [currentOrganization?.id, currentUserId]);

  useEffect(() => {
    async function loadDms() {
      if (!activeConv) return;
      const dms = await getDirectMessages(activeConv.id);
      setMessages(dms);
    }
    loadDms();
  }, [activeConv?.id]);

  useEffect(() => {
    if (!activeConv?.id) return;
    const unsubscribe = subscribeToDirectMessages(activeConv.id, (incomingDm) => {
      setMessages((prev) => {
        if (prev.some((m) => m.id === incomingDm.id)) return prev;
        return [...prev, incomingDm];
      });
      setActiveConv((prev) =>
        prev && prev.id === incomingDm.conversation_id ? { ...prev, last_message: incomingDm } : prev
      );
      setConversations((prev) =>
        prev.map((c) => (c.id === incomingDm.conversation_id ? { ...c, last_message: incomingDm } : c))
      );
    });

    return () => {
      unsubscribe();
    };
  }, [activeConv?.id]);

  const handleStartConv = async (otherUserId: string) => {
    if (!currentOrganization || !currentUserId) return;
    const conv = await getOrCreateDirectConversation(
      currentOrganization.id,
      currentUserId,
      otherUserId
    );
    setActiveConv(conv);
    setConversations((prev) => {
      if (prev.some((c) => c.id === conv.id)) return prev;
      return [conv, ...prev];
    });
  };

  const handleCreateGroupPing = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!currentOrganization || selectedMemberIds.length === 0) return;

    setIsCreatingGroup(true);
    try {
      if (selectedMemberIds.length === 1 && !groupTitle.trim()) {
        await handleStartConv(selectedMemberIds[0]);
      } else {
        const newGroup = await createGroupDirectConversation(
          currentOrganization.id,
          selectedMemberIds,
          groupTitle.trim() || undefined,
          currentUserId
        );
        setConversations((prev) => [newGroup, ...prev]);
        setActiveConv(newGroup);
      }
      setIsGroupModalOpen(false);
      setSelectedMemberIds([]);
      setGroupTitle('');
    } catch (err) {
      console.error(err);
    } finally {
      setIsCreatingGroup(false);
    }
  };

  const toggleSelectMember = (userId: string) => {
    setSelectedMemberIds((prev) =>
      prev.includes(userId) ? prev.filter((id) => id !== userId) : [...prev, userId]
    );
  };

  const handleSend = async (content: string) => {
    if (!activeConv || !currentUserId) return;
    const newDm = await sendDirectMessage(activeConv.id, currentUserId, content);
    setMessages((prev) => {
      if (prev.some((m) => m.id === newDm.id)) return prev;
      return [...prev, newDm];
    });
    setActiveConv((prev) => (prev ? { ...prev, last_message: newDm } : null));
    setConversations((prev) =>
      prev.map((c) => (c.id === activeConv.id ? { ...c, last_message: newDm } : c))
    );
  };

  const filteredMembers = members.filter((m) => {
    if (m.user_id === currentUserId) return false;
    const name = m.profile?.full_name || m.profile?.email || '';
    return name.toLowerCase().includes(memberSearch.toLowerCase());
  });

  return (
    <div className="space-y-6 animate-in fade-in duration-300 max-w-7xl mx-auto flex flex-col h-[calc(100vh-140px)]">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Pings (Direct & Group Messages)
          </h1>
          <p className="text-sm text-slate-500">Fast 1-on-1 and multi-person private team conversations</p>
        </div>
        <Button
          variant="primary"
          onClick={() => setIsGroupModalOpen(true)}
          className="flex items-center gap-1.5 shadow-md shadow-blue-500/20"
        >
          <Plus className="w-4 h-4" />
          <span>New Ping</span>
        </Button>
      </div>

      <div className="flex-1 grid grid-cols-1 md:grid-cols-3 gap-6 overflow-hidden">
        {/* Left Column: Member Search & Conversation List */}
        <div className="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-200/80 dark:border-slate-800 flex flex-col space-y-4 overflow-hidden">
          <div className="relative">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              type="text"
              value={memberSearch}
              onChange={(e) => setMemberSearch(e.target.value)}
              placeholder="Search conversations & people..."
              className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none"
            />
          </div>

          <div className="flex-1 overflow-y-auto space-y-1.5">
            {/* Conversation Threads */}
            <div className="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-2 pt-1">
              Active Pings ({conversations.length})
            </div>

            {conversations.map((c) => {
              const isActive = activeConv?.id === c.id;
              const isGroup = c.is_group;
              const title = isGroup
                ? c.title || 'Group Ping'
                : c.other_user?.full_name || 'Direct Ping';

              return (
                <div
                  key={c.id}
                  onClick={() => setActiveConv(c)}
                  className={`p-3 rounded-2xl border transition-all cursor-pointer flex items-center gap-3 ${
                    isActive
                      ? 'bg-blue-50 dark:bg-blue-950/60 border-blue-200 dark:border-blue-800 shadow-xs'
                      : 'border-slate-100 dark:border-slate-800/80 hover:bg-slate-50 dark:hover:bg-slate-800/50'
                  }`}
                >
                  {isGroup ? (
                    <div className="w-9 h-9 rounded-full bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-300 flex items-center justify-center shrink-0">
                      <Users className="w-4 h-4" />
                    </div>
                  ) : (
                    <Avatar
                      src={c.other_user?.avatar_url}
                      name={c.other_user?.full_name || 'Member'}
                      size="sm"
                    />
                  )}

                  <div className="flex-1 min-w-0">
                    <div className="flex items-center justify-between">
                      <p className="font-bold text-xs text-slate-900 dark:text-slate-100 truncate">
                        {title}
                      </p>
                      {isGroup && (
                        <span className="text-[9px] font-bold px-1.5 py-0.2 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded-full">
                          Group
                        </span>
                      )}
                    </div>
                    {c.last_message ? (
                      <p className="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                        {c.last_message.content}
                      </p>
                    ) : (
                      <p className="text-[10px] text-slate-400 italic">No messages yet</p>
                    )}
                  </div>
                </div>
              );
            })}

            {/* Quick 1-on-1 Member Contacts */}
            <div className="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-2 pt-4">
              Team Members
            </div>
            {filteredMembers.map((m) => (
              <div
                key={m.id}
                onClick={() => handleStartConv(m.user_id)}
                className="p-2.5 rounded-xl border border-transparent hover:border-slate-200 dark:hover:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer flex items-center gap-2.5"
              >
                <Avatar src={m.profile?.avatar_url} name={m.profile?.full_name || 'Member'} size="xs" />
                <div className="flex-1 min-w-0">
                  <p className="font-semibold text-xs text-slate-800 dark:text-slate-200 truncate">
                    {m.profile?.full_name || 'Team Member'}
                  </p>
                  <p className="text-[10px] text-slate-400 truncate">{m.role}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Right Column: Chat Window */}
        <div className="md:col-span-2 bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 flex flex-col overflow-hidden">
          {activeConv ? (
            <>
              <div className="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 shrink-0">
                <div className="flex items-center gap-3">
                  {activeConv.is_group ? (
                    <div className="w-10 h-10 rounded-2xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-300 flex items-center justify-center">
                      <Users className="w-5 h-5" />
                    </div>
                  ) : (
                    <Avatar
                      src={activeConv.other_user?.avatar_url}
                      name={activeConv.other_user?.full_name || 'User'}
                      size="md"
                    />
                  )}
                  <div>
                    <h3 className="font-extrabold text-sm text-slate-900 dark:text-slate-100">
                      {activeConv.is_group
                        ? activeConv.title || 'Group Ping'
                        : activeConv.other_user?.full_name || 'Team Member'}
                    </h3>
                    <p className="text-[11px] text-slate-500">
                      {activeConv.is_group
                        ? `${activeConv.participants?.length || 'Multiple'} participants in this ping`
                        : activeConv.other_user?.email}
                    </p>
                  </div>
                </div>

                {activeConv.is_group && activeConv.participants && (
                  <div className="flex -space-x-1.5 overflow-hidden">
                    {activeConv.participants.slice(0, 4).map((p) => (
                      <div
                        key={p.id}
                        className="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-slate-900 bg-slate-200 dark:bg-slate-700 text-[10px] font-bold text-center leading-6 text-slate-700 dark:text-slate-200"
                        title={p.profile?.full_name || p.user_id}
                      >
                        {(p.profile?.full_name || 'M')[0]}
                      </div>
                    ))}
                  </div>
                )}
              </div>

              <div className="flex-1 overflow-y-auto py-4 space-y-3">
                {messages.length === 0 ? (
                  <div className="h-full flex flex-col items-center justify-center text-slate-400 text-xs">
                    <MessageSquare className="w-8 h-8 text-slate-300 dark:text-slate-600 mb-2" />
                    <p>No messages in this ping yet.</p>
                    <p className="text-[11px] text-slate-400 mt-1">Send a message below to start the conversation.</p>
                  </div>
                ) : (
                  messages.map((m) => (
                    <ChatMessageBubble key={m.id} message={m as any} isSelf={m.sender_id === user?.id} />
                  ))
                )}
              </div>

              <div className="shrink-0 pt-2">
                <ChatInput
                  onSendMessage={handleSend}
                  placeholder={`Ping ${
                    activeConv.is_group
                      ? 'everyone in this group'
                      : activeConv.other_user?.full_name || 'member'
                  }...`}
                />
              </div>
            </>
          ) : (
            <div className="h-full flex flex-col items-center justify-center text-slate-400 text-xs font-semibold gap-2">
              <MessageSquare className="w-10 h-10 text-slate-300 dark:text-slate-700" />
              <span>Select or start a ping conversation to chat.</span>
            </div>
          )}
        </div>
      </div>

      {/* New Ping / Group Ping Modal */}
      {isGroupModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in duration-200">
          <div className="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div className="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="p-2.5 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                  <Users className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="text-base font-extrabold text-slate-900 dark:text-slate-100">
                    Start a New Ping
                  </h3>
                  <p className="text-xs text-slate-500">1-on-1 or multi-person group ping</p>
                </div>
              </div>
              <button
                onClick={() => setIsGroupModalOpen(false)}
                className="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleCreateGroupPing} className="p-6 space-y-4">
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-slate-700 dark:text-slate-300">
                  Group Ping Title (Optional)
                </label>
                <input
                  type="text"
                  value={groupTitle}
                  onChange={(e) => setGroupTitle(e.target.value)}
                  placeholder="e.g. Design Sync, Client Q&A, Ops Squad"
                  className="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                />
              </div>

              <div className="space-y-1.5">
                <div className="flex items-center justify-between">
                  <label className="text-xs font-bold text-slate-700 dark:text-slate-300">
                    Select Participants ({selectedMemberIds.length} selected)
                  </label>
                </div>

                <div className="max-h-56 overflow-y-auto space-y-1 border border-slate-100 dark:border-slate-800 rounded-2xl p-2">
                  {members
                    .filter((m) => m.user_id !== currentUserId)
                    .map((m) => {
                      const isSelected = selectedMemberIds.includes(m.user_id);
                      return (
                        <div
                          key={m.id}
                          onClick={() => toggleSelectMember(m.user_id)}
                          className={`p-2.5 rounded-xl flex items-center justify-between cursor-pointer transition-colors ${
                            isSelected
                              ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-900 dark:text-blue-100'
                              : 'hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-300'
                          }`}
                        >
                          <div className="flex items-center gap-2.5">
                            <Avatar
                              src={m.profile?.avatar_url}
                              name={m.profile?.full_name || 'Member'}
                              size="xs"
                            />
                            <div>
                              <p className="text-xs font-bold leading-tight">
                                {m.profile?.full_name || 'Team Member'}
                              </p>
                              <p className="text-[10px] text-slate-400">{m.profile?.email}</p>
                            </div>
                          </div>
                          <div
                            className={`w-4 h-4 rounded border flex items-center justify-center transition-colors ${
                              isSelected
                                ? 'bg-blue-600 border-blue-600 text-white'
                                : 'border-slate-300 dark:border-slate-600'
                            }`}
                          >
                            {isSelected && <Check className="w-3 h-3 stroke-[3]" />}
                          </div>
                        </div>
                      );
                    })}
                </div>
              </div>

              <div className="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => setIsGroupModalOpen(false)}
                >
                  Cancel
                </Button>
                <Button
                  type="submit"
                  variant="primary"
                  size="sm"
                  disabled={selectedMemberIds.length === 0 || isCreatingGroup}
                  isLoading={isCreatingGroup}
                >
                  Start Ping
                </Button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
