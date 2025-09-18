<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\URL;

class customToolsController extends Controller
{
    protected $viewData = array();

    public function __construct(){ 
        $this->middleware('auth');
    }

    public function setViewData($options, $variableName = null)
    {
        $this->middleware('auth');

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
            
            if(!is_numeric($component)){
                $currentPath .= '/' . $component;
                $breadcrumbs[] = [
                    'name' => str_replace(['-', '_'], ' ', $component),
                    'url' => $currentPath
                ];
            }
        }

        $lastSegment = request()->segment(count(request()->segments()));
    
        if (ctype_digit($lastSegment)) {
            
            array_pop($components);
            
            array_pop($breadcrumbs);
    
            $breadcrumbs[] = [
                'name' => str_replace(['-', '_'], ' ', end($components)),
                'url' => url()->current()
            ];
        }
        
        return array_slice($breadcrumbs, 0, 4);
    }

}
