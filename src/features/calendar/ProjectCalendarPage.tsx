import React, { useState, useEffect, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Calendar as CalendarIcon,
  Plus,
  ChevronLeft,
  ChevronRight,
  CheckSquare,
  Clock,
  Bookmark,
  MoreHorizontal,
  CalendarDays,
  Printer,
  Share2,
  Trash2,
  Sparkles,
  Check,
  Search,
  Download,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { useProject } from '../../context/ProjectContext';
import { CalendarEvent, Task, Project } from '../../types';
import {
  getProjectScheduleItems,
  deleteCalendarEvent,
  downloadCalendarIcs,
} from '../../services/calendarService';
import { CreateEventModal } from '../../components/calendar/CreateEventModal';
import { EventDetailModal } from '../../components/calendar/EventDetailModal';

// Project color dots/checks matching Basecamp palette
const PROJECT_COLORS: Record<string, string> = {
  'proj-hq': 'bg-[#16a34a]', // Green
  'proj-bipl': 'bg-[#854d0e]', // Brown
  'proj-exibine': 'bg-[#ea580c]', // Orange
  'proj-hihlo': 'bg-[#9333ea]', // Purple
  'proj-hrms': 'bg-[#2563eb]', // Blue
  'proj-rmc': 'bg-[#0284c7]', // Sky Blue
  'proj-veggie': 'bg-[#d97706]', // Amber
  'proj-pm': 'bg-[#db2777]', // Pink
  'proj-hii': 'bg-[#10b981]', // Teal/Mint
};

function getProjectColor(id: string): string {
  if (PROJECT_COLORS[id]) return PROJECT_COLORS[id];
  return 'bg-[#2563eb]';
}

export function ProjectCalendarPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const { projects } = useProject();
  const { user } = useAuth();
  const { addToast } = useToast();
  const navigate = useNavigate();

  // If no projectId, we are on the global Basecamp Calendar (/calendar)
  const isGlobalCalendar = !projectId;

  // Set browser title
  useEffect(() => {
    document.title = isGlobalCalendar ? 'Calendar' : 'Schedule';
    return () => {
      document.title = 'Ajath PMT';
    };
  }, [isGlobalCalendar]);

  // Global Calendar states
  const [selectedProjectIds, setSelectedProjectIds] = useState<Set<string>>(() => {
    return new Set(projects.map((p) => p.id));
  });
  const [projectFilterQuery, setProjectFilterQuery] = useState('');
  const [calendarView, setCalendarView] = useState<'calendar' | 'agenda'>('calendar');
  const [scopeFilter, setScopeFilter] = useState<'mine' | 'everyone'>('mine');
  const [typeFilter, setTypeFilter] = useState<'events' | 'all'>('events');
  const [filterSearch, setFilterSearch] = useState('');

  // Modals & Events
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [selectedEventForDetail, setSelectedEventForDetail] = useState<CalendarEvent | null>(null);
  const [allEvents, setAllEvents] = useState<CalendarEvent[]>([]);
  const [loading, setLoading] = useState(false);

  // Function to load all events
  const loadAllEvents = async () => {
    setLoading(true);
    try {
      const results = await Promise.all(
        projects.map((p) => getProjectScheduleItems(p.id).catch(() => ({ events: [], tasks: [] })))
      );
      const merged = results.flatMap((r) => r.events);
      setAllEvents(merged);
    } catch (err) {
      console.error('Failed to load events:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadAllEvents();
  }, [projects]);

  // Keep selectedProjectIds in sync with loaded projects
  useEffect(() => {
    if (projects.length > 0 && selectedProjectIds.size === 0) {
      setSelectedProjectIds(new Set(projects.map((p) => p.id)));
    }
  }, [projects]);

  const toggleAllProjects = () => {
    if (selectedProjectIds.size === projects.length) {
      setSelectedProjectIds(new Set());
    } else {
      setSelectedProjectIds(new Set(projects.map((p) => p.id)));
    }
  };

  const toggleProject = (id: string) => {
    const next = new Set(selectedProjectIds);
    if (next.has(id)) {
      next.delete(id);
    } else {
      next.add(id);
    }
    setSelectedProjectIds(next);
  };

  const filteredProjectsList = useMemo(() => {
    if (!projectFilterQuery.trim()) return projects;
    const q = projectFilterQuery.toLowerCase();
    return projects.filter((p) => p.name.toLowerCase().includes(q));
  }, [projects, projectFilterQuery]);

  // Filter events by selected projects and search term
  const visibleEvents = useMemo(() => {
    return allEvents.filter((evt) => {
      if (selectedProjectIds.size > 0 && !selectedProjectIds.has(evt.project_id)) {
        return false;
      }
      if (filterSearch.trim()) {
        const q = filterSearch.toLowerCase();
        const matchTitle = evt.title.toLowerCase().includes(q);
        const matchDesc = evt.description?.toLowerCase().includes(q);
        if (!matchTitle && !matchDesc) return false;
      }
      return true;
    });
  }, [allEvents, selectedProjectIds, filterSearch]);

  // Handle iCal feed subscription/download (RFC 5545)
  const handleSubscribeIcs = () => {
    if (visibleEvents.length === 0) {
      addToast('No events to export in current selection', 'info');
      return;
    }
    downloadCalendarIcs(visibleEvents, 'worksphere-schedule.ics');
    addToast('Schedule .ics file downloaded & feed synchronized!', 'success');
  };

  // Basecamp 6-week view dates definition
  const sixWeeksGrid = useMemo(() => {
    const startDate = new Date(2026, 8, 6); // Sep 6, 2026
    const weeks: Array<Array<{ date: Date; dayNum: number; isToday: boolean; isNewMonth: boolean; monthLabel: string }>> = [];

    for (let w = 0; w < 6; w++) {
      const week: Array<{ date: Date; dayNum: number; isToday: boolean; isNewMonth: boolean; monthLabel: string }> = [];
      for (let d = 0; d < 7; d++) {
        const cur = new Date(startDate);
        cur.setDate(startDate.getDate() + w * 7 + d);

        const isToday = cur.getMonth() === 8 && cur.getDate() === 8; // Sep 8, 2026
        const isNewMonth = cur.getDate() === 1;
        const monthLabel = cur.toLocaleString('default', { month: 'short' }) + ' ' + cur.getDate() + ', ' + cur.getFullYear();

        week.push({
          date: cur,
          dayNum: cur.getDate(),
          isToday,
          isNewMonth,
          monthLabel,
        });
      }
      weeks.push(week);
    }
    return weeks;
  }, []);

  // Check which events belong to a given date
  const getEventsForDate = (date: Date) => {
    const y = date.getFullYear();
    const m = date.getMonth();
    const d = date.getDate();
    return visibleEvents.filter((evt) => {
      const evtDate = new Date(evt.start_at);
      return evtDate.getFullYear() === y && evtDate.getMonth() === m && evtDate.getDate() === d;
    });
  };

  return (
    <div className="w-full max-w-7xl mx-auto space-y-6 animate-in fade-in duration-200 pb-16">
      {/* Modern Calendar Card */}
      <div className="w-full bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
        
        {/* Split Two-Column Container */}
        <div className="flex flex-col lg:flex-row items-start gap-8">
          
          {/* Left Sidebar: Project Filter Controls */}
          <div className="w-full lg:w-64 shrink-0 space-y-4 pt-1">
            {/* All Projects Checkbox */}
            <label className="flex items-center gap-2 cursor-pointer select-none text-xs font-bold text-slate-800 dark:text-slate-200">
              <input
                type="checkbox"
                checked={selectedProjectIds.size === projects.length && projects.length > 0}
                onChange={toggleAllProjects}
                className="w-4 h-4 rounded text-blue-600 focus:ring-0 cursor-pointer"
              />
              <span>All projects</span>
            </label>

            {/* Filter Projects search box */}
            <div>
              <input
                type="text"
                value={projectFilterQuery}
                onChange={(e) => setProjectFilterQuery(e.target.value)}
                placeholder="Filter projects..."
                className="w-full text-xs px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 placeholder:text-slate-400 text-slate-900 dark:text-slate-100 focus:outline-none focus:border-slate-400"
              />
            </div>

            {/* Project List with Basecamp colored square checkboxes */}
            <div className="space-y-2 pt-1 max-h-[calc(100vh-22rem)] overflow-y-auto pr-1">
              {filteredProjectsList.map((p) => {
                const isChecked = selectedProjectIds.has(p.id);
                const color = getProjectColor(p.id);

                return (
                  <label
                    key={p.id}
                    className="flex items-center gap-2.5 cursor-pointer select-none group text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white"
                  >
                    <div
                      onClick={() => toggleProject(p.id)}
                      className={`w-4 h-4 rounded-[4px] flex items-center justify-center transition-all ${
                        isChecked
                          ? `${color} text-white shadow-xs`
                          : 'border border-slate-300 bg-white text-transparent'
                      }`}
                    >
                      <Check className="w-3 h-3 stroke-[3]" />
                    </div>
                    <span className="truncate">{p.name}</span>
                  </label>
                );
              })}
            </div>
          </div>

          {/* Right Main Area: Calendar Grid & Controls */}
          <div className="flex-1 w-full space-y-4">
            
            {/* Top Bar matching Basecamp Schedule */}
            <div className="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
              <div className="flex flex-wrap items-center gap-2 sm:gap-3 text-xs">
                {/* All Projects button */}
                <button
                  onClick={() => navigate('/projects')}
                  className="font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white cursor-pointer"
                >
                  ← All projects
                </button>

                {/* Calendar / Agenda Tabs */}
                <div className="flex items-center gap-0.5 border border-slate-200 dark:border-slate-700 rounded-lg p-0.5 bg-slate-50 dark:bg-slate-800">
                  <button
                    onClick={() => setCalendarView('calendar')}
                    className={`px-3 py-1 rounded-md font-bold transition-all cursor-pointer ${
                      calendarView === 'calendar'
                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-2xs'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
                    }`}
                  >
                    Calendar
                  </button>
                  <button
                    onClick={() => setCalendarView('agenda')}
                    className={`px-3 py-1 rounded-md font-bold transition-all cursor-pointer ${
                      calendarView === 'agenda'
                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-2xs'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
                    }`}
                  >
                    Agenda
                  </button>
                </div>

                {/* Mine / Everyone Tabs */}
                <div className="flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                  <button
                    onClick={() => setScopeFilter('mine')}
                    className={`cursor-pointer ${
                      scopeFilter === 'mine'
                        ? 'text-slate-900 dark:text-white font-bold underline underline-offset-4'
                        : 'hover:text-slate-900'
                    }`}
                  >
                    Mine
                  </button>
                  <button
                    onClick={() => setScopeFilter('everyone')}
                    className={`cursor-pointer ${
                      scopeFilter === 'everyone'
                        ? 'text-slate-900 dark:text-white font-bold underline underline-offset-4'
                        : 'hover:text-slate-900'
                    }`}
                  >
                    Everyone
                  </button>
                </div>

                {/* Events / Events + tasks */}
                <div className="flex items-center gap-1 border border-slate-200 dark:border-slate-700 rounded-lg p-0.5 bg-slate-50 dark:bg-slate-800">
                  <button
                    onClick={() => setTypeFilter('events')}
                    className={`px-2.5 py-0.5 rounded font-bold cursor-pointer ${
                      typeFilter === 'events'
                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-2xs'
                        : 'text-slate-500'
                    }`}
                  >
                    Events
                  </button>
                  <button
                    onClick={() => setTypeFilter('all')}
                    className={`px-2.5 py-0.5 rounded font-bold cursor-pointer ${
                      typeFilter === 'all'
                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-2xs'
                        : 'text-slate-500'
                    }`}
                  >
                    Events + tasks
                  </button>
                </div>

                {/* Filter Input */}
                <div>
                  <input
                    type="text"
                    value={filterSearch}
                    onChange={(e) => setFilterSearch(e.target.value)}
                    placeholder="Filter..."
                    className="w-24 text-xs px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 placeholder:text-slate-400 text-slate-900 dark:text-white focus:outline-none"
                  />
                </div>
              </div>

              {/* Right: Subscribe... & New event button */}
              <div className="flex items-center gap-3">
                <button
                  onClick={handleSubscribeIcs}
                  className="text-xs font-semibold text-blue-600 hover:underline cursor-pointer flex items-center gap-1"
                  title="Export / Subscribe RFC 5545 iCal Feed"
                >
                  <Download className="w-3 h-3" />
                  <span>Subscribe (iCal)</span>
                </button>
                <button
                  onClick={() => setIsCreateModalOpen(true)}
                  className="px-4 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer flex items-center gap-1.5"
                >
                  <Plus className="w-3.5 h-3.5 stroke-[3]" />
                  <span>New event</span>
                </button>
              </div>
            </div>

            {/* Navigation Header: < Next 6 Weeks > */}
            <div className="flex items-center justify-center gap-6 py-1">
              <button
                className="p-1 text-slate-400 hover:text-slate-800 transition-colors cursor-pointer"
                title="Previous"
              >
                <ChevronLeft className="w-4 h-4" />
              </button>
              <h2 className="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight">
                Next 6 Weeks
              </h2>
              <button
                className="p-1 text-slate-400 hover:text-slate-800 transition-colors cursor-pointer"
                title="Next"
              >
                <ChevronRight className="w-4 h-4" />
              </button>
            </div>

            {/* 6-Week Calendar Grid matching Screenshot 3 */}
            <div className="border border-slate-200/90 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900">
              {/* Day of Week Headers */}
              <div className="grid grid-cols-7 border-b border-slate-200/80 dark:border-slate-800 bg-[#fbfbfb] dark:bg-slate-850 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider py-1.5">
                <div>SUN</div>
                <div>MON</div>
                <div>TUE</div>
                <div>WED</div>
                <div>THU</div>
                <div>FRI</div>
                <div>SAT</div>
              </div>

              {/* 6 Weeks Grid Rows */}
              <div className="divide-y divide-slate-100 dark:divide-slate-800/80">
                {sixWeeksGrid.map((week, wIdx) => (
                  <div key={wIdx} className="grid grid-cols-7 divide-x divide-slate-100 dark:divide-slate-800/80 min-h-[105px]">
                    {week.map((day, dIdx) => {
                      const dayEvents = getEventsForDate(day.date);

                      return (
                        <div
                          key={dIdx}
                          onClick={(e) => {
                            // If clicked on day cell background, open new event modal
                            if (e.target === e.currentTarget) {
                              setIsCreateModalOpen(true);
                            }
                          }}
                          className={`p-1.5 sm:p-2 transition-colors relative group flex flex-col justify-between ${
                            day.isToday
                              ? 'bg-[#FAF7E8] dark:bg-amber-950/20'
                              : 'hover:bg-slate-50/70 dark:hover:bg-slate-800/50'
                          }`}
                        >
                          {/* Day Label & Today badge */}
                          <div className="flex items-start justify-between">
                            {day.isToday ? (
                              <span className="text-[11px] font-bold text-slate-900 dark:text-slate-100">
                                Today, Sep 8
                              </span>
                            ) : day.isNewMonth ? (
                              <span className="bg-[#A37B58] text-white text-[10px] font-black px-1.5 py-0.5 rounded shadow-2xs">
                                {day.monthLabel}
                              </span>
                            ) : (
                              <span className="text-xs font-bold text-slate-700 dark:text-slate-300 ml-auto">
                                {day.dayNum}
                              </span>
                            )}
                          </div>

                          {/* Events List inside Day Cell */}
                          <div className="space-y-1 my-1 overflow-hidden">
                            {dayEvents.map((evt) => (
                              <div
                                key={evt.id}
                                onClick={(e) => {
                                  e.stopPropagation();
                                  setSelectedEventForDetail(evt);
                                }}
                                className="px-1.5 py-1 rounded text-[11px] font-medium text-white shadow-2xs truncate cursor-pointer transition-transform hover:scale-[1.02] active:scale-[0.98]"
                                style={{ backgroundColor: evt.color || '#3b82f6' }}
                                title={`${evt.title} - Click for details and RSVP`}
                              >
                                {evt.title}
                              </div>
                            ))}
                          </div>

                          {/* Hover + button to add event on this date */}
                          <div className="opacity-0 group-hover:opacity-100 transition-opacity flex justify-end">
                            <button
                              onClick={(e) => {
                                e.stopPropagation();
                                setIsCreateModalOpen(true);
                              }}
                              className="text-[10px] text-blue-600 font-bold hover:underline"
                            >
                              +
                            </button>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                ))}
              </div>
            </div>

          </div>

        </div>

      </div>

      {/* Create Event Modal */}
      <CreateEventModal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
        projectId={projectId || projects[0]?.id || 'proj-hq'}
        userId={user?.id}
        onSuccess={() => {
          loadAllEvents();
          addToast('Event created successfully!', 'success');
        }}
      />

      {/* Event Detail & RSVP Modal */}
      <EventDetailModal
        isOpen={Boolean(selectedEventForDetail)}
        onClose={() => setSelectedEventForDetail(null)}
        event={selectedEventForDetail}
        onEventUpdated={() => {
          loadAllEvents();
        }}
        onEventDeleted={() => {
          loadAllEvents();
        }}
      />
    </div>
  );
}
