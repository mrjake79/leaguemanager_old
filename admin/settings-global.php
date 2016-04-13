<?php

if ( !current_user_can( 'manage_leaguemanager' ) ) :
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
else :

?>
<script type='text/javascript'>
	jQuery(function() {
		jQuery("#tabs.form").tabs({
			active: <?php echo $tab ?>
		});
	});
</script>

<form action='' method='post' name='settings'>
<?php wp_nonce_field( 'leaguemanager_manage-global-league-options' ); ?>

<div class='wrap'>
	<h1><?php _e( 'Leaguemanager Global Settings', 'leaguemanager' ) ?></h1>
	<div class="settings-blocks form" id="tabs">
		<input type="hidden" class="active-tab" name="active-tab" value="<?php echo $tab ?>" ?>
		
		<ul id="tablist" style="display: none;">
			<li><a href="#colors"><?php _e( 'Color Scheme', 'projectmanager' ) ?></a></li>
			<li><a href="#dashboard-widget"><?php _e( 'Dashboard Widget Support News', 'projectmanager' ) ?></a></li>
		</ul>
		
		<div id="colors" class="settings-block-container">
			<h2><?php _e( 'Color Scheme', 'leaguemanager' ) ?></h2>
			<div class="settings-block">
				<table class='form-table'>
				<tr valign='top'>
					<th scope='row'><label for='color_headers'><?php _e( 'Table Headers', 'leaguemanager' ) ?></label></th><td><input type='text' name='color_headers' id='color_headers' value='<?php echo ( isset($options['colors']['headers']) ? ($options['colors']['headers']) : '' ) ?>' size='7' class="leaguemanager-colorpicker color" /><span class="colorbox" style="background-color: <?php echo $options['colors']['headers'] ?>"></span></td>
				</tr>
				<tr valign='top'>
					<th scope='row'><label for='color_rows'><?php _e( 'Table Rows', 'leaguemanager' ) ?></label></th>
					<td>
						<p class='table_rows'><input type='text' name='color_rows_alt' id='color_rows_alt' value='<?php echo (isset($options['colors']['rows']['alternate']) ? ($options['colors']['rows']['alternate']) : '' ) ?>' size='7' class="leaguemanager-colorpicker color" /><span class="colorbox" style="background-color: <?php echo $options['colors']['rows']['alternate'] ?>"></span></p>
						<p class='table_rows'><input type='text' name='color_rows' id='color_rows' value='<?php echo ( isset($options['colors']['rows']['main']) ? ($options['colors']['rows']['main']) : '' ) ?>' size='7' class="leaguemanager-colorpicker color" /><span class="colorbox" style="background-color: <?php echo $options['colors']['rows']['main'] ?>"></span></p>
					</td>
				</tr>
				<tr valign='top'>
					<th scope='row'><label for='color_rows_ascend'><?php _e( 'Teams Ascend', 'leaguemanager' ) ?></label></th><td><input type='text' name='color_rows_ascend' id='color_rows_ascend' value='<?php echo ( isset($options['colors']['rows']['ascend']) ? ($options['colors']['rows']['ascend']) : '' ) ?>' size='7' class="leaguemanager-colorpicker color" /><span class="colorbox" style="background-color: <?php echo $options['colors']['rows']['ascend'] ?>"></span></td>
				</tr>
				<tr valign='top'>
					<th scope='row'><label for='color_rows_descend'><?php _e( 'Teams Descend', 'leaguemanager' ) ?></label></th><td><input type='text' name='color_rows_descend' id='color_rows_descend' value='<?php echo ( isset($options['colors']['rows']['descend']) ? ($options['colors']['rows']['descend']) : '' ) ?>' size='7' class="leaguemanager-colorpicker color" /><span class="colorbox" style="background-color: <?php echo $options['colors']['rows']['descend'] ?>"></span></td>
				</tr>
				<tr valign='top'>
					<th scope='row'><label for='color_rows_relegation'><?php _e( 'Teams Relegation', 'leaguemanager' ) ?></label></th><td><input type='text' name='color_rows_relegation' id='color_rows_relegation' value='<?php echo ( isset($options['colors']['rows']['relegation']) ? ($options['colors']['rows']['relegation']) : '' ) ?>' size='7' class="leaguemanager-colorpicker color" /><span class="colorbox" style="background-color: <?php echo $options['colors']['rows']['relegation'] ?>"></span></td>
				</tr>
				<tr valign='top'>
					<th scope='row'><label for='color_rows'><?php _e( 'Box Header', 'projectmanager' ) ?></label></th>
					<td>
						<p class='table_rows'><input type='text' name='color_boxheader1' id='color_boxheader1' value='<?php echo $options['colors']['boxheader'][0] ?>' size='7' class="leaguemanager-colorpicker color" /><span class="colorbox" style="background-color: <?php echo $options['colors']['boxheader'][0] ?>"></span></p>				<p class='table_rows'><input type='text' name='color_boxheader2' id='color_boxheader2' value='<?php echo $options['colors']['boxheader'][1] ?>' size='7' class="leaguemanager-colorpicker color" /><span class="colorbox" style="background-color: <?php echo $options['colors']['boxheader'][1] ?>"></span></p>
					</td>
				</tr>
				</table>
			</div>
		</div>
	
		<div id="dashboard-widget" class="settings-block-container">
			<h2><?php _e('Dashboard Widget Support News', 'leaguemanager') ?></h2>
			<div class="settings-block">
				<table class='form-table'>
                <tr valign='top'>
                    <th scope='row'><label for='hide_admin_widgets'><?php _e( 'Hide Admin Widgets', 'leaguemanager') ?></label></th>
                    <td>
                        <label>
                            <input type='radio' name='hide_admin_widgets' id='hide_admin_widgets_no' value='0'
                            <?php if(!isset($options['hide_admin_widgets']) || !$options['hide_admin_widgets']) print 'checked="checked"'; ?> />
                            No
                        </label>
                        <label>
                            <input type='radio' name='hide_admin_widgets' id='hide_admin_widgets_yes' value='1'
                            <?php if(isset($options['hide_admin_widgets']) && $options['hide_admin_widgets']) print 'checked="checked"'; ?> />
                            Yes
                        </label>
                    </td>
                </tr>
				<tr valign='top'>
					<th scope='row'><label for='dashboard_num_items'><?php _e( 'Number of Support Threads', 'leaguemanager' ) ?></label></th><td><input type="number" step="1" min="0" class="small-text" name='dashboard[num_items]' id='dashboard_num_items' value='<?php echo $options['dashboard_widget']['num_items'] ?>' size='2' /></td>
				</tr>
				<tr valign='top'>
					<th scope='row'><label for='dashboard_show_author'><?php _e( 'Show Author', 'leaguemanager' ) ?></label></th><td><input type='checkbox' name='dashboard[show_author]' id='dashboard_show_author'<?php checked($options['dashboard_widget']['show_author'], 1) ?> /></td>
				</tr>
				<tr valign='top'>
					<th scope='row'><label for='dashboard_show_date'><?php _e( 'Show Date', 'leaguemanager' ) ?></label></th><td><input type='checkbox' name='dashboard[show_date]' id='dashboard_show_date'<?php checked($options['dashboard_widget']['show_date'], 1) ?> /></td>
				</tr>
					<tr valign='top'>
					<th scope='row'><label for='dashboard_show_summary'><?php _e( 'Show Summary', 'leaguemanager' ) ?></label></th><td><input type='checkbox' name='dashboard[show_summary]' id='dashboard_show_summary'<?php checked($options['dashboard_widget']['show_summary'], 1) ?> /></td>
				</tr>
				</table>
			</div>
		</div>
	</div>
	
	<input type='hidden' name='page_options' value='color_headers,color_rows,color_rows_alt,color_rows_ascend,color_rows_descend,color_rows_relegation' />
	<p class='submit'><input type='submit' name='updateLeagueManager' value='<?php _e( 'Save Preferences', 'leaguemanager' ) ?>' class='button button-primary' /></p>
</div>
</form>
	
<script>
	syncColor("pick_color_headers", "color_headers", document.getElementById("color_headers").value);
	syncColor("pick_color_rows", "color_rows", document.getElementById("color_rows").value);
	syncColor("pick_color_rows_alt", "color_rows_alt", document.getElementById("color_rows_alt").value);
	syncColor("pick_color_rows_ascend", "color_rows_ascend", document.getElementById("color_rows_ascend").value);
	syncColor("pick_color_rows_descend", "color_rows_descend", document.getElementById("color_rows_descend").value);
	syncColor("pick_color_rows_relegation", "color_rows_relegation", document.getElementById("color_rows_relegation").value);
</script>

<?php endif; ?>
