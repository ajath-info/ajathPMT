import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Layers, Building, CheckCircle2, XCircle, ArrowRight, ShieldCheck } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { useOrganization } from '../../context/OrganizationContext';
import { fetchInvitationByToken, acceptInvitation } from '../../services/organizationService';
import { OrganizationInvitation } from '../../types';
import { Button } from '../../components/common/Button';
import { Badge } from '../../components/common/Badge';

export function AcceptInvitePage() {
  const { token } = useParams<{ token: string }>();
  const { profile } = useAuth();
  const { refreshOrganizationData, switchOrganization, declineUserInvitation } = useOrganization();
  const navigate = useNavigate();

  const [invitation, setInvitation] = useState<OrganizationInvitation | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [isAccepting, setIsAccepting] = useState(false);

  useEffect(() => {
    if (!token) return;
    fetchInvitationByToken(token)
      .then((inv) => {
        setInvitation(inv);
        setLoading(false);
      })
      .catch(() => {
        setError('Invitation token is invalid or expired.');
        setLoading(false);
      });
  }, [token]);

  const handleAccept = async () => {
    if (!token || !profile) {
      navigate('/login');
      return;
    }

    setIsAccepting(true);
    const { error: err, organizationId } = await acceptInvitation(token, profile.id);
    setIsAccepting(false);

    if (err) {
      setError(err.message || 'Failed to accept invitation.');
    } else {
      await refreshOrganizationData();
      if (organizationId) {
        switchOrganization(organizationId);
      }
      navigate('/dashboard');
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-slate-950">
        <div className="text-center space-y-3">
          <div className="w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center animate-bounce mx-auto shadow-lg shadow-brand-500/30">
            <Layers className="w-6 h-6" />
          </div>
          <p className="text-sm font-semibold text-slate-600 dark:text-slate-400">Loading Invitation...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 via-slate-100 to-indigo-50/30 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950/20 p-4 sm:p-6">
      <div className="w-full max-w-md space-y-6">
        <div className="text-center space-y-2">
          <div className="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 via-indigo-600 to-violet-500 text-white flex items-center justify-center mx-auto shadow-xl shadow-brand-500/25">
            <Building className="w-8 h-8" />
          </div>
          <h1 className="text-2xl font-extrabold text-slate-900 dark:text-slate-100">
            Workspace Invitation
          </h1>
          <p className="text-sm text-slate-500 dark:text-slate-400">
            You've been invited to join a company workspace
          </p>
        </div>

        <div className="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200/80 dark:border-slate-800 space-y-6">
          {error ? (
            <div className="text-center space-y-4">
              <XCircle className="w-12 h-12 text-rose-500 mx-auto" />
              <p className="text-sm font-semibold text-rose-600 dark:text-rose-400">{error}</p>
              <Button variant="outline" className="w-full" onClick={() => navigate('/dashboard')}>
                Return to Home Dashboard
              </Button>
            </div>
          ) : (
            <>
              <div className="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 space-y-3">
                <div className="flex items-center justify-between">
                  <span className="text-xs font-bold uppercase tracking-wider text-slate-400">Organization</span>
                  <Badge variant="brand">{invitation?.role || 'MEMBER'}</Badge>
                </div>
                <h3 className="text-lg font-extrabold text-slate-900 dark:text-slate-100">
                  {invitation?.organization?.name || 'Company Workspace'}
                </h3>

                {invitation?.personal_message && (
                  <div className="p-3 rounded-xl bg-white dark:bg-slate-900 text-xs italic text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800">
                    "{invitation.personal_message}"
                  </div>
                )}
              </div>

              <div className="space-y-3">
                <Button
                  variant="primary"
                  className="w-full py-2.5"
                  isLoading={isAccepting}
                  onClick={handleAccept}
                  rightIcon={<ArrowRight className="w-4 h-4" />}
                >
                  Accept & Join Workspace
                </Button>

                <Button
                  variant="ghost"
                  className="w-full text-slate-500"
                  onClick={async () => {
                    if (token) {
                      await declineUserInvitation(token);
                    }
                    navigate('/dashboard');
                  }}
                >
                  Decline Invitation
                </Button>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
