export interface Edit {
  value: string;
  start: number;
  end: number;
}

export type MarkdownAction = "h2" | "bold" | "italic" | "quote" | "list" | "link" | "image" | "code" | "block";

function lineBounds(value: string, start: number, end: number): [number, number] {
  const from = value.lastIndexOf("\n", start - 1) + 1;
  const next = value.indexOf("\n", end);

  return [from, next === -1 ? value.length : next];
}

function wrap(value: string, start: number, end: number, before: string, after: string, placeholder: string): Edit {
  const selected = value.slice(start, end) || placeholder;
  const text = `${before}${selected}${after}`;

  return {
    value: value.slice(0, start) + text + value.slice(end),
    start: start + before.length,
    end: start + before.length + selected.length,
  };
}

function prefixLines(value: string, start: number, end: number, prefix: string): Edit {
  const [from, to] = lineBounds(value, start, end);
  const block = value.slice(from, to);
  const lines = block.split("\n");
  const all = lines.every((line) => line.startsWith(prefix));
  const next = lines.map((line) => (all ? line.slice(prefix.length) : `${prefix}${line}`)).join("\n");

  return { value: value.slice(0, from) + next + value.slice(to), start: from, end: from + next.length };
}

export function applyMarkdown(action: MarkdownAction, value: string, start: number, end: number): Edit {
  switch (action) {
    case "h2":
      return prefixLines(value, start, end, "## ");
    case "quote":
      return prefixLines(value, start, end, "> ");
    case "list":
      return prefixLines(value, start, end, "- ");
    case "bold":
      return wrap(value, start, end, "**", "**", "qalin");
    case "italic":
      return wrap(value, start, end, "*", "*", "kursiv");
    case "code":
      return wrap(value, start, end, "`", "`", "kod");
    case "block":
      return wrap(value, start, end, "```\n", "\n```", "kod");
    case "link": {
      const selected = value.slice(start, end) || "havola";
      const text = `[${selected}](https://)`;
      const urlStart = start + selected.length + 3;

      return { value: value.slice(0, start) + text + value.slice(end), start: urlStart, end: urlStart + 8 };
    }
    case "image": {
      const text = "![tavsif](https://)";
      const urlStart = start + 11;

      return { value: value.slice(0, start) + text + value.slice(end), start: urlStart, end: urlStart + 8 };
    }
  }
}
