<?php
/**
 * Basketball Class
 *
 * @author 	Kolja Schleich, LaMonte Forthun
 * @package	LeagueManager
 * @copyright Copyright 2008
*/
class LeagueManagerBasketball extends LeagueManager
{
	/**
	 * sports keys
	 *
	 * @var array
	 */
	var $key = 'basketball';

	/**
	 * load specific settings
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );
		add_filter( 'rank_teams_'.$this->key, array(&$this, 'rankTeams') );
		add_filter( 'team_points2_'.$this->key, array(&$this, 'calculateBasketStatistics') );

		add_filter( 'leaguemanager_export_matches_header_'.$this->key, array(&$this, 'exportMatchesHeader') );
		add_filter( 'leaguemanager_export_matches_data_'.$this->key, array(&$this, 'exportMatchesData'), 10, 2 );
		add_filter( 'leaguemanager_import_matches_'.$this->key, array(&$this, 'importMatches'), 10, 3 );

		add_action( 'matchtable_header_'.$this->key, array(&$this, 'displayMatchesHeader'), 10, 0 );
		add_action( 'matchtable_columns_'.$this->key, array(&$this, 'displayMatchesColumns') );
		add_action( 'leaguemanager_standings_header_'.$this->key, array(&$this, 'displayStandingsHeader') );
		add_action( 'leaguemanager_standings_columns_'.$this->key, array(&$this, 'displayStandingsColumns'), 10, 2 );
		
		add_action( 'leaguemanager_update_results_'.$this->key, array(&$this, 'updateResults') );
		add_action( 'leaguemanager_get_standings_'.$this->key, array(&$this, 'getStandingsFilter'), 10, 3 );
	}
	function LeagueManagerBasketball()
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
		$sports[$this->key] = __( 'Basketball', 'leaguemanager' );

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
			$GA[$key] = round((($row->points2_plus) > 0 ? (($row->points2_minus) > 0 ? ((($row->points2_plus)/($row->points2_minus))*1000) : 1000) : 0),0);
            $WinPerc[$key] =  round((($row->won_matches) > 0 ? (($row->done_matches) > 0 ? ((($row->won_matches)/($row->done_matches))*100) : 100) : 0));
		}
		array_multisort( $points, SORT_DESC, SORT_NUMERIC, $WinPerc, SORT_DESC, SORT_NUMERIC, $GA, SORT_DESC, SORT_NUMERIC, $teams );
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
		$baskets = $this->calculateBasketStatistics( $team->id, $matches );
		$team->points2_plus = $baskets['plus'];
		$team->points2_minus = $baskets['minus'];
		
		return $team;
	}
	
	
	/**
	 * calculate baskets. Penalty is not counted in statistics
	 *
	 * @param int $team_id
	 * @param string $option
	 * @return int
	 */
	function calculateBasketStatistics( $team_id, $matches = false )
	{
		global $wpdb, $leaguemanager;

		$goals = array( 'plus' => 0, 'minus' => 0 );

		$team_id = intval($team_id);
		if ( !$matches ) $matches =  $leaguemanager->getMatches( array("team_id" => $team_id, "limit" => false, "cache" => false) );
		if ( $matches ) {
			foreach ( $matches AS $match ) {
				$custom = maybe_unserialize($match->custom);
				$home_goals = $match->home_points;
				$away_goals = $match->away_points;
					
				// home match
				if ( $match->home_team == $team_id ) {
					$goals['plus'] += $home_goals;
					$goals['minus'] += $away_goals;
				}
				
				// away match
				if ( $match->away_team == $team_id ) {
					$goals['plus'] += $away_goals;
					$goals['minus'] += $home_goals;
				}
			}
		}

		return $goals;
	}


