<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AI\AiGatewayService;
use Illuminate\Http\Request;
use Throwable;

class LsgAiGatewayController extends Controller
{
    protected bool $hasPageActions = false;

    public function index(AiGatewayService $ai)
    {
        $this->preparePageMeta();

        return $this->view('admin.lsg-ai.index', [
            'health' => $this->safeHealth($ai),
            'prompt' => $this->defaultPrompt(),
            'result' => null,
            'error' => null,
            'config' => $this->publicConfig(),
            'interfaces' => $this->interfaces(),
        ]);
    }

    public function test(Request $request, AiGatewayService $ai)
    {
        $this->preparePageMeta();

        $data = $request->validate([
            'prompt' => ['required', 'string', 'max:8000'],
        ]);

        $health = $this->safeHealth($ai);
        $result = null;
        $error = null;

        try {
            $result = $ai->generate($data['prompt']);
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }

        return $this->view('admin.lsg-ai.index', [
            'health' => $health,
            'prompt' => $data['prompt'],
            'result' => $result,
            'error' => $error,
            'config' => $this->publicConfig(),
            'interfaces' => $this->interfaces(),
        ]);
    }

    public function smoke(AiGatewayService $ai)
    {
        return response()->json([
            'health' => $ai->health(),
            'generate' => $ai->generate($this->defaultPrompt()),
        ]);
    }

    private function safeHealth(AiGatewayService $ai): array
    {
        try {
            return [
                'ok' => true,
                'data' => $ai->health(),
                'error' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'data' => [],
                'error' => $exception->getMessage(),
            ];
        }
    }

    private function publicConfig(): array
    {
        return [
            'gateway_url' => config('lsg_ai.gateway_url'),
            'timeout' => config('lsg_ai.timeout'),
            'default_model' => config('lsg_ai.default_model'),
            'token_configured' => filled(config('lsg_ai.token')) && config('lsg_ai.token') !== 'COLOCAR_TOKEN_AQUI',
        ];
    }

    private function interfaces(): array
    {
        return [
            [
                'label' => 'Document OCR',
                'summary' => 'OCR de imagens/PDFs e texto extraido no Documents Manager.',
                'icon' => 'fa-solid fa-file-lines',
                'route' => 'document-manager.ai.index',
            ],
            [
                'label' => 'Documents Manager',
                'summary' => 'Documentos, anexos, OCR, resumos e analise documental.',
                'icon' => 'fa-solid fa-folder-tree',
                'route' => 'document-manager.dashboard',
            ],
            [
                'label' => 'AI Consensus',
                'summary' => 'Runs, templates, providers e logs AI existentes.',
                'icon' => 'fa-solid fa-star-of-life',
                'route' => 'ai_consensus.index',
            ],
            [
                'label' => 'WebCatalogue Recognition',
                'summary' => 'Reconhecimento visual/OCR de produtos no WebCatalogue.',
                'icon' => 'fa-solid fa-camera-retro',
                'route' => 'webcatalogue.recognition.index',
            ],
        ];
    }

    private function defaultPrompt(): string
    {
        return 'Responde em português europeu numa frase: o Laravel está ligado ao servidor AI LSG?';
    }

    private function preparePageMeta(): void
    {
        $this->setPageTitle('LSG AI Gateway');
        $this->setBreadcrumbs([
            [
                'label' => 'Administration',
                'url' => route('administration.index'),
                'params' => [],
                'translate' => false,
            ],
            [
                'label' => 'LSG AI Gateway',
                'url' => route('admin.lsg-ai.index'),
                'params' => [],
                'translate' => false,
            ],
        ]);
    }
}
