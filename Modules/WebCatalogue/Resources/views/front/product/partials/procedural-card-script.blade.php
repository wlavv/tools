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
    const width = 2.5;
    const height = width * ratio;
    const radius = width * 0.055;
    const faceOffset = Math.max(thickness / 2 + 0.01, 0.018);
    const scene = new THREE.Scene();
    scene.background = null;

    const camera = new THREE.PerspectiveCamera(35, 1, 0.1, 100);
    camera.position.set(0, 0.25, 7.2);

    const renderer = new THREE.WebGLRenderer({antialias: true, alpha: true});
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    mount.appendChild(renderer.domElement);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.autoRotate = true;
    controls.autoRotateSpeed = 0.7;
    controls.enablePan = false;
    controls.minDistance = 4.2;
    controls.maxDistance = 9;
    controls.target.set(0, 0, 0);

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

    scene.add(new THREE.HemisphereLight(0xffffff, 0x0f172a, 1.6));
    const key = new THREE.DirectionalLight(0xffffff, 2.2);
    key.position.set(3, 4, 6);
    scene.add(key);
    const rim = new THREE.DirectionalLight(0x9bdcff, 1.4);
    rim.position.set(-4, 2, -3);
    scene.add(rim);

    const resize = () => {
        const rect = mount.getBoundingClientRect();
        const w = Math.max(1, rect.width);
        const h = Math.max(1, rect.height);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h, false);
    };

    const animate = () => {
        controls.update();
        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    };

    window.addEventListener('resize', resize, {passive: true});
    resize();
    animate();
});

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
