<html><head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta aframe-injected="" name="mobile-web-app-capable" content="yes"><meta aframe-injected="" name="theme-color" content="black"><script src="https://aframe.io/releases/1.4.2/aframe.min.js"></script><style>/* .a-fullscreen means not embedded. */
html.a-fullscreen {
  bottom: 0;
  left: 0;
  position: fixed;
  right: 0;
  top: 0;
}

html.a-fullscreen body {
  height: 100%;
  margin: 0;
  overflow: hidden;
  padding: 0;
  width: 100%;
}

/* Class is removed when doing <a-scene embedded>. */
html.a-fullscreen .a-canvas {
  width: 100% !important;
  height: 100% !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  position: fixed !important;
}

html:not(.a-fullscreen) .a-enter-vr,
html:not(.a-fullscreen) .a-enter-ar {
  right: 5px;
  bottom: 5px;
}

html:not(.a-fullscreen) .a-enter-ar {
  right: 60px;
}

/* In chrome mobile the user agent stylesheet set it to white  */
:-webkit-full-screen {
  background-color: transparent;
}

.a-hidden {
  display: none !important;
}

.a-canvas {
  height: 100%;
  left: 0;
  position: absolute;
  top: 0;
  width: 100%;
}

.a-canvas.a-grab-cursor:hover {
  cursor: grab;
  cursor: -moz-grab;
  cursor: -webkit-grab;
}

canvas.a-canvas.a-mouse-cursor-hover:hover {
  cursor: pointer;
}

.a-inspector-loader {
  background-color: #ed3160;
  position: fixed;
  left: 3px;
  top: 3px;
  padding: 6px 10px;
  color: #fff;
  text-decoration: none;
  font-size: 12px;
  font-family: Roboto,sans-serif;
  text-align: center;
  z-index: 99999;
  width: 204px;
}

/* Inspector loader animation */
@keyframes dots-1 { from { opacity: 0; } 25% { opacity: 1; } }
@keyframes dots-2 { from { opacity: 0; } 50% { opacity: 1; } }
@keyframes dots-3 { from { opacity: 0; } 75% { opacity: 1; } }
@-webkit-keyframes dots-1 { from { opacity: 0; } 25% { opacity: 1; } }
@-webkit-keyframes dots-2 { from { opacity: 0; } 50% { opacity: 1; } }
@-webkit-keyframes dots-3 { from { opacity: 0; } 75% { opacity: 1; } }

.a-inspector-loader .dots span {
  animation: dots-1 2s infinite steps(1);
  -webkit-animation: dots-1 2s infinite steps(1);
}

.a-inspector-loader .dots span:first-child + span {
  animation-name: dots-2;
  -webkit-animation-name: dots-2;
}

.a-inspector-loader .dots span:first-child + span + span {
  animation-name: dots-3;
  -webkit-animation-name: dots-3;
}

a-scene {
  display: block;
  position: relative;
  height: 100%;
  width: 100%;
}

a-assets,
a-scene video,
a-scene img,
a-scene audio {
  display: none;
}

.a-enter-vr-modal,
.a-orientation-modal {
  font-family: Consolas, Andale Mono, Courier New, monospace;
}

.a-enter-vr-modal a {
  border-bottom: 1px solid #fff;
  padding: 2px 0;
  text-decoration: none;
  transition: .1s color ease-in;
}

.a-enter-vr-modal a:hover {
  background-color: #fff;
  color: #111;
  padding: 2px 4px;
  position: relative;
  left: -4px;
}

.a-enter-vr,
.a-enter-ar {
  font-family: sans-serif, monospace;
  font-size: 13px;
  width: 100%;
  font-weight: 200;
  line-height: 16px;
  position: absolute;
  right: 20px;
  bottom: 20px;
}

.a-enter-ar {
  right: 80px;
}

.a-enter-vr-button,
.a-enter-vr-modal,
.a-enter-vr-modal a {
  color: #fff;
  user-select: none;
  outline: none;
}

.a-enter-vr-button {
  background: rgba(0, 0, 0, 0.35) url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27108%27 height=%2762%27 viewBox=%270 0 108 62%27%3E%3Ctitle%3Eaframe-vrmode-noborder-reduced-tracking%3C/title%3E%3Cpath d=%27M68.81,21.56H64.23v8.27h4.58a4.13,4.13,0,0,0,3.1-1.09,4.2,4.2,0,0,0,1-3,4.24,4.24,0,0,0-1-3A4.05,4.05,0,0,0,68.81,21.56Z%27 fill=%27%23fff%27/%3E%3Cpath d=%27M96,0H12A12,12,0,0,0,0,12V50A12,12,0,0,0,12,62H96a12,12,0,0,0,12-12V12A12,12,0,0,0,96,0ZM41.9,46H34L24,16h8l6,21.84,6-21.84H52Zm39.29,0H73.44L68.15,35.39H64.23V46H57V16H68.81q5.32,0,8.34,2.37a8,8,0,0,1,3,6.69,9.68,9.68,0,0,1-1.27,5.18,8.9,8.9,0,0,1-4,3.34l6.26,12.11Z%27 fill=%27%23fff%27/%3E%3C/svg%3E") 50% 50% no-repeat;
}

.a-enter-ar-button {
  background: rgba(0, 0, 0, 0.20) url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27108%27 height=%2762%27 viewBox=%270 0 108 62%27%3E%3Ctitle%3Eaframe-armode-noborder-reduced-tracking%3C/title%3E%3Cpath d=%27M96,0H12A12,12,0,0,0,0,12V50A12,12,0,0,0,12,62H96a12,12,0,0,0,12-12V12A12,12,0,0,0,96,0Zm8,50a8,8,0,0,1-8,8H12a8,8,0,0,1-8-8V12a8,8,0,0,1,8-8H96a8,8,0,0,1,8,8Z%27 fill=%27%23fff%27/%3E%3Cpath d=%27M43.35,39.82H32.51L30.45,46H23.88L35,16h5.73L52,46H45.43Zm-9.17-5h7.5L37.91,23.58Z%27 fill=%27%23fff%27/%3E%3Cpath d=%27M68.11,35H63.18V46H57V16H68.15q5.31,0,8.2,2.37a8.18,8.18,0,0,1,2.88,6.7,9.22,9.22,0,0,1-1.33,5.12,9.09,9.09,0,0,1-4,3.26l6.49,12.26V46H73.73Zm-4.93-5h5a5.09,5.09,0,0,0,3.6-1.18,4.21,4.21,0,0,0,1.28-3.27,4.56,4.56,0,0,0-1.2-3.34A5,5,0,0,0,68.15,21h-5Z%27 fill=%27%23fff%27/%3E%3C/svg%3E") 50% 50% no-repeat;
}

.a-enter-vr.fullscreen .a-enter-vr-button {
  background-image: url("data:image/svg+xml,%3C%3Fxml version=%271.0%27 encoding=%27UTF-8%27 standalone=%27no%27%3F%3E%3Csvg width=%27108%27 height=%2762%27 viewBox=%270 0 108 62%27 version=%271.1%27 id=%27svg320%27 sodipodi:docname=%27fullscreen-aframe.svg%27 xml:space=%27preserve%27 inkscape:version=%271.2.1 %289c6d41e  2022-07-14%29%27 xmlns:inkscape=%27http://www.inkscape.org/namespaces/inkscape%27 xmlns:sodipodi=%27http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd%27 xmlns=%27http://www.w3.org/2000/svg%27 xmlns:svg=%27http://www.w3.org/2000/svg%27 xmlns:rdf=%27http://www.w3.org/1999/02/22-rdf-syntax-ns%23%27 xmlns:cc=%27http://creativecommons.org/ns%23%27 xmlns:dc=%27http://purl.org/dc/elements/1.1/%27%3E%3Cdefs id=%27defs324%27 /%3E%3Csodipodi:namedview id=%27namedview322%27 pagecolor=%27%23ffffff%27 bordercolor=%27%23000000%27 borderopacity=%270.25%27 inkscape:showpageshadow=%272%27 inkscape:pageopacity=%270.0%27 inkscape:pagecheckerboard=%270%27 inkscape:deskcolor=%27%23d1d1d1%27 showgrid=%27false%27 inkscape:zoom=%273.8064516%27 inkscape:cx=%2791.423729%27 inkscape:cy=%27-1.4449153%27 inkscape:window-width=%271440%27 inkscape:window-height=%27847%27 inkscape:window-x=%2732%27 inkscape:window-y=%2725%27 inkscape:window-maximized=%270%27 inkscape:current-layer=%27svg320%27 /%3E%3Ctitle id=%27title312%27%3Eaframe-armode-noborder-reduced-tracking%3C/title%3E%3Cpath d=%27M96 0H12A12 12 0 0 0 0 12V50A12 12 0 0 0 12 62H96a12 12 0 0 0 12-12V12A12 12 0 0 0 96 0Zm8 50a8 8 0 0 1-8 8H12a8 8 0 0 1-8-8V12a8 8 0 0 1 8-8H96a8 8 0 0 1 8 8Z%27 fill=%27%23fff%27 id=%27path314%27 style=%27fill:%23ffffff%27 /%3E%3Cg id=%27g356%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g358%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g360%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g362%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g364%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g366%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g368%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g370%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g372%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g374%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g376%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g378%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g380%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g382%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cg id=%27g384%27 transform=%27translate%28-206.61017 -232.61864%29%27%3E%3C/g%3E%3Cmetadata id=%27metadata561%27%3E%3Crdf:RDF%3E%3Ccc:Work rdf:about=%27%27%3E%3Cdc:title%3Eaframe-armode-noborder-reduced-tracking%3C/dc:title%3E%3C/cc:Work%3E%3C/rdf:RDF%3E%3C/metadata%3E%3Cpath d=%27m 98.168511 40.083649 c 0 -1.303681 -0.998788 -2.358041 -2.239389 -2.358041 -1.230088 0.0031 -2.240892 1.05436 -2.240892 2.358041 v 4.881296 l -9.041661 -9.041662 c -0.874129 -0.875631 -2.288954 -0.875631 -3.16308 0 -0.874129 0.874126 -0.874129 2.293459 0 3.167585 l 8.995101 8.992101 h -4.858767 c -1.323206 0.0031 -2.389583 1.004796 -2.389583 2.239386 0 1.237598 1.066377 2.237888 2.389583 2.237888 h 10.154599 c 1.323206 0 2.388082 -0.998789 2.392587 -2.237888 -0.0044 -0.03305 -0.009 -0.05858 -0.0134 -0.09161 0.0046 -0.04207 0.0134 -0.08712 0.0134 -0.13066 V 40.085172 h -1.52e-4%27 id=%27path596%27 style=%27fill:%23ffffff%3Bstroke-width:1.50194%27 /%3E%3Cpath d=%27m 23.091002 35.921781 -9.026643 9.041662 v -4.881296 c 0 -1.303681 -1.009302 -2.355037 -2.242393 -2.358041 -1.237598 0 -2.237888 1.05436 -2.237888 2.358041 l -0.0031 10.016421 c 0 0.04356 0.01211 0.08862 0.0015 0.130659 -0.0031 0.03153 -0.009 0.05709 -0.01211 0.09161 0.0031 1.239099 1.069379 2.237888 2.391085 2.237888 h 10.156101 c 1.320202 0 2.388079 -1.000291 2.388079 -2.237888 0 -1.234591 -1.067877 -2.236383 -2.388079 -2.239387 h -4.858767 l 8.995101 -8.9921 c 0.871126 -0.874127 0.871126 -2.293459 0 -3.167586 -0.875628 -0.877132 -2.291957 -0.877132 -3.169087 -1.52e-4%27 id=%27path598%27 style=%27fill:%23ffffff%3Bstroke-width:1.50194%27 /%3E%3Cpath d=%27m 84.649572 25.978033 9.041662 -9.041664 v 4.881298 c 0 1.299176 1.010806 2.350532 2.240891 2.355037 1.240601 0 2.23939 -1.055861 2.23939 -2.355037 V 11.798242 c 0 -0.04356 -0.009 -0.08862 -0.0134 -0.127671 0.0044 -0.03153 0.009 -0.06157 0.0134 -0.09313 -0.0044 -1.240598 -1.069379 -2.2393873 -2.391085 -2.2393873 h -10.1546 c -1.323205 0 -2.38958 0.9987893 -2.38958 2.2393873 0 1.233091 1.066375 2.237887 2.38958 2.240891 h 4.858768 l -8.995102 8.9921 c -0.874129 0.872625 -0.874129 2.288954 0 3.161578 0.874127 0.880137 2.288951 0.880137 3.16308 1.5e-4%27 id=%27path600%27 style=%27fill:%23ffffff%3Bstroke-width:1.50194%27 /%3E%3Cpath d=%27m 17.264988 13.822853 h 4.857265 c 1.320202 -0.0031 2.388079 -1.0078 2.388079 -2.240889 0 -1.240601 -1.067877 -2.2393893 -2.388079 -2.2393893 H 11.967654 c -1.321707 0 -2.388082 0.9987883 -2.391085 2.2393893 0.0031 0.03153 0.009 0.06157 0.01211 0.09313 -0.0031 0.03905 -0.0015 0.08262 -0.0015 0.127671 l 0.0031 10.020926 c 0 1.299176 1.00029 2.355038 2.237887 2.355038 1.233092 -0.0044 2.242393 -1.055862 2.242393 -2.355038 v -4.881295 l 9.026644 9.041661 c 0.877132 0.878635 2.293459 0.878635 3.169087 0 0.871125 -0.872624 0.871125 -2.288953 0 -3.161577 l -8.995282 -8.993616%27 id=%27path602%27 style=%27fill:%23ffffff%3Bstroke-width:1.50194%27 /%3E%3C/svg%3E");
}

