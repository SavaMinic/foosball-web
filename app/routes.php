<?php

Route::get('/', 'HomeController@showHome');

Route::get('/api/standings', 'HomeController@getStandings');
Route::get('/api/live', 'HomeController@getLiveMatches');
Route::get('/api/team/{id}', 'HomeController@getTeam');

Route::post('/api/login', 'HomeController@login');
Route::post('/api/logout', 'HomeController@logout');