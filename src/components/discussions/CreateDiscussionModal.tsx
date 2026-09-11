import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

interface CreateDiscussionModalProps {
  isOpen: boolean;
  onClose: () => void;
  projectId: string;
  authorId?: string;
  onSuccess: () => void;
}

export function CreateDiscussionModal({
  isOpen,
  onClose,
  projectId,
}: CreateDiscussionModalProps) {
  const navigate = useNavigate();

  useEffect(() => {
    if (isOpen) {
      onClose();
      navigate(`/projects/${projectId}/messages/new`);
    }
  }, [isOpen, projectId, onClose, navigate]);

  return null;
}
