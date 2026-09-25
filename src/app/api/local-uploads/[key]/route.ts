import { NextRequest, NextResponse } from "next/server";
import { readFile } from "node:fs/promises";
import path from "node:path";

/**
 * Dev-only: serves files written by the local filesystem fallback in
 * lib/store/backend.ts (used when BLOB_READ_WRITE_TOKEN is unset). In
 * production, uploads go straight to Vercel Blob's own public URLs and
 * this route is never hit.
 */
export async function GET(
  _req: NextRequest,
  { params }: { params: Promise<{ key: string }> }
) {
  const { key } = await params;
  const decoded = decodeURIComponent(key);

  if (decoded.includes("..")) {
    return NextResponse.json({ error: "Invalid key" }, { status: 400 });
  }

  const filePath = path.join(process.cwd(), ".data", "uploads", decoded);

  try {
    const data = await readFile(filePath);
    const ext = path.extname(decoded).toLowerCase();
    const contentType =
      ext === ".png"
        ? "image/png"
        : ext === ".webp"
          ? "image/webp"
          : ext === ".gif"
            ? "image/gif"
            : "image/jpeg";
    return new NextResponse(new Uint8Array(data), {
      headers: { "Content-Type": contentType, "Cache-Control": "no-store" },
    });
  } catch {
    return NextResponse.json({ error: "Not found" }, { status: 404 });
  }
}
