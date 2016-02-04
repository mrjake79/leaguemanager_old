<?php
/*
Plugin Name: LeagueManager
Plugin URI: http://wordpress.org/extend/plugins/leaguemanager/
Description: Manage and present sports league results.
Version: 4.0.6
Author: Kolja Schleich, LaMonte Forthun

Copyright 2008-2016  Kolja Schleich  (email : kolja.schleich@googlemail.com)
					 LaMonte Forthun (email : lamontef@collegefundsoftware.com, lamontef@yahoo.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
* Loading class for the WordPress plugin LeagueManager
*
* @author 	Kolja Schleich, LaMonte Forthun
* @package	LeagueManager
* @copyright Copyright 2008-2015
*/
class LeagueManagerLoader
{
	/**
	 * plugin version
	 *
	 * @var string
	 */
	var $version = '4.0.6';


	/**
	 * database version
	 *
	 * @var string
	 */
	var $dbversion = '3.7.4';


	/**
	 * check if bridge to projectmanager is active
	 *
	 * @var boolean
	 */
	var $bridge = false;


	/**
	 * admin Panel object
	 *
	 * @var object
	 */
	var $adminPanel;


	/**
	 * constructor
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		global $leaguemanager, $lmStats, $wpdb, $championship;
		$wpdb->show_errors();
		$this->loadOptions();
		$this->defineConstants();
		$this->defineTables();
		$this->loadTextdomain();
		$this->loadLibraries();

		register_activation_hook(__FILE__, array(&$this, 'activate') );

		if (function_exists('register_uninstall_hook'))
			register_uninstall_hook(__FILE__, array('LeagueManagerLoader', 'uninstall'));

		add_action( 'widgets_init', array(&$this, 'registerWidget') );
		
		add_action('wp_enqueue_scripts', array(&$this, 'loadStyles'), 5 );
		add_action('wp_enqueue_scripts', array(&$this, 'loadScripts') );
		
		// Add TinyMCE Button
		add_action( 'init', array(&$this, 'addTinyMCEButton') );
		add_filter( 'tiny_mce_version', array(&$this, 'changeTinyMCEVersion') );
		// register AJAX action to show TinyMCE Window
		add_action( 'wp_ajax_leaguemanager_tinymce_window', array(&$this, 'showTinyMCEWindow') );
		
		// Start this plugin once all other plugins are fully loaded
		//add_action( 'plugins_loaded', array(&$this, 'initialize') );

		// Enable matches in Fancy slideshows
		add_filter( 'fancy_slideshow_sources', array( &$this, 'addSlideshowSources' ) );
		add_filter( 'fancy_slideshow_overlay_styles', array( &$this, 'addSlideshowOverlayStyles' ) );
		add_filter( 'fancy_slideshow_get_slides_matches', array(&$this, 'getSlideshowSlides') );
		
		$leaguemanager = new LeagueManager( $this->bridge );
		$championship = new LeagueManagerChampionship();
		$lmStats = new LeagueManagerStats();

		if ( is_admin() )
			$this->adminPanel = new LeagueManagerAdminPanel();
	}
	function LeagueManagerLoader()
	{
		$this->__construct();
	}


	/**
	 * Add individual league matches to slideshow categories
	 *
	 * @param string $categories
	 * @return string
	 */
	function addSlideshowSources( $categories )
	{
		global $leaguemanager;
		
		$categories['leagues_matches'] = array( "title" => __( 'League Matches', 'leaguemanager'), "options" => array() );
		foreach ( $leaguemanager->getLeagues() AS $league ) {
			$categories["leagues_matches"]["options"][] = array( "value" => "matches_".$league->id, "label" => $league->title );
			// Include Options for next and previous matches
			$times = array( 'next' => __('Next Matches', 'leaguemanager'), 'prev' => __('Previous Matches', 'leaguemanager') );
			foreach ( $times AS $time => $name ) {
				$categories["leagues_matches"]["options"][] = array( "value" => sprintf("matches_%d_%s", $league->id, $time), "label" => sprintf("%s - %s", $league->title, $name) );
			}
		}
		
		return $categories;
	}
	
	
	/**
	 * add custom league slideshow overlay style
	 *
	 * @param array $styles
	 * @return array
	 */
	function addSlideshowOverlayStyles( $styles )
	{
		$styles['leaguemanager'] = __( 'LeagueManager', 'leaguemanager' );
		return $styles;
	}
	
	
	/**
	 * retrieve Slideshow Slideshow
	 *
	 * @param array $category
	 * @return array
	 */
	function getSlideshowSlides( $category )
	{
		global $leaguemanager;
		
		$league = $leaguemanager->getLeague( $category[1] );
		
		if ( $category[0] == 'matches' ) {
			$show_logos = ( $league->slideshow['show_logos'] == 1 ) ? true : false;
			$time = isset($category[2]) ? $category[2] : false;
			$limit = ( $league->slideshow['num_matches'] > 0 ) ? intval($league->slideshow['num_matches']) : false;
			$latest_season = end($league->seasons);
			$season = ( $league->slideshow['season'] == 'latest' ) ? $latest_season['name'] : $league->slideshow['season'];
			$matches = $leaguemanager->getMatches( array('league_id' => $league->id, 'season' => $season, 'time' => $time, 'limit' => $limit) );
			$team_args = array("league_id" => $league->id, "season" => false, "orderby" => array("id" => "ASC"));
			//if ( !empty($instance['group']) ) $team_args["group"] = $instance['group'];
			
			$teams = $leaguemanager->getTeams( $team_args, 'ARRAY' );
			
			$slides = array();
			if ( $matches ) {
				foreach ( $matches AS $match ) {
					$title = $leaguemanager->getMatchTitle($match->id, $show_logos, $match);
					
					if ( $time == 'next' ) {
						$score = ' v.s. ';
					} else {
						if ( $match->home_points == "" && $match->away_points == "" ) {
							$score = "N/A";
						} else {
							if ( $match->hadPenalty )
								$score = sprintf($league->point_format, $match->penalty['home'], $match->penalty['away'])." "._x( 'o.P.', 'leaguemanager' );
							elseif ( $match->hadOvertime )
								$score = sprintf($league->point_format, $match->overtime['home'], $match->overtime['away'])." "._x( 'AET', 'leaguemanager' );
							else
								$score = sprintf($league->point_format, $match->home_points, $match->away_points);
						}
						
						$score = "<span class='result'>".$score."</span>";
					}
					
					$home_team = $teams[$match->home_team]['title'];
					$away_team = $teams[$match->away_team]['title'];
					if ( !isset($match->title) ) $match->title = sprintf("%s &#8211; %s", $home_team, $away_team);
					
					$slide_title = "";
					$slide_title .= "<p class='match_title'><strong>". $match->title."</strong></p>";
					if ( $show_logos )
						$slide_title .= "<p class='logos'><img class='logo home_logo' src='".$teams[$match->home_team]['logo']."' alt='' />".$score."<img class='logo away_logo' src='".$teams[$match->away_team]['logo']."' alt='' /></p>";
					else
						$slide_title .= "<p class='logos'>".$score."</p>";

					$slide_desc = "";
					if ( !empty($match->match_day) )
						$slide_desc .= "<p class='match_day'>".sprintf(__("<strong>%d.</strong> Match Day", 'leaguemanager'), $match->match_day)."</p>";
					
					$slide_desc .= "<p class='date'>".mysql2date(get_option('date_format'), $match->date).", <span class='time'>".$match->time."</span></p>";
					$slide_desc .= "<p class='location'>".$match->location."</p>";
				
					if ( $match->post_id != 0 )
						$slide_desc .=  "<p class='report'><a href='".get_permalink($match->post_id)."'>".__( 'Report', 'leaguemanager' )."</a></p>";
				
					$slide = array(
						"name" => $title,
						"image" => '', // DEPRECATED
						"imageurl" => '',
						"imagepath" => '',
						"url" => '',
						"url_target" => '',
						"link_class" => '',
						"link_rel" => '',
						"title" => $title,
						"slide_title" => $slide_title,
						"slide_desc" => $slide_desc
					);
					
					$slides[] = (object)$slide;
				}
			}
			
			return $slides;
		}
		
		return array();
	}
	
	
	/**
	 * initialize plugin
	 *
	 * @param none
	 * @return void
	 */
	function initialize()
	{
		// Add TinyMCE Button
		add_action( 'init', array(&$this, 'addTinyMCEButton') );
		add_filter( 'tiny_mce_version', array(&$this, 'changeTinyMCEVersion') );
	}


