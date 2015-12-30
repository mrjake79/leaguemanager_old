<?php
/**
* Shortcodes class for the WordPress plugin LeagueManager
*
* @author 	Kolja Schleich
* @package	LeagueManager
* @copyright Copyright 2008
*/

class LeagueManagerShortcodes extends LeagueManager
{
	/**
	 * checks if bridge is active
	 *
	 * @var boolean
	 */
	var $bridge = false;


	/**
	 * initialize shortcodes
	 *
	 * @param boolean $bridge
	 * @return void
	 */
	function __construct($bridge = false)
	{
		global $lmLoader;

		$this->addShortcodes();
		if ( $bridge ) {
			global $lmBridge;
			$this->bridge =  true;
			$this->lmBridge = $lmBridge;
		}
	}
	function LeagueManagerShortcodes($bridge = false)
	{
		$this->__construct($bridge);
	}


	/**
	 * Adds shortcodes
	 *
	 * @param none
	 * @return void
	 */
	function addShortcodes()
	{
		add_shortcode( 'standings', array(&$this, 'showStandings') );
		add_shortcode( 'matches', array(&$this, 'showMatches') );
		add_shortcode( 'match', array(&$this, 'showMatch') );
		add_shortcode( 'championship', array(&$this, 'showChampionship') );
		add_shortcode( 'crosstable', array(&$this, 'showCrosstable') );
		add_shortcode( 'teams', array(&$this, 'showTeams') );
		add_shortcode( 'team', array(&$this, 'showTeam') );
		add_shortcode( 'leaguearchive', array(&$this, 'showArchive') );

		add_action( 'leaguemanager_teampage', array(&$this, 'showTeam') );
	}


	/**
	 * Function to display League Standings
	 *
	 *	[standings league_id="1" mode="extend|compact" template="name"]
	 *
	 * - league_id is the ID of league
	 * - league_name (optional) get league by name and not id
	 * - season: display specific season (optional). default is current season
	 * - template is the template used for displaying. Replace name appropriately. Templates must be named "standings-template.php" (optional)
	 * - group: optional
	 *
	 * @param array $atts
	 * @param boolean $widget (optional)
	 * @return the content
	 */
	function showStandings( $atts, $widget = false )
	{
		global $wpdb, $leaguemanager;

		extract(shortcode_atts(array(
			'league_id' => 0,
			'league_name' => '',
			'logo' => 'true',
			'show_website' => 'true',
			'template' => 'extend',
			'season' => false,
			'group' => false,
			'home' => 0
		), $atts ));

		$search = !empty($league_name) ? $league_name : intval($league_id);
		$league = $leaguemanager->getLeague( $search );
		if (!$season) {
			$season = $leaguemanager->getSeason( $league );
			$league->season = $season;
			$season = $season['name'];
		}

		$team_args = array("league_id" => $league->id, "season" => $season);
		if ( $group ) $team_args["group"] = $group;
		$teams = $leaguemanager->getTeams( $team_args );
		
		if ( !empty($home) ) {
			$teamlist = array();
			foreach ( $teams AS $offset => $team ) {
				if ( $team->home == 1 ) {
					$low = $offset-$home;
					$high = $offset+$home;

					if ( $low < 0 ) {
						$high -= $low;
						$low = 0;
					} elseif ( $high > count($teams)-1 ) {
						$low -= $high - count($teams)+1;
						$high = count($teams)-1;
					}

					for ( $x = $low; $x <= $high; $x++ ) {
						if ( !array_key_exists($teams[$x]->rank, $teamlist) )
							$teamlist[$teams[$x]->rank] = $teams[$x];
					}
				}
			}

			$teams = array_values($teamlist);
		}

		$i = 0; $class = array('alternate');
		foreach ( $teams AS $team ) {
			$class = ( !isset($class) || in_array('alternate', $class) ) ? array() : array('alternate');
			// Add classes for ascend or descend
			if ( $team->rank <= $league->num_ascend ) $class[] = 'ascend';
			elseif ( count($teams)-$team->rank < $league->num_descend ) $class[] =  'descend';

			// Add class for relegation
			if ( $team->rank >  count($teams)-$league->num_descend-$league->num_relegation && $team->rank <= count($teams)-$league->num_descend ) $class[] = 'relegation';

			// Add class for home team
			if ( 1 == $team->home ) $class[] = 'homeTeam';

			$url = get_permalink();
			$url = add_query_arg( 'team_'.$league->id, $team->id, $url );

			$teams[$i]->pageURL = $url;
			//if ( $league->team_ranking == 'auto' ) $teams[$i]->rank = $i+1;
			$teams[$i]->class = implode(' ', $class);
			$teams[$i]->logoURL = $leaguemanager->getThumbnailUrl($team->logo);
			if ( 1 == $team->home ) $teams[$i]->title = '<strong>'.$team->title.'</strong>';

            if (  $show_website == 'true'  ){
    			if ( $team->website != '' ) $teams[$i]->title = '<a href="http://'.$team->website.'" target="_blank">'.$team->title.'</a>';
            } else {
    			$teams[$i]->title = $team->title;
            }

			$team->points_plus += $team->add_points; // add or substract points
			$teams[$i]->points = sprintf($league->point_format, $team->points_plus, $team->points_minus);
			$teams[$i]->points2 = sprintf($league->point_format2, $team->points2_plus, $team->points2_minus);
			$i++;
		}

		$league->show_logo = ( $logo == 'true' ) ? true : false;
		$league->show_website = ( $show_website == 'true' ) ? true : false;

		if ( !$widget && $this->checkTemplate('standings-'.$league->sport) )
			$filename = 'standings-'.$league->sport;
		else
			$filename = 'standings-'.$template;

		$out = $this->loadTemplate( $filename, array('league' => $league, 'teams' => $teams, 'widget' => $widget) );

		return $out;
	}


