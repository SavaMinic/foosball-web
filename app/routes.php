<?php

// web front page
Route::get('/', 'HomeController@showHome');

// Calls for front end to show data
// Standings with all teams
Route::get('/api/standings', 'ApiController@getStandings');
// All live matches in the system
Route::get('/api/live', 'ApiController@getLiveMatches');
// Team information (statistics and matches list)
Route::get('/api/team/{id}', 'ApiController@getTeam');
// Match information
Route::get('/api/match/{id}', 'ApiController@getMatch');

// starts the match (or puts the player into waiting state)
Route::post('/api/match', 'MatchController@startMatch');
// manually ends the match (or removes the player from waiting state)
Route::delete('/api/match', 'MatchController@endMatch');
// concede the goal (our device is checking only our goal sensors, so we have to sygnal it)
Route::post('/api/goal', 'MatchController@concedeGoal');
// undo our last scored goal in the current match, or the last played match in last 5 minutes
Route::delete('/api/goal', 'MatchController@deleteGoal');

// Basic functionality
Route::post('/api/login', 'AccountController@login');
Route::post('/api/logout', 'AccountController@logout');
Route::post('/api/register', 'AccountController@register');