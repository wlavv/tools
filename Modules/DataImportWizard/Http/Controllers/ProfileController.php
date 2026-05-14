<?php

namespace Modules\DataImportWizard\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DataImportWizard\Services\ImportRegistry;
use Modules\DataImportWizard\Services\ImportSchemaBuilder;

class ProfileController extends Controller
{
    public function index(ImportRegistry $registry)
    {
        return $this->view('data-import-wizard::profiles.index', [
            'profiles' => $registry->all(),
        ]);
    }

    public function show(string $profile, ImportRegistry $registry, ImportSchemaBuilder $schemaBuilder)
    {
        $class = $registry->require($profile);

        return $this->view('data-import-wizard::profiles.show', [
            'profile' => $registry->describe($class),
            'schema' => $schemaBuilder->build($class),
        ]);
    }
}
