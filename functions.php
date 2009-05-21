<?php
/**
 * display widget statically
 *
 * @param int $league_id ID of league
 */
function leaguemanager_display_widget( $league_id ) {
	$widget = new LeagueManagerWidget();
	
	echo "<ul id='leaguemanger-widget-".$league_id."' class='leaguemanager_widget'>";
	$widget->display( array( 'league_id' => $league_id ) );
	echo "</ul>";
}


/**
 * display standings table manually
 *
 * @param int $league_id ID of league
 * @param mixed $season
 * @param string $template (optional)
 * @param string $logo 'true' or 'false' (default: 'true')
 * @param string $mode 'extend' or 'compact' (default: 'extend')
 *
 * @return void
 */
function leaguemanager_standings( $league_id, $season = false, $template = 'extend', $logo = 'true' ) {
	global $lmShortcodes;
	echo $lmShortcodes->showStandings( array('league_id' => $league_id, 'logo' => $logo, 'season' => $season, 'template' => $template) );
}


/**
 * display crosstable table manually
 *
 * @param int $league_id ID of league
 * @param mixed $season
 * @param string $mode empty or 'popup' (default: empty)
 * @return void
 */
function leaguemanager_crosstable( $league_id, $season = false, $template = '', $mode = '' ) {
	global $lmShortcodes;
	echo $lmShortcodes->showCrosstable( array('league_id' => $league_id, 'mode' => $mode, 'template' => $temaplate, 'season' => $season) );
}


/**
 * display matches table manually
 *
 * @param int $league_id ID of league
 * @param mixed $season
 * @param string $template (optional)
 * @param string $mode empty or 'all' or 'home' (default: empty => matches are displayed ordered by match day)
 * @param boolean $archive
 * @return void
 */
function leaguemanager_matches( $league_id, $season = false, $template = '', $mode = '', $archive = false ) {
	global $lmShortcodes;
	echo $lmShortcodes->showMatches( array('league_id' => $league_id, 'mode' => $mode, 'season' => $season, 'archive' => $archive) );
}


/**
 * display one match manually
 *
 * @param int $match_id
 * @return void
 */
function leaguemanager_match( $match_id, $template = '' ) {
	global $lmShortcodes;
	echo $lmShortcodes->showMatch( array('id' => $match_id, 'template' => $template) );
}

/**
 * Ajax Response to set match index in widget
 *
 * @param none
 * @return void
 */
function leaguemanager_get_match_box() {
	global $lmWidget;
	$current = $_POST['current'];
	$element = $_POST['element'];
	$operation = $_POST['operation'];
	$league_id = $_POST['league_id'];
	$match_limit = ( $_POST['match_limit'] == 'false' ) ? false : $_POST['match_limit'];
	$widget_number = $_POST['widget_number'];
	$season = $_POST['season'];

	if ( $operation == 'next' )
		$index = $current + 1;
	elseif ( $operation == 'prev' )
		$index = $current - 1;
	
	$lmWidget->setMatchIndex( $index, $element );

	if ( $element == 'next' ) {
		$parent_id = 'next_matches_'.$widget_number;
		//$el_id = 'next_match_box';
		$match_box = $lmWidget->showNextMatchBox($widget_number, $league_id, $season, $match_limit, false);
	} elseif ( $element == 'prev' ) {
		$parent_id = 'prev_matches_'.$widget_number;
		//$el_id = 'prev_match_box';
		$match_box = $lmWidget->showPrevMatchBox($widget_number, $league_id, $season, $match_limit, false);
	}

	die( "jQuery('div#".$parent_id."').fadeOut('fast', function() {
		jQuery('div#".$parent_id."').html('".addslashes_gpc($match_box)."').fadeIn('fast');
	});");
}


/**
 * SACK response to manually set team ranking
 *
 * @since 2.8
 */
function leaguemanager_save_team_standings() {
	global $wpdb, $lmLoader;
	$ranking = $_POST['ranking'];
	$ranking = $lmLoader->adminPanel->getRanking($ranking);
	foreach ( $ranking AS $rank => $team_id ) {
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `rank` = '%d' WHERE `id` = '%d'", $rank, $team_id ) );
	}
}

/**
* SACK response to manually set team ranking
*
* @since 2.8
*/
function leaguemanager_save_add_points() {
	global $wpdb;
	$team_id = intval($_POST['team_id']);
	$points = intval($_POST['points']);
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `add_points` = '%d' WHERE `id` = '%d'", $points, $team_id ) );

	die("Leaguemanager.doneLoading('loading_".$team_id."')");
}


/**
 * helper function to allocate matches and teams of a league to a aseason and maybe other league
 *
 * @param int $league_id ID of current league
 * @param string $season season to set
 * @param int $new_league_id ID of different league to add teams and matches to (optionl)
 */
function move_league_to_season( $league_id, $season, $old_season = false, $new_league_id = false ) {
	global $leaguemanager, $wpdb;
	if ( !$new_league_id ) $new_league_id = $league_id;
	
	if ( $teams = $leaguemanager->getTeams("`league_id` = ".$league_id." AND `season` = ".$old_season."") ) {
		foreach ( $teams AS $team ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `season` = '%d', `league_id` = '%d' WHERE `id` = '%d'", $season, $new_league_id, $team->id ) );
		}
	}
	if ( $matches = $leaguemanager->getMatches("`league_id` = ".$league_id." AND `season` = ".$old_season."") ) {
		foreach ( $matches AS $match ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `season` = '%d', `league_id` = '%d' WHERE `id` = '%d'", $season, $new_league_id, $match->id ) );
		}
	}
}

?>
