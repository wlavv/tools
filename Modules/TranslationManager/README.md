# Translation Manager — WebTools Manager / LSG

Módulo Laravel para gerir traduções dos módulos diretamente no B.O. sem alterar ficheiros versionados pelo Git.

## O que inclui

- Lista módulos em `Modules/*`.
- Lê traduções base em:
  - `Modules/{Module}/Resources/lang/{locale}/*.php`
  - `Modules/{Module}/lang/{locale}/*.php`
- Grava overrides em:
  - `storage/app/translations/modules/{module_slug}/{locale}/{file}.php`
- Mostra contadores por módulo e por ficheiro:
  - total de tags base
  - total de tags custom
  - tags sem override
  - tags vazias
  - tags extra/obsoletas
  - ficheiros sem custom
- Nunca altera os ficheiros base dos módulos.

## Instalação

Copiar para o projeto:

```text
Modules/TranslationManager
app/Support/Translations
```

Adicionar ao `.gitignore`:

```gitignore
/storage/app/translations/
```

Adicionar o provider global ao `config/app.php`, idealmente antes dos providers dos módulos:

```php
App\Support\Translations\StorageTranslationOverrideServiceProvider::class,
```

Depois limpar cache:

```bash
php artisan optimize:clear
```

## Ajuste recomendado nos ServiceProviders dos módulos

Antes:

```php
$this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'notifications');
```

Depois:

```php
use App\Support\Translations\LoadsModuleTranslationsWithOverrides;

class NotificationsServiceProvider extends ServiceProvider
{
    use LoadsModuleTranslationsWithOverrides;

    public function boot(): void
    {
        $this->loadModuleTranslationsWithOverrides(__DIR__ . '/../Resources/lang', 'notifications');
    }
}
```

Nota: o merge runtime é feito pelo provider global `StorageTranslationOverrideServiceProvider`. O trait serve para manter todos os providers alinhados e explícitos.

## Como funciona o runtime

Quando o Laravel resolve:

```php
__('notifications::messages.title')
```

O loader lê primeiro o ficheiro base:

```text
Modules/Notifications/Resources/lang/pt/messages.php
```

Depois aplica override, se existir:

```text
storage/app/translations/modules/notifications/pt/messages.php
```

Resultado:

```text
base + override = tradução final
```

## Rota

```text
/translation-manager
```

Pode ser alterado em:

```text
Modules/TranslationManager/Routes/web.php
```

## Configuração

```text
Modules/TranslationManager/Config/config.php
```

Principais opções:

```php
'modules_path' => base_path('Modules'),
'override_path' => storage_path('app/translations/modules'),
'default_locale' => 'pt',
'route_middleware' => ['web', 'auth'],
```
