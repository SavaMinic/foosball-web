<?php

Route::get('/', 'HomeController@showHome');

Route::get('/api/standings', 'HomeController@getStandings');