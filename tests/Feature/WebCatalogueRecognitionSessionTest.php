<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\VisualRecognitionCapture;
use Modules\WebCatalogue\Models\VisualRecognitionMatch;
use Modules\WebCatalogue\Models\VisualRecognitionSession;
use Tests\TestCase;

class WebCatalogueRecognitionSessionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_manual_match_updates_session_and_creates_confirmed_match(): void
    {
        Config::set('app.url', 'http://localhost');
        $this->actingAs(User::factory()->create(['password' => Hash::make('password')]));

        $store = Store::create(['name' => 'Recognition Store', 'slug' => 'recognition-store', 'status' => 'active']);
        $product = Product::create([
            'id_store' => $store->id,
            'reference' => 'REC-001',
            'name' => 'Recognition Product',
            'slug' => 'recognition-product',
            'status' => 'active',
        ]);
        $session = VisualRecognitionSession::create([
            'id_store' => $store->id,
            'session_token' => 'recognition-session-token',
            'status' => 'suggestions_found',
            'matched_score' => 72,
        ]);

        $this->post('http://localhost/webcatalogue/recognition/sessions/' . $session->id . '/associate-product', [
            'id_product' => $product->id,
        ])->assertSessionHas('success');

        $session->refresh();
        $this->assertSame('manual_matched', $session->status);
        $this->assertSame($product->id, (int) $session->id_product);
        $this->assertDatabaseHas('wc_visual_recognition_matches', [
            'id_session' => $session->id,
            'id_product' => $product->id,
            'match_provider' => 'manual_review',
            'status' => 'manual_confirmed',
        ]);
    }

    public function test_destroy_removes_session_children(): void
    {
        Config::set('app.url', 'http://localhost');
        $this->actingAs(User::factory()->create(['password' => Hash::make('password')]));

        $store = Store::create(['name' => 'Delete Recognition Store', 'slug' => 'delete-recognition-store', 'status' => 'active']);
        $product = Product::create([
            'id_store' => $store->id,
            'reference' => 'DEL-001',
            'name' => 'Delete Product',
            'slug' => 'delete-product',
            'status' => 'active',
        ]);
        $session = VisualRecognitionSession::create([
            'id_store' => $store->id,
            'session_token' => 'delete-recognition-session-token',
            'status' => 'suggestions_found',
        ]);
        VisualRecognitionCapture::create([
            'id_session' => $session->id,
            'id_store' => $store->id,
            'capture_type' => 'object_photo',
            'status' => 'stored',
        ]);
        VisualRecognitionMatch::create([
            'id_session' => $session->id,
            'id_product' => $product->id,
            'match_provider' => 'internal_composite_v2_26',
            'score' => 80,
            'rank' => 1,
            'status' => 'suggested',
        ]);

        $this->delete('http://localhost/webcatalogue/recognition/sessions/' . $session->id)->assertStatus(302);

        $this->assertDatabaseMissing('wc_visual_recognition_sessions', ['id' => $session->id]);
        $this->assertDatabaseMissing('wc_visual_recognition_captures', ['id_session' => $session->id]);
        $this->assertDatabaseMissing('wc_visual_recognition_matches', ['id_session' => $session->id]);
    }
}
