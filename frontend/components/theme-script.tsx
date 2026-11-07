import { THEME_SCRIPT } from "@/lib/theme";

export function ThemeScript() {
  return <script dangerouslySetInnerHTML={{ __html: THEME_SCRIPT }} />;
}
