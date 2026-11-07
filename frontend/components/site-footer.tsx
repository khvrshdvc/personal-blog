import { Suspense } from "react";
import { requestTime } from "@/lib/time";

async function Year() {
  const now = await requestTime();

  return <>{new Date(now).getFullYear()} </>;
}

export function SiteFooter() {
  return (
    <footer className="foot">
      © <Suspense fallback={null}><Year /></Suspense>
      Utkirbek · Toshkent
    </footer>
  );
}
