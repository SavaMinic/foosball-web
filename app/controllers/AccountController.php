<?php

class AccountController  extends BaseController {
	
	public function login() {
		$team = Session::get('team');
		if (empty($team)) {
			$team = TeamModel::getTeamByKey( Input::get('key') );
			if (empty($team)) {
				return array('error' => 'No team registered with this key!');
			}
			$team = $team[0];
			// put data into session
			Session::put('team', $team);
		}
		// check if there is match in progress
		$activeMatch = MatchModel::getActiveMatchForTeam($team->id);
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
		// user is alredy logged in (so no need for registering)
		if (!empty(Session::get('team'))) {
			return array('status' => 'ok');
		}
		// Get and check parameters
		$name = Input::get('name');
		$key = Input::get('key');
		if (empty($name) || strlen($name) > 100) {
			return array('error' => 'Invalid name!');
		}
		if (empty($key)) {
			return array('error' => 'Invalid key!');
		}
		// check if team already exists
		$team = TeamModel::getTeamIfExists($name, $key);
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
		if (!TeamModel::registerNewTeam($name, $key)) {
			return array('error' => 'Error while registering team!');
		}
		return array('status' => 'ok');
	}
	
}