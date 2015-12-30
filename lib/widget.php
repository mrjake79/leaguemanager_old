<?php
/** Widget class for the WordPress plugin LeagueManager
* 
* @author 	Kolja Schleich
* @package	LeagueManager
* @copyright 	Copyright 2008-2009
*/

class LeagueManagerWidget extends WP_Widget
{
	/**
	 * index for matches in widget
	 *
	 * @var array
	 */
	var $match_index = array( 'next' => 0, 'prev' => 0 );


	/**
	 * initialize
	 *
	 * @param none
	 * @return void
	 */
	function __construct( $template = false )
	{
		add_action( 'leaguemanager_widget_next_match', array(&$this, 'showNextMatchBox'), 10, 3 );
		add_action( 'leaguemanager_widget_prev_match', array(&$this, 'showPrevMatchBox'), 10, 3 );

		if ( !$template ) {
			$widget_ops = array('classname' => 'leaguemanager_widget', 'description' => __('League results and upcoming matches at a glance.', 'leaguemanager') );
			parent::__construct('leaguemanager-widget', __( 'League Manager', 'leaguemanager' ), $widget_ops);
		}
		return;
	}
	function LeagueManagerWidget( $template = false )
	{
		$this->__construct($template);
	}
	
	
	/**
	 * get index for current match
	 *
	 * @param string $type next|prev
	 * @return the index
	 */
	function getMatchIndex( $type )
	{
		return $this->match_index[$type];
	}
	
	
	/**
	 * set index for current match
	 *
	 * @param int $index
	 * @param string $type
	 * @return void
	 */
	function setMatchIndex( $index, $type )
	{
		$this->match_index[$type] = $index;
	}
	
		
	/**
	 * displays widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance )
	{
		global $lmBridge, $lmShortcodes, $leaguemanager;

		$defaults = array(
			'before_widget' => '<li id="'.sanitize_title(get_class($this)).'" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'number' => $this->number,
		);
		$args = array_merge( $defaults, $args );
		extract( $args , EXTR_SKIP );
	
		$league = $leaguemanager->getLeague( $instance['league'] );
		if (empty($instance['season']) ) {
            $season = $leaguemanager->getSeason($league, false, 'name');
		} else {
		    $season = $instance['season'];
		}
		
		if ( empty($instance['group']) ) {
			echo $before_widget . $before_title .  $league->title . ' - ' . __('Season', 'leaguemanager') . " " . $season . $after_title;
		} else {
			echo $before_widget . $before_title . $league->title . ' - ' . __('Season', 'leaguemanager') . " " . $season . " - " . __('Group', 'leaguemanager') . " " . $instance['group'] . $after_title;
		}
				
		echo "<div class='leaguemanager_widget_content'>";
		if ( $instance['match_display'] != 'none' ) {
			$show_prev_matches = $show_next_matches = false;
			if ( $instance['match_display'] == 'prev' )
				$show_prev_matches = true;
			elseif ( $instance['match_display'] == 'next' )
				$show_next_matches = true;
			elseif ( $instance['match_display'] == 'all' )
				$show_prev_matches = $show_next_matches = true;
			
			if ( $show_next_matches ) {
				echo "<div id='next_matches_".$number."'>";
				do_action( 'leaguemanager_widget_next_match', $number, $instance );
				echo "</div>";
			}

			if ( $show_prev_matches ) {
				echo "<div id='prev_matches_".$number."'>";
				do_action( 'leaguemanager_widget_prev_match', $number, $instance );
				echo "</div>";
			}
	
		}
		
		if ( $instance['table'] != 'none' && !empty($instance['table']) ) {
			if( empty($instance['show_logos']) ) {
				$show_logos = "false";
			} else {
				$show_logos = ( $instance['show_logos'] ) ? "true" : "false";
			}
			echo "<h4 class='standings'>". __( 'Table', 'leaguemanager' ). "</h4>";
			echo $lmShortcodes->showStandings( array('template' => $instance['table'], 'league_id' => $instance['league'], 'group' => $instance['group'], 'season' => $instance['season'], 'logo' => $show_logos), true );
		}

		echo "</div>";
		echo $after_widget;
	}


	/**
	 * show next match box
	 *
	 * @param int $number
	 * @param array $instance
	 * @param boolean $echo (optional)
	 * @return void
	 */
	function showNextMatchBox($number, $instance, $echo = true)
	{
		global $leaguemanager;

		$league = $leaguemanager->getLeague($instance['league']);
		$match_limit = ( is_numeric($instance['match_limit']) && intval($instance['match_limit']) > 0 ) ? intval($instance['match_limit']) : false;			
		$match_args = array("league_id" => $instance['league'], "final" => '', "season" => $instance['season'], "time" => "next");
		if ( !empty($instance['group']) ) {
			$match_args['group'] = $instance['group'];
		}

		if ( isset($instance['home_only']) && $instance['home_only'] == 1 )
			$match_args['home_only'] = true;
		
		$match_args['limit'] = $match_limit;
			
		$matches = $leaguemanager->getMatches( $match_args );
		if ( $matches ) {
			$team_args = array("league_id" => $instance['league'], "season" => $instance['season'], "orderby" => array("id" => "ASC"));
			if ( !empty($instance['group']) ) $team_args["group"] = $instance['group'];
			
			$teams = $leaguemanager->getTeams( $team_args, 'ARRAY' );
			
			$curr = $this->getMatchIndex('next');
			if (isset($matches[$curr]))
				$match = $matches[$curr];
			else
				die("Error: Match with index ".$curr." does not exist");
			
			$match_limit_js = ( $match_limit ) ? $match_limit : 'false';
			$home_only = ( isset($instance['home_only']) ) ? $home_only = $instance['home_only'] : $home_only = 0;

			$next_link = $prev_link = '';
			if ( $curr < count($matches) - 1 ) {
				$next_link = "<a class='next' href='#null' onclick='Leaguemanager.setMatchBox(\"".LEAGUEMANAGER_URL."/ajax.php\", ".$curr.", \"next\", \"next\", ".$instance['league'].", \"".$match_limit_js."\", ".$number.", \"".$instance['season']."\", \"".$instance['group']."\", ".intval($home_only).", \"".$instance['date_format']."\"); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_right.png' alt='&raquo;' /></a>";
			}
			if ( $curr > 0 ) {
				$prev_link = "<a class='prev' href='#null' onclick='Leaguemanager.setMatchBox(\"".LEAGUEMANAGER_URL."/ajax.php\", ".$curr.", \"prev\", \"next\", ".$instance['league'].", \"".$match_limit_js."\", ".$number.", \"".$instance['season']."\", \"".$instance['group']."\", ".intval($home_only).", \"".$instance['date_format']."\"); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_left.png' alt='&laquo;' /></a>";
			}
	
			$out = "<div id='next_match_box_".$number."' class='match_box'>";
			$out .= "<h4>$prev_link".__( 'Next Match', 'leaguemanager' )."$next_link</h4>";
						
			$out .= "<div class='match' id='match-".$match->id."'>";
							
			$home_team = $teams[$match->home_team]['title'];
			$away_team = $teams[$match->away_team]['title'];

			if ( !empty($teams[$match->home_team]['website']) )
				$home_team = "<a href='http://".$teams[$match->home_team]['website']."' target='_blank'>".$home_team."</a>";
			if ( $teams[$match->away_team]['website'] != '' )
				$away_team = "<a href='http://".$teams[$match->away_team]['website']."' target='_blank'>".$away_team."</a>";
								
			if ( !isset($match->title) ) $match->title = sprintf("%s &#8211; %s", $home_team, $away_team);

			$out .= "<p class='match_title'><strong>". $match->title."</strong></p>";
			$out .= "<p class='logos'><img class='home_logo' src='".$teams[$match->home_team]['logo']."' alt='' /><img class='away_logo' src='".$teams[$match->away_team]['logo']."' alt='' /></p>";

			if ( !empty($match->match_day) )
			$out .= "<p class='match_day'>".sprintf(__("<strong>%d.</strong> Match Day", 'leaguemanager'), $match->match_day)."</p>";
			
			$time = ( '00:00' == $match->hour.":".$match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);
			$out .= "<p class='date'>".mysql2date(get_option('date_format'), $match->date).", <span class='time'>".$time."</span></p>";
			$out .= "<p class='location'>".$match->location."</p>";
			
			$out .= "</div></div>";
		
	
			if ( $echo )
				echo $out;
				
			return $out;
		}
	}
	
	
	/**
	 * show previous match box
	 *
	 * @param int $number
	 * @param array $instance
	 * @param boolean $echo (optional)
	 * @return void
	 */
	function showPrevMatchBox($number, $instance, $echo = true, $test = false)
	{
		global $leaguemanager;

		$league = $leaguemanager->getLeague($instance['league']);
		$match_limit = ( is_numeric($instance['match_limit']) && intval($instance['match_limit']) > 0 ) ? intval($instance['match_limit']) : false;
		$match_args = array("league_id" => $instance['league'], "final" => '', "season" => $instance['season'], "time" => "prev");
		if ( !empty($instance['group']) ) {
			$match_args['group'] = $instance['group'];
		}

		if ( isset($instance['home_only']) && $instance['home_only'] == 1 )
			$match_args['home_only'] = true;
		
		$match_args['limit'] = $match_limit;
		$match_args['orderby'] = array("date" => "DESC", "id" => "DESC");
		
		$matches = $leaguemanager->getMatches( $match_args );
		if ( $matches ) {
			$team_args = array("league_id" => $instance['league'], "season" => $instance['season'], "orderby" => array("id" => "ASC"));
			if ( !empty($instance['group']) ) $team_args["group"] = $instance['group'];
			
			$teams = $leaguemanager->getTeams( $team_args, 'ARRAY' );
							
			$curr = $this->getMatchIndex('prev');
			if (isset($matches[$curr]))
				$match = $matches[$curr];
			else
				die("Error: Match with index ".$curr." does not exist");
		
			
			$match_limit_js = ( $match_limit ) ? $match_limit : 'false';
			$home_only = ( isset($instance['home_only']) ) ? $home_only = $instance['home_only'] : $home_only = 0;
			
			$next_link = $prev_link = '';
			if ( $curr < count($matches) - 1 ) {
				$next_link = "<a class='next' href='#null' onclick='Leaguemanager.setMatchBox(\"".LEAGUEMANAGER_URL."/ajax.php\", ".$curr.", \"next\", \"prev\", ".$instance['league'].", \"".$match_limit_js."\", ".$number.", \"".$instance['season']."\", \"".$instance['group']."\", ".intval($home_only).", \"".$instance['date_format']."\"); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_right.png' alt='&raquo;' /></a>";
			}
			if ( $curr > 0 ) {
				$prev_link = "<a class='prev' href='#null' onclick='Leaguemanager.setMatchBox(\"".LEAGUEMANAGER_URL."/ajax.php\", ".$curr.", \"prev\", \"prev\", ".$instance['league'].", \"".$match_limit_js."\", ".$number.", \"".$instance['season']."\", \"".$instance['group']."\", ".intval($home_only).", \"".$instance['date_format']."\"); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_left.png' alt='&laquo;' /></a>";
			}
			
			$out = "<div id='prev_match_box_".$number."' class='match_box'>";
			$out .= "<h4>$prev_link".__( 'Last Match', 'leaguemanager' )."$next_link</h4>";			
			
			$out .= "<div class='match' id='match-".$match->id."'>";
			
			$match->hadOvertime = ( isset($match->overtime) && $match->overtime['home'] != '' && $match->overtime['away'] != '' ) ? true : false;
			$match->hadPenalty = ( isset($match->penalty) && $match->penalty['home'] != '' && $match->penalty['away'] != '' ) ? true : false;

			$home_team = $teams[$match->home_team]['title'];
			$away_team = $teams[$match->away_team]['title'];

			if ( !empty($teams[$match->home_team]['website']) )
				$home_team = "<a href='http://".$teams[$match->home_team]['website']."' target='_blank'>".$home_team."</a>";
			if ( $teams[$match->away_team]['website'] != '' )
				$away_team = "<a href='http://".$teams[$match->away_team]['website']."' target='_blank'>".$away_team."</a>";
								
			if ( !isset($match->title) ) $match->title = sprintf("%s &#8211; %s", $home_team, $away_team);

			if ( $match->home_points == "" && $match->away_points == "" ) {
				$score = "N/A";
			} else {
				if ( $match->hadPenalty )
					$score = sprintf("%d - %d", $match->penalty['home'], $match->penalty['away'])." "._x( 'o.P.', 'leaguemanager' );
				elseif ( $match->hadOvertime )
					$score = sprintf("%d - %d", $match->overtime['home'], $match->overtime['away'])." "._x( 'AET', 'leaguemanager' );
				else
					$score = sprintf("%d - %d", $match->home_points, $match->away_points);
			}
			
			$out .= "<p class='match_title'><strong>". $match->title."</strong></p>";
			$out .= "<p class='logos'><img class='home_logo' src='".$teams[$match->home_team]['logo']."' alt='' /><span class='result'>".$score."</span><img class='away_logo' src='".$teams[$match->away_team]['logo']."' alt='' /></p>";

			if ( !empty($match->match_day) )
			$out .= "<p class='match_day'>".sprintf(__("<strong>%d.</strong> Match Day", 'leaguemanager'), $match->match_day)."</p>";
			
			$time = ( '00:00' == $match->hour.":".$match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);
			$out .= "<p class='date'>".mysql2date(get_option('date_format'), $match->date).", <span class='time'>".$time."</span></p>";
			$out .= "<p class='location'>".$match->location."</p>";
			
			if ( $match->post_id != 0 && ( isset($instance['report']) && ($instance['report'] == 1)) )
				$out .=  "<p class='report'><a href='".get_permalink($match->post_id)."'>".__( 'Report', 'leaguemanager' )."&raquo;</a></p>";
					
			$out .= "</div></div>";
		
			if ( $echo )
				echo $out;
			
			return $out;
		}
	}
	
	
	/**
	 * save settings
	 *
	 * @param array $new_instance
	 * @param $old_instance
	 * @return array
	 */
	function update( $new_instance, $old_instance )
	{
		return $new_instance;
	}


