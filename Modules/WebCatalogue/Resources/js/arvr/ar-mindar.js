import * as THREE from "three";
import { GLTFLoader } from "three/examples/jsm/loaders/GLTFLoader.js";

function sleep(ms){ return new Promise(r=>setTimeout(r,ms)); }

async function loadMindARBundle() {
  // MindAR exposes window.MINDAR.IMAGE.MindARThree in this bundle
  await import(/* @vite-ignore */ "https://cdn.jsdelivr.net/npm/mind-ar@1.2.5/dist/mindar-image-three.prod.js");
  if (!window.MINDAR?.IMAGE?.MindARThree) throw new Error("MindAR bundle loaded but MindARThree not found.");
}

export async function startMindARSession({ canvas, targetUrl, glbUrl, onFound }) {
  await loadMindARBundle();

  // MindAR creates its own WebGL canvas in the container.
  // Hide the existing viewer canvas while MindAR runs to avoid overlapping renders.
  const baseCanvas = canvas;
  const prevVis = baseCanvas?.style?.visibility;
  try { if (baseCanvas) baseCanvas.style.visibility = "hidden"; } catch {}


  const MindARThree = window.MINDAR.IMAGE.MindARThree;

  // Create MindAR context on same canvas
  const mindarThree = new MindARThree({
    container: canvas.parentElement, // the #viewer div
    imageTargetSrc: targetUrl,
    uiScanning: true,
    uiLoading: true
  });

  const { renderer, scene, camera } = mindarThree;

  // Ensure we render to our canvas
  renderer.setSize(canvas.clientWidth, canvas.clientHeight, false);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

  // Load model and attach to anchor
  const loader = new GLTFLoader();
  const gltf = await loader.loadAsync(glbUrl);
  const model = gltf.scene;

  // Basic scale normalization (optional)
  model.scale.setScalar(1);

  const anchor = mindarThree.addAnchor(0);
  anchor.group.add(model);

  let found = false;
  anchor.onTargetFound = () => {
    if (found) return;
    found = true;
    onFound?.();
  };

  await mindarThree.start();

  renderer.setAnimationLoop(() => {
    renderer.render(scene, camera);
  });

  const stop = async () => {
  renderer.setAnimationLoop(null);
  try { await mindarThree.stop(); } catch {}

  // Dispose renderer + release GL context
  try { renderer.dispose?.(); } catch {}
  try { renderer.forceContextLoss?.(); } catch {}

  // Clean DOM elements MindAR may inject
  const uiEls = canvas.parentElement.querySelectorAll(".mindar-ui-overlay, .mindar-ui-loading, .mindar-ui-scanning");
  uiEls.forEach(el => el.remove());

  // MindAR injects its own canvas element(s). Remove any extra canvases except the base canvas.
  try {
    const canvases = canvas.parentElement.querySelectorAll("canvas");
    canvases.forEach(c => { if (c !== baseCanvas) c.remove(); });
  } catch {}

  // Restore viewer canvas
  try { if (baseCanvas) baseCanvas.style.visibility = prevVis || ""; } catch {}
};

  return { stop, isFound: () => found };
}

export async function tryMindAR({ canvas, targetUrl, glbUrl, timeoutMs = 12000 }) {
  let session;
  let timer;
  try {
    let resolveFound;
    const foundPromise = new Promise((res)=> resolveFound = res);

    session = await startMindARSession({
      canvas, targetUrl, glbUrl,
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
