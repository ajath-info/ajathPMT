import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { CheckinQuestion, CheckinResponse } from '../types';
import { createNotification } from './notificationService';
import { getProjectMembers } from './projectService';
import { logActivity } from './organizationService';

let MOCK_CHECKIN_QUESTIONS: CheckinQuestion[] = [
  {
    id: 'chk-1',
    project_id: 'proj-hq',
    question: 'What did you accomplish today and what are your priorities for tomorrow?',
    schedule: 'DAILY',
    is_active: true,
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 5 * 24 * 3600 * 1000).toISOString(),
    creator: {
      id: 'demo-user-owner',
      email: 'claire.client@partner.com',
      full_name: 'Claire Watson',
      avatar_url: '',
    },
    responses_count: 2,
  },
  {
    id: 'chk-2',
    project_id: 'proj-rmc',
    question: 'Are there any vehicle inspection blockers stopping app testing?',
    schedule: 'WEEKLY',
    is_active: true,
    created_by: 'demo-user-owner',
    created_at: new Date(Date.now() - 3 * 24 * 3600 * 1000).toISOString(),
    creator: {
      id: 'demo-user-owner',
      email: 'claire.client@partner.com',
      full_name: 'Claire Watson',
      avatar_url: '',
    },
    responses_count: 1,
  },
];

let MOCK_CHECKIN_RESPONSES: CheckinResponse[] = [
  {
    id: 'resp-1',
    question_id: 'chk-1',
    user_id: 'demo-user-admin',
    response: 'Completed the Kanban drag-and-drop integration. Tomorrow working on document editor auto-save.',
    created_at: new Date(Date.now() - 3 * 3600 * 1000).toISOString(),
    user: {
      id: 'demo-user-admin',
      email: 'sarah.admin@worksphere.io',
      full_name: 'Sarah Jenkins',
      avatar_url: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
    },
  },
  {
    id: 'resp-2',
    question_id: 'chk-1',
    user_id: 'demo-user-owner',
    response: 'Configured Supabase DDL schema, RLS policies, and global search command menu.',
    created_at: new Date(Date.now() - 1 * 3600 * 1000).toISOString(),
    user: {
      id: 'demo-user-owner',
      email: 'alex.owner@worksphere.io',
      full_name: 'Alex Vance',
      avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
    },
  },
];

export async function getCheckinQuestions(projectId: string): Promise<CheckinQuestion[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('checkin_questions')
        .select('*, creator:profiles(*), responses:checkin_responses(count)')
        .eq('project_id', projectId)
        .order('created_at', { ascending: false });

      if (!error && data) {
        return data.map((q: any) => ({
          ...q,
          responses_count: q.responses?.[0]?.count || 0,
        })) as CheckinQuestion[];
      }
    } catch (e) {
      console.warn('Failed to fetch check-in questions via Supabase:', e);
    }
  }

  return MOCK_CHECKIN_QUESTIONS.filter((q) => q.project_id === projectId);
}

export async function createCheckinQuestion(
  projectId: string,
  question: string,
  schedule = 'DAILY',
  createdBy?: string
): Promise<CheckinQuestion> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('checkin_questions')
        .insert([
          {
            project_id: projectId,
            question: question.trim(),
            schedule,
            is_active: true,
            created_by: createdBy || null,
          },
        ])
        .select('*, creator:profiles(*)')
        .single();

      if (!error && data) return { ...data, responses_count: 0 } as CheckinQuestion;
    } catch (e) {
      console.warn('Failed to create checkin question via Supabase:', e);
    }
  }

  const newQuestion: CheckinQuestion = {
    id: `chk-${Date.now()}`,
    project_id: projectId,
    question: question.trim(),
    schedule,
    is_active: true,
    created_by: createdBy,
    created_at: new Date().toISOString(),
    responses_count: 0,
  };
  MOCK_CHECKIN_QUESTIONS.unshift(newQuestion);
  return newQuestion;
}

