import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { ProjectTemplate, CreateProjectInput, Project } from '../types';
import { createProject } from './projectService';
import { createTodoList } from './todoService';
import { createTask } from './taskService';
import { createKanbanColumn } from './kanbanService';

export const SYSTEM_TEMPLATES: ProjectTemplate[] = [
  {
    id: 'tpl-software',
    name: 'Software Development',
    category: 'Engineering',
    description: 'Sprint backlog, kanban workflow, tech specs, bug tracking, and code reviews.',
    structure: {
      todo_lists: [
        { title: 'Sprint Backlog', tasks: ['Architecture Review', 'API Route Specs', 'Unit Tests Setup'] },
        { title: 'In Progress / Feature Branch', tasks: ['Core Logic Implementation', 'UI Component Integration'] },
        { title: 'QA & Testing', tasks: ['Regression Test Suite', 'Security Audit Check'] },
      ],
      kanban_columns: [
        { name: 'Backlog', color: '#64748b', mapped_status: 'NOT_STARTED' },
        { name: 'In Dev', color: '#3b82f6', mapped_status: 'IN_PROGRESS' },
        { name: 'Code Review', color: '#8b5cf6', mapped_status: 'IN_REVIEW' },
        { name: 'QA / Test', color: '#f59e0b', mapped_status: 'WAITING' },
        { name: 'Production Done', color: '#10b981', mapped_status: 'COMPLETED' },
      ],
      disabled_tools: [],
    },
    is_system: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'tpl-website',
    name: 'Website Development',
    category: 'Design & Web',
    description: 'Wireframes, visual design tokens, responsive frontend layouts, SEO, and CMS setup.',
    structure: {
      todo_lists: [
        { title: 'Discovery & Design', tasks: ['Brand Color Tokens', 'Figma Wireframe Handoff'] },
        { title: 'Frontend Development', tasks: ['Responsive Layouts', 'Dark Mode Integration'] },
        { title: 'Launch Checklist', tasks: ['SEO Meta Tags Audit', 'Performance Lighthouse Test'] },
      ],
      kanban_columns: [
        { name: 'Wireframes', color: '#64748b', mapped_status: 'NOT_STARTED' },
        { name: 'UI Build', color: '#3b82f6', mapped_status: 'IN_PROGRESS' },
        { name: 'Review', color: '#8b5cf6', mapped_status: 'IN_REVIEW' },
        { name: 'Published', color: '#10b981', mapped_status: 'COMPLETED' },
      ],
      disabled_tools: [],
    },
    is_system: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'tpl-marketing',
    name: 'Marketing Campaign',
    category: 'Marketing',
    description: 'Content strategy, social media schedule, email newsletters, asset design, and analytics.',
    structure: {
      todo_lists: [
        { title: 'Campaign Planning', tasks: ['Target Audience Specs', 'Content Calendar Outline'] },
        { title: 'Asset Creation', tasks: ['Banner Ad Designs', 'Copywriting Drafts'] },
        { title: 'Execution', tasks: ['Newsletter Broadcast', 'Social Post Launch'] },
      ],
      kanban_columns: [
        { name: 'Ideas', color: '#64748b', mapped_status: 'NOT_STARTED' },
        { name: 'Drafting', color: '#3b82f6', mapped_status: 'IN_PROGRESS' },
        { name: 'Live', color: '#10b981', mapped_status: 'COMPLETED' },
      ],
      disabled_tools: [],
    },
    is_system: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'tpl-client',
    name: 'Client Project',
    category: 'Agency',
    description: 'Client onboarding, weekly progress updates, deliverables, feedback rounds, and signoff.',
    structure: {
      todo_lists: [
        { title: 'Onboarding', tasks: ['Kickoff Meeting Agenda', 'Client Assets Collection'] },
        { title: 'Milestones', tasks: ['Milestone 1 Deliverables', 'Milestone 2 Review'] },
      ],
      kanban_columns: [
        { name: 'To Do', color: '#64748b', mapped_status: 'NOT_STARTED' },
        { name: 'In Progress', color: '#3b82f6', mapped_status: 'IN_PROGRESS' },
        { name: 'Client Feedback', color: '#f59e0b', mapped_status: 'WAITING' },
        { name: 'Approved', color: '#10b981', mapped_status: 'COMPLETED' },
      ],
      disabled_tools: [],
    },
    is_system: true,
    created_at: new Date().toISOString(),
  },
  {
    id: 'tpl-product-launch',
    name: 'Product Launch',
    category: 'Product',
    description: 'PR announcements, beta testing group feedback, launch checklist, and support readiness.',
    structure: {
      todo_lists: [
        { title: 'Pre-Launch Prep', tasks: ['Beta Group Onboarding', 'Press Release Draft'] },
        { title: 'Launch Day', tasks: ['Product Hunt Post', 'Social Media Blast', 'Server Capacity Monitor'] },
      ],
      kanban_columns: [
        { name: 'Pre-Launch', color: '#64748b', mapped_status: 'NOT_STARTED' },
        { name: 'Launch Day', color: '#ef4444', mapped_status: 'IN_PROGRESS' },
        { name: 'Post-Launch', color: '#10b981', mapped_status: 'COMPLETED' },
      ],
      disabled_tools: [],
    },
    is_system: true,
    created_at: new Date().toISOString(),
  },
];

