<?php

namespace Modules\LSG\SiteManager\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\LSG\SiteManager\Http\Requests\StoreSiteRequest;
use Modules\LSG\SiteManager\Models\Site;

class SiteController extends BaseSiteManagerController
{
    public function index()
    {
        $this->prepareSiteManagerPage('Sites LSG');
        $this->addNewSiteAction();

        $items = Schema::hasTable('lsg_sites')
            ? Site::query()->latest()->paginate(25)
            : collect();

        return $this->view('site-manager::sites.index', compact('items'));
    }

    public function create()
    {
        $this->prepareSiteManagerPage('Novo site');
        $this->addBackToSitesAction();
        $this->addSaveAction();

        return $this->view('site-manager::sites.form', ['item' => new Site()]);
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['logo']);

        $site = Site::create($data);
        $this->storeLogoIfUploaded($request, $site);

        return redirect()->route('lsg.site_manager.sites.index')->with('success', 'Site criado.');
    }

    public function show(Site $site)
    {
        $this->prepareSiteManagerPage($site->name, [
            ['label' => 'Sites LSG', 'url' => route('lsg.site_manager.sites.index'), 'translate' => false],
        ]);
        $this->disableDefaultAction('back');
        $this->disableDefaultAction('delete');
        $this->addPageSpeedAction($site);
        $this->addEditSiteAction($site);

        $site->load(['integrations', 'pageSpeedRuns' => fn ($query) => $query->latest()->limit(30)]);

        return $this->view('site-manager::sites.show', compact('site'));
    }

    public function edit(Site $site)
    {
        $this->prepareSiteManagerPage('Editar site', [
            ['label' => 'Sites LSG', 'url' => route('lsg.site_manager.sites.index'), 'translate' => false],
            ['label' => $site->name, 'url' => route('lsg.site_manager.sites.show', $site), 'translate' => false],
        ]);
        $this->addBackToSitesAction();
        $this->addSaveAction();

        return $this->view('site-manager::sites.form', ['item' => $site]);
    }

    public function update(StoreSiteRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        unset($data['logo']);

        $site->update($data);
        $this->storeLogoIfUploaded($request, $site);

        return redirect()->route('lsg.site_manager.sites.index')->with('success', 'Site atualizado.');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $site->update(['status' => 'archived']);
        $site->delete();

        return back()->with('success', 'Site arquivado.');
    }

    private function storeLogoIfUploaded(StoreSiteRequest $request, Site $site): void
    {
        if (!$request->hasFile('logo')) {
            return;
        }

        $file = $request->file('logo');
        if (!$file || !$file->isValid()) {
            return;
        }

        $directory = public_path('uploads/lsg-sites/logos');
        File::ensureDirectoryExists($directory);

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'png');
        $filename = Str::slug($site->slug ?: $site->name) . '-' . $site->id . '-' . time() . '.' . $extension;
        $file->move($directory, $filename);

        $url = '/uploads/lsg-sites/logos/' . $filename;
        $settings = is_array($site->settings) ? $site->settings : [];
        $settings['logo_url'] = $url;
        $settings['logo'] = $url;
        $settings['image'] = $url;

        $site->forceFill(['settings' => $settings])->save();
    }

}
