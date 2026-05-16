<?php

namespace Modules\WebCatalogue\Http\Controllers\Recognition;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\UnmatchedProductLead;
use Modules\WebCatalogue\Models\VisualRecognitionMatch;
use Modules\WebCatalogue\Models\VisualRecognitionSession;
use Modules\WebCatalogue\Services\Recognition\InternalImageMatchService;

class RecognitionSessionController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(Request $request): View
    {
        $this->disableDefaultAction('new');

        $groups = [
            'review' => [
                'label' => 'To review',
                'statuses' => ['suggestions_found', 'no_match', 'unmatched_lead_created', 'capture_missing', 'match_failed'],
            ],
            'suggestions_found' => [
                'label' => 'Suggestions',
                'statuses' => ['suggestions_found'],
            ],
            'started' => [
                'label' => 'Started',
                'statuses' => ['started'],
            ],
            'manual_matched' => [
                'label' => 'Manual matched',
                'statuses' => ['manual_matched'],
            ],
            'matched' => [
                'label' => 'Auto matched',
                'statuses' => ['matched'],
            ],
            'converted' => [
                'label' => 'Converted',
                'statuses' => ['product_created', 'manual_lead_created'],
            ],
            'other' => [
                'label' => 'Other',
                'statuses' => [],
            ],
            'all' => [
                'label' => 'All',
                'statuses' => null,
            ],
        ];

        $activeGroup = array_key_exists((string) $request->query('group'), $groups)
            ? (string) $request->query('group')
            : 'review';

        $knownStatuses = collect($groups)
            ->pluck('statuses')
            ->filter()
            ->flatten()
            ->unique()
            ->values()
            ->all();

        $itemsQuery = VisualRecognitionSession::query()
            ->with([
                'store',
                'product',
                'lead',
                'captures' => fn ($query) => $query->latest('id'),
                'matches' => fn ($query) => $query->with(['product.mainImageResource'])->orderBy('rank'),
            ])
            ->when($groups[$activeGroup]['statuses'] !== null, function ($query) use ($groups, $activeGroup, $knownStatuses) {
                if ($activeGroup === 'other') {
                    $query->whereNotIn('status', $knownStatuses);
                    return;
                }

                $query->whereIn('status', $groups[$activeGroup]['statuses']);
            })
            ->latest();

        $groupCounts = [];
        foreach ($groups as $key => $group) {
            $groupCounts[$key] = VisualRecognitionSession::query()
                ->when($group['statuses'] !== null, function ($query) use ($key, $group, $knownStatuses) {
                    if ($key === 'other') {
                        $query->whereNotIn('status', $knownStatuses);
                        return;
                    }

                    $query->whereIn('status', $group['statuses']);
                })
                ->count();
        }

        return $this->view('webcatalogue::recognition.sessions.index', [
            'items' => $itemsQuery->paginate(25)->withQueryString(),
            'groups' => $groups,
            'activeGroup' => $activeGroup,
            'groupCounts' => $groupCounts,
        ]);
    }

    public function show(VisualRecognitionSession $session): View
    {
        $this->disableDefaultAction('new');

        return $this->view('webcatalogue::recognition.sessions.show', [
            'item' => $session->load(['store', 'product', 'captures', 'matches.product.mainImageResource', 'lead']),
            'products' => Product::query()
                ->with('mainImageResource')
                ->when($session->id_store, fn ($query) => $query->where('id_store', $session->id_store))
                ->orderBy('reference')
                ->limit(1000)
                ->get(),
        ]);
    }

    public function destroy(VisualRecognitionSession $session): RedirectResponse
    {
        DB::transaction(function () use ($session) {
            $session->load(['captures', 'matches', 'lead']);

            foreach ($session->captures as $capture) {
                if ($capture->file_path && Storage::disk('public')->exists($capture->file_path)) {
                    Storage::disk('public')->delete($capture->file_path);
                }
            }

            $session->matches()->delete();
            $session->captures()->delete();
            $session->lead()->delete();
            $session->delete();
        });

        return redirect()
            ->route('webcatalogue.recognition.sessions.index')
            ->with('success', 'Recognition session removed.');
    }

    public function associateProduct(Request $request, VisualRecognitionSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'id_product' => ['required', 'integer'],
            'match_id' => ['nullable', 'integer'],
        ]);

        $product = Product::query()
            ->where('id', $validated['id_product'])
            ->when($session->id_store, fn ($query) => $query->where('id_store', $session->id_store))
            ->firstOrFail();

        $session->update([
            'id_product' => $product->id,
            'status' => 'manual_matched',
            'matched_score' => $session->matched_score ?: 100,
            'matched_at' => now(),
            'metadata' => array_replace_recursive($session->metadata ?: [], [
                'manual_review' => [
                    'matched_by' => auth()->id(),
                    'matched_at' => now()->toIso8601String(),
                    'product_id' => $product->id,
                ],
            ]),
        ]);

        $session->captures()->update(['id_product' => $product->id, 'status' => 'linked_to_product']);
        $this->storeFeedbackCaptureAsProductResource($session, $product);

        VisualRecognitionMatch::updateOrCreate(
            [
                'id_session' => $session->id,
                'id_product' => $product->id,
                'match_provider' => 'manual_review',
            ],
            [
                'score' => 100,
                'rank' => 1,
                'status' => 'manual_confirmed',
                'metadata' => [
                    'source_match_id' => $validated['match_id'] ?? null,
                    'confirmed_by' => auth()->id(),
                    'confirmed_at' => now()->toIso8601String(),
                ],
            ]
        );

        if ($session->lead) {
            $session->lead->update(['status' => 'resolved', 'notes' => trim(($session->lead->notes ? $session->lead->notes . "\n" : '') . 'Resolved by manual product association #' . $product->id)]);
        }

        return back()->with('success', 'Scan associated with product.');
    }

    public function compareProduct(Request $request, VisualRecognitionSession $session, InternalImageMatchService $matcher): RedirectResponse
    {
        $validated = $request->validate([
            'id_product' => ['required', 'integer'],
        ]);

        $product = Product::query()
            ->where('id', $validated['id_product'])
            ->when($session->id_store, fn ($query) => $query->where('id_store', $session->id_store))
            ->firstOrFail();

        $result = $matcher->compareSessionWithProduct($session, $product);

        return back()
            ->withInput(['id_product' => $product->id])
            ->with($result['ok'] ? 'success' : 'error', $result['message'] ?? 'Comparison finished.')
            ->with('forced_compare', $result);
    }

    private function storeFeedbackCaptureAsProductResource(VisualRecognitionSession $session, Product $product): void
    {
        $capture = $session->captures()->where('capture_type', 'object_photo')->latest()->first();

        if (!$capture?->file_path || !Storage::disk('public')->exists($capture->file_path)) {
            return;
        }

        $extension = pathinfo($capture->file_path, PATHINFO_EXTENSION) ?: 'jpg';
        $target = 'webcatalogue/stores/' . (int) $product->id_store
            . '/products/' . (int) $product->id
            . '/feedback/recognition_session_' . (int) $session->id . '.' . $extension;

        if (!Storage::disk('public')->exists($target)) {
            Storage::disk('public')->copy($capture->file_path, $target);
        }

        Resource::updateOrCreate(
            [
                'id_store' => $product->id_store,
                'id_product' => $product->id,
                'resource_type' => 'image',
                'source_url' => 'recognition_session:' . $session->id,
            ],
            [
                'resource_owner_type' => 'product',
                'resource_owner_id' => $product->id,
                'title' => $product->reference . ' - recognition feedback',
                'description' => 'Camera capture confirmed by manual recognition review.',
                'source_type' => 'recognition_feedback',
                'file_path' => $target,
                'public_url' => '/storage/' . ltrim($target, '/'),
                'filename' => basename($target),
                'mime_type' => $capture->mime_type ?: 'image/jpeg',
                'file_size' => Storage::disk('public')->size($target),
                'extension' => $extension,
                'is_main' => false,
                'sort_order' => 80,
                'status' => 'active',
                'metadata' => [
                    'source_capture_id' => $capture->id,
                    'source_session_id' => $session->id,
                    'feedback_type' => 'manual_match',
                ],
            ]
        );
    }

    public function createLead(Request $request, VisualRecognitionSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'customer_email' => ['nullable', 'email', 'max:255'],
        ]);

        $capture = $session->captures()->where('capture_type', 'object_photo')->latest()->first();

        UnmatchedProductLead::updateOrCreate(
            ['id_session' => $session->id],
            [
                'id_store' => $session->id_store,
                'brand' => $validated['brand'] ?: null,
                'model' => $validated['model'] ?: null,
                'reference' => $validated['reference'] ?: null,
                'description' => $validated['description'] ?: null,
                'customer_email' => $validated['customer_email'] ?: null,
                'object_photo_path' => $capture?->file_path,
                'status' => 'new',
                'lead_score' => $this->leadScore($validated),
                'metadata' => [
                    'source' => 'backoffice_scan_review',
                    'created_by' => auth()->id(),
                    'created_at' => now()->toIso8601String(),
                ],
            ]
        );

        $session->update(['status' => 'manual_lead_created']);

        return back()->with('success', 'Lead created from scan.');
    }

    public function createProduct(Request $request, VisualRecognitionSession $session): RedirectResponse
    {
        abort_unless($session->id_store, 422, 'Recognition session has no store.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $baseReference = trim((string) ($validated['reference'] ?: 'SCAN-' . $session->id));
        $reference = $baseReference;
        $referenceSuffix = 2;
        while (Product::where('id_store', $session->id_store)->where('reference', $reference)->exists()) {
            $reference = $baseReference . '-' . $referenceSuffix++;
        }
        $baseSlug = Str::slug($validated['name']) ?: 'scan-product-' . $session->id;
        $slug = $baseSlug;
        $suffix = 2;
        while (Product::where('id_store', $session->id_store)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        $product = Product::create([
            'id_store' => $session->id_store,
            'reference' => $reference,
            'name' => $validated['name'],
            'slug' => $slug,
            'brand' => $validated['brand'] ?: null,
            'category' => $validated['category'] ?: null,
            'short_description' => $validated['description'] ?: null,
            'status' => 'draft',
            'metadata' => [
                'source' => 'visual_recognition_scan',
                'session_id' => $session->id,
            ],
        ]);

        $capture = $session->captures()->where('capture_type', 'object_photo')->latest()->first();
        if ($capture?->file_path && Storage::disk('public')->exists($capture->file_path)) {
            $extension = pathinfo($capture->file_path, PATHINFO_EXTENSION) ?: 'jpg';
            $target = 'webcatalogue/stores/' . (int) $session->id_store . '/products/' . (int) $product->id . '/images/scan_' . $session->id . '.' . $extension;
            Storage::disk('public')->copy($capture->file_path, $target);

            Resource::create([
                'id_store' => $session->id_store,
                'id_product' => $product->id,
                'resource_owner_type' => 'product',
                'resource_owner_id' => $product->id,
                'resource_type' => 'image',
                'title' => $product->reference . ' - scan image',
                'file_path' => $target,
                'public_url' => Storage::disk('public')->url($target),
                'filename' => basename($target),
                'mime_type' => $capture->mime_type,
                'file_size' => Storage::disk('public')->size($target),
                'is_main' => true,
                'status' => 'active',
                'metadata' => [
                    'source_capture_id' => $capture->id,
                    'source_session_id' => $session->id,
                ],
            ]);
        }

        $session->update([
            'id_product' => $product->id,
            'status' => 'product_created',
            'matched_at' => now(),
        ]);
        $session->captures()->update(['id_product' => $product->id]);

        if ($session->lead) {
            $session->lead->update(['status' => 'converted']);
        }

        return redirect()->route('webcatalogue.products.edit', $product)->with('success', 'Product created from scan. Complete the product data before publishing.');
    }

    private function leadScore(array $data): int
    {
        $score = 10;
        foreach (['brand', 'model', 'reference', 'description', 'customer_email'] as $field) {
            if (!empty($data[$field])) {
                $score += 12;
            }
        }

        return min(100, $score);
    }
}