.a-enter-vr-button,
.a-enter-ar-button {
  background-size: 90% 90%;
  border: 0;
  bottom: 0;
  cursor: pointer;
  min-width: 58px;
  min-height: 34px;
  /* 1.74418604651 */
  /*
    In order to keep the aspect ratio when resizing
    padding-top percentages are relative to the containing block's width.
    http://stackoverflow.com/questions/12121090/responsively-change-div-size-keeping-aspect-ratio
  */
  padding-right: 0;
  padding-top: 0;
  position: absolute;
  right: 0;
  transition: background-color .05s ease;
  -webkit-transition: background-color .05s ease;
  z-index: 9999;
  border-radius: 8px;
  touch-action: manipulation; /* Prevent iOS double tap zoom on the button */
}

.a-enter-ar-button {
  background-size: 100% 90%;
  margin-right: 10px;
  border-radius: 7px;
}

.a-enter-ar-button:active,
.a-enter-ar-button:hover,
.a-enter-vr-button:active,
.a-enter-vr-button:hover {
  background-color: #ef2d5e;
}

.a-enter-vr-button.resethover {
  background-color: rgba(0, 0, 0, 0.35);
}


.a-enter-vr-modal {
  background-color: #666;
  border-radius: 0;
  display: none;
  min-height: 32px;
  margin-right: 70px;
  padding: 9px;
  width: 280px;
  right: 2%;
  position: absolute;
}

.a-enter-vr-modal:after {
  border-bottom: 10px solid transparent;
  border-left: 10px solid #666;
  border-top: 10px solid transparent;
  display: inline-block;
  content: '';
  position: absolute;
  right: -5px;
  top: 5px;
  width: 0;
  height: 0;
}

.a-enter-vr-modal p,
.a-enter-vr-modal a {
  display: inline;
}

.a-enter-vr-modal p {
  margin: 0;
}

.a-enter-vr-modal p:after {
  content: ' ';
}

.a-orientation-modal {
  background: rgba(244, 244, 244, 1) url("data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20xmlns%3Axlink%3D%22http%3A//www.w3.org/1999/xlink%22%20version%3D%221.1%22%20x%3D%220px%22%20y%3D%220px%22%20viewBox%3D%220%200%2090%2090%22%20enable-background%3D%22new%200%200%2090%2090%22%20xml%3Aspace%3D%22preserve%22%3E%3Cpolygon%20points%3D%220%2C0%200%2C0%200%2C0%20%22%3E%3C/polygon%3E%3Cg%3E%3Cpath%20d%3D%22M71.545%2C48.145h-31.98V20.743c0-2.627-2.138-4.765-4.765-4.765H18.456c-2.628%2C0-4.767%2C2.138-4.767%2C4.765v42.789%20%20%20c0%2C2.628%2C2.138%2C4.766%2C4.767%2C4.766h5.535v0.959c0%2C2.628%2C2.138%2C4.765%2C4.766%2C4.765h42.788c2.628%2C0%2C4.766-2.137%2C4.766-4.765V52.914%20%20%20C76.311%2C50.284%2C74.173%2C48.145%2C71.545%2C48.145z%20M18.455%2C16.935h16.344c2.1%2C0%2C3.808%2C1.708%2C3.808%2C3.808v27.401H37.25V22.636%20%20%20c0-0.264-0.215-0.478-0.479-0.478H16.482c-0.264%2C0-0.479%2C0.214-0.479%2C0.478v36.585c0%2C0.264%2C0.215%2C0.478%2C0.479%2C0.478h7.507v7.644%20%20%20h-5.534c-2.101%2C0-3.81-1.709-3.81-3.81V20.743C14.645%2C18.643%2C16.354%2C16.935%2C18.455%2C16.935z%20M16.96%2C23.116h19.331v25.031h-7.535%20%20%20c-2.628%2C0-4.766%2C2.139-4.766%2C4.768v5.828h-7.03V23.116z%20M71.545%2C73.064H28.757c-2.101%2C0-3.81-1.708-3.81-3.808V52.914%20%20%20c0-2.102%2C1.709-3.812%2C3.81-3.812h42.788c2.1%2C0%2C3.809%2C1.71%2C3.809%2C3.812v16.343C75.354%2C71.356%2C73.645%2C73.064%2C71.545%2C73.064z%22%3E%3C/path%3E%3Cpath%20d%3D%22M28.919%2C58.424c-1.466%2C0-2.659%2C1.193-2.659%2C2.66c0%2C1.466%2C1.193%2C2.658%2C2.659%2C2.658c1.468%2C0%2C2.662-1.192%2C2.662-2.658%20%20%20C31.581%2C59.617%2C30.387%2C58.424%2C28.919%2C58.424z%20M28.919%2C62.786c-0.939%2C0-1.703-0.764-1.703-1.702c0-0.939%2C0.764-1.704%2C1.703-1.704%20%20%20c0.94%2C0%2C1.705%2C0.765%2C1.705%2C1.704C30.623%2C62.022%2C29.858%2C62.786%2C28.919%2C62.786z%22%3E%3C/path%3E%3Cpath%20d%3D%22M69.654%2C50.461H33.069c-0.264%2C0-0.479%2C0.215-0.479%2C0.479v20.288c0%2C0.264%2C0.215%2C0.478%2C0.479%2C0.478h36.585%20%20%20c0.263%2C0%2C0.477-0.214%2C0.477-0.478V50.939C70.131%2C50.676%2C69.917%2C50.461%2C69.654%2C50.461z%20M69.174%2C51.417V70.75H33.548V51.417H69.174z%22%3E%3C/path%3E%3Cpath%20d%3D%22M45.201%2C30.296c6.651%2C0%2C12.233%2C5.351%2C12.551%2C11.977l-3.033-2.638c-0.193-0.165-0.507-0.142-0.675%2C0.048%20%20%20c-0.174%2C0.198-0.153%2C0.501%2C0.045%2C0.676l3.883%2C3.375c0.09%2C0.075%2C0.198%2C0.115%2C0.312%2C0.115c0.141%2C0%2C0.273-0.061%2C0.362-0.166%20%20%20l3.371-3.877c0.173-0.2%2C0.151-0.502-0.047-0.675c-0.194-0.166-0.508-0.144-0.676%2C0.048l-2.592%2C2.979%20%20%20c-0.18-3.417-1.629-6.605-4.099-9.001c-2.538-2.461-5.877-3.817-9.404-3.817c-0.264%2C0-0.479%2C0.215-0.479%2C0.479%20%20%20C44.72%2C30.083%2C44.936%2C30.296%2C45.201%2C30.296z%22%3E%3C/path%3E%3C/g%3E%3C/svg%3E") center no-repeat;
  background-size: 50% 50%;
  bottom: 0;
  font-size: 14px;
  font-weight: 600;
  left: 0;
  line-height: 20px;
  right: 0;
  position: fixed;
  top: 0;
  z-index: 9999999;
}

.a-orientation-modal:after {
  color: #666;
  content: "Insert phone into Cardboard holder.";
  display: block;
  position: absolute;
  text-align: center;
  top: 70%;
  transform: translateY(-70%);
  width: 100%;
}

.a-orientation-modal button {
  background: url("data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20xmlns%3Axlink%3D%22http%3A//www.w3.org/1999/xlink%22%20version%3D%221.1%22%20x%3D%220px%22%20y%3D%220px%22%20viewBox%3D%220%200%20100%20100%22%20enable-background%3D%22new%200%200%20100%20100%22%20xml%3Aspace%3D%22preserve%22%3E%3Cpath%20fill%3D%22%23000000%22%20d%3D%22M55.209%2C50l17.803-17.803c1.416-1.416%2C1.416-3.713%2C0-5.129c-1.416-1.417-3.713-1.417-5.129%2C0L50.08%2C44.872%20%20L32.278%2C27.069c-1.416-1.417-3.714-1.417-5.129%2C0c-1.417%2C1.416-1.417%2C3.713%2C0%2C5.129L44.951%2C50L27.149%2C67.803%20%20c-1.417%2C1.416-1.417%2C3.713%2C0%2C5.129c0.708%2C0.708%2C1.636%2C1.062%2C2.564%2C1.062c0.928%2C0%2C1.856-0.354%2C2.564-1.062L50.08%2C55.13l17.803%2C17.802%20%20c0.708%2C0.708%2C1.637%2C1.062%2C2.564%2C1.062s1.856-0.354%2C2.564-1.062c1.416-1.416%2C1.416-3.713%2C0-5.129L55.209%2C50z%22%3E%3C/path%3E%3C/svg%3E") no-repeat;
  border: none;
  height: 50px;
  text-indent: -9999px;
  width: 50px;
}

.a-loader-title {
  background-color: rgba(0, 0, 0, 0.6);
  font-family: sans-serif, monospace;
  text-align: center;
  font-size: 20px;
  height: 50px;
  font-weight: 300;
  line-height: 50px;
  position: absolute;
  right: 0px;
  left: 0px;
  top: 0px;
  color: white;
}

.a-modal {
  position: absolute;
  background: rgba(0, 0, 0, 0.60);
  background-size: 50% 50%;
  bottom: 0;
  font-size: 14px;
  font-weight: 600;
  left: 0;
  line-height: 20px;
  right: 0;
  position: fixed;
  top: 0;
  z-index: 9999999;
}

.a-dialog {
  position: relative;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  z-index: 199995;
  width: 300px;
  height: 200px;
  background-size: contain;
  background-color: white;
  font-family: sans-serif, monospace;
  font-size: 20px;
  border-radius: 3px;
  padding: 6px;
}

.a-dialog-text-container {
  width: 100%;
  height: 70%;
  align-self: flex-start;
  display: flex;
  justify-content: center;
  align-content: center;
  flex-direction: column;
}

.a-dialog-text {
  display: inline-block;
  font-weight: normal;
  font-size: 14pt;
  margin: 8px;
}

.a-dialog-buttons-container {
  display: inline-flex;
  align-self: flex-end;
  width: 100%;
  height: 30%;
}

.a-dialog-button {
  cursor: pointer;
  align-self: center;
  opacity: 0.9;
  height: 80%;
  width: 50%;
  font-size: 12pt;
  margin: 4px;
  border-radius: 2px;
  text-align:center;
  border: none;
  display: inline-block;
  -webkit-transition: all 0.25s ease-in-out;
  transition: all 0.25s ease-in-out;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.10), 0 1px 2px rgba(0, 0, 0, 0.20);
  user-select: none;
}

.a-dialog-permission-button:hover {
  box-shadow: 0 7px 14px rgba(0,0,0,0.20), 0 2px 2px rgba(0,0,0,0.20);
}

.a-dialog-allow-button {
  background-color: #00ceff;
}

.a-dialog-deny-button {
  background-color: #ff005b;
}

.a-dialog-ok-button {
  background-color: #00ceff;
  width: 100%;
}

.a-dom-overlay:not(.a-no-style) {
  overflow: hidden;
  position: absolute;
  pointer-events: none;
  box-sizing: border-box;
  bottom: 0;
  left: 0;
  right: 0;
  top: 0;
  padding: 1em;
}

.a-dom-overlay:not(.a-no-style)>* {
  pointer-events: auto;
}

