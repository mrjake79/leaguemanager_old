<?php
/**
 * Gymnastics Class
 *
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright Copyright 2008
*/
class LeagueManagerGymnastics extends LeagueManager
{
	/**
	 * sports key
	 *
	 * @var string
	 */
	var $key = 'gymnastics';


	/**
	 * load custom setings
	 *
	 * @param
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );
		add_filter( 'rank_teams_'.$this->key, array(&$this, 'rankTeams') );
		add_filter( 'team_points2_'.$this->key, array(&$this, 'calculateApparatusPoints') );

		add_filter( 'leaguemanager_export_matches_header_'.$this->key, array(&$this, 'exportMatchesHeader') );
		add_filter( 'leaguemanager_export_matches_data_'.$this->key, array(&$this, 'exportMatchesData'), 10, 2 );
		add_filter( 'leaguemanager_import_matches_'.$this->key, array(&$this, 'importMatches'), 10, 3 );
		
		add_filter( 'leaguemanager_export_teams_header_'.$this->key, array(&$this, 'exportTeamsHeader') );
		add_filter( 'leaguemanager_export_teams_data_'.$this->key, array(&$this, 'exportTeamsData'), 10, 2 );
		add_filter( 'leaguemanager_import_teams_'.$this->key, array(&$this, 'importTeams'), 10, 2 );

		add_action( 'matchtable_header_'.$this->key, array(&$this, 'displayMatchesHeader'), 10, 0 );
		add_action( 'matchtable_columns_'.$this->key, array(&$this, 'displayMatchesColumns') );
		add_action( 'leaguemanager_standings_header_'.$this->key, array(&$this, 'displayStandingsHeader') );
		add_action( 'leaguemanager_standings_columns_'.$this->key, array(&$this, 'displayStandingsColumns'), 10, 2 );
		
		add_action( 'leaguemanager_save_standings_'.$this->key, array(&$this, 'saveStandings') );
		add_action( 'leaguemanager_update_results_'.$this->key, array(&$this, 'updateResults') );
	}
	function LeagueManagerGymnastics()
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
		$sports[$this->key] = __( 'Gymnastics', 'leaguemanager' );
		return $sports;
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
	 * get standings data for given team
	 *
	 * @param int $team_id
	 * @param array $data
	 * @return array number of runs for and against as assoziative array
	 */
	function getStandingsData( $team_id, $data = array() )
	{
		global $leaguemanager;
		
		$data['apparatus'] = array( "floor" => 0, "pommelhorse" => 0, "rings" => 0, "vault" => 0, "parallelbars" => 0, "highbars" => 0 );
		$points = array( 'win' => 2, 'lost' => 0);
		
		$matches = $leaguemanager->getMatches( array("team_id" => $team_id, "limit" => false) );
		foreach ( $matches AS $match ) {
			if ( isset($match->floor) ) {
				// Home Match
				if ( $team_id == $match->home_team ) {
					$data['apparatus']['floor'] += ( $match->floor['home'] > $match->floor['guest'] ) ? $points['win'] : $points['lost'];
					$data['apparatus']['pommelhorse'] += ( $match->pommelhorse['home'] > $match->floor['guest'] ) ? $points['win'] : $points['lost'];
					$data['apparatus']['rings'] += ( $match->rings['home'] > $match->rings['guest'] ) ? $points['win'] : $points['lost'];
					$data['apparatus']['vault'] += ( $match->vault['home'] > $match->vault['guest'] ) ? $points['win'] : $points['lost'];
					$data['apparatus']['parallelbars'] += ( $match->parallelbars['home'] > $match->parallelbars['guest'] ) ? $points['win'] : $points['lost'];
					$data['apparatus']['highbars'] += ( $match->highbars['home'] > $match->highbars['guest'] ) ? $points['win'] : $points['lost'];
				} else {
					$data['apparatus']['floor'] += ( $match->floor['home'] < $match->floor['guest'] ) ? $points['win'] : $points['lost'];
					$data['apparatus']['pommelhorse'] += ( $match->pommelhorse['home'] < $match->floor['guest'] ) ? $points['win'] : $points['lost'];
					$data['apparatus']['rings'] += ( $match->rings['home'] < $match->rings['guest'] ) ? $points['win'] : $points['lost'];
					$data['apparatus']['vault'] += ( $match->vault['home'] < $match->vault['guest'] ) ? $points['win'] : $points['lost'];
					$data['apparatus']['parallelbars'] += ( $match->parallelbars['home'] < $match->parallelbars['guest'] ) ? $points['win'] : $points['lost'];
					$data['apparatus']['highbars'] += ( $match->highbars['home'] < $match->highbars['guest'] ) ? $points['win'] : $points['lost'];
				}
			}
		}
		
		return $data;
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
			$points2[$key] = $row->points2['plus'];
			$done[$key] = $row->done_matches;
		}

