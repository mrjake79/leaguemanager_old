<?php
/**
 * display widget statically
 *
 * @param int $number
 * @param array $instance
 */
function leaguemanager_display_widget( $number, $instance ) {
	echo "<ul id='leaguemanger-widget-".$instance['league']."' class='leaguemanager_widget'>";
	$widget = new LeagueManagerWidget(true);
	$widget->widget( array('number' => $number), $instance );
	echo "</ul>";
}


/**
 * display next match box
 *
 * @param int $number
 * @param array $instance
 */
function leaguemanager_display_next_match_box( $number, $instance ) {
	$widget = new LeagueManagerWidget(true);
	$widget->showNextMatchBox( $number, $instance );
}


/**
 * display previous match box
 *
 * @param int $number
 * @param array $instance
 */
function leaguemanager_display_prev_match_box( $number, $instance ) {
	$widget = new LeagueManagerWidget(true);
	$widget->showPrevMatchBox( $number, $instance );
}


/**
 * get last N matches of given team
 *
 * @param int $team_id
 * @param int $ne number of matches
 */
function get_last_matches( $team_id, $n = 1 ) {
	global $leaguemanager;
	
	$matches = $leaguemanager->getMatches( array( 'time' => 'prev', 'team_id' => intval($team_id), 'limit' => intval($n) ) );
	return $matches;
}


/**
 * get next N matches of given team
 *
 * @param int $team_id
 * @param int $ne number of matches
 */
function get_next_matches( $team_id, $n = 1 ) {
	global $leaguemanager;
	
	$matches = $leaguemanager->getMatches( array( 'time' => 'next', 'team_id' => intval($team_id), 'limit' => intval($n) ) );
	return $matches;
}


/**
 * display standings table manually
 *
 * @param int $league_id League ID
 * @param array $args associative array of parameters, see default values (optional)
 * @return void
 */
function leaguemanager_standings( $league_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array( 'season' => false, 'template' => 'last5', 'logo' => 'true', 'group' => false, 'home' => 0 );
	$args = array_merge($defaults, $args);
	$args['league_id'] = intval($league_id);
	echo $lmShortcodes->showStandings( $args );
	
	//extract($args, EXTR_SKIP);
	//echo $lmShortcodes->showStandings( array('league_id' => $league_id, 'logo' => $logo, 'season' => $season, 'template' => $template, 'group' => $group, 'home' => $home) );
}

/**
 * display latest results manually
 *
 * @param int $id_team
 * @param int $limit additional argument (optional)
 * @return $latest_results
 */

