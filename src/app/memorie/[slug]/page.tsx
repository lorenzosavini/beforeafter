import { redirect } from "next/navigation";

/** /memorie/[slug] was the original public gallery URL — kept as a
 * redirect so any already-shared link keeps working, now that event
 * detail + gallery live together at /eventi/[slug]. */
export default async function LegacyMemoriePage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  redirect(`/eventi/${slug}`);
}
