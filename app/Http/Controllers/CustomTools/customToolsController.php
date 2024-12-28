<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;

class customToolsController extends Controller
{
    protected $viewData = array();

    public function __construct(){ 
        $this->middleware('auth');
    }

    public function setViewData($options, $variableName = null)
    {
        
        
        if (is_array($options)) {
            
            foreach ($options as $key => $value) $this->viewData[$key] = $value;
            
        } elseif ($variableName !== null) {

            $this->viewData[$variableName] = $options;

        } else {

            throw new InvalidArgumentException('A variable name must be provided for single values.');

        }

        $this->viewData['breadcrumbs'] = $this->generateBreadcrumbs();
    }

    public function getViewData()
    {
        return $this->viewData;
    }

    function generateBreadcrumbs(string $url = null): array{

        if ($url === null) {
            $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';

        $components = array_filter(explode('/', trim($path, '/')));

        $breadcrumbs = [];
        $currentPath = '';

        foreach ($components as $component) {
            $currentPath .= '/' . $component;
            $breadcrumbs[] = [
                'name' => str_replace(['-', '_'], ' ', $component),
                'url' => $currentPath
            ];
        }

        return array_slice($breadcrumbs, 0, 3);
    }

}
