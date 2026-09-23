"use client";

import ChapterMarker from "@/components/chapter/ChapterMarker";
import SplitLines from "@/components/motion/SplitLines";
import GeneratedArtworkGL from "@/components/webgl/GeneratedArtworkGL";
import { setCursor } from "@/lib/cursor";
import { markDiscovered } from "@/lib/discovery";
import { archiveEvents } from "@/lib/data/events";

export default function EventArchive() {
  return (
    <section
      id="archive"
      className="relative bg-night-soft px-5 py-24 text-paper sm:px-8 sm:py-32"
    >
      <ChapterMarker
        index="05"
        label="Archivio"
        tone="paper"
        className="mb-10"
      />

      <SplitLines
        lines={["Ogni capitolo", "lascia un segno."]}
        className="font-display mb-14 text-[13vw] leading-[0.85] sm:text-[6.5vw] lg:mb-20"
      />

      <div className="-mx-5 flex snap-x snap-mandatory gap-4 overflow-x-auto px-5 pb-6 sm:-mx-8 sm:gap-6 sm:px-8">
        {archiveEvents.map((event) => (
          <a
            key={event.slug}
            href="#footer"
            className="group flex w-[72vw] shrink-0 snap-start flex-col gap-4 sm:w-[34vw] lg:w-[22vw]"
            onMouseEnter={() => {
              setCursor({ variant: "view", label: "Presto" });
              markDiscovered(`archive:${event.slug}`);
            }}
            onMouseLeave={() => setCursor(null)}
            onTouchStart={() => markDiscovered(`archive:${event.slug}`)}
          >
            <div className="aspect-[3/4] overflow-hidden">
              <GeneratedArtworkGL
                seed={event.seed}
                label={event.edition}
                className="h-full w-full transition-transform duration-700 ease-out group-hover:scale-[1.04]"
              />
            </div>
            <div className="font-mono-label flex items-baseline justify-between text-paper/70">
              <span className="text-paper">{event.title}</span>
              <span>{event.dateLabel}</span>
            </div>
            <div className="font-mono-label -mt-3 text-paper/50">
              {event.city} · {event.venue}
            </div>
          </a>
        ))}
      </div>
    </section>
  );
}
