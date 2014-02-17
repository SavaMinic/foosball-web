<?php

Route::get('/', 'HomeController@showHome');

Route::get('/api/standings', 'ApiController@getStandings');
Route::get('/api/live', 'ApiController@getLiveMatches');
Route::get('/api/team/{id}', 'ApiController@getTeam');
Route::get('/api/match/{id}', 'ApiController@getMatch');

Route::post('/api/match', 'MatchController@startMatch');
Route::delete('/api/match', 'MatchController@endMatch');

Route::post('/api/login', 'AccountController@login');
Route::post('/api/logout', 'AccountController@logout');
Route::post('/api/register', 'AccountController@register');