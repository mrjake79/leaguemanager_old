<?php
if ( !current_user_can( 'manage_leaguemanager' ) ) :
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';

else :
	$tab = 0;
	$options = get_option('leaguemanager');
	//$league = $leaguemanager->getCurrentLeague();
	$league = $leaguemanager->getLeague( intval($_GET['league_id']) );
	if ( isset($_POST['updateSettings']) ) {
		check_admin_referer('leaguemanager_manage-league-options');

		$settings = (array)$_POST['settings'];

		// Set textdomain
		$options['textdomain'] = (string)$settings['sport'];
		update_option('leaguemanager', $options);

		if ( $settings['point_rule'] == 'user' && isset($_POST['forwin']) && is_numeric($_POST['forwin']) )
			$settings['point_rule'] = array( 'forwin' => intval($_POST['forwin']), 'fordraw' => intval($_POST['fordraw']), 'forloss' => intval($_POST['forloss']), 'forwin_overtime' => intval($_POST['forwin_overtime']), 'forloss_overtime' => intval($_POST['forloss_overtime']) );

		$settings['tiny_size'] = array( 'width' => intval($settings['tiny_size']['width']), 'height' => intval($settings['tiny_size']['height']) );
		$settings['thumb_size'] = array( 'width' => intval($settings['thumb_size']['width']), 'height' => intval($settings['thumb_size']['height']) );
		$settings['large_size'] = array( 'width' => intval($settings['large_size']['width']), 'height' => intval($settings['large_size']['height']) );
		$settings['crop_image']['tiny'] = isset($settings['crop_image']['tiny']) ? 1 : 0;
		$settings['crop_image']['thumb'] = isset($settings['crop_image']['thumb']) ? 1 : 0;
		$settings['crop_image']['large'] = isset($settings['crop_image']['large']) ? 1 : 0;
		$settings['standings']['pld'] = isset($settings['standings']['pld']) ? 1 : 0;
		$settings['standings']['won'] = isset($settings['standings']['won']) ? 1 : 0;
		$settings['standings']['tie'] = isset($settings['standings']['tie']) ? 1 : 0;
		$settings['standings']['lost'] = isset($settings['standings']['lost']) ? 1 : 0;
		
		$settings['slideshow']['show_logos'] = ( isset($settings['slideshow']['show_logos']) && $settings['slideshow']['show_logos'] == 1 ) ? 1 : 0;
		
		$this->editLeague( htmlspecialchars($_POST['league_title']), $settings, intval($_POST['league_id']) );
		$this->printMessage();
		
		$options = get_option('leaguemanager');
		$league = $leaguemanager->getLeague( intval($_GET['league_id']) );
		
		// Set active tab
		$tab = intval($_POST['active-tab']);
	}
	
	$forwin = $fordraw = $forloss = $forwin_overtime = $forloss_overtime = 0;
	// Manual point rule
	if ( is_array($league->point_rule) ) {
		$forwin = $league->point_rule['forwin'];
		$forwin_overtime = $league->point_rule['forwin_overtime'];
		$fordraw = $league->point_rule['fordraw'];
		$forloss = $league->point_rule['forloss'];
		$forloss_overtime = $league->point_rule['forloss_overtime'];
		$league->point_rule = 'user';
	}
	
	if ( isset($_GET['regenerate_thumbnails']) ) {
		$this->regenerateThumbnails();
		$tab = 1;
	}
	
	if ( isset($_GET['cleanUnusedFiles']) ) {
		$this->cleanUnusedMediaFiles();
		$tab = 1;
	}
	
	$menu_page_url = menu_page_url('leaguemanager', 0) . "&amp;subpage=settings&amp;league_id=".$league->id."&amp;season=".htmlspecialchars($_GET['season'])."&amp;group=".htmlspecialchars($_GET['group']);
?>

<script type='text/javascript'>
	jQuery(function() {
		jQuery("#tabs.form").tabs({
			active: <?php echo $tab ?>
		});
	});
