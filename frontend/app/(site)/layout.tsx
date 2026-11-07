import { Suspense } from "react";
import { Glow } from "@/components/glow";
import { NavLoader } from "@/components/nav/nav-loader";
import { PaletteProvider } from "@/components/nav/palette-context";
import { NavFallback } from "@/components/nav/site-nav";
import { ToastProvider } from "@/components/toast";

export default function SiteLayout({ children }: LayoutProps<"/">) {
  return (
    <ToastProvider>
    <PaletteProvider>
      <div id="site">
        <a className="skip" href="#view">
          Asosiy qismga oʻtish
        </a>
        <Suspense fallback={<NavFallback />}>
          <NavLoader />
        </Suspense>
        <main id="view">{children}</main>
        <footer className="foot">© 2026 Utkirbek · Toshkent</footer>
      </div>
      <Glow />
    </PaletteProvider>
    </ToastProvider>
  );
}
