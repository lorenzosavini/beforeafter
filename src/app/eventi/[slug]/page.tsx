import { notFound } from "next/navigation";
import type { Metadata } from "next";
import ChapterMarker from "@/components/chapter/ChapterMarker";
import GalleryGrid from "@/components/eventi/GalleryGrid";
import { getEventBySlug } from "@/lib/store/events";
import { formatEventDate } from "@/lib/formatDate";
import { isPastEvent } from "@/lib/isPastEvent";

export const dynamic = "force-dynamic";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  const event = await getEventBySlug(slug);
  return { title: event ? `${event.title} — beforeafter` : "beforeafter" };
}

export default async function EventDetailPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const event = await getEventBySlug(slug);
  if (!event) notFound();

  const isPast = isPastEvent(event.isoDate);

  return (
    <main className="min-h-svh bg-night px-5 pb-24 pt-28 text-paper sm:px-8">
      <ChapterMarker
        index={event.edition.replace(/\D/g, "") || "—"}
        total="—"
        label={isPast ? "Memorie" : "Eventi"}
        tone="paper"
        className="mb-8"
      />

      <h1 className="font-display mb-4 text-[13vw] leading-[0.9] sm:text-[6vw]">
        {event.title}
      </h1>

      {event.coverImageUrl && (
        <div className="mb-10 aspect-[16/9] w-full overflow-hidden sm:aspect-[21/9]">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img
            src={event.coverImageUrl}
            alt=""
            className="h-full w-full object-cover"
          />
        </div>
      )}

      <div className="font-mono-label mb-10 grid grid-cols-2 gap-6 text-paper/60 sm:grid-cols-4">
        <div>
          <div className="opacity-60">Data</div>
          <div className="mt-1 text-paper">{formatEventDate(event.isoDate)}</div>
        </div>
        <div>
          <div className="opacity-60">Città</div>
          <div className="mt-1 text-paper">{event.city}</div>
        </div>
        <div>
          <div className="opacity-60">Venue</div>
          <div className="mt-1 text-paper">{event.venue}</div>
        </div>
        {event.coordinates && (
          <div>
            <div className="opacity-60">Coordinate</div>
            <div className="mt-1 text-paper">{event.coordinates}</div>
          </div>
        )}
        {event.price && (
          <div>
            <div className="opacity-60">Prezzo</div>
            <div className="mt-1 text-paper">{event.price}</div>
          </div>
        )}
        {event.ageRestriction && (
          <div>
            <div className="opacity-60">Età minima</div>
            <div className="mt-1 text-paper">{event.ageRestriction}</div>
          </div>
        )}
        {event.dressCode && (
          <div>
            <div className="opacity-60">Dress code</div>
            <div className="mt-1 text-paper">{event.dressCode}</div>
          </div>
        )}
      </div>

      {event.summary && (
        <p className="font-serif-italic mb-10 max-w-2xl text-xl text-paper/70">
          {event.summary}
        </p>
      )}

      {event.lineup.length > 0 && (
        <div className="font-mono-label mb-10 max-w-md">
          <div className="mb-3 text-paper/60">Lineup</div>
          <ul className="flex flex-col gap-1.5">
            {event.lineup.map((entry) => (
              <li key={entry.id} className="flex items-baseline gap-3">
                <span className="text-paper">{entry.name}</span>
                {entry.role && <span className="text-paper/50">{entry.role}</span>}
              </li>
            ))}
          </ul>
        </div>
      )}

      {event.timeline.length > 0 && (
        <div className="font-mono-label mb-10 max-w-md">
          <div className="mb-3 text-paper/60">Timeline</div>
          <ul className="flex flex-col gap-1.5">
            {event.timeline.map((entry) => (
              <li key={entry.id} className="flex items-baseline gap-4">
                <span className="text-flame">{entry.time}</span>
                <span className="text-paper/80">{entry.label}</span>
              </li>
            ))}
          </ul>
        </div>
      )}

      {event.ctaButtons.length > 0 && (
        <div className="mb-14 flex flex-wrap gap-4">
          {event.ctaButtons.map((btn) => (
            <a
              key={btn.id}
              href={btn.url}
              target="_blank"
              rel="noreferrer"
              className="font-mono-label border border-flame/60 px-6 py-3 text-flame transition-colors hover:bg-flame hover:text-night"
            >
              {btn.label}
            </a>
          ))}
        </div>
      )}

      <div className="border-t border-paper/10 pt-10">
        <span className="font-mono-label mb-8 block text-paper/50">
          Memorie {event.gallery.length > 0 && `(${event.gallery.length})`}
        </span>
        {event.gallery.length === 0 ? (
          <p className="font-serif-italic text-xl text-paper/60">
            {isPast
              ? "Le foto di questa serata arrivano presto."
              : "Le foto arriveranno dopo l'evento."}
          </p>
        ) : (
          <GalleryGrid slug={event.slug} photos={event.gallery} />
        )}
      </div>
    </main>
  );
}
