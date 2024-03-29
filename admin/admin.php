<?php
/**
* Admin class holding all administrative functions for the WordPress plugin LeagueManager
*
* @author 	Kolja Schleich
* @package	LeagueManager
* @copyright Copyright 2014
*/

class LeagueManagerAdminPanel extends LeagueManager
{
	/**
	 * load admin area
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
        require_once( ABSPATH . 'wp-admin/includes/template.php' );

        // Get options
        $options = get_option('leaguemanager');

		add_action('admin_enqueue_scripts', array(&$this, 'loadScripts') );
		add_action('admin_enqueue_scripts', array(&$this, 'loadStyles') );
        if(!isset($options['hide_admin_widgets']) || !$options['hide_admin_widgets']) {
            add_action('wp_dashboard_setup', array( $this, 'register_admin_widgets'));
        }
		add_action( 'admin_menu', array(&$this, 'menu') );

		// Add meta box to post screen

		add_action( 'publish_post', array(&$this, 'editMatchReport') );
		add_action( 'edit_post', array(&$this, 'editMatchReport') );
		add_action('add_meta_boxes', array(&$this, 'metaboxes'));
		add_action('wp_ajax_leaguemanager_get_season_dropdown', array(&$this, 'getSeasonDropdown'));
		add_action('wp_ajax_leaguemanager_get_match_dropdown', array(&$this, 'getMatchDropdown'));
	}
	function LeagueManagerAdminPanel()
	{
		$this->__construct();
	}


	/**
	 * adds menu to the admin interface
	 *
	 * @param none
	 */
	function menu()
	{
		$plugin = 'leaguemanager/leaguemanager.php';

		/*if ( function_exists('add_object_page') )
			$page = add_object_page( 
				__('LeagueManager','leaguemanager'),
				__('LeagueManager','leaguemanager'),
				'league_manager',
				'leaguemanager',
				array(&$this, 'display'),
				LEAGUEMANAGER_URL.'/admin/icons/cup.png' );
		else*/
			$page = add_menu_page(
                __('LeagueManager','leaguemanager'),
                __('LeagueManager','leaguemanager'),
                'league_manager',
                'leaguemanager',
                array(&$this, 'display'),
                LEAGUEMANAGER_URL.'/admin/icons/cup.png'
			);

		add_submenu_page('leaguemanager', __('LeagueManager', 'leaguemanager'), __('Overview','leaguemanager'),'league_manager', 'leaguemanager', array(&$this, 'display'));
		$page = add_submenu_page('leaguemanager', __('Settings', 'leaguemanager'), __('Settings','leaguemanager'),'manage_leaguemanager', 'leaguemanager-settings', array( $this, 'display' ));
		//add_action("admin_print_scripts-$page", array(&$this, 'loadColorpicker') );
		add_submenu_page('leaguemanager', __('Import'), __('Import'),'manage_leaguemanager', 'leaguemanager-import', array( $this, 'display' ));
		add_submenu_page('leaguemanager', __('Export'), __('Export'),'manage_leaguemanager', 'leaguemanager-export', array( $this, 'display' ));
		add_submenu_page('leaguemanager', __('Documentation', 'leaguemanager'), __('Documentation','leaguemanager'),'league_manager', 'leaguemanager-doc', array( $this, 'display' ));

		add_action("admin_print_scripts-$page", array(&$this, 'loadScriptsPage') );
		add_filter( 'plugin_action_links_' . $plugin, array( &$this, 'pluginActions' ) );
	}

	/**
	 * adds the required Metaboxes
	 */
	function metaboxes() {
		add_meta_box( 'leaguemanager', __('Match-Report','leaguemanager'), array(&$this, 'addMetaBox'), 'post' );
	}

	/**
	* Register LeagueManager Dashboard Widget
	*
	* @param  none
	* @return void
	*/
	public static function register_admin_widgets()
	{
		wp_add_dashboard_widget(
			'leaguemanager_dashboard',
			__('LeagueManager Latest Support News', 'leaguemanager'),
			array(
				'LeagueManager_Widgets',
				'latest_support_news'
			)
		);
	}


	/**
	 * build league menu
	 *
	 * @param none
	 * @return array
	 */
	function getMenu()
	{
		global $leaguemanager;

		$league = $leaguemanager->getCurrentLeague();
		$league_id = (isset($_GET['league_id']) ? intval($_GET['league_id']) : $league->id);
		$season = (isset($_GET['season']) ? htmlspecialchars($_GET['season']) : $leaguemanager->getCurrentLeague());
		$sport = (isset($league->sport) ? ($league->sport) : '' );
		$league_mode = (isset($league->mode) ? ($league->mode) : '' );
		
		$menu = array();
		$menu['settings'] = array( 'title' => __('Preferences', 'leaguemanager'), 'file' => dirname(__FILE__) . '/settings.php', 'show' => true );
		$menu['seasons'] = array( 'title' => __('Seasons', 'leaguemanager'), 'file' => dirname(__FILE__) . '/seasons.php', 'show' => true );
		$menu['team'] = array( 'title' => __('Add Team', 'leaguemanager'), 'file' => dirname(__FILE__) . '/team.php', 'show' => true );
		$menu['match'] = array( 'title' => __('Add Matches', 'leaguemanager'), 'file' => dirname(__FILE__) . '/match.php', 'show' => true );
		
		$menu = apply_filters('league_menu_'.$sport, $menu, $league_id, $season);
		$menu = apply_filters('league_menu_'.$league_mode, $menu, $league_id, $season);

		return $menu;
	}


	/**
	 * showMenu() - show admin menu
	 *
	 * @param none
	 */
	function display()
	{
		global $leaguemanager;

		$options = get_option('leaguemanager');

		// Update Plugin Version
		if ( $options['version'] != LEAGUEMANAGER_VERSION ) {
			$options['version'] = LEAGUEMANAGER_VERSION;
			update_option('leaguemanager', $options);
		}

		// Update database
		if( $options['dbversion'] != LEAGUEMANAGER_DBVERSION ) {
			include_once ( dirname (__FILE__) . '/upgrade.php' );
			leaguemanager_upgrade_page();
			return;
		}
		// Do some upgrade
		/*if ( isset($_GET['update']) ) {
			include_once ( dirname (__FILE__) . '/upgrade.php' );
			leaguemanager_upgrade();
			return;
		}*/

		if ( $leaguemanager->hasBridge() ) global $lmBridge;

		switch ($_GET['page']) {
			case 'leaguemanager-doc':
				include_once( dirname(__FILE__) . '/documentation.php' );
				break;
			case 'leaguemanager-settings':
				$this->displayOptionsPage();
				break;
			case 'leaguemanager-import':
				include_once( dirname(__FILE__) . '/import.php' );
				break;
			case 'leaguemanager-export':
				include_once( dirname(__FILE__) . '/export.php' );
				break;
			case 'leaguemanager':
			default:
				if ( isset($_GET['subpage']) ) {
					$menu = $this->getMenu();
					$page = htmlspecialchars($_GET['subpage']);
					if ( array_key_exists( $page, $menu ) ) {
						if ( isset($menu[$page]['callback']) && is_callable($menu[$page]['callback']) )
							call_user_func($menu[$page]['callback']);
						else
							include_once( $menu[$page]['file'] );
					} else {
						include_once( dirname(__FILE__) . '/show-league.php' );
					}
				} else {
					include_once( dirname(__FILE__) . '/index.php' );
				}

				break;
		}
	}


