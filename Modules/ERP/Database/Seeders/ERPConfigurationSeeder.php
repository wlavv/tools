<?php

namespace Modules\ERP\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ERP\Models\ERPConfiguration;
use Modules\ERP\Models\ERPDocumentType;
use Modules\ERP\Models\ERPStatus;
use Modules\ERP\Models\ERPNumberingSequence;
use Modules\ERP\Models\ERPDashboardWidget;
use Modules\ERP\Models\ERPTimelineTask;

class ERPConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        ERPConfiguration::updateOrCreate(
            ['group' => 'general', 'key' => 'module_name'],
            ['value' => 'ERP', 'type' => 'string', 'is_public' => true]
        );

        ERPConfiguration::updateOrCreate(
            ['group' => 'legacy', 'key' => 'previous_module'],
            ['value' => 'OMS', 'type' => 'string', 'is_public' => false]
        );

        $types = [
            ['code' => 'order_note', 'name' => 'Order Note', 'icon' => 'fa-solid fa-clipboard-list', 'color' => '#0d6efd', 'affects_stock' => false, 'affects_prices' => false],
            ['code' => 'invoice', 'name' => 'Billed Order', 'icon' => 'fa-solid fa-file-invoice', 'color' => '#198754', 'affects_stock' => false, 'affects_prices' => true, 'is_financial' => true],
            ['code' => 'reception', 'name' => 'Reception', 'icon' => 'fa-solid fa-boxes-stacked', 'color' => '#fd7e14', 'affects_stock' => true, 'affects_prices' => false],
            ['code' => 'credit_note', 'name' => 'Credit Note', 'icon' => 'fa-solid fa-rotate-left', 'color' => '#dc3545', 'affects_stock' => true, 'affects_prices' => false, 'is_financial' => true],
        ];

        foreach ($types as $index => $type) {
            ERPDocumentType::updateOrCreate(
                ['code' => $type['code']],
                array_merge(['is_active' => true, 'sort_order' => ($index + 1) * 10], $type)
            );
        }

        $statuses = [
            ['scope' => 'document', 'code' => 'draft', 'name' => 'Draft', 'color' => '#6c757d', 'icon' => 'fa-regular fa-file', 'is_initial' => true],
            ['scope' => 'document', 'code' => 'open', 'name' => 'Open', 'color' => '#0d6efd', 'icon' => 'fa-solid fa-folder-open'],
            ['scope' => 'document', 'code' => 'partial', 'name' => 'Partial', 'color' => '#fd7e14', 'icon' => 'fa-solid fa-circle-half-stroke'],
            ['scope' => 'document', 'code' => 'closed', 'name' => 'Closed', 'color' => '#198754', 'icon' => 'fa-solid fa-check', 'is_final' => true],
            ['scope' => 'document', 'code' => 'cancelled', 'name' => 'Cancelled', 'color' => '#dc3545', 'icon' => 'fa-solid fa-ban', 'is_final' => true],
        ];

        foreach ($statuses as $index => $status) {
            ERPStatus::updateOrCreate(
                ['scope' => $status['scope'], 'code' => $status['code']],
                array_merge(['is_active' => true, 'sort_order' => ($index + 1) * 10], $status)
            );
        }

        $sequences = [
            ['document_type_code' => 'order_note', 'prefix' => 'ERP-ON', 'pattern' => 'ERP-ON-{Y}-{00000}'],
            ['document_type_code' => 'invoice', 'prefix' => 'ERP-INV', 'pattern' => 'ERP-INV-{Y}-{00000}'],
            ['document_type_code' => 'reception', 'prefix' => 'ERP-REC', 'pattern' => 'ERP-REC-{Y}-{00000}'],
            ['document_type_code' => 'credit_note', 'prefix' => 'ERP-CN', 'pattern' => 'ERP-CN-{Y}-{00000}'],
        ];

        foreach ($sequences as $sequence) {
            ERPNumberingSequence::updateOrCreate(
                ['document_type_code' => $sequence['document_type_code'], 'year' => now()->year],
                array_merge($sequence, ['year' => now()->year, 'padding' => 5, 'reset_yearly' => true, 'is_active' => true])
            );
        }

        $widgets = [
            ['key' => 'open_order_notes', 'title' => 'Open Order Notes', 'icon' => 'fa-solid fa-clipboard-list', 'component' => 'erp::partials.widgets.metric', 'zone' => 'left'],
            ['key' => 'billed_not_received', 'title' => 'Billed Not Received', 'icon' => 'fa-solid fa-file-invoice', 'component' => 'erp::partials.widgets.metric', 'zone' => 'center'],
            ['key' => 'supplier_terms', 'title' => 'Supplier Terms', 'icon' => 'fa-solid fa-layer-group', 'component' => 'erp::partials.widgets.supplier_terms', 'zone' => 'right'],
        ];

        foreach ($widgets as $index => $widget) {
            ERPDashboardWidget::updateOrCreate(
                ['key' => $widget['key']],
                array_merge($widget, ['is_enabled' => true, 'sort_order' => ($index + 1) * 10])
            );
        }

        $timelineTasks = [
            ['supplier_selection', 'select_supplier', 'Selecionar fornecedor', 'fa-solid fa-truck-field', 10],
            ['supplier_selection', 'validate_currency_terms', 'Validar moeda e condições comerciais', 'fa-solid fa-coins', 20],
            ['supplier_selection', 'check_supplier_level', 'Confirmar escalão atual e próximo objetivo', 'fa-solid fa-layer-group', 30],

            ['order_note', 'add_products', 'Adicionar produtos à nota', 'fa-solid fa-plus', 10],
            ['order_note', 'validate_quantities', 'Validar quantidades pretendidas', 'fa-solid fa-list-ol', 20],
            ['order_note', 'prepare_export', 'Preparar exportação para fornecedor', 'fa-solid fa-file-export', 30],

            ['billing', 'register_supplier_document', 'Registar documento de fornecedor', 'fa-solid fa-file-invoice', 10],
            ['billing', 'validate_cost_prices', 'Validar preços de custo', 'fa-solid fa-euro-sign', 20],
            ['billing', 'update_price_history', 'Atualizar histórico de preços', 'fa-solid fa-clock-rotate-left', 30],

            ['reception', 'confirm_received_quantities', 'Confirmar quantidades recebidas', 'fa-solid fa-boxes-stacked', 10],
            ['reception', 'update_prestashop_stock', 'Atualizar stock PrestaShop', 'fa-solid fa-database', 20],
            ['reception', 'generate_reception_history', 'Gerar histórico de receção', 'fa-solid fa-clipboard-check', 30],

            ['validation', 'validate_price_differences', 'Validar diferenças de preço', 'fa-solid fa-scale-balanced', 10],
            ['validation', 'validate_quantity_differences', 'Validar diferenças de quantidade', 'fa-solid fa-not-equal', 20],
            ['validation', 'attach_documents_notes', 'Associar documentos e notas internas', 'fa-solid fa-paperclip', 30],

            ['closed', 'confirm_total_reception', 'Confirmar receção total', 'fa-solid fa-circle-check', 10],
            ['closed', 'close_erp_document', 'Fechar documento ERP', 'fa-solid fa-lock', 20],
            ['closed', 'update_supplier_metrics', 'Atualizar métricas do fornecedor', 'fa-solid fa-chart-simple', 30],
        ];

        foreach ($timelineTasks as [$step, $key, $title, $icon, $order]) {
            ERPTimelineTask::updateOrCreate(
                ['step_key' => $step, 'task_key' => $key],
                [
                    'title' => $title,
                    'icon' => $icon,
                    'status' => 'pending',
                    'is_required' => true,
                    'is_active' => true,
                    'sort_order' => $order,
                ]
            );
        }
    }
}
