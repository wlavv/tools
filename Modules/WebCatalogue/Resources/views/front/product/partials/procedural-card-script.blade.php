<script type="module">
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

document.querySelectorAll('[data-procedural-card]').forEach((mount) => {
    const frontUrl = mount.dataset.frontUrl;
    if (!frontUrl) return;

    const backUrl = mount.dataset.backUrl || frontUrl;
    const finish = mount.dataset.finish || 'normal';
    const ratio = Number.parseFloat(mount.dataset.ratio || '1.395') || 1.395;
    const thickness = Number.parseFloat(mount.dataset.thickness || '0.012') || 0.012;
    const environment = parseEnvironmentPayload(mount.dataset.environment);
    const width = 2.5;
    const height = width * ratio;
    const radius = width * 0.055;
    const faceOffset = Math.max(thickness / 2 + 0.01, 0.018);
    const scene = new THREE.Scene();
    scene.background = environment?.background_color ? new THREE.Color(environment.background_color) : null;

    const camera = new THREE.PerspectiveCamera(35, 1, 0.1, 100);
    camera.position.set(0, 0.25, 7.2);
    if (environment?.camera) {
        camera.fov = Number.parseFloat(environment.camera.fov ?? camera.fov) || camera.fov;
        camera.near = Number.parseFloat(environment.camera.near ?? camera.near) || camera.near;
        camera.far = Number.parseFloat(environment.camera.far ?? camera.far) || camera.far;
        applyVector3(camera.position, environment.camera.position, [0, 0.25, 7.2]);
        if (camera.position.length() < 4.2) {
            camera.position.set(0.55, 0.32, 6.4);
        }
        camera.updateProjectionMatrix();
    }

    const renderer = new THREE.WebGLRenderer({antialias: true, alpha: true});
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.xr.enabled = true;
    mount.appendChild(renderer.domElement);
    renderer.domElement.classList.add('wc-procedural-canvas');

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.enableRotate = true;
    controls.enableZoom = true;
    controls.enablePan = true;
    controls.autoRotate = true;
    controls.autoRotateSpeed = 0.7;
    controls.screenSpacePanning = true;
    controls.minDistance = 4.2;
    controls.maxDistance = 9;
    controls.target.set(0, 0, 0);
    if (environment?.camera) {
        controls.minDistance = Math.min(4.2, Number.parseFloat(environment.camera.minDistance ?? controls.minDistance) || controls.minDistance);
        controls.maxDistance = Math.max(9, Number.parseFloat(environment.camera.maxDistance ?? controls.maxDistance) || controls.maxDistance);
        applyVector3(controls.target, environment.camera.target, [0, 0, 0]);
    }
    controls.addEventListener('start', () => {
        controls.autoRotate = false;
        mount.classList.add('is-interacting');
    });
    controls.addEventListener('end', () => mount.classList.remove('is-interacting'));

    const loader = new THREE.TextureLoader();
    loader.setCrossOrigin('anonymous');
    const alphaMap = roundedAlphaTexture(1024, 1428, 82);

    const frontMaterial = new THREE.MeshPhysicalMaterial({
        color: 0xffffff,
        alphaMap,
        transparent: true,
        roughness: finish === 'foil' ? 0.28 : 0.48,
        metalness: finish === 'foil' ? 0.07 : 0.02,
        clearcoat: finish === 'foil' ? 0.62 : 0.28,
        clearcoatRoughness: finish === 'foil' ? 0.18 : 0.32,
        side: THREE.FrontSide,
        depthWrite: false,
        polygonOffset: true,
        polygonOffsetFactor: -2,
        polygonOffsetUnits: -2
    });
    const backMaterial = new THREE.MeshPhysicalMaterial({
        color: 0xffffff,
        alphaMap,
        transparent: true,
        roughness: 0.42,
        metalness: 0.02,
        clearcoat: 0.22,
        clearcoatRoughness: 0.3,
        side: THREE.FrontSide,
        depthWrite: false,
        polygonOffset: true,
        polygonOffsetFactor: -2,
        polygonOffsetUnits: -2
    });
    const edgeMaterial = new THREE.MeshStandardMaterial({
        color: 0x111827,
        roughness: 0.55,
        metalness: 0.04
    });

    loadMaterialTexture(loader, frontUrl, frontMaterial);
    loadMaterialTexture(loader, backUrl, backMaterial);

    const cardGroup = new THREE.Group();
    const cardDisplayPosition = new THREE.Vector3(0, 0, 0);
    const cardArPosition = new THREE.Vector3(0, -0.05, -1.15);
    const cardVrPosition = new THREE.Vector3(0, 1.45, -2.1);
    const faceGeometry = new THREE.PlaneGeometry(width, height, 1, 1);
    const front = new THREE.Mesh(faceGeometry, frontMaterial);
    front.position.z = faceOffset;
    front.renderOrder = 2;
    cardGroup.add(front);

    const back = new THREE.Mesh(faceGeometry.clone(), backMaterial);
    back.rotation.y = Math.PI;
    back.position.z = -faceOffset;
    back.renderOrder = 2;
    cardGroup.add(back);

    const edge = new THREE.Mesh(roundedCardGeometry(width, height, radius, thickness), edgeMaterial);
    edge.renderOrder = 0;
    edge.scale.set(0.996, 0.996, 1);
    cardGroup.add(edge);
    scene.add(cardGroup);

    if (finish === 'foil') {
        const foilTexture = new THREE.CanvasTexture(createFoilTexture());
        foilTexture.colorSpace = THREE.SRGBColorSpace;
        const foil = new THREE.Mesh(faceGeometry.clone(), new THREE.MeshBasicMaterial({
            map: foilTexture,
            transparent: true,
            opacity: 0.09,
            blending: THREE.AdditiveBlending,
            depthWrite: false
        }));
        foil.position.z = faceOffset + 0.006;
        foil.renderOrder = 3;
        cardGroup.add(foil);
    }

    applyEnvironmentSkybox(scene, renderer, environment);

    const lighting = environment?.lighting || {};
    const hemisphere = lighting.hemisphere || {};
    scene.add(new THREE.HemisphereLight(
        colorFromConfig(hemisphere.skyColor, 0xffffff),
        colorFromConfig(hemisphere.groundColor, 0x0f172a),
        Number.parseFloat(hemisphere.intensity ?? 1.6) || 1.6
    ));
    const key = new THREE.DirectionalLight(0xffffff, 2.2);
    const keyConfig = lighting.key || {};
    key.color.set(colorFromConfig(keyConfig.color, 0xffffff));
    key.intensity = Number.parseFloat(keyConfig.intensity ?? 2.2) || 2.2;
    applyVector3(key.position, keyConfig.position, [3, 4, 6]);
    scene.add(key);
    const rim = new THREE.DirectionalLight(0x9bdcff, 1.4);
    const rimConfig = lighting.rim || {};
    rim.color.set(colorFromConfig(rimConfig.color, 0x9bdcff));
    rim.intensity = Number.parseFloat(rimConfig.intensity ?? 1.4) || 1.4;
    applyVector3(rim.position, rimConfig.position, [-4, 2, -3]);
    scene.add(rim);
    const environmentGroup = createMirrodinEnvironment(width, height);
    environmentGroup.visible = !environment?.skybox_url;
    scene.add(environmentGroup);
    const ambientAudio = createEnvironmentAudio(mount, environment);

    const resize = () => {
        const rect = mount.getBoundingClientRect();
        const w = Math.max(1, rect.width);
        const h = Math.max(1, rect.height);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h, false);
    };

    const xrButtons = document.createElement('div');
    xrButtons.className = 'wc-procedural-xr-actions';
    xrButtons.hidden = true;
    mount.appendChild(xrButtons);

    const arButton = document.createElement('button');
    arButton.type = 'button';
    arButton.className = 'wc-procedural-ar-btn';
    arButton.dataset.xrMode = 'immersive-ar';
    arButton.innerHTML = '<i class="fa-solid fa-vr-cardboard"></i> View in AR';
    xrButtons.appendChild(arButton);

    const vrButton = document.createElement('button');
    vrButton.type = 'button';
    vrButton.className = 'wc-procedural-ar-btn wc-procedural-vr-btn';
    vrButton.dataset.xrMode = 'immersive-vr';
    vrButton.innerHTML = '<i class="fa-solid fa-headset"></i> Open VR';
    xrButtons.appendChild(vrButton);

    const arNote = document.createElement('div');
    arNote.className = 'wc-procedural-ar-note';
    arNote.textContent = 'VR/AR available on compatible WebXR browsers.';
    arNote.hidden = true;
    mount.appendChild(arNote);

    const controlsPanel = document.createElement('div');
    controlsPanel.className = 'wc-procedural-controls';
    controlsPanel.innerHTML = `
        <button type="button" data-card-control="left" title="Rotate left"><i class="fa-solid fa-rotate-left"></i></button>
        <button type="button" data-card-control="right" title="Rotate right"><i class="fa-solid fa-rotate-right"></i></button>
        <button type="button" data-card-control="tilt-up" title="Tilt up"><i class="fa-solid fa-arrow-up"></i></button>
        <button type="button" data-card-control="tilt-down" title="Tilt down"><i class="fa-solid fa-arrow-down"></i></button>
        <button type="button" data-card-control="flip" title="Flip"><i class="fa-solid fa-repeat"></i></button>
        <button type="button" data-card-control="reset" title="Reset"><i class="fa-solid fa-crosshairs"></i></button>
    `;
    mount.appendChild(controlsPanel);

    const detailsToggle = document.createElement('button');
    detailsToggle.type = 'button';
    detailsToggle.className = 'wc-procedural-details-toggle';
    detailsToggle.innerHTML = '<i class="fa-solid fa-circle-info"></i> Details';
    mount.appendChild(detailsToggle);

    const detailsPanel = document.createElement('div');
    detailsPanel.className = 'wc-procedural-details';
    detailsPanel.hidden = true;
    detailsPanel.innerHTML = `
        <h3>${escapeHtml(mount.dataset.cardName || 'Card')}</h3>
        <div>
            ${mount.dataset.cardReference ? `<span>${escapeHtml(mount.dataset.cardReference)}</span>` : ''}
            ${mount.dataset.cardCategory ? `<span>${escapeHtml(mount.dataset.cardCategory)}</span>` : ''}
            <span>${escapeHtml(finish)}</span>
        </div>
        ${mount.dataset.cardDescription ? `<p>${escapeHtml(mount.dataset.cardDescription).slice(0, 520)}</p>` : ''}
    `;
    mount.appendChild(detailsPanel);

    const rotateStep = Math.PI / 10;
    const tiltStep = Math.PI / 16;
    controlsPanel.addEventListener('click', (event) => {
        const button = event.target.closest('[data-card-control]');
        if (!button) return;
        const action = button.dataset.cardControl;
        controls.autoRotate = false;
        if (action === 'left') cardGroup.rotation.y -= rotateStep;
        if (action === 'right') cardGroup.rotation.y += rotateStep;
        if (action === 'tilt-up') cardGroup.rotation.x -= tiltStep;
        if (action === 'tilt-down') cardGroup.rotation.x += tiltStep;
        if (action === 'flip') cardGroup.rotation.y += Math.PI;
        if (action === 'reset') {
            cardGroup.rotation.set(0, 0, 0);
            controls.reset();
            controls.autoRotate = false;
        }
    });

    detailsToggle.addEventListener('click', () => {
        detailsPanel.hidden = !detailsPanel.hidden;
    });

    let xrSession = null;
    let xrMode = null;
    const xrControllers = [];
    const xrGrab = {active: false, controller: null, originalParent: null};
    if ('xr' in navigator) {
        Promise.all([
            navigator.xr.isSessionSupported('immersive-ar').catch(() => false),
            navigator.xr.isSessionSupported('immersive-vr').catch(() => false)
        ]).then(([arSupported, vrSupported]) => {
            if (arSupported || vrSupported) {
                xrButtons.hidden = false;
                arButton.hidden = !arSupported;
                vrButton.hidden = !vrSupported;
                arNote.hidden = true;
            } else {
                xrButtons.hidden = true;
                arNote.textContent = 'VR/AR is not available on this browser.';
                arNote.hidden = false;
            }
        }).catch(() => {
            xrButtons.hidden = true;
            arNote.hidden = false;
        });
    } else {
        arNote.hidden = false;
    }

    xrButtons.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-xr-mode]');
        if (!button) return;
        ambientAudio?.unlock();
        if (xrSession) {
            await xrSession.end();
            return;
        }

        try {
            const requestedMode = button.dataset.xrMode || 'immersive-vr';
            const started = await requestProceduralXrSession(requestedMode);
            xrMode = started.mode;
            xrSession = started.session;
            renderer.xr.setReferenceSpaceType(xrMode === 'immersive-vr' ? 'local-floor' : 'local');
            await renderer.xr.setSession(xrSession);
            ensureXrControllers();
            mount.classList.add('is-xr-active');
            controls.enabled = false;
            controls.autoRotate = false;
            cardGroup.position.copy(xrMode === 'immersive-ar' ? cardArPosition : cardVrPosition);
            cardGroup.rotation.set(0, 0, 0);
            cardGroup.scale.setScalar(xrMode === 'immersive-ar' ? 0.22 : 0.28);
            environmentGroup.visible = xrMode === 'immersive-vr' && !environment?.skybox_url;
            if (xrMode === 'immersive-ar') {
                scene.background = null;
                renderer.setClearColor(0x000000, 0);
            } else {
                applyEnvironmentSkybox(scene, renderer, environment);
            }
            xrButtons.classList.add('is-exit');
            arButton.hidden = true;
            vrButton.hidden = false;
            vrButton.innerHTML = '<i class="fa-solid fa-xmark"></i> Exit';
            xrSession.addEventListener('end', () => {
                releaseXrGrab();
                xrSession = null;
                xrMode = null;
                mount.classList.remove('is-xr-active');
                controls.enabled = true;
                controls.autoRotate = false;
                cardGroup.position.copy(cardDisplayPosition);
                cardGroup.rotation.set(0, 0, 0);
                cardGroup.scale.setScalar(1);
                environmentGroup.visible = !environment?.skybox_url;
                applyEnvironmentSkybox(scene, renderer, environment);
                xrButtons.classList.remove('is-exit');
                arButton.innerHTML = '<i class="fa-solid fa-vr-cardboard"></i> View in AR';
                vrButton.innerHTML = '<i class="fa-solid fa-headset"></i> Open VR';
                navigator.xr?.isSessionSupported('immersive-ar').then((supported) => arButton.hidden = !supported).catch(() => arButton.hidden = true);
                navigator.xr?.isSessionSupported('immersive-vr').then((supported) => vrButton.hidden = !supported).catch(() => vrButton.hidden = true);
            });
        } catch (error) {
            console.warn('[WebCatalogue] Procedural XR failed', error);
            arNote.textContent = `VR/AR could not start on this device/browser${error?.message ? ': ' + error.message : '.'}`;
            arNote.hidden = false;
        }
    });

    async function requestProceduralXrSession(preferredMode){
        if (!navigator.xr) {
            throw new Error('WebXR unavailable');
        }

        const quest = /OculusBrowser/i.test(navigator.userAgent);
        const attempts = [];

        if (preferredMode === 'immersive-ar') {
            if (quest) {
                attempts.push(
                    {mode: 'immersive-ar', init: {requiredFeatures: ['local'], optionalFeatures: ['local-floor']}},
                    {mode: 'immersive-ar', init: {requiredFeatures: ['local']}},
                    {mode: 'immersive-ar', init: {}}
                );
            } else {
                attempts.push(
                    {mode: 'immersive-ar', init: {requiredFeatures: ['local'], optionalFeatures: ['hit-test', 'dom-overlay'], domOverlay: {root: document.body}}},
                    {mode: 'immersive-ar', init: {requiredFeatures: ['local'], optionalFeatures: ['hit-test']}},
                    {mode: 'immersive-ar', init: {requiredFeatures: ['local']}},
                    {mode: 'immersive-ar', init: {}}
                );
            }
        }

        attempts.push(
            {mode: 'immersive-vr', init: {optionalFeatures: ['local-floor', 'bounded-floor', 'hand-tracking']}},
            {mode: 'immersive-vr', init: {}}
        );

        let lastError = null;
        for (const attempt of attempts) {
            try {
                const supported = await navigator.xr.isSessionSupported(attempt.mode).catch(() => false);
                if (!supported) continue;
                const session = await navigator.xr.requestSession(attempt.mode, attempt.init);
                return {mode: attempt.mode, session};
            } catch (error) {
                lastError = error;
            }
        }

        throw lastError || new Error('No compatible XR session mode');
    }

    function ensureXrControllers(){
        if (xrControllers.length) return;
        for (let i = 0; i < 2; i++) {
            const controller = renderer.xr.getController(i);
            controller.addEventListener('selectstart', () => grabWithController(controller));
            controller.addEventListener('selectend', releaseXrGrab);
            xrControllers.push(controller);
            scene.add(controller);
        }
    }

    function grabWithController(controller){
        if (xrGrab.active || !cardGroup.parent) return;
        xrGrab.active = true;
        xrGrab.controller = controller;
        xrGrab.originalParent = cardGroup.parent;
        controller.attach(cardGroup);
    }

    function releaseXrGrab(){
        if (!xrGrab.active) return;
        const parent = xrGrab.originalParent || scene;
        parent.attach(cardGroup);
        xrGrab.active = false;
        xrGrab.controller = null;
        xrGrab.originalParent = null;
    }

    const animate = () => {
        controls.update();
        renderer.render(scene, camera);
    };

    window.addEventListener('resize', resize, {passive: true});
    mount.addEventListener('pointerdown', () => ambientAudio?.unlock(), {passive: true});
    resize();
    renderer.setAnimationLoop(animate);
});

