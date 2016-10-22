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
Route::post('authenticate', 'AuthController@authenticate');

Route::group(['prefix' => 'v1'], function () {
  Route::resource('users', 'UsersController');
  Route::get('projects/archived', 'ProjectsController@indexArchived');
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
