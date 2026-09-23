import Hero from "@/components/sections/Hero";
import NextEvent from "@/components/sections/NextEvent";
import MemoryGallery from "@/components/sections/MemoryGallery";
import EventArchive from "@/components/sections/EventArchive";
import Manifesto from "@/components/sections/Manifesto";
import Footer from "@/components/sections/Footer";

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
