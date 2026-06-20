<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('[data-upload-zone]').forEach(function(zone){
        const input = zone.querySelector('[data-upload-input]');
        const preview = zone.querySelector('[data-upload-preview]');
        const filename = zone.querySelector('[data-upload-filename]');
        const kind = zone.dataset.uploadKind || 'image';
        const multiple = zone.hasAttribute('data-upload-multiple');

        if (!input || !preview) {
            return;
        }

        const setFiles = function(files){
            const selectedFiles = Array.from(files || []);

            if (!selectedFiles.length) {
                return;
            }

            if (filename) {
                filename.textContent = multiple
                    ? selectedFiles.length + ' ficheiro(s) selecionado(s)'
                    : selectedFiles[0].name;
                filename.classList.add('is-selected');
            }

            preview.innerHTML = '';

            selectedFiles.slice(0, multiple ? 12 : 1).forEach(function(file){
                const url = URL.createObjectURL(file);

                if (kind === 'video' || file.type.indexOf('video/') === 0) {
                    const video = document.createElement('video');
                    video.src = url;
                    video.controls = true;
                    video.muted = true;
                    preview.appendChild(video);
                    return;
                }

                const image = document.createElement('img');
                image.src = url;
                image.alt = file.name;
                preview.appendChild(image);
            });
        };

        input.addEventListener('change', function(){
            setFiles(input.files);
        });

        zone.addEventListener('dragover', function(event){
            event.preventDefault();
            zone.classList.add('is-dragover');
        });

        zone.addEventListener('dragleave', function(){
            zone.classList.remove('is-dragover');
        });

        zone.addEventListener('drop', function(event){
            event.preventDefault();
            zone.classList.remove('is-dragover');

            if (event.dataTransfer?.files?.length) {
                input.files = event.dataTransfer.files;
                setFiles(input.files);
            }
        });
    });

});
function submitReviewItemForm(form, reason) {
    const reviewItem = form.closest('[data-review-item]');
    const reasonInput = form.querySelector('[data-review-reject-reason]');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value || '';
    const formData = new FormData(form);

    if (typeof reason === 'string' && reasonInput) {
        reasonInput.value = reason;
        formData.set('reason', reason);
    }

    form.querySelectorAll('button').forEach(button => button.disabled = true);

    return fetch(form.action, {
        method: form.method || 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Nao foi possivel guardar a validacao.');
            }

            return response.json();
        })
        .then(data => {
            if (!reviewItem) {
                return data;
            }

            const reviewArea = reviewItem.closest('.pc-review-area-card');
            reviewItem.dataset.reviewStatus = data.item_status || '';

            if (data.item_status === 'approved') {
                reviewItem.classList.add('is-approved');
            } else {
                reviewItem.classList.remove('is-approved');
            }

            const reasonOutput = reviewItem.querySelector('[data-review-reason-output]');
            if (reasonOutput) {
                reasonOutput.textContent = data.reason || '';
            }

            if (reviewArea) {
                const areaAction = reviewArea.querySelector('[data-review-area-action]');
                const areaHead = reviewArea.querySelector('[data-review-area-head]');
                const areaKey = reviewArea.getAttribute('data-review-panel');

                if (areaAction && data.area_status === 'approved') {
                    areaAction.classList.add('is-hidden');
                } else if (areaAction && data.area_status !== 'approved') {
                    areaAction.classList.remove('is-hidden');
                }

                if (areaHead && data.area_status === 'approved') {
                    areaHead.classList.add('is-hidden');
                } else if (areaHead && data.area_status !== 'approved') {
                    areaHead.classList.remove('is-hidden');
                }

                if (areaKey) {
                    document.querySelectorAll('[data-review-panel="' + areaKey + '"] [data-review-area-status], [data-review-tab="' + areaKey + '"] [data-review-area-status]').forEach(statusBadge => {
                        statusBadge.textContent = data.area_status || '';
                        statusBadge.classList.toggle('pc-badge--success', data.area_status === 'approved');
                        statusBadge.classList.toggle('pc-badge--danger', data.area_status === 'rejected');
                        statusBadge.classList.toggle('pc-badge--warning', data.area_status !== 'approved' && data.area_status !== 'rejected');
                    });
                }
            }

            return data;
        })
        .catch(error => {
            if (window.Swal) {
                Swal.fire({ title: 'Erro', text: error.message, icon: 'error' });
            } else {
                alert(error.message);
            }
        })
        .finally(() => {
            form.querySelectorAll('button').forEach(button => button.disabled = false);
        });
}
document.addEventListener('submit', function(event){
    const reviewForm = event.target.closest('[data-review-item-form]');
    if (reviewForm) {
        event.preventDefault();
        submitReviewItemForm(reviewForm);
        return;
    }

    const form = event.target.closest('[data-confirm]');
    if(!form) return;
    const message = form.getAttribute('data-confirm') || 'Confirmar ação?';
    if(window.Swal){
        event.preventDefault();
        Swal.fire({title:message,icon:'warning',showCancelButton:true,confirmButtonText:'Sim',cancelButtonText:'Cancelar'}).then(r=>{ if(r.isConfirmed) form.submit(); });
    } else if(!confirm(message)) { event.preventDefault(); }
});
document.addEventListener('click', function(event){
    const previewOpen = event.target.closest('[data-product-preview-open]');
    if (previewOpen) {
        const modal = document.querySelector('[data-product-preview-modal]');
        if (modal) {
            modal.hidden = false;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
        }
        return;
    }

    const previewClose = event.target.closest('[data-product-preview-close]');
    if (previewClose) {
        const modal = previewClose.closest('[data-product-preview-modal]') || document.querySelector('[data-product-preview-modal]');
        if (modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            modal.hidden = true;
            document.body.classList.remove('modal-open');
        }
        return;
    }

    const tabButton = event.target.closest('[data-review-tab]');
    if (tabButton) {
        const tabsRoot = tabButton.closest('[data-review-tabs]');
        const targetKey = tabButton.getAttribute('data-review-tab');

        if (!tabsRoot || !targetKey) {
            return;
        }

        tabsRoot.querySelectorAll('[data-review-tab]').forEach(button => {
            const isActive = button === tabButton;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        tabsRoot.querySelectorAll('[data-review-panel]').forEach(panel => {
            const isActive = panel.getAttribute('data-review-panel') === targetKey;
            panel.classList.toggle('is-active', isActive);
            panel.hidden = !isActive;
        });

        return;
    }

    if (event.target.closest('[data-review-area-action]')) {
        event.stopPropagation();
    }

    const button = event.target.closest('[data-review-reject]');
    if (!button) {
        return;
    }

    const form = button.closest('[data-review-reject-form]');
    if (!form) {
        return;
    }

    const reasonInput = form.querySelector('[data-review-reject-reason]');
    const field = button.getAttribute('data-review-field') || 'campo';
    const currentReason = button.getAttribute('data-review-current-reason') || (reasonInput ? reasonInput.value : '');
    const submitReject = function(reason) {
        if (reasonInput) {
            reasonInput.value = reason;
        }
        submitReviewItemForm(form, reason);
    };

    if (!window.Swal) {
        const reason = window.prompt('Motivo da recusa para ' + field, currentReason);
        if (reason !== null && reason.trim() !== '') {
            submitReject(reason.trim());
        }
        return;
    }

    Swal.fire({
        title: 'Recusar ' + field,
        input: 'textarea',
        inputValue: currentReason,
        inputPlaceholder: 'Indica a razão da recusa',
        inputAttributes: {
            'aria-label': 'Motivo da recusa'
        },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Recusar',
        cancelButtonText: 'Cancelar',
        preConfirm: function(value) {
            if (!value || value.trim() === '') {
                Swal.showValidationMessage('Indica a razão da recusa.');
                return false;
            }

            return value.trim();
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            submitReject(result.value);
        }
    });
});
document.addEventListener('keydown', function(event){
    if (event.key !== 'Escape') {
        return;
    }

    const modal = document.querySelector('[data-product-preview-modal].is-open');
    if (modal) {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modal.hidden = true;
        document.body.classList.remove('modal-open');
    }
});
</script>
<script>
document.addEventListener('input', function(event){
    if (!event.target.matches('#width, #height, #depth')) {
        return;
    }

    const output = document.querySelector('[data-volumetric-weight]');
    if (!output) {
        return;
    }

    const width = parseFloat(document.getElementById('width')?.value || '0');
    const height = parseFloat(document.getElementById('height')?.value || '0');
    const depth = parseFloat(document.getElementById('depth')?.value || '0');
    const volumetric = width > 0 && height > 0 && depth > 0 ? (width * height * depth) / 5000 : 0;

    output.textContent = volumetric.toFixed(3) + ' kg';
});

document.addEventListener('DOMContentLoaded', function(){
    const width = document.getElementById('width');
    if (width) {
        width.dispatchEvent(new Event('input', { bubbles: true }));
    }

    const purchaseOriginal = document.getElementById('purchase_price_original');
    const currencyRate = document.getElementById('currency_rate_to_eur');
    const supplierRecommendedSale = document.getElementById('supplier_recommended_sale_price');
    const vatRule = document.getElementById('tax_rule');
    const baseCost = document.getElementById('purchase_price') || document.getElementById('base_cost');
    const basePrice = document.getElementById('base_sale_price') || document.getElementById('base_price');
    const desiredDiscount = document.getElementById('desired_discount');
    const profitMargin = document.querySelector('[data-profit-margin]');
    const costEurOutput = document.querySelector('[data-price-cost-eur-output]');
    const marginOutput = document.querySelector('[data-price-margin-output]');
    const profitOutput = document.querySelector('[data-price-profit-output]');
    const saleVatOutput = document.querySelector('[data-price-sale-vat-output]');
    const dilutedProfitOutput = document.querySelector('[data-price-diluted-profit-output]');
    const discountOutput = document.querySelector('[data-price-discount-output]');

    if (baseCost && basePrice) {
        function syncConvertedPrices() {
            if (!currencyRate) {
                return;
            }

            const rate = parseFloat(currencyRate.value || '0');
            if (rate <= 0) {
                return;
            }

            const originalCost = parseFloat(purchaseOriginal?.value || '0');
            const originalSaleRaw = supplierRecommendedSale?.value ?? '';
            const originalSale = parseFloat(originalSaleRaw || '0');
            if (purchaseOriginal) {
                baseCost.value = (Math.max(originalCost, 0) * rate).toFixed(4);
            }
            if (supplierRecommendedSale && originalSaleRaw !== '') {
                basePrice.value = (Math.max(originalSale, 0) * rate).toFixed(4);
            }
        }

        function priceValues() {
            const cost = parseFloat(baseCost.value || '0');
            const price = parseFloat(basePrice.value || '0');
            const vatRate = parseFloat(vatRule?.options?.[vatRule.selectedIndex]?.dataset?.vatRate || '0.23');

            return { cost, price, vatRate };
        }

        function syncProfitMargin() {
            const { cost, price } = priceValues();
            const margin = cost > 0 && price > 0 ? (((price - cost) / price) * 100) : 0;
            if (cost > 0 && price > 0) {
                if (profitMargin) {
                    profitMargin.value = margin.toFixed(2);
                }
            } else if (profitMargin) {
                profitMargin.value = '';
            }

            syncPriceSummary();
        }

        function syncPriceSummary() {
            const { cost, price, vatRate } = priceValues();
            const currentMargin = profitMargin
                ? parseFloat(profitMargin.value || '0')
                : (cost > 0 && price > 0 ? ((price - cost) / price) * 100 : 0);
            const grossProfit = price > 0 && cost > 0 ? price - cost : 0;
            const saleWithVat = price > 0 ? price * (1 + Math.max(vatRate, 0)) : 0;
            const dilutedProfit = saleWithVat > 0 && grossProfit > 0 ? (grossProfit / saleWithVat) * 100 : 0;
            const discountPercent = Math.min(Math.max(parseFloat(desiredDiscount?.value || '0'), 0), 100);
            const discountedWithVat = saleWithVat > 0 ? saleWithVat * (1 - discountPercent / 100) : 0;

            if (costEurOutput) {
                costEurOutput.textContent = cost > 0 ? cost.toFixed(2) : '0.00';
            }
            if (marginOutput) {
                marginOutput.textContent = currentMargin > 0 ? currentMargin.toFixed(2) + '%' : '0.00%';
            }
            if (profitOutput) {
                profitOutput.textContent = grossProfit > 0 ? grossProfit.toFixed(2) : '0.00';
            }
            if (saleVatOutput) {
                saleVatOutput.textContent = saleWithVat > 0 ? saleWithVat.toFixed(2) : '0.00';
            }
            if (dilutedProfitOutput) {
                dilutedProfitOutput.textContent = dilutedProfit > 0 ? dilutedProfit.toFixed(2) + '%' : '0.00%';
            }
            if (discountOutput) {
                discountOutput.textContent = discountedWithVat > 0 ? discountedWithVat.toFixed(2) : '0.00';
            }
        }

        purchaseOriginal?.addEventListener('input', function(){
            syncConvertedPrices();
            syncProfitMargin();
        });
        supplierRecommendedSale?.addEventListener('input', function(){
            syncConvertedPrices();
            syncProfitMargin();
        });
        currencyRate?.addEventListener('input', function(){
            syncConvertedPrices();
            syncProfitMargin();
        });
        baseCost.addEventListener('input', syncProfitMargin);
        basePrice.addEventListener('input', syncProfitMargin);
        desiredDiscount?.addEventListener('input', syncPriceSummary);
        vatRule?.addEventListener('change', syncPriceSummary);
        profitMargin?.addEventListener('input', syncPriceSummary);
        if (purchaseOriginal && !purchaseOriginal.value && baseCost.value) {
            purchaseOriginal.value = baseCost.value;
        }
        syncConvertedPrices();
        syncProfitMargin();
    }

    const sku = document.getElementById('product_sku');
    const internalSku = document.getElementById('product_internal_sku');

    if (sku && internalSku) {
        let internalWasEdited = internalSku.value.trim() !== '';

        internalSku.addEventListener('input', function(){
            internalWasEdited = internalSku.value.trim() !== '';
        });

        sku.addEventListener('input', function(){
            if (!internalWasEdited) {
                internalSku.value = sku.value;
            }
        });

        if (!internalSku.value.trim() && sku.value.trim()) {
            internalSku.value = sku.value;
        }
    }

    document.querySelectorAll('[data-active-state-toggle]').forEach(function(toggle){
        const label = toggle.closest('.pc-switch-field')?.querySelector('[data-active-state-label]');

        function syncActiveLabel() {
            if (label) {
                label.textContent = toggle.checked ? 'Ativo' : 'Inativo';
            }
        }

        toggle.addEventListener('change', syncActiveLabel);
        syncActiveLabel();
    });

    document.querySelectorAll('[data-purchase-product-type]').forEach(function(select){
        const sections = Array.from(document.querySelectorAll('[data-purchase-type-section]'));

        function syncPurchaseTypeSections() {
            sections.forEach(function(section){
                const active = section.getAttribute('data-purchase-type-section') === select.value;
                section.hidden = !active;
                section.querySelectorAll('input, select, textarea, button').forEach(function(field){
                    if (field.matches('[data-combination-add], [data-combination-remove], [data-pack-add], [data-pack-remove]')) {
                        field.disabled = !active;
                        return;
                    }

                    field.disabled = !active;
                });
            });
        }

        select.addEventListener('change', syncPurchaseTypeSections);
        syncPurchaseTypeSections();
    });

    document.querySelectorAll('[data-sales-workbench]').forEach(function(workbench){
        const cost = parseFloat(workbench.getAttribute('data-sales-cost') || '0');
        const salePrice = workbench.querySelector('#sale_price');
        const promoPrice = workbench.querySelector('#promo_price');
        const discountLimit = workbench.querySelector('#discount_limit');
        const marginInput = workbench.querySelector('[data-sales-margin-input]');
        const marginOutput = workbench.querySelector('[data-sales-margin-output]');
        const profitOutput = workbench.querySelector('[data-sales-profit-output]');
        const discountOutput = workbench.querySelector('[data-sales-discount-output]');

        function money(value) {
            return value > 0 ? value.toFixed(2) : '0.00';
        }

        function syncSalesSummary() {
            const base = parseFloat(salePrice?.value || '0');
            const promo = parseFloat(promoPrice?.value || '0');
            const effective = promo > 0 ? promo : base;
            const margin = cost > 0 && effective > 0 ? ((effective - cost) / effective) * 100 : 0;
            const profit = cost > 0 && effective > 0 ? effective - cost : 0;
            const promoDiscount = base > 0 && promo > 0 && promo < base ? ((base - promo) / base) * 100 : 0;
            const limit = parseFloat(discountLimit?.value || '0');
            const visibleDiscount = Math.max(promoDiscount, limit > 0 ? limit : 0);

            if (marginInput) {
                marginInput.value = margin > 0 ? margin.toFixed(2) : '';
            }
            if (marginOutput) {
                marginOutput.textContent = margin > 0 ? margin.toFixed(2) + '%' : '0.00%';
            }
            if (profitOutput) {
                profitOutput.textContent = money(profit);
            }
            if (discountOutput) {
                discountOutput.textContent = visibleDiscount > 0 ? visibleDiscount.toFixed(2) + '%' : '0.00%';
            }
        }

        salePrice?.addEventListener('input', syncSalesSummary);
        promoPrice?.addEventListener('input', syncSalesSummary);
        discountLimit?.addEventListener('input', syncSalesSummary);
        syncSalesSummary();
    });

    document.querySelectorAll('[data-combination-builder]').forEach(function(builder){
        const list = builder.querySelector('[data-combination-list]');
        const template = builder.querySelector('[data-combination-template]');
        const addButton = builder.querySelector('[data-combination-add]');
        const baseReference = document.getElementById('reference');

        const codeMap = {
            condition: {
                mint: 'M',
                near_mint: 'NM',
                excellent: 'EX',
                good: 'GD',
                light_played: 'LP',
                played: 'PL',
                poor: 'PO'
            },
            language: {
                english: 'EN',
                portuguese: 'PT',
                spanish: 'ES',
                french: 'FR',
                german: 'DE',
                italian: 'IT',
                japanese: 'JP',
                korean: 'KR',
                russian: 'RU',
                simplified_chinese: 'SC',
                traditional_chinese: 'TC'
            },
            finish: {
                non_foil: 'NF',
                traditional_foil: 'TF',
                etched_foil: 'EF',
                glossy: 'GL'
            },
            version_treatment: {
                regular: 'REG',
                extended_art: 'EA',
                borderless: 'BL',
                showcase: 'SH',
                retro_frame: 'RF',
                full_art: 'FA',
                textured_foil: 'TXF',
                surge_foil: 'SF',
                galaxy_foil: 'GF',
                confetti_foil: 'CF',
                serialized: 'SER',
                promo: 'PR',
                prerelease_promo: 'PP',
                buy_a_box_promo: 'BAB',
                bundle_promo: 'BP',
                store_championship_promo: 'SCP'
            }
        };

        function nextIndex() {
            return list ? list.querySelectorAll('[data-combination-row]').length : 0;
        }

        function normalizeCode(value) {
            return String(value || '')
                .trim()
                .replace(/['’]/g, '')
                .replace(/[^a-zA-Z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '')
                .toLowerCase();
        }

        function codeForSelect(select) {
            const match = select.name.match(/\[attributes]\[([^\]]+)]/);
            const attribute = match ? match[1] : '';
            const value = normalizeCode(select.value);

            if (!value) {
                return '';
            }

            return codeMap[attribute]?.[value] || value.split('_').map(function(part){
                return part.charAt(0).toUpperCase();
            }).join('').slice(0, 6);
        }

        function currentBaseReference() {
            const base = baseReference?.value || '';

            return base.trim().replace(/\s+/g, '-').toUpperCase();
        }

        function generatedSkuForRow(row) {
            const base = currentBaseReference();
            const codes = Array.from(row.querySelectorAll('select[name*="[attributes]"]'))
                .map(codeForSelect)
                .filter(Boolean);

            return [base].concat(codes).filter(Boolean).join('-');
        }

        function syncRowSku(row, force) {
            const skuInput = row.querySelector('input[name$="[sku]"]');
            if (!skuInput) {
                return;
            }

            const generated = generatedSkuForRow(row);
            const previousGenerated = skuInput.getAttribute('data-generated-sku') || '';
            const canUpdate = force || !skuInput.value.trim() || skuInput.value === previousGenerated;

            if (generated && canUpdate) {
                skuInput.value = generated;
                skuInput.setAttribute('data-generated-sku', generated);
            }
        }

        function syncAllCombinationSkus(force) {
            if (!list) {
                return;
            }

            list.querySelectorAll('[data-combination-row]').forEach(function(row){
                syncRowSku(row, force);
            });
        }

        function syncRemoveButtons() {
            const rows = list ? list.querySelectorAll('[data-combination-row]') : [];
            rows.forEach(function(row){
                const button = row.querySelector('[data-combination-remove]');
                if (button) {
                    button.disabled = rows.length <= 1;
                }
            });
        }

        addButton?.addEventListener('click', function(){
            if (!list || !template) {
                return;
            }

            const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex()));
            list.insertAdjacentHTML('beforeend', html);
            syncRemoveButtons();
            syncAllCombinationSkus(true);
        });

        list?.addEventListener('click', function(event){
            const button = event.target.closest('[data-combination-remove]');
            if (!button) {
                return;
            }

            const row = button.closest('[data-combination-row]');
            if (row && list.querySelectorAll('[data-combination-row]').length > 1) {
                row.remove();
                syncRemoveButtons();
            }
        });

        list?.addEventListener('change', function(event){
            const row = event.target.closest('[data-combination-row]');
            if (row && event.target.matches('select[name*="[attributes]"]')) {
                syncRowSku(row, true);
            }
        });

        list?.addEventListener('input', function(event){
            if (event.target.matches('input[name$="[sku]"]')) {
                event.target.removeAttribute('data-generated-sku');
            }
        });

        baseReference?.addEventListener('input', function(){
            syncAllCombinationSkus(false);
        });

        syncRemoveButtons();
        syncAllCombinationSkus(false);
    });

    document.querySelectorAll('[data-pack-builder]').forEach(function(builder){
        const list = builder.querySelector('[data-pack-list]');
        const template = builder.querySelector('[data-pack-template]');
        const addButton = builder.querySelector('[data-pack-add]');
        let suggestions = [];
        let activeInput = null;
        const suggestionMenu = document.createElement('div');
        suggestionMenu.className = 'pc-pack-suggestion-menu';
        suggestionMenu.hidden = true;
        builder.appendChild(suggestionMenu);

        try {
            suggestions = JSON.parse(builder.getAttribute('data-pack-suggestions') || '[]');
        } catch (error) {
            suggestions = [];
        }

        if (!Array.isArray(suggestions)) {
            suggestions = [];
        }

        function nextIndex() {
            return list ? list.querySelectorAll('[data-pack-row]').length : 0;
        }

        function syncRemoveButtons() {
            const rows = list ? list.querySelectorAll('[data-pack-row]') : [];
            rows.forEach(function(row){
                const button = row.querySelector('[data-pack-remove]');
                if (button) {
                    button.disabled = rows.length <= 1;
                }
            });
        }

        function normalizedText(value) {
            return String(value || '').trim().toLowerCase();
        }

        function hideSuggestions() {
            suggestionMenu.hidden = true;
            suggestionMenu.innerHTML = '';
            activeInput = null;
        }

        function renderSuggestionEmpty(input, message) {
            activeInput = input;
            suggestionMenu.innerHTML = '';

            const empty = document.createElement('div');
            empty.className = 'pc-pack-suggestion-empty';
            empty.textContent = message;
            suggestionMenu.appendChild(empty);

            positionSuggestionMenu(input);
            suggestionMenu.hidden = false;
        }

        function positionSuggestionMenu(input) {
            const inputRect = input.getBoundingClientRect();
            const builderRect = builder.getBoundingClientRect();

            suggestionMenu.style.left = (inputRect.left - builderRect.left + builder.scrollLeft) + 'px';
            suggestionMenu.style.top = (inputRect.bottom - builderRect.top + builder.scrollTop + 4) + 'px';
            suggestionMenu.style.width = inputRect.width + 'px';
        }

        function renderSuggestions(input) {
            const term = normalizedText(input.value);
            activeInput = input;

            if (!term || suggestions.length === 0) {
                if (term && suggestions.length === 0) {
                    renderSuggestionEmpty(input, 'Sem produtos com referencia disponivel.');
                } else {
                    hideSuggestions();
                }
                return;
            }

            const matches = suggestions
                .filter(function(item){
                    const value = normalizedText(item.value);
                    const label = normalizedText(item.label);

                    return value.includes(term) || label.includes(term);
                })
                .slice(0, 8);

            if (matches.length === 0) {
                renderSuggestionEmpty(input, 'Sem resultados para "' + input.value + '".');
                return;
            }

            suggestionMenu.innerHTML = '';
            matches.forEach(function(item){
                const value = String(item.value || '');
                const label = String(item.label || value);
                const button = document.createElement('button');
                const strong = document.createElement('strong');
                const span = document.createElement('span');

                button.type = 'button';
                button.setAttribute('data-pack-suggestion-value', value);
                strong.textContent = value;
                span.textContent = label;
                button.appendChild(strong);
                button.appendChild(span);
                suggestionMenu.appendChild(button);
            });

            positionSuggestionMenu(input);
            suggestionMenu.hidden = false;
        }

        addButton?.addEventListener('click', function(){
            if (!list || !template) {
                return;
            }

            const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex()));
            list.insertAdjacentHTML('beforeend', html);
            syncRemoveButtons();
        });

        suggestionMenu.addEventListener('mousedown', function(event){
            const button = event.target.closest('[data-pack-suggestion-value]');
            if (!button || !activeInput) {
                return;
            }

            event.preventDefault();
            activeInput.value = button.getAttribute('data-pack-suggestion-value') || '';
            activeInput.dispatchEvent(new Event('input', { bubbles: true }));
            hideSuggestions();
        });

        list?.addEventListener('click', function(event){
            const button = event.target.closest('[data-pack-remove]');
            if (!button) {
                return;
            }

            const row = button.closest('[data-pack-row]');
            if (row && list.querySelectorAll('[data-pack-row]').length > 1) {
                row.remove();
                syncRemoveButtons();
            }
        });

        list?.addEventListener('input', function(event){
            if (event.target.matches('[data-pack-reference-input]')) {
                renderSuggestions(event.target);
            }
        });

        list?.addEventListener('focusin', function(event){
            if (event.target.matches('[data-pack-reference-input]')) {
                renderSuggestions(event.target);
            }
        });

        list?.addEventListener('focusout', function(event){
            if (event.target.matches('[data-pack-reference-input]')) {
                window.setTimeout(hideSuggestions, 120);
            }
        });

        window.addEventListener('resize', function(){
            if (!suggestionMenu.hidden && activeInput) {
                positionSuggestionMenu(activeInput);
            }
        });

        syncRemoveButtons();
    });

    document.querySelectorAll('[data-sales-relations]').forEach(function(section){
        let suggestions = [];
        let activeInput = null;
        const suggestionMenu = document.createElement('div');
        suggestionMenu.className = 'pc-pack-suggestion-menu pc-sales-suggestion-menu';
        suggestionMenu.hidden = true;
        section.appendChild(suggestionMenu);

        try {
            suggestions = JSON.parse(section.getAttribute('data-product-suggestions') || '[]');
        } catch (error) {
            suggestions = [];
        }

        if (!Array.isArray(suggestions)) {
            suggestions = [];
        }

        function normalizedText(value) {
            return String(value || '').trim().toLowerCase();
        }

        function hideSuggestions() {
            suggestionMenu.hidden = true;
            suggestionMenu.innerHTML = '';
            activeInput = null;
        }

        function positionSuggestionMenu(input) {
            const inputRect = input.getBoundingClientRect();
            const sectionRect = section.getBoundingClientRect();

            suggestionMenu.style.left = (inputRect.left - sectionRect.left + section.scrollLeft) + 'px';
            suggestionMenu.style.top = (inputRect.bottom - sectionRect.top + section.scrollTop + 4) + 'px';
            suggestionMenu.style.width = inputRect.width + 'px';
        }

        function renderSuggestionEmpty(input, message) {
            activeInput = input;
            suggestionMenu.innerHTML = '';

            const empty = document.createElement('div');
            empty.className = 'pc-pack-suggestion-empty';
            empty.textContent = message;
            suggestionMenu.appendChild(empty);

            positionSuggestionMenu(input);
            suggestionMenu.hidden = false;
        }

        function renderSuggestions(input) {
            const term = normalizedText(input.value);
            activeInput = input;

            if (!term || suggestions.length === 0) {
                if (term && suggestions.length === 0) {
                    renderSuggestionEmpty(input, 'Sem produtos aprovados disponiveis nesta loja.');
                } else {
                    hideSuggestions();
                }
                return;
            }

            const matches = suggestions
                .filter(function(item){
                    const value = normalizedText(item.value);
                    const label = normalizedText(item.label);

                    return value.includes(term) || label.includes(term);
                })
                .slice(0, 8);

            if (matches.length === 0) {
                renderSuggestionEmpty(input, 'Sem resultados para "' + input.value + '".');
                return;
            }

            suggestionMenu.innerHTML = '';
            matches.forEach(function(item){
                const value = String(item.value || '');
                const label = String(item.label || value);
                const button = document.createElement('button');
                const strong = document.createElement('strong');
                const span = document.createElement('span');

                button.type = 'button';
                button.setAttribute('data-sales-suggestion-value', value);
                strong.textContent = value;
                span.textContent = label;
                button.appendChild(strong);
                button.appendChild(span);
                suggestionMenu.appendChild(button);
            });

            positionSuggestionMenu(input);
            suggestionMenu.hidden = false;
        }

        function nextRecommendedIndex() {
            return section.querySelectorAll('[data-sales-recommendation-list] [data-sales-reference-row]').length;
        }

        function nextBundleIndex() {
            return section.querySelectorAll('[data-sales-upsell-row]').length;
        }

        function syncRecommendationButtons() {
            const rows = Array.from(section.querySelectorAll('[data-sales-recommendation-list] [data-sales-reference-row]'));
            const addButton = section.querySelector('[data-sales-recommendation-add]');

            rows.forEach(function(row){
                const button = row.querySelector('[data-sales-reference-remove]');
                if (button) {
                    button.disabled = false;
                }
            });

            if (addButton) {
                addButton.disabled = rows.length >= 6;
            }
        }

        section.querySelector('[data-sales-recommendation-add]')?.addEventListener('click', function(){
            const list = section.querySelector('[data-sales-recommendation-list]');
            const template = section.querySelector('[data-sales-recommendation-template]');
            if (!list || !template || list.querySelectorAll('[data-sales-reference-row]').length >= 6) {
                return;
            }

            list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(nextRecommendedIndex())));
            syncRecommendationButtons();
        });

        section.addEventListener('click', function(event){
            const removeReference = event.target.closest('[data-sales-reference-remove]');
            if (removeReference) {
                const recommendedRow = removeReference.closest('[data-sales-recommendation-list] [data-sales-reference-row]');
                const upsellRow = removeReference.closest('[data-sales-upsell-products] [data-sales-reference-row]');

                if (recommendedRow) {
                    recommendedRow.remove();
                    syncRecommendationButtons();
                    return;
                }

                if (upsellRow) {
                    upsellRow.remove();
                    return;
                }
            }

            const addBundle = event.target.closest('[data-sales-upsell-add]');
            if (addBundle) {
                const list = section.querySelector('[data-sales-upsell-list]');
                const template = section.querySelector('[data-sales-upsell-template]');
                if (!list || !template) {
                    return;
                }

                list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__BUNDLE__', String(nextBundleIndex())));
                return;
            }

            const removeBundle = event.target.closest('[data-sales-upsell-remove]');
            if (removeBundle) {
                const row = removeBundle.closest('[data-sales-upsell-row]');
                if (row) {
                    row.remove();
                }
                return;
            }

            const addBundleProduct = event.target.closest('[data-sales-upsell-product-add]');
            if (addBundleProduct) {
                const bundle = addBundleProduct.closest('[data-sales-upsell-row]');
                const list = bundle ? bundle.querySelector('[data-sales-upsell-products]') : null;
                if (!bundle || !list || list.querySelectorAll('[data-sales-reference-row]').length >= 6) {
                    return;
                }

                const bundleIndexMatch = (bundle.querySelector('input[name*="[title]"]')?.name || '').match(/upsell_bundles\[([^\]]+)]/);
                const bundleIndex = bundleIndexMatch ? bundleIndexMatch[1] : String(nextBundleIndex());
                const productIndex = list.querySelectorAll('[data-sales-reference-row]').length;
                const row = document.createElement('div');
                row.className = 'pc-sales-reference-row';
                row.setAttribute('data-sales-reference-row', '');
                row.innerHTML = '<input class="pc-input" name="upsell_bundles[' + bundleIndex + '][products][' + productIndex + ']" data-sales-reference-input autocomplete="off" placeholder="Referencia do produto"><button type="button" class="pc-icon-action pc-icon-action--danger" data-sales-reference-remove title="Remover produto"><i class="fa-solid fa-xmark"></i></button>';
                list.appendChild(row);
            }
        });

        section.addEventListener('input', function(event){
            if (event.target.matches('[data-sales-reference-input]')) {
                renderSuggestions(event.target);
            }
        });

        section.addEventListener('focusin', function(event){
            if (event.target.matches('[data-sales-reference-input]')) {
                renderSuggestions(event.target);
            }
        });

        section.addEventListener('focusout', function(event){
            if (event.target.matches('[data-sales-reference-input]')) {
                window.setTimeout(hideSuggestions, 120);
            }
        });

        suggestionMenu.addEventListener('mousedown', function(event){
            const button = event.target.closest('[data-sales-suggestion-value]');
            if (!button || !activeInput) {
                return;
            }

            event.preventDefault();
            activeInput.value = button.getAttribute('data-sales-suggestion-value') || '';
            activeInput.dispatchEvent(new Event('input', { bubbles: true }));
            hideSuggestions();
        });

        window.addEventListener('resize', function(){
            if (!suggestionMenu.hidden && activeInput) {
                positionSuggestionMenu(activeInput);
            }
        });

        syncRecommendationButtons();
    });

    document.querySelectorAll('[data-mana-cost-builder]').forEach(function(builder){
        const wrapper = builder.closest('.pc-characteristic-field') || builder.parentElement;
        const input = wrapper ? wrapper.querySelector('[data-mana-cost-input]') : null;
        const selected = builder.querySelector('[data-mana-cost-selected]');
        const remove = builder.querySelector('[data-mana-remove]');
        const clear = builder.querySelector('[data-mana-clear]');
        const manaValueTarget = input?.getAttribute('data-mana-value-target') || '';
        const manaValueInput = manaValueTarget
            ? document.querySelector('[data-mana-value-input="' + manaValueTarget + '"]')
            : null;
        let tokens = parseManaCost(input?.value || '');

        function parseManaCost(value) {
            const matches = String(value || '').match(/\{([^}]+)\}/g);

            if (matches && matches.length) {
                return matches.map(function(match){
                    return match.replace(/[{}]/g, '').trim().toUpperCase();
                }).filter(Boolean);
            }

            return String(value || '')
                .split(/[\s,]+/)
                .map(function(token){ return token.trim().replace(/[{}]/g, '').toUpperCase(); })
                .filter(Boolean);
        }

        function tokenValue(token) {
            const normalized = String(token || '').toUpperCase();

            if (/^\d+$/.test(normalized)) {
                return parseInt(normalized, 10);
            }

            if (['W', 'U', 'B', 'R', 'G', 'C'].includes(normalized)) {
                return 1;
            }

            return 0;
        }

        function renderManaSymbolContent(element, token) {
            const normalized = String(token || '').toUpperCase();

            element.innerHTML = '';

            if (['W', 'U', 'B', 'R', 'G', 'C'].includes(normalized)) {
                const image = document.createElement('img');
                image.src = '/images/mtg/custom_images/' + normalized + '.svg';
                image.alt = normalized;
                element.appendChild(image);
                return;
            }

            const label = document.createElement('span');
            label.textContent = normalized;
            element.appendChild(label);
        }

        function renderManaCost() {
            if (input) {
                input.value = tokens.map(function(token){ return '{' + token + '}'; }).join('');
            }

            if (manaValueInput) {
                manaValueInput.value = tokens.reduce(function(total, token){
                    return total + tokenValue(token);
                }, 0);
            }

            if (!selected) {
                return;
            }

            selected.innerHTML = '';

            if (!tokens.length) {
                const empty = document.createElement('span');
                empty.className = 'text-muted';
                empty.textContent = 'Sem custo definido';
                selected.appendChild(empty);
                return;
            }

            tokens.forEach(function(token){
                const chip = document.createElement('span');
                chip.className = 'pc-mana-symbol pc-mana-symbol--selected';
                renderManaSymbolContent(chip, token);
                selected.appendChild(chip);
            });
        }

        builder.addEventListener('click', function(event){
            const symbol = event.target.closest('[data-mana-symbol]');

            if (symbol) {
                tokens.push(String(symbol.getAttribute('data-mana-symbol') || '').toUpperCase());
                renderManaCost();
                return;
            }

            if (event.target.closest('[data-mana-remove]')) {
                tokens.pop();
                renderManaCost();
                return;
            }

            if (event.target.closest('[data-mana-clear]')) {
                tokens = [];
                renderManaCost();
            }
        });

        renderManaCost();
    });

    document.querySelectorAll('[data-characteristic-picker]').forEach(function(picker){
        const wrapper = picker.closest('.pc-form-span-6') || picker.parentElement;
        const select = wrapper ? wrapper.querySelector('[data-characteristic-picker-select]') : null;
        const availableList = picker.querySelector('[data-characteristic-picker-available]');
        const selectedList = picker.querySelector('[data-characteristic-picker-selected]');
        const search = picker.querySelector('[data-characteristic-picker-search]');
        const customInput = picker.querySelector('[data-characteristic-picker-custom-input]') || search;
        const customAdd = picker.querySelector('[data-characteristic-picker-custom-add]');
        const availableCount = picker.querySelector('[data-characteristic-picker-count="available"]');
        const selectedCount = picker.querySelector('[data-characteristic-picker-count="selected"]');
        const mode = picker.getAttribute('data-characteristic-picker-mode') || 'multiple';

        if (!select || !availableList || !selectedList) {
            return;
        }

        function optionFor(value) {
            return Array.from(select.options).find(function(option){
                return option.value === value;
            });
        }

        function selectedValues() {
            return Array.from(select.options)
                .filter(function(option){ return option.selected && option.value !== ''; })
                .map(function(option){ return option.value.toLowerCase(); });
        }

        function buttonForValue(value) {
            return Array.from(picker.querySelectorAll('[data-characteristic-picker-option]')).find(function(button){
                return button.getAttribute('data-value') === value;
            });
        }

        function syncCounts() {
            if (availableCount) {
                availableCount.textContent = String(availableList.querySelectorAll('[data-characteristic-picker-option]').length);
            }
            if (selectedCount) {
                selectedCount.textContent = String(selectedList.querySelectorAll('[data-characteristic-picker-option]').length);
            }
        }

        function applySearch() {
            const term = (search?.value || '').trim().toLowerCase();

            availableList.querySelectorAll('[data-characteristic-picker-option]').forEach(function(button){
                const searchableText = (button.getAttribute('data-search') || button.textContent || '').toLowerCase();
                button.hidden = term !== '' && !searchableText.includes(term);
            });
        }

        function moveButton(button, targetList, selected) {
            const value = button.getAttribute('data-value') || '';
            const option = optionFor(value);

            if (selected && mode === 'single') {
                Array.from(selectedList.querySelectorAll('[data-characteristic-picker-option]')).forEach(function(selectedButton){
                    const selectedValue = selectedButton.getAttribute('data-value') || '';
                    const selectedOption = optionFor(selectedValue);

                    if (selectedOption) {
                        selectedOption.selected = false;
                    }

                    selectedButton.classList.remove('is-selected');
                    availableList.appendChild(selectedButton);
                });
            }

            if (option) {
                option.selected = selected;
            }

            button.classList.toggle('is-selected', selected);
            targetList.appendChild(button);
            syncCounts();
            applySearch();
        }

        availableList.addEventListener('click', function(event){
            const button = event.target.closest('[data-characteristic-picker-option]');
            if (!button) {
                return;
            }

            moveButton(button, selectedList, true);
        });

        selectedList.addEventListener('click', function(event){
            const button = event.target.closest('[data-characteristic-picker-option]');
            if (!button) {
                return;
            }

            moveButton(button, availableList, false);
        });

        search?.addEventListener('input', applySearch);
        customAdd?.addEventListener('click', function(){
            const value = (customInput?.value || '').trim();
            if (!value) {
                return;
            }

            const normalized = value.toLowerCase();
            if (selectedValues().includes(normalized)) {
                customInput.value = '';
                applySearch();
                return;
            }

            let option = optionFor(value);
            if (!option) {
                option = document.createElement('option');
                option.value = value;
                option.textContent = value;
                select.appendChild(option);
            }

            let button = buttonForValue(value);
            if (!button) {
                button = document.createElement('button');
                button.type = 'button';
                button.className = 'pc-characteristic-picker__item';
                button.setAttribute('data-characteristic-picker-option', '');
                button.setAttribute('data-custom', '1');
                button.setAttribute('data-value', value);
                button.textContent = value;
            }

            moveButton(button, selectedList, true);
            customInput.value = '';
            applySearch();
        });
        customInput?.addEventListener('keydown', function(event){
            if (event.key === 'Enter') {
                event.preventDefault();
                customAdd?.click();
            }
        });
        syncCounts();
        applySearch();
    });

    function setupProductCategoryCascade(row) {
        const levels = row.querySelector('[data-product-category-levels]');
        const hiddenInput = row.querySelector('[data-product-category-final]');
        const legacySelect = row.querySelector('[data-product-category-legacy]');

        if (!levels || !hiddenInput) {
            return null;
        }

        if (legacySelect) {
            legacySelect.hidden = true;
            legacySelect.disabled = true;
        }

        let categories = [];
        try {
            categories = JSON.parse(row.getAttribute('data-categories') || '[]');
        } catch (e) {
            categories = [];
        }

        if (!Array.isArray(categories)) {
            categories = [];
        }

        const byParent = categories.reduce(function(map, item){
            const key = item.parent_id ? String(item.parent_id) : 'root';
            map[key] = map[key] || [];
            map[key].push(item);
            return map;
        }, {});

        const byId = categories.reduce(function(map, item){
            map[String(item.id)] = item;
            return map;
        }, {});

        function selectedPath() {
            const selectedId = hiddenInput.value ? String(hiddenInput.value) : String(row.getAttribute('data-selected-category') || '');
            const path = [];
            let cursor = selectedId && byId[selectedId] ? byId[selectedId] : null;

            while (cursor) {
                path.unshift(String(cursor.id));
                cursor = cursor.parent_id ? byId[String(cursor.parent_id)] : null;
            }

            return path;
        }

        function clearFromLevel(level) {
            Array.from(levels.querySelectorAll('[data-product-category-level]')).forEach(function(select){
                if (parseInt(select.getAttribute('data-product-category-level'), 10) >= level) {
                    select.remove();
                }
            });
        }

        function renderLevel(parentId, level, path) {
            clearFromLevel(level);

            const parentKey = parentId ? String(parentId) : 'root';
            const items = byParent[parentKey] || [];
            if (items.length === 0) {
                return;
            }

            const select = document.createElement('select');
            select.className = 'pc-select pc-category-cascade__select';
            select.setAttribute('data-product-category-level', String(level));
            select.innerHTML = '<option value="">Selecionar categoria</option>';

            items.forEach(function(item){
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                select.appendChild(option);
            });

            const selected = path && path[level] ? path[level] : '';
            if (selected) {
                select.value = selected;
            }

            select.addEventListener('change', function(){
                const selectedId = this.value;
                clearFromLevel(level + 1);

                if (!selectedId) {
                    const previousSelect = levels.querySelector('[data-product-category-level="' + (level - 1) + '"]');
                    hiddenInput.value = previousSelect ? previousSelect.value : '';
                    return;
                }

                hiddenInput.value = selectedId;
                renderLevel(selectedId, level + 1, []);
            });

            levels.appendChild(select);

            if (selected) {
                hiddenInput.value = selected;
                renderLevel(selected, level + 1, path);
            }
        }

        function syncEnabled(enabled) {
            hiddenInput.disabled = !enabled;
            Array.from(levels.querySelectorAll('select')).forEach(function(select){
                select.disabled = !enabled;
            });

            if (!enabled) {
                hiddenInput.value = '';
                clearFromLevel(0);
                renderLevel(null, 0, []);
                Array.from(levels.querySelectorAll('select')).forEach(function(select){
                    select.disabled = true;
                });
            }
        }

        levels.innerHTML = '';
        renderLevel(null, 0, selectedPath());

        return {
            syncEnabled: syncEnabled
        };
    }

    const categoryCascades = {};
    document.querySelectorAll('[data-product-category-cascade]').forEach(function(row){
        const storeId = row.getAttribute('data-product-category-store');
        categoryCascades[String(storeId)] = setupProductCategoryCascade(row);
    });

    document.querySelectorAll('[data-product-store-toggle]').forEach(function(toggle){
        const storeId = toggle.getAttribute('data-product-store-toggle');
        const row = document.querySelector('[data-product-category-store="' + storeId + '"]');
        const legacySelect = row ? row.querySelector('[data-product-category-legacy]') : null;
        const cascade = categoryCascades[String(storeId)] || null;
        const hint = row ? row.querySelector('small') : null;

        if (!cascade && !legacySelect) {
            return;
        }

        function syncCategoryState() {
            if (legacySelect) {
                legacySelect.disabled = true;
            }
            if (cascade) {
                cascade.syncEnabled(toggle.checked);
            }
            if (hint) {
                hint.textContent = toggle.checked ? 'Loja selecionada' : 'Seleciona a loja para ativar esta categoria';
            }
            if (row) {
                row.classList.toggle('is-active', toggle.checked);
                row.classList.toggle('is-hidden', !toggle.checked);
            }
        }

        toggle.addEventListener('change', syncCategoryState);
        syncCategoryState();
    });

    document.querySelectorAll('[data-ai-category-builder]').forEach(function(builder){
        const levels = builder.querySelector('[data-ai-category-levels]');
        const hiddenInput = document.querySelector('[data-ai-category-id]');
        const promptInput = document.querySelector('textarea[name="prompt"]');

        if (!levels || !hiddenInput || !promptInput) {
            return;
        }

        let categories = [];
        try {
            categories = JSON.parse(builder.getAttribute('data-categories') || '[]');
        } catch (e) {
            categories = [];
        }

        if (!Array.isArray(categories) || categories.length === 0) {
            return;
        }

        let defaultPrompt = '';
        try {
            defaultPrompt = JSON.parse(builder.getAttribute('data-default-prompt') || '""');
        } catch (e) {
            defaultPrompt = '';
        }

        const byParent = categories.reduce(function(map, item){
            const key = item.parent_id ? String(item.parent_id) : 'root';
            map[key] = map[key] || [];
            map[key].push(item);
            return map;
        }, {});

        const byId = categories.reduce(function(map, item){
            map[String(item.id)] = item;
            return map;
        }, {});

        const selectedInitial = hiddenInput.value ? String(hiddenInput.value) : '';
        const pathToInitial = [];
        let cursor = selectedInitial && byId[selectedInitial] ? byId[selectedInitial] : null;
        while (cursor) {
            pathToInitial.unshift(String(cursor.id));
            cursor = cursor.parent_id ? byId[String(cursor.parent_id)] : null;
        }

        function renderLevel(parentId, level, selectedPath) {
            const parentKey = parentId ? String(parentId) : 'root';
            const items = byParent[parentKey] || [];

            Array.from(levels.querySelectorAll('[data-ai-category-level]')).forEach(function(select){
                if (parseInt(select.getAttribute('data-ai-category-level'), 10) >= level) {
                    select.remove();
                }
            });

            if (items.length === 0) {
                return;
            }

            const select = document.createElement('select');
            select.className = 'pc-select pc-ai-category-select';
            select.setAttribute('data-ai-category-level', String(level));
            select.innerHTML = '<option value="">Categoria</option>';

            items.forEach(function(item){
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                select.appendChild(option);
            });

            const selected = selectedPath && selectedPath[level] ? selectedPath[level] : '';
            if (selected) {
                select.value = selected;
            }

            select.addEventListener('change', function(){
                const selectedId = this.value;
                hiddenInput.value = selectedId;

                const category = byId[String(selectedId)];
                if (category && category.prompt) {
                    promptInput.value = category.prompt;
                } else if (!selectedId && defaultPrompt) {
                    promptInput.value = defaultPrompt;
                }

                renderLevel(selectedId, level + 1, []);
            });

            levels.appendChild(select);

            if (selected) {
                const selectedItem = byId[String(selected)];
                if (selectedItem && selectedItem.prompt && !promptInput.value.trim()) {
                    promptInput.value = selectedItem.prompt;
                }
                renderLevel(selected, level + 1, selectedPath);
            }
        }

        if (!promptInput.value.trim() && defaultPrompt) {
            promptInput.value = defaultPrompt;
        }

        renderLevel(null, 0, pathToInitial);
    });
});
</script>
