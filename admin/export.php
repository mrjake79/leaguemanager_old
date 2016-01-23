<?php
if ( !current_user_can( 'manage_leaguemanager' ) ) :
     echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
else :

if ( isset($_POST['leaguemanager_export']) ) {
	$options = get_option('leaguemanager');
	if ($_POST['exportkey'] ==	$options['exportkey']) {
		ob_end_clean();
		$this->export((int)$_POST['league_id'], $_POST['mode']);
		unset($options['exportkey']);
		update_option('projectmanager', $options);
	} else {
		ob_end_flush();
		$this->setMessage( __("You don't have permission to perform this task", 'projectmanager'), true );
		$this->printMessage();
	}
}

$options = get_option('leaguemanager');
$options['exportkey'] = uniqid(rand(), true);
update_option('leaguemanager', $options);
?>
<div class="wrap narrow">
	<h1><?php _e('LeagueManager Export', 'leaguemanager') ?></h1>
	<p><?php _e( 'Here you can export teams and matches for a specific league.', 'leaguemanager' ) ?></p>
	<p><?php _e('Once you&#8217;ve saved the download file, you can use the Import function on another WordPress blog to import this blog.'); ?></p>
	<form action="" method="post">
		<input type="hidden" name="exportkey" value="<?php echo $options['exportkey'] ?>" />
		<?php wp_nonce_field( 'leaguemanager_export-datasets' ) ?>
		<h3><?php _e('Options'); ?></h3>
		<table class="form-table">
		<tr>
			<th><label for="league_id"><?php _e('League', 'leaguemanager'); ?></label></th>
			<td>
				<?php if ( $leagues = $leaguemanager->getLeagues() ) : ?>
				<select size="1" name="league_id" id="league_id">
				<?php foreach ( $leagues AS $league ) : ?>
					<option value="<?php echo $league->id ?>"><?php echo $league->title ?></option>
				<?php endforeach; ?>
				</select>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th><label for="mode"><?php _e('Data', 'leaguemanager'); ?></label></th>
			<td>
				<select size="1" name="mode" id="mode">
					<option value="teams"><?php _e( 'Teams', 'leaguemanager' ) ?></option>
					<option value="matches"><?php _e( 'Matches', 'leaguemanager' ) ?></option>
				</select>
			</td>
		</tr>
		</table>
		<p class="submit"><input type="submit" name="leaguemanager_export" value="<?php _e('Download File'); ?>" class="button button-primary" /></p>
	</form>
</div>

<?php endif; ?>