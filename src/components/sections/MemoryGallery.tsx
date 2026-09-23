"use client";

import ChapterMarker from "@/components/chapter/ChapterMarker";
import SplitLines from "@/components/motion/SplitLines";
import RevealImage from "@/components/motion/RevealImage";
import GeneratedArtwork from "@/components/media/GeneratedArtwork";
import { setCursor } from "@/lib/cursor";

interface Memory {
  seed: number;
  caption: string;
  span: string;
}

const MEMORIES: Memory[] = [
  { seed: 3, caption: "Capitolo VI — Roma", span: "sm:col-span-4 sm:row-span-2" },
  { seed: 8, caption: "Backstage", span: "sm:col-span-2" },
  { seed: 1, caption: "Capitolo V — Milano", span: "sm:col-span-2" },
  { seed: 5, caption: "Dopo mezzanotte", span: "sm:col-span-3" },
  { seed: 9, caption: "Capitolo IV — Roma", span: "sm:col-span-3" },
  { seed: 2, caption: "L'ultima ora", span: "sm:col-span-6" },
];

export default function MemoryGallery() {
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

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-6 sm:auto-rows-[14vw]">
        {MEMORIES.map((memory, i) => (
          <RevealImage
            key={i}
            direction={i % 2 === 0 ? "up" : "down"}
            delay={i * 0.05}
            className={`group relative aspect-[4/5] sm:aspect-auto ${memory.span}`}
          >
            <div
              className="relative h-full w-full"
              onMouseEnter={() => setCursor({ variant: "image" })}
              onMouseLeave={() => setCursor(null)}
            >
              <GeneratedArtwork
                seed={memory.seed}
                label=""
                className="h-full w-full transition-transform duration-700 ease-out group-hover:scale-[1.03]"
              />
              <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-night/80 to-transparent p-4">
                <span className="font-mono-label text-paper/80">
                  {memory.caption}
                </span>
              </div>
            </div>
          </RevealImage>
        ))}
      </div>
    </section>
  );
}