</script>
<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php _e( 'League Preferences', 'leaguemanager' ) ?></p>

	<h1><?php printf( "%s &mdash; %s",  $league->title, __( 'Preferences', 'leaguemanager' ) ); ?></h1>
	<form action="" method="post">
		<?php wp_nonce_field( 'leaguemanager_manage-league-options' ) ?>

		<div class="theme-settings-blocks form" id="tabs">
			<input type="hidden" class="active-tab" name="active-tab" value="<?php echo $tab ?>" ?>
			
			<ul id="tablist" style="display: none";>
				<li><a href="#general"><?php _e( 'General', 'leaguemanager' ) ?></a></li>
				<li><a href='#logos'><?php _e( 'Logos', 'projectmanager' ) ?></a></li>
				<li><a href="#standings"><?php _e( 'Standings Table', 'leaguemanager' ) ?></a></li>
				<li><a href="#slideshows"><?php _e( 'Slideshows', 'leaguemanager' ) ?></a></li>
				<li><a href="#advanced"><?php _e( 'Advanced', 'leaguemanager' ) ?></a></li>
			</ul>
			
			<div id='general' class='settings-block-container'>
				<h2><?php _e( 'General', 'projectmanager' ) ?></h2>
				<div class="settings-block">
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><label for="league_title"><?php _e( 'Title', 'leaguemanager' ) ?></label></th><td><input type="text" name="league_title" id="league_title" value="<?php echo $league->title ?>" size="30" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="sport"><?php _e( 'Sport', 'leaguemanager' ) ?></label></th>
							<td>
								<select size="1" name="settings[sport]" id="sport">
									<?php foreach ( $leaguemanager->getLeagueTypes() AS $id => $title ) : ?>
										<option value="<?php echo $id ?>"<?php selected( $id, $league->sport ) ?>><?php echo $title ?></option>
									<?php endforeach; ?>
								</select>
								<span class="setting-description"><?php printf( __( "Check the <a href='%s'>Documentation</a> for details", 'leaguemanager'), admin_url() . 'admin.php?page=leaguemanager-doc' ) ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="point_rule"><?php _e( 'Point Rule', 'leaguemanager' ) ?></label></th>
							<td>
								<select size="1" name="settings[point_rule]" id="point_rule" onchange="Leaguemanager.checkPointRule(<?php echo $forwin ?>, <?php echo $forwin_overtime ?>, <?php echo $fordraw ?>, <?php echo $forloss ?>, <?php echo $forloss_overtime ?>)">
								<?php foreach ( $this->getPointRules() AS $id => $point_rule ) : ?>
								<option value="<?php echo $id ?>"<?php selected( $id, $league->point_rule ) ?>><?php echo $point_rule ?></option>
								<?php endforeach; ?>
								</select>
								<span class="setting-description"><?php printf( __("For details on point rules see the <a href='%s'>Documentation</a>", 'leaguemanager'), admin_url() . 'admin.php?page=leaguemanager-doc' ) ?></span>
								<div id="point_rule_manual" style="display: block;">
								<?php if ( $league->point_rule == 'user' ) : ?>
									<div id="point_rule_manual_content">
										<input type='text' name='forwin' id='forwin' value='<?php echo $forwin ?>' size='2' />
										<input type='text' name='forwin_overtime' id='forwin_overtime' value='<?php echo $forwin_overtime ?>' size='2' />
										<input type='text' name='fordraw' id='fordraw' value='<?php echo $fordraw ?>' size='2' />
										<input type='text' name='forloss' id='forloss' value='<?php echo $forloss ?>' size='2' />
										<input type='text' name='forloss_overtime' id='forloss_overtime' value='<?php echo $forloss_overtime ?>' size='2' />
										&#160;<span class='setting-description'><?php _e( 'Order: win, win overtime, tie, loss, loss overtime', 'leaguemanager' ) ?></span>
									</div>
								<?php endif; ?>
								</div>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="point_format"><?php _e( 'Point Format', 'leaguemanager' ) ?></label></th>
							<td>
								<select size="1" name="settings[point_format]" id="point_format" >
								<?php foreach ( $this->getPointFormats() AS $id => $format ) : ?>
								<option value="<?php echo $id ?>"<?php selected ( $id, $league->point_format ) ?>><?php echo $format ?></option>
								<?php endforeach; ?>
								</select>
								<select size="1" name="settings[point_format2]" id="point_format2" >
								<?php foreach ( $this->getPointFormats() AS $id => $format ) : ?>
								<option value="<?php echo $id ?>"<?php selected ( $id, $league->point_format2 ); ?>><?php echo $format ?></option>
								<?php endforeach; ?>
								</select>
								&#160;<span class="setting-description"><?php _e( 'Point formats for primary and seconday points (e.g. Goals)', 'leaguemanager' ) ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="team_ranking"><?php _e( 'Team Ranking', 'leaguemanager' ) ?></label></th>
							<td>
								<select size="1" name="settings[team_ranking]" id="team_ranking" >
									<option value="auto"<?php selected( 'auto', $league->team_ranking  ) ?>><?php _e( 'Automatic', 'leaguemanager' ) ?></option>
									<option value="manual"<?php selected( 'manual', $league->team_ranking  ) ?>><?php _e( 'Manual', 'leaguemanager' ) ?></option>
								</select>
								<!--&#160;<span class="setting-description"><?php _e( 'Team Ranking via Drag & Drop probably will only work in Firefox', 'leaguemanager' ) ?></span>-->
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="mode"><?php _e( 'Mode', 'leaguemanager' ) ?></label></th>
							<td>
								<select size="1" name="settings[mode]" id="mode">
								<?php foreach ( $this->getModes() AS $id => $mode ) : ?>
									<option value="<?php echo $id ?>"<?php selected( $id, $league->mode ) ?>><?php echo $mode ?></option>
								<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<!--<tr valign="top">
							<th scope="row"><label for="upload_dir"><?php _e( 'Upload Directory', 'leaguemanager' ) ?></label></th>
							<td><input type="text" size="40" name="settings[upload_dir]" id="upload_dir" value="<?php echo $league->upload_dir ?>" /></td>
						</tr>-->
						<tr valign="top">
							<th scope="row"><label for="default_start_time"><?php _e( 'Default Match Start Time', 'leaguemanager' ) ?></label></th>
							<td>
								<select size="1" name="settings[default_match_start_time][hour]">
								<?php for ( $hour = 0; $hour <= 23; $hour++ ) : ?>
									<option value="<?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?>"<?php selected( $hour, $league->default_match_start_time['hour'] ) ?>><?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?></option>
								<?php endfor; ?>
								</select>
								<select size="1" name="settings[default_match_start_time][minutes]">
								<?php for ( $minute = 0; $minute <= 60; $minute++ ) : ?>
									<?php if ( 0 == $minute % 5 && 60 != $minute ) : ?>
									<option value="<?php  echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?>"<?php selected( $minute, $league->default_match_start_time['minutes'] ) ?>><?php echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?></option>
								<?php endif; ?>
								<?php endfor; ?>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="num_matches_per_page"><?php _e( 'Matches per page', 'leaguemanager' ) ?></label></th>
							<td><input type="number" step="1" min="0" class="small-text" name="settings[num_matches_per_page]" id="num_matches_per_page" value="<?php echo $league->num_matches_per_page ?>" size="2" />&#160;<span class="setting-description"><?php _e( 'Number of matches to show per page', 'leaguemanager' ) ?></span></td>
						</tr>
						<?php global $projectmanager; ?>
						<?php if ( $leaguemanager->hasBridge() && isset($projectmanager) ) : $projects = $projectmanager->getProjects(); ?>
						<tr valign="top">
							<th scope="row"><label for="teamprofiles_project_id"><?php _e( 'Team Profile', 'leaguemanager' ) ?></label></th>
							<td>
								<select size="1" name="settings[teamprofiles][project_id]" id="teamprofiles_project_id">
									<option value=""><?php _e('None','leaguemanager') ?></option>
									<?php foreach ( $projects AS $project ) : ?>
									<option value="<?php echo $project->id ?>"<?php selected($project->id, $league->teamprofiles['project_id']) ?>><?php echo $project->title ?></option>
									<?php endforeach; ?>
								</select>
								<select size='1' name='settings[teamprofiles][cat_id]' id="teamprofiles_cat_id">
									<option value=""><?php _e('Category','projectmanager') ?></option>
									<?php foreach ( $projects AS $project ) : ?>
										<optgroup label="<?php echo $project->title ?>">
											<?php foreach ( $projectmanager->getCategories( $project->id ) AS $category ) : ?>
											<option value='<?php echo $category->id ?>'<?php selected($category->id, $league->teamprofiles['cat_id']) ?>><?php echo $category->title ?></option>
											<?php endforeach; ?>
										</optgroup>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<?php endif; ?>
					</table>
				</div>
			</div>
			
			<div id='logos' class='settings-block-container'>
				<h2><?php _e( 'Logos', 'leaguemanager' ) ?></h2>
				<div class="settings-block">
					<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="thumb_size"><?php _e( 'Tiny size', 'leaguemanager' ) ?></label></th><td><label for="tiny_width"><?php _e( 'Max Width' ) ?>&#160;</label><input type="number" step="1" min="0" class="small-text" name="settings[tiny_size][width]" id="tiny_width" value="<?php echo $league->tiny_size['width'] ?>" />  <label for="tiny_height"><?php _e( 'Max Height' ) ?>&#160;</label><input type="number" step="1" min="0" class="small-text" name="settings[tiny_size][height]" id="tiny_height" value="<?php echo $league->tiny_size['height'] ?>" /><p><input type="checkbox" value="1" name="settings[crop_image][tiny]" <?php checked( 1, $league->crop_image['tiny'] ) ?> id="crop_image_tiny" /><label for="crop_image_tiny"><?php _e( 'Crop image to exact dimensions', 'leaguemanager') ?></label></p></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="thumb_size"><?php _e( 'Thumbnail size', 'leaguemanager' ) ?></label></th><td><label for="thumb_width"><?php _e( 'Max Width' ) ?>&#160;</label><input type="number" step="1" min="0" class="small-text" name="settings[thumb_size][width]" id="thumb_width" value="<?php echo $league->thumb_size['width'] ?>" />  <label for="thumb_height"><?php _e( 'Max Height' ) ?>&#160;</label><input type="number" step="1" min="0" class="small-text" name="settings[thumb_size][height]" id="thumb_height" value="<?php echo $league->thumb_size['height'] ?>" /><p><input type="checkbox" value="1" name="settings[crop_image][thumb]" <?php checked( 1, $league->crop_image['thumb'] ) ?> id="crop_image_thumb" /><label for="crop_image_thumb"><?php _e( 'Crop image to exact dimensions', 'leaguemanager') ?></label></p></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="large_size"><?php _e( 'Large size', 'leaguemanager' ) ?></label></th><td><label for="large_width"><?php _e( 'Max Width' ) ?>&#160;</label><input type="number" step="1" min="0" class="small-text" id="large_width" name="settings[large_size][width]" value="<?php echo $league->large_size['width'] ?>" /> <label for="large_height"><?php _e( 'Max Height' ) ?>&#160;</label> <input type="number" step="1" min="0" class="small-text" id="large_height" name="settings[large_size][height]" value="<?php echo $league->large_size['height'] ?>" /><p><input type="checkbox" value="1" name="settings[crop_image][large]" <?php checked( 1, $league->crop_image['large'] ) ?> id="crop_image_large" /><label for="crop_image_large"><?php _e( 'Crop image to exact dimensions', 'leaguemanager') ?></label></p></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="regenerate_thumbnails"><?php _e( 'Regenerate Thumbnails', 'leaguemanager' ) ?></th><td><a href="<?php echo $menu_page_url ?>&amp;regenerate_thumbnails" class="button button-secondary"><?php _e( 'Regenerate Thumbnails Now', 'leaguemanager' ) ?></a><p class="setting-description"><?php _e( 'This will re-create all thumbnail images of this project. Depending on the number of images it could take some time.', 'leaguemanager' ) ?></p></td>
					</tr>
					</table>
					
					<p><a href="<?php echo $menu_page_url ?>&amp;cleanUnusedFiles" class="button-secondary"><?php _e( 'List unused media files', 'leaguemanager' ) ?></a></p>
				</div>
			</div>
			
			<div id='standings' class='settings-block-container'>
				<h2><?php _e( 'Standings Table', 'leaguemanager' ) ?></h2>
				<div class="settings-block">
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><label for="standings_table"><?php _e( 'Standings Table Display', 'leaguemanager' ) ?></label></th>
							<td>
								<p><input type="checkbox" name="settings[standings][pld]" id="standings_pld" value="1" <?php checked(1, $league->standings['pld']) ?> /><label for="standings_pld" style="margin-left: 0.5em;"><?php _e( 'Played Games', 'leaguemanager' ) ?></label></p>
								<p><input type="checkbox" name="settings[standings][won]" id="standings_won" value="1" <?php checked(1, $league->standings['won']) ?> /><label for="standings_won" style="margin-left: 0.5em;"><?php _e( 'Won Games', 'leaguemanager' ) ?></label></p>
								<p><input type="checkbox" name="settings[standings][tie]" id="standings_tie" value="1" <?php checked(1, $league->standings['tie']) ?> /><label for="standings_tie" style="margin-left: 0.5em;"><?php _e('Tie Games', 'leaguemanager' ) ?></label></p>
								<p><input type="checkbox" name="settings[standings][lost]" id="standings_lost" value="1" <?php checked(1, $league->standings['lost']) ?> /><label for="standings_lost" style="margin-left: 0.5em;"><?php _e( 'Lost Games', 'leaguemanager' ) ?></label></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="teams_ascend"><?php _e( 'Teams Ascend', 'leaguemanager' ) ?></label></th>
							<td><input type="number" step="1" min="0" class="small-text" name="settings[num_ascend]" id="teams_ascend" value="<?php echo $league->num_ascend ?>" size="2" />&#160;<span class="setting-description"><?php _e( 'Number of Teams that ascend into higher league', 'leaguemanager' ) ?></span></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="teams_descend"><?php _e( 'Teams Descend', 'leaguemanager' ) ?></label></th>
							<td><input type="number" step="1" min="0" class="small-text" name="settings[num_descend]" id="teams_descend" value="<?php echo $league->num_descend ?>" size="2" />&#160;<span class="setting-description"><?php _e( 'Number of Teams that descend into lower league', 'leaguemanager' ) ?></span></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="teams_relegation"><?php _e( 'Teams Relegation', 'leaguemanager' ) ?></label></th>
							<td><input type="number" step="1" min="0" class="small-text" name="settings[num_relegation]" id="teams_relegation" value="<?php echo $league->num_relegation ?>" size="2" />&#160;<span class="setting-description"><?php _e( 'Number of Teams that need to go into relegation', 'leaguemanager' ) ?></span></td>
						</tr>
					</table>
				</div>
			</div>
				
			<div id='slideshows' class='settings-block-container'>
				<h2><?php _e( 'Slideshows', 'projectmanager' ) ?></h2>
				<div class="settings-block">
					<?php if ( is_plugin_active("sponsors-slideshow-widget/sponsors-slideshow-widget.php") ) : ?>
					<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="slideshow_season"><?php _e( 'Season', 'leaguemanager' ) ?></label></th>
						<td>
							<select size="1" name="settings[slideshow][season]" id="slideshow_season">
								<?php foreach ( $league->seasons AS $season ) : ?>
								<option value="<?php echo $season['name'] ?>"<?php selected($season['name'], $league->slideshow["season"]) ?>><?php echo $season['name'] ?></option>
								<?php endforeach; ?>
								<option value="latest"<?php selected('latest', $league->slideshow["season"]) ?>><?php _e( 'Latest', 'leaguemanager' ) ?></option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="slideshow_num_matches"><?php _e( 'Number of Matches', 'projectmanager' ) ?></label></th><td><input type="number" step="1" min="0" class="small-text" name="settings[slideshow][num_matches]" id="slideshow_num_matches" size="2" value="<?php echo intval($league->slideshow["num_matches"]) ?>" /><?php _e( 'Set to 0 for no limit', 'projectmanager' ) ?></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="slideshow_show_logos"><?php _e( 'Show Logos', 'projectmanager' ) ?></label></th><td><input type="checkbox" name="settings[slideshow][show_logos]" id="slideshow_show_logos" value="1" <?php checked(1, $league->slideshow['show_logos']) ?>/></td>
					</tr>
					</table>
					<?php else : ?>
					<p><?php printf(__("You can easily create fancy slideshows of datasets using the <a href='%s' target='_blank'>Fancy Slideshows Plugin</a>. After installing and activating the plugin, come back here for slideshow configuration. There are some restrictions and required settings, please look at the <a href='%s'>Documentation</a>.", 'leaguemanager'), "https://wordpress.org/plugins/sponsors-slideshow-widget/", "admin.php?page=leaguemanager-doc") ?></p>
					<?php endif; ?>
				</div>
			</div>
				
			<div id='advanced' class="settings-block-container">
				<h2><?php _e( 'Advanced', 'leaguemanager' ) ?></h2>
				<div class="settings-block">
					<table class="form-table">
						<?php do_action( 'league_settings_'.$league->sport, $league ); ?>
						<?php do_action( 'league_settings_'.$league->mode, $league ); ?>
						<?php do_action( 'league_settings', $league ); ?>
					</table>
				</div>
			</div>
		</div>
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
		<p class="submit"><input type="submit" name="updateSettings" value="<?php _e( 'Save Preferences', 'leaguemanager' ) ?>" class="button button-primary" /></p>
	</form>
</div>

<?php endif; ?>