	/**
	 * Function to display League Matches
	 *
	 *	[matches league_id="1" mode="all|home|racing" template="name" roster=ID]
	 *
	 * - league_id is the ID of league
	 * - league_name: get league by name and not ID (optional)
	 * - mode can be either "all" or "home". For racing it must be "racing". If it is not specified the matches are displayed on a weekly basis
	 * - season: display specific season (optional)
	 * - template is the template used for displaying. Replace name appropriately. Templates must be named "matches-template.php" (optional)
	 * - archive: true or false, check if archive page
	 * - roster is the ID of individual team member (currently only works with racing)
	 * - match_day: specific match day (integer)
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showMatches( $atts )
	{
		global $leaguemanager, $championship;

		extract(shortcode_atts(array(
			'league_id' => 0,
			'league_name' => '',
			'team' => 0,
			'template' => '',
			'mode' => '',
			'season' => '',
			'limit' => 'true',
			'archive' => false,
			'roster' => false,
			'order' => false,
			'match_day' => '',
			'home_only' => 'false',
			'match_date' => false,
			'group' => false,
			'time' => false,
			'dateformat' => '',
			'timeformat' => '',
			'show_team_selection' => '',
			'show_match_day_selection' => ''
		), $atts ));
		
		$search = !empty($league_name) ? $league_name : intval($league_id);
		$league = $leaguemanager->getLeague( $search );
		$league_id = $this->league_id = $league->id;
		$leaguemanager->setLeagueId($league_id);
		$match_date = $match_date . " 00:00:00";
		$class = 'alternate';
		if ($match_day == 'last' || $match_day == 'next' || $match_day == 'current' || $match_day == 'latest') $league->isCurrMatchDay = $match_day;
		else $league->isCurrMatchDay = '';
		
		$league->show_match_day_selection = (!empty($match_day) && is_numeric($match_day)) ? false : true;
		$league->show_team_selection = empty($team) ? true : false;
		
		if ($show_team_selection == 'true') $league->show_team_selection = true;
		if ($show_team_selection == 'false') $league->show_team_selection = false;
		if (!empty($team) && empty($match_day)) $league->show_match_day_selection = false;
		if ($show_match_day_selection == 'true') $league->show_match_day_selection = true;
		if ($show_match_day_selection == 'false') $league->show_match_day_selection = false;
		
		if ( $league->mode == 'championship' ) $championship->initialize($league->id);

		if ( !$group && isset($_GET['group']) ) $group = $_GET['group'];

		if ( !isset($_GET['match']) ) {
			$season = $leaguemanager->getSeason($league, $season);
			$league->num_match_days = $season['num_match_days'];
			$leaguemanager->setSeason($season);
			$season = $season['name'];

			// Set match day
			if (isset($_GET['match_day']))
				$match_day = intval($_GET['match_day']);
			elseif (isset($_GET['match_day_'.$league->id]))
				$match_day = intval($_GET['match_day_'.$league->id]);
			elseif ($league->isCurrMatchDay != "")
				$match_day = $leaguemanager->getMatchDay($league->isCurrMatchDay);
			
			$leaguemanager->setMatchDay($match_day);
			
			$league->match_days = ( ( empty($mode) || $mode == 'racing' ) && !$time && $league->num_match_days > 0 ) ? true : false;
			/*if (!empty($team)) {
				$league->match_days = false;
				$match_day = -1;
			}*/
			
