# AIConsensus

Módulo Laravel para agregação de respostas de múltiplos providers de IA.

## Objetivo
Permite:
- criar um pedido (run) com prompt e ficheiros
- enviar o prompt para Claude e Gemini
- consolidar as respostas no OpenAI
- guardar histórico, anexos, tokens e custo estimado
- guardar credenciais dos providers de forma encriptada

## Estrutura resumida
- `Http/Controllers`: controller do módulo
- `Http/Requests`: validações
- `Models`: models do módulo e tabelas relacionadas
- `Services`: lógica de negócio
- `Database/Migrations`: migration da tabela de credenciais
- `Database/Seeders`: seeder inicial
- `Resources/views`: UI do módulo
- `Routes/web.php`: rotas
- `Providers`: service provider do módulo

## Dependências
- Sem packages externas de modularização
- Usa apenas componentes nativos do Laravel
- Requer tabela já existente:
  - `ai_runs`
  - `ai_run_responses`
  - `ai_files`

## Tabela criada
- `ai_provider_credentials`

## Rota principal
- `/ai-consensus`

## Instalação
1. Copiar a pasta `Modules/AIConsensus` para o projeto
2. Executar os comandos abaixo

## Comandos
```bash
composer dump-autoload
php artisan optimize:clear
php artisan modules:list
php artisan modules:check
php artisan migrate --path=Modules/AIConsensus/Database/Migrations
php artisan db:seed --class="Modules\\AIConsensus\\Database\\Seeders\\AIConsensusSeeder" --force
```

## Storage
O módulo grava ficheiros em `storage/app/ai-consensus`.
Não precisa de `storage:link` porque os anexos não são públicos.

## Notas importantes
- As API keys ficam encriptadas com `Crypt`
- O parsing de PDF é best-effort sem OCR
- O parsing de DOCX lê o `word/document.xml`
- O custo estimado pode ser afinado por provider/modelo no `config.php` ou no campo `meta` da tabela `ai_provider_credentials`
- O processamento está síncrono; numa fase posterior pode ser movido para queue/jobs
