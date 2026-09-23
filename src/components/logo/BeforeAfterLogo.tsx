import BeforeAfterMark from "@/components/logo/BeforeAfterMark";

interface BeforeAfterLogoProps {
  className?: string;
  variant?: "chrome" | "solid";
}

/** Static usage of the official mark — nav, footer, anywhere the logo sits at rest. */
export default function BeforeAfterLogo({
  className,
  variant,
}: BeforeAfterLogoProps) {
  return <BeforeAfterMark className={className} variant={variant} />;
}
