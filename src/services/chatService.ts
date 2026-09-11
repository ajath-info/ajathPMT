import { supabase, isSupabaseConfigured } from '../lib/supabase';
import {
  ChatMessage,
  DirectConversation,
  DirectConversationParticipant,
  DirectMessage,
  MessageReaction,
  Profile,
  OrgRole,
} from '../types';
import { createNotification } from './notificationService';
import { realtimeBus } from '../lib/realtimeEventBus';
import { canAccessCampfire } from '../lib/permissions';

let MOCK_CHAT_MESSAGES: ChatMessage[] = [
  {
    id: 'msg-1',
    room_id: 'room-proj-hq',
    sender_id: 'demo-user-owner',
    content: 'Welcome to the Ajath Infotech Pvt Ltd HQ Campfire! Feel free to post quick questions or updates anytime.',
    created_at: new Date(Date.now() - 3 * 3600 * 1000).toISOString(),
    sender: {
      id: 'demo-user-owner',
      email: 'shivy@ajath.com',
      full_name: 'Shivy Narain',
      avatar_url: '',
    },
    reactions: [{ id: 'react-1', message_id: 'msg-1', user_id: 'user-gk', emoji: '🔥', created_at: new Date().toISOString() }],
  },
  {
    id: 'msg-2',
    room_id: 'room-proj-hq',
    sender_id: 'user-gk',
    content: 'All systems operational. The new Ajath PMT navigation is live!',
    created_at: new Date(Date.now() - 1 * 3600 * 1000).toISOString(),
    sender: {
      id: 'user-gk',
      email: 'gaurav@ajath.com',
      full_name: 'Gaurav Kumar',
      avatar_url: '',
    },
    reactions: [],
  },
  {
    id: 'msg-3',
    room_id: 'room-proj-rmc',
    sender_id: 'user-e',
    content: 'Hey Shivy, testing the vehicle inspection upload today. Looking very crisp!',
    created_at: new Date(Date.now() - 2 * 3600 * 1000).toISOString(),
    sender: {
      id: 'user-e',
      email: 'edward@ndchomes.com',
      full_name: 'Edward',
      avatar_url: '',
    },
    reactions: [],
  },
];

let MOCK_CONVERSATION_PARTICIPANTS: DirectConversationParticipant[] = [
  {
    id: 'part-1',
    conversation_id: 'conv-group-1',
    user_id: 'demo-user-owner',
    joined_at: new Date(Date.now() - 2 * 24 * 3600 * 1000).toISOString(),
  },
  {
    id: 'part-2',
    conversation_id: 'conv-group-1',
    user_id: 'demo-user-admin',
    joined_at: new Date(Date.now() - 2 * 24 * 3600 * 1000).toISOString(),
  },
  {
    id: 'part-3',
    conversation_id: 'conv-group-1',
    user_id: 'demo-user-member',
    joined_at: new Date(Date.now() - 2 * 24 * 3600 * 1000).toISOString(),
  },
];

let MOCK_DIRECT_CONVERSATIONS: DirectConversation[] = [
  {
    id: 'conv-1',
    organization_id: 'demo-org-acme',
    user1_id: 'demo-user-owner',
    user2_id: 'demo-user-admin',
    is_group: false,
    created_at: new Date(Date.now() - 2 * 24 * 3600 * 1000).toISOString(),
    other_user: {
      id: 'demo-user-admin',
      email: 'sarah.admin@worksphere.io',
      full_name: 'Sarah Jenkins',
      avatar_url: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
    },
    last_message: {
      id: 'dm-1',
      conversation_id: 'conv-1',
      sender_id: 'demo-user-admin',
      content: 'I have finalized the security RLS policies update.',
      is_read: true,
      created_at: new Date(Date.now() - 4 * 3600 * 1000).toISOString(),
    },
    unread_count: 0,
  },
  {
    id: 'conv-group-1',
    organization_id: 'demo-org-acme',
    title: 'Core Architecture Sync (Shivy, Sarah, David)',
    is_group: true,
    created_at: new Date(Date.now() - 1 * 24 * 3600 * 1000).toISOString(),
    last_message: {
      id: 'dm-grp-1',
      conversation_id: 'conv-group-1',
      sender_id: 'demo-user-member',
      content: 'Database indexes and full-text search triggers are verified.',
      is_read: true,
      created_at: new Date(Date.now() - 2 * 3600 * 1000).toISOString(),
    },
    unread_count: 0,
  },
];