function parseEnvironmentPayload(raw){
    if (!raw) return null;
    try {
        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' ? parsed : null;
    } catch {
        return null;
    }
}

function applyEnvironmentSkybox(scene, renderer, environment){
    const backgroundColor = environment?.background_color || '#080a10';
    if (!environment?.skybox_url) {
        scene.background = new THREE.Color(backgroundColor);
        renderer.setClearColor(new THREE.Color(backgroundColor), 1);
        return;
    }

    const loader = new THREE.TextureLoader();
    loader.setCrossOrigin('anonymous');
    loader.load(environment.skybox_url, (texture) => {
        texture.mapping = THREE.EquirectangularReflectionMapping;
        texture.colorSpace = THREE.SRGBColorSpace;
        texture.generateMipmaps = false;
        texture.minFilter = THREE.LinearFilter;
        texture.magFilter = THREE.LinearFilter;
        texture.anisotropy = Math.min(8, renderer.capabilities.getMaxAnisotropy?.() || 1);
        scene.background = texture;
        scene.environment = texture;
        renderer.setClearColor(new THREE.Color(backgroundColor), 1);
    }, undefined, () => {
        scene.background = new THREE.Color(backgroundColor);
        renderer.setClearColor(new THREE.Color(backgroundColor), 1);
    });
}

