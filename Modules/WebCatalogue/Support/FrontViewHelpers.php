<?php

namespace Modules\WebCatalogue\Support;

class FrontViewHelpers
{
    public static function productUrl($product, $store, $catalogue = null): string
    {
        if (!empty($catalogue) && !empty($catalogue->slug)) {
            return route('webcatalogue.front.catalogue.product.show', [$store->slug, $catalogue->slug, $product->slug]);
        }

        return route('webcatalogue.front.product.show', [$store->slug, $product->slug]);
    }

    public static function productThumb($product)
    {
        $resources = $product->relationLoaded('resources') ? $product->resources : $product->resources()->get();

        return $resources->firstWhere('is_main', true)
            ?: $resources->first(fn ($resource) => in_array($resource->resource_type, ['image', 'gallery_image', 'thumbnail', 'cover'], true));
    }

    public static function productFlags($product): array
    {
        $resources = $product->relationLoaded('resources') ? $product->resources : $product->resources()->get();

        return [
            'image' => (bool) $resources->first(fn ($resource) => in_array($resource->resource_type, ['image', 'gallery_image', 'thumbnail', 'cover'], true)),
            'model' => (bool) $resources->firstWhere('resource_type', 'model_3d'),
            'ar' => (bool) $resources->firstWhere('resource_type', 'ar_file'),
            'vr' => (bool) $resources->first(fn ($resource) => in_array($resource->resource_type, ['vr_file', 'vr_scene'], true)),
            'video' => (bool) $resources->firstWhere('resource_type', 'video'),
            'audio' => (bool) $resources->first(fn ($resource) => in_array($resource->resource_type, ['audio', 'ambient_audio', 'voiceover', 'sound_effect', 'music_track'], true)),
            'docs' => (bool) $resources->first(fn ($resource) => in_array($resource->resource_type, ['manual', 'datasheet', 'assembly_instructions', 'download'], true)),
        ];
    }

    public static function productPrice($product)
    {
        $prices = $product->relationLoaded('prices') ? $product->prices : $product->prices()->get();

        return $prices->firstWhere('status', 'active')
            ?: $prices->firstWhere('status', 'published')
            ?: $prices->first();
    }
}
