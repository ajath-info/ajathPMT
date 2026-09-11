import React, { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import {
  Plus,
  Bookmark,
  MoreHorizontal,
  FolderPlus,
  FileUp,
  FileText,
  FileCode,
  Link as LinkIcon,
  ChevronDown,
  ChevronRight,
  GripVertical,
  Download,
  Eye,
  Trash2,
  X,
  Search,
  UploadCloud,
  Check,
  Share2,
  FolderOpen,
  Edit2,
  Folder as FolderIcon,
} from 'lucide-react';
import {
  getProjectFilesAndFolders,
  getFolderById,
  createFolder,
  uploadFile,
  deleteFile,
  deleteFolder,
  renameFolder,
} from '../../services/fileService';
import { getProjectById } from '../../services/projectService';
import { Folder, ProjectFile, Project } from '../../types';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { useProject } from '../../context/ProjectContext';
import { FileDetailModal } from '../../components/docs/FileDetailModal';

function formatBytes(bytes: number): string {
  if (!bytes || bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

export function ProjectDocsPage() {
  const { projectId, folderId } = useParams<{ projectId: string; folderId?: string }>();
  const { projects } = useProject();
  const { profile, userRole } = useAuth();
  const { addToast } = useToast();
  const navigate = useNavigate();

  const targetProjectId = projectId || 'proj-rmc';
  const currentFolderId = folderId || null;

  const [project, setProject] = useState<Project | null>(null);
  const [currentFolder, setCurrentFolder] = useState<Folder | null>(null);
  const [folders, setFolders] = useState<Folder[]>([]);
  const [files, setFiles] = useState<ProjectFile[]>([]);
  const [allSubFiles, setAllSubFiles] = useState<Record<string, ProjectFile[]>>({});
  const [loading, setLoading] = useState(true);

  // Inline folder creation & renaming states
  const [isInlineCreatingFolder, setIsInlineCreatingFolder] = useState(false);
  const [inlineFolderName, setInlineFolderName] = useState('');
  const [renamingFolderId, setRenamingFolderId] = useState<string | null>(null);
  const [renamingFolderName, setRenamingFolderName] = useState('');
  const [openFolderMenuId, setOpenFolderMenuId] = useState<string | null>(null);

  // Filters & State
  const [searchQuery, setSearchQuery] = useState('');
  const [fileTypeFilter, setFileTypeFilter] = useState<'all' | 'pdf' | 'doc' | 'sheet' | 'image' | 'folder'>('all');
  const [sortBy, setSortBy] = useState<'manual' | 'name_asc' | 'name_desc' | 'newest' | 'oldest'>('manual');
  const [keepFoldersOpen, setKeepFoldersOpen] = useState(false);
  const [expandedFolderIds, setExpandedFolderIds] = useState<Record<string, boolean>>({});

  // Menus & Modals
  const [isNewMenuOpen, setIsNewMenuOpen] = useState(false);
  const [isFileTypeMenuOpen, setIsFileTypeMenuOpen] = useState(false);
  const [isSortMenuOpen, setIsSortMenuOpen] = useState(false);
  const [isMoreMenuOpen, setIsMoreMenuOpen] = useState(false);
  const [isCreateFolderModalOpen, setIsCreateFolderModalOpen] = useState(false);
  const [isCreateDocModalOpen, setIsCreateDocModalOpen] = useState(false);
  const [newFolderName, setNewFolderName] = useState('');
  const [newDocTitle, setNewDocTitle] = useState('');
  const [newDocContent, setNewDocContent] = useState('');
  const [selectedPreviewFile, setSelectedPreviewFile] = useState<ProjectFile | null>(null);

  // Bookmarking
  const bookmarkKey = `basecamp_bookmark_docs_${targetProjectId}`;
  const [isBookmarked, setIsBookmarked] = useState<boolean>(() => {
    try {
      return localStorage.getItem(bookmarkKey) === 'true';
    } catch {
      return false;
    }
  });

  const fileInputRef = useRef<HTMLInputElement>(null);
  const newMenuRef = useRef<HTMLDivElement>(null);
  const fileTypeMenuRef = useRef<HTMLDivElement>(null);
  const sortMenuRef = useRef<HTMLDivElement>(null);
  const moreMenuRef = useRef<HTMLDivElement>(null);

  const loadData = async () => {
    setLoading(true);
    try {
      const [projData, data, folderObj] = await Promise.all([
        getProjectById(targetProjectId),
        getProjectFilesAndFolders(targetProjectId, currentFolderId),
        currentFolderId ? getFolderById(currentFolderId) : Promise.resolve(null),
      ]);
      setProject(projData);
      setFolders(data.folders);
      setFiles(data.files);
      setCurrentFolder(folderObj);

      // Load sub-files for each folder to calculate accurate item counts and support "Keep folders open"
      const subFilesMap: Record<string, ProjectFile[]> = {};
      await Promise.all(
        data.folders.map(async (f: Folder) => {
          const subData = await getProjectFilesAndFolders(targetProjectId, f.id);
          subFilesMap[f.id] = subData.files;
        })
      );
      setAllSubFiles(subFilesMap);
    } catch (err) {
      console.error('Failed to load docs & files', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [targetProjectId, currentFolderId]);

  // Click outside menus
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (newMenuRef.current && !newMenuRef.current.contains(e.target as Node)) {
        setIsNewMenuOpen(false);
      }
      if (fileTypeMenuRef.current && !fileTypeMenuRef.current.contains(e.target as Node)) {
        setIsFileTypeMenuOpen(false);
      }
      if (sortMenuRef.current && !sortMenuRef.current.contains(e.target as Node)) {
        setIsSortMenuOpen(false);
      }
      if (moreMenuRef.current && !moreMenuRef.current.contains(e.target as Node)) {
        setIsMoreMenuOpen(false);
      }
      setOpenFolderMenuId(null);
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleBookmarkToggle = () => {
    const next = !isBookmarked;
    setIsBookmarked(next);
    try {
      localStorage.setItem(bookmarkKey, String(next));
      addToast(next ? 'Bookmarked Docs & Files' : 'Bookmark removed', 'info');
    } catch {
      // ignore
    }
  };

  const handleToggleKeepFoldersOpen = () => {
    const next = !keepFoldersOpen;
    setKeepFoldersOpen(next);
    if (next) {
      const allOpen: Record<string, boolean> = {};
      folders.forEach((f) => {
        allOpen[f.id] = true;
      });
      setExpandedFolderIds(allOpen);
      addToast('Folders expanded', 'info');
    } else {
      setExpandedFolderIds({});
    }
  };

  const handleToggleFolderExpand = (fId: string, e?: React.MouseEvent) => {
    if (e) e.stopPropagation();
    setExpandedFolderIds((prev) => ({ ...prev, [fId]: !prev[fId] }));
  };

  const handleFolderClick = (fId: string) => {
    navigate(`/projects/${targetProjectId}/docs/${fId}`);
  };

  const handleCreateFolder = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newFolderName.trim()) return;
    try {
      await createFolder(targetProjectId, newFolderName.trim(), currentFolderId, profile?.id);
      setNewFolderName('');
      setIsCreateFolderModalOpen(false);
      loadData();
      addToast('Folder created', 'success');
    } catch (err) {
      console.error(err);
      addToast('Failed to create folder', 'error');
    }
  };

  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const uploadedFiles = Array.from(e.target.files || []);
    if (!uploadedFiles.length) return;

    for (const file of uploadedFiles) {
      await uploadFile(file, targetProjectId, currentFolderId || undefined, profile?.id);
    }
    e.target.value = '';
    loadData();
    addToast(`Uploaded ${uploadedFiles.length} file(s)`, 'success');
  };

  const handleCreateDoc = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newDocTitle.trim()) return;
    // Creates a document representation as a file or doc
    const mockFile = new File([newDocContent || ''], `${newDocTitle.trim()}.docx`, {
      type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    });
    await uploadFile(mockFile, targetProjectId, currentFolderId || undefined, profile?.id);
    setNewDocTitle('');
    setNewDocContent('');
    setIsCreateDocModalOpen(false);
    loadData();
    addToast('Document created', 'success');
  };

  const handleDownloadFile = (file: ProjectFile, e?: React.MouseEvent) => {
    if (e) e.stopPropagation();
    const sampleContent = `Document: ${file.name}\nProject: ${project?.name || 'Ride My Cars (Edward)'}\nCreated: ${file.created_at}\nSize: ${formatBytes(file.size)}`;
    const blob = new Blob([sampleContent], { type: file.mime_type || 'application/octet-stream' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = file.name;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    addToast(`Downloading ${file.name}...`, 'success');
  };

  const handleInlineCreateFolder = async () => {
    if (!inlineFolderName.trim()) return;
    try {
      await createFolder(targetProjectId, inlineFolderName.trim(), currentFolderId, profile?.id);
      setInlineFolderName('');
      setIsInlineCreatingFolder(false);
      loadData();
      addToast('Folder created', 'success');
    } catch (err) {
      console.error(err);
      addToast('Failed to create folder', 'error');
    }
  };

  const handleStartRenameFolder = (folder: Folder, e?: React.MouseEvent) => {
    if (e) e.stopPropagation();
    setRenamingFolderId(folder.id);
    setRenamingFolderName(folder.name);
    setOpenFolderMenuId(null);
  };

  const handleSaveRenameFolder = async (folderId: string, e?: React.FormEvent) => {
    if (e) e.preventDefault();
    if (!renamingFolderName.trim()) return;
    try {
      await renameFolder(folderId, renamingFolderName.trim());
      setRenamingFolderId(null);
      loadData();
      addToast('Folder renamed', 'success');
    } catch (err) {
      console.error(err);
      addToast('Failed to rename folder', 'error');
    }
  };

  const handleDeleteFolderItem = async (folderId: string, folderName: string, e?: React.MouseEvent) => {
    if (e) e.stopPropagation();
    setOpenFolderMenuId(null);
    if (userRole === 'CLIENT') {
      addToast('Client accounts cannot delete folders.', 'error');
      return;
    }
    if (window.confirm(`Are you sure you want to delete folder "${folderName}" and all items inside it?`)) {
      try {
        await deleteFolder(folderId, userRole);
        loadData();
        addToast(`Folder "${folderName}" deleted`, 'info');
      } catch (err) {
        console.error(err);
        addToast('Failed to delete folder', 'error');
      }
    }
  };

  // Filter items
  const filteredFiles = files.filter((f) => {
    const matchesSearch =
      !searchQuery ||
      f.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      (f.description && f.description.toLowerCase().includes(searchQuery.toLowerCase()));

    if (!matchesSearch) return false;

    if (fileTypeFilter === 'pdf') return f.name.toLowerCase().endsWith('.pdf');
    if (fileTypeFilter === 'doc')
      return f.name.toLowerCase().endsWith('.docx') || f.name.toLowerCase().endsWith('.doc');
    if (fileTypeFilter === 'sheet')
      return f.name.toLowerCase().endsWith('.xlsx') || f.name.toLowerCase().endsWith('.csv');
    if (fileTypeFilter === 'image') return f.mime_type?.startsWith('image/');
    if (fileTypeFilter === 'folder') return false;

    return true;
  });

  const filteredFolders = folders.filter((f) => {
    if (fileTypeFilter !== 'all' && fileTypeFilter !== 'folder') return false;
    return !searchQuery || f.name.toLowerCase().includes(searchQuery.toLowerCase());
  });

  // Sorting
  const sortedFiles = [...filteredFiles].sort((a, b) => {
    if (sortBy === 'name_asc') return a.name.localeCompare(b.name);
    if (sortBy === 'name_desc') return b.name.localeCompare(a.name);
    if (sortBy === 'newest')
      return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
    if (sortBy === 'oldest')
      return new Date(a.created_at).getTime() - new Date(b.created_at).getTime();
    return 0;
  });

  const sortedFolders = [...filteredFolders].sort((a, b) => {
    if (sortBy === 'name_asc') return a.name.localeCompare(b.name);
    if (sortBy === 'name_desc') return b.name.localeCompare(a.name);
    return 0;
  });

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 pb-16">
      <input
        type="file"
        ref={fileInputRef}
        onChange={handleFileUpload}
        multiple
        className="hidden"
      />

      {/* Main Modern Document Card Container */}
      <div className="w-full bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm relative space-y-6">
        {/* Card Top Utility Bar */}
        <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 text-sm">
          {/* Left: Breadcrumbs */}
          <div className="flex items-center gap-2 text-sm font-sans flex-wrap">
            <Link
              to={`/projects/${targetProjectId}`}
              className="font-extrabold text-slate-900 dark:text-slate-100 hover:text-blue-600 dark:hover:text-blue-400 hover:underline transition-colors"
            >
              {project?.name || 'Ride My Cars (Edward)'}
            </Link>
            {currentFolder && (
              <>
                <span className="text-slate-400 font-bold">‹</span>
                <Link
                  to={`/projects/${targetProjectId}/docs`}
                  className="text-slate-500 hover:text-slate-900 dark:hover:text-slate-200 font-medium hover:underline transition-colors"
                >
                  Docs & Files
                </Link>
                <span className="text-slate-400 font-bold">‹</span>
                <span className="text-slate-900 dark:text-slate-100 font-extrabold">{currentFolder.name}</span>
              </>
            )}
          </div>

          {/* Right: Bookmark & More Menu */}
          <div className="flex items-center gap-2 sm:gap-3">
            <button
              type="button"
              onClick={handleBookmarkToggle}
              className={`inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-xl border transition-all cursor-pointer ${
                isBookmarked
                  ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-800 shadow-xs'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 border-transparent hover:border-slate-200 dark:hover:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'
              }`}
            >
              <Bookmark className={`w-3.5 h-3.5 ${isBookmarked ? 'fill-amber-600 text-amber-600' : ''}`} />
              <span>{isBookmarked ? 'Bookmarked' : 'Bookmark'}</span>
            </button>

            <div className="relative" ref={moreMenuRef}>
              <button
                type="button"
                onClick={() => setIsMoreMenuOpen(!isMoreMenuOpen)}
                className="p-1.5 rounded-xl text-slate-500 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
              >
                <MoreHorizontal className="w-4 h-4" />
              </button>

              {isMoreMenuOpen && (
                <div className="absolute right-0 mt-1 w-52 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 py-1.5 z-40 text-xs font-medium animate-in fade-in">
                  <button
                    onClick={() => {
                      setIsMoreMenuOpen(false);
                      fileInputRef.current?.click();
                    }}
                    className="w-full px-3.5 py-2 text-left text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2"
                  >
                    <FileUp className="w-3.5 h-3.5 text-slate-400" />
                    <span>Upload files</span>
                  </button>
                  <button
                    onClick={() => {
                      setIsMoreMenuOpen(false);
                      setIsCreateFolderModalOpen(true);
                    }}
                    className="w-full px-3.5 py-2 text-left text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2"
                  >
                    <FolderPlus className="w-3.5 h-3.5 text-slate-400" />
                    <span>New folder</span>
                  </button>
                  <button
                    onClick={() => {
                      setIsMoreMenuOpen(false);
                      navigator.clipboard.writeText(window.location.href);
                      addToast('Link copied to clipboard', 'success');
                    }}
                    className="w-full px-3.5 py-2 text-left text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2"
                  >
                    <Share2 className="w-3.5 h-3.5 text-slate-400" />
                    <span>Copy link to clipboard</span>
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Docs & Files Heading */}
        <div className="pt-2">
          <h1 className="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            {currentFolder ? currentFolder.name : 'Docs & Files'}
          </h1>
          {currentFolder && (
            <p className="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">
              Inside folder • {files.length} {files.length === 1 ? 'file' : 'files'}
              {folders.length > 0 ? ` • ${folders.length} subfolder(s)` : ''}
            </p>
          )}
        </div>

        {/* Action Toolbar */}
        <div className="flex items-center gap-2.5 flex-wrap">
          {/* + New... Button */}
          <div className="relative" ref={newMenuRef}>
            <button
              type="button"
              onClick={() => setIsNewMenuOpen(!isNewMenuOpen)}
              className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer select-none"
            >
              <Plus className="w-3.5 h-3.5 stroke-[3]" />
              <span>New...</span>
            </button>

              {isNewMenuOpen && (
                <div className="absolute left-0 mt-1 w-64 bg-white rounded-2xl shadow-2xl border border-slate-200 py-2 z-40 text-xs font-medium animate-in fade-in zoom-in-95">
                  <button
                    onClick={() => {
                      setIsNewMenuOpen(false);
                      setIsCreateDocModalOpen(true);
                    }}
                    className="w-full px-4 py-2.5 text-left text-slate-700 hover:bg-slate-50 flex items-center gap-3"
                  >
                    <FileText className="w-4 h-4 text-amber-600" />
                    <div>
                      <p className="font-bold text-slate-900">Start a document</p>
                      <p className="text-[11px] text-slate-400">Write specs, notes, or guidelines</p>
                    </div>
                  </button>

                  <button
                    onClick={() => {
                      setIsNewMenuOpen(false);
                      fileInputRef.current?.click();
                    }}
                    className="w-full px-4 py-2.5 text-left text-slate-700 hover:bg-slate-50 flex items-center gap-3"
                  >
                    <FileUp className="w-4 h-4 text-blue-600" />
                    <div>
                      <p className="font-bold text-slate-900">Upload files</p>
                      <p className="text-[11px] text-slate-400">PDFs, images, docx, spreadsheets</p>
                    </div>
                  </button>

                  <button
                    onClick={() => {
                      setIsNewMenuOpen(false);
                      setIsInlineCreatingFolder(true);
                    }}
                    className="w-full px-4 py-2.5 text-left text-slate-700 hover:bg-slate-50 flex items-center gap-3"
                  >
                    <FolderPlus className="w-4 h-4 text-emerald-600" />
                    <div>
                      <p className="font-bold text-slate-900">Create a folder</p>
                      <p className="text-[11px] text-slate-400">Organize files into categories</p>
                    </div>
                  </button>

                  <div className="border-t border-slate-100 my-1" />

                  <button
                    onClick={() => {
                      setIsNewMenuOpen(false);
                      const url = prompt('Enter Google Doc or external link URL:');
                      if (url) addToast('External link attached', 'success');
                    }}
                    className="w-full px-4 py-2.5 text-left text-slate-700 hover:bg-slate-50 flex items-center gap-3"
                  >
                    <LinkIcon className="w-4 h-4 text-slate-500" />
                    <div>
                      <p className="font-bold text-slate-900">Link to external file</p>
                      <p className="text-[11px] text-slate-400">Google Drive, Figma, Notion</p>
                    </div>
                  </button>
                </div>
              )}
            </div>

            {/* Quick "Add a folder" button */}
            <button
              type="button"
              onClick={() => setIsInlineCreatingFolder(true)}
              className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition-all cursor-pointer"
            >
              <FolderPlus className="w-4 h-4 text-emerald-600" />
              <span>Add a folder</span>
            </button>

            {/* All Files Pill */}
            <button
              type="button"
              onClick={() => setFileTypeFilter('all')}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all cursor-pointer ${
                fileTypeFilter === 'all'
                  ? 'bg-slate-100 border-slate-300 text-slate-900'
                  : 'border-transparent text-slate-600 hover:bg-slate-50'
              }`}
            >
              All files
            </button>

            {/* File type dropdown */}
            <div className="relative" ref={fileTypeMenuRef}>
              <button
                type="button"
                onClick={() => setIsFileTypeMenuOpen(!isFileTypeMenuOpen)}
                className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-all cursor-pointer"
              >
                <span>
                  {fileTypeFilter === 'all'
                    ? 'File type'
                    : fileTypeFilter === 'pdf'
                    ? 'PDFs'
                    : fileTypeFilter === 'doc'
                    ? 'Documents'
                    : fileTypeFilter === 'sheet'
                    ? 'Spreadsheets'
                    : fileTypeFilter === 'folder'
                    ? 'Folders'
                    : 'File type'}
                </span>
                <ChevronDown className="w-3.5 h-3.5 text-slate-400" />
              </button>

              {isFileTypeMenuOpen && (
                <div className="absolute left-0 mt-1 w-44 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-40 text-xs font-medium animate-in fade-in">
                  <button
                    onClick={() => {
                      setFileTypeFilter('all');
                      setIsFileTypeMenuOpen(false);
                    }}
                    className="w-full px-3 py-1.5 text-left text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                  >
                    <span>All file types</span>
                    {fileTypeFilter === 'all' && <Check className="w-3.5 h-3.5 text-blue-600" />}
                  </button>
                  <button
                    onClick={() => {
                      setFileTypeFilter('pdf');
                      setIsFileTypeMenuOpen(false);
                    }}
                    className="w-full px-3 py-1.5 text-left text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                  >
                    <span>PDFs (.pdf)</span>
                    {fileTypeFilter === 'pdf' && <Check className="w-3.5 h-3.5 text-blue-600" />}
                  </button>
                  <button
                    onClick={() => {
                      setFileTypeFilter('doc');
                      setIsFileTypeMenuOpen(false);
                    }}
                    className="w-full px-3 py-1.5 text-left text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                  >
                    <span>Word / Docs (.docx)</span>
                    {fileTypeFilter === 'doc' && <Check className="w-3.5 h-3.5 text-blue-600" />}
                  </button>
                  <button
                    onClick={() => {
                      setFileTypeFilter('sheet');
                      setIsFileTypeMenuOpen(false);
                    }}
                    className="w-full px-3 py-1.5 text-left text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                  >
                    <span>Spreadsheets (.xlsx)</span>
                    {fileTypeFilter === 'sheet' && <Check className="w-3.5 h-3.5 text-blue-600" />}
                  </button>
                  <button
                    onClick={() => {
                      setFileTypeFilter('folder');
                      setIsFileTypeMenuOpen(false);
                    }}
                    className="w-full px-3 py-1.5 text-left text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                  >
                    <span>Folders only</span>
                    {fileTypeFilter === 'folder' && <Check className="w-3.5 h-3.5 text-blue-600" />}
                  </button>
                </div>
              )}
            </div>

            {/* Sort manually dropdown */}
            <div className="relative" ref={sortMenuRef}>
              <button
                type="button"
                onClick={() => setIsSortMenuOpen(!isSortMenuOpen)}
                className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-all cursor-pointer"
              >
                <span>
                  {sortBy === 'manual'
                    ? 'Sort manually'
                    : sortBy === 'name_asc'
                    ? 'Name A-Z'
                    : sortBy === 'name_desc'
                    ? 'Name Z-A'
                    : sortBy === 'newest'
                    ? 'Newest first'
                    : 'Oldest first'}
                </span>
                <ChevronDown className="w-3.5 h-3.5 text-slate-400" />
              </button>

              {isSortMenuOpen && (
                <div className="absolute left-0 mt-1 w-44 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-40 text-xs font-medium animate-in fade-in">
                  <button
                    onClick={() => {
                      setSortBy('manual');
                      setIsSortMenuOpen(false);
                    }}
                    className="w-full px-3 py-1.5 text-left text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                  >
                    <span>Sort manually</span>
                    {sortBy === 'manual' && <Check className="w-3.5 h-3.5 text-blue-600" />}
                  </button>
                  <button
                    onClick={() => {
                      setSortBy('name_asc');
                      setIsSortMenuOpen(false);
                    }}
                    className="w-full px-3 py-1.5 text-left text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                  >
                    <span>Name A-Z</span>
                    {sortBy === 'name_asc' && <Check className="w-3.5 h-3.5 text-blue-600" />}
                  </button>
                  <button
                    onClick={() => {
                      setSortBy('newest');
                      setIsSortMenuOpen(false);
                    }}
                    className="w-full px-3 py-1.5 text-left text-slate-700 hover:bg-slate-50 flex items-center justify-between"
                  >
                    <span>Newest first</span>
                    {sortBy === 'newest' && <Check className="w-3.5 h-3.5 text-blue-600" />}
                  </button>
                </div>
              )}
            </div>

            {/* Filter... Search Input */}
            <div className="relative flex-1 min-w-[140px] max-w-xs">
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Filter..."
                className="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 text-slate-900 placeholder:text-slate-400"
              />
              {searchQuery && (
                <button
                  type="button"
                  onClick={() => setSearchQuery('')}
                  className="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                >
                  <X className="w-3.5 h-3.5" />
                </button>
              )}
            </div>

            {/* [Off/On] Keep folders open toggle button */}
            <button
              type="button"
              onClick={handleToggleKeepFoldersOpen}
              className="inline-flex items-center gap-2 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 rounded-lg transition-colors cursor-pointer select-none ml-auto"
            >
              <span
                className={`px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider transition-colors ${
                  keepFoldersOpen
                    ? 'bg-blue-600 text-white shadow-xs'
                    : 'bg-slate-500 text-white'
                }`}
              >
                {keepFoldersOpen ? 'On' : 'Off'}
              </span>
              <span>Keep folders open</span>
            </button>
          </div>

          {/* Basecamp Inline Folder Creator */}
          {isInlineCreatingFolder && (
            <div className="p-4 bg-amber-50/80 border border-amber-200/90 rounded-2xl mb-5 flex flex-col sm:flex-row items-start sm:items-center gap-3 animate-in fade-in shadow-xs">
              <div className="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <FolderIcon className="w-5 h-5 fill-amber-500 text-amber-500" />
              </div>
              <div className="flex-1 w-full">
                <input
                  type="text"
                  autoFocus
                  placeholder="Name this folder..."
                  value={inlineFolderName}
                  onChange={(e) => setInlineFolderName(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') handleInlineCreateFolder();
                    if (e.key === 'Escape') {
                      setIsInlineCreatingFolder(false);
                      setInlineFolderName('');
                    }
                  }}
                  className="w-full px-3.5 py-2 text-sm bg-white border border-amber-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/30 text-slate-900 placeholder:text-slate-400 font-medium"
                />
              </div>
              <div className="flex items-center gap-2 shrink-0">
                <button
                  type="button"
                  onClick={handleInlineCreateFolder}
                  disabled={!inlineFolderName.trim()}
                  className="px-4 py-2 bg-[#0c66e4] hover:bg-[#0055cc] text-white font-bold text-xs rounded-xl disabled:opacity-50 cursor-pointer shadow-xs transition-all"
                >
                  Create this folder
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setIsInlineCreatingFolder(false);
                    setInlineFolderName('');
                  }}
                  className="px-3 py-2 text-slate-600 hover:text-slate-900 text-xs font-semibold rounded-xl hover:bg-white/80 cursor-pointer transition-colors"
                >
                  Cancel
                </button>
              </div>
            </div>
          )}

          {/* List of Files and Folders */}
          <div className="divide-y divide-slate-100 pt-2">
            {/* Top Files: e.g. WEB PROPOSAL .pdf */}
            {sortedFiles.map((file) => (
              <div
                key={file.id}
                onClick={() => setSelectedPreviewFile(file)}
                className="py-4 flex items-start gap-4 hover:bg-slate-50/70 transition-colors cursor-pointer rounded-xl px-2 sm:px-3 group"
              >
                {/* File Thumbnail Preview */}
                <div className="w-12 h-14 bg-white border border-slate-200 rounded-md shadow-2xs relative flex flex-col justify-between p-1 shrink-0 group-hover:border-slate-300 transition-colors">
                  <div className="space-y-1 pt-1 px-1">
                    <div className="h-0.5 bg-slate-300 rounded-full w-full" />
                    <div className="h-0.5 bg-slate-200 rounded-full w-3/4" />
                    <div className="h-0.5 bg-slate-200 rounded-full w-5/6" />
                    <div className="h-0.5 bg-slate-200 rounded-full w-2/3" />
                  </div>
                  <div className="text-center">
                    <span className="text-[9px] font-bold text-slate-500 uppercase tracking-tighter">
                      {file.name.split('.').pop() || 'PDF'}
                    </span>
                  </div>
                </div>

                {/* File Title & Description */}
                <div className="flex-1 min-w-0 pt-1">
                  <h3 className="font-extrabold text-base text-slate-900 group-hover:text-blue-600 transition-colors truncate">
                    {file.name}
                  </h3>
                  <p className="text-xs text-slate-500 mt-0.5">
                    {file.description ||
                      `${file.uploader?.full_name || 'Nirdesh Verma'} • Aug 12, 2025 • ${formatBytes(
                        file.size
                      )}`}
                  </p>
                </div>

                {/* Right Download Button */}
                <div className="shrink-0 pt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button
                    type="button"
                    onClick={(e) => handleDownloadFile(file, e)}
                    className="p-1.5 rounded-lg hover:bg-slate-200 text-slate-600 transition-colors cursor-pointer"
                    title={`Download ${file.name}`}
                  >
                    <Download className="w-4 h-4" />
                  </button>
                </div>
              </div>
            ))}

            {/* Folders List */}
            {sortedFolders.map((folder) => {
              const subItems = allSubFiles[folder.id] || [];
              const itemCount = subItems.length;
              const isExpanded = Boolean(expandedFolderIds[folder.id] || keepFoldersOpen);

              return (
                <div key={folder.id} className="py-4 px-2 sm:px-3">
                  <div
                    onClick={() => handleFolderClick(folder.id)}
                    className="flex items-center gap-3 cursor-pointer group"
                  >
                    {/* Drag Handle ⋮ */}
                    <div className="text-slate-300 group-hover:text-slate-500 transition-colors cursor-grab p-1">
                      <GripVertical className="w-4 h-4" />
                    </div>

                    {/* Folder Icon: Vibrant Emerald Gradient */}
                    <div className="w-11 h-11 bg-gradient-to-br from-emerald-500/15 to-teal-500/20 dark:from-emerald-500/25 dark:to-teal-500/30 border border-emerald-500/30 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 shadow-sm shrink-0 group-hover:scale-105 transition-all">
                      <FolderIcon className="w-5 h-5 fill-current" />
                    </div>

                    {/* Folder Info or Inline Rename Form */}
                    {renamingFolderId === folder.id ? (
                      <form
                        onSubmit={(e) => handleSaveRenameFolder(folder.id, e)}
                        onClick={(e) => e.stopPropagation()}
                        className="flex items-center gap-2 flex-1 min-w-0"
                      >
                        <input
                          type="text"
                          autoFocus
                          value={renamingFolderName}
                          onChange={(e) => setRenamingFolderName(e.target.value)}
                          className="px-3 py-1 text-sm rounded-lg border border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-500 font-bold text-slate-900 bg-white"
                        />
                        <button
                          type="submit"
                          className="px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer shadow-xs"
                        >
                          Save
                        </button>
                        <button
                          type="button"
                          onClick={() => setRenamingFolderId(null)}
                          className="px-2.5 py-1 text-slate-600 text-xs font-medium hover:bg-slate-100 rounded-lg cursor-pointer"
                        >
                          Cancel
                        </button>
                      </form>
                    ) : (
                      <div className="flex-1 min-w-0">
                        <h3 className="font-extrabold text-base text-slate-900 group-hover:text-blue-600 transition-colors truncate">
                          {folder.name}
                        </h3>
                        <p className="text-xs text-slate-500 mt-0.5">
                          {itemCount} {itemCount === 1 ? 'item' : 'items'}
                        </p>
                      </div>
                    )}

                    {/* Expand/Collapse Chevron Button */}
                    <button
                      type="button"
                      onClick={(e) => handleToggleFolderExpand(folder.id, e)}
                      className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                      title={isExpanded ? 'Collapse folder' : 'Expand folder'}
                    >
                      {isExpanded ? (
                        <ChevronDown className="w-4 h-4" />
                      ) : (
                        <ChevronRight className="w-4 h-4" />
                      )}
                    </button>

                    {/* 3-Dots Folder Options Menu */}
                    <div className="relative" onClick={(e) => e.stopPropagation()}>
                      <button
                        type="button"
                        onClick={() => setOpenFolderMenuId(openFolderMenuId === folder.id ? null : folder.id)}
                        className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                        title="Folder options"
                      >
                        <MoreHorizontal className="w-4 h-4" />
                      </button>
                      {openFolderMenuId === folder.id && (
                        <div className="absolute right-0 mt-1 w-44 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-40 text-xs font-medium animate-in fade-in">
                          <button
                            type="button"
                            onClick={() => {
                              setOpenFolderMenuId(null);
                              handleFolderClick(folder.id);
                            }}
                            className="w-full px-3 py-2 text-left text-slate-700 hover:bg-slate-50 flex items-center gap-2 cursor-pointer"
                          >
                            <FolderOpen className="w-3.5 h-3.5 text-slate-500" />
                            <span>Open folder</span>
                          </button>
                          <button
                            type="button"
                            onClick={() => handleStartRenameFolder(folder)}
                            className="w-full px-3 py-2 text-left text-slate-700 hover:bg-slate-50 flex items-center gap-2 cursor-pointer"
                          >
                            <Edit2 className="w-3.5 h-3.5 text-slate-500" />
                            <span>Rename folder</span>
                          </button>
                          {userRole !== 'CLIENT' && (
                            <button
                              type="button"
                              onClick={(e) => handleDeleteFolderItem(folder.id, folder.name, e)}
                              className="w-full px-3 py-2 text-left text-rose-600 hover:bg-rose-50 flex items-center gap-2 cursor-pointer"
                            >
                              <Trash2 className="w-3.5 h-3.5 text-rose-500" />
                              <span>Delete folder</span>
                            </button>
                          )}
                        </div>
                      )}
                    </div>
                  </div>

                  {/* Inline Expanded Folder Contents */}
                  {isExpanded && (
                    <div className="mt-3 pl-12 sm:pl-16 pr-2 space-y-2 border-l-2 border-slate-100 ml-4 animate-in fade-in">
                      {subItems.length === 0 ? (
                        <p className="text-xs text-slate-400 italic py-2">This folder is empty.</p>
                      ) : (
                        subItems.map((subFile) => (
                          <div
                            key={subFile.id}
                            onClick={() => setSelectedPreviewFile(subFile)}
                            className="flex items-center justify-between py-2 px-3 rounded-xl hover:bg-slate-50 text-xs transition-colors cursor-pointer group/sub"
                          >
                            <div className="flex items-center gap-2.5 min-w-0">
                              <FileText className="w-4 h-4 text-blue-500 shrink-0" />
                              <span className="font-bold text-slate-800 truncate group-hover/sub:text-blue-600">
                                {subFile.name}
                              </span>
                              <span className="text-slate-400 text-[11px] hidden sm:inline">
                                • {subFile.description || formatBytes(subFile.size)}
                              </span>
                            </div>
                            <button
                              type="button"
                              onClick={(e) => handleDownloadFile(subFile, e)}
                              className="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-200 transition-colors"
                              title={`Download ${subFile.name}`}
                            >
                              <Download className="w-3.5 h-3.5" />
                            </button>
                          </div>
                        ))
                      )}
                    </div>
                  )}
                </div>
              );
            })}

            {sortedFiles.length === 0 && sortedFolders.length === 0 && (
              currentFolderId ? (
                <div className="py-16 text-center text-slate-500 space-y-3">
                  <div className="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto border border-amber-200 shadow-2xs">
                    <FolderOpen className="w-6 h-6" />
                  </div>
                  <p className="font-extrabold text-base text-slate-800">This folder is empty</p>
                  <p className="text-xs text-slate-500 max-w-sm mx-auto">
                    Upload files or create subfolders inside &ldquo;{currentFolder?.name || 'this folder'}&rdquo; to organize your work.
                  </p>
                  <div className="pt-2 flex items-center justify-center gap-2.5">
                    <button
                      type="button"
                      onClick={() => fileInputRef.current?.click()}
                      className="px-4 py-2 bg-[#0c66e4] hover:bg-[#0055cc] text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer flex items-center gap-1.5"
                    >
                      <FileUp className="w-3.5 h-3.5" />
                      <span>Upload files to this folder</span>
                    </button>
                    <button
                      type="button"
                      onClick={() => setIsInlineCreatingFolder(true)}
                      className="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 cursor-pointer flex items-center gap-1.5"
                    >
                      <FolderPlus className="w-3.5 h-3.5 text-emerald-600" />
                      <span>New subfolder</span>
                    </button>
                  </div>
                </div>
              ) : (
                <div className="py-16 text-center text-slate-400 text-xs space-y-2">
                  <FileText className="w-8 h-8 mx-auto text-slate-300" />
                  <p className="font-bold text-slate-700">No documents or files match your filter</p>
                  <p>Click + New... or &ldquo;Add a folder&rdquo; to organize your project files.</p>
                </div>
              )
            )}
          </div>
        </div>

      {/* File Detail, Comments & Version Replacement Modal (Basecamp 4 Parity) */}
      <FileDetailModal
        isOpen={Boolean(selectedPreviewFile)}
        onClose={() => setSelectedPreviewFile(null)}
        file={selectedPreviewFile}
        onFileUpdated={loadData}
        onFileDeleted={loadData}
      />

      {/* Create Folder Modal */}
      {isCreateFolderModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <form
            onSubmit={handleCreateFolder}
            className="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-sm w-full p-6 space-y-4"
          >
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <FolderPlus className="w-5 h-5 text-emerald-600" />
                <h3 className="font-extrabold text-slate-900 text-base">New Folder</h3>
              </div>
              <button
                type="button"
                onClick={() => setIsCreateFolderModalOpen(false)}
                className="p-1 text-slate-400 hover:text-slate-700"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-700 mb-1">Folder Name</label>
              <input
                type="text"
                autoFocus
                value={newFolderName}
                onChange={(e) => setNewFolderName(e.target.value)}
                placeholder="e.g. Design Assets, Invoices"
                className="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:border-blue-500"
              />
            </div>

            <div className="flex items-center justify-end gap-2 pt-2">
              <button
                type="button"
                onClick={() => setIsCreateFolderModalOpen(false)}
                className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
              >
                Cancel
              </button>
              <button
                type="submit"
                disabled={!newFolderName.trim()}
                className="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 rounded-xl"
              >
                Create Folder
              </button>
            </div>
          </form>
        </div>
      )}

      {/* Start Document Modal */}
      {isCreateDocModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in">
          <form
            onSubmit={handleCreateDoc}
            className="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-4"
          >
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <FileText className="w-5 h-5 text-amber-600" />
                <h3 className="font-extrabold text-slate-900 text-base">Start a Document</h3>
              </div>
              <button
                type="button"
                onClick={() => setIsCreateDocModalOpen(false)}
                className="p-1 text-slate-400 hover:text-slate-700"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <div className="space-y-3">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Document Title</label>
                <input
                  type="text"
                  autoFocus
                  value={newDocTitle}
                  onChange={(e) => setNewDocTitle(e.target.value)}
                  placeholder="e.g. Architecture Overview"
                  className="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:border-blue-500"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Content Notes</label>
                <textarea
                  rows={5}
                  value={newDocContent}
                  onChange={(e) => setNewDocContent(e.target.value)}
                  placeholder="Write document content..."
                  className="w-full p-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-blue-500"
                />
              </div>
            </div>

            <div className="flex items-center justify-end gap-2 pt-2">
              <button
                type="button"
                onClick={() => setIsCreateDocModalOpen(false)}
                className="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
              >
                Cancel
              </button>
              <button
                type="submit"
                disabled={!newDocTitle.trim()}
                className="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 rounded-xl"
              >
                Save Document
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