			if ( !$league_id ) {
    			$teams = $leaguemanager->getTeams( array("league_id" => 'any', "season" => "any", "orderby" => array("title" => "ASC")), 'ARRAY' );
    			$match_args = array("league_id" => 'any', "season" => 'any', "final" => '');
    		} else {
    			$teams = $leaguemanager->getTeams( array("league_id" => $league_id, "season" => $season, "orderby" => array("title" => "ASC")), 'ARRAY' );
				$match_args = array("league_id" => $league_id, "season" => $season, "final" => '');
    		}

			// Standard is match day based with team dropdown
			if ( !empty($team) || (isset($_GET['team_id_'.$league->id]) && !empty($_GET['team_id_'.$league->id])) )
				$team_id = !empty($team) ? $team : (int)$_GET['team_id_'.$league->id];

			if ( !empty($team_id) ) {
				$match_args['team_id'] = $team_id;
			} elseif ( !empty($group) ) {
				$match_args['group'] = $group;
			}
			
			if ($match_day != "" && $match_day != -1 && empty($mode)) $match_args['match_day'] = $match_day;
	
			if ( $limit && is_numeric($limit) ) $leaguemanager->setNumMatchesPerPage($limit);
			
			$leaguemanager->getMatchDay();
			if ( $limit === 'false' )  {
				$match_args['limit'] = false;
			} elseif ( $limit && is_numeric($limit) ) {
				$match_args['limit'] = intval($limit);
			}
			
			$match_args['time'] = $time;
			$match_args['order'] = $order;
			$match_args['match_date'] = $match_date;
			$match_args['home_only'] = ( $home_only == 1 || $home_only == 'true' ) ? true : false;
			//if ($home_only == 0) $match_args['home_only'] = false;
			
			$matches = $leaguemanager->getMatches( $match_args );

			$leaguemanager->setNumMatches($leaguemanager->getMatches(array_merge($match_args, array('limit' => false, 'count' => true))));
			$league->current_page = $leaguemanager->getCurrentPage($league->id);
			$league->pagination = ( $limit == 'true' ) ? $leaguemanager->getPageLinks($league->current_page, "paged_".$league->id) : '';
			
            foreach ( $matches AS $key => $row ) {
                $matchdate[$key] = $row->date;
            }

            if ($time=='prev1'){ array_multisort( $matchdate, SORT_ASC, $matches ); }

			$i = 0; $class = 'alternate';
			foreach ( $matches AS $match ) {
				$class = ( 'alternate' == $class ) ? '' : 'alternate';

				$matches[$i]->class = $class;
				$matches[$i]->hadPenalty = $match->hadPenalty = ( isset($match->penalty) && $match->penalty['home'] != '' && $match->penalty['away'] != '' ) ? true : false;
				$matches[$i]->hadOvertime = $match->hadOvertime = ( isset($match->overtime) && $match->overtime['home'] != '' && $match->overtime['away'] != '' ) ? true : false;

				$home = $leaguemanager->getTeam($match->home_team);
				$away = $leaguemanager->getTeam($match->away_team);
				$matches[$i]->homeLogo = $home ? $leaguemanager->getThumbnailUrl($home->logo) : '';
				$matches[$i]->awayLogo = $away ? $leaguemanager->getThumbnailUrl($away->logo) : '';
		
				$url = esc_url(get_permalink());
				$url = add_query_arg( 'match_'.$league->id, $match->id, $url );
				$matches[$i]->pageURL = esc_url($url);

				if ( $timeformat ) {
    				$matches[$i]->start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? '' : mysql2date($timeformat, $match->date);
	            } else {
    				$matches[$i]->start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);
	            }
				if ( $dateformat ) {
    				$matches[$i]->date = ( substr($match->date, 0, 10) == '0000-00-00' ) ? 'N/A' : mysql2date($dateformat, $match->date);
	            } else {
    				$matches[$i]->date = ( substr($match->date, 0, 10) == '0000-00-00' ) ? 'N/A' : mysql2date(get_option('date_format'), $match->date);
	            }

//				$matches[$i]->start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);
//				$matches[$i]->date = ( substr($match->date, 0, 10) == '0000-00-00' ) ? 'N/A' : mysql2date(get_option('date_format'), $match->date);
/*
				$home_logo_img = ($teams[$match->home_team]['logo'] != "") ? "<img src='".$leaguemanager->getThumbnailUrl($teams[$match->home_team]['logo'])."' alt='' />" : "";
				$away_logo_img = ($teams[$match->away_team]['logo'] != "") ? "<img src='".$leaguemanager->getThumbnailUrl($teams[$match->away_team]['logo'])."' alt='' />" : "";
				$matches[$i]->title = ( isset($matches[$i]->title) && !empty($matches[$i]->title) ) ? $match->title : sprintf("%s %s &#8211; %s %s", $teams[$match->home_team]['title'], $home_logo_img, $away_logo_img, $teams[$match->away_team]['title']);
				$matches[$i]->title = apply_filters( 'leaguemanager_matchtitle_'.$league->sport, $matches[$i]->title, $match, $teams );
				if ( $leaguemanager->isHomeTeamMatch( $match->home_team, $match->away_team, $teams ) )
					$matches[$i]->title = '<strong>'.$matches[$i]->title.'</strong>';
*/
				$matches[$i]->title = $leaguemanager->getMatchTitle($match->id);
				$matches[$i]->report = ( $match->post_id != 0 ) ? '(<a href="'.get_permalink($match->post_id).'">'.__('Report', 'leaguemanager').'</a>)' : '';

