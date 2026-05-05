export const State = Object.freeze({
  VIEW_3D: "VIEW_3D",
  XR_VR: "XR_VR",
  XR_AR: "XR_AR",
  TRACK_IMAGE: "TRACK_IMAGE",
  TRACK_MARKER: "TRACK_MARKER",
  AR_RUNNING: "AR_RUNNING"
});

export class AppState {
  constructor() { this.state = State.VIEW_3D; }
  set(next) { this.state = next; }
}
