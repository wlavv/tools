<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeCustomModuleCommand extends Command
{
    protected $signature = 'make:custom-module {name} {--slug=}';
    protected $description = 'Create a new custom module skeleton inside /Modules';

    public function handle(Filesystem $files): int
    {
        $name = Str::studly($this->argument('name'));
        $slug = $this->option('slug') ?: Str::kebab($name);
        $modulePath = base_path('Modules/' . $name);

        if ($files->exists($modulePath)) {
            $this->error("Module already exists: {$modulePath}");
            return self::FAILURE;
        }

        $directories = [
            'Config',
            'Http/Controllers',
            'Http/Requests',
            'Models',
            'Services',
            'Database/Migrations',
            'Database/Seeders',
            'Resources/views/Includes/_components',
            'Resources/views/pages',
            'Routes',
            'Providers',
        ];

        foreach ($directories as $directory) {
            $files->makeDirectory($modulePath . '/' . $directory, 0755, true);
        }

        $namespace = "Modules\\{$name}";
        $snake = Str::snake($name);
        $title = Str::headline($name);

        $manifest = [
            'name' => $name,
            'slug' => $slug,
            'enabled' => true,
            'version' => '1.0.0',
            'description' => "Module {$title}",
            'provider' => "{$namespace}\\Providers\\{$name}ServiceProvider",
            'dependencies' => [
                'composer' => [],
                'npm' => [],
                'internal_modules' => [],
            ],
        ];

        $files->put($modulePath . '/module.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $files->put($modulePath . '/README.md', "# {$title}\n");
        $files->put($modulePath . '/Config/config.php', "<?php\n\nreturn [\n    'enabled' => true,\n];\n");

        $files->put($modulePath . "/Http/Controllers/{$name}Controller.php", $this->controllerStub($namespace, $name, $slug));
        $files->put($modulePath . "/Http/Requests/Store{$name}Request.php", $this->requestStub($namespace, "Store{$name}Request"));
        $files->put($modulePath . "/Http/Requests/Update{$name}Request.php", $this->requestStub($namespace, "Update{$name}Request"));
        $files->put($modulePath . "/Models/{$name}.php", $this->modelStub($namespace, $name, $snake));
        $files->put($modulePath . "/Services/{$name}Service.php", $this->serviceStub($namespace, $name));
        $files->put($modulePath . '/Resources/views/Index.blade.php', $this->indexStub($slug, $title));
        $files->put($modulePath . '/Resources/views/Includes/css.blade.php', $this->cssStub($slug));
        $files->put($modulePath . '/Resources/views/Includes/js.blade.php', $this->jsStub($slug));
        $files->put($modulePath . '/Resources/views/pages/show.blade.php', "<div>{$title} show page</div>\n");
        $files->put($modulePath . '/Routes/web.php', $this->routesStub($namespace, $name, $slug, $snake));
        $files->put($modulePath . "/Providers/{$name}ServiceProvider.php", $this->providerStub($namespace, $name, $slug));

        $this->info("Module created: {$modulePath}");

        return self::SUCCESS;
    }

    protected function controllerStub(string $namespace, string $name, string $slug): string
    {
        return <<<PHP
<?php

namespace {$namespace}\Http\Controllers;

use Illuminate\Routing\Controller;

class {$name}Controller extends Controller
{
    public function index()
    {
        return view('{$slug}::Index');
    }
}
PHP;
    }

    protected function requestStub(string $namespace, string $class): string
    {
        return <<<PHP
<?php

namespace {$namespace}\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {$class} extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
PHP;
    }

    protected function modelStub(string $namespace, string $name, string $snake): string
    {
        return <<<PHP
<?php

namespace {$namespace}\Models;

use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    protected \$table = '{$snake}';

    protected \$guarded = [];
}
PHP;
    }

    protected function serviceStub(string $namespace, string $name): string
    {
        return <<<PHP
<?php

namespace {$namespace}\Services;

class {$name}Service
{
}
PHP;
    }

    protected function indexStub(string $slug, string $title): string
    {
        return <<<BLADE
@include('{$slug}::Includes.css')

<div class="module-page {$slug}-page">
    <h1>{$title}</h1>
</div>

@include('{$slug}::Includes.js')
BLADE;
    }

    protected function cssStub(string $slug): string
    {
        return <<<BLADE
<style>
.{$slug}-page {
    padding: 1rem;
}
</style>
BLADE;
    }

    protected function jsStub(string $slug): string
    {
        return <<<BLADE
<script>
console.log('{$slug} loaded');
</script>
BLADE;
    }

    protected function routesStub(string $namespace, string $name, string $slug, string $snake): string
    {
        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;
use {$namespace}\Http\Controllers\{$name}Controller;

Route::middleware(['web', 'auth'])
    ->prefix('{$slug}')
    ->name('{$snake}.')
    ->group(function () {
        Route::get('/', [{$name}Controller::class, 'index'])->name('index');
    });
PHP;
    }

    protected function providerStub(string $namespace, string $name, string $slug): string
    {
        return <<<PHP
<?php

namespace {$namespace}\Providers;

use Illuminate\Support\ServiceProvider;

class {$name}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$modulePath = dirname(__DIR__);

        if (file_exists(\$modulePath . '/Config/config.php')) {
            \$this->mergeConfigFrom(\$modulePath . '/Config/config.php', '{$slug}');
        }
    }

    public function boot(): void
    {
        \$modulePath = dirname(__DIR__);

        \$this->loadRoutesFrom(\$modulePath . '/Routes/web.php');
        \$this->loadViewsFrom(\$modulePath . '/Resources/views', '{$slug}');
        \$this->loadMigrationsFrom(\$modulePath . '/Database/Migrations');
    }
}
PHP;
    }
}
