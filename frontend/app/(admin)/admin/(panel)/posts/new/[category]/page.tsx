import { Suspense } from "react";
import { notFound } from "next/navigation";
import { PostEditor } from "@/components/admin/post-editor";

async function Content({ params }: { params: PageProps<"/admin/posts/new/[category]">["params"] }) {
  const { category } = await params;

  if (category !== "fikr" && category !== "dev") notFound();

  return <PostEditor key={category} id={null} category={category} />;
}

export const instant = false;

export default function NewPostCategoryPage({ params }: PageProps<"/admin/posts/new/[category]">) {
  return (
    <Suspense fallback={null}>
      <Content params={params} />
    </Suspense>
  );
}