let MOCK_DIRECT_MESSAGES: DirectMessage[] = [
  {
    id: 'dm-1',
    conversation_id: 'conv-1',
    sender_id: 'demo-user-admin',
    content: 'I have finalized the security RLS policies update.',
    is_read: true,
    created_at: new Date(Date.now() - 4 * 3600 * 1000).toISOString(),
    sender: {
      id: 'demo-user-admin',
      email: 'sarah.admin@worksphere.io',
      full_name: 'Sarah Jenkins',
      avatar_url: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
    },
  },
  {
    id: 'dm-grp-1',
    conversation_id: 'conv-group-1',
    sender_id: 'demo-user-member',
    content: 'Database indexes and full-text search triggers are verified.',
    is_read: true,
    created_at: new Date(Date.now() - 2 * 3600 * 1000).toISOString(),
    sender: {
      id: 'demo-user-member',
      email: 'david.member@worksphere.io',
      full_name: 'David Chen',
      avatar_url: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
    },
  },
];

export async function getOrCreateProjectChatRoom(projectId: string, userRole?: OrgRole): Promise<string> {
  if (userRole && !canAccessCampfire(userRole)) {
    throw new Error('Access denied: Campfire is restricted to internal team members.');
  }

  if (isSupabaseConfigured) {
    try {
      const { data: existing } = await supabase
        .from('chat_rooms')
        .select('id')
        .eq('project_id', projectId)
        .maybeSingle();

      if (existing) return existing.id;

      const { data: created, error } = await supabase
        .from('chat_rooms')
        .insert([{ project_id: projectId, name: `Project ${projectId} Room` }])
        .select('id')
        .single();

      if (!error && created) return created.id;
    } catch (e) {
      console.warn('Failed chat room creation via Supabase:', e);
    }
  }

  return `room-${projectId}`;
}

export async function getProjectChatMessages(roomId: string, userRole?: OrgRole): Promise<ChatMessage[]> {
  if (userRole && !canAccessCampfire(userRole)) {
    return [];
  }

  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('chat_messages')
        .select('*, sender:profiles(*), reactions:message_reactions(*)')
        .eq('room_id', roomId)
        .order('created_at', { ascending: true });

      if (!error && data) return data as ChatMessage[];
    } catch (e) {
      console.warn('Failed to fetch chat messages via Supabase:', e);
    }
  }

  return MOCK_CHAT_MESSAGES.filter((m) => m.room_id === roomId);
}

export async function sendChatMessage(
  roomId: string,
  senderId: string,
  content: string,
  replyToId?: string,
  attachment?: { url?: string; name?: string; type?: string },
  userRole?: OrgRole
): Promise<ChatMessage> {
  if (userRole && !canAccessCampfire(userRole)) {
    throw new Error('Access denied: Campfire is restricted to internal team members.');
  }

  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('chat_messages')
        .insert([
          {
            room_id: roomId,
            sender_id: senderId,
            content: content.trim(),
            reply_to_id: replyToId || null,
            attachment_url: attachment?.url || null,
            attachment_name: attachment?.name || null,
            attachment_type: attachment?.type || null,
          },
        ])
        .select('*, sender:profiles(*), reactions:message_reactions(*)')
        .single();

      if (!error && data) {
        const msg = data as ChatMessage;
        realtimeBus.emit('CHAT_MESSAGE', msg, { entityId: msg.id, senderId });
        return msg;
      }
    } catch (e) {
      console.warn('Failed to send chat message via Supabase:', e);
    }
  }

  const senderProfile = DEMO_PROFILES_LOOKUP[senderId] || {
    id: senderId,
    email: 'user@worksphere.io',
    full_name: 'Team Member',
  };

  const newMsg: ChatMessage = {
    id: `msg-${Date.now()}`,
    room_id: roomId,
    sender_id: senderId,
    content: content.trim(),
    reply_to_id: replyToId,
    attachment_url: attachment?.url,
    attachment_name: attachment?.name,
    attachment_type: attachment?.type,
    created_at: new Date().toISOString(),
    sender: senderProfile,
    reactions: [],
  };
  MOCK_CHAT_MESSAGES.push(newMsg);
  realtimeBus.emit('CHAT_MESSAGE', newMsg, { entityId: newMsg.id, senderId });
  return newMsg;
}