/*# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8uL3NyYy9zdHlsZS9hZnJhbWUuY3NzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBLHNDQUFzQztBQUN0QztFQUNFLFNBQVM7RUFDVCxPQUFPO0VBQ1AsZUFBZTtFQUNmLFFBQVE7RUFDUixNQUFNO0FBQ1I7O0FBRUE7RUFDRSxZQUFZO0VBQ1osU0FBUztFQUNULGdCQUFnQjtFQUNoQixVQUFVO0VBQ1YsV0FBVztBQUNiOztBQUVBLG9EQUFvRDtBQUNwRDtFQUNFLHNCQUFzQjtFQUN0Qix1QkFBdUI7RUFDdkIsaUJBQWlCO0VBQ2pCLGtCQUFrQjtFQUNsQixtQkFBbUI7RUFDbkIsb0JBQW9CO0VBQ3BCLDBCQUEwQjtBQUM1Qjs7QUFFQTs7RUFFRSxVQUFVO0VBQ1YsV0FBVztBQUNiOztBQUVBO0VBQ0UsV0FBVztBQUNiOztBQUVBLGdFQUFnRTtBQUNoRTtFQUNFLDZCQUE2QjtBQUMvQjs7QUFFQTtFQUNFLHdCQUF3QjtBQUMxQjs7QUFFQTtFQUNFLFlBQVk7RUFDWixPQUFPO0VBQ1Asa0JBQWtCO0VBQ2xCLE1BQU07RUFDTixXQUFXO0FBQ2I7O0FBRUE7RUFDRSxZQUFZO0VBQ1osaUJBQWlCO0VBQ2pCLG9CQUFvQjtBQUN0Qjs7QUFFQTtFQUNFLGVBQWU7QUFDakI7O0FBRUE7RUFDRSx5QkFBeUI7RUFDekIsZUFBZTtFQUNmLFNBQVM7RUFDVCxRQUFRO0VBQ1IsaUJBQWlCO0VBQ2pCLFdBQVc7RUFDWCxxQkFBcUI7RUFDckIsZUFBZTtFQUNmLDhCQUE4QjtFQUM5QixrQkFBa0I7RUFDbEIsY0FBYztFQUNkLFlBQVk7QUFDZDs7QUFFQSwrQkFBK0I7QUFDL0Isb0JBQW9CLE9BQU8sVUFBVSxFQUFFLEVBQUUsTUFBTSxVQUFVLEVBQUUsRUFBRTtBQUM3RCxvQkFBb0IsT0FBTyxVQUFVLEVBQUUsRUFBRSxNQUFNLFVBQVUsRUFBRSxFQUFFO0FBQzdELG9CQUFvQixPQUFPLFVBQVUsRUFBRSxFQUFFLE1BQU0sVUFBVSxFQUFFLEVBQUU7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LHNCQUFzQjtFQUN0QixTQUFTO0VBQ1QsT0FBTztFQUNQLFFBQVE7RUFDUixNQUFNO0VBQ04sWUFBWTtBQUNkOztBQUVBO0VBQ0Usb0JBQW9CO0FBQ3RCIiwic291cmNlc0NvbnRlbnQiOlsiLyogLmEtZnVsbHNjcmVlbiBtZWFucyBub3QgZW1iZWRkZWQuICovXG5odG1sLmEtZnVsbHNjcmVlbiB7XG4gIGJvdHRvbTogMDtcbiAgbGVmdDogMDtcbiAgcG9zaXRpb246IGZpeGVkO1xuICByaWdodDogMDtcbiAgdG9wOiAwO1xufVxuXG5odG1sLmEtZnVsbHNjcmVlbiBib2R5IHtcbiAgaGVpZ2h0OiAxMDAlO1xuICBtYXJnaW46IDA7XG4gIG92ZXJmbG93OiBoaWRkZW47XG4gIHBhZGRpbmc6IDA7XG4gIHdpZHRoOiAxMDAlO1xufVxuXG4vKiBDbGFzcyBpcyByZW1vdmVkIHdoZW4gZG9pbmcgPGEtc2NlbmUgZW1iZWRkZWQ+LiAqL1xuaHRtbC5hLWZ1bGxzY3JlZW4gLmEtY2FudmFzIHtcbiAgd2lkdGg6IDEwMCUgIWltcG9ydGFudDtcbiAgaGVpZ2h0OiAxMDAlICFpbXBvcnRhbnQ7XG4gIHRvcDogMCAhaW1wb3J0YW50O1xuICBsZWZ0OiAwICFpbXBvcnRhbnQ7XG4gIHJpZ2h0OiAwICFpbXBvcnRhbnQ7XG4gIGJvdHRvbTogMCAhaW1wb3J0YW50O1xuICBwb3NpdGlvbjogZml4ZWQgIWltcG9ydGFudDtcbn1cblxuaHRtbDpub3QoLmEtZnVsbHNjcmVlbikgLmEtZW50ZXItdnIsXG5odG1sOm5vdCguYS1mdWxsc2NyZWVuKSAuYS1lbnRlci1hciB7XG4gIHJpZ2h0OiA1cHg7XG4gIGJvdHRvbTogNXB4O1xufVxuXG5odG1sOm5vdCguYS1mdWxsc2NyZWVuKSAuYS1lbnRlci1hciB7XG4gIHJpZ2h0OiA2MHB4O1xufVxuXG4vKiBJbiBjaHJvbWUgbW9iaWxlIHRoZSB1c2VyIGFnZW50IHN0eWxlc2hlZXQgc2V0IGl0IHRvIHdoaXRlICAqL1xuOi13ZWJraXQtZnVsbC1zY3JlZW4ge1xuICBiYWNrZ3JvdW5kLWNvbG9yOiB0cmFuc3BhcmVudDtcbn1cblxuLmEtaGlkZGVuIHtcbiAgZGlzcGxheTogbm9uZSAhaW1wb3J0YW50O1xufVxuXG4uYS1jYW52YXMge1xuICBoZWlnaHQ6IDEwMCU7XG4gIGxlZnQ6IDA7XG4gIHBvc2l0aW9uOiBhYnNvbHV0ZTtcbiAgdG9wOiAwO1xuICB3aWR0aDogMTAwJTtcbn1cblxuLmEtY2FudmFzLmEtZ3JhYi1jdXJzb3I6aG92ZXIge1xuICBjdXJzb3I6IGdyYWI7XG4gIGN1cnNvcjogLW1vei1ncmFiO1xuICBjdXJzb3I6IC13ZWJraXQtZ3JhYjtcbn1cblxuY2FudmFzLmEtY2FudmFzLmEtbW91c2UtY3Vyc29yLWhvdmVyOmhvdmVyIHtcbiAgY3Vyc29yOiBwb2ludGVyO1xufVxuXG4uYS1pbnNwZWN0b3ItbG9hZGVyIHtcbiAgYmFja2dyb3VuZC1jb2xvcjogI2VkMzE2MDtcbiAgcG9zaXRpb246IGZpeGVkO1xuICBsZWZ0OiAzcHg7XG4gIHRvcDogM3B4O1xuICBwYWRkaW5nOiA2cHggMTBweDtcbiAgY29sb3I6ICNmZmY7XG4gIHRleHQtZGVjb3JhdGlvbjogbm9uZTtcbiAgZm9udC1zaXplOiAxMnB4O1xuICBmb250LWZhbWlseTogUm9ib3RvLHNhbnMtc2VyaWY7XG4gIHRleHQtYWxpZ246IGNlbnRlcjtcbiAgei1pbmRleDogOTk5OTk7XG4gIHdpZHRoOiAyMDRweDtcbn1cblxuLyogSW5zcGVjdG9yIGxvYWRlciBhbmltYXRpb24gKi9cbkBrZXlmcmFtZXMgZG90cy0xIHsgZnJvbSB7IG9wYWNpdHk6IDA7IH0gMjUlIHsgb3BhY2l0eTogMTsgfSB9XG5Aa2V5ZnJhbWVzIGRvdHMtMiB7IGZyb20geyBvcGFjaXR5OiAwOyB9IDUwJSB7IG9wYWNpdHk6IDE7IH0gfVxuQGtleWZyYW1lcyBkb3RzLTMgeyBmcm9tIHsgb3BhY2l0eTogMDsgfSA3NSUgeyBvcGFjaXR5OiAxOyB9IH1cbkAtd2Via2l0LWtleWZyYW1lcyBkb3RzLTEgeyBmcm9tIHsgb3BhY2l0eTogMDsgfSAyNSUgeyBvcGFjaXR5OiAxOyB9IH1cbkAtd2Via2l0LWtleWZyYW1lcyBkb3RzLTIgeyBmcm9tIHsgb3BhY2l0eTogMDsgfSA1MCUgeyBvcGFjaXR5OiAxOyB9IH1cbkAtd2Via2l0LWtleWZyYW1lcyBkb3RzLTMgeyBmcm9tIHsgb3BhY2l0eTogMDsgfSA3NSUgeyBvcGFjaXR5OiAxOyB9IH1cblxuLmEtaW5zcGVjdG9yLWxvYWRlciAuZG90cyBzcGFuIHtcbiAgYW5pbWF0aW9uOiBkb3RzLTEgMnMgaW5maW5pdGUgc3RlcHMoMSk7XG4gIC13ZWJraXQtYW5pbWF0aW9uOiBkb3RzLTEgMnMgaW5maW5pdGUgc3RlcHMoMSk7XG59XG5cbi5hLWluc3BlY3Rvci1sb2FkZXIgLmRvdHMgc3BhbjpmaXJzdC1jaGlsZCArIHNwYW4ge1xuICBhbmltYXRpb24tbmFtZTogZG90cy0yO1xuICAtd2Via2l0LWFuaW1hdGlvbi1uYW1lOiBkb3RzLTI7XG59XG5cbi5hLWluc3BlY3Rvci1sb2FkZXIgLmRvdHMgc3BhbjpmaXJzdC1jaGlsZCArIHNwYW4gKyBzcGFuIHtcbiAgYW5pbWF0aW9uLW5hbWU6IGRvdHMtMztcbiAgLXdlYmtpdC1hbmltYXRpb24tbmFtZTogZG90cy0zO1xufVxuXG5hLXNjZW5lIHtcbiAgZGlzcGxheTogYmxvY2s7XG4gIHBvc2l0aW9uOiByZWxhdGl2ZTtcbiAgaGVpZ2h0OiAxMDAlO1xuICB3aWR0aDogMTAwJTtcbn1cblxuYS1hc3NldHMsXG5hLXNjZW5lIHZpZGVvLFxuYS1zY2VuZSBpbWcsXG5hLXNjZW5lIGF1ZGlvIHtcbiAgZGlzcGxheTogbm9uZTtcbn1cblxuLmEtZW50ZXItdnItbW9kYWwsXG4uYS1vcmllbnRhdGlvbi1tb2RhbCB7XG4gIGZvbnQtZmFtaWx5OiBDb25zb2xhcywgQW5kYWxlIE1vbm8sIENvdXJpZXIgTmV3LCBtb25vc3BhY2U7XG59XG5cbi5hLWVudGVyLXZyLW1vZGFsIGEge1xuICBib3JkZXItYm90dG9tOiAxcHggc29saWQgI2ZmZjtcbiAgcGFkZGluZzogMnB4IDA7XG4gIHRleHQtZGVjb3JhdGlvbjogbm9uZTtcbiAgdHJhbnNpdGlvbjogLjFzIGNvbG9yIGVhc2UtaW47XG59XG5cbi5hLWVudGVyLXZyLW1vZGFsIGE6aG92ZXIge1xuICBiYWNrZ3JvdW5kLWNvbG9yOiAjZmZmO1xuICBjb2xvcjogIzExMTtcbiAgcGFkZGluZzogMnB4IDRweDtcbiAgcG9zaXRpb246IHJlbGF0aXZlO1xuICBsZWZ0OiAtNHB4O1xufVxuXG4uYS1lbnRlci12cixcbi5hLWVudGVyLWFyIHtcbiAgZm9udC1mYW1pbHk6IHNhbnMtc2VyaWYsIG1vbm9zcGFjZTtcbiAgZm9udC1zaXplOiAxM3B4O1xuICB3aWR0aDogMTAwJTtcbiAgZm9udC13ZWlnaHQ6IDIwMDtcbiAgbGluZS1oZWlnaHQ6IDE2cHg7XG4gIHBvc2l0aW9uOiBhYnNvbHV0ZTtcbiAgcmlnaHQ6IDIwcHg7XG4gIGJvdHRvbTogMjBweDtcbn1cblxuLmEtZW50ZXItYXIge1xuICByaWdodDogODBweDtcbn1cblxuLmEtZW50ZXItdnItYnV0dG9uLFxuLmEtZW50ZXItdnItbW9kYWwsXG4uYS1lbnRlci12ci1tb2RhbCBhIHtcbiAgY29sb3I6ICNmZmY7XG4gIHVzZXItc2VsZWN0OiBub25lO1xuICBvdXRsaW5lOiBub25lO1xufVxuXG4uYS1lbnRlci12ci1idXR0b24ge1xuICBiYWNrZ3JvdW5kOiByZ2JhKDAsIDAsIDAsIDAuMzUpIHVybChcImRhdGE6aW1hZ2Uvc3ZnK3htbCwlM0NzdmcgeG1sbnM9J2h0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnJyB3aWR0aD0nMTA4JyBoZWlnaHQ9JzYyJyB2aWV3Qm94PScwIDAgMTA4IDYyJyUzRSUzQ3RpdGxlJTNFYWZyYW1lLXZybW9kZS1ub2JvcmRlci1yZWR1Y2VkLXRyYWNraW5nJTNDL3RpdGxlJTNFJTNDcGF0aCBkPSdNNjguODEsMjEuNTZINjQuMjN2OC4yN2g0LjU4YTQuMTMsNC4xMywwLDAsMCwzLjEtMS4wOSw0LjIsNC4yLDAsMCwwLDEtMyw0LjI0LDQuMjQsMCwwLDAtMS0zQTQuMDUsNC4wNSwwLDAsMCw2OC44MSwyMS41NlonIGZpbGw9JyUyM2ZmZicvJTNFJTNDcGF0aCBkPSdNOTYsMEgxMkExMiwxMiwwLDAsMCwwLDEyVjUwQTEyLDEyLDAsMCwwLDEyLDYySDk2YTEyLDEyLDAsMCwwLDEyLTEyVjEyQTEyLDEyLDAsMCwwLDk2LDBaTTQxLjksNDZIMzRMMjQsMTZoOGw2LDIxLjg0LDYtMjEuODRINTJabTM5LjI5LDBINzMuNDRMNjguMTUsMzUuMzlINjQuMjNWNDZINTdWMTZINjguODFxNS4zMiwwLDguMzQsMi4zN2E4LDgsMCwwLDEsMyw2LjY5LDkuNjgsOS42OCwwLDAsMS0xLjI3LDUuMTgsOC45LDguOSwwLDAsMS00LDMuMzRsNi4yNiwxMi4xMVonIGZpbGw9JyUyM2ZmZicvJTNFJTNDL3N2ZyUzRVwiKSA1MCUgNTAlIG5vLXJlcGVhdDtcbn1cblxuLmEtZW50ZXItYXItYnV0dG9uIHtcbiAgYmFja2dyb3VuZDogcmdiYSgwLCAwLCAwLCAwLjIwKSB1cmwoXCJkYXRhOmltYWdlL3N2Zyt4bWwsJTNDc3ZnIHhtbG5zPSdodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2Zycgd2lkdGg9JzEwOCcgaGVpZ2h0PSc2Micgdmlld0JveD0nMCAwIDEwOCA2MiclM0UlM0N0aXRsZSUzRWFmcmFtZS1hcm1vZGUtbm9ib3JkZXItcmVkdWNlZC10cmFja2luZyUzQy90aXRsZSUzRSUzQ3BhdGggZD0nTTk2LDBIMTJBMTIsMTIsMCwwLDAsMCwxMlY1MEExMiwxMiwwLDAsMCwxMiw2Mkg5NmExMiwxMiwwLDAsMCwxMi0xMlYxMkExMiwxMiwwLDAsMCw5NiwwWm04LDUwYTgsOCwwLDAsMS04LDhIMTJhOCw4LDAsMCwxLTgtOFYxMmE4LDgsMCwwLDEsOC04SDk2YTgsOCwwLDAsMSw4LDhaJyBmaWxsPSclMjNmZmYnLyUzRSUzQ3BhdGggZD0nTTQzLjM1LDM5LjgySDMyLjUxTDMwLjQ1LDQ2SDIzLjg4TDM1LDE2aDUuNzNMNTIsNDZINDUuNDNabS05LjE3LTVoNy41TDM3LjkxLDIzLjU4WicgZmlsbD0nJTIzZmZmJy8lM0UlM0NwYXRoIGQ9J002OC4xMSwzNUg2My4xOFY0Nkg1N1YxNkg2OC4xNXE1LjMxLDAsOC4yLDIuMzdhOC4xOCw4LjE4LDAsMCwxLDIuODgsNi43LDkuMjIsOS4yMiwwLDAsMS0xLjMzLDUuMTIsOS4wOSw5LjA5LDAsMCwxLTQsMy4yNmw2LjQ5LDEyLjI2VjQ2SDczLjczWm0tNC45My01aDVhNS4wOSw1LjA5LDAsMCwwLDMuNi0xLjE4LDQuMjEsNC4yMSwwLDAsMCwxLjI4LTMuMjcsNC41Niw0LjU2LDAsMCwwLTEuMi0zLjM0QTUsNSwwLDAsMCw2OC4xNSwyMWgtNVonIGZpbGw9JyUyM2ZmZicvJTNFJTNDL3N2ZyUzRVwiKSA1MCUgNTAlIG5vLXJlcGVhdDtcbn1cblxuLmEtZW50ZXItdnIuZnVsbHNjcmVlbiAuYS1lbnRlci12ci1idXR0b24ge1xuICBiYWNrZ3JvdW5kLWltYWdlOiB1cmwoXCJkYXRhOmltYWdlL3N2Zyt4bWwsJTNDJTNGeG1sIHZlcnNpb249JzEuMCcgZW5jb2Rpbmc9J1VURi04JyBzdGFuZGFsb25lPSdubyclM0YlM0UlM0Nzdmcgd2lkdGg9JzEwOCcgaGVpZ2h0PSc2Micgdmlld0JveD0nMCAwIDEwOCA2MicgdmVyc2lvbj0nMS4xJyBpZD0nc3ZnMzIwJyBzb2RpcG9kaTpkb2NuYW1lPSdmdWxsc2NyZWVuLWFmcmFtZS5zdmcnIHhtbDpzcGFjZT0ncHJlc2VydmUnIGlua3NjYXBlOnZlcnNpb249JzEuMi4xICg5YzZkNDFlICAyMDIyLTA3LTE0KScgeG1sbnM6aW5rc2NhcGU9J2h0dHA6Ly93d3cuaW5rc2NhcGUub3JnL25hbWVzcGFjZXMvaW5rc2NhcGUnIHhtbG5zOnNvZGlwb2RpPSdodHRwOi8vc29kaXBvZGkuc291cmNlZm9yZ2UubmV0L0RURC9zb2RpcG9kaS0wLmR0ZCcgeG1sbnM9J2h0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnJyB4bWxuczpzdmc9J2h0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnJyB4bWxuczpyZGY9J2h0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyUyMycgeG1sbnM6Y2M9J2h0dHA6Ly9jcmVhdGl2ZWNvbW1vbnMub3JnL25zJTIzJyB4bWxuczpkYz0naHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8nJTNFJTNDZGVmcyBpZD0nZGVmczMyNCcgLyUzRSUzQ3NvZGlwb2RpOm5hbWVkdmlldyBpZD0nbmFtZWR2aWV3MzIyJyBwYWdlY29sb3I9JyUyM2ZmZmZmZicgYm9yZGVyY29sb3I9JyUyMzAwMDAwMCcgYm9yZGVyb3BhY2l0eT0nMC4yNScgaW5rc2NhcGU6c2hvd3BhZ2VzaGFkb3c9JzInIGlua3NjYXBlOnBhZ2VvcGFjaXR5PScwLjAnIGlua3NjYXBlOnBhZ2VjaGVja2VyYm9hcmQ9JzAnIGlua3NjYXBlOmRlc2tjb2xvcj0nJTIzZDFkMWQxJyBzaG93Z3JpZD0nZmFsc2UnIGlua3NjYXBlOnpvb209JzMuODA2NDUxNicgaW5rc2NhcGU6Y3g9JzkxLjQyMzcyOScgaW5rc2NhcGU6Y3k9Jy0xLjQ0NDkxNTMnIGlua3NjYXBlOndpbmRvdy13aWR0aD0nMTQ0MCcgaW5rc2NhcGU6d2luZG93LWhlaWdodD0nODQ3JyBpbmtzY2FwZTp3aW5kb3cteD0nMzInIGlua3NjYXBlOndpbmRvdy15PScyNScgaW5rc2NhcGU6d2luZG93LW1heGltaXplZD0nMCcgaW5rc2NhcGU6Y3VycmVudC1sYXllcj0nc3ZnMzIwJyAvJTNFJTNDdGl0bGUgaWQ9J3RpdGxlMzEyJyUzRWFmcmFtZS1hcm1vZGUtbm9ib3JkZXItcmVkdWNlZC10cmFja2luZyUzQy90aXRsZSUzRSUzQ3BhdGggZD0nTTk2IDBIMTJBMTIgMTIgMCAwIDAgMCAxMlY1MEExMiAxMiAwIDAgMCAxMiA2Mkg5NmExMiAxMiAwIDAgMCAxMi0xMlYxMkExMiAxMiAwIDAgMCA5NiAwWm04IDUwYTggOCAwIDAgMS04IDhIMTJhOCA4IDAgMCAxLTgtOFYxMmE4IDggMCAwIDEgOC04SDk2YTggOCAwIDAgMSA4IDhaJyBmaWxsPSclMjNmZmYnIGlkPSdwYXRoMzE0JyBzdHlsZT0nZmlsbDolMjNmZmZmZmYnIC8lM0UlM0NnIGlkPSdnMzU2JyB0cmFuc2Zvcm09J3RyYW5zbGF0ZSgtMjA2LjYxMDE3IC0yMzIuNjE4NjQpJyUzRSUzQy9nJTNFJTNDZyBpZD0nZzM1OCcgdHJhbnNmb3JtPSd0cmFuc2xhdGUoLTIwNi42MTAxNyAtMjMyLjYxODY0KSclM0UlM0MvZyUzRSUzQ2cgaWQ9J2czNjAnIHRyYW5zZm9ybT0ndHJhbnNsYXRlKC0yMDYuNjEwMTcgLTIzMi42MTg2NCknJTNFJTNDL2clM0UlM0NnIGlkPSdnMzYyJyB0cmFuc2Zvcm09J3RyYW5zbGF0ZSgtMjA2LjYxMDE3IC0yMzIuNjE4NjQpJyUzRSUzQy9nJTNFJTNDZyBpZD0nZzM2NCcgdHJhbnNmb3JtPSd0cmFuc2xhdGUoLTIwNi42MTAxNyAtMjMyLjYxODY0KSclM0UlM0MvZyUzRSUzQ2cgaWQ9J2czNjYnIHRyYW5zZm9ybT0ndHJhbnNsYXRlKC0yMDYuNjEwMTcgLTIzMi42MTg2NCknJTNFJTNDL2clM0UlM0NnIGlkPSdnMzY4JyB0cmFuc2Zvcm09J3RyYW5zbGF0ZSgtMjA2LjYxMDE3IC0yMzIuNjE4NjQpJyUzRSUzQy9nJTNFJTNDZyBpZD0nZzM3MCcgdHJhbnNmb3JtPSd0cmFuc2xhdGUoLTIwNi42MTAxNyAtMjMyLjYxODY0KSclM0UlM0MvZyUzRSUzQ2cgaWQ9J2czNzInIHRyYW5zZm9ybT0ndHJhbnNsYXRlKC0yMDYuNjEwMTcgLTIzMi42MTg2NCknJTNFJTNDL2clM0UlM0NnIGlkPSdnMzc0JyB0cmFuc2Zvcm09J3RyYW5zbGF0ZSgtMjA2LjYxMDE3IC0yMzIuNjE4NjQpJyUzRSUzQy9nJTNFJTNDZyBpZD0nZzM3NicgdHJhbnNmb3JtPSd0cmFuc2xhdGUoLTIwNi42MTAxNyAtMjMyLjYxODY0KSclM0UlM0MvZyUzRSUzQ2cgaWQ9J2czNzgnIHRyYW5zZm9ybT0ndHJhbnNsYXRlKC0yMDYuNjEwMTcgLTIzMi42MTg2NCknJTNFJTNDL2clM0UlM0NnIGlkPSdnMzgwJyB0cmFuc2Zvcm09J3RyYW5zbGF0ZSgtMjA2LjYxMDE3IC0yMzIuNjE4NjQpJyUzRSUzQy9nJTNFJTNDZyBpZD0nZzM4MicgdHJhbnNmb3JtPSd0cmFuc2xhdGUoLTIwNi42MTAxNyAtMjMyLjYxODY0KSclM0UlM0MvZyUzRSUzQ2cgaWQ9J2czODQnIHRyYW5zZm9ybT0ndHJhbnNsYXRlKC0yMDYuNjEwMTcgLTIzMi42MTg2NCknJTNFJTNDL2clM0UlM0NtZXRhZGF0YSBpZD0nbWV0YWRhdGE1NjEnJTNFJTNDcmRmOlJERiUzRSUzQ2NjOldvcmsgcmRmOmFib3V0PScnJTNFJTNDZGM6dGl0bGUlM0VhZnJhbWUtYXJtb2RlLW5vYm9yZGVyLXJlZHVjZWQtdHJhY2tpbmclM0MvZGM6dGl0bGUlM0UlM0MvY2M6V29yayUzRSUzQy9yZGY6UkRGJTNFJTNDL21ldGFkYXRhJTNFJTNDcGF0aCBkPSdtIDk4LjE2ODUxMSA0MC4wODM2NDkgYyAwIC0xLjMwMzY4MSAtMC45OTg3ODggLTIuMzU4MDQxIC0yLjIzOTM4OSAtMi4zNTgwNDEgLTEuMjMwMDg4IDAuMDAzMSAtMi4yNDA4OTIgMS4wNTQzNiAtMi4yNDA4OTIgMi4zNTgwNDEgdiA0Ljg4MTI5NiBsIC05LjA0MTY2MSAtOS4wNDE2NjIgYyAtMC44NzQxMjkgLTAuODc1NjMxIC0yLjI4ODk1NCAtMC44NzU2MzEgLTMuMTYzMDggMCAtMC44NzQxMjkgMC44NzQxMjYgLTAuODc0MTI5IDIuMjkzNDU5IDAgMy4xNjc1ODUgbCA4Ljk5NTEwMSA4Ljk5MjEwMSBoIC00Ljg1ODc2NyBjIC0xLjMyMzIwNiAwLjAwMzEgLTIuMzg5NTgzIDEuMDA0Nzk2IC0yLjM4OTU4MyAyLjIzOTM4NiAwIDEuMjM3NTk4IDEuMDY2Mzc3IDIuMjM3ODg4IDIuMzg5NTgzIDIuMjM3ODg4IGggMTAuMTU0NTk5IGMgMS4zMjMyMDYgMCAyLjM4ODA4MiAtMC45OTg3ODkgMi4zOTI1ODcgLTIuMjM3ODg4IC0wLjAwNDQgLTAuMDMzMDUgLTAuMDA5IC0wLjA1ODU4IC0wLjAxMzQgLTAuMDkxNjEgMC4wMDQ2IC0wLjA0MjA3IDAuMDEzNCAtMC4wODcxMiAwLjAxMzQgLTAuMTMwNjYgViA0MC4wODUxNzIgaCAtMS41MmUtNCcgaWQ9J3BhdGg1OTYnIHN0eWxlPSdmaWxsOiUyM2ZmZmZmZiUzQnN0cm9rZS13aWR0aDoxLjUwMTk0JyAvJTNFJTNDcGF0aCBkPSdtIDIzLjA5MTAwMiAzNS45MjE3ODEgLTkuMDI2NjQzIDkuMDQxNjYyIHYgLTQuODgxMjk2IGMgMCAtMS4zMDM2ODEgLTEuMDA5MzAyIC0yLjM1NTAzNyAtMi4yNDIzOTMgLTIuMzU4MDQxIC0xLjIzNzU5OCAwIC0yLjIzNzg4OCAxLjA1NDM2IC0yLjIzNzg4OCAyLjM1ODA0MSBsIC0wLjAwMzEgMTAuMDE2NDIxIGMgMCAwLjA0MzU2IDAuMDEyMTEgMC4wODg2MiAwLjAwMTUgMC4xMzA2NTkgLTAuMDAzMSAwLjAzMTUzIC0wLjAwOSAwLjA1NzA5IC0wLjAxMjExIDAuMDkxNjEgMC4wMDMxIDEuMjM5MDk5IDEuMDY5Mzc5IDIuMjM3ODg4IDIuMzkxMDg1IDIuMjM3ODg4IGggMTAuMTU2MTAxIGMgMS4zMjAyMDIgMCAyLjM4ODA3OSAtMS4wMDAyOTEgMi4zODgwNzkgLTIuMjM3ODg4IDAgLTEuMjM0NTkxIC0xLjA2Nzg3NyAtMi4yMzYzODMgLTIuMzg4MDc5IC0yLjIzOTM4NyBoIC00Ljg1ODc2NyBsIDguOTk1MTAxIC04Ljk5MjEgYyAwLjg3MTEyNiAtMC44NzQxMjcgMC44NzExMjYgLTIuMjkzNDU5IDAgLTMuMTY3NTg2IC0wLjg3NTYyOCAtMC44NzcxMzIgLTIuMjkxOTU3IC0wLjg3NzEzMiAtMy4xNjkwODcgLTEuNTJlLTQnIGlkPSdwYXRoNTk4JyBzdHlsZT0nZmlsbDolMjNmZmZmZmYlM0JzdHJva2Utd2lkdGg6MS41MDE5NCcgLyUzRSUzQ3BhdGggZD0nbSA4NC42NDk1NzIgMjUuOTc4MDMzIDkuMDQxNjYyIC05LjA0MTY2NCB2IDQuODgxMjk4IGMgMCAxLjI5OTE3NiAxLjAxMDgwNiAyLjM1MDUzMiAyLjI0MDg5MSAyLjM1NTAzNyAxLjI0MDYwMSAwIDIuMjM5MzkgLTEuMDU1ODYxIDIuMjM5MzkgLTIuMzU1MDM3IFYgMTEuNzk4MjQyIGMgMCAtMC4wNDM1NiAtMC4wMDkgLTAuMDg4NjIgLTAuMDEzNCAtMC4xMjc2NzEgMC4wMDQ0IC0wLjAzMTUzIDAuMDA5IC0wLjA2MTU3IDAuMDEzNCAtMC4wOTMxMyAtMC4wMDQ0IC0xLjI0MDU5OCAtMS4wNjkzNzkgLTIuMjM5Mzg3MyAtMi4zOTEwODUgLTIuMjM5Mzg3MyBoIC0xMC4xNTQ2IGMgLTEuMzIzMjA1IDAgLTIuMzg5NTggMC45OTg3ODkzIC0yLjM4OTU4IDIuMjM5Mzg3MyAwIDEuMjMzMDkxIDEuMDY2Mzc1IDIuMjM3ODg3IDIuMzg5NTggMi4yNDA4OTEgaCA0Ljg1ODc2OCBsIC04Ljk5NTEwMiA4Ljk5MjEgYyAtMC44NzQxMjkgMC44NzI2MjUgLTAuODc0MTI5IDIuMjg4OTU0IDAgMy4xNjE1NzggMC44NzQxMjcgMC44ODAxMzcgMi4yODg5NTEgMC44ODAxMzcgMy4xNjMwOCAxLjVlLTQnIGlkPSdwYXRoNjAwJyBzdHlsZT0nZmlsbDolMjNmZmZmZmYlM0JzdHJva2Utd2lkdGg6MS41MDE5NCcgLyUzRSUzQ3BhdGggZD0nbSAxNy4yNjQ5ODggMTMuODIyODUzIGggNC44NTcyNjUgYyAxLjMyMDIwMiAtMC4wMDMxIDIuMzg4MDc5IC0xLjAwNzggMi4zODgwNzkgLTIuMjQwODg5IDAgLTEuMjQwNjAxIC0xLjA2Nzg3NyAtMi4yMzkzODkzIC0yLjM4ODA3OSAtMi4yMzkzODkzIEggMTEuOTY3NjU0IGMgLTEuMzIxNzA3IDAgLTIuMzg4MDgyIDAuOTk4Nzg4MyAtMi4zOTEwODUgMi4yMzkzODkzIDAuMDAzMSAwLjAzMTUzIDAuMDA5IDAuMDYxNTcgMC4wMTIxMSAwLjA5MzEzIC0wLjAwMzEgMC4wMzkwNSAtMC4wMDE1IDAuMDgyNjIgLTAuMDAxNSAwLjEyNzY3MSBsIDAuMDAzMSAxMC4wMjA5MjYgYyAwIDEuMjk5MTc2IDEuMDAwMjkgMi4zNTUwMzggMi4yMzc4ODcgMi4zNTUwMzggMS4yMzMwOTIgLTAuMDA0NCAyLjI0MjM5MyAtMS4wNTU4NjIgMi4yNDIzOTMgLTIuMzU1MDM4IHYgLTQuODgxMjk1IGwgOS4wMjY2NDQgOS4wNDE2NjEgYyAwLjg3NzEzMiAwLjg3ODYzNSAyLjI5MzQ1OSAwLjg3ODYzNSAzLjE2OTA4NyAwIDAuODcxMTI1IC0wLjg3MjYyNCAwLjg3MTEyNSAtMi4yODg5NTMgMCAtMy4xNjE1NzcgbCAtOC45OTUyODIgLTguOTkzNjE2JyBpZD0ncGF0aDYwMicgc3R5bGU9J2ZpbGw6JTIzZmZmZmZmJTNCc3Ryb2tlLXdpZHRoOjEuNTAxOTQnIC8lM0UlM0Mvc3ZnJTNFXCIpO1xufVxuXG4uYS1lbnRlci12ci1idXR0b24sXG4uYS1lbnRlci1hci1idXR0b24ge1xuICBiYWNrZ3JvdW5kLXNpemU6IDkwJSA5MCU7XG4gIGJvcmRlcjogMDtcbiAgYm90dG9tOiAwO1xuICBjdXJzb3I6IHBvaW50ZXI7XG4gIG1pbi13aWR0aDogNThweDtcbiAgbWluLWhlaWdodDogMzRweDtcbiAgLyogMS43NDQxODYwNDY1MSAqL1xuICAvKlxuICAgIEluIG9yZGVyIHRvIGtlZXAgdGhlIGFzcGVjdCByYXRpbyB3aGVuIHJlc2l6aW5nXG4gICAgcGFkZGluZy10b3AgcGVyY2VudGFnZXMgYXJlIHJlbGF0aXZlIHRvIHRoZSBjb250YWluaW5nIGJsb2NrJ3Mgd2lkdGguXG4gICAgaHR0cDovL3N0YWNrb3ZlcmZsb3cuY29tL3F1ZXN0aW9ucy8xMjEyMTA5MC9yZXNwb25zaXZlbHktY2hhbmdlLWRpdi1zaXplLWtlZXBpbmctYXNwZWN0LXJhdGlvXG4gICovXG4gIHBhZGRpbmctcmlnaHQ6IDA7XG4gIHBhZGRpbmctdG9wOiAwO1xuICBwb3NpdGlvbjogYWJzb2x1dGU7XG4gIHJpZ2h0OiAwO1xuICB0cmFuc2l0aW9uOiBiYWNrZ3JvdW5kLWNvbG9yIC4wNXMgZWFzZTtcbiAgLXdlYmtpdC10cmFuc2l0aW9uOiBiYWNrZ3JvdW5kLWNvbG9yIC4wNXMgZWFzZTtcbiAgei1pbmRleDogOTk5OTtcbiAgYm9yZGVyLXJhZGl1czogOHB4O1xuICB0b3VjaC1hY3Rpb246IG1hbmlwdWxhdGlvbjsgLyogUHJldmVudCBpT1MgZG91YmxlIHRhcCB6b29tIG9uIHRoZSBidXR0b24gKi9cbn1cblxuLmEtZW50ZXItYXItYnV0dG9uIHtcbiAgYmFja2dyb3VuZC1zaXplOiAxMDAlIDkwJTtcbiAgbWFyZ2luLXJpZ2h0OiAxMHB4O1xuICBib3JkZXItcmFkaXVzOiA3cHg7XG59XG5cbi5hLWVudGVyLWFyLWJ1dHRvbjphY3RpdmUsXG4uYS1lbnRlci1hci1idXR0b246aG92ZXIsXG4uYS1lbnRlci12ci1idXR0b246YWN0aXZlLFxuLmEtZW50ZXItdnItYnV0dG9uOmhvdmVyIHtcbiAgYmFja2dyb3VuZC1jb2xvcjogI2VmMmQ1ZTtcbn1cblxuLmEtZW50ZXItdnItYnV0dG9uLnJlc2V0aG92ZXIge1xuICBiYWNrZ3JvdW5kLWNvbG9yOiByZ2JhKDAsIDAsIDAsIDAuMzUpO1xufVxuXG5cbi5hLWVudGVyLXZyLW1vZGFsIHtcbiAgYmFja2dyb3VuZC1jb2xvcjogIzY2NjtcbiAgYm9yZGVyLXJhZGl1czogMDtcbiAgZGlzcGxheTogbm9uZTtcbiAgbWluLWhlaWdodDogMzJweDtcbiAgbWFyZ2luLXJpZ2h0OiA3MHB4O1xuICBwYWRkaW5nOiA5cHg7XG4gIHdpZHRoOiAyODBweDtcbiAgcmlnaHQ6IDIlO1xuICBwb3NpdGlvbjogYWJzb2x1dGU7XG59XG5cbi5hLWVudGVyLXZyLW1vZGFsOmFmdGVyIHtcbiAgYm9yZGVyLWJvdHRvbTogMTBweCBzb2xpZCB0cmFuc3BhcmVudDtcbiAgYm9yZGVyLWxlZnQ6IDEwcHggc29saWQgIzY2NjtcbiAgYm9yZGVyLXRvcDogMTBweCBzb2xpZCB0cmFuc3BhcmVudDtcbiAgZGlzcGxheTogaW5saW5lLWJsb2NrO1xuICBjb250ZW50OiAnJztcbiAgcG9zaXRpb246IGFic29sdXRlO1xuICByaWdodDogLTVweDtcbiAgdG9wOiA1cHg7XG4gIHdpZHRoOiAwO1xuICBoZWlnaHQ6IDA7XG59XG5cbi5hLWVudGVyLXZyLW1vZGFsIHAsXG4uYS1lbnRlci12ci1tb2RhbCBhIHtcbiAgZGlzcGxheTogaW5saW5lO1xufVxuXG4uYS1lbnRlci12ci1tb2RhbCBwIHtcbiAgbWFyZ2luOiAwO1xufVxuXG4uYS1lbnRlci12ci1tb2RhbCBwOmFmdGVyIHtcbiAgY29udGVudDogJyAnO1xufVxuXG4uYS1vcmllbnRhdGlvbi1tb2RhbCB7XG4gIGJhY2tncm91bmQ6IHJnYmEoMjQ0LCAyNDQsIDI0NCwgMSkgdXJsKGRhdGE6aW1hZ2Uvc3ZnK3htbCwlM0NzdmclMjB4bWxucyUzRCUyMmh0dHAlM0EvL3d3dy53My5vcmcvMjAwMC9zdmclMjIlMjB4bWxucyUzQXhsaW5rJTNEJTIyaHR0cCUzQS8vd3d3LnczLm9yZy8xOTk5L3hsaW5rJTIyJTIwdmVyc2lvbiUzRCUyMjEuMSUyMiUyMHglM0QlMjIwcHglMjIlMjB5JTNEJTIyMHB4JTIyJTIwdmlld0JveCUzRCUyMjAlMjAwJTIwOTAlMjA5MCUyMiUyMGVuYWJsZS1iYWNrZ3JvdW5kJTNEJTIybmV3JTIwMCUyMDAlMjA5MCUyMDkwJTIyJTIweG1sJTNBc3BhY2UlM0QlMjJwcmVzZXJ2ZSUyMiUzRSUzQ3BvbHlnb24lMjBwb2ludHMlM0QlMjIwJTJDMCUyMDAlMkMwJTIwMCUyQzAlMjAlMjIlM0UlM0MvcG9seWdvbiUzRSUzQ2clM0UlM0NwYXRoJTIwZCUzRCUyMk03MS41NDUlMkM0OC4xNDVoLTMxLjk4VjIwLjc0M2MwLTIuNjI3LTIuMTM4LTQuNzY1LTQuNzY1LTQuNzY1SDE4LjQ1NmMtMi42MjglMkMwLTQuNzY3JTJDMi4xMzgtNC43NjclMkM0Ljc2NXY0Mi43ODklMjAlMjAlMjBjMCUyQzIuNjI4JTJDMi4xMzglMkM0Ljc2NiUyQzQuNzY3JTJDNC43NjZoNS41MzV2MC45NTljMCUyQzIuNjI4JTJDMi4xMzglMkM0Ljc2NSUyQzQuNzY2JTJDNC43NjVoNDIuNzg4YzIuNjI4JTJDMCUyQzQuNzY2LTIuMTM3JTJDNC43NjYtNC43NjVWNTIuOTE0JTIwJTIwJTIwQzc2LjMxMSUyQzUwLjI4NCUyQzc0LjE3MyUyQzQ4LjE0NSUyQzcxLjU0NSUyQzQ4LjE0NXolMjBNMTguNDU1JTJDMTYuOTM1aDE2LjM0NGMyLjElMkMwJTJDMy44MDglMkMxLjcwOCUyQzMuODA4JTJDMy44MDh2MjcuNDAxSDM3LjI1VjIyLjYzNiUyMCUyMCUyMGMwLTAuMjY0LTAuMjE1LTAuNDc4LTAuNDc5LTAuNDc4SDE2LjQ4MmMtMC4yNjQlMkMwLTAuNDc5JTJDMC4yMTQtMC40NzklMkMwLjQ3OHYzNi41ODVjMCUyQzAuMjY0JTJDMC4yMTUlMkMwLjQ3OCUyQzAuNDc5JTJDMC40NzhoNy41MDd2Ny42NDQlMjAlMjAlMjBoLTUuNTM0Yy0yLjEwMSUyQzAtMy44MS0xLjcwOS0zLjgxLTMuODFWMjAuNzQzQzE0LjY0NSUyQzE4LjY0MyUyQzE2LjM1NCUyQzE2LjkzNSUyQzE4LjQ1NSUyQzE2LjkzNXolMjBNMTYuOTYlMkMyMy4xMTZoMTkuMzMxdjI1LjAzMWgtNy41MzUlMjAlMjAlMjBjLTIuNjI4JTJDMC00Ljc2NiUyQzIuMTM5LTQuNzY2JTJDNC43Njh2NS44MjhoLTcuMDNWMjMuMTE2eiUyME03MS41NDUlMkM3My4wNjRIMjguNzU3Yy0yLjEwMSUyQzAtMy44MS0xLjcwOC0zLjgxLTMuODA4VjUyLjkxNCUyMCUyMCUyMGMwLTIuMTAyJTJDMS43MDktMy44MTIlMkMzLjgxLTMuODEyaDQyLjc4OGMyLjElMkMwJTJDMy44MDklMkMxLjcxJTJDMy44MDklMkMzLjgxMnYxNi4zNDNDNzUuMzU0JTJDNzEuMzU2JTJDNzMuNjQ1JTJDNzMuMDY0JTJDNzEuNTQ1JTJDNzMuMDY0eiUyMiUzRSUzQy9wYXRoJTNFJTNDcGF0aCUyMGQlM0QlMjJNMjguOTE5JTJDNTguNDI0Yy0xLjQ2NiUyQzAtMi42NTklMkMxLjE5My0yLjY1OSUyQzIuNjZjMCUyQzEuNDY2JTJDMS4xOTMlMkMyLjY1OCUyQzIuNjU5JTJDMi42NThjMS40NjglMkMwJTJDMi42NjItMS4xOTIlMkMyLjY2Mi0yLjY1OCUyMCUyMCUyMEMzMS41ODElMkM1OS42MTclMkMzMC4zODclMkM1OC40MjQlMkMyOC45MTklMkM1OC40MjR6JTIwTTI4LjkxOSUyQzYyLjc4NmMtMC45MzklMkMwLTEuNzAzLTAuNzY0LTEuNzAzLTEuNzAyYzAtMC45MzklMkMwLjc2NC0xLjcwNCUyQzEuNzAzLTEuNzA0JTIwJTIwJTIwYzAuOTQlMkMwJTJDMS43MDUlMkMwLjc2NSUyQzEuNzA1JTJDMS43MDRDMzAuNjIzJTJDNjIuMDIyJTJDMjkuODU4JTJDNjIuNzg2JTJDMjguOTE5JTJDNjIuNzg2eiUyMiUzRSUzQy9wYXRoJTNFJTNDcGF0aCUyMGQlM0QlMjJNNjkuNjU0JTJDNTAuNDYxSDMzLjA2OWMtMC4yNjQlMkMwLTAuNDc5JTJDMC4yMTUtMC40NzklMkMwLjQ3OXYyMC4yODhjMCUyQzAuMjY0JTJDMC4yMTUlMkMwLjQ3OCUyQzAuNDc5JTJDMC40NzhoMzYuNTg1JTIwJTIwJTIwYzAuMjYzJTJDMCUyQzAuNDc3LTAuMjE0JTJDMC40NzctMC40NzhWNTAuOTM5QzcwLjEzMSUyQzUwLjY3NiUyQzY5LjkxNyUyQzUwLjQ2MSUyQzY5LjY1NCUyQzUwLjQ2MXolMjBNNjkuMTc0JTJDNTEuNDE3VjcwLjc1SDMzLjU0OFY1MS40MTdINjkuMTc0eiUyMiUzRSUzQy9wYXRoJTNFJTNDcGF0aCUyMGQlM0QlMjJNNDUuMjAxJTJDMzAuMjk2YzYuNjUxJTJDMCUyQzEyLjIzMyUyQzUuMzUxJTJDMTIuNTUxJTJDMTEuOTc3bC0zLjAzMy0yLjYzOGMtMC4xOTMtMC4xNjUtMC41MDctMC4xNDItMC42NzUlMkMwLjA0OCUyMCUyMCUyMGMtMC4xNzQlMkMwLjE5OC0wLjE1MyUyQzAuNTAxJTJDMC4wNDUlMkMwLjY3NmwzLjg4MyUyQzMuMzc1YzAuMDklMkMwLjA3NSUyQzAuMTk4JTJDMC4xMTUlMkMwLjMxMiUyQzAuMTE1YzAuMTQxJTJDMCUyQzAuMjczLTAuMDYxJTJDMC4zNjItMC4xNjYlMjAlMjAlMjBsMy4zNzEtMy44NzdjMC4xNzMtMC4yJTJDMC4xNTEtMC41MDItMC4wNDctMC42NzVjLTAuMTk0LTAuMTY2LTAuNTA4LTAuMTQ0LTAuNjc2JTJDMC4wNDhsLTIuNTkyJTJDMi45NzklMjAlMjAlMjBjLTAuMTgtMy40MTctMS42MjktNi42MDUtNC4wOTktOS4wMDFjLTIuNTM4LTIuNDYxLTUuODc3LTMuODE3LTkuNDA0LTMuODE3Yy0wLjI2NCUyQzAtMC40NzklMkMwLjIxNS0wLjQ3OSUyQzAuNDc5JTIwJTIwJTIwQzQ0LjcyJTJDMzAuMDgzJTJDNDQuOTM2JTJDMzAuMjk2JTJDNDUuMjAxJTJDMzAuMjk2eiUyMiUzRSUzQy9wYXRoJTNFJTNDL2clM0UlM0Mvc3ZnJTNFKSBjZW50ZXIgbm8tcmVwZWF0O1xuICBiYWNrZ3JvdW5kLXNpemU6IDUwJSA1MCU7XG4gIGJvdHRvbTogMDtcbiAgZm9udC1zaXplOiAxNHB4O1xuICBmb250LXdlaWdodDogNjAwO1xuICBsZWZ0OiAwO1xuICBsaW5lLWhlaWdodDogMjBweDtcbiAgcmlnaHQ6IDA7XG4gIHBvc2l0aW9uOiBmaXhlZDtcbiAgdG9wOiAwO1xuICB6LWluZGV4OiA5OTk5OTk5O1xufVxuXG4uYS1vcmllbnRhdGlvbi1tb2RhbDphZnRlciB7XG4gIGNvbG9yOiAjNjY2O1xuICBjb250ZW50OiBcIkluc2VydCBwaG9uZSBpbnRvIENhcmRib2FyZCBob2xkZXIuXCI7XG4gIGRpc3BsYXk6IGJsb2NrO1xuICBwb3NpdGlvbjogYWJzb2x1dGU7XG4gIHRleHQtYWxpZ246IGNlbnRlcjtcbiAgdG9wOiA3MCU7XG4gIHRyYW5zZm9ybTogdHJhbnNsYXRlWSgtNzAlKTtcbiAgd2lkdGg6IDEwMCU7XG59XG5cbi5hLW9yaWVudGF0aW9uLW1vZGFsIGJ1dHRvbiB7XG4gIGJhY2tncm91bmQ6IHVybChkYXRhOmltYWdlL3N2Zyt4bWwsJTNDc3ZnJTIweG1sbnMlM0QlMjJodHRwJTNBLy93d3cudzMub3JnLzIwMDAvc3ZnJTIyJTIweG1sbnMlM0F4bGluayUzRCUyMmh0dHAlM0EvL3d3dy53My5vcmcvMTk5OS94bGluayUyMiUyMHZlcnNpb24lM0QlMjIxLjElMjIlMjB4JTNEJTIyMHB4JTIyJTIweSUzRCUyMjBweCUyMiUyMHZpZXdCb3glM0QlMjIwJTIwMCUyMDEwMCUyMDEwMCUyMiUyMGVuYWJsZS1iYWNrZ3JvdW5kJTNEJTIybmV3JTIwMCUyMDAlMjAxMDAlMjAxMDAlMjIlMjB4bWwlM0FzcGFjZSUzRCUyMnByZXNlcnZlJTIyJTNFJTNDcGF0aCUyMGZpbGwlM0QlMjIlMjMwMDAwMDAlMjIlMjBkJTNEJTIyTTU1LjIwOSUyQzUwbDE3LjgwMy0xNy44MDNjMS40MTYtMS40MTYlMkMxLjQxNi0zLjcxMyUyQzAtNS4xMjljLTEuNDE2LTEuNDE3LTMuNzEzLTEuNDE3LTUuMTI5JTJDMEw1MC4wOCUyQzQ0Ljg3MiUyMCUyMEwzMi4yNzglMkMyNy4wNjljLTEuNDE2LTEuNDE3LTMuNzE0LTEuNDE3LTUuMTI5JTJDMGMtMS40MTclMkMxLjQxNi0xLjQxNyUyQzMuNzEzJTJDMCUyQzUuMTI5TDQ0Ljk1MSUyQzUwTDI3LjE0OSUyQzY3LjgwMyUyMCUyMGMtMS40MTclMkMxLjQxNi0xLjQxNyUyQzMuNzEzJTJDMCUyQzUuMTI5YzAuNzA4JTJDMC43MDglMkMxLjYzNiUyQzEuMDYyJTJDMi41NjQlMkMxLjA2MmMwLjkyOCUyQzAlMkMxLjg1Ni0wLjM1NCUyQzIuNTY0LTEuMDYyTDUwLjA4JTJDNTUuMTNsMTcuODAzJTJDMTcuODAyJTIwJTIwYzAuNzA4JTJDMC43MDglMkMxLjYzNyUyQzEuMDYyJTJDMi41NjQlMkMxLjA2MnMxLjg1Ni0wLjM1NCUyQzIuNTY0LTEuMDYyYzEuNDE2LTEuNDE2JTJDMS40MTYtMy43MTMlMkMwLTUuMTI5TDU1LjIwOSUyQzUweiUyMiUzRSUzQy9wYXRoJTNFJTNDL3N2ZyUzRSkgbm8tcmVwZWF0O1xuICBib3JkZXI6IG5vbmU7XG4gIGhlaWdodDogNTBweDtcbiAgdGV4dC1pbmRlbnQ6IC05OTk5cHg7XG4gIHdpZHRoOiA1MHB4O1xufVxuXG4uYS1sb2FkZXItdGl0bGUge1xuICBiYWNrZ3JvdW5kLWNvbG9yOiByZ2JhKDAsIDAsIDAsIDAuNik7XG4gIGZvbnQtZmFtaWx5OiBzYW5zLXNlcmlmLCBtb25vc3BhY2U7XG4gIHRleHQtYWxpZ246IGNlbnRlcjtcbiAgZm9udC1zaXplOiAyMHB4O1xuICBoZWlnaHQ6IDUwcHg7XG4gIGZvbnQtd2VpZ2h0OiAzMDA7XG4gIGxpbmUtaGVpZ2h0OiA1MHB4O1xuICBwb3NpdGlvbjogYWJzb2x1dGU7XG4gIHJpZ2h0OiAwcHg7XG4gIGxlZnQ6IDBweDtcbiAgdG9wOiAwcHg7XG4gIGNvbG9yOiB3aGl0ZTtcbn1cblxuLmEtbW9kYWwge1xuICBwb3NpdGlvbjogYWJzb2x1dGU7XG4gIGJhY2tncm91bmQ6IHJnYmEoMCwgMCwgMCwgMC42MCk7XG4gIGJhY2tncm91bmQtc2l6ZTogNTAlIDUwJTtcbiAgYm90dG9tOiAwO1xuICBmb250LXNpemU6IDE0cHg7XG4gIGZvbnQtd2VpZ2h0OiA2MDA7XG4gIGxlZnQ6IDA7XG4gIGxpbmUtaGVpZ2h0OiAyMHB4O1xuICByaWdodDogMDtcbiAgcG9zaXRpb246IGZpeGVkO1xuICB0b3A6IDA7XG4gIHotaW5kZXg6IDk5OTk5OTk7XG59XG5cbi5hLWRpYWxvZyB7XG4gIHBvc2l0aW9uOiByZWxhdGl2ZTtcbiAgbGVmdDogNTAlO1xuICB0b3A6IDUwJTtcbiAgdHJhbnNmb3JtOiB0cmFuc2xhdGUoLTUwJSwgLTUwJSk7XG4gIHotaW5kZXg6IDE5OTk5NTtcbiAgd2lkdGg6IDMwMHB4O1xuICBoZWlnaHQ6IDIwMHB4O1xuICBiYWNrZ3JvdW5kLXNpemU6IGNvbnRhaW47XG4gIGJhY2tncm91bmQtY29sb3I6IHdoaXRlO1xuICBmb250LWZhbWlseTogc2Fucy1zZXJpZiwgbW9ub3NwYWNlO1xuICBmb250LXNpemU6IDIwcHg7XG4gIGJvcmRlci1yYWRpdXM6IDNweDtcbiAgcGFkZGluZzogNnB4O1xufVxuXG4uYS1kaWFsb2ctdGV4dC1jb250YWluZXIge1xuICB3aWR0aDogMTAwJTtcbiAgaGVpZ2h0OiA3MCU7XG4gIGFsaWduLXNlbGY6IGZsZXgtc3RhcnQ7XG4gIGRpc3BsYXk6IGZsZXg7XG4gIGp1c3RpZnktY29udGVudDogY2VudGVyO1xuICBhbGlnbi1jb250ZW50OiBjZW50ZXI7XG4gIGZsZXgtZGlyZWN0aW9uOiBjb2x1bW47XG59XG5cbi5hLWRpYWxvZy10ZXh0IHtcbiAgZGlzcGxheTogaW5saW5lLWJsb2NrO1xuICBmb250LXdlaWdodDogbm9ybWFsO1xuICBmb250LXNpemU6IDE0cHQ7XG4gIG1hcmdpbjogOHB4O1xufVxuXG4uYS1kaWFsb2ctYnV0dG9ucy1jb250YWluZXIge1xuICBkaXNwbGF5OiBpbmxpbmUtZmxleDtcbiAgYWxpZ24tc2VsZjogZmxleC1lbmQ7XG4gIHdpZHRoOiAxMDAlO1xuICBoZWlnaHQ6IDMwJTtcbn1cblxuLmEtZGlhbG9nLWJ1dHRvbiB7XG4gIGN1cnNvcjogcG9pbnRlcjtcbiAgYWxpZ24tc2VsZjogY2VudGVyO1xuICBvcGFjaXR5OiAwLjk7XG4gIGhlaWdodDogODAlO1xuICB3aWR0aDogNTAlO1xuICBmb250LXNpemU6IDEycHQ7XG4gIG1hcmdpbjogNHB4O1xuICBib3JkZXItcmFkaXVzOiAycHg7XG4gIHRleHQtYWxpZ246Y2VudGVyO1xuICBib3JkZXI6IG5vbmU7XG4gIGRpc3BsYXk6IGlubGluZS1ibG9jaztcbiAgLXdlYmtpdC10cmFuc2l0aW9uOiBhbGwgMC4yNXMgZWFzZS1pbi1vdXQ7XG4gIHRyYW5zaXRpb246IGFsbCAwLjI1cyBlYXNlLWluLW91dDtcbiAgYm94LXNoYWRvdzogMCAxcHggM3B4IHJnYmEoMCwgMCwgMCwgMC4xMCksIDAgMXB4IDJweCByZ2JhKDAsIDAsIDAsIDAuMjApO1xuICB1c2VyLXNlbGVjdDogbm9uZTtcbn1cblxuLmEtZGlhbG9nLXBlcm1pc3Npb24tYnV0dG9uOmhvdmVyIHtcbiAgYm94LXNoYWRvdzogMCA3cHggMTRweCByZ2JhKDAsMCwwLDAuMjApLCAwIDJweCAycHggcmdiYSgwLDAsMCwwLjIwKTtcbn1cblxuLmEtZGlhbG9nLWFsbG93LWJ1dHRvbiB7XG4gIGJhY2tncm91bmQtY29sb3I6ICMwMGNlZmY7XG59XG5cbi5hLWRpYWxvZy1kZW55LWJ1dHRvbiB7XG4gIGJhY2tncm91bmQtY29sb3I6ICNmZjAwNWI7XG59XG5cbi5hLWRpYWxvZy1vay1idXR0b24ge1xuICBiYWNrZ3JvdW5kLWNvbG9yOiAjMDBjZWZmO1xuICB3aWR0aDogMTAwJTtcbn1cblxuLmEtZG9tLW92ZXJsYXk6bm90KC5hLW5vLXN0eWxlKSB7XG4gIG92ZXJmbG93OiBoaWRkZW47XG4gIHBvc2l0aW9uOiBhYnNvbHV0ZTtcbiAgcG9pbnRlci1ldmVudHM6IG5vbmU7XG4gIGJveC1zaXppbmc6IGJvcmRlci1ib3g7XG4gIGJvdHRvbTogMDtcbiAgbGVmdDogMDtcbiAgcmlnaHQ6IDA7XG4gIHRvcDogMDtcbiAgcGFkZGluZzogMWVtO1xufVxuXG4uYS1kb20tb3ZlcmxheTpub3QoLmEtbm8tc3R5bGUpPioge1xuICBwb2ludGVyLWV2ZW50czogYXV0bztcbn1cbiJdLCJzb3VyY2VSb290IjoiIn0= */</style><style>.rs-base {
  background-color: #333;
  color: #fafafa;
  border-radius: 0;
  font: 10px monospace;
  left: 5px;
  line-height: 1em;
  opacity: 0.85;
  overflow: hidden;
  padding: 10px;
  position: fixed;
  top: 5px;
  width: 300px;
  z-index: 10000;
}

