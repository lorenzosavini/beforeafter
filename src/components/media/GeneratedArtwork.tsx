import clsx from "clsx";

interface GeneratedArtworkProps {
  seed: number;
  label: string;
  className?: string;
  toneClassName?: string;
}

const GRADIENTS = [
  "radial-gradient(120% 120% at 15% 10%, #ff3d1f 0%, transparent 55%), radial-gradient(120% 120% at 85% 90%, #0d0c0b 0%, transparent 60%)",
  "radial-gradient(110% 140% at 80% 15%, #ff6a45 0%, transparent 50%), linear-gradient(200deg, #0d0c0b 0%, #33302a 100%)",
  "conic-gradient(from 200deg at 50% 50%, #0d0c0b, #33302a, #ff3d1f, #0d0c0b)",
  "radial-gradient(140% 100% at 20% 100%, #c72e14 0%, transparent 55%), radial-gradient(100% 100% at 90% 0%, #1c1a17 0%, transparent 60%)",
  "linear-gradient(160deg, #1c1a17 0%, #0d0c0b 45%, #ff3d1f 130%)",
  "radial-gradient(100% 100% at 50% 0%, #33302a 0%, #0d0c0b 60%)",
  "radial-gradient(130% 90% at 90% 100%, #ff3d1f 0%, transparent 50%), linear-gradient(210deg, #0d0c0b, #1c1a17)",
];

/**
 * Deterministic, seed-driven generated composition standing in for real
 * event photography/artwork — deliberately editorial (numbers + gradient
 * field + noise) rather than a broken-image placeholder or stock photo.
 */
export default function GeneratedArtwork({
  seed,
  label,
  className,
  toneClassName,
}: GeneratedArtworkProps) {
  const gradient = GRADIENTS[seed % GRADIENTS.length];

  return (
    <div
      className={clsx(
        "relative flex items-end overflow-hidden bg-night",
        className
      )}
    >
      <div
        className="absolute inset-0"
        style={{ backgroundImage: gradient }}
      />
      <svg className="absolute inset-0 h-full w-full opacity-[0.08] mix-blend-overlay" aria-hidden>
        <filter id={`noise-${seed}`}>
          <feTurbulence
            type="fractalNoise"
            baseFrequency="0.9"
            numOctaves="2"
            stitchTiles="stitch"
          />
        </filter>
        <rect width="100%" height="100%" filter={`url(#noise-${seed})`} />
      </svg>
      <span
        className={clsx(
          "font-display relative z-10 w-full break-words p-4 text-[16vw] leading-[0.8] text-paper/90 sm:text-[8vw] sm:p-6",
          toneClassName
        )}
      >
        {label}
      </span>
    </div>
  );
}
