<?php

class TeamModel {
	
	public static function getTeamByKey($key) {
		return DB::select(
			'SELECT * FROM team WHERE unique_key = ? LIMIT 1',
			array($key)
		);
	}
	
	public static function getTeamById($teamId) {
		// we are not returning key here
		return DB::select(
			'SELECT id, name, games_won, games_lost,
			goals_scored, goals_conceded
			FROM team WHERE id = ? LIMIT 1',
			array($id)
		);
	}
	
	public static function getTeamIfExists($name, $key) {
		return DB::select(
			'SELECT * FROM team WHERE name = ? OR unique_key = ? LIMIT 1',
			array($name, $key)
		);
	}
	
	public static function registerNewTeam($name, $key) {
		return DB::insert(
			'INSERT INTO team(name,unique_key) VALUES (?,?)',
			array($name, $key)
		);
	}
	
	public static function getTeams() {
		return DB::select(
			'SELECT id, name, games_won, games_lost,
			goals_scored, goals_conceded
			FROM team ORDER BY games_won DESC,
			(goals_scored-goals_conceded) DESC'
		);
	}
	
	public static function getLiveMatches() {
		return DB::select(
			'SELECT home.id AS home_id, home.name AS home_name,
				away.id AS away_id, away.name AS away_name,
				m.id, m.home_score, m.away_score,
				m.created_at, to_char(m.updated_at, \'YYYY-MM-DD HH24:MI:SS\') as updated_at
			FROM match m
			LEFT JOIN team home ON home.id = m.home_team_id
			LEFT JOIN team away ON away.id = m.away_team_id
			WHERE (m.finished = FALSE OR updated_at > now() - interval \'1 minute\')
			ORDER BY m.created_at ASC'
		);
	}
	
}