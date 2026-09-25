import ChapterMarker from "@/components/chapter/ChapterMarker";
import EventsListClient from "@/components/eventi/EventsListClient";
import { getAllEvents } from "@/lib/store/events";
import { isPastEvent } from "@/lib/isPastEvent";

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Eventi — beforeafter",
};

export default async function EventsPage() {
  const events = await getAllEvents();
  const items = events
    .map((event) => ({ event, isPast: isPastEvent(event.isoDate) }))
    .sort((a, b) => new Date(b.event.isoDate).getTime() - new Date(a.event.isoDate).getTime());

  return (
    <main className="min-h-svh bg-night px-5 pb-24 pt-28 text-paper sm:px-8">
      <ChapterMarker index="—" total="—" label="Eventi" tone="paper" className="mb-8" />

      <h1 className="font-display mb-14 text-[13vw] leading-[0.85] sm:text-[6vw]">
        Tutti i capitoli.
      </h1>

      {items.length === 0 ? (
        <p className="font-serif-italic text-xl text-paper/60">
          Il primo capitolo deve ancora essere scritto.
        </p>
      ) : (
        <EventsListClient items={items} />
      )}
    </main>
  );
}
