import type { Metadata, Viewport } from "next";
import { Jost, Fraunces, Inter } from "next/font/google";
import "./globals.css";
import SmoothScrollProvider from "@/components/system/SmoothScrollProvider";
import Cursor from "@/components/system/Cursor";
import DiscoveryBadge from "@/components/system/DiscoveryBadge";
import Navigation from "@/components/nav/Navigation";

const jost = Jost({
  variable: "--font-jost",
  subsets: ["latin"],
  weight: ["300", "400", "500"],
  display: "swap",
});

const fraunces = Fraunces({
  variable: "--font-fraunces",
  subsets: ["latin"],
  style: ["italic", "normal"],
  weight: ["400", "500"],
  display: "swap",
});

const inter = Inter({
  variable: "--font-inter",
  subsets: ["latin"],
  weight: ["400", "500", "600"],
  display: "swap",
});

export const metadata: Metadata = {
  title: "beforeafter",
  description:
    "beforeafter — nightlife and cultural events. The experience begins before and continues after.",
};

export const viewport: Viewport = {
  themeColor: "#0a0a0a",
};

export default function RootLayout({
  children,
}: Readonly<{ children: React.ReactNode }>) {
  return (
    <html
      lang="en"
      className={`${jost.variable} ${fraunces.variable} ${inter.variable}`}
    >
      <body>
        <SmoothScrollProvider>
          <Cursor />
          <DiscoveryBadge />
          <Navigation />
          {children}
        </SmoothScrollProvider>
      </body>
    </html>
  );
}
