import Link from "next/link";
import AdminShell from "@/components/admin/AdminShell";
import {
  getPastEvents,
  getUpcomingEvents,
  type EventRecord,
} from "@/lib/store/events";
import { formatEventDate } from "@/lib/formatDate";

export const dynamic = "force-dynamic";

export default async function AdminDashboard() {
  const [upcoming, past] = await Promise.all([
    getUpcomingEvents(),
    getPastEvents(),
  ]);

  return (
    <AdminShell title="Eventi">
      <Link
        href="/admin/events/new"
        className="font-mono-label mb-12 inline-block border border-paper/30 px-6 py-3 text-paper transition-colors hover:border-paper"
      >
        + Nuovo evento
      </Link>

      <section className="mb-16">
        <h2 className="font-mono-label mb-4 text-paper/50">
          In programma ({upcoming.length})
        </h2>
        {upcoming.length === 0 ? (
          <p className="font-serif-italic text-paper/50">
            Nessun evento programmato.
          </p>
        ) : (
          <EventTable events={upcoming} />
        )}
      </section>

      <section>
        <h2 className="font-mono-label mb-4 text-paper/50">
          Archivio ({past.length})
        </h2>
        {past.length === 0 ? (
          <p className="font-serif-italic text-paper/50">Nessun evento passato.</p>
        ) : (
          <EventTable events={past} showGallery />
        )}
      </section>
    </AdminShell>
  );
}

function EventTable({
  events,
  showGallery,
}: {
  events: EventRecord[];
  showGallery?: boolean;
}) {
  return (
    <div className="flex flex-col divide-y divide-paper/10 border-y border-paper/10">
      {events.map((event) => (
        <Link
          key={event.id}
          href={`/admin/events/${event.id}`}
          className="group flex items-center justify-between gap-4 py-4 hover:bg-paper/5"
        >
          <div>
            <div className="flex items-baseline gap-3">
              <span className="font-display text-xl">{event.title}</span>
              <span className="font-mono-label text-paper/40">
                {event.edition}
              </span>
            </div>
            <div className="font-mono-label mt-1 text-paper/50">
              {formatEventDate(event.isoDate)} · {event.city}
              {showGallery && ` · ${event.gallery.length} foto`}
            </div>
          </div>
          <span className="font-mono-label text-paper/40 group-hover:text-paper">
            Modifica →
          </span>
        </Link>
      ))}
    </div>
  );
}
