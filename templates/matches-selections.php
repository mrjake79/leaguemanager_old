<?php if ( ($league->show_match_day_selection || $league->show_team_selection) && $league->mode != 'championship' ) : ?>
<div style='float: left; margin-top: 1em;'>
	<form method='get' action='<?php the_permalink(get_the_ID()) ?>'>
	<div>
		<input type='hidden' name='page_id' value='<?php the_ID() ?>' />
		<input type="hidden" name="season_<?php echo $league->id ?>" value="<?php echo $season ?>" />
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />

		<?php if ($league->show_match_day_selection) : ?>
		<select size='1' name='match_day_<?php echo $league->id ?>'>
			<?php $selected = ( isset($_GET['match_day_'.$league->id]) && intval($_GET['match_day_'.$league->id]) == -1 ) ? ' selected="selected"' : ''; ?>
			<option value="-1"<?php echo $selected ?>><?php _e( 'Show all Matches', 'leaguemanager' ) ?></option>
		<?php for ($i = 1; $i <= $league->num_match_days; $i++) : ?>
			<option value='<?php echo $i ?>'<?php if ($leaguemanager->getMatchDay() == $i) echo ' selected="selected"'?>><?php printf(__( '%d. Match Day', 'leaguemanager'), $i) ?></option>
		<?php endfor; ?>
		</select>
		<?php endif; ?>
		<?php if ($league->show_team_selection) : ?>
		<select size="1" name="team_id_<?php echo $league->id ?>">
		<option value=""><?php _e( 'Choose Team', 'leaguemanager' ) ?></option>
		<?php foreach ( $teams AS $team_id => $team ) : ?>
			<?php $selected = (isset($_GET['team_id_'.$league->id]) && intval($_GET['team_id_'.$league->id]) == $team_id) ? ' selected="selected"' : ''; ?>
			<option value="<?php echo $team_id ?>"<?php echo $selected ?>><?php echo $team['title'] ?></option>
		<?php endforeach; ?>
		</select>
		<?php endif; ?>
		<input type='submit' class="button" value='<?php _e('Show') ?>' />
	</div>
	</form>
</div>
<br style='clear: both;' />
<?php endif; ?>