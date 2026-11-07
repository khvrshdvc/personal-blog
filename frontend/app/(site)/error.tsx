"use client";

import { ErrorPage } from "@/components/error-page";

export default function SiteError({ reset }: { error: Error; reset: () => void }) {
  return (
    <ErrorPage
      heading="Nimadir xato ketdi"
      text="Kutilmagan xatolik yuz berdi. Birozdan keyin qayta urinib koʻring."
      actionLabel="Qayta urinish"
      onAction={reset}
    />
  );
}