function createEnvironmentAudio(mount, environment){
    if (!environment?.audio_url) return null;

    const audio = new Audio(environment.audio_url);
    audio.loop = true;
    audio.volume = Math.max(0, Math.min(1, Number.parseFloat(environment.audio_volume ?? 0.24) || 0.24));
    audio.preload = 'metadata';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'wc-procedural-audio-toggle';
    button.innerHTML = '<i class="fa-solid fa-volume-xmark"></i> Ambience';
    mount.appendChild(button);

    const setState = (playing) => {
        button.classList.toggle('is-playing', playing);
        button.innerHTML = playing
            ? '<i class="fa-solid fa-volume-high"></i> Ambience'
            : '<i class="fa-solid fa-volume-xmark"></i> Ambience';
    };

    const play = async () => {
        try {
            await audio.play();
            setState(true);
        } catch {
            setState(false);
        }
    };

    button.addEventListener('click', async () => {
        if (audio.paused) await play();
        else {
            audio.pause();
            setState(false);
        }
    });

    return {unlock: play};
}

function colorFromConfig(value, fallback){
    if (!value) return new THREE.Color(fallback);
    try {
        return new THREE.Color(value);
    } catch {
        return new THREE.Color(fallback);
    }
}

function applyVector3(vector, value, fallback){
    const source = Array.isArray(value) && value.length >= 3 ? value : fallback;
    vector.set(
        Number.parseFloat(source[0]) || 0,
        Number.parseFloat(source[1]) || 0,
        Number.parseFloat(source[2]) || 0
    );
}

