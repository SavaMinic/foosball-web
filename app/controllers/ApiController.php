<?php

class ApiController extends BaseController {
	
	public function getStandings() {
		return array(
			'teams' => TeamModel::getTeams(),
		);
	}
	
	public function getLiveMatches() {
		return array(
			'matches' => TeamModel::getLiveMatches(),
		);
	}
	
	public function getTeam($id) {
		$team = TeamModel::getTeamById($id);
		if (empty($team)) {
			return array('error' => 'Team not found!');
		}
		$teamMatches = MatchModel::getTeamMatches($id);
		return array(
			'team' => $team[0],
			'matches' => $teamMatches,
		);
	}
	
	public function getMatch($id) {
		$match = MatchModel::getMatch($id);
		if (empty($match)) {
			return array('error' => 'Match not found!');
		}
		return array(
			'match' => $match[0],
		);
	}
}