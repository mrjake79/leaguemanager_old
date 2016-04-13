<?php
global $championship;

$finalkey = isset($_GET['final']) ? htmlspecialchars($_GET['final']) : $championship->getFinalKeys(1);

$league = $leaguemanager->getLeague( intval($_GET['league_id']) );
$season = $leaguemanager->getSeason( $league );
$num_first_round = $championship->getNumTeamsFirstRound();
$class = 'alternate';
if ( empty($group) ) {
	$group_tmp = ((array)explode(";", $league->groups));
	$group = $group_tmp[0];
}

if ( isset($_POST['startFinals']) ) {
	$championship->startFinalRounds($league->id);
}

if ( isset($_POST['updateFinalResults']) ) {
	if ( !is_numeric(end($_POST['home_team'])) ) {
		$leaguemanager->setMessage(__( "It seems the previous round is not over yet.", 'leaguemanager'), true);
		$leaguemanager->printMessage();
	} else {
		$custom = isset($_POST['custom']) ? $_POST['custom'] : false;
		$championship->updateResults(intval($_POST['league_id']), $_POST['matches'], $_POST['home_points'], $_POST['away_points'], $_POST['home_team'], $_POST['away_team'], $custom, $_POST['round']);
	}
}
$tab = 0;
if (isset($_GET['jquery-ui-tab'])) $tab = intval($_GET['jquery-ui-tab']);
if (isset($_POST['jquery-ui-tab'])) $tab = intval($_POST['jquery-ui-tab']);

?>
<script type='text/javascript'>
	jQuery(function() {
		jQuery("#tabs").tabs({
			activate: function(event ,ui){
				jQuery(".jquery_ui_tab_index").val(ui.newTab.index());
			},
			active: <?php echo $tab ?>
		});
	});
</script>