export async function getProjectTemplates(): Promise<ProjectTemplate[]> {
  if (!isSupabaseConfigured) {
    return SYSTEM_TEMPLATES;
  }

  try {
    const { data, error } = await supabase
      .from('project_templates')
      .select('*')
      .order('created_at', { ascending: false });

    if (!error && data && data.length > 0) {
      return [...data, ...SYSTEM_TEMPLATES] as ProjectTemplate[];
    }
  } catch (e) {
    console.warn('Templates Supabase fetch error:', e);
  }

  return SYSTEM_TEMPLATES;
}

export type { ProjectTemplate };

export async function createProjectFromTemplate(
  template: ProjectTemplate,
  input: CreateProjectInput
): Promise<{ data: Project | null; error: Error | null }> {
  // 1. Create base project
  const res = await createProject(input);
  if (res.error || !res.data) return res;

  const project = res.data;

  try {
    // 2. Instantiate Kanban columns
    if (template.structure.kanban_columns) {
      for (let i = 0; i < template.structure.kanban_columns.length; i++) {
        const col = template.structure.kanban_columns[i];
        await createKanbanColumn({
          project_id: project.id,
          name: col.name,
          color: col.color,
          mapped_status: col.mapped_status,
          created_by: input.created_by,
        });
      }
    }

    // 3. Instantiate To-Do Lists & Tasks
    if (template.structure.todo_lists) {
      for (const listSpec of template.structure.todo_lists) {
        const listRes = await createTodoList({
          project_id: project.id,
          title: listSpec.title,
          created_by: input.created_by,
        });

        if (listSpec.tasks && listRes.data) {
          for (const taskTitle of listSpec.tasks) {
            await createTask({
              project_id: project.id,
              todo_list_id: listRes.data.id,
              title: taskTitle,
              created_by: input.created_by,
            });
          }
        }
      }
    }
  } catch (err) {
    console.error('Error instantiating template structure:', err);
  }

  return { data: project, error: null };
}

export async function instantiateProjectFromTemplate(
  templateId: string,
  projectName: string,
  organizationId: string,
  userId?: string
): Promise<{ data: Project | null; error: Error | null }> {
  const templates = await getProjectTemplates();
  const template = templates.find((t) => t.id === templateId) || SYSTEM_TEMPLATES[0];

  return createProjectFromTemplate(template, {
    name: projectName,
    organization_id: organizationId,
    description: template.description,
    created_by: userId,
  });
}

