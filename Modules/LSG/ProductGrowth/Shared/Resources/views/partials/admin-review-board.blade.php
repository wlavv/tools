<?php
    $workflowAreas = $workflowAreas ?? [];
    $areaReviews = $areaReviews ?? [];
    $firstWorkflowAreaKey = array_key_first($workflowAreas);
    $characteristicLabels = [];
    $characteristicSlugs = [];
    $characteristicValueMedia = [];

    if (\Illuminate\Support\Facades\Schema::hasTable('lsg_catalog_core_characteristics')) {
        $characteristicsMeta = \Illuminate\Support\Facades\DB::table('lsg_catalog_core_characteristics')
            ->select('id', 'name', 'slug')
            ->get();

        $characteristicLabels = $characteristicsMeta
            ->mapWithKeys(fn ($item) => [(string) $item->id => $item->name])
            ->all();

        $characteristicSlugs = $characteristicsMeta
            ->mapWithKeys(fn ($item) => [(string) $item->id => $item->slug])
            ->all();
    }

    if (\Illuminate\Support\Facades\Schema::hasTable('lsg_catalog_core_characteristic_values')) {
        \Illuminate\Support\Facades\DB::table('lsg_catalog_core_characteristic_values')
            ->select('characteristic_id', 'value', 'label', 'image_url')
            ->whereNotNull('image_url')
            ->orderBy('position')
            ->get()
            ->each(function ($item) use (&$characteristicValueMedia): void {
                foreach ([$item->value, $item->label] as $lookupValue) {
                    if (!filled($lookupValue)) {
                        continue;
                    }

                    $key = (string) $item->characteristic_id . ':' . \Illuminate\Support\Str::of((string) $lookupValue)->lower()->trim()->toString();
                    $characteristicValueMedia[$key] = [
                        'label' => $item->label ?: $item->value,
                        'image_url' => $item->image_url,
                    ];
                }
            });
    }

    $formatScalarReviewValue = function ($value): string {
        if (!filled($value)) {
            return 'Sem valor registado';
        }

        if (is_bool($value)) {
            return $value ? 'Sim' : 'Nao';
        }

        return \Illuminate\Support\Str::of(strip_tags((string) $value))
            ->replace(['_', '-'], ' ')
            ->headline()
            ->toString();
    };

    $humanLabel = function ($key) use ($characteristicLabels): string {
        if (isset($characteristicLabels[(string) $key])) {
            return (string) $characteristicLabels[(string) $key];
        }

        if (is_int($key) || ctype_digit((string) $key)) {
            return 'Item ' . ((int) $key + 1);
        }

        return \Illuminate\Support\Str::of((string) $key)
            ->replace(['_', '-'], ' ')
            ->headline()
            ->toString();
    };

    $formatKeyedReviewValue = function ($key, $value) use ($formatScalarReviewValue): string {
        $technicalKeys = ['sku', 'reference', 'internal_sku', 'ean', 'ean13', 'supplier_reference'];

        if (in_array((string) $key, $technicalKeys, true)) {
            return strip_tags((string) $value);
        }

        return $formatScalarReviewValue($value);
    };

    $renderCharacteristicScalar = function ($characteristicId, $value) use ($formatScalarReviewValue, $characteristicSlugs, $characteristicValueMedia): string {
        if (!filled($value)) {
            return '<span>Sem valor registado</span>';
        }

        $slug = $characteristicSlugs[(string) $characteristicId] ?? null;
        $rawValue = strip_tags((string) $value);

        if ($slug === 'mana_cost') {
            preg_match_all('/\{([^}]+)\}/', $rawValue, $matches);
            $tokens = $matches[1] ?? [];

            if ($tokens !== []) {
                $html = collect($tokens)->map(function ($token) use ($characteristicValueMedia): string {
                    $normalizedToken = \Illuminate\Support\Str::of((string) $token)->lower()->trim()->toString();
                    $media = $characteristicValueMedia['5:' . $normalizedToken] ?? $characteristicValueMedia['4:' . $normalizedToken] ?? null;

                    if ($media && filled($media['image_url'] ?? null)) {
                        return '<span class="pc-review-token"><img src="' . e($media['image_url']) . '" alt="' . e($token) . '"></span>';
                    }

                    return '<span class="pc-review-token pc-review-token--text">' . e($token) . '</span>';
                })->implode('');

                return '<span class="pc-review-token-list">' . $html . '</span>';
            }
        }

        $lookupKey = (string) $characteristicId . ':' . \Illuminate\Support\Str::of($rawValue)->lower()->trim()->toString();
        $media = $characteristicValueMedia[$lookupKey] ?? null;

        if ($media && filled($media['image_url'] ?? null)) {
            return '<span class="pc-review-value-chip"><img src="' . e($media['image_url']) . '" alt="' . e($media['label'] ?? $rawValue) . '"><span>' . e($media['label'] ?? $formatScalarReviewValue($rawValue)) . '</span></span>';
        }

        return '<span>' . e($formatScalarReviewValue($rawValue)) . '</span>';
    };

    $renderCharacteristicValue = function ($characteristicId, $value) use (&$renderCharacteristicValue, $renderCharacteristicScalar): string {
        if (is_object($value)) {
            $value = (array) $value;
        }

        if (!is_array($value)) {
            return $renderCharacteristicScalar($characteristicId, $value);
        }

        $items = collect($value)
            ->filter(fn ($item) => filled($item))
            ->values();

        if ($items->isEmpty()) {
            return '<span>Sem valor registado</span>';
        }

        return '<div class="pc-review-value-chip-list">' . $items
            ->map(fn ($item) => is_array($item) || is_object($item)
                ? $renderCharacteristicValue($characteristicId, $item)
                : $renderCharacteristicScalar($characteristicId, $item)
            )
            ->implode('') . '</div>';
    };

    $renderHumanReviewValue = function ($value) use (&$renderHumanReviewValue, $formatScalarReviewValue, $formatKeyedReviewValue, $humanLabel, $characteristicLabels, $renderCharacteristicValue): string {
        if (!filled($value)) {
            return '<span>Sem valor registado</span>';
        }

        if (is_object($value)) {
            $value = (array) $value;
        }

        if (!is_array($value)) {
            return '<span>' . e($formatScalarReviewValue($value)) . '</span>';
        }

        $wasList = array_is_list($value);

        $value = collect($value)
            ->reject(fn ($item) => !filled($item))
            ->when($wasList, fn ($collection) => $collection->values())
            ->all();

        if ($value === []) {
            return '<span>Sem valor registado</span>';
        }

        if (array_is_list($value)) {
            $items = collect($value)->map(function ($item) use (&$renderHumanReviewValue, $formatScalarReviewValue): string {
                if (is_array($item) || is_object($item)) {
                    return '<li>' . $renderHumanReviewValue($item) . '</li>';
                }

                return '<li>' . e($formatScalarReviewValue($item)) . '</li>';
            })->implode('');

            return '<ul class="pc-review-human-list">' . $items . '</ul>';
        }

        if (isset($value['attributes']) && is_array($value['attributes'])) {
            foreach (array_keys($value['attributes']) as $attributeKey) {
                if (array_key_exists($attributeKey, $value) && (string) $value[$attributeKey] === (string) $value['attributes'][$attributeKey]) {
                    unset($value[$attributeKey]);
                }
            }
        }

        $rows = collect($value)->map(function ($item, $key) use (&$renderHumanReviewValue, $formatKeyedReviewValue, $humanLabel, $characteristicLabels, $renderCharacteristicValue): string {
            $label = '<dt>' . e($humanLabel($key)) . '</dt>';

            if (isset($characteristicLabels[(string) $key])) {
                return $label . '<dd>' . $renderCharacteristicValue($key, $item) . '</dd>';
            }

            if (is_array($item) || is_object($item)) {
                return $label . '<dd>' . $renderHumanReviewValue($item) . '</dd>';
            }

            return $label . '<dd>' . e($formatKeyedReviewValue($key, $item)) . '</dd>';
        })->implode('');

        return '<dl class="pc-review-human-map">' . $rows . '</dl>';
    };
