import { shaderMaterial } from "@react-three/drei";
import { extend } from "@react-three/fiber";
import * as THREE from "three";

const vertexShader = /* glsl */ `
  varying vec2 vUv;
  void main() {
    vUv = uv;
    gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
  }
`;

const fragmentShader = /* glsl */ `
  precision mediump float;
  varying vec2 vUv;

  uniform vec3 uColorBg;
  uniform vec3 uColorA;
  uniform vec3 uColorB;
  uniform vec2 uCenterA;
  uniform vec2 uCenterB;
  uniform float uRadiusA;
  uniform float uRadiusB;
  uniform vec2 uMouse;
  uniform float uHover;
  uniform float uTime;

  float hash(vec2 p) {
    return fract(sin(dot(p, vec2(12.9898, 78.233))) * 43758.5453);
  }

  void main() {
    vec2 uv = vUv;

    /* ripple distortion, centered on the pointer, active only on hover */
    vec2 toMouse = uv - uMouse;
    float dist = length(toMouse);
    float wave = sin(dist * 22.0 - uTime * 3.0) * 0.5 + 0.5;
    float falloff = smoothstep(0.5, 0.0, dist);
    vec2 dir = dist > 0.0001 ? toMouse / dist : vec2(0.0);
    vec2 duv = uv + dir * wave * falloff * uHover * 0.055;

    float da = length(duv - uCenterA) / uRadiusA;
    float db = length(duv - uCenterB) / uRadiusB;

    vec3 color = uColorBg;
    color = mix(color, uColorB, smoothstep(1.0, 0.0, db));
    color = mix(color, uColorA, smoothstep(1.0, 0.0, da));

    float grain = (hash(floor(uv * 400.0)) - 0.5) * 0.045;
    color += grain;

    gl_FragColor = vec4(color, 1.0);
  }
`;

const ArtworkMaterial = shaderMaterial(
  {
    uColorBg: new THREE.Color("#0a0a0a"),
    uColorA: new THREE.Color("#ff3d1f"),
    uColorB: new THREE.Color("#1c1a17"),
    uCenterA: new THREE.Vector2(0.3, 0.7),
    uCenterB: new THREE.Vector2(0.7, 0.3),
    uRadiusA: 0.6,
    uRadiusB: 0.6,
    uMouse: new THREE.Vector2(0.5, 0.5),
    uHover: 0,
    uTime: 0,
  },
  vertexShader,
  fragmentShader
);

extend({ ArtworkMaterial });

declare module "@react-three/fiber" {
  interface ThreeElements {
    artworkMaterial: ThreeElements["shaderMaterial"] & {
      uColorBg?: THREE.Color;
      uColorA?: THREE.Color;
      uColorB?: THREE.Color;
      uCenterA?: THREE.Vector2;
      uCenterB?: THREE.Vector2;
      uRadiusA?: number;
      uRadiusB?: number;
    };
  }
}

export default ArtworkMaterial;
