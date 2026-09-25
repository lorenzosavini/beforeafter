"use client";

import RevealImage from "@/components/motion/RevealImage";
import { setCursor } from "@/lib/cursor";
import type { GalleryPhoto } from "@/lib/store/events";

interface GalleryGridProps {
  slug: string;
  photos: GalleryPhoto[];
}

export default function GalleryGrid({ slug, photos }: GalleryGridProps) {
  return (
    <div>
      <div className="mb-8 flex items-center justify-between">
        <span className="font-mono-label text-paper/50">
          {photos.length} foto
        </span>
        <a
          href={`/api/gallery/${slug}/download`}
          className="font-mono-label border border-paper/30 px-5 py-2.5 text-paper transition-colors hover:border-flame hover:text-flame"
        >
          Scarica tutte
        </a>
      </div>

      <div className="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
        {photos.map((photo, i) => (
          <RevealImage
            key={photo.id}
            direction={i % 2 === 0 ? "up" : "down"}
            delay={Math.min(i * 0.03, 0.4)}
            className="aspect-[4/5]"
          >
            <a
              href={`/api/gallery/${slug}/photo/${photo.id}`}
              className="group relative block h-full w-full"
              onMouseEnter={() => setCursor({ variant: "image", label: "Scarica" })}
              onMouseLeave={() => setCursor(null)}
            >
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img
                src={photo.url}
                alt=""
                loading="lazy"
                className="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]"
              />
            </a>
          </RevealImage>
        ))}
      </div>
    </div>
  );
}
