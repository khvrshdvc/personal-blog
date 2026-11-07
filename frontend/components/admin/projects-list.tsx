"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { ListPane, MinusIcon } from "@/components/admin/list-pane";
import { useAdmin } from "@/components/admin/session";
import { Icon } from "@/components/icon";
import { useNotify } from "@/components/toast";
import { adminApi } from "@/lib/admin/api";
import { initials } from "@/lib/format";

export function ProjectsList({ activeId }: { activeId: number | null }) {
  const { projects, removeProject } = useAdmin();
  const notify = useNotify();
  const router = useRouter();

  async function remove(id: number) {
    try {
      await adminApi.deleteProject(id);
    } catch {
      notify("Oʻchirib boʻlmadi");

      return;
    }

    removeProject(id);
    notify("Loyiha oʻchirildi");

    if (activeId === id) router.replace("/admin/projects");
  }

  return (
    <ListPane title="Loyihalar" count={projects?.length ?? null} newHref="/admin/projects/new" newLabel="Yangi loyiha">
      {({ editing, revealed, reveal }) => (
        <>
          {(projects ?? []).map((project) => {
            const active = project.id === activeId;

            return (
              <li key={project.id} className={`ali${revealed === project.id ? " reveal" : ""}`} data-active={active ? "1" : undefined}>
                <button
                  className="minus"
                  type="button"
                  aria-label={`${project.name} — oʻchirish`}
                  onClick={() => reveal(revealed === project.id ? null : project.id)}
                >
                  <MinusIcon />
                </button>
                <Link className={`arow pj${active ? " on" : ""}`} href={`/admin/projects/${project.id}`} aria-current={active ? "true" : undefined}>
                  <span className="app-m" aria-hidden="true">
                    {initials(project.name)}
                  </span>
                  <span className="pj-b">
                    <b className="rt">{project.name}</b>
                    <span className="pj-r">{project.repo || "repo koʻrsatilmagan"}</span>
                  </span>
                  <span className="pj-c">
                    <Icon name="chevR" />
                  </span>
                </Link>
                <button className="delbtn" type="button" onClick={() => remove(project.id)} tabIndex={editing ? 0 : -1}>
                  Oʻchirish
                </button>
              </li>
            );
          })}
          {projects !== null && projects.length === 0 ? <li className="a-empty-l">Hali loyiha yoʻq</li> : null}
        </>
      )}
    </ListPane>
  );
}
