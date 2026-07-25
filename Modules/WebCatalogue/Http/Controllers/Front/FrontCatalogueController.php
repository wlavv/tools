<?php

namespace Modules\WebCatalogue\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\PublicLink;
use Modules\WebCatalogue\Models\SessionLog;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\StoreEnvironment;

class FrontCatalogueController extends Controller
{
    public function __construct()
    {
    }

    public function store(Request $request, string $store_slug): View
    {
        $store = $this->findStore($store_slug);

        $statuses = $this->frontVisibleStatuses();
        $catalogues = $this->storeCatalogues($store, $statuses)->get();
        $featuredProducts = $this->storeProducts($store, $statuses)
            ->orderBy('name')
            ->limit(12)
            ->get();
        $filters = $this->buildFilters($store);

        return view('webcatalogue::front.store.show', compact('store', 'catalogues', 'featuredProducts', 'filters'));
    }

    public function products(Request $request, string $store_slug): View
    {
        $store = $this->findStore($store_slug);
        $statuses = $this->frontVisibleStatuses();

        $catalogues = $this->storeCatalogues($store, $statuses)->get();
        $productsQuery = $this->storeProducts($store, $statuses);
        $this->applyFrontFilters($productsQuery, $request);

        $products = $productsQuery->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $filters = $this->buildFilters($store);

        return view('webcatalogue::front.store.products', compact('store', 'catalogues', 'products', 'filters'));
    }

    public function preview(Request $request, string $token): View
    {
        $link = PublicLink::query()
            ->with('store')
            ->where('token', $token)
            ->where('status', 'preview')
            ->usable()
            ->firstOrFail();

        $this->trackLink($link, $request, 'preview.view');

        return $this->renderStoreLink($request, $link, ['draft', 'active', 'published', 'preview'], true);
    }

    public function publicLink(Request $request, string $token): View
    {
        $link = PublicLink::query()
            ->with('store')
            ->where('token', $token)
            ->where('status', 'active')
            ->usable()
            ->firstOrFail();

        $this->trackLink($link, $request, 'public_link.view');

        return $this->renderStoreLink($request, $link, $this->frontVisibleStatuses(), false);
    }

    public function catalogue(Request $request, string $store_slug, string $catalogue_slug): View
    {
        $store = $this->findStore($store_slug);

        $catalogue = Catalogue::query()
            ->where('id_store', $store->id)
            ->where('slug', $catalogue_slug)
            ->whereIn('status', $this->frontVisibleStatuses())
            ->firstOrFail();

        $productsQuery = Product::query()
            ->with(['resources', 'prices', 'promotions'])
            ->where('wc_products.id_store', $store->id)
            ->whereHas('catalogues', function ($query) use ($catalogue) {
                $query->where('wc_catalogues.id', $catalogue->id);
            });

        $this->applyFrontFilters($productsQuery, $request);

        $products = $productsQuery->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $filters = $this->buildFilters($store, $catalogue);

        return view('webcatalogue::front.catalogue.show', compact('store', 'catalogue', 'products', 'filters'));
    }

    public function product(string $store_slug, string $product_slug): View
    {
        $store = $this->findStore($store_slug);
        $product = $this->findProduct($store, $product_slug);

        $payload = $this->buildProductPayload($product);
        $environmentPayload = $this->buildEnvironmentPayload($store);

        return view('webcatalogue::front.product.show', $payload + compact('store', 'product', 'environmentPayload'));
    }

    public function catalogueProduct(string $store_slug, string $catalogue_slug, string $product_slug): View
    {
        $store = $this->findStore($store_slug);
        $catalogue = Catalogue::query()
            ->where('id_store', $store->id)
            ->where('slug', $catalogue_slug)
            ->whereIn('status', $this->frontVisibleStatuses())
            ->firstOrFail();

        $product = $this->findProduct($store, $product_slug);

        abort_unless(
            $product->catalogues()->where('wc_catalogues.id', $catalogue->id)->exists(),
            404
        );

        $payload = $this->buildProductPayload($product);
        $environmentPayload = $this->buildEnvironmentPayload($store, $catalogue);

        return view('webcatalogue::front.product.show', $payload + compact('store', 'catalogue', 'product', 'environmentPayload'));
    }

    public function viewer(string $store_slug, string $product_slug): View
    {
        $store = $this->findStore($store_slug);
        $product = $this->findProduct($store, $product_slug);
        $payload = $this->buildProductPayload($product);
        $environmentPayload = $this->buildEnvironmentPayload($store);

        return view('webcatalogue::front.viewer.show', $payload + compact('store', 'product', 'environmentPayload'));
    }