?>

<div class="pc-admin-review-tabs" data-review-tabs>
    <div class="pc-review-tab-list" role="tablist" aria-label="Departamentos para validacao">
        <?php foreach ($workflowAreas as $areaKey => $area): ?>
            <?php
                $review = $areaReviews[$areaKey] ?? [];
                $reviewStatus = $review['status'] ?? 'pending';
                $reviewBadgeClass = $reviewStatus === 'approved'
                    ? 'pc-badge--success'
                    : ($reviewStatus === 'rejected' ? 'pc-badge--danger' : 'pc-badge--warning');
                $isActiveTab = $firstWorkflowAreaKey === $areaKey;
            ?>
            <button type="button" class="pc-review-tab <?= $isActiveTab ? 'is-active' : '' ?>" role="tab" aria-selected="<?= $isActiveTab ? 'true' : 'false' ?>" aria-controls="pc-review-panel-<?= e($areaKey) ?>" data-review-tab="<?= e($areaKey) ?>">
                <i class="<?= e($area['icon'] ?? 'fa-solid fa-clipboard-check') ?>"></i>
                <span><?= e($area['label'] ?? $areaKey) ?></span>
                <em class="pc-badge <?= e($reviewBadgeClass) ?>" data-review-area-status><?= e($reviewStatus) ?></em>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="pc-admin-review-grid">
    <?php foreach ($workflowAreas as $areaKey => $area): ?>
        <?php
            $review = $areaReviews[$areaKey] ?? [];
            $reviewStatus = $review['status'] ?? 'pending';
            $reviewBadgeClass = $reviewStatus === 'approved'
                ? 'pc-badge--success'
                : ($reviewStatus === 'rejected' ? 'pc-badge--danger' : 'pc-badge--warning');
            $isActivePanel = $firstWorkflowAreaKey === $areaKey;
        ?>
        <section class="pc-review-area-card <?= $isActivePanel ? 'is-active' : '' ?>" role="tabpanel" id="pc-review-panel-<?= e($areaKey) ?>" data-review-panel="<?= e($areaKey) ?>" <?= $isActivePanel ? '' : 'hidden' ?>>
            <?php if ($reviewStatus !== 'approved') { ?>
            <div class="pc-review-area-head" data-review-area-head>
                <form method="POST" action="<?= e(route('product_growth.workflow_manager.product.review_area', [$product, $areaKey])) ?>" data-review-area-action data-confirm="Aprovar todos os pontos de <?= e($area['label'] ?? $areaKey) ?>?">
                    <?= csrf_field() ?>
                    <button type="submit" class="pc-review-area-approve" title="Aprovar todos os pontos de <?= e($area['label'] ?? $areaKey) ?>">
                        <i class="fa-solid fa-check-double"></i>
                        <span>Aprovar departamento</span>
                    </button>
                </form>
                <em class="pc-badge <?= e($reviewBadgeClass) ?>" data-review-area-status><?= e($reviewStatus) ?></em>
            </div>
            <?php } ?>

            <div class="pc-review-item-list">
                <?php foreach (($area['items'] ?? []) as $itemKey => $item): ?>
                    <?php
                        $itemReview = $review['items'][$itemKey] ?? [];
                        $value = data_get($product, $item['path'] ?? '');

                        if ($value === null && str_starts_with((string) ($item['path'] ?? ''), 'metadata.')) {
                            $value = data_get($product->metadata ?? [], substr($item['path'], 9));
                        }

                        $isStructuredValue = is_array($value) || is_object($value);
                        $itemStatus = $itemReview['status'] ?? null;
                    ?>
                    <div class="pc-review-item <?= $itemStatus === 'approved' ? 'is-approved' : '' ?>" data-review-item data-review-status="<?= e($itemStatus ?? 'pending') ?>">
                        <div class="pc-review-actions">
                            <form method="POST" action="<?= e(route('product_growth.workflow_manager.product.review_item', [$product, $areaKey, $itemKey, 'approved'])) ?>" data-review-item-form>
                                <?= csrf_field() ?>
                                <button type="submit" class="pc-icon-action pc-icon-action--success" title="Aprovar <?= e($item['label'] ?? $itemKey) ?>">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>
                            <form method="POST" action="<?= e(route('product_growth.workflow_manager.product.review_item', [$product, $areaKey, $itemKey, 'rejected'])) ?>" class="pc-reject-form" data-review-item-form data-review-reject-form>
                                <?= csrf_field() ?>
                                <input type="hidden" name="reason" value="<?= e($itemReview['reason'] ?? '') ?>" data-review-reject-reason>
                                <button type="button" class="pc-icon-action pc-icon-action--danger" title="Recusar <?= e($item['label'] ?? $itemKey) ?>" data-review-reject data-review-field="<?= e($item['label'] ?? $itemKey) ?>" data-review-current-reason="<?= e($itemReview['reason'] ?? '') ?>">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </form>
                        </div>
                        <div class="pc-review-item-value">
                            <strong><?= e($item['label'] ?? $itemKey) ?></strong>
                            <?php if ($isStructuredValue): ?>
                                <div class="pc-review-human-value">
                                    <?= $renderHumanReviewValue($value) ?>
                                </div>
                            <?php else: ?>
                                <span><?= e($formatKeyedReviewValue($itemKey, $value)) ?></span>
                            <?php endif; ?>
                            <small class="pc-review-reason" data-review-reason-output><?= e($itemReview['reason'] ?? '') ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
    </div>
</div>
