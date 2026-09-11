import { isSupabaseConfigured, supabase } from '../lib/supabase';
import { GlobalSearchResult, OrgRole } from '../types';
import { getProjects } from './projectService';
import { getTasks } from './taskService';
import { getDiscussions } from './discussionService';
import { getProjectDocuments } from './documentService';
import { getProjectFiles } from './fileService';
import { getCalendarEvents } from './calendarService';
import { getTodoLists } from './todoService';
import { fetchOrganizationMembers } from './organizationService';

/**
 * Universal Global Search across Projects, To-dos, Todo Lists, Message Board Discussions,
 * Collaborative Documents, Files & Uploads, Calendar Events, and People/Members.
 * Strictly scoped to currently active organization and user permissions.
 */
export async function globalSearch(
  query: string,
  orgId: string,
  projectId?: string,
  userId?: string,
  userRole?: OrgRole
): Promise<GlobalSearchResult[]> {
  if (!query || query.trim().length < 2 || !orgId) return [];
  const q = query.trim().toLowerCase();

  const results: GlobalSearchResult[] = [];

  try {
    // 1. Projects Search (Active & Accessible to user)
    const projects = await getProjects(orgId, userId, userRole);
    projects.forEach((p) => {
      if (
        p.name.toLowerCase().includes(q) ||
        (p.description && p.description.toLowerCase().includes(q))
      ) {
        results.push({
          id: p.id,
          type: 'project',
          title: p.name,
          subtitle: p.description || 'Project Workspace',
          url: `/projects/${p.id}`,
          badge: p.status === 'ARCHIVED' ? 'Archived Project' : 'Project',
        });
      }
    });

    // 2. Organization Members / People Search (Internal staff only; clients cannot search company employee directory)
    if (orgId && userRole !== 'CLIENT') {
      try {
        const orgMembers = await fetchOrganizationMembers(orgId);
        orgMembers.forEach((m) => {
          const name = m.profile?.full_name || '';
          const email = m.profile?.email || '';
          if (name.toLowerCase().includes(q) || email.toLowerCase().includes(q)) {
            results.push({
              id: m.id,
              type: 'member',
              title: name || email || 'Team Member',
              subtitle: `${m.role} • ${email}`,
              url: '/members',
              badge: m.role,
            });
          }
        });
      } catch (err) {
        console.warn('Member search error:', err);
      }
    }

    // 3. Search target projects (all accessible projects if not scoped to a single project)
    const targetProjects = projectId
      ? projects.filter((p) => p.id === projectId)
      : projects;

    // Concurrently search tools across target projects
    await Promise.allSettled(
      targetProjects.map(async (proj) => {
        // Parallel queries per project
        const [tasksRes, discussionsRes, docsRes, filesRes, eventsRes, listsRes] =
          await Promise.allSettled([
            getTasks(proj.id),
            getDiscussions(proj.id, userRole),
            getProjectDocuments(proj.id),
            getProjectFiles(proj.id),
            getCalendarEvents(proj.id),
            getTodoLists(proj.id),
          ]);

        // A. Tasks / To-dos
        if (tasksRes.status === 'fulfilled' && tasksRes.value) {
          tasksRes.value.forEach((t) => {
            if (
              t.title.toLowerCase().includes(q) ||
              (t.description && t.description.toLowerCase().includes(q))
            ) {
              results.push({
                id: t.id,
                type: 'task',
                title: t.title,
                subtitle: `${proj.name} • ${t.status}`,
                url: `/projects/${t.project_id}/todos`,
                badge: t.priority || 'To-do',
              });
            }
          });
        }

        // B. To-do Lists
        if (listsRes.status === 'fulfilled' && listsRes.value) {
          listsRes.value.forEach((l) => {
            if (
              l.title.toLowerCase().includes(q) ||
              (l.description && l.description.toLowerCase().includes(q))
            ) {
              results.push({
                id: l.id,
                type: 'list',
                title: l.title,
                subtitle: `${proj.name} • To-do List`,
                url: `/projects/${proj.id}/todos`,
                badge: 'To-do List',
              });
            }
          });
        }

        // C. Discussions / Message Board
        if (discussionsRes.status === 'fulfilled' && discussionsRes.value) {
          discussionsRes.value.forEach((d) => {
            if (
              d.title.toLowerCase().includes(q) ||
              (d.content && d.content.toLowerCase().includes(q))
            ) {
              results.push({
                id: d.id,
                type: 'discussion',
                title: d.title,
                subtitle: `${proj.name} • ${d.category || 'Message'}`,
                url: `/projects/${d.project_id}/discussions`,
                badge: d.is_announcement ? 'Announcement' : 'Message',
              });
            }
          });
        }

        // D. Documents
        if (docsRes.status === 'fulfilled' && docsRes.value) {
          docsRes.value.forEach((doc) => {
            if (
              doc.title.toLowerCase().includes(q) ||
              (doc.content && doc.content.toLowerCase().includes(q))
            ) {
              results.push({
                id: doc.id,
                type: 'document',
                title: doc.title,
                subtitle: `${proj.name} • Collaborative Doc`,
                url: `/projects/${doc.project_id}/docs`,
                badge: 'Document',
              });
            }
          });
        }

        // E. Files & Uploads
        if (filesRes.status === 'fulfilled' && Array.isArray(filesRes.value)) {
          filesRes.value.forEach((f) => {
            if (
              f.name.toLowerCase().includes(q) ||
              (f.description && f.description.toLowerCase().includes(q))
            ) {
              results.push({
                id: f.id,
                type: 'file',
                title: f.name,
                subtitle: `${proj.name} • ${(f.size / 1024).toFixed(0)} KB`,
                url: `/projects/${f.project_id}/files`,
                badge: 'File',
              });
            }
          });
        }

        // F. Calendar Events
        if (eventsRes.status === 'fulfilled' && eventsRes.value) {
          eventsRes.value.forEach((evt) => {
            if (
              evt.title.toLowerCase().includes(q) ||
              (evt.description && evt.description.toLowerCase().includes(q))
            ) {
              const startDate = evt.start_at
                ? new Date(evt.start_at).toLocaleDateString(undefined, {
                    month: 'short',
                    day: 'numeric',
                  })
                : '';
              results.push({
                id: evt.id,
                type: 'event',
                title: evt.title,
                subtitle: `${proj.name} • ${startDate}`,
                url: `/projects/${evt.project_id}/calendar`,
                badge: 'Event',
              });
            }
          });
        }
      })
    );
  } catch (err) {
    console.warn('Global search error:', err);
  }

  return results.slice(0, 50);
}
