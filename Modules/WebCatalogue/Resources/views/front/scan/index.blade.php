@extends('webcatalogue::front.layouts.app')

@section('content')
<div class="wc-front-container wc-scan-page">
    <section class="wc-front-hero wc-scan-hero">
        <div>
            <span class="wc-front-kicker"><i class="fa-solid fa-camera-viewfinder"></i> Visual Recognition</span>
            <h1>Scan a product</h1>
            <p>Point your camera at a product. For best results, center the object, use a neutral background and avoid strong shadows. If we find it, we open the product page. If not, you can request it and help us identify market demand.</p>
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
                <div id="wcScanPlaceholder" class="wc-scan-placeholder">
                    <i class="fa-solid fa-camera"></i>
                    <strong>Camera not started</strong>
                    <span>Use your mobile camera to capture the product.</span>
                </div>
            </div>
            <div class="wc-scan-actions">
                <button type="button" class="wc-front-btn wc-front-btn-primary" id="wcStartCamera"><i class="fa-solid fa-video"></i> Open camera</button>
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
            <h3>When no match is found</h3>
            <p>We ask for brand, model, reference or a label photo. This creates a WebCatalogue lead that can later be used to contact the brand.</p>
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
.wc-scan-grid{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(280px,.8fr);gap:24px}.wc-scan-camera-card,.wc-scan-side-card,.wc-scan-modal-card{background:var(--wc-card,#fff);border:1px solid var(--wc-border,#e5e7eb);border-radius:var(--wc-radius,5px);box-shadow:0 14px 35px rgba(15,23,42,.08);padding:18px}.wc-scan-video-wrap{height:min(62vh,560px);background:#111;border-radius:var(--wc-radius,5px);display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}.wc-scan-video-wrap video{width:100%;height:100%;object-fit:contain;background:#111}.wc-scan-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#fff;gap:8px;background:linear-gradient(135deg,#111827,#334155)}.wc-scan-placeholder i{font-size:54px;opacity:.8}.wc-scan-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}.wc-scan-actions-end{justify-content:flex-end}.wc-scan-side-card ul{padding:0;margin:18px 0 0;list-style:none}.wc-scan-side-card li{margin:10px 0;color:var(--wc-text,#111827)}.wc-scan-side-card i{color:var(--wc-accent,#c9a96e);margin-right:8px}.wc-scan-message{margin-top:12px;color:var(--wc-muted,#64748b)}.wc-scan-modal{position:fixed;inset:0;background:rgba(15,23,42,.65);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px}.wc-scan-modal[hidden]{display:none}.wc-scan-modal-card{max-width:780px;width:100%;position:relative}.wc-scan-modal-close{position:absolute;right:14px;top:10px;background:transparent;border:0;font-size:28px}.wc-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.wc-scan-modal label{display:flex;flex-direction:column;gap:6px;margin-bottom:12px;font-weight:600}.wc-scan-modal input,.wc-scan-modal textarea{border:1px solid var(--wc-border,#e5e7eb);border-radius:var(--wc-radius,5px);padding:10px;background:#fff}.wc-front-btn[disabled]{opacity:.5;cursor:not-allowed}.wc-scan-focus-overlay{position:absolute;left:50%;top:50%;width:min(68%,520px);height:min(68%,420px);transform:translate(-50%,-50%);z-index:2;border:1px dashed rgba(255,255,255,.7);border-radius:var(--wc-radius,5px);box-shadow:0 0 0 9999px rgba(0,0,0,.22);display:flex;align-items:flex-end;justify-content:center;padding:14px;pointer-events:none;color:#fff;text-shadow:0 1px 8px rgba(0,0,0,.65);font-size:13px;letter-spacing:.02em}.wc-scan-focus-corner{position:absolute;width:34px;height:34px;border-color:var(--wc-accent,#c9a96e);border-style:solid}.wc-scan-focus-corner-tl{left:-2px;top:-2px;border-width:3px 0 0 3px}.wc-scan-focus-corner-tr{right:-2px;top:-2px;border-width:3px 3px 0 0}.wc-scan-focus-corner-bl{left:-2px;bottom:-2px;border-width:0 0 3px 3px}.wc-scan-focus-corner-br{right:-2px;bottom:-2px;border-width:0 3px 3px 0}.wc-scan-focus-tools{margin-top:12px;padding:12px;border:1px solid var(--wc-border,#e5e7eb);border-radius:var(--wc-radius,5px);background:color-mix(in srgb,var(--wc-card,#fff) 88%,var(--wc-accent,#c9a96e) 12%)}.wc-scan-toggle{display:flex;align-items:center;gap:9px;font-weight:700;color:var(--wc-text,#111827);cursor:pointer}.wc-scan-toggle input{width:17px;height:17px;accent-color:var(--wc-accent,#c9a96e)}.wc-scan-focus-tools small{display:block;margin-top:5px;color:var(--wc-muted,#64748b)}@media(max-width:900px){.wc-scan-grid,.wc-form-grid{grid-template-columns:1fr}.wc-scan-focus-overlay{width:76%;height:62%;font-size:12px}}
.wc-scan-suggestions{margin-top:16px;border-top:1px solid var(--wc-border,#e5e7eb);padding-top:16px}.wc-scan-suggestions h3{margin:0 0 4px}.wc-scan-suggestion-help{margin:0 0 12px;color:var(--wc-muted,#64748b)}.wc-scan-suggestion-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:12px}.wc-scan-suggestion-card{text-decoration:none;color:var(--wc-text,#111827);border:1px solid var(--wc-border,#e5e7eb);border-radius:var(--wc-radius,5px);background:var(--wc-surface,#fff);padding:10px;display:flex;flex-direction:column;gap:5px;transition:.18s ease}.wc-scan-suggestion-card:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(15,23,42,.12)}.wc-scan-suggestion-img{height:110px;display:flex;align-items:center;justify-content:center;background:var(--wc-image-bg,#f8fafc);border-radius:var(--wc-radius,5px);overflow:hidden}.wc-scan-suggestion-img img{width:100%;height:100%;object-fit:contain}.wc-scan-suggestion-img i{font-size:34px;color:var(--wc-muted,#64748b)}.wc-scan-suggestion-card small{color:var(--wc-muted,#64748b)}.wc-scan-suggestion-card em{font-style:normal;color:var(--wc-accent,#c9a96e);font-weight:700}
</style>

<script>
(() => {
    const routes = {
        session: @json(route('webcatalogue.front.scan.session', $store->slug)),
        capture: @json(route('webcatalogue.front.scan.capture', $store->slug)),
        match: @json(route('webcatalogue.front.scan.match', $store->slug)),
        unmatched: @json(route('webcatalogue.front.scan.unmatched', $store->slug)),
        resultBase: @json(url('/catalogue/' . $store->slug . '/scan/result')),
    };
    const csrf = @json(csrf_token());
    const video = document.getElementById('wcScanVideo');
    const canvas = document.getElementById('wcScanCanvas');
    const placeholder = document.getElementById('wcScanPlaceholder');
    const msg = document.getElementById('wcScanMessage');
    const suggestionsBox = document.getElementById('wcScanSuggestions');
    const startBtn = document.getElementById('wcStartCamera');
    const captureBtn = document.getElementById('wcCaptureProduct');
    const searchBtn = document.getElementById('wcSearchProduct');
    const focusEnhance = document.getElementById('wcFocusEnhance');
    const modal = document.getElementById('wcUnmatchedModal');
    const closeBtn = document.getElementById('wcCloseUnmatched');
    const tokenInput = document.getElementById('wcSessionToken');
    const form = document.getElementById('wcUnmatchedForm');
    let sessionToken = null;
    let stream = null;

    function setMessage(text){ msg.textContent = text || ''; }
    function clearSuggestions(){ suggestionsBox.hidden = true; suggestionsBox.innerHTML = ''; }

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

    function applyFocusBlur(sourceCanvas){
        const width = sourceCanvas.width;
        const height = sourceCanvas.height;
        if(!width || !height) return;

        const rect = getFocusRect(width, height);
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
            clearSuggestions();
            setMessage('Camera ready. Center the product, keep a neutral background and avoid shadows before capturing.');
        }catch(e){ setMessage('Could not open camera: ' + e.message); }
    });

    captureBtn.addEventListener('click', async () => {
        await ensureSession();
        canvas.width = video.videoWidth || 1280;
        canvas.height = video.videoHeight || 720;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        if(focusEnhance && focusEnhance.checked){
            applyFocusBlur(canvas);
        }
        const photoData = canvas.toDataURL('image/jpeg', .92);
        const res = await fetch(routes.capture, {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:JSON.stringify({session_token:sessionToken,capture_type:'object_photo',photo_data:photoData})});
        const data = await res.json();
        if(data.ok){ searchBtn.disabled = false; clearSuggestions(); setMessage('Product image captured. You can now search the catalogue.'); }
        else{ setMessage(data.message || 'Could not capture image.'); }
    });

    searchBtn.addEventListener('click', async () => {
        setMessage('Searching catalogue...');
        const res = await fetch(routes.match, {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:JSON.stringify({session_token:sessionToken})});
        const data = await res.json();
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
