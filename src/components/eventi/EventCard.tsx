"use client";

import Link from "next/link";
import GeneratedArtworkGL from "@/components/webgl/GeneratedArtworkGL";
import { setCursor } from "@/lib/cursor";
import type { EventRecord } from "@/lib/store/events";
import { formatEventDate } from "@/lib/formatDate";

interface EventCardProps {
  event: EventRecord;
  isPast: boolean;
}

export default function EventCard({ event, isPast }: EventCardProps) {
  return (
    <Link
      href={`/eventi/${event.slug}`}
      className="group flex flex-col gap-4"
      onMouseEnter={() => setCursor({ variant: "view", label: "Scheda" })}
      onMouseLeave={() => setCursor(null)}
    >
      <div className="relative aspect-[3/4] overflow-hidden">
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
      <div className="font-mono-label flex items-baseline justify-between text-paper/70">
        <span className="text-paper">{event.title}</span>
        <span>{formatEventDate(event.isoDate)}</span>
      </div>
      <div className="font-mono-label -mt-3 text-paper/50">
        {event.city} · {event.venue}
      </div>
    </Link>
  );
}
