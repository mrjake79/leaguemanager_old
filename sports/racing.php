<?php
/**
 * Racing Class 
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright Copyright 2008
*/
class LeagueManagerRacing extends LeagueManager
{
	/**
	 * sports keys
	 *
	 * @var string
	 */
	var $key = 'racing';


	/**
	 * load specific settings
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );
		add_filter( 'league_menu_'.$this->key, array(&$this, 'menu'), 10, 3 );

		add_filter( 'leaguemanager_export_matches_header_'.$this->key, array(&$this, 'exportMatchesHeader') );
		add_filter( 'leaguemanager_export_matches_data_'.$this->key, array(&$this, 'exportMatchesData'), 10, 2 );
		add_filter( 'leaguemanager_import_matches_'.$this->key, array(&$this, 'importMatches'), 10, 3 );

		add_action( 'matchtable_header_'.$this->key, array(&$this, 'displayMatchesHeader'), 10, 0);
		add_action( 'matchtable_columns_'.$this->key, array(&$this, 'displayMatchesColumns') );
		add_action( 'edit_matches_header_'.$this->key, array(&$this, 'displayEditMatchesHeader'), 10, 0);
		add_action( 'edit_matches_columns_'.$this->key, array(&$this, 'displayEditMatchesColumns'), 10, 2);

		add_action('leaguemanager_edit_match_'.$this->key, array(&$this, 'matchForm'), 10, 7);
	}
	function LeagueManagerRacing()
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
		$sports[$this->key] = __( 'Racing', 'leaguemanager' );
		return $sports;
	}


	/**
	 * add menu page
	 *
	 * @param array $menu
	 * @param int $league_id
	 * @param mixed $season
	 * @return void
	 */
	function menu( $menu, $league_id, $season )
	{
		$menu[$this->key] = array( 'title' => __( 'Race Results', 'leaguemanager' ), 'file' => false, 'show' => false, 'callback' => array(&$this, 'page') );
		return $menu;
	}


	/**
	 * display Table Header for Match Administration
	 *
	 * @param none
	 * @return void
	 */
	function displayMatchesHeader()
	{
		echo '<th>'.__( 'Race Type', 'leaguemanager' ).'</th><th>'.__( 'Results', 'leaguemanager' ).'</th>';
	}


	/**
	 * display Table Header for Match Editing
	 *
	 * @param none
	 * @return void
	 */
	function displayEditMatchesHeader()
	{
		echo '<th>'.__( 'Race Type', 'leaguemanager' ).'</th><th>'.__( 'Description', 'leaguemanager' ).'</th>';
	}


	/**
	 * display Table columns for Match Administration
	 *
	 * @param object $match
	 * @return void
	 */
	function displayMatchesColumns( $match )
	{
		if (!isset($match->racetype)) $match->racetype = '';
		echo '<td>'.$match->racetype.'</td><td><a href="admin.php?page=leaguemanager&subpage='.$this->key.'&league_id='.$match->league_id.'&season='.$match->season.'&match='.$match->id.'">'.__( 'Results', 'leaguemanager' ).'</a></td>';
	}


	/**
	 * display Table columns for Match Editing
	 *
	 * @param object $match
	 * @return void
	 */
	function displayEditMatchesColumns( $i, $match )
	{
		if (!isset($match->racetype)) $match->racetype = '';
		echo '<td><input type"text" name="custom['.$i.'][racetype]" value="'.$match->racetype.'" size="10" /></td><td></td>';
	}


	/**
	 * export matches header
	 *
	 * @param string $content
	 * @return the content
	 */
	function exportMatchesHeader( $content )
	{
		$content .= "\t".__( 'Race Type', 'leaguemanager' );
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
		if ( isset($match->racetype) )
			$content .= "\t".$match->racetype;
		else
			$content .= "\t";

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
		$custom[$match_id]['racetype'] = $line[8];
		return $custom;
	}


