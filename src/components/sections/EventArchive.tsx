import ChapterMarker from "@/components/chapter/ChapterMarker";
import SplitLines from "@/components/motion/SplitLines";
import EventArchiveCard from "@/components/sections/EventArchiveCard";
import { getPastEvents } from "@/lib/store/events";

export default async function EventArchive() {
  const pastEvents = await getPastEvents();

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

      {pastEvents.length === 0 ? (
        <p className="font-serif-italic max-w-md text-xl text-paper/60">
          Il primo capitolo deve ancora essere scritto.
        </p>
      ) : (
        <div className="-mx-5 flex snap-x snap-mandatory gap-4 overflow-x-auto px-5 pb-6 sm:-mx-8 sm:gap-6 sm:px-8">
          {pastEvents.map((event) => (
            <EventArchiveCard key={event.id} event={event} />
          ))}
        </div>
      )}
    </section>
  );
}
