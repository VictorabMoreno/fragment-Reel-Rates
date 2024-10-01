<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return 'O app está funcionando';
});

Route::get('/example', [ExampleController::class, 'getJson']);


Route::middleware('token.auth')->group(function () {
   ///Rotas não disponíveis nessa versão de visualização
});