	/**
	 * register Widget
	 */
	function registerWidget()
	{
		register_widget('LeagueManagerWidget');
	}


	/**
	 * define constants
	 *
	 * @param none
	 * @return void
	 */
	function defineConstants()
	{
		define( 'LEAGUEMANAGER_VERSION', $this->version );
		define( 'LEAGUEMANAGER_DBVERSION', $this->dbversion );
		define( 'LEAGUEMANAGER_URL', rtrim(esc_url(plugin_dir_url(__FILE__)), "/") ); // remove trailing slash as the plugin has been coded without it
		define( 'LEAGUEMANAGER_PATH', dirname(__FILE__) );
	}


	/**
	 * define database tables
	 *
	 * @param none
	 * @return void
	 */
	function defineTables()
	{
		global $wpdb;
		$wpdb->leaguemanager = $wpdb->prefix . 'leaguemanager_leagues';
		$wpdb->leaguemanager_teams = $wpdb->prefix . 'leaguemanager_teams';
		$wpdb->leaguemanager_matches = $wpdb->prefix . 'leaguemanager_matches';
		$wpdb->leaguemanager_stats = $wpdb->prefix . 'leaguemanager_stats';
	}


	/**
	 * load libraries
	 *
	 * @param none
	 * @return void
	 */
	function loadLibraries()
	{
		global $lmShortcodes, $lmAJAX;

		// Global libraries
		require_once (dirname (__FILE__) . '/lib/core.php');
		require_once (dirname (__FILE__) . '/lib/ajax.php');
		require_once (dirname (__FILE__) . '/lib/stats.php');
		require_once (dirname (__FILE__) . '/lib/shortcodes.php');
		require_once (dirname (__FILE__) . '/lib/widget.php');
		require_once (dirname (__FILE__) . '/functions.php');
		require_once (dirname (__FILE__) . '/lib/championship.php');
		require_once (dirname (__FILE__) . '/lib/tiebreakers.php');
		$this->loadSports();
		$lmAJAX = new LeagueManagerAJAX();

		if ( is_admin() ) {
			require_once (dirname (__FILE__) . '/lib/image.php');
			require_once (dirname (__FILE__) . '/admin/admin.php');
		}

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		// define bridge to ProjectManager
		if ( file_exists(WP_PLUGIN_DIR . '/projectmanager/projectmanager.php') && is_plugin_active("projectmanager/projectmanager.php") ) {
			$p = get_option('projectmanager');
			if (version_compare($p['version'], '2.4.7', '>=')) {
				global $lmBridge;
				require_once(dirname (__FILE__) . '/lib/bridge.php');
				$lmBridge = new LeagueManagerBridge();
				$this->bridge = true;
			}
		}
		
		$lmShortcodes = new LeagueManagerShortcodes($this->bridge);
	}


