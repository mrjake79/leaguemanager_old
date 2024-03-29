<?php
/**
 * Rugby Class 
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright Copyright 2008
*/
class LeagueManagerRugby extends LeagueManager
{

	/**
	 * sports key
	 *
	 * @var string
	 */
	var $key = 'rugby';


	/**
	 * load specifif settings
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );
		add_filter( 'team_points_'.$this->key, array(&$this, 'calculatePoints'), 10, 3 );
		add_filter( 'rank_teams_'.$this->key, array(&$this, 'rankTeams') );

		add_filter( 'leaguemanager_point_rules_list', array(&$this, 'getPointRuleList') );
		add_filter( 'leaguemanager_point_rules',  array(&$this, 'getPointRules') );

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

		add_action( 'leaguemanager_update_results_'.$this->key, array(&$this, 'updateResults') );
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
		$sports[$this->key] = __( 'Rugby', 'leaguemanager' );
		return $sports;
	}


	/**
	 * get Point Rule list
	 *
	 * @param array $rules
	 * @return array
	 */
	function getPointRuleList( $rules )
	{
		$rules[$this->key] = __('Rugby', 'leaguemanager');

		return $rules;
	}


	/**
	 * get Point rules
	 *
	 * @param array $rules
	 * @return array
	 */
	function getPointRules( $rules )
	{
		$rules[$this->key] = array( 'forwin' => 4, 'fordraw' => 2, 'forloss' => 0 );

		return $rules;
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
			$diff[$key] = $row->diff;
		}

