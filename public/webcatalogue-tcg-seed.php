<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Console\RebuildRecognitionFingerprintsCommand;
use Modules\WebCatalogue\Console\SeedTcgCollectorsMirrodinCommand;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\Store;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$temporaryFallbackToken = '123cc97024677566686aab57ca7dfc67c2664c8edeec6980';
$configuredToken = (string) (env('WEBCATALOGUE_TEMP_TCG_SEED_TOKEN') ?: $temporaryFallbackToken);
$requestToken = (string) ($_GET['token'] ?? '');

if ($configuredToken === '' || !hash_equals($configuredToken, $requestToken)) {
    http_response_code(404);
    echo "Not found\n";
    exit;
}

ignore_user_abort(true);
set_time_limit(0);

header('Content-Type: text/plain; charset=UTF-8');

$output = [];
$output[] = 'WebCatalogue temporary TCG seed started at ' . now()->toDateTimeString();

Artisan::registerCommand($app->make(SeedTcgCollectorsMirrodinCommand::class));
Artisan::registerCommand($app->make(RebuildRecognitionFingerprintsCommand::class));

try {
    $storageExitCode = Artisan::call('storage:link');
    $output[] = 'storage:link exit code: ' . $storageExitCode;
    $output[] = trim(Artisan::output());
} catch (Throwable $exception) {
    $output[] = 'storage:link warning: ' . $exception->getMessage();
}

$seedExitCode = 1;

try {
    $seedExitCode = Artisan::call('webcatalogue:seed-tcg-collectors-mirrodin', [
        '--refresh-images' => filter_var($_GET['refresh_images'] ?? false, FILTER_VALIDATE_BOOL),
    ]);

    $output[] = trim(Artisan::output());
    $output[] = 'seed exit code: ' . $seedExitCode;
} catch (Throwable $exception) {
    $output[] = 'seed error: ' . $exception->getMessage();
}

if (filter_var($_GET['rebuild_fingerprints'] ?? false, FILTER_VALIDATE_BOOL)) {
    $store = Store::where('slug', 'tcg-collectors')->first();

    if ($store) {
        try {
            $rebuildExitCode = Artisan::call('webcatalogue:recognition-rebuild-fingerprints', [
                '--store' => $store->id,
                '--sync' => true,
            ]);

            $output[] = trim(Artisan::output());
            $output[] = 'fingerprint rebuild exit code: ' . $rebuildExitCode;
        } catch (Throwable $exception) {
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

http_response_code($seedExitCode === 0 ? 200 : 500);
echo implode("\n", array_filter($output)) . "\n";