export const sendProjectChatMessage = sendChatMessage;

export async function addMessageReaction(
  messageId: string,
  userId: string,
  emoji: string
): Promise<MessageReaction> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('message_reactions')
        .insert([{ message_id: messageId, user_id: userId, emoji }])
        .select('*')
        .single();
      if (!error && data) return data as MessageReaction;
    } catch (e) {
      console.warn('Failed to add message reaction via Supabase:', e);
    }
  }

  const newReaction: MessageReaction = {
    id: `react-${Date.now()}-${Math.random()}`,
    message_id: messageId,
    user_id: userId,
    emoji,
    created_at: new Date().toISOString(),
  };

  const targetMsg = MOCK_CHAT_MESSAGES.find((m) => m.id === messageId);
  if (targetMsg) {
    if (!targetMsg.reactions) targetMsg.reactions = [];
    targetMsg.reactions.push(newReaction);
  }

  return newReaction;
}

export const DEMO_PROFILES_LOOKUP: Record<string, any> = {
  'demo-user-owner': {
    id: 'demo-user-owner',
    email: 'alex.owner@worksphere.io',
    full_name: 'Alex Vance',
    avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
  },
  'demo-user-admin': {
    id: 'demo-user-admin',
    email: 'sarah.admin@worksphere.io',
    full_name: 'Sarah Jenkins',
    avatar_url: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
  },
  'demo-user-member': {
    id: 'demo-user-member',
    email: 'david.member@worksphere.io',
    full_name: 'David Chen',
    avatar_url: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
  },
  'demo-user-client': {
    id: 'demo-user-client',
    email: 'claire.client@partner.com',
    full_name: 'Claire Watson',
    avatar_url: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&auto=format&fit=crop&q=80',
  },
};

/**
 * Direct & Group Conversations (Basecamp 4 Pings)
 */
export async function getDirectConversations(
  orgId: string,
  currentUserId: string
): Promise<DirectConversation[]> {
  if (isSupabaseConfigured) {
    try {
      // 1-on-1 conversations where user is user1 or user2
      const { data: directData } = await supabase
        .from('direct_conversations')
        .select('*, user1:profiles!user1_id(*), user2:profiles!user2_id(*)')
        .eq('organization_id', orgId)
        .or(`user1_id.eq.${currentUserId},user2_id.eq.${currentUserId}`);

      // Group conversations where user is participant
      const { data: groupParticipants } = await supabase
        .from('direct_conversation_participants')
        .select('conversation_id')
        .eq('user_id', currentUserId);

      let groupConvs: any[] = [];
      if (groupParticipants && groupParticipants.length > 0) {
        const groupIds = groupParticipants.map((gp) => gp.conversation_id);
        const { data: groupData } = await supabase
          .from('direct_conversations')
          .select('*, participants:direct_conversation_participants(*, profile:profiles(*))')
          .eq('organization_id', orgId)
          .in('id', groupIds);
        if (groupData) groupConvs = groupData;
      }

      const all = [...(directData || []), ...groupConvs];
      return all.map((c: any) => ({
        ...c,
        other_user: c.is_group ? null : c.user1_id === currentUserId ? c.user2 : c.user1,
      })) as DirectConversation[];
    } catch (e) {
      console.warn('Failed to fetch direct conversations via Supabase:', e);
    }
  }

  // Mock Fallback
  return MOCK_DIRECT_CONVERSATIONS.filter((c) => {
    if (c.organization_id !== orgId) return false;
    if (c.is_group) {
      const isParticipant = MOCK_CONVERSATION_PARTICIPANTS.some(
        (p) => p.conversation_id === c.id && p.user_id === currentUserId
      );
      return isParticipant;
    }
    return c.user1_id === currentUserId || c.user2_id === currentUserId;
  }).map((c) => {
    if (c.is_group) {
      const parts = MOCK_CONVERSATION_PARTICIPANTS.filter((p) => p.conversation_id === c.id).map((p) => ({
        ...p,
        profile: DEMO_PROFILES_LOOKUP[p.user_id] || { id: p.user_id, full_name: 'Member' },
      }));
      return {
        ...c,
        participants: parts,
      };
    }
    return {
      ...c,
      other_user:
        c.user1_id === currentUserId
          ? c.user2 || DEMO_PROFILES_LOOKUP[c.user2_id || '']
          : c.user1 || DEMO_PROFILES_LOOKUP[c.user1_id || ''],
    };
  });
}

