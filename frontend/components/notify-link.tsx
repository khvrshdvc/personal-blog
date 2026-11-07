"use client";

import { useNotify } from "@/components/toast";

interface NotifyLinkProps {
  text: string;
  action?: { label: string; href: string };
  className?: string;
  ariaLabel?: string;
  children: React.ReactNode;
}

export function NotifyLink({ text, action, className, ariaLabel, children }: NotifyLinkProps) {
  const notify = useNotify();

  return (
    <a
      href="#"
      className={className}
      aria-label={ariaLabel}
      onClick={(event) => {
        event.preventDefault();
        notify(text, action);
      }}
    >
      {children}
    </a>
  );
}
