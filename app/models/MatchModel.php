<?php

class MatchModel {
	
	public static function getActiveMatchForTeam($teamId, $checkRecentMatches = false) {
		$condition = 'm.finished = false';
		if ($checkRecentMatches) {
			$condition .= ' OR updated_at > now() - interval \'5 minutes\'';
		}
		return DB::select(
			'SELECT m.id, home.id AS home_id, home.name AS home_name,
				away.id AS away_id, away.name AS away_name,
				m.home_score, m.away_score, m.created_at, m.updated_at
			FROM match m
			INNER JOIN team home ON home.id = m.home_team_id
			INNER JOIN team away ON away.id = m.away_team_id
			WHERE (home.id = ? OR away.id = ?) AND (' . $condition . ')
			ORDER BY m.updated_at DESC
			LIMIT 1 FOR UPDATE',
			array($teamId, $teamId)
		);
	}
	
	public static function finishMatchOfficially($matchId, $homeTeamsWins) {
		$homeScore = $homeTeamsWins ? 10 : 0;
		DB::update(
			'UPDATE match SET home_score = ?, away_score = ?, finished = true, updated_at = now()
			WHERE id = ?', array($homeScore, 10 - $homeScore, $matchId)
		);
	}
	
	public static function concedeGoalOnMatch($matchId, $forHomeTeam) {
		// increase the opponent score
		if ($forHomeTeam) {
			DB::update(
				'UPDATE match SET away_score = away_score + 1,
				finished = (away_score = 9)::bool, updated_at = now()
				WHERE id = ?', array($matchId)
			);
		} else {
			DB::update(
				'UPDATE match SET home_score = home_score + 1,
				finished = (home_score = 9)::bool, updated_at = now()
				WHERE id = ?', array($matchId)
			);
		}
	}
	
	public static function updateTeamGoals($homeId, $awayId, $homeScore, $awayScore, $decreaseGoal = false) {
		$winChange = $decreaseGoal ? -1 : 1;
		$homeTeamWon = $homeScore == 10;
		// update home team
		DB::update(
			'UPDATE team SET games_won = games_won + ?, games_lost = games_lost + ?,
			goals_scored = goals_scored + ?, goals_conceded = goals_conceded + ?
			WHERE id = ?',
			array(
				$homeTeamWon ? $winChange : 0,
				$homeTeamWon ? 0 : $winChange,
				$winChange * $homeScore, $winChange * $awayScore, $homeId
			)
		);
		// update away team
		DB::update(
			'UPDATE team SET games_won = games_won + ?, games_lost = games_lost + ?,
			goals_scored = goals_scored + ?, goals_conceded = goals_conceded + ?
			WHERE id = ?',
			array(
				$homeTeamWon ? 0 : $winChange,
				$homeTeamWon ? $winChange : 0,
				$winChange * $awayScore, $winChange * $homeScore, $awayId
			)
		);
	}

	public static function deleteGoalOnMatch($matchId, $forHomeTeam) {
		// decrease our score
		if ($forHomeTeam) {
			DB::update(
				'UPDATE match SET home_score = home_score - 1, finished = false, updated_at = now()
				WHERE id = ? AND home_score > 0', array($matchId)
			);
		} else {
			DB::update(
				'UPDATE match SET away_score = away_score - 1, finished = false, updated_at = now()
				WHERE id = ? AND away_score > 0', array($matchId)
			);
		}
	}
	
	public static function getTeamMatches($teamId) {
		return DB::select(
			'SELECT home.id AS home_id, home.name AS home_name,
				away.id AS away_id, away.name AS away_name,
				m.id, m.home_score, m.away_score, m.created_at, m.updated_at, m.finished
			FROM match m
			LEFT JOIN team home ON home.id = m.home_team_id
			LEFT JOIN team away ON away.id = m.away_team_id
			WHERE home.id = ? OR away.id = ? ORDER BY m.finished ASC, m.created_at ASC',
			array($teamId, $teamId)
		);
	}
	
	public static function getMatch($matchId) {
		return DB::select(
			'SELECT home.id AS home_id, home.name AS home_name,
				away.id AS away_id, away.name AS away_name,
				m.home_score, m.away_score, m.created_at, m.updated_at, m.finished
			FROM match m
			LEFT JOIN team home ON home.id = m.home_team_id
			LEFT JOIN team away ON away.id = m.away_team_id
			WHERE m.id = ? LIMIT 1', array($matchId)
		);
	}
	
}