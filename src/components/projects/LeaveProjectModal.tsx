import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { LogOut, AlertCircle } from 'lucide-react';
import { Modal } from '../common/Modal';
import { Button } from '../common/Button';
import { useToast } from '../../context/ToastContext';
import { useAuth } from '../../context/AuthContext';
import { useProject } from '../../context/ProjectContext';
import { removeProjectMember } from '../../services/projectService';

interface LeaveProjectModalProps {
  isOpen: boolean;
  onClose: () => void;
  projectId: string;
  projectName: string;
}

export function LeaveProjectModal({
  isOpen,
  onClose,
  projectId,
  projectName,
}: LeaveProjectModalProps) {
  const navigate = useNavigate();
  const { profile } = useAuth();
  const { refreshProjects } = useProject();
  const { addToast } = useToast();
  const [isProcessing, setIsProcessing] = useState(false);

  const handleConfirmLeave = async () => {
    setIsProcessing(true);
    try {
      if (profile?.id) {
        await removeProjectMember(projectId, profile.id);
      }
      await refreshProjects();
      addToast(`You have left "${projectName}"`, 'info');
      onClose();
      navigate('/dashboard');
    } catch (err) {
      addToast('Failed to leave project', 'error');
    } finally {
      setIsProcessing(false);
    }
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title={`Leave ${projectName}?`} maxWidth="sm">
      <div className="space-y-4 pt-1">
        <div className="p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800 flex items-start gap-2.5 text-xs text-amber-800 dark:text-amber-200 leading-relaxed">
          <AlertCircle className="w-4 h-4 shrink-0 mt-0.5 text-amber-600" />
          <span>
            You will no longer receive notifications, pings, or activity updates for this project. An administrator can add you back anytime.
          </span>
        </div>

        <div className="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
          <Button variant="outline" size="sm" onClick={onClose} disabled={isProcessing}>
            Stay on project
          </Button>
          <Button
            variant="danger"
            size="sm"
            onClick={handleConfirmLeave}
            disabled={isProcessing}
          >
            {isProcessing ? 'Leaving...' : 'Leave project'}
          </Button>
        </div>
      </div>
    </Modal>
  );
}
