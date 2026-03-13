<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

Route::get('/', function () { return view('auth.login'); });

Auth::routes();

 
Route::get('/language/{locale}', function (string $locale) {

    app()->setLocale($locale);
    session()->put('locale', $locale);
    return redirect()->back();

});

Route::get('/artisan/{cmd}', function ($cmd) {
    Artisan::call($cmd);
    return Artisan::output();
});