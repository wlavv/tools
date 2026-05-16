<?php

namespace Modules\WebCatalogue\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Modules\Mtg\Console\ImportTcgCollectorsSetCommand;
use Modules\WebCatalogue\Console\RebuildRecognitionFingerprintsCommand;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\Store;

class TemporaryTcgSeedController extends Controller
{
    public function __invoke(Request $request)
    {
        $token = (string) (config('webcatalogue.temporary_tcg_seed_token') ?: env('WEBCATALOGUE_TEMP_TCG_SEED_TOKEN', ''));

        if ($token === '' || !hash_equals($token, (string) $request->query('token', ''))) {
            abort(404);
        }

        @ignore_user_abort(true);
        @set_time_limit(0);

        $output = [];
        $output[] = 'WebCatalogue temporary TCG seed started at ' . now()->toDateTimeString();

        Artisan::registerCommand(app(ImportTcgCollectorsSetCommand::class));
        Artisan::registerCommand(app(RebuildRecognitionFingerprintsCommand::class));

        try {
            $storageExitCode = Artisan::call('storage:link');
            $output[] = 'storage:link exit code: ' . $storageExitCode;
            $output[] = trim(Artisan::output());
        } catch (\Throwable $exception) {
            $output[] = 'storage:link warning: ' . $exception->getMessage();
        }

        $seedExitCode = 1;

        try {
            $seedExitCode = Artisan::call('mtg:tcg-collectors:import-set', [
                'set_code' => 'mrd',
                '--refresh-images' => $request->boolean('refresh_images'),
            ]);

            $output[] = trim(Artisan::output());
            $output[] = 'seed exit code: ' . $seedExitCode;
        } catch (\Throwable $exception) {
            $output[] = 'seed error: ' . $exception->getMessage();
        }

        if ($request->boolean('rebuild_fingerprints')) {
            $store = Store::where('slug', 'tcg-collectors')->first();

            if ($store) {
                try {
                    $rebuildExitCode = Artisan::call('webcatalogue:recognition-rebuild-fingerprints', [
                        '--store' => $store->id,
                        '--sync' => true,
                    ]);

                    $output[] = trim(Artisan::output());
                    $output[] = 'fingerprint rebuild exit code: ' . $rebuildExitCode;
                } catch (\Throwable $exception) {
                    $output[] = 'fingerprint rebuild error: ' . $exception->getMessage();
                }
            } else {
                $output[] = 'fingerprint rebuild skipped: store not found.';
            }
        }

        $store = Store::where('slug', 'tcg-collectors')->first();
        $catalogue = $store
            ? Catalogue::where('id_store', $store->id)->where('slug', 'mtg-mirrodin')->first()
            : null;

        $output[] = 'Summary';
        $output[] = 'store_id=' . ($store?->id ?? 'missing');
        $output[] = 'catalogue_id=' . ($catalogue?->id ?? 'missing');
        $output[] = 'products=' . ($store ? Product::where('id_store', $store->id)->count() : 0);
        $output[] = 'catalogue_products=' . ($catalogue ? $catalogue->products()->count() : 0);
        $output[] = 'image_resources=' . ($store ? Resource::where('id_store', $store->id)->where('resource_type', 'image')->count() : 0);
        $output[] = 'Finished at ' . now()->toDateTimeString();

        return response(implode("\n", array_filter($output)) . "\n", $seedExitCode === 0 ? 200 : 500)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