export async function getOrCreateDirectConversation(
  orgId: string,
  user1Id: string,
  user2Id: string
): Promise<DirectConversation> {
  const [sorted1, sorted2] = [user1Id, user2Id].sort();

  if (isSupabaseConfigured) {
    try {
      const { data: existing } = await supabase
        .from('direct_conversations')
        .select('*, user1:profiles!user1_id(*), user2:profiles!user2_id(*)')
        .eq('organization_id', orgId)
        .eq('user1_id', sorted1)
        .eq('user2_id', sorted2)
        .maybeSingle();

      if (existing) {
        return {
          ...existing,
          other_user: existing.user1_id === user1Id ? existing.user2 : existing.user1,
        } as DirectConversation;
      }

      const { data: created, error } = await supabase
        .from('direct_conversations')
        .insert([
          {
            organization_id: orgId,
            user1_id: sorted1,
            user2_id: sorted2,
            is_group: false,
          },
        ])
        .select('*, user1:profiles!user1_id(*), user2:profiles!user2_id(*)')
        .single();

      if (!error && created) {
        return {
          ...created,
          other_user: created.user1_id === user1Id ? created.user2 : created.user1,
        } as DirectConversation;
      }
    } catch (e) {
      console.warn('Failed to get/create direct conversation via Supabase:', e);
    }
  }

  let existing = MOCK_DIRECT_CONVERSATIONS.find(
    (c) => !c.is_group && c.organization_id === orgId && c.user1_id === sorted1 && c.user2_id === sorted2
  );

  if (!existing) {
    const otherId = sorted1 === user1Id ? sorted2 : sorted1;
    const newConv: DirectConversation = {
      id: `conv-${Date.now()}`,
      organization_id: orgId,
      user1_id: sorted1,
      user2_id: sorted2,
      is_group: false,
      created_at: new Date().toISOString(),
      user1: DEMO_PROFILES_LOOKUP[sorted1] || { id: sorted1, full_name: 'Team Member', email: 'member@worksphere.io' },
      user2: DEMO_PROFILES_LOOKUP[sorted2] || { id: sorted2, full_name: 'Team Member', email: 'member@worksphere.io' },
      other_user: DEMO_PROFILES_LOOKUP[otherId] || { id: otherId, full_name: 'Team Member', email: 'member@worksphere.io' },
      unread_count: 0,
    };
    MOCK_DIRECT_CONVERSATIONS.unshift(newConv);
    existing = newConv;
  }

  const targetConv = existing as DirectConversation;
  const other = targetConv.user1_id === user1Id ? targetConv.user2 : targetConv.user1;
  return {
    ...targetConv,
    other_user: other || DEMO_PROFILES_LOOKUP[targetConv.user1_id === user1Id ? (targetConv.user2_id || '') : (targetConv.user1_id || '')],
  };
}

/**
 * Basecamp 4 Multi-Person Group Ping
 */
