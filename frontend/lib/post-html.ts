import { ICON_PATHS } from "@/lib/icon-paths";

const COPY_ICON = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICON_PATHS.copy}</svg>`;

export function enhanceCodeBlocks(html: string): string {
  return html
    .replace(
      /<pre><code(?: class="language-([A-Za-z0-9_+#.-]{1,30})")?>/g,
      (match, lang: string | undefined) =>
        `<div class="code-wrap"><div class="code-bar"><span>${lang ?? "kod"}</span><button type="button" class="copy-code">${COPY_ICON}<span>Nusxalash</span></button></div>${match}`,
    )
    .replace(/<\/code><\/pre>/g, "</code></pre></div>");
}
