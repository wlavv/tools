<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'wc_products';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'raw_payload' => 'array',
        'vr_scene_config' => 'array',
        'ar_scene_config' => 'array',
        'is_default' => 'boolean',
        'is_featured' => 'boolean',
        'is_main' => 'boolean',
        'show_prices' => 'boolean',
        'show_promotions' => 'boolean',
        'published_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function store(){return $this->belongsTo(Store::class, 'id_store');}
    public function catalogues(){return $this->belongsToMany(Catalogue::class, 'wc_catalogue_products', 'id_product', 'id_catalogue')->withPivot(['id_store','sort_order','is_featured','status','metadata'])->withTimestamps();}
    public function resources(){return $this->hasMany(Resource::class, 'id_product');}
    public function prices(){return $this->hasMany(ProductPrice::class, 'id_product');}
    public function promotions(){return $this->belongsToMany(Promotion::class, 'wc_promotion_products', 'id_product', 'id_promotion')->withPivot(['id_store','custom_badge_label','custom_sale_price','sort_order','status','metadata'])->withTimestamps();}

    public function mainImageResource()
    {
        return $this->hasOne(Resource::class, 'id_product')
            ->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover'])
            ->where(function ($query) {
                $query->where('is_main', true)->orWhere('sort_order', 0);
            })
            ->orderByDesc('is_main')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function readinessChecklist(): array
    {
        $resources = $this->relationLoaded('resources') ? $this->resources : collect();
        $prices = $this->relationLoaded('prices') ? $this->prices : collect();
        $catalogues = $this->relationLoaded('catalogues') ? $this->catalogues : collect();

        $hasImage = $this->relationLoaded('mainImageResource')
            ? (bool) $this->mainImageResource
            : $resources->contains(fn ($resource) => $this->resourceIsImage($resource));

        return [
            'store' => [
                'label' => 'Store',
                'ok' => !empty($this->id_store),
                'hint' => 'Assign product to a store.',
            ],
            'reference' => [
                'label' => 'Reference',
                'ok' => trim((string) $this->reference) !== '',
                'hint' => 'Add product reference.',
            ],
            'title' => [
                'label' => 'Name',
                'ok' => trim(strip_tags((string) $this->name)) !== '',
                'hint' => 'Add product name.',
            ],
            'description' => [
                'label' => 'Description',
                'ok' => trim(strip_tags((string) ($this->short_description ?: $this->description))) !== '',
                'hint' => 'Add short or long description.',
            ],
            'category' => [
                'label' => 'Category',
                'ok' => trim((string) $this->category) !== '',
                'hint' => 'Add product category.',
            ],
            'image' => [
                'label' => 'Image',
                'ok' => $hasImage,
                'hint' => 'Add a main image or gallery image.',
            ],
            'price' => [
                'label' => 'Price',
                'ok' => $this->hasReadyPrice($prices),
                'hint' => 'Add base price or active price rule.',
            ],
            'catalogue' => [
                'label' => 'Catalogue',
                'ok' => $catalogues->isNotEmpty() || (int) ($this->catalogues_count ?? 0) > 0,
                'hint' => 'Attach product to at least one catalogue.',
            ],
            'published' => [
                'label' => 'Status',
                'ok' => in_array((string) $this->status, ['active', 'published'], true),
                'hint' => 'Set product status to active or published.',
            ],
            'immersive' => [
                'label' => '3D / AR',
                'ok' => $resources->contains(fn ($resource) => in_array($resource->resource_type, ['model_3d', 'ar_file', 'vr_file', 'vr_scene'], true)),
                'hint' => 'Optional: add 3D, AR or VR resource.',
                'optional' => true,
            ],
        ];
    }

    public function readinessScore(): int
    {
        $required = collect($this->readinessChecklist())
            ->reject(fn ($item) => $item['optional'] ?? false);

        if ($required->isEmpty()) {
            return 0;
        }

        return (int) round(($required->where('ok', true)->count() / $required->count()) * 100);
    }

    public function readinessMissing(): Collection
    {
        return collect($this->readinessChecklist())
            ->filter(fn ($item) => !($item['ok'] ?? false));
    }

    public function readinessState(): string
    {
        $score = $this->readinessScore();

        if ($score >= 100) {
            return 'ready';
        }

        if ($score >= 70) {
            return 'almost';
        }

        return 'needs_work';
    }

    private function hasReadyPrice(Collection $prices): bool
    {
        if ($this->price !== null && (float) $this->price > 0) {
            return true;
        }

        return $prices->contains(function ($price) {
            return ($price->status ?? 'active') === 'active'
                && (
                    ($price->regular_price !== null && (float) $price->regular_price > 0)
                    || ($price->sale_price !== null && (float) $price->sale_price > 0)
                );
        });
    }

    private function resourceIsImage(Resource $resource): bool
    {
        return in_array($resource->resource_type, ['image', 'gallery_image', 'thumbnail', 'cover'], true)
            || str_starts_with((string) $resource->mime_type, 'image/');
    }

}
