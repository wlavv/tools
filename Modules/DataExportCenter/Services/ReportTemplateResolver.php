<?php

namespace Modules\DataExportCenter\Services;

use Modules\DataExportCenter\Models\DataExportReportTemplate;

class ReportTemplateResolver
{
    public function resolve(string $profileKey, array $context = []): ?DataExportReportTemplate
    {
        foreach ($this->candidateScopes($context) as $candidate) {
            $template = DataExportReportTemplate::query()
                ->where(function ($query) use ($profileKey) {
                    $query->where('profile_key', $profileKey)->orWhereNull('profile_key');
                })
                ->where('scope_type', $candidate['scope_type'])
                ->where(function ($query) use ($candidate) {
                    if ($candidate['scope_key'] === null) {
                        $query->whereNull('scope_key');
                    } else {
                        $query->where('scope_key', (string) $candidate['scope_key']);
                    }
                })
                ->orderByRaw('case when profile_key = ? then 0 else 1 end', [$profileKey])
                ->orderByDesc('is_default')
                ->latest('updated_at')
                ->first();

            if ($template) {
                return $template;
            }
        }

        return DataExportReportTemplate::query()
            ->where(function ($query) use ($profileKey) {
                $query->where('profile_key', $profileKey)->orWhereNull('profile_key');
            })
            ->where('is_default', true)
            ->orderByRaw('case when profile_key = ? then 0 else 1 end', [$profileKey])
            ->latest('updated_at')
            ->first();
    }

    private function candidateScopes(array $context): array
    {
        $candidates = [];

        if (! empty($context['shop_id'])) {
            $candidates[] = ['scope_type' => 'shop', 'scope_key' => $context['shop_id']];
        }

        if (! empty($context['shop_key'])) {
            $candidates[] = ['scope_type' => 'shop', 'scope_key' => $context['shop_key']];
        }

        if (! empty($context['platform'])) {
            $candidates[] = ['scope_type' => 'platform', 'scope_key' => $context['platform']];
        }

        if (! empty($context['module'])) {
            $candidates[] = ['scope_type' => 'module', 'scope_key' => $context['module']];
        }

        $candidates[] = ['scope_type' => 'global', 'scope_key' => null];

        return $candidates;
    }
}
