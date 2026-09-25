import { NextRequest, NextResponse } from "next/server";
import { setCoverImage, setCoverImageFromUrl } from "@/lib/store/events";

const MAX_SIZE = 8 * 1024 * 1024; // 8MB — only applies to the local-dev relay path below

export async function POST(
  req: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;

  // Client already uploaded directly to Blob storage (production path) —
  // just record the resulting URL.
  if (req.headers.get("content-type")?.includes("application/json")) {
    const { url } = (await req.json()) as { url?: string };
    if (!url) {
      return NextResponse.json({ error: "URL mancante" }, { status: 400 });
    }
    try {
      const event = await setCoverImageFromUrl(id, url);
      return NextResponse.json({ event });
    } catch {
      return NextResponse.json({ error: "Evento non trovato" }, { status: 404 });
    }
  }

  const form = await req.formData();
  const file = form.get("file");

  if (!(file instanceof File)) {
    return NextResponse.json({ error: "Nessun file" }, { status: 400 });
  }
  if (!file.type.startsWith("image/")) {
    return NextResponse.json({ error: "Solo immagini" }, { status: 400 });
  }
  if (file.size > MAX_SIZE) {
    return NextResponse.json({ error: "File troppo grande (max 8MB)" }, { status: 400 });
  }

  const buffer = Buffer.from(await file.arrayBuffer());
  try {
    const event = await setCoverImage(id, buffer, file.name, file.type);
    return NextResponse.json({ event });
  } catch {
    return NextResponse.json({ error: "Evento non trovato" }, { status: 404 });
  }
}
