<?php
/**
Template page for Team List

The following variables are usable:
	
	$league league object
	$teams: all teams of league
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if ( $teams ) : ?>

<div class="teamlist jquery-ui-accordion">
<?php foreach ( $teams AS $team ) : ?>
	<?php include('team.php'); ?>
<?php endforeach; ?>
</div>

<?php endif; ?>
