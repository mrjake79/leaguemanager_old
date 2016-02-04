<?php
/**
Template page for the match table for Irish Gaelic Football

The following variables are usable:
	
	$league: contains data of current league
	$matches: contains all matches for current league
	$teams: contains teams of current league in an assosiative array
	$season: current season
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if (isset($$_GET['match_'.$league->id]) ) : ?>
	<?php leaguemanager_match($_GET['match_'.$league->id]); ?>
<?php else : ?>

<?php include('matches-selections.php'); ?>

<?php if ( $matches ) : ?>

<table class='leaguemanager matchtable' summary='' title='<?php echo __( 'Match Plan', 'leaguemanager' )." ".$league->title ?>'>
<tr>
	<th class='match'><?php _e( 'Match', 'leaguemanager' ) ?></th>
	<th class='score'><?php _e( 'Score', 'leaguemanager' ) ?></th>
</tr>
<?php foreach ( $matches AS $match ) : ?>

<?php
$match->homeScore = sprintf("%d&#8211;%d", $match->num_goals['home'], $match->num_points['home']);
$match->awayScore = sprintf("%d&#8211;%d", $match->num_goals['away'], $match->num_points['away']);
$match->score = sprintf("(%s) &#8211; (%s)", $match->homeScore, $match->awayScore);
?>
<tr class='<?php echo $match->class ?>'>
	<td class='match'><?php echo $match->date." ".$match->start_time." ".$match->location ?><br /><a href="<?php echo $match->pageURL ?>"><?php echo $match->title ?></a> <?php echo $match->report ?></td>
	<td class='score' valign='bottom'><?php echo $match->score ?></td>
</tr>

<?php endforeach; ?>
</table>

<?php endif; ?>

<?php endif; ?>
