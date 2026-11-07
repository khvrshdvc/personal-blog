"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useCallback, useEffect, useLayoutEffect, useRef, useState } from "react";
import { useConfirm } from "@/components/admin/confirm";
import { applyMarkdown, type MarkdownAction } from "@/components/admin/markdown-tools";
import { useAdmin } from "@/components/admin/session";
import { Icon } from "@/components/icon";
import { Seg } from "@/components/seg";
import { useNotify } from "@/components/toast";
import { AdminApiError, adminApi } from "@/lib/admin/api";
import {
  CATEGORY_TAGS,
  STATE_CLASS,
  STATE_LABEL,
  countWords,
  coverSrc,
  emptyFields,
  postState,
  postView,
  readMinutes,
} from "@/lib/admin/post-model";
import type { AdminPost, PostFields } from "@/lib/admin/types";
import { fmtLong } from "@/lib/format";
import { enhanceCodeBlocks } from "@/lib/post-html";

type SaveState = "idle" | "dirty" | "saving" | "saved" | "error";

interface PostEditorProps {
  id: number | null;
  category?: "fikr" | "dev";
}

const TOOLS: { action: MarkdownAction; label: string; title: string; icon?: Parameters<typeof Icon>[0]["name"]; text?: string }[] = [
  { action: "h2", label: "Sarlavha", title: "Sarlavha", text: "H2" },
  { action: "bold", label: "Qalin", title: "Qalin (Ctrl+B)", text: "B" },
  { action: "italic", label: "Kursiv", title: "Kursiv (Ctrl+I)", text: "I" },
  { action: "quote", label: "Iqtibos", title: "Iqtibos", icon: "quote" },
  { action: "list", label: "Roʻyxat", title: "Roʻyxat", icon: "list" },
  { action: "link", label: "Havola", title: "Havola", icon: "link" },
  { action: "image", label: "Rasm", title: "Rasm", icon: "image" },
  { action: "code", label: "Kod", title: "Kod", icon: "code" },
  { action: "block", label: "Kod bloki", title: "Kod bloki", text: "{ }" },
];

