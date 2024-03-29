<?php
/**
 * Tennis Class 
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright Copyright 2008
*/
class LeagueManagerTennis extends LeagueManager
{

	/**
	 * sports key
	 *
	 * @var string
	 */
	var $key = 'tennis';


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
		add_filter( 'team_points_'.$this->key, array(&$this, 'calculatePoints'), 10, 3 );

		add_filter( 'leaguemanager_point_rules_list', array(&$this, 'getPointRuleList') );
		add_filter( 'leaguemanager_point_rules',  array(&$this, 'getPointRules') );

		add_filter( 'leaguemanager_export_matches_header_'.$this->key, array(&$this, 'exportMatchesHeader') );
		add_filter( 'leaguemanager_export_matches_data_'.$this->key, array(&$this, 'exportMatchesData'), 10, 2 );
		add_filter( 'leaguemanager_import_matches_'.$this->key, array(&$this, 'importMatches'), 10, 3 );
		add_filter( 'leaguemanager_export_teams_header_'.$this->key, array(&$this, 'exportTeamsHeader') );
		add_filter( 'leaguemanager_export_teams_data_'.$this->key, array(&$this, 'exportTeamsData'), 10, 2 );
		add_filter( 'leaguemanager_import_teams_'.$this->key, array(&$this, 'importTeams'), 10, 2 );

		add_filter( 'leaguemanager_matchtitle_'.$this->key, array(&$this, 'matchTitle'), 10, 3 );

		add_action( 'matchtable_header_'.$this->key, array(&$this, 'displayMatchesHeader'), 10, 0);
		add_action( 'matchtable_columns_'.$this->key, array(&$this, 'displayMatchesColumns') );
		add_action( 'leaguemanager_standings_header_'.$this->key, array(&$this, 'displayStandingsHeader') );
		add_action( 'leaguemanager_standings_columns_'.$this->key, array(&$this, 'displayStandingsColumns'), 10, 2 );
		add_action( 'team_edit_form_'.$this->key, array(&$this, 'editTeam') );

		add_action( 'edit_matches_header_'.$this->key, array(&$this, 'editMatchesHeader') );
		add_action( 'edit_matches_columns_'.$this->key, array(&$this, 'editMatchesColumns'), 10, 5 );	
		
		add_filter( 'leaguemanager_done_matches_'.$this->key, array(&$this, 'getNumDoneMatches'), 10, 2 );
		add_filter( 'leaguemanager_won_matches_'.$this->key, array(&$this, 'getNumWonMatches'), 10, 2 );
		add_filter( 'leaguemanager_tie_matches_'.$this->key, array(&$this, 'getNumTieMatches'), 10, 2 );
		add_filter( 'leaguemanager_lost_matches_'.$this->key, array(&$this, 'getNumLostMatches'), 10, 2 );
		add_action( 'leaguemanager_save_standings_'.$this->key, array(&$this, 'saveStandings') );
		add_action( 'leaguemanager_get_standings_'.$this->key, array(&$this, 'getStandingsFilter'), 10, 3 );

		add_action( 'league_settings_'.$this->key, array(&$this, 'leagueSettings') );
		
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
		$sports[$this->key] = __( 'Tennis', 'leaguemanager' );
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
		$rules[$this->key] = __('Tennis', 'leaguemanager');

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
		$rules[$this->key] = array( 'forwin' => 3, 'fordraw' => 0, 'forloss' => 0, 'forwin_split' => 2, 'forloss_split' => 1 );

