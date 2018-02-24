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
Route::controller('tutorial','TutorialController');
Route::controller('baggage','BaggageController');
Route::controller('workshop','WorkshopController');
Route::controller('shop','ShopController');
Route::controller('match','MatchController');
Route::controller('load','LoadBattleController');
Route::controller('battle','BattleController');
Route::controller('friend','FriendController');
Route::controller('loginreward','LoginRewardController');
Route::controller('updateinfo','UpdateController');
Route::controller('event','EventController');

Route::post('/quicklogin', 'AccessController@quickLogin');
Route::post('/access', 'AccessController@login');

Route::group(['middleware'=>'checktoken'],function(){
Route::post('/updateUser','AccessController@update');
Route::get('/test', 'AccessController@test');

Route::post('/upchar', 'TutorialController@createChar');
Route::post('/passtu', 'TutorialController@passTu');
Route::post('/logout', 'AccessController@logout');
Route::post('/uservalue','AccessController@showStatus');


Route::post('/baggage','BaggageController@baggage');
Route::post('/getItemInfo','BaggageController@getItemInfo');
Route::post('/sellItem','BaggageController@sellItem');
Route::post('/scrollMerage','BaggageController@scrollMerage');
Route::post('/equipmentUpgrade','BaggageController@equipmentUpgrade');

Route::post('/workshop','WorkshopController@workshop');
Route::post('/showEquipmentInfo','WorkshopController@showEquipmentInfo');
Route::post('/showSkillInfo','WorkshopController@showSkillInfo');
Route::post('/compareEquipment','WorkshopController@compareEquipment');
Route::post('/equipEquipment','WorkshopController@equipEquipment');
Route::post('/shoplist','ShopController@shopCoin');
Route::post('/shop','ShopController@shop');
Route::post('/buyResource','ShopController@buyResouceBYCoin');
Route::post('/rareResourceList','ShopController@rareResourceList');
Route::post('/refresh','ShopController@refreshResource');
Route::post('/buyRareResouce','ShopController@buyFromRefreshList');
Route::post('/buyCoin','ShopController@buyCoin');
Route::post('/coinList','ShopController@getCoinList');
Route::post('/gemList','ShopController@getGemList');

Route::controller('luckdraw','LuckdrawController');
Route::post('/showluck', 'LuckdrawController@showLuck');
Route::post('/onedraw', 'LuckdrawController@oneDraw');
Route::post('/multidraw', 'LuckdrawController@multiDraw');

Route::post('/battlematch', 'MatchController@testWebsocket');


Route::post('/load', 'LoadBattleController@loadingGame');
Route::post('/loadmap', 'LoadBattleController@loadMap');
Route::post('/testbattle', 'BattleController@testBattle');
Route::post('/battleresult', 'BattleController@battleResult');

Route::post('/addfriend', 'FriendController@addFriend');
Route::post('/removefriend', 'FriendController@removeFriend');
Route::post('/friendlist', 'FriendController@friend_list');
Route::post('/getFriendRequest', 'FriendController@get_friend_request');
Route::post('/delFriendRequest', 'FriendController@reject_request');
Route::post('/sendRequest', 'FriendController@send_friendrequest');
Route::post('/friendSendCoin', 'FriendController@sendCoin');
Route::post('/friendRecevieCoin', 'FriendController@recieveCoin');
Route::post('/coinlist', 'FriendController@recieveCoinList');
Route::post('/suggest_friend', 'FriendController@suggest_friend');
Route::post('/search_friend', 'FriendController@searchFriend');
Route::post('/like_friend','FriendController@like_friend');
Route::post('/friendDetails','FriendController@friend_details');
Route::post('/sendMessage','FriendController@sendMessage');
Route::post('/receiveMessage','FriendController@receiveMessage');

Route::post('/loginrewardslist', 'LoginRewardController@getLoginReward');
Route::post('/gettoday', 'LoginRewardController@getToday');

Route::controller('mission','MissionController');
Route::post('/daily_mission', 'MissionController@missionList');
Route::post('/collect_mission', 'MissionController@collectMissionReward');
Route::post('/getMissionDetails', 'MissionController@getMissionDetails');
Route::post('/getLevelMission', 'MissionController@getLevelMission');


Route::post('/udpate_email', 'UpdateController@updateEmail');
Route::post('/udpate_pw', 'UpdateController@updatePassword');
Route::post('/refresh_setting', 'UpdateController@refreshSetting');


Route::post('/get_event', 'EventController@getEventList');
});


// Route::group(['middleware' => 'auth', 'namespace' => 'Admin', 'prefix' => 'admin'], function() {
  
// });
// Route::auth();post

Route::get('/', 'HomeController@index');
