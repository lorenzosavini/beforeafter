import { NextRequest, NextResponse } from "next/server";
import { createEvent, getAllEvents, type EventInput } from "@/lib/store/events";

// This route reads/writes admin-editable event data — never let Next.js
// statically cache the GET handler, or it would keep serving whatever was
// true at build/first-request time instead of the current store.
export const dynamic = "force-dynamic";

export async function GET() {
  const events = await getAllEvents();
  const sorted = [...events].sort(
    (a, b) => new Date(b.isoDate).getTime() - new Date(a.isoDate).getTime()
  );
  return NextResponse.json({ events: sorted });
}

export async function POST(req: NextRequest) {
  const body = (await req.json().catch(() => null)) as EventInput | null;
  if (!body?.title || !body?.isoDate) {
    return NextResponse.json(
      { error: "Titolo e data sono obbligatori" },
      { status: 400 }
    );
  }
  const event = await createEvent(body);
  return NextResponse.json({ event }, { status: 201 });
}
