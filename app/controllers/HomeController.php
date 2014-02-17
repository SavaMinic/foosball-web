<?php

class HomeController extends BaseController {

	public function showHome() {
		$teams = DB::select('select * from team');
		return View::make('home', array(
			'teams' => $teams,
		));
	}
	
	public function getStandings() {
		$teams = DB::select(
			'select * from team order by games_won desc,
			(goals_scored-goals_conceded) desc'
		);
		return array(
			'teams' => $teams,
		);
	}

}