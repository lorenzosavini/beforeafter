import BeforeAfterMark from "@/components/logo/BeforeAfterMark";

interface BeforeAfterLogoProps {
  className?: string;
}

/** Static usage of the official mark — nav, footer, anywhere the logo sits at rest. */
export default function BeforeAfterLogo({ className }: BeforeAfterLogoProps) {
  return <BeforeAfterMark className={className} />;
}
