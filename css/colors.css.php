<?php if ( !empty($this->options['colors']['headers']) ) : ?>
table.leaguemanager th,
div.matchlist table.leaguemanager th {
	background-color: <?php echo $this->options['colors']['headers'] ?>;
}
<?php endif; ?>
<?php if ( !empty($this->options['colors']['rows']['main']) ) : ?>
table.leaguemanager tr {
	background-color: <?php echo $this->options['colors']['rows']['main'] ?>;
}
<?php endif; ?>
<?php if ( !empty($this->options['colors']['rows']['alternate']) ) : ?>
table.leaguemanager tr.alternate { 
	background-color: <?php echo $this->options['colors']['rows']['alternate'] ?>;
}
<?php endif; ?>
<?php if ( !empty($this->options['colors']['rows']['ascend']) ) : ?>
table.standingstable tr.ascend td,
table.standingstable tr.ascend.alternate td {
	background-color: <?php echo $this->options['colors']['rows']['ascend'] ?>;
}
<?php endif; ?>
<?php if ( !empty($this->options['colors']['rows']['descend']) ) : ?>
table.standingstable .descend td,
table.standingstable .descend.alternate td {
	background-color: <?php echo $this->options['colors']['rows']['descend'] ?>;
}
<?php endif; ?>
<?php if ( !empty($this->options['colors']['rows']['relegation']) ) : ?>
table.standingstable tr.relegation,
table.standingstable tr.relegation.alternate {
	background-color: <?php echo $this->options['colors']['rows']['relegation'] ?>;
}
<?php endif; ?>
<?php if ( !empty($this->options['colors']['rows']['alternate']) ) : ?>
table.crosstable th,
table.crosstable td {
	border: 1px solid <?php echo $this->options['colors']['rows']['alternate'] ?>;
}
<?php endif; ?>
<?php if ( !empty($this->options['colors']['boxheader']) ) : ?>
<?php if ( !empty($this->options['colors']['boxheader'][1]) ) : ?>
div.teampage h3.header,
div.teamlist h3.header,
div.match h3.header,
div.team-content .prev_match .header,
div.team-content .next_match .header {
	background: <?php echo $this->options['colors']['boxheader'][0] ?>;
	background: -moz-linear-gradient(top, <?php echo $this->options['colors']['boxheader'][0] ?> 0%, <?php echo $this->options['colors']['boxheader'][1] ?> 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, <?php echo $this->options['colors']['boxheader'][0] ?>), color-stop(100%, <?php echo $this->options['colors']['boxheader'][1] ?>));
	background: -webkit-linear-gradient(top, <?php echo $this->options['colors']['boxheader'][0] ?> 0%, <?php echo $this->options['colors']['boxheader'][1] ?> 100%);
	background: -o-linear-gradient(top, <?php echo $this->options['colors']['boxheader'][0] ?> 0%, <?php echo $this->options['colors']['boxheader'][1] ?> 100%);
	background: -ms-linear-gradient(top, <?php echo $this->options['colors']['boxheader'][0] ?> 0%, <?php echo $this->options['colors']['boxheader'][1] ?> 100%);
	background: linear-gradient(top, <?php echo $this->options['colors']['boxheader'][0] ?> 0%, <?php echo $this->options['colors']['boxheader'][1] ?> 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $this->options['colors']['boxheader'][0] ?>', endColorstr='<?php echo $this->options['colors']['boxheader'][1] ?>', GradientType=0 );
}
<?php else : ?>
div.teampage h3.header,
div.teamlist h3.header,
div.match h3.header,
div.team-content .prev_match .header,
div.team-content .next_match .header {
	background-color: <?php echo $this->options['colors']['boxheader'][0] ?>;
}
<?php endif; ?>
<?php endif; ?>