function createFoilTexture(){
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 512;
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 512, 512);
    gradient.addColorStop(0, 'rgba(255,255,255,0)');
    gradient.addColorStop(.24, 'rgba(255,140,220,.24)');
    gradient.addColorStop(.44, 'rgba(120,205,255,.24)');
    gradient.addColorStop(.64, 'rgba(255,235,130,.18)');
    gradient.addColorStop(.82, 'rgba(150,255,205,.18)');
    gradient.addColorStop(1, 'rgba(255,255,255,0)');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, 512, 512);
    ctx.globalAlpha = .08;
    for(let i = -512; i < 512; i += 26){
        ctx.fillStyle = 'white';
        ctx.fillRect(i, 0, 2, 512);
    }
    return canvas;
}

function roundedCardGeometry(width, height, radius, depth){
    const shape = roundedRectShape(width, height, radius);
    const geometry = new THREE.ExtrudeGeometry(shape, {
        depth,
        bevelEnabled: true,
        bevelThickness: Math.min(depth * 0.35, 0.012),
        bevelSize: Math.min(depth * 0.35, 0.012),
        bevelSegments: 2,
        curveSegments: 24
    });
    geometry.center();
    geometry.computeVertexNormals();
    return geometry;
}

function roundedRectShape(width, height, radius){
    const x = -width / 2;
    const y = -height / 2;
    const r = Math.min(radius, width / 2, height / 2);
    const shape = new THREE.Shape();
    shape.moveTo(x + r, y);
    shape.lineTo(x + width - r, y);
    shape.quadraticCurveTo(x + width, y, x + width, y + r);
    shape.lineTo(x + width, y + height - r);
    shape.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
    shape.lineTo(x + r, y + height);
    shape.quadraticCurveTo(x, y + height, x, y + height - r);
    shape.lineTo(x, y + r);
    shape.quadraticCurveTo(x, y, x + r, y);
    return shape;
}

