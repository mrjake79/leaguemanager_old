<?php
/**
Template page for the match table

The following variables are usable:
	
	$league: contains data of current league
	$matches: contains all matches for current league
	$teams: contains teams of current league in an associative array
	$season: current season
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if (isset($_GET['match_'.$league->id]) ) : ?>
	<?php leaguemanager_match(intval($_GET['match_'.$league->id])); ?>
<?php else : ?>

<?php include('matches-selections.php'); ?>

<?php if ( $matches ) : ?>
<table class='leaguemanager matchtable' summary='' title='<?php echo __( 'Match Plan', 'leaguemanager' )." ".$league->title ?>'>
<tr>
	<th class='match'><?php _e( 'Match', 'leaguemanager' ) ?></th>
	<th class='score'><?php _e( 'Score', 'leaguemanager' ) ?></th>
</tr>
<?php foreach ( $matches AS $match ) : ?>

<tr class='<?php echo $match->class ?>'>
	<td class='match'><?php echo $match->date." ".$match->start_time." ".$match->location ?><br /><a href="<?php echo $match->pageURL ?>"><?php echo $leaguemanager->getMatchTitle($match->id) ?></a> <?php echo $match->report ?></td>
	<td class='score' valign='bottom'><?php echo $match->score ?></td>
</tr>

<?php endforeach; ?>
</table>

<p class='page-numbers'><?php echo $league->pagination ?></p>

<?php endif; ?>

<?php endif; ?>
