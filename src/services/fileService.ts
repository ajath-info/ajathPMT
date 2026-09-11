import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { Folder, ProjectFile, FileComment, FileVersion, OrgRole } from '../types';

let MOCK_FOLDERS: Folder[] = [
  { id: 'fld-rmc-1', project_id: 'proj-rmc', name: 'Configuration Management (CM)', created_at: new Date().toISOString() },
  { id: 'fld-rmc-2', project_id: 'proj-rmc', name: 'Project Planning (PP)', created_at: new Date().toISOString() },
  { id: 'fld-rmc-3', project_id: 'proj-rmc', name: 'Project Monitoring and Control (PMC)', created_at: new Date().toISOString() },
  { id: 'fld-hq-1', project_id: 'proj-hq', name: 'Engineering & Architecture', created_at: new Date().toISOString() },
  { id: 'fld-hq-2', project_id: 'proj-hq', name: 'HR & Company Policies', created_at: new Date().toISOString() },
];

let MOCK_FILES: ProjectFile[] = [
  {
    id: 'file-rmc-1',
    project_id: 'proj-rmc',
    folder_id: undefined,
    name: 'WEB PROPOSAL .pdf',
    storage_path: 'mock/web-proposal.pdf',
    mime_type: 'application/pdf',
    size: 585728, // ~572 KB
    description: 'Nirdesh Verma • Aug 12, 2025 • 572 KB',
    created_at: '2025-08-12T10:30:00.000Z',
    uploader: {
      id: 'user-nv',
      email: 'nirdesh@ajath.com',
      full_name: 'Nirdesh Verma',
      avatar_url: '',
    },
  },
  {
    id: 'file-rmc-pp-1',
    project_id: 'proj-rmc',
    folder_id: 'fld-rmc-2',
    name: 'Ride My Cars Sprint Plan v1.0.docx',
    storage_path: 'mock/sprint-plan.docx',
    mime_type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    size: 43008,
    description: 'Claire Watson • Aug 10, 2025 • 42 KB',
    created_at: '2025-08-10T11:00:00.000Z',
    uploader: {
      id: 'demo-user-owner',
      email: 'claire.client@partner.com',
      full_name: 'Claire Watson',
      avatar_url: '',
    },
  },
  {
    id: 'file-rmc-pp-2',
    project_id: 'proj-rmc',
    folder_id: 'fld-rmc-2',
    name: 'Delivery Roadmap & Milestone Schedule.xlsx',
    storage_path: 'mock/roadmap.xlsx',
    mime_type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    size: 120832,
    description: 'Pankaj Kumar • Aug 08, 2025 • 118 KB',
    created_at: '2025-08-08T15:20:00.000Z',
    uploader: {
      id: 'user-pk',
      email: 'pankaj@ajath.com',
      full_name: 'Pankaj Kumar',
      avatar_url: '',
    },
  },
  {
    id: 'file-rmc-pmc-1',
    project_id: 'proj-rmc',
    folder_id: 'fld-rmc-3',
    name: 'PMC Weekly Status Report #1.pdf',
    storage_path: 'mock/pmc-report-1.pdf',
    mime_type: 'application/pdf',
    size: 245760,
    description: 'Shachish Sneh • Aug 20, 2025 • 240 KB',
    created_at: '2025-08-20T09:00:00.000Z',
    uploader: {
      id: 'demo-user-owner',
      email: 'shachish@ajath.com',
      full_name: 'Shachish Sneh',
      avatar_url: '',
    },
  },
];

let MOCK_FILE_COMMENTS: FileComment[] = [
  {
    id: 'fcomm-1',
    file_id: 'file-rmc-1',
    user_id: 'demo-user-owner',
    content: 'Client has acknowledged and signed page 4 of the proposal.',
    created_at: new Date(Date.now() - 2 * 24 * 3600 * 1000).toISOString(),
    profile: {
      id: 'demo-user-owner',
      full_name: 'Shivy Narain',
      email: 'shivy@ajath.com',
    },
  },
];

