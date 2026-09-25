import { notFound } from "next/navigation";
import AdminShell from "@/components/admin/AdminShell";
import EditEventClient from "@/app/admin/events/[id]/EditEventClient";
import { getEventById } from "@/lib/store/events";
import { usingBlobStorage } from "@/lib/store/backend";

export default async function EditEventPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const event = await getEventById(id);
  if (!event) notFound();

  return (
    <AdminShell title={event.title}>
      <EditEventClient event={event} usingBlobStorage={usingBlobStorage} />
    </AdminShell>
  );
}