	/**
	 * display link to settings page in plugin table
	 *
	 * @param array $links array of action links
	 * @return void
	 */
	function pluginActions( $links )
	{
		$settings_link = '<a href="admin.php?page=leaguemanager-settings">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}


	/**
	 * load scripts
	 *
	 * @param none
	 * @return void
	 */
	function loadScriptsPage()
	{
		wp_register_script( 'leaguemanager-functions', LEAGUEMANAGER_URL.'/admin/js/functions.js', array( 'thickbox', 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-tabs', 'jquery-ui-accordion', 'jquery-ui-sortable', 'jquery-ui-tooltip', 'jquery-effects-core', 'jquery-effects-slide', 'jquery-effects-explode', 'scriptaculous-dragdrop', 'iris' ), LEAGUEMANAGER_VERSION );
		wp_enqueue_script('leaguemanager-functions');
	}
	function loadScripts()
	{
		wp_register_script( 'leaguemanager-functions', LEAGUEMANAGER_URL.'/admin/js/functions.js', array( 'thickbox', 'jquery', 'jquery-ui-datepicker', 'jquery-ui-tabs', 'jquery-ui-accordion', 'jquery-ui-sortable', 'jquery-ui-tooltip', 'jquery-effects-core', 'jquery-effects-slide', 'jquery-effects-explode', 'iris' ), LEAGUEMANAGER_VERSION );
		wp_enqueue_script('leaguemanager-functions');
		wp_register_script( 'leaguemanager-ajax', LEAGUEMANAGER_URL.'/admin/js/ajax.js', array('sack'), LEAGUEMANAGER_VERSION );
		wp_enqueue_script('leaguemanager-ajax');

		?>
		<script type='text/javascript'>
		<!--<![CDATA[-->
		LeagueManagerAjaxL10n = {
			requestUrl: "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php", manualPointRuleDescription: "<?php _e( 'Order: win, win overtime, tie, loss, loss overtime', 'leaguemanager' ) ?>", pluginUrl: "<?php echo LEAGUEMANAGER_URL; ?>", Edit: "<?php _e("Edit"); ?>", Post: "<?php _e("Post"); ?>", Save: "<?php _e("Save"); ?>", Cancel: "<?php _e("Cancel"); ?>", pleaseWait: "<?php _e("Please wait..."); ?>", Delete: "<?php _e('Delete', 'leaguemanager') ?>", Yellow: "<?php _e( 'Yellow', 'leaguemanager') ?>", Red: "<?php _e( 'Red', 'leaguemanager') ?>", Yellow_Red: "<?php _e('Yellow/Red', 'leaguemanager') ?>", Insert: "<?php _e( 'Insert', 'leaguemanager' ) ?>", InsertPlayer: "<?php _e( 'Insert Player', 'leaguemanager' ) ?>", AddPlayerFromRoster: "<?php _e( 'Add Player from Team Roster', 'leaguemanager' ) ?>"
		}
		<!--]]>-->
		</script>
		<?php
	}
	function loadColorpicker()
	{
		wp_register_script ('leaguemanager_colorpicker', LEAGUEMANAGER_URL.'/admin/js/colorpicker.js', array( 'colorpicker' ), LEAGUEMANAGER_VERSION );
		wp_enqueue_script('leaguemanager_colorpicker');
	}


	/**
	 * load styles
	 *
	 * @param none
	 * @return void
	 */
	function loadStyles()
	{
		wp_register_style('leaguemanager', LEAGUEMANAGER_URL . "/style.css", false, '1.0', 'screen');
		wp_enqueue_style('leaguemanager');
		
		wp_register_style('jquery-ui', LEAGUEMANAGER_URL . "/css/jquery/jquery-ui.min.css", false, '1.11.4', 'all');
		wp_register_style('jquery-ui-structure', LEAGUEMANAGER_URL . "/css/jquery/jquery-ui.structure.min.css", array('jquery-ui'), '1.11.4', 'all');
		wp_register_style('jquery-ui-theme', LEAGUEMANAGER_URL . "/css/jquery/jquery-ui.theme.min.css", array('jquery-ui', 'jquery-ui-structure'), '1.11.4', 'all');
		
		wp_enqueue_style('jquery-ui-structure');
		wp_enqueue_style('jquery-ui-theme');
		
		//wp_register_style('jquery_ui_css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css', false, '1.0', 'screen');
		//wp_enqueue_style('jquery_ui_css');
		wp_enqueue_style('thickbox');
	}


	/**
	 * set message by calling parent function
	 *
	 * @param string $message
	 * @param boolean $error (optional)
	 * @return void
	 */
	function setMessage( $message, $error = false )
	{
		parent::setMessage( $message, $error );
	}


	/**
	 * print message calls parent
	 *
	 * @param none
	 * @return string
	 */
	function printMessage()
	{
		parent::printMessage();
	}


	/**
	 * get available league modes
	 *
	 * @param none
	 * @return array
	 */
	function getModes()
	{
		$modes = array( 'default' => __('Default', 'leaguemanager') );
		$modes = apply_filters( 'leaguemanager_modes', $modes);
		return $modes;
	}


	/**
	 * savePointsManually() - update points manually
	 *
	 * @param array $teams
	 * @param array $points_plus
	 * @param array $points_minus
	 * @param array $num_done_matches
	 * @param array $num_won_matches
	 * @param array $num_draw_matches
	 * @param array $num_lost_matches
	 * @param array $add_points
	 * @return none
	 */
	function saveStandingsManually( $teams, $points_plus, $points_minus,  $num_done_matches, $num_won_matches, $num_draw_matches, $num_lost_matches, $add_points, $custom )
	{
		global $wpdb, $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		while ( list($id) = each($teams) ) {
			$points2_plus = isset($custom[$id]['points2']) ? $custom[$id]['points2']['plus'] : 0;
			$points2_minus = isset($custom[$id]['points2']) ? $custom[$id]['points2']['minus'] : 0;
			$diff = $points2_plus - $points2_minus;

			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `points_plus` = '%d', `points_minus` = '%d', `points2_plus` = '%d', `points2_minus` = '%d', `done_matches` = '%d', `won_matches` = '%d', `draw_matches` = '%d', `lost_matches` = '%d', `diff` = '%d', `add_points` = '%d' WHERE `id` = '%d'", $points_plus[$id], $points_minus[$id], $points2_plus, $points2_minus, $num_done_matches[$id], $num_won_matches[$id], $num_draw_matches[$id], $num_lost_matches[$id], $diff[$id], $add_points[$id], $id ) );
		}

		// Update Teams Rank and Status if not set to manual ranking
		if ($league->team_ranking != 'manual')
			$leaguemanager->rankTeams( $league->id );
	}


	/**
	 * get array of supported point rules
	 *
	 * @param none
	 * @return array
	 */
	function getPointRules()
	{
		$rules = array( 'manual' => __( 'Update Standings Manually', 'leaguemanager' ), 'one' => __( 'One-Point-Rule', 'leaguemanager' ), 'two' => __('Two-Point-Rule','leaguemanager'), 'three' => __('Three-Point-Rule', 'leaguemanager'), 'score' => __( 'Score', 'leaguemanager'), 'user' => __('User defined', 'leaguemanager') );

		$rules = apply_filters( 'leaguemanager_point_rules_list', $rules );
		asort($rules);

		return $rules;
	}


	/**
	 * get available point formats
	 *
	 * @param none
	 * @return array
	 */
	function getPointFormats()
	{
		$point_formats = array( '%d:%d' => '%d:%d', '%d - %d' => '%d - %d', '%d' => '%d', '%.1f:%.1f' => '%f:%f', '%.1f - %.1f' => '%f - %f', '%.1f' => '%f' );
		$point_formats = apply_filters( 'leaguemanager_point_formats', $point_formats );
		return $point_formats;
	}


	/**
	 * get number of matches for team
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumDoneMatches( $team_id )
	{
		global $wpdb, $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		//$num_matches = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE (`home_team` = '%d' OR `away_team` = '%d') AND `home_points` IS NOT NULL AND `away_points` IS NOT NULL", $team_id, $team_id) );
		$num_matches = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `final` = '' AND (`home_team` = '%d' OR `away_team` = '%d') AND `home_points` != '' AND `away_points` != ''", $team_id, $team_id) );
		$num_matches = apply_filters( 'leaguemanager_done_matches_'.$league->sport, $num_matches, $team_id );
		return $num_matches;
	}


	/**
	 * get number of won matches
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumWonMatches( $team_id )
	{
		global $wpdb, $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		$num_win = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `final` = '' AND `winner_id` = '%d'", $team_id) );
		$num_win = apply_filters( 'leaguemanager_won_matches_'.$league->sport, $num_win, $team_id );
		return $num_win;
	}


	/**
	 * get number of draw matches
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumDrawMatches( $team_id )
	{
		global $wpdb, $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		$num_draw = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `final` = '' AND `winner_id` = -1 AND `loser_id` = -1 AND (`home_team` = '%d' OR `away_team` = '%d')", $team_id, $team_id) );
		$num_draw = apply_filters( 'leaguemanager_tie_matches_'.$league->sport, $num_draw, $team_id );
		return $num_draw;
	}


	/**
	 * get number of lost matches
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumLostMatches( $team_id )
	{
		global $wpdb, $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		$num_lost = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->leaguemanager_matches} WHERE `final` = '' AND `loser_id` = '%d'", $team_id) );
		$num_lost = apply_filters( 'leaguemanager_lost_matches_'.$league->sport, $num_lost, $team_id );
		return $num_lost;
	}


	/**
	 * update points for given team
	 *
	 * @param int $team_id
	 * @return none
	 */
	function saveStandings( $team_id )
	{
		global $wpdb, $leaguemanager;

		$this->league = $league = parent::getLeague($this->league_id);
		if ( $league->point_rule != 'manual' ) {
			$this->num_done = $this->getNumDoneMatches($team_id);
			$this->num_won = $this->getNumWonMatches($team_id);
			$this->num_draw = $this->getNumDrawMatches($team_id);
			$this->num_lost = $this->getNumLostMatches($team_id);

			$points['plus'] = $this->calculatePoints( $team_id, 'plus' );
			$points['minus'] = $this->calculatePoints( $team_id, 'minus' );

            $points2 = array( 'plus' => 0, 'minus' => 0 );
            if(has_filter('team_points2_'.$league->sport)) {
                $points2 = apply_filters( 'team_points2_'.$league->sport, $team_id );
            }

			$diff = $points2['plus'] - $points2['minus'];

			$wpdb->query ( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `points_plus` = '%d', `points_minus` = '%d', `points2_plus` = '%d', `points2_minus` = '%d', `done_matches` = '%d', `won_matches` = '%d', `draw_matches` = '%d', `lost_matches` = '%d', `diff` = '%d' WHERE `id` = '%d'", $points['plus'], $points['minus'], $points2['plus'], $points2['minus'], $this->num_done, $this->num_won, $this->num_draw, $this->num_lost, $diff, $team_id ) );

			do_action( 'leaguemanager_save_standings_'.$league->sport, $team_id );
		}
	}


	/**
	 * calculate points for given team depending on point rule
	 *
	 * @param int $team_id
	 * @param string $option
	 * @return int
	 */
	function calculatePoints( $team_id, $option )
	{
		global $wpdb, $leaguemanager;

		$league = $this->league;

		$rule = $leaguemanager->getPointRule( $league->point_rule );
		$team_id = intval($team_id);
		$points = array( 'plus' => 0, 'minus' => 0 );
		$team_points = 0;
		
		if ( 'score' == $rule ) {
			$home = $leaguemanager->getMatches( array("home_team" => $team_id, "limit" => false) );
			foreach ( $home AS $match ) {
				$points['plus'] += $match->home_points;
				$points['minus'] += $match->away_points;
			}

			$away = $leaguemanager->getMatches( array("away_team" => $team_id, "limit" => false) );
			foreach ( $away AS $match ) {
				$points['plus'] += $match->away_points;
				$points['minus'] += $match->home_points;
			}
		} else {
			extract( (array)$rule );
			$home = $leaguemanager->getMatches( array("home_team" => $team_id, "limit" => false) );
			foreach ( $home AS $match ) {
				$team_points += $match->home_points;
			}
			
			$away = $leaguemanager->getMatches( array("away_team" => $team_id, "limit" => false) );
			foreach ( $away AS $match ) {
				$team_points += $match->away_points;
			}
				
			$points['plus'] = $this->num_won * $forwin + $this->num_draw * $fordraw + $this->num_lost * $forloss + ($team_points * (isset($forscoring) ? $forscoring : 0));
			$points['minus'] = $this->num_draw * $fordraw + $this->num_lost * $forwin + $this->num_won * $forloss;
		}

		$points = apply_filters( 'team_points_'.$league->sport, $points, $team_id, $rule );
		return $points[$option];
	}


/************
*
*   LEAGUE SECTION
*
*
*/

	/**
	 * add new League
	 *
	 * @param string $title
	 * @return void
	 */
	function addLeague( $title, $sport = null )
	{
		global $wpdb;

		$settings = array( 'upload_dir' => 'wp-content/uploads/leaguemanager', 'standings' => array('pld' => 1, 'won' => 1, 'tie' => 1, 'lost' => 1) );
        if($sport) {
            $settings['sport'] = $sport;
        }
		$wpdb->query( $wpdb->prepare ( "INSERT INTO {$wpdb->leaguemanager} (title, settings, seasons) VALUES ('%s', '%s', '%s')", $title, maybe_serialize($settings), '') );
		parent::setMessage( __('League added', 'leaguemanager') );
	}


	/**
	 * edit League
	 *
	 * @param string $title
	 * @param array $settings
	 * @param int $league_id
	 * @return void
	 */
	function editLeague( $title, $settings, $league_id )
	{
		global $wpdb;

		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager} SET `title` = '%s', `settings` = '%s' WHERE `id` = '%d'", $title, maybe_serialize($settings), $league_id ) );
		parent::setMessage( __('Settings saved', 'leaguemanager') );
	}


	/**
	 * delete League
	 *
	 * @param int $league_id
	 * @return void
	 */
	function delLeague( $league_id )
	{
		global $wpdb, $leaguemanager;

		// Delete Teams and with it Matches
		foreach ( parent::getTeams( array("league_id" => $league_id )) AS $team ) {
			$this->delTeam( $team->id );
		}

		// remove remaining matches
		$wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->leaguemanager_matches} WHERE `league_id` = '%d'", $league_id) );
		
		// remove statistics
		$wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->leaguemanager_stats} WHERE `league_id` = '%d'", $league_id) );
		$wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->leaguemanager} WHERE `id` = '%d'", $league_id) );
		
		$leaguemanager->setLeagueID($league_id);
		// Try deleting league subfolder
		@unlink($leaguemanager->getImagePath());
	}


