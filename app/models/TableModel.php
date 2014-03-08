<?php

class TableModel {

	public static function getMatchOnTable($tableKey) {
		return DB::select(
			'SELECT id, home_team_id, away_team_id FROM match
			WHERE table_key = ? AND finished = false LIMIT 1',
			array($tableKey)
		);
	}

	public static function getTeamWaitingOnTable($tableKey) {
		return DB::select(
			'SELECT team_id FROM teams_waiting WHERE table_key = ?',
			array($tableKey)
		);
	}
	
	public static function insertTeamWaiting($teamId, $tableKey) {
		DB::insert(
			'INSERT INTO teams_waiting(team_id, table_key) VALUES(?,?)',
			array($teamId, $tableKey)
		);
	}
	
	public static function startMatchOnTable($homeTeamId, $awayTeamId, $tableKey) {
		return DB::transaction(function() use ($homeTeamId, $awayTeamId, $tableKey) {
			// opponent is waiting for user, so delete this 'wait' row
			DB::delete('DELETE FROM teams_waiting WHERE table_key = ?', array($tableKey));
			// insert the match
			$matchId = DB::table('match')->insertGetId(array(
				'home_team_id' => $homeTeamId,
				'away_team_id' => $awayTeamId,
				'table_key' => $tableKey,
				'created_at' => 'now()',
				'updated_at' => 'now()',
			));
			return array('match' => $matchId);
		});
	}
	
	public static function removeTeamFromWaiting($teamId) {
		DB::delete('DELETE FROM teams_waiting WHERE team_id = ?', array($teamId));
	}
}