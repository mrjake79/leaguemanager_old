<?php
/**
 * Other Sports with Two Scores Class 
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright Copyright 2008
*/
class LeagueManagerTwoScores extends LeagueManager
{

	/**
	 * sports key
	 *
	 * @var string
	 */
	var $key = 'two-scores';


	/**
	 * load specifif settings
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );
		add_filter( 'rank_teams_'.$this->key, array(&$this, 'rankTeams') );
		add_filter( 'team_points2_'.$this->key, array(&$this, 'calculateGoalStatistics') );

		add_action( 'leaguemanager_standings_header_'.$this->key, array(&$this, 'displayStandingsHeader') );
		add_action( 'leaguemanager_standings_columns_'.$this->key, array(&$this, 'displayStandingsColumns'), 10, 2 );

		add_action( 'leaguemanager_get_standings_'.$this->key, array(&$this, 'getStandingsFilter'), 10, 3 );
	}
	function LeagueManagerTwoScores()
	{
		$this->__construct();
	}


	/**
	 * add sports to list
	 *
	 * @param array $sports
	 * @return array
	 */
	function sports( $sports )
	{
		$sports[$this->key] = __( 'Sports with two Scores', 'leaguemanager' );
		return $sports;
	}


	/**
	 * rank Teams
	 *
	 * @param array $teams
	 * @return array of teams
	 */
	function rankTeams( $teams )
	{
		foreach ( $teams AS $key => $row ) {
			$points[$key] = $row->points['plus']+$row->add_points;
			$done[$key] = $row->done_matches;
			$diff[$key] = $row->diff;
		}

		array_multisort( $points, SORT_DESC, $diff, SORT_DESC, $done, SORT_ASC, $teams );
		return $teams;
	}


	/**
	 * get standings table data
	 *
	 * @param object $team
	 * @param array $matches
	 */
	function getStandingsFilter( $team, $matches, $point_rule )
	{
		/*
		 * analogue to team_points2_$sport filter
		 */
		$goals = $this->calculateGoalStatistics( $team->id, $matches );
		$team->points2_plus = $goals['plus'];
		$team->points2_minus = $goals['minus'];
		
		return $team;
	}
	
	
	/**
	 * extend header for Standings Table in Backend
	 *
	 * @param none
	 * @return void
	 */
	function displayStandingsHeader()
	{
		echo '<th class="num">'.__( 'Scores', 'leaguemanager' ).'</th><th>'.__( 'Diff', 'leaguemanager').'</th>';
	}


	/**
	 * extend columns for Standings Table in Backend
	 *
	 * @param object $team
	 * @param string $rule
	 * @return void
	 */
	function displayStandingsColumns( $team, $rule )
	{
		global $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		echo '<td class="num">';

		if ( is_admin() && $rule == 'manual' )
			echo '<input type="text" size="2" name="custom['.$team->id.'][points2][plus]" value="'.$team->points2_plus.'" /> : <input type="text" size="2" name="custom['.$team->id.'][points2][minus]" value="'.$team->points2_minus.'" />';
		else
			printf($league->point_format2, $team->points2_plus, $team->points2_minus);

		echo '</td>';
		echo '<td class="num">'.$team->diff.'</td>';
	}


	/**
	 * calculate goals. Penalty is not counted in statistics
	 *
	 * @param int $team_id
	 * @param string $option
	 * @return int
	 */
	function calculateGoalStatistics( $team_id, $matches = false )
	{
		global $wpdb, $leaguemanager;
		
		$goals = array( 'plus' => 0, 'minus' => 0 );
				
		if ( !$matches ) $matches =  $leaguemanager->getMatches( array("team_id" => $team_id, "limit" => false, "cache" => false) );
		if ( $matches ) {
			foreach ( $matches AS $match ) {
				$custom = maybe_unserialize($match->custom);
				if ( !empty($custom['overtime']['home']) && !empty($custom['overtime']['away']) ) {
					$home_goals = $custom['overtime']['home'];
					$away_goals = $custom['overtime']['away'];
				} else {
					$home_goals = $match->home_points;
					$away_goals = $match->away_points;
				}
				
				if ( $match->home_team == $team_id ) {
					$goals['plus'] += $home_goals;
					$goals['minus'] += $away_goals;
				} else {
					$goals['plus'] += $away_goals;
					$goals['minus'] += $home_goals;
				}
			}
		}
		
		return $goals;
	}
}

$two_point_sports = new LeagueManagerTwoScores();
?>
