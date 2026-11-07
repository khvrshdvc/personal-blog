import { Seg } from "@/components/seg";

const TABS = [
  { key: "yozuvlar", label: "Yozuvlar", href: "/dasturlash" },
  { key: "loyihalar", label: "Loyihalar", href: "/loyihalar" },
  { key: "faollik", label: "Faollik", href: "/faollik" },
];

export function DevShell({ tab, children }: { tab: string; children: React.ReactNode }) {
  return (
    <div className="wrapx">
      <header className="ph">
        <p className="eyebrow">Dasturlash</p>
        <h1>
          Backend yoʻlim. <span>PHP, Laravel va SQL.</span>
        </h1>
      </header>
      <div className="seg-wrap">
        <Seg items={TABS} active={tab} label="Dasturlash boʻlimi" />
      </div>
      <div id="dbody">{children}</div>
    </div>
  );
}
