import { NextRequest, NextResponse } from "next/server";
import { getEventBySlug } from "@/lib/store/events";

export const runtime = "nodejs";
export const dynamic = "force-dynamic";

/** Proxies a single gallery photo with a Content-Disposition header, so the
 * browser downloads it (with its original filename) instead of navigating
 * to it — plain cross-origin `<a download>` isn't reliable across browsers. */
export async function GET(
  req: NextRequest,
  { params }: { params: Promise<{ slug: string; photoId: string }> }
) {
  const { slug, photoId } = await params;
  const event = await getEventBySlug(slug);
  const photo = event?.gallery.find((p) => p.id === photoId);
  if (!photo) {
    return NextResponse.json({ error: "Not found" }, { status: 404 });
  }

  // photo.url is absolute in production (Blob) but root-relative under the
  // local filesystem fallback — resolve against the incoming request so
  // fetch() works in both cases.
  const upstream = await fetch(new URL(photo.url, req.url));
  if (!upstream.ok || !upstream.body) {
    return NextResponse.json({ error: "Not found" }, { status: 404 });
  }

  return new NextResponse(upstream.body, {
    headers: {
      "Content-Type": upstream.headers.get("content-type") ?? "image/jpeg",
      "Content-Disposition": `attachment; filename="${photo.filename}"`,
      "Cache-Control": "public, max-age=31536000, immutable",
    },
  });
}
