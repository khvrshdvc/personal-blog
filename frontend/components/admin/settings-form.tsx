"use client";

import { useEffect, useRef, useState } from "react";
import { useAdmin } from "@/components/admin/session";
import { CardTitle } from "@/components/card-title";
import { Icon } from "@/components/icon";
import { ThemeSeg } from "@/components/theme-seg";
import { AdminApiError, adminApi } from "@/lib/admin/api";
import type { Settings } from "@/lib/admin/types";
import { clampPercent } from "@/lib/format";

interface FormSettings {
  bio: string;
  open: boolean;
  github: string;
  now: { k: string; v: string }[];
  learn: { n: string; p: string }[];
  telegram: { u: string; n: string; posts: { t: string; d: string }[] };
}

function pad<T>(list: T[] | null | undefined, size: number, empty: () => T): T[] {
  const items = [...(list ?? [])].slice(0, size);

  while (items.length < size) items.push(empty());

  return items;
}

function toForm(settings: Settings): FormSettings {
  return {
    bio: settings.bio ?? "",
    open: settings.open ?? true,
    github: settings.github ?? "",
    now: pad(settings.now, 4, () => ({ k: "", v: "" })),
    learn: pad((settings.learn ?? []).map((item) => ({ n: item.n, p: String(item.p) })), 3, () => ({ n: "", p: "0" })),
    telegram: {
      u: settings.telegram?.u ?? "",
      n: settings.telegram?.n ?? "",
      posts: pad(settings.telegram?.posts, 2, () => ({ t: "", d: "" })),
    },
  };
}

function toPayload(form: FormSettings): Settings {
  return {
    bio: form.bio,
    open: form.open,
    github: form.github,
    now: form.now,
    learn: form.learn.map((item) => ({ n: item.n, p: clampPercent(Number(item.p)) })),
    telegram: {
      u: form.telegram.u,
      n: form.telegram.n,
      posts: form.telegram.posts.map((post) => ({ t: post.t, d: post.d || "" })),
    },
  };
}

