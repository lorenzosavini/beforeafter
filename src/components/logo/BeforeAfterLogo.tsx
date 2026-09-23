import clsx from "clsx";

interface BeforeAfterLogoProps {
  className?: string;
  panelClassName?: string;
}

/**
 * Placeholder wordmark built as real DOM/typography (not a raster fake),
 * structured so it can be swapped 1:1 for the official vector logo later —
 * every consumer renders <BeforeAfterLogo /> rather than raw text.
 * "after" sits in an inverted panel: the brand's own before/after contrast
 * lives inside the mark itself.
 */
export default function BeforeAfterLogo({
  className,
  panelClassName,
}: BeforeAfterLogoProps) {
  return (
    <span
      className={clsx(
        "font-display inline-flex items-baseline leading-none select-none",
        className
      )}
    >
      <span className="inline-block">before</span>
      <span
        className={clsx(
          "ml-[0.06em] inline-block bg-ink px-[0.14em] text-paper",
          panelClassName
        )}
      >
        after
      </span>
    </span>
  );
}
