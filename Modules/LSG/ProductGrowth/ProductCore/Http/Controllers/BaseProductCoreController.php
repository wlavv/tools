<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Http\Controllers;

use App\Http\Controllers\Controller;

abstract class BaseProductCoreController extends Controller
{
    protected function prepareProductCorePage(string $title, array $trail = []): void
    {
        $this->disabledDefaultActions = ['new', 'back', 'save', 'show', 'edit', 'delete'];

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index'), 'translate' => false],
            ['label' => 'LSG', 'url' => route('lsg.index'), 'translate' => false],
            ['label' => 'Product Growth', 'url' => route('product_growth.product_core.dashboard'), 'translate' => false],
        ];

        foreach ($trail as $item) {
            $breadcrumbs[] = array_merge(['translate' => false], $item);
        }

        if (empty($trail) || end($trail)['label'] !== $title) {
            $breadcrumbs[] = ['label' => $title, 'url' => null, 'translate' => false];
        }

        $this->setPageTitle($title);
        $this->setBreadcrumbs($breadcrumbs);
        $this->setActions([]);
    }

    protected function addNewProductAction(): void
    {
        $this->addAction([
            'key' => 'new_product',
            'label' => 'Novo anuncio',
            'name' => 'Novo anuncio',
            'icon' => 'fa-solid fa-plus',
            'type' => 'link',
            'url' => route('product_growth.product_core.products.create'),
            'class' => 'lsg-action-btn--success',
        ]);
    }

    protected function addBackToProductsAction(): void
    {
        $this->addAction([
            'key' => 'back_products',
            'label' => 'Anuncios',
            'name' => 'Anuncios',
            'icon' => 'fa-solid fa-angle-left',
            'type' => 'link',
            'url' => route('product_growth.product_core.products.index'),
            'class' => 'lsg-action-btn--back',
        ]);
    }

    protected function addEditProductAction(object $product): void
    {
        $this->addAction([
            'key' => 'edit_product',
            'label' => 'Editar',
            'name' => 'Editar',
            'icon' => 'fa-solid fa-pencil',
            'type' => 'link',
            'url' => route('product_growth.product_core.products.edit', $product),
            'class' => 'lsg-action-btn--warning',
        ]);
    }

    protected function addProductShowAction(object $product): void
    {
        $this->addAction([
            'key' => 'show_product',
            'label' => 'Anuncio',
            'name' => 'Anuncio',
            'icon' => 'fa-solid fa-angle-left',
            'type' => 'link',
            'url' => route('product_growth.product_core.products.show', $product),
            'class' => 'lsg-action-btn--back',
        ]);
    }

    protected function addApproveProductAction(object $product): void
    {
        $this->addAction([
            'key' => 'approve_product',
            'label' => 'Aprovar',
            'name' => 'Aprovar',
            'icon' => 'fa-solid fa-check',
            'type' => 'form',
            'method' => 'POST',
            'url' => route('product_growth.product_core.products.approve', $product),
            'class' => 'lsg-action-btn--success',
        ]);
    }

    protected function addReadyToSyncAction(object $product): void
    {
        $this->addAction([
            'key' => 'sync_product',
            'label' => 'Ready sync',
            'name' => 'Ready sync',
            'icon' => 'fa-solid fa-rotate',
            'type' => 'form',
            'method' => 'POST',
            'url' => route('product_growth.product_core.products.mark_ready_to_sync', $product),
            'class' => 'lsg-action-btn--primary',
        ]);
    }

    protected function addSaveAction(string $formId = 'product-core-form'): void
    {
        $this->addAction([
            'key' => 'save',
            'label' => 'Guardar',
            'name' => 'Guardar',
            'icon' => 'fa-solid fa-floppy-disk',
            'type' => 'submit',
            'form' => $formId,
            'class' => 'lsg-action-btn--primary',
        ]);
    }
}