		return $rules;
	}


	/**
	 * add league settings
	 *
	 * @param object $league
	 * @return void
	 */
	function leagueSettings( $league )
	{
		$league->num_sets = isset($league->num_sets) ? $league->num_sets : '';
		echo "<tr valign='top'>";
		echo "<th scope='row'><label for='num_sets'>".__('Number of Sets', 'leaguemanager')."</label></th>";
		echo "<td><input type='text' name='settings[num_sets]' id='num_sets' value='".$league->num_sets."' size='3' /></td>";
		echo "</tr>";
	}


	/**
	 * calculate Points: add match score
	 *
	 * @param array $points
	 * @param int $team_id
	 * @param array $rule
	 */
	function calculatePoints( $points, $team_id, $rule )
	{
		global $leaguemanager;

		extract($rule);

		$data = $this->getStandingsData($team_id);
		$points['plus'] = $data['straight_set']['win'] * $forwin + $data['split_set']['win'] * $forwin_split + $data['split_set']['lost'] * $forloss_split;
		$points['minus'] = $data['straight_set']['lost'] * $forwin + $data['split_set']['win'] * $forloss_split + $data['split_set']['lost'] * $forwin_split;

		return $points;
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
			$games_allowed[$key] = $row->games_allowed;
		}

		array_multisort( $points, SORT_DESC, $games_allowed, SORT_ASC, $teams );
		return $teams;
	}

	
	/**
	 * get number of done matches for partners
	 *
	 * @param int $num_done
	 * @param int $team_id
	 * @return int
	 */
	function getNumDoneMatches( $num_done, $team_id )
	{
		global $wpdb, $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();
		$season = $leaguemanager->getSeason($league);

		$matches = $leaguemanager->getMatches( array("league_id" => $league->id, "season" => $season['name'], "final" => '', "home_points" => "not_null", "away_points" => "not_null", "limit" => false) );
		foreach ( $matches AS $match ) {
			if ( isset($match->home_partner) && isset($match->guest_partner) ) {
				if ( $match->home_partner == $team_id || $match->guest_partner == $team_id )
					$num_done++;
			}
		}
		return $num_done;
	}


	/**
	 * get number of won matches for partners
	 *
	 * @param int $num_won
	 * @param int $team_id
	 * @return int
	 */
	function getNumWonMatches( $num_won, $team_id )
	{
		global $wpdb, $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();
		$season = $leaguemanager->getSeason($league);

		$matches = $leaguemanager->getMatches( array("league_id" => $league->id, "season" => $season['name'], "final" => '', "limit" => false) );
		foreach ( $matches AS $match ) {
			if ( isset($match->home_partner) && isset($match->guest_partner) ) {
				if ( $match->home_partner == $team_id && $match->winner_id == $match->home_team )
					$num_won++;
				elseif ( $match->guest_partner == $team_id && $match->winner_id == $match->away_team )
					$num_won++;
					
			}
		}
		return $num_won;
	}


	/**
	 * get number of tie matches for partners
	 *
	 * @param int $num_tie
	 * @param int $team_id
	 * @return int
	 */
	function getNumTieMatches( $num_tie, $team_id )
	{
		global $wpdb, $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();
		$season = $leaguemanager->getSeason($league);

		$matches = $leaguemanager->getMatches( array("league_id" => $league->id, "season" => $season['name'], "final" => '', "winner_id" => -1, "loser_id" => -1, "limit" => false) );
		foreach ( $matches AS $match ) {
			if ( isset($match->home_partner) && isset($match->guest_partner) ) {
				if ( $match->home_partner == $team_id || $match->guest_partner == $team_id )
					$num_tie++;
					
			}
		}
		return $num_tie;
	}
	

	/**
	 * get number of lost matches for partners
	 *
	 * @param int $num_lost
	 * @param int $team_id
	 * @return int
	 */
	function getNumLostMatches( $num_lost, $team_id )
	{
		global $wpdb, $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();
		$season = $leaguemanager->getSeason($league);

		$matches = $leaguemanager->getMatches( array("league_id" => $league->id, "season" => $season['name'], "final" => '', "limit" => false) );
		foreach ( $matches AS $match ) {
			if ( isset($match->home_partner) && isset($match->guest_partner) ) {
				if ( $match->home_partner == $team_id && $match->winner_id == $match->away_team )
					$num_lost++;
				elseif ( $match->guest_partner == $team_id && $match->winner_id == $match->home_team )
					$num_lost++;
					
			}
		}
		return $num_lost;
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

		$team = $wpdb->get_results( "SELECT `custom` FROM {$wpdb->leaguemanager_teams} WHERE `id` = {$team_id}" );
		$team = $team[0];
		$custom = isset($team->custom) ? maybe_unserialize($team->custom) : '';
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
		$team->straight_set = $data['straight_set'];
		$team->split_set = $data['split_set'];
		$team->games_allowed = $data['games_allowed'];
		
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
		
		$data['straight_set'] = $data['split_set'] = array( 'win' => 0, 'lost' => 0 );
		$data['games_allowed'] = 0;

		$league = $leaguemanager->getCurrentLeague();
		$season = $leaguemanager->getSeason($league);

		if ( !$matches ) $matches = $leaguemanager->getMatches( array("league_id" => $league->id, "season" => $season['name'], "final" => '', "limit" => false, "cache" => false) );
		foreach ( $matches AS $match ) {
			if ( $match->home_team == $team_id || $match->away_team == $team_id || ( isset($match->home_partner) && $match->home_partner == $team_id ) || ( isset($match->guest_partner) && $match->guest_partner == $team_id ) ) {
				if (!isset($match->home_partner)) $match->home_partner = '';
				$index = ( $team_id == $match->home_team || $team_id == $match->home_partner ) ? 'player2' : 'player1';

				// First check for Split Set, else it's straight set
				if ( $match->sets[$league->num_sets]['player1'] != '' && $match->sets[$league->num_sets]['player2'] != '' ) {
					if ( $match->winner_id == $team_id || ($team_id == $match->home_partner && $match->winner_id == $match->home_team) || ($team_id == $match->guest_partner && $match->winner_id == $match->away_team) ) {
						$data['split_set']['win'] += 1;
						for ( $j = 1; $j <= $league->num_sets-1; $j++  ) {
							$data['games_allowed'] += $match->sets[$j][$index];
						}
					} else {
						$data['split_set']['lost'] += 1;
						for ( $j = 1; $j <= $league->num_sets-1; $j++  ) {
							$data['games_allowed'] += $match->sets[$j][$index];
						}
						$data['games_allowed'] += 1;
					}
				} else {
					if (!isset($match->home_partner)) $match->home_partner = "";
					if (!isset($match->guest_partner)) $match->guest_partner = "";
					if ( $match->winner_id == $team_id || ($team_id == $match->home_partner && $match->winner_id == $match->home_team) || ($team_id == $match->guest_partner && $match->winner_id == $match->away_team) ) {
						$data['straight_set']['win'] += 1;
						for ( $j = 1; $j <= $league->num_sets-1; $j++  ) {
							$data['games_allowed'] += $match->sets[$j][$index];
						}
					} else {
						$data['straight_set']['lost'] += 1;
						for ( $j = 1; $j <= $league->num_sets-1; $j++  ) {
							$data['games_allowed'] += $match->sets[$j][$index];
						}
					}
				}
			}
		}

		return $data;
	}


	/**
	 * Filter match title for double matches
	 *
	 * @param object $match
	 * @param array $teams
	 * @param string $title
	 * @return string
	 */
	function matchTitle( $title, $match, $teams )
	{
		$homeTeam = ( isset($match->home_partner) && !empty($match->home_partner) ) ? $teams[$match->home_team]['title'] . '/' . $teams[$match->home_partner]['title'] : $teams[$match->home_team]['title'];
		$awayTeam = ( isset($match->guest_partner) && !empty($match->guest_partner) ) ? $teams[$match->away_team]['title'] . '/' . $teams[$match->guest_partner]['title'] : $teams[$match->away_team]['title'];

		$title = sprintf("%s - %s", $homeTeam, $awayTeam);
		
		return $title;

	}


	/**
	 * extend header for Standings Table in Backend
	 *
	 * @param none
	 * @return void
	 */
	function displayStandingsHeader()
	{
		echo '<th class="num">'.__( 'Straight Set', 'leaguemanager' ).'</th><th>'.__( 'Split Set', 'leaguemanager' ).'</th><th>'.__( 'Games Allowed', 'leaguemanager' ).'</th>';
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

		if (!isset($team->straight_set)) $team->straight_set = array('win' => '', 'lost' => '');
		if (!isset($team->split_set)) $team->split_set = array('win' => '', 'lost' => '');
		if (!isset($team->games_allowed)) $team->games_allowed = '';
		if ( is_admin() && $rule == 'manual' )
			echo '<td><input type="text" size="2" name="custom['.$team->id.'][straight_set][win]" value="'.$team->straight_set['win'].'" />:<input type="text" size="2" name="custom['.$team->id.'][straight_set][lost]" value="'.$team->straight_set['lost'].'" /></td><td><input type="text" size="2" name="custom['.$team->id.'][split_set][win]" value="'.$team->split_set['win'].'" />:<input type="text" size="2" name="custom['.$team->id.'][split_set][lost]" value="'.$team->split_set['lost'].'" /></td><td><input type="text" size="2" name="custom['.$team->id.'][games_allowed]" value="'.$team->games_allowed.'" /></td>';
		else
			echo '<td class="num">'.sprintf($league->point_format2, $team->straight_set['win'], $team->straight_set['lost']).'</td><td class="num">'.sprintf($league->point_format2, $team->split_set['win'], $team->split_set['lost']).'</td><td class="num">'.$team->games_allowed.'</td>';
	}


	/**
	 * display hidden fields in team edit form
	 *
	 * @param object $team
	 * @return void
	 */
	function editTeam( $team )
	{
		if (!isset($team->straight_set)) $team->straight_set = array('win' => '', 'lost' => '');
		if (!isset($team->split_set)) $team->split_set = array('win' => '', 'lost' => '');
		if (!isset($team->games_allowd)) $team->games_allowed = '';
		
		echo '<input type="hidden" name="custom[straight_set][win]" value="'.$team->straight_set['win'].'" /><input type="hidden" name="custom[straight_set][lost]" value="'.$team->straight_set['lost'].'" /><input type="hidden" name="custom[split_set][win]" value="'.$team->split_set['win'].'" /><input type="hidden" name="custom[split_set][lost]" value="'.$team->split_set['lost'].'" /><input type="hidden" name="custom[games_allowed]" value="'.$team->games_allowed.'" />';
	}


	/**
	 * Add custom fields to match form
	 *
	 * @param none
	 */
	function editMatchesHeader()
	{
		echo '<th scope="col">'.__( 'Home Partner', 'leaguemanager' ).'</th><th scope="col">'.__( 'Guest Partner', 'leaguemanager' ).'</th>';
	}
	function editMatchesColumns( $match, $league, $season, $teams, $i )
	{
		$cols = array( 'home_partner', 'guest_partner' );
		foreach ( $cols AS $col ) {
			echo '<td>';
			echo '<select name="custom['.$i.']['.$col.']" id="custom_'.$i.'_'.$col.'">';
			echo '<option value="0">'.__('None','leaguemanager').'</option>';
			foreach ( $teams AS $team ) {
				
				echo '<option value="'.$team->id.'"'.selected($team->id, $match->{$col}).'>'.$team->title.'</option>';
			}
			echo '</select>';
			echo '</td>';
		}
	}


	/**
	 * display Table Header for Match Administration
	 *
	 * @param none
	 * @return void
	 */
	function displayMatchesHeader()
	{
		global $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();
		echo '<th colspan="'.$league->num_sets.'" style="text-align: center;">'.__( 'Sets', 'leaguemanager' ).'</th>';
	}


	/**
	 * display Table columns for Match Administration
	 *
	 * @param object $match
	 * @return void
	 */
	function displayMatchesColumns( $match )
	{
		global $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		if ( !isset($league->num_sets) || empty($league->num_sets) ) {
			$leaguemanager->setMessage(__('You have to define the number of sets', 'leaguemanager'), true);
			$leaguemanager->printMessage();
			echo "<td></td>";
		} else {
			for ( $i = 1; $i <= $league->num_sets; $i++ ) {
				if (!isset($match->sets[$i])) {
					$match->sets[$i] = array('player1' => '', 'player2' => '');
				}
				echo '<td><input class="points" type="text" size="2" id="set_'.$match->id.'_'.$i.'_player1" name="custom['.$match->id.'][sets]['.$i.'][player1]" value="'.$match->sets[$i]['player1'].'" /> : <input class="points" type="text" size="2" id="set_'.$match->id.'_'.$i.'_player2" name="custom['.$match->id.'][sets]['.$i.'][player2]" value="'.$match->sets[$i]['player2'].'" /></td>';
			}
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
		global $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		$content .= "\t".utf8_decode(__( 'Sets', 'leaguemanager' )).str_repeat("\t", $league->num_sets-1);
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
		global $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		if ( isset($match->sets) ) {
			foreach ( $match->sets AS $j => $set ) {
				$content .= "\t".implode(":", $set);
			}
		} else {
			$content .= str_repeat("\t", $league->num_sets);
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
		for( $x = 9; $x <= 11; $x++ ) {
			$set = isset($line[$x]) ? explode(":",$line[$x]) : array('','');
			$custom[$match_id]['sets'][] = array( 'player1' => $set[0], 'player2' => $set[1] );
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
		$content .= "\t".utf8_decode(__( 'Straight Set Win', 'leaguemanager' ))."\t".utf8_decode(__('Split Set Win', 'leaguemanager'))."\t".utf8_decode(__('Straight Set Lost'))."\t".utf8_decode(__('Split Set Lost', 'leaguemanager'))."\t".utf8_decode(__('Games Allowed', 'leaguemanager'));
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
		if ( isset($team->straight_set) )
			$content .= "\t".$team->straight_set['win']."\t".$team->split_set['win']."\t".$team->straight_set['lost']."\t".$team->split_set['lost']."\t".$team->games_allowed;
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
		$custom['straight_set'] = ( isset($line[16]) && isset($line[17]) ) ? array( 'win' => $line[16], 'lost' => $line[17] ) : array( 'win' => '', 'lost' => '' );
		$custom['split_set'] = ( isset($line[18]) && isset($line[19]) ) ? array( 'win' => $line[18], 'lost' => $line[19] ) : array( 'win' => '', 'lost' => '' );
		$custom['games_allowed'] = isset($line[20])? $line[20] : '';

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
			foreach ( $match->sets AS $set ) {
				if ( $set['player1'] != '' && $set['player2'] != '' ) {
					if ( $set['player1'] > $set['player2'] ) {
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

$tennis = new LeagueManagerTennis();
?>