<?php

class MatchController extends BaseController {

	/**
	 * Called when user wants to start match.
	 * If noone is waiting on this table, puts this user on waiting for opponent
	 * If someone is already waiting, create a new match
	 * @return status => waiting | match => integer
	 */
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
	
	/**
	 * If user is just waiting, delete the wait
	 * If user is in match already, this will end the current match,
	 * and the opponent will win with official result 10:0
	 * @return multitype:string
	 */
	public function endMatch() {
		$team = Session::get('team');
		if (empty($team)) {
			return array('error' => 'You are not logged in!');
		}
		// check if user is in some match
		$match = DB::select(
			'SELECT id, home_team_id, home_score, away_score FROM match
			WHERE finished = false AND (home_team_id = ? OR away_team_id = ?)
			LIMIT 1 FOR UPDATE',
			array($team->id, $team->id)
		);
		if (!empty($match)) {
			$match = $match[0];
			$homeScore = ($team->id == $match->home_team_id) ? 0 : 10;
			// finish this match
			DB::update(
				'UPDATE match SET home_score = ?, away_score = ?, finished = true, updated_at = now()
				WHERE id = ?', array($homeScore, 10 - $homeScore, $match->id)
			);
		} else {
			// user is not in match, delete him from the waiting
			DB::delete('DELETE FROM teams_waiting WHERE team_id = ?', array($team->id));
		}
		return array('status' => 'ok');
	}
	
	/**
	 * Increase the score for the opponent
	 * (as only our device know when the ball has passed the mark)
	 */
	public function concedeGoal() {
		$team = Session::get('team');
		if (empty($team)) {
			return array('error' => 'You are not logged in!');
		}
		// check if user is in some match
		$match = DB::select(
			'SELECT id, home_team_id FROM match
			WHERE finished = false AND (home_team_id = ? OR away_team_id = ?)
			LIMIT 1 FOR UPDATE',
			array($team->id, $team->id)
		);
		if (empty($match)) {
			return array('error' => 'You are not in match!');
		}
		$match = $match[0];
		// increase the opponent score
		if ($match->home_team_id == $team->id) {
			DB::update(
				'UPDATE match SET away_score = away_score + 1, finished = (away_score = 9)::bool
				WHERE id = ?', array($match->id)
			);
		} else {
			DB::update(
				'UPDATE match SET home_score = home_score + 1, finished = (home_score = 9)::bool
				WHERE id = ?', array($match->id)
			);
		}
		return array('status' => 'ok');
	}
	
}