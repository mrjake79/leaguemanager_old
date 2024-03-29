<?php
/**
 * Pool Class
 *
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright Copyright 2008
*/
class LeagueManagerPool extends LeagueManager
{

	/**
	 * sports key
	 *
	 * @var string
	 */
	var $key = 'pool';


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

		add_filter( 'leaguemanager_export_matches_header_'.$this->key, array(&$this, 'exportMatchesHeader') );
		add_filter( 'leaguemanager_export_matches_data_'.$this->key, array(&$this, 'exportMatchesData'), 10, 2 );
		add_filter( 'leaguemanager_import_matches_'.$this->key, array(&$this, 'importMatches'), 10, 3 );
		add_filter( 'leaguemanager_export_teams_header_'.$this->key, array(&$this, 'exportTeamsHeader') );
		add_filter( 'leaguemanager_export_teams_data_'.$this->key, array(&$this, 'exportTeamsData'), 10, 2 );
		add_filter( 'leaguemanager_import_teams_'.$this->key, array(&$this, 'importTeams'), 10, 2 );

		add_action( 'matchtable_header_'.$this->key, array(&$this, 'displayMatchesHeader'), 10, 0);
		add_action( 'matchtable_columns_'.$this->key, array(&$this, 'displayMatchesColumns') );
		add_action( 'leaguemanager_standings_header_'.$this->key, array(&$this, 'displayStandingsHeader') );
		add_action( 'leaguemanager_standings_columns_'.$this->key, array(&$this, 'displayStandingsColumns'), 10, 2 );
		add_action( 'team_edit_form_'.$this->key, array(&$this, 'editTeam') );

