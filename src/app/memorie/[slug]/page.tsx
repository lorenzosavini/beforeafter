import { notFound } from "next/navigation";
import type { Metadata } from "next";
import ChapterMarker from "@/components/chapter/ChapterMarker";
import { getEventBySlug } from "@/lib/store/events";
import { formatEventDate } from "@/lib/formatDate";
import GalleryGrid from "@/app/memorie/[slug]/GalleryGrid";

export const dynamic = "force-dynamic";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  const event = await getEventBySlug(slug);
  return { title: event ? `${event.title} — Memorie — beforeafter` : "beforeafter" };
}

export default async function GalleryPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const event = await getEventBySlug(slug);
  if (!event) notFound();

  return (
    <main className="min-h-svh bg-night px-5 pb-24 pt-28 text-paper sm:px-8">
      <ChapterMarker
        index={event.edition.replace(/\D/g, "") || "—"}
        total="—"
        label="Memorie"
        tone="paper"
        className="mb-8"
      />

      <h1 className="font-display mb-4 text-[13vw] leading-[0.9] sm:text-[6vw]">
        {event.title}
      </h1>

      <div className="font-mono-label mb-14 flex flex-wrap gap-x-6 gap-y-2 text-paper/60">
        <span>{formatEventDate(event.isoDate)}</span>
        <span>{event.city}</span>
        <span>{event.venue}</span>
      </div>

      {event.gallery.length === 0 ? (
        <p className="font-serif-italic text-xl text-paper/60">
          Le foto di questa serata arrivano presto.
        </p>
      ) : (
        <GalleryGrid slug={event.slug} photos={event.gallery} />
      )}
    </main>
  );
}
