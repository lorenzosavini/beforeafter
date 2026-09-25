"use client";

import { useRouter } from "next/navigation";
import AdminShell from "@/components/admin/AdminShell";
import EventForm, { type EventFormValues } from "@/components/admin/EventForm";

export default function NewEventPage() {
  const router = useRouter();

  const handleSubmit = async (values: EventFormValues) => {
    const res = await fetch("/api/admin/events", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(values),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error ?? "Errore nella creazione");
    router.push(`/admin/events/${data.event.id}`);
  };

  return (
    <AdminShell title="Nuovo evento">
      <EventForm submitLabel="Crea evento" onSubmit={handleSubmit} />
    </AdminShell>
  );
}
