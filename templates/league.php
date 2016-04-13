<?php
/**
Template page for a whole league

The following variables are usable:
	
	$league: league
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
$archive = true;
$tab = 0;

if ( isset($_GET['match_day_'.$league->id]) || isset($_GET['team_id_'.$league->id]) ) 
	$tab = 2;

if ( isset($_GET['team_'.$league->id]) )
	$tab = 3;

if ( isset($_GET['match_'.$league->id]) )
	$tab = 2;
?>
<script type='text/javascript'>
	jQuery(function() {
		jQuery(".jquery-ui-tabs").tabs({
			active: <?php echo $tab ?>
		});
	});
</script>
<h2><?php echo $league->title ?></h2>

<?php if ( $league->mode == 'championship' ) : ?>
	<?php leaguemanager_championship( $league->id, array('season' => $league->season) ); ?>
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
			<h4 class="tab-header"><?php _e('Standings', 'leaguemanager') ?></h4>
			<?php leaguemanager_standings( $league->id, array( 'season' => $league->season, 'template' => $league->templates['standingstable'] ) ) ?>
		</div>
			
		<!-- Crosstable -->
		<div id="crosstable-archive" class="jquery-ui-tab">
			<h4 class="tab-header"><?php _e('Crosstable', 'leaguemanager') ?></h4>
			<?php leaguemanager_crosstable( $league->id, array('season' => $league->season, 'template' => $league->templates['crosstable']) ) ?>
		</div>
			
		<!-- Match Overview -->
		<div id="matches-archive" class="jquery-ui-tab">
			<h4 class="tab-header"><?php _e('Matches', 'leaguemanager') ?></h4>
			<?php leaguemanager_matches( $league->id, array('season' => $league->season, 'match_day' => 'current' , 'show_match_day_selection' => 'true', 'template' => $league->templates['matches']) ) ?>
		</div>
			
		<!-- Teamlist -->
		<div id="teams-archive" class="jquery-ui-tab">
			<h4 class="header"><?php _e('Teams', 'leaguemanager') ?></h4>
			<?php leaguemanager_teams( $league->id, array('season' => $league->season, 'template' => $league->templates['teams']) ) ?>
		</div>
	</div>
<?php endif; ?>