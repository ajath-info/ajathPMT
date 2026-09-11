import React from 'react';
import { X, Download, ExternalLink, FileText, Image as ImageIcon, Film, Music, File } from 'lucide-react';

interface FilePreviewModalProps {
  isOpen: boolean;
  onClose: () => void;
  file: {
    name: string;
    url: string;
    size?: number;
    type?: string;
  } | null;
}

export const FilePreviewModal: React.FC<FilePreviewModalProps> = ({
  isOpen,
  onClose,
  file,
}) => {
  if (!isOpen || !file) return null;

  const isImage = file.type?.startsWith('image/') || /\.(png|jpe?g|gif|webp|svg)$/i.test(file.url || file.name);
  const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.url || file.name);
  const isVideo = file.type?.startsWith('video/') || /\.(mp4|webm|ogg)$/i.test(file.url || file.name);

  const formatFileSize = (bytes?: number) => {
    if (!bytes) return '';
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-md animate-fadeIn">
      <div className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-5xl h-[85vh] flex flex-col shadow-2xl overflow-hidden">
        {/* Header */}
        <div className="p-4 px-6 border-b border-slate-800 flex items-center justify-between bg-slate-900/60">
          <div className="flex items-center gap-3 min-w-0">
            <div className="p-2 rounded-lg bg-indigo-500/10 text-indigo-400">
              {isImage ? <ImageIcon className="w-5 h-5" /> : isPdf ? <FileText className="w-5 h-5" /> : <File className="w-5 h-5" />}
            </div>
            <div className="truncate">
              <h3 className="text-base font-semibold text-slate-100 truncate">{file.name}</h3>
              {file.size && <p className="text-xs text-slate-400">{formatFileSize(file.size)}</p>}
            </div>
          </div>

          <div className="flex items-center gap-2">
            <a
              href={file.url}
              download={file.name}
              target="_blank"
              rel="noopener noreferrer"
              className="p-2 text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors flex items-center gap-1.5 text-xs font-medium"
            >
              <Download className="w-4 h-4" />
              <span>Download</span>
            </a>
            <a
              href={file.url}
              target="_blank"
              rel="noopener noreferrer"
              className="p-2 text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors flex items-center gap-1.5 text-xs font-medium"
            >
              <ExternalLink className="w-4 h-4" />
              <span>Open Original</span>
            </a>
            <button
              onClick={onClose}
              className="p-2 text-slate-400 hover:text-slate-200 hover:bg-slate-800 rounded-lg transition-colors ml-2"
            >
              <X className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* Content Body */}
        <div className="flex-1 bg-slate-950/60 flex items-center justify-center p-6 overflow-hidden">
          {isImage ? (
            <img
              src={file.url}
              alt={file.name}
              className="max-w-full max-h-full object-contain rounded-lg shadow-xl"
            />
          ) : isPdf ? (
            <iframe
              src={file.url}
              title={file.name}
              className="w-full h-full rounded-lg border border-slate-800 bg-white"
            />
          ) : isVideo ? (
            <video
              src={file.url}
              controls
              className="max-w-full max-h-full rounded-lg shadow-xl"
            />
          ) : (
            <div className="text-center p-8 max-w-md">
              <File className="w-16 h-16 text-slate-600 mx-auto mb-4" />
              <h4 className="text-lg font-medium text-slate-200 mb-2">No Preview Available</h4>
              <p className="text-sm text-slate-400 mb-6">This file type ({file.type || 'unknown'}) cannot be previewed directly in the browser lightbox.</p>
              <a
                href={file.url}
                download={file.name}
                className="px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm inline-flex items-center gap-2 shadow-lg shadow-indigo-600/20 transition-all"
              >
                <Download className="w-4 h-4" />
                Download File
              </a>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
