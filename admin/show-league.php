<?php
$tab = 0; $matchDay = false;
if ( isset($_POST['updateLeague']) && !isset($_POST['doaction']) && !isset($_POST['doaction2']) && !isset($_POST['doaction3']) )  {
	if ( 'team' == $_POST['updateLeague'] ) {
        check_admin_referer('leaguemanager_manage-teams');

        $_POST = stripslashes_deep($_POST);

		$home = isset( $_POST['home'] ) ? 1 : 0;
		$custom = !isset($_POST['custom']) ? array() : $_POST['custom'];
		$roster = ( isset($_POST['roster_group']) && isset($_POST['roster']) ) ? array('id' => intval($_POST['roster']), 'cat_id' => intval($_POST['roster_group'])) : array( 'id' => '', 'cat_id' => false );
		$profile = isset($_POST['profile']) ? intval($_POST['profile']) : 0;
		$group = isset($_POST['group']) ? htmlspecialchars(strip_tags($_POST['group'])) : '';
		if ( '' == $_POST['team_id'] ) {
			$this->addTeam( intval($_POST['league_id']), htmlspecialchars(strip_tags($_POST['season'])), htmlspecialchars(strip_tags($_POST['team'])), htmlspecialchars(strip_tags($_POST['website'])), htmlspecialchars($_POST['coach']), htmlspecialchars($_POST['stadium']), $home, $group, $roster, $profile, $custom, htmlspecialchars($_POST['logo_db']) );
		} else {
			$del_logo = isset( $_POST['del_logo'] ) ? true : false;
			$overwrite_image = isset( $_POST['overwrite_image'] ) ? true: false;
			$this->editTeam( intval($_POST['team_id']), htmlspecialchars(strip_tags($_POST['team'])), htmlspecialchars(strip_tags($_POST['website'])), htmlspecialchars($_POST['coach']), htmlspecialchars($_POST['stadium']), $home, $group, $roster, $profile, $custom, htmlspecialchars($_POST['logo_db']), $del_logo, $overwrite_image );
		}
	} elseif ( 'match' == $_POST['updateLeague'] ) {
		check_admin_referer('leaguemanager_manage-matches');

		$group = isset($_POST['group']) ? htmlspecialchars(strip_tags($_POST['group'])) : '';
		if ( 'add' == $_POST['mode'] ) {
			$num_matches = count($_POST['match']);
			foreach ( $_POST['match'] AS $i => $match_id ) {
				if ( isset($_POST['add_match'][$i]) || $_POST['away_team'][$i] != $_POST['home_team'][$i]  ) {
					$index = ( isset($_POST['mydatepicker'][$i]) ) ? $i : 0;
					$date = $_POST['mydatepicker'][$index].' '.intval($_POST['begin_hour'][$i]).':'.intval($_POST['begin_minutes'][$i]).':00';
					$match_day = ( isset($_POST['match_day'][$i]) ? $_POST['match_day'][$i] : (!empty($_POST['match_day']) ? intval($_POST['match_day']) : '' )) ;
					$custom = isset($_POST['custom']) ? $_POST['custom'][$i] : array();

					$this->addMatch( $date, $_POST['home_team'][$i], $_POST['away_team'][$i], $match_day, htmlspecialchars(strip_tags($_POST['location'][$i])), intval($_POST['league_id']), htmlspecialchars(strip_tags($_POST['season'])), $group, htmlspecialchars(strip_tags($_POST['final'])), $custom );
				} else {
					$num_matches -= 1;
				}
			}
			$leaguemanager->setMessage(sprintf(_n('%d Match added', '%d Matches added', $num_matches, 'leaguemanager'), $num_matches));
		} else {
			$num_matches = count($_POST['match']);
			$post_match = $this->htmlspecialchars_array($_POST['match']);
			foreach ( $post_match AS $i => $match_id ) {
				if( isset($_POST['mydatepicker'][$i]) ) {
					$index = ( isset($_POST['mydatepicker'][$i]) ) ? $i : 0;
					$date = htmlspecialchars(strip_tags($_POST['mydatepicker'][$index])).' '.intval($_POST['begin_hour'][$i]).':'.intval($_POST['begin_minutes'][$i]).':00';
				} else {
					$index = ( isset($_POST['year'][$i]) && isset($_POST['month'][$i]) && isset($_POST['day'][$i]) ) ? $i : 0;
					$date = intval($_POST['year'][$index]).'-'.intval($_POST['month'][$index]).'-'.intval($_POST['day'][$index]).' '.intval($_POST['begin_hour'][$i]).':'.intval($_POST['begin_minutes'][$i]).':00';
				}
				$match_day = (isset($_POST['match_day']) && is_array($_POST['match_day'])) ? intval($_POST['match_day'][$i]) : (isset($_POST['match_day']) && !empty($_POST['match_day']) ? intval($_POST['match_day']) : '' ) ;
				$custom = isset($_POST['custom']) ? $_POST['custom'][$i] : array();
				$home_team = isset($_POST['home_team'][$i]) ? htmlspecialchars(strip_tags($_POST['home_team'][$i])) : '';
				$away_team = isset($_POST['away_team'][$i]) ? htmlspecialchars(strip_tags($_POST['away_team'][$i])) : '';
				$this->editMatch( $date, $home_team, $away_team, $match_day, htmlspecialchars($_POST['location'][$i]), intval($_POST['league_id']), $match_id, $group, htmlspecialchars(strip_tags($_POST['final'])), $custom );
			}
			$leaguemanager->setMessage(sprintf(_n('%d Match updated', '%d Matches updated', $num_matches, 'leaguemanager'), $num_matches));
		}
	} elseif ( 'results' == $_POST['updateLeague'] ) {
		check_admin_referer('matches-bulk');
		$custom = isset($_POST['custom']) ? $_POST['custom'] : array();
		$this->updateResults( intval($_POST['league_id']), $_POST['matches'], $_POST['home_points'], $_POST['away_points'], $_POST['home_team'], $_POST['away_team'], $custom );
		$tab = 2;
		$matchDay = intval($_POST['current_match_day']);
	} elseif ( 'teams_manual' == $_POST['updateLeague'] ) {
		check_admin_referer('teams-bulk');
		$this->saveStandingsManually( $_POST['team_id'], $_POST['points_plus'], $_POST['points_minus'], $_POST['num_done_matches'], $_POST['num_won_matches'], $_POST['num_draw_matches'], $_POST['num_lost_matches'], $_POST['add_points'], $_POST['custom'] );

		$leaguemanager->setMessage(__('Standings Table updated','leaguemanager'));
	}
	
	$leaguemanager->printMessage();
}  elseif ( isset($_POST['doaction']) || isset($_POST['doaction2']) ) {
	if ( isset($_POST['doaction']) && $_POST['action'] == "delete" ) {
		check_admin_referer('teams-bulk');
		foreach ( $_POST['team'] AS $team_id )
			$this->delTeam( intval($team_id), true );
	} elseif ( isset($_POST['doaction2']) && $_POST['action2'] == "delete" ) {
		check_admin_referer('matches-bulk');
		foreach ( $_POST['match'] AS $match_id )
			$this->delMatch( intval($match_id) );
			
		$tab = 2;
	}
}

