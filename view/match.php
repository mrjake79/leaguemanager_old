<?php
/**
Template page for a single match

The following variables are usable:
	
	$match: contains data of displayed match
	$league: contains data of current league
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if ( $match ) : ?>

<div class="match" id="match-<?php echo $match->id ?>">
	<h3><?php echo $match->title ?></h3>
	
	<img src="<?php echo $match->homeLogo ?>" alt="" class="alignleft" />
	<img src="<?php echo $match->awayLogo ?>" alt="" class="alignright" />
	
	<?php if ( $match->score == '0:0' ) : ?>
	<p class="matchdate"><?php echo mysql2date(get_option('date_format'), $match->date)." ".$match->start_time." ".$match->location ?></p>
	<?php else : ?>
	<p class="score"><?php echo $match->score ?></p>
	<?php endif; ?>
	
	<br style="clear: both;" />
	<?php if ( isset($match->hasStats) && $match->hasStats ) :?>
	<?php foreach ( $lmStats->get($match->league_id) AS $stat ) : ?>

	<h4><?php echo $stat->name ?></h4>

	<table>
	<tr>
		<?php foreach ( (array)maybe_unserialize($stat->fields) AS $field ) : ?>
		<th scope="col"><?php echo $field['name'] ?></th>
		<?php endforeach; ?>
	</tr>
	<?php if ( isset($match->{sanitize_title($stat->name)}) ) : ?>
	<?php foreach ( (array)$match->{sanitize_title($stat->name)} AS $i => $data ) : ?>
	<tr>
		<?php foreach ( (array)maybe_unserialize($stat->fields) AS $field ) : ?>
		<td><?php echo $data[sanitize_title($field['name'])] ?></td>
		<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>
	<?php endif; ?>
	</table>

	<?php endforeach; ?>
	<?php endif; ?>
</div>

<?php endif; ?>
