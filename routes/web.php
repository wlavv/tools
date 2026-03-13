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

use Illuminate\Support\Facades\Artisan;

Route::get('/tmp-run-migrations', function () {
    Artisan::call('queue:table');
    $out1 = Artisan::output();

    Artisan::call('queue:failed-table');
    $out2 = Artisan::output();

    Artisan::call('migrate', ['--force' => true]);
    $out3 = Artisan::output();

    return nl2br($out1 . "\n" . $out2 . "\n" . $out3);
});