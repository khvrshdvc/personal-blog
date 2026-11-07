import { Suspense } from "react";
import { Glow } from "@/components/glow";
import { NavLoader } from "@/components/nav/nav-loader";
import { PaletteProvider } from "@/components/nav/palette-context";
import { SiteNav } from "@/components/nav/site-nav";

export default function SiteLayout({ children }: LayoutProps<"/">) {
  return (
    <PaletteProvider>
      <div id="site">
        <a className="skip" href="#view">
          Asosiy qismga oʻtish
        </a>
        <Suspense fallback={<SiteNav counts={null} open={null} />}>
          <NavLoader />
        </Suspense>
        <main id="view">{children}</main>
        <footer className="foot">© 2026 Utkirbek · Toshkent</footer>
      </div>
      <Glow />
    </PaletteProvider>
  );
}
