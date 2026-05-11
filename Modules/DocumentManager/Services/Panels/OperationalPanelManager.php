<?php

namespace Modules\DocumentManager\Services\Panels;

use Modules\DocumentManager\Support\DocumentTable;

class OperationalPanelManager
{
    public function resolve(): array
    {
        return [
            'missing_ocr' => [
                'label' => 'Sem OCR',
                'icon' => 'fa-solid fa-file-lines',
                'count' => DocumentTable::count('document_core_documents', function ($query) {
                    $query->where('has_file', true)->where('has_ocr', false);
                }),
                'tone' => 'warning',
            ],
            'missing_preview' => [
                'label' => 'Sem preview',
                'icon' => 'fa-solid fa-eye-slash',
                'count' => DocumentTable::count('document_core_documents', function ($query) {
                    $query->where('has_file', true)->where('has_preview', false);
                }),
                'tone' => 'info',
            ],
            'pending_approvals' => [
                'label' => 'Aprovacoes pendentes',
                'icon' => 'fa-solid fa-clipboard-check',
                'count' => DocumentTable::count('document_workflow_approvals', function ($query) {
                    $query->where('status', 'pending');
                }),
                'tone' => 'primary',
            ],
            'expiring_documents' => [
                'label' => 'A expirar',
                'icon' => 'fa-solid fa-hourglass-half',
                'count' => DocumentTable::count('document_core_documents', function ($query) {
                    $query->whereNotNull('expires_at')->where('expires_at', '<=', now()->addDays(30));
                }),
                'tone' => 'danger',
            ],
        ];
    }
}
