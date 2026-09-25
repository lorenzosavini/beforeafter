import { NextRequest, NextResponse } from "next/server";
import { removeGalleryPhoto } from "@/lib/store/events";

export async function DELETE(
  _req: NextRequest,
  { params }: { params: Promise<{ id: string; photoId: string }> }
) {
  const { id, photoId } = await params;
  try {
    const event = await removeGalleryPhoto(id, photoId);
    return NextResponse.json({ event });
  } catch {
    return NextResponse.json({ error: "Evento non trovato" }, { status: 404 });
  }
}
