<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
  return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
  //
});

Route::group(['prefix' => 'api'], function () {
  Route::post('authenticate', 'AuthController@authenticate');

  Route::group(['prefix' => 'v1'], function () {
    Route::resource('users', 'UsersController');
    Route::resource('projects', 'ProjectsController');
    Route::put('projects/{id}/members/{userId}', 'ProjectsController@addMember');
    Route::delete('projects/{id}/members/{userId}', 'ProjectsController@removeMember');

    Route::get('projects/{projectId}/categories', 'CategoriesController@index');
    Route::get('categories/{id}', 'CategoriesController@show');
    Route::post('categories', 'CategoriesController@store');
    Route::put('categories/{id}', 'CategoriesController@update');
    Route::resource('projects.entries', 'EntriesController');

    Route::get('projects/{projectId}/entries', 'EntriesController@index');
    Route::get('entries/{id}', 'EntriesController@show');
    Route::post('entries', 'EntriesController@store');
    Route::put('entries/{id}', 'EntriesController@update');
    Route::delete('entries/{id}', 'EntriesController@destroy');
  });
});

