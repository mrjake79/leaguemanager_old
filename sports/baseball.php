<?php
/**
 * Baseball Class 
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright Copyright 2008
*/
class LeagueManagerBaseball extends LeagueManager
{

	/**
	 * sports key
	 *
	 * @var string
	 */
	var $key = 'baseball';


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

	}
	function LeagueManagerBaseball()
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
		$sports[$this->key] = __( 'Baseball', 'leaguemanager' );
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
		$won = $lost = array();
		foreach ( $teams AS $key => $row ) {
			$won[$key] = $row->won_matches;
			$lost[$key] = $row->lost_matches;
		}

		array_multisort( $won, SORT_DESC, $lost, SORT_ASC, $teams );
		return $teams;
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

		$team_id = intval($team_id);
		$team = $wpdb->get_results( $wpdb->prepare("SELECT `custom` FROM {$wpdb->leaguemanager_teams} WHERE `id` = '%d'", $team_id) );
		$team = $team[0];
		$custom = maybe_unserialize($team->custom);

		$custom['runs'] = $this->getRuns($team_id);
		$custom['gb'] = $this->getGamesBehind($team_id);
		$custom['shutouts'] = $this->getShutouts($team_id);

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `custom` = '%s' WHERE `id` = '%d'", maybe_serialize($custom), $team_id ) );
	}


	/**
	 * get number of runs for team
	 *
	 * @param int $team_id
	 * @return array number of runs for and against as associative array
	 */
	function getRuns( $team_id )
	{
		global $leaguemanager;
		
		$runs = array( 'for' => 0, 'against' => 0 );

		$home = $leaguemanager->getMatches( array("home_team" => $team_id, "limit" => false) );
		foreach ( $home AS $match ) {
			$runs['for'] += $match->runs['for'];
			$runs['against'] += $match->runs['against'];
		}

		$away = $leaguemanager->getMatches( array("away_team" => $team_id, "limit" => false) );
		foreach ( $away AS $match ) {
			$runs['for'] += $match->runs['against'];
			$runs['against'] += $match->runs['for'];
		}
		
		return $runs;
	}


	/**
	 * get number of games behind
	 *
	 * @param int $team_id
	 * @return int games behind
	 */
	function getGamesBehind( $team_id )
	{
		global $wpdb, $leaguemanager;

		$team = $leaguemanager->getTeam($team_id);
		if ( $team->rank == 1 ) {
			return 0;
		} else {
			$first = $wpdb->get_results( $wpdb->prepare("SELECT `rank`, `won_matches`, `lost_matches` FROM {$wpdb->leaguemanager_teams} WHERE `rank` = 1 AND `league_id` = '%d' AND `season` = '%s'", $team->league_id, $team->season) );
			$gb = ( $first[0]->won_matches - $team->won_matches + $team->lost_matches - $first[0]->lost_matches ) / 2;
			return round($gb, 3);
		}
	}


	/**
	 * get number of shutouts
	 *
	 * @param int $team_id
	 * @return int numbe rof shutouts
	 */
	function getShutouts( $team_id )
	{
		global $leaguemanager;
		
		$shutouts = 0;

		$home = $leaguemanager->getMatches( array("home_team" => $team_id) );
		foreach ( $home AS $match ) {
			$shutouts += $match->shutouts['home'];
		}

		$away = $leaguemanager->getMatches( array("away_team" => $team_id) );
		foreach ( $away AS $match ) {
			$shutouts += $match->shutouts['away'];
		}
		
		return $shutouts;
	}
	

	/**
	 * extend header for Standings Table in Backend
	 *
	 * @param none
	 * @return void
	 */
	function displayStandingsHeader()
	{
		echo '<th class="num">'.__( 'RF', 'leaguemanager' ).'</th><th>'.__( 'RA', 'leaguemanager' ).'</th><th>'.__('PCT', 'leaguemanager' ).'</th><th>'.__( 'GB', 'leaguemanager' ).'</th><th>'.__( 'SO', 'leaguemanager' ).'</th>';
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
		$win_percent = ( $team->done_matches > 0 ) ? round($team->won_matches/$team->done_matches, 3) : 0;
		if (!isset($team->runs)) $team->runs = array('for' => '', 'against' => '');
		if (!isset($team->gb)) $team->gb = '';
		if (!isset($team->shutouts)) $team->shutouts = '';
		
		if ( is_admin() && $rule == 'manual' )
			echo '<td><input type="text" size="2" name="custom['.$team->id.'][runs][for]" value="'.$team->runs['for'].'" /></td><td><input type="text" size="2" name="custom['.$team->id.'][runs][against]" value="'.$team->runs['against'].'" /></td><td>'.$win_percent.'</td><td><input type="text" size="2" name="custom['.$team->id.'][gb]" value="'.$team->gb.'" /></td><td><input type="text" size="2" name="custom['.$team->id.'][shutouts]" value="'.$team->shutouts.'" /></td>';
		else
			echo '<td class="num">'.$team->runs['for'].'</td><td class="num">'.$team->runs['against'].'</td><td class="num">'.$win_percent.'</td><td class="num">'.$team->gb.'</td><td class="num">'.$team->shutouts.'</td>';
	}


	/**
	 * display hidden fields in team edit form
	 *
	 * @param object $team
	 * @return void
	 */
	function editTeam( $team )
	{
		if ( !isset($team->runs) ) $team->runs = array( 'for' => '', 'against' => '' );
		if ( !isset($team->gb) ) $team->gb = '';
		if ( !isset($team->shutouts) ) $team->shutouts = '';
		
		echo '<input type="hidden" name="custom[runs][for]" value="'.$team->runs['for'].'" /><input type="hidden"  name="custom[runs][against]" value="'.$team->runs['against'].'" /><input type="hidden" name="custom[gb]" value="'.$team->gb.'" /><input type="hidden" name="custom[shutouts]" value="'.$team->shutouts.'" />';
	}


	/**
	 * display Table Header for Match Administration
	 *
	 * @param none
	 * @return void
	 */
	function displayMatchesHeader()
	{
		echo '<th>'.__( 'RF', 'leaguemanager' ).'</th><th>'.__( 'RA', 'leaguemanager' ).'</th><th>'.__( 'SO', 'leaguemanager' ).'</th>';
	}


	/**
	 * display Table columns for Match Administration
	 *
	 * @param object $match
	 * @return void
	 */
	function displayMatchesColumns( $match )
	{
		if (!isset($match->runs)) $match->runs = array('for' => '', 'against' => '');
		if (!isset($match->shutouts)) $match->shutouts = array('home' => '', 'away' => '');
		
		echo '<td><input class="points" type="text" size="2" id="runs_for_'.$match->id.'" name="custom['.$match->id.'][runs][for]" value="'.$match->runs['for'].'" /></td><td><input clas="points" type="text" size="2" id="runs_against_'.$match->id.'" name="custom['.$match->id.'][runs][against]" value="'.$match->runs['against'].'" /></td>';
		echo '<td><input class="points" type="text" size="2" id="shutouts_home_'.$match->id.'" name="custom['.$match->id.'][shutouts][home]" value="'.$match->shutouts['home'].'" /> : <input class="points" type="text" size="2" id="shutouts_away_'.$match->id.'" name="custom['.$match->id.'][shutouts][away]" value="'.$match->shutouts['away'].'" /></td>';
	}


	/**
	 * export matches header
	 *
	 * @param string $content
	 * @return the content
	 */
	function exportMatchesHeader( $content )
	{
		$content .= "\t".utf8_decode(__( 'RF', 'leaguemanager' ))."\t".utf8_decode(__('RA', 'leaguemanager'))."\t".utf8_decode(__('SO', 'leaguemanager'));
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
		if ( isset($match->runs) )
			$content .= "\t".$match->runs['for']."\t".$match->runs['against']."\t".sprintf("%d:%d", $match->shutouts['home'], $match->shutouts['away']);
		else
			$content .= "\t\t\t";

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
		$shutouts = isset($line[11]) ? explode(":", $line[11]) : array('','');

		$custom[$match_id]['runs'] = ( isset($line[9]) && isset($line[10]) ) ? array( 'for' => $line[9], 'against' => $line[10] ) : array('for' => '', 'against' => '');
		$custom[$match_id]['shutouts'] = array( 'home' => $shutouts[0], 'away' => $shutouts[1] );

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
		$content .= "\t".utf8_decode(__( 'RF', 'leaguemanager' ))."\t".utf8_decode(__('RA', 'leaguemanager'))."\t".utf8_decode(__('PCT'))."\t".utf8_decode(__( 'GB', 'leaguemanager'))."\t".utf8_decode(__('SO', 'leaguemanager'));
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
		if ( isset($team->runs) )
			$content .= "\t".$team->runs['for']."\t".$team->runs['against']."\t".($team->won_matches/$team->done_matches)."\t".$team->gb."\t".$team->shutouts;
		else
			$content .= "\t\t\t\t\t";

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
		$custom['runs'] = array( 'for' => $line[16], 'against' => $line[17] );
		$custom['gb'] = $line[18];
		$custom['shutouts'] = $line[19];

		return $custom;
	}
}

$baseball = new LeagueManagerBaseball();
?>