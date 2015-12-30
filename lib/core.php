<?php
/**
 * Core class for the WordPress plugin LeagueManager
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright Copyright 2008
*/
class LeagueManager
{
	/**
	 * array of leagues
	 *
	 * @var array
	 */
	var $leagues = array();
	

	/**
	 * data of certain league
	 *
	 * @var array
	 */
	var $league = array();


	/**
	 * ID of current league
	 *
	 * @var int
	 */
	var $league_id;

	
	/**
	 * current season
	 *
	 * @var mixed
	 */
	var $season;


	/**
	 * error handling
	 *
	 * @var boolean
	 */
	var $error = false;
	
	
	/**
	 * message
	 *
	 * @var string
	 */
	var $message;
	
	
	/**
	 * control variable if bridge is active
	 *
	 * @var boolean
	 */
	var $bridge = false;

	
	/**
	 * number of matches per page
	 *
	 * @var int
	 */
	var $num_matches_per_page = 0;
	
	
	/**
	 * number of pages for matches
	 *
	 * @var int
	 */
	var $num_max_pages = 0;
	

	/**
	 * Initializes plugin
	 *
	 * @param boolean $bridge
	 * @return void
	 */
	function __construct( $bridge = false )
	{
		$this->bridge = $bridge;
		if (isset($_GET['league_id'])) {
			$this->setLeagueID( intval($_GET['league_id']) );
			$this->league = $this->getLeague(intval($this->getLeagueID()));
		}

		$this->loadOptions();
	}
	function LeagueManager( $bridge = false )
	{
		$this->__construct( $bridge );
	}
	
	
	/**
	 * load options
	 *
	 * @param none
	 * @return void
	 */
	function loadOptions()
	{
		$this->options = get_option('leaguemanager');
	}
	
	
	/**
	 * get options
	 *
	 * @param none
	 * @return void
	 */
	function getOptions()
	{
		return $this->options;
	}
	
	
	/**
	 * check if bridge is active
	 *
	 * @param none
	 * @return boolean
	 */
	function hasBridge()
	{
		return $this->bridge;
	}
	
	
	/**
	 * set league id
	 *
	 * @param int $league_id
	 * @return void
	 */
	function setLeagueID( $league_id )
	{
		$this->league_id = intval($league_id);
	}
	
	
	/**
	 * retrieve league ID
	 *
	 * @param none
	 * @return int ID of current league
	 */
	function getLeagueID()
	{
		return intval($this->league_id);
	}
	

	/**
	 * get current league object
	 *
	 * @param none
	 * @return object
	 */
	function getCurrentLeague()
	{
		return $this->league;
	}
	

	/**
	 * get current league ID
	 *
	 * @param none
	 * @return int
	 */
	function getCurrentLeagueID()
	{
		return intval($this->league->id);
	}
	
	
	/**
	 * set season
	 *
	 * @param mixed $season
	 * @return void
	 */
	function setSeason( $season )
	{
		$this->season = $season;
	}
	
	
	/**
	 * get current season
	 *
	 * @param mixed $index
	 * @return array
	 */
	function getCurrentSeason( $index = false )
	{
		if ( $index )
			return $this->season[$index];

		return $this->season;
	}


