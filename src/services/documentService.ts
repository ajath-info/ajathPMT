import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { ProjectDocument } from '../types';

let MOCK_DOCUMENTS: ProjectDocument[] = [
  {
    id: 'doc-1',
    project_id: 'proj-hq',
    title: 'Ajath Infotech Pvt Ltd - Company Handbook & SOPs',
    content: '## Executive Summary\nWelcome to Ajath Infotech Pvt Ltd! This document outlines company-wide procedures, project execution frameworks, and standard operating procedures.\n\n### Core Tenets\n1. Transparent client collaboration\n2. Rapid milestone delivery\n3. High craftsmanship in mobile & web solutions',
    created_by: 'demo-user-owner',
    updated_by: 'demo-user-owner',
    is_archived: false,
    created_at: new Date(Date.now() - 4 * 24 * 3600 * 1000).toISOString(),
    updated_at: new Date(Date.now() - 1 * 24 * 3600 * 1000).toISOString(),
    creator: {
      id: 'demo-user-owner',
      email: 'claire.client@partner.com',
      full_name: 'Claire Watson',
      avatar_url: '',
    },
  },
  {
    id: 'doc-2',
    project_id: 'proj-rmc',
    title: 'Ride My Cars - Driver App Specifications',
    content: '## Overview\nRide My Cars is a high-availability driver dispatch and vehicle inspection application.\n\n- Driver login & biometric auth\n- Real-time ride bookings\n- Vehicle safety checklist & photo evidence upload',
    created_by: 'demo-user-owner',
    updated_by: 'demo-user-owner',
    is_archived: false,
    created_at: new Date(Date.now() - 2 * 24 * 3600 * 1000).toISOString(),
    updated_at: new Date(Date.now() - 2 * 24 * 3600 * 1000).toISOString(),
    creator: {
      id: 'demo-user-owner',
      email: 'claire.client@partner.com',
      full_name: 'Claire Watson',
      avatar_url: '',
    },
  },
];

export async function getProjectDocuments(
  projectId: string,
  includeArchived = false
): Promise<ProjectDocument[]> {
  if (isSupabaseConfigured) {
    try {
      let query = supabase
        .from('documents')
        .select('*, creator:profiles!created_by(*), editor:profiles!updated_by(*)')
        .eq('project_id', projectId)
        .order('updated_at', { ascending: false });

      if (!includeArchived) {
        query = query.eq('is_archived', false);
      }

      const { data, error } = await query;
      if (!error && data) return data as ProjectDocument[];
    } catch (e) {
      console.warn('Failed to fetch documents via Supabase:', e);
    }
  }

  return MOCK_DOCUMENTS.filter(
    (d) => d.project_id === projectId && (includeArchived || !d.is_archived)
  );
}

export async function getDocumentById(id: string): Promise<ProjectDocument | null> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('documents')
        .select('*, creator:profiles!created_by(*), editor:profiles!updated_by(*)')
        .eq('id', id)
        .single();

      if (!error && data) return data as ProjectDocument;
    } catch (e) {
      console.warn('Failed to fetch document by id via Supabase:', e);
    }
  }

  return MOCK_DOCUMENTS.find((d) => d.id === id) || null;
}

export async function createDocument(input: {
  project_id: string;
  title: string;
  content?: string;
  created_by?: string;
}): Promise<ProjectDocument> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('documents')
        .insert([
          {
            project_id: input.project_id,
            title: input.title.trim(),
            content: input.content || '',
            created_by: input.created_by || null,
            updated_by: input.created_by || null,
          },
        ])
        .select('*, creator:profiles!created_by(*)')
        .single();

      if (!error && data) return data as ProjectDocument;
    } catch (e) {
      console.warn('Failed to create document via Supabase:', e);
    }
  }

  const newDoc: ProjectDocument = {
    id: `doc-${Date.now()}`,
    project_id: input.project_id,
    title: input.title.trim(),
    content: input.content || '',
    created_by: input.created_by,
    updated_by: input.created_by,
    is_archived: false,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  };
  MOCK_DOCUMENTS.unshift(newDoc);
  return newDoc;
}

export async function updateDocument(
  id: string,
  updates: { title?: string; content?: string; updated_by?: string }
): Promise<ProjectDocument> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('documents')
        .update({
          ...updates,
          updated_at: new Date().toISOString(),
        })
        .eq('id', id)
        .select('*, creator:profiles!created_by(*), editor:profiles!updated_by(*)')
        .single();

      if (!error && data) return data as ProjectDocument;
    } catch (e) {
      console.warn('Failed to update document via Supabase:', e);
    }
  }

  const idx = MOCK_DOCUMENTS.findIndex((d) => d.id === id);
  if (idx !== -1) {
    MOCK_DOCUMENTS[idx] = {
      ...MOCK_DOCUMENTS[idx],
      ...updates,
      updated_at: new Date().toISOString(),
    };
    return MOCK_DOCUMENTS[idx];
  }
  throw new Error('Document not found');
}

export async function archiveDocument(id: string): Promise<void> {
  if (isSupabaseConfigured) {
    await supabase.from('documents').update({ is_archived: true }).eq('id', id);
  }
  const doc = MOCK_DOCUMENTS.find((d) => d.id === id);
  if (doc) doc.is_archived = true;
}

export async function restoreDocument(id: string): Promise<void> {
  if (isSupabaseConfigured) {
    await supabase.from('documents').update({ is_archived: false }).eq('id', id);
  }
  const doc = MOCK_DOCUMENTS.find((d) => d.id === id);
  if (doc) doc.is_archived = false;
}

export async function deleteDocument(id: string): Promise<void> {
  if (isSupabaseConfigured) {
    await supabase.from('documents').delete().eq('id', id);
  }
  MOCK_DOCUMENTS = MOCK_DOCUMENTS.filter((d) => d.id !== id);
}

// Document Versions
let MOCK_DOC_VERSIONS: any[] = [];

export async function getDocumentVersions(documentId: string) {
  if (isSupabaseConfigured) {
    const { data, error } = await supabase
      .from('document_versions')
      .select('*, creator:profiles!created_by(*)')
      .eq('document_id', documentId)
      .order('version_number', { ascending: false });

    if (!error && data) return data;
  }

  return MOCK_DOC_VERSIONS.filter((v) => v.document_id === documentId);
}

export async function saveDocumentVersion(documentId: string, title: string, content: string, createdBy?: string) {
  const currentVersions = await getDocumentVersions(documentId);
  const nextVer = (currentVersions.length > 0 ? currentVersions[0].version_number : 0) + 1;

  if (isSupabaseConfigured) {
    const { data, error } = await supabase
      .from('document_versions')
      .insert([{
        document_id: documentId,
        title,
        content,
        created_by: createdBy || null,
        version_number: nextVer,
      }])
      .select('*, creator:profiles!created_by(*)')
      .single();

    if (!error && data) return data;
  }

  const newVer = {
    id: `ver-${Date.now()}`,
    document_id: documentId,
    title,
    content,
    created_by: createdBy,
    version_number: nextVer,
    created_at: new Date().toISOString(),
  };
  MOCK_DOC_VERSIONS.unshift(newVer);
  return newVer;
}

export async function restoreDocumentVersion(documentId: string, versionId: string): Promise<ProjectDocument> {
  const versions = await getDocumentVersions(documentId);
  const targetVersion = versions.find((v: any) => v.id === versionId);
  if (!targetVersion) throw new Error('Version not found');

  return updateDocument(documentId, {
    title: targetVersion.title,
    content: targetVersion.content,
  });
}