	/**
	 * widget control panel
	 *
	 * @param int|array $widget_args
	 */
	function form( $instance )
	{
		global $leaguemanager;
		$group = ( isset($instance['group']) ) ? $instance['group'] : '';

		$season = isset($instance['season']) ? $instance['season'] : '';
		$match_limit = isset($instance['match_limit']) ? $instance['match_limit'] : '';
		$date_format = isset($instance['date_format']) ? $instance['date_format'] : '';
		
		echo '<div class="leaguemanager_widget_control" id="leaguemanager_widget_control_'.$this->number.'">';
		echo '<p><label for="'.$this->get_field_id('league').'">'.__('League','leaguemanager').': </label>';
		echo '<select size="1" name="'.$this->get_field_name('league').'" id="'.$this->get_field_id('league').'">';
		foreach ( $leaguemanager->getLeagues() AS $league ) {
			$selected = ( $instance['league'] == $league->id ) ? ' selected="selected"' : '';
			echo '<option value="'.$league->id.'"'.$selected.'>'.$league->title.'</option>';
		}
		echo '</select>';
		echo '<p><label for="'.$this->get_field_id('season').'">'.__('Season','leaguemanager').': </label><input type="text" name="'.$this->get_field_name('season').'" id="'.$this->get_field_id('season').'" size="8" value="'.$season.'" /></p>';
		echo '<p><label for="'.$this->get_field_id('group').'">'.__('Group','leaguemanager').': </label><input type="text" name="'.$this->get_field_name('group').'" id="'.$this->get_field_id('group').'" size="8" value="'.$group.'" /></p>';
		echo '<p><label for="'.$this->get_field_id('match_display').'">'.__('Matches','leaguemanager').': </label>';
		$match_display = array( 'none' => __('Do not show','leaguemanager'), 'prev' => __('Last Matches','leaguemanager'), 'next' => __('Next Matches','leaguemanager'), 'all' => __('Next & Last Matches','leaguemanager') );
		echo '<select size="1" name="'.$this->get_field_name('match_display').'" id="'.$this->get_field_id('match_display').'">';
		foreach ( $match_display AS $key => $text ) {
			$selected = ( $key == $instance['match_display'] ) ? ' selected="selected"' : '';
			echo '<option value="'.$key.'"'.$selected.'>'.$text.'</option>';
		}
		echo '</select></p>';
		$home_checked = ( isset($instance['home_only']) && $instance['home_only'] == 1 ) ? ' checked="checked"' : '';
		echo '<p><input type="checkbox" name="'.$this->get_field_name('home_only').'" id="'.$this->get_field_id('home_only').'" value="1"'.$home_checked.' /><label for="'.$this->get_field_id('home_only').'" class="right">'.__('Only own matches','leaguemanager').'</label></p>';
		echo '<p><label for="'.$this->get_field_id('match_limit').'">'.__('Limit','leaguemanager').': </label><input type="text" name="'.$this->get_field_name('match_limit').'" id="'.$this->get_field_id('match_limit').'" value="'.$match_limit.'" size="5" /></p>';

		$table_display = array( 'none' => __('Do not show','leaguemanager'), 'compact' => __('Compact Version','leaguemanager'), 'extend' => __('Extend Version','leaguemanager') );
		echo '<p><label for="'.$this->get_field_id('table').'">'.__('Table','leaguemanager').': </label>';
		echo '<select size="1" name="'.$this->get_field_name('table').'" id="'.$this->get_field_id('table').'">';
		foreach ( $table_display AS $key => $text ) {
			$selected = ( $key == $instance['table'] ) ? ' selected="selected"' : '';
			echo '<option value="'.$key.'"'.$selected.'>'.$text.'</option>';
		}
		echo '</select></p>';
		$report_checked = ( isset($instance['report']) && $instance['report'] == 1 ) ? ' checked="checked"' : '';
		echo '<p><input type="checkbox" name="'.$this->get_field_name('report').'" id="'.$this->get_field_id('report').'" value="1"'.$report_checked.' /><label for="'.$this->get_field_id('report').'" class="right">'.__('Link to report','leaguemanager').'</label></p>';
		$logos_checked = ( isset($instance['show_logos']) && $instance['show_logos'] == 1 ) ? ' checked="checked"' : '';
		echo '<p><input type="checkbox" name="'.$this->get_field_name('show_logos').'" id="'.$this->get_field_id('show_logos').'" value="1"'.$logos_checked.' /><label for="'.$this->get_field_id('show_logos').'" class="right">'.__('Show Logos','leaguemanager').'</label></p>';
		echo '<p><label for="'.$this->get_field_id('date_format').'">'.__('Date Format').': </label><input type="text" id="'.$this->get_field_id('date_format').'" name="'.$this->get_field_name('date_format').'" value="'.$date_format.'" size="10" /></p>';
		echo '</div>';
		
		return;
	}
}


if ( !class_exists('LeagueManager_Widgets')) {

	/**
	 * Manage the LeagueManager widget in the dashboard.
	 *
	 * @category   Widgets
	 * @package    LeagueManager
	 * @author     LaMonte M. Forthun
	 * @copyright  (c) 2014 CollegeFund Software
	 */
	class LeagueManager_Widgets {

		/**
		 * Get latest news from LeagueManager Support on WordPress.org
		 *
		 * @param  none
		 * @return string
		 */
		public static function latest_support_news()
		{
			$options = get_option('leaguemanager');
			
			echo '<div class="rss-widget">';

			wp_widget_rss_output(array(
			'url'          => 'http://wordpress.org/support/rss/plugin/leaguemanager',
			'title'        => __('Latest LeagueManager support discussions...', 'leaguemanager'),
			'show_author' => $options['dashboard_widget']['show_author'],
			'show_date' => $options['dashboard_widget']['show_date'],
			'show_summary' => $options['dashboard_widget']['show_summary'],
			'items' => $options['dashboard_widget']['num_items']
			));

			echo '</div>';
		}
	}
}

?>
