import React, { useState, useRef, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { ChevronLeft, Check } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useToast } from '../../context/ToastContext';

export function ProfilePage() {
  const { profile, updateProfile } = useAuth();
  const { currentOrganization } = useOrganization();
  const { addToast } = useToast();
  const navigate = useNavigate();

  const [name, setName] = useState('');
  const [title, setTitle] = useState('');
  const [location, setLocation] = useState('');
  const [currentStatus, setCurrentStatus] = useState('');
  const [email, setEmail] = useState('');
  const [avatarUrl, setAvatarUrl] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (profile) {
      setName(profile.full_name || 'Claire Watson');
      setTitle(profile.job_title || '');
      setLocation(profile.location || '');
      setCurrentStatus(profile.status || '');
      setEmail(profile.email || 'claire.client@partner.com');
      setAvatarUrl(profile.avatar_url || '');
    } else {
      setName('Claire Watson');
      setEmail('claire.client@partner.com');
    }
  }, [profile]);

  const orgName = currentOrganization?.name || 'Ajath Infotech Pvt Ltd';

  const getInitials = (fullName: string) => {
    const parts = fullName.trim().split(/\s+/);
    if (parts.length >= 2) {
      return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return fullName.slice(0, 2).toUpperCase() || 'CW';
  };

  const handlePhotoUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
      const result = event.target?.result as string;
      setAvatarUrl(result);
      addToast('Profile photo ready to save!', 'info');
    };
    reader.readAsDataURL(file);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim() || !email.trim()) return;

    setSubmitting(true);
    try {
      await updateProfile({
        full_name: name.trim(),
        job_title: title.trim(),
        location: location.trim(),
        status: currentStatus.trim(),
        avatar_url: avatarUrl,
      });
      addToast('Your profile changes have been saved!', 'success');
    } catch (err) {
      addToast('Failed to save profile changes', 'error');
    } finally {
      setSubmitting(false);
    }
  };

  const handleRemoveAccount = () => {
    if (confirm(`Are you sure you want to leave ${orgName}? You will lose access to all projects and documents.`)) {
      addToast('Account removal request submitted to account admin.', 'info');
    }
  };

  return (
    <div className="w-full min-h-[calc(100vh-7rem)] flex flex-col justify-start items-center pt-2 sm:pt-4">
      {/* Signature Basecamp White Document Card */}
      <div className="w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-6 animate-in fade-in duration-200">
        
        {/* Top Breadcrumb */}
        <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800/80">
          <div className="flex items-center gap-2 text-sm">
            <button
              onClick={() => navigate('/dashboard')}
              className="font-bold text-slate-800 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:underline transition-colors cursor-pointer flex items-center gap-1.5"
            >
              <ChevronLeft className="w-4 h-4 text-slate-400" />
              Home
            </button>
            <span className="text-slate-300 dark:text-slate-600 font-normal">/</span>
            <span className="font-extrabold text-slate-900 dark:text-white">My Profile</span>
          </div>
        </div>

        {/* 1. Top Avatar & Photo Section */}
        <div className="flex flex-col items-center pt-2 pb-4">
          <div className="relative">
            {avatarUrl ? (
              <img
                src={avatarUrl}
                alt={name}
                className="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover shadow-sm border-2 border-white dark:border-slate-800"
              />
            ) : (
              <div className="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-[#8A3FFC] text-white flex items-center justify-center font-black text-3xl sm:text-4xl shadow-sm select-none">
                {getInitials(name)}
              </div>
            )}
          </div>

          <input
            type="file"
            ref={fileInputRef}
            onChange={handlePhotoUpload}
            accept="image/*"
            className="hidden"
          />

          <button
            type="button"
            onClick={() => fileInputRef.current?.click()}
            className="mt-3.5 px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-2xs transition-colors cursor-pointer"
          >
            Upload a photo...
          </button>

          {/* Hand-drawn Playful Callout Note with curved arrow matching Basecamp screenshot */}
          <div className="relative mt-3 flex items-center justify-center">
            <svg
              className="absolute -top-4 -left-6 w-8 h-8 text-amber-400 dark:text-amber-500"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <path d="M4 18c2-6 8-10 16-10" />
              <path d="M16 4l4 4-4 4" />
            </svg>

            <div className="border border-dashed border-amber-300 dark:border-amber-700 bg-amber-50/70 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200 text-xs px-3.5 py-1.5 rounded-2xl flex items-center gap-1 font-medium shadow-2xs">
              <span>Upload a photo to show your 🤠!</span>
            </div>
          </div>
        </div>

        {/* 2. Main Form Fields */}
        <form onSubmit={handleSubmit} className="space-y-4 pt-2">
          <div>
            <label className="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
              Name <span className="font-normal text-slate-400 ml-1">required</span>
            </label>
            <input
              type="text"
              required
              value={name}
              onChange={(e) => setName(e.target.value)}
              className="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0c66e4]/20 focus:border-[#0c66e4] transition-all"
            />
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
              Title at {orgName}
            </label>
            <input
              type="text"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              className="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0c66e4]/20 focus:border-[#0c66e4] transition-all"
            />
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
              Location
            </label>
            <input
              type="text"
              value={location}
              onChange={(e) => setLocation(e.target.value)}
              className="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0c66e4]/20 focus:border-[#0c66e4] transition-all"
            />
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
              Current status
            </label>
            <input
              type="text"
              value={currentStatus}
              onChange={(e) => setCurrentStatus(e.target.value)}
              className="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0c66e4]/20 focus:border-[#0c66e4] transition-all"
            />
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
              Email address <span className="font-normal text-slate-400 ml-1">required</span>
            </label>
            <input
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0c66e4]/20 focus:border-[#0c66e4] transition-all"
            />
          </div>

          <div className="pt-2 text-xs space-y-1">
            <h4 className="font-bold text-slate-900 dark:text-slate-100">Login info</h4>
            <p className="text-slate-600 dark:text-slate-400">You log in with a password.</p>
            <button
              type="button"
              onClick={() => {
                addToast('2FA and password management settings are active for your account.', 'info');
              }}
              className="text-[#0c66e4] dark:text-blue-400 hover:underline font-medium cursor-pointer"
            >
              Set up 2FA or change your login info here.
            </button>
          </div>

          <div className="pt-2">
            <button
              type="submit"
              disabled={submitting}
              className="w-full py-2.5 rounded-xl bg-[#0c66e4] hover:bg-[#0052cc] text-white font-bold text-sm shadow-xs transition-all cursor-pointer active:scale-99 disabled:opacity-50"
            >
              {submitting ? 'Saving...' : 'Save my changes'}
            </button>
          </div>

          <div className="pt-3 text-xs space-y-1">
            <p className="font-bold text-slate-900 dark:text-slate-100">
              Want to leave the {orgName} Ajath PMT account?
            </p>
            <button
              type="button"
              onClick={handleRemoveAccount}
              className="text-[#0c66e4] dark:text-blue-400 hover:underline font-medium cursor-pointer"
            >
              Remove me from this account...
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
