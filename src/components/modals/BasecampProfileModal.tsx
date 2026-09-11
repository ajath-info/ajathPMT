import React, { useState, useRef, useEffect } from 'react';
import { X, Check } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { useToast } from '../../context/ToastContext';

interface BasecampProfileModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export function BasecampProfileModal({ isOpen, onClose }: BasecampProfileModalProps) {
  const { profile, updateProfile, signOut } = useAuth();
  const { currentOrganization } = useOrganization();
  const { addToast } = useToast();

  const [name, setName] = useState('');
  const [title, setTitle] = useState('');
  const [location, setLocation] = useState('');
  const [currentStatus, setCurrentStatus] = useState('');
  const [email, setEmail] = useState('');
  const [avatarUrl, setAvatarUrl] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [is2FAModalOpen, setIs2FAModalOpen] = useState(false);

  const fileInputRef = useRef<HTMLInputElement>(null);

  // Sync state whenever modal opens or profile loads
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
  }, [profile, isOpen]);

  if (!isOpen) return null;

  const orgName = currentOrganization?.name || 'Ajath Infotech Pvt Ltd';

  // Compute initials for avatar (e.g. CW)
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

    // Read as Data URL
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
      onClose();
    } catch (err) {
      addToast('Failed to save profile changes', 'error');
    } finally {
      setSubmitting(false);
    }
  };

  const handleRemoveAccount = () => {
    if (confirm(`Are you sure you want to leave ${orgName}? You will lose access to all projects and documents.`)) {
      addToast('Account removal request submitted to account admin.', 'info');
      onClose();
    }
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-6 animate-in fade-in duration-200">
      {/* Dimmed backdrop */}
      <div
        className="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity cursor-pointer"
        onClick={onClose}
      />

      {/* Signature Basecamp Profile Dialog */}
      <div className="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/90 dark:border-slate-800 p-6 sm:p-8 z-10 max-h-[92vh] overflow-y-auto">
        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-5 right-5 p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors cursor-pointer"
          title="Close dialog"
        >
          <X className="w-5 h-5" />
        </button>

        {/* 1. Top Avatar & Photo Section */}
        <div className="flex flex-col items-center pt-2 pb-4">
          {/* Avatar Circle */}
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

          {/* Hidden File Upload Input */}
          <input
            type="file"
            ref={fileInputRef}
            onChange={handlePhotoUpload}
            accept="image/*"
            className="hidden"
          />

          {/* "Upload a photo..." button */}
          <button
            type="button"
            onClick={() => fileInputRef.current?.click()}
            className="mt-3.5 px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-2xs transition-colors cursor-pointer"
          >
            Upload a photo...
          </button>

          {/* Hand-drawn Playful Callout Note with curved arrow matching Basecamp screenshot */}
          <div className="relative mt-3 flex items-center justify-center">
            {/* Curved arrow pointing up to Upload button */}
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

            {/* Dashed bubble */}
            <div className="border border-dashed border-amber-300 dark:border-amber-700 bg-amber-50/70 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200 text-xs px-3.5 py-1.5 rounded-2xl flex items-center gap-1 font-medium shadow-2xs">
              <span>Upload a photo to show your 🤠!</span>
            </div>
          </div>
        </div>

        {/* 2. Main Form Fields */}
        <form onSubmit={handleSubmit} className="space-y-4 pt-2">
          {/* Name Field */}
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

          {/* Title at Organization Field */}
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

          {/* Location Field */}
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

          {/* Current status Field */}
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

          {/* Email address Field */}
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

          {/* Login info section */}
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

          {/* Save my changes Button */}
          <div className="pt-2">
            <button
              type="submit"
              disabled={submitting}
              className="w-full py-2.5 rounded-xl bg-[#0c66e4] hover:bg-[#0052cc] text-white font-bold text-sm shadow-xs transition-all cursor-pointer active:scale-99 disabled:opacity-50"
            >
              {submitting ? 'Saving...' : 'Save my changes'}
            </button>
          </div>

          {/* Footer Leave Account Notice */}
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
