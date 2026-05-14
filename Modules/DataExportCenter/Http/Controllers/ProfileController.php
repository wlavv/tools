<?php

namespace Modules\DataExportCenter\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DataExportCenter\Services\ExportRegistry;
use Modules\DataExportCenter\Services\ExportSchemaBuilder;
use Modules\DataExportCenter\Support\ExportProfileTypes;

class ProfileController extends Controller
{
    public function index(ExportRegistry $registry)
    {
        return $this->view('data-export-center::profiles.index', [
            'profiles' => $registry->all(),
        ]);
    }

    public function show(string $profile, ExportRegistry $registry, ExportSchemaBuilder $schemaBuilder)
    {
        $definition = $registry->require($profile);
        $schema = null;

        if ($definition['type'] === ExportProfileTypes::MODEL && ! empty($definition['class'])) {
            $schema = $schemaBuilder->build($definition['class']);
        }

        return $this->view('data-export-center::profiles.show', [
            'profile' => $definition,
            'schema' => $schema,
        ]);
    }
}