export function SettingsForm() {
  const { logout } = useAdmin();
  const [form, setForm] = useState<FormSettings | null>(null);
  const [state, setState] = useState<"idle" | "dirty" | "saving" | "saved" | "error">("idle");
  const [error, setError] = useState("");
  const formRef = useRef<FormSettings | null>(null);
  const timer = useRef<ReturnType<typeof setTimeout> | null>(null);
  const rev = useRef(0);
  const savedRev = useRef(0);
  const inflight = useRef(false);

  useEffect(() => {
    adminApi
      .settings()
      .then((settings) => {
        const next = toForm(settings);
        formRef.current = next;
        setForm(next);
      })
      .catch(() => setError("Sozlamalarni yuklab boʻlmadi"));

    return () => {
      if (timer.current) clearTimeout(timer.current);
    };
  }, []);

  async function save() {
    if (inflight.current || !formRef.current) return;

    const target = rev.current;

    if (target === savedRev.current) return;

    inflight.current = true;
    setState("saving");
    setError("");

    try {
      await adminApi.saveSettings(toPayload(formRef.current));
      savedRev.current = target;
      setState(rev.current === target ? "saved" : "dirty");
    } catch (failure) {
      setState("error");
      setError(failure instanceof AdminApiError && failure.status === 422 ? failure.first() : "Saqlab boʻlmadi");
    } finally {
      inflight.current = false;
    }

    if (rev.current !== savedRev.current) {
      timer.current = setTimeout(() => void save(), 600);
    }
  }

  function update(change: (current: FormSettings) => FormSettings) {
    setForm((current) => {
      if (!current) return current;

      const next = change(current);
      formRef.current = next;

      return next;
    });
    rev.current += 1;
    setState("dirty");
    if (timer.current) clearTimeout(timer.current);
    timer.current = setTimeout(() => void save(), 600);
  }

  const saveText = state === "saving" ? "Saqlanmoqda…" : state === "dirty" ? "Oʻzgarish bor…" : state === "saved" ? "Saqlandi" : state === "error" ? error : "";

  if (!form) {
    return (
      <div className="d-scroll">
        <div className="adash">
          <header className="ad-h">
            <div>
              <p className="eyebrow">Sozlamalar</p>
              <h1>{error || "Yuklanmoqda…"}</h1>
            </div>
          </header>
        </div>
      </div>
    );
  }

  return (
    <div className="d-scroll">
      <div className="adash">
        <header className="ad-h">
          <div>
            <p className="eyebrow">Sozlamalar</p>
            <h1>
              Sayt va profil. <span>Oʻzgarishlar avtomatik saqlanadi.</span>
            </h1>
          </div>
          <span className={`sv${state === "saved" ? " on" : ""}`} aria-live="polite">
            {saveText}
          </span>
        </header>
        <div className="sgrid">
          <section className="c sc sc-bio">
            <CardTitle icon="person">Men haqimda</CardTitle>
            <label className="fld">
              <span>Bio · boʻsh qator bilan xatboshilarga ajrating</span>
              <textarea
                className="inp"
                rows={9}
                value={form.bio}
                onChange={(event) => update((current) => ({ ...current, bio: event.target.value }))}
                aria-label="Men haqimda matni"
              />
            </label>
          </section>
          <section className="c sc sc-st">
            <CardTitle icon="sliders">Holat va koʻrinish</CardTitle>
            <div className="sw-r">
              <span>
                <b id="sopen-l">Ishga ochiq</b>
                <small>Saytda yashil belgi bilan koʻrinadi</small>
              </span>
              <button
                className="switch"
                type="button"
                role="switch"
                aria-checked={form.open}
                aria-labelledby="sopen-l"
                onClick={() => update((current) => ({ ...current, open: !current.open }))}
              />
            </div>
            <div className="sw-r">
              <span>
                <b>Mavzu</b>
                <small>Sayt va admin panel uchun</small>
              </span>
              <ThemeSeg />
            </div>
            <label className="fld">
              <span>GitHub username</span>
              <span className="ipx">
                <em>github.com/</em>
                <input
                  id="sgh"
                  className="inp"
                  value={form.github}
                  onChange={(event) => update((current) => ({ ...current, github: event.target.value }))}
                  placeholder="username"
                  spellCheck={false}
                  autoComplete="off"
                />
              </span>
            </label>
            <p className="hint">Faollik xaritasi, Actions va loyihalar shu akkauntdan olinadi.</p>
          </section>
          <section className="c sc">
            <CardTitle icon="sparkle">Hozir</CardTitle>
            <div className="kv">
              {form.now.map((item, index) => (
                <div key={index} className="kv-r">
                  <input
                    className="inp now-k"
                    value={item.k}
                    onChange={(event) =>
                      update((current) => ({ ...current, now: current.now.map((row, i) => (i === index ? { ...row, k: event.target.value } : row)) }))
                    }
                    placeholder="Sarlavha"
                    aria-label={`${index + 1}-band sarlavhasi`}
                  />
                  <input
                    className="inp now-v"
                    value={item.v}
                    onChange={(event) =>
                      update((current) => ({ ...current, now: current.now.map((row, i) => (i === index ? { ...row, v: event.target.value } : row)) }))
                    }
                    placeholder="Matn"
                    aria-label={`${index + 1}-band matni`}
                  />
                </div>
              ))}
            </div>
            <p className="hint">“Men haqimda” sahifasida chiqadi. Boʻsh qatorlar koʻrinmaydi.</p>
          </section>
          <section className="c sc">
            <CardTitle icon="book">Oʻrganyapman</CardTitle>
            <div className="kv">
              {form.learn.map((item, index) => (
                <div key={index} className="kv-r lr">
                  <input
                    className="inp ln-n"
                    value={item.n}
                    onChange={(event) =>
                      update((current) => ({ ...current, learn: current.learn.map((row, i) => (i === index ? { ...row, n: event.target.value } : row)) }))
                    }
                    placeholder="Mavzu"
                    aria-label={`${index + 1}-mavzu nomi`}
                  />
                  <span className="ipx pcn">
                    <input
                      className="inp ln-p"
                      type="number"
                      min={0}
                      max={100}
                      inputMode="numeric"
                      placeholder="0"
                      value={item.p}
                      onChange={(event) =>
                        update((current) => ({ ...current, learn: current.learn.map((row, i) => (i === index ? { ...row, p: event.target.value } : row)) }))
                      }
                      aria-label={`${index + 1}-mavzu foizi`}
                    />
                    <em>%</em>
                  </span>
                  <i className="lbar" aria-hidden="true">
                    <b style={{ width: `${clampPercent(Number(item.p))}%` }} />
                  </i>
                </div>
              ))}
            </div>
            <p className="hint">Bosh sahifadagi halqada navbat bilan koʻrinadi.</p>
          </section>
          <section className="c sc sc-tg">
            <CardTitle icon="send">Telegram kanal</CardTitle>
            <div className="two">
              <label className="fld">
                <span>Username</span>
                <span className="ipx">
                  <em>t.me/</em>
                  <input
                    id="stgu"
                    className="inp"
                    value={form.telegram.u}
                    onChange={(event) => update((current) => ({ ...current, telegram: { ...current.telegram, u: event.target.value } }))}
                    placeholder="username"
                    spellCheck={false}
                    autoComplete="off"
                  />
                </span>
              </label>
              <label className="fld">
                <span>Kanal nomi</span>
                <input
                  className="inp"
                  value={form.telegram.n}
                  onChange={(event) => update((current) => ({ ...current, telegram: { ...current.telegram, n: event.target.value } }))}
                  placeholder="Kanal nomi"
                />
              </label>
            </div>
            <div className="kv">
              {form.telegram.posts.map((post, index) => (
                <div key={index} className="kv-r tp">
                  <input
                    className="inp tgp-t"
                    value={post.t}
                    onChange={(event) =>
                      update((current) => ({
                        ...current,
                        telegram: { ...current.telegram, posts: current.telegram.posts.map((row, i) => (i === index ? { ...row, t: event.target.value } : row)) },
                      }))
                    }
                    placeholder={`${index + 1}-post matni`}
                    aria-label={`${index + 1}-post matni`}
                  />
                  <input
                    className="inp tgp-d"
                    type="date"
                    value={post.d}
                    onChange={(event) =>
                      update((current) => ({
                        ...current,
                        telegram: { ...current.telegram, posts: current.telegram.posts.map((row, i) => (i === index ? { ...row, d: event.target.value } : row)) },
                      }))
                    }
                    aria-label={`${index + 1}-post sanasi`}
                  />
                </div>
              ))}
            </div>
            <p className="hint">
              Bosh sahifadagi kartada kanalning soʻnggi postlari avtomatik chiqadi. Kanal javob bermasa, shu yerdagi postlar koʻrsatiladi.
            </p>
          </section>
          <section className="c sc sc-dz">
            <CardTitle icon="info">Hisob</CardTitle>
            <div className="dz">
              <span>
                <b>Hisobdan chiqish</b>
                <small>Admin sessiyasi yopiladi.</small>
              </span>
              <button className="dbtn" type="button" onClick={logout}>
                <Icon name="logout" />
                Chiqish
              </button>
            </div>
          </section>
        </div>
      </div>
    </div>
  );
}