export function PostEditor({ id, category = "fikr" }: PostEditorProps) {
  const router = useRouter();
  const notify = useNotify();
  const confirm = useConfirm();
  const { upsertPost, removePost } = useAdmin();

  const [loaded, setLoaded] = useState(id === null);
  const [post, setPost] = useState<AdminPost | null>(null);
  const [fields, setFields] = useState<PostFields>(() => emptyFields(category));
  const [mode, setMode] = useState<"write" | "preview">("write");
  const [sheet, setSheet] = useState(false);
  const [saveState, setSaveState] = useState<SaveState>("idle");
  const [saveError, setSaveError] = useState("");
  const [slugTouched, setSlugTouched] = useState(id !== null);
  const [slugNote, setSlugNote] = useState("");
  const [previewHtml, setPreviewHtml] = useState("");
  const [dragging, setDragging] = useState(false);

  const fieldsRef = useRef(fields);
  const postRef = useRef<AdminPost | null>(null);
  const idRef = useRef<number | null>(id);
  const slugTouchedRef = useRef(slugTouched);
  const rev = useRef(0);
  const savedRev = useRef(0);
  const timer = useRef<ReturnType<typeof setTimeout> | null>(null);
  const queue = useRef<Promise<unknown>>(Promise.resolve());
  const mounted = useRef(true);
  const saveRef = useRef<(() => Promise<void>) | null>(null);
  const titleRef = useRef<HTMLTextAreaElement>(null);
  const bodyRef = useRef<HTMLTextAreaElement>(null);
  const selection = useRef<{ start: number; end: number } | null>(null);

  const state = post ? postState(post) : "draft";
  const published = post?.status === "published";
  const hasPending = Boolean(post?.pending);
  const words = countWords(fields.body);
  const minutes = readMinutes(words);
  const cover = coverSrc(fields.cover);

  useEffect(() => {
    fieldsRef.current = fields;
  }, [fields]);

  useEffect(() => {
    slugTouchedRef.current = slugTouched;
  }, [slugTouched]);

  const adopt = useCallback(
    (next: AdminPost, resetFields: boolean) => {
      postRef.current = next;
      setPost(next);
      upsertPost(next);

      if (resetFields) {
        const view = postView(next);
        fieldsRef.current = view;
        setFields(view);
      }
    },
    [upsertPost],
  );

  useEffect(() => {
    if (id === null) return;

    let cancelled = false;

    adminApi
      .post(id)
      .then((loadedPost) => {
        if (cancelled) return;
        adopt(loadedPost, true);
        setSlugTouched(true);
        setLoaded(true);
      })
      .catch(() => {
        notify("Yozuv topilmadi");
        router.replace("/admin/posts");
      });

    return () => {
      cancelled = true;
    };
  }, [id, adopt, notify, router]);

  useLayoutEffect(() => {
    for (const el of [titleRef.current, bodyRef.current]) {
      if (!el) continue;

      el.style.height = "auto";
      el.style.height = `${el.scrollHeight}px`;
    }
  }, [fields.title, fields.body, mode, loaded]);

  useLayoutEffect(() => {
    if (selection.current && bodyRef.current) {
      bodyRef.current.setSelectionRange(selection.current.start, selection.current.end);
      selection.current = null;
    }
  });

  const save = useCallback((): Promise<void> => {
    const run = async () => {
      const target = rev.current;

      if (target === savedRev.current && idRef.current !== null) return;

      const current = fieldsRef.current;
      const empty = current.title.trim() === "" && current.body.trim() === "";

      if (idRef.current === null && empty) {
        savedRev.current = target;
        setSaveState("idle");

        return;
      }

      setSaveState("saving");
      setSaveError("");

      try {
        if (idRef.current === null) {
          const created = await adminApi.createPost({ category: current.category, title: current.title, body: current.body });
          idRef.current = created.id;
          window.history.replaceState(null, "", `/admin/posts/${created.id}`);
        }

        const draft = !postRef.current || postRef.current.status !== "published";
        const payload = { ...current, slug: draft && !slugTouchedRef.current ? "" : current.slug };
        const saved = await adminApi.savePost(idRef.current, payload);

        postRef.current = saved;
        setPost(saved);
        upsertPost(saved);
        savedRev.current = Math.max(savedRev.current, target);

        if (saved.status !== "published" && !slugTouchedRef.current) {
          setFields((value) => ({ ...value, slug: saved.slug }));
        }

        const requested = current.slug.trim();
        setSlugNote(saved.status !== "published" && slugTouchedRef.current && requested !== "" && saved.slug !== requested ? "Bu havola band edi — raqam qoʻshildi" : "");
        setSaveState(rev.current === target ? "saved" : "dirty");
      } catch (error) {
        setSaveState("error");
        setSaveError(error instanceof AdminApiError && error.status === 422 ? error.first() : "Saqlab boʻlmadi — qayta urinib koʻring");
      }

      if (rev.current !== savedRev.current && mounted.current) {
        timer.current = setTimeout(() => void saveRef.current?.(), 600);
      }
    };

    queue.current = queue.current.then(run, run);

    return queue.current as Promise<void>;
  }, [upsertPost]);

  useEffect(() => {
    saveRef.current = save;
  }, [save]);

  useEffect(() => {
    mounted.current = true;

    return () => {
      mounted.current = false;
      if (timer.current) clearTimeout(timer.current);
    };
  }, []);

  const schedule = useCallback(() => {
    rev.current += 1;
    setSaveState("dirty");
    if (timer.current) clearTimeout(timer.current);
    timer.current = setTimeout(() => void save(), 600);
  }, [save]);

  const flush = useCallback(async () => {
    if (timer.current) clearTimeout(timer.current);
    await save();
  }, [save]);

  const patch = useCallback(
    (changes: Partial<PostFields>) => {
      setFields((value) => {
        const next = { ...value, ...changes };
        fieldsRef.current = next;

        return next;
      });
      schedule();
    },
    [schedule],
  );

  function setCategory(next: "fikr" | "dev") {
    if (next === fields.category) return;

    const tags = CATEGORY_TAGS[next] as readonly string[];
    patch({ category: next, tag: tags.includes(fields.tag) ? fields.tag : tags[0] });
  }

  function toolbar(action: MarkdownAction) {
    const el = bodyRef.current;

    if (!el) return;

    const edit = applyMarkdown(action, fields.body, el.selectionStart, el.selectionEnd);
    selection.current = { start: edit.start, end: edit.end };
    patch({ body: edit.value });
    el.focus();
  }

  function onBodyKey(event: React.KeyboardEvent<HTMLTextAreaElement>) {
    if (!(event.ctrlKey || event.metaKey)) return;

    if (event.key.toLowerCase() === "b") {
      event.preventDefault();
      toolbar("bold");
    } else if (event.key.toLowerCase() === "i") {
      event.preventDefault();
      toolbar("italic");
    }
  }

  const reportError = useCallback(
    (error: unknown) => {
      if (error instanceof AdminApiError) {
        if (error.errors.slug) setSheet(true);
        notify(error.first());
      } else {
        notify("Amalni bajarib boʻlmadi");
      }
    },
    [notify],
  );

  const publish = useCallback(async () => {
    await flush();

    if (idRef.current === null) {
      notify("Avval sarlavha va matn yozing");

      return;
    }

    const current = postRef.current;

    try {
      if (current?.status === "published") {
        if (!current.pending) {
          notify("Oʻzgarish yoʻq");

          return;
        }

        const applied = await adminApi.applyPost(idRef.current);
        adopt(applied, true);
        notify("Yozuv yangilandi", { label: "Saytda koʻrish", href: `/post/${applied.slug}` });
      } else {
        const result = await adminApi.publishPost(idRef.current);
        adopt(result, true);
        setSlugTouched(true);
        notify("Yozuv chop etildi", { label: "Saytda koʻrish", href: `/post/${result.slug}` });
      }

      setSaveState("saved");
    } catch (error) {
      reportError(error);
    }
  }, [flush, notify, adopt, reportError]);

  useEffect(() => {
    function onKey(event: KeyboardEvent) {
      if (!(event.ctrlKey || event.metaKey)) return;

      if (event.key.toLowerCase() === "s") {
        event.preventDefault();
        void flush();
      } else if (event.key === "Enter") {
        event.preventDefault();
        void publish();
      }
    }

    document.addEventListener("keydown", onKey);

    return () => document.removeEventListener("keydown", onKey);
  }, [flush, publish]);

  useEffect(() => {
    if (!sheet) return;

    function onKey(event: KeyboardEvent) {
      if (event.key === "Escape") setSheet(false);
    }

    document.addEventListener("keydown", onKey);

    return () => document.removeEventListener("keydown", onKey);
  }, [sheet]);

  useEffect(() => {
    if (mode !== "preview") return;

    const controller = new AbortController();
    const timeout = setTimeout(async () => {
      try {
        setPreviewHtml((await adminApi.preview(fields.body, controller.signal)).html);
      } catch {}
    }, 150);

    return () => {
      clearTimeout(timeout);
      controller.abort();
    };
  }, [mode, fields.body]);

  async function discard() {
    if (idRef.current === null) return;

    try {
      adopt(await adminApi.discardPending(idRef.current), true);
      setSaveState("saved");
      notify("Oʻzgarishlar bekor qilindi");
    } catch (error) {
      reportError(error);
    }
  }

  async function unpublish() {
    if (idRef.current === null) return;

    const ok = await confirm({
      title: "Qoralamaga olinsinmi?",
      message: "Yozuv saytdan yashiriladi, lekin oʻchmaydi.",
      ok: "Qoralamaga olish",
    });

    if (!ok) return;

    try {
      adopt(await adminApi.unpublishPost(idRef.current), true);
      setSlugTouched(false);
      setSheet(false);
      notify("Yozuv qoralamaga olindi");
    } catch (error) {
      reportError(error);
    }
  }

  async function remove() {
    if (idRef.current === null) return;

    const ok = await confirm({ title: "Yozuv oʻchirilsinmi?", message: "Bu amalni qaytarib boʻlmaydi." });

    if (!ok) return;

    try {
      await adminApi.deletePost(idRef.current);
      removePost(idRef.current);
      notify("Yozuv oʻchirildi");
      router.replace("/admin/posts");
    } catch (error) {
      reportError(error);
    }
  }

  async function uploadCover(file: File | undefined) {
    if (!file) return;

    try {
      const result = await adminApi.upload(file);
      patch({ cover: result.path });
    } catch (error) {
      reportError(error);
    }
  }

  const saveText =
    saveState === "saving"
      ? "Saqlanmoqda…"
      : saveState === "dirty"
        ? "Oʻzgarish bor…"
        : saveState === "saved"
          ? "Saqlandi"
          : saveState === "error"
            ? saveError
            : "";

  if (!loaded) {
    return <div className="a-empty" aria-busy="true" />;
  }

  return (
    <>
      <header className="d-nav">
        <div className="d-l">
          <Link className="gbtn glass cap only-sm" href="/admin/posts">
            <Icon name="chevL" />
            <span>Yozuvlar</span>
          </Link>
          <span className={`pill ${STATE_CLASS[state]}`}>{STATE_LABEL[state]}</span>
          <span className="save-st" aria-live="polite">
            {saveText}
          </span>
        </div>
        <div className="d-c">
          <Seg
            items={[
              { key: "write", label: "Yozish" },
              { key: "preview", label: "Koʻrinish" },
            ]}
            active={mode}
            label="Rejim"
            onPick={(key) => setMode(key === "preview" ? "preview" : "write")}
          />
        </div>
        <div className="d-r">
          <button className="gbtn glass" type="button" onClick={() => setSheet(true)} aria-label="Yozuv sozlamalari" title="Yozuv sozlamalari">
            <Icon name="sliders" />
          </button>
          {published && post ? (
            <a className="gbtn glass" href={`/post/${post.slug}`} aria-label="Saytda koʻrish" title="Saytda koʻrish">
              <Icon name="eye" />
            </a>
          ) : null}
          {published && hasPending ? (
            <button className="capbtn plain sm" type="button" onClick={discard}>
              Bekor qilish
            </button>
          ) : null}
          {published ? (
            <button className="capbtn filled sm" type="button" disabled={!hasPending} onClick={publish}>
              Yangilash
            </button>
          ) : (
            <button className="capbtn filled sm" type="button" onClick={publish}>
              Chop etish
            </button>
          )}
        </div>
      </header>
      <div className="d-scroll" id="edscroll">
        <div className={`ed ${fields.category}`} id="edwrap">
          {mode === "write" ? (
            <>
              <label className="sr" htmlFor="ft">
                Sarlavha
              </label>
              <textarea
                ref={titleRef}
                className="ed-title"
                id="ft"
                rows={1}
                placeholder="Sarlavha"
                value={fields.title}
                onChange={(event) => patch({ title: event.target.value.replace(/\n/g, " ") })}
                onKeyDown={(event) => {
                  if (event.key === "Enter") {
                    event.preventDefault();
                    bodyRef.current?.focus();
                  }
                }}
              />
              <div className="ed-meta">
                <Seg
                  items={[
                    { key: "fikr", label: "Fikr" },
                    { key: "dev", label: "Dasturlash" },
                  ]}
                  active={fields.category}
                  label="Boʻlim"
                  onPick={(key) => setCategory(key === "dev" ? "dev" : "fikr")}
                />
                <label className="menubtn">
                  <span className="sr">Teg</span>
                  <select id="ftag" value={fields.tag} onChange={(event) => patch({ tag: event.target.value })}>
                    {CATEGORY_TAGS[fields.category].map((tag) => (
                      <option key={tag} value={tag}>
                        {tag}
                      </option>
                    ))}
                  </select>
                  <Icon name="chevUD" />
                </label>
                <span className="ed-words">
                  {words} soʻz · {minutes} daqiqa
                </span>
              </div>
              <div className="ed-tools glass" role="toolbar" aria-label="Formatlash">
                {TOOLS.map((tool, index) => (
                  <span key={tool.action} style={{ display: "contents" }}>
                    {index === 5 ? <span className="tsep" /> : null}
                    <button className="tb" type="button" onClick={() => toolbar(tool.action)} title={tool.title} aria-label={tool.label}>
                      {tool.icon ? <Icon name={tool.icon} /> : tool.action === "italic" ? <i>I</i> : tool.text}
                    </button>
                  </span>
                ))}
              </div>
              <label className="sr" htmlFor="fb">
                Matn
              </label>
              <textarea
                ref={bodyRef}
                className="ed-body"
                id="fb"
                placeholder="Fikringizni yozing…"
                value={fields.body}
                onChange={(event) => patch({ body: event.target.value })}
                onKeyDown={onBodyKey}
              />
            </>
          ) : (
            <div className={`ed-prev ${fields.category === "fikr" ? "fikr" : "dev"}`}>
              <div className="r-meta">
                <span>{fields.tag}</span>
                <span>·</span>
                <span>{fmtLong(fields.published_at)}</span>
                <span>·</span>
                <span>{minutes} daqiqa oʻqish</span>
              </div>
              <h1 className="r-title">{fields.title || "Sarlavhasiz"}</h1>
              {fields.excerpt ? <p className="r-lead">{fields.excerpt}</p> : null}
              {cover ? (
                <div className="r-cover">
                  <div className="cover">
                    {/* eslint-disable-next-line @next/next/no-img-element */}
                    <img src={cover} alt="" />
                  </div>
                </div>
              ) : null}
              <div
                className="prose"
                dangerouslySetInnerHTML={{ __html: previewHtml ? enhanceCodeBlocks(previewHtml) : "<p><em>Hali hech narsa yozilmagan.</em></p>" }}
              />
            </div>
          )}
        </div>
      </div>
      {sheet ? (
        <div
          className="sheet-back"
          onMouseDown={(event) => {
            if (event.target === event.currentTarget) setSheet(false);
          }}
        >
          <div className="sheet" role="dialog" aria-modal="true" aria-labelledby="sh-t">
            <div className="grabber" aria-hidden="true" />
            <div className="sheet-h">
              <span />
              <h2 id="sh-t">Yozuv sozlamalari</h2>
              <button className="gbtn tint" type="button" onClick={() => setSheet(false)} aria-label="Tayyor" title="Tayyor">
                <Icon name="check" />
              </button>
            </div>
            <div className="sheet-b">
              <div className="fgroup">
                <h3 className="fg-h">Havola</h3>
                <div className="list">
                  <label className="row frow">
                    <span className="pfx">/post/</span>
                    <input
                      id="fslug"
                      value={fields.slug}
                      onChange={(event) => {
                        setSlugTouched(true);
                        patch({ slug: event.target.value });
                      }}
                      spellCheck={false}
                      autoComplete="off"
                      aria-label="Havola"
                    />
                  </label>
                </div>
                <p className="fg-f err">{slugNote}</p>
                <p className="fg-f">{slugTouched ? "" : "Sarlavhadan avtomatik yaratiladi"}</p>
              </div>
              <div className="fgroup">
                <h3 className="fg-h">Qisqacha tavsif</h3>
                <div className="list">
                  <label className="row frow">
                    <textarea
                      id="fex"
                      rows={3}
                      maxLength={200}
                      value={fields.excerpt}
                      onChange={(event) => patch({ excerpt: event.target.value })}
                      placeholder="Boʻsh qolsa, matndan avtomatik olinadi"
                      aria-label="Qisqacha tavsif"
                    />
                  </label>
                </div>
                <p className="fg-f">{fields.excerpt.length} / 200</p>
              </div>
              <div className="fgroup">
                <div className="list">
                  <label className="row frow">
                    <span className="rt">Sana</span>
                    <input type="date" value={fields.published_at} onChange={(event) => patch({ published_at: event.target.value })} aria-label="Sana" />
                  </label>
                </div>
              </div>
              <div className="fgroup">
                <h3 className="fg-h">Muqova · ixtiyoriy</h3>
                <div
                  className={`sh-cover${dragging ? " drag" : ""}`}
                  onDragOver={(event) => {
                    event.preventDefault();
                    setDragging(true);
                  }}
                  onDragLeave={() => setDragging(false)}
                  onDrop={(event) => {
                    event.preventDefault();
                    setDragging(false);
                    void uploadCover(event.dataTransfer.files[0]);
                  }}
                >
                  {cover ? (
                    <div className="cover">
                      {/* eslint-disable-next-line @next/next/no-img-element */}
                      <img src={cover} alt="" />
                    </div>
                  ) : (
                    <div className="cover-empty">
                      <Icon name="image" />
                      <span>Muqova yoʻq</span>
                    </div>
                  )}
                </div>
                <div className="sh-cbtns">
                  <label className="capbtn gray sm">
                    <Icon name="upload" /> Rasm yuklash
                    <input
                      className="sr"
                      type="file"
                      accept="image/jpeg,image/png,image/webp"
                      onChange={(event) => {
                        void uploadCover(event.target.files?.[0]);
                        event.target.value = "";
                      }}
                    />
                  </label>
                  <button className="capbtn plain sm" type="button" onClick={() => patch({ cover: "" })}>
                    Olib tashlash
                  </button>
                </div>
                <p className="fg-f">Rasmni shu yerga tortib tashlash ham mumkin.</p>
              </div>
              <div className="fgroup">
                <div className="list">
                  <div className="row">
                    <span className="rt" style={{ flex: 1 }}>
                      Soʻzlar
                    </span>
                    <span className="rd">{words}</span>
                  </div>
                  <div className="row">
                    <span className="rt" style={{ flex: 1 }}>
                      Oʻqish vaqti
                    </span>
                    <span className="rd">{minutes} daqiqa</span>
                  </div>
                </div>
              </div>
              {post ? (
                <div className="fgroup">
                  <div className="list">
                    {published ? (
                      <button className="row rbtn" type="button" onClick={unpublish}>
                        Qoralamaga olish
                      </button>
                    ) : null}
                    <button className="row rbtn red" type="button" onClick={remove}>
                      Yozuvni oʻchirish
                    </button>
                  </div>
                  {published ? <p className="fg-f">Qoralamaga olinsa, yozuv saytdan yashiriladi, lekin oʻchmaydi.</p> : null}
                </div>
              ) : null}
            </div>
          </div>
        </div>
      ) : null}
    </>
  );
}
