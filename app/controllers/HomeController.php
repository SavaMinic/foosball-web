<?php

class HomeController extends BaseController {

	public function showHome() {
		$teams = DB::select('SELECT * FROM team');
		return View::make('home', array(
			'teams' => $teams,
		));
	}

}