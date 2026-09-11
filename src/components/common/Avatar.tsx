import React from 'react';
import { getInitials, cn } from '../../lib/utils';

export interface AvatarProps {
  src?: string;
  name: string;
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
  className?: string;
  status?: 'online' | 'offline' | 'busy';
}

export function Avatar({ src, name, size = 'md', className, status }: AvatarProps) {
  const sizeClasses = {
    xs: 'w-6 h-6 text-[10px]',
    sm: 'w-8 h-8 text-xs',
    md: 'w-10 h-10 text-sm',
    lg: 'w-12 h-12 text-base',
    xl: 'w-16 h-16 text-xl',
  };

  const statusClasses = {
    online: 'bg-emerald-500',
    offline: 'bg-slate-400',
    busy: 'bg-rose-500',
  };

  return (
    <div className="relative inline-block select-none">
      {src ? (
        <img
          src={src}
          alt={name}
          className={cn(
            'rounded-full object-cover ring-2 ring-white dark:ring-slate-900',
            sizeClasses[size],
            className
          )}
        />
      ) : (
        <div
          className={cn(
            'rounded-full bg-gradient-to-tr from-brand-600 to-indigo-500 text-white font-semibold flex items-center justify-center ring-2 ring-white dark:ring-slate-900 shadow-sm',
            sizeClasses[size],
            className
          )}
        >
          {getInitials(name)}
        </div>
      )}

      {status && (
        <span
          className={cn(
            'absolute bottom-0 right-0 rounded-full ring-2 ring-white dark:ring-slate-900',
            size === 'xs' || size === 'sm' ? 'w-2 h-2' : 'w-3 h-3',
            statusClasses[status]
          )}
        />
      )}
    </div>
  );
}
