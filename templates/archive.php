<?php
/**
Template page for the Archive

The following variables are usable:
	
	$leagues: array of all leagues
	$curr_league: current league
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
$archive = true;
$tab = 0;

if ( isset($_GET['match_day_'.$curr_league->id]) || isset($_GET['team_id_'.$curr_league->id]) ) 
	$tab = 2;
if ( isset($_GET['team_'.$curr_league->id]) )
	$tab = 0;
?>
<script type='text/javascript'>
	jQuery(function() {
		jQuery(".jquery-ui-tabs").tabs({
			active: <?php echo $tab ?>
		});
	});
</script>
<h2><?php printf("%s &mdash; %s %s", $curr_league->title, __('Season', 'leaguemanager'), $curr_league->season); ?></h2>
<div id="leaguemanager_archive_selections" class="">
	<form method="get" action="<?php get_permalink(get_the_ID()) ?>">
		<input type="hidden" name="page_id" value="<?php the_ID() ?>" />
		<?php if ( $single_league ) : ?>
		<select size="1" name="season">
			<option value=""><?php _e( 'Season', 'leaguemanager' ) ?></option>
			<!--<option value=""><?php _e( 'Season', 'leaguemanager' ) ?></option>-->
			<?php foreach ( $curr_league->seasons AS $key => $season ) : ?>
			<option value="<?php echo $key ?>"<?php if ( $season['name'] == $curr_league->season ) echo ' selected="selected"' ?>><?php echo $season['name'] ?></option>
			<?php endforeach ?>
		</select>
		<?php else : ?>
		<select size="1" name="league_id">
			<option value=""><?php _e( 'League', 'leaguemanager' ) ?></option>
			<?php foreach ( $leagues AS $league ) : ?>
			<option value="<?php echo $league->id ?>"<?php if ( $league->id == $curr_league->id ) echo ' selected="selected"' ?>><?php echo $league->title ?></option>
			<?php endforeach ?>
		</select>
		<select size="1" name="season">
			<option value=""><?php _e( 'Season', 'leaguemanager' ) ?></option>
			<?php foreach ( $leagues AS $league ) : ?>
			<optgroup label="<?php echo $league->title ?>">
				<!--<option value=""><?php _e( 'Season', 'leaguemanager' ) ?></option>-->
				<?php foreach ( $league->seasons AS $key => $season ) : ?>
				<option value="<?php echo $key ?>"<?php if ( $season['name'] == $curr_league->season ) echo ' selected="selected"' ?>><?php echo $season['name'] ?></option>
				<?php endforeach ?>
			</optgroup>
			<?php endforeach; ?>
		</select>
		<?php endif; ?>
		<input type="submit" class="submit" value="<?php _e( 'Show' ) ?>" />
	</form>
</div>

<?php //if ( isset($_GET['team_'.$curr_league->id]) ) : ?>
	<?php //leaguemanager_team(intval($_GET['team_'.$curr_league->id])); ?>
<?php //elseif ( isset($_GET['match_'.$curr_league->id]) ) : ?>
	<?php //leaguemanager_match(intval($_GET['match_'.$curr_league->id])); ?>
<?php //else : ?>
	<?php //$league = $leaguemanager->getLeague($curr_league->id); ?>
	<?php if ( $curr_league->mode == 'championship' ) : ?>
		<?php leaguemanager_championship( $curr_league->id, array('season' => $curr_league->season) ); ?>
	<?php else : ?>
		<div class="jquery-ui-tabs">
			<ul class="tablist">
				<li><a href="#standings-archive"><?php _e( 'Standings', 'leaguemanager' ) ?></a></li>
				<li><a href="#crosstable-archive"><?php _e( 'Crosstable', 'leaguemanager' ) ?></a></li>
				<li><a href="#matches-archive"><?php _e( 'Matches', 'leaguemanager' ) ?></a></li>
				<li><a href="#teams-archive"><?php _e( 'Teams', 'leaguemanager' ) ?></a></li>
			</ul>
		
			<!-- Standings Table -->
			<div id="standings-archive" class="jquery-ui-tab">
				<h4 class="header"><?php _e('Standings', 'leaguemanager') ?></h4>
				<?php leaguemanager_standings( $curr_league->id, array( 'season' => $curr_league->season, 'template' => 'last5' ) ) ?>
			</div>
			
			<!-- Crosstable -->
			<div id="crosstable-archive" class="jquery-ui-tab">
				<h4 class="header"><?php _e('Crosstable', 'leaguemanager') ?></h4>
				<?php leaguemanager_crosstable( $curr_league->id, array('season' => $curr_league->season) ) ?>
			</div>
			
			<!-- Match Overview -->
			<div id="matches-archive" class="jquery-ui-tab">
				<h4 class="header"><?php _e('Matches', 'leaguemanager') ?></h4>
				<?php leaguemanager_matches( $curr_league->id, array('season' => $curr_league->season, 'match_day' => 'current' , 'show_match_day_selection' => 'true') ) ?>
			</div>
			
			<!-- Teamlist -->
			<div id="teams-archive" class="jquery-ui-tab">
				<h4 class="header"><?php _e('Teams', 'leaguemanager') ?></h4>
				<?php leaguemanager_teams( $curr_league->id, array('season' => $curr_league->season, 'template' => 'list') ) ?>
			</div>
		</div>
	<?php endif; ?>
<?php //endif; ?>