	/**
	 * load sport types
	 *
	 * @param none
	 * @return void
	 */
	function loadSports()
	{
		$files = array();
		
		// First save default sport files in array indexed by filename
		$dir = LEAGUEMANAGER_PATH."/sports";
		if ( $handle = opendir($dir) ) {
			while ( false !== ($file = readdir($handle)) ) {
				$file_info = pathinfo($dir.'/'.$file);
				$file_type = (isset($file_info['extension'])) ? $file_info['extension'] : '';
				if ( $file != "." && $file != ".." && !is_dir($file) && substr($file, 0,1) != "."  && $file_type == 'php' )  {
					//require_once($dir.'/'.$file);
					$files[$file] = $dir.'/'.$file;
				}
			}
		}
		
		// Save sport files in user stylesheet directory indexed by filename, thus overwriting original files with the same name
		$dir = get_stylesheet_directory() . "/sports";
		if ( file_exists($dir) ) {
			if ( $handle = opendir($dir) ) {
				while ( false !== ($file = readdir($handle)) ) {
				$file_info = pathinfo($dir.'/'.$file);
				$file_type = (isset($file_info['extension'])) ? $file_info['extension'] : '';
				if ( $file != "." && $file != ".." && !is_dir($file) && substr($file, 0,1) != "."  && $file_type == 'php' )  {
					$files[$file] = $dir.'/'.$file;
				}
			}
			}
		}
		
		// load files
		foreach ( $files AS $file ) {
			require_once($file);
		}
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
	 * @param boolean $index (optional)
	 * @return void
	 */
	function getOptions($index = false)
	{
		if ( $index )
			return $this->options[$index];
		
		return $this->options;
	}


	/**
	 * load textdomain
	 *
	 * @param none
	 * @return void
	 */
	function loadTextdomain()
	{
		global $leaguemanager;

		$textdomain = $this->getOptions('textdomain');
		if ( !empty($textdomain) ) {
			$locale = get_locale();
			$path = dirname(__FILE__) . '/languages';
			$domain = 'leaguemanager';
			$mofile = $path . '/'. $domain . '-' . $textdomain . '-' . $locale . '.mo';

			if ( file_exists($mofile) ) {
				load_textdomain($domain, $mofile);
				return true;
			}
		}
		
		load_plugin_textdomain( 'leaguemanager', false, 'leaguemanager/languages' );
	}


	/**
	 * load scripts
	 *
	 * @param none
	 * @return void
	 */
	function loadScripts()
	{
		wp_register_script( 'leaguemanager', LEAGUEMANAGER_URL.'/leaguemanager.js', array('jquery', 'jquery-ui-core', 'jquery-ui-accordion', 'jquery-ui-tabs', 'jquery-effects-core', 'jquery-effects-slide', 'sack', 'thickbox'), LEAGUEMANAGER_VERSION );
		wp_enqueue_script('leaguemanager');
		?>
		<script type="text/javascript">
		//<![CDATA[
		LeagueManagerAjaxL10n = {
			blogUrl: "<?php bloginfo( 'wpurl' ); ?>",
			//pluginPath: "<?php echo LEAGUEMANAGER_PATH; ?>",
			pluginUrl: "<?php echo LEAGUEMANAGER_URL; ?>",
			requestUrl: "<?php echo LEAGUEMANAGER_URL ?>/ajax.php",
			Edit: "<?php _e("Edit"); ?>",
			Post: "<?php _e("Post"); ?>",
			Save: "<?php _e("Save"); ?>",
			Cancel: "<?php _e("Cancel"); ?>",
			pleaseWait: "<?php _e("Please wait..."); ?>",
			Revisions: "<?php _e("Page Revisions"); ?>",
			Time: "<?php _e("Insert time"); ?>",
			Options: "<?php _e("Options") ?>",
			Delete: "<?php _e('Delete') ?>"
	 	}
		//]]>
		</script>
		<?php
	}


	/**
	 * load styles
	 *
	 * @param none
	 * @return void
	 */
	function loadStyles()
	{
		wp_enqueue_style('thickbox');
		wp_enqueue_style('leaguemanager', LEAGUEMANAGER_URL . "/style.css", false, '1.0', 'all');
		
		wp_register_style('jquery-ui', LEAGUEMANAGER_URL . "/css/jquery/jquery-ui.min.css", false, '1.11.4', 'all');
		wp_register_style('jquery-ui-structure', LEAGUEMANAGER_URL . "/css/jquery/jquery-ui.structure.min.css", array('jquery-ui'), '1.11.4', 'all');
		wp_register_style('jquery-ui-theme', LEAGUEMANAGER_URL . "/css/jquery/jquery-ui.theme.min.css", array('jquery-ui', 'jquery-ui-structure'), '1.11.4', 'all');
		
		wp_enqueue_style('jquery-ui-structure');
		wp_enqueue_style('jquery-ui-theme');
		
		//wp_register_style('jquery_ui_css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css', false, '1.0', 'screen');
		//wp_enqueue_style('jquery_ui_css');

		ob_start();
		require_once(LEAGUEMANAGER_PATH.'/css/colors.css.php');
		$css = ob_get_contents();
		ob_end_clean();
		
		wp_add_inline_style( 'leaguemanager', $css );
	}


	/**
	 * add TinyMCE Button
	 *
	 * @param none
	 * @return void
	 */
	function addTinyMCEButton()
	{
		// Don't bother doing this stuff if the current user lacks permissions
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;

		// Check for LeagueManager capability
		if ( !current_user_can('manage_leaguemanager') ) return;

		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", array(&$this, 'addTinyMCEPlugin'));
			add_filter('mce_buttons', array(&$this, 'registerTinyMCEButton'));
		}
	}
	function addTinyMCEPlugin( $plugin_array )
	{
		$plugin_array['LeagueManager'] = LEAGUEMANAGER_URL.'/admin/tinymce/editor_plugin.js';
		return $plugin_array;
	}
	function registerTinyMCEButton( $buttons )
	{
		array_push($buttons, "separator", "LeagueManager");
		return $buttons;
	}
	function changeTinyMCEVersion( $version )
	{
		return ++$version;
	}