let MOCK_FILE_VERSIONS: FileVersion[] = [
  {
    id: 'fver-1',
    file_id: 'file-rmc-pp-1',
    version_number: 1,
    name: 'Ride My Cars Sprint Plan v0.9 (Draft).docx',
    storage_path: 'mock/sprint-plan-v0.9.docx',
    mime_type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    size: 40100,
    uploaded_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 5 * 24 * 3600 * 1000).toISOString(),
  },
];

export async function getProjectFolders(projectId: string, parentFolderId?: string | null): Promise<Folder[]> {
  if (isSupabaseConfigured) {
    try {
      let query = supabase.from('folders').select('*').eq('project_id', projectId);
      if (parentFolderId) {
        query = query.eq('parent_folder_id', parentFolderId);
      } else {
        query = query.is('parent_folder_id', null);
      }
      const { data, error } = await query.order('name', { ascending: true });
      if (!error && data) return data as Folder[];
    } catch (e) {
      console.warn('Failed to fetch folders via Supabase:', e);
    }
  }

  return MOCK_FOLDERS.filter(
    (f) => f.project_id === projectId && (parentFolderId ? f.parent_folder_id === parentFolderId : !f.parent_folder_id)
  );
}

export async function getProjectFiles(projectId: string, folderId?: string | null): Promise<ProjectFile[]> {
  if (isSupabaseConfigured) {
    try {
      let query = supabase.from('files').select('*, uploader:profiles(*)').eq('project_id', projectId);
      if (folderId) {
        query = query.eq('folder_id', folderId);
      } else {
        query = query.is('folder_id', null);
      }
      const { data, error } = await query.order('created_at', { ascending: false });
      if (!error && data) return data as ProjectFile[];
    } catch (e) {
      console.warn('Failed to fetch files via Supabase:', e);
    }
  }

  return MOCK_FILES.filter(
    (f) => f.project_id === projectId && (folderId ? f.folder_id === folderId : !f.folder_id)
  );
}

export async function getProjectFilesAndFolders(
  projectId: string,
  parentFolderId?: string | null
): Promise<{ files: ProjectFile[]; folders: Folder[] }> {
  const [folders, files] = await Promise.all([
    getProjectFolders(projectId, parentFolderId),
    getProjectFiles(projectId, parentFolderId),
  ]);
  return { files, folders };
}

export async function createFolder(
  projectId: string,
  name: string,
  parentFolderId?: string | null,
  createdBy?: string,
  userRole?: OrgRole
): Promise<Folder> {
  if (userRole === 'CLIENT') {
    throw new Error('Unauthorized: Client accounts cannot create folders.');
  }

  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('folders')
        .insert([
          {
            project_id: projectId,
            parent_folder_id: parentFolderId || null,
            name: name.trim(),
            created_by: createdBy || null,
          },
        ])
        .select()
        .single();

      if (!error && data) return data as Folder;
    } catch (e) {
      console.warn('Failed to create folder via Supabase:', e);
    }
  }

  const newFolder: Folder = {
    id: `fld-${Date.now()}`,
    project_id: projectId,
    parent_folder_id: parentFolderId || undefined,
    name: name.trim(),
    created_by: createdBy,
    created_at: new Date().toISOString(),
  };
  MOCK_FOLDERS.push(newFolder);
  return newFolder;
}

export async function deleteFolder(folderId: string, userRole?: OrgRole): Promise<void> {
  if (userRole === 'CLIENT') {
    throw new Error('Unauthorized: Client accounts cannot delete project folders.');
  }
  if (isSupabaseConfigured) {
    await supabase.from('folders').delete().eq('id', folderId);
  }
  MOCK_FOLDERS = MOCK_FOLDERS.filter((f) => f.id !== folderId);
  MOCK_FILES = MOCK_FILES.filter((f) => f.folder_id !== folderId);
}

export async function deleteFile(fileId: string, userRole?: OrgRole): Promise<void> {
  if (userRole === 'CLIENT') {
    throw new Error('Unauthorized: Client accounts cannot delete project files.');
  }
  if (isSupabaseConfigured) {
    await supabase.from('files').delete().eq('id', fileId);
  }
  MOCK_FILES = MOCK_FILES.filter((f) => f.id !== fileId);
}

