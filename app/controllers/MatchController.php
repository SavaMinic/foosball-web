<?php

class MatchController extends BaseController {

	public function startMatch() {
		$team = Session::get('team');
		if (empty($team)) {
			return array('error' => 'You are not logged in!');
		}
		$tableKey = Input::get('table');
		if (empty($tableKey)) {
			return array('error' => 'Table parameter is missing!');
		}
		// check if there is already a match on this table
		$match = DB::select(
			'SELECT id, home_team_id, away_team_id FROM match
			WHERE table_key = ? AND finished = false LIMIT 1',
			array($tableKey)
		);
		if (!empty($match)) {
			$match = $match[0];
			// check if our team is in this match
			if ($team->id != $match->home_team_id && $team->id != $match->away_team_id) {
				return array('error' => 'Someone else is already playing on this table!');
			}
			return array('match' => $match->id);
		}
		// check if someone waits on this table
		$wait = DB::select(
			'SELECT team_id FROM teams_waiting WHERE table_key = ?',
			array($tableKey)
		);
		if (empty($wait)) {
			// no-one is waiting, so insert this team on waiting
			DB::insert(
				'INSERT INTO teams_waiting(team_id, table_key) VALUES(?,?)',
				array($team->id, $tableKey)
			);
			return array('status' => 'wait');
		} else if ($wait[0]->team_id == $team->id) {
			// user is already waiting for opponent
			return array('status' => 'wait');
		} else {
			return DB::transaction(function() use ($team, $tableKey, $wait) {
				// opponent is waiting for user, so delete this 'wait' row
				DB::delete('DELETE FROM teams_waiting WHERE table_key = ?', array($tableKey));
				// insert the match
				$matchId = DB::table('match')->insertGetId(array(
					'home_team_id' => $wait[0]->team_id,
					'away_team_id' => $team->id,
					'table_key' => $tableKey,
					'created_at' => 'now()',
					'updated_at' => 'now()',
				));
				return array('match' => $matchId);
			});
		}
	}
	
	public function endMatch() {
		// this will end the current match
		// and the opponent will win with official result 10:0
	}
	
}