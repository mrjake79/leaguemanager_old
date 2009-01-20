<?php
if ( isset($_POST['updateLeague']) && !isset($_POST['doaction']) && !isset($_POST['doaction2']) )  {
	if ( 'team' == $_POST['updateLeague'] ) {
		check_admin_referer('leaguemanager_manage-teams');
		$home = isset( $_POST['home'] ) ? 1 : 0;
		if ( '' == $_POST['team_id'] ) {
			$message = $leaguemanager->addTeam( $_POST['league_id'], $_POST['short_title'], $_POST['team'], $home );
		} else {
			$del_logo = isset( $_POST['del_logo'] ) ? true : false;
			$overwrite_image = isset( $_POST['overwrite_image'] ) ? true: false;
			$message = $leaguemanager->editTeam( $_POST['team_id'], $_POST['short_title'], $_POST['team'], $home, $del_logo, $_POST['image_file'], $overwrite_image );
		}
	} elseif ( 'match' == $_POST['updateLeague'] ) {
		check_admin_referer('leaguemanager_manage-matches');
		
		if ( 'add' == $_POST['mode'] ) {
			$num_matches = count($_POST['match']);
			foreach ( $_POST['match'] AS $i ) {
				if ( $_POST['away_team'][$i] != $_POST['home_team'][$i] ) {
					$date = $_POST['year'][0].'-'.$_POST['month'][0].'-'.$_POST['day'][0].' '.$_POST['begin_hour'][$i].':'.$_POST['begin_minutes'][$i].':00';
					
					$leaguemanager->addMatch( $date, $_POST['home_team'][$i], $_POST['away_team'][$i], $_POST['match_day'], $_POST['location'][$i], $_POST['league_id'] );
				} else {
					$num_matches -= 1;
				}
			}
			$message = sprintf(__ngettext('%d Match added', '%d Matches added', $num_matches, 'leaguemanager'), $num_matches);
		} else {
			$num_matches = count($_POST['match']);
			foreach ( $_POST['match'] AS $i ) {
				$index = ( isset($_POST['year'][$i]) && isset($_POST['month'][$i]) && isset($_POST['day'][$i]) ) ? $i : 0;
								
				$date = $_POST['year'][$index].'-'.$_POST['month'][$index].'-'.$_POST['day'][$index].' '.$_POST['begin_hour'][$i].':'.$_POST['begin_minutes'][$i].':00';
			
				$leaguemanager->editMatch( $date, $_POST['home_team'][$i], $_POST['away_team'][$i], $_POST['match_day'], $_POST['location'][$i], $_POST['league_id'], $_POST['match_id'][$i], $_POST['home_points'][$i], $_POST['away_points'][$i],  $_POST['home_apparatus_points'][$i], $_POST['away_apparatus_points'][$i] );
			}
			$message = sprintf(__ngettext('%d Match updated', '%d Matches updated', $num_matches, 'leaguemanager'), $num_matches);
		}
	} elseif ( 'results' == $_POST['updateLeague'] ) {
		check_admin_referer('matches-bulk');
		
		$message = $leaguemanager->updateResults( $_POST['matches'], $_POST['home_apparatus_points'], $_POST['away_apparatus_points'], $_POST['home_points'], $_POST['away_points'], $_POST['home_team'], $_POST['away_team'] );
	}
		
	echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';
}  elseif ( isset($_POST['doaction']) || isset($_POST['doaction2']) ) {
	if ( isset($_POST['doaction']) && $_POST['action'] == "delete" ) {
		check_admin_referer('teams-bulk');
		foreach ( $_POST['team'] AS $team_id )
			$leaguemanager->delTeam( $team_id);
	} elseif ( isset($_POST['doaction2']) && $_POST['action2'] == "delete" ) {
		check_admin_referer('matches-bulk');
		foreach ( $_POST['match'] AS $match_id )
			$leaguemanager->delMatch( $match_id );
	}
}

$league_id = $_GET['id'];
$curr_league = $leaguemanager->getLeagues( $league_id );

