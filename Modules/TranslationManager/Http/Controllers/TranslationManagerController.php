<?php

namespace Modules\TranslationManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\TranslationManager\Services\ModuleTranslationDiscoveryService;
use Modules\TranslationManager\Services\ModuleTranslationReaderService;
use Modules\TranslationManager\Services\ModuleTranslationWriterService;

class TranslationManagerController extends Controller
{
    public function __construct(
        protected ModuleTranslationDiscoveryService $discovery,
        protected ModuleTranslationReaderService $reader,
        protected ModuleTranslationWriterService $writer
    ) {
        parent::__construct();
    }

    public function index(Request $request): View
    {
        $locale = (string) $request->query('locale', config('translation-manager.default_locale', app()->getLocale()));
        $modules = $this->discovery->listModules($locale);

        $selectedModuleSlug = (string) $request->query('module', ($modules[0]['slug'] ?? ''));
        $selectedFile = (string) $request->query('file', '');

        $selectedModule = collect($modules)->firstWhere('slug', $selectedModuleSlug);
        $payload = null;

        if ($selectedModule) {
            $files = $this->discovery->listTranslationFiles($selectedModule, $locale);
            $selectedFile = $selectedFile ?: ($files[0]['file'] ?? '');

            if ($selectedFile) {
                $payload = $this->reader->read($selectedModule, $locale, $selectedFile);
            }
        }

        return $this->view('translation-manager::index', [
            'modules' => $modules,
            'selectedModule' => $selectedModule,
            'selectedModuleSlug' => $selectedModuleSlug,
            'selectedFile' => $selectedFile,
            'locale' => $locale,
            'locales' => config('translation-manager.locales', []),
            'files' => $selectedModule ? $this->discovery->listTranslationFiles($selectedModule, $locale) : [],
            'payload' => $payload,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'module' => ['required', 'string'],
            'locale' => ['required', 'string'],
            'file' => ['required', 'string'],
            'translations' => ['array'],
            'translations.*' => ['nullable', 'string'],
        ]);

        $module = $this->discovery->findModule($data['module']);
        abort_if(! $module, 404, 'Module not found.');

        $this->writer->writeOverride(
            $module,
            $data['locale'],
            $data['file'],
            $data['translations'] ?? []
        );

        return redirect()
            ->route('translation_manager.index', [
                'module' => $data['module'],
                'locale' => $data['locale'],
                'file' => $data['file'],
            ])
            ->with('success', __('translation-manager::messages.saved'));
    }

    public function removeOverride(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'module' => ['required', 'string'],
            'locale' => ['required', 'string'],
            'file' => ['required', 'string'],
        ]);

        $module = $this->discovery->findModule($data['module']);
        abort_if(! $module, 404, 'Module not found.');

        $this->writer->deleteOverrideFile($module, $data['locale'], $data['file']);

        return redirect()
            ->route('translation_manager.index', [
                'module' => $data['module'],
                'locale' => $data['locale'],
                'file' => $data['file'],
            ])
            ->with('success', __('translation-manager::messages.override_removed'));
    }

    public function removeExtraKey(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'module' => ['required', 'string'],
            'locale' => ['required', 'string'],
            'file' => ['required', 'string'],
            'key' => ['required', 'string'],
        ]);

        $module = $this->discovery->findModule($data['module']);
        abort_if(! $module, 404, 'Module not found.');

        $this->writer->removeKey($module, $data['locale'], $data['file'], $data['key']);

        return redirect()
            ->route('translation_manager.index', [
                'module' => $data['module'],
                'locale' => $data['locale'],
                'file' => $data['file'],
            ])
            ->with('success', __('translation-manager::messages.extra_removed'));
    }
}