export async function createGroupDirectConversation(
  orgId: string,
  participantUserIds: string[],
  title?: string,
  creatorId?: string
): Promise<DirectConversation> {
  // Ensure unique list
  const uniqueIds = Array.from(new Set(participantUserIds));
  if (creatorId && !uniqueIds.includes(creatorId)) {
    uniqueIds.push(creatorId);
  }

  const defaultTitle =
    title?.trim() ||
    uniqueIds
      .map((uid) => DEMO_PROFILES_LOOKUP[uid]?.full_name || 'Member')
      .slice(0, 3)
      .join(', ') + (uniqueIds.length > 3 ? ` +${uniqueIds.length - 3} more` : '');

  if (isSupabaseConfigured) {
    try {
      const { data: convData, error: convError } = await supabase
        .from('direct_conversations')
        .insert([
          {
            organization_id: orgId,
            is_group: true,
            title: defaultTitle,
          },
        ])
        .select('*')
        .single();

      if (!convError && convData) {
        // Insert participants
        const participantsPayload = uniqueIds.map((uid) => ({
          conversation_id: convData.id,
          user_id: uid,
        }));
        await supabase.from('direct_conversation_participants').insert(participantsPayload);

        return {
          ...convData,
          participants: uniqueIds.map((uid) => ({
            id: `part-${Date.now()}-${uid}`,
            conversation_id: convData.id,
            user_id: uid,
            joined_at: new Date().toISOString(),
          })),
        } as DirectConversation;
      }
    } catch (e) {
      console.warn('Failed to create group ping via Supabase:', e);
    }
  }

  const newGroupConvId = `conv-group-${Date.now()}`;
  const participantsList: DirectConversationParticipant[] = uniqueIds.map((uid, idx) => ({
    id: `part-${Date.now()}-${idx}`,
    conversation_id: newGroupConvId,
    user_id: uid,
    joined_at: new Date().toISOString(),
    profile: DEMO_PROFILES_LOOKUP[uid] || { id: uid, full_name: 'Member', email: `${uid}@worksphere.io` },
  }));

  participantsList.forEach((p) => MOCK_CONVERSATION_PARTICIPANTS.push(p));

  const newConv: DirectConversation = {
    id: newGroupConvId,
    organization_id: orgId,
    title: defaultTitle,
    is_group: true,
    created_at: new Date().toISOString(),
    participants: participantsList,
    unread_count: 0,
  };

  MOCK_DIRECT_CONVERSATIONS.unshift(newConv);
  return newConv;
}

export async function getConversationParticipants(conversationId: string): Promise<DirectConversationParticipant[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('direct_conversation_participants')
        .select('*, profile:profiles(*)')
        .eq('conversation_id', conversationId);

      if (!error && data) return data as DirectConversationParticipant[];
    } catch (e) {
      console.warn('Failed to fetch conversation participants via Supabase:', e);
    }
  }

  return MOCK_CONVERSATION_PARTICIPANTS.filter((p) => p.conversation_id === conversationId).map((p) => ({
    ...p,
    profile: DEMO_PROFILES_LOOKUP[p.user_id] || { id: p.user_id, full_name: 'Member' },
  }));
}

export async function getDirectMessages(conversationId: string): Promise<DirectMessage[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('direct_messages')
        .select('*, sender:profiles(*)')
        .eq('conversation_id', conversationId)
        .order('created_at', { ascending: true });

      if (!error && data) return data as DirectMessage[];
    } catch (e) {
      console.warn('Failed to fetch direct messages via Supabase:', e);
    }
  }

  return MOCK_DIRECT_MESSAGES.filter((m) => m.conversation_id === conversationId);
}

export async function sendDirectMessage(
  conversationId: string,
  senderId: string,
  content: string
): Promise<DirectMessage> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('direct_messages')
        .insert([
          {
            conversation_id: conversationId,
            sender_id: senderId,
            content: content.trim(),
          },
        ])
        .select('*, sender:profiles(*)')
        .single();

      if (!error && data) {
        const dm = data as DirectMessage;
        realtimeBus.emit('DIRECT_MESSAGE', dm, { entityId: dm.id, senderId });
        return dm;
      }
    } catch (e) {
      console.warn('Failed to send direct message via Supabase:', e);
    }
  }

  const senderProfile =
    DEMO_PROFILES_LOOKUP[senderId] || {
      id: senderId,
      email: 'user@worksphere.io',
      full_name: 'User',
    };

  const newDm: DirectMessage = {
    id: `dm-${Date.now()}`,
    conversation_id: conversationId,
    sender_id: senderId,
    content: content.trim(),
    is_read: false,
    created_at: new Date().toISOString(),
    sender: senderProfile,
  };
  MOCK_DIRECT_MESSAGES.push(newDm);

  const conv = MOCK_DIRECT_CONVERSATIONS.find((c) => c.id === conversationId);
  if (conv) {
    conv.last_message = newDm;

    // Send notifications to other participants
    if (conv.is_group) {
      const otherParts = MOCK_CONVERSATION_PARTICIPANTS.filter(
        (p) => p.conversation_id === conversationId && p.user_id !== senderId
      );
      for (const p of otherParts) {
        createNotification({
          user_id: p.user_id,
          title: `New ping in ${conv.title || 'group'}`,
          message: `${senderProfile.full_name}: ${content.slice(0, 80)}`,
          type: 'MENTION',
          link_url: `/pings`,
        }).catch(console.warn);
      }
    } else {
      const targetUserId = conv.user1_id === senderId ? conv.user2_id : conv.user1_id;
      if (targetUserId) {
        createNotification({
          user_id: targetUserId,
          title: `Ping from ${senderProfile.full_name}`,
          message: content.slice(0, 80),
          type: 'DIRECT_MESSAGE',
          link_url: `/pings`,
        }).catch(console.warn);
      }
    }
  }

  realtimeBus.emit('DIRECT_MESSAGE', newDm, { entityId: newDm.id, senderId });
  return newDm;
}