// rank teams manually
if (isset($_POST['updateRanking'])) {
	$league = $leaguemanager->getCurrentLeague();
	$season = $leaguemanager->getSeason($league);
	$js = ( $_POST['js-active'] == 1 ) ? true : false;
	
	$team_ranks = array();
	$team_ids = array_values($_POST['team_id']);
	foreach ($team_ids AS $key => $team_id) {
		if ( $js ) {
			$rank = $key + 1;
		} else {
			$rank = intval($_POST['rank'][$team_id]);
		}
		
		$team = $leaguemanager->getTeam($team_id);
		$team_ranks[$rank-1] = $team;
	}
	ksort($team_ranks);
	updateRanking($league->id, $season, "", $team_ranks, $team_ranks);
	$leaguemanager->setMessage(__('Team ranking saved','leaguemanager'));
	$leaguemanager->printMessage();
	
	$tab = 0;
}
	
$league = $leaguemanager->getCurrentLeague();
$season = $leaguemanager->getSeason($league);
$leaguemanager->setSeason($season);
$league_mode = (isset($league->mode) ? ($league->mode) : '' );
	
// check if league is a cup championship
$cup = ( $league_mode == 'championship' ) ? true : false;

$group = isset($_GET['group']) ? htmlspecialchars(strip_tags($_GET['group'])) : '';
if ( empty($group) && isset($_POST['group']) ) $group = htmlspecialchars(strip_tags($_POST['group']));

$team_id = isset($_POST['team_id']) ? intval($_POST['team_id']) : false;

$team_list = $leaguemanager->getTeams( array("league_id" => $league->id, "season" => $season['name'], "orderby" => array("id" => "ASC")), 'ARRAY' );
$options = get_option('leaguemanager');

$match_args = array("league_id" => $league->id, "final" => "");
if ( $season )
	$match_args["season"] = $season['name'];
if ( $group )
	$match_args["group"] = $group;
if ( $team_id )
	$match_args['team_id'] = $team_id;

//if (intval($league->num_matches_per_page) > 0)
//	$match_args['limit'] = intval($league->num_matches_per_page);

if ( isset($_POST['doaction3'])) {
	if ($_POST['match_day'] != -1) {
		$matchDay = intval($_POST['match_day']);
		$leaguemanager->setMatchDay($matchDay);
		$match_args["match_day"] = $matchDay;
	}
	$tab = 2;
} else {
	if ( !$matchDay ) $matchDay = $leaguemanager->getMatchDay('current');
	$leaguemanager->setMatchDay($matchDay);
	$match_args["match_day"] = $matchDay;
}

if ( empty($league->seasons)  ) {
	$leaguemanager->setMessage( __( 'You need to add at least one season', 'leaguemanager' ), true );
	$leaguemanager->printMessage();
}