		add_action( 'leaguemanager_save_standings_'.$this->key, array(&$this, 'saveStandings') );
		add_action( 'leaguemanager_get_standings_'.$this->key, array(&$this, 'getStandingsFilter'), 10, 3 );
	}
	function LeagueManagerSoccer()
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
		$sports[$this->key] = __( 'Pool Billiard', 'leaguemanager' );
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
			$diff[$key] = $row->forScore - $row->againstScore;
			$won[$key] = $row->won_matches;
			$draw[$key] = $row->draw_matches;
			$done[$key] = $row->done_matches;
		}

		array_multisort( $points, SORT_DESC, $diff, SORT_DESC, $won, SORT_DESC, $draw, SORT_DESC, $done, SORT_ASC, $teams );
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
		 * analogue to leaguemanager_save_standings_$sport filter
		 */
		$team->forScore = $this->getPoolScore($team->id, 'for', $matches);
		$team->againstScore = $this->getPoolScore($team->id, 'against', $matches);
		
		return $team;
	}
	
	
	/**
	 * save custom standings
	 *
	 * @param int $team_id
	 * @return void
	 */
	function saveStandings( $team_id )
	{
		global $wpdb;

		$team = $wpdb->get_results( $wpdb->prepare("SELECT `custom` FROM {$wpdb->leaguemanager_teams} WHERE `id` = '%d'", $team_id) );
		$team = $team[0];
		$custom = maybe_unserialize($team->custom);

		$custom['forScore'] = $this->getPoolScore($team_id, 'for');
		$custom['againstScore'] = $this->getPoolScore($team_id, 'against');

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `custom` = '%s' WHERE `id` = '%d'", maybe_serialize($custom), $team_id ) );
	}


	/**
	 * get score for left balls
	 *
	 * @param int $team_id
	 * @return array number of runs for and against as assoziative array
	 */
	function getPoolScore( $team_id, $index, $matches = false )
	{
		global $leaguemanager;

		$score = array( 'for' => 0, 'against' => 0 );
		if ( !$matches ) $matches = $leaguemanager->getMatches( array("team_id" => $team_id, "limit" => false, "cache" => false) );
		foreach ( $home AS $match ) {
			if ( $team_id == $match->home_team ) {
				$score['for'] += $match->forScore;
				$score['against'] += $match->againstScore;
			} else {
				$score['for'] += $match->againstScore;
				$score['against'] += $match->forScore;
			}
		}

		return $score[$index];
	}


	/**
	 * extend header for Standings Table
	 *
	 * @param none
	 * @return void
	 */
	function displayStandingsHeader()
	{
		echo '<th class="num">'.__( 'For', 'leaguemanager' ).'</th><th>'.__( 'Against', 'leaguemanager' ).'</th>';
	}


	/**
	 * extend columns for Standings Table
	 *
	 * @param object $team
	 * @param string $rule
	 * @return void
	 */
	function displayStandingsColumns( $team, $rule )
	{
		if (!isset($team->forScore)) $team->forScore = '';
		if (!isset($team->againstScore)) $team->againstScore = '';
		
		if ( is_admin() && $rule == 'manual' )
			echo '<td><input type="text" size="2" name="custom['.$team->id.'][forScore]" value="'.$team->forScore.'" /></td><td><input type="text" size="2" name="custom['.$team->id.'][againstScore]" value="'.$team->againstScore.'" /></td>';
		else
			echo '<td class="num">'.$team->forScore.'</td><td class="num">'.$team->againstScore.'</td>';
	}


	/**
	 * display hidden fields in team form
	 *
	 * @param object $team
	 * @return void
	 */
	function editTeam( $team )
	{
		if ( !isset($team->forScore) )
			$team->forScore = '';
		if ( !isset($team->againstScore) )
			$team->againstScore = '';
		
		echo '<input type="hidden" name="custom[forScore]" value="'.$team->forScore.'" /><input type="hidden" name="custom[againstScore]" value="'.$team->againstScore.'" />';
	}


	/**
	 * display Table Header for Match Administration
	 *
	 * @param none
	 * @return void
	 */
	function displayMatchesHeader()
	{
		echo '<th>'.__( 'For', 'leaguemanager' ).'</th><th>'.__( 'Against', 'leaguemanager' ).'</th>';
	}


	/**
	 * display Table columns for Match Administration
	 *
	 * @param object $match
	 * @return void
	 */
	function displayMatchesColumns( $match )
	{
		if (!isset($match->forScore)) $match->forScore = '';
		if (!isset($match->againstScore)) $match->againstScore = '';
		
		echo '<td><input class="points" type="text" size="2" id="forscore_'.$match->id.'" name="custom['.$match->id.'][forScore]" value="'.$match->forScore.'" /></td><td><input class="points" type="text" size="2" id="againstscore_'.$match->id.'" name="custom['.$match->id.'][againstScore]" value="'.$match->againstScore.'" /></td>';
	}


	/**
	 * export matches header
	 *
	 * @param string $content
	 * @return the content
	 */
	function exportMatchesHeader( $content )
	{
		$content .= "\t".utf8_decode(__( 'For', 'leaguemanager' ))."\t".utf8_decode(__('Against', 'leaguemanager'));
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
		if ( isset($match->forScore) )
			$content .= "\t".$match->forScore."\t".$match->againstScore;
		else
			$content .= "\t\t";

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
		
		$custom[$match_id]['forScore'] = isset($line[9]) ? $line[9] : '';
		$custom[$match_id]['againstScore'] = isset($line[10]) ? $line[10] : '';
		return $custom;
	}


	/**
	 * export teams header
	 *
	 * @param string $content
	 * @return the content
	 */
	function exportTeamsHeader( $content )
	{
		$content .= "\t".utf8_decode(__( 'For', 'leaguemanager' ))."\t".utf8_decode(__('Against', 'leaguemanager'));
		return $content;
	}


	/**
	 * export teams data
	 *
	 * @param string $content
	 * @param object $team
	 * @return the content
	 */
	function exportTeamsData( $content, $team )
	{
		if ( isset($team->forScore) )
			$content .= "\t".$team->forScore."\t".$team->againstScore;
		else
			$content .= "\t\t";

		return $content;
	}


	/**
	 * import teams
	 *
	 * @param array $custom
	 * @param array $line elements start at index 8
	 * @return array
	 */
	function importTeams( $custom, $line )
	{
		$custom['forScore'] = isset($line[16]) ? $line[16] : '';
		$custom['againstScore'] = isset($line[17]) ? $line[17] : '';
		return $custom;
	}
}

$pool = new LeagueManagerPool();
?>