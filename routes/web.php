<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| O sistema agora usa Filament v3 no painel /admin.
| Todas as rotas antigas foram removidas e redirecionam para o Filament.
|
*/

// Redireciona a raiz para o painel Filament
Route::get('/', fn () => redirect('/admin'));

// Redireciona rotas antigas para o painel Filament
Route::get('/login', fn () => redirect('/admin/login'));
Route::get('/dashboard', fn () => redirect('/admin'));
Route::get('/inbox', fn () => redirect('/admin'));
Route::get('/inbox/{any}', fn () => redirect('/admin'))->where('any', '.*');
Route::get('/leads', fn () => redirect('/admin'));
Route::get('/leads/{any}', fn () => redirect('/admin'))->where('any', '.*');
Route::get('/tickets', fn () => redirect('/admin'));
Route::get('/tickets/{any}', fn () => redirect('/admin'))->where('any', '.*');
Route::get('/campaigns', fn () => redirect('/admin'));
Route::get('/campaigns/{any}', fn () => redirect('/admin'))->where('any', '.*');
Route::get('/vehicles', fn () => redirect('/admin'));
Route::get('/vehicles/{any}', fn () => redirect('/admin'))->where('any', '.*');
Route::get('/contacts', fn () => redirect('/admin'));
Route::get('/contacts/{any}', fn () => redirect('/admin'))->where('any', '.*');
Route::get('/reports', fn () => redirect('/admin'));
Route::get('/knowledge', fn () => redirect('/admin'));
Route::get('/knowledge/{any}', fn () => redirect('/admin'))->where('any', '.*');
Route::get('/settings', fn () => redirect('/admin'));
Route::get('/settings/{any}', fn () => redirect('/admin'))->where('any', '.*');