	/**
	 * add new season to league
	 *
	 * @param string $season
	 * @param int $num_match_days
	 * @param int $league_id
	 * @param boolean $add_teams
	 * @return void
	 */
	function saveSeason( $season, $num_match_days, $add_teams = false, $key = false )
	{
		global $leaguemanager, $wpdb;

		$league = $leaguemanager->getCurrentLeague();
		//$league = $leaguemanager->getLeague($league_id);
		if ( $add_teams && !empty($league->seasons) && !$key ) {
			$last_season = end($league->seasons);
			if ( !empty($last_season) ) {
				if ( $teams = $leaguemanager->getTeams(array("league_id" => $league->id, "season" => $last_season['name'])) ) {
					foreach ( $teams AS $team ) {
						$this->addTeamFromDB( $league->id, $season, $team->id, false );
					}
				}
			}
		}

		if ( $key ) {
			$key = htmlspecialchars($key);
			if ( $teams = $leaguemanager->getTeams( array("season" => $key, "league_id" => $league->id) ) ) {
				foreach ( $teams AS $team ) {
					$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->leaguemanager_teams} SET `season` = '%s' WHERE `id` = '%d'", $season, $team->id) );
				}
			}
			if ( $matches = $leaguemanager->getMatches( array("season" => $key, "league_id" => $league->id, "limit" => false) ) ) {
				foreach ( $matches AS $match ) {
					$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->leaguemanager_matches} SET `season` = '%s' WHERE `id` = '%d'", $season, $match->id) );
				}
			}
		}

		// unset broken season, due to delete bug
		if ( $key && $key != $season )
			unset($league->seasons[$key]);

		//array_push($league->seasons, array( 'name' => $season, 'num_match_days' => $num_match_days ));
		$league->seasons[$season] = array( 'name' => $season, 'num_match_days' => $num_match_days );
		ksort($league->seasons);
		$this->saveSeasons($league->seasons, $league->id);

		parent::setMessage( sprintf(__('Season <strong>%s</strong> added','leaguemanager'), $season ) );
		parent::printMessage();
	}


	/**
	 * delete season of league
	 *
	 * @param array $seasons
	 * @param int $league_id
	 * @return array of new options
	 */
	function delSeasons( $seasons, $league_id )
	{
		global $leaguemanager;
		$league = $leaguemanager->getCurrentLeague();

		if ( !empty($seasons) ) {
			foreach ( $seasons AS $key ) {
				$season = $league->seasons[$key];

				// Delete teams and matches if there are any
				if ( $teams = $leaguemanager->getTeams(array("league_id" => $league->id, "season" => $season['name'])) ) {
					foreach ( $teams AS $team )
						$this->delTeam($team->id);
				}

				unset($league->seasons[$key]);
			}
			$this->saveSeasons($league->seasons, $league->id);
		}
	}


	/**
	 * save seasons array to database
	 *
	 * @param array $seasons
	 * @param int $league_id
	 */
	function saveSeasons($seasons, $league_id)
	{
		global $wpdb;
		$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->leaguemanager} SET `seasons` = '%s' WHERE `id` = '%d'", maybe_serialize($seasons), $league_id) );
	}

