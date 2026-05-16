<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\ProductPrice;
use Modules\WebCatalogue\Models\PublicLink;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\Store;
use Tests\TestCase;

class WebCataloguePublishFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_store_publish_flow_generates_preview_and_public_link(): void
    {
        Config::set('app.url', 'http://localhost');
        $this->actingAs(User::factory()->create(['password' => Hash::make('password')]));

        $store = Store::create([
            'name' => 'Publish Test Store',
            'slug' => 'publish-test-store',
            'domain' => 'publish-test.local',
            'status' => 'active',
        ]);

        $catalogue = Catalogue::create([
            'id_store' => $store->id,
            'name' => 'Main Catalogue',
            'slug' => 'main-catalogue',
            'status' => 'active',
        ]);

        $product = Product::create([
            'id_store' => $store->id,
            'reference' => 'PUB-001',
            'name' => 'Publish Product',
            'slug' => 'publish-product',
            'short_description' => 'Ready to publish.',
            'category' => 'Test',
            'status' => 'active',
            'price' => 10,
        ]);

        $catalogue->products()->attach($product->id, [
            'id_store' => $store->id,
            'status' => 'active',
        ]);

        Resource::create([
            'id_store' => $store->id,
            'id_product' => $product->id,
            'resource_type' => 'image',
            'file_path' => 'webcatalogue/tests/product.jpg',
            'is_main' => true,
            'status' => 'active',
        ]);

        ProductPrice::create([
            'id_store' => $store->id,
            'id_product' => $product->id,
            'regular_price' => 10,
            'currency' => 'EUR',
            'status' => 'active',
        ]);

        $this->post('http://localhost/webcatalogue/stores/' . $store->id . '/publish/preview')->assertStatus(302);
        $this->assertDatabaseHas('wc_public_links', [
            'id_store' => $store->id,
            'link_type' => 'store',
            'status' => 'preview',
        ]);

        $this->post('http://localhost/webcatalogue/stores/' . $store->id . '/publish')->assertStatus(302);
        $this->assertDatabaseHas('wc_public_links', [
            'id_store' => $store->id,
            'link_type' => 'store',
            'status' => 'active',
        ]);
        $this->assertSame('published', $catalogue->fresh()->status);
        $this->assertSame('published', $product->fresh()->status);

        $this->post('http://localhost/webcatalogue/stores/' . $store->id . '/publish/unpublish')->assertStatus(302);
        $this->assertSame('inactive', PublicLink::where('id_store', $store->id)->where('link_type', 'store')->where('status', 'inactive')->latest('id')->first()?->status);
    }
}
