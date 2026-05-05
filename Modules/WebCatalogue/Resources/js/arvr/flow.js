import { toast, setStatus, showARControls } from "./ui.js";
import { State } from "./state.js";
import { openQuickLookUSDZ } from "./ios-quicklook.js";
import { enterVR, enterAR } from "./xr-webxr.js";
import { tryMindAR } from "./ar-mindar.js";
import { tryARjs } from "./ar-arjs.js";
import { emit } from "./telemetry.js";
import { HotspotManager } from "./hotspots.js";
import { ExplodeAnimator } from "./animations.js";

let activeARSession = null;
let hotspotMgr = null;
let explodeAnim = null;
let explodeActive = false;
let hotspotUpdater = null;
let explodeUpdater = null;

function dispatchSessionEnded(reason = "unknown") {
  try {
    document.dispatchEvent(new CustomEvent("arvr-session-ended", { detail: { reason } }));
  } catch {
    document.dispatchEvent(new Event("arvr-session-ended"));
  }
}

function setExplodeUIButton(active) {
  window.__arvrUISetExplodeActive?.(!!active);
}

export function bindExitAR(viewer, appState) {
  document.querySelector("#btnExitAR").addEventListener("click", async () => {
    await stopActiveAR(viewer, appState);
    toast("AR terminada. Viewer 3D retomado.");
  });

  document.querySelector("#btnToggleExplode").addEventListener("click", () => {
    if (!explodeAnim) return;

    // deterministic toggle (so we can keep UI state in sync)
    const next = !explodeActive;
    if (typeof explodeAnim.setEnabled === "function") {
      explodeAnim.setEnabled(next);
    } else {
      explodeAnim.toggle?.();
    }
    explodeActive = next;
    setExplodeUIButton(explodeActive);
  });
}

async function stopActiveAR(viewer, appState) {
  showARControls(false);

  // remove updaters
  if (hotspotUpdater) viewer.removeUpdater(hotspotUpdater);
  if (explodeUpdater) viewer.removeUpdater(explodeUpdater);
  hotspotUpdater = null;
  explodeUpdater = null;

  // clear hotspots
  if (hotspotMgr) {
    hotspotMgr.clear();
    hotspotMgr.setVisible(false);
  }
  hotspotMgr = null;

  // reset explode
  if (explodeAnim) {
    try { explodeAnim.setEnabled(false); } catch {}
  }
  explodeAnim = null;
  explodeActive = false;
  setExplodeUIButton(false);

  if (activeARSession) {
    try { await activeARSession.stop(); } catch {}
    activeARSession = null;
  }

  viewer.resume();
  appState.set(State.VIEW_3D);
  emit("ar_end", { reason: "user_or_fallback" });
  dispatchSessionEnded("user_or_fallback");
}

function enableHotspotsAndAnimations(viewer, product) {
  const layer = document.querySelector("#hotspotLayer");

  hotspotMgr = new HotspotManager({ layerEl: layer, camera: viewer.camera });
  hotspotMgr.load(product.hotspots || []);
  hotspotMgr.setVisible(true);

  hotspotUpdater = ({ renderer, scene }) => hotspotMgr.update(renderer, scene);
  viewer.addUpdater(hotspotUpdater);

  // Explode
  if (product.explode?.enabled) {
    explodeAnim = new ExplodeAnimator(viewer.root, product.explode.factor ?? 0.18);
    explodeActive = false;
    setExplodeUIButton(false);
    explodeUpdater = () => explodeAnim.update(1.0);
    viewer.addUpdater(explodeUpdater);
  }
}

