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
Route::post('/getEquipmentInfo', 'WorkshopController@getEquipmentInfo');
Route::post('/getSkillInfo', 'WorkshopController@getSkillInfo');
Route::controller('luckdraw','LuckdrawController');
Route::post('/freedraw', 'LuckdrawController@draw');
Route::post('/onedraw', 'LuckdrawController@oneDraw');
Route::post('/multidraw', 'LuckdrawController@multiDraw');

Route::controller('match','MatchController');
Route::post('/battlematch', 'MatchController@match');
Route::post('/addfriend', 'FriendController@addFriend');
Route::post('/removeriend', 'FriendController@removeFriend');
Route::post('/friendlist', 'FriendController@friend_list');
Route::post('/get_friend_request', 'FriendController@friend_list');
Route::post('/del_friend_request', 'FriendController@reject_request');
// Route::group(['middleware' => 'auth', 'namespace' => 'Admin', 'prefix' => 'admin'], function() {
  
// });
// Route::auth();post

Route::get('/', 'HomeController@index');
