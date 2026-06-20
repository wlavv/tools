<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Documentation\ModuleDocumentationService;
use RuntimeException;

class InfrastructureDocumentationController extends Controller
{
    public function index(ModuleDocumentationService $documentation)
    {
        $documents = $documentation->all();
        $firstDocument = $documents->first();
        $selected = $firstDocument ? $documentation->find($firstDocument['slug']) : null;

        $this->preparePageMeta($selected['title'] ?? 'Documentacao Tecnica');

        return $this->view('admin.infrastructure.documentation.index', [
            'documents' => $documents->groupBy('group'),
            'selectedDocument' => $selected,
        ]);
    }

    public function show(string $slug, ModuleDocumentationService $documentation)
    {
        try {
            $document = $documentation->find($slug);
        } catch (RuntimeException) {
            abort(404);
        }

        $this->preparePageMeta($document['title'], $document);

        return $this->view('admin.infrastructure.documentation.index', [
            'documents' => $documentation->all()->groupBy('group'),
            'selectedDocument' => $document,
        ]);
    }

    private function preparePageMeta(string $title, ?array $document = null): void
    {
        $this->setPageTitle($title);
        $this->setBreadcrumbs([
            [
                'label' => 'Dashboard',
                'url' => route('dashboard.index'),
                'translate' => false,
            ],
            [
                'label' => 'LSG',
                'url' => route('lsg.index'),
                'translate' => false,
            ],
            [
                'label' => 'Infraestrutura',
                'url' => route('lsg.infrastructure'),
                'translate' => false,
            ],
            [
                'label' => 'Documentacao',
                'url' => $document ? route('admin.infrastructure.documentation.index') : null,
                'translate' => false,
            ],
        ]);

        if ($document) {
            $this->addBreadcrumb($document['title'], null, [], false);
        }

        $this->setActions([]);
        $this->disableDefaultAction('new');
        $this->disableDefaultAction('back');
        $this->disableDefaultAction('edit');
        $this->disableDefaultAction('delete');
        $this->disableDefaultAction('show');
        $this->disableDefaultAction('save');
    }
}
