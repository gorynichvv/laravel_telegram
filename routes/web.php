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

/* use Telegram\Bot\Keyboard\Keyboard;
use App\TelegramUser;

Route::get('/', function () {
    return view('welcome');
}); */

Route::post('707655937:AAGqZe80AgSiYvyOqiwdEmC9yOXSbh10KVo/webhook', 'TelegramController@webhookHandler');