function isIOS() {
  // iPhone/iPod/iPad classic UA
  if (/iPad|iPhone|iPod/.test(navigator.userAgent)) return true;

  // iPadOS 13+ often masquerades as Mac; detect via touch points
  return navigator.platform === "MacIntel" && (navigator.maxTouchPoints || 0) > 1;
}

function isQuestBrowser() {
  return /OculusBrowser/i.test(navigator.userAgent);
}

async function canAccessCamera() {
  if (!navigator.mediaDevices?.getUserMedia) return false;
  try {
    const s = await navigator.mediaDevices.getUserMedia({ video: true });
    s.getTracks().forEach(t => t.stop());
    return true;
  } catch {
    return false;
  }
}

function supportsQuickLookUSDZ() {
  // Quick Look is supported on iOS/iPadOS Safari
  return isIOS();
}

async function webxrSupport() {
  const hasXR = !!navigator.xr;
  if (!hasXR) return { webxr: false, vr: false, ar: false };
  const [vr, ar] = await Promise.all([
    navigator.xr.isSessionSupported("immersive-vr").catch(() => false),
    navigator.xr.isSessionSupported("immersive-ar").catch(() => false)
  ]);
  return { webxr: true, vr, ar };
}

export async function detectCapabilities() {
  const ios = isIOS();
  const quest = isQuestBrowser();
  const hasCamera = await canAccessCamera();
  const xr = await webxrSupport();

  return {
    ios,
    quest,
    hasCamera,
    webxr: xr.webxr,
    xrVR: xr.vr,
    xrAR: xr.ar,
    canQuickLook: ios && supportsQuickLookUSDZ()
  };
}

export function platformLabel(caps) {
  if (caps.quest) return "Meta Quest Browser";
  if (caps.ios) return "iOS/iPadOS (Safari)";
  if (/Android/i.test(navigator.userAgent)) return "Android";
  return "Desktop/Other";
}
