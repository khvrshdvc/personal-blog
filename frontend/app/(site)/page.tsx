import type { Metadata } from "next";
import { Suspense } from "react";
import { BentoSkeleton, HomeBento } from "@/components/home/bento";
import { pageAlternates } from "@/lib/seo";

export const metadata: Metadata = {
  title: "Utkirbek",
  alternates: pageAlternates("/"),
};

export default function Home() {
  return (
    <div className="wrapx">
      <section className="hero">
        <h1>
          Backend dasturchi. Ustoz. <span>Fikrlarini yozib boradigan odam.</span>
        </h1>
        <p>PHP va Laravelʼni mustaqil oʻrganyapman, bolalarga dars beraman va bu yerda fikrlarimni yozaman.</p>
      </section>
      <Suspense fallback={<BentoSkeleton />}>
        <HomeBento />
      </Suspense>
    </div>
  );
}
