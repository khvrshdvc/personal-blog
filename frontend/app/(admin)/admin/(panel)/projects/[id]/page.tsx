import { Suspense } from "react";
import { notFound } from "next/navigation";
import { ProjectForm } from "@/components/admin/project-form";

async function Content({ params }: { params: PageProps<"/admin/projects/[id]">["params"] }) {
  const { id } = await params;
  const number = Number(id);

  if (!Number.isInteger(number) || number < 1) notFound();

  return <ProjectForm key={number} id={number} />;
}

export default function EditProjectPage({ params }: PageProps<"/admin/projects/[id]">) {
  return (
    <Suspense fallback={null}>
      <Content params={params} />
    </Suspense>
  );
}
