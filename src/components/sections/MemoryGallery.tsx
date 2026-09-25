import ChapterMarker from "@/components/chapter/ChapterMarker";
import SplitLines from "@/components/motion/SplitLines";
import MemoryGalleryTile from "@/components/sections/MemoryGalleryTile";
import { getPastEvents } from "@/lib/store/events";

const SPANS = [
  "sm:col-span-4 sm:row-span-2",
  "sm:col-span-2",
  "sm:col-span-2",
  "sm:col-span-3",
  "sm:col-span-3",
  "sm:col-span-6",
];

export default async function MemoryGallery() {
  const pastEvents = await getPastEvents();

  const tiles = pastEvents
    .flatMap((event) =>
      event.gallery.map((photo) => ({
        photoUrl: photo.url,
        caption: `${event.title} — ${event.city}`,
        href: `/eventi/${event.slug}`,
      }))
    )
    .slice(0, 6);

  return (
    <section
      id="memories"
      className="relative bg-night px-5 py-24 text-paper sm:px-8 sm:py-32"
    >
      <ChapterMarker index="03" label="Memorie" tone="paper" className="mb-10" />

      <SplitLines
        lines={["Quello che resta,", "dopo."]}
        className="font-display mb-14 text-[13vw] leading-[0.85] sm:text-[6.5vw] lg:mb-20"
      />

      {tiles.length === 0 ? (
        <p className="font-serif-italic max-w-md text-xl text-paper/60">
          Le prime memorie arrivano dopo il primo capitolo.
        </p>
      ) : (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-6 sm:auto-rows-[14vw]">
          {tiles.map((tile, i) => (
            <MemoryGalleryTile
              key={tile.photoUrl}
              {...tile}
              span={SPANS[i % SPANS.length]}
              index={i}
            />
          ))}
        </div>
      )}
    </section>
  );
}
