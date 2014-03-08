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
		$match = TableModel::getMatchOnTable($tableKey);
		if (!empty($match)) {
			$match = $match[0];
			// check if our team is in this match
			if ($team->id != $match->home_team_id && $team->id != $match->away_team_id) {
				return array('error' => 'Someone else is already playing on this table!');
			}
			return array('match' => $match->id);
		}
		// check if someone waits on this table
		$wait = TableModel::getTeamWaitingOnTable($tableKey);
		if (empty($wait)) {
			// no-one is waiting, so insert this team on waiting
			TableModel::insertTeamWaiting($team->id, $tableKey);
			return array('status' => 'wait');
		} else if ($wait[0]->team_id == $team->id) {
			// this user is already waiting for opponent
			return array('status' => 'wait');
		} else {
			// another user is already waiting, so we can start match
			return TableModel::startMatchOnTable(
				$wait[0]->team_id, // waiting team becomes home team
				$team->id, // our team becomes away team
				$tableKey
			);
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
		$match = MatchModel::getActiveMatchForTeam($team->id);
		if (!empty($match)) {
			$match = $match[0];
			// run this as transaction
			DB::transaction(function() use ($match, $team) {
				// finish this match as 10:0 (or 0:10)
				MatchModel::finishMatchOfficially($match->id, $team->id != $match->home_id);
				// update the teams goals counters, so our team concedes 10 goals
				MatchModel::updateTeamGoals(
					$match->home_id,
					$match->away_id,
					($team->id == $match->away_id) ? 10 : 0, // if we are away, then home scored 10
					($team->id == $match->home_id) ? 10 : 0 // if we are home, then away scored 10
				);
			});
		} else {
			// user is not in match, delete him from the waiting
			TableModel::removeTeamFromWaiting($team->id);
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
		$match = MatchModel::getActiveMatchForTeam($team->id);
		if (empty($match)) {
			return array('error' => 'You are not in match!');
		}
		$match = $match[0];
		// run this as transaction
		DB::transaction(function() use ($match, $team) {
			// increase the opponent score
			$forHomeTeam = $match->home_id == $team->id;
			MatchModel::concedeGoalOnMatch($match->id, $forHomeTeam);
			if ($forHomeTeam) $match->away_score++; else $match->home_score++;
			
			// if the game is finished now, update the goal counters
			if ($match->home_score == 10 || $match->away_score == 10) {
				MatchModel::updateTeamGoals(
					$match->home_id, $match->away_id,
					$match->home_score, $match->away_score
				);
			}
		});
		return array('status' => 'ok');
	}
	
	/**
	 * Called to delete our last scored goal
	 * (mostly for goals with middle row players)
	 */
	public function deleteGoal() {
		$team = Session::get('team');
		if (empty($team)) {
			return array('error' => 'You are not logged in!');
		}
		// check if user is in some match, or his match finished in last 5 minutes
		$match = MatchModel::getActiveMatchForTeam($team->id, true);
		if (empty($match)) {
			return array('error' => 'You are not in match!');
		}
		$match = $match[0];
		// run this as transaction
		return DB::transaction(function() use ($match, $team) {
			$forHomeTeam = $match->home_id == $team->id;
			// decrease our score
			MatchModel::deleteGoalOnMatch($match->id, $forHomeTeam);
			// update the teams goals counters, if the game was reopened
			if ($match->home_score == 10 || $match->away_score == 10) {
				$homeTeamWon = $match->home_score == 10;
				// if we try to delete goal if we didn't win
				if ($forHomeTeam != $homeTeamWon ) {
					DB::rollback();
					return array('error' => 'You can\'t delete goal in lost match!');
				}
				MatchModel::updateTeamGoals(
					$match->home_id, $match->away_id,
					$match->home_score, $match->away_score,
					true // decrease the wins/lost
				);
			}
			return array('status' => 'ok');
		});
	}
	
}