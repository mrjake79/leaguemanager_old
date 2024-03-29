<?php
/**
Template page for the crosstable

The following variables are usable:
	
	$league: contains data of current league
	$teams: contains teams of current league in an assosiative array
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if ( $teams ) : ?>

<?php if ( 'popup' == $mode ) : ?>
<div id='leaguemanager_crosstable' style='overfow:auto;display:none;'><div>
<?php endif; ?>

<?php $rank = 0; ?>
<table class='leaguemanager crosstable' summary='' title='<?php echo __( 'Crosstable', 'leaguemanager' )." ".$league->title ?>'>
<tr>
	<th colspan='2' style='text-align: center;'><?php _e( 'Club', 'leaguemanager' ) ?></th>
	<?php for ( $i = 1; $i <= count($teams); $i++ ) : ?>
	<th style='text-align: center;'><?php echo $i ?></th>
	<?php endfor; ?>
</tr>

<?php foreach ( $teams AS $team ) : $rank++; ?>
<?php if ( 1 == $team->home ) $team->title = '<strong>'.$team->title.'</strong>'; ?>
<tr>
	<th scope='row' class='rank'><?php echo $rank ?></th>
	<td>
		<?php if ( $league->show_logo && $team->logo != '' ) : ?>
		<img src='<?php echo $leaguemanager->getImageUrl($team->logo, false, 'tiny') ?>' alt='<?php _e('Logo','leaguemanager') ?>' title='<?php _e('Logo','leaguemanager')." ".$team->title ?>' />
		<?php endif; ?>
		<?php echo $team->title ?>
	</td>
	<?php for ( $i = 1; $i <= count($teams); $i++ ) : ?>
		
	<?php if ( ($rank == $i) ) : ?>
	<td class='num'>-</td>
	<?php else : ?>
	<?php echo $this->getCrosstableField($team->id, $teams[$i-1]->id, $team->home); ?>
	<?php endif; ?>
	
	<?php endfor; ?>
</tr>
<?php endforeach; ?>
</table>
							
<?php if ( 'popup' == $mode ) : ?>
</div></div>
<p><a class='thickbox' href='#TB_inline&width=800&height=500&inlineId=leaguemanager_crosstable' title='<?php _e( 'Crosstable', 'leaguemanager' )." ".$league->title ?>'><?php _e( 'Crosstable', 'leaguemanager' )." ".$league->title ?> (<?php _e('Popup','leaguemanager') ?>)</a></p>
<?php endif; ?>

<?php endif; ?>