	/**
	 * custom match editing form
	 *
	 * @param object $league
	 * @param object $teams
	 * @param arrray $season
	 * @param int $max_matches
	 * @param array $matches
	 * @param string $submit_title
	 * @param string $mode
	 * @return void
	 */
	function matchForm( $league, $teams, $season, $max_matches, $matches, $submit_title, $mode ) 
	{
		global $lmLoader;
		$admin = $lmLoader->getAdminPanel();
		$edit = ($mode == 'edit') ? true : false;
		$class = '';
	?>
		<form action="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id?>&amp;season=<?php echo $season['name'] ?>" method="post">
			<?php wp_nonce_field( 'leaguemanager_manage-matches' ) ?>
			
			<table class="widefat">
				<thead>
					<tr>
						<?php if ( !isset($edit) || (isset($edit) && !$edit) ) : ?>
						<th scope="col"><?php _e( 'Add', 'leaguemanager' ) ?></th>
						<?php endif; ?>
						<th scope="col"><?php _e( 'Date', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Match Day', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Event', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Location','leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Race Type', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Begin','leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Description', 'leaguemanger' ) ?></th>
					</tr>
				</thead>
				<tbody id="the-list" class="form-table">
				<?php for ( $i = 0; $i < $max_matches; $i++ ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; if (!isset($matches[$i]->racetype)) $matches[$i]->racetype = ''; if (!isset($matches[$i]->description)) $matches[$i]->description = ''; if (!isset($matches[$i]->title)) $matches[$i]->title = '';?>
				<tr class="<?php echo $class; ?>">
					<?php if ( !isset($edit) || (isset($edit) && !$edit) ) : ?>
					<td><input type="checkbox" name="add_match[<?php echo $i ?>]" id="add_match_<?php echo $i ?>" /></td>
					<?php endif; ?>
					<td><?php echo $admin->getDateSelection( $matches[$i]->day, $matches[$i]->month, $matches[$i]->year, $i) ?></td>
					<td>
					<select size="1" name="match_day[<?php echo $i ?>]">
						<?php for ($d = 1; $d <= $season['num_match_days']; $d++) : ?>
						<option value="<?php echo $d ?>"<?php selected($d, $matches[$i]->match_day) ?>><?php echo $d ?></option>
						<?php endfor; ?>
					</select>
					</td>
					<td><input type="text" size="15" name="custom[<?php echo $i ?>][title]" id="title_<?php echo $i ?>" value="<?php echo $matches[$i]->title ?>" /></td>
					<td><input type="text" name="location[<?php echo $i ?>]" id="location[<?php echo $i ?>]" size="20" value="<?php echo $matches[$i]->location ?>" size="30" /></td>
					<td><input type="text" size="15" name="custom[<?php echo $i ?>][racetype]" id="racetype_<?php echo $i ?>" value="<?php echo $matches[$i]->racetype ?>" /></td>
					<td>
						<select size="1" name="begin_hour[<?php echo $i ?>]">
						<?php for ( $hour = 0; $hour <= 23; $hour++ ) : ?>
							<option value="<?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?>"<?php selected( $hour, $matches[$i]->hour ) ?>><?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?></option>
						<?php endfor; ?>
						</select>
						<select size="1" name="begin_minutes[<?php echo $i ?>]">
						<?php for ( $minute = 0; $minute <= 60; $minute++ ) : ?>
							<?php if ( 0 == $minute % 5 && 60 != $minute ) : ?>
							<option value="<?php  echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?>"<?php selected( $minute, $matches[$i]->minutes ) ?>><?php echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?></option>
							<?php endif; ?>
						<?php endfor; ?>
						</select>
					</td>
					<td><textarea name="custom[<?php echo $i ?>][description]" id="description_<?php echo $i ?>" cols="20" rows="5"><?php echo $matches[$i]->description ?></textarea></td>
					<input type="hidden" name="match[<?php echo $i ?>]" value="<?php echo $matches[$i]->id ?>" />
				</tr>
				<?php endfor; ?>
				</tbody>
			</table>
			
			<input type="hidden" name="mode" value="<?php echo $mode ?>" />
			<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
			<input type="hidden" name="season" value="<?php echo $season['name'] ?>" />
			<input type="hidden" name="updateLeague" value="match" />
			
			<p class="submit"><input type="submit" value="<?php echo $submit_title ?> &raquo;" class="button" /></p>
		</form>
	<?php
	}


	/**
	 * display reslts managing page
	 *
	 * @param none
	 * @return void
	 */
	function page()
	{
		global $leaguemanager;

		$league_id = (int)$_GET['league_id'];
		$match_id = (int)$_GET['match'];
		$season = htmlspecialchars($_GET['season']);

		if ( isset($_POST['save_results']) ) {
			$this->saveResults( $_POST['racer'], $_POST['racer_name'], $_POST['category'], $_POST['points'], $_POST['time'], $_POST['info'], $match_id );
			$leaguemanager->printMessage();
		}
		$league = $leaguemanager->getLeague($league_id);
		$match = $leaguemanager->getMatch($match_id);
		$teams = $leaguemanager->getTeams("`league_id` = {$league_id} AND `season` = '".$season."'");
		$team_list = $teams;
		$team_id = isset($_GET['team']) ? (int)$_GET['team'] : false;

		if ($team_id) $teams = array($leaguemanager->getTeam($team_id));
		$class = '';
		if (!isset($match->title)) $match->title = $leaguemanager->getMatchTitle($match_id);
	?>
	<div class="wrap">
		<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'LeagueManager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php _e( 'Race Results', 'leaguemanager' ) ?></p>
		<h2><?php printf(__( 'Racing Results - %s', 'leaguemanager' ), $match->title) ?></h2>

		<form action="admin.php" method="get" class="alignright">
			<input type="hidden" name="page" value="leaguemanager" />
			<input type="hidden" name="subpage" value="racing" />
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<input type="hidden" name="season" value="<?php echo $season ?>" />
			<input type="hidden" name="match" value="<?php echo $match_id ?>" />
			
			<label for="team"><?php _e( 'Choose Team', 'leaguemanager' ) ?></label>
			<select size="1" name="team" id="team">
				<option value="" <?php selected('', $team_id) ?>><?php _e('Show all Teams', 'leaguemanager') ?>
			<?php foreach ( $team_list AS $t ) : ?>
				<option value="<?php echo $t->id ?>"<?php selected($t->id, $team_id) ?>><?php echo $t->title ?></option>
			<?php endforeach; ?>
			</select>
			<input type="submit" value="<?php _e('Filter', 'leaguemanager') ?>" class="button-secondary" />
		</form>

		<?php if ($leaguemanager->hasTeamRoster($teams)) : ?>
		<form action="" method="post">
		
		<?php foreach ($teams AS $team) : ?>
		<h3><?php echo $team->title ?></h3>

		<?php if ( isset($team->teamRoster) && !empty($team->teamRoster) ) : ?>
		<table class="widefat">
		<thead>
		<tr>
			<th><?php _e( 'Name', 'leaguemanager' ) ?></th>
			<th><?php _e( 'Points', 'leaguemanager' ) ?></th>
			<th><?php _e( 'Time', 'leaguemanager' ) ?></th>
			<th><?php _e( 'Category', 'leaguemanager' ) ?></th>
			<th><?php _e( 'Other Info', 'leaguemanager' ) ?></th>
		</tr>
		</thead>
		<tbody id="the-list" class="form-table">
		<?php foreach ( $team->teamRoster AS $roster ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
		<?php if (!isset($match->raceresult[$roster->id])) $match->raceresult[$roster->id] = array('category' => '', 'points' => '', 'time' => '', 'info' => ''); ?>
		<?php if (isset($match->raceresult[$roster->id]['result'])) $match->raceresult[$roster->id]['time'] = $match->raceresult[$roster->id]['result']; ?>
		<tr class="<?php echo $class ?>">
			<td><input type="hidden" name="racer[<?php echo $roster->id ?>]" value="<?php echo $roster->id ?>" /><input type="hidden" name="racer_name[<?php echo $roster->id ?>]" value="<?php echo $roster->name ?>" /><?php echo $roster->name ?></td>
			<td><input type="text" name="points[<?php echo $roster->id ?>]" id="points_<?php echo $roster->id ?>" value="<?php echo $match->raceresult[$roster->id]['points'] ?>" /></td>
			<td><input type="text" name="time[<?php echo $roster->id ?>]" id="time_<?php echo $roster->id ?>" value="<?php echo $match->raceresult[$roster->id]['time'] ?>" /></td>
			<td><input type="text" name="category[<?php echo $roster->id ?>]" id="category_<?php echo $roster->id ?>" value="<?php echo $match->raceresult[$roster->id]['category'] ?>" /></td>
			<td><input type="text" name="info[<?php echo $roster->id ?>]" id="info_<?php echo $roster->id ?>" value="<?php echo $match->raceresult[$roster->id]['info'] ?>" /></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
		<?php else : ?>
			<p class="error"><?php _e( 'No Team Roster found.', 'leaguemanager' ) ?></p>
		<?php endif; ?>
		<?php endforeach; ?>
		<input type="hidden" name="match_id" value="<?php echo $match->id ?>" />
		<p class="submit"><input type="submit" name="save_results" value="<?php _e( 'Save Team Results', 'leaguemanager' ) ?>" class="button button-primary" /></p>
		</form>
		<?php endif; ?>
	</div>
	<?php
	}


	/**
	 * save race results for one team
	 *
	 * @param array $racer
	 * @param array $racer_name
	 * @param array $category
	 * @param array $points
	 * @param array $time
	 * @param array $info
	 * @param int $match_id
	 * @return true
	 */
	function saveResults($racer, $racer_name, $category, $points, $time, $info, $match_id)
	{
		global $wpdb, $leaguemanager;

		$match = $leaguemanager->getMatch( $match_id );
		$custom = $match->custom;

		$data = isset($custom['raceresult']) ? $custom['raceresult'] : array();
		while ( list($id) = each($racer) ) {
			$data[$id]['name'] = $racer_name[$id];
			$data[$id]['category'] = $category[$id];
			$data[$id]['time'] = $time[$id];
			$data[$id]['points'] = $points[$id];
			$data[$id]['info'] = $info[$id];
		}

		$custom['raceresult'] = $data;

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `custom` = '%s' WHERE `id` = '%d'", maybe_serialize($custom), $match_id ) );
		$leaguemanager->setMessage( __( 'Race Results Saved', 'leaguemanager' ) );
		return true;
	}
	
	/**
	 * get racing results for one racer
	 *
	 */
	function getRacerResults($teams)
	{
		global $leaguemanager; global $projectmanager;
		
		$team_roster = array();
		foreach ($teams AS $team) {
			if (isset($team->teamRoster) && is_array($team->teamRoster)) {
				foreach ($team->teamRoster AS $racer) {
					$team_roster[$racer->id] = array('project_id' => $team->roster['id'], 'racer_name' => $racer->name, 'team_id' => $team->id);
				}
			}
		}

		$url = get_permalink();
		$r = array();
		$keys = array();
		$points = array();
		$matches = $leaguemanager->getMatches("`league_id` = '".$teams[0]->league_id."' AND `season` = '".$teams[0]->season."'");
		foreach ($matches AS $match) {
			if (isset($match->raceresult)) {
				foreach ($match->raceresult AS $id => $result) {
					if (isset($team_roster[$id])) {
						if (!isset($points[$id])) $points[$id] = 0;
						$url = add_query_arg('show_'.$team_roster[$id]['project_id'], $id, $url);
						
						$r[$id]['points'] = $points[$id] + $result['points'];
						$r[$id]['name'] = $result['name'];
						$r[$id]['name_url'] = '<a href="'.$url.'">'.$result['name'].'</a>';
						$r[$id]['time'][] = $result['time'];
						$r[$id]['team_id'] = $team_roster[$id]['team_id'];
						$r[$id]['id'] = $id;
					
						$points[$id] = $points[$id] + $result['points'];
					}
				}
				arsort($points);
			}
		}
		
		$keys = array();
		foreach ($points AS $id => $pts) {
			$keys[] = $id;
		}

		$rank = 1; $class = ""; $i = 0;
		$racerlist = array();
		foreach ($points AS $id => $pts) {
			$racer = $r[$id];
			$racer['rank'] = $rank;
			$racer['class'] = ($class == 'alternate') ? '' : 'alternate';
			
			$team = $leaguemanager->getTeam($racer['team_id']);
			if ( 1 == $team->home ) $team->title = '<strong>'.$team->title.'</strong>';
			$racer['team_logo'] = $team->logo;
			$racer['team_logo_url'] = $leaguemanager->getThumbnailUrl($team->logo);
			
			if ( $team->website != '' )
				$racer['team_name'] = '<a href="http://'.$team->website.'" target="_blank">'.$team->title.'</a>';
            else
    			$racer['team_name'] = $team->title;
            
			$racerlist[$id] = $racer;
		
			if ($i > 0) {
				$key = $keys[$i-1];
			}
			$curr = $pts;
			
			$rank++;
			$i++;
		}
		
		return $racerlist;
	}
}

$racing = new LeagueManagerRacing();
?>
