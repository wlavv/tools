import * as THREE from "three";
import { GLTFLoader } from "three/examples/jsm/loaders/GLTFLoader.js";

async function loadARjsBundle() {
  await import(/* @vite-ignore */ "https://cdn.jsdelivr.net/gh/AR-js-org/AR.js/three.js/build/ar-threex.js");
  if (!window.THREEx?.ArToolkitSource) throw new Error("AR.js bundle loaded but THREEx not found.");
}

export async function startARjsSession({ canvas, patternUrl, glbUrl, onFound }) {
  await loadARjsBundle();

  // IMPORTANT: do NOT create a second WebGL context on the same canvas used by the 3D viewer.
  // We create a temporary overlay canvas for AR tracking, and remove it on stop.
  const baseCanvas = canvas;
  const container = baseCanvas.parentElement || document.body;

  const overlayCanvas = document.createElement("canvas");
  overlayCanvas.className = "ar-overlay-canvas";
  overlayCanvas.style.position = "absolute";
  overlayCanvas.style.inset = "0";
  overlayCanvas.style.width = "100%";
  overlayCanvas.style.height = "100%";
  overlayCanvas.style.zIndex = "5";
  overlayCanvas.style.pointerEvents = "none";

  try {
    const cs = getComputedStyle(container);
    if (cs.position === "static") container.style.position = "relative";
  } catch {}

  container.appendChild(overlayCanvas);

  const prevVis = baseCanvas.style.visibility;
  baseCanvas.style.visibility = "hidden";

  const renderer = new THREE.WebGLRenderer({ canvas: overlayCanvas, antialias: false, alpha: true });
  {
  const rect = (baseCanvas || overlayCanvas).getBoundingClientRect();
  renderer.setSize(Math.max(1, Math.floor(rect.width)), Math.max(1, Math.floor(rect.height)), false);
}
renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

  const scene = new THREE.Scene();
  const camera = new THREE.Camera();
  scene.add(camera);

  // AR toolkit source (camera)
  const source = new window.THREEx.ArToolkitSource({ sourceType: "webcam" });

  await new Promise((resolve) => {
    source.init(() => resolve());
  });

  // Resize
  const resize = () => {
    source.onResizeElement();
    source.copyElementSizeTo(renderer.domElement);
    if (typeof context !== 'undefined' && context && context.arController !== null) {
      source.copyElementSizeTo(context.arController.canvas);
    }
  };
  window.addEventListener("resize", resize);

  // AR toolkit context
  const context = new window.THREEx.ArToolkitContext({
    cameraParametersUrl: "https://cdn.jsdelivr.net/gh/AR-js-org/AR.js/three.js/data/camera_para.dat",
    detectionMode: "mono"
  });

  await new Promise((resolve) => {
    context.init(() => resolve());
  });

  camera.projectionMatrix.copy(context.getProjectionMatrix());

  // Marker root + controls
  const markerRoot = new THREE.Group();
  markerRoot.visible = false;
  scene.add(markerRoot);

  new window.THREEx.ArMarkerControls(context, markerRoot, {
    type: "pattern",
    patternUrl
  });

  // Load model and attach to marker root
  const loader = new GLTFLoader();
  const gltf = await loader.loadAsync(glbUrl);
  const model = gltf.scene;
  markerRoot.add(model);

  let found = false;
  const tick = () => {
    if (!running) return;
    if (source.ready) context.update(source.domElement);

    // Detect first visibility -> found
    if (!found && markerRoot.visible) {
      found = true;
      onFound?.();
    }

    renderer.render(scene, camera);
    requestAnimationFrame(tick);
  };

  let running = true;
  tick();
  resize();

  const stop = async () => {
  running = false;
  window.removeEventListener("resize", resize);
  // Stop webcam tracks
  const video = source.domElement;
  if (video?.srcObject) {
    try { video.srcObject.getTracks().forEach(t => t.stop()); } catch {}
  }
  // Best-effort clean (AR.js attaches video to body in some builds)
  try { source.domElement?.remove(); } catch {}

  // Dispose renderer + release GL context
  try { renderer.dispose?.(); } catch {}
  try { renderer.forceContextLoss?.(); } catch {}

  // Restore base canvas
  try { baseCanvas.style.visibility = prevVis || ""; } catch {}

  // Remove overlay canvas
  try { overlayCanvas.remove(); } catch {}
};

  return { stop, isFound: () => found };
}

export async function tryARjs({ canvas, patternUrl, glbUrl, timeoutMs = 12000 }) {
  let session;
  let timer;
  try {
    let resolveFound;
    const foundPromise = new Promise((res)=> resolveFound = res);
    session = await startARjsSession({
      canvas, patternUrl, glbUrl,
      onFound: () => resolveFound(true)
    });

    const timeoutPromise = new Promise((res)=> {
      timer = setTimeout(()=>res(false), timeoutMs);
    });

    const ok = await Promise.race([foundPromise, timeoutPromise]);
    clearTimeout(timer);

    if (!ok) {
      await session.stop();
      return { ok: false, session: null };
    }
    return { ok: true, session };
  } catch (e) {
    try { if (session) await session.stop(); } catch {}
    throw e;
  }
}
