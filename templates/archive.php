<?php
/**
Template page for the Archive

The following variables are usable:
	
	$leagues: array of all leagues
	$seasons: available seasons of all leagues
	$curr_league: current league
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
$archive = true;
?>
<div id="leaguemanager_archive_selections">
	<form method="get" action="<?php get_permalink(get_the_ID()) ?>">
		<input type="hidden" name="page_id" value="<?php the_ID() ?>" />
		<select size="1" name="league_id">
			<option value=""><?php _e( 'League', 'leaguemanager' ) ?></option>
			<?php foreach ( $leagues AS $league ) : ?>
			<option value="<?php echo $league->id ?>"<?php if ( $league->id == $curr_league->id ) echo ' selected="selected"' ?>><?php echo $league->title ?></option>
			<?php endforeach ?>
		</select>
		<select size="1" name="season">
			<?php foreach ( $leagues AS $league ) : ?>
			<optgroup label="<?php echo $league->title ?>">
				<option value=""><?php _e( 'Season', 'leaguemanager' ) ?></option>
				<?php foreach ( $league->seasons AS $key => $season ) : ?>
				<option value="<?php echo $key ?>"<?php if ( $season['name'] == $curr_league->season ) echo ' selected="selected"' ?>><?php echo $season['name'] ?></option>
				<?php endforeach ?>
			</optgroup>
			<?php endforeach; ?>
		</select>
		<input type="submit" class="submit" value="<?php _e( 'Show' ) ?>" />
	</form>
</div>

<?php if ( isset($_GET['team']) ) : ?>
	<?php leaguemanager_team(intval($_GET['team'])); ?>
<?php elseif ( isset($_GET['match']) ) : ?>
	<?php leaguemanager_match(intval($_GET['match'])); ?>
<?php else : ?>
	<?php //$league = $leaguemanager->getLeague($curr_league->id); ?>
	<?php if ( $curr_league->mode == 'championship' ) : ?>
		<?php leaguemanager_championship( $curr_league->id, array('season' => $curr_league->season) ); ?>
	<?php else : ?>
		<!-- Standings Table -->
		<h4><?php _e('Standings', 'leaguemanager') ?></h4>
		<?php leaguemanager_standings( $curr_league->id, array( 'season' => $curr_league->season ) ) ?>

		<!-- Match Overview -->
		<h4><?php _e('Matches', 'leaguemanager') ?></h4>
		<?php leaguemanager_matches( $curr_league->id, array('season' => $curr_league->season, 'archive' => $archive) ) ?>

		<!-- Crosstable -->
		<h4><?php _e('Crosstable', 'leaguemanager') ?></h4>
		<?php leaguemanager_crosstable( $curr_league->id, array('season' => $curr_league->season) ) ?>
	<?php endif; ?>
<?php endif; ?>