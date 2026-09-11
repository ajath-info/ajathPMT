import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { Layers, Mail, Lock, Eye, EyeOff, Sparkles, ArrowRight } from 'lucide-react';
import { useAuth, DEMO_USERS } from '../../context/AuthContext';
import { Button } from '../../components/common/Button';
import { Input } from '../../components/common/Input';
import { OrgRole } from '../../types';

export function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const { signIn, signInAsDemo, signInAsFreshOwner } = useAuth();
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  // Auto sign-in if role is provided in the query string
  useEffect(() => {
    const roleParam = searchParams.get('as') || searchParams.get('role');
    const targetPath = searchParams.get('to') || searchParams.get('redirect') || '/dashboard';
    if (roleParam) {
      const lower = roleParam.toLowerCase();
      if (lower.includes('owner') && (lower.includes('fresh') || lower.includes('clean'))) {
        signInAsFreshOwner();
        navigate(targetPath);
        return;
      }
      const upper = roleParam.toUpperCase();
      if (['OWNER', 'ADMIN', 'MEMBER', 'CLIENT'].includes(upper)) {
        signInAsDemo(upper as OrgRole);
        navigate(targetPath);
      }
    }
  }, [searchParams, signInAsDemo, signInAsFreshOwner, navigate]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email || !password) {
      setError('Please fill in both email and password.');
      return;
    }
    setError(null);
    setIsLoading(true);

    const { error: err } = await signIn(email, password);
    setIsLoading(false);

    if (err) {
      setError(err.message || 'Invalid email or password credentials.');
    } else {
      navigate('/dashboard');
    }
  };

  const handleDemoLogin = (role: OrgRole) => {
    signInAsDemo(role);
    navigate('/dashboard');
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 via-slate-100 to-indigo-50/30 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950/20 p-4 sm:p-6">
      <div className="w-full max-w-md space-y-6">
        {/* Logo & Header */}
        <div className="text-center space-y-2">
          <div className="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 via-indigo-600 to-violet-500 text-white flex items-center justify-center mx-auto shadow-xl shadow-brand-500/25">
            <Layers className="w-8 h-8" />
          </div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Sign in to Ajath PMT
          </h1>
          <p className="text-sm text-slate-500 dark:text-slate-400">
            Enterprise project management & team collaboration hub
          </p>
        </div>

        {/* Card Form */}
        <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200/80 dark:border-slate-800 space-y-5">
          {error && (
            <div className="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-xs font-medium text-rose-600 dark:text-rose-400">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <Input
              label="Email Address"
              type="email"
              placeholder="alex@worksphere.io"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              leftIcon={<Mail className="w-4 h-4" />}
              required
            />

            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                  Password
                </label>
                <Link
                  to="/forgot-password"
                  className="text-xs font-medium text-brand-600 dark:text-brand-400 hover:underline"
                >
                  Forgot password?
                </Link>
              </div>
              <Input
                type={showPassword ? 'text' : 'password'}
                placeholder="••••••••"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                leftIcon={<Lock className="w-4 h-4" />}
                rightIcon={
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="focus:outline-none"
                  >
                    {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                }
                required
              />
            </div>

            <Button
              type="submit"
              variant="primary"
              className="w-full py-2.5"
              isLoading={isLoading}
              rightIcon={<ArrowRight className="w-4 h-4" />}
            >
              Sign In to Workspace
            </Button>
          </form>

          {/* Quick Demo Persona Switcher */}
          <div className="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
            <div className="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-400">
              <span className="flex items-center gap-1 text-brand-600 dark:text-brand-400">
                <Sparkles className="w-3.5 h-3.5" /> Instant Demo Access
              </span>
              <span>1-Click Test</span>
            </div>

            <div className="grid grid-cols-2 gap-2">
              {(['OWNER', 'ADMIN', 'MEMBER', 'CLIENT'] as OrgRole[]).map((role) => (
                <button
                  key={role}
                  type="button"
                  onClick={() => handleDemoLogin(role)}
                  className="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-brand-500 dark:hover:border-brand-500 bg-slate-50/50 dark:bg-slate-800/40 hover:bg-brand-50/50 dark:hover:bg-brand-950/40 text-left transition-all group"
                >
                  <p className="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover:text-brand-600 dark:group-hover:text-brand-400">
                    {role} Persona
                  </p>
                  <p className="text-[10px] text-slate-400 truncate">{DEMO_USERS[role].profile.full_name}</p>
                </button>
              ))}
            </div>

            <button
              type="button"
              onClick={() => {
                signInAsFreshOwner();
                navigate('/dashboard');
              }}
              className="w-full p-2.5 rounded-xl border border-emerald-200 dark:border-emerald-800/80 bg-emerald-50/60 dark:bg-emerald-950/40 hover:bg-emerald-100/70 text-emerald-800 dark:text-emerald-300 text-left transition-all flex items-center justify-between group cursor-pointer"
            >
              <div>
                <p className="text-xs font-bold flex items-center gap-1.5">
                  <Sparkles className="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                  <span>Fresh Owner (Start from Scratch)</span>
                </p>
                <p className="text-[10px] text-emerald-600/80 font-normal">0 pre-existing companies, 0 members, 0 projects</p>
              </div>
              <ArrowRight className="w-3.5 h-3.5 text-emerald-600 group-hover:translate-x-0.5 transition-transform" />
            </button>
          </div>

          <div className="text-center pt-2 text-xs text-slate-500 dark:text-slate-400">
            Don't have an account?{' '}
            <Link to="/signup" className="font-semibold text-brand-600 dark:text-brand-400 hover:underline">
              Create an Organization
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
