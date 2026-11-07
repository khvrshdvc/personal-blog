"use client";

import { useRouter } from "next/navigation";
import { createContext, useCallback, useContext, useEffect, useMemo, useState } from "react";
import { AdminApiError, adminApi, onUnauthorized } from "@/lib/admin/api";
import type { AdminPost, Project } from "@/lib/admin/types";

type SessionStatus = "loading" | "authed";

interface AdminContextValue {
  status: SessionStatus;
  posts: AdminPost[] | null;
  projects: Project[] | null;
  loadPosts: () => Promise<void>;
  loadProjects: () => Promise<void>;
  upsertPost: (post: AdminPost) => void;
  removePost: (id: number) => void;
  upsertProject: (project: Project) => void;
  removeProject: (id: number) => void;
  logout: () => Promise<void>;
}

const AdminContext = createContext<AdminContextValue | null>(null);

export function useAdmin(): AdminContextValue {
  const value = useContext(AdminContext);

  if (!value) throw new Error("AdminProvider topilmadi");

  return value;
}

export function AdminProvider({ children }: { children: React.ReactNode }) {
  const router = useRouter();
  const [status, setStatus] = useState<SessionStatus>("loading");
  const [posts, setPosts] = useState<AdminPost[] | null>(null);
  const [projects, setProjects] = useState<Project[] | null>(null);

  useEffect(() => {
    onUnauthorized(() => router.replace("/admin/login"));

    adminApi
      .me()
      .then(() => setStatus("authed"))
      .catch((error) => {
        if (error instanceof AdminApiError && error.status === 401) router.replace("/admin/login");
      });

    return () => onUnauthorized(null);
  }, [router]);

  const loadPosts = useCallback(async () => {
    setPosts(await adminApi.posts());
  }, []);

  const loadProjects = useCallback(async () => {
    setProjects(await adminApi.projects());
  }, []);

  useEffect(() => {
    if (status !== "authed") return;

    adminApi.posts().then(setPosts).catch(() => {});
    adminApi.projects().then(setProjects).catch(() => {});
  }, [status]);

  const upsertPost = useCallback((post: AdminPost) => {
    setPosts((list) => {
      if (!list) return [post];
      const rest = list.filter((item) => item.id !== post.id);

      return [post, ...rest];
    });
  }, []);

  const removePost = useCallback((id: number) => setPosts((list) => list?.filter((item) => item.id !== id) ?? null), []);

  const upsertProject = useCallback((project: Project) => {
    setProjects((list) => {
      if (!list) return [project];
      const exists = list.some((item) => item.id === project.id);

      return exists ? list.map((item) => (item.id === project.id ? project : item)) : [...list, project];
    });
  }, []);

  const removeProject = useCallback((id: number) => setProjects((list) => list?.filter((item) => item.id !== id) ?? null), []);

  const logout = useCallback(async () => {
    try {
      await adminApi.logout();
    } finally {
      router.replace("/admin/login");
    }
  }, [router]);

  const value = useMemo(
    () => ({ status, posts, projects, loadPosts, loadProjects, upsertPost, removePost, upsertProject, removeProject, logout }),
    [status, posts, projects, loadPosts, loadProjects, upsertPost, removePost, upsertProject, removeProject, logout],
  );

  return <AdminContext.Provider value={value}>{children}</AdminContext.Provider>;
}