	/**
	 * get league types
	 *
	 * @param none
	 * @return array
	 */
	function getLeagueTypes()
	{
		$types = array( 'other' => __('Other', 'leaguemanager') );
		$types = apply_filters('leaguemanager_sports', $types);
		asort($types);

		return $types;
	}
	
	
	/**
	 * get supported image types from Image class
	 *
	 * @param none
	 * @return array
	 */
	function getSupportedImageTypes()
	{
		return LeagueManagerImage::getSupportedImageTypes();
	}
	
	
	/**
	 * build home only query
	 *
	 * @param int $league_id
	 * @return string MySQL search query
	 */
	function buildHomeOnlyQuery($league_id)
	{
		global $wpdb;
		
		$queries = array();
		$teams = $wpdb->get_results($wpdb->prepare("SELECT `id` FROM {$wpdb->leaguemanager_teams} WHERE `league_id` = '%d' AND `home` = 1", intval($league_id)) );
		if ( $teams ) {
			foreach ( $teams AS $team )
				$queries[] = $wpdb->prepare("`home_team` = '%d' OR `away_team` = '%d'", $team->id, $team->id);
		
			$query = " AND (".implode(" OR ", $queries).")";
			
			return $query;
		}
		
		return false;
	}
	
	
	/**
	 * get months
	 *
	 * @param none
	 * @return void
	 */
	function getMonths()
	{
		$locale = get_locale();
		setlocale(LC_ALL, $locale);
		for ( $month = 1; $month <= 12; $month++ ) 
			$months[$month] = htmlentities( strftime( "%B", mktime( 0,0,0, $month, date("m"), date("Y") ) ) );
			
		return $months;
	}
	
	
	/**
	 * returns image directory
	 *
	 * @param string|false $file
	 * @return string
	 */
	function getImagePath( $file = false )
	{
		$league = $this->getCurrentLeague();
		if ( $file )
			return trailingslashit($_SERVER['DOCUMENT_ROOT']) . substr($file,strlen($_SERVER['HTTP_HOST'])+8, strlen($file));
		 else 
			return ABSPATH . $league->upload_dir;
	}
	
	
	/**
	 * returns url of image directory
	 *
	 * @param string|false $file image file
	 * @return string
	 */
	function getImageUrl( $file = false )
	{
		$league = $this->getCurrentLeague();
		if ( $file )
			return trailingslashit(get_option('siteurl')) . trailingslashit($league->upload_dir) . $file;
		else
			return trailingslashit(get_option('siteurl')) . $league->upload_dir;
	}

	
	/**
	 * get Thumbnail image
	 *
	 * @param string $file
	 * @return string
	 */
	function getThumbnailUrl( $file )
	{
		if ( file_exists($this->getThumbnailPath($file)) )
			return trailingslashit(dirname($file)) . 'thumb_' . basename($file);
		else
			return trailingslashit(dirname($file)) . 'thumb.' . basename($file);
	}

	
	/**
	 * get Thumbnail path
	 *
	 * @param string $file
	 * @return string
	 */
	function getThumbnailPath( $file )
	{
		return trailingslashit($_SERVER['DOCUMENT_ROOT']) . dirname(substr($file,strlen($_SERVER['HTTP_HOST'])+8, strlen($file))) . '/thumb_' . basename($file);
	}
	
	
	/**
	 * set message
	 *
	 * @param string $message
	 * @param boolean $error triggers error message if true
	 * @return none
	 */
	function setMessage( $message, $error = false )
	{
		$type = 'success';
		if ( $error ) {
			$this->error = true;
			$type = 'error';
		}
		$this->message[$type] = $message;
	}
	
	
	/**
	 * return message
	 *
	 * @param none
	 * @return string
	 */
	function getMessage()
	{
		if ( $this->error )
			return $this->message['error'];
		else
			return $this->message['success'];
	}
	
	
	/**
	 * print formatted message
	 *
	 * @param none
	 * @return string
	 */
	function printMessage()
	{
		if ( $this->error )
			echo "<div class='error'><p>".$this->getMessage()."</p></div>";
		else
			echo "<div id='message' class='updated fade'><p><strong>".$this->getMessage()."</strong></p></div>";
	}

	
	/**
	 * Set match day
	 *
	 * @param int 
	 * @return void
	 */
	function setMatchDay( $match_day )
	{
		$this->match_day = intval($match_day);
	}
	
	
	/**
	* retrieve match day
	 *
	 * @param none
	 * @return int
	 */
	function getMatchDay( $select = '' )
	{
		global $wpdb;
		
		$league_id = $this->getCurrentLeagueID();
		
		$season = isset($this->season['name']) ? $this->season['name'] : '';
		if ( isset($_GET['match_day']) ) {
			$match_day = intval($_GET['match_day']);
		} elseif ( isset($_GET['match_day_'.$league_id])) {
			$match_day = intval($_GET['match_day_'.$league_id]);
		} elseif ( isset($this->match_day) && $this->match_day != -1) {
			$match_day = intval($this->match_day);
		} elseif (isset($_POST['match_day'])) {
			$match_day = intval($_POST['match_day']);
		} elseif ( $select == "last" ) {
			$sql = "SELECT `match_day`, DATEDIFF(NOW(), `date`) AS datediff FROM {$wpdb->leaguemanager_matches} WHERE `league_id` = '%d' AND `season` = '%s' AND DATEDIFF(NOW(), `date`) > 0 ORDER BY datediff ASC";
			$matches = $wpdb->get_results( $wpdb->prepare($sql, $this->getLeagueID(), $season) );
			if ($matches[0]) $match_day = $matches[0]->match_day;
			else $match_day = -1;
		} elseif ( $select == "next" ) {
			$sql = "SELECT `match_day`, DATEDIFF(NOW(), `date`) AS datediff FROM {$wpdb->leaguemanager_matches} WHERE `league_id` = '%d' AND `season` = '%s' AND DATEDIFF(NOW(), `date`) < 0 ORDER BY datediff DESC";
			$matches = $wpdb->get_results( $wpdb->prepare($sql, $this->getLeagueID(), $season) );
			if ($matches[0]) $match_day = $matches[0]->match_day;
			else $match_day = -1;
		} elseif ( $select == "current" || $select == "latest") {
			$sql = "SELECT `id`, `match_day`, DATEDIFF(NOW(), `date`) AS datediff FROM {$wpdb->leaguemanager_matches} WHERE `league_id` = '%d' AND `season` = '%s' ORDER BY datediff ASC";
			$matches = $wpdb->get_results( $wpdb->prepare($sql, $this->getLeagueID(), $season) );
			if ($matches) {
				$datediff = array();
				foreach ($matches AS $key => $match) {
					$datediff[$key] = abs($match->datediff);
				}
				asort($datediff);
				$keys = array_keys($datediff);
				$match_day = $matches[$keys[0]]->match_day;
			} else {
				$match_day = -1;
			}
		} else {
			$match_day = -1;
		}
		
		$this->setMatchDay($match_day);
		
		return $match_day;
	}
	
	
	/**
	 * get current season
	 *
	 * @param object $league
	 * @param mixed $season
	 * @return array
	 */
	function getSeason( $league, $season = false, $index = false )
	{
		if ( isset($_GET['season']) && !empty($_GET['season']) )
			$data = $league->seasons[htmlspecialchars($_GET['season'])];
		elseif ( isset($_GET['season_'.$league->id]) )
			$data = $league->seasons[htmlspecialchars($_GET['season_'.$league->id])];
		elseif ( $season )
			$data = $league->seasons[$season];
		elseif ( !empty($league->seasons) )
			$data = end($league->seasons);
		else
			return false;

		if ( $index )
			return $data[$index];
		else
			return $data;
	}


