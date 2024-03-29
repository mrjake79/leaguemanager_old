<?php
/**
Template page for the match table showing matches divided by match day

The following variables are usable:
	
	$league: contains data of current league
	$matches: contains all matches for current league
	$teams: contains teams of current league in an associative array
	$season: current season
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<script type='text/javascript'>
	jQuery(function() {
		jQuery(".matchlist").accordion({
			active: <?php echo $league->current_match_day - 1 ?>
		});
	});
</script>
<?php if ( isset($_GET['match_'.$league->id]) ) : ?>
	<?php leaguemanager_match(intval($_GET['match_'.$league->id])); ?>
<?php else : ?>

<?php if ( $matches ) : ?>
<div class="matchlist jquery-ui-accordion">
	<?php for ($i = 1; $i <= $league->num_match_days; $i++) : ?>
	<div class="match">
		<h3 class="header"><?php printf(__('%d. Match Day', 'leaguemanager'), $i) ?></h3>
		<div class="match-content">
			<table class='leaguemanager matchtable' summary='' title='<?php echo __( 'Match Plan', 'leaguemanager' )." ".$league->title ?>'>
			<tr>
				<th class='match'><?php _e( 'Match', 'leaguemanager' ) ?></th>
				<th class='score'><?php _e( 'Score', 'leaguemanager' ) ?></th>
			</tr>
			
			<?php $class = ''; ?>
			<?php foreach ( $matches AS $match ) : ?>
			<?php if ($match->match_day == $i) : ?>
			<?php $class = ( $class == 'alternate' ) ? '' : 'alternate'; ?>
			<tr class='<?php echo $class ?>'>
				<td class='match'><?php echo $match->match_date." ".$match->start_time." ".$match->location ?><br /><a href="<?php echo $match->pageURL ?>"><?php echo $match->title ?></a> <?php echo $match->report ?></td>
				<td class='score' valign='bottom'><?php echo $match->score ?></td>
			</tr>
			<?php endif; ?>
			<?php endforeach; ?>

			</table>
		</div>
	</div>
	<?php endfor; ?>
</div>
<?php endif; ?>

<?php endif; ?>
