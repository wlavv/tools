<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Closure;
use Auth;

use App\Models\User;


class Authenticate extends Middleware
{

    protected function redirectTo(Request $request): ?string
    {        
        return $request->expectsJson() ? null : route('login');
    }    
    
    public function handle($request, Closure $next, ...$guards){        
        
        if( is_null(auth()->user())) return $this->auth->authenticate();
        
        return $next($request);
    }

}
