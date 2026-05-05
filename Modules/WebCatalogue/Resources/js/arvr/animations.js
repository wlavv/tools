import * as THREE from "three";
import { emit } from "./telemetry.js";

export class ExplodeAnimator {
  constructor(root, factor = 0.18) {
    this.root = root;
    this.factor = factor;
    this.enabled = false;
    this._original = new Map();
    this._center = new THREE.Vector3();
    this._tmp = new THREE.Vector3();
    this._collectOriginals();
  }

  _collectOriginals() {
    // store original local positions for all meshes/groups
    this.root.updateMatrixWorld(true);
    const box = new THREE.Box3().setFromObject(this.root);
    box.getCenter(this._center);

    this.root.traverse(obj => {
      if (!obj.isObject3D) return;
      this._original.set(obj.uuid, obj.position.clone());
    });
  }

  toggle() {
    this.setEnabled(!this.enabled);
  }

  setEnabled(on) {
    this.enabled = on;
    emit("explode_toggle", { enabled: on, factor: this.factor });
    if (!on) this.reset();
  }

  reset() {
    this.root.traverse(obj => {
      const p = this._original.get(obj.uuid);
      if (p) obj.position.copy(p);
    });
  }

  update(alpha = 1.0) {
    // apply explode by pushing objects away from center along their world direction
    if (!this.enabled) return;

    this.root.updateMatrixWorld(true);

    this.root.traverse(obj => {
      if (!obj.isObject3D) return;
      const orig = this._original.get(obj.uuid);
      if (!orig) return;

      // world position direction from center
      obj.getWorldPosition(this._tmp);
      const dir = this._tmp.sub(this._center).normalize();

      // convert push into local space approximately by adding to local position
      const push = dir.multiplyScalar(this.factor * alpha);
      obj.position.copy(orig).add(push);
    });
  }
}
