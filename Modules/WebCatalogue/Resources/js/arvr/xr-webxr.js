const getARInteractionMode = () => (window.__ARVR_AR_INTERACTION_MODE__ || "orbit");

import * as THREE from "three";

/**
 * WebXR helpers (AR/VR) tuned for:
 * - Android AR (S25): hit-test reticle + tap-to-place + DRAG-TO-MOVE using transient hit-test (native-like)
 * - Pinch to scale, two-finger twist to rotate
 * - Quest: avoid dom-overlay (can behave oddly / cause black overlay), use controller inputs to move/rotate/scale
 */

function $(sel) { return document.querySelector(sel); }
function isQuest() { return /OculusBrowser/i.test(navigator.userAgent); }
function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }


function lerp(a, b, t) { return a + (b - a) * t; }
function damp(current, target, lambda, dt) {
  // Exponential smoothing (frame-rate independent)
  return lerp(current, target, 1 - Math.exp(-lambda * dt));
}

function getRayFromPose(pose) {
  const m = new THREE.Matrix4().fromArray(pose.transform.matrix);
  const origin = new THREE.Vector3().setFromMatrixPosition(m);
  const dir = new THREE.Vector3(0, 0, -1).applyMatrix4(new THREE.Matrix4().extractRotation(m)).normalize();
  return { origin, dir };
}

function intersectObjectRay(object3d, rayOrigin, rayDir) {
  // Ensure world matrices are up to date
  object3d.updateMatrixWorld(true);
  const raycaster = new THREE.Raycaster(rayOrigin, rayDir, 0.01, 20);
  const hits = raycaster.intersectObject(object3d, true);
  return hits?.length ? hits[0] : null;
}

function getOverlayRoot() {
  // For dom-overlay (mobile). On Quest we avoid dom-overlay.
  return $("#xrOverlayRoot") || document.body;
}

function makeReticle() {
  const geo = new THREE.RingGeometry(0.07, 0.10, 32).rotateX(-Math.PI / 2);
  const mat = new THREE.MeshBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.9 });
  const mesh = new THREE.Mesh(geo, mat);
  mesh.visible = false;
  mesh.matrixAutoUpdate = false;
  return mesh;
}

function angleBetween(a, b) {
  return Math.atan2(b.y - a.y, b.x - a.x);
}

async function requestARSession({ preferHitTest = true } = {}) {
  const quest = isQuest();
  const overlayRoot = getOverlayRoot();

  const attempts = quest
    ? [
        // Quest: avoid dom-overlay; request local-floor as optional
        { requiredFeatures: ["local"], optionalFeatures: ["local-floor"] },
        { requiredFeatures: ["local"] },
        {},
      ]
    : [
        // Mobile: prefer hit-test + dom-overlay (dedicated root)
        {
          requiredFeatures: ["local"],
          optionalFeatures: preferHitTest ? ["hit-test", "dom-overlay"] : ["dom-overlay"],
          domOverlay: { root: overlayRoot },
        },
        // No dom-overlay fallback
        { requiredFeatures: ["local"], optionalFeatures: preferHitTest ? ["hit-test"] : [] },
        { requiredFeatures: ["local"] },
        {},
      ];

  let lastErr = null;
  for (const init of attempts) {
    try {
      return await navigator.xr.requestSession("immersive-ar", init);
    } catch (e) {
      lastErr = e;
    }
  }
  throw lastErr ?? new Error("Falha ao iniciar sessão WebXR AR");
}

function getFirstGamepad(session, handedness) {
  for (const src of session.inputSources) {
    if (!src?.gamepad) continue;
    if (handedness && src.handedness !== handedness) continue;
    return src.gamepad;
  }
  return null;
}