/************
*
*   TEAM SECTION
*
*
*/

	/**
	 * add new team
	 *
	 * @param int $league_id
	 * @param mixed $season
	 * @param string $title
	 * @param string $website
	 * @param string $coach
	 * @param string $stadium
	 * @param int $home 1 | 0
	 * @param mixed $group
	 * @param int|array $roster
	 * @param int $profile
	 * @param array $custom
	 * @param string $logo (optional)
	 * @param boolean $message (optional)
	 * @return void
	 */
	function addTeam( $league_id, $season, $title, $website, $coach, $stadium, $home, $group, $roster, $profile, $custom, $logo = '', $message = true )
	{
		global $wpdb, $leaguemanager;

		$sql = "INSERT INTO {$wpdb->leaguemanager_teams} (`title`, `website`, `coach`, `stadium`, `home`, `group`, `roster`, `profile`, `season`, `custom`, `logo`, `league_id`) VALUES ('%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d')";
		$wpdb->query( $wpdb->prepare ( $sql, $title, $website, $coach, $stadium, $home, $group, maybe_serialize($roster), $profile, $season, maybe_serialize($custom), basename($logo), $league_id ) );
		$team_id = $wpdb->insert_id;

		if ( isset($_FILES['logo']) && $_FILES['logo']['name'] != '' )
			$this->uploadLogo($team_id, $_FILES['logo']);

		if ( !empty($logo) ) {
			$logo_file = new LeagueManagerImage($logo);
			$logo_file->createThumbnail();
		}
		
		if ( $message )
			$leaguemanager->setMessage( __('Team added','leaguemanager') );

		return $team_id;
	}


	/**
	 * add new team with data from existing team
	 *
	 * @param int $league_id
	 * @param string $season
	 * @param int $team_id
	 * @param boolean $message (optional)
	 * @return void
	 */
	function addTeamFromDB( $league_id, $season, $team_id, $message = false )
	{
		global $wpdb;
		$team = $wpdb->get_results( $wpdb->prepare("SELECT `league_id`, `title`, `website`, `coach`, `stadium`, `home`, `group`, `roster`, `profile`, `logo`, `custom` FROM {$wpdb->leaguemanager_teams} WHERE `id` = '%d'", $team_id) );
		$team = $team[0];

		$new_team_id = $this->addTeam($league_id, $season, $team->title, $team->website, $team->coach, $team->stadium, $team->home, $team->group, maybe_unserialize($team->roster), $team->profile, maybe_unserialize($team->custom), $team->logo, $message);
	}


	/**
	 * edit team
	 *
	 * @param int $team_id
	 * @param string $title
	 * @param string $website
	 * @param string $coach
	 * @param string $stadium
	 * @param int $home 1 | 0
	 * @param mixed $group
	 * @param int|array $roster
	 * @param int $profile
	 * @param array $custom
	 * @param boolean $del_logo
	 * @param string $image_file
	 * @param boolean $overwrite_image
	 * @return void
	 */
	function editTeam( $team_id, $title, $website, $coach, $stadium, $home, $group, $roster, $profile, $custom, $logo, $del_logo = false, $overwrite_image = false )
	{
		global $wpdb, $leaguemanager;

		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager_teams} SET `title` = '%s', `website` = '%s', `coach` = '%s', `stadium` = '%s', `logo` = '%s', `home` = '%d', `group` = '%s', `roster`= '%s', `profile` = '%d', `custom` = '%s' WHERE `id` = %d", $title, $website, $coach, $stadium, basename($logo), $home, $group, maybe_serialize($roster), $profile, maybe_serialize($custom), $team_id ) );

		// Delete Image if options is checked
		if ($del_logo || $overwrite_image) {
			$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->leaguemanager_teams} SET `logo` = '' WHERE `id` = '%d'", $team_id) );
			$this->delLogo( $logo );
		}

		if ( !empty($logo) && !$del_logo ) {
			//$logo_image = new LeagueManagerImage($logo);
			//$logo_image->createThumbnail();
		}

		if ( isset($_FILES['logo']) && $_FILES['logo']['name'] != '' )
			$this->uploadLogo($team_id, $_FILES['logo'], $overwrite_image);

		$leaguemanager->setMessage( __('Team updated','leaguemanager') );
	}


	/**
	 * delete Team
	 *
	 * @param int $team_id
	 * @return void
	 */
	function delTeam( $team_id )
	{
		global $wpdb;

		$team = parent::getTeam( $team_id );
		$logo = $team->logo;
		// check if other team uses the same logo
		//$keep_logo = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->leaguemanager_teams} WHERE `logo` = '%s'", $team->logo) );
		//if ( $keep_logo == 0 )

		$wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->leaguemanager_matches} WHERE `home_team` = '%d' OR `away_team` = '%d'", $team_id, $team_id) );
		$wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->leaguemanager_teams} WHERE `id` = '%d'", $team_id) );
		
		$this->delLogo( $logo );
	}


	/**
	 * display dropdon menu of teams (cleaned from double entries)
	 *
	 * @param none
	 * @return void
	 */
	function teamsDropdownCleaned()
	{
		global $wpdb;
		$all_teams = $wpdb->get_results( "SELECT `title`, `id` FROM {$wpdb->leaguemanager_teams} ORDER BY `title` ASC" );
		$teams = array();
		foreach ( $all_teams AS $team ) {
			if ( !in_array($team->title, $teams) )
				$teams[$team->id] = htmlspecialchars(stripslashes($team->title), ENT_QUOTES);
		}
		foreach ( $teams AS $team_id => $name )
			echo "<option value='".$team_id."'>".$name."</option>";
	}


	/**
	 * gets ranking of teams
	 *
	 * @param string $input serialized string with order
	 * @param string $listname ID of list to sort
	 * @return sorted array of parameters
	 */
	function getRanking( $input, $listname = 'the-list-standings' )
	{
		parse_str( $input, $input_array );
		$input_array = $input_array[$listname];
		$order_array = array();
		for ( $i = 0; $i < count($input_array); $i++ ) {
			if ( $input_array[$i] != '' )
				$order_array[$i+1] = $input_array[$i];
		}
		return $order_array;
	}


	/**
	 * crop image
	 *
	 * @param string $imagepath
	 * @return string image url
	 */
	function resizeImage ( $imagepath, $dest_size, $size, $crop = false, $force_resize = false )
	{
		global $leaguemanager;
		
		$options = get_option('leaguemanager');
		
		// load image editor
		$image = wp_get_image_editor( $imagepath );
		
		$imageurl = $leaguemanager->getImageUrl( $imagepath );
		
		// editor will return an error if the path is invalid - save original image url
		if ( is_wp_error( $image ) ) {
			return $imageurl;
		} else {
			// create destination file name
			$destination_file = $leaguemanager->getImagePath($imagepath, false, $size);
			$this->destination_file = $destination_file;

			// resize only if the image does not exists
			if ( !file_exists($destination_file) || $force_resize ) {			
				// resize image, optionally with cropping enabled
				$image->resize( $dest_size['width'], $dest_size['height'], $crop );
				// save image
				$saved = $image->save( $destination_file );
				// return original url if an error occured
				 if ( is_wp_error( $saved ) ) {
					return $imageurl;
				}
			}
			
			$new_img_url = dirname($imageurl) . '/' . basename($destination_file);
			
			// record resized images with key using md5 hash of path to original image
			if ( isset($saved) && !in_array(basename($new_img_url), $options['resized_images'][$this->league->id][md5($imagepath)]) )
				$options['resized_images'][$this->league->id][md5($imagepath)][] = basename($new_img_url);
			
			update_option('leaguemanager', $options);
			
			return esc_url($new_img_url);
		}
	}
	
	
	/**
	 * Create different thumbnail sizes
	 *
	 * @param string $filename
	 * @param string $filename
	 */
	function createThumbnails($filename, $force_resize = false)
	{
		global $leaguemanager;
		
		$this->league = $league = $leaguemanager->getCurrentLeague();
		
		$options = get_option('leaguemanager');
		/*
		 * create resized image records
		 */
		if ( !isset($options['resized_images']) )
			$options['resized_images'] = array();
	
		if ( !isset($options['resized_images'][$league->id]) ) {
			$options['resized_images'][$league->id] = array();
		}
		
		if ( !isset($options['resized_images'][$league->id][md5($filename)]) ) {
			$options['resized_images'][$league->id][md5($filename)] = array();
		}
		update_option('leaguemanager', $options);
		
		//require_once (PROJECTMANAGER_PATH . '/lib/image.php');
		//$image = new ProjectManagerImage($filename);
		
		// create different thumbnails
		$sizes = array( 'tiny' => $league->tiny_size, 'thumb' => $league->thumb_size, 'large' => $league->large_size );
		foreach ( $sizes AS $size => $dest_size ) {
			$crop = ( $league->crop_image[$size] == 1 ) ? true : false;
			$imageurl = $this->resizeImage( $filename, $dest_size, $size, $crop, $force_resize );
		}
	}
	
	
	/**
	 * get supported file types
	 *
	 * @param none
	 * @return array
	 */
	function getSupportedImageTypes()
	{
		return array( "jpg", "jpeg", "png", "gif" );
	}
	

	/**
	 * check if image type is supported
	 *
	 * @param none
	 * @return boolean
	 */
	function isSupportedImage( $image )
	{
		if ( in_array($this->getImageType($image), $this->getSupportedImageTypes()) )
			return true;
		
		return false;
	}
	
	
	/**
	 * get image type of supplied image
	 *
	 * @param none
	 * @return file extension
	 */
	function getImageType( $image )
	{
		global $leaguemanager;
		$file = $leaguemanager->getImagePath($image);
		$file_info = pathinfo($file);
		return strtolower($file_info['extension']);
	}
	
	
	/**
	 * regenerate all thumbnails of current project
	 *
	 * @param none
	 */
	function regenerateThumbnails()
	{
		global $wpdb, $leaguemanager;
		
		$league = $leaguemanager->getCurrentLeague();
		
		/*
		 * regenerate dataset image thumbnails
		 */
		$teams = $leaguemanager->getTeams( array('league_id' => $league->id) );
		foreach ( $teams AS $team ) {
			if ( $team->logo != "" ) {
				$this->createThumbnails($leaguemanager->getImagePath($team->logo), true);
			}
		}
	}
	
	
	/*
	 * list or remove unused media files
	 *
	 * @param none
	 */
	function cleanUnusedMediaFiles( )
	{
		global $wpdb, $leaguemanager;
		
		$league = $leaguemanager->getCurrentLeague();
		
		$dir = $leaguemanager->getImagePath();
		$files = array_diff(scandir($dir), array('.','..'));
		$img_sizes = array( 'tiny', 'thumb', 'large' );
		// get all thumbnail images
		$thumbs = array();
		foreach ( $img_sizes AS $size ) {
			$thumbs = array_merge($thumbs, preg_grep("/".$size."\_/", $files));
		}
		// remove thumbnail images from filelist
		$files = array_diff($files, $thumbs);
		
		// check if file is used
		foreach ( $files AS $key => $file ) {
			$query = $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->leaguemanager_teams} WHERE `logo` = '%s'", basename($file));
			$num = $wpdb->get_var( $query );
			
			if ( $num > 0 ) {
				$file_info = pathinfo( $leaguemanager->getImagePath($file) );
				// remove file from list
				unset($files[$key]);
				// remove fancy slideshow widget thumbnails
				$files = array_diff($files, preg_grep("/".str_replace(".{$file_info['extension']}", "", basename($file))."-\d+x\d+.+/", $files));
			}
			
			$query = $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->leaguemanager_teams} WHERE `logo` = '%s'", $leaguemanager->getImageUrl(basename($file)));
			$num = $wpdb->get_var( $query );
			
			if ( $num > 0 ) {
				$file_info = pathinfo( $leaguemanager->getImagePath($file) );
				// remove file from list
				unset($files[$key]);
				// remove fancy slideshow widget thumbnails
				$files = array_diff($files, preg_grep("/".str_replace(".{$file_info['extension']}", "", basename($file))."-\d+x\d+.+/", $files));
			}
		}
		
		if ( isset($_POST['delete_unused_files']) ) {
			check_admin_referer('leaguemanager_delete-unused-media-files');
			
			foreach ( $files AS $file ) {
				//@unlink($leaguemanager->getImagePath($file));
				$this->delLogo($file);
			}
			echo "<div class='box success updated fade'>";
			echo "<p>".__( 'Unused media files deleted', 'leaguemanager' )."</p>";
			echo "</div>";
		} else {
			echo "<div class='box fade'>";
			if ( count($files) == 0 ) {
				echo "<p>".__( 'Congratulations! This league has no orphaned media files.', 'leaguemanager' )."</p>";
			} else {
				echo "<p>".__( 'The following files do not do not seem to be used. If this is true you can subsequently delete them.', 'leaguemanager' )."</p>";
				echo "<ul>";
				foreach ( $files AS $file ) {
					echo "<li style='margin-left: 2em;'>".$leaguemanager->getImagePath($file)."</li>";
				}
				echo "</ul>";
				echo "<form action='' method='post'>";
				echo "<input type='submit' class='button-primary' value='".__('Delete unused media files', 'leaguemanager')."' />";
				wp_nonce_field( 'leaguemanager_delete-unused-media-files' ); 
				echo "<input type='hidden' name='delete_unused_files' value='yes' />";
				echo "</form>";
			}
			echo "</div>";
		}
		
		//return $files;
	}
	
	
	/**
	 * set image path in database and upload image to server
	 *
	 * @param int  $team_id
	 * @param string $file
	 * @param string $uploaddir
	 * @param boolean $overwrite_image
	 * @return void | string
	 */
	function uploadLogo( $team_id, $file, $overwrite = false )
	{
		global $wpdb, $leaguemanager;

		$new_file = $leaguemanager->getImagePath( basename($file['name']) );
		$info = pathinfo( $new_file );
		// make sure that file extension is lowercase
		$new_file = str_replace($info['extension'], strtolower($info['extension']), $new_file);
		
		//$logo = new LeagueManagerImage(basename($file['name']));
		if ( $this->isSupportedImage($new_file) ) {
			if ( $file['size'] > 0 ) {
				if ( file_exists($new_file) && !$overwrite ) {
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `logo` = '%s' WHERE id = '%d'", basename($file['name']), $team_id ) );
					parent::setMessage( __('Logo exists and is not uploaded. Set the overwrite option if you want to replace it.','leaguemanager'), true );
				} else {
					if ( move_uploaded_file($file['tmp_name'], $new_file) ) {
						$team = $this->getTeam( $team_id );
						$logo_file = $team->logo;

						$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `logo` = '%s' WHERE id = '%d'", basename($file['name']), $team_id ) );
						
						$this->delLogo($logo_file);

						$this->createThumbnails($new_file, $overwrite);
						//$logo->createThumbnail();
					} else {
						parent::setMessage( sprintf( __('The uploaded file could not be moved to %s.' ), $leaguemanager->getImagePath() ), true );
					}
				}
			}
		} else {
			parent::setMessage( __('The file type is not supported.','leaguemanager'), true );
		}
	}


	/**
	 * delete logo from server
	 *
	 * @param string $image
	 * @return void
	 *
	 */
	function delLogo( $image )
	{
		global $wpdb, $leaguemanager;
		
		$num = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->leaguemanager_teams} WHERE `logo` = '%s'", basename($image)) );
		if ( $num == 0 ) {
			$sizes = array( 'tiny', 'thumb', 'large', 'full' );
			foreach ($sizes AS $size) {
				@unlink( $leaguemanager->getImagePath($image, false, $size) );
			}
			//@unlink( $leaguemanager->getImagePath($image) );
			//@unlink( $leaguemanager->getThumbnailPath($image) );
		}
	}

