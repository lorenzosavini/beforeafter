"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import EventForm, { type EventFormValues } from "@/components/admin/EventForm";
import CoverUploader from "@/components/admin/CoverUploader";
import GalleryManager from "@/components/admin/GalleryManager";
import type { EventRecord } from "@/lib/store/events";

export default function EditEventClient({
  event: initialEvent,
  usingBlobStorage,
}: {
  event: EventRecord;
  usingBlobStorage: boolean;
}) {
  const router = useRouter();
  const [event, setEvent] = useState(initialEvent);
  const [deleting, setDeleting] = useState(false);

  const handleSubmit = async (values: EventFormValues) => {
    const res = await fetch(`/api/admin/events/${event.id}`, {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(values),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error ?? "Errore nel salvataggio");
    setEvent(data.event);
    router.refresh();
  };

  const handleDelete = async () => {
    if (!confirm(`Eliminare definitivamente "${event.title}"?`)) return;
    setDeleting(true);
    await fetch(`/api/admin/events/${event.id}`, { method: "DELETE" });
    router.push("/admin");
    router.refresh();
  };

  return (
    <div className="flex flex-col gap-14">
      <a
        href={`/eventi/${event.slug}`}
        target="_blank"
        rel="noreferrer"
        className="font-mono-label w-fit text-flame hover:underline"
      >
        Vedi scheda pubblica dell&apos;evento →
      </a>

      <EventForm
        submitLabel="Salva modifiche"
        initial={event}
        onSubmit={handleSubmit}
      />

      <CoverUploader
        eventId={event.id}
        coverImageUrl={event.coverImageUrl}
        usingBlobStorage={usingBlobStorage}
        onUploaded={(url) => setEvent((e) => ({ ...e, coverImageUrl: url }))}
      />

      <div className="flex flex-col gap-3">
        <span className="font-mono-label text-paper/60">
          Gallery foto ({event.gallery.length})
        </span>
        <GalleryManager
          eventId={event.id}
          photos={event.gallery}
          usingBlobStorage={usingBlobStorage}
          onChange={(gallery) => setEvent((e) => ({ ...e, gallery }))}
        />
      </div>

      <button
        type="button"
        onClick={handleDelete}
        disabled={deleting}
        className="font-mono-label w-fit border border-flame/40 px-6 py-3 text-flame transition-colors hover:border-flame disabled:opacity-40"
      >
        {deleting ? "Eliminazione..." : "Elimina evento"}
      </button>
    </div>
  );
}
