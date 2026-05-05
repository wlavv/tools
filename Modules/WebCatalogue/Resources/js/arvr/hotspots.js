import * as THREE from "three";
import { emit } from "./telemetry.js";
import { openSidePanel } from "./ui.js";

export class HotspotManager {
  constructor({ layerEl, camera }) {
    this.layerEl = layerEl;
    this.camera = camera;
    this.items = [];
    this._vec = new THREE.Vector3();
    this._ray = new THREE.Raycaster();
  }

  load(hotspots = []) {
    this.clear();
    this.items = hotspots.map(hs => {
      const el = document.createElement("button");
      el.className = "hotspot";
      el.type = "button";
      el.textContent = hs.label;
      el.addEventListener("click", () => {
        emit("hotspot_click", { hotspot_id: hs.id, hotspot_label: hs.label });
        emit("hotspot_click", { hotspot_id: hs.id, hotspot_label: hs.label });

        const specs = Array.isArray(hs.specs) ? hs.specs : [];
        const actions = Array.isArray(hs.actions) ? hs.actions : [];
        const specsHtml = specs.length ? `
          <table class="specTable">
            ${specs.map(s => `<tr><td class="specKey">${escapeHtml(String(s.key ?? ""))}</td><td>${escapeHtml(String(s.value ?? ""))}</td></tr>`).join("")}
          </table>` : "";

        const actionsHtml = actions.length ? `
          <div class="panelActions">
            ${actions.map(a => renderAction(a)).join("")}
          </div>` : "";

        const body = `
          <p>${escapeHtml(String(hs.body ?? ""))}</p>
          ${specsHtml}
          ${actionsHtml}
        `;

        openSidePanel({ title: hs.label, bodyHtml: body });
      });
      this.layerEl.appendChild(el);
      return { ...hs, el, pos: new THREE.Vector3(...hs.pos) };
    });
  }

  clear() {
    this.layerEl.innerHTML = "";
    this.items = [];
  }

  setVisible(visible) {
    this.layerEl.style.display = visible ? "block" : "none";
  }

  update(renderer, scene) {
    const w = renderer.domElement.clientWidth;
    const h = renderer.domElement.clientHeight;

    for (const hs of this.items) {
      // Project 3D point (in model local space) -> world -> screen
      this._vec.copy(hs.pos);

      // If you want hotspots in world space anchored to model root, translate by root in caller.
      // Here we assume hs.pos is already in world space or you set a transform externally.
      this._vec.project(this.camera);

      const x = (this._vec.x * 0.5 + 0.5) * w;
      const y = (-this._vec.y * 0.5 + 0.5) * h;

      const behind = this._vec.z > 1;
      hs.el.style.display = behind ? "none" : "block";
      hs.el.style.left = `${x}px`;
      hs.el.style.top = `${y}px`;
    }
  }
}


function escapeHtml(str) {
  return str
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function renderAction(a) {
  const type = String(a?.type || "link");
  const label = escapeHtml(String(a?.label || "Abrir"));
  if (type === "link") {
    const href = escapeHtml(String(a?.href || "#"));
    return `<a href="${href}" target="_blank" rel="noopener noreferrer">${label}</a>`;
  }
  if (type === "note") {
    const text = escapeHtml(String(a?.text || ""));
    return `<button type="button" onclick="alert('${text.replaceAll("'", "\\'")}')">${label}</button>`;
  }
  return `<button type="button">${label}</button>`;
}
