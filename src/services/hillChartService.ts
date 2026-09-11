import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { realtimeBus } from '../lib/realtimeEventBus';

export interface HillScopeRecord {
  id: string;
  project_id: string;
  name: string;
  color: string;
  progress: number; // 0 to 100
  description?: string;
  tasks_count?: number;
  completed_count?: number;
  created_by?: string;
  created_at?: string;
  updated_at?: string;
}

const DEFAULT_SCOPES: Record<string, HillScopeRecord[]> = {
  'proj-1': [
    { id: 'scope-ui', project_id: 'proj-1', name: 'UI & Visual Design Tokens', color: '#6366f1', progress: 85, tasks_count: 12, completed_count: 10 },
    { id: 'scope-auth', project_id: 'proj-1', name: 'Authentication & RLS Security', color: '#10b981', progress: 95, tasks_count: 8, completed_count: 8 },
    { id: 'scope-chat', project_id: 'proj-1', name: 'Campfire Realtime Broadcast', color: '#f59e0b', progress: 65, tasks_count: 6, completed_count: 4 },
    { id: 'scope-deps', project_id: 'proj-1', name: 'Task Dependency DAG Engine', color: '#8b5cf6', progress: 40, tasks_count: 9, completed_count: 4 },
    { id: 'scope-checkin', project_id: 'proj-1', name: 'Automated Daily Check-ins', color: '#ec4899', progress: 30, tasks_count: 5, completed_count: 2 },
  ],
};

function getLocalScopesKey(projectId: string): string {
  return `worksphere_hill_scopes_${projectId}`;
}

export async function getProjectHillScopes(projectId: string): Promise<HillScopeRecord[]> {
  // 1. Try Supabase if configured
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('hill_chart_scopes')
        .select('*')
        .eq('project_id', projectId)
        .order('created_at', { ascending: true });

      if (!error && data && data.length > 0) {
        return data as HillScopeRecord[];
      }
    } catch (e) {
      console.warn('Supabase fetch hill scopes error:', e);
    }
  }

  // 2. Check localStorage cache for persistence across page reloads & sessions
  if (typeof window !== 'undefined') {
    try {
      const cached = localStorage.getItem(getLocalScopesKey(projectId));
      if (cached) {
        const parsed = JSON.parse(cached);
        if (Array.isArray(parsed) && parsed.length > 0) {
          return parsed;
        }
      }
    } catch (e) {
      console.warn('Error reading local hill scopes:', e);
    }
  }

  // 3. Fallback to default scopes and cache them
  const defaults = DEFAULT_SCOPES[projectId] || [
    { id: `scope-${Date.now()}-1`, project_id: projectId, name: 'Core Architecture', color: '#6366f1', progress: 50 },
    { id: `scope-${Date.now()}-2`, project_id: projectId, name: 'Implementation', color: '#10b981', progress: 25 },
  ];

  if (typeof window !== 'undefined') {
    try {
      localStorage.setItem(getLocalScopesKey(projectId), JSON.stringify(defaults));
    } catch (e) {
      console.warn('Error caching default hill scopes:', e);
    }
  }

  return defaults;
}

export async function updateHillScopePosition(
  projectId: string,
  scopeId: string,
  newProgress: number
): Promise<HillScopeRecord | null> {
  const clampedProgress = Math.max(0, Math.min(100, Math.round(newProgress)));

  // 1. Persist to Supabase if configured
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('hill_chart_scopes')
        .update({ progress: clampedProgress, updated_at: new Date().toISOString() })
        .eq('id', scopeId)
        .select()
        .single();

      if (!error && data) {
        realtimeBus.emit('HILL_SCOPE_MOVED', data, { projectId, scopeId });
        return data as HillScopeRecord;
      }
    } catch (e) {
      console.warn('Failed to update hill scope in Supabase:', e);
    }
  }

  // 2. Persist to localStorage for development & mock mode persistence
  if (typeof window !== 'undefined') {
    try {
      const existing = await getProjectHillScopes(projectId);
      const updated = existing.map((s) => (s.id === scopeId ? { ...s, progress: clampedProgress, updated_at: new Date().toISOString() } : s));
      localStorage.setItem(getLocalScopesKey(projectId), JSON.stringify(updated));

      const modified = updated.find((s) => s.id === scopeId) || null;
      if (modified) {
        realtimeBus.emit('HILL_SCOPE_MOVED', modified, { projectId, scopeId });
      }
      return modified;
    } catch (e) {
      console.warn('Error saving local hill scope position:', e);
    }
  }

  return null;
}

export async function createHillScope(
  projectId: string,
  name: string,
  color = '#6366f1',
  initialProgress = 30,
  description?: string
): Promise<HillScopeRecord> {
  const newRecord: HillScopeRecord = {
    id: `scope-${Date.now()}`,
    project_id: projectId,
    name: name.trim(),
    color,
    progress: initialProgress,
    description,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  };

  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('hill_chart_scopes')
        .insert([newRecord])
        .select()
        .single();

      if (!error && data) {
        realtimeBus.emit('HILL_SCOPE_MOVED', data, { projectId });
        return data as HillScopeRecord;
      }
    } catch (e) {
      console.warn('Supabase create hill scope error:', e);
    }
  }

  if (typeof window !== 'undefined') {
    const existing = await getProjectHillScopes(projectId);
    existing.push(newRecord);
    localStorage.setItem(getLocalScopesKey(projectId), JSON.stringify(existing));
    realtimeBus.emit('HILL_SCOPE_MOVED', newRecord, { projectId });
  }

  return newRecord;
}
