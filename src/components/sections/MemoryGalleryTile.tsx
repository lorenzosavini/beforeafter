"use client";

import Link from "next/link";
import RevealImage from "@/components/motion/RevealImage";
import { setCursor } from "@/lib/cursor";
import { markDiscovered } from "@/lib/discovery";

interface MemoryGalleryTileProps {
  photoUrl: string;
  caption: string;
  href: string;
  span: string;
  index: number;
}

export default function MemoryGalleryTile({
  photoUrl,
  caption,
  href,
  span,
  index,
}: MemoryGalleryTileProps) {
  return (
    <RevealImage
      direction={index % 2 === 0 ? "up" : "down"}
      delay={index * 0.05}
      className={`group relative aspect-[4/5] sm:aspect-auto ${span}`}
    >
      <Link
        href={href}
        className="relative block h-full w-full"
        onMouseEnter={() => {
          setCursor({ variant: "image" });
          markDiscovered(`memory:${index}`);
        }}
        onMouseLeave={() => setCursor(null)}
        onTouchStart={() => markDiscovered(`memory:${index}`)}
      >
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img
          src={photoUrl}
          alt=""
          className="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]"
        />
        <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-night/80 to-transparent p-4">
          <span className="font-mono-label text-paper/80">{caption}</span>
        </div>
      </Link>
    </RevealImage>
  );
}