.rs-base div.hidden {
  display: none;
}

.rs-base h1 {
  color: #fff;
  cursor: pointer;
  font-size: 1.4em;
  font-weight: 300;
  margin: 0 0 5px;
  padding: 0;
}

.rs-group {
  display: -webkit-box;
  display: -webkit-flex;
  display: flex;
  -webkit-flex-direction: column-reverse;
  flex-direction: column-reverse;
  margin-bottom: 5px;
}

.rs-group:last-child {
  margin-bottom: 0;
}

.rs-counter-base {
  align-items: center;
  display: -webkit-box;
  display: -webkit-flex;
  display: flex;
  height: 10px;
  -webkit-justify-content: space-between;
  justify-content: space-between;
  margin: 2px 0;
}

.rs-counter-base.alarm {
  color: #b70000;
  text-shadow: 0 0 0 #b70000,
               0 0 1px #fff,
               0 0 1px #fff,
               0 0 2px #fff,
               0 0 2px #fff,
               0 0 3px #fff,
               0 0 3px #fff,
               0 0 4px #fff,
               0 0 4px #fff;
}

.rs-counter-id {
  font-weight: 300;
  -webkit-box-ordinal-group: 0;
  -webkit-order: 0;
  order: 0;
  width: 54px;
}

.rs-counter-value {
  font-weight: 300;
  -webkit-box-ordinal-group: 1;
  -webkit-order: 1;
  order: 1;
  text-align: right;
  width: 35px;
}

