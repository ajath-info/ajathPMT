import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { PersonalNote, UserBookmark, RecentlyViewedItem } from '../types';

let MOCK_PERSONAL_NOTES: PersonalNote[] = [
  {
    id: 'note-1',
    user_id: 'demo-user-owner',
    title: 'Weekly Priorities & Refactoring Strategy',
    content: '- Finalize Supabase RLS security policies\n- Conduct UI design audit\n- Review task attachment storage limits',
    created_at: new Date(Date.now() - 2 * 24 * 3600 * 1000).toISOString(),
    updated_at: new Date().toISOString(),
  },
];

let MOCK_BOOKMARKS: UserBookmark[] = [
  {
    id: 'bm-1',
    user_id: 'demo-user-owner',
    item_type: 'project',
    item_id: 'proj-1',
    title: 'Ajath PMT Web Platform v1.0',
    url: '/projects/proj-1',
    created_at: new Date().toISOString(),
  },
];

let MOCK_RECENTLY_VIEWED: RecentlyViewedItem[] = [
  {
    id: 'rv-1',
    user_id: 'demo-user-owner',
    item_type: 'project',
    item_id: 'proj-1',
    title: 'Ajath PMT Web Platform v1.0',
    url: '/projects/proj-1',
    viewed_at: new Date().toISOString(),
  },
];

// --- PERSONAL NOTES ---
export async function getPersonalNotes(userId: string): Promise<PersonalNote[]> {
  if (!isSupabaseConfigured) {
    return MOCK_PERSONAL_NOTES.filter((n) => n.user_id === userId);
  }

  try {
    const { data, error } = await supabase
      .from('user_personal_notes')
      .select('*')
      .eq('user_id', userId)
      .order('updated_at', { ascending: false });

    if (!error && data) return data as PersonalNote[];
  } catch (e) {
    console.warn('Personal notes fetch error:', e);
  }

  return MOCK_PERSONAL_NOTES;
}

export async function createPersonalNote(userId: string, title: string, content: string): Promise<PersonalNote> {
  if (!isSupabaseConfigured) {
    const newNote: PersonalNote = {
      id: `note-${Date.now()}`,
      user_id: userId,
      title: title || 'Untitled Note',
      content: content || '',
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    };
    MOCK_PERSONAL_NOTES.unshift(newNote);
    return newNote;
  }

  const { data } = await supabase
    .from('user_personal_notes')
    .insert([{ user_id: userId, title, content }])
    .select()
    .single();

  return data as PersonalNote;
}

export async function updatePersonalNote(id: string, title: string, content: string): Promise<void> {
  if (!isSupabaseConfigured) {
    MOCK_PERSONAL_NOTES = MOCK_PERSONAL_NOTES.map((n) =>
      n.id === id ? { ...n, title, content, updated_at: new Date().toISOString() } : n
    );
    return;
  }

  await supabase
    .from('user_personal_notes')
    .update({ title, content, updated_at: new Date().toISOString() })
    .eq('id', id);
}

export async function deletePersonalNote(id: string): Promise<void> {
  if (!isSupabaseConfigured) {
    MOCK_PERSONAL_NOTES = MOCK_PERSONAL_NOTES.filter((n) => n.id !== id);
    return;
  }

  await supabase.from('user_personal_notes').delete().eq('id', id);
}

// --- BOOKMARKS ---
export async function getUserBookmarks(userId: string): Promise<UserBookmark[]> {
  if (!isSupabaseConfigured) {
    return MOCK_BOOKMARKS.filter((b) => b.user_id === userId);
  }

  try {
    const { data, error } = await supabase
      .from('user_bookmarks')
      .select('*')
      .eq('user_id', userId)
      .order('created_at', { ascending: false });

    if (!error && data) return data as UserBookmark[];
  } catch (e) {
    console.warn('Bookmarks fetch error:', e);
  }

  return MOCK_BOOKMARKS;
}

