import type { SearchItem } from "@/lib/api/types";
import type { IconName } from "@/lib/icon-paths";

export interface PaletteItem {
  title: string;
  href: string;
  icon: IconName;
  group: string;
  note: string;
}

export const PALETTE_PAGES: { title: string; href: string; icon: IconName }[] = [
  { title: "Asosiy", href: "/", icon: "grid" },
  { title: "Fikrlar", href: "/fikrlar", icon: "bookOpen" },
  { title: "Dasturlash", href: "/dasturlash", icon: "code" },
  { title: "Loyihalar", href: "/loyihalar", icon: "folder" },
  { title: "GitHub faollik", href: "/faollik", icon: "branch" },
  { title: "Men haqimda", href: "/men", icon: "person" },
];

export function buildPaletteItems(query: string, results: SearchItem[]): PaletteItem[] {
  const needle = query.trim().toLowerCase();
  const pages: PaletteItem[] = PALETTE_PAGES.filter((page) => needle === "" || page.title.toLowerCase().includes(needle)).map(
    (page) => ({ ...page, group: "Sahifalar", note: "" }),
  );

  const posts = results.filter((item) => item.type === "post");
  const projects = results.filter((item) => item.type === "project");

  const toPost = (item: SearchItem, group: string): PaletteItem => ({
    title: item.title,
    href: item.path,
    icon: item.category === "dev" ? "code" : "bookOpen",
    group,
    note: item.subtitle,
  });

  if (needle === "") {
    return [...posts.map((item) => toPost(item, "Soʻnggi yozuvlar")), ...pages];
  }

  return [
    ...posts.filter((item) => item.category === "fikr").map((item) => toPost(item, "Fikrlar")),
    ...posts.filter((item) => item.category === "dev").map((item) => toPost(item, "Dasturlash")),
    ...projects.map((item) => ({
      title: item.subtitle ? `${item.title} — ${item.subtitle}` : item.title,
      href: item.path,
      icon: "folder" as const,
      group: "Loyihalar",
      note: "",
    })),
    ...pages,
  ];
}
