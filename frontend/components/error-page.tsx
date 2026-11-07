import Link from "next/link";
import { Icon } from "@/components/icon";

interface ErrorPageProps {
  heading: string;
  text: string;
  actionLabel?: string;
  href?: string;
  onAction?: () => void;
}

export function ErrorPage({ heading, text, actionLabel = "Bosh sahifaga", href = "/", onAction }: ErrorPageProps) {
  return (
    <div className="nf">
      <span className="nf-ic">
        <Icon name="search" />
      </span>
      <h1>{heading}</h1>
      <p>{text}</p>
      {onAction ? (
        <button className="nf-btn" type="button" onClick={onAction}>
          {actionLabel}
        </button>
      ) : (
        <Link className="nf-btn" href={href}>
          {actionLabel}
        </Link>
      )}
    </div>
  );
}