function roundedAlphaTexture(width, height, radius){
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#000';
    ctx.fillRect(0, 0, width, height);
    ctx.fillStyle = '#fff';
    roundedCanvasPath(ctx, 0, 0, width, height, radius);
    ctx.fill();

    const texture = new THREE.CanvasTexture(canvas);
    return texture;
}

function loadMaterialTexture(loader, url, material){
    loader.load(url, (texture) => {
        texture.colorSpace = THREE.SRGBColorSpace;
        texture.anisotropy = 8;
        material.map = texture;
        material.needsUpdate = true;
    });
}

function createMirrodinEnvironment(cardWidth, cardHeight){
    const group = new THREE.Group();
    const floor = new THREE.Mesh(
        new THREE.CircleGeometry(5.5, 96),
        new THREE.MeshStandardMaterial({color: 0x151a20, roughness: 0.72, metalness: 0.25})
    );
    floor.rotation.x = -Math.PI / 2;
    floor.position.y = 0.55;
    group.add(floor);

    const ringMaterial = new THREE.MeshBasicMaterial({color: 0x62b6ff, transparent: true, opacity: 0.18, side: THREE.DoubleSide});
    for (let i = 0; i < 4; i++) {
        const ring = new THREE.Mesh(new THREE.TorusGeometry(1.35 + i * 0.72, 0.008, 8, 96), ringMaterial);
        ring.rotation.x = Math.PI / 2;
        ring.position.y = 0.57;
        group.add(ring);
    }

    const pillarMaterial = new THREE.MeshStandardMaterial({color: 0x2f2719, roughness: 0.58, metalness: 0.5});
    for (let i = 0; i < 8; i++) {
        const angle = (Math.PI * 2 * i) / 8;
        const pillar = new THREE.Mesh(new THREE.BoxGeometry(0.16, 1.8, 0.16), pillarMaterial);
        pillar.position.set(Math.cos(angle) * 3.8, 1.35, Math.sin(angle) * 3.8 - 1.2);
        pillar.rotation.y = -angle;
        group.add(pillar);
    }

    const backGlow = new THREE.Mesh(
        new THREE.PlaneGeometry(cardWidth * 1.35, cardHeight * 1.18),
        new THREE.MeshBasicMaterial({color: 0x62b6ff, transparent: true, opacity: 0.08, side: THREE.DoubleSide})
    );
    backGlow.position.set(0, 1.45, -2.18);
    group.add(backGlow);

    return group;
}

function escapeHtml(value){
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
}

function roundedCanvasPath(ctx, x, y, width, height, radius){
    const r = Math.min(radius, width / 2, height / 2);
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.lineTo(x + width - r, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + r);
    ctx.lineTo(x + width, y + height - r);
    ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
    ctx.lineTo(x + r, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - r);
    ctx.lineTo(x, y + r);
    ctx.quadraticCurveTo(x, y, x + r, y);
    ctx.closePath();
}
</script>
