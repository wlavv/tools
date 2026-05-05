import { createViewer } from "./viewer.js";
import { detectCapabilities, platformLabel } from "./capabilities.js";
import { setChips, toast, bindSidePanel } from "./ui.js";
import { AppState } from "./state.js";
import { startFlow, bindExitAR } from "./flow.js";
import { emit } from "./telemetry.js";

const product = window.__ARVR_PRODUCT__;
const appState = new AppState();

const canvas = document.querySelector("#renderCanvas");
const btn3d = document.querySelector("#btn3d");
const btnPlace = document.querySelector("#btnPlace");
const btnRecognize = document.querySelector("#btnRecognize");
const btnVR = document.querySelector("#btnVR");
const envBtn = document.querySelector("#btnEnv");
const envPanel = document.querySelector("#envPanel");
const envList = document.querySelector("#envList");
const envClose = document.querySelector("#envClose");
const envModeChips = document.querySelectorAll("[data-envmode]");

(async () => {
  const t0 = performance.now();
  const caps = await detectCapabilities();

  setChips({
    platformText: `platform: ${platformLabel(caps)}`,
    capsText: `caps: cam=${caps.hasCamera} webxr=${caps.webxr} vr=${caps.xrVR} ar=${caps.xrAR} iOSQL=${caps.canQuickLook}`
  });

  emit("page_loaded", { product_id: product.id, caps });

  const viewer = await createViewer({ canvas, glbUrl: product.glbUrl });

  bindSidePanel();
  window.__ARVR_VIEWER__ = viewer;

  emit("viewer_ready", { ms: Math.round(performance.now() - t0) });

  bindExitAR(viewer, appState);

  // ---------------------------
  // Environment presets (360 HDRI backgrounds)
  // ---------------------------
  const ENV_PRESETS = [
    { id: "simple_webp", label: "Simple 360 (WebP)", url: "/envs/360_simple.webp" },
    { id: "simple_4k_jpg", label: "Simple 360 (4K JPG)", url: "/envs/360_simple_4k.jpg" },
    { id: "simple_8k_webp", label: "Simple 360 (8K WebP)", url: "/envs/360_simple_8k.webp" },
    { id: "simple_8k_jpg", label: "Simple 360 (8K JPG)", url: "/envs/360_simple_8k.jpg" },

    /**
    { id: "beach", label: "Beach", url: "/envs/360_beach_2.png" },
    { id: "auto_showroom", label: "Auto Showroom", url: "/envs/360_showroom_clean.png" },
    { id: "art_gallery", label: "Art Gallery PNG", url: "/envs/360_art_gallery.png" },
    { id: "art_gallery", label: "Art Gallery JPG", url: "/envs/360_art_gallery.jpg" },
    { id: "art_gallery", label: "Art Gallery WEBP", url: "/envs/360_art_gallery.webp" },
    { id: "art_gallery_night", label: "Art Gallery at night", url: "/envs/360_art_gallery_clean_darker.png" },
    { id: "art_gallery_spot", label: "Art Gallery spotlight", url: "/envs/360_art_gallery_spotlight.png" },
    { id: "store_premium", label: "Store premium", url: "/envs/360_store_showroom.png" },
    { id: "store_clean_light", label: "Store premium clean light", url: "/envs/360_store_clean_light.png" },
    { id: "store_clean_dark", label: "Store premium clean dark", url: "/envs/360_store_clean_dark.png" },
    { id: "store_tech_clean", label: "Store tech clean", url: "/envs/360_store_tech_clean.png" },
    { id: "store_hybrid", label: "Store hybrid", url: "/envs/360_store_ultra_clean.png" },
    { id: "store_toys", label: "Store toys", url: "/envs/360_store_toys.png" },
    { id: "store_toys_clean", label: "Store toys clean", url: "/envs/360_store_toys_clean.png" },
    { id: "store_tcg", label: "Store TCG", url: "/envs/360_store_tcg.png" },
    { id: "store_tcg_collectors", label: "Store TCG-Collectors", url: "/envs/360_store_tcg-collectors.png" },
    { id: "store_specific_tcg_collectors", label: "Store TCG-Collectors Specific", url: "/envs/360_store_specific_tcg-collectors.webp" },
    **/
  ];

  const ENV_STORAGE_KEY = "webcatalogue_env_selection_v1";

  function openEnvPanel(){ envPanel?.classList.add("open"); envPanel?.setAttribute("aria-hidden","false"); }
  function closeEnvPanel(){ envPanel?.classList.remove("open"); envPanel?.setAttribute("aria-hidden","true"); }

  async function applyEnvMode(mode, presetUrl=null){
    const solid = (product?.env?.solid || "#0f0f10");
    const hdriUrl = presetUrl || (product?.env?.hdriUrl || "/envs/360_store_ultra_clean.png");
    const res = await viewer.setEnvironment?.(mode, { solid, hdriUrl });
    if (res && res.ok === false) toast("HDRI falhou → fallback ROOM");
  }

  function renderEnvList(){
    if(!envList) return;
    envList.innerHTML="";
    for(const p of ENV_PRESETS){
      const btn=document.createElement("button");
      btn.type="button";
      btn.className="env-item";
      btn.innerHTML = `<span class="env-dot"></span><span class="env-name">${p.label}</span>`;
      btn.addEventListener("click", async ()=>{
        try{ localStorage.setItem(ENV_STORAGE_KEY, JSON.stringify({ mode:"hdri", presetId:p.id, url:p.url })); }catch{}
        await applyEnvMode("hdri", p.url);
        closeEnvPanel();
      });
      envList.appendChild(btn);
    }
  }

  if(envBtn) envBtn.addEventListener("click", openEnvPanel);
  if(envClose) envClose.addEventListener("click", closeEnvPanel);
  if(envPanel) envPanel.addEventListener("click", (e)=>{ if(e.target===envPanel) closeEnvPanel(); });

  envModeChips?.forEach((el)=>{
    el.addEventListener("click", async ()=>{
      const mode = el.getAttribute("data-envmode");
      if(!mode) return;
      if(mode==="hdri"){
        let saved=null; try{ saved=JSON.parse(localStorage.getItem(ENV_STORAGE_KEY)||"null"); }catch{}
        const url = saved?.url || (product?.env?.hdriUrl || "/envs/360_store_ultra_clean.png");
        try{ localStorage.setItem(ENV_STORAGE_KEY, JSON.stringify({ mode:"hdri", presetId:saved?.presetId||"store_hybrid", url })); }catch{}
        await applyEnvMode("hdri", url);
      } else {
        try{ localStorage.setItem(ENV_STORAGE_KEY, JSON.stringify({ mode })); }catch{}
        await applyEnvMode(mode);
      }
    });
  });

  renderEnvList();
  (async ()=>{
    let saved=null; try{ saved=JSON.parse(localStorage.getItem(ENV_STORAGE_KEY)||"null"); }catch{}
    if(saved?.mode==="hdri") await applyEnvMode("hdri", saved.url);
    else if(saved?.mode) await applyEnvMode(saved.mode);
    else await applyEnvMode("hdri", "/envs/360_store_ultra_clean.png");
  })();

  // UI mode helpers (optional, provided by the Blade template)
  function setMode(mode) {
    window.__arvrUISetMode?.(mode);
  }
  // default
  setMode("3d");

  // When XR session ends (system/back or user exit), return to 3D state
  document.addEventListener("arvr-session-ended", () => setMode("3d"));

  btn3d.addEventListener("click", () => { toast("Viewer 3D ativo."); setMode("3d"); });
btnPlace.addEventListener("click", async () => { setMode("ar"); await startFlow({ intent: "PLACE", caps, appState, product, viewer }); });
btnRecognize.addEventListener("click", async () => { setMode("recognize"); await startFlow({ intent: "RECOGNIZE", caps, appState, product, viewer }); });
btnVR.addEventListener("click", async () => { setMode("vr"); await startFlow({ intent: "VR", caps, appState, product, viewer }); });
btnVR.disabled = !caps.xrVR;
})();
