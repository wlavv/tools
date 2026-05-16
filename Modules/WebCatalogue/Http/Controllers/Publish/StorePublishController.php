<?php

namespace Modules\WebCatalogue\Http\Controllers\Publish;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\PublicLink;
use Modules\WebCatalogue\Models\Store;

class StorePublishController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'WebCatalogue Publish Flow';
    }

    public function show(Store $store): View
    {
        $store->loadCount(['catalogues', 'products', 'resources', 'themes', 'environments', 'prices'])
            ->load([
                'publicLinks' => fn ($query) => $query->latest('id'),
            ]);

        return $this->view('webcatalogue::publish.store', [
            'store' => $store,
            'stats' => $this->publishStats($store),
        ]);
    }

    public function preview(Store $store): RedirectResponse
    {
        $link = $this->ensureStoreLink($store, 'preview', [
            'generated_at' => now()->toIso8601String(),
            'generated_by' => auth()->id(),
        ]);

        return redirect()
            ->route('webcatalogue.stores.publish.show', $store)
            ->with('success', 'Preview link ready.')
            ->with('preview_url', route('webcatalogue.front.preview.store', $link->token));
    }

    public function publish(Store $store): RedirectResponse
    {
        $stats = $this->publishStats($store);
        $now = now();

        $store->update(['status' => 'active']);

        Catalogue::query()
            ->where('id_store', $store->id)
            ->whereHas('products')
            ->update([
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => $now,
            ]);

        $stats['ready_products']->each(function (Product $product) {
            $product->update(['status' => 'published']);
        });

        $link = $this->ensureStoreLink($store, 'active', [
            'published_at' => $now->toIso8601String(),
            'published_by' => auth()->id(),
            'published_counts' => [
                'catalogues' => $stats['publishable_catalogues'],
                'products' => $stats['ready_products']->count(),
                'total_products' => $stats['products'],
            ],
        ]);

        return redirect()
            ->route('webcatalogue.stores.publish.show', $store)
            ->with('success', 'Store published. Public link is active.')
            ->with('public_url', route('webcatalogue.front.public_link', $link->token));
    }

    public function unpublish(Store $store): RedirectResponse
    {
        PublicLink::query()
            ->where('id_store', $store->id)
            ->where('link_type', 'store')
            ->where('status', 'active')
            ->update(['status' => 'inactive']);

        return redirect()
            ->route('webcatalogue.stores.publish.show', $store)
            ->with('success', 'Public link disabled.');
    }

    private function ensureStoreLink(Store $store, string $status, array $metadata): PublicLink
    {
        $link = PublicLink::query()
            ->where('id_store', $store->id)
            ->where('link_type', 'store')
            ->where('status', $status)
            ->first();

        if (!$link) {
            $link = new PublicLink([
                'id_store' => $store->id,
                'link_type' => 'store',
                'status' => $status,
                'token' => Str::random(48),
            ]);
        }

        $existingMetadata = is_array($link->metadata ?? null) ? $link->metadata : [];
        $link->fill([
            'title' => $store->name . ' - ' . ucfirst($status),
            'metadata' => array_replace_recursive($existingMetadata, $metadata),
        ]);
        $link->save();

        return $link;
    }

    private function publishStats(Store $store): array
    {
        $products = Product::query()
            ->with(['resources', 'prices', 'catalogues', 'mainImageResource'])
            ->withCount('catalogues')
            ->where('id_store', $store->id)
            ->get();

        $readyProducts = $products->filter(fn (Product $product) => $product->readinessScore() >= 100)->values();
        $previewLink = PublicLink::query()
            ->where('id_store', $store->id)
            ->where('link_type', 'store')
            ->where('status', 'preview')
            ->usable()
            ->latest('id')
            ->first();
        $publicLink = PublicLink::query()
            ->where('id_store', $store->id)
            ->where('link_type', 'store')
            ->where('status', 'active')
            ->usable()
            ->latest('id')
            ->first();

        return [
            'catalogues' => Catalogue::query()->where('id_store', $store->id)->count(),
            'publishable_catalogues' => Catalogue::query()->where('id_store', $store->id)->whereHas('products')->count(),
            'products' => $products->count(),
            'ready_products' => $readyProducts,
            'themes' => (int) ($store->themes_count ?? $store->themes()->count()),
            'environments' => (int) ($store->environments_count ?? $store->environments()->count()),
            'prices' => (int) ($store->prices_count ?? $store->prices()->count()),
            'has_store' => filled($store->name) && filled($store->slug) && filled($store->domain),
            'has_theme' => (int) ($store->themes_count ?? $store->themes()->count()) > 0,
            'preview_link' => $previewLink,
            'public_link' => $publicLink,
        ];
    }
}