/************
*
*   MATCH SECTION
*
*
*/

	/**
	 * add Match
	 *
	 * @param string $date
	 * @param int $home_team
	 * @param int $away_team
	 * @param int $match_day
	 * @param string $location
	 * @param int $league_id
	 * @param mixed $season
	 * @param mixed $group
	 * @param string $final
	 * @param array $custom
	 * @return string
	 */
	function addMatch( $date, $home_team, $away_team, $match_day, $location, $league_id, $season, $group, $final, $custom )
	{
	 	global $wpdb;
		$sql = "INSERT INTO {$wpdb->leaguemanager_matches} (date, home_team, away_team, match_day, location, league_id, season, final, custom, `group`) VALUES ('%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s')";
		$wpdb->query ( $wpdb->prepare ( $sql, $date, $home_team, $away_team, $match_day, $location, $league_id, $season, $final, maybe_serialize($custom), $group ) );
		return $wpdb->insert_id;
	}


	/**
	 * edit Match
	 *
	 * @param string $date
	 * @param int $home_team
	 * @param int $away_team
	 * @param int $match_day
	 * @param string $location
	 * @param int $league_id
	 * @param int $match_id
	 * @param mixed $group
	 * @param string $final
	 * @param array $custom
	 * @return string
	 */
	function editMatch( $date, $home_team, $away_team, $match_day, $location, $league_id, $match_id, $group, $final, $custom )
	{
	 	global $wpdb;
		$this->league_id = $league_id;
		$home_points = (!isset($home_points)) ? 'NULL' : $home_points;
		$away_points = (!isset($away_points)) ? 'NULL' : $away_points;

		$match = $wpdb->get_results( $wpdb->prepare("SELECT `custom` FROM {$wpdb->leaguemanager_matches} WHERE `id` = '%d'", $match_id) );
		$custom = (!empty($match) ? array_merge( (array)maybe_unserialize($match[0]->custom), $custom ) : '' );
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->leaguemanager_matches} SET `date` = '%s', `home_team` = '%s', `away_team` = '%s', `match_day` = '%d', `location` = '%s', `league_id` = '%d', `group` = '%s', `final` = '%s', `custom` = '%s' WHERE `id` = %d", $date, $home_team, $away_team, $match_day, $location, $league_id, $group, $final, maybe_serialize($custom), $match_id ) );
	}


	/**
	 * delete Match
	 *
	 * @param int $cid
	 * @return void
	 */
	function delMatch( $match_id )
	{
	  	global $wpdb;
		$wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->leaguemanager_matches} WHERE `id` = '%d'", $match_id) );
		return;
	}


	/**
	 * update match results
	 *
	 * @param int $league_id
	 * @param array $matches
	 * @param array $home_points2
	 * @param array $away_points2
	 * @param array $home_points
	 * @param array $away_points
	 * @return string
	 */
	function updateResults( $league_id, $matches, $home_points, $away_points, $home_team, $away_team, $custom, $final = false, $message = true )
	{
		global $wpdb, $leaguemanager;

		$this->league_id = $league_id;
		$league = $leaguemanager->getLeague($this->league_id);
		$season = $leaguemanager->getSeason($league);

		$num_matches = count($matches);

		if ( !empty($matches) ) {
			while ( list($match_id) = each($matches) ) {
				$home_points[$match_id] = ( '' === $home_points[$match_id] ) ? 'NULL' : $home_points[$match_id];
				$away_points[$match_id] = ( '' === $away_points[$match_id] ) ? 'NULL' : $away_points[$match_id];

				// Support for penalty and overtime hardcoded to determine winner of match
				if ( isset($custom[$match_id]['penalty']) && !empty($custom[$match_id]['penalty']['home']) && !empty($custom[$match_id]['penalty']['home']) )
					$points = array( 'home' => $custom[$match_id]['penalty']['home'], 'away' => $custom[$match_id]['penalty']['away'] );
				elseif ( isset($custom[$match_id]['overtime']) && !empty($custom[$match_id]['overtime']['home']) && !empty($custom[$match_id]['overtime']['away']) )
					$points = array( 'home' => $custom[$match_id]['overtime']['home'], 'away' => $custom[$match_id]['overtime']['away'] );
				else
					$points = array( 'home' => $home_points[$match_id], 'away' => $away_points[$match_id] );

				$winner = $this->getMatchResult( $points['home'], $points['away'], $home_team[$match_id], $away_team[$match_id], 'winner' );
				$loser = $this->getMatchResult($points['home'], $points['away'], $home_team[$match_id], $away_team[$match_id], 'loser' );

				$m = $leaguemanager->getMatch( $match_id, false );
				$cv = isset($custom[$match_id]) ? $custom[$match_id] : array();
				$c = array_merge( (array)$m->custom, (array)$cv );
				$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->leaguemanager_matches} SET `home_points` = ".$home_points[$match_id].", `away_points` = ".$away_points[$match_id].", `winner_id` = '%d', `loser_id` = '%d', `custom` = '%s' WHERE `id` = '%d'", intval($winner), intval($loser), maybe_serialize($c), $match_id) );

				do_action('leaguemanager_update_results_'.$league->sport, $match_id);
			}
		}

		if ( !$final ) {
			// update Standings for each team
			$teams = $leaguemanager->getTeams( array("league_id" => $league->id, "season" => $season['name']) );
			foreach ( $teams AS $team ) {
				$this->saveStandings($team->id);
			}

			// Update Teams Rank and Status
			$leaguemanager->rankTeams( $league->id );

			/*
			 * Initialize finals if championship mode is activated and all matches have results
			 */
			/*$matches = $leaguemanager->getMatches( array("league_id" => $league_id, "season" => $season['name'], "final" => '', "home_points" => "null", "away_points" => "null") );
			if ( !$matches && $league->mode == 'championship' ) {
				global $championship;
				$championship->proceed( false, $championship->getFinalKeys(1), $league_id );
			}*/
		}

		if ( $message )
			$leaguemanager->setMessage( sprintf(__('Updated Results of %d matches','leaguemanager'), $num_matches) );
	}


	/**
	 * determine match result
	 *
	 * @param int $home_points
	 * @param int $away_points
	 * @param int $home_team
	 * @param int $away_team
	 * @param string $option
	 * @return int
	 */
	function getMatchResult( $home_points, $away_points, $home_team, $away_team, $option )
	{
		if ( $home_points > $away_points ) {
			$match['winner'] = $home_team;
			$match['loser'] = $away_team;
		} elseif ( $home_points < $away_points ) {
			$match['winner'] = $away_team;
			$match['loser'] = $home_team;
		} elseif ( 'NULL' === $home_points && 'NULL' === $away_points ) {
			$match['winner'] = 0;
			$match['loser'] = 0;
		} elseif ( '' == $home_points && '' == $away_points ) {
			$match['winner'] = 0;
			$match['loser'] = 0;
		} else {
			$match['winner'] = -1;
			$match['loser'] = -1;
		}

		return $match[$option];
	}


	/**
	 * get date selection.
	 *
	 * @param int $day
	 * @param int $month
	 * @param int $year
	 * @param int $index default 0
	 * @return string
	 */
	function getDateSelection( $day, $month, $year, $index = 0 )
	{
		$out = '<select size="1" name="day['.$index.']" class="date">';
		$out .= "<option value='00'>".__('Day','leaguemanager')."</option>";
		for ( $d = 1; $d <= 31; $d++ ) {
			$selected = ( $d == $day ) ? ' selected="selected"' : '';
			$out .= '<option value="'.str_pad($d, 2, 0, STR_PAD_LEFT).'"'.$selected.'>'.$d.'</option>';
		}
		$out .= '</select>';
		$out .= '<select size="1" name="month['.$index.']" class="date">';
		$out .= "<option value='00'>".__('Month','leaguemanager')."</option>";
		foreach ( parent::getMonths() AS $key => $m ) {
			$selected = ( $key == $month ) ? ' selected="selected"' : '';
			$out .= '<option value="'.str_pad($key, 2, 0, STR_PAD_LEFT).'"'.$selected.'>'.$m.'</option>';
		}
		$out .= '</select>';
		$out .= '<select size="1" name="year['.$index.']" class="date">';
		$out .= "<option value='0000'>".__('Year','leaguemanager')."</option>";
		for ( $y = date("Y")-20; $y <= date("Y")+10; $y++ ) {
			$selected =  ( $y == $year ) ? ' selected="selected"' : '';
			$out .= '<option value="'.$y.'"'.$selected.'>'.$y.'</option>';
		}
		$out .= '</select>';
		return $out;
	}


	/**
	 * display global settings page (e.g. color scheme options)
	 *
	 * @param none
	 * @return void
	 */
	function displayOptionsPage()
	{
		$options = get_option('leaguemanager');

		$tab = 0;
		if ( isset($_POST['updateLeagueManager']) ) {
			check_admin_referer('leaguemanager_manage-global-league-options');
			$options['colors']['headers'] = htmlspecialchars($_POST['color_headers']);
            $options['colors']['rows'] = array( 'alternate' => htmlspecialchars($_POST['color_rows_alt']), 'main' => htmlspecialchars($_POST['color_rows']), 'ascend' => htmlspecialchars($_POST['color_rows_ascend']), 'descend' => htmlspecialchars($_POST['color_rows_descend']), 'relegation' => htmlspecialchars($_POST['color_rows_relegation']) );
            $options['hide_admin_widgets'] = (isset($_POST['hide_admin_widgets']) ? ((bool) $_POST['hide_admin_widgets']) : false);
			$options['colors']['boxheader'] = array(htmlspecialchars($_POST['color_boxheader1']), htmlspecialchars($_POST['color_boxheader2']));
			$options['dashboard_widget']['num_items'] = intval($_POST['dashboard']['num_items']);
			$options['dashboard_widget']['show_author'] = isset($_POST['dashboard']['show_author']) ? 1 : 0;
			$options['dashboard_widget']['show_date'] = isset($_POST['dashboard']['show_date']) ? 1 : 0;
			$options['dashboard_widget']['show_summary'] = isset($_POST['dashboard']['show_summary']) ? 1 : 0;
			
			update_option( 'leaguemanager', $options );
			parent::setMessage(__( 'Settings saved', 'leaguemanager' ));
			parent::printMessage();
			
			// Set active tab
			$tab = intval($_POST['active-tab']);
		}

		require_once (dirname (__FILE__) . '/settings-global.php');
	}


	/**
	 * add meta box to post screen
	 *
	 * @param object $post
	 * @return none
	 */
	function addMetaBox( $post )
	{
		global $wpdb, $post_ID, $leaguemanager;

		if ( $leagues = $wpdb->get_results( "SELECT `title`, `id` FROM {$wpdb->leaguemanager} ORDER BY id ASC" ) ) {
			$league_id = $season = 0;
			$curr_league = $match = false;
			if ( $post->ID != 0 ) {
				$match = $wpdb->get_results( $wpdb->prepare("SELECT `id`, `league_id`, `season` FROM {$wpdb->leaguemanager_matches} WHERE `post_id` = '%d'", $post->ID) );
				$match = ( isset($match[0]) ) ? $match[0] : '';

				if ( $match ) {
					$match_id = ( $match ) ? $match->id : 0;
					$league_id = $match->league_id;
					$season = $match->season;
					$curr_league = $leaguemanager->getLeague($league_id);
				} else {
					$match_id = 0;
				} 
			}

			echo "<input type='hidden' name='curr_match_id' value='".$match_id."' />";
			echo "<select name='league_id' class='alignleft' id='league_id' onChange='Leaguemanager.getSeasonDropdown(this.value, ".$season.")'>";
			echo "<option value='0'>".__('Choose League','leaguemanager')."</option>";
			foreach ( $leagues AS $league ) {
				$selected = ( $league_id == $league->id ) ? ' selected="selected"' : '';
				echo "<option value='".$league->id."'".$selected.">".$league->title."</option>";
			}
			echo "</select>";

			echo "<div id='seasons'>";
			if ( $match )
				echo $this->getSeasonDropdown($curr_league, $season);
			echo '</div>';
			echo "<div id='matches'>";
			if ( $match )
				echo $this->getMatchDropdown($match);
			echo '</div>';

			echo '<br style="clear: both;" />';
		}
	}


	/**
	 * display Season dropdown
	 *
	 * @param mixed $league
	 * @param mixed $season
	 * @return void|string
	 */
	function getSeasonDropdown( $league = false, $season = false )
	{
		global $leaguemanager;

		if ( !$league ) {
			$league_id = (int)$_POST['league_id'];
			$league = $leaguemanager->getLeague($league_id);
			$ajax = true;
		} else {
			$league_id = $league->id;
			$ajax = false;
		}

		$league->seasons = maybe_unserialize($league->seasons);

		$out = '<select size="1" class="alignleft" id="season" name="season" onChange="Leaguemanager.getMatchDropdown('.$league_id.', this.value);">';
		$out .= '<option value="">'.__('Choose Season', 'leaguemanager').'</option>';
		foreach ( $league->seasons AS $s ) {
			$selected = ( $season == $s['name'] ) ? ' selected="selected"' : '';
			$out .= '<option value="'.$s['name'].'"'.$selected.'>'.$s['name'].'</option>';
		}
		$out .= '</select>';

		if ( !$ajax ) {
			return $out;
		} else {
			die( "jQuery('div#matches').fadeOut('fast', function() {
				jQuery('div#seasons').fadeOut('fast');
				jQuery('div#seasons').html('".addslashes_gpc($out)."').fadeIn('fast');
			});");
		}
	}


	/**
	 * display match dropdown
	 *
	 * @param mixed $match
	 * @return void|string
	 */
	function getMatchDropdown( $match = false )
	{
		global $leaguemanager;

		if ( !$match ) {
			$league_id = intval($_POST['league_id']);
			$season = htmlspecialchars($_POST['season']);
			$match_id = false;
			$ajax = true;
		} else {
			$league_id = $match->league_id;
			$season = $match->season;
			$match_id = $match->id;
			$ajax = false;
		}

		$matches = $leaguemanager->getMatches( array("league_id" => $league_id, "season" => $season, "limit" => false) );
		$teams = $leaguemanager->getTeams( array("league_id" => $league_id, "season" => $season, "orderby" => array("id" => "ASC")), 'ARRAY');

		$out = '<select size="1" name="match_id" id="match_id" class="alignleft">';
		$out .= '<option value="0">'.__('Choose Match', 'leaguemanager').'</option>';
		foreach ( $matches AS $match ) {
			$selected = ( $match_id == $match->id ) ? ' selected="selected"' : '';
			$out .= '<option value="'.$match->id.'"'.$selected.'>'.$teams[$match->home_team]['title'] . ' &#8211; ' . $teams[$match->away_team]['title'].'</option>';
		}
		$out .= '</select>';

		if ( !$ajax ) {
			return $out;
		} else {
			die( "jQuery('div#matches').fadeOut('fast', function() {
				jQuery('div#matches').html('".addslashes_gpc($out)."').fadeIn('fast');
			});");
		}
	}


	/**
	 * update post id for match report
	 *
	 * @param none
	 * @return none
	 */
	function editMatchReport()
	{
		global $wpdb;

		if (isset($_POST['post_ID'])) {
			$post_ID = (int) $_POST['post_ID'];
			$match_ID = isset($_POST['match_id']) ? (int) $_POST['match_id'] : false;
			$curr_match_ID = isset($_POST['curr_match_id']) ? (int) $_POST['curr_match_id'] : false;
			
			if ( $match_ID && $curr_match_ID != $match_ID ) {
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `post_id` = '%d' WHERE `id` = '%d'", $post_ID, $match_ID ) );
				if ( $curr_match_ID != 0 )
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `post_id` = 0 WHERE `id` = '%d'", $curr_match_ID ) );
			}
		}
	}


	/**
	 * import data from CSV file
	 *
	 * @param int $league_id
	 * @param array $file CSV file
	 * @param string $delimiter
	 * @param array $mode 'teams' | 'matches'
	 * @return string
	 */
	function import( $league_id, $file, $delimiter, $mode )
	{
		global $leaguemanager;

		$league_id = intval($league_id);
		if ( $file['size'] > 0 ) {
			/*
			* Upload CSV file to image directory, temporarily
			*/
			$new_file =  ABSPATH.'wp-content/uploads/'.basename($file['name']);
			if ( move_uploaded_file($file['tmp_name'], $new_file) ) {
				$this->league_id = $league_id;
				if ( 'teams' == $mode )
					$this->importTeams($new_file, $delimiter);
				elseif ( 'matches' == $mode )
					$this->importMatches($new_file, $delimiter);
			} else {
				parent::setMessage(sprintf( __('The uploaded file could not be moved to %s.' ), ABSPATH.'wp-content/uploads') );
			}
			@unlink($new_file); // remove file from server after import is done
		} else {
			parent::setMessage( __('The uploaded file seems to be empty', 'leaguemanager'), true );
		}
	}


	/**
	 * import teams from CSV file
	 *
	 * @param string $file
	 * @param string $delimiter
	 */
	function importTeams( $file, $delimiter )
	{
		global $leaguemanager;

		$handle = @fopen($file, "r");
		if ($handle) {
			$league = $leaguemanager->getLeague( $this->league_id );
			if ( "TAB" == $delimiter ) $delimiter = "\t"; // correct tabular delimiter

			$teams = $points_plus = $points_minus = $points2_plus = $points2_minus = $pld = $won = $draw = $lost = $custom = array();

			$i = $x = 0;
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				$line = explode($delimiter, $buffer);

				// ignore header and empty lines
				if ( $i > 0 && count($line) > 1 ) {
					$season = $line[0];
					$team = utf8_encode($line[1]);
					$website = isset($line[2]) ? $line[2] : '';
					$coach = isset($line[3]) ? utf8_encode($line[3]) : '';
					$stadium = isset($line[4]) ? utf8_encode($line[4]) : '';
					$home = isset($line[5]) ? $line[5] : '';
					$group = isset($line[6]) ? $line[6] : '';
					$logo = isset($line[7]) ? basename($line[7]) : '';
					if ( isset($line[15]) ) {
						$roster = explode("_", $line[15]);
						$cat_id = isset($roster[1]) ? $roster[1] : false;
						$roster = array( 'id' => $roster[0], 'cat_id' => $cat_id );
					} else {
						$roster = array( 'id' => '', 'cat_id' => false );
					}
					$profile = 0;

					$custom = apply_filters( 'leaguemanager_import_teams_'.$league->sport, $custom, $line );
					$team_id = $this->addTeam( $this->league_id, $season, $team, $website, $coach, $stadium, $home, $group, $roster, $profile, $custom, $logo, false );

					$teams[$team_id] = $team_id;
					$pld[$team_id] = isset($line[8]) ? $line[8] : 0;
					$won[$team_id] = isset($line[9]) ? $line[9] : 0;
					$draw[$team_id] = isset($line[10]) ? $line[10] : 0;
					$lost[$team_id] = isset($line[11]) ? $line[11] : 0;
					
					if ( isset($line[12]) )
						$points2 = explode(":", $line[12]);
					else
						$points2 = array(0,0);
					
					if ( isset($line[14]) )
						$points = explode(":", $line[14]);
					else
						$points = array(0,0);
					
					$points_plus[$team_id] = $points[0];
					$points_minus[$team_id] = $points[1];
					$custom[$team_id]['points2'] = array( 'plus' => $points2[0], 'minus' => $points2[1] );

					$x++;
				}
				$i++;
			}

			$this->saveStandingsManually($teams, $points_plus, $points_minus, $pld, $won, $draw, $lost, 0, $custom);

			fclose($handle);

			parent::setMessage(sprintf(__( '%d Teams imported', 'leaguemanager' ), $x));
		}
	}


	/**
	 * import matches from CSV file
	 *
	 * @param string $file
	 * @param string $delimiter
	 */
	function importMatches( $file, $delimiter )
	{
		global $leaguemanager;

		$handle = @fopen($file, "r");
		if ($handle) {
			if ( "TAB" == $delimiter ) $delimiter = "\t"; // correct tabular delimiter

			$league = $leaguemanager->getLeague( $this->league_id );

			$matches = $home_points = $away_points = $home_teams = $away_teams = $custom = array();

			$i = $x = 0;
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				$line = explode($delimiter, $buffer);

				// ignore header and empty lines
				if ( $i > 0 && count($line) > 1 ) {
					$date = ( !empty($line[6]) ) ? $line[0]." ".$line[6] : $line[0]. " 00:00";
					$season = $this->season = isset($line[1]) ? $line[1] : '';
					$match_day = isset($line[2]) ? $line[2] : '';
					$date = trim($date);
					$home_team = $this->getTeamID(utf8_encode($line[3]));
					$away_team = $this->getTeamID(utf8_encode($line[4]));
					$location = isset($line[5]) ? utf8_encode($line[5]) : '';
					$group = isset($line[7]) ? $line[7] : '';

					$match_id = $this->addMatch($date, $home_team, $away_team, $match_day, $location, $this->league_id, $season, $group,'', array());

					$matches[$match_id] = $match_id;
					$home_teams[$match_id] = $home_team;
					$away_teams[$match_id] = $away_team;
					if ( isset($line[8]) && !empty($line[8]) ) {
						$score = explode(":", $line[8]);
						$home_points[$match_id] = $score[0];
						$away_points[$match_id] = $score[1];
					} else {
						$home_points[$match_id] = $away_points[$match_id] = '';
					}
					
					$custom = apply_filters( 'leaguemanager_import_matches_'.$league->sport, $custom, $line, $match_id );

					$x++;
				}

				$i++;
			}
			$this->updateResults( $league->id, $matches, $home_points, $away_points, $home_teams, $away_teams, $custom, false );

			fclose($handle);

			parent::setMessage(sprintf(__( '%d Matches imported', 'leaguemanager' ), $x));
		}
	}


	/**
	 * get Team ID for given string
	 *
	 * @param string $title
	 * @return int
	 */
	function getTeamID( $title )
	{
		global $wpdb;

		$team = $wpdb->get_results( $wpdb->prepare("SELECT `id` FROM {$wpdb->leaguemanager_teams} WHERE `title` = '%s' AND `league_id` = '%d' AND `season` = '%s'", $title, $this->league_id, $this->season) );
		return $team[0]->id;
	}


	/**
	 * Checks if a particular user has a role.
	 * Returns true if a match was found.
	 *
	 * @param string $role Role name.
	 * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
	 * @return bool
	 *
	 * put together by AppThemes (http://docs.appthemes.com/tutorials/wordpress-check-user-role-function/)
	 */
	function checkUserRole( $role, $user_id = null ) {

		if ( is_numeric( $user_id ) )
			$user = get_userdata( $user_id );
		else
			$user = wp_get_current_user();

		if ( empty( $user ) )
			return false;

		return in_array( $role, (array) $user->roles );
	}


	/**
	 * export league data
	 *
	 * @param int $league_id
	 * @param string $mode
	 * @return file
	 */
	function export( $league_id, $mode )
	{
		global $leaguemanager;

		//if ( $this->checkUserRole('league_manager') ) {
			$this->league_id = (int)$league_id;
			$this->league = $leaguemanager->getLeague($this->league_id);
			$filename = sanitize_title($this->league->title)."-".$mode."_".date("Y-m-d").".xls";

			if ( 'teams' == $mode )
				$contents = $this->exportTeams();
			elseif ( 'matches' ==  $mode )
				$contents = $this->exportMatches();

			header('Content-Type: text/csv');
			header('Content-Disposition: inline; filename="'.$filename.'"');
			echo $contents;
			exit();
		//}
	}


	/**
	 * export teams
	 *
	 * @param none
	 * @return string
	 */
	function exportTeams()
	{
		global $leaguemanager;

		$league = $this->league;

		$teams = parent::getTeams( array("league_id" => $this->league_id) );

		if ( $teams ) {
			$contents = __('Season','leaguemanager')."\t".
			__('Team','leaguemanager')."\t".
			__('Website','leaguemanager')."\t".
			__('Coach','leaguemanager')."\t".
			__('Stadium','leaguemanager')."\t".
			__('Home Team','leaguemanager') ."\t".
			__('Group','leaguemanager')."\t".
			__('Logo','leaguemanager')."\t".
			__('Pld|Played','leaguemanager')."\t".
			__('W|Won','leaguemanager')."\t".
			__('T|Tie','leaguemanager')."\t".
			__('L|Lost','leaguemanager')."\t".
			__('Points2', 'leaguemanager')."\t".
			__('Diff','leaguemanager')."\t".
			__('Pts','leaguemanager')."\t".
			__('Roster', 'leaguemanager');

			$contents = apply_filters( 'leaguemanager_export_teams_header_'.$league->sport, $contents );

			foreach ( $teams AS $team ) {
				$home = ( $team->home == 1 ) ? 1 : 0;
				$contents .= "\n".$team->season."\t"
				.utf8_decode($team->title)."\t"
				.$team->website."\t"
				.utf8_decode($team->coach)."\t"
				.utf8_decode($team->stadium)."\t"
				.$team->home."\t"
				.$team->group."\t"
				.basename($team->logo)."\t"
				.$team->done_matches."\t"
				.$team->won_matches."\t"
				.$team->draw_matches."\t"
				.$team->lost_matches."\t"

				.sprintf("%d:%d",$team->points2_plus, $team->points2_minus)."\t".$team->diff."\t".sprintf("%d:%d", $team->points_plus, $team->points_minus);
				
				if ( isset($team->roster) && $team->roster['id'] != "" ) {
					$roster = $team->roster['id'];
					if ( isset($team->roster['cat_id']) && $team->roster['cat_id'] != "" ) $roster .= "-".$team->roster['cat_id'];
				} else {
					$roster = "";
				}
				$contents .= "\t".$roster;

				$contents = apply_filters( 'leaguemanager_export_teams_data_'.$league->sport, $contents, $team );
			}
			return $contents;
		}
		return false;
	}


	/**
	 * export matches
	 *
	 * @param none
	 * @return string
	 */
	function exportMatches()
	{
		global $leaguemanager;

		$matches = parent::getMatches( array("league_id" => $this->league_id) );
		if ( $matches ) {
	  	$league = $this->league;
			$teams = parent::getTeams( array("league_id" => $this->league_id, "orderby" => array("id" => "ASC")), 'ARRAY' );

			// Build header
			$contents =
			__('Date','leaguemanager')."\t".
            __('Season','leaguemanager')."\t".
            __('Match Day','leaguemanager')."\t".
            __('Home','leaguemanager')."\t".
            __('Guest','leaguemanager')."\t".
            __('Location','leaguemanager')."\t".
            __('Begin','leaguemanager')."\t".
            __('Group','leaguemanager')."\t".
            __('Score','leaguemanager');

			$contents = apply_filters( 'leaguemanager_export_matches_header_'.$league->sport, $contents );

			foreach ( $matches AS $match ) {
				$contents .= "\n".mysql2date('Y-m-d', $match->date)."\t".
				$match->season."\t".
				$match->match_day."\t".
				utf8_decode($teams[$match->home_team]['title'])."\t".
				utf8_decode($teams[$match->away_team]['title'])."\t".
				utf8_decode($match->location)."\t".
				mysql2date("H:i", $match->date)."\t".
				$match->group."\t";

				$contents .= !empty($match->home_points) ? sprintf("%d:%d",$match->home_points, $match->away_points) : '';
				$contents = apply_filters( 'leaguemanager_export_matches_data_'.$league->sport, $contents, $match );
			}

			return $contents;
		}

		return false;
	}

	function htmlspecialchars_array($arr = array()) {
		$rs =  array();
		while(list($key,$val) = each($arr)) {
			if(is_array($val)) {
				$rs[$key] = $this->htmlspecialchars_array($val);
			} else {
				$rs[$key] = htmlspecialchars($val, ENT_QUOTES);
			}   
		}
		return $rs;
	}
	
	
	function showDatabaseColumns()
	{
		global  $wpdb;
		
		$tables = array($wpdb->leaguemanager, $wpdb->leaguemanager_teams, $wpdb->leaguemanager_matches, $wpdb->leaguemanager_stats);
		
		foreach( $tables AS $table ) {
			$results = $wpdb->get_results("SHOW COLUMNS FROM {$table}");
			$columns = array();
			foreach ( $results AS $result ) {
				$columns[] = "<li>".$result->Field." ".$result->Type.", NULL: ".$result->Null.", Default: ".$result->Default.", Extra: ".$result->Extra."</li>";
			}
			echo "<p>Table ".$table."<ul>";
			echo implode("", $columns);
			echo "</ul></p>";
		}
	}
}
?>
