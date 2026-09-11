import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Layers, Lock, CheckCircle2, ArrowRight } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { Button } from '../../components/common/Button';
import { Input } from '../../components/common/Input';

export function ResetPasswordPage() {
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const { updatePassword } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (password.length < 6) {
      setError('Password must be at least 6 characters.');
      return;
    }
    if (password !== confirmPassword) {
      setError('Passwords do not match.');
      return;
    }

    setError(null);
    setIsLoading(true);

    const { error: err } = await updatePassword(password);
    setIsLoading(false);

    if (err) {
      setError(err.message || 'Failed to update password.');
    } else {
      setSuccess(true);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 via-slate-100 to-indigo-50/30 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950/20 p-4 sm:p-6">
      <div className="w-full max-w-md space-y-6">
        <div className="text-center space-y-2">
          <div className="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 via-indigo-600 to-violet-500 text-white flex items-center justify-center mx-auto shadow-xl shadow-brand-500/25">
            <Layers className="w-8 h-8" />
          </div>
          <h1 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100">
            Set New Password
          </h1>
          <p className="text-sm text-slate-500 dark:text-slate-400">
            Enter a strong new password for your account
          </p>
        </div>

        <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200/80 dark:border-slate-800 space-y-5">
          {success ? (
            <div className="text-center py-4 space-y-4">
              <div className="w-12 h-12 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto">
                <CheckCircle2 className="w-7 h-7" />
              </div>
              <h3 className="text-lg font-bold text-slate-900 dark:text-slate-100">
                Password Successfully Updated
              </h3>
              <p className="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                Your password has been changed. You can now access your workspace.
              </p>
              <Button
                variant="primary"
                className="w-full py-2.5"
                onClick={() => navigate('/dashboard')}
                rightIcon={<ArrowRight className="w-4 h-4" />}
              >
                Go to Workspace Dashboard
              </Button>
            </div>
          ) : (
            <>
              {error && (
                <div className="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-xs font-medium text-rose-600 dark:text-rose-400">
                  {error}
                </div>
              )}

              <form onSubmit={handleSubmit} className="space-y-4">
                <Input
                  label="New Password"
                  type="password"
                  placeholder="At least 6 characters"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  leftIcon={<Lock className="w-4 h-4" />}
                  required
                />

                <Input
                  label="Confirm New Password"
                  type="password"
                  placeholder="Repeat new password"
                  value={confirmPassword}
                  onChange={(e) => setConfirmPassword(e.target.value)}
                  leftIcon={<Lock className="w-4 h-4" />}
                  required
                />

                <Button
                  type="submit"
                  variant="primary"
                  className="w-full py-2.5"
                  isLoading={isLoading}
                  rightIcon={<ArrowRight className="w-4 h-4" />}
                >
                  Update Account Password
                </Button>
              </form>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
