<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

class Controller extends BaseController{

    use AuthorizesRequests, ValidatesRequests;

    protected $defaultLang;

    protected array $breadcrumbs = [];
    protected array $actions = [];
    protected ?string $pageTitle = null;
    protected array $accessList = [];

    public function __construct(){

        $this->middleware('auth');

        $this->defaultLang = 1;
        Config::set('defaultLang', $this->defaultLang);
    }

    protected function setPageTitle(?string $title): void{
        $this->pageTitle = $title;
    }

    protected function setBreadcrumbs(array $items = []): void{
        $this->breadcrumbs = $items;
    }

    protected function addBreadcrumb(string $label, ?string $url = null, array $params = [], bool $translate = true): void{
        $this->breadcrumbs[] = [
            'label' => $label,
            'url' => $url,
            'params' => $params,
            'translate' => $translate,
        ];
    }

    protected function setActions(?array $actions = null): void{
        $this->actions = $actions ?? [];
    }

    protected function setAccessList(?array $accessList = null): void{
        $this->accessList = $accessList;
    }

    protected function addAccess( string $url, string $name, ?string $icon = null, ?string $image = null ): void {
        $this->accessList[] = [
            'url'   => $url,
            'name'  => $name,
            'icon'  => $icon,
            'image' => $image,
        ];
    }

    protected function resetAccessList(): void{
        $this->accessList = [];
    }

    protected function setIndexPage(string $sectionKey, string $routeName): void{
        $this->setPageTitle($sectionKey);

        $this->setBreadcrumbs([
            [
                'label' => $sectionKey,
                'url' => route($routeName),
                'translate' => true,
            ]
        ]);
    }

    protected function view(string $view, array $data = []){
        return \View::make($view)->with(array_merge([
            'pageTitle'   => $this->pageTitle,
            'breadcrumbs' => $this->breadcrumbs,
            'actions'     => $this->actions,
            'accessList'  => $this->accessList,
        ], $data));
    }
}