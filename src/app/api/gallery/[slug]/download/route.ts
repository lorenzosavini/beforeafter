import { NextRequest, NextResponse } from "next/server";
import { Readable } from "node:stream";
import archiver from "archiver";
import { getEventBySlug } from "@/lib/store/events";

export const runtime = "nodejs";

/** Streams every gallery photo for an event as a single zip download. */
export async function GET(
  req: NextRequest,
  { params }: { params: Promise<{ slug: string }> }
) {
  const { slug } = await params;
  const event = await getEventBySlug(slug);
  if (!event || event.gallery.length === 0) {
    return NextResponse.json({ error: "Not found" }, { status: 404 });
  }

  const archive = archiver("zip", { zlib: { level: 6 } });

  // Append each photo as its bytes arrive from Blob storage — fetched
  // sequentially to keep memory bounded on the serverless function.
  // Disambiguates duplicate filenames so no photo silently overwrites another.
  (async () => {
    const usedNames = new Set<string>();
    for (const photo of event.gallery) {
      try {
        // photo.url is absolute in production (Blob) but root-relative
        // under the local filesystem fallback — resolve against the
        // incoming request so fetch() works in both cases.
        const res = await fetch(new URL(photo.url, req.url));
        if (!res.ok || !res.body) continue;
        const buffer = Buffer.from(await res.arrayBuffer());

        let name = photo.filename;
        if (usedNames.has(name)) {
          const dot = name.lastIndexOf(".");
          const base = dot === -1 ? name : name.slice(0, dot);
          const ext = dot === -1 ? "" : name.slice(dot);
          name = `${base}-${photo.id.slice(0, 6)}${ext}`;
        }
        usedNames.add(name);

        archive.append(buffer, { name });
      } catch {
        // skip photos that fail to fetch rather than aborting the whole zip
      }
    }
    archive.finalize();
  })();

  const webStream = Readable.toWeb(
    archive as unknown as Readable
  ) as ReadableStream;

  return new NextResponse(webStream, {
    headers: {
      "Content-Type": "application/zip",
      "Content-Disposition": `attachment; filename="${event.slug}-foto.zip"`,
    },
  });
}