				if ( $match->hadPenalty ) {
					$matches[$i]->homeScore = $match->penalty['home']+$match->overtime['home'];
					$matches[$i]->awayScore = $match->penalty['away']+$match->overtime['away'];
					$matches[$i]->score = sprintf("%s - %s", $matches[$i]->homeScore, $matches[$i]->awayScore)." ".__( '(o.P.)', 'leaguemanager' );
				} elseif ( $match->hadOvertime ) {
					$matches[$i]->homeScore = $match->overtime['home'];
					$matches[$i]->awayScore = $match->overtime['away'];
					$matches[$i]->score = sprintf("%s - %s", $matches[$i]->homeScore, $matches[$i]->awayScore)." ".__( '(AET)', 'leaguemanager' );
					//$matches[$i]->score = sprintf("%s - %s", $matches[$i]->home_points, $matches[$i]->away_points);
				} elseif ( $match->home_points != NULL && $match->away_points != NULL ) {
					$matches[$i]->homeScore = $match->home_points;
					$matches[$i]->awayScore = $match->away_points;
					$matches[$i]->score = sprintf("%s - %s", $matches[$i]->homeScore, $matches[$i]->awayScore);
				} else {
					$matches[$i]->homeScore = "-";
					$matches[$i]->awayScore = "-";
					$matches[$i]->score = sprintf("%s:%s", $matches[$i]->homeScore, $matches[$i]->awayScore);
				}
				