export async function toggleCheckinQuestion(id: string, isActive: boolean): Promise<void> {
  if (isSupabaseConfigured) {
    await supabase.from('checkin_questions').update({ is_active: isActive }).eq('id', id);
  }
  const q = MOCK_CHECKIN_QUESTIONS.find((item) => item.id === id);
  if (q) q.is_active = isActive;
}

export async function deleteCheckinQuestion(id: string): Promise<void> {
  if (isSupabaseConfigured) {
    await supabase.from('checkin_questions').delete().eq('id', id);
  }
  MOCK_CHECKIN_QUESTIONS = MOCK_CHECKIN_QUESTIONS.filter((q) => q.id !== id);
}

export async function getCheckinResponses(questionId: string): Promise<CheckinResponse[]> {
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('checkin_responses')
        .select('*, user:profiles(*)')
        .eq('question_id', questionId)
        .order('created_at', { ascending: false });

      if (!error && data) return data as CheckinResponse[];
    } catch (e) {
      console.warn('Failed to fetch check-in responses via Supabase:', e);
    }
  }

  return MOCK_CHECKIN_RESPONSES.filter((r) => r.question_id === questionId);
}

export async function submitCheckinResponse(
  questionId: string,
  userId: string,
  response: string
): Promise<CheckinResponse> {
  let resultResp: CheckinResponse | null = null;
  if (isSupabaseConfigured) {
    try {
      const { data, error } = await supabase
        .from('checkin_responses')
        .insert([
          {
            question_id: questionId,
            user_id: userId,
            response: response.trim(),
          },
        ])
        .select('*, user:profiles(*)')
        .single();

      if (!error && data) resultResp = data as CheckinResponse;
    } catch (e) {
      console.warn('Failed to submit check-in response via Supabase:', e);
    }
  }

  if (!resultResp) {
    resultResp = {
      id: `resp-${Date.now()}`,
      question_id: questionId,
      user_id: userId,
      response: response.trim(),
      created_at: new Date().toISOString(),
      user: {
        id: userId,
        email: 'alex.owner@worksphere.io',
        full_name: 'Alex Vance',
        avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
      },
    };
    MOCK_CHECKIN_RESPONSES.unshift(resultResp);
  }

  // Notify Question Creator
  const targetQ = MOCK_CHECKIN_QUESTIONS.find((q) => q.id === questionId);
  if (targetQ) {
    if (targetQ.created_by && targetQ.created_by !== userId) {
      await createNotification({
        user_id: targetQ.created_by,
        type: 'CHECKIN_RESPONSE',
        title: 'Check-In Response Received',
        body: `A response was posted for: "${targetQ.question.slice(0, 50)}..."`,
        link_url: `/projects/${targetQ.project_id}/checkins`,
      });
    }

    await logActivity(
      '',
      userId,
      'answered check-in question',
      targetQ.question,
      targetQ.project_id
    );
  }

  return resultResp;
}

const SENT_CHECKIN_PROMPTS = new Set<string>();

export async function runScheduledCheckins(projectId: string): Promise<{ notifiedCount: number }> {
  const questions = await getCheckinQuestions(projectId);
  const activeQuestions = questions.filter((q) => q.is_active);
  if (activeQuestions.length === 0) return { notifiedCount: 0 };

  const members = await getProjectMembers(projectId);
  if (members.length === 0) return { notifiedCount: 0 };

  const todayStr = new Date().toISOString().split('T')[0];
  let count = 0;

  for (const q of activeQuestions) {
    for (const m of members) {
      const promptKey = `${q.id}:${todayStr}:${m.user_id}`;
      if (SENT_CHECKIN_PROMPTS.has(promptKey)) continue;

      await createNotification({
        user_id: m.user_id,
        type: 'CHECKIN_PROMPT',
        title: 'Time for your Check-In',
        body: `Prompt: "${q.question.length > 60 ? q.question.slice(0, 60) + '...' : q.question}"`,
        link_url: `/projects/${projectId}/checkins`,
      });

      SENT_CHECKIN_PROMPTS.add(promptKey);
      count++;
    }
  }

  return { notifiedCount: count };
}