		array_multisort( $points, SORT_DESC, $diff, SORT_DESC, $teams );
		return $teams;
	}


	/**
	 * re-calculate points
	 *
	 * @param array $points
	 * @param int $team_id
	 * @param array $rule
	 * @return array with modified points
	 */
	function calculatePoints( $points, $team_id, $rule )
	{
		global $leaguemanager;
		extract($rule);

		$matches = $leaguemanager->getMatches( array("team_id" => $team_id, "limit" => false, "cache" => false) );
		foreach ( $matches AS $match ) {
			$index = ( $match->home_team == $team_id ) ? 'home' : 'away';

			if ( $match->tries[$index] > 4 )
				$points['plus'] += 1;
			
			// Current Team Lost Match with less than 6 points
			if ( $match->loser_id == $team_id && abs($match->home_points-$match->away_points) < 6 )
				$points['plus'] += 1;
		}

		return $points;
	}


	/**
	 * save results
	 *
	 * @param int $team_id
	 * @return void
	 */
	function updateResults( $match_id )
	{
		global $wpdb, $lmLoader;
	
		$admin = $lmLoader->getAdminPanel();
		
		$home_points = $_POST['home_points'][$match_id];
		$away_points = $_POST['away_points'][$match_id];
		
		$home_team = $_POST['home_team'][$match_id];
		$away_team = $_POST['away_team'][$match_id];

		$tries = $_POST['custom'][$match_id]['tries'];
		$conversions = $_POST['custom'][$match_id]['conversions'];
		$penalties = $_POST['custom'][$match_id]['penaltykicks'];

		$score['home'] = $tries['home'] * 5 + $conversions['home'] * 2 + $penalties['home'] * 3;
		$score['away'] = $tries['away'] * 5 + $conversions['away'] * 2 + $penalties['away'] * 3;

		if ( empty($score['home']) && empty($tries['away']) && empty($conversions['away']) && empty($penalties['away']) ) $score['home'] = 'NULL';
		if ( empty($score['away']) && empty($tries['home']) && empty($conversions['home']) && empty($penalties['home']) ) $score['away'] = 'NULL';

		$winner = $admin->getMatchResult( $score['home'], $score['away'], $home_team, $away_team, 'winner' );
		$loser =  $admin->getMatchResult( $score['home'], $score['away'], $home_team, $away_team, 'loser' );

		$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->leaguemanager_matches} SET `home_points` = '%d', `away_points` = '%d', `winner_id` = '%d', `loser_id` = '%d' WHERE `id` = '%d'", $score['home'], $score['away'], $winner, $loser, $match_id) );
	}


	/**
	 * save custom standings
	 *
	 * @param int $team_id
	 * @return void
	 */
	function saveStandings( $team_id )
	{
		global $wpdb, $leaguemanager;

		$team = $wpdb->get_results( $wpdb->prepare("SELECT `custom` FROM {$wpdb->leaguemanager_teams} WHERE `id` = '%d'", $team_id) );
		$team = $team[0];
		$custom = maybe_unserialize($team->custom);
		$custom = $this->getStandingsData($team_id, $custom);

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `custom` = '%s' WHERE `id` = '%d'", maybe_serialize($custom), $team_id ) );
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
		$data = $this->getStandingsData( $team->id, maybe_unserialize($team->custom), $matches );
		$team->gamepoints = $data['gamepoints'];
		
		return $team;
	}
	
	
	/**
	 * get standings data for given team
	 *
	 * @param int $team_id
	 * @param array $data
	 * @return array number of runs for and against as assoziative array
	 */
	function getStandingsData( $team_id, $data = array(), $matches = false )
	{
		global $leaguemanager;
		
		$data['gamepoints'] = array( 'plus' => 0, 'minus' => 0 );

		if ( !$matches ) $matches = $leaguemanager->getMatches( array("team_id" => $team_id, "limit" => false, "cache" => false) );
		foreach ( $matches AS $match ) {
			// Home Match
			if ( $team_id == $match->home_team ) {
				$data['gamepoints']['plus'] += $match->home_points;
				$data['gamepoints']['minus'] += $match->away_points;
			} else {
				$data['gamepoints']['plus'] += $match->away_points;
				$data['gamepoints']['minus'] += $match->home_points;
			}
		}
		
		return $data;
	}


	/**
	 * extend header for Standings Table in Backend
	 *
	 * @param none
	 * @return void
	 */
	function displayStandingsHeader()
	{
		echo '<th class="num">'.__( 'Game Points', 'leaguemanager' ).'</th>';
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
		global $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		if (!isset($team->gamepoints)) $team->gamepoints = array('plus' => '', 'minus' => '');
		
		if ( is_admin() && $rule == 'manual' )
			echo '<td><input type="text" size="2" name="custom['.$team->id.'][gamepoints][plus]" value="'.$team->gamepoints['plus'].'" />:<input type="text" size="2" name="custom['.$team->id.'][gamepoints][minus]" value="'.$team->gamepoints['minus'].'" /></td>';
		else
			echo '<td class="num">'.sprintf($league->point_format2, $team->gamepoints['plus'], $team->gamepoints['minus']).'</td>';
	}


	/**
	 * display hidden fields in team edit form
	 *
	 * @param object $team
	 * @return void
	 */
	function editTeam( $team )
	{
		if (!isset($team->gamepoints)) $team->gamepoints = array('plus' => '', 'minus' => '');
		echo '<input type="hidden" name="custom[gamepoints][plus]" value="'.$team->gamepoints['plus'].'" /><input type="hidden" name="custom[gamepoints][minus]" value="'.$team->gamepoints['minus'].'" />';
	}


	/**
	 * display Table Header for Match Administration
	 *
	 * @param none
	 * @return void
	 */
	function displayMatchesHeader()
	{
		echo '<th>'.__( 'Tries', 'leaguemanager' ).'</th><th>'.__('Conversions','leaguemanager').'</th><th>'.__('Penaltykicks', 'leaguemanager').'</th>';
	}


	/**
	 * display Table columns for Match Administration
	 *
	 * @param object $match
	 * @return void
	 */
	function displayMatchesColumns( $match )
	{
		if (!isset($match->tries)) $match->tries = array('home' => '', 'away' => '');
		if (!isset($match->conversions)) $match->conversions = array('home' => '', 'away' => '');
		if (!isset($match->penaltykicks)) $match->penaltykicks = array('home' => '', 'away' => '');
		
		echo '<td><input class="points" type="text" size="2" id="tries_'.$match->id.'_home" name="custom['.$match->id.'][tries][home]" value="'.$match->tries['home'].'" /> : <input class="points" type="text" size="2" id="tries_'.$match->id.'_away" name="custom['.$match->id.'][tries][away]" value="'.$match->tries['away'].'" /></td>';
		echo '<td><input class="points" type="text" size="2" id="conversions_'.$match->id.'_home" name="custom['.$match->id.'][conversions][home]" value="'.$match->conversions['home'].'" /> : <input class="points" type="text" size="2" id="conversions_'.$match->id.'_away" name="custom['.$match->id.'][conversions][away]" value="'.$match->conversions['away'].'" /></td>';
		echo '<td><input class="points" type="text" size="2" id="penaltykicks_'.$match->id.'_home" name="custom['.$match->id.'][penaltykicks][home]" value="'.$match->penaltykicks['home'].'" /> : <input class="points" type="text" size="2" id="penaltykicks_'.$match->id.'_away" name="custom['.$match->id.'][penaltykicks][away]" value="'.$match->penaltykicks['away'].'" /></td>';
	}


	/**
	 * export matches header
	 *
	 * @param string $content
	 * @return the content
	 */
	function exportMatchesHeader( $content )
	{
		$content .= "\t".utf8_decode(__( 'Tries', 'leaguemanager' ))."\t".utf8_decode(__('Conversions','leaguemanager'))."\t".utf8_decode(__('Penaltykicks','leaguemanager'));
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
		if ( isset($match->tries) ) {
			$content .= "\t".implode(":", $match->tries)."\t".implode(":",$match->conversions)."\t".implode(":", $match->penaltykicks);
		} else {
			$content .= "\t\t\t";
		}

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
		
		$tries = isset($line[9]) ? explode(":",$line[9]) : array('','');
		$conversions = isset($line[10]) ? explode(":",$line[10]) : array('','');
		$penalties = isset($line[11]) ? explode(":",$line[11]) : array('','');

		$custom[$match_id]['tries'] = array( 'home' => $tries[0], 'away' => $tries[1] );
		$custom[$match_id]['conversions'] = array( 'home' => $conversions[0], 'away' => $conversions[1] );
		$custom[$match_id]['penaltykicks'] = array( 'home' => $penalties[0], 'away' => $penalties[1] );

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
		$content .= "\t".__('Gamepoints', 'leaguemanager');
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
		if ( isset($team->gamepoints) )
			$content .= "\t".sprintf(":",$team->gamepoints['plus'], $team->gamepoints['minus']);
		else
			$content .= "\t";

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
		$gamepoints = isset($line[16]) ? explode(":", $line[16]) : array('','');
		$custom['gamepoints'] = array( 'plus' => $gamepoints[0], 'minus' => $gamepoints[1] );

		return $custom;
	}
}

$rugby = new LeagueManagerRugby();
?>