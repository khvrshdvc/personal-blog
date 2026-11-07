"use client";

import { Icon } from "@/components/icon";
import { useNotify } from "@/components/toast";

export function ShareButton({ title }: { title: string }) {
  const notify = useNotify();

  async function share() {
    const url = window.location.href;

    if (navigator.share) {
      try {
        await navigator.share({ title, url });
      } catch {}

      return;
    }

    try {
      await navigator.clipboard.writeText(url);
      notify("Havola nusxalandi");
    } catch {
      notify("Havolani nusxalab boʻlmadi");
    }
  }

  return (
    <button className="ibtn" type="button" onClick={share} aria-label="Ulashish" title="Ulashish">
      <Icon name="share" />
    </button>
  );
}