export async function enterVR(viewer) {
  const { renderer, scene, root } = viewer;
  if (!navigator.xr) throw new Error("WebXR não disponível");

  viewer.pause();
  renderer.xr.enabled = true;
  renderer.xr.setReferenceSpaceType("local-floor");

  const prev = {
    pos: root.position.clone(),
    quat: root.quaternion.clone(),
    scale: root.scale.clone(),
  };

  // Start in front of user
  root.position.set(0, 1.3, -2.0);

  const session = await navigator.xr.requestSession("immersive-vr", {
    optionalFeatures: ["local-floor", "bounded-floor", "hand-tracking", "layers"],
  });

  await renderer.xr.setSession(session);

// --- VR render definition (Quest 3) ---
// Increase framebuffer resolution for crisper background/detail.
try { renderer.xr.setFramebufferScaleFactor?.(1.5); } catch {}
// Reduce foveation to avoid peripheral blur/pixelization where supported.
try { renderer.xr.setFoveation?.(0); } catch {}


  // --- VR Grab (ray + trigger) ---
  const controller0 = renderer.xr.getController(0);
  const controller1 = renderer.xr.getController(1);
  scene.add(controller0);
  scene.add(controller1);

  const raycaster = new THREE.Raycaster();
  const tmpMat = new THREE.Matrix4();
  const tmpPos = new THREE.Vector3();
  const tmpDir = new THREE.Vector3();

  let grabbedBy = null; // controller object
  const originalParent = root.parent;

  function controllerRayHit(ctrl) {
    tmpMat.identity().extractRotation(ctrl.matrixWorld);
    tmpPos.setFromMatrixPosition(ctrl.matrixWorld);
    tmpDir.set(0, 0, -1).applyMatrix4(tmpMat).normalize();
    raycaster.ray.origin.copy(tmpPos);
    raycaster.ray.direction.copy(tmpDir);
    // Intersect root's children meshes
    const hits = raycaster.intersectObject(root, true);
    return hits && hits.length ? hits[0] : null;
  }

  function tryGrab(ctrl) {
    if (grabbedBy) return;
    const hit = controllerRayHit(ctrl);
    if (!hit) return;
    grabbedBy = ctrl;
    // Attach keeps world transform
    try { ctrl.attach(root); } catch { ctrl.add(root); }
  }

  function releaseGrab() {
    if (!grabbedBy) return;
    try { originalParent.attach(root); } catch { originalParent.add(root); }
    grabbedBy = null;
  }

  controller0.addEventListener("selectstart", () => tryGrab(controller0));
  controller1.addEventListener("selectstart", () => tryGrab(controller1));
  controller0.addEventListener("selectend", () => releaseGrab());
  controller1.addEventListener("selectend", () => releaseGrab());


  renderer.setAnimationLoop(() => {
    const gpL = getFirstGamepad(session, "left") || getFirstGamepad(session);
    const gpR = getFirstGamepad(session, "right");

    // Move model
    if (gpL?.axes?.length >= 2) {
      const ax = gpL.axes[0] || 0;
      const ay = gpL.axes[1] || 0;
      const speed = 0.05;
      root.position.x += ax * speed;
      root.position.z += ay * speed;
    }
    // Rotate yaw
    if (gpR?.axes?.length >= 2) {
      const rx = (gpR.axes[2] ?? gpR.axes[0] ?? 0);
      root.rotation.y -= rx * 0.03;
    }
    // Scale (thumbstick preferred; fallback buttons)
    const sMin = 0.08, sMax = 8.0;
    let scaleDelta = 0;
    if (gpR?.axes?.length >= 4) {
      scaleDelta = -(gpR.axes[3] || 0); // up/down on right stick
    } else if (gpR?.axes?.length >= 2) {
      scaleDelta = -(gpR.axes[1] || 0);
    }
    if (Math.abs(scaleDelta) > 0.12) {
      const factor = Math.exp(scaleDelta * 0.03);
      const next = Math.min(sMax, Math.max(sMin, root.scale.x * factor));
      root.scale.setScalar(next);
    } else {
      const gpAny = gpR || gpL;
      if (gpAny?.buttons?.length) {
        const inc = gpAny.buttons[3]?.pressed; // often Y
        const dec = gpAny.buttons[1]?.pressed; // often B
        const s = 0.02;
        if (inc) root.scale.setScalar(Math.min(sMax, root.scale.x * (1 + s)));
        if (dec) root.scale.setScalar(Math.max(sMin, root.scale.x * (1 - s)));
      }
    }

    renderer.render(scene, viewer.camera);
  });

  session.addEventListener("end", () => {
    renderer.setAnimationLoop(null);
    try { releaseGrab(); } catch {}
    // overlay is only used in AR; ignore in VR

    root.position.copy(prev.pos);
    root.quaternion.copy(prev.quat);
    root.scale.copy(prev.scale);
    viewer.resume();
  });

  return session;
}