		array_multisort( $points, SORT_DESC, $points2, SORT_DESC, $done, SORT_ASC, $teams );
		return $teams;
	}


	/**
	 * extend header for Standings Table
	 *
	 * @param none
	 * @return void
	 */
	function displayStandingsHeader()
	{
		echo '<th class="num">'.__( 'Floor', 'leaguemanager' ).'</th>';
		echo '<th class="num">'.__( 'Pommelhorse', 'leaguemanager' ).'</th>';
		echo '<th class="num">'.__( 'Rings', 'leaguemanager' ).'</th>';
		echo '<th class="num">'.__( 'Vault', 'leaguemanager' ).'</th>';
		echo '<th class="num">'.__( 'Parallel bars', 'leaguemanager' ).'</th>';
		echo '<th class="num">'.__( 'High bars', 'leaguemanager' ).'</th>';
		echo '<th class="num">'.__( 'AP', 'leaguemanager' ).'</th><th>'.__( 'Diff', 'leaguemanager').'</th>';
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

		if ( !isset($team->apparatus) ) $team->apparatus = array( 'floor' => 0, 'pommelhorse' => 0, 'rings' => 0, 'vault' => 0, 'parallelbars' => 0, 'highbars' => 0 );
		
		if ( is_admin() && $rule == 'manual' ) {
			echo '<td class="num"><input type="text" size="2" name="custom['.$team->id.'][apparatus][floor]" value="'.$team->apparatus['floor'].'" /></td>';
			echo '<td class="num"><input type="text" size="2" name="custom['.$team->id.'][apparatus][pommelhorse]" value="'.$team->apparatus['pommelhorse'].'" /></td>';
			echo '<td class="num"><input type="text" size="2" name="custom['.$team->id.'][apparatus][rings]" value="'.$team->apparatus['rings'].'" /></td>';
			echo '<td class="num"><input type="text" size="2" name="custom['.$team->id.'][apparatus][vault]" value="'.$team->apparatus['vault'].'" /></td>';
			echo '<td class="num"><input type="text" size="2" name="custom['.$team->id.'][apparatus][parallelbars]" value="'.$team->apparatus['parallelbars'].'" /></td>';
			echo '<td class="num"><input type="text" size="2" name="custom['.$team->id.'][apparatus][highbars]" value="'.$team->apparatus['highbars'].'" /></td>';
			echo '<td class="num"><input type="text" size="2" name="custom['.$team->id.'][points2][plus]" value="'.$team->points2_plus.'" /> : <input type="text" size="2" name="custom['.$team->id.'][points2][minus]" value="'.$team->points2_minus.'" /></td>';
		} else {
			echo "<td class='num'>".$team->apparatus['floor']."</td>";
			echo "<td class='num'>".$team->apparatus['pommelhorse']."</td>";
			echo "<td class='num'>".$team->apparatus['rings']."</td>";
			echo "<td class='num'>".$team->apparatus['vault']."</td>";
			echo "<td class='num'>".$team->apparatus['parallelbars']."</td>";
			echo "<td class='num'>".$team->apparatus['highbars']."</td>";
			echo "<td class='num'>".sprintf($league->point_format2, $team->points2_plus, $team->points2_minus)."</td>";
		}
	
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
		echo '<th>'.__( 'Floor', 'leaguemanager' ).'</th>';
		echo '<th>'.__( 'Pommelhorse', 'leaguemanager' ).'</th>';
		echo '<th>'.__( 'Rings', 'leaguemanager' ).'</th>';
		echo '<th>'.__( 'Vault', 'leaguemanager' ).'</th>';
		echo '<th>'.__( 'Parallel bars', 'leaguemanager' ).'</th>';
		echo '<th>'.__( 'High bars', 'leaguemanager' ).'</th>';
		echo '<th>'.__( 'Apparatus Points', 'leaguemanager' ).'</th>';
	}


	/**
	 * display Table columns for Match Administration
	 *
	 * @param object $match
	 * @return void
	 */
	function displayMatchesColumns( $match )
	{
		if (!isset($match->apparatus_points))
			$match->apparatus_points = array('plus' => '', 'minus' => '');
		if ( !isset($match->floor) )
			$match->floor = array('home' => '', 'guest' => '');
		if ( !isset($match->pommelhorse) )
			$match->pommelhorse = array('home' => '', 'guest' => '');
		if ( !isset($match->rings) )
			$match->rings = array('home' => '', 'guest' => '');
		if ( !isset($match->vault) )
			$match->vault = array('home' => '', 'guest' => '');
		if ( !isset($match->parallelbars) )
			$match->parallelbars = array('home' => '', 'guest' => '');
		if ( !isset($match->highbars) )
			$match->highbars = array('home' => '', 'guest' => '');
		
		echo '<td><input class="points" type="text" size="2" id="floor_home_'.$match->id.'" name="custom['.$match->id.'][floor][home]" value="'.$match->floor['home'].'" /> : <input class="points" type="text" size="2" id="floor_guest_'.$match->id.'" name="custom['.$match->id.'][floor][guest]" value="'.$match->floor['guest'].'" /></td>';
		echo '<td><input class="points" type="text" size="2" id="pommelhorse_home_'.$match->id.'" name="custom['.$match->id.'][pommelhorse][home]" value="'.$match->pommelhorse['home'].'" /> : <input class="points" type="text" size="2" id="pommelhorse_guest_'.$match->id.'" name="custom['.$match->id.'][pommelhorse][guest]" value="'.$match->pommelhorse['guest'].'" /></td>';
		echo '<td><input class="points" type="text" size="2" id="rings_home_'.$match->id.'" name="custom['.$match->id.'][rings][home]" value="'.$match->rings['home'].'" /> : <input class="points" type="text" size="2" id="rings_guest_'.$match->id.'" name="custom['.$match->id.'][rings][guest]" value="'.$match->rings['guest'].'" /></td>';
		echo '<td><input class="points" type="text" size="2" id="vault_home_'.$match->id.'" name="custom['.$match->id.'][vault][home]" value="'.$match->vault['home'].'" /> : <input class="points" type="text" size="2" id="vault_guest_'.$match->id.'" name="custom['.$match->id.'][vault][guest]" value="'.$match->vault['guest'].'" /></td>';
		echo '<td><input class="points" type="text" size="2" id="parallelbars_home_'.$match->id.'" name="custom['.$match->id.'][parallelbars][home]" value="'.$match->parallelbars['home'].'" /> : <input class="points" type="text" size="2" id="parallelbars_guest_'.$match->id.'" name="custom['.$match->id.'][parallelbars][guest]" value="'.$match->parallelbars['guest'].'" /></td>';
		echo '<td><input class="points" type="text" size="2" id="highbars_home_'.$match->id.'" name="custom['.$match->id.'][highbars][home]" value="'.$match->highbars['home'].'" /> : <input class="points" type="text" size="2" id="highbars_guest_'.$match->id.'" name="custom['.$match->id.'][highbars][guest]" value="'.$match->highbars['guest'].'" /></td>';
		echo '<td><input class="points" type="text" size="2" id="apparatus_points_plus_'.$match->id.'" name="custom['.$match->id.'][apparatus_points][plus]" value="'.$match->apparatus_points['plus'].'" /> : <input class="points" type="text" size="2" id="apparatus_points_minus_'.$match->id.'" name="custom['.$match->id.'][apparatus_points][minus]" value="'.$match->apparatus_points['minus'].'" /></td>';
	}


	/**
	 * export matches header
	 *
	 * @param string $content
	 * @return the content
	 */
	function exportMatchesHeader( $content )
	{
		$content .= "\t".utf8_decode(__('Floor', 'leaguemanager'))."\t".utf8_decode(__('Pommelhorse', 'leaguemanager'))."\t".utf8_decode(__('Rings', 'leaguemanager'))."\t".utf8_decode(__('Vault', 'leaguemanager'))."\t".utf8_decode(__('Parallel bars', 'leaguemanager'))."\t".utf8_decode(__('High bars', 'leaguemanager'))."\t".utf8_decode(_x( 'AP', 'apparatus points', 'leaguemanager' ));
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
		if ( isset($match->apparatus_points) && $match->apparatus_points['plus'] !== "" && $match->apparatus_points['minus'] !== "" ) {
			$content .= "\t".sprintf("%d:%d", $match->floor['home'], $match->floor['guest'])."\t".sprintf("%d:%d", $match->pommelhorse['home'], $match->pommelhorse['guest'])."\t".sprintf("%d:%d", $match->rings['home'], $match->rings['guest'])."\t".sprintf("%d:%d", $match->vault['home'], $match->vault['guest'])."\t".sprintf("%d:%d", $match->parallelbars['home'], $match->parallelbars['guest'])."\t".sprintf("%d:%d", $match->highbars['home'], $match->highbars['guest'])."\t".sprintf("%d:%d", $match->apparatus_points['plus'], $match->apparatus_points['minus']);
		} else {
			$content .= "\t\t\t\t\t\t\t";
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
		$floor = (isset($line[9]) && !empty($line[9])) ? explode(":", $line[9]) : array("","");
		$pommelhorse = (isset($line[10]) && !empty($line[10])) ? explode(":", $line[10]) : array("","");
		$rings = (isset($line[11]) && !empty($line[11])) ? explode(":", $line[11]) : array("","");
		$vault = (isset($line[12]) && !empty($line[12])) ? explode(":", $line[12]) : array("","");
		$parallelbars = (isset($line[13]) && !empty($line[13])) ? explode(":", $line[13]) : array("","");
		$highbars = (isset($line[14]) && !empty($line[14])) ? explode(":", $line[14]) : array("","");
		$ap = (isset($line[15]) && !empty($line[15])) ? explode(":", $line[15]) : array("","");
		$custom[$match_id]['apparatus_points'] = array( 'plus' => $ap[0], 'minus' => isset($ap[1]) ? $ap[1] : '' );
		$custom[$match_id]['floor'] = array( 'home' => $floor[0], 'guest' => $floor[1] );
		$custom[$match_id]['pommelhorse'] = array( 'home' => $pommelhorse[0], 'guest' => $pommelhorse[1] );
		$custom[$match_id]['rings'] = array( 'home' => $rings[0], 'guest' => $rings[1] );
		$custom[$match_id]['vault'] = array( 'home' => $vault[0], 'guest' => $vault[1] );
		$custom[$match_id]['parallelbars'] = array( 'home' => $parallelbars[0], 'guest' => $parallelbars[1] );
		$custom[$match_id]['highbars'] = array( 'home' => $highbars[0], 'guest' => $highbars[1] );

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
		$content .= "\t".utf8_decode(__( 'Floor', 'leaguemanager' ))."\t".utf8_decode(__('Pommelhorse', 'leaguemanager'))."\t".utf8_decode(__('Rings', 'leaguemanager'))."\t".utf8_decode(__('Vault', 'leaguemanager'))."\t".utf8_decode(__('Parallel bars', 'leaguemanager'))."\t".utf8_decode(__('High bars', 'leaguemanager'));
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
		if ( isset($team->apparatus['floor']) )
			$content .= "\t".$team->apparatus['floor']."\t".$team->apparatus['pommelhorse']."\t".$team->apparatus['rings']."\t".$team->apparatus['vault']."\t".$team->apparatus['parallelbars']."\t".$team->apparatus['highbars'];
		else
			$content .= "\t\t\t\t\t\t";

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
		$custom['floor'] = isset($line[16]) ? $line[16] : 0;
		$custom['pommelhorse'] = isset($line[17]) ? $line[17] : 0;
		$custom['rings'] = isset($line[18]) ? $line[18] : 0;
		$custom['vault'] = isset($line[19]) ? $line[19] : 0;
		$custom['parallelbars'] = isset($line[20]) ? $line[20] : 0;
		$custom['highbars'] = isset($line[21]) ? $line[21] : 0;

		return $custom;
	}
	
	
	/**
	 * calculate apparatus points
	 *
	 * @param int $team_id
	 * @return array of points
	 */
	function calculateApparatusPoints( $team_id )
	{
		global $wpdb, $leaguemanager;

		$home = $leaguemanager->getMatches( array("home_team" => $team_id, "limit" => false) );
		$away = $leaguemanager->getMatches( array("away_team" => $team_id, "limit" => false) );

		$points = array( 'plus' => 0, 'minus' => 0);
		if ( count($home) > 0 ) {
			foreach ( $home AS $match ) {
				$custom = (array)maybe_unserialize($match->custom);
				
				if (!isset($custom['apparatus_points'])) $custom['apparatus_points'] = array('plus' => '', 'minus' => '');
				$points['plus'] += intval($custom['apparatus_points']['plus']);
				$points['minus'] += intval($custom['apparatus_points']['minus']);
			}
		}

		if ( count($away) > 0 ) {
			foreach ( $away AS $match ) {
				$custom = (array)maybe_unserialize($match->custom);
				
				if (!isset($custom['apparatus_points'])) $custom['apparatus_points'] = array('plus' => '', 'minus' => '');
				$points['plus'] += intval($custom['apparatus_points']['minus']);
				$points['minus'] += intval($custom['apparatus_points']['plus']);
			}
		}

		return $points;
	}
	
	
	/**
	 * update match results and automatically calculate apparatus points and score
	 *
	 * @param int $match_id
	 * @return none
	 */
	function updateResults( $match_id )
	{
		global $wpdb, $leaguemanager;
		
		$match = $leaguemanager->getMatch( $match_id );
		
		if ( $match->home_points == "" && $match->away_points == "" ) {
			$home_points = $match->floor['home'] + $match->pommelhorse['home'] + $match->rings['home'] + $match->vault['home'] + $match->parallelbars['home'] + $match->highbars['home'];
			$away_points = $match->floor['guest'] + $match->pommelhorse['guest'] + $match->rings['guest'] + $match->vault['guest'] + $match->parallelbars['guest'] + $match->highbars['guest'];
			if ( $home_points == 0 && $away_points == 0 )
				$home_points = $away_points = '';
			
			$ap = array('plus' => 0, 'minus' => 0);
			$apparatus = array( 'floor', 'pommelhorse', 'rings', 'vault', 'parallelbars', 'highbars' );
			foreach ( $apparatus AS $key ) {
				if ( $match->{$key}['home'] != '' && $match->{$key}['guest'] != '' ) {
					if ( $match->{$key}['home'] > $match->{$key}['guest'] )
						$ap['plus'] += 2;
					else
						$ap['minus'] += 2;
				}
			}
			if ( $ap['plus'] == 0 && $ap['minus'] == 0 ) $ap = array( 'plus' => '', 'minus' => '' );
					
			$custom = $match->custom;
			
			if ( $custom['apparatus_points']['plus'] == '' && $custom['apparatus_points']['minus'] == '' )
				$custom['apparatus_points'] = $ap;
			
			$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->leaguemanager_matches} SET `home_points` = '%s', `away_points` = '%s', `custom` = '%s' WHERE `id` = '%d'", $home_points, $away_points, maybe_serialize($custom), $match_id) );
		}
	}
}

$gymnastics = new LeagueManagerGymnastics();
?>