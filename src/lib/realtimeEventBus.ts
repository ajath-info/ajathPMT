/**
 * Realtime Event Bus for Ajath PMT / WorkSphere
 * Leverages the browser BroadcastChannel API for multi-tab/multi-window synchronization in development/mock mode,
 * and proxies to Supabase Realtime channels in production.
 */

import { isSupabaseConfigured } from './supabase';

export type RealtimeEventType =
  | 'CHAT_MESSAGE'
  | 'DIRECT_MESSAGE'
  | 'TASK_UPDATED'
  | 'TASK_COMPLETED'
  | 'KANBAN_MOVED'
  | 'HILL_SCOPE_MOVED'
  | 'NOTIFICATION_RECEIVED'
  | 'CALENDAR_EVENT'
  | 'DISCUSSION_CREATED';

export interface RealtimeEventPayload<T = any> {
  type: RealtimeEventType;
  entityId?: string;
  scopeId?: string;
  projectId?: string;
  organizationId?: string;
  senderId?: string;
  data: T;
  timestamp: string;
}

type EventCallback<T = any> = (payload: RealtimeEventPayload<T>) => void;

class RealtimeEventBus {
  private channel: BroadcastChannel | null = null;
  private listeners: Map<string, Set<EventCallback>> = new Map();

  constructor() {
    if (typeof window !== 'undefined' && 'BroadcastChannel' in window) {
      try {
        this.channel = new BroadcastChannel('ajath_pmt_realtime_bus');
        this.channel.onmessage = (event: MessageEvent<RealtimeEventPayload>) => {
          this.notifySubscribers(event.data);
        };
      } catch (err) {
        console.warn('BroadcastChannel initialization failed, using in-memory bus:', err);
      }
    }
  }

  /**
   * Broadcast an event to all other open tabs/windows
   */
  public emit<T = any>(
    type: RealtimeEventType,
    data: T,
    metadata?: {
      entityId?: string;
      scopeId?: string;
      projectId?: string;
      organizationId?: string;
      senderId?: string;
    }
  ): void {
    const payload: RealtimeEventPayload<T> = {
      type,
      entityId: metadata?.entityId,
      scopeId: metadata?.scopeId,
      projectId: metadata?.projectId,
      organizationId: metadata?.organizationId,
      senderId: metadata?.senderId,
      data,
      timestamp: new Date().toISOString(),
    };

    // Dispatch locally to same tab subscribers
    this.notifySubscribers(payload);

    // Broadcast across other browser tabs/windows
    if (this.channel) {
      try {
        this.channel.postMessage(payload);
      } catch (e) {
        console.warn('Failed to broadcast realtime event:', e);
      }
    }
  }

  /**
   * Subscribe to specific event type
   */
  public subscribe<T = any>(type: RealtimeEventType, callback: EventCallback<T>): () => void {
    if (!this.listeners.has(type)) {
      this.listeners.set(type, new Set());
    }
    const set = this.listeners.get(type)!;
    set.add(callback as EventCallback);

    return () => {
      set.delete(callback as EventCallback);
      if (set.size === 0) {
        this.listeners.delete(type);
      }
    };
  }

  private notifySubscribers(payload: RealtimeEventPayload): void {
    const set = this.listeners.get(payload.type);
    if (set) {
      set.forEach((cb) => {
        try {
          cb(payload);
        } catch (e) {
          console.error(`Error in realtime subscriber for ${payload.type}:`, e);
        }
      });
    }
  }
}

export const realtimeBus = new RealtimeEventBus();