if ( $league_mode != 'championship' ) {
	$teams = $leaguemanager->getTeams( array("league_id" => $league->id, "season" => $season['name'], "cache" => false), 'OBJECT' );
	$matches = $leaguemanager->getMatches( $match_args );
	$leaguemanager->setNumMatches($leaguemanager->getMatches(array_merge($match_args, array('limit' => false, 'count' => true))));
}

if(has_filter('leaguemanager_matches_file_'.$league->sport))
{
    $matches_file = apply_filters('leaguemanager_matches_file_'.$league->sport, $league, $season['name']);
}
elseif(has_filter('leaguemanager_matches_file'))
{
    $matches_file = apply_filters('leaguemanager_matches_file');
}
else
{
    $matches_file = 'matches.php';
}

if ( isset($_GET['match_paged']) ) 
	$tab = 2;

if ( isset($_GET['standingstable']) ) {
	$get = $_GET['standingstable'];
	$match_day = false;
	$mode = 'all';
	if ( preg_match('/match_day-\d/', $get, $hits) ) {
		$res = explode("-", $hits[0]);
		$match_day = $res[1];
	} elseif ( in_array($get, array('home', 'away')) ) {
		$mode = htmlspecialchars($get);
	}
	$teams = $leaguemanager->getStandings( $teams, $match_day, $mode );
}
/*$tmp = $leaguemanager->getTeams( array('league_id' => $league->id) );
foreach ( $tmp AS $t )
	$this->delTeam($t->id);*/

if ( !wp_mkdir_p( $leaguemanager->getImagePath() ) ) : ?>
  <div class="error"><p><?php printf( __( 'Unable to create directory %s. Is its parent directory writeable by the server?' ), $leaguemanager->getImagePath() ) ?></p></div>
<?php endif; ?>

<script type='text/javascript'>
	jQuery(function() {
		jQuery("#tabs").tabs({
			active: <?php echo $tab ?>
		});
	});
</script>
<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'LeagueManager', 'leaguemanager' ) ?></a> &raquo; <?php echo $league->title ?></p>

	<h1><?php echo $league->title ?></h1>

	<?php if ( !empty($league->seasons) ) : ?>
	<!-- Season Dropdown -->
	<div class="alignright" style="clear: both;">
	<form action="admin.php" method="get" style="display: inline;">
		<input type="hidden" name="page" value="leaguemanager" />
		<input type="hidden" name="subpage" value="show-league" />
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
		<label for="season" style="vertical-align: middle;"><?php _e( 'Season', 'leaguemanager' ) ?></label>
		<select size="1" name="season" id="season">
		<?php foreach ( $league->seasons AS $s ) : ?>
			<option value="<?php echo htmlspecialchars($s['name']) ?>"<?php if ( $s['name'] == $season['name'] ) echo ' selected="selected"' ?>><?php echo $s['name'] ?></option>
		<?php endforeach; ?>
		</select>
		<input type="submit" value="<?php _e( 'Show', 'leaguemanager' ) ?>" class="button" />
	</form>
	</div>
	<?php endif; ?>

	<!-- League Menu -->
	<ul class="subsubsub">
	<?php foreach ( $this->getMenu() AS $key => $menu ) : ?>
	<?php if ( !isset($menu['show']) || $menu['show'] ) : ?>
		<li><a class="button-secondary" href="admin.php?page=leaguemanager&amp;subpage=<?php echo $key ?>&amp;league_id=<?php echo $league->id ?>&amp;season=<?php echo $season['name'] ?>&amp;group=<?php echo $group ?>"><?php echo $menu['title'] ?></a></li>
	<?php endif; ?>
	<?php endforeach; ?>
	</ul>


	<?php if ( $league_mode == 'championship' ) : ?>
		<?php include('championship.php'); ?>
	<?php else : ?>
		<div id="tabs" class="league-blocks">
			<ul id="tablist" style="display: none;">
				<li><a href="#standings-table"><?php _e( 'Standings', 'leaguemanager' ) ?></a></li>
				<li><a href="#crosstable"><?php _e( 'Crosstable', 'leaguemanager' ) ?></a></li>
				<li><a href="#matches-table"><?php _e( 'Match Plan', 'leaguemanager' ) ?></a></li>
			</ul>
			
			<div id="standings-table" class="league-block-container">
				<h2 class="header"><?php _e( 'Table', 'leaguemanager' ) ?></h2>
				<div class="alignright">
					<form action="admin.php" method="get">
					<input type="hidden" name="page" value="leaguemanager" />
					<input type="hidden" name="subpage" value="show-league" />
					<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
					
					<?php echo $leaguemanager->getStandingsSelection( $league ); ?>
					<input type="submit" class="button-secondary" value="<?php _e( 'Show', 'leaguemanager' ) ?>" />
					</form>
				</div>
				<?php include_once('standings.php'); ?>
			</div>
			
			<div id="crosstable" class="league-block-container">
				<h2 class="header"><?php _e( 'Crosstable', 'leaguemanager' ) ?></h2>
				<?php include('crosstable.php'); ?>
			</div>
		
			<div id="matches-table" class="league-block-container">
				<h2 class="header"><?php _e( 'Match Plan','leaguemanager' ) ?></h2>
                <?php include($matches_file); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
