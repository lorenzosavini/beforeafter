"use client";

import Link from "next/link";
import GeneratedArtworkGL from "@/components/webgl/GeneratedArtworkGL";
import { setCursor } from "@/lib/cursor";
import { markDiscovered } from "@/lib/discovery";
import type { EventRecord } from "@/lib/store/events";
import { formatEventDate } from "@/lib/formatDate";

export default function EventArchiveCard({ event }: { event: EventRecord }) {
  const hasGallery = event.gallery.length > 0;

  return (
    <Link
      href={hasGallery ? `/memorie/${event.slug}` : "#"}
      aria-disabled={!hasGallery}
      className="group flex w-[72vw] shrink-0 snap-start flex-col gap-4 sm:w-[34vw] lg:w-[22vw]"
      onMouseEnter={() => {
        setCursor({
          variant: "view",
          label: hasGallery ? "Memorie" : "Presto",
        });
        markDiscovered(`archive:${event.slug}`);
      }}
      onMouseLeave={() => setCursor(null)}
      onTouchStart={() => markDiscovered(`archive:${event.slug}`)}
      onClick={(e) => {
        if (!hasGallery) e.preventDefault();
      }}
    >
      <div className="aspect-[3/4] overflow-hidden">
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
