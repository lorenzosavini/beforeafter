import clsx from "clsx";

interface ChapterMarkerProps {
  index: string;
  total?: string;
  label: string;
  className?: string;
  tone?: "ink" | "paper";
}

/** Recurring editorial index device that threads the page's chapters together. */
export default function ChapterMarker({
  index,
  total = "06",
  label,
  className,
  tone = "ink",
}: ChapterMarkerProps) {
  return (
    <div
      className={clsx(
        "font-mono-label flex items-center gap-3",
        tone === "paper" ? "text-paper/60" : "text-ink-soft/70",
        className
      )}
    >
      <span>
        {index} / {total}
      </span>
      <span className="h-px w-6 bg-current opacity-50" />
      <span>{label}</span>
    </div>
  );
}