	/**
	 * Display the TinyMCE Window.
	 *
	 */
	function showTinyMCEWindow() {
		require_once( LEAGUEMANAGER_PATH . '/admin/tinymce/window.php' );
		exit;
	}
	
	
	/**
	 * Activate plugin
	 *
	 * @param none
	 */
	function activate()
	{
		global $leaguemanager;
		
		$options = array();
		$options['version'] = $this->version;
		$options['dbversion'] = $this->dbversion;
		$options['textdomain'] = 'default';
		$options['colors']['headers'] = '#dddddd';
		$options['colors']['rows'] = array( 'main' => '#ffffff', 'alternate' => '#efefef', 'ascend' => '#ffffff', 'descend' => '#ffffff', 'relegate' => '#ffffff');
		$options['dashboard_widget']['num_items'] = 4;
		$options['dashboard_widget']['show_author'] = 1;
		$options['dashboard_widget']['show_date'] = 1;
		$options['dashboard_widget']['show_summary'] = 1;
 
		add_option( 'leaguemanager', $options, '', 'yes' );
		add_option( 'leaguemanager_widget', array(), '', 'yes' );

		// create directory
		wp_mkdir_p($leaguemanager->getImagePath(false, true));
		
		/*
		* Set Capabilities
		*/
		$role = get_role('administrator');
		if ( $role !== null ) {
			$role->add_cap('manage_leaguemanager');
			$role->add_cap('league_manager');
		}
		
		$role = get_role('editor');
		if ( $role !== null ) {
			$role->add_cap('league_manager');
		}

		$this->install();
	}



