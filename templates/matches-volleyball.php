<?php
/**
Template page for the match table

The following variables are usable:
	
	$league: contains data of current league
	$matches: contains all matches for current league
	$teams: contains teams of current league in an assosiative array
	$season: current season
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if (isset($_GET['match']) ) : ?>
	<?php leaguemanager_match(intval($_GET['match'])); ?>
<?php else : ?>

<?php if ( ($league->show_match_day_selection || $league->show_team_selection) && $league->mode != 'championship' ) : ?>
<div style='float: left; margin-top: 1em;'>
	<form method='get' action='<?php the_permalink(get_the_ID()) ?>'>
	<div>
		<input type='hidden' name='page_id' value='<?php the_ID() ?>' />
		<input type="hidden" name="season" value="<?php echo $season ?>" />
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />

		<?php if ($league->show_match_day_selection) : ?>
		<select size='1' name='match_day'>
			<?php $selected = ( isset($_GET['match_day']) && $_GET['match_day'] == -1 ) ? ' selected="selected"' : ''; ?>
			<option value="-1"<?php echo $selected ?>><?php _e( 'Show all Matches', 'leaguemanager' ) ?></option>
		<?php for ($i = 1; $i <= $league->num_match_days; $i++) : ?>
			<option value='<?php echo $i ?>'<?php if ($leaguemanager->getMatchDay() == $i) echo ' selected="selected"'?>><?php printf(__( '%d. Match Day', 'leaguemanager'), $i) ?></option>
		<?php endfor; ?>
		</select>
		<?php endif; ?>
		<?php if ($league->show_team_selection) : ?>
		<select size="1" name="team_id">
		<option value=""><?php _e( 'Choose Team', 'leaguemanager' ) ?></option>
		<?php foreach ( $teams AS $team_id => $team ) : ?>
			<?php $selected = (isset($_GET['team_id']) && $_GET['team_id'] == $team_id) ? ' selected="selected"' : ''; ?>
			<option value="<?php echo $team_id ?>"<?php echo $selected ?>><?php echo $team['title'] ?></option>
		<?php endforeach; ?>
		</select>
		<?php endif; ?>
		<input type='submit' value='<?php _e('Show') ?>' />
	</div>
	</form>
</div>
<br style='clear: both;' />
<?php endif; ?>
	
<?php if ( $matches ) : ?>

<table class='leaguemanager matchtable' summary='' title='<?php echo __( 'Match Plan', 'leaguemanager' )." ".$league->title ?>'>
<tr>
	<th class='match'><?php _e( 'Match', 'leaguemanager' ) ?></th>
	<th class='score'><?php _e( 'Score', 'leaguemanager' ) ?></th>
</tr>
<?php foreach ( $matches AS $match ) : ?>
<?php
$sets = array();
foreach ( (array)$match->sets AS $j => $set ) {
	if ( !empty($set['home']) && !empty($set['away']) )
		$sets[] = implode(":", $set);
}
?>
<tr class='<?php echo $match->class ?>'>
	<td class='match'><?php echo $match->date." ".$match->start_time." ".$match->location ?><br /><a href="<?php echo $match->pageURL ?>"><?php echo $match->title ?></a> <?php echo $match->report ?></td>
	<td class='score' valign='bottom'>
		<?php echo $match->score . " (".implode(", ", $sets).")" ?>
	</td>
</tr>

<?php endforeach; ?>
</table>

<?php endif; ?>

<?php endif; ?>
