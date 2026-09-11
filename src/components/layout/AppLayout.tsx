import React, { useState, useEffect } from 'react';
import { Outlet, useLocation, useNavigate } from 'react-router-dom';
import { BasecampHeader } from './BasecampHeader';
import { BasecampBottomDock } from './BasecampBottomDock';
import { CommandMenu } from './CommandMenu';
import { PingsDrawer } from '../modals/PingsDrawer';
import { HeyNotificationDrawer } from '../modals/HeyNotificationDrawer';
import { DockDrawers, DockDrawerType } from '../modals/DockDrawers';
import { SupportModal } from '../modals/SupportModal';
import { BasecampProfileModal } from '../modals/BasecampProfileModal';
import { useAuth } from '../../context/AuthContext';

export function AppLayout() {
  const location = useLocation();
  const navigate = useNavigate();
  const isHomePage = location.pathname === '/' || location.pathname === '/dashboard';
  const isEnrollmentsPage = location.pathname.includes('enrollments') || location.pathname.includes('invite');
  const isAdminlandPage = location.pathname.includes('adminland') || location.pathname.includes('account');
  const isBasecampDocPage =
    location.pathname.includes('everything') ||
    location.pathname.includes('reports') ||
    location.pathname.includes('calendar') ||
    location.pathname.includes('activity');
  const isCleanCanvas =
    isHomePage ||
    location.pathname.startsWith('/projects') ||
    isEnrollmentsPage ||
    isAdminlandPage ||
    isBasecampDocPage;

  const [isCommandMenuOpen, setIsCommandMenuOpen] = useState(false);

  // Basecamp Drawers & Modals state
  const [isPingsOpen, setIsPingsOpen] = useState(false);
  const [isHeyOpen, setIsHeyOpen] = useState(false);
  const [isSupportOpen, setIsSupportOpen] = useState(false);
  const [isProfileModalOpen, setIsProfileModalOpen] = useState(false);
  const [activeDockDrawer, setActiveDockDrawer] = useState<DockDrawerType>(null);

  const { isMockMode, user, signInAsDemo, signInAsFreshAdmin } = useAuth();

  // Support ?as=admin or ?as=fresh-admin quick switch parameter
  useEffect(() => {
    const params = new URLSearchParams(location.search);
    const asRole = params.get('as') || params.get('role');
    if (asRole) {
      const lower = asRole.toLowerCase();
      if (lower.includes('fresh') || lower.includes('clean')) {
        signInAsFreshAdmin();
        params.delete('as');
        params.delete('role');
        const remaining = params.toString();
        navigate(`${location.pathname}${remaining ? `?${remaining}` : ''}`, { replace: true });
        return;
      }
      const upper = asRole.toUpperCase();
      if (['OWNER', 'ADMIN', 'MEMBER', 'CLIENT'].includes(upper)) {
        signInAsDemo(upper as any);
        params.delete('as');
        params.delete('role');
        const remaining = params.toString();
        navigate(`${location.pathname}${remaining ? `?${remaining}` : ''}`, { replace: true });
      }
    }
  }, [location.search, location.pathname, signInAsDemo, signInAsFreshAdmin, navigate]);

  // Listen to Shift+J or Ctrl+K or Cmd+K
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      // Ctrl+K or Cmd+K
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        setIsCommandMenuOpen((prev) => !prev);
      }
      // Shift+J (Basecamp signature shortcut)
      if (e.shiftKey && e.key.toLowerCase() === 'j') {
        // Don't trigger if user is actively typing in an input or textarea
        const tag = (e.target as HTMLElement)?.tagName?.toLowerCase();
        if (tag !== 'input' && tag !== 'textarea') {
          e.preventDefault();
          setIsCommandMenuOpen(true);
        }
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);



  return (
    <div className="min-h-screen bg-slate-50/70 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col font-sans selection:bg-blue-500 selection:text-white">
      {/* Modern Top Masthead */}
      <BasecampHeader
        onOpenPings={() => setIsPingsOpen((prev) => !prev)}
        onOpenHey={() => setIsHeyOpen((prev) => !prev)}
        isPingsOpen={isPingsOpen}
        onClosePings={() => setIsPingsOpen(false)}
        onOpenSearch={() => setIsCommandMenuOpen(true)}
        onOpenProfile={() => setIsProfileModalOpen(true)}
        onOpenSupport={() => setIsSupportOpen(true)}
        onOpenDrawer={(type) => setActiveDockDrawer(type)}
      />

      {/* Main Workspace Canvas */}
      <div className="flex-1 flex flex-col min-w-0 transition-all duration-300">
        <main
          className={`flex-1 w-full mx-auto animate-in fade-in duration-200 ${
            isCleanCanvas ? 'pt-2 pb-12 px-2 sm:px-4' : 'p-4 sm:p-6 lg:p-8 max-w-7xl pb-16'
          }`}
        >
          <Outlet
            context={{
              openJumpMenu: () => setIsCommandMenuOpen(true),
              openDockDrawer: (type: DockDrawerType) => setActiveDockDrawer(type),
              openSupport: () => setIsSupportOpen(true),
              openProfile: () => setIsProfileModalOpen(true),
              openPings: () => setIsPingsOpen((prev) => !prev),
              openHey: () => setIsHeyOpen((prev) => !prev),
            }}
          />
        </main>
      </div>

      {/* Basecamp Bottom Dock */}
      <BasecampBottomDock
        activeDrawer={activeDockDrawer}
        onOpenDrawer={(type) => setActiveDockDrawer(type)}
        onOpenSupport={() => setIsSupportOpen(true)}
        onOpenProfile={() => setIsProfileModalOpen(true)}
      />

      {/* Global Drawers & Modals */}
      <PingsDrawer
        isOpen={isPingsOpen}
        onClose={() => setIsPingsOpen(false)}
      />

      <HeyNotificationDrawer
        isOpen={isHeyOpen}
        onClose={() => setIsHeyOpen(false)}
      />

      <DockDrawers
        activeDrawer={activeDockDrawer}
        onClose={() => setActiveDockDrawer(null)}
      />

      <SupportModal
        isOpen={isSupportOpen}
        onClose={() => setIsSupportOpen(false)}
      />

      <BasecampProfileModal
        isOpen={isProfileModalOpen}
        onClose={() => setIsProfileModalOpen(false)}
      />

      <CommandMenu
        isOpen={isCommandMenuOpen}
        onClose={() => setIsCommandMenuOpen(false)}
      />
    </div>
  );
}
