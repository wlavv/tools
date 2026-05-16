<?php

namespace Modules\Mtg\Console;

use Illuminate\Console\Command;
use Modules\Mtg\Services\TcgCollectorsWebCatalogueService;

class ImportTcgCollectorsSetCommand extends Command
{
    protected $signature = 'mtg:tcg-collectors:import-set {set_code : Scryfall set code, for example mrd, lea, ltr} {--refresh-images : Download images again even when local files exist}';

    protected $description = 'Import one MTG set into the TCG-Collectors WebCatalogue store.';

    public function handle(TcgCollectorsWebCatalogueService $service): int
    {
        $result = $service->importSet((string) $this->argument('set_code'), (bool) $this->option('refresh-images'));

        $this->info('TCG-Collectors set imported.');
        $this->line('Set: ' . $result['set']['name'] . ' (' . strtoupper($result['set']['code']) . ')');
        $this->line('Products processed: ' . $result['products']);
        $this->line('Cards processed: ' . $result['cards_processed']);
        $this->line('Resources processed: ' . $result['resources']);
        $this->line('Images downloaded/updated: ' . $result['images_downloaded']);

        return self::SUCCESS;
    }
}