export async function uploadFileRecord(
  projectId: string,
  folderId: string | null,
  uploadedBy: string | undefined,
  fileObj: File,
  description?: string
): Promise<ProjectFile> {
  const storagePath = `${projectId}/${Date.now()}-${fileObj.name}`;

  if (isSupabaseConfigured) {
    try {
      await supabase.storage.from('project-files').upload(storagePath, fileObj);

      const { data, error } = await supabase
        .from('files')
        .insert([
          {
            project_id: projectId,
            folder_id: folderId || null,
            uploaded_by: uploadedBy || null,
            name: fileObj.name,
            storage_path: storagePath,
            mime_type: fileObj.type || 'application/octet-stream',
            size: fileObj.size,
            description: description?.trim() || null,
          },
        ])
        .select('*, uploader:profiles(*)')
        .single();

      if (!error && data) return data as ProjectFile;
    } catch (e) {
      console.warn('Storage upload error via Supabase:', e);
    }
  }

  const newFile: ProjectFile = {
    id: `file-${Date.now()}`,
    project_id: projectId,
    folder_id: folderId || undefined,
    uploaded_by: uploadedBy,
    name: fileObj.name,
    storage_path: storagePath,
    mime_type: fileObj.type || 'application/octet-stream',
    size: fileObj.size,
    description: description?.trim(),
    created_at: new Date().toISOString(),
  };
  MOCK_FILES.push(newFile);
  return newFile;
}

export async function renameFile(fileId: string, newName: string): Promise<void> {
  if (isSupabaseConfigured) {
    await supabase.from('files').update({ name: newName.trim(), updated_at: new Date().toISOString() }).eq('id', fileId);
  }
  const f = MOCK_FILES.find((item) => item.id === fileId);
  if (f) f.name = newName.trim();
}

export async function renameFolder(folderId: string, newName: string): Promise<void> {
  if (isSupabaseConfigured) {
    await supabase.from('folders').update({ name: newName.trim() }).eq('id', folderId);
  }
  const f = MOCK_FOLDERS.find((item) => item.id === folderId);
  if (f) f.name = newName.trim();
}

export async function moveFile(fileId: string, targetFolderId: string | null): Promise<void> {
  if (isSupabaseConfigured) {
    await supabase
      .from('files')
      .update({ folder_id: targetFolderId, updated_at: new Date().toISOString() })
      .eq('id', fileId);
  }
  const f = MOCK_FILES.find((item) => item.id === fileId);
  if (f) f.folder_id = targetFolderId || undefined;
}

export async function getAllProjectFolders(projectId: string): Promise<Folder[]> {
  if (isSupabaseConfigured) {
    const { data } = await supabase.from('folders').select('*').eq('project_id', projectId);
    if (data) return data as Folder[];
  }
  return MOCK_FOLDERS.filter((f) => f.project_id === projectId);
}

export async function getFolderById(folderId: string): Promise<Folder | null> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase.from('folders').select('*').eq('id', folderId).single();
      if (!error && data) return data as Folder;
    } catch (e) {
      console.warn('Failed to fetch folder by id via Supabase:', e);
    }
  }
  const folder = MOCK_FOLDERS.find((f) => f.id === folderId);
  return folder || null;
}

export async function uploadFile(
  fileObj: File,
  projectId: string,
  folderId?: string | null,
  uploadedBy?: string,
  description?: string
): Promise<ProjectFile> {
  return uploadFileRecord(projectId, folderId || null, uploadedBy, fileObj, description);
}

/**
 * Basecamp 4 File Comments & Discussion
 */
export async function getFileComments(fileId: string): Promise<FileComment[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('file_comments')
        .select('*, profile:profiles(*)')
        .eq('file_id', fileId)
        .order('created_at', { ascending: true });

      if (!error && data) return data as FileComment[];
    } catch (e) {
      console.warn('Failed to fetch file comments via Supabase:', e);
    }
  }

  return MOCK_FILE_COMMENTS.filter((c) => c.file_id === fileId);
}