				$i++;
			}
		} else {
			$matches = $teams = false;
		}

		if ( empty($template) && $this->checkTemplate('matches-'.$league->sport) )
			$filename = 'matches-'.$league->sport;
		else
			$filename = ( !empty($template) ) ? 'matches-'.$template : 'matches';

		$out = $this->loadTemplate( $filename, array('league' => $league, 'matches' => $matches, 'teams' => $teams, 'season' => $season, 'roster' => $roster ) );

		return $out;
	}


	/**
	 * Function to display single match
	 *
	 * [match id="1" template="name"]
	 *
	 * - id is the ID of the match to display
	 * - template is the template used for displaying. Replace name appropriately. Templates must be named "match-template.php" (optional)
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showMatch( $atts )
	{
		global $leaguemanager, $lmStats;
		extract(shortcode_atts(array(
			'id' => 0,
			'template' => '',
		), $atts ));

		$match = $leaguemanager->getMatch(intval($id));
		$league = $leaguemanager->getLeague($match->league_id);
		$home = $leaguemanager->getTeam($match->home_team);
		$away = $leaguemanager->getTeam($match->away_team);

		$match->hadPenalty = ( isset($match->penalty) && $match->penalty['home'] != '' && $match->penalty['away'] != '' ) ? true : false;
		$match->hadOvertime = ( isset($match->overtime) && $match->overtime['home'] != '' && $match->overtime['away'] != '' ) ? true : false;

		//$match->home_points = ( NULL == $match->home_points ) ? '-' : $match->home_points;
		//$match->away_points = ( NULL == $match->away_points ) ? '-' : $match->away_points;

		$match->homeTeam = $home ? $home->title : '';
		$match->awayTeam = $away ? $away->title : '';
		if (!isset($match->title)) $match->title = $match->homeTeam . "&#8211;" . $match->awayTeam;

		$match->homeLogo = $home ? $home->logo : '';
		$match->awayLogo = $away ? $away->logo : '';

		$match->start_time = ( '00' == $match->hour && '00' == $match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);
		$match->date = ( substr($match->date, 0, 10) == '0000-00-00' ) ? 'N/A' : mysql2date(get_option('date_format'), $match->date);

		$match->report = ( $match->post_id != 0 ) ? '(<a href="'.get_permalink($match->post_id).'">'.__('Report', 'leaguemanager').'</a>)' : '';

		if ( $match->hadPenalty ) {
			$match->homeScore = $match->penalty['home']+$match->overtime['home'];
			$match->awayScore = $match->penalty['away']+$match->overtime['away'];
			$match->score = sprintf("%s - %s", $match->homeScore, $match->awayScore)." ".__( '(o.P.)', 'leaguemanager' );
		} elseif ( $match->hadOvertime ) {
			$match->homeScore = $match->overtime['home'];
			$match->awayScore = $match->overtime['away'];
			$match->score = sprintf("%s - %s", $match->homeScore, $match->awayScore)." ".__( '(AET)', 'leaguemanager' );
			//$match->score = sprintf("%s - %s", $match->home_points, $match->away_points);
		} elseif ( $match->home_points != NULL && $match->away_points != NULL ) {
			$match->homeScore = $match->home_points;
			$match->awayScore = $match->away_points;
			$match->score = sprintf("%s - %s", $match->homeScore, $match->awayScore);
		} else {
			$match->homeScore = "-";
			$match->awayScore = "-";
			$match->score = sprintf("%s:%s", $match->homeScore, $match->awayScore);
		}
		
		if ( empty($template) && $this->checkTemplate('match-'.$league->sport) )
			$filename = 'match-'.$league->sport;
		else
			$filename = ( !empty($template) ) ? 'match-'.$template : 'match';

		$out = $this->loadTemplate( $filename, array('league' => $league, 'match' => $match) );

		return $out;
	}


	/**
	 * Function to display Championship
	 *
	 *	[championship league_id="1" template="name"]
	 *
	 * - league_id is the ID of league
	 * - league_name: get league by name and not ID (optional)
	 * - season: display specific season (optional)
	 * - template is the template used for displaying. Replace name appropriately. Templates must be named "matches-template.php" (optional)
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showChampionship( $atts )
	{
		global $leaguemanager, $championship;

		extract(shortcode_atts(array(
			'league_id' => 0,
			'league_name' => '',
			'template' => '',
			'season' => false,
		), $atts ));

		$search = !empty($league_name) ? $league_name : intval($league_id);
		$league = $leaguemanager->getLeague( $search );
		if ( !$season ) {
			$season = $leaguemanager->getSeason($league);
			$season = $season['name'];
		}
		$league->season = $season;
		$league_id = $this->league_id = $league->id;

		$championship->initialize($league->id);

		$finals = array();
		foreach ( $championship->getFinals() AS $final ) {
			$class = 'alternate';
			$data['key'] = $final['key'];
			$data['name'] = $final['name'];
			$data['num_matches'] = $final['num_matches'];
			$data['colspan'] = ( $championship->getNumTeamsFirstRound()/2 >= 4 ) ? ceil(4/$final['num_matches']) : ceil(($championship->getNumTeamsFirstRound()/2)/$final['num_matches']);

			$matches_raw = $leaguemanager->getMatches( array("league_id" => $league->id, "season" => $season, "final" => $final['key'], "orderby" => array("id" => "ASC")) );
			$team_args = array("league_id" => $league->id, "season" => $season, "orderby" => array("id" => "ASC"));
			$teams = $leaguemanager->getTeams( $team_args, 'ARRAY' );
			$teams2 = $championship->getFinalTeams($final, 'ARRAY');

			$matches = array();
			for ( $i = 1; $i <= $final['num_matches']; $i++ ) {
				$match = $matches_raw[$i-1];
				
				if ( $match ) {
					$class = ( !isset($class) || 'alternate' == $class ) ? '' : 'alternate';
					$match->class = $class;
				
					if ( is_numeric($match->home_team) && is_numeric($match->away_team) ) {
						$match->title = $match->title2 = sprintf("%s &#8211; %s", $teams[$match->home_team]['title'], $teams[$match->away_team]['title']);
					} else {
						$match->title = sprintf("%s &#8211; %s", $teams2[$match->home_team], $teams2[$match->away_team]);
						$match->title2 = "&#8211;";
					}

					$match->hadPenalty = $match->hadPenalty = ( isset($match->penalty) && $match->penalty['home'] != '' && $match->penalty['away'] != '' ) ? true : false;
					$match->hadOvertime = $match->hadOvertime = ( isset($match->overtime) && $match->overtime['home'] != '' && $match->overtime['away'] != '' ) ? true : false;

					if ( $match->home_points != NULL && $match->away_points != NULL ) {
						if ( $match->hadPenalty )
							$match->score = sprintf("%s:%s", $match->penalty['home'], $match->penalty['away'])." ".__( 'o.P.', 'leaguemanager' );
						elseif ( $match->hadOvertime )
							$match->score = sprintf("%s:%s", $match->overtime['home'], $match->overtime['away'])." ".__( 'AET', 'leaguemanager' );
							//$match->score = sprintf("%s:%s", $match->home_points, $match->away_points);
						else
							$match->score = sprintf("%s:%s", $match->home_points, $match->away_points);
						if ( $final['key'] == 'final' ) {
							$data['isFinal'] = true;
							$data['field_id'] = ( $match->winner_id == $match->home_team ) ? "final_home" : "final_away";
						} else {
							$data['isFinal'] = false;
							}
					} else {
						$match->score = "-:-";
					}

					$match->date = ( substr($match->date, 0, 10) == '0000-00-00' ) ? 'N/A' : mysql2date(get_option('date_format'), $match->date);
					$match->time = ( '00:00' == $match->hour.":".$match->minutes ) ? 'N/A' : mysql2date(get_option('time_format'), $match->date);
					if ( empty($match->location) ) $match->location = 'N/A';

					$matches[$i] = $match;
				}
			}

			$data['matches'] = $matches;
			$finals[] = (object)$data;
		}

		if ( empty($template) && $this->checkTemplate('championship-'.$league->sport) )
			$filename = 'championship-'.$league->sport;
		else
			$filename = ( !empty($template) ) ? 'championship-'.$template : 'championship';

		$out = $this->loadTemplate( $filename, array('league' => $league, 'championship' => $championship, 'finals' => $finals) );

		return $out;
	}


	/**
	 * Function to display Team list
	 *
	 *	[teams league_id=ID template=X season=x]
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showTeams( $atts )
	{
		global $leaguemanager;
		extract(shortcode_atts(array(
			'league_id' => 0,
			'template' => '',
			'season' => false,
			'group' => false
		), $atts ));

		$league = $leaguemanager->getLeague(intval($league_id));
		if (empty($season)) {
			$season = $leaguemanager->getSeason($league);
			$season = $season['name'];
		}

		$team_args = array("league_id" => $league->id, "season" => $season);
		if ( $group ) $team_args["group"] = $group;
		$teams = $leaguemanager->getTeams( $team_args );

		if ( empty($template) && $this->checkTemplate('teams-'.$league->sport) )
			$filename = 'teams-'.$league->sport;
		else
			$filename = ( !empty($template) ) ? 'teams-'.$template : 'teams';

		$out = $this->loadTemplate( $filename, array('league' => $league, 'teams' => $teams) );

		return $out;
	}


	/**
	 * Function to display Team Info Page
	 *
	 *	[team id=ID template=X]
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showTeam( $atts )
	{
		global $leaguemanager;
		extract(shortcode_atts(array(
			'id' => 0,
			'template' => '',
			'echo' => 0,
		), $atts ));

		$team = $leaguemanager->getTeam( intval($id) );
		$league = $leaguemanager->getLeague( $team->league_id );

		// Get next match
		$next_matches = $leaguemanager->getMatches(array("team_id" => $team->id, "time" => "next"));
		$next_match = isset($next_matches[0]) ? $next_matches[0] : false;
		if ( $next_match ) {
			if ( $next_match->home_team == $team->id ) {
				$opponent = $leaguemanager->getTeam($next_match->away_team);
				$next_match->match = $team->title . " &#8211; " . $opponent->title;
			} else {
				$opponent = $leaguemanager->getTeam($next_match->home_team);
				$next_match->match = $opponent->title  . " &#8211; " . $team->title;
			}
		}

		// Get last match
		$prev_matches = $leaguemanager->getMatches(array("team_id" => $team->id, "time" => "prev", "limit" => 1, "orderby" => array("date" => "DESC")));
		$prev_match = $prev_matches[0];
		if ( $prev_match ) {
			if ( $prev_match->home_team == $team->id ) {
				$opponent = $leaguemanager->getTeam($prev_match->away_team);
				$prev_match->match = $team->title . " &#8211; " . $opponent->title;
			} else {
				$opponent = $leaguemanager->getTeam($prev_match->home_team);
				$prev_match->match = $opponent->title  . " &#8211; " . $team->title;
			}

			$prev_match->hadOvertime = ( isset($prev_match->overtime) && $prev_match->overtime['home'] != '' && $prev_match->overtime['away'] != '' ) ? true : false;
			$prev_match->hadPenalty = ( isset($prev_match->penalty) && $prev_match->penalty['home'] != '' && $prev_match->penalty['away'] != '' ) ? true : false;

			if ( $prev_match->hadPenalty )
				$prev_match->score = sprintf("%s - %s", $prev_match->penalty['home'], $prev_match->penalty['away'])." "._x( 'o.P.', 'leaguemanager' );
			elseif ( $prev_match->hadOvertime )
			//	$prev_match->score = sprintf("%s - %s", $prev_match->overtime['home'], $prev_match->overtime['away'])." "._x( 'AET|after extra time', 'leaguemanager' );
				$prev_match->score = sprintf("%s - %s", $prev_match->home_points, $prev_match->away_points);
			else
				$prev_match->score = sprintf("%s - %s", $prev_match->home_points, $prev_match->away_points);
		}


		if ( empty($template) && $this->checkTemplate('team-'.$league->sport) )
			$filename = 'team-'.$league->sport;
		else
			$filename = ( !empty($template) ) ? 'team-'.$template : 'team';

		$out = $this->loadTemplate( $filename, array('league' => $league, 'team' => $team, 'next_match' => $next_match, 'prev_match' => $prev_match) );

		if ( $echo )
			echo $out;
		else
			return $out;
	}


	/**
	 * Function to display Crosstable
	 *
	 * [crosstable league_id="1" mode="popup" template="name"]
	 *
	 * - league_id is the ID of league to display
	 * - league_name: get league by name and not ID (optional)
	 * - mode set to "popup" makes the crosstable be displayed in a thickbox popup window.
	 * - template is the template used for displaying. Replace name appropriately. Templates must be named "crosstable-template.php" (optional)
	 * - season: display crosstable of given season (optional)
	 *
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showCrosstable( $atts )
	{
		global $leaguemanager;
		extract(shortcode_atts(array(
			'league_id' => 0,
			'league_name' => '',
			'group' => '',
			'template' => '',
			'mode' => '',
			'season' => false
		), $atts ));

		$search = !empty($league_name) ? $league_name : intval($league_id);
		$league = $leaguemanager->getLeague( $search );
		if (empty($season)) {
			$season = $leaguemanager->getSeason($league);
			$season = $season['name'];
		}
		$team_args = array("league_id" => $league->id, "season" => $season, "group" => $group);
		$teams = $leaguemanager->getTeams( $team_args );

		if ( empty($template) && $this->checkTemplate('crosstable-'.$league->sport) )
			$filename = 'crosstable-'.$league->sport;
		else
			$filename = ( !empty($template) ) ? 'crosstable-'.$template : 'crosstable';

		$out = $this->loadTemplate( $filename, array('league' => $league, 'teams' => $teams, 'mode' => $mode) );

		return $out;
	}


	/**
	 * show Archive
	 *
	 *	[leaguearchive league_id=ID season=x template=X]
	 *
	 * - league_id: ID of league
	 * - league_name: get league by name and not ID (optional)
	 * - template: template to use
	 *
	 * @param array $atts
	 * @return the content
	 */
	function showArchive( $atts )
	{
		global $leaguemanager, $championship;
		extract(shortcode_atts(array(
			'league_id' => false,
			'league_name' => '',
			'template' => ''
		), $atts ));

		// get all leagues, needed for dropdown
		$leagues = $leaguemanager->getLeagues($offset=0, $limit=99999999);
		$league = false; // Initialize league variable

		// Get League by Name
		if (!empty($league_name)) {
			$league = $leaguemanager->getLeague( $league_name );
			$league_id = $league->id;
		}

		// Get League ID from shortcode or $_GET
		$league_id = ( !$league_id && isset($_GET['league_id']) && !empty($_GET['league_id']) ) ? (int)$_GET['league_id'] : false;

		if ( isset($_GET['season']) && !empty($_GET['season']) )
			$season = $_GET['season'];
		elseif ( isset($_GET['season_'.$league_id]) )
			$season = $_GET['season_'.$league_id];
		else
			$season = false;
		
		// select first league
		if ( !$league_id )
			$league_id = $leagues[0]->id;

		// Get League and first Season if not set
		if ( !$league ) $curr_league = $leaguemanager->getLeague( $league_id );
		if ( !$season ) {
			$season = reset($curr_league->seasons);
			$season = $season['name'];
		}

		$curr_league->season = $season;

		if ( $curr_league->mode == 'championship' ) $championship->initialize($curr_league->id);

		$seasons = array();
		foreach ( $leagues AS $l ) {
			foreach( (array)$l->seasons AS $l_season ) {
				if ( !in_array($l_season['name'], $seasons) && !empty($l_season['name']) )
					$seasons[] = $l_season['name'];
			}
		}
		sort($seasons);

		if ( empty($template) && $this->checkTemplate('archive-'.$curr_league->sport) )
			$filename = 'archive-'.$curr_league->sport;
		else
			$filename = ( !empty($template) ) ? 'archive-'.$template : 'archive';

		$out = $this->loadTemplate( $filename, array('leagues' => $leagues, 'seasons' => $seasons, 'curr_league' => $curr_league) );
		return $out;
	}


	/**
	 * get specific field for crosstable
	 *
	 * @param int $curr_team_id
	 * @param int $opponent_id
	 * @return string
	 */
	function getCrosstableField($curr_team_id, $opponent_id)
	{
		global $wpdb, $leaguemanager;

		$match = $leaguemanager->getMatches( array("home_team" => $curr_team_id, "away_team" => $opponent_id) );
		if ($match) $match = $match[0];

 		if ( $match ) {
			return $this->getScore($curr_team_id, $opponent_id, $match);
		} else {
			$match = $leaguemanager->getMatches( array("home_team" => $opponent_id, "away_team" => $curr_team_id) );
			if ($match) $match = $match[0];
			return $this->getScore($curr_team_id, $opponent_id, $match);

		}
	}


	/**
	 * get score for specific field of crosstable
	 *
	 * @param int $curr_team_id
	 * @param int $opponent_id
	 * @return string
	 */
	function getScore($curr_team_id, $opponent_id, $match)
	{
		global $wpdb, $leaguemanager;

		if ($match) {
			if ( !empty($match->penalty['home']) && !empty($match->penalty['away']) ) {
				$match->overtime = maybe_unserialize($match->overtime);
				$match->penalty = maybe_unserialize($match->penalty);
				$points = array( 'home' => $match->overtime['home']+$match->penalty['home'], 'away' => $match->overtime['away']+$match->penalty['away']);
			} elseif ( !empty($match->overtime['home']) && !empty($match->overtime['away']) ) {
				$match->overtime = maybe_unserialize($match->overtime);
				$points = array( 'home' => $match->overtime['home'], 'away' => $match->overtime['away']);
			} else {
				$points = array( 'home' => $match->home_points, 'away' => $match->away_points );
			}
		}
		
		// unplayed match
		if ( !$match || (NULL == $match->home_points && NULL == $match->away_points) )
			$out = "<td class='num'>-:-</td>";
		// match at home
		elseif ( $curr_team_id == $match->home_team )
			$out = "<td class='num'>".sprintf("%s:%s", $points['home'], $points['away'])."</td>";
		// match away
		elseif ( $opponent_id == $match->home_team )
			$out = "<td class='num'>".sprintf("%s:%s", $points['away'], $points['home'])."</td>";
		
		return $out;
	}


	/**
	 * Load template for user display. First the current theme directory is checked for a template
	 * before defaulting to the plugin
	 *
	 * @param string $template Name of the template file (without extension)
	 * @param array $vars Array of variables name=>value available to display code (optional)
	 * @return the content
	 */
	function loadTemplate( $template, $vars = array() )
	{
		global $leaguemanager, $lmStats, $championship;
		extract($vars);

		ob_start();

		if ( file_exists( get_stylesheet_directory() . "/leaguemanager/$template.php")) {
			include(get_stylesheet_directory() . "/leaguemanager/$template.php");
		} elseif ( file_exists( get_template_directory() . "/leaguemanager/$template.php")) {
			include(get_template_directory() . "/leaguemanager/$template.php");
		} elseif ( file_exists(LEAGUEMANAGER_PATH . "/templates/".$template.".php") ) {
			include(LEAGUEMANAGER_PATH . "/templates/".$template.".php");
		} else {
			parent::setMessage( sprintf(__('Could not load template %s.php', 'leaguemanager'), $template), true );
			parent::printMessage();
		}
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}


	/**
	 * check if template exists
	 *
	 * @param string $template
	 * @return boolean
	 */
	function checkTemplate( $template )
	{
		if ( file_exists( get_stylesheet_directory() . "/leaguemanager/$template.php")) {
			return true; //include(get_stylesheet_directory() . "/leaguemanager/$template.php");
		} elseif  ( file_exists( get_template_directory() . "/leaguemanager/$template.php")) {
			return true;
		} elseif ( file_exists(LEAGUEMANAGER_PATH . "/templates/".$template.".php") ) {
			return true;
		}

		return false;
	}
}

?>
