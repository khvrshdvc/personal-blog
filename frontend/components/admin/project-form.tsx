"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useRef, useState } from "react";
import { useConfirm } from "@/components/admin/confirm";
import { useAdmin } from "@/components/admin/session";
import { CardTitle } from "@/components/card-title";
import { Icon } from "@/components/icon";
import { useNotify } from "@/components/toast";
import { AdminApiError, adminApi } from "@/lib/admin/api";
import type { Project } from "@/lib/admin/types";
import { initials } from "@/lib/format";

interface FormState {
  name: string;
  repo: string;
  description: string;
  stack: string;
  demo: string;
}

function toState(project: Project | null): FormState {
  return {
    name: project?.name ?? "",
    repo: project?.repo ?? "",
    description: project?.description ?? "",
    stack: (project?.stack ?? []).join(", "),
    demo: project?.demo_url ?? "",
  };
}

function ProjectFormBody({ project }: { project: Project | null }) {
  const router = useRouter();
  const notify = useNotify();
  const confirm = useConfirm();
  const { upsertProject, removeProject } = useAdmin();
  const [state, setState] = useState<FormState>(() => toState(project));
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [busy, setBusy] = useState(false);

  const stackList = state.stack
    .split(",")
    .map((item) => item.trim())
    .filter(Boolean);

  function set<K extends keyof FormState>(key: K, value: FormState[K]) {
    setState((current) => ({ ...current, [key]: value }));
  }

  async function submit(event: React.FormEvent) {
    event.preventDefault();

    if (busy) return;

    setBusy(true);
    setErrors({});

    const input = {
      name: state.name,
      repo: state.repo,
      description: state.description,
      stack: stackList,
      demo_url: state.demo,
    };

    try {
      const saved = project ? await adminApi.saveProject(project.id, input) : await adminApi.createProject(input);
      upsertProject(saved);
      setState(toState(saved));
      notify("Loyiha saqlandi", { label: "Saytda koʻrish", href: "/loyihalar" });

      if (!project) router.replace(`/admin/projects/${saved.id}`);
    } catch (error) {
      if (error instanceof AdminApiError && error.status === 422) {
        setErrors(error.errors);
      } else {
        notify("Saqlab boʻlmadi");
      }
    } finally {
      setBusy(false);
    }
  }

  async function remove() {
    if (!project) return;

    const ok = await confirm({ title: "Loyiha oʻchirilsinmi?", message: "Bu amalni qaytarib boʻlmaydi." });

    if (!ok) return;

    try {
      await adminApi.deleteProject(project.id);
      removeProject(project.id);
      notify("Loyiha oʻchirildi");
      router.replace("/admin/projects");
    } catch {
      notify("Oʻchirib boʻlmadi");
    }
  }

  return (
    <>
      <header className="d-nav">
        <div className="d-l">
          <Link className="gbtn glass cap only-sm" href="/admin/projects">
            <Icon name="chevL" />
            <span>Loyihalar</span>
          </Link>
          <b className="d-title">{project ? project.name : "Yangi loyiha"}</b>
        </div>
        <div className="d-c" />
        <div className="d-r">
          {project ? (
            <button className="capbtn plain sm" type="button" onClick={remove}>
              Oʻchirish
            </button>
          ) : null}
          <button className="capbtn filled sm" type="submit" form="pform" disabled={busy}>
            Saqlash
          </button>
        </div>
      </header>
      <div className="d-scroll">
        <div className="pf">
          <form className="c pf-f" id="pform" onSubmit={submit} noValidate>
            <CardTitle icon="folder">{project ? "Loyiha maʼlumotlari" : "Yangi loyiha"}</CardTitle>
            <label className="fld">
              <span>Nomi</span>
              <input
                className="inp"
                name="name"
                value={state.name}
                onChange={(event) => set("name", event.target.value)}
                placeholder="Masalan, CRM tizimi"
                autoComplete="off"
                autoFocus={!project}
              />
            </label>
            <p className="err">{errors.name?.[0] ?? ""}</p>
            <label className="fld">
              <span>GitHub repozitoriy</span>
              <span className="ipx">
                <em>github.com/</em>
                <input
                  className="inp"
                  name="repo"
                  value={state.repo}
                  onChange={(event) => set("repo", event.target.value)}
                  placeholder="repo-nomi yoki owner/repo"
                  spellCheck={false}
                  autoComplete="off"
                />
              </span>
            </label>
            <p className="hint">Actions natijalari, til va yangilanish vaqti shu repodan olinadi.</p>
            <label className="fld">
              <span>Tavsif</span>
              <textarea
                className="inp"
                name="description"
                value={state.description}
                onChange={(event) => set("description", event.target.value)}
                rows={3}
                maxLength={220}
                placeholder="Loyiha nima qiladi — bir-ikki gap"
              />
            </label>
            <p className="err">{errors.description?.[0] ?? ""}</p>
            <div className="two">
              <label className="fld">
                <span>Stack · vergul bilan</span>
                <input
                  className="inp"
                  name="stack"
                  value={state.stack}
                  onChange={(event) => set("stack", event.target.value)}
                  placeholder="Laravel, MySQL, Redis"
                  autoComplete="off"
                />
              </label>
              <label className="fld">
                <span>Demo havola · ixtiyoriy</span>
                <input
                  className="inp"
                  name="demo_url"
                  value={state.demo}
                  onChange={(event) => set("demo", event.target.value)}
                  type="url"
                  placeholder="https://"
                  autoComplete="off"
                />
              </label>
            </div>
            <p className="err">{errors.stack?.[0] ?? errors["stack.0"]?.[0] ?? ""}</p>
          </form>
          <aside className="pf-p">
            <p className="pf-l">
              <Icon name="eye" /> Saytda shunday koʻrinadi
            </p>
            <div className="c app">
              <div className="app-h">
                <span className="app-m" aria-hidden="true">
                  {initials(state.name || "?")}
                </span>
                <span className="app-n">{state.name.trim() || "Loyiha nomi"}</span>
                {state.repo.trim() || state.demo.trim() ? (
                  <span className="app-ls">
                    {state.repo.trim() ? (
                      <span className="app-l">
                        Kod <Icon name="arrowUR" />
                      </span>
                    ) : null}
                    {state.demo.trim() ? (
                      <span className="app-l">
                        Demo <Icon name="arrowUR" />
                      </span>
                    ) : null}
                  </span>
                ) : null}
              </div>
              <p className="app-d">{state.description.trim() || "Qisqa tavsif"}</p>
              {stackList.length ? <span className="app-s">{stackList.join(" · ")}</span> : null}
            </div>
          </aside>
        </div>
      </div>
    </>
  );
}

export function ProjectForm({ id }: { id: number | null }) {
  const router = useRouter();
  const notify = useNotify();
  const { projects } = useAdmin();
  const handled = useRef(false);
  const project = id === null ? null : (projects?.find((item) => item.id === id) ?? null);
  const ready = id === null || project !== null;

  useEffect(() => {
    if (id !== null && projects !== null && project === null && !handled.current) {
      handled.current = true;
      notify("Loyiha topilmadi");
      router.replace("/admin/projects");
    }
  }, [id, projects, project, notify, router]);

  if (!ready) return <div className="a-empty" aria-busy="true" />;

  return <ProjectFormBody key={project?.id ?? "new"} project={project} />;
}
