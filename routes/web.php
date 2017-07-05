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

Route::get('/', 'ProductsController@index')->name('home');

Route::get('/register','RegistrationController@create');
Route::post('/register','RegistrationController@store');

Route::get('/login','SessionsController@create');
Route::post('/login', 'SessionsController@store');
Route::get('/logout','SessionsController@destroy');

Route::group([
  'prefix' => 'shop',
  'as'     => 'shop.', ], function ()
{
  Route::get('/','CartController@show')->name('index');

  Route::get('{id}/add','CartController@add')->name('add');
  Route::get('destroy','CartController@destroy')->name('destroy');
  Route::get('checkout','CartController@getCheckout')->name('checkout');

  Route::get('orders', 'CartController@myOrders')->name('orders');

  Route::post('pay','CartController@pay')->name('pay');
  Route::post('callback', 'CartController@callback')->name('callback');
});
