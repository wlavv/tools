<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\ProductCore\Models\Product;

class ProductGrowthAreaPanelService
{
    public function forArea(string $areaKey): array
    {
        $workflowArea = $this->workflowAreaKey($areaKey);
        $areas = config('product-core.workflow_areas.areas', []);

        if ($workflowArea === 'admin') {
            $area = [
                'label' => 'Admin',
                'route' => 'product_growth.workflow_manager.dashboard',
                'icon' => 'fa-solid fa-user-shield',
                'summary' => 'Validacao final por area, devolucoes para correcao e aprovacao de publicacao.',
            ];
        } elseif ($workflowArea && isset($areas[$workflowArea])) {
            $area = $areas[$workflowArea];
        } else {
            return [
                'enabled' => false,
                'title' => 'Product Growth',
                'subtitle' => 'Sem bloco Product Growth atribuido a esta area.',
                'area' => $areaKey,
                'icon' => 'fa-solid fa-boxes-stacked',
                'entry_url' => null,
                'new' => collect(),
                'verified' => collect(),
                'corrections' => collect(),
                'queues' => [
                    'new' => ['label' => 'Novos anuncios', 'icon' => 'fa-solid fa-inbox', 'class' => ''],
                    'corrections' => ['label' => 'Correcoes necessarias', 'icon' => 'fa-solid fa-triangle-exclamation', 'class' => 'pg-area-counter--warning'],
                ],
            ];
        }

        $products = Product::with(['brand', 'supplier'])
            ->whereNotIn('status', ['synced', 'archived'])
            ->latest()
            ->limit(80)
            ->get();

        if ($workflowArea === 'admin') {
            $new = $products
                ->filter(fn (Product $product) => !$this->hasRejectedArea($product) && !$this->isFullyApproved($product, $areas))
                ->take(8)
                ->values();
            $corrections = $products
                ->filter(fn (Product $product) => $this->hasRejectedArea($product))
                ->take(8)
                ->values();
            $verified = $products
                ->filter(fn (Product $product) => $this->isFullyApproved($product, $areas))
                ->take(8)
                ->values();
        } else {
            $new = $products
                ->filter(fn (Product $product) => in_array($this->reviewStatus($product, $workflowArea), ['pending', 'submitted', 'resubmitted'], true))
                ->take(8)
                ->values();
            $corrections = $products
                ->filter(fn (Product $product) => $this->reviewStatus($product, $workflowArea) === 'rejected')
                ->take(8)
                ->values();
            $verified = collect();
        }

        return [
            'enabled' => true,
            'title' => $workflowArea === 'admin' ? 'Product Growth Approval' : 'Product Growth - ' . $area['label'],
            'subtitle' => $workflowArea === 'admin'
                ? 'Produtos para validacao final, aprovacao e devolucao por area.'
                : $area['summary'],
            'area' => $workflowArea,
            'icon' => $area['icon'],
            'entry_url' => Route::has($area['route']) ? route($area['route']) : null,
            'new' => $new,
            'verified' => $verified,
            'corrections' => $corrections,
            'queues' => $workflowArea === 'admin'
                ? [
                    'new' => ['label' => 'Para validar', 'icon' => 'fa-solid fa-clipboard-check', 'class' => ''],
                    'corrections' => ['label' => 'Em correcao', 'icon' => 'fa-solid fa-triangle-exclamation', 'class' => 'pg-area-counter--warning'],
                    'verified' => ['label' => 'Verificados', 'icon' => 'fa-solid fa-circle-check', 'class' => 'pg-area-counter--success'],
                ]
                : [
                    'new' => ['label' => 'Novos anuncios', 'icon' => 'fa-solid fa-inbox', 'class' => ''],
                    'corrections' => ['label' => 'Correcoes necessarias', 'icon' => 'fa-solid fa-triangle-exclamation', 'class' => 'pg-area-counter--warning'],
                ],
        ];
    }

    private function workflowAreaKey(string $areaKey): ?string
    {
        return [
            'administration' => 'admin',
            'admin' => 'admin',
            'purchasing' => 'purchase',
            'purchase' => 'purchase',
            'logistics' => 'logistics',
            'finance' => null,
            'sales' => 'sales',
            'marketing' => 'marketing',
            'customerSupport' => null,
            'support' => null,
            'hr' => null,
        ][$areaKey] ?? null;
    }

    private function reviewStatus(Product $product, string $area): string
    {
        return data_get($product->metadata ?? [], 'department_reviews.' . $area . '.status', 'pending');
    }

    private function hasRejectedArea(Product $product): bool
    {
        foreach (data_get($product->metadata ?? [], 'department_reviews', []) as $review) {
            if (($review['status'] ?? null) === 'rejected') {
                return true;
            }
        }

        return false;
    }

    private function isFullyApproved(Product $product, array $areas): bool
    {
        foreach (array_keys($areas) as $areaKey) {
            if ($this->reviewStatus($product, $areaKey) !== 'approved') {
                return false;
            }
        }

        return !empty($areas);
    }
}