	/**
	 * get leagues from database
	 *
	 * @param int $league_id (default: false)
	 * @param string $search
	 * @return array
	 */
	function getLeagues( $offset=0, $limit=99999999 )
	{
		global $wpdb;
		
		$leagues = $wpdb->get_results($wpdb->prepare( "SELECT `title`, `id`, `settings`, `seasons` FROM {$wpdb->leaguemanager} ORDER BY id ASC LIMIT %d, %d", intval($offset), intval($limit) ));
		$i = 0;
		foreach ( $leagues AS $league ) {
			$leagues[$i]->title = stripslashes($league->title);
			$leagues[$i]->seasons = $league->seasons = maybe_unserialize($league->seasons);
			$league->settings = maybe_unserialize($league->settings);

			$leagues[$i] = (object)array_merge((array)$league,(array)$league->settings);
			unset($leagues[$i]->settings, $league->settings);

			$this->leagues[$league->id] = $leagues[$i];
			$i++;
		}
		return $leagues;
	}
	
	
	/**
	 * get league
	 *
	 * @param mixed $league_id either ID of League or title
	 * @return league object
	 */
	function getLeague( $league_id )
	{
		global $wpdb;
		
		$league = $wpdb->get_results($wpdb->prepare("SELECT `title`, `id`, `seasons`, `settings` FROM {$wpdb->leaguemanager} WHERE `id` = '%d' OR `title` = '%s'", intval($league_id), $league_id) );
		$league[] = new stdClass();
		$league = $league[0];
		$league->title = stripslashes($league->title);
		$league->seasons = maybe_unserialize($league->seasons);
		$league->settings = (array)maybe_unserialize($league->settings);

		$this->league_id = $league->id;
		$league->hasBridge = $this->hasBridge();

		$league = (object)array_merge((array)$league,(array)$league->settings);
		unset($league->settings);

		$league = $this->getDefaultLeagueSettings( $league );
		
		if (isset($league->num_matches_per_page) && $league->num_matches_per_page > 0)
			$this->setNumMatchesPerPage($league->num_matches_per_page);
		
		$this->league = $league;
		return $league;
	}
	
	
	/**
	 * get default league settings
	 *
	 * @param object $league
	 * @return object
	 */
	function getDefaultLeagueSettings( $league )
	{
		if ( !isset($league->sport) ) $league->sport = "soccer";
		if ( !isset($league->point_rule) ) $league->point_rule = "three";
		if ( !isset($league->point_format) ) $league->point_format = "%d:%d";
		if ( !isset($league->point_format2) ) $league->point_format2 = "%d:%d";
		if ( !isset($league->team_ranking) ) $league->team_ranking = "auto";
		if ( !isset($league->mode) ) $league->mode = "default";
		if ( !isset($league->default_match_start_time) ) $league->default_match_start_time = array("hour" => 0, "minutes" => 0);
		if ( !isset($league->standings) ) $league->standings = array( 'pld' => 1, 'won' => 1, 'tie' => 1, 'lost' => 1 );
		if ( !isset($league->num_ascend) ) $league->num_ascend = "";
		if ( !isset($league->num_descend) ) $league->num_descend = "";
		if ( !isset($league->num_relegation) ) $league->num_relegation = "";
		if ( !isset($league->num_matches_per_page) ) $league->num_matches_per_page = 10;
		if ( !isset($league->use_stats) ) $league->use_stats = 0;
		
		return $league;
		}
	
	
	/**
	 * get teams from database
	 *
	 * @param array $args
	 * @param string $output OBJECT | ARRAY
	 * @return array database results
	 */
	function getTeams( $args = array(), $output = 'OBJECT' )
	{
		global $wpdb;
		
		$defaults = array( 'league_id' => false, 'season' => false, 'group' => false, 'rank' => false, 'orderby' => array("rank" => "ASC", "id" => "ASC"));
		$args = array_merge($defaults, $args);
		extract($args, EXTR_SKIP);
		
		$search_terms = array();
		if ($league_id) {
			if ($league_id == "any")
				$search_terms[] = "`league_id` != ''";
			else
				$search_terms[] = $wpdb->prepare("`league_id` = '%d'", intval($league_id));
		}
		if ($season) {
			if ($season == "any") 
				$search_terms[] = "`season` != ''";
			elseif ($this->seasonExists($league_id, htmlspecialchars($season)))
				$search_terms[] = $wpdb->prepare("`season` = '%s'", htmlspecialchars($season));
		}
		if ($group && $this->groupExists($league_id, htmlspecialchars($group))) $search_terms[] = $wpdb->prepare("`group` = '%s'", htmlspecialchars($group));
		if ($rank) $search_terms[] = $wpdb->prepare("`rank` = '%s'", $rank);
		
		$search = "";
		if (count($search_terms) > 0) {
			$search = " WHERE ";
			$search .= implode(" AND ", $search_terms);
		}
		
		$orderby_string = ""; $i = 0;
		foreach ($orderby AS $order => $direction) {
			if (!in_array($direction, array("DESC", "ASC", "desc", "asc"))) $direction = "ASC";
			if ($this->databaseColumnExists("teams", $order)) {
				$orderby_string .= "`".$order."` ".$direction;
				if ($i < (count($orderby)-1)) $orderby_string .= ",";
			}
			$i++;
		}
		$orderby = $orderby_string;
		
		$teamlist = $wpdb->get_results( "SELECT `title`, `website`, `coach`, `stadium`, `logo`, `home`, `group`, `roster`, `points_plus`, `points_minus`, `points2_plus`, `points2_minus`, `add_points`, `done_matches`, `won_matches`, `draw_matches`, `lost_matches`, `diff`, `league_id`, `id`, `season`, `rank`, `status`, `custom` FROM {$wpdb->leaguemanager_teams} $search ORDER BY $orderby" );
		$teams = array(); $i = 0;
		foreach ( $teamlist AS $team ) {
			$team->custom = stripslashes_deep(maybe_unserialize($team->custom));
			if ( 'ARRAY' == $output ) {
				$teams[$team->id]['title'] = htmlspecialchars(stripslashes($team->title), ENT_QUOTES);
				$teams[$team->id]['rank'] = $team->rank;
				$teams[$team->id]['status'] = $team->status;
				$teams[$team->id]['season'] = $team->season;
				$teams[$team->id]['website'] = $team->website;
				$teams[$team->id]['coach'] = stripslashes($team->coach);
				$teams[$team->id]['stadium'] = stripslashes($team->stadium);
				$teams[$team->id]['logo'] = $team->logo;
				$teams[$team->id]['home'] = $team->home;
				$teams[$team->id]['group'] = $team->group;
				$teams[$team->id]['roster'] = maybe_unserialize($team->roster);
				if ( $this->hasBridge() ) {
					global $lmBridge;
					$teams[$team->id]['teamRoster'] = $lmBridge->getTeamRoster(maybe_unserialize($team->roster));
				}
				$teams[$team->id]['points'] = array( 'plus' => $team->points_plus, 'minus' => $team->points_minus );
				$teams[$team->id]['points2'] = array( 'plus' => $team->points2_plus, 'minus' => $team->points2_minus );
				$teams[$team->id]['add_points'] = $team->add_points;
				foreach ( (array)$team->custom AS $key => $value )
					$teams[$team->id][$key] = stripslashes_deep($value);
			} else {
				$teamlist[$i]->roster = maybe_unserialize($team->roster);
				if ( $this->hasBridge() ) {
					global $lmBridge;
					$teamlist[$i]->teamRoster = $lmBridge->getTeamRoster(maybe_unserialize($team->roster));
				}
				$teamlist[$i]->title = htmlspecialchars(stripslashes($team->title), ENT_QUOTES);
				$teamlist[$i]->coach = stripslashes($team->coach);
				$teamlist[$i]->stadium = stripslashes($team->stadium);
				$teamlist[$i] = (object)array_merge((array)$team, (array)$team->custom);
			}

			unset($teamlist[$i]->custom, $team->custom);
			$i++;
		}

		if ( 'ARRAY' == $output )
			return $teams;

		return $teamlist;
	}
	
	
	/**
	 * get single team
	 *
	 * @param int $team_id
	 * @return object
	 */
	function getTeam( $team_id )
	{
		global $wpdb;

		$team = $wpdb->get_results( $wpdb->prepare("SELECT `title`, `website`, `coach`, `stadium`, `logo`, `home`, `group`, `roster`, `points_plus`, `points_minus`, `points2_plus`, `points2_minus`, `add_points`, `done_matches`, `won_matches`, `draw_matches`, `lost_matches`, `diff`, `league_id`, `id`, `season`, `rank`, `status`, `custom` FROM {$wpdb->leaguemanager_teams} WHERE `id` = '%d' ORDER BY `rank` ASC, `id` ASC", intval($team_id)) );
		
		if (!isset($team[0])) return false;
		
		$team = $team[0];

		$team->title = htmlspecialchars(stripslashes($team->title), ENT_QUOTES);
		$team->coach = stripslashes($team->coach);
		$team->stadium = stripslashes($team->stadium);
		$team->custom = stripslashes_deep(maybe_unserialize($team->custom));
		$team->roster = maybe_unserialize($team->roster);
		if ( $this->hasBridge() ) {
			global $lmBridge;
			$team->teamRoster = $lmBridge->getTeamRoster($team->roster);
		}

		$team = (object)array_merge((array)$team,(array)$team->custom);
		unset($team->custom);
		
		return $team;
	}
	
	
	/**
	 * get number of seasons
	 *
	 * @param array $seasons
	 * @return int
	 */
	function getNumSeasons( $seasons )
	{
		if (empty($seasons))
			return 0;
		else
			return count($seasons);
	}


