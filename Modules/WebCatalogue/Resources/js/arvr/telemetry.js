function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

export function emit(event, data = {}) {
  const payload = {
    event,
    ts: Date.now(),
    url: location.href,
    ua: navigator.userAgent,
    ...data
  };

  // Also log to console for dev visibility
  console.debug("[arvr_event]", payload);

  const endpoint = window.__ARVR_EVENT_URL__;
  if (!endpoint) return;

  // Prefer sendBeacon when available (non-blocking). It does not allow custom headers reliably,
  // so we fallback to fetch with CSRF for Laravel web routes.
  try {
    if (navigator.sendBeacon && !csrfToken()) {
      const blob = new Blob([JSON.stringify(payload)], { type: "application/json" });
      navigator.sendBeacon(endpoint, blob);
      return;
    }
  } catch {}

  // Fetch with CSRF
  fetch(endpoint, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": csrfToken()
    },
    body: JSON.stringify(payload),
    keepalive: true
  }).catch(() => {});
}
