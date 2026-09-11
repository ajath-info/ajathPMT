/**
 * WorkSphere / Ajath PMT - Inbound Email Processing Service
 * Enables Basecamp-style "Reply-by-Email" for To-Dos, Message Boards / Discussions, and Campfire.
 */

import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { addTaskComment } from './taskService';
import { addDiscussionComment } from './discussionService';
import { sendChatMessage } from './chatService';

export interface InboundEmailWebhookPayload {
  from: string;
  to: string;
  subject?: string;
  text: string;
  html?: string;
  headers?: Record<string, string>;
  messageId?: string;
}

export interface InboundProcessResult {
  success: boolean;
  actionTaken?: 'TASK_COMMENT_CREATED' | 'DISCUSSION_COMMENT_CREATED' | 'CHAT_MESSAGE_SENT';
  entityId?: string;
  userId?: string;
  error?: string;
}

const INBOUND_LOGS_STORAGE_KEY = 'worksphere_inbound_email_logs';

/**
 * Generates an inbound reply-to address token containing the target entity & user.
 * Format: reply+<type>_<id>_<userId>_<checksum>@inbound.worksphere.io
 */
export function generateInboundReplyToken(
  entityType: 'TASK' | 'DISCUSSION' | 'CHAT',
  entityId: string,
  userId: string
): string {
  const cleanType = entityType.toLowerCase();
  const rawPayload = `${cleanType}:${entityId}:${userId}`;
  const checksum = simpleChecksum(rawPayload);
  return `${cleanType}_${entityId}_${userId}_${checksum}`;
}

export function parseInboundReplyToken(token: string): {
  entityType: 'TASK' | 'DISCUSSION' | 'CHAT';
  entityId: string;
  userId: string;
  isValid: boolean;
} | null {
  const parts = token.split('_');
  if (parts.length < 4) return null;

  const entityType = parts[0].toUpperCase() as 'TASK' | 'DISCUSSION' | 'CHAT';
  const entityId = parts[1];
  const userId = parts[2];
  const givenChecksum = parts[3];

  const expectedChecksum = simpleChecksum(`${parts[0]}:${entityId}:${userId}`);
  const isValid = givenChecksum === expectedChecksum;

  return {
    entityType,
    entityId,
    userId,
    isValid,
  };
}

/**
 * Strips quoted reply history commonly inserted by mail clients (Gmail, Apple Mail, Outlook).
 */
export function stripQuotedEmailReplies(rawText: string): string {
  if (!rawText) return '';

  const lines = rawText.split(/\r?\n/);
  const keptLines: string[] = [];

  for (const line of lines) {
    const trimmed = line.trim();

    // Standard quote headers
    if (/^on\s+.+wrote:$/i.test(trimmed)) break;
    if (/^-+\s*original message\s*-+$/i.test(trimmed)) break;
    if (/^from:\s+.+$/i.test(trimmed) && keptLines.length > 0) break;
    if (/^_{10,}/.test(trimmed)) break; // Outlook underline separator
    if (/^--\s*$/.test(trimmed)) break; // Standard signature separator

    // Skip quoted lines starting with >
    if (line.startsWith('>')) continue;

    keptLines.push(line);
  }

  return keptLines.join('\n').trim();
}

function simpleChecksum(str: string): string {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    const char = str.charCodeAt(i);
    hash = (hash << 5) - hash + char;
    hash |= 0; // Convert to 32bit integer
  }
  return Math.abs(hash).toString(36);
}

export function getLinkedInboundLogs(): any[] {
  try {
    const raw = localStorage.getItem(INBOUND_LOGS_STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

/**
 * Processes an inbound email webhook payload.
 */
export async function processInboundEmail(
  payload: InboundEmailWebhookPayload
): Promise<InboundProcessResult> {
  const toAddress = payload.to || '';
  const match = toAddress.match(/reply\+([a-zA-Z0-9_\-]+)@/);

  if (!match) {
    return {
      success: false,
      error: `Could not parse inbound reply token from address: ${toAddress}`,
    };
  }

  const tokenString = match[1];
  const parsed = parseInboundReplyToken(tokenString);

  if (!parsed || !parsed.isValid) {
    return {
      success: false,
      error: 'Invalid or forged reply-to token',
    };
  }

  const cleanBody = stripQuotedEmailReplies(payload.text);
  if (!cleanBody) {
    return {
      success: false,
      error: 'Email reply body is empty after stripping quotes',
    };
  }

  let actionTaken: InboundProcessResult['actionTaken'] = undefined;

  try {
    if (parsed.entityType === 'TASK') {
      await addTaskComment(parsed.entityId, parsed.userId, cleanBody);
      actionTaken = 'TASK_COMMENT_CREATED';
    } else if (parsed.entityType === 'DISCUSSION') {
      await addDiscussionComment(parsed.entityId, parsed.userId, cleanBody);
      actionTaken = 'DISCUSSION_COMMENT_CREATED';
    } else if (parsed.entityType === 'CHAT') {
      await sendChatMessage(parsed.entityId, parsed.userId, cleanBody);
      actionTaken = 'CHAT_MESSAGE_SENT';
    }

    // Record to database if configured
    if (isSupabaseConfigured) {
      await supabase.from('inbound_email_logs').insert([
        {
          from_address: payload.from,
          to_address: payload.to,
          subject: payload.subject || 'Re:',
          entity_type: parsed.entityType,
          entity_id: parsed.entityId,
          user_id: parsed.userId,
          status: 'PROCESSED',
          raw_body: cleanBody,
        },
      ]);
    }

    // Persist to local audit logs
    const logs = getLinkedInboundLogs();
    logs.push({
      timestamp: new Date().toISOString(),
      from: payload.from,
      to: payload.to,
      entityType: parsed.entityType,
      entityId: parsed.entityId,
      userId: parsed.userId,
      status: 'PROCESSED',
      cleanBody,
    });
    if (logs.length > 50) logs.shift();
    localStorage.setItem(INBOUND_LOGS_STORAGE_KEY, JSON.stringify(logs));

    return {
      success: true,
      actionTaken,
      entityId: parsed.entityId,
      userId: parsed.userId,
    };
  } catch (err: any) {
    return {
      success: false,
      error: err?.message || 'Failed to dispatch inbound comment',
    };
  }
}
