<?php
/**
Template page for a single match

The following variables are usable:
	
	$match: contains data of displayed match
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if ( $match ) : ?>

<div class="match" id="match-<?php echo $match->id ?>">
	<h3 class="header"><?php _e( 'Match', 'leaguemanager' ) ?></h3>
	
	<div class="match-content">
		<h4><?php echo $leaguemanager->getMatchTitle($match->id, false) ?></h4>
		
		<?php if ( $match->score == '0:0' ) : ?>
		<p class="matchdate"><?php echo $match->date." ".$match->start_time." ".$match->location ?></p>
		<?php else : ?>
		<p class="score">
			<span class="home_logo"><img src="<?php echo $match->homeLogo ?>" alt="" /></span>
			<?php echo $match->score ?>
			<span class="away_logo"><img src="<?php echo $match->awayLogo ?>" alt="" /></span>
		</p>
		<?php endif; ?>
		
		<?php if ( !empty($match->match_day) ) : ?>
			<p class='match_day'><?php printf(__("<strong>%d.</strong> Match Day", 'leaguemanager'), $match->match_day) ?></p>
		<?php endif; ?>
		
		<p class='date'><?php echo mysql2date(get_option('date_format'), $match->date) ?>, <span class='time'><?php echo $match->time ?></span></p>
		<p class='location'><?php echo $match->location ?></p>
		
		<?php if ( $match->post_id != 0 ) : ?>
			<p class='report'><a href='<?php the_permalink($match->post_id) ?>'><?php _e( 'Report', 'leaguemanager' ) ?></a></p>
		<?php endif; ?>
			
		<?php if ( isset($match->hasStats) && $match->hasStats ) :?>
		<div class="match-stats">
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
		</div>
		<?php endif; ?>
	</div>
</div>

<?php endif; ?>
