import Hero from "@/components/sections/Hero";
import NextEvent from "@/components/sections/NextEvent";
import MemoryGallery from "@/components/sections/MemoryGallery";
import EventArchive from "@/components/sections/EventArchive";
import Manifesto from "@/components/sections/Manifesto";
import Footer from "@/components/sections/Footer";

// Event/gallery data is admin-editable at runtime — never statically
// cache the page that shows it, or new events wouldn't appear without a
// full rebuild.
export const dynamic = "force-dynamic";

export default function Home() {
  return (
    <main>
      <Hero />
      <NextEvent />
      <MemoryGallery />
      <EventArchive />
      <Manifesto />
      <Footer />
    </main>
  );
}