.rs-canvas {
  -webkit-box-ordinal-group: 2;
  -webkit-order: 2;
  order: 2;
}

@media (min-width: 480px) {
  .rs-base {
    left: 20px;
    top: 20px;
  }
}

/*# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8uL3NyYy9zdHlsZS9yU3RhdHMuY3NzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0VBQ0Usc0JBQXNCO0VBQ3RCLGNBQWM7RUFDZCxnQkFBZ0I7RUFDaEIsb0JBQW9CO0VBQ3BCLFNBQVM7RUFDVCxnQkFBZ0I7RUFDaEIsYUFBYTtFQUNiLGdCQUFnQjtFQUNoQixhQUFhO0VBQ2IsZUFBZTtFQUNmLFFBQVE7RUFDUixZQUFZO0VBQ1osY0FBYztBQUNoQjs7QUFFQTtFQUNFLGFBQWE7QUFDZjs7QUFFQTtFQUNFLFdBQVc7RUFDWCxlQUFlO0VBQ2YsZ0JBQWdCO0VBQ2hCLGdCQUFnQjtFQUNoQixlQUFlO0VBQ2YsVUFBVTtBQUNaOztBQUVBO0VBQ0Usb0JBQW9CO0VBQ3BCLHFCQUFxQjtFQUNyQixhQUFhO0VBQ2Isc0NBQXNDO0VBQ3RDLDhCQUE4QjtFQUM5QixrQkFBa0I7QUFDcEI7O0FBRUE7RUFDRSxnQkFBZ0I7QUFDbEI7O0FBRUE7RUFDRSxtQkFBbUI7RUFDbkIsb0JBQW9CO0VBQ3BCLHFCQUFxQjtFQUNyQixhQUFhO0VBQ2IsWUFBWTtFQUNaLHNDQUFzQztFQUN0Qyw4QkFBOEI7RUFDOUIsYUFBYTtBQUNmOztBQUVBO0VBQ0UsY0FBYztFQUNkOzs7Ozs7OzsyQkFReUI7QUFDM0I7O0FBRUE7RUFDRSxnQkFBZ0I7RUFDaEIsNEJBQTRCO0VBQzVCLGdCQUFnQjtFQUNoQixRQUFRO0VBQ1IsV0FBVztBQUNiOztBQUVBO0VBQ0UsZ0JBQWdCO0VBQ2hCLDRCQUE0QjtFQUM1QixnQkFBZ0I7RUFDaEIsUUFBUTtFQUNSLGlCQUFpQjtFQUNqQixXQUFXO0FBQ2I7O0FBRUE7RUFDRSw0QkFBNEI7RUFDNUIsZ0JBQWdCO0VBQ2hCLFFBQVE7QUFDVjs7QUFFQTtFQUNFO0lBQ0UsVUFBVTtJQUNWLFNBQVM7RUFDWDtBQUNGIiwic291cmNlc0NvbnRlbnQiOlsiLnJzLWJhc2Uge1xuICBiYWNrZ3JvdW5kLWNvbG9yOiAjMzMzO1xuICBjb2xvcjogI2ZhZmFmYTtcbiAgYm9yZGVyLXJhZGl1czogMDtcbiAgZm9udDogMTBweCBtb25vc3BhY2U7XG4gIGxlZnQ6IDVweDtcbiAgbGluZS1oZWlnaHQ6IDFlbTtcbiAgb3BhY2l0eTogMC44NTtcbiAgb3ZlcmZsb3c6IGhpZGRlbjtcbiAgcGFkZGluZzogMTBweDtcbiAgcG9zaXRpb246IGZpeGVkO1xuICB0b3A6IDVweDtcbiAgd2lkdGg6IDMwMHB4O1xuICB6LWluZGV4OiAxMDAwMDtcbn1cblxuLnJzLWJhc2UgZGl2LmhpZGRlbiB7XG4gIGRpc3BsYXk6IG5vbmU7XG59XG5cbi5ycy1iYXNlIGgxIHtcbiAgY29sb3I6ICNmZmY7XG4gIGN1cnNvcjogcG9pbnRlcjtcbiAgZm9udC1zaXplOiAxLjRlbTtcbiAgZm9udC13ZWlnaHQ6IDMwMDtcbiAgbWFyZ2luOiAwIDAgNXB4O1xuICBwYWRkaW5nOiAwO1xufVxuXG4ucnMtZ3JvdXAge1xuICBkaXNwbGF5OiAtd2Via2l0LWJveDtcbiAgZGlzcGxheTogLXdlYmtpdC1mbGV4O1xuICBkaXNwbGF5OiBmbGV4O1xuICAtd2Via2l0LWZsZXgtZGlyZWN0aW9uOiBjb2x1bW4tcmV2ZXJzZTtcbiAgZmxleC1kaXJlY3Rpb246IGNvbHVtbi1yZXZlcnNlO1xuICBtYXJnaW4tYm90dG9tOiA1cHg7XG59XG5cbi5ycy1ncm91cDpsYXN0LWNoaWxkIHtcbiAgbWFyZ2luLWJvdHRvbTogMDtcbn1cblxuLnJzLWNvdW50ZXItYmFzZSB7XG4gIGFsaWduLWl0ZW1zOiBjZW50ZXI7XG4gIGRpc3BsYXk6IC13ZWJraXQtYm94O1xuICBkaXNwbGF5OiAtd2Via2l0LWZsZXg7XG4gIGRpc3BsYXk6IGZsZXg7XG4gIGhlaWdodDogMTBweDtcbiAgLXdlYmtpdC1qdXN0aWZ5LWNvbnRlbnQ6IHNwYWNlLWJldHdlZW47XG4gIGp1c3RpZnktY29udGVudDogc3BhY2UtYmV0d2VlbjtcbiAgbWFyZ2luOiAycHggMDtcbn1cblxuLnJzLWNvdW50ZXItYmFzZS5hbGFybSB7XG4gIGNvbG9yOiAjYjcwMDAwO1xuICB0ZXh0LXNoYWRvdzogMCAwIDAgI2I3MDAwMCxcbiAgICAgICAgICAgICAgIDAgMCAxcHggI2ZmZixcbiAgICAgICAgICAgICAgIDAgMCAxcHggI2ZmZixcbiAgICAgICAgICAgICAgIDAgMCAycHggI2ZmZixcbiAgICAgICAgICAgICAgIDAgMCAycHggI2ZmZixcbiAgICAgICAgICAgICAgIDAgMCAzcHggI2ZmZixcbiAgICAgICAgICAgICAgIDAgMCAzcHggI2ZmZixcbiAgICAgICAgICAgICAgIDAgMCA0cHggI2ZmZixcbiAgICAgICAgICAgICAgIDAgMCA0cHggI2ZmZjtcbn1cblxuLnJzLWNvdW50ZXItaWQge1xuICBmb250LXdlaWdodDogMzAwO1xuICAtd2Via2l0LWJveC1vcmRpbmFsLWdyb3VwOiAwO1xuICAtd2Via2l0LW9yZGVyOiAwO1xuICBvcmRlcjogMDtcbiAgd2lkdGg6IDU0cHg7XG59XG5cbi5ycy1jb3VudGVyLXZhbHVlIHtcbiAgZm9udC13ZWlnaHQ6IDMwMDtcbiAgLXdlYmtpdC1ib3gtb3JkaW5hbC1ncm91cDogMTtcbiAgLXdlYmtpdC1vcmRlcjogMTtcbiAgb3JkZXI6IDE7XG4gIHRleHQtYWxpZ246IHJpZ2h0O1xuICB3aWR0aDogMzVweDtcbn1cblxuLnJzLWNhbnZhcyB7XG4gIC13ZWJraXQtYm94LW9yZGluYWwtZ3JvdXA6IDI7XG4gIC13ZWJraXQtb3JkZXI6IDI7XG4gIG9yZGVyOiAyO1xufVxuXG5AbWVkaWEgKG1pbi13aWR0aDogNDgwcHgpIHtcbiAgLnJzLWJhc2Uge1xuICAgIGxlZnQ6IDIwcHg7XG4gICAgdG9wOiAyMHB4O1xuICB9XG59XG4iXSwic291cmNlUm9vdCI6IiJ9 */</style>
    <script src="https://cdn.jsdelivr.net/npm/mind-ar@1.2.2/dist/mindar-image-aframe.prod.js"></script>

    <script>
      const showInfo = () => {
        let y = 0;
        const profileButton = document.querySelector("#profile-button");
        const webButton = document.querySelector("#web-button");
        const emailButton = document.querySelector("#email-button");
        const locationButton = document.querySelector("#location-button");
        const text = document.querySelector("#text");

        profileButton.setAttribute("visible", true);
        setTimeout(() => {
          webButton.setAttribute("visible", true);
        }, 300);
        setTimeout(() => {
          emailButton.setAttribute("visible", true);
        }, 600);
        setTimeout(() => {
          locationButton.setAttribute("visible", true);
        }, 900);

        let currentTab = '';
        webButton.addEventListener('click', function (evt) {
          text.setAttribute("value", "https://softmind.tech");
          currentTab = 'web';
        });
        emailButton.addEventListener('click', function (evt) {
          text.setAttribute("value", "hello@softmind.tech");
          currentTab = 'email';
        });
        profileButton.addEventListener('click', function (evt) {
          text.setAttribute("value", "AR, VR solutions and consultation");
          currentTab = 'profile';
        });
        locationButton.addEventListener('click', function (evt) {
          console.log("loc");
          text.setAttribute("value", "Vancouver, Canada | Hong Kong");
          currentTab = 'location';
        });

        text.addEventListener('click', function (evt) {
          if (currentTab === 'web') {
            window.location.href="https://softmind.tech";
          }
        });
      }

      const showPortfolio = (done) => {
        const portfolio = document.querySelector("#portfolio-panel");
        const portfolioLeftButton = document.querySelector("#portfolio-left-button");
        const portfolioRightButton = document.querySelector("#portfolio-right-button");
        const paintandquestPreviewButton = document.querySelector("#paintandquest-preview-button");

        let y = 0;
        let currentItem = 0;

        portfolio.setAttribute("visible", true);

        const showPortfolioItem = (item) => {
          for (let i = 0; i <= 2; i++) {
            document.querySelector("#portfolio-item" + i).setAttribute("visible", i === item);
          }
        }
        const id = setInterval(() => {
          y += 0.008;
          if (y >= 0.6) {
            clearInterval(id);
            portfolioLeftButton.setAttribute("visible", true);
            portfolioRightButton.setAttribute("visible", true);
            portfolioLeftButton.addEventListener('click', () => {
              currentItem = (currentItem + 1) % 3;
              showPortfolioItem(currentItem);
            });
            portfolioRightButton.addEventListener('click', () => {
              currentItem = (currentItem - 1 + 3) % 3;
              showPortfolioItem(currentItem);
            });

            paintandquestPreviewButton.addEventListener('click', () => {
              paintandquestPreviewButton.setAttribute("visible", false);
              const testVideo = document.createElement( "video" );
              const canplayWebm = testVideo.canPlayType( 'video/webm; codecs="vp8, vorbis"' );
              if (canplayWebm == "") {
                document.querySelector("#paintandquest-video-link").setAttribute("src", "#paintandquest-video-mp4");
                document.querySelector("#paintandquest-video-mp4").play();
              } else {
                document.querySelector("#paintandquest-video-link").setAttribute("src", "#paintandquest-video-webm");
                document.querySelector("#paintandquest-video-webm").play();
              }
            });

            setTimeout(() => {
              done();
            }, 500);
          }
          portfolio.setAttribute("position", "0 " + y + " -0.01");
        }, 10);
      }

      const showAvatar = (onDone) => {
        const avatar = document.querySelector("#avatar");
        let z = -0.3;
        const id = setInterval(() => {
          z += 0.008;
          if (z >= 0.3) {
            clearInterval(id);
            onDone();
          }
          avatar.setAttribute("position", "0 -0.25 " + z);
        }, 10);
      }

      AFRAME.registerComponent('mytarget', {
        init: function () {
          this.el.addEventListener('targetFound', event => {
            console.log("target found");
            showAvatar(() => {
              setTimeout(() => {
                showPortfolio(() => {
                  setTimeout(() => {
                    showInfo();
                  }, 300);
                });
              }, 300);
            });
          });
          this.el.addEventListener('targetLost', event => {
            console.log("target found");
          });
          //this.el.emit('targetFound');
        }
      });
    </script>

    <style>
      body {
        margin: 0;
      }
      .example-container {
        overflow: hidden;
        position: absolute;
        width: 100%;
        height: 100%;
      }
     
      #example-scanning-overlay {
	display: flex;
	align-items: center;
	justify-content: center;
	position: absolute;
	left: 0;
	right: 0;
	top: 0;
	bottom: 0;
	background: transparent;
	z-index: 2;
      }
      @media (min-aspect-ratio: 1/1) {
	#example-scanning-overlay .inner {
	  width: 50vh;
	  height: 50vh;
	}
      }
      @media (max-aspect-ratio: 1/1) {
	#example-scanning-overlay .inner {
	  width: 80vw;
	  height: 80vw;
	}
      }

      #example-scanning-overlay .inner {
	display: flex;
	align-items: center;
	justify-content: center;
	position: relative;

	background:
	  linear-gradient(to right, white 10px, transparent 10px) 0 0,
	  linear-gradient(to right, white 10px, transparent 10px) 0 100%,
	  linear-gradient(to left, white 10px, transparent 10px) 100% 0,
	  linear-gradient(to left, white 10px, transparent 10px) 100% 100%,
	  linear-gradient(to bottom, white 10px, transparent 10px) 0 0,
	  linear-gradient(to bottom, white 10px, transparent 10px) 100% 0,
	  linear-gradient(to top, white 10px, transparent 10px) 0 100%,
	  linear-gradient(to top, white 10px, transparent 10px) 100% 100%;
	background-repeat: no-repeat;
	background-size: 40px 40px;
      }

      #example-scanning-overlay.hidden {
	display: none;
      }

      #example-scanning-overlay img {
	opacity: 0.6;
	width: 90%;
	align-self: center;
      }

      #example-scanning-overlay .inner .scanline {
	position: absolute;
	width: 100%;
	height: 10px;
	background: white;
	animation: move 2s linear infinite;
      }
      @keyframes move {
	0%, 100% { top: 0% }
	50% { top: calc(100% - 10px) }
      }
    </style>
  <style>.mindar-ui-overlay{display:flex;align-items:center;justify-content:center;position:absolute;left:0;right:0;top:0;bottom:0;background:transparent;z-index:2}.mindar-ui-overlay.hidden{display:none}.mindar-ui-loading .loader{border:16px solid #222;border-top:16px solid white;opacity:.8;border-radius:50%;width:120px;height:120px;animation:spin 2s linear infinite}@keyframes spin{0%{transform:rotate(0)}to{transform:rotate(360deg)}}.mindar-ui-compatibility .content{background:black;color:#fff;opacity:.8;text-align:center;margin:20px;padding:20px;min-height:50vh}@media (min-aspect-ratio: 1/1){.mindar-ui-scanning .scanning{width:50vh;height:50vh}}@media (max-aspect-ratio: 1/1){.mindar-ui-scanning .scanning{width:80vw;height:80vw}}.mindar-ui-scanning .scanning .inner{position:relative;width:100%;height:100%;opacity:.8;background:linear-gradient(to right,white 10px,transparent 10px) 0 0,linear-gradient(to right,white 10px,transparent 10px) 0 100%,linear-gradient(to left,white 10px,transparent 10px) 100% 0,linear-gradient(to left,white 10px,transparent 10px) 100% 100%,linear-gradient(to bottom,white 10px,transparent 10px) 0 0,linear-gradient(to bottom,white 10px,transparent 10px) 100% 0,linear-gradient(to top,white 10px,transparent 10px) 0 100%,linear-gradient(to top,white 10px,transparent 10px) 100% 100%;background-repeat:no-repeat;background-size:40px 40px}.mindar-ui-scanning .scanning .inner .scanline{position:absolute;width:100%;height:10px;background:white;animation:move 2s linear infinite}@keyframes move{0%,to{top:0%}50%{top:calc(100% - 10px)}}</style></head>
  <body cz-shortcut-listen="true">
    <div class="example-container">
      <div id="example-scanning-overlay" class="">
	<div class="inner">
	  <img src="./assets/card-example/card.png">
	  <div class="scanline"></div>
	</div>
      </div>

      <a-scene mindar-image="imageTargetSrc: https://cdn.jsdelivr.net/gh/hiukim/mind-ar-js@1.2.2/examples/image-tracking/assets/card-example/card.mind; showStats: false; uiScanning: #example-scanning-overlay;" embedded="" color-space="sRGB" renderer="colorManagement: true, physicallyCorrectLights" vr-mode-ui="enabled: false" device-orientation-permission-ui="enabled: false" inspector="" keyboard-shortcuts="" screenshot="">
        <a-assets>
          <img id="card" src="./assets/card-example/card.png">
          <img id="icon-web" src="./assets/card-example/icons/web.png">
          <img id="icon-location" src="./assets/card-example/icons/location.png">
          <img id="icon-profile" src="./assets/card-example/icons/profile.png">
          <img id="icon-phone" src="./assets/card-example/icons/phone.png">
          <img id="icon-email" src="./assets/card-example/icons/email.png">
          <img id="icon-play" src="./assets/card-example/icons/play.png">
          <img id="icon-left" src="./assets/card-example/icons/left.png">
          <img id="icon-right" src="./assets/card-example/icons/right.png">
          <img id="paintandquest-preview" src="./assets/card-example/portfolio/paintandquest-preview.png">
          <video id="paintandquest-video-mp4" autoplay="false" loop="true" src="./assets/card-example/portfolio/paintandquest.mp4" playsinline="" webkit-playsinline=""></video>
          <video id="paintandquest-video-webm" autoplay="false" loop="true" src="./assets/card-example/portfolio/paintandquest.webm" playsinline="" webkit-playsinline=""></video>
          <img id="coffeemachine-preview" src="./assets/card-example/portfolio/coffeemachine-preview.png">
          <img id="peak-preview" src="./assets/card-example/portfolio/peak-preview.png">
          
          <a-asset-item id="avatarModel" src="https://cdn.jsdelivr.net/gh/hiukim/mind-ar-js@1.2.2/examples/image-tracking/assets/card-example/softmind/scene.gltf"></a-asset-item>
        </a-assets>

        <a-camera position="0 0 0" look-controls="enabled: false" cursor="fuse: false; rayOrigin: mouse;" raycaster="far: 10000; objects: .clickable" camera="" rotation="" wasd-controls="">
        </a-camera>

        <a-entity id="mytarget" mytarget="" mindar-image-target="targetIndex: 0">
          <a-plane src="#card" position="0 0 0" height="0.552" width="1" rotation="0 0 0" material="" geometry=""></a-plane>

          <a-entity visible="false" id="portfolio-panel" position="0 0 -0.01">
            <a-text value="Portfolio" color="black" align="center" width="2" position="0 0.4 0" text=""></a-text>
            <a-entity id="portfolio-item0">
              <a-video id="paintandquest-video-link" webkit-playsinline="" playsinline="" width="1" height="0.552" position="0 0 0" material="" geometry=""></a-video>
              <a-image id="paintandquest-preview-button" class="clickable" src="#paintandquest-preview" alpha-test="0.5" position="0 0 0" height="0.552" width="1" material="" geometry="">
              </a-image>
            </a-entity>
            <a-entity id="portfolio-item1" visible="false">
              <a-image class="clickable" src="#coffeemachine-preview" alpha-test="0.5" position="0 0 0" height="0.552" width="1" material="" geometry="">
              </a-image>
            </a-entity>
            <a-entity id="portfolio-item2" visible="false">
              <a-image class="clickable" src="#peak-preview" alpha-test="0.5" position="0 0 0" height="0.552" width="1" material="" geometry="">
              </a-image>
            </a-entity>

            <a-image visible="false" id="portfolio-left-button" class="clickable" src="#icon-left" position="-0.7 0 0" height="0.15" width="0.15" material="" geometry=""></a-image>
            <a-image visible="false" id="portfolio-right-button" class="clickable" src="#icon-right" position="0.7 0 0" height="0.15" width="0.15" material="" geometry=""></a-image>
          </a-entity>

          <a-image visible="false" id="profile-button" class="clickable" src="#icon-profile" position="-0.42 -0.5 0" height="0.15" width="0.15" animation="property: scale; to: 1.2 1.2 1.2; dur: 1000; easing: easeInOutQuad; loop: true; dir: alternate" material="" geometry=""></a-image>

          <a-image visible="false" id="web-button" class="clickable" src="#icon-web" alpha-test="0.5" position="-0.14 -0.5 0" height="0.15" width="0.15" animation="property: scale; to: 1.2 1.2 1.2; dur: 1000; easing: easeInOutQuad; loop: true; dir: alternate" material="" geometry=""></a-image>

          <a-image visible="false" id="email-button" class="clickable" src="#icon-email" position="0.14 -0.5 0" height="0.15" width="0.15" animation="property: scale; to: 1.2 1.2 1.2; dur: 1000; easing: easeInOutQuad; loop: true; dir: alternate" material="" geometry=""></a-image>

          <a-image visible="false" id="location-button" class="clickable" src="#icon-location" position="0.42 -0.5 0" height="0.15" width="0.15" animation="property: scale; to: 1.2 1.2 1.2; dur: 1000; easing: easeInOutQuad; loop: true; dir: alternate" material="" geometry=""></a-image>

          <a-gltf-model id="avatar" rotation="0 0 0" position="0 -0.25 0" scale="0.004 0.004 0.004" src="#avatarModel" gltf-model=""></a-gltf-model>

          <a-text id="text" class="clickable" value="" color="black" align="center" width="2" position="0 -1 0" geometry="primitive:plane; height: 0.1; width: 2;" material="opacity: 0.5" text=""></a-text>
        </a-entity>
      <canvas class="a-canvas" data-aframe-canvas="true" data-engine="three.js r147" width="1920" height="955"></canvas><div class="a-loader-title" style="display: none;"></div><a-entity light="" data-aframe-default-light="" aframe-injected=""></a-entity><a-entity light="" position="" data-aframe-default-light="" aframe-injected=""></a-entity></a-scene>
    <video autoplay="" muted="" playsinline="" style="position: absolute; top: -242.5px; left: 0px; z-index: -2; width: 1920px; height: 1440px;" width="640" height="480"></video></div>
  

<div class="mindar-ui-overlay mindar-ui-loading hidden">
  <div class="loader">
</div></div><div class="mindar-ui-overlay mindar-ui-compatibility hidden">
  <div class="content">
    <h1>Failed to launch :(</h1>
    <p>
      Looks like your device/browser is not compatible.
    </p>

    <br>
    <br>
    <p>
      Please try the following recommended browsers:
    </p>
    <p>
      For Android device - Chrome
    </p>
    <p>
      For iOS device - Safari
    </p>
  </div>
</div></body></html>