export async function editChatMessage(messageId: string, content: string): Promise<{ error: Error | null }> {
  const editedAt = new Date().toISOString();
  if (isSupabaseConfigured) {
    const { error } = await supabase
      .from('chat_messages')
      .update({ content: content.trim(), is_edited: true, edited_at: editedAt })
      .eq('id', messageId);
    return { error: error as Error | null };
  }

  MOCK_CHAT_MESSAGES = MOCK_CHAT_MESSAGES.map((m) =>
    m.id === messageId ? { ...m, content: content.trim(), is_edited: true, edited_at: editedAt } : m
  );
  return { error: null };
}

export async function deleteChatMessage(messageId: string): Promise<{ error: Error | null }> {
  if (isSupabaseConfigured) {
    const { error } = await supabase.from('chat_messages').delete().eq('id', messageId);
    return { error: error as Error | null };
  }

  MOCK_CHAT_MESSAGES = MOCK_CHAT_MESSAGES.filter((m) => m.id !== messageId);
  return { error: null };
}

export function subscribeToRoomMessages(
  roomId: string,
  callback: (msg: ChatMessage) => void
): () => void {
  const unsubscribeBus = realtimeBus.subscribe('CHAT_MESSAGE', (payload) => {
    const msg = payload?.data as ChatMessage | undefined;
    if (msg && msg.room_id === roomId) {
      callback(msg);
    }
  });

  let channel: any = null;
  if (isSupabaseConfigured) {
    channel = supabase
      .channel(`chat-room-${roomId}`)
      .on(
        'postgres_changes',
        {
          event: 'INSERT',
          schema: 'public',
          table: 'chat_messages',
          filter: `room_id=eq.${roomId}`,
        },
        async (payload: any) => {
          if (payload?.new) {
            callback(payload.new as ChatMessage);
          }
        }
      )
      .subscribe();
  }

  return () => {
    unsubscribeBus();
    if (channel && isSupabaseConfigured) {
      supabase.removeChannel(channel);
    }
  };
}

export function subscribeToDirectMessages(
  conversationId: string,
  callback: (msg: DirectMessage) => void
): () => void {
  const unsubscribeBus = realtimeBus.subscribe('DIRECT_MESSAGE', (payload) => {
    const dm = payload?.data as DirectMessage | undefined;
    if (dm && dm.conversation_id === conversationId) {
      callback(dm);
    }
  });

  let channel: any = null;
  if (isSupabaseConfigured) {
    channel = supabase
      .channel(`dm-${conversationId}`)
      .on(
        'postgres_changes',
        {
          event: 'INSERT',
          schema: 'public',
          table: 'direct_messages',
          filter: `conversation_id=eq.${conversationId}`,
        },
        async (payload: any) => {
          if (payload?.new) {
            callback(payload.new as DirectMessage);
          }
        }
      )
      .subscribe();
  }

  return () => {
    unsubscribeBus();
    if (channel && isSupabaseConfigured) {
      supabase.removeChannel(channel);
    }
  };
}
