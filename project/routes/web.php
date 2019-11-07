<?php

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

Route::get('/', 'WelcomeController@index')->name('welcome');
Route::get('/home', 'HomeController@index')->name('home');


Route::get('createTenant', 'TenantController@store');

Route::get('createCargo', 'Tenants\CargoController@store');
Route::get('showCargo', 'Tenants\CargoController@show');
Route::get('updateCargo', 'Tenants\CargoController@update');
Route::get('deleteCargo', 'Tenants\CargoController@destroy');
Route::get('toListCargos', 'Tenants\CargoController@index');

Route::get('createColaborador', 'Tenants\ColaboradorController@store');
Route::get('showColaborador', 'Tenants\ColaboradorController@show');
Route::get('updateColaborador', 'Tenants\ColaboradorController@update');
Route::get('deleteColaborador', 'Tenants\ColaboradorController@destroy');
Route::get('toListColaboradores', 'Tenants\ColaboradorController@index');


Auth::routes();
