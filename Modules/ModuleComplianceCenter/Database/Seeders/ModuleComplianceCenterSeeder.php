<?php

namespace Modules\ModuleComplianceCenter\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ModuleComplianceCenter\Services\ComplianceValidatorRegistry;

class ModuleComplianceCenterSeeder extends Seeder
{
    public function run(): void
    {
        app(ComplianceValidatorRegistry::class)->sync();
    }
}
