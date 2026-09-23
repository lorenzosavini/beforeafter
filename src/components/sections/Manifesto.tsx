import ChapterMarker from "@/components/chapter/ChapterMarker";
import SplitLines from "@/components/motion/SplitLines";
import AnimatedWords from "@/components/motion/AnimatedWords";

export default function Manifesto() {
  return (
    <section
      id="manifesto"
      className="relative bg-night px-5 py-24 text-paper sm:px-8 sm:py-32"
    >
      <ChapterMarker index="06" label="Chi siamo" tone="paper" className="mb-10" />

      <div className="space-y-3 sm:space-y-4">
        <SplitLines
          lines={["Non organizziamo eventi."]}
          className="font-display text-[11vw] leading-[0.92] sm:text-[5.5vw]"
        />
        <SplitLines
          lines={["Costruiamo momenti che restano."]}
          className="font-display text-[11vw] leading-[0.92] text-paper/60 sm:text-[5.5vw]"
          delay={0.1}
        />
      </div>

      <div className="mt-16 grid gap-8 sm:mt-24 sm:grid-cols-3 sm:gap-10">
        <AnimatedWords
          text="beforeafter nasce a Roma nel 2024, tra chi crede che una serata ben costruita sia un atto culturale."
          className="font-serif-italic text-xl leading-snug text-paper/70 sm:col-span-2 sm:text-2xl"
        />
        <div className="font-mono-label flex flex-col gap-4 text-paper/60">
          <div>
            <div className="opacity-60">Cosa facciamo</div>
            <div className="mt-1 text-paper">Eventi · Direzione artistica · Spazi</div>
          </div>
          <div>
            <div className="opacity-60">Dove</div>
            <div className="mt-1 text-paper">Roma — Milano — Napoli</div>
          </div>
        </div>
      </div>
    </section>
  );
}
