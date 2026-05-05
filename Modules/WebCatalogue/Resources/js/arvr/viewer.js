import * as THREE from "three";
import { GLTFLoader } from "three/examples/jsm/loaders/GLTFLoader.js";
import { OrbitControls } from "three/examples/jsm/controls/OrbitControls.js";

/**
 * Viewer 3D with WebXR-friendly renderer:
 * - alpha:true so immersive-ar can composite camera feed (transparent background)
 * - keep opaque background in normal 3D mode via scene.background + clear alpha 1
 *
 * Patch focus:
 * - Improve perceived sharpness (Quest 3 / WebXR browsers)
 * - Avoid low-res rendering caused by DPR caps
 * - Make background pano sampling "sharp mode" by default
 *
 * NEW:
 * - If glbUrl not provided -> uses a TorusKnot test mesh as root
 * - Adds setRootFromGLB(glbUrl) and setRootPrimitive(shapeKey) to swap objects dynamically
 */
export async function createViewer({ canvas, glbUrl } = {}) {
  if (!canvas) throw new Error("createViewer: canvas is required");

  function createRendererWithFallback(optsList) {
    let lastErr = null;
    for (const opts of optsList) {
      try {
        const r = new THREE.WebGLRenderer(opts);
        const gl = r.getContext?.();
        if (!gl) throw new Error("WebGL context is null");
        return r;
      } catch (e) {
        lastErr = e;
      }
    }
    throw lastErr || new Error("Failed to create WebGLRenderer");
  }

  const renderer = createRendererWithFallback([
    { canvas, antialias: true, alpha: true, powerPreference: "high-performance" },
    { canvas, antialias: false, alpha: true, powerPreference: "high-performance" },
    { canvas, antialias: false, alpha: true },
    { canvas, antialias: false, alpha: false },
  ]);

  const computeTargetPixelRatio = () => {
    const dpr = window.devicePixelRatio || 1;
    const cap = 2.0;
    const isQuest = /OculusBrowser/i.test(navigator.userAgent);
    const minBoost = isQuest ? 1.5 : 1.0;
    return Math.min(Math.max(dpr, minBoost), cap);
  };

  const applySizeAndDPR = () => {
    const rect = canvas.getBoundingClientRect();
    const w = Math.max(1, Math.floor(rect.width));
    const h = Math.max(1, Math.floor(rect.height));
    renderer.setSize(w, h, false);
    renderer.setPixelRatio(computeTargetPixelRatio());
    return { w, h };
  };

  const { w: initW, h: initH } = applySizeAndDPR();

  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.0;

  try {
    // eslint-disable-next-line no-console
    console.log("[WebCatalogue] maxTextureSize:", renderer.capabilities.maxTextureSize);
  } catch {}

  const pmrem = new THREE.PMREMGenerator(renderer);
  pmrem.compileEquirectangularShader();

  const scene = new THREE.Scene();
  scene.background = new THREE.Color(0x0f0f10);
  renderer.setClearColor(0x0f0f10, 1);

  const camera = new THREE.PerspectiveCamera(55, initW / initH, 0.01, 200);
  camera.position.set(0.9, 0.6, 1.5);

  const controls = new OrbitControls(camera, canvas);
  controls.enableDamping = true;

  scene.add(new THREE.HemisphereLight(0xffffff, 0x222222, 1.0));
  const dir = new THREE.DirectionalLight(0xffffff, 1.0);
  dir.position.set(2, 3, 2);
  scene.add(dir);

  const __env = {
    mode: "solid",
    solid: "#0f0f10",
    hdriUrl: "/envs/360_store_ultra_clean.png",
    room: null,
    tex: null,
    envMap: null,
  };

  function ensureRoom() {
    if (__env.room) return __env.room;
    const mat = new THREE.MeshStandardMaterial({
      color: 0x1b1e23,
      roughness: 1,
      metalness: 0,
      side: THREE.BackSide,
    });
    const room = new THREE.Mesh(new THREE.BoxGeometry(12, 6, 12), mat);
    room.name = "wc_room";
    room.position.set(0, 2.5, 0);
    __env.room = room;
    return room;
  }

  async function loadHDRI(url) {
    const loader = new THREE.TextureLoader();
    const tex = await loader.loadAsync(url);

    tex.mapping = THREE.EquirectangularReflectionMapping;

    try {
      tex.colorSpace = THREE.SRGBColorSpace;
    } catch {}

    const SHARP_MODE = true;

    if (SHARP_MODE) {
      tex.generateMipmaps = false;
      tex.minFilter = THREE.LinearFilter;
      tex.magFilter = THREE.LinearFilter;
    } else {
      tex.generateMipmaps = true;
      tex.minFilter = THREE.LinearMipmapLinearFilter;
      tex.magFilter = THREE.LinearFilter;
    }

    const maxAniso = renderer.capabilities.getMaxAnisotropy?.() || 1;
    tex.anisotropy = Math.min(8, maxAniso);
    tex.wrapS = THREE.ClampToEdgeWrapping;
    tex.wrapT = THREE.ClampToEdgeWrapping;
    tex.needsUpdate = true;

    let envMap = null;
    try {
      const pm = pmrem.fromEquirectangular(tex);
      envMap = pm.texture;
      pm.dispose();
    } catch {}

    return { tex, envMap };
  }

  async function setEnvironment(mode, opts = {}) {
    __env.mode = mode || __env.mode;
    if (opts.solid) __env.solid = opts.solid;
    if (opts.hdriUrl) __env.hdriUrl = opts.hdriUrl;

    try {
      scene.environment = null;
    } catch {}
    try {
      scene.remove(scene.getObjectByName("wc_room"));
    } catch {}
    if (__env.tex) {
      try {
        __env.tex.dispose?.();
      } catch {}
      __env.tex = null;
    }
    if (__env.envMap) {
      try {
        __env.envMap.dispose?.();
      } catch {}
      __env.envMap = null;
    }

    if (__env.mode === "none") {
      scene.background = null;
      try {
        renderer.setClearColor(0x000000, 1);
      } catch {}
      return { mode: __env.mode, ok: true };
    }

    if (__env.mode === "solid") {
      scene.background = new THREE.Color(__env.solid);
      try {
        renderer.setClearColor(new THREE.Color(__env.solid), 1);
      } catch {}
      return { mode: __env.mode, ok: true };
    }

    if (__env.mode === "room") {
      scene.background = new THREE.Color(0x0f0f10);
      try {
        renderer.setClearColor(0x0f0f10, 1);
      } catch {}
      scene.add(ensureRoom());
      return { mode: __env.mode, ok: true };
    }

    try {
      const { tex, envMap } = await loadHDRI(__env.hdriUrl);
      __env.tex = tex;
      __env.envMap = envMap;

      scene.background = tex;
      scene.environment = envMap || tex;

      try {
        renderer.setClearColor(0x0f0f10, 1);
      } catch {}

      return { mode: __env.mode, ok: true };
    } catch (e) {
      scene.background = new THREE.Color(0x0f0f10);
      try {
        renderer.setClearColor(0x0f0f10, 1);
      } catch {}
      scene.add(ensureRoom());
      __env.mode = "room";
      return { mode: __env.mode, ok: false, error: String(e) };
    }
  }

  // --------------------------
  // ROOT SWAP HELPERS (NEW)
  // --------------------------
  let root = null;

  function disposeObject3D(obj) {
    if (!obj) return;
    obj.traverse?.((n) => {
      if (n.geometry) n.geometry.dispose?.();
      if (n.material) {
        if (Array.isArray(n.material)) n.material.forEach((m) => m.dispose?.());
        else n.material.dispose?.();
      }
    });
  }

  function recenterAndFit(obj) {
    if (!obj) return;

    const box = new THREE.Box3().setFromObject(obj);
    const size = box.getSize(new THREE.Vector3());
    const center = box.getCenter(new THREE.Vector3());
    obj.position.sub(center);

    const maxDim = Math.max(size.x, size.y, size.z) || 1;
    const dist = maxDim * 2.2;
    camera.position.set(dist * 0.55, dist * 0.35, dist * 0.75);
    camera.lookAt(0, 0, 0);
    controls.target.set(0, 0, 0);
  }

  async function setRootFromGLB(newGlbUrl) {
    if (!newGlbUrl || !String(newGlbUrl).trim()) return;

    if (root) {
      scene.remove(root);
      disposeObject3D(root);
      root = null;
    }

    const loader = new GLTFLoader();
    const gltf = await loader.loadAsync(newGlbUrl);
    root = gltf.scene;
    scene.add(root);

    recenterAndFit(root);
  }

  function setRootPrimitive(shapeKey = "torusknot") {
    if (root) {
      scene.remove(root);
      disposeObject3D(root);
      root = null;
    }

    let geom;
    switch (shapeKey) {
      case "sphere":
        geom = new THREE.SphereGeometry(0.16, 48, 32);
        break;
      case "pyramid":
        // cone com 4 lados = pirâmide
        geom = new THREE.ConeGeometry(0.18, 0.30, 4, 1);
        break;
      case "cone":
        geom = new THREE.ConeGeometry(0.16, 0.30, 64, 1);
        break;
      case "parallelepiped":
        geom = new THREE.BoxGeometry(0.28, 0.18, 0.22);
        break;
      case "cylinder":
        geom = new THREE.CylinderGeometry(0.14, 0.14, 0.28, 64);
        break;
      case "irregular_01":
        geom = new THREE.IcosahedronGeometry(0.18, 1);
        break;
      case "irregular_02":
        geom = new THREE.DodecahedronGeometry(0.18, 0);
        break;
      default:
        geom = new THREE.TorusKnotGeometry(0.18, 0.06, 220, 32);
        break;
    }

    const mat = new THREE.MeshStandardMaterial({
      color: 0xffffff,
      metalness: 0.55,
      roughness: 0.3,
    });

    root = new THREE.Mesh(geom, mat);
    root.name = `wc_shape_${shapeKey}`;
    scene.add(root);

    recenterAndFit(root);
  }

  // Init root (GLB if provided, else TorusKnot)
  const shouldUseGLB = typeof glbUrl === "string" && glbUrl.trim().length > 0;
  if (shouldUseGLB) await setRootFromGLB(glbUrl);
  else setRootPrimitive("torusknot");

  function resize() {
    const { w, h } = applySizeAndDPR();
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
  }
  window.addEventListener("resize", resize);

  const updaters = new Set();
  const addUpdater = (fn) => updaters.add(fn);
  const removeUpdater = (fn) => updaters.delete(fn);

  let running = true;
  function loop() {
    if (!running) return;
    controls.update();
    for (const fn of updaters) fn({ renderer, scene, camera, root });
    renderer.render(scene, camera);
    requestAnimationFrame(loop);
  }
  loop();

  return {
    renderer,
    scene,
    camera,
    controls,
    root: () => root,

    setEnvironment: (mode, opts) => setEnvironment(mode, opts),
    getEnvironment: () => ({
      mode: __env?.mode,
      solid: __env?.solid,
      hdriUrl: __env?.hdriUrl,
    }),

    // NEW public API
    setRootFromGLB,
    setRootPrimitive,

    addUpdater,
    removeUpdater,

    pause: () => {
      running = false;
    },
    resume: () => {
      if (!running) {
        running = true;
        loop();
      }
    },
  };
}