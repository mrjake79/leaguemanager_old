<script type="javascript">
Leaguemanager.reInit();
</script>
<?php
if ( !current_user_can( 'manage_leaguemanager' ) ) :
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
else :
	$edit = false;
	$myGroup = isset($_GET['group']) ? htmlspecialchars(strip_tags($_GET['group'])) : '';
	if ( isset( $_GET['edit'] ) ) {
		$edit = true;
		$team = $leaguemanager->getTeam(intval($_GET['edit']));
		if ( !isset($team->roster['id']) ) $team->roster = array('id' => '', 'cat_id' => '');
		
		$league_id = intval($team->league_id);
		$form_title = __( 'Edit Team', 'leaguemanager' );
	} else {
		$form_title = __( 'Add Team', 'leaguemanager' );
		$league_id = intval($_GET['league_id']);
		$team = (object)array( 'title' => '', 'home' => 0, 'id' => '', 'logo' => '', 'website' => '', 'coach' => '', 'stadium' => '', 'roster' => array('id' => '', 'cat_id' => '') );
	}
	$league = $leaguemanager->getLeague( $league_id );
	$season = isset($_GET['season']) ? htmlspecialchars(strip_tags($_GET['season'])) : '';

	
	if ( !wp_mkdir_p( $leaguemanager->getImagePath() ) )
		echo "<div class='error'><p>".sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $leaguemanager->getImagePath() )."</p></div>";
	?>

	<div class="wrap league-block">
		<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'LeagueManager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php echo $form_title ?></p>
		<h1><?php printf( "%s &mdash; %s",  $league->title, $form_title ); ?></h1>

		<form action="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league_id ?>&amp;season=<?php echo $season ?><?php if(isset($myGroup)) echo '&amp;group=' . $myGroup; ?>" method="post" enctype="multipart/form-data" name="team_edit">		
		
			<?php wp_nonce_field( 'leaguemanager_manage-teams' ) ?>

			<table class="form-table">
			<tr valign="top">
				<th scope="row" style="width: 225px;"><label for="team"><?php _e( 'Team', 'leaguemanager' ) ?></label></th>
				<td>
					<input type="text" id="team" name="team" value="<?php echo $team->title ?>" />
					<?php if ( !$edit ) : ?>

					<div id="teams_db" style="display: none; overflow: auto; width: 300px; height: 80px;"><div>
					<select size="1" name="team_db_select" id="team_db_select" style="display: block; margin: 0.5em auto;">
						<option value=""><?php _e( 'Choose Team', 'leaguemanager' ) ?></option>
						<?php $this->teamsDropdownCleaned() ?>
					</select>

					<div style='text-align: center; margin-top: 1em;'><input type="button" value="<?php _e('Insert', 'leaguemanager') ?>" class="button-secondary" onClick="Leaguemanager.getTeamFromDatabase(); return false;" />&#160;<input type="button" value="<?php _e('Cancel', 'leaguemanager') ?>" class="button-secondary" onClick="tb_remove();" /></div>
					</div></div>

					<a class="thickbox" href="#TB_inline&amp;width=300&amp;height=80&amp;inlineId=teams_db" title="<?php _e( 'Add Team from Database', 'leaguemanager' ) ?>"><img src="<?php echo LEAGUEMANAGER_URL ?>/admin/icons/database.png" alt="<?php _e( 'Add Team from Database', 'leaguemanager' ) ?>" title="<?php _e( 'Add Team from Database', 'leaguemanager' ) ?>" style="vertical-align: middle;" /></a>
					<?php endif; ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="logo"><?php _e( 'Logo', 'leaguemanager' ) ?></label></th>
				<td>
					<div id="logo_library" style="display: none; overflow: auto;">
						<p style="text-align: center;">http://<input type="text" id="logo_library_url" size="30" /></p>
						<div style='text-align: center; margin-top: 1em;'><input type="button" value="<?php _e('Insert', 'leaguemanager') ?>" class="button-secondary" onClick="Leaguemanager.insertLogoFromLibrary(); return false;" />&#160;<input type="button" value="<?php _e('Cancel', 'leaguemanager') ?>" class="button-secondary" onClick="tb_remove();" /></div>
					</div>
					<div class="alignright" id="logo_db_box">
						<?php if ( '' != $team->logo ) : ?>
						<img id="logo_image" src="<?php echo $leaguemanager->getImageUrl($team->logo, false, 'thumb'); ?>" />
						<?php endif; ?>
					</div>

					<input type="file" name="logo" id="logo" size="35"/>&#160;<a class="thickbox" href="#TB_inline&amp;width=350&amp;height=100&amp;inlineId=logo_library" title="<?php _e( 'Add Logo from Url', 'leaguemanager' ) ?>"><img src="<?php echo LEAGUEMANAGER_URL ?>/admin/icons/image.png" alt="<?php _e( 'Add Logo from Url', 'leaguemanager' ) ?>" title="<?php _e( 'Add Logo from Url', 'leaguemanager' ) ?>" style="vertical-align: middle;" /></a>

					<p><?php _e( 'Supported file types', 'leaguemanager' ) ?>: <?php echo implode( ',',$this->getSupportedImageTypes() ); ?></p>
					
					<?php if ( '' != $team->logo ) : ?>
					<p style="float: left;"><label for="overwrite_image"><?php _e( 'Overwrite existing image', 'leaguemanager' ) ?></label><input type="checkbox" id="overwrite_image" name="overwrite_image" value="1" style="margin-left: 1em;" /><label for="del_logo"><?php _e( 'Delete Logo', 'leaguemanager' ) ?></label><input type="checkbox" id="del_logo" name="del_logo" value="1" style="margin-left: 1em;" /></p>
					<?php endif; ?>
					<input type="hidden" name="logo_db" id="logo_db" value="<?php echo $team->logo ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="website"><?php _e( 'Website', 'leaguemanager' ) ?></label></th><td><input type="text" name="website" id="website" value="<?php echo $team->website ?>" size="30" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="coach"><?php _e( 'Coach', 'leaguemanager' ) ?></label></th><td><input type="text" name="coach" id="coach" value="<?php echo $team->coach ?>" size="40" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="stadium"><?php _e( 'Stadium', 'leaguemanager' ) ?></label></th><td><input type="text" name="stadium" id="stadium" value="<?php echo $team->stadium ?>" size="50" /></td>
			</tr>
			<!--<tr valign="top">
				<th scope="row"><label for="team_default_start_time"><?php _e( 'Default Team Match Start Time', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="team_default_start_time">
					<?php for ( $hour = 0; $hour <= 23; $hour++ ) : ?>
						<option value="<?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?>"<?php selected( $hour, (isset($league->default_match_start_time['hour']) ? ($league->default_match_start_time['hour']) : '' ) ) ?>><?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?></option>
					<?php endfor; ?>
					</select>
					<select size="1" name="settings[default_match_start_time][minutes]">
					<?php for ( $minute = 0; $minute <= 60; $minute++ ) : ?>
						<?php if ( 0 == $minute % 5 && 60 != $minute ) : ?>
						<option value="<?php  echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?>"<?php selected( $minute, (isset($league->default_match_start_time['minutes']) ? ($league->default_match_start_time['minutes']) : '' ) ) ?>><?php echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?></option>
					<?php endif; ?>
					<?php endfor; ?>
					</select>
				</td>
			</tr>-->
			<tr valign="top">
				<th scope="row"><label for="home"><?php _e( 'Home Team', 'leaguemanager' ) ?></label></th><td><input type="checkbox" name="home" id="home"<?php if ($team->home == 1) echo ' checked="checked""' ?>/></td>
			</tr>
			<?php if ( !empty($league->groups) ) : ?>
			<tr valign="top">
				<th scope="row"><label for="group"><?php _e( 'Group', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="group" id="group">
					<?php foreach ( (array)explode(";", $league->groups) AS $group ) : ?>
                	<?php if ( isset( $_GET['edit'] ) ) { ?>
						<option value="<?php echo $group ?>" <?php selected( $group, $team->group ) ?>><?php echo $group ?></option>
                    <?php } else { ?>
						<option value="<?php echo $group ?>" <?php if($group == $myGroup) echo ' selected="selected"' ?>><?php echo $group ?></option>
                    <?php } ?>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<?php endif; ?>
			<?php global $projectmanager; ?>
			<?php if ( $leaguemanager->hasBridge() && isset($projectmanager) ) : ?>
			<?php if ( isset($league->teamprofiles) && $league->teamprofiles['project_id'] > 0 ) : $projectmanager->init($league->teamprofiles['project_id']); ?>
			<tr valign="top">
				<th scope="row"><label for="profile"><?php _e( 'Team Profile', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="profile" id="profile">
						<option value=""><?php _e('None','leaguemanager') ?></option>
						<?php $datasets = $projectmanager->getDatasets( array( "limit" => false ) ); ?>
						<?php foreach ( $datasets AS $dataset ) : ?>
						<option value="<?php echo $dataset->id ?>"<?php selected($dataset->id, $team->profile) ?>><?php echo $dataset->name ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<?php endif; ?>
			<tr valign="top">
				<th scope="row"><label for="roster"><?php _e( 'Team Roster', 'leaguemanager' ) ?></label></th>
				<td>
					<span id="rosterbox"><select size="1" name="roster" id="roster" onChange="Leaguemanager.toggleTeamRosterGroups(this.value);return false;">
						<option value=""><?php _e('None','leaguemanager') ?></option>
						<?php foreach ( $projectmanager->getProjects() AS $roster ) : ?>
						<option value="<?php echo $roster->id ?>"<?php if ( $roster->id == $team->roster['id'] ) echo ' selected="selected"' ?>><?php echo $roster->title ?></option>
						<?php endforeach; ?>
					</select></span>
					<span id="team_roster_groups">
					<?php if ( isset($team->roster['cat_id']) && !empty($team->roster['id']) ) : ?>
						<?php $project = $projectmanager->getProject($team->roster['id']) ?>
						<select size="1" name="roster_group" id="roster_group">
							<option value=""><?php _e( 'Select Group (Optional)', 'leaguemanager' ) ?></option>
							<?php foreach ( $projectmanager->getCategories( $project->id ) AS $category ) : ?>
								<option value='<?php echo $category->id ?>'<?php selected($category->id, $team->roster['cat_id']) ?>><?php echo $category->title ?></option>
							<?php endforeach; ?>
						</select>
						<?php //wp_dropdown_categories(array('hide_empty' => 0, 'child_of' => $project->category,'name' => 'roster_group', 'orderby' => 'name', 'show_option_none' => __('Select Group (Optional)', 'leaguemanager'), 'selected' => $team->roster['cat_id'])); ?>
					<?php endif; ?>
					</span>
				</td>
			</tr>
			<?php endif; ?>

			<?php do_action( 'team_edit_form', $team ) ?>
			<?php do_action( 'team_edit_form_'.(isset($league->sport) ? ($league->sport) : '' ), $team ) ?>
			</table>

			<input type="hidden" name="team_id" value="<?php echo $team->id ?>" />
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<input type="hidden" name="updateLeague" value="team" />
			<input type="hidden" name="season" value="<?php echo $season ?>" />

			<p class="submit"><input type="submit" value="<?php echo $form_title ?>" class="button button-primary" /></p>
		</form>
	</div>
<?php endif; ?>
