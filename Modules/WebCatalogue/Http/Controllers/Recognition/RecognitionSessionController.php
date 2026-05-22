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
use Modules\WebCatalogue\Models\RecognitionScan;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\UnmatchedProductLead;
use Modules\WebCatalogue\Models\VisualRecognitionMatch;
use Modules\WebCatalogue\Models\VisualRecognitionSession;
use Modules\WebCatalogue\Services\Recognition\InternalImageMatchService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
                'statuses' => ['suggestions_found', 'no_match', 'unmatched_lead_created', 'capture_missing', 'capture_failed', 'capture_received', 'matching', 'match_failed'],
            ],
            'suggestions_found' => [
                'label' => 'Suggestions',
                'statuses' => ['suggestions_found'],
            ],
            'started' => [
                'label' => 'Started',
                'statuses' => ['started', 'capture_received', 'matching'],
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
                $cropPath = $capture->metadata['detected_object_crop_path'] ?? null;
                if ($cropPath && Storage::disk('public')->exists($cropPath)) {
                    Storage::disk('public')->delete($cropPath);
                }
                foreach (['normalized_path', 'debug_path'] as $opencvPathKey) {
                    $opencvPath = $capture->metadata['opencv_analysis'][$opencvPathKey] ?? null;
                    if ($opencvPath && Storage::disk('public')->exists($opencvPath)) {
                        Storage::disk('public')->delete($opencvPath);
                    }
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
            ->with('mainImageResource')
            ->where('id', $validated['id_product'])
            ->when($session->id_store, fn ($query) => $query->where('id_store', $session->id_store))
            ->firstOrFail();

        $session->load(['matches']);
        $review = $this->buildGroundTruthReview($session, $product);

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
                'ground_truth' => $review,
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

        $this->syncGroundTruthToLatestScan($session, $product, $review);

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

    public function groundTruth(Request $request, VisualRecognitionSession $session, InternalImageMatchService $matcher): RedirectResponse
    {
        $validated = $request->validate([
            'id_product' => ['required', 'integer'],
            'scenario_label' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'run_forced_compare' => ['nullable', 'boolean'],
        ]);

        $product = Product::query()
            ->with('mainImageResource')
            ->where('id', $validated['id_product'])
            ->when($session->id_store, fn ($query) => $query->where('id_store', $session->id_store))
            ->firstOrFail();

        $forcedCompare = !empty($validated['run_forced_compare'])
            ? $matcher->compareSessionWithProduct($session, $product)
            : null;
        $session->load(['matches.product.mainImageResource', 'captures']);
        $review = $this->buildGroundTruthReview(
            $session,
            $product,
            $validated['scenario_label'] ?: null,
            $validated['notes'] ?: null,
            $forcedCompare
        );

        $session->update([
            'metadata' => array_replace_recursive($session->metadata ?: [], [
                'ground_truth' => $review,
            ]),
        ]);

        $this->syncGroundTruthToLatestScan($session, $product, $review);

        return back()
            ->withInput(['id_product' => $product->id])
            ->with($forcedCompare && !($forcedCompare['ok'] ?? false) ? 'error' : 'success', 'Ground truth saved: ' . str_replace('_', ' ', $review['classification']) . '.')
            ->with('forced_compare', $forcedCompare);
    }

    public function diagnosticZip(Request $request, VisualRecognitionSession $session): BinaryFileResponse
    {
        abort_unless(class_exists(\ZipArchive::class), 500, 'PHP Zip extension is not available.');

        $session->load(['store', 'product.mainImageResource', 'captures', 'matches.product.mainImageResource']);
        $groundTruthProductId = (int) ($request->query('id_product') ?: data_get($session->metadata, 'ground_truth.expected_product_id') ?: $session->id_product);
        $groundTruthProduct = $groundTruthProductId
            ? Product::query()->with('mainImageResource')->find($groundTruthProductId)
            : null;
        $topMatches = $this->algorithmMatches($session)->take(5)->values();
        $zipPath = storage_path('app/webcatalogue-session-' . $session->id . '-diagnostic-' . now()->format('YmdHis') . '.zip');
        $zip = new \ZipArchive();

        abort_unless($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true, 500, 'Could not create diagnostic ZIP.');

        $manifest = [
            'session' => [
                'id' => $session->id,
                'status' => $session->status,
                'store' => $session->store?->name,
                'created_at' => $session->created_at?->toIso8601String(),
                'metadata' => $session->metadata,
            ],
            'ground_truth_product' => $groundTruthProduct ? [
                'id' => $groundTruthProduct->id,
                'reference' => $groundTruthProduct->reference,
                'name' => strip_tags((string) $groundTruthProduct->name),
                'image_path' => $groundTruthProduct->mainImageResource?->file_path,
            ] : null,
            'top_5_candidates' => $topMatches->map(fn (VisualRecognitionMatch $match) => [
                'rank' => (int) $match->rank,
                'product_id' => $match->id_product,
                'reference' => $match->product?->reference,
                'name' => strip_tags((string) ($match->product?->name ?? '')),
                'score' => (float) $match->score,
                'provider' => $match->match_provider,
                'status' => $match->status,
                'image_path' => $match->product?->mainImageResource?->file_path,
                'scores' => $match->metadata['scores'] ?? [],
            ])->values()->all(),
            'captures' => $session->captures->map(fn ($capture) => [
                'id' => $capture->id,
                'type' => $capture->capture_type,
                'file_path' => $capture->file_path,
                'crop_path' => $capture->metadata['detected_object_crop_path'] ?? null,
                'opencv_normalized_path' => $capture->metadata['opencv_analysis']['normalized_path'] ?? null,
                'opencv_debug_path' => $capture->metadata['opencv_analysis']['debug_path'] ?? null,
                'metadata' => $capture->metadata,
            ])->values()->all(),
        ];

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        foreach ($session->captures as $capture) {
            $prefix = 'captures/capture_' . $capture->id . '/';
            $this->addPublicStorageFileToZip($zip, $capture->file_path, $prefix . 'original');
            $this->addPublicStorageFileToZip($zip, $capture->metadata['detected_object_crop_path'] ?? null, $prefix . 'detected_crop');
            $this->addPublicStorageFileToZip($zip, $capture->metadata['opencv_analysis']['normalized_path'] ?? null, $prefix . 'opencv_normalized');
            $this->addPublicStorageFileToZip($zip, $capture->metadata['opencv_analysis']['debug_path'] ?? null, $prefix . 'opencv_debug');
        }

        foreach ($topMatches as $match) {
            $this->addPublicStorageFileToZip(
                $zip,
                $match->product?->mainImageResource?->file_path,
                'candidates/rank_' . str_pad((string) $match->rank, 2, '0', STR_PAD_LEFT) . '_product_' . $match->id_product
            );
        }

        if ($groundTruthProduct) {
            $this->addPublicStorageFileToZip($zip, $groundTruthProduct->mainImageResource?->file_path, 'ground_truth/product_' . $groundTruthProduct->id);
        }

        $zip->close();

        return response()
            ->download($zipPath, 'webcatalogue-session-' . $session->id . '-diagnostic.zip')
            ->deleteFileAfterSend(true);
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

    private function groundTruthClassification(?int $rank): string
    {
        if ($rank === 1) {
            return 'top_1_match';
        }

        if ($rank !== null && $rank <= 3) {
            return 'failed_auto_but_in_top_3';
        }

        if ($rank !== null && $rank <= 5) {
            return 'failed_auto_but_in_top_5';
        }

        return 'missed_top_5';
    }

    private function buildGroundTruthReview(
        VisualRecognitionSession $session,
        Product $product,
        ?string $scenarioLabel = null,
        ?string $notes = null,
        ?array $forcedCompare = null
    ): array {
        $rank = $this->algorithmMatches($session)->firstWhere('id_product', $product->id)?->rank;
        $classification = $this->groundTruthClassification($rank ? (int) $rank : null);

        return [
            'expected_product_id' => $product->id,
            'expected_product_name' => strip_tags((string) $product->name),
            'expected_product_reference' => $product->reference,
            'scenario_label' => $scenarioLabel,
            'classification' => $classification,
            'rank' => $rank ? (int) $rank : null,
            'top_1_correct' => $rank !== null && (int) $rank === 1,
            'top_3_correct' => $rank !== null && (int) $rank <= 3,
            'top_5_correct' => $rank !== null && (int) $rank <= 5,
            'notes' => $notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()->toIso8601String(),
            'forced_compare' => $forcedCompare,
        ];
    }

    private function syncGroundTruthToLatestScan(VisualRecognitionSession $session, Product $product, array $review): void
    {
        $scan = RecognitionScan::query()
            ->where('id_session', $session->id)
            ->latest('id')
            ->first();

        if (!$scan) {
            return;
        }

        $scan->update([
            'expected_product_id' => $product->id,
            'scenario_label' => $review['scenario_label'] ?: null,
            'top_1_correct' => $review['top_1_correct'],
            'top_3_correct' => $review['top_3_correct'],
            'false_positive' => in_array($session->status, ['matched', 'manual_matched'], true) && !$review['top_1_correct'],
            'false_negative' => !$review['top_1_correct'] && $review['top_3_correct'],
            'metadata' => array_replace_recursive($scan->metadata ?: [], [
                'manual_ground_truth' => $review,
            ]),
        ]);
    }

    private function algorithmMatches(VisualRecognitionSession $session)
    {
        return $session->matches
            ->reject(fn (VisualRecognitionMatch $match) => $match->match_provider === 'manual_review')
            ->sortBy('rank')
            ->values();
    }

    private function addPublicStorageFileToZip(\ZipArchive $zip, ?string $path, string $zipBaseName): void
    {
        if (!$path || !Storage::disk('public')->exists($path)) {
            return;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $zipName = $zipBaseName . ($extension ? '.' . $extension : '');
        $zip->addFile(Storage::disk('public')->path($path), $zipName);
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
