<?php
/**
 * Volleyball Class 
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright Copyright 2008
*/
class LeagueManagerVolleyball extends LeagueManager
{

	/**
	 * sports key
	 *
	 * @var string
	 */
	var $key = 'volleyball';


	/**
	 * number of sets
	 *
	 * @var int
	 */
	var $num_sets = 5;
	
	
	/**
	 * load specifif settings
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );
		add_filter( 'leaguemanager_point_rules_list', array(&$this, 'getPointRuleList') );
		add_filter( 'leaguemanager_point_rules',  array(&$this, 'getPointRules') );
		add_filter( 'team_points_'.$this->key, array(&$this, 'calculatePoints'), 10, 3 );
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
		add_action( 'leaguemanager_update_results_'.$this->key, array(&$this, 'updateResults') );
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
		$sports[$this->key] = __( 'Volleyball', 'leaguemanager' );
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
		$rules[$this->key] = __( 'Volleyball', 'leaguemanager' );

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
		$rules[$this->key] = array( 'forwin' => 3, 'fordraw' => 0, 'forloss' => 0, 'forwin_3_2' => 2, 'forloss_3_2' => 1 );
		
		return $rules;
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
		extract($rule);

		$num_won_3_2 = $this->getNumWonMatches3_2( $team_id );
		$num_lost_3_2 = $this->getNumLostMatches3_2( $team_id );

		$points['plus'] = $points['plus'] - $num_won_3_2 * $forwin + $num_won_3_2 * $forwin_3_2 + $num_lost_3_2 * $forloss_3_2;
		$points['minus'] = $points['minus'] - $num_lost_3_2 * $forwin + $num_won_3_2 * $forloss_3_2 + $num_lost_3_2 * $forwin_3_2;

		return $points;
	}
	
	
	/**
	 * get number of won matches after overtime
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumWonMatches3_2( $team_id )
	{
		global $wpdb;
		$matches = $wpdb->get_results( $wpdb->prepare("SELECT `home_team`, `away_team`, `home_points`, `away_points`, `custom` FROM {$wpdb->leaguemanager_matches} WHERE `winner_id` = '%d'", $team_id) );
		$num = 0;
		foreach ( $matches AS $match ) {
			$custom = maybe_unserialize($match->custom);
			// Home match won 3:2
			if ( $match->home_team == $team_id && $match->home_points == 3 && $match->away_points == 2 )
				$num++;
			// Away match won 3:2
			if ( $match->away_team == $team_id && $match->home_points == 2 && $match->away_points == 3 )
				$num++;
		}
		return $num;
	}


	/**
	 * get number of lost matches after overtime
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumLostMatches3_2( $team_id )
	{
		global $wpdb;
		$matches = $wpdb->get_results( $wpdb->prepare("SELECT `home_team`, `away_team`, `home_points`, `away_points`, `custom` FROM {$wpdb->leaguemanager_matches} WHERE `loser_id` = '%d'", $team_id) );
		$num = 0;
		foreach ( $matches AS $match ) {
			$custom = maybe_unserialize($match->custom);
			// Home match lost 3:2
			if ( $match->away_team == $team_id && $match->home_points == 3 && $match->away_points == 2 )
				$num++;
			// Away match lost 3:2
			if ( $match->home_team == $team_id && $match->home_points == 2 && $match->away_points == 3 )
				$num++;
		}
		return $num;
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
			$set_diff[$key] = $row->sets['won']-$row->sets['lost'];
			$won_sets[$key] = $row->sets['won'];
			$ballpoints_diff[$key] = $row->ballpoints['plus']-$row->ballpoints['minus'];
			$ballpoints[$key] = $row->ballpoints['plus'];
		}

		array_multisort( $points, SORT_DESC, $set_diff, SORT_DESC, $won_sets, SORT_DESC, $ballpoints_diff, SORT_DESC, $ballpoints, SORT_DESC, $teams );
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
		$team->sets = $data['sets'];
		$team->ballpoints = $data['ballpoints'];
		
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
		
		$data['sets'] = array( "won" => 0, "lost" => 0 );
		$data['ballpoints'] = array( 'plus' => 0, 'minus' => 0 );

		if ( !$matches ) $matches = $leaguemanager->getMatches( array("team_id" => $team_id, "limit" => false, "cache" => false) );
		foreach ( $matches AS $match ) {
			// Home Match
			if ( $team_id == $match->home_team ) {
				$data['sets']['won'] += $match->home_points;
				$data['sets']['lost'] += $match->away_points;
	
				foreach ( $match->sets AS $s => $set ) {
					$data['ballpoints']['plus'] += $set['home'];
					$data['ballpoints']['minus'] += $set['away'];
				}
			} else {
				$data['sets']['won'] += $match->away_points;
				$data['sets']['lost'] += $match->home_points;
	
				foreach ( $match->sets AS $s => $set ) {
					$data['ballpoints']['plus'] += $set['away'];
					$data['ballpoints']['minus'] += $set['home'];
				}
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
		echo '<th class="num">'.__( 'Sets', 'leaguemanager' ).'</th><th class="num">'.__( 'Ballpoints', 'leaguemanager' ).'</th>';
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

		if (!isset($team->sets)) $team->sets = array('won' => '', 'lost' => '');
		if (!isset($team->ballpoints)) $team->ballpoints = array('plus' => '', 'minus' => '');
		
		if ( is_admin() && $rule == 'manual' )
			echo '<td><input type="text" size="2" name="custom['.$team->id.'][sets][won]" value="'.$team->sets['won'].'" />:<input type="text" size="2" name="custom['.$team->id.'][sets][lost]" value="'.$team->sets['lost'].'" /></td><td><input type="text" size="2" name="custom['.$team->id.'][ballpoints][plus]" value="'.$team->ballpoints['plus'].'" />:<input type="text" size="2" name="custom['.$team->id.'][ballpoints][minus]" value="'.$team->ballpoints['minus'].'" /></td>';
		else
			echo '<td class="num">'.sprintf($league->point_format2, $team->sets['won'], $team->sets['lost']).'</td><td class="num">'.sprintf($league->point_format2, $team->ballpoints['plus'], $team->ballpoints['minus']).'</td>';
	}


	/**
	 * display hidden fields in team edit form
	 *
	 * @param object $team
	 * @return void
	 */
	function editTeam( $team )
	{
		if (!isset($team->sets)) $team->sets = array('won' => '', 'lost' => '');
		if (!isset($team->ballpoints)) $team->ballpoints = array('plus' => '', 'minus' => '');
		
		echo '<input type="hidden" name="custom[sets][won]" value="'.$team->sets['won'].'" /><input type="hidden" name="custom[sets][lost]" value="'.$team->sets['lost'].'" /><input type="hidden" name="custom[ballpoints][plus]" value="'.$team->ballpoints['plus'].'" /><input type="hidden" name="custom[ballpoints][minus]" value="'.$team->ballpoints['minus'].'" />';
	}


