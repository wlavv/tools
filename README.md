# Webtools Manager

## LSG AI Gateway

The application integrates with the local LSG AI server through `App\Services\AI\AiGatewayService`.
Keep gateway credentials in `.env`; do not hardcode URLs, tokens, or model names in controllers or modules.

```env
LSG_AI_GATEWAY_URL=https://api-ai.lsg-labs.com
LSG_AI_GATEWAY_TOKEN=COLOCAR_TOKEN_AQUI
LSG_AI_GATEWAY_TIMEOUT=180
LSG_AI_ADMIN_TOKEN=COLOCAR_TOKEN_ADMIN_AQUI
LSG_AI_BACKUP_TIMEOUT=300
LSG_AI_DEFAULT_MODEL=qwen2.5:7b
```

Used endpoints:

- `GET /health`
- `POST /api/llm/generate`
- `POST /api/llm/chat`
- `POST /api/ocr/image`
- `POST /api/ocr/pdf`
- `POST /api/vision/analyze`
- `POST /api/vision/phash`
- `POST /api/vision/compare-phash`
- `POST /api/documents/extract-expense`
- `GET /api/admin/backups`
- `POST /api/admin/backups/create`
- `GET /api/admin/backups/{filename}`
- `GET /api/admin/backups/{filename}/download`
- `GET /api/admin/backups/{filename}/checksum`
- `GET /api/admin/backups/{filename}/manifest`
- `GET /api/admin/backups/logs`
- `DELETE /api/admin/backups/{filename}`

Validation commands:

```bash
php artisan config:clear
php artisan cache:clear
php artisan lsg:ai-test
php artisan lsg:ocr-test --file=/path/to/test.pdf
php artisan lsg:ai-extract-expense --file=/path/to/test-invoice.pdf
```

AI Server backup administration:

- BO path: `Admin -> AI Server Backups`
- Laravel routes are under `admin/infrastructure/ai-backups`.
- Admin token is sent only server-side with `x-lsg-ai-admin-token`.
- Downloads pass through Laravel and are audited in `infrastructure_backup_logs`.
- The FastAPI side still needs the admin endpoints documented in `docs/lsg-ai-gateway-admin-backups.md` if they are not already deployed.

Optional prompt:

```bash
php artisan lsg:ai-test "Resume este documento em três pontos."
```

Service examples:

```php
use App\Services\AI\AiGatewayService;

$ai->generate('Classifica esta despesa: ' . $description);

$ai->generate('Cria uma descrição SEO para este produto: ' . $productName);

$ai->chat([
    ['role' => 'system', 'content' => 'És um assistente interno do grupo LSG.'],
    ['role' => 'user', 'content' => 'Resume este documento.'],
]);
```

The local/staging route `GET /admin/ai-test` is available behind `auth` only outside production and should be removed or further protected before any production exposure.

LSG AI OCR is enabled for Document Manager with:

```env
DOCUMENT_MANAGER_OCR_PROVIDER=lsg_ai
```

Document flow:

- Upload or open a document in Documents Manager.
- Use `Processar OCR` from the document detail.
- Open `Ver OCR` to inspect extracted text.
- Results are stored in the existing `document_ai_ocr` table and linked to the document/version.
- Use `Processar AI / Despesa` from the document detail to call `/api/documents/extract-expense`.
- Open `Ver resultado AI` to inspect OCR text, extracted expense fields, confidence, notes and raw payload.
- Expense suggestions are stored in `document_manager_ai_results`; OCR text is also stored in `document_ai_ocr`.
- `Criar despesa a partir da sugestao` redirects to an Expense Manager create route if one is registered. If the route is not available in this checkout, Document Manager shows the prefill payload for mapping.
- The application never creates an expense automatically; a user must validate the suggested data first.
- Active expenses must keep an associated `document_id`.

Expense extraction command:

```bash
php artisan migrate
php artisan config:clear
php artisan cache:clear
php artisan lsg:ai-extract-expense 123
php artisan lsg:ai-extract-expense --file=/path/to/invoice.pdf
```

Service examples:

```php
use App\Services\AI\DocumentAiService;

$result = $documentsAi->extractExpenseFromDocument($document);
$latest = $documentsAi->getLatestAiResult($document, 'extract_expense');
```

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
