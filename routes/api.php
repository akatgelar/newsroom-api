<?php

use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLevelController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\PodcastController;
use App\Http\Controllers\KoranController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['middleware' => 'auth:api'], function () {

    Route::get('user', 'UserController@getAll');
    Route::get('user/{id}', 'UserController@getById');
    Route::post('user', 'UserController@insert');
    Route::put('user/{id}', 'UserController@update');
    Route::delete('user/{id}', 'UserController@delete');

    Route::get('user_level', 'UserLevelController@getAll');
    Route::get('user_level/{id}', 'UserLevelController@getById');
    Route::post('user_level', 'UserLevelController@insert');
    Route::put('user_level/{id}', 'UserLevelController@update');
    Route::delete('user_level/{id}', 'UserLevelController@delete');

    Route::post('upload', 'UploadController@upload');
    Route::get('profile', 'UserController@profile');

    Route::get('slider', 'SliderController@getAll');
    Route::get('slider/{id}', 'SliderController@getById');
    Route::get('slider_category', 'SliderController@getCategory');
    Route::post('slider', 'SliderController@insert');
    Route::put('slider/{id}', 'SliderController@update');
    Route::delete('slider/{id}', 'SliderController@delete');

    Route::get('artikel', 'ArtikelController@getAll');
    Route::get('artikel/{id}', 'ArtikelController@getById');
    Route::get('artikel_category', 'ArtikelController@getCategory');
    Route::post('artikel', 'ArtikelController@insert');
    Route::put('artikel/{id}', 'ArtikelController@update');
    Route::delete('artikel/{id}', 'ArtikelController@delete');

    Route::get('video', 'VideoController@getAll');
    Route::get('video/{id}', 'VideoController@getById');
    Route::get('video_category', 'VideoController@getCategory');
    Route::post('video', 'VideoController@insert');
    Route::put('video/{id}', 'VideoController@update');
    Route::delete('video/{id}', 'VideoController@delete');

    Route::get('podcast', 'PodcastController@getAll');
    Route::get('podcast/{id}', 'PodcastController@getById');
    Route::get('podcast_category', 'PodcastController@getCategory');
    Route::post('podcast', 'PodcastController@insert');
    Route::put('podcast/{id}', 'PodcastController@update');
    Route::delete('podcast/{id}', 'PodcastController@delete');

    Route::get('koran', 'KoranController@getAll');
    Route::get('koran/{id}', 'KoranController@getById');
    Route::get('koran_category', 'KoranController@getCategory');
    Route::post('koran', 'KoranController@insert');
    Route::put('koran/{id}', 'KoranController@update');
    Route::delete('koran/{id}', 'KoranController@delete');

});

Route::post('register', 'Auth\RegisterController@register');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout');

Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:cache');
    return 'DONE'; //Return anything
});

// Route::get('register/activation/{token}', 'Auth\RegisterController@activationEmail');
// Route::post('forgot_password', 'Auth\RegisterController@forgotPassword');
