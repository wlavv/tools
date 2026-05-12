<?php

namespace Modules\ERP\Services;

use Illuminate\Support\Collection;
use Modules\ERP\Models\ERPTimelineTask;

class ERPTimelineService
{
    public function steps(?string $activeStepKey = null): Collection
    {
        $configuredSteps = collect(config('erp.timeline.steps', []))
            ->map(function (array $step, string $key) {
                return array_merge($step, ['key' => $key]);
            })
            ->sortBy('sort_order')
            ->values();

        $tasks = ERPTimelineTask::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('step_key');

        $activeStepKey = $activeStepKey ?: $configuredSteps->first()['key'];

        return $configuredSteps->map(function (array $step, int $index) use ($tasks, $activeStepKey) {
            $stepTasks = $tasks->get($step['key'], collect());

            if ($stepTasks->isEmpty()) {
                $stepTasks = collect($this->fallbackTasks($step['key']));
            }

            $completed = $stepTasks->where('status', 'completed')->count();
            $total = max($stepTasks->count(), 1);
            $progress = (int) round(($completed / $total) * 100);

            $status = 'locked';

            if ($step['key'] === $activeStepKey) {
                $status = 'active';
            } elseif ($index === 0 || $progress > 0) {
                $status = 'pending';
            }

            return [
                'key' => $step['key'],
                'label' => trans($step['label']),
                'description' => trans($step['description']),
                'icon' => $step['icon'],
                'sort_order' => $step['sort_order'],
                'status' => $status,
                'pending' => $stepTasks->where('status', '!=', 'completed')->count(),
                'progress' => $progress,
                'tasks' => $stepTasks->map(fn ($task) => is_array($task) ? $task : [
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'icon' => $task->icon,
                    'is_required' => $task->is_required,
                ])->values(),
            ];
        });
    }

    private function fallbackTasks(string $stepKey): array
    {
        return match ($stepKey) {
            'supplier_selection' => [
                ['title' => 'Selecionar fornecedor', 'status' => 'pending', 'icon' => 'fa-solid fa-truck-field', 'is_required' => true],
                ['title' => 'Validar moeda e condições comerciais', 'status' => 'pending', 'icon' => 'fa-solid fa-coins', 'is_required' => true],
                ['title' => 'Confirmar escalão atual e próximo objetivo', 'status' => 'pending', 'icon' => 'fa-solid fa-layer-group', 'is_required' => false],
            ],
            'order_note' => [
                ['title' => 'Adicionar produtos à nota', 'status' => 'pending', 'icon' => 'fa-solid fa-plus', 'is_required' => true],
                ['title' => 'Validar quantidades pretendidas', 'status' => 'pending', 'icon' => 'fa-solid fa-list-ol', 'is_required' => true],
                ['title' => 'Preparar exportação para fornecedor', 'status' => 'pending', 'icon' => 'fa-solid fa-file-export', 'is_required' => false],
            ],
            'billing' => [
                ['title' => 'Registar documento de fornecedor', 'status' => 'pending', 'icon' => 'fa-solid fa-file-invoice', 'is_required' => true],
                ['title' => 'Validar preços de custo', 'status' => 'pending', 'icon' => 'fa-solid fa-euro-sign', 'is_required' => true],
                ['title' => 'Atualizar histórico de preços', 'status' => 'pending', 'icon' => 'fa-solid fa-clock-rotate-left', 'is_required' => true],
            ],
            'reception' => [
                ['title' => 'Confirmar quantidades recebidas', 'status' => 'pending', 'icon' => 'fa-solid fa-boxes-stacked', 'is_required' => true],
                ['title' => 'Atualizar stock PrestaShop', 'status' => 'pending', 'icon' => 'fa-solid fa-database', 'is_required' => true],
                ['title' => 'Gerar histórico de receção', 'status' => 'pending', 'icon' => 'fa-solid fa-clipboard-check', 'is_required' => true],
            ],
            'validation' => [
                ['title' => 'Validar diferenças de preço', 'status' => 'pending', 'icon' => 'fa-solid fa-scale-balanced', 'is_required' => true],
                ['title' => 'Validar diferenças de quantidade', 'status' => 'pending', 'icon' => 'fa-solid fa-not-equal', 'is_required' => true],
                ['title' => 'Associar documentos e notas internas', 'status' => 'pending', 'icon' => 'fa-solid fa-paperclip', 'is_required' => false],
            ],
            'closed' => [
                ['title' => 'Confirmar receção total', 'status' => 'pending', 'icon' => 'fa-solid fa-circle-check', 'is_required' => true],
                ['title' => 'Fechar documento ERP', 'status' => 'pending', 'icon' => 'fa-solid fa-lock', 'is_required' => true],
                ['title' => 'Atualizar métricas do fornecedor', 'status' => 'pending', 'icon' => 'fa-solid fa-chart-simple', 'is_required' => false],
            ],
            default => [],
        };
    }
}
