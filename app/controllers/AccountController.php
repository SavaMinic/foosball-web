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
		if (!empty(Session::get('team'))) {
			return array('status' => 'ok');
		}
		$name = Input::get('name');
		$key = Input::get('key');
		if (empty($name) || strlen($name) > 100) {
			return array('error' => 'Invalid name!');
		}
		if (empty($key)) {
			return array('error' => 'Invalid key!');
		}
		// check if already exists
		$team = DB::select(
			'SELECT * FROM team WHERE name = ? OR unique_key = ? LIMIT 1',
			array($name, $key)
		);
		if (!empty($team)) {
			if ($team[0]->unique_key == $key) {
				// this user is already registered, so do nothing.
				// call to /register will be made 
				// only if the call to /login failed,
				// so this should not happen in normal case
				return array('status' => 'ok');
			}
			return array('error' => 'This team name is taken!');
		}
		// everything is fine, insert user
		if (!DB::insert(
			'INSERT INTO team(name,unique_key) VALUES (?,?)',
			array($name, $key)
		)) {
			return array('error' => 'Error while registering team!');
		}
		return array('status' => 'ok');
	}
	
}