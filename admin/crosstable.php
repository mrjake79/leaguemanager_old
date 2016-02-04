<table class='widefat crosstable' summary='' title='<?php echo __( 'Crosstable', 'leaguemanager' )." ".$league->title ?>'>
<tr>
	<th colspan='2' style='text-align: center;'><?php _e( 'Club', 'leaguemanager' ) ?></th>
	<?php for ( $i = 1; $i <= count($teams); $i++ ) : ?>
	<th style='text-align: center;'><?php echo $i ?></th>
	<?php endfor; ?>
</tr>

<?php foreach ( $teams AS $rank => $team ) : ?>
<?php if ( 1 == $team->home ) $team->title = '<strong>'.$team->title.'</strong>'; ?>
<tr class="<?php echo $team->class ?>">
	<th scope='row' class='rank'><?php echo $rank + 1 ?></th>
	<td>
		<?php if ( $team->logo != '' ) : ?>
		<img src='<?php echo $leaguemanager->getThumbnailUrl($team->logo) ?>' alt='<?php _e('Logo','leaguemanager') ?>' title='<?php _e('Logo','leaguemanager')." ".$team->title ?>' />
		<?php endif; ?>
		<?php echo $team->title ?>
	</td>
	<?php for ( $i = 0; $i < count($teams); $i++ ) : ?>
		
	<?php if ( ($rank == $i) ) : ?>
	<td class='num'>-</td>
	<?php else : ?>
	<?php echo $leaguemanager->getCrosstableField($team->id, $teams[$i]->id, $team->home); ?>
	<?php endif; ?>
	
	<?php endfor; ?>
</tr>
<?php endforeach; ?>
</table>