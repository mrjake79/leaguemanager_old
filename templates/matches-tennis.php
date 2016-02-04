<?php
/**
Template page for the match table in tennis

The following variables are usable:
	
	$league: contains data of current league
	$matches: contains all matches for current league
	$teams: contains teams of current league in an assosiative array
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
<?php if ( $match->winner_id == $match->away_team ) $match->title = $teams[$match->away_team]['title'] . ' &#8211; ' . $teams[$match->home_team]['title']; ?>

<tr class='<?php echo $match->class ?>'>
	<td class='match'><?php echo $match->date." ".$match->start_time." ".$match->location ?><br /><a href="<?php echo $match->pageURL ?>"><?php echo $match->title ?></a> <?php echo $match->report ?></td>
	<td class='score' valign='bottom'>
		<?php
			$sets = array();
			foreach ( (array)$match->sets AS $j => $set ) {
				if ( $set['player1'] != "" && $set['player2'] != "" ) {
					if ( $match->winner_id == $match->away_team )
						$sets[] = sprintf($league->point_format2, $set['player2'], $set['player1']);
					else
						$sets[] = sprintf($league->point_format2, $set['player1'], $set['player2']);
				}
			}
		?>
		<?php echo implode(", ", $sets) ?>
	</td>
</tr>

<?php endforeach; ?>
</table>

<?php endif; ?>

<?php endif; ?>
