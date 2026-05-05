export function openQuickLookUSDZ(usdzUrl) {
  const a = document.createElement("a");
  a.setAttribute("rel", "ar");
  a.setAttribute("href", usdzUrl);
  a.style.display = "none";
  document.body.appendChild(a);
  a.click();
  a.remove();
}