	/**
	 * display Table Header for Match Administration
	 *
	 * @param none
	 * @return void
	 */
	function displayMatchesHeader()
	{
		echo '<th colspan="5" style="text-align: center;">'.__( 'Sets', 'leaguemanager' ).'</th>';
	}


	/**
	 * display Table columns for Match Administration
	 *
	 * @param object $match
	 * @return void
	 */
	function displayMatchesColumns( $match )
	{
		if (!isset($match->sets)) {
			$match->sets = array();
		}
		
		for ( $i = 1; $i <= $this->num_sets; $i++ ) {
			if (!isset($match->sets[$i]['home'])) $match->sets[$i]['home'] = '';
			if (!isset($match->sets[$i]['away'])) $match->sets[$i]['away'] = '';
			
			echo '<td><input class="points" type="text" size="2" id="set_'.$match->id.'_'.$i.'_home" name="custom['.$match->id.'][sets]['.$i.'][home]" value="'.$match->sets[$i]['home'].'" /> : <input class="points" type="text" size="2" id="set_'.$match->id.'_'.$i.'_away" name="custom['.$match->id.'][sets]['.$i.'][away]" value="'.$match->sets[$i]['away'].'" /></td>';
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
		$content .= "\t".utf8_decode(__( 'Sets', 'leaguemanager' ))."\t\t\t\t";
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
		if ( isset($match->sets) ) {
			foreach ( $match->sets AS $j => $set ) {
				$content .= "\t".implode(":", $set);
			}
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
		for( $x = 9; $x <= 13; $x++ ) {
			$set = isset($line[$x]) ? explode(":",$line[$x]) : array('','');
			$custom[$match_id]['sets'][] = array( 'home' => $set[0], 'away' => $set[1] );
		}

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
		$content .= "\t".utf8_decode(__( 'Sets', 'leaguemanager' ))."\t".utf8_decode(__('Ballpoints', 'leaguemanager'));
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
		if ( isset($team->sets) )
			$content .= "\t".sprintf(":",$team->sets['won'], $team->sets['lost'])."\t".sprintf(":", $team->ballpoints['plus'], $team->ballpoints['minus']);
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
		$sets = isset($line[16]) ? explode(":", $line[16]) : array('','');
		$ballpoints = isset($line[17]) ? explode(":", $line[17]) : array('','');
		$custom['sets'] = array( 'won' => $sets[0], 'lost' => $sets[1] );
		$custom['ballpoints'] = array( 'plus' => $ballpoints[0], 'minus' => $ballpoints[1] );

		return $custom;
	}
	
	
	/**
	 * update match results and automatically calculate score
	 *
	 * @param int $match_id
	 * @return none
	 */
	function updateResults( $match_id )
	{
		global $wpdb, $leaguemanager;
		
		$match = $leaguemanager->getMatch( $match_id, false );
		if ( $match->home_points == "" && $match->away_points == "" ) {
			$score = array( 'home' => '', 'guest' => '' );
			for ( $i = 1; $i <= $this->num_sets; $i++ ) {
				if ( $match->sets[$i]['home'] != '' && $match->sets[$i]['away'] != '' ) {
					if ( $match->sets[$i]['home'] > $match->sets[$i]['away'] ) {
						$score['home'] += 1;
					} else {
						$score['guest'] += 1;
					}
				}
			}
			
			$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->leaguemanager_matches} SET `home_points` = '%s', `away_points` = '%s' WHERE `id` = '%d'", $score['home'], $score['guest'], $match_id) );
		}
	}
}

$volleyball = new LeagueManagerVolleyball();
?>