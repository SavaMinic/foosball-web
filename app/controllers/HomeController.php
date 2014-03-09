<?php

class HomeController extends BaseController {
	
	public function showMain() {
		return View::make('main');
	}

	public function showHome() {
		$teams = DB::select('SELECT * FROM team');
		return View::make('home', array(
			'teams' => $teams,
		));
	}
	
	public function showTeam($id) {
		$team = TeamModel::getTeamById($id);
		if (empty($team)) {
			return View::make('error', array(
				'error' => 'Team not found!'
			));
		}
		$teamMatches = MatchModel::getTeamMatches($id);
		return View::make('team', array(
			'team' => $team,
			'matches' => $teamMatches,
		));
	}

}