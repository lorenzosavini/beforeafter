import type { Metadata, Viewport } from "next";
import { Rubik_Wet_Paint, Fraunces, Inter, JetBrains_Mono } from "next/font/google";
import "./globals.css";
import SmoothScrollProvider from "@/components/system/SmoothScrollProvider";
import Cursor from "@/components/system/Cursor";
import Navigation from "@/components/nav/Navigation";

const rubikWetPaint = Rubik_Wet_Paint({
  variable: "--font-rubik-wet-paint",
  subsets: ["latin"],
  weight: "400",
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

const jetbrainsMono = JetBrains_Mono({
  variable: "--font-jetbrains",
  subsets: ["latin"],
  weight: ["400", "500"],
  display: "swap",
});

export const metadata: Metadata = {
  title: "beforeafter",
  description:
    "beforeafter — nightlife and cultural events. The experience begins before and continues after.",
};

export const viewport: Viewport = {
  themeColor: "#0d0c0b",
};

export default function RootLayout({
  children,
}: Readonly<{ children: React.ReactNode }>) {
  return (
    <html
      lang="en"
      className={`${rubikWetPaint.variable} ${fraunces.variable} ${inter.variable} ${jetbrainsMono.variable}`}
    >
      <body>
        <SmoothScrollProvider>
          <Cursor />
          <Navigation />
          {children}
        </SmoothScrollProvider>
      </body>
    </html>
  );
}