$league_title = $curr_league['title'];
$league_preferences = $leaguemanager->getLeaguePreferences( $league_id );
$team_list = $leaguemanager->getTeams( 'league_id = "'.$league_id.'"', 'ARRAY' );
?>
<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="edit.php?page=leaguemanager/manage-leagues.php"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <?php echo $league_title ?></p>
	
	<h2><?php echo $league_title ?></h2>
	
	<ul class="subsubsub">
		<li><a href="admin.php?page=leaguemanager/settings.php&amp;edit=<?php echo $league_id ?>"><?php _e( 'Preferences', 'leaguemanager' ) ?></a></li> |
		<li><a href="admin.php?page=leaguemanager/team.php&amp;league_id=<?php echo $league_id ?>"><?php _e( 'Add Team','leaguemanager' ) ?></a></li> |
		<li><a href="admin.php?page=leaguemanager/match.php&amp;league_id=<?php echo $league_id ?>"><?php _e( 'Add Matches','leaguemanager' ) ?></a></li>
	</ul>
	
	<h3 style="clear: both;"><?php _e( 'Table', 'leaguemanager' ) ?></h3>
	
	<form id="teams-filter" action="" method="post">
		<?php wp_nonce_field( 'teams-bulk' ) ?>
			
		<div class="tablenav" style="margin-bottom: 0.1em;">
			<!-- Bulk Actions -->
			<select name="action" size="1">
				<option value="-1" selected="selected"><?php _e('Bulk Actions') ?></option>
				<option value="delete"><?php _e('Delete')?></option>
			</select>
			<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
		</div>
		
		<table class="widefat" summary="" title="<?php _e( 'Table', 'leaguemanager' ) ?>">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="Leaguemanager.checkAll(document.getElementById('teams-filter'));" /></th>
			<th class="num">#</th>
			<?php if ( $league_preferences->show_logo ) : ?>
			<th class="logo">&#160;</th>
			<?php endif; ?>
			<th><?php _e( 'Club', 'leaguemanager' ) ?></th>
			<th class="num"><?php _e( 'Pld', 'leaguemanager' ) ?></th>
			<th class="num"><?php _e( 'W','leaguemanager' ) ?></th>
			<th class="num"><?php _e( 'T','leaguemanager' ) ?></th>
			<th class="num"><?php _e( 'L','leaguemanager' ) ?></th>
			<?php if ( $leaguemanager->isGymnasticsLeague( $league_id ) ) : ?>
				<th class="num"><?php _e( 'AP', 'leaguemanager' ) ?></th>
			<?php else : ?>
				<th class="num"><?php _e( 'Goals', 'leaguemanager' ) ?></th>
			<?php endif; ?>
			<th class="num"><?php _e( 'Diff', 'leaguemanager' ) ?></th>
			<th class="num"><?php _e( 'Pts', 'leaguemanager' ) ?></th>
		</tr>
		</thead>
		<tbody id="the-list">
		<?php $teams = $leaguemanager->rankTeams( $league_id ) ?>
		<?php if ( count($teams) > 0 ) : $rank = 0; ?>
		<?php foreach( $teams AS $team ) : $rank++; $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
		<tr class="<?php echo $class ?>">
			<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $team['id'] ?>" name="team[<?php echo $team['id'] ?>]" /></th>
			<td class="num"><?php echo $rank ?></td>
			<?php if ( $league_preferences->show_logo ) : ?>
			<td class="logo">
			<?php if ( $team['logo'] != '' ) : ?>
				<img src="<?php echo $leaguemanager->getImageUrl($team['logo']) ?>" alt="<?php _e( 'Logo', 'leaguemanager' ) ?>" title="<?php _e( 'Logo', 'leaguemanager' ) ?> <?php echo $team['title'] ?>" />
			<?php endif; ?>
			</td>
			<?php endif; ?>
			<td><a href="edit.php?page=leaguemanager/team.php&amp;edit=<?php echo $team['id']; ?>"><?php echo $team['title'] ?></a></td>
			<td class="num"><?php echo $leaguemanager->getNumDoneMatches( $team['id'] ) ?></td>
			<td class="num"><?php echo $leaguemanager->getNumWonMatches( $team['id'] ) ?></td>
			<td class="num"><?php echo $leaguemanager->getNumDrawMatches( $team['id'] ) ?></td>
			<td class="num"><?php echo $leaguemanager->getNumLostMatches( $team['id'] ) ?></td>
			<?php if ( $leaguemanager->isGymnasticsLeague( $league_id ) ) : ?>
				<td class="num"><?php echo $team['apparatus_points']['plus'] ?>:<?php echo $team['apparatus_points']['minus'] ?></td>
				<td class="num"><?php echo $team['diff'] ?></td>
			<?php else : ?>
				<td class="num"><?php echo $team['goals']['plus'] ?>:<?php echo $team['goals']['minus'] ?></td>
				<td class="num"><?php echo $team['diff'] ?></td>
			<?php endif; ?>
			<?php  if ( $leaguemanager->isGymnasticsLeague( $league_id ) ) : ?>
				<td class="num"><?php echo $team['points']['plus'] ?>:<?php echo $team['points']['minus'] ?></td>
			<?php else : ?>
				<td class="num"><?php echo $team['points']['plus'] ?></td>
			<?php endif; ?>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
	</form>
	
	<h3><?php _e( 'Match Plan','leaguemanager' ) ?></h3>
	<!-- Bulk Editing of Matches -->
	<form action="admin.php" method="get" style="float: right;">
		<input type="hidden" name="page" value="leaguemanager/match.php" />
		<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
		<select size="1" name="match_day">
			<?php for ($i = 1; $i <= $leaguemanager->getNumMatchDays($league_id); $i++) : ?>
			<option value="<?php echo $i ?>"><?php printf(__( '%d. Match Day', 'leaguemanager'), $i) ?></option>
			<?php endfor; ?>
		</select>
		<input type="submit" value="<?php _e('Edit Matches', 'leaguemanager'); ?>" class="button-secondary action" />
	</form>
	<form id="competitions-filter" action="" method="post">
		<?php wp_nonce_field( 'matches-bulk' ) ?>
		
		<div class="tablenav" style="margin-bottom: 0.1em; clear: none;">
			<!-- Bulk Actions -->
			<select name="action2" size="1">
				<option value="-1" selected="selected"><?php _e('Bulk Actions') ?></option>
				<option value="delete"><?php _e('Delete')?></option>
			</select>
			<input type="submit" value="<?php _e('Apply'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
		</div>
		
		<table class="widefat" summary="" title="<?php _e( 'Match Plan','leaguemanager' ) ?>" style="margin-bottom: 2em;">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="Leaguemanager.checkAll(document.getElementById('competitions-filter'));" /></th>
			<th><?php _e( 'Date','leaguemanager' ) ?></th>
			<th><?php _e( 'Match','leaguemanager' ) ?></th>
			<th><?php _e( 'Location','leaguemanager' ) ?></th>
			<th><?php _e( 'Begin','leaguemanager' ) ?></th>
			<?php if ( $leaguemanager->isGymnasticsLeague( $league_id ) ) : ?>
				<th><?php _e( 'Apparatus Points', 'leaguemanager' ) ?></th>
			<?php endif; ?>
			<th><?php _e( 'Points', 'leaguemanager' ) ?></th>
		</tr>
		</thead>
		<tbody id="the-list" class="form-table">
		<?php if ( $matches = $leaguemanager->getMatches( 'league_id = "'.$league_id.'"' ) ) : ?>
			<?php foreach ( $matches AS $match ) :
				$class = ( 'alternate' == $class ) ? '' : 'alternate';
			?>
			<tr class="<?php echo $class ?>">
				<th scope="row" class="check-column">
					<input type="hidden" name="matches[<?php echo $match->id ?>]" value="<?php echo $match->id ?>" />
					<input type="hidden" name="home_team[<?php echo $match->id ?>]" value="<?php echo $match->home_team ?>" />
					<input type="hidden" name="away_team[<?php echo $match->id ?>]" value="<?php echo $match->away_team ?>" />
					<input type="checkbox" value="<?php echo $match->id ?>" name="match[<?php echo $match->id ?>]" /></th>
				<td><?php echo mysql2date(get_option('date_format'), $match->date) ?></td>
				<td><a href="edit.php?page=leaguemanager/match.php&amp;edit=<?php echo $match->id ?>">
				<?php echo $team_list[$match->home_team]['title'] ?> - <?php echo $team_list[$match->away_team]['title'] ?>
				</td>
				<td><?php echo ( '' == $match->location ) ? 'N/A' : $match->location ?></td>
				<td><?php echo ( '00:00' == $match->hour.":".$match->minutes ) ? 'N/A' : mysql2date(get_option('time_format'), $match->date) ?></td>
				<?php if ( $leaguemanager->isGymnasticsLeague( $league_id ) ) : ?>
				<td><input class="points" type="text" size="2" id="home_apparatus_points[<?php echo $match->id ?>]" name="home_apparatus_points[<?php echo $match->id ?>]" value="<?php echo $match->home_apparatus_points ?>" /> : <input class="points" type="text" size="2" id="away_apparatus_points[<?php echo $match->id ?>]" name="away_apparatus_points[<?php echo $match->id ?>]" value="<?php echo $match->away_apparatus_points ?>" /></td>
				<?php endif; ?>
				<td><input class="points" type="text" size="2" id="home_points[<?php echo $match->id ?>]" name="home_points[<?php echo $match->id ?>]" value="<?php echo $match->home_points ?>" /> : <input class="points" type="text" size="2" id="away_points[<?php echo $match->id ?>]" name="away_points[<?php echo $match->id ?>]" value="<?php echo $match->away_points ?>" /></td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
		
		<?php if ( count($matches) > 0 ) : ?>
			<input type="hidden" name="updateLeague" value="results" />
			<p class="submit"><input type="submit" name="updateResults" value="<?php _e( 'Update Results','leaguemanager' ) ?> &raquo;" class="button" /></p>
		<?php endif; ?>
	</form>
</div>