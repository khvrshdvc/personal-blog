import { Suspense } from "react";
import { Glow } from "@/components/glow";
import { NavLoader } from "@/components/nav/nav-loader";
import { PaletteProvider } from "@/components/nav/palette-context";
import { SiteFooter } from "@/components/site-footer";
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
        <SiteFooter />
      </div>
      <Glow />
    </PaletteProvider>
    </ToastProvider>
  );
}
