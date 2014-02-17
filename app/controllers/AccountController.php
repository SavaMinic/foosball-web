<?php

class AccountController  extends BaseController {
	
	public function login() {
		$key = Input::get('key');
		$team = DB::select(
			'SELECT * FROM team WHERE unique_key = ? LIMIT 1',
			array($key)
		);
		if (empty($team)) {
			return array('error' => 'No team registered with this key!');
		}
		$team = $team[0];
		// check if there is match in progress
		$activeMatch = DB::select(
			'SELECT home.id AS home_id, home.name AS home_name,
				away.id AS away_id, away.name AS away_name,
				m.home_score, m.away_score, m.created_at, m.updated_at
			FROM match m
			LEFT JOIN team home ON home.id = m.home_team_id
			LEFT JOIN team away ON away.id = m.away_team_id
			WHERE m.finished = false AND (home.id = ? OR away.id = ?) LIMIT 1',
			array($team->id, $team->id)
		);
		// put data into session
		Session::put('team', $team);
		return array(
				'team' => $team,
				'match' => $activeMatch,
		);
	}
	
	public function logout() {
		Session::flush();
		return array('status' => 'ok');
	}
	
	public function register() {
	
	}
	
}