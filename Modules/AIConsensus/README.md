# AI Consensus

Servico central de inteligencia do WebTools Manager / B.O. Custom LSG.

O modulo mantem compatibilidade com o fluxo antigo de runs manuais, mas o ponto de entrada recomendado para todos os modulos passa a ser:

```php
use Modules\AIConsensus\Services\AIConsensusGateway;

$run = app(AIConsensusGateway::class)->createRun([
    'source_module' => 'IdeaLab',
    'source_type' => 'project_idea',
    'source_id' => $idea->id,
    'template_key' => 'idealab.project_idea_to_lsg_module',
    'output_type' => 'lsg_module_blueprint',
    'input_payload' => [
        'title' => $idea->title,
        'description' => $idea->description,
        'business_context' => 'WebTools Manager / LSG',
    ],
    'options' => [
        'language' => 'pt',
        'tone' => 'technical',
        'consensus_mode' => 'architect_reviewer',
        'return_format' => 'json',
        'store_result' => true,
        'allow_code_generation' => false,
        'async' => true,
    ],
    'requested_by' => auth()->id(),
]);
```

## Fluxo

Modulo origem -> `AIConsensusGateway` -> Template Resolver -> Context Builder -> Run Creator -> Provider Orchestrator -> Consensus Engine -> Output Normalizer -> Result Storage.

## Estrutura central

- `Config/`: providers, templates, output types, regras de consensus e standard LSG.
- `Models/`: runs, templates, providers, messages, outputs, contexts e logs.
- `Services/`: gateway, resolvers, orquestracao, normalizacao e chat.
- `Jobs/`: processamento de runs e base para providers/resultados.
- `Routes/web.php`: UI B.O.
- `Routes/api.php`: endpoint interno `POST /ai-consensus/api/runs`.

## Setup

```bash
php artisan migrate --path=Modules/AIConsensus/Database/Migrations --force
php artisan db:seed --class="Modules\\AIConsensus\\Database\\Seeders\\AIConsensusCentralSeeder" --force
```

## Queue

Runs assincronos usam o mesmo job do AI Consensus legado:

```php
Modules\AIConsensus\Jobs\ProcessAIConsensusRunJob
```

Por defeito a queue e `ai-consensus`. Em producao, garantir que o worker escuta essa queue ou definir:

```env
AI_CONSENSUS_QUEUE=default
```

Exemplo:

```bash
php artisan queue:work --queue=ai-consensus,default --tries=3 --timeout=1800
```

Para IdeaLab:

```bash
php artisan migrate --path=Modules/IdeaLab/Database/Migrations --force
```

## Seguranca

- Providers reais ficam inativos por defeito.
- O provider ativo inicial e `internal_rules_engine`, sem chamadas externas.
- O modulo guarda prompts, contexto, respostas e logs.
- Geracao de codigo fica apenas preparada; nada e aplicado automaticamente.
- Outputs criticos devem ser aprovados por humano antes de alimentar automacoes.
