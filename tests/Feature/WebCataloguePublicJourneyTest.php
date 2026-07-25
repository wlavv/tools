<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\ProductPrice;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\Store;
use Tests\TestCase;

class WebCataloguePublicJourneyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_journey_renders_for_desktop_and_mobile_clients(): void
    {
        Config::set('app.url', 'http://localhost');

        $store = Store::create([
            'name' => 'Public Journey Store',
            'slug' => 'public-journey-store',
            'status' => 'active',
        ]);

        $catalogue = Catalogue::create([
            'id_store' => $store->id,
            'name' => 'Public Journey Catalogue',
            'slug' => 'public-journey-catalogue',
            'status' => 'active',
        ]);

        $product = Product::create([
            'id_store' => $store->id,
            'reference' => 'JOURNEY-001',
            'name' => 'Public Journey Product',
            'slug' => 'public-journey-product',
            'short_description' => 'Product used to validate the public journey.',
            'category' => 'Journey',
            'status' => 'active',
            'price' => 19.99,
        ]);

        $catalogue->products()->attach($product->id, [
            'id_store' => $store->id,
            'status' => 'active',
        ]);

        Resource::create([
            'id_store' => $store->id,
            'id_product' => $product->id,
            'resource_type' => 'image',
            'file_path' => 'webcatalogue/tests/public-journey-product.jpg',
            'is_main' => true,
            'status' => 'active',
        ]);

        ProductPrice::create([
            'id_store' => $store->id,
            'id_product' => $product->id,
            'regular_price' => 19.99,
            'currency' => 'EUR',
            'status' => 'active',
        ]);

        $paths = [
            '/catalogue/'.$store->slug,
            '/catalogue/'.$store->slug.'/products',
            '/catalogue/'.$store->slug.'/'.$catalogue->slug,
            '/catalogue/'.$store->slug.'/product/'.$product->slug,
            '/catalogue/'.$store->slug.'/product/'.$product->slug.'/viewer',
            '/catalogue/'.$store->slug.'/scan',
        ];

        $userAgents = [
            'desktop' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/126 Safari/537.36',
            'mobile' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
        ];

        foreach ($userAgents as $profile => $userAgent) {
            foreach ($paths as $path) {
                $this->withHeader('User-Agent', $userAgent)
                    ->get('http://localhost'.$path)
                    ->assertOk()
                    ->assertDontSee('Whoops, looks like something went wrong', false);
            }
        }
    }
}
