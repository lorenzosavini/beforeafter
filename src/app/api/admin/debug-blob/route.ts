import { NextResponse } from "next/server";
import { list } from "@vercel/blob";

export const dynamic = "force-dynamic";

/** TEMPORARY diagnostic route — inspects raw blob metadata for the events
 * document (new versioned scheme + legacy fixed path) to debug a
 * write-persistence issue. Remove after use. */
export async function GET() {
  const [versioned, legacy] = await Promise.all([
    list({ prefix: "data/events/" }),
    list({ prefix: "data/events.json" }),
  ]);
  return NextResponse.json({
    versioned: versioned.blobs.map((b) => ({
      pathname: b.pathname,
      url: b.url,
      size: b.size,
      uploadedAt: b.uploadedAt,
    })),
    legacy: legacy.blobs.map((b) => ({
      pathname: b.pathname,
      url: b.url,
      size: b.size,
      uploadedAt: b.uploadedAt,
    })),
  });
}