	/**
	 * extend header for Standings Table in Backend
	 *
	 * @param none
	 * @return void
	 */
	function displayStandingsHeader()
	{
		echo '<th class="num">'.__( 'Baskets', 'leaguemanager' ).'</th><th class="num">'.__( 'Diff', 'leaguemanager').'</th>';
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
	 * display Table Header for Match Administration
	 *
	 * @param none
	 * @return void
	 */
	function displayMatchesHeader()
	{
		echo '<th style="text-align: center;">'.__( 'Quarters', 'leaguemanager' ).'</th><th style="text-align: center;">'.__( 'Overtime', 'leaguemanager' ).'</th>';
	}


	/**
	 * display Table columns for Match Administration
	 *
	 * @param object $match
	 * @return void
	 */
	function displayMatchesColumns( $match )
	{
		echo '<td style="text-align: center;">';
		for ( $i = 1; $i <= 4; $i++ )
			if(isset($match)) {
				$match_id = ( isset($match->id) ? $match->id : '');
				echo '<input class="points" type="text" size="2" style="text-align: center;" id="quarters_plus_'.$i.'_'.$match_id.'" name="custom['.$match_id.'][quarters]['.$i.'][plus]" value="'. (isset($match->quarters[$i]['plus']) ? $match->quarters[$i]['plus'] : "") .'" /> : <input class="points" type="text" size="2" style="text-align: center;" id="quarters_minus_'.$i.'_'.$match_id.'" name="custom['.$match_id.'][quarters]['.$i.'][minus]" value="'. (isset($match->quarters[$i]['minus']) ? $match->quarters[$i]['minus'] : "") .'" /><br />';
			} else {
				echo '<input class="points" type="text" size="2" style="text-align: center;" id="" name="" value="" /> : <input class="points" type="text" size="2" style="text-align: center;" id="" name="" value="" /><br />';
			}
		echo '</td>';

		if(isset($match)) {
			$match_id = ( isset($match->id) ? $match->id : '');
			echo '<td style="text-align: center;"><input class="points" type="text" size="2" style="text-align: center;" id="overtime_home_'.$match_id.'" name="custom['.$match_id.'][overtime][home]" value="'. (isset($match->overtime['home']) ? $match->overtime['home'] : "") .'" /> : <input class="points" type="text" size="2" style="text-align: center;" id="overtime_away_'.$match_id.'" name="custom['.$match_id.'][overtime][away]" value="'. (isset($match->overtime['away']) ? $match->overtime['away'] : "") .'" /></td>';
		} else {
			echo '<td style="text-align: center;"><input class="points" type="text" size="2" style="text-align: center;" id="" name="" value="" /> : <input class="points" type="text" size="2" style="text-align: center;" id="" name="" value="" /></td>';
		}
	}

	/**
	 * export matches header
	 *
	 * @param string $content
	 * @return the content
	 */
	function exportMatchesHeader( $content )
	{
		$content .= "\t".utf8_decode(__( 'Quarters', 'leaguemanager' ))."\t\t\t\t".utf8_decode(__('Overtime', 'leaguemanager'));
		return $content;
	}


	/**
	 * export matches data
	 *
	 * @param string $content
	 * @param object $match
	 * @return the content
	 */
	function exportMatchesData( $content, $match )
	{
		if ( isset($match->quarters) ) {
			for ( $i = 1; $i <= 4; $i++ )
				$content .= "\t".sprintf("%d:%d", $match->quarters[$i]['plus'], $match->quarters[$i]['minus']);
		} else {
			$content .= "\t\t\t\t";
		}

		if ( isset($match->overtime) )
			$content .= "\t".sprintf("%d:%d", $match->overtime['home'], $match->overtime['away']);
		else
			$content .= "\t";

		return $content;
	}


	/**
	 * import matches
	 *
	 * @param array $custom
	 * @param array $line elements start at index 8
	 * @param int $match_id
	 * @return array
	 */
	function importMatches( $custom, $line, $match_id )
	{
		$match_id = intval($match_id);
		$quarters = ( isset($line[9]) && isset($line[10]) && isset($line[11]) && isset($line[12]) ) ? array( explode(":", $line[9]), explode(":", $line[10]), explode(":", $line[11]), explode(":", $line[12]) ) : array( array("", ""), array("", ""), array("", ""), array("", ""));
		$overtime = isset($line[13]) ? explode(":", $line[13]) : array("", "");

		foreach ( $quarters AS $i => $quarter ) {
			$x = $i+1;
			$custom[$match_id]['quarters'][$x]['plus'] = $quarter[0];
			$custom[$match_id]['quarters'][$x]['minus'] = $quarter[1];
		}

		$custom[$match_id]['overtime'] = array( 'home' => $overtime[0], 'away' => $overtime[1] );

		return $custom;
	}
	
	
	/**
	 * update match results and automatically calculate match score
	 *
	 * @param int $match_id
	 * @return none
	 */
	function updateResults( $match_id )
	{
		global $wpdb, $leaguemanager;
		
		$match = $leaguemanager->getMatch( $match_id, false );
		
		if ( $match->home_points == "" && $match->away_points == "" ) {
			$score = array( 'home' => "", 'guest' => "" );
			foreach ( $match->quarters AS $quarter ) {
				if ($quarter['plus'] != '' && $quarter['minus'] != '') {
					$score['home'] += intval($quarter['plus']);
					$score['guest'] += intval($quarter['minus']);
				}
			}
			if ($match->overtime['home'] != '' && $match->overtime['away'] != '') {
				$score['home'] += intval($match->overtime['home']);
				$score['guest'] += intval($match->overtime['away']);			
			}
			
			$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->leaguemanager_matches} SET `home_points` = '%s', `away_points` = '%s' WHERE `id` = '%d'", $score['home'], $score['guest'], $match_id) );
		}
	}
}

$basketball = new LeagueManagerBasketball();
?>