function get_latest_results($id_team, $limit = 5) {
     global $wpdb;
     $latest_results = $wpdb->get_results( $wpdb->prepare("SELECT `id`, `date`, `home_points`, `away_points`, `home_team`, `away_team`, `custom`
             FROM {$wpdb->leaguemanager_matches}
             WHERE (home_team = %d OR away_team = %d)
             AND (DATEDIFF(NOW(), `date`) >= 0)
             AND (home_points != '' OR away_points != '')
             ORDER BY date DESC
             LIMIT %d", $id_team, $id_team, $limit) );

	$i = 0;
	foreach ( $latest_results AS $match ) {
		$latest_results[$i]->custom = $match->custom = maybe_unserialize($match->custom);
		$latest_results[$i]->custom = $match->custom = stripslashes_deep($match->custom);
		$latest_results[$i] = (object)array_merge((array)$match, (array)$match->custom);
		//	unset($matches[$i]->custom);

		$i++;
	}
    return $latest_results;
}

/**
 * get next game for Last 5 function
 *
 * @param int $id_team
 * @param int $limit additional argument (optional)
 * @return $next_results
 */

function get_next_match($id_team, $limit = 1) {
     global $wpdb;
     $next_results = $wpdb->get_results( $wpdb->prepare("SELECT `id`, `date`, `home_team`, `away_team`
             FROM {$wpdb->leaguemanager_matches}
             WHERE (home_team = %d OR away_team = %d)
             AND (DATEDIFF(NOW(), `date`) <= 0)
             ORDER BY date ASC
             LIMIT %d", $id_team, $id_team, $limit) );

             return $next_results;
}


/**
 * display crosstable table manually
 *
 * @param int $league_id
 * @param array $args associative array of parameters, see default values (optional)
 * @return void
 */
function leaguemanager_crosstable( $league_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('season' => false, 'logo' => 'true', 'group' => '', 'template' => '', 'mode' => '');
	$args = array_merge($defaults, $args);
	$args['league_id'] = intval($league_id);
	echo $lmShortcodes->showCrosstable( $args );
	//extract($args, EXTR_SKIP);
	//echo $lmShortcodes->showCrosstable( array('league_id' => $league_id, 'mode' => $mode, 'template' => $template, 'season' => $season) );
}


/**
 * display matches table manually
 *
 * @param int $league_id
 * @param array $args associative array of parameters, see default values (optional)
 * @return void
 */
function leaguemanager_matches( $league_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('season' => '', 'template' => '', 'mode' => '', 'limit' => 'true', 'archive' => false, 'match_day' => -1, 'group' => false, 'roster' => false, 'order' => false, 'show_match_day_selection' => '', 'show_team_selection' => '', 'time' => '', 'team' => 0, 'home_only' => 'false', 'match_date' => false, 'dateformat' => '', 'timeformat' => '');
	$args = array_merge($defaults, $args);
	$args['league_id'] = intval($league_id);
	
	//extract($args, EXTR_SKIP);
	echo $lmShortcodes->showMatches($args);
	//echo $lmShortcodes->showMatches( array('league_id' => $league_id, 'limit' => $limit, 'mode' => $mode, 'season' => $season, 'archive' => $archive, 'template' => $template, 'roster' => $roster, 'order' => $order, 'match_day' => $match_day, 'group' => $group) );
}


/**
 * display one match manually
 *
 * @param int $match_id
 * @param array $args additional arguments as associative array (optional)
 * @return void
 */
function leaguemanager_match( $match_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('template' => '');
	$args = array_merge($defaults, $args);
	extract($args, EXTR_SKIP);

	echo $lmShortcodes->showMatch( array('id' => $match_id, 'template' => $template) );
}


/**
 * display team list manually
 *
 * @param int|string $league_id
 * @param array $args additional arguments as associative array (optional)
 * @return void
 */
function leaguemanager_teams( $league_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('season' => false, 'template' => '', 'group' => false);
	$args = array_merge($defaults, $args);
	$args['league_id'] = intval($league_id);
	echo $lmShortcodes->showTeams( $args );
	//extract($args, EXTR_SKIP);

	//echo $lmShortcodes->showTeams( array('league_id' => $league_id, 'season' => $season, 'template' => $template) );
}


/**
 * display one team manually
 *
 * @param int $team_id
 * @param array $args additional arguments as associative array (optional)
 * @return void
 */
function leaguemanager_team( $team_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('template' => '');
	$args = array_merge($defaults, $args);
	extract($args, EXTR_SKIP);

	echo $lmShortcodes->showTeam( array('id' => intval($team_id), 'template' => $template) );
}


/**
 * display championship manually
 *
 * @param int $league_id
 * @param array $args additional arguments as associative array (optional)
 * @return void
 */
function leaguemanager_championship( $league_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('template' => '', 'season' => false);
	$args = array_merge($defaults, $args);
	extract($args, EXTR_SKIP);

	echo $lmShortcodes->showChampionship( array('league_id' => intval($league_id), 'template' => $template, 'season' => $season) );
}


/**
 * display championship manually
 *
 * @param int $league_id
 * @param array $args additional arguments as associative array (optional)
 * @return void
 */
function leaguemanager_archive( $league_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('template' => '');
	$args = array_merge($defaults, $args);
	//extract($args, EXTR_SKIP);

	echo $lmShortcodes->showArchive( $args );
}


/**
 * display championship manually
 *
 * @param int $league_id
 * @param array $args additional arguments as associative array (optional)
 * @return void
 */
function leaguemanager_league( $league_id, $args = array() ) {
	global $lmShortcodes;
	$defaults = array('season' => false, 'template' => '');
	$args = array_merge($defaults, $args);
	//extract($args, EXTR_SKIP);

	echo $lmShortcodes->showLeague( $args );
}


/**
 * helper function to allocate matches and teams of a league to a season and maybe other league
 *
 * @param int $league_id ID of current league
 * @param string $season season to set
 * @param int $new_league_id ID of different league to add teams and matches to (optionl)
 * @param int $old_season (optional) old season if you want to re-allocate teams and matches
 */
function move_league_to_season( $league_id, $season, $new_league_id = false, $old_season = false ) {
	global $leaguemanager, $wpdb;
	if ( !$new_league_id ) $new_league_id = $league_id;
	
	$team_args = array("league_id" => $league_id);
	if ( $old_season ) $team_args["season"] = $old_season;
	
	$match_args = $team_args;
	
	if ( $teams = $leaguemanager->getTeams($team_args) ) {
		foreach ( $teams AS $team ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `season` = '%d', `league_id` = '%d' WHERE `id` = '%d'", $season, $new_league_id, $team->id ) );
		}
	}
	if ( $matches = $leaguemanager->getMatches($match_args) ) {
		foreach ( $matches AS $match ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `season` = '%d', `league_id` = '%d' WHERE `id` = '%d'", $season, $new_league_id, $match->id ) );
		}
	}
}

?>