<div class="wrap">
	<!--<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'LeagueManager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php _e( 'Championship Finals', 'leaguemanager') ?></p>-->

	<div class="alignright" style="margin-right: 1em;">
		<form action="admin.php" method="get" style="display: inline;">
			<input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page']) ?>" />
			<input type="hidden" name="subpage" value="<?php echo htmlspecialchars($_GET['subpage']) ?>" />
			<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
			<select name="group" size="1">
			<?php foreach ( (array)explode(";", $league->groups) AS $key => $g ) : ?>
				<option value="<?php echo $g ?>"<?php selected($g, $group) ?>><?php printf(__('Group %s','leaguemanager'), $g) ?></option>
			<?php endforeach; ?>
			</select>
			<input type="hidden" name="jquery-ui-tab" value="<?php echo $tab ?>" class="jquery_ui_tab_index" />
			<input type="submit" class="button-secondary" value="<?php _e( 'Show', 'leaguemanager' ) ?>" />
		</form>
	</div>
	
	<div id="tabs" class="championship-blocks">
		<ul id="tablist" style="display: none">
			<li><a href="#finalresults"><?php _e( 'Final Results', 'leaguemanager' ) ?></a></li>
			<li><a href="#finals"><?php _e( 'Finals', 'leaguemanager' ) ?></a></li>
			<li><a href="#preliminary"><?php _e( 'Preliminary Rounds', 'leaguemanager' ) ?></a></li>
		</ul>
		
		<div id="finalresults" class="championship-block-container">
		<h2><?php _e( 'Final Results', 'leaguemanager' ) ?></h2>
			<div class="championship-block">
				<table class="widefat">
				<thead>
				<tr>
					<th scope="col"><?php _e( 'Round', 'leaguemanager' ) ?></th>
					<th scope="col" colspan="<?php echo ($num_first_round > 4) ? 4 : $num_first_round; ?>" style="text-align: center;"><?php _e( 'Matches', 'leaguemanager' ) ?></th>
				</tr>
				<tbody id="the-list-finals" class="form-table">
				<?php foreach ( $championship->getFinals() AS $final ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
				<?php
					if ( $matches = $leaguemanager->getMatches( array("league_id" => $league->id, "season" => $season['name'], "final" => $final['key'], "orderby" => array("id" => "ASC"))) ) {
						$teams = $leaguemanager->getTeams( array("league_id" => $league->id, "season" => $season['name']), 'ARRAY' );
						$teams2 = $championship->getFinalTeams( $final, 'ARRAY' );
					}
				?>
					<tr class="<?php echo $class ?>">
						<th scope="row" style="padding-left: 1em;"><strong><?php echo $final['name'] ?></strong></th>
						<?php for ( $i = 1; $i <= $final['num_matches']; $i++ ) : ((isset($matches[0])) ? $match = $matches[$i-1] : 0); ?>
						<?php $colspan = ( $num_first_round/2 >= 4 ) ? ceil(4/$final['num_matches']) : ceil(($num_first_round/2)/$final['num_matches']); ?>
						<td colspan="<?php echo $colspan ?>" style="text-align: center;">
							<?php if ( isset($match) ) : ?>

							<?php 
							$match->hadPenalty = $match->hadPenalty = ( isset($match->penalty) && $match->penalty['home'] != '' && $match->penalty['away'] != '' ) ? true : false;
							$match->hadOvertime = $match->hadOvertime = ( isset($match->overtime) && $match->overtime['home'] != '' && $match->overtime['away'] != '' ) ? true : false;
							
							if ( is_numeric($match->home_team) && is_numeric($match->away_team) ) {
								//$title = sprintf("%s &#8211; %s", $teams[$match->home_team]['title'], $teams[$match->away_team]['title']);
								$title = $leaguemanager->getMatchTitle($match->id);
							} else {
								$title = sprintf("%s &#8211; %s", $teams2[$match->home_team], $teams2[$match->away_team]);
							}  
							?>
							<?php //if ( isset($teams[$match->home_team]) && isset($teams[$match->away_team]) ) : ?>
								<?php if ( $final['key'] == 'final' ) : ?>
								<p><span id="final_home" style="margin-right: 0.5em;"></span><?php echo $title;//printf('%s &#8211; %s', $teams[$match->home_team]['title'], $teams[$match->away_team]['title']) ?><span id="final_away" style="margin-left: 0.5em;"></span></p>
								<?php else : ?>
								<p><?php echo $title;//printf('%s &#8211; %s', $teams[$match->home_team]['title'], $teams[$match->away_team]['title']) ?></p>
								<?php endif; ?>

								<?php if ( $match->home_points != NULL && $match->away_points != NULL ) : ?>
									<?php if ( $final['key'] == 'final' ) : ?>
									<?php $field_id = ( $match->winner_id == $match->home_team ) ? "final_home" : "final_away"; ?>
									<script type="text/javascript">
										<?php $img = '<img style="vertical-align: middle;" src="'.LEAGUEMANAGER_URL . '/admin/icons/cup.png" />'; ?>
										jQuery('span#<?php echo $field_id ?>').html('<?php echo addslashes_gpc($img) ?>').fadeIn('fast');
									</script>
									<?php endif; ?>

									<?php
									if ( $match->hadPenalty )
										$match->score = sprintf("%d:%d", $match->overtime['home'] + $match->penalty['home'], $match->overtime['away'] + $match->penalty['away'])." "._x( 'o.P.', 'leaguemanager' );
									elseif ( $match->hadOvertime )
										$match->score = sprintf("%d:%d", $match->overtime['home'], $match->overtime['away'])." "._x( '(AET)', 'leaguemanager' );
									else
										$match->score = sprintf("%d:%d", $match->home_points, $match->away_points);
									?>
									<p><strong><?php echo $match->score ?></strong></p>
								<?php else : ?>
									<p>-:-</p>
								<?php endif; ?>
							<?php //else : ?>
								<!--&#8211;-->
							<?php //endif; ?>

							<?php endif; ?>
						</td>
						<?php if ( $i%4 == 0 && $i < $final['num_matches'] ) : ?>
						</tr><tr class="<?php echo $class ?>"><th>&#160;</th>
						<?php endif; ?>

						<?php endfor; ?>
					</tr>
				<?php endforeach ?>
				</tbody>
				</table>
			</div>
		</div>
		
		<div id="finals" class="championship-block-container">
			<h2><?php printf(__( 'Finals &#8211; %s', 'leaguemanager' ), $championship->getFinalName($finalkey)) ?></h2>
			<div class="championship-block">			
				<div class="tablenav">
				<form action="admin.php" method="get" style="display: inline;">
					<input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page']) ?>" />
					<input type="hidden" name="subpage" value="<?php echo htmlspecialchars($_GET['subpage']) ?>" />
					<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />

					<select size="1" name="final" id="final">
						<?php foreach ( $championship->getFinals() AS $final ) : ?>
						<option value="<?php echo $final['key'] ?>"<?php selected($finalkey, $final['key']) ?>><?php echo $final['name'] ?></option>	
						<?php endforeach; ?>
					</select>
					<input type="hidden" name="jquery-ui-tab" value="<?php echo $tab ?>" class="jquery_ui_tab_index" />
					<input type="submit" class="button-secondary" value="<?php _e( 'Show', 'leaguemanager' ) ?>" />
				</form>
				<form action="admin.php" method="get" style="display: inline;">
					<input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page']) ?>" />
					<input type="hidden" name="subpage" value="match" />
					<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />

					<!-- Bulk Actions -->
					<select name="mode" size="1">
						<option value="-1" selected="selected"><?php _e('Actions', 'leaguemanager') ?></option>
						<option value="add"><?php _e('Add Matches', 'leaguemanager')?></option>
						<option value="edit"><?php _e( 'Edit Matches', 'leaguemanager' ) ?></option>
					</select>

					<select size="1" name="final" id="final1">
					<?php foreach ( $championship->getFinals() AS $final ) : ?>
						<option value="<?php echo $final['key'] ?>"><?php echo $final['name'] ?></option>
					<?php endforeach; ?>
					</select>
					<input type="hidden" name="jquery-ui-tab" value="<?php echo $tab ?>" class="jquery_ui_tab_index" />
					<input type="submit" class="button-secondary" value="<?php _e( 'Go', 'leaguemanager' ) ?>" />
				</form>
				</div>

				<?php $final = $championship->getFinals($finalkey); ?>
				<!--<h3><?php echo $final['name'] ?></h3>-->
				<?php $teams = $leaguemanager->getTeams( array("league_id" => $league->id, "season" => $season['name']), 'ARRAY' ); ?>
				<?php $teams2 = $championship->getFinalTeams( $final, 'ARRAY' ); ?>
				<?php $matches = $leaguemanager->getMatches( array("league_id" => $league->id, "final" => (!empty($final['key']) ? $final['key'] : '' ), "orderby" => array("id" => "ASC")) ); ?>

				<form method="post" action="">
				<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
				<input type="hidden" name="round" value="<?php echo $final['round'] ?>" />
				<input type="hidden" name="jquery-ui-tab" value="<?php echo $tab ?>" class="jquery_ui_tab_index" />
				
				<table class="widefat">
				<thead>
				<tr>
					<th><?php _e( '#', 'leaguemanager' ) ?></th>
					<th><?php _e( 'ID', 'leaguemanager' ) ?></th>
					<th><?php _e( 'Date','leaguemanager' ) ?></th>
					<th style="text-align: center;"><?php _e( 'Match','leaguemanager' ) ?></th>
					<th><?php _e( 'Location','leaguemanager' ) ?></th>
					<th><?php _e( 'Begin','leaguemanager' ) ?></th>
					<th style="text-align: center;"><?php _e( 'Score', 'leaguemanager' ) ?></th>
					<?php do_action( 'matchtable_header_'.$league->sport ); ?>
				</tr>
				</thead>
				<tbody id="the-list-<?php echo $final['key'] ?>" class="form-table">
				<?php for ( $i = 1; $i <= ( isset($final['num_matches']) ? $final['num_matches'] : 0 ); $i++ ) : ( isset($matches[0]) ) ? $match = $matches[$i-1] : 0; ?>
					<?php 
					$class = ( 'alternate' == $class ) ? '' : 'alternate';
					$title = "N/A";
					if ( ( isset($match)) && ((is_numeric($match->home_team)) && (is_numeric($match->away_team))) ) {
						//$title = sprintf("%s &#8211; %s", $teams[$match->home_team]['title'], $teams[$match->away_team]['title']);
						$title = $leaguemanager->getMatchTitle($match->id);
					} elseif ( (isset($match)) ) {
						$title = sprintf("%s &#8211; %s", $teams2[$match->home_team], $teams2[$match->away_team]);
					}     
					?>
					<tr class="<?php echo $class ?>">
						<td><?php echo $i ?><input type="hidden" name="matches[<?php echo $match->id ?>]" value="<?php echo $match->id ?>" /><input type="hidden" name="home_team[<?php echo $match->id ?>]" value="<?php echo $match->home_team ?>" /><input type="hidden" name="away_team[<?php echo $match->id ?>]" value="<?php echo $match->away_team ?>" /></td>
						<td><?php echo $match->id ?></td>
						<td><?php echo ( isset($match->date) ) ? mysql2date(get_option('date_format'), $match->date) : 'N/A' ?></td>
						<td style="text-align: center;"><?php echo $title ?></td>
						<td><?php echo ( isset($match->location) ) ? $match->location : 'N/A' ?></td>
						<td><?php echo ( isset($match->hour) ) ? mysql2date(get_option('time_format'), $match->date) : 'N/A' ?></td>
						<td style="text-align: center;">
							<input class="points" type="text" size="2" style="text-align: center;" id="home_points[<?php echo $match->id ?>]" name="home_points[<?php echo $match->id ?>]" value="<?php echo ((isset($match->home_points)) ? $match->home_points : '') ?>" /> : <input class="points" type="text" size="2" style="text-align: center;" id="away_points[<?php echo $match->id ?>]" name="away_points[<?php echo $match->id ?>]" value="<?php echo ((isset($match->away_points)) ? $match->away_points : '') ?>" />
						</td>
						<?php do_action( 'matchtable_columns_'.$league->sport, ( ( isset($match) ) ? $match : '' ) ) ?>
					</tr>
				<?php endfor; ?>
				</tbody>
				</table>

				<p class="submit"><input type="submit" name="updateFinalResults" value="<?php _e( 'Save Results','leaguemanager' ) ?>" class="button-primary" /></p>
				</form>
			</div>
		</div>
		
		<div id='preliminary' class="championship-block-container">
			<h2><?php _e( 'Preliminary Rounds', 'leaguemanager' ) ?></h2>
			<div class="championship-block">
				<form action="" method="post" style="display: inline;">
					<input type="hidden" name="jquery-ui-tab" value="1" />
					<p><?php _e( 'After the preliminary rounds are complete carefully check your results and then ', 'leaguemanager' ) ?><input type="submit" class="button-secondary" value="<?php _e( 'Proceed to Final Rounds', 'leaguemanager' ) ?>" name="startFinals" /> <?php _e( 'Afterwards changes to preliminary results will NOT affect the final results', 'leaguemanager' ) ?></p>
				</form>
					
				<div class="alignright">
					<form action="admin.php" method="get" style="display: inline;">
						<input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page']) ?>" />
						<input type="hidden" name="subpage" value="<?php echo htmlspecialchars($_GET['subpage']) ?>" />
						<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />

						<select name="group" size="1">
						<?php foreach ( (array)explode(";", $league->groups) AS $key => $g ) : ?>
						<option value="<?php echo $g ?>"<?php selected($g, $group) ?>><?php printf(__('Group %s','leaguemanager'), $g) ?></option>
						<?php endforeach; ?>
						</select>
						<input type="hidden" name="jquery-ui-tab" value="<?php echo $tab ?>" class="jquery_ui_tab_index" />
						<input type="submit" class="button-secondary" value="<?php _e( 'Show', 'leaguemanager' ) ?>" />
					</form>
				</div>

				<?php $teams = $leaguemanager->getTeams( array("league_id" => $league->id, "season" => $season['name'], "group" => $group) ); ?>
				<h3><?php printf(__( 'Table &#8211; Group %s', 'leaguemanager' ), $group) ?></h3>
				<?php include('standings.php'); ?>
				
				<?php $matches = $leaguemanager->getMatches( array("league_id" => $league->id, "season" => $season['name'], "final" => '', "group" => $group) ); ?>
				<h3><?php printf(__( 'Match Plan &#8211; Group %s','leaguemanager' ), $group) ?></h3>
				<?php include('matches.php'); ?>

				<?php $matches = $leaguemanager->getMatches( array("league_id" => $league->id, "season" => $season['name'], "final" => '', "group" => "") ); ?>
				<?php if ( $matches ) : ?>
				<h3><?php _e( 'Inter Group Matches', 'leaguemanager' ) ?></h3>
				<?php include('matches.php'); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
