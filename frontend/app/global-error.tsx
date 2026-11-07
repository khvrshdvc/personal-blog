"use client";

import "./globals.css";
import { ErrorPage } from "@/components/error-page";

export default function GlobalError({ reset }: { error: Error; reset: () => void }) {
  return (
    <html lang="uz" data-theme="dark">
      <body>
        <div id="site">
          <ErrorPage
            heading="Nimadir xato ketdi"
            text="Kutilmagan xatolik yuz berdi. Birozdan keyin qayta urinib koʻring."
            actionLabel="Qayta urinish"
            onAction={reset}
          />
        </div>
      </body>
    </html>
  );
}
