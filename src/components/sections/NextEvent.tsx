import ChapterMarker from "@/components/chapter/ChapterMarker";
import SplitLines from "@/components/motion/SplitLines";
import RevealImage from "@/components/motion/RevealImage";
import EventPoster from "@/components/sections/EventPoster";
import { getUpcomingEvent } from "@/lib/store/events";
import { formatEventDate } from "@/lib/formatDate";

export default async function NextEvent() {
  const nextEvent = await getUpcomingEvent();

  return (
    <section
      id="next-event"
      className="relative bg-night-soft px-5 py-24 text-paper sm:px-8 sm:py-32"
    >
      <ChapterMarker index="02" label="Prossimo capitolo" tone="paper" className="mb-10" />

      {!nextEvent ? (
        <SplitLines
          lines={["Il prossimo capitolo", "è in scrittura."]}
          className="font-display text-[13vw] leading-[0.9] text-paper/60 sm:text-[6.2vw]"
        />
      ) : (
        <div className="grid items-start gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:gap-16">
          <div className="flex flex-col justify-between">
            <SplitLines
              lines={[nextEvent.title, "in arrivo."]}
              className="font-display text-[16vw] leading-[0.85] sm:text-[9vw] lg:text-[6.2vw]"
            />

            <div className="font-mono-label mt-10 grid grid-cols-2 gap-6 text-paper/60 sm:grid-cols-4 lg:mt-16">
              <div>
                <div className="opacity-60">Data</div>
                <div className="mt-1 text-paper">
                  {formatEventDate(nextEvent.isoDate)}
                </div>
              </div>
              <div>
                <div className="opacity-60">Città</div>
                <div className="mt-1 text-paper">{nextEvent.city}</div>
              </div>
              <div>
                <div className="opacity-60">Venue</div>
                <div className="mt-1 text-paper">{nextEvent.venue}</div>
              </div>
              <div>
                <div className="opacity-60">Coordinate</div>
                <div className="mt-1 text-paper">{nextEvent.coordinates}</div>
              </div>
              {nextEvent.price && (
                <div>
                  <div className="opacity-60">Prezzo</div>
                  <div className="mt-1 text-paper">{nextEvent.price}</div>
                </div>
              )}
              {nextEvent.ageRestriction && (
                <div>
                  <div className="opacity-60">Età minima</div>
                  <div className="mt-1 text-paper">{nextEvent.ageRestriction}</div>
                </div>
              )}
              {nextEvent.dressCode && (
                <div>
                  <div className="opacity-60">Dress code</div>
                  <div className="mt-1 text-paper">{nextEvent.dressCode}</div>
                </div>
              )}
            </div>

            {nextEvent.summary && (
              <p className="font-serif-italic mt-10 max-w-md text-xl text-paper/70 lg:mt-14">
                {nextEvent.summary}
              </p>
            )}

            {nextEvent.lineup.length > 0 && (
              <div className="font-mono-label mt-10 lg:mt-14">
                <div className="mb-3 text-paper/60">Lineup</div>
                <ul className="flex flex-col gap-1.5">
                  {nextEvent.lineup.map((entry) => (
                    <li key={entry.id} className="flex items-baseline gap-3">
                      <span className="text-paper">{entry.name}</span>
                      {entry.role && (
                        <span className="text-paper/50">{entry.role}</span>
                      )}
                    </li>
                  ))}
                </ul>
              </div>
            )}

            {nextEvent.timeline.length > 0 && (
              <div className="font-mono-label mt-10 lg:mt-14">
                <div className="mb-3 text-paper/60">Timeline</div>
                <ul className="flex flex-col gap-1.5">
                  {nextEvent.timeline.map((entry) => (
                    <li key={entry.id} className="flex items-baseline gap-4">
                      <span className="text-flame">{entry.time}</span>
                      <span className="text-paper/80">{entry.label}</span>
                    </li>
                  ))}
                </ul>
              </div>
            )}

            {nextEvent.ctaButtons.length > 0 && (
              <div className="mt-10 flex flex-wrap gap-4 lg:mt-14">
                {nextEvent.ctaButtons.map((btn) => (
                  <a
                    key={btn.id}
                    href={btn.url}
                    target="_blank"
                    rel="noreferrer"
                    className="font-mono-label border border-flame/60 px-6 py-3 text-flame transition-colors hover:bg-flame hover:text-night"
                  >
                    {btn.label}
                  </a>
                ))}
              </div>
            )}
          </div>

          <RevealImage direction="up" className="aspect-[3/4] w-full lg:aspect-[4/5]">
            <EventPoster event={nextEvent} className="h-full w-full" cursorLabel="Presto" />
          </RevealImage>
        </div>
      )}
    </section>
  );
}
