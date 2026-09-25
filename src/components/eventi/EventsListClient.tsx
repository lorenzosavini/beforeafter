"use client";

import { useMemo, useState } from "react";
import EventCard from "@/components/eventi/EventCard";
import type { EventRecord } from "@/lib/store/events";

interface EventWithStatus {
  event: EventRecord;
  isPast: boolean;
}

type Filter = "all" | "upcoming" | "past";

const FILTERS: { value: Filter; label: string }[] = [
  { value: "all", label: "Tutti" },
  { value: "upcoming", label: "In programma" },
  { value: "past", label: "Passati" },
];

export default function EventsListClient({ items }: { items: EventWithStatus[] }) {
  const [search, setSearch] = useState("");
  const [filter, setFilter] = useState<Filter>("all");

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase();
    return items.filter(({ event, isPast }) => {
      if (filter === "upcoming" && isPast) return false;
      if (filter === "past" && !isPast) return false;
      if (!q) return true;
      return (
        event.title.toLowerCase().includes(q) ||
        event.city.toLowerCase().includes(q) ||
        event.venue.toLowerCase().includes(q)
      );
    });
  }, [items, search, filter]);

  return (
    <div>
      <div className="mb-10 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
        <div className="font-mono-label flex gap-2">
          {FILTERS.map((f) => (
            <button
              key={f.value}
              type="button"
              onClick={() => setFilter(f.value)}
              className={`border px-4 py-2 transition-colors ${
                filter === f.value
                  ? "border-flame text-flame"
                  : "border-paper/25 text-paper/60 hover:border-paper/50"
              }`}
            >
              {f.label}
            </button>
          ))}
        </div>

        <input
          type="search"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder="Cerca per nome, città, venue..."
          className="font-sans w-full max-w-xs border border-paper/25 bg-transparent px-4 py-2.5 text-base text-paper outline-none transition-colors focus:border-flame"
        />
      </div>

      {filtered.length === 0 ? (
        <p className="font-serif-italic text-xl text-paper/60">
          Nessun evento trovato.
        </p>
      ) : (
        <div className="grid grid-cols-1 gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
          {filtered.map(({ event, isPast }) => (
            <EventCard key={event.id} event={event} isPast={isPast} />
          ))}
        </div>
      )}
    </div>
  );
}
