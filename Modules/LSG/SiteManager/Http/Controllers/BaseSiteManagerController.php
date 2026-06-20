<?php

namespace Modules\LSG\SiteManager\Http\Controllers;

use App\Http\Controllers\Controller;

abstract class BaseSiteManagerController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->setModuleHomeRoute('lsg.site_manager.dashboard');

        $routeName = request()->route()?->getName();
        if ($routeName) {
            $pageTitles = config('site-manager.page_titles', []);
            $this->setPageTitle($pageTitles[$routeName] ?? $this->pageTitle ?: 'Site Manager');
            $this->setBreadcrumbs($this->breadcrumbsFor($routeName));
            $this->setActions([]);

            if ($routeName === 'lsg.site_manager.dashboard') {
                $this->addNewSiteAction();
            }
        }
    }

    protected function prepareSiteManagerPage(string $title, array $extraParents = []): void
    {
        $this->setPageTitle($title);
        $this->setBreadcrumbs(array_merge([
            ['label' => 'LSG', 'url' => route('lsg.index'), 'translate' => false],
            ['label' => 'Site Manager', 'url' => route('lsg.site_manager.dashboard'), 'translate' => false],
        ], $extraParents, [
            ['label' => $title, 'url' => null, 'translate' => false],
        ]));
        $this->setActions([]);
    }

    protected function addNewSiteAction(): void
    {
        $this->addAction([
            'key' => 'new',
            'label' => 'Novo site',
            'name' => 'Novo site',
            'icon' => 'fa-solid fa-plus',
            'route' => 'lsg.site_manager.sites.create',
            'url' => route('lsg.site_manager.sites.create'),
            'type' => 'link',
            'class' => 'lsg-action-btn lsg-action-btn--success',
        ]);
    }

    protected function addBackToSitesAction(): void
    {
        $this->addAction([
            'key' => 'back',
            'label' => 'Sites',
            'name' => 'Sites',
            'icon' => 'fa-solid fa-angle-left',
            'route' => 'lsg.site_manager.sites.index',
            'url' => route('lsg.site_manager.sites.index'),
            'type' => 'link',
            'class' => 'lsg-action-btn lsg-action-btn--back',
        ]);
    }

    protected function addSaveAction(): void
    {
        $this->addAction([
            'key' => 'save',
            'label' => 'Guardar',
            'name' => 'Guardar',
            'icon' => 'fa-solid fa-floppy-disk',
            'type' => 'submit',
            'form' => 'lsg-form',
            'class' => 'lsg-action-btn lsg-action-btn--gold',
        ]);
    }

    protected function addEditSiteAction(object $site): void
    {
        $this->addAction([
            'key' => 'edit',
            'label' => 'Editar',
            'name' => 'Editar',
            'icon' => 'fa-solid fa-pencil',
            'route' => 'lsg.site_manager.sites.edit',
            'url' => route('lsg.site_manager.sites.edit', $site),
            'type' => 'link',
            'class' => 'lsg-action-btn lsg-action-btn--warning',
        ]);
    }

    protected function addPageSpeedAction(object $site): void
    {
        $this->addAction([
            'key' => 'pagespeed',
            'label' => 'Atualizar PageSpeed',
            'name' => 'Atualizar PageSpeed',
            'icon' => 'fa-solid fa-gauge-high',
            'route' => 'lsg.site_manager.sites.pagespeed.run',
            'url' => route('lsg.site_manager.sites.pagespeed.run', $site) . '?force=1',
            'type' => 'form',
            'method' => 'POST',
            'class' => 'lsg-action-btn lsg-action-btn--success',
        ]);
    }

    protected function addOpenSiteAction(object $site): void
    {
        if (empty($site->resolved_url)) {
            return;
        }

        $this->addAction([
            'key' => 'open_site',
            'label' => 'Abrir site',
            'name' => 'Abrir site',
            'icon' => 'fa-solid fa-arrow-up-right-from-square',
            'url' => $site->resolved_url,
            'type' => 'link',
            'class' => 'lsg-action-btn lsg-action-btn--neutral',
        ]);
    }

    private function breadcrumbsFor(string $routeName): array
    {
        if ($routeName === 'lsg.site_manager.dashboard') {
            return [
                ['label' => 'LSG', 'url' => route('lsg.index'), 'translate' => false],
                ['label' => 'Site Manager', 'url' => null, 'translate' => false],
            ];
        }

        return [
            ['label' => 'LSG', 'url' => route('lsg.index'), 'translate' => false],
            ['label' => 'Site Manager', 'url' => route('lsg.site_manager.dashboard'), 'translate' => false],
        ];
    }
}
