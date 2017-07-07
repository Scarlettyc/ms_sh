<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('now', function () {
    return date("Y-m-d H:i:s");
});

Route::auth();
Route::controller('access','AccessController');
Route::post('/access', 'AccessController@login');
Route::post('/updateUser','AccessController@update');
Route::get('/test', 'AccessController@test');
Route::post('/upchar', 'TutorialController@createChar');
Route::post('/passtu', 'TutorialController@passTu');
Route::post('/logout', 'AccessController@logout');
Route::post('/workshop', 'WorkshopController@workshop');
Route::controller('luckdraw','LuckdrawController');
Route::post('/freedraw', 'Luck_drawController@draw');
Route::post('/buydraw', 'Luck_drawController@buydraw');
// Route::group(['middleware' => 'auth', 'namespace' => 'Admin', 'prefix' => 'admin'], function() {
  
// });
// Route::auth();post

Route::get('/', 'HomeController@index');
