import type { Metadata, Viewport } from "next";
import { Inter, JetBrains_Mono, Source_Serif_4 } from "next/font/google";
import "./globals.css";
import { ThemeScript } from "@/components/theme-script";

const inter = Inter({
  variable: "--font-inter",
  subsets: ["latin", "latin-ext", "cyrillic"],
  weight: ["200", "300", "400", "500", "600", "700"],
});

const serif = Source_Serif_4({
  variable: "--font-serif-src",
  subsets: ["latin", "latin-ext"],
  style: ["normal", "italic"],
  weight: ["400", "600", "700"],
});

const mono = JetBrains_Mono({
  variable: "--font-mono-src",
  subsets: ["latin", "latin-ext"],
  weight: ["400", "500"],
});

export const metadata: Metadata = {
  title: "Utkirbek",
  description: "Utkirbek — oʻrganish, ustozlik, til va hayot haqida fikrlar. Alohida boʻlimda dasturlash.",
};

export const viewport: Viewport = {
  themeColor: "#09090b",
  viewportFit: "cover",
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html
      lang="uz"
      data-theme="dark"
      suppressHydrationWarning
      className={`${inter.variable} ${serif.variable} ${mono.variable}`}
    >
      <head>
        <ThemeScript />
      </head>
      <body>
        <div id="bgfx" aria-hidden="true" />
        {children}
      </body>
    </html>
  );
}
