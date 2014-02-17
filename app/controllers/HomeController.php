<?php

class HomeController extends BaseController {

	public function showHome() {
		$teams = DB::select('SELECT * FROM team');
		return View::make('home', array(
			'teams' => $teams,
		));
	}
	
	public function getStandings() {
		$teams = DB::select(
			'SELECT * FROM team ORDER BY games_won DESC,
			(goals_scored-goals_conceded) DESC'
		);
		return array(
			'teams' => $teams,
		);
	}
	
	public function getLiveMatches() {
		$matches = DB::select(
			'SELECT home.id AS home_id, home.name AS home_name,
				away.id AS away_id, away.name AS away_name,
				m.id, m.home_score, m.away_score, m.created_at, m.updated_at
			FROM match m
			LEFT JOIN team home ON home.id = m.home_team_id
			LEFT JOIN team away ON away.id = m.away_team_id
			WHERE m.finished = FALSE ORDER BY m.created_at ASC'
		);
		return array(
			'matches' => $matches,
		);
	}
	
	public function getTeam($id) {
		$team = DB::select('SELECT * FROM team WHERE id = ?', array($id));
		if (!$team) {
			return array('error' => 'Team not found!');
		}
		$teamMatches = DB::select(
			'SELECT home.id AS home_id, home.name AS home_name,
				away.id AS away_id, away.name AS away_name,
				m.id, m.home_score, m.away_score, m.created_at, m.updated_at, m.finished
			FROM match m
			LEFT JOIN team home ON home.id = m.home_team_id
			LEFT JOIN team away ON away.id = m.away_team_id
			WHERE home.id = ? OR away.id = ? ORDER BY m.finished ASC, m.created_at ASC',
			array($id, $id)
		);
		return array(
			'team' => $team,
			'matches' => $teamMatches,
		);
	}
	
	public function getMatch($id) {
		$match = DB::select(
			'SELECT home.id AS home_id, home.name AS home_name,
				away.id AS away_id, away.name AS away_name,
				m.home_score, m.away_score, m.created_at, m.updated_at, m.finished
			FROM match m
			LEFT JOIN team home ON home.id = m.home_team_id
			LEFT JOIN team away ON away.id = m.away_team_id
			WHERE m.id = ?', array($id)
		);
		if (!$match) {
			return array('error' => 'Match not found!');
		}
		return array(
			'match' => $match,
		);
	}

}