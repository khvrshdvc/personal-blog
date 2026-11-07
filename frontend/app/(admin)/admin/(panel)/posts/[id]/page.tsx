import { Suspense } from "react";
import { notFound } from "next/navigation";
import { PostEditor } from "@/components/admin/post-editor";

async function Content({ params }: { params: PageProps<"/admin/posts/[id]">["params"] }) {
  const { id } = await params;
  const number = Number(id);

  if (!Number.isInteger(number) || number < 1) notFound();

  return <PostEditor key={number} id={number} />;
}

export const instant = false;

export default function EditPostPage({ params }: PageProps<"/admin/posts/[id]">) {
  return (
    <Suspense fallback={null}>
      <Content params={params} />
    </Suspense>
  );
}
