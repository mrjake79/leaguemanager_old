<?php
/**
Template page to display single team

The following variables are usable:
	
	$league: league object
	$team: team object
	$next_match: next match object
	$prev_match: previous match object

	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>

<?php //if ( isset($team->single) && $team->single ) : ?>
<div class="teampage">
<?php //endif; ?>

	<h3 class="header"><?php echo $team->title ?></h3>

	<div class="team-content">
		<dl class="team">
			<dt><?php _e( 'Rank', 'leaguemanager' ) ?></dt><dd><?php echo $team->rank ?></dd>
			<dt><?php _e( 'Matches', 'leaguemanager' ) ?></dt><dd><?php echo $team->done_matches ?></dd>
			<dt><?php _e( 'Won', 'leaguemanager' ) ?></dt><dd><?php echo $team->won_matches ?></dd>
			<dt><?php _e( 'Tied', 'leaguemanager' ) ?></dt><dd><?php echo $team->draw_matches ?></dd>
			<dt><?php _e( 'Lost', 'leaguemanager' ) ?></dt><dd><?php echo $team->lost_matches ?></dd>
			<?php if ( !empty($team->coach) ) : ?>
			<dt><?php _e( 'Coach', 'leaguemanager' ) ?></dt><dd><?php echo $team->coach ?></dd>
			<?php endif; ?>
			<?php if ( !empty($team->website) ) : ?>
			<dt><?php _e( 'Website', 'leaguemanager' ) ?></dt><dd><a href="<?php echo $team->website ?>" target="_blank"><?php echo $team->website ?></a></dd>
			<?php endif; ?>
		</dl>

		<?php if ( !empty($team->logo) ) : ?>
		<p class="teamlogo alignright"><img src="<?php echo $team->logo ?>" alt="<?php _e( 'Logo', 'leaguemanager' ) ?>" /></p>
		<?php endif; ?>
		
		<div class="matches">			
			<?php if ( $team->next_match ) : ?>
			<div class="matches-container">
			<div class="next_match">
				<h4 class="header"><?php _e( 'Next Match','leaguemanager' ) ?></h4>
				<div class="content">
					<p class="match"><?php echo $team->next_match->match ?></p>
					<p class='match_day'><?php printf(__("<strong>%d.</strong> Match Day", 'leaguemanager'), $team->next_match->match_day); ?></p>
					<p class='match_date'><?php echo mysql2date("j. F Y", $team->next_match->date) ?>&#160;<span class='time'><?php echo $team->next_match->time ?></span> <span class='location'><?php echo $team->next_match->location ?></span></p>
					<p class="score">&#160;</p>
				</div>
			</div>
			</div>
			<?php endif; ?>

			<?php if ( $team->prev_match ) : ?>
			<div class="matches-container">
			<div class="prev_match">
				<h4 class="header"><?php _e( 'Last Match','leaguemanager' ) ?></h4>
				<div class="content">
					<p class="match"><?php echo $team->prev_match->match ?></p>
					<p class='match_day'><?php printf(__("<strong>%d.</strong> Match Day", 'leaguemanager'), $team->prev_match->match_day); ?></p>
					<p class='match_date'><?php echo mysql2date("j. F Y", $team->prev_match->date) ?>&#160;<span class='time'><?php echo $team->prev_match->time ?></span> <span class='location'><?php echo $team->prev_match->location ?></span></p>
					<p class="score"><?php echo $team->prev_match->score ?></p>
				</div>
			</div>
			</div>
			<?php endif; ?>
		</div>

		<?php if ( function_exists('project') ) : ?>
		<div class="team-projectmanager<?php if ( $team->projects_tabs ) echo ' jquery-ui-tabs'; ?>">
			<ul class="tablist" style="display: none";>
				<li><a href="#roster-<?php echo $team->id ?>"><?php _e( 'Team Roster', 'leaguemanaager' ) ?></a></li>
				<li><a href="#profile-<?php echo $team->id ?>"><?php _e( 'Team Profile', 'leaguemanaager' ) ?></a></li>
			</ul>
			
			<?php if ( !empty($team->roster['id']) ) : ?>
			<div id="roster-<?php echo $team->id ?>" class="tab">
				<h4 class="header"><?php _e( 'Team Roster', 'leaguemanager' ) ?></h4>
				<?php $cat_id = ( isset($team->roster['cat_id']) && $team->roster['cat_id'] > 0 ) ? intval($team->roster['cat_id']) : false; ?>
				<?php project( $team->roster['id'], array('cat_id' => $cat_id, 'template' => 'gallery', 'show_title' => false, 'selections' => false, 'searchform' => false) ); ?>
			</div>
			<?php endif; ?>
			
			<?php if ( !empty($team->profile) ) : ?>
			<div id="profile-<?php echo $team->id ?>" class="tab">
				<h4 class="header"><?php _e( 'Team Profile', 'leaguemanager' ) ?></h4>
				<?php dataset( $team->profile, array('template' => 'leaguemanager-teamprofile') ); ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	
<?php //if ( isset($team->single) && $team->single ) : ?>
</div>
<?php //standingstableendif; ?>