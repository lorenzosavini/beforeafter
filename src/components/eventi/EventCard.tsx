"use client";

import Link from "next/link";
import GeneratedArtworkGL from "@/components/webgl/GeneratedArtworkGL";
import { setCursor } from "@/lib/cursor";
import type { EventRecord } from "@/lib/store/events";
import { formatEventDateShort, formatEventTime } from "@/lib/formatDate";

interface EventCardProps {
  event: EventRecord;
  isPast: boolean;
}

export default function EventCard({ event, isPast }: EventCardProps) {
  const href = `/eventi/${event.slug}`;
  const lineupLine = event.lineup
    .slice(0, 4)
    .map((l) => l.name)
    .filter(Boolean)
    .join(", ");
  const buttons = event.ctaButtons.slice(0, 2);

  return (
    <div className="flex flex-col gap-4">
      <Link
        href={href}
        className="group block"
        onMouseEnter={() => setCursor({ variant: "view", label: "Scheda" })}
        onMouseLeave={() => setCursor(null)}
      >
        <div className="relative aspect-square overflow-hidden">
          {event.coverImageUrl ? (
            // eslint-disable-next-line @next/next/no-img-element
            <img
              src={event.coverImageUrl}
              alt=""
              className="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.04]"
            />
          ) : (
            <GeneratedArtworkGL
              seed={event.seed}
              label={event.edition}
              className="h-full w-full transition-transform duration-700 ease-out group-hover:scale-[1.04]"
            />
          )}
          <span className="font-mono-label absolute left-3 top-3 border border-paper/30 bg-night/60 px-2.5 py-1 text-paper/80 backdrop-blur-sm">
            {isPast ? "Passato" : "In programma"}
          </span>
        </div>

        <div className="font-mono-label mt-4 text-paper/50">
          {formatEventDateShort(event.isoDate)} · {formatEventTime(event.isoDate)}
        </div>
        <div className="font-display mt-1 text-2xl leading-tight text-paper transition-colors group-hover:text-flame">
          {event.title}
        </div>
        <div className="font-mono-label mt-1 text-paper/50">
          {event.city} · {event.venue}
        </div>
        {lineupLine && (
          <div className="font-mono-label mt-2 text-paper/70">
            LINEUP: {lineupLine}
          </div>
        )}
      </Link>

      {(buttons.length > 0 || event.price) && (
        <div className="flex flex-col gap-2">
          {buttons.map((btn, i) => (
            <a
              key={btn.id}
              href={btn.url}
              target="_blank"
              rel="noreferrer"
              className={
                i === 0
                  ? "font-mono-label border border-flame px-5 py-2.5 text-center text-flame transition-colors hover:bg-flame hover:text-night"
                  : "font-mono-label border border-paper/25 px-5 py-2.5 text-center text-paper/70 transition-colors hover:border-paper hover:text-paper"
              }
            >
              {btn.label}
            </a>
          ))}
          {buttons.length === 0 && event.price && (
            <span className="font-mono-label border border-paper/25 px-5 py-2.5 text-center text-paper/70">
              {event.price}
            </span>
          )}
        </div>
      )}
    </div>
  );
}
