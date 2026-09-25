import { NextRequest, NextResponse } from "next/server";
import { addGalleryPhotos } from "@/lib/store/events";

const MAX_SIZE = 12 * 1024 * 1024; // 12MB per photo
const MAX_FILES = 40; // per request

export async function POST(
  req: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const form = await req.formData();
  const entries = form.getAll("files").filter((f): f is File => f instanceof File);

  if (entries.length === 0) {
    return NextResponse.json({ error: "Nessun file" }, { status: 400 });
  }
  if (entries.length > MAX_FILES) {
    return NextResponse.json(
      { error: `Massimo ${MAX_FILES} foto per caricamento` },
      { status: 400 }
    );
  }

  const files: { buffer: Buffer; filename: string; contentType: string }[] = [];
  for (const file of entries) {
    if (!file.type.startsWith("image/")) {
      return NextResponse.json(
        { error: `${file.name} non è un'immagine` },
        { status: 400 }
      );
    }
    if (file.size > MAX_SIZE) {
      return NextResponse.json(
        { error: `${file.name} supera i 12MB` },
        { status: 400 }
      );
    }
    files.push({
      buffer: Buffer.from(await file.arrayBuffer()),
      filename: file.name,
      contentType: file.type,
    });
  }

  try {
    const event = await addGalleryPhotos(id, files);
    return NextResponse.json({ event });
  } catch {
    return NextResponse.json({ error: "Evento non trovato" }, { status: 404 });
  }
}