export async function startFlow({ intent, caps, appState, product, viewer }) {
  setStatus(`intent=${intent} state=${appState.state}`);
  emit("intent_start", { intent, product_id: product.id });

  // Ensure any running AR overlay is closed
  if (activeARSession) await stopActiveAR(viewer, appState);

  if (intent === "VR") {
    if (!caps.xrVR) {
      toast("VR (WebXR immersive-vr) não disponível neste dispositivo.");
      emit("vr_unavailable", {});
      return;
    }
    appState.set(State.XR_VR);
    toast("A entrar em VR…");
    emit("vr_start", {});
    try {
      const s = await enterVR(viewer);
      activeARSession = { stop: () => s?.end?.() };
      showARControls(true);
      // If the underlying XRSession ends (system/back), return to 3D mode cleanly
      try {
        s?.addEventListener?.("end", () => {
          stopActiveAR(viewer, appState);
        }, { once: true });
      } catch {}
    } catch (e) {
      console.warn(e);
      toast("Falha ao iniciar VR. Mantém-se viewer 3D.");
      appState.set(State.VIEW_3D);
      emit("vr_error", { message: String(e?.message || e) });
    }
    return;
  }

  if (intent === "PLACE") {
    // PLACE = placement AR (WebXR) or iOS Quick Look.
    // It should NOT fall back to tracking/recognize, otherwise you get "Não é possível reconhecer..."
    if (caps.ios && caps.canQuickLook) {
      if (!product.usdzUrl) {
        toast("iOS Quick Look requer um modelo .usdz configurado. Mantém-se viewer 3D.");
        emit("quicklook_missing_usdz", { product_id: product.id });
        return;
      }
      toast("A abrir AR (Quick Look)…");
      emit("quicklook_open", { product_id: product.id });
      openQuickLookUSDZ(product.usdzUrl);
      return;
    }

    if (!caps.xrAR) {
      toast("AR (PLACE) via WebXR não disponível neste dispositivo. Usa 'Reconhecer' (tracking) ou mantém 3D.");
      emit("webxr_ar_unavailable", { product_id: product.id });
      return;
    }

    appState.set(State.XR_AR);
    toast("A iniciar AR via WebXR…");
    emit("webxr_ar_start", { product_id: product.id });
    try {
      const s = await enterAR(viewer);
      activeARSession = { stop: () => s?.end?.() };
      showARControls(true);
      // If the underlying XRSession ends (system/back), return to 3D mode cleanly
      try {
        s?.addEventListener?.("end", () => {
          stopActiveAR(viewer, appState);
        }, { once: true });
      } catch {}
      emit("webxr_ar_ok", { product_id: product.id });
      return;
    } catch (e) {
      console.warn(e);
      toast("Falha ao iniciar WebXR AR. Mantém-se viewer 3D (podes tentar 'Reconhecer').");
      appState.set(State.VIEW_3D);
      emit("webxr_ar_error", { message: String(e?.message || e) });
      return;
    }
  }

  if (intent === "RECOGNIZE") {
    if (!caps.hasCamera) {
      toast("Sem acesso à câmara. Mantém-se viewer 3D.");
      appState.set(State.VIEW_3D);
      emit("camera_unavailable", {});
      return;
    }
    return await recognizeThenFallback({ appState, product, viewer });
  }
}

async function recognizeThenFallback({ appState, product, viewer }) {
  // Guardrails: require at least one tracking source URL
  if (!product.mindTargetUrl && !product.arjsPatternUrl) {
    toast("Sem ficheiros de tracking configurados (mind/patt). Mantém-se viewer 3D.");
    emit("tracking_missing_assets", {});
    return;
  }

  viewer.pause();
  showARControls(true);

  // 1) MindAR
  if (product.mindTargetUrl) {
    appState.set(State.TRACK_IMAGE);
    toast("A procurar imagem alvo (MindAR)…");
    emit("mindar_start", { target: product.mindTargetUrl });

    try {
      const res = await tryMindAR({
        canvas: viewer.renderer.domElement,
        targetUrl: product.mindTargetUrl,
        glbUrl: product.glbUrl,
        timeoutMs: 12000
      });

      if (res.ok) {
        activeARSession = res.session;
        appState.set(State.AR_RUNNING);
        emit("mindar_success", {});
        toast("Imagem reconhecida. AR ativa (MindAR).");
        enableHotspotsAndAnimations(viewer, product);
        return;
      }
      emit("mindar_timeout", {});
    } catch (e) {
      console.warn(e);
      emit("mindar_error", { message: String(e?.message || e) });
    }
  }

  // 2) AR.js marker fallback
  if (product.arjsPatternUrl) {
    appState.set(State.TRACK_MARKER);
    toast("Falhou imagem. A tentar marcador (AR.js)…");
    emit("arjs_start", { pattern: product.arjsPatternUrl });

    try {
      const res2 = await tryARjs({
        canvas: viewer.renderer.domElement,
        patternUrl: product.arjsPatternUrl,
        glbUrl: product.glbUrl,
        timeoutMs: 12000
      });

      if (res2.ok) {
        activeARSession = res2.session;
        appState.set(State.AR_RUNNING);
        emit("arjs_success", {});
        toast("Marcador reconhecido. AR ativa (AR.js).");
        enableHotspotsAndAnimations(viewer, product);
        return;
      }
      emit("arjs_timeout", {});
    } catch (e) {
      console.warn(e);
      emit("arjs_error", { message: String(e?.message || e) });
    }
  }

  await stopActiveAR(viewer, appState);
  toast("Não foi possível reconhecer. Melhora a luz, aproxima, ou usa o marcador.");
  emit("fallback_to_3d", { reason: "no_tracking" });
}
