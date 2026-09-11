import { supabase, isSupabaseConfigured } from '../lib/supabase';
import { UserOnboardingState } from '../types';

let MOCK_ONBOARDING: UserOnboardingState = {
  id: 'onboard-1',
  user_id: 'demo-user-owner',
  step: 3,
  is_completed: true,
};

export async function getUserOnboarding(userId: string): Promise<UserOnboardingState> {
  if (!isSupabaseConfigured) {
    return MOCK_ONBOARDING;
  }

  try {
    const { data, error } = await supabase
      .from('user_onboarding')
      .select('*')
      .eq('user_id', userId)
      .maybeSingle();

    if (!error && data) return data as UserOnboardingState;

    // Create initial state
    const { data: created } = await supabase
      .from('user_onboarding')
      .insert([{ user_id: userId, step: 1, is_completed: false }])
      .select()
      .single();

    if (created) return created as UserOnboardingState;
  } catch (e) {
    console.warn('Onboarding fetch error:', e);
  }

  return MOCK_ONBOARDING;
}

export async function updateOnboardingStep(userId: string, step: number): Promise<void> {
  if (!isSupabaseConfigured) {
    MOCK_ONBOARDING.step = step;
    return;
  }

  await supabase
    .from('user_onboarding')
    .update({ step, updated_at: new Date().toISOString() })
    .eq('user_id', userId);
}

export async function completeOnboarding(userId: string): Promise<void> {
  if (!isSupabaseConfigured) {
    MOCK_ONBOARDING.is_completed = true;
    return;
  }

  await supabase
    .from('user_onboarding')
    .update({ is_completed: true, updated_at: new Date().toISOString() })
    .eq('user_id', userId);
}

export const completeUserOnboarding = completeOnboarding;

