import { NextRequest, NextResponse } from "next/server";
import { addGalleryPhotos, addGalleryPhotosFromUrls } from "@/lib/store/events";

const MAX_SIZE = 12 * 1024 * 1024; // 12MB per photo — only applies to the local-dev relay path below
const MAX_FILES = 40; // per request — only applies to the local-dev relay path below

export async function POST(
  req: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;

  // Client already uploaded directly to Blob storage (production path).
  // Takes the whole batch from one bulk upload at once — see
  // addGalleryPhotosFromUrls for why registering photos one at a time
  // is unsafe against this store's single-document read-modify-write.
  if (req.headers.get("content-type")?.includes("application/json")) {
    const { photos } = (await req.json()) as {
      photos?: { url: string; filename: string }[];
    };
    if (!photos || photos.length === 0) {
      return NextResponse.json({ error: "Dati mancanti" }, { status: 400 });
    }
    try {
      const event = await addGalleryPhotosFromUrls(id, photos);
      return NextResponse.json({ event });
    } catch {
      return NextResponse.json({ error: "Evento non trovato" }, { status: 404 });
    }
  }

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
