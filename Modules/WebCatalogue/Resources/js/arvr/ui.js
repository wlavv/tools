export function setStatus(text) {
  const el = document.querySelector("#status");
  if (el) el.textContent = text;
}

export function setChips({ platformText, capsText }) {
  const p = document.querySelector("#chipPlatform");
  const c = document.querySelector("#chipCaps");
  if (p) p.textContent = platformText;
  if (c) c.textContent = capsText;
}

export function toast(message, ms = 3500) {
  const overlay = document.querySelector("#overlay");
  const toastEl = document.querySelector("#toast");
  if (!overlay || !toastEl) return;

  toastEl.textContent = message;
  overlay.style.display = "flex";
  window.clearTimeout(toast.__t);
  toast.__t = window.setTimeout(() => (overlay.style.display = "none"), ms);
}

/**
 * In AR mode on mobile, hide noisy UI elements to maximize camera view:
 * - chipPlatform, chipCaps, status, hint
 */
function setMinimalTopbar(isAr) {
  const isMobile = window.matchMedia?.("(max-width: 768px)")?.matches ?? (window.innerWidth <= 768);
  const targets = ["#chipPlatform", "#chipCaps", "#status", "#hint"];

  for (const sel of targets) {
    const el = document.querySelector(sel);
    if (!el) continue;
    el.style.display = (isAr && isMobile) ? "none" : "";
  }
}

export function showARControls(show) {
  const arUi = document.querySelector("#arUi");
  if (arUi) arUi.style.display = show ? "flex" : "none";

  // Mobile AR cleanup
  setMinimalTopbar(!!show);
}

export function openSidePanel({ title, bodyHtml }) {
  const panel = document.querySelector("#sidePanel");
  const backdrop = document.querySelector("#sidePanelBackdrop");
  const titleEl = document.querySelector("#panelTitle");
  const bodyEl = document.querySelector("#panelBody");

  if (titleEl) titleEl.textContent = title || "Detalhes";
  if (bodyEl) bodyEl.innerHTML = bodyHtml || "";

  if (backdrop) backdrop.style.display = "block";
  if (panel) panel.classList.add("open");
}

export function closeSidePanel() {
  const panel = document.querySelector("#sidePanel");
  const backdrop = document.querySelector("#sidePanelBackdrop");
  if (backdrop) backdrop.style.display = "none";
  if (panel) panel.classList.remove("open");
}

export function bindSidePanel() {
  const backdrop = document.querySelector("#sidePanelBackdrop");
  const closeBtn = document.querySelector("#panelClose");
  backdrop?.addEventListener("click", closeSidePanel);
  closeBtn?.addEventListener("click", closeSidePanel);
}
