@extends('webcatalogue::front.layouts.app')

@section('content')
<div class="wc-front-container wc-scan-page">
    <section class="wc-front-hero wc-scan-hero">
        <div>
            <span class="wc-front-kicker"><i class="fa-solid fa-camera"></i> Visual Recognition</span>
            <h1>{{ !empty($isGlobalScan) ? 'Understand this object' : 'Scan a product' }}</h1>
            <p>{{ !empty($isGlobalScan) ? 'Point your camera at an object. We identify it and show useful information: manuals, assembly instructions, videos, technical files, images, 3D/AR resources and related documentation.' : 'Point your camera at a product. For best results, center the object, use a neutral background and avoid strong shadows. If we find it, we open the product intelligence page. If not, you can request it and help us improve the dataset.' }}</p>
        </div>
    </section>

    <div class="wc-scan-grid">
        <div class="wc-scan-camera-card">
            <div class="wc-scan-video-wrap">
                <video id="wcScanVideo" autoplay playsinline muted></video>
                <canvas id="wcScanCanvas" style="display:none"></canvas>
                <div class="wc-scan-focus-overlay" aria-hidden="true">
                    <span class="wc-scan-focus-corner wc-scan-focus-corner-tl"></span>
                    <span class="wc-scan-focus-corner wc-scan-focus-corner-tr"></span>
                    <span class="wc-scan-focus-corner wc-scan-focus-corner-bl"></span>
                    <span class="wc-scan-focus-corner wc-scan-focus-corner-br"></span>
                    <strong>Keep the product inside this area</strong>
                </div>
                <div id="wcDetectedObjectBox" class="wc-scan-detected-box" aria-hidden="true"><span>Object focus</span></div>
                <div id="wcScanPlaceholder" class="wc-scan-placeholder">
                    <i class="fa-solid fa-camera"></i>
                    <strong>Camera not started</strong>
                    <span>Use your mobile camera to capture the product.</span>
                </div>
            </div>
            <div class="wc-scan-actions">
                <button type="button" class="wc-front-btn wc-front-btn-primary" id="wcStartCamera"><i class="fa-solid fa-video"></i> Open camera</button>
                <button type="button" class="wc-front-btn" id="wcToggleTorch" disabled><i class="fa-solid fa-bolt"></i> Flash</button>
                <button type="button" class="wc-front-btn" id="wcCaptureProduct" disabled><i class="fa-solid fa-camera"></i> Capture product</button>
                <button type="button" class="wc-front-btn" id="wcSearchProduct" disabled><i class="fa-solid fa-wand-magic-sparkles"></i> Find product</button>
            </div>
            <div class="wc-scan-focus-tools">
                <label class="wc-scan-toggle">
                    <input type="checkbox" id="wcFocusEnhance" checked>
                    <span><i class="fa-solid fa-bullseye"></i> Focus product and blur background before matching</span>
                </label>
                <small>This improves recognition when the product is centered and the background is busy.</small>
            </div>
            <div id="wcScanMessage" class="wc-scan-message"></div>
            <div id="wcScanSuggestions" class="wc-scan-suggestions" hidden></div>
        </div>

        <aside class="wc-scan-side-card">
            <h3>{{ !empty($isGlobalScan) ? 'Object intelligence' : 'When no match is found' }}</h3>
            <p>{{ !empty($isGlobalScan) ? 'This scan is not tied to one store. The system searches the shared object dataset and opens the best available information page for the matched item.' : 'We ask for brand, model, reference or a label photo. This creates a WebCatalogue lead that can later enrich the product knowledge base.' }}</p>
            <ul>
                <li><i class="fa-solid fa-check"></i> Use a plain / neutral background</li>
                <li><i class="fa-solid fa-check"></i> Center the product inside the focus frame</li>
                <li><i class="fa-solid fa-check"></i> Avoid blur and strong shadows</li>
                <li><i class="fa-solid fa-check"></i> Submit label details if no match is found</li>
            </ul>
        </aside>
    </div>

    <div id="wcUnmatchedModal" class="wc-scan-modal" hidden>
        <div class="wc-scan-modal-card">
            <button type="button" class="wc-scan-modal-close" id="wcCloseUnmatched">&times;</button>
            <span class="wc-front-kicker"><i class="fa-solid fa-circle-question"></i> Product not found</span>
            <h2>Help us identify this product</h2>
            <form id="wcUnmatchedForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="session_token" id="wcSessionToken">
                <div class="wc-form-grid">
                    <label>Brand<input name="brand" type="text" placeholder="Brand name"></label>
                    <label>Model<input name="model" type="text" placeholder="Model"></label>
                    <label>Reference<input name="reference" type="text" placeholder="Reference / SKU"></label>
                    <label>Email optional<input name="customer_email" type="email" placeholder="your@email.com"></label>
                </div>
                <label>Description<textarea name="description" rows="3" placeholder="Any visible details, product family, materials, etc."></textarea></label>
                <label>Label photo<input name="label_photo" type="file" accept="image/*"></label>
                <div class="wc-scan-actions wc-scan-actions-end">
                    <button type="submit" class="wc-front-btn wc-front-btn-primary"><i class="fa-solid fa-paper-plane"></i> Submit request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.wc-scan-grid{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(280px,.8fr);gap:24px}.wc-scan-camera-card,.wc-scan-side-card,.wc-scan-modal-card{background:var(--wc-card,#fff);border:1px solid var(--wc-border,#e5e7eb);border-radius:var(--wc-radius,5px);box-shadow:0 14px 35px rgba(15,23,42,.08);padding:18px}.wc-scan-video-wrap{height:min(62vh,560px);background:#111;border-radius:var(--wc-radius,5px);display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}.wc-scan-video-wrap video{width:100%;height:100%;object-fit:contain;background:#111}.wc-scan-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#fff;gap:8px;background:linear-gradient(135deg,#111827,#334155)}.wc-scan-placeholder i{font-size:54px;opacity:.8}.wc-scan-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}.wc-scan-actions-end{justify-content:flex-end}.wc-scan-side-card ul{padding:0;margin:18px 0 0;list-style:none}.wc-scan-side-card li{margin:10px 0;color:var(--wc-text,#111827)}.wc-scan-side-card i{color:var(--wc-accent,#c9a96e);margin-right:8px}.wc-scan-message{margin-top:12px;color:var(--wc-muted,#64748b)}.wc-scan-modal{position:fixed;inset:0;background:rgba(15,23,42,.65);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px}.wc-scan-modal[hidden]{display:none}.wc-scan-modal-card{max-width:780px;width:100%;position:relative}.wc-scan-modal-close{position:absolute;right:14px;top:10px;background:transparent;border:0;font-size:28px}.wc-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.wc-scan-modal label{display:flex;flex-direction:column;gap:6px;margin-bottom:12px;font-weight:600}.wc-scan-modal input,.wc-scan-modal textarea{border:1px solid var(--wc-border,#e5e7eb);border-radius:var(--wc-radius,5px);padding:10px;background:#fff}.wc-front-btn[disabled]{opacity:.5;cursor:not-allowed}.wc-scan-focus-overlay{position:absolute;left:50%;top:50%;width:min(68%,520px);height:min(68%,420px);transform:translate(-50%,-50%);z-index:2;border:1px dashed rgba(255,255,255,.7);border-radius:var(--wc-radius,5px);box-shadow:0 0 0 9999px rgba(0,0,0,.22);display:flex;align-items:flex-end;justify-content:center;padding:14px;pointer-events:none;color:#fff;text-shadow:0 1px 8px rgba(0,0,0,.65);font-size:13px;letter-spacing:.02em}.wc-scan-focus-corner{position:absolute;width:34px;height:34px;border-color:var(--wc-accent,#c9a96e);border-style:solid}.wc-scan-focus-corner-tl{left:-2px;top:-2px;border-width:3px 0 0 3px}.wc-scan-focus-corner-tr{right:-2px;top:-2px;border-width:3px 3px 0 0}.wc-scan-focus-corner-bl{left:-2px;bottom:-2px;border-width:0 0 3px 3px}.wc-scan-focus-corner-br{right:-2px;bottom:-2px;border-width:0 3px 3px 0}.wc-scan-detected-box{position:absolute;z-index:3;display:none;border:3px solid var(--wc-accent,#c9a96e);border-radius:var(--wc-radius,5px);box-shadow:0 0 0 1px rgba(0,0,0,.34),0 0 28px rgba(201,169,110,.55);pointer-events:none;transition:left .12s ease,top .12s ease,width .12s ease,height .12s ease}.wc-scan-detected-box.is-visible{display:block}.wc-scan-detected-box.is-estimated{border-style:dashed;border-color:rgba(255,255,255,.86);box-shadow:0 0 0 1px rgba(0,0,0,.28),0 0 18px rgba(255,255,255,.22)}.wc-scan-detected-box span{position:absolute;left:8px;top:-28px;background:rgba(15,23,42,.86);color:#fff;border-radius:4px;padding:4px 7px;font-size:11px;font-weight:700;white-space:nowrap}.wc-scan-focus-tools{margin-top:12px;padding:12px;border:1px solid var(--wc-border,#e5e7eb);border-radius:var(--wc-radius,5px);background:color-mix(in srgb,var(--wc-card,#fff) 88%,var(--wc-accent,#c9a96e) 12%)}.wc-scan-toggle{display:flex;align-items:center;gap:9px;font-weight:700;color:var(--wc-text,#111827);cursor:pointer}.wc-scan-toggle input{width:17px;height:17px;accent-color:var(--wc-accent,#c9a96e)}.wc-scan-focus-tools small{display:block;margin-top:5px;color:var(--wc-muted,#64748b)}@media(max-width:900px){.wc-scan-grid,.wc-form-grid{grid-template-columns:1fr}.wc-scan-focus-overlay{width:76%;height:62%;font-size:12px}}
.wc-scan-suggestions{margin-top:16px;border-top:1px solid var(--wc-border,#e5e7eb);padding-top:16px}.wc-scan-suggestions h3{margin:0 0 4px}.wc-scan-suggestion-help{margin:0 0 12px;color:var(--wc-muted,#64748b)}.wc-scan-suggestion-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:12px}.wc-scan-suggestion-card{text-decoration:none;color:var(--wc-text,#111827);border:1px solid var(--wc-border,#e5e7eb);border-radius:var(--wc-radius,5px);background:var(--wc-surface,#fff);padding:10px;display:flex;flex-direction:column;gap:5px;transition:.18s ease}.wc-scan-suggestion-card:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(15,23,42,.12)}.wc-scan-suggestion-img{height:110px;display:flex;align-items:center;justify-content:center;background:var(--wc-image-bg,#f8fafc);border-radius:var(--wc-radius,5px);overflow:hidden}.wc-scan-suggestion-img img{width:100%;height:100%;object-fit:contain}.wc-scan-suggestion-img i{font-size:34px;color:var(--wc-muted,#64748b)}.wc-scan-suggestion-card small{color:var(--wc-muted,#64748b)}.wc-scan-suggestion-card em{font-style:normal;color:var(--wc-accent,#c9a96e);font-weight:700}
</style>

<script>
(() => {
    const routes = {
        session: @json(!empty($isGlobalScan) ? route('webcatalogue.front.scan.global.session') : route('webcatalogue.front.scan.session', $store->slug)),
        capture: @json(!empty($isGlobalScan) ? route('webcatalogue.front.scan.global.capture') : route('webcatalogue.front.scan.capture', $store->slug)),
        match: @json(!empty($isGlobalScan) ? route('webcatalogue.front.scan.global.match') : route('webcatalogue.front.scan.match', $store->slug)),
        unmatched: @json(!empty($isGlobalScan) ? route('webcatalogue.front.scan.global.unmatched') : route('webcatalogue.front.scan.unmatched', $store->slug)),
        resultBase: @json(!empty($isGlobalScan) ? url('/catalogue/scan/result') : url('/catalogue/' . $store->slug . '/scan/result')),
    };
    const csrf = @json(csrf_token());
    const video = document.getElementById('wcScanVideo');
    const canvas = document.getElementById('wcScanCanvas');
    const videoWrap = video.closest('.wc-scan-video-wrap');
    const detectedBox = document.getElementById('wcDetectedObjectBox');
    const placeholder = document.getElementById('wcScanPlaceholder');
    const msg = document.getElementById('wcScanMessage');
    const suggestionsBox = document.getElementById('wcScanSuggestions');
    const startBtn = document.getElementById('wcStartCamera');
    const torchBtn = document.getElementById('wcToggleTorch');
    const captureBtn = document.getElementById('wcCaptureProduct');
    const searchBtn = document.getElementById('wcSearchProduct');
    const focusEnhance = document.getElementById('wcFocusEnhance');
    const modal = document.getElementById('wcUnmatchedModal');
    const closeBtn = document.getElementById('wcCloseUnmatched');
    const tokenInput = document.getElementById('wcSessionToken');
    const form = document.getElementById('wcUnmatchedForm');
    let sessionToken = null;
    let stream = null;
    let torchTrack = null;
    let torchEnabled = false;
    let detectionTimer = null;
    let detectedRect = null;
    let detectedRectIsEstimated = false;
    let trackedRect = null;
    let missedDetections = 0;
    let tfDetector = null;
    let tfDetectorPromise = null;
    let tfDetecting = false;
    let tfStatusShown = false;
    let lastDetectionSource = 'none';

    function setMessage(text){ msg.textContent = text || ''; }
    function clearSuggestions(){ suggestionsBox.hidden = true; suggestionsBox.innerHTML = ''; }

    function updateTorchButton(){
        if(!torchBtn) return;
        torchBtn.classList.toggle('wc-front-btn-primary', torchEnabled);
        torchBtn.innerHTML = torchEnabled
            ? '<i class="fa-solid fa-bolt"></i> Flash on'
            : '<i class="fa-solid fa-bolt"></i> Flash';
    }

    async function setupTorch(){
        torchEnabled = false;
        torchTrack = null;
        if(!torchBtn || !stream) return;

        const [track] = stream.getVideoTracks();
        const capabilities = track?.getCapabilities ? track.getCapabilities() : {};
        const hasTorch = !!capabilities?.torch;
        torchTrack = hasTorch ? track : null;
        torchBtn.disabled = !hasTorch;
        torchBtn.hidden = false;
        updateTorchButton();

        if(!hasTorch){
            torchBtn.title = 'Flash is not supported by this browser/camera.';
        }else{
            torchBtn.title = 'Toggle camera flash.';
        }
    }

    async function setTorch(enabled){
        if(!torchTrack) return false;
        try{
            await torchTrack.applyConstraints({advanced:[{torch:enabled}]});
            torchEnabled = enabled;
            updateTorchButton();
            return true;
        }catch(e){
            torchEnabled = false;
            torchBtn.disabled = true;
            updateTorchButton();
            setMessage('Flash is not available on this camera/browser.');
            return false;
        }
    }

    function getFocusRect(width, height){
        const ratio = 0.68;
        const focusWidth = Math.round(width * ratio);
        const focusHeight = Math.round(height * ratio);
        return {
            x: Math.round((width - focusWidth) / 2),
            y: Math.round((height - focusHeight) / 2),
            w: focusWidth,
            h: focusHeight
        };
    }

    function videoDisplayMetrics(){
        const wrapRect = videoWrap.getBoundingClientRect();
        const videoWidth = video.videoWidth || 0;
        const videoHeight = video.videoHeight || 0;
        if(!videoWidth || !videoHeight) return null;

        const videoRatio = videoWidth / videoHeight;
        const wrapRatio = wrapRect.width / wrapRect.height;
        let displayWidth = wrapRect.width;
        let displayHeight = wrapRect.height;
        let offsetX = 0;
        let offsetY = 0;

        if(wrapRatio > videoRatio){
            displayHeight = wrapRect.height;
            displayWidth = displayHeight * videoRatio;
            offsetX = (wrapRect.width - displayWidth) / 2;
        }else{
            displayWidth = wrapRect.width;
            displayHeight = displayWidth / videoRatio;
            offsetY = (wrapRect.height - displayHeight) / 2;
        }

        return {videoWidth, videoHeight, displayWidth, displayHeight, offsetX, offsetY};
    }

    function updateDetectedBox(rect, estimated = false){
        detectedRect = rect || null;
        detectedRectIsEstimated = estimated;
        const metrics = videoDisplayMetrics();
        if(!rect || !metrics){
            detectedBox.classList.remove('is-visible');
            return;
        }

        detectedBox.style.left = `${metrics.offsetX + (rect.x / metrics.videoWidth) * metrics.displayWidth}px`;
        detectedBox.style.top = `${metrics.offsetY + (rect.y / metrics.videoHeight) * metrics.displayHeight}px`;
        detectedBox.style.width = `${(rect.w / metrics.videoWidth) * metrics.displayWidth}px`;
        detectedBox.style.height = `${(rect.h / metrics.videoHeight) * metrics.displayHeight}px`;
        detectedBox.classList.toggle('is-estimated', estimated);
        const label = detectedBox.querySelector('span');
        if(label) label.textContent = estimated ? 'Focus area' : 'Object focus';
        detectedBox.classList.add('is-visible');
    }

    function smoothRect(previous, next, amount = .38){
        if(!previous) return next;
        return {
            x: Math.round(previous.x + ((next.x - previous.x) * amount)),
            y: Math.round(previous.y + ((next.y - previous.y) * amount)),
            w: Math.round(previous.w + ((next.w - previous.w) * amount)),
            h: Math.round(previous.h + ((next.h - previous.h) * amount))
        };
    }

    function loadScannerScript(src){
        return new Promise((resolve, reject) => {
            const existing = document.querySelector('script[src="' + src + '"]');
            if(existing){
                existing.addEventListener('load', resolve, {once:true});
                existing.addEventListener('error', reject, {once:true});
                if(existing.dataset.loaded === '1') resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = () => { script.dataset.loaded = '1'; resolve(); };
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async function ensureTensorFlowDetector(){
        if(tfDetector) return tfDetector;
        if(tfDetectorPromise) return tfDetectorPromise;

        tfDetectorPromise = (async () => {
            await loadScannerScript('https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.22.0/dist/tf.min.js');
            await loadScannerScript('https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd@2.2.3/dist/coco-ssd.min.js');
            if(!window.cocoSsd) throw new Error('TensorFlow object model unavailable');
            tfDetector = await window.cocoSsd.load({base: 'lite_mobilenet_v2'});
            if(!tfStatusShown){
                tfStatusShown = true;
                setMessage('AI object tracking enabled. Keep the product inside the highlighted area.');
            }
            return tfDetector;
        })().catch(() => {
            tfDetectorPromise = null;
            return null;
        });

        return tfDetectorPromise;
    }

    async function findTensorFlowObjectRect(){
        if(tfDetecting || !stream || video.readyState < 2) return null;
        tfDetecting = true;

        try{
            const detector = await ensureTensorFlowDetector();
            if(!detector) return null;

            const predictions = await detector.detect(video, 8);
            const sourceWidth = video.videoWidth || 0;
            const sourceHeight = video.videoHeight || 0;
            if(!sourceWidth || !sourceHeight || !predictions.length) return null;

            let best = null;
            predictions.forEach(prediction => {
                const score = Number(prediction.score || 0);
                if(score < .55 || !prediction.bbox) return;

                const [x, y, w, h] = prediction.bbox.map(value => Math.max(0, Number(value || 0)));
                const widthRatio = w / sourceWidth;
                const heightRatio = h / sourceHeight;
                if(widthRatio < .12 || heightRatio < .12 || widthRatio > .90 || heightRatio > .92) return;

                const centerX = x + (w / 2);
                const centerY = y + (h / 2);
                const centerPenalty = (Math.abs(centerX - (sourceWidth / 2)) / sourceWidth)
                    + (Math.abs(centerY - (sourceHeight / 2)) / sourceHeight);
                const areaScore = Math.min(1, (w * h) / (sourceWidth * sourceHeight) * 4);
                const finalScore = (score * .72) + (areaScore * .18) + ((1 - Math.min(.8, centerPenalty)) * .10);

                if(!best || finalScore > best.score){
                    best = {
                        x: Math.round(Math.max(0, x)),
                        y: Math.round(Math.max(0, y)),
                        w: Math.round(Math.min(sourceWidth - x, w)),
                        h: Math.round(Math.min(sourceHeight - y, h)),
                        score: finalScore,
                        className: prediction.class || 'object'
                    };
                }
            });

            return best;
        }catch(e){
            return null;
        }finally{
            tfDetecting = false;
        }
    }

    function findProminentObjectRect(){
        const sourceWidth = video.videoWidth || 0;
        const sourceHeight = video.videoHeight || 0;
        if(!sourceWidth || !sourceHeight) return null;

        const sampleWidth = 120;
        const sampleHeight = Math.max(80, Math.min(180, Math.round(sourceHeight * (sampleWidth / sourceWidth))));
        const sample = document.createElement('canvas');
        sample.width = sampleWidth;
        sample.height = sampleHeight;
        const ctx = sample.getContext('2d', {willReadFrequently:true});
        ctx.drawImage(video, 0, 0, sampleWidth, sampleHeight);
        const {data} = ctx.getImageData(0, 0, sampleWidth, sampleHeight);
        const border = {r:0,g:0,b:0,count:0};

        function readPixel(x, y){
            const idx = ((y * sampleWidth) + x) * 4;
            return {r:data[idx], g:data[idx+1], b:data[idx+2]};
        }

        for(let x = 0; x < sampleWidth; x += 4){
            [readPixel(x, 0), readPixel(x, sampleHeight - 1)].forEach(p => { border.r += p.r; border.g += p.g; border.b += p.b; border.count++; });
        }
        for(let y = 0; y < sampleHeight; y += 4){
            [readPixel(0, y), readPixel(sampleWidth - 1, y)].forEach(p => { border.r += p.r; border.g += p.g; border.b += p.b; border.count++; });
        }
        border.r /= Math.max(1, border.count);
        border.g /= Math.max(1, border.count);
        border.b /= Math.max(1, border.count);

        const mask = new Uint8Array(sampleWidth * sampleHeight);
        for(let y = 0; y < sampleHeight; y++){
            for(let x = 0; x < sampleWidth; x++){
                const p = readPixel(x, y);
                const max = Math.max(p.r, p.g, p.b);
                const min = Math.min(p.r, p.g, p.b);
                const luma = (0.299 * p.r) + (0.587 * p.g) + (0.114 * p.b);
                const saturation = max > 0 ? (max - min) / max : 0;
                const distance = Math.hypot(p.r - border.r, p.g - border.g, p.b - border.b);
                mask[(y * sampleWidth) + x] = (distance > 38 || luma < 70 || (luma < 120 && saturation > .22)) ? 1 : 0;
            }
        }

        const visited = new Uint8Array(sampleWidth * sampleHeight);
        const edgeX = Math.max(1, Math.floor(sampleWidth * .03));
        const edgeY = Math.max(1, Math.floor(sampleHeight * .03));
        let best = null;

        for(let y = 0; y < sampleHeight; y++){
            for(let x = 0; x < sampleWidth; x++){
                const start = (y * sampleWidth) + x;
                if(visited[start] || !mask[start]) continue;

                const queue = [[x, y]];
                visited[start] = 1;
                let minX = x, maxX = x, minY = y, maxY = y, count = 0, touchesEdge = false;

                while(queue.length){
                    const [cx, cy] = queue.pop();
                    count++;
                    minX = Math.min(minX, cx); maxX = Math.max(maxX, cx);
                    minY = Math.min(minY, cy); maxY = Math.max(maxY, cy);
                    touchesEdge = touchesEdge || cx <= edgeX || cy <= edgeY || cx >= sampleWidth - 1 - edgeX || cy >= sampleHeight - 1 - edgeY;

                    [[1,0],[-1,0],[0,1],[0,-1]].forEach(([dx, dy]) => {
                        const nx = cx + dx, ny = cy + dy;
                        if(nx < 0 || ny < 0 || nx >= sampleWidth || ny >= sampleHeight) return;
                        const idx = (ny * sampleWidth) + nx;
                        if(visited[idx] || !mask[idx]) return;
                        visited[idx] = 1;
                        queue.push([nx, ny]);
                    });
                }

                const w = maxX - minX + 1;
                const h = maxY - minY + 1;
                const widthRatio = w / sampleWidth;
                const heightRatio = h / sampleHeight;
                if(touchesEdge || count < 40 || widthRatio < .18 || heightRatio < .18 || widthRatio > .92 || heightRatio > .96) continue;

                const centerPenalty = (Math.abs(((minX + maxX) / 2) - (sampleWidth / 2)) / sampleWidth)
                    + (Math.abs(((minY + maxY) / 2) - (sampleHeight / 2)) / sampleHeight);
                const score = w * h * (1 - Math.min(.7, centerPenalty));
                if(!best || score > best.score) best = {minX, minY, maxX, maxY, score};
            }
        }

        if(!best){
            const focus = getFocusRect(sampleWidth, sampleHeight);
            const xs = [];
            const ys = [];
            const startX = Math.max(0, focus.x);
            const startY = Math.max(0, focus.y);
            const endX = Math.min(sampleWidth - 2, focus.x + focus.w);
            const endY = Math.min(sampleHeight - 2, focus.y + focus.h);

            for(let y = startY; y <= endY; y++){
                for(let x = startX; x <= endX; x++){
                    const p = readPixel(x, y);
                    const right = readPixel(Math.min(sampleWidth - 1, x + 1), y);
                    const down = readPixel(x, Math.min(sampleHeight - 1, y + 1));
                    const max = Math.max(p.r, p.g, p.b);
                    const min = Math.min(p.r, p.g, p.b);
                    const luma = (0.299 * p.r) + (0.587 * p.g) + (0.114 * p.b);
                    const saturation = max > 0 ? (max - min) / max : 0;
                    const distance = Math.hypot(p.r - border.r, p.g - border.g, p.b - border.b);
                    const edge = Math.max(
                        Math.abs(p.r - right.r) + Math.abs(p.g - right.g) + Math.abs(p.b - right.b),
                        Math.abs(p.r - down.r) + Math.abs(p.g - down.g) + Math.abs(p.b - down.b)
                    ) / 3;

                    if(distance > 45 || edge > 34 || luma < 72 || (luma < 126 && saturation > .28)){
                        xs.push(x);
                        ys.push(y);
                    }
                }
            }

            if(xs.length > 90){
                xs.sort((a, b) => a - b);
                ys.sort((a, b) => a - b);
                const q = (items, ratio) => items[Math.max(0, Math.min(items.length - 1, Math.floor(items.length * ratio)))];
                let minX = q(xs, .04);
                let maxX = q(xs, .96);
                let minY = q(ys, .04);
                let maxY = q(ys, .96);
                const w = maxX - minX + 1;
                const h = maxY - minY + 1;
                const widthRatio = w / sampleWidth;
                const heightRatio = h / sampleHeight;

                if(widthRatio >= .18 && heightRatio >= .18 && widthRatio <= .88 && heightRatio <= .94){
                    best = {minX, minY, maxX, maxY, score: xs.length};
                }
            }
        }

        if(!best) return null;

        const padX = Math.round((best.maxX - best.minX + 1) * .04);
        const padY = Math.round((best.maxY - best.minY + 1) * .04);
        const x = Math.max(0, best.minX - padX);
        const y = Math.max(0, best.minY - padY);
        const right = Math.min(sampleWidth - 1, best.maxX + padX);
        const bottom = Math.min(sampleHeight - 1, best.maxY + padY);

        return {
            x: Math.round((x / sampleWidth) * sourceWidth),
            y: Math.round((y / sampleHeight) * sourceHeight),
            w: Math.round(((right - x + 1) / sampleWidth) * sourceWidth),
            h: Math.round(((bottom - y + 1) / sampleHeight) * sourceHeight)
        };
    }

    function startObjectDetection(){
        stopObjectDetection();
        ensureTensorFlowDetector();
        detectionTimer = window.setInterval(async () => {
            if(!stream || video.readyState < 2) return;
            const aiDetected = tfDetector ? await findTensorFlowObjectRect() : null;
            const heuristicDetected = aiDetected ? null : findProminentObjectRect();
            const detected = aiDetected || heuristicDetected;
            if(detected){
                trackedRect = smoothRect(trackedRect, detected);
                missedDetections = 0;
                lastDetectionSource = aiDetected ? 'tensorflow' : 'heuristic';
                updateDetectedBox(trackedRect, false);
            }else if(trackedRect && missedDetections < 8){
                missedDetections++;
                updateDetectedBox(trackedRect, false);
            }else{
                trackedRect = null;
                missedDetections++;
                lastDetectionSource = 'estimated';
                updateDetectedBox(getFocusRect(video.videoWidth || 1280, video.videoHeight || 720), true);
            }
        }, 260);
    }

    function stopObjectDetection(){
        if(detectionTimer) window.clearInterval(detectionTimer);
        detectionTimer = null;
        trackedRect = null;
        missedDetections = 0;
        lastDetectionSource = 'none';
        updateDetectedBox(null);
    }

    function cropCanvasToRect(sourceCanvas, rect, paddingRatio = .08){
        const sourceWidth = sourceCanvas.width;
        const sourceHeight = sourceCanvas.height;
        if(!sourceWidth || !sourceHeight || !rect) return sourceCanvas;

        const padX = Math.round(rect.w * paddingRatio);
        const padY = Math.round(rect.h * paddingRatio);
        const x = Math.max(0, rect.x - padX);
        const y = Math.max(0, rect.y - padY);
        const right = Math.min(sourceWidth, rect.x + rect.w + padX);
        const bottom = Math.min(sourceHeight, rect.y + rect.h + padY);
        const width = Math.max(1, right - x);
        const height = Math.max(1, bottom - y);
        const maxSide = 1200;
        const scale = Math.min(1, maxSide / Math.max(width, height));
        const output = document.createElement('canvas');
        output.width = Math.max(1, Math.round(width * scale));
        output.height = Math.max(1, Math.round(height * scale));
        output.getContext('2d').drawImage(sourceCanvas, x, y, width, height, 0, 0, output.width, output.height);

        return output;
    }

    function normalizeRectangularObjectCanvas(sourceCanvas){
        const width = sourceCanvas.width;
        const height = sourceCanvas.height;
        if(width < 120 || height < 120) return sourceCanvas;

        const aspect = height / Math.max(1, width);
        if(aspect < 1.05 || aspect > 1.95) return sourceCanvas;

        const sampleWidth = 140;
        const sampleHeight = Math.max(100, Math.min(220, Math.round(height * (sampleWidth / width))));
        const sample = document.createElement('canvas');
        sample.width = sampleWidth;
        sample.height = sampleHeight;
        const sampleCtx = sample.getContext('2d', {willReadFrequently:true});
        sampleCtx.drawImage(sourceCanvas, 0, 0, sampleWidth, sampleHeight);
        const {data} = sampleCtx.getImageData(0, 0, sampleWidth, sampleHeight);

        const lumaAt = (x, y) => {
            const idx = ((y * sampleWidth) + x) * 4;
            return (0.299 * data[idx]) + (0.587 * data[idx + 1]) + (0.114 * data[idx + 2]);
        };

        const colScores = [];
        for(let x = 0; x < sampleWidth; x++){
            let score = 0;
            for(let y = Math.round(sampleHeight * .06); y < Math.round(sampleHeight * .94); y++){
                if(lumaAt(x, y) < 96) score++;
            }
            colScores[x] = score / sampleHeight;
        }

        const rowScores = [];
        for(let y = 0; y < sampleHeight; y++){
            let score = 0;
            for(let x = Math.round(sampleWidth * .06); x < Math.round(sampleWidth * .94); x++){
                if(lumaAt(x, y) < 96) score++;
            }
            rowScores[y] = score / sampleWidth;
        }

        const findEdge = (scores, fromStart, minIndex, maxIndex) => {
            const threshold = .16;
            if(fromStart){
                for(let i = minIndex; i <= maxIndex; i++){
                    if(scores[i] >= threshold) return i;
                }
            }else{
                for(let i = maxIndex; i >= minIndex; i--){
                    if(scores[i] >= threshold) return i;
                }
            }
            return null;
        };

        const left = findEdge(colScores, true, 0, Math.round(sampleWidth * .42));
        const right = findEdge(colScores, false, Math.round(sampleWidth * .58), sampleWidth - 1);
        const top = findEdge(rowScores, true, 0, Math.round(sampleHeight * .42));
        const bottom = findEdge(rowScores, false, Math.round(sampleHeight * .58), sampleHeight - 1);
        if(left === null || right === null || top === null || bottom === null) return sourceCanvas;

        const cropX = Math.max(0, Math.round((left / sampleWidth) * width) - Math.round(width * .01));
        const cropY = Math.max(0, Math.round((top / sampleHeight) * height) - Math.round(height * .01));
        const cropRight = Math.min(width, Math.round(((right + 1) / sampleWidth) * width) + Math.round(width * .01));
        const cropBottom = Math.min(height, Math.round(((bottom + 1) / sampleHeight) * height) + Math.round(height * .01));
        const cropWidth = Math.max(1, cropRight - cropX);
        const cropHeight = Math.max(1, cropBottom - cropY);
        const cropAspect = cropHeight / cropWidth;

        if(cropAspect < 1.18 || cropAspect > 1.72 || cropWidth < width * .45 || cropHeight < height * .45) {
            return sourceCanvas;
        }

        const targetHeight = 900;
        const targetWidth = Math.round(targetHeight / 1.395);
        const normalized = document.createElement('canvas');
        normalized.width = targetWidth;
        normalized.height = targetHeight;
        const normalizedCtx = normalized.getContext('2d');
        normalizedCtx.fillStyle = '#fff';
        normalizedCtx.fillRect(0, 0, targetWidth, targetHeight);
        normalizedCtx.drawImage(sourceCanvas, cropX, cropY, cropWidth, cropHeight, 0, 0, targetWidth, targetHeight);

        return normalized;
    }

    function detectedRectForCanvas(width, height){
        if(!detectedRect || detectedRectIsEstimated) return null;

        return {
            x: Math.max(0, Math.round((detectedRect.x / (video.videoWidth || width)) * width)),
            y: Math.max(0, Math.round((detectedRect.y / (video.videoHeight || height)) * height)),
            w: Math.max(1, Math.round((detectedRect.w / (video.videoWidth || width)) * width)),
            h: Math.max(1, Math.round((detectedRect.h / (video.videoHeight || height)) * height))
        };
    }

    function applyFocusBlur(sourceCanvas){
        const width = sourceCanvas.width;
        const height = sourceCanvas.height;
        if(!width || !height) return;

        const rect = detectedRectForCanvas(width, height) || getFocusRect(width, height);
        const ctx = sourceCanvas.getContext('2d');
        const original = document.createElement('canvas');
        original.width = width;
        original.height = height;
        original.getContext('2d').drawImage(sourceCanvas, 0, 0);

        ctx.save();
        ctx.clearRect(0, 0, width, height);
        ctx.filter = 'blur(14px) saturate(0.92) brightness(0.92)';
        ctx.drawImage(original, 0, 0);
        ctx.restore();

        ctx.save();
        ctx.beginPath();
        ctx.rect(rect.x, rect.y, rect.w, rect.h);
        ctx.clip();
        ctx.filter = 'contrast(1.06) saturate(1.04)';
        ctx.drawImage(original, 0, 0);
        ctx.restore();
    }

    function createCaptureCanvas(){
        canvas.width = video.videoWidth || 1280;
        canvas.height = video.videoHeight || 720;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const rect = detectedRectForCanvas(canvas.width, canvas.height);
        if(rect){
            const cropped = cropCanvasToRect(canvas, rect, lastDetectionSource === 'tensorflow' ? .10 : .06);
            return normalizeRectangularObjectCanvas(cropped);
        }

        if(focusEnhance && focusEnhance.checked){
            applyFocusBlur(canvas);
        }

        return canvas;
    }

    function wait(ms){
        return new Promise(resolve => window.setTimeout(resolve, ms));
    }

    async function sendCaptureFrame(frameIndex, frameCount){
        const captureCanvas = createCaptureCanvas();
        const photoData = captureCanvas.toDataURL('image/jpeg', .92);

        return fetch(routes.capture, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
            body:JSON.stringify({
                session_token:sessionToken,
                capture_type:'object_photo',
                photo_data:photoData,
                frame_index:frameIndex,
                frame_count:frameCount,
                detection_source:lastDetectionSource,
                cropped:!!(detectedRect && !detectedRectIsEstimated)
            })
        });
    }

    function renderSuggestions(items){
        if(!items || !items.length){ clearSuggestions(); return; }
        suggestionsBox.hidden = false;
        suggestionsBox.innerHTML = '<h3>Possible matches</h3><p class="wc-scan-suggestion-help">Choose one of these products, or continue and submit it as a missing product.</p><div class="wc-scan-suggestion-grid">' + items.map(item => `
            <a class="wc-scan-suggestion-card" href="${item.product_url}">
                <span class="wc-scan-suggestion-img">${item.image_url ? `<img src="${item.image_url}" alt="">` : '<i class="fa-solid fa-box"></i>'}</span>
                <strong>${item.name || 'Product'}</strong>
                <small>${item.reference || ''}</small>
                <em>${item.score}% match</em>
            </a>`).join('') + '</div><button type="button" class="wc-front-btn" id="wcOpenMissingFromSuggestions"><i class="fa-solid fa-circle-question"></i> None of these</button>';
        const missingBtn = document.getElementById('wcOpenMissingFromSuggestions');
        if(missingBtn){ missingBtn.addEventListener('click', () => { modal.hidden = false; }); }
    }

    async function ensureSession(){
        if(sessionToken) return sessionToken;
        const res = await fetch(routes.session, {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:JSON.stringify({device_type:navigator.userAgent})});
        const data = await res.json();
        sessionToken = data.session_token;
        tokenInput.value = sessionToken;
        return sessionToken;
    }

    startBtn.addEventListener('click', async () => {
        try{
            await ensureSession();
            stream = await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'}}, audio:false});
            video.srcObject = stream;
            placeholder.style.display = 'none';
            captureBtn.disabled = false;
            await setupTorch();
            clearSuggestions();
            startObjectDetection();
            video.addEventListener('loadedmetadata', () => {
                updateDetectedBox(getFocusRect(video.videoWidth || 1280, video.videoHeight || 720), true);
            }, {once:true});
            setMessage('Camera ready. Center the product, keep a neutral background and avoid shadows before capturing.');
        }catch(e){ setMessage('Could not open camera: ' + e.message); }
    });

    if(torchBtn){
        torchBtn.addEventListener('click', async () => {
            await setTorch(!torchEnabled);
        });
    }

    captureBtn.addEventListener('click', async () => {
        await ensureSession();
        captureBtn.disabled = true;
        searchBtn.disabled = true;
        clearSuggestions();
        const frameCount = 3;
        let stored = 0;

        for(let i = 1; i <= frameCount; i++){
            setMessage('Capturing frame ' + i + ' of ' + frameCount + '...');
            const res = await sendCaptureFrame(i, frameCount);
            const data = await res.json();
            if(data.ok) stored++;
            if(i < frameCount) await wait(180);
        }

        captureBtn.disabled = false;
        if(stored > 0){
            searchBtn.disabled = false;
            setMessage(stored + ' frames captured. The matcher will use the best frame.');
        }else{
            setMessage('Could not capture image.');
        }
    });

    searchBtn.addEventListener('click', async () => {
        setMessage('Searching catalogue...');
        const res = await fetch(routes.match, {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:JSON.stringify({session_token:sessionToken})});
        const data = await res.json();
        if(data.result_url){ window.location.href = data.result_url; return; }
        if(data.matched && data.product_url){ window.location.href = data.product_url; return; }
        if(data.suggestions && data.suggestions.length){
            setMessage('We found possible matches. Select a product or tell us this is not listed yet.');
            renderSuggestions(data.suggestions);
            return;
        }
        clearSuggestions();
        setMessage('No product found. Please submit details so we can review it.');
        modal.hidden = false;
    });

    closeBtn.addEventListener('click', () => modal.hidden = true);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        fd.set('session_token', sessionToken);
        const res = await fetch(routes.unmatched, {method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:fd});
        const data = await res.json();
        if(data.ok){ window.location.href = routes.resultBase + '/' + sessionToken; }
        else{ setMessage(data.message || 'Could not submit request.'); }
    });
})();
</script>
@endsection
