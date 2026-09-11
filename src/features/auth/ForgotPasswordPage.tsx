import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { Layers, Mail, CheckCircle2, ArrowLeft, ArrowRight } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { Button } from '../../components/common/Button';
import { Input } from '../../components/common/Input';

export function ForgotPasswordPage() {
  const [email, setEmail] = useState('');
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const { resetPassword } = useAuth();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email) {
      setError('Please enter your email address.');
      return;
    }

    setError(null);
    setIsLoading(true);

    const { error: err } = await resetPassword(email);
    setIsLoading(false);

    if (err) {
      setError(err.message || 'Failed to send password reset email.');
    } else {
      setSubmitted(true);
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
            Reset Password
          </h1>
          <p className="text-sm text-slate-500 dark:text-slate-400">
            Enter your account email to receive reset instructions
          </p>
        </div>

        <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200/80 dark:border-slate-800 space-y-5">
          {submitted ? (
            <div className="text-center py-4 space-y-4">
              <div className="w-12 h-12 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto">
                <CheckCircle2 className="w-7 h-7" />
              </div>
              <h3 className="text-lg font-bold text-slate-900 dark:text-slate-100">
                Password Reset Sent
              </h3>
              <p className="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                If an account exists for <span className="font-semibold text-slate-900 dark:text-slate-200">{email}</span>, you will receive password reset instructions shortly.
              </p>
              <Link to="/login" className="inline-block w-full">
                <Button variant="outline" className="w-full" leftIcon={<ArrowLeft className="w-4 h-4" />}>
                  Back to Sign In
                </Button>
              </Link>
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
                  label="Work Email"
                  type="email"
                  placeholder="alex@worksphere.io"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  leftIcon={<Mail className="w-4 h-4" />}
                  required
                />

                <Button
                  type="submit"
                  variant="primary"
                  className="w-full py-2.5"
                  isLoading={isLoading}
                  rightIcon={<ArrowRight className="w-4 h-4" />}
                >
                  Send Password Reset Link
                </Button>
              </form>

              <div className="text-center pt-2 text-xs text-slate-500 dark:text-slate-400">
                Remember your password?{' '}
                <Link to="/login" className="font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                  Sign In
                </Link>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