    public function catalogueViewer(string $store_slug, string $catalogue_slug, string $product_slug): View
    {
        $store = $this->findStore($store_slug);
        $catalogue = Catalogue::query()
            ->where('id_store', $store->id)
            ->where('slug', $catalogue_slug)
            ->whereIn('status', $this->frontVisibleStatuses())
            ->firstOrFail();
        $product = $this->findProduct($store, $product_slug);

        abort_unless(
            $product->catalogues()->where('wc_catalogues.id', $catalogue->id)->exists(),
            404
        );

        $payload = $this->buildProductPayload($product);
        $environmentPayload = $this->buildEnvironmentPayload($store, $catalogue);

        return view('webcatalogue::front.viewer.show', $payload + compact('store', 'catalogue', 'product', 'environmentPayload'));
    }

    private function findStore(string $slug): Store
    {
        return Store::query()
            ->with(['themes', 'environments', 'resources'])
            ->where('status', 'active')
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)->orWhere('code', $slug);
            })
            ->firstOrFail();
    }

    private function findProduct(Store $store, string $slug): Product
    {
        return Product::query()
            ->with(['resources', 'prices', 'promotions', 'catalogues'])
            ->where('id_store', $store->id)
            ->whereIn('status', $this->frontVisibleStatuses())
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('reference', $slug)
                    ->orWhere('sku', $slug);
            })
            ->firstOrFail();
    }

    private function storeCatalogues(Store $store, array $statuses): Builder
    {
        return Catalogue::query()
            ->where('id_store', $store->id)
            ->whereIn('status', $statuses)
            ->orderByRaw("FIELD(status, 'published', 'active')")
            ->orderBy('name');
    }

    private function storeProducts(Store $store, array $statuses): Builder
    {
        return Product::query()
            ->with(['resources', 'prices', 'promotions'])
            ->where('id_store', $store->id)
            ->whereIn('status', $statuses);
    }

    private function applyFrontFilters(Builder $query, Request $request): void
    {
        if ($request->filled('q')) {
            $term = trim((string) $request->query('q'));
            $query->where(function (Builder $sub) use ($term) {
                $sub->where('name', 'like', '%' . $term . '%')
                    ->orWhere('reference', 'like', '%' . $term . '%')
                    ->orWhere('sku', 'like', '%' . $term . '%')
                    ->orWhere('brand', 'like', '%' . $term . '%')
                    ->orWhere('category', 'like', '%' . $term . '%')
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(IF(JSON_VALID(metadata), metadata, NULL), '$.set_code')) LIKE ?", ['%' . $term . '%'])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(IF(JSON_VALID(metadata), metadata, NULL), '$.set_name')) LIKE ?", ['%' . $term . '%'])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(IF(JSON_VALID(metadata), metadata, NULL), '$.collector_number')) LIKE ?", ['%' . $term . '%'])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(IF(JSON_VALID(metadata), metadata, NULL), '$.rarity')) LIKE ?", ['%' . $term . '%'])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(IF(JSON_VALID(metadata), metadata, NULL), '$.artist')) LIKE ?", ['%' . $term . '%']);
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->query('brand'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        foreach ((array) $request->query('resources', []) as $resourceFilter) {
            $this->applyResourceFilter($query, (string) $resourceFilter);
        }
    }

    private function buildFilters(Store $store, ?Catalogue $catalogue = null, ?array $statuses = null): array
    {
        $base = Product::query()
            ->where('wc_products.id_store', $store->id)
            ->whereIn('status', $statuses ?: $this->frontVisibleStatuses());

        if ($catalogue) {
            $base->whereHas('catalogues', function (Builder $query) use ($catalogue) {
                $query->where('wc_catalogues.id', $catalogue->id);
            });
        }

        return [
            'brands' => (clone $base)->whereNotNull('brand')->where('brand', '!=', '')->distinct()->orderBy('brand')->pluck('brand')->values(),
            'categories' => (clone $base)->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category')->values(),
            'resource_options' => $this->buildResourceFilterOptions($base),
        ];
    }

    private function buildResourceFilterOptions(Builder $base): array
    {
        $definitions = [
            'image' => ['label' => 'Images', 'icon' => 'fa-solid fa-image'],
            '3d' => ['label' => '3D', 'icon' => 'fa-solid fa-cube'],
            'ar' => ['label' => 'AR', 'icon' => 'fa-solid fa-vr-cardboard'],
            'vr' => ['label' => 'VR', 'icon' => 'fa-solid fa-headset'],
            'video' => ['label' => 'Video', 'icon' => 'fa-solid fa-video'],
            'audio' => ['label' => 'Audio', 'icon' => 'fa-solid fa-volume-high'],
            'document' => ['label' => 'Docs', 'icon' => 'fa-solid fa-file-lines'],
            'price' => ['label' => 'Price', 'icon' => 'fa-solid fa-tag'],
        ];

        $options = [];

        foreach ($definitions as $key => $definition) {
            $countQuery = clone $base;
            $this->applyResourceFilter($countQuery, $key);
            $count = (int) $countQuery->count();

            if ($count <= 0) {
                continue;
            }

            $options[$key] = $definition + ['count' => $count];
        }

        return $options;
    }

    private function applyResourceFilter(Builder $query, string $resourceFilter): void
    {
        match ($resourceFilter) {
            'image' => $query->whereHas('resources', fn (Builder $r) => $r->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover'])),
            '3d' => $query->whereHas('resources', fn (Builder $r) => $r->where('resource_type', 'model_3d')),
            'ar' => $query->whereHas('resources', fn (Builder $r) => $r->where('resource_type', 'ar_file')),
            'vr' => $query->whereHas('resources', fn (Builder $r) => $r->whereIn('resource_type', ['vr_file', 'vr_scene'])),
            'video' => $query->whereHas('resources', fn (Builder $r) => $r->where('resource_type', 'video')),
            'audio' => $query->whereHas('resources', fn (Builder $r) => $r->whereIn('resource_type', ['audio', 'ambient_audio', 'voiceover', 'sound_effect', 'music_track'])),
            'document' => $query->whereHas('resources', fn (Builder $r) => $r->whereIn('resource_type', ['manual', 'datasheet', 'assembly_instructions', 'download'])),
            'price' => $query->whereHas('prices', fn (Builder $p) => $p->whereIn('status', ['active', 'published'])),
            default => null,
        };
    }

    private function buildProductPayload(Product $product): array
    {
        $resources = $product->resources()->orderByDesc('is_main')->orderBy('sort_order')->orderBy('id')->get();

        $images = $resources->filter(fn ($resource) => in_array($resource->resource_type, ['image', 'gallery_image', 'thumbnail', 'cover'], true))->values();
        $documents = $resources->filter(fn ($resource) => in_array($resource->resource_type, ['manual', 'datasheet', 'assembly_instructions', 'download'], true))->values();
        $videos = $resources->filter(fn ($resource) => in_array($resource->resource_type, ['video'], true))->values();
        $audio = $resources->filter(fn ($resource) => in_array($resource->resource_type, ['audio', 'ambient_audio', 'voiceover', 'sound_effect', 'music_track'], true))->values();

        $model3d = $resources->firstWhere('resource_type', 'model_3d');
        $arFile = $resources->firstWhere('resource_type', 'ar_file');
        $vrFile = $resources->firstWhere('resource_type', 'vr_file') ?: $resources->firstWhere('resource_type', 'vr_scene');
        $thumbnail = $images->firstWhere('is_main', true) ?: $images->first();
        $card3d = $this->buildProceduralCardPayload($product, $thumbnail, $resources);

        $activePrice = $product->prices()
            ->whereIn('status', ['active', 'published'])
            ->orderByRaw("CASE WHEN price_type = 'standard' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->first();

        return compact('resources', 'images', 'documents', 'videos', 'audio', 'model3d', 'arFile', 'vrFile', 'thumbnail', 'activePrice', 'card3d');
    }

    private function buildProceduralCardPayload(Product $product, ?Resource $frontImage, $resources): ?array
    {
        if (!$frontImage?->resolved_url) {
            return null;
        }

        $productMeta = is_array($product->metadata ?? null) ? $product->metadata : [];
        $backResource = $resources->first(function ($resource): bool {
            $metadata = is_array($resource->metadata ?? null) ? $resource->metadata : [];

            return in_array($resource->resource_type, ['card_back', 'back_image'], true)
                || (($metadata['card_side'] ?? null) === 'back');
        });

        $backUrl = $backResource?->resolved_url
            ?: ($productMeta['card_back_url'] ?? $productMeta['back_url'] ?? config('webcatalogue.recognition.card_default_back_url'));

        $finish = in_array(($productMeta['finish'] ?? $productMeta['visual_finish'] ?? 'normal'), ['foil', 'normal'], true)
            ? ($productMeta['finish'] ?? $productMeta['visual_finish'] ?? 'normal')
            : 'normal';

        return [
            'enabled' => true,
            'front_url' => $frontImage->resolved_url,
            'back_url' => $backUrl,
            'finish' => $finish,
            'ratio' => 1.395,
            'thickness' => 0.012,
        ];
    }

    private function buildEnvironmentPayload(Store $store, ?Catalogue $catalogue = null): ?array
    {
        $environmentQuery = StoreEnvironment::query()
            ->where('id_store', $store->id)
            ->where('status', 'active')
            ->when($catalogue, function ($query) use ($catalogue) {
                $query->where(function ($sub) use ($catalogue) {
                    $sub->where('id_catalogue', $catalogue->id)
                        ->orWhereNull('id_catalogue');
                });
            }, fn ($query) => $query->whereNull('id_catalogue'));

        if ($catalogue) {
            $environmentQuery->orderByRaw('CASE WHEN id_catalogue = ? THEN 0 ELSE 1 END', [$catalogue->id]);
        }

        $environment = $environmentQuery
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();

        if (!$environment) {
            return null;
        }

        $vrConfig = is_array($environment->vr_scene_config ?? null) ? $environment->vr_scene_config : [];
        $metadata = is_array($environment->metadata ?? null) ? $environment->metadata : [];
        $background = is_array($vrConfig['background'] ?? null) ? $vrConfig['background'] : [];
        $audio = is_array($vrConfig['audio'] ?? null) ? $vrConfig['audio'] : [];

        return [
            'id' => $environment->id,
            'name' => $environment->name,
            'slug' => $environment->slug,
            'type' => $environment->environment_type,
            'background_type' => $environment->background_type,
            'background_color' => $environment->background_color ?: '#0b1018',
            'skybox_url' => $background['url'] ?? null,
            'audio_url' => $audio['url'] ?? null,
            'audio_volume' => $audio['volume'] ?? 0.24,
            'lighting' => $vrConfig['lighting'] ?? null,
            'camera' => $vrConfig['camera'] ?? null,
            'scene' => $vrConfig['scene'] ?? null,
            'metadata' => $metadata,
        ];
    }

    private function frontVisibleStatuses(): array
    {
        $statuses = config('webcatalogue.front_visible_statuses', ['published', 'active']);

        return array_values(array_filter((array) $statuses, fn ($status) => is_string($status) && trim($status) !== ''));
    }

    private function renderStoreLink(Request $request, PublicLink $link, array $statuses, bool $isPreview): View
    {
        $store = Store::query()
            ->with(['themes', 'environments', 'resources'])
            ->findOrFail($link->id_store);

        $catalogues = Catalogue::query()
            ->where('id_store', $store->id)
            ->whereIn('status', $statuses)
            ->orderByRaw("FIELD(status, 'published', 'active', 'draft', 'preview')")
            ->orderBy('name')
            ->get();

        $featuredProducts = $this->storeProducts($store, $statuses)
            ->orderBy('name')
            ->limit(12)
            ->get();

        $filters = $this->buildFilters($store, null, $statuses);
        $publicLink = $link;

        return view('webcatalogue::front.store.show', compact('store', 'catalogues', 'featuredProducts', 'filters', 'publicLink', 'isPreview'));
    }

    private function trackLink(PublicLink $link, Request $request, string $event): void
    {
        $metadata = is_array($link->metadata ?? null) ? $link->metadata : [];
        $metadata['tracking'] = is_array($metadata['tracking'] ?? null) ? $metadata['tracking'] : [];
        $metadata['tracking']['views'] = (int) ($metadata['tracking']['views'] ?? 0) + 1;
        $metadata['tracking']['last_viewed_at'] = now()->toIso8601String();
        $metadata['tracking']['last_ip'] = $request->ip();

        $link->forceFill(['metadata' => $metadata])->save();

        SessionLog::create([
            'id_store' => $link->id_store,
            'id_product' => $link->id_product,
            'session_token' => $request->session()->getId(),
            'event' => $event,
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'payload' => [
                'public_link_id' => $link->id,
                'link_type' => $link->link_type,
                'status' => $link->status,
            ],
        ]);
    }
}