	function install()
	{
		global $wpdb;
		include_once( ABSPATH.'/wp-admin/includes/upgrade.php' );

		$charset_collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}

		$create_leagues_sql = "CREATE TABLE {$wpdb->leaguemanager} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT,
						`title` varchar( 100 ) NOT NULL default '',
						`settings` longtext NOT NULL,
						`seasons` longtext NOT NULL,
						PRIMARY KEY ( `id` )) $charset_collate;";
		maybe_create_table( $wpdb->leaguemanager, $create_leagues_sql );

		$create_teams_sql = "CREATE TABLE {$wpdb->leaguemanager_teams} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`status` varchar( 50 ) NOT NULL default '&#8226;',
						`title` varchar( 100 ) NOT NULL default '',
						`logo` varchar( 150 ) NOT NULL default '',
						`website` varchar( 255 ) NOT NULL default '',
						`coach` varchar( 100 ) NOT NULL default '',
						`stadium` varchar( 150 ) NOT NULL default '',
						`home` tinyint( 1 ) NOT NULL default '0',
						`points_plus` float NOT NULL default '0',
						`points_minus` float NOT NULL default '0',
						`points2_plus` int( 11 ) NOT NULL default '0',
						`points2_minus` int( 11 ) NOT NULL default '0',
						`add_points` int( 11 ) NOT NULL default '0',
						`done_matches` int( 11 ) NOT NULL default '0',
						`won_matches` int( 11 ) NOT NULL default '0',
						`draw_matches` int( 11 ) NOT NULL default '0',
						`lost_matches` int( 11 ) NOT NULL default '0',
						`diff` int( 11 ) NOT NULL default '0',
						`group` varchar( 30 ) NOT NULL default '',
						`league_id` int( 11 ) NOT NULL,
						`season` varchar( 255 ) NOT NULL default '',
						`rank` int( 11 ) NOT NULL default '0',
						`roster` longtext NOT NULL default '',
						`profile` int( 11 ) NOT NULL default '0',
						`custom` longtext NOT NULL,
						PRIMARY KEY ( `id` )) $charset_collate;";
		maybe_create_table( $wpdb->leaguemanager_teams, $create_teams_sql );

		$create_matches_sql = "CREATE TABLE {$wpdb->leaguemanager_matches} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`group` varchar( 30 ) NOT NULL default '',
						`date` datetime NOT NULL default '0000-00-00',
						`home_team` varchar( 255 ) NOT NULL default '0',
						`away_team` varchar( 255 ) NOT NULL default '0',
						`match_day` tinyint( 4 ) NOT NULL default '0',
						`location` varchar( 100 ) NOT NULL default '',
						`league_id` int( 11 ) NOT NULL default '0',
						`season` varchar( 255 ) NOT NULL default '',
						`home_points` varchar( 30 ) NULL default NULL,
						`away_points` varchar( 30 ) NULL default NULL,
						`winner_id` int( 11 ) NOT NULL default '0',
						`loser_id` int( 11 ) NOT NULL default '0',
						`post_id` int( 11 ) NOT NULL default '0',
						`final` varchar( 150 ) NOT NULL default '',
						`custom` longtext NOT NULL,
						PRIMARY KEY ( `id` )) $charset_collate;";
		maybe_create_table( $wpdb->leaguemanager_matches, $create_matches_sql );

		$create_stats_sql = "CREATE TABLE {$wpdb->leaguemanager_stats} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT,
						`name` varchar( 30 ) NOT NULL default '',
						`fields` longtext NOT NULL,
						`league_id` int( 11 ) NOT NULL,
						PRIMARY KEY ( `id` )) $charset_collate;";
		maybe_create_table( $wpdb->leaguemanager_stats, $create_stats_sql );
	}


	/**
	 * Uninstall Plugin
	 *
	 * @param none
	 */
	static function uninstall()
	{
		global $wpdb, $leaguemanager;

		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager_matches}" );
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager_teams}" );
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager_stats}" );
		$wpdb->query( "DROP TABLE {$wpdb->leaguemanager}" );

		delete_option( 'leaguemanager_widget' );
		delete_option( 'leaguemanager' );

		/*
		* Set Capabilities
		*/
		$role = get_role('administrator');
		if ( $role !== null ) {
			$role->remove_cap('manage_leaguemanager');
			$role->remove_cap('league_manager');
		}
		
		$role = get_role('editor');
		if ( $role !== null ) {
			$role->remove_cap('league_manager');
		}
		
		// Delete upload directory
		$leaguemanager->removeDir($leaguemanager->getImagePath(false, true));
	}
	
	
	/**
	 * get admin object
	 *
	 * @param none
	 * @return object
	 */
	function getAdminPanel()
	{
		return $this->adminPanel;
	}
}

/**
 * Checks if a particular user has a role.
 * Returns true if a match was found.
 *
 * @param string $role Role name.
 * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
 * @return boolean
 *
 * Put together by AppThemes (http://docs.appthemes.com/tutorials/wordpress-check-user-role-function/)
 */
/*function leaguemanager_check_user_role( $role, $user_id = null ) {

    if ( is_numeric( $user_id ) )
		$user = get_userdata( $user_id );
    else
        $user = wp_get_current_user();

    if ( empty( $user ) )
		return false;

    return in_array( $role, (array) $user->roles );
}*/

// Run the Plugin
global $lmLoader;
$lmLoader = new LeagueManagerLoader();

// suppress output
if ( isset($_POST['leaguemanager_export']) )
	ob_start();
?>
