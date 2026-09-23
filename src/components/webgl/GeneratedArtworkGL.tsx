"use client";

import { useMemo, useRef, useState } from "react";
import { Canvas, useFrame, useThree, type ThreeEvent } from "@react-three/fiber";
import * as THREE from "three";
import clsx from "clsx";
import "@/components/webgl/artworkMaterial";
import { useInView } from "framer-motion";
import { useWebGLSupported } from "@/lib/hooks/useWebGLSupported";
import { useReducedMotion } from "@/lib/hooks/useReducedMotion";
import GeneratedArtwork from "@/components/media/GeneratedArtwork";

interface GeneratedArtworkGLProps {
  seed: number;
  label: string;
  className?: string;
  toneClassName?: string;
}

const GOLD = ["#c9a876", "#9c7f4d"];
const CHROME = ["#4a4d52", "#1c1a17"];

function seededRandom(seed: number) {
  let s = (seed + 1) * 9301 + 49297;
  return () => {
    s = (s * 9301 + 49297) % 233280;
    return s / 233280;
  };
}

interface PlaneProps {
  seed: number;
  interactive: boolean;
}

function ArtworkPlane({ seed, interactive }: PlaneProps) {
  const { viewport } = useThree();
  const matRef = useRef<InstanceType<typeof THREE.ShaderMaterial> | null>(
    null
  );
  const hover = useRef(0);
  const targetHover = useRef(0);
  const mouse = useRef(new THREE.Vector2(0.5, 0.5));

  const uniforms = useMemo(() => {
    const rand = seededRandom(seed);
    return {
      colorA: new THREE.Color(GOLD[seed % GOLD.length]),
      colorB: new THREE.Color(CHROME[seed % CHROME.length]),
      centerA: new THREE.Vector2(0.15 + rand() * 0.45, 0.15 + rand() * 0.7),
      centerB: new THREE.Vector2(0.4 + rand() * 0.5, 0.2 + rand() * 0.6),
      radiusA: 0.45 + rand() * 0.35,
      radiusB: 0.5 + rand() * 0.4,
    };
  }, [seed]);

  useFrame((state) => {
    const mat = matRef.current as unknown as Record<string, unknown> | null;
    if (!mat) return;
    hover.current += (targetHover.current - hover.current) * 0.08;
    mat.uHover = hover.current;
    mat.uMouse = mouse.current;
    mat.uTime = state.clock.elapsedTime;
  });

  const handleMove = (e: ThreeEvent<PointerEvent>) => {
    if (!interactive) return;
    if (e.uv) mouse.current.set(e.uv.x, e.uv.y);
    targetHover.current = 1;
  };

  const handleOut = () => {
    targetHover.current = 0;
  };

  return (
    <mesh
      scale={[viewport.width, viewport.height, 1]}
      onPointerMove={handleMove}
      onPointerOut={handleOut}
    >
      <planeGeometry args={[1, 1]} />
      <artworkMaterial
        ref={matRef}
        uColorBg={new THREE.Color("#0a0a0a")}
        uColorA={uniforms.colorA}
        uColorB={uniforms.colorB}
        uCenterA={uniforms.centerA}
        uCenterB={uniforms.centerB}
        uRadiusA={uniforms.radiusA}
        uRadiusB={uniforms.radiusB}
      />
    </mesh>
  );
}

/**
 * Real WebGL replacement for GeneratedArtwork: a procedural gradient shader
 * (same palette/language) with a pointer-driven ripple distortion that
 * settles back to rest — falls back to the CSS version when WebGL isn't
 * available, when reduced motion is requested, or before the tile scrolls
 * into view (each Canvas is its own GL context, so mounting is lazy to
 * keep the concurrent-context count low).
 */
export default function GeneratedArtworkGL({
  seed,
  label,
  className,
  toneClassName,
}: GeneratedArtworkGLProps) {
  const ref = useRef<HTMLDivElement>(null);
  const inView = useInView(ref, { margin: "200px 0px 200px 0px" });
  const webglSupported = useWebGLSupported();
  const reducedMotion = useReducedMotion();
  const [lostContext, setLostContext] = useState(false);

  const canRenderGL = webglSupported && inView && !lostContext;

  return (
    <div
      ref={ref}
      className={clsx("relative overflow-hidden bg-night", className)}
    >
      {canRenderGL ? (
        <Canvas
          dpr={[1, 1.5]}
          gl={{ antialias: false, powerPreference: "low-power" }}
          style={{ position: "absolute", inset: 0 }}
          onCreated={({ gl }) => {
            gl.domElement.addEventListener(
              "webglcontextlost",
              () => setLostContext(true),
              { once: true }
            );
          }}
        >
          <ArtworkPlane seed={seed} interactive={!reducedMotion} />
        </Canvas>
      ) : (
        <div className="absolute inset-0">
          <GeneratedArtwork seed={seed} label="" className="h-full w-full" />
        </div>
      )}

      <span
        className={clsx(
          "font-display pointer-events-none relative z-10 block w-full break-words p-4 text-[16vw] leading-[0.8] text-paper/90 sm:p-6 sm:text-[8vw]",
          toneClassName
        )}
      >
        {label}
      </span>
    </div>
  );
}