export async function addFileComment(
  fileId: string,
  userId: string,
  content: string
): Promise<FileComment> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('file_comments')
        .insert([
          {
            file_id: fileId,
            user_id: userId,
            content: content.trim(),
          },
        ])
        .select('*, profile:profiles(*)')
        .single();

      if (!error && data) return data as FileComment;
    } catch (e) {
      console.warn('Failed to add file comment via Supabase:', e);
    }
  }

  const newComment: FileComment = {
    id: `fcomm-${Date.now()}`,
    file_id: fileId,
    user_id: userId,
    content: content.trim(),
    created_at: new Date().toISOString(),
  };
  MOCK_FILE_COMMENTS.push(newComment);
  return newComment;
}

/**
 * Basecamp 4 File Version Replacement & History
 */
export async function getFileVersions(fileId: string): Promise<FileVersion[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('file_versions')
        .select('*, creator:profiles(*)')
        .eq('file_id', fileId)
        .order('version_number', { ascending: false });

      if (!error && data) return data as FileVersion[];
    } catch (e) {
      console.warn('Failed to fetch file versions via Supabase:', e);
    }
  }

  return MOCK_FILE_VERSIONS.filter((v) => v.file_id === fileId).sort(
    (a, b) => b.version_number - a.version_number
  );
}

export async function replaceFileVersion(
  fileId: string,
  newFileObj: File,
  replacedBy?: string
): Promise<{ updatedFile: ProjectFile; archivedVersion: FileVersion }> {
  // 1. Fetch current file
  const currentFile = MOCK_FILES.find((f) => f.id === fileId);
  const existingVersions = await getFileVersions(fileId);
  const nextVersionNum = existingVersions.length + 1;

  if (isSupabaseConfigured) {
    try {
      const { data: dbFile } = await supabase.from('files').select('*').eq('id', fileId).single();
      if (dbFile) {
        // Archive current version
        const { data: archived } = await supabase
          .from('file_versions')
          .insert([
            {
              file_id: fileId,
              version_number: nextVersionNum,
              name: dbFile.name,
              storage_path: dbFile.storage_path,
              mime_type: dbFile.mime_type,
              size: dbFile.size,
              created_by: dbFile.uploaded_by,
            },
          ])
          .select('*')
          .single();

        // Upload new file
        const newStoragePath = `${dbFile.project_id}/${Date.now()}-${newFileObj.name}`;
        await supabase.storage.from('project-files').upload(newStoragePath, newFileObj);

        // Update file record
        const { data: updated } = await supabase
          .from('files')
          .update({
            name: newFileObj.name,
            storage_path: newStoragePath,
            mime_type: newFileObj.type || 'application/octet-stream',
            size: newFileObj.size,
            uploaded_by: replacedBy || dbFile.uploaded_by,
            updated_at: new Date().toISOString(),
          })
          .eq('id', fileId)
          .select('*, uploader:profiles(*)')
          .single();

        if (updated && archived) {
          return { updatedFile: updated as ProjectFile, archivedVersion: archived as FileVersion };
        }
      }
    } catch (e) {
      console.warn('Failed to replace file version via Supabase:', e);
    }
  }

  // Mock implementation
  const archived: FileVersion = {
    id: `fver-${Date.now()}`,
    file_id: fileId,
    version_number: nextVersionNum,
    name: currentFile?.name || 'Previous Version',
    storage_path: currentFile?.storage_path || '',
    mime_type: currentFile?.mime_type,
    size: currentFile?.size || 0,
    uploaded_by: currentFile?.uploaded_by,
    created_at: currentFile?.created_at || new Date().toISOString(),
  };
  MOCK_FILE_VERSIONS.unshift(archived);

  if (currentFile) {
    currentFile.name = newFileObj.name;
    currentFile.size = newFileObj.size;
    currentFile.mime_type = newFileObj.type || 'application/octet-stream';
    currentFile.uploaded_by = replacedBy || currentFile.uploaded_by;
    currentFile.description = `Updated • Version ${nextVersionNum + 1}`;
  }

  return {
    updatedFile: currentFile || ({} as ProjectFile),
    archivedVersion: archived,
  };
}