export async function toggleBookmark(
  userId: string,
  itemType: 'task' | 'discussion' | 'document' | 'file' | 'project',
  itemId: string,
  title: string,
  url: string
): Promise<boolean> {
  if (!isSupabaseConfigured) {
    const idx = MOCK_BOOKMARKS.findIndex((b) => b.user_id === userId && b.item_id === itemId);
    if (idx >= 0) {
      MOCK_BOOKMARKS.splice(idx, 1);
      return false;
    } else {
      MOCK_BOOKMARKS.unshift({
        id: `bm-${Date.now()}`,
        user_id: userId,
        item_type: itemType,
        item_id: itemId,
        title,
        url,
        created_at: new Date().toISOString(),
      });
      return true;
    }
  }

  const { data: existing } = await supabase
    .from('user_bookmarks')
    .select('id')
    .eq('user_id', userId)
    .eq('item_id', itemId)
    .maybeSingle();

  if (existing) {
    await supabase.from('user_bookmarks').delete().eq('id', existing.id);
    return false;
  } else {
    await supabase.from('user_bookmarks').insert([{ user_id: userId, item_type: itemType, item_id: itemId, title, url }]);
    return true;
  }
}

// --- RECENTLY VIEWED ---
export async function getRecentlyViewed(userId: string): Promise<RecentlyViewedItem[]> {
  if (!isSupabaseConfigured) {
    return MOCK_RECENTLY_VIEWED.filter((r) => r.user_id === userId);
  }

  try {
    const { data, error } = await supabase
      .from('user_recently_viewed')
      .select('*')
      .eq('user_id', userId)
      .order('viewed_at', { ascending: false })
      .limit(10);

    if (!error && data) return data as RecentlyViewedItem[];
  } catch (e) {
    console.warn('Recently viewed fetch error:', e);
  }

  return MOCK_RECENTLY_VIEWED.filter((r) => r.user_id === userId);
}

export async function recordRecentlyViewed(
  userId: string,
  itemType: 'task' | 'discussion' | 'document' | 'file' | 'project',
  itemId: string,
  title: string,
  url: string
): Promise<void> {
  if (!isSupabaseConfigured) {
    MOCK_RECENTLY_VIEWED = MOCK_RECENTLY_VIEWED.filter((r) => r.item_id !== itemId);
    MOCK_RECENTLY_VIEWED.unshift({
      id: `rv-${Date.now()}`,
      user_id: userId,
      item_type: itemType,
      item_id: itemId,
      title,
      url,
      viewed_at: new Date().toISOString(),
    });
    return;
  }

  await supabase.from('user_recently_viewed').upsert(
    [{ user_id: userId, item_type: itemType, item_id: itemId, title, url, viewed_at: new Date().toISOString() }],
    { onConflict: 'user_id,item_type,item_id' }
  );
}

// Aliases and helpers for Personal Productivity UI
export async function getUserPersonalNotes(userId: string): Promise<string> {
  const notes = await getPersonalNotes(userId);
  return notes.length > 0 ? notes[0].content : '';
}

export async function updateUserPersonalNotes(userId: string, content: string): Promise<void> {
  const notes = await getPersonalNotes(userId);
  if (notes.length > 0) {
    await updatePersonalNote(notes[0].id, notes[0].title, content);
  } else {
    await createPersonalNote(userId, 'My Personal Notes', content);
  }
}

export async function addBookmark(userId: string, title: string, url: string): Promise<{ data: UserBookmark | null }> {
  const newBm: UserBookmark = {
    id: `bm-${Date.now()}`,
    user_id: userId,
    item_type: 'project',
    item_id: `item-${Date.now()}`,
    title,
    url,
    created_at: new Date().toISOString(),
  };

  if (!isSupabaseConfigured) {
    MOCK_BOOKMARKS.unshift(newBm);
    return { data: newBm };
  }

  const { data } = await supabase
    .from('user_bookmarks')
    .insert([newBm])
    .select()
    .single();

  return { data: (data as UserBookmark) || newBm };
}

export async function deleteBookmark(id: string): Promise<void> {
  if (!isSupabaseConfigured) {
    MOCK_BOOKMARKS = MOCK_BOOKMARKS.filter((b) => b.id !== id);
    return;
  }
  await supabase.from('user_bookmarks').delete().eq('id', id);
}

export const getUserRecentlyViewed = getRecentlyViewed;