export async function enterAR(viewer) {
  const { renderer, scene, root } = viewer;
  if (!navigator.xr) throw new Error("WebXR não disponível");

  const quest = isQuest();

  viewer.pause();
  renderer.xr.enabled = true;
  renderer.xr.setReferenceSpaceType("local");

  // Save background + clear state
  const prevBackground = scene.background;
  const prevClearColor = renderer.getClearColor(new THREE.Color()).clone();
  const prevClearAlpha = renderer.getClearAlpha();

  // Save model transform
  const prev = {
    pos: root.position.clone(),
    quat: root.quaternion.clone(),
    scale: root.scale.clone(),
  };

  // Transparent clear so camera feed is visible (AR passthrough)
  scene.background = null;
  renderer.setClearColor(0x000000, 0);
  renderer.domElement.style.background = "transparent";

  // Reticle (for hit-test)
  const reticle = makeReticle();
  scene.add(reticle);

  // Start visible
  root.position.set(0, 0, -1.2);

  let session;
  try {
    session = await requestARSession({ preferHitTest: true });
  } catch (e) {
    scene.remove(reticle);
    scene.background = prevBackground;
    renderer.setClearColor(prevClearColor, prevClearAlpha);
    renderer.domElement.style.background = "";
    viewer.resume();
    throw e;
  }

  await renderer.xr.setSession(session);

// --- VR render definition (Quest 3) ---
// Increase framebuffer resolution for crisper background/detail.
try { renderer.xr.setFramebufferScaleFactor?.(1.5); } catch {}
// Reduce foveation to avoid peripheral blur/pixelization where supported.
try { renderer.xr.setFoveation?.(0); } catch {}


  // --- VR Grab (ray + trigger) ---
  const controller0 = renderer.xr.getController(0);
  const controller1 = renderer.xr.getController(1);
  scene.add(controller0);
  scene.add(controller1);

  const raycaster = new THREE.Raycaster();
  const tmpMat = new THREE.Matrix4();
  const tmpPos = new THREE.Vector3();
  const tmpDir = new THREE.Vector3();

  let grabbedBy = null; // controller object
  const originalParent = root.parent;

  function controllerRayHit(ctrl) {
    tmpMat.identity().extractRotation(ctrl.matrixWorld);
    tmpPos.setFromMatrixPosition(ctrl.matrixWorld);
    tmpDir.set(0, 0, -1).applyMatrix4(tmpMat).normalize();
    raycaster.ray.origin.copy(tmpPos);
    raycaster.ray.direction.copy(tmpDir);
    // Intersect root's children meshes
    const hits = raycaster.intersectObject(root, true);
    return hits && hits.length ? hits[0] : null;
  }

  function tryGrab(ctrl) {
    if (grabbedBy) return;
    const hit = controllerRayHit(ctrl);
    if (!hit) return;
    grabbedBy = ctrl;
    // Attach keeps world transform
    try { ctrl.attach(root); } catch { ctrl.add(root); }
  }

  function releaseGrab() {
    if (!grabbedBy) return;
    try { originalParent.attach(root); } catch { originalParent.add(root); }
    grabbedBy = null;
  }

  controller0.addEventListener("selectstart", () => tryGrab(controller0));
  controller1.addEventListener("selectstart", () => tryGrab(controller1));
  controller0.addEventListener("selectend", () => releaseGrab());
  controller1.addEventListener("selectend", () => releaseGrab());


  const refSpace = await session.requestReferenceSpace("local");
  const viewerSpace = await session.requestReferenceSpace("viewer");

  // Regular hit-test (for reticle + tap)
  let hitTestSource = null;
  try {
    if (session.requestHitTestSource) {
      hitTestSource = await session.requestHitTestSource({ space: viewerSpace });
    }
  } catch {
    hitTestSource = null;
  }

  // Transient hit-test (native-like drag to move on touchscreen)
  let transientHitTestSource = null;
  try {
    if (session.requestHitTestSourceForTransientInput) {
      transientHitTestSource = await session.requestHitTestSourceForTransientInput({
        profile: "generic-touchscreen",
      });
    }
  } catch {
    transientHitTestSource = null;
  }

  // Placement via tap
  const onSelect = () => {
    if (!reticle.visible) return;
    const m = reticle.matrix;
    const pos = new THREE.Vector3().setFromMatrixPosition(m);
    const quat = new THREE.Quaternion().setFromRotationMatrix(m);
    root.position.copy(pos);
    root.quaternion.copy(quat);
  };
  session.addEventListener("select", onSelect);

  // Gesture state (mobile only; Quest uses controllers)
  const overlay = getOverlayRoot();
  // Make overlay capture gestures across the whole viewport (critical for mobile AR)
  try {
    overlay.style.position = "absolute";
    overlay.style.inset = "0";
    overlay.style.width = "100%";
    overlay.style.height = "100%";
    overlay.style.pointerEvents = "auto";
    overlay.style.touchAction = "none";
  } catch {}
  let pointers = new Map();
  // Orbit-like targets (for smooth AR like 3D)
  let targetYaw = root.rotation.y;
  let targetPitch = 0;
  let targetScale = root.scale.x;
  const targetPos = new THREE.Vector3().copy(root.position);
 // id -> {x,y}
  let gesture = null;       // {mode, startScale, startRotY, startAngle, startDist}
  let draggingMove = false; // when 1 finger down, we move using transient hit-test
  // Drag smoothing (mobile): transient hit-test updates a target position, we lerp root towards it.
  const dragTargetPos = new THREE.Vector3().copy(root.position);
  let lastT = null;

  // Quest grab (ray + trigger): press trigger while pointing at model to "grab" and move it.
  let grabbed = false;
  let grabControllerSpace = null; // XRSpace (targetRaySpace) that initiated grab
  let grabDistance = 1.0;         // distance from controller ray origin to hit point at grab start
  const grabOffset = new THREE.Vector3(); // world-space offset between desired hit point and root.position
  const triggerPrev = new Map(); // inputSource -> boolean


  function onPointerDown(e) {
    try { e.preventDefault(); } catch {}
    if (quest) return;
    if (e.pointerType === "mouse") return;

    overlay.setPointerCapture?.(e.pointerId);
    pointers.set(e.pointerId, { x: e.clientX, y: e.clientY });

    if (pointers.size === 1) {
      draggingMove = true; // drag to move using transient hit test
      gesture = null;
    } else if (pointers.size === 2) {
      draggingMove = false;
      const pts = Array.from(pointers.values());
      const a0 = angleBetween(pts[0], pts[1]);
      const d0 = Math.hypot(pts[0].x - pts[1].x, pts[0].y - pts[1].y);
      gesture = {
        mode: "pinch",
        startScale: root.scale.x,
        startRotY: root.rotation.y,
        startAngle: a0,
        startDist: d0 || 1,
      };
    }
  }

  function onPointerMove(e) {
    try { e.preventDefault(); } catch {}
    if (quest) return;
    if (!pointers.has(e.pointerId)) return;

    pointers.set(e.pointerId, { x: e.clientX, y: e.clientY });

    if (gesture?.mode === "pinch-twist" && pointers.size === 2) {
      const pts = Array.from(pointers.values());
      const a1 = angleBetween(pts[0], pts[1]);
      const d1 = Math.hypot(pts[0].x - pts[1].x, pts[0].y - pts[1].y) || 1;

      // scale
      const scaleFactor = d1 / gesture.startDist;
      const newScale = clamp(gesture.startScale * scaleFactor, 0.05, 20);
      root.scale.setScalar(newScale);

      // rotate (yaw)
      const da = a1 - gesture.startAngle;
      root.rotation.y = gesture.startRotY + da;
    }
  }

  function onPointerUp(e) {
    try { e.preventDefault(); } catch {}
    if (quest) return;
    pointers.delete(e.pointerId);
    if (pointers.size === 0) {
      draggingMove = false;
      gesture = null;
    } else if (pointers.size === 1) {
      draggingMove = true;
      gesture = null;
    }
  }

  if (!quest) {
    overlay.addEventListener("pointerdown", onPointerDown, { passive: false });
    overlay.addEventListener("pointermove", onPointerMove, { passive: false });
    overlay.addEventListener("pointerup", onPointerUp, { passive: false });
    overlay.addEventListener("pointercancel", onPointerUp, { passive: false });
  }

  renderer.setAnimationLoop((t, frame) => {
    renderer.setClearColor(0x000000, 0);

    // Reticle update
    if (frame && hitTestSource) {
      const hits = frame.getHitTestResults(hitTestSource);
      if (hits.length) {
        const pose = hits[0].getPose(refSpace);
        if (pose) {
          reticle.visible = true;
          reticle.matrix.fromArray(pose.transform.matrix);
        }
      } else {
        reticle.visible = false;
      }
    }

    // Drag-to-move using transient hit-test (only on mobile touch)
    if (frame && transientHitTestSource && draggingMove) {
      const transientResults = frame.getHitTestResultsForTransientInput(transientHitTestSource);
      if (transientResults?.length) {
        const r0 = transientResults[0];
        const hits = r0.results || [];
        if (hits.length) {
          const pose = hits[0].getPose(refSpace);
          if (pose) {
            const m = new THREE.Matrix4().fromArray(pose.transform.matrix);
            const pos = new THREE.Vector3().setFromMatrixPosition(m);
            dragTargetPos.copy(pos);
          }
        }
      }
    }

    
    // Apply smoothing towards drag target (mobile only)
    if (!quest) {
      const dt = lastT == null ? 0.016 : Math.max(0.001, (t - lastT) / 1000);
      lastT = t;
      // Move only when we have a meaningful target delta
      if (root.position.distanceToSquared(dragTargetPos) > 1e-6) {
        root.position.set(
          damp(root.position.x, dragTargetPos.x, 18, dt),
          damp(root.position.y, dragTargetPos.y, 18, dt),
          damp(root.position.z, dragTargetPos.z, 18, dt),
        );
      }
    }
// Quest AR: controller manipulation
    if (quest) {
      // --- Quest grab interaction (ray + trigger) ---
      // We read the trigger state from each inputSource gamepad (button[0] usually trigger).
      // On rising edge, if the ray intersects the model, we start "grab".
      // While held, we move the model along the ray keeping the initial distance + offset.
      if (frame) {
        for (const src of session.inputSources) {
          if (!src?.gamepad || !src?.targetRaySpace) continue;

          const pressed = !!src.gamepad.buttons?.[0]?.pressed;
          const prevPressed = triggerPrev.get(src) || false;
          triggerPrev.set(src, pressed);

          const pose = frame.getPose(src.targetRaySpace, refSpace);
          if (!pose) continue;

          const { origin, dir } = getRayFromPose(pose);

          if (pressed && !prevPressed && !grabbed) {
            const hit = intersectObjectRay(root, origin, dir);
            if (hit) {
              grabbed = true;
              grabControllerSpace = src.targetRaySpace;

              // Distance to the hit point along the ray
              grabDistance = Math.max(0.15, origin.distanceTo(hit.point));

              // Offset so the grabbed point stays under the ray
              grabOffset.copy(root.position).sub(hit.point);
            }
          }

          // Release if trigger goes up on the controller that initiated grab
          if (!pressed && prevPressed && grabbed && grabControllerSpace === src.targetRaySpace) {
            grabbed = false;
            grabControllerSpace = null;
          }

          // Update while grabbing
          if (pressed && grabbed && grabControllerSpace === src.targetRaySpace) {
            const desired = origin.clone().add(dir.clone().multiplyScalar(grabDistance)).add(grabOffset);
            root.position.lerp(desired, 0.35);
          }
        }
      }

      const gpL = getFirstGamepad(session, "left") || getFirstGamepad(session);
      const gpR = getFirstGamepad(session, "right");

      if (!grabbed && gpL?.axes?.length >= 2) {
        const ax = gpL.axes[0] || 0;
        const ay = gpL.axes[1] || 0;
        const speed = 0.04;
        root.position.x += ax * speed;
        root.position.z += ay * speed;
      }
      if (!grabbed && gpR?.axes?.length >= 2) {
        const rx = (gpR.axes[2] ?? gpR.axes[0] ?? 0);
        root.rotation.y -= rx * 0.03;
      }
      const gpAny = gpR || gpL;
      if (gpAny?.buttons?.length) {
        const inc = gpAny.buttons[3]?.pressed;
        const dec = gpAny.buttons[1]?.pressed;
        const s = 0.012;
        if (inc) root.scale.multiplyScalar(1 + s);
        if (dec) root.scale.multiplyScalar(1 - s);
      }
    }

    renderer.render(scene, viewer.camera);
  });

  session.addEventListener("end", () => {
    renderer.setAnimationLoop(null);
    try { releaseGrab(); } catch {}
    // overlay is only used in AR; ignore in VR

    session.removeEventListener("select", onSelect);

    if (!quest) {
      overlay.removeEventListener("pointerdown", onPointerDown);
      overlay.removeEventListener("pointermove", onPointerMove);
      overlay.removeEventListener("pointerup", onPointerUp);
      overlay.removeEventListener("pointercancel", onPointerUp);
    }

    try { hitTestSource?.cancel?.(); } catch {}
    try { transientHitTestSource?.cancel?.(); } catch {}

    scene.remove(reticle);

    root.position.copy(prev.pos);
    root.quaternion.copy(prev.quat);
    root.scale.copy(prev.scale);

    scene.background = prevBackground;
    renderer.setClearColor(prevClearColor, prevClearAlpha);
    renderer.domElement.style.background = "";

    viewer.resume();
  });

  return session;
}
