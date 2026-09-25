import { NextRequest, NextResponse } from "next/server";
import { setCoverImage } from "@/lib/store/events";

const MAX_SIZE = 8 * 1024 * 1024; // 8MB

export async function POST(
  req: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
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
