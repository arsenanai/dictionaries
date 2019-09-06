<?php

use Illuminate\Http\Request;

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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::namespace('Api')->group(function () {
    
});*/
Route::group(['middleware' => ['json.response']], function () {

    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });

    // public routes
    Route::post('/login', 'Api\AuthController@login')->name('login.api');
    //Route::post('/register', 'Api\AuthController@register')->name('register.api');
    Route::get('/codes/export', 'Api\TRUController@excel');

    // private routes
    Route::namespace('Api')->middleware('auth:api')->group(function () {
        Route::get('/users', 'UsersController@index');
        Route::get('/users/{user}', 'UsersController@show');
        Route::post('/users', 'UsersController@store');
        Route::put('/users/{user}', 'UsersController@update');
        Route::delete('/users/{user}', 'UsersController@destroy');

        Route::get('/groups', 'TRUController@indexGroup');
        Route::get('/groups/show/{group}', 'TRUController@showGroup');
        Route::post('/groups', 'TRUController@createGroup');
        Route::put('/groups/{group}', 'TRUController@updateGroup');
        Route::delete('/groups/{group}', 'TRUController@destroyGroup');
        Route::get('/groups/by-name', 'TRUController@searchGroupsByName');

        Route::get('/subgroups', 'TRUController@indexSubGroup');
        //Route::get('/subgroups/by-group/{group}', 'TRUController@indexSubGroupByGroup');
        Route::get('/subgroups/show/{subgroup}', 'TRUController@showSubGroup');
        Route::post('/subgroups', 'TRUController@createSubGroup');
        Route::put('/subgroups/{subgroup}', 'TRUController@updateSubGroup');
        Route::delete('/subgroups/{subgroup}', 'TRUController@destroySubGroup');
        Route::get('/subgroups/by-name', 'TRUController@searchSubgroupsByName');
        Route::post('/subgroups/migrate', 'TRUController@migrateSubgroups');

        Route::get('/codes', 'TRUController@indexCode');
        Route::get('/codes/show/{code}', 'TRUController@showCode');
        Route::post('/codes', 'TRUController@createCode');
        Route::put('/codes/{code}', 'TRUController@updateCode');
        Route::delete('/codes/{code}', 'TRUController@destroyCode');
        Route::get('/codes/by-name', 'TRUController@searchCodes');
        Route::post('/codes/migrate', 'TRUController@migrateCodes');

        Route::get('/logout', 'AuthController@logout')->name('logout');
    });
});
