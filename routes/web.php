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

Route::get('/', function () {
    return view('welcome');
});

Route::any('/test/pay','TestController@alipay');


Route::get('/test/alipay/return','Alipay\PayController@aliReturn');
Route::post('/test/alipay/notify','Alipay\PayController@notify');


//jiekou
Route::get('/api/test','Api\TestController@test');
Route::post('/api/user/reg','Api\TestController@reg');          //用户注册
Route::post('/api/user/login','Api\TestController@login');      //用户登录
Route::get('/api/user/list','Api\TestController@userList');      //用户列表
Route::get('/api/user/showData','Api\TestController@showData');
Route::get('/test/postman','Api\TestController@postman');
Route::get('/test/postman1','Api\TestController@postman1')->middleware('filter','check.token');        //接口防刷

Route::get('/test/md5','Api\TestController@md5test');
Route::get('/test/sign2','Api\TestController@sign2');
Route::get('/test/sign3','Api\TestController@sign3');
Route::get('/test/aes','Api\TestController@aes');//对称加密

//凯撒加密
Route::get('/api/accii','Api\TestController@accii');

//Route::get('/api/decrypt','Api\TestController@decrypt');

Route::get('api/encrypt','TestController@encrypt');
Route::get('api/decrypt','TestController@decrypt');
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
        