	/**
	 * gets number of teams for specific league
	 *
	 * @param int $league_id
	 * @param string $group
	 * @return int
	 */
	function getNumTeams( $league_id, $group = '' )
	{
		global $wpdb;
		$league_id = intval($league_id);
		if ($group == ''){
			$num_teams = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->leaguemanager_teams} WHERE `league_id` = '%d'", $league_id) );
		} else {
			$num_teams = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->leaguemanager_teams} WHERE `league_id` = '%d' AND `group` = '%s'", $league_id, $group) );
		}
		return $num_teams;
	}
	
		
	/**
	 * check if any team has a team roster
	 *
	 * @param array $teams
	 * @return boolean
	 */
	function hasTeamRoster($teams)
	{
		foreach ($teams AS $team) {
			if (!empty($team->teamRoster)) return true;
		}
		return false;
	}
	
	
	/**
	 * gets number of matches
	 *
	 * @param string $search
	 * @return int
	 */
	function getNumMatches( $league_id )
	{
		global $wpdb;
	
		$num_matches = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `league_id` = '%d'", $league_id) );
		return $num_matches;
	}
	
	
	/**
	 * get number of matches saved in class
	 *
	 * @param none
	 * @return int
	 */
	function getNumMatchesQuery()
	{
		return intval($this->num_matches);
	}
	
	
	/**
	 * set number of matches
	 *
	 * @param int $num_matches
	 */
	function setNumMatches( $num_matches )
	{
		$this->num_matches = intval($num_matches);
	}
	
	
	/**
	 * rank teams
	 *
	 * The Team Ranking can be altered by sport specific rules via the hook <em>rank_teams_`sport_type`</em>
	 * `sport_type` needs to be the key of current sport type. Below is an example how it could be used
	 *
	 * add_filter('rank_teams_soccer', 'soccer_ranking');
	 *
	 * function soccer_ranking( $teams ) {
	 *	// do some stuff
	 *	return $teams
	 * }
	 *
	 *
	 * @param int $league_id
	 * @param mixed $season
	 * @param boolean $update
	 * @return array $teams ordered
	 */
	function rankTeams( $league_id )
	{
		global $wpdb;
		$league = $this->getLeague( $league_id );
                                    
		if ( !isset($season) )
			$season = $this->getSeason($league);

		$season = is_array($season) ? $season['name'] : $season;

		// rank Teams in groups
		$groups = !empty($league->groups) ? explode(";", $league->groups) : array( '' );

		foreach ( $groups AS $group ) {
			$team_args = array("league_id" => $league_id, "season" => $season);
			if ( !empty($group) ) $team_args["group"] = $group;

			$teams = $teamsTmp = array();
			foreach ( $this->getTeams( $team_args ) AS $team ) {
				$team->diff = ( $team->diff > 0 ) ? '+'.$team->diff : $team->diff;
				$team->points = array( 'plus' => $team->points_plus, 'minus' => $team->points_minus );
				$team->points2 = array( 'plus' => $team->points2_plus, 'minus' => $team->points2_minus );
				$team->winPercent = ($team->done_matches > 0) ? ($team->won_matches/$team->done_matches) * 100 : 0;

				$teams[] = $team;
				$teamsTmp[] = $team;
			}
		
			if ( !empty($teams) && $league->team_ranking == 'auto' ) {
				if ( has_filter( 'rank_teams_'.$league->sport ) ) {
					$teams = apply_filters( 'rank_teams_'.$league->sport, $teams );
				} else {
					foreach ( $teams AS $key => $row ) {
						$points[$key] = $row->points['plus'] + $row->add_points;
						$done[$key] = $row->done_matches;
					}
			
					array_multisort($points, SORT_DESC, $done, SORT_ASC, $teams);
				}
			}
            updateRanking( $league_id, $season, $group, $teams, $teamsTmp );
		}

		return true;
	}
	

	/**
	 * determine if two teams are tied
	 *
	 * @param object $team
	 * @param object $team2
	 * @return boolean
	 */
	function isTie( $team, $team2 )
	{
//    echo "Check for tie here1 <br>";
		if ( $team->points['plus'] == $team2->points['plus'] && $team->diff == $team2->diff && $team->points2['plus'] == $team2->points2['plus'] )
			return true;

		return false;
	}

	
	/**
	 * gets matches from database
	 * 
	 * @param array $args
	 * @param string $output (optional)
	 * @return array
	 */
	function getMatches( $args, $output = 'OBJECT' )
	{
	 	global $wpdb;
	
		$defaults = array( 'league_id' => false, 'count' => false, 'season' => false, 'group' => false, 'final' => false, 'match_day' => false, 'time' => false, 'home_only' => false, 'winner_id' => false, 'loser_id' => false, 'team_id' => false, 'home_team' => false, 'away_team' => false, 'home_points' => false, 'away_points' => false, 'limit' => true, 'orderby' => array("date" => "ASC"));
		$args = array_merge($defaults, (array)$args);
		extract($args, EXTR_SKIP);
		
		$league = $this->getCurrentLeague();
		// disable limit for championship mode
		if ( isset($league->mode) && $league->mode == "championship" ) $limit = false;
		
		$search_terms = array();
		if ($league_id) {
			if ($league_id == "any")
				$search_terms[] = "`league_id` != ''";
			else
				$search_terms[] = $wpdb->prepare("`league_id` = '%d'", intval($league_id));
		}
		if ($season) {
			if ($season == "any") 
				$search_terms[] = "`season` != ''";
			elseif ($this->seasonExists($league_id, htmlspecialchars($season)))
				$search_terms[] = $wpdb->prepare("`season` = '%s'", htmlspecialchars($season));
		}
		
		if ($final != false && $this->finalExists(htmlspecialchars($final))) $search_terms[] = $wpdb->prepare("`final` = '%s'", htmlspecialchars($final));
		if ($group != false && $this->groupExists($league_id, htmlspecialchars($group))) $search_terms[] = $wpdb->prepare("`group` = '%s'", htmlspecialchars($group));
		if ($team_id) {
			$search_terms[] = $wpdb->prepare("(`home_team` = '%d' OR `away_team` = '%d')", $team_id, $team_id);
		} else {
			if ($home_team) $search_terms[] = $wpdb->prepare("`home_team` = '%s'", $home_team);
			if ($away_team) $search_terms[] = $wpdb->prepare("`away_team` = '%s'", $away_team);
		}
		if ($match_day) $search_terms[] = $wpdb->prepare("`match_day` = '%d'", $match_day);
		if ($home_points) {
			if ($home_points == "null")
				$search_terms[] = "`home_points` IS NULL";
			elseif ($home_points == "not_null")
				$search_terms[] = "`home_points` IS NOT NULL";
		}
		if ($away_points) {
			if ($away_points == "null")
				$search_terms[] = "`away_points` IS NULL";
			elseif ($away_points == "not_null")
				$search_terms[] = "`away_points` IS NOT NULL";
		}
		if ($winner_id)
			$search_terms[] = $wpdb->prepare("`winner_id` = '%d'", $winner_id);
		if ($loser_id)
			$search_terms[] = $wpdb->prepare("`loser_id` = '%d'", $loser_id);
		
		if ( $time == 'next' )
			$search_terms[] = "TIMESTAMPDIFF(MINUTE, NOW(), `date`) >= 0";
		elseif ( $time == 'prev' || $time == 'latest' )
			$search_terms[] = "TIMESTAMPDIFF(MINUTE, NOW(), `date`) < 0";
		elseif ( $time == 'prev1' )
			$search_terms[] = "TIMESTAMPDIFF(MINUTE, NOW(), `date`) < 0) AND (`winner_id` != 0) ";
		elseif ( $time == 'today' )
			$search_terms[] = "DATEDIFF(NOW(), `date`) = 0";
		elseif ( $time == 'day' )
			$search_terms[] = "DATEDIFF('". htmlspecialchars($match_date)."', `date`) = 0";
		
		$search = "";
		if (count($search_terms) > 0) {
			$search = implode(" AND ", $search_terms);
		}
		
		if ($home_only) {
			$search .= $this->buildHomeOnlyQuery($league_id);
		}
			
		// Force ordering by date ascending if next matches are queried
		if ( $time == 'next' ) {
			$orderby['date'] = 'ASC';
		}
		// Force ordering by date descending if previous/latest matches are queried
		if ( $time == 'prev' || $time == 'latest' ) {
			$orderby['date'] = 'DESC';
		}
		
		$orderby_string = ""; $i = 0;
		foreach ($orderby AS $order => $direction) {
			if (!in_array($direction, array("DESC", "ASC", "desc", "asc"))) $direction = "ASC";
			if ($this->databaseColumnExists("matches", $order)) {
				$orderby_string .= "`".$order."` ".$direction;
				if ($i < (count($orderby)-1)) $orderby_string .= ",";
			}
			$i++;
		}
		$order = $orderby_string;
		
		$num_matches_per_page = ( $limit && is_numeric($limit) ) ? intval($limit) : $this->getNumMatchesPerPage();
		
		if ( $limit === true )
			$offset = ( $this->getCurrentPage() - 1 ) * $this->getNumMatchesPerPage();
		else 
			$offset = 0;
		
		if ( $count ) {
			$sql = "SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches}";
			if ( $search != "") $sql .= " WHERE $search";
			$sql .= " ORDER BY $order";
			//if ( $limit ) $sql .= " LIMIT ".intval($offset).",".intval($limit)."";
			return $wpdb->get_var($sql);
		} else {
			$sql = "SELECT `group`, `home_team`, `away_team`, DATE_FORMAT(`date`, '%Y-%m-%d %H:%i') AS date, DATE_FORMAT(`date`, '%e') AS day, DATE_FORMAT(`date`, '%c') AS month, DATE_FORMAT(`date`, '%Y') AS year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `match_day`, `location`, `league_id`, `home_points`, `away_points`, `winner_id`, `loser_id`, `post_id`, `season`, `id`, `custom` FROM {$wpdb->leaguemanager_matches}";
			if ( $search != "") $sql .= " WHERE $search";
			$sql .= " ORDER BY $order";
			if ( $limit && intval($num_matches_per_page) > 0 ) $sql .= " LIMIT ".$offset.",".intval($num_matches_per_page)."";
	
			$matches = $wpdb->get_results( $sql, $output );
		}

		$i = 0;
		foreach ( $matches AS $match ) {
			$matches[$i]->location = stripslashes($match->location);
			$matches[$i]->custom = $match->custom = stripslashes_deep(maybe_unserialize($match->custom));
			$matches[$i] = (object)array_merge((array)$match, (array)$match->custom);
		//	unset($matches[$i]->custom);

			$i++;
		}
		return $matches;
	}
	
	
	/**
	 * get single match
	 *
	 * @param int $match_id
	 * @return object
	 */
	function getMatch( $match_id )
	{
		global $wpdb;

		$match = $wpdb->get_results("SELECT `group`, `home_team`, `away_team`, DATE_FORMAT(`date`, '%Y-%m-%d %H:%i') AS date, DATE_FORMAT(`date`, '%e') AS day, DATE_FORMAT(`date`, '%c') AS month, DATE_FORMAT(`date`, '%Y') AS year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `match_day`, `location`, `league_id`, `home_points`, `away_points`, `winner_id`, `loser_id`, `post_id`, `season`, `id`, `custom` FROM {$wpdb->leaguemanager_matches} WHERE `id` = '".intval($match_id)."'");
		$match = $match[0];

		$match->location = stripslashes($match->location);
		$match->custom = stripslashes_deep(maybe_unserialize($match->custom));
		$match = (object)array_merge((array)$match, (array)$match->custom);
		//unset($match->custom);

		return $match;
	}
	
	
	/**
	 * get match title
	 *
	 * @param int $match_id
	 * @param boolean show_logo
	 *
	 */
	function getMatchTitle( $match_id, $show_logo = true)
	{
		$match = $this->getMatch($match_id);
		$league = $this->getLeague($match->league_id);
		$teams = $this->getTeams( array("league_id" => $match->league_id, "season" => $match->season), 'ARRAY');

		if (!isset($teams[$match->home_team]) || !isset($teams[$match->away_team]) || $match->home_team == $match->away_team) {
			if (isset($match->title))
				$title = stripslashes($match->title);
			else
				$title = "";
		} else {
			$home_logo_img = ($teams[$match->home_team]['logo'] != "" && $show_logo) ? "<img class='home-logo logo' src='".$this->getThumbnailUrl($teams[$match->home_team]['logo'])."' alt='' />" : "";
			$away_logo_img = ($teams[$match->away_team]['logo'] != "" && $show_logo) ? "<img class='away-logo logo' src='".$this->getThumbnailUrl($teams[$match->away_team]['logo'])."' alt='' />" : "";
			$home_team_name = ($this->isHomeTeamMatch($match->home_team, $match->away_team, $teams)) ? "<strong>".$teams[$match->home_team]['title']."</strong>" : $teams[$match->home_team]['title']; 
			$away_team_name = ($this->isHomeTeamMatch($match->home_team, $match->away_team, $teams)) ? "<strong>".$teams[$match->away_team]['title']."</strong>" : $teams[$match->away_team]['title']; 
		
			$title = sprintf("%s %s &#8211; %s %s", $home_team_name, $home_logo_img, $away_logo_img, $away_team_name);
			$title = apply_filters( 'leaguemanager_matchtitle_'.$league->sport, $title, $match, $teams );
		}
		
		return $title;
	}
	
	
	/**
	 * test if it's a match of home team
	 *
	 * @param int $home_team
	 * @param int $away_team
	 * @param array $teams
	 * @return boolean
	 */
	function isHomeTeamMatch( $home_team, $away_team, $teams )
	{
		if ( isset($teams[$home_team]) && 1 == $teams[$home_team]['home'] )
			return true;
		elseif ( isset($teams[$away_team]) && 1 == $teams[$away_team]['home'] )
			return true;
		else
			return false;
	}


	/**
	 * display pagination
	 *
	 * @param int $current_page
	 * @param string $base
	 * @return string
	 */
	function getPageLinks($current_page = false, $base = 'paged')
	{
		if (!$current_page) $current_page = $this->getCurrentPage();
		
		if (is_admin()) $query_args = array('league_id' => $this->getLeagueID());
		else $query_args = (isset($this->query_args)) ? $this->query_args : array();
		
		if (isset($_POST['match_day']) && is_string($_POST['match_day'])) {
			$query_args['match_day'] = htmlspecialchars($_POST['match_day']);
		}
		
		$page_links = paginate_links( array(
			'base' => add_query_arg( $base, '%#%' ),
			'format' => '',
			'prev_text' => '&#9668;',
			'next_text' => '&#9658;',
			'total' => $this->getNumPages(),
			'current' => $current_page,
			'add_args' => $query_args
		));
			
		return $page_links;
	}
	
	
	/**
	 * retrieve current page
	 *
	 * @param none
	 * @return int
	 */
	function getCurrentPage($league_id = false)
	{
		global $wp;
		
		if (!$league_id) $league_id = $this->getLeagueID();
		
		$key = "paged_".$league_id;
		if (isset($_GET['paged']))
			$this->current_page = intval($_GET['paged']);
		elseif (isset($wp->query_vars['paged']))
			$this->current_page = max(1, intval($wp->query_vars['paged']));
		elseif (isset($_GET[$key]))
			$this->current_page = intval($_GET[$key]);
		elseif (isset($wp->query_vars[$key]))
			$this->current_page = max(1, intval($wp->query_vars[$key]));
		else
			$this->current_page = 1;

		return intval($this->current_page);
	}
	
	
	/**
	 * get number of matches per page
	 *
	 * @param none
	 * @return int
	 */
	function getNumMatchesPerPage()
	{
		//return intval($this->league->num_matches_per_page);
		return $this->num_matches_per_page;
	}
	
	
	/**
	 * set number of matches per page
	 *
	 * @param int $num
	 * @return void
	 */
	function setNumMatchesPerPage( $num )
	{
		$this->num_matches_per_page = $num;
	}
	
	
	/**
	 * set number of pages
	 *
	 * @param int $num_max_pages
	 * @return none
	 */
	function getNumPages()
	{
		$this->num_max_pages = ( 0 == $this->getNumMatchesPerPage() ) ? 1 : ceil( $this->getNumMatchesQuery()/$this->getNumMatchesPerPage() );
		return $this->num_max_pages;
	}
	
	
	/**
	 * get card name
	 *
	 * @param string $type
	 * @return nice card name
	 */
	function getCards( $type = false )
	{
		$cards = array( 'red' => __( 'Red', 'leaguemanager' ), 'yellow' => __( 'Yellow', 'leaguemanager' ), 'yellow-red' => __( 'Yellow/Red', 'leaguemanager' ) );
		$cards = apply_filters( 'leaguemanager_cards', $cards );

		if ( $type )
			return $cards[$type];
		else
			return $cards;
	}
	
	function lm_pagination($paged, $pages = '', $range = 4) {
		$showitems = ($range * 2)+1;
	
		if(empty($paged)) $paged = 1;
		if($pages == '') {
			global $wp_query;
			$pages = $wp_query->max_num_pages;
			if(!$pages) {
				$pages = 1;
			}
		}

		$div_output = '';
		if (1 != $pages) {
			$div_output .= "<ul class='pagination'>";
			if($paged > 2 && $paged > $range+1 && $showitems < $pages) $div_output .= "<li><a href='".get_pagenum_link(1)."' class='first_page'>&laquo; First</a></li>";
			if($paged > 1) $div_output .= "<li><a href='".get_pagenum_link($paged - 1)."' class='prev_page'>&lsaquo; Previous</a></li>";
				
			for ($i=1; $i <= $pages; $i++) {
				if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )) {
					$div_output .= ($paged == $i)? "<li class='active'><a href=''>".$i."</a></li>":"<li><a href='".get_pagenum_link($i)."' class='inactive'>".$i."</a></li>";
				}
			}
	
			if ($paged < $pages) $div_output .= "<li><a href='".get_pagenum_link($paged + 1)."' class='next_page'>Next &rsaquo;</a></li>";
			if ($paged < $pages-1 && $paged+$range-1 < $pages && $showitems < $pages) $div_output .= "<li><a href='".get_pagenum_link($pages)."' class='last_page'>Last &raquo;</a></li>";
			$div_output .= "</ul>\n";
		}
		return $div_output;
	}
	
	
	/**
	 * some security checks to prevent SQL injections
	 *
	 */
	function seasonExists($league_id, $season)
	{
		$league = $this->getLeague($league_id);
		if (is_array($league->seasons) && in_array($season, array_keys($league->seasons)))
			return true;
		else
			return false;
	}
	
	function groupExists($league_id, $group)
	{
		$league = $this->getLeague($league_id);
		if (isset($league->groups)) {
			$groups = explode(";", $league->groups);
			if (in_array($group, $groups))
				return true;
		}
		return false;
	}
	
	function finalExists($final)
	{
		global $championship;
		$finals = $championship->getFinals();
		if (in_array($final, array_keys($finals)))
			return true;
		else
			return false;
	}
	
	function databaseColumnExists($table, $column)
	{
		global $wpdb;
		
		if ($table == "teams")
			$table = $wpdb->leaguemanager_teams;
		elseif ($table == "matches")
			$table = $wpdb->leaguemanager_matches;
		else
			return false;
		
		$num = $wpdb->query( $wpdb->prepare("SHOW COLUMNS FROM {$table} LIKE %s", $column) );
		if ($num == 1)
			return true;
		else
			return false;
	}
	
	
	/**
	 * small helper function to correct 0:0 in overtime fields to empty values
	 */
	function cleanOvertime()
	{
		global $wpdb;
		
		// get all matches from database
		$matches = $wpdb->get_results( "SELECT * FROM {$wpdb->leaguemanager_matches} ORDER By id ASC" );
		foreach ( $matches AS $match ) {
			$match->custom = stripslashes_deep(maybe_unserialize($match->custom));
			
			if ( count($match->custom) && $match->custom['overtime']['home'] == "0" && $match->custom['overtime']['away'] == "0" ) {
				$match->custom['overtime'] = array('home' => '', 'away' => '');
			}
			$wpdb->query($wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `custom` = '%s' WHERE `id` = '%d'", maybe_serialize($match->custom), $match->id ));
		}
	}
}
?>