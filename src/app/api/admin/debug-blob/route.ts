import { NextResponse } from "next/server";
import { list } from "@vercel/blob";

export const dynamic = "force-dynamic";

/** TEMPORARY diagnostic route — inspects raw blob metadata for the events
 * document to debug a write-persistence issue. Remove after use. */
export async function GET() {
  const { blobs } = await list({ prefix: "data/events.json" });
  return NextResponse.json({
    count: blobs.length,
    blobs: blobs.map((b) => ({
      pathname: b.pathname,
      url: b.url,
      size: b.size,
      uploadedAt: b.uploadedAt,
    })),
  });
}
