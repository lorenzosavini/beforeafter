import { NextRequest, NextResponse } from "next/server";
import {
  deleteEvent,
  getEventById,
  updateEvent,
  type EventInput,
} from "@/lib/store/events";

// See events/route.ts — GET handlers are eligible for Next.js's static
// route cache unless opted out explicitly.
export const dynamic = "force-dynamic";

export async function GET(
  _req: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const event = await getEventById(id);
  if (!event) return NextResponse.json({ error: "Not found" }, { status: 404 });
  return NextResponse.json({ event });
}

export async function PATCH(
  req: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const body = (await req.json().catch(() => null)) as Partial<EventInput> | null;
  if (!body) {
    return NextResponse.json({ error: "Invalid body" }, { status: 400 });
  }
  try {
    const event = await updateEvent(id, body);
    return NextResponse.json({ event });
  } catch {
    return NextResponse.json({ error: "Not found" }, { status: 404 });
  }
}

export async function DELETE(
  _req: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  await deleteEvent(id);
  return NextResponse.json({ ok: true });
}
