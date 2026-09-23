import ChapterMarker from "@/components/chapter/ChapterMarker";
import SplitLines from "@/components/motion/SplitLines";
import RevealImage from "@/components/motion/RevealImage";
import EventPoster from "@/components/sections/EventPoster";
import { nextEvent } from "@/lib/data/events";

export default function NextEvent() {
  return (
    <section
      id="next-event"
      className="relative bg-paper-dim px-5 py-24 text-ink sm:px-8 sm:py-32"
    >
      <ChapterMarker index="02" label="Prossimo capitolo" className="mb-10" />

      <div className="grid items-start gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:gap-16">
        <div className="flex flex-col justify-between">
          <SplitLines
            lines={[nextEvent.title, "in arrivo."]}
            className="font-display text-[16vw] leading-[0.85] sm:text-[9vw] lg:text-[6.2vw]"
          />

          <div className="font-mono-label mt-10 grid grid-cols-2 gap-6 text-ink-soft sm:grid-cols-4 lg:mt-16">
            <div>
              <div className="opacity-60">Data</div>
              <div className="mt-1 text-ink">{nextEvent.dateLabel}</div>
            </div>
            <div>
              <div className="opacity-60">Città</div>
              <div className="mt-1 text-ink">{nextEvent.city}</div>
            </div>
            <div>
              <div className="opacity-60">Venue</div>
              <div className="mt-1 text-ink">{nextEvent.venue}</div>
            </div>
            <div>
              <div className="opacity-60">Coordinate</div>
              <div className="mt-1 text-ink">{nextEvent.coordinates}</div>
            </div>
          </div>

          <p className="font-serif-italic mt-10 max-w-md text-xl text-ink-soft lg:mt-14">
            {nextEvent.summary}
          </p>
        </div>

        <RevealImage direction="up" className="aspect-[3/4] w-full lg:aspect-[4/5]">
          <EventPoster event={nextEvent} className="h-full w-full" cursorLabel="Presto" />
        </RevealImage>
      </div>
    </section>
  );
}
