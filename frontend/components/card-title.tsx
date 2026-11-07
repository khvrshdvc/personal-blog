import { Icon } from "@/components/icon";
import type { IconName } from "@/lib/icon-paths";

export function CardTitle({ icon, children }: { icon: IconName; children: React.ReactNode }) {
  return (
    <span className="ck">
      <Icon name={icon} size={14} />
      {children}
    </span>
  );
}
