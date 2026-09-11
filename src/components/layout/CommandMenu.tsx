import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Search,
  FolderKanban,
  CheckSquare,
  MessageSquare,
  FileText,
  Calendar,
  Users,
  ArrowRight,
  File,
  MessageCircle,
  Loader2,
  Clock,
  X,
  CornerDownLeft,
} from 'lucide-react';
import { Modal } from '../common/Modal';
import { useProject } from '../../context/ProjectContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useAuth } from '../../context/AuthContext';
import { globalSearch } from '../../services/searchService';
import { GlobalSearchResult } from '../../types';

interface CommandMenuProps {
  isOpen: boolean;
  onClose: () => void;
}

export function CommandMenu({ isOpen, onClose }: CommandMenuProps) {
  const [query, setQuery] = useState('');
  const [searchResults, setSearchResults] = useState<GlobalSearchResult[]>([]);
  const [loading, setLoading] = useState(false);
  const [filterType, setFilterType] = useState<string>('ALL');
  const [selectedIndex, setSelectedIndex] = useState<number>(0);
  const [recentSearches, setRecentSearches] = useState<string[]>([]);

  const { projects } = useProject();
  const { currentOrganization } = useOrganization();
  const { profile, userRole } = useAuth();
  const navigate = useNavigate();
  const inputRef = useRef<HTMLInputElement>(null);

  // Load recent searches from localStorage
  useEffect(() => {
    if (isOpen) {
      try {
        const stored = localStorage.getItem('worksphere_recent_searches');
        if (stored) setRecentSearches(JSON.parse(stored));
      } catch (e) {}
      setSelectedIndex(0);
      setTimeout(() => inputRef.current?.focus(), 50);
    }
  }, [isOpen]);

  const saveRecentSearch = (searchTerm: string) => {
    if (!searchTerm.trim()) return;
    const term = searchTerm.trim();
    const updated = [term, ...recentSearches.filter((s) => s.toLowerCase() !== term.toLowerCase())].slice(0, 6);
    setRecentSearches(updated);
    try {
      localStorage.setItem('worksphere_recent_searches', JSON.stringify(updated));
    } catch (e) {}
  };

  const clearRecentSearches = (e: React.MouseEvent) => {
    e.stopPropagation();
    setRecentSearches([]);
    localStorage.removeItem('worksphere_recent_searches');
  };

  useEffect(() => {
    if (!query.trim()) {
      setSearchResults([]);
      setLoading(false);
      return;
    }

    const timer = setTimeout(async () => {
      setLoading(true);
      try {
        const results = await globalSearch(query, currentOrganization?.id || '', undefined, profile?.id, userRole);
        setSearchResults(results);
        setSelectedIndex(0);
      } catch (err) {
        console.error('Search error:', err);
      } finally {
        setLoading(false);
      }
    }, 200);

    return () => clearTimeout(timer);
  }, [query, currentOrganization?.id, profile?.id, userRole]);

  const projectCommands = projects.map((p) => ({
    title: p.name,
    category: 'Project',
    type: 'project',
    icon: FolderKanban,
    path: `/projects/${p.id}`,
  }));

  const defaultCommands = [
    { title: 'My Personal Tasks', category: 'Tasks', type: 'task', icon: CheckSquare, path: '/my-tasks' },
    { title: 'Message Board', category: 'Discussions', type: 'discussion', icon: MessageSquare, path: '/discussions' },
    { title: 'Docs & Files Workspace', category: 'Documents', type: 'doc', icon: FileText, path: '/docs' },
    { title: 'Interactive Calendar', category: 'Calendar', type: 'calendar', icon: Calendar, path: '/calendar' },
    { title: 'Organization Members', category: 'Members', type: 'member', icon: Users, path: '/members' },
  ];

  const staticFiltered = query.trim()
    ? [...projectCommands, ...defaultCommands].filter(
        (c) =>
          (filterType === 'ALL' || c.type === filterType) &&
          (c.title.toLowerCase().includes(query.toLowerCase()) ||
            c.category.toLowerCase().includes(query.toLowerCase()))
      )
    : [...projectCommands, ...defaultCommands].filter((c) => filterType === 'ALL' || c.type === filterType);

  const filteredSearchResults = filterType === 'ALL'
    ? searchResults
    : searchResults.filter((r) => r.type === filterType);

  const activeItemsCount = searchResults.length > 0 ? filteredSearchResults.length : staticFiltered.length;

  const getResultIcon = (type: string) => {
    switch (type) {
      case 'task': return CheckSquare;
      case 'discussion': return MessageSquare;
      case 'doc':
      case 'document': return FileText;
      case 'file': return File;
      case 'chat': return MessageCircle;
      case 'project': return FolderKanban;
      case 'member': return Users;
      case 'event': return Calendar;
      case 'list': return CheckSquare;
      default: return ArrowRight;
    }
  };

  const handleSelect = (path: string, termToSave?: string) => {
    if (termToSave) saveRecentSearch(termToSave);
    onClose();
    navigate(path);
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (activeItemsCount === 0) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setSelectedIndex((prev) => (prev + 1) % activeItemsCount);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setSelectedIndex((prev) => (prev - 1 + activeItemsCount) % activeItemsCount);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (searchResults.length > 0 && filteredSearchResults[selectedIndex]) {
        handleSelect(filteredSearchResults[selectedIndex].url, query);
      } else if (staticFiltered[selectedIndex]) {
        handleSelect(staticFiltered[selectedIndex].path, query);
      }
    }
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} maxWidth="lg">
      <div className="space-y-3" onKeyDown={handleKeyDown}>
        {/* Search input inside modal */}
        <div className="relative flex items-center">
          <Search className="absolute left-3.5 w-5 h-5 text-brand-500 pointer-events-none" />
          <input
            ref={inputRef}
            type="text"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder="Search projects, tasks, discussions, docs, files, members..."
            className="w-full pl-11 pr-20 py-3 bg-slate-100 dark:bg-slate-800 rounded-2xl text-slate-900 dark:text-slate-100 placeholder:text-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 font-medium"
            autoFocus
          />
          <div className="absolute right-3.5 flex items-center gap-1.5 text-xs text-slate-400">
            {loading ? (
              <Loader2 className="w-4 h-4 text-brand-500 animate-spin" />
            ) : query.trim() ? (
              <button
                type="button"
                onClick={() => setQuery('')}
                className="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
              >
                <X className="w-3.5 h-3.5" />
              </button>
            ) : (
              <span className="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-500 flex items-center gap-0.5">
                <CornerDownLeft className="w-2.5 h-2.5" /> Enter
              </span>
            )}
          </div>
        </div>

        {/* Quick Filter Chips */}
        <div className="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs custom-scrollbar">
          {[
            { id: 'ALL', label: 'All Results' },
            { id: 'task', label: 'Tasks' },
            { id: 'list', label: 'To-do Lists' },
            { id: 'discussion', label: 'Discussions' },
            { id: 'event', label: 'Events' },
            { id: 'doc', label: 'Documents' },
            { id: 'file', label: 'Files' },
            { id: 'project', label: 'Projects' },
            { id: 'member', label: 'Members' },
          ].map((chip) => (
            <button
              key={chip.id}
              type="button"
              onClick={() => {
                setFilterType(chip.id);
                setSelectedIndex(0);
              }}
              className={`px-3 py-1 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors ${
                filterType === chip.id
                  ? 'bg-brand-600 text-white shadow-xs'
                  : 'bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
              }`}
            >
              {chip.label}
            </button>
          ))}
        </div>

        {/* Recent Searches Header (if no query) */}
        {!query.trim() && recentSearches.length > 0 && (
          <div className="pt-2 border-t border-slate-200/60 dark:border-slate-800/60">
            <div className="flex items-center justify-between px-2 mb-2">
              <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <Clock className="w-3 h-3" /> Recent Searches
              </span>
              <button
                type="button"
                onClick={clearRecentSearches}
                className="text-[10px] font-semibold text-slate-400 hover:text-rose-400 transition-colors"
              >
                Clear History
              </button>
            </div>
            <div className="flex flex-wrap gap-1.5 px-2">
              {recentSearches.map((term, i) => (
                <button
                  key={i}
                  type="button"
                  onClick={() => setQuery(term)}
                  className="px-2.5 py-1 rounded-lg text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-brand-50 dark:hover:bg-brand-950/40 hover:text-brand-600 font-medium transition-colors"
                >
                  {term}
                </button>
              ))}
            </div>
          </div>
        )}

        {/* Results list */}
        <div className="max-h-80 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
          {searchResults.length > 0 ? (
            <div className="space-y-1">
              <div className="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                Search Matches ({filteredSearchResults.length})
              </div>
              {filteredSearchResults.length === 0 ? (
                <p className="text-xs text-slate-400 p-4 text-center">No results in this category.</p>
              ) : (
                filteredSearchResults.map((item, idx) => {
                  const IconComponent = getResultIcon(item.type);
                  const isSelected = idx === selectedIndex;
                  return (
                    <button
                      key={item.id}
                      onClick={() => handleSelect(item.url, query)}
                      onMouseEnter={() => setSelectedIndex(idx)}
                      className={`w-full flex items-center justify-between p-3 rounded-2xl text-left transition-all group ${
                        isSelected
                          ? 'bg-brand-50 dark:bg-brand-950/70 border border-brand-300 dark:border-brand-800'
                          : 'hover:bg-slate-100 dark:hover:bg-slate-800/80 border border-transparent'
                      }`}
                    >
                      <div className="flex items-center gap-3 min-w-0">
                        <div
                          className={`p-2 rounded-xl transition-colors shrink-0 ${
                            isSelected
                              ? 'bg-brand-600 text-white'
                              : 'bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 group-hover:bg-brand-500 group-hover:text-white'
                          }`}
                        >
                          <IconComponent className="w-4 h-4" />
                        </div>
                        <div className="min-w-0">
                          <p className={`text-sm font-semibold truncate transition-colors ${
                            isSelected ? 'text-brand-600 dark:text-brand-400' : 'text-slate-900 dark:text-slate-100'
                          }`}>
                            {item.title}
                          </p>
                          <p className="text-xs text-slate-500 dark:text-slate-400 truncate">
                            {item.subtitle}
                          </p>
                        </div>
                      </div>
                      <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-200/60 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 shrink-0">
                        {item.type}
                      </span>
                    </button>
                  );
                })
              )}
            </div>
          ) : staticFiltered.length > 0 ? (
            <div className="space-y-1">
              <div className="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                {query.trim() ? 'Matching Shortcuts' : 'Workspace Navigation & Shortcuts'}
              </div>
              {staticFiltered.map((item, idx) => {
                const isSelected = idx === selectedIndex;
                return (
                  <button
                    key={idx}
                    onClick={() => handleSelect(item.path, query || item.title)}
                    onMouseEnter={() => setSelectedIndex(idx)}
                    className={`w-full flex items-center justify-between p-3 rounded-2xl text-left transition-all group ${
                      isSelected
                        ? 'bg-brand-50 dark:bg-brand-950/70 border border-brand-300 dark:border-brand-800'
                        : 'hover:bg-slate-100 dark:hover:bg-slate-800/80 border border-transparent'
                    }`}
                  >
                    <div className="flex items-center gap-3">
                      <div
                        className={`p-2 rounded-xl transition-colors ${
                          isSelected
                            ? 'bg-brand-600 text-white'
                            : 'bg-slate-200/60 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 group-hover:bg-brand-500 group-hover:text-white'
                        }`}
                      >
                        <item.icon className="w-4 h-4" />
                      </div>
                      <div>
                        <p className={`text-sm font-semibold transition-colors ${
                          isSelected ? 'text-brand-600 dark:text-brand-400' : 'text-slate-900 dark:text-slate-100'
                        }`}>
                          {item.title}
                        </p>
                        <span className="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                          {item.category}
                        </span>
                      </div>
                    </div>
                    <ArrowRight className={`w-4 h-4 transition-all ${
                      isSelected ? 'text-brand-600 translate-x-1' : 'text-slate-300 dark:text-slate-600'
                    }`} />
                  </button>
                );
              })}
            </div>
          ) : (
            <div className="text-center py-8 text-slate-400 text-sm">
              No matching records found for "{query}"
            </div>
          )}
        </div>
      </div>
    </Modal>
  );
}
