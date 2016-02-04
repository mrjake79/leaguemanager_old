<?php
if ( !current_user_can( 'league_manager' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
else :
?>

<div class="wrap leaguemanager_documentation">
	<h1 id="top"><?php _e( 'LeagueManager Documentation', 'leaguemanager' ) ?></h1>

	<div id="tabs">
		<ul>
			<li><a href="#shortcodes"><?php _e( 'Shortcodes', 'leaguemanager' ) ?></a></li>
			<li><a href="#templates"><?php _e( 'Templates', 'leaguemanager' ) ?></a></li>
			<li><a href="#import"><?php _e( 'Data Import', 'leaguemanager' ) ?></a></li>
			<li><a href="#slideshows"><?php _e( 'Slideshows', 'leaguemanager' ) ?></a></li>
			<li><a href="#roster_profiles"><?php _e( 'Team Roster & Profiles', 'leaguemanager' ) ?></a></li>
			<li><a href="#template_tags"><?php _e( 'Template Tags', 'leaguemanager' ) ?></a></li>
			<li><a href="#settings"><?php _e( 'League Settings', 'leaguemanager' ) ?></a></li>
			<li><a href="#customization"><?php _e( 'Customization', 'leaguemanager' ) ?></a></li>
			<li>
				<a href="#howto_intro"><?php _e( 'Howto', 'leaguemanager' ) ?></a>
				<!--<ul style="margin-left: 2em;">
					<li><a href="#championship"><?php _e( 'Championship', 'leaguemanager' ) ?></a></li>
					<li><a href="#team_roster"><?php _e( 'Team Roster', 'leaguemanager' ) ?></a></li>
					<li><a href="#match_statistics"><?php _e( 'Match Statistics', 'leaguemanager' ) ?></a></li>

				</ul>-->
			</li>
			<li><a href="#racing"><?php _e( 'Setup Racing Sports', 'leaguemanager' ) ?></a></li>
		</ul>
	
		<div id='shortcodes'>
			<a href="#top" class="alignright top-link"><?php _e( 'Top', 'leaguemanager' ) ?></a>
			<h2><?php _e( 'Shortcodes', 'leaguemanager' ) ?></h2>

			<!-- Shortcode for Standings table -->
			<p><?php _e( 'The standings table of a league is displayed with', 'leaguemanager' ) ?></p>
			<blockquote><p>[standings league_id=ID]</p></blockquote>
			<table class="widefat">
			<thead>
			<tr>
					<th scope="col"><?php _e( 'Parameter', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Description', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Possible Values', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Default', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Optional', 'leaguemanager' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr class="" valign="top">
					<td>league_id</td>
					<td><?php _e( 'ID of League', 'leaguemanager' ) ?></td>
					<td><em>integer</em></td>
					<td>&#160;</td>
					<td><?php _e( 'No', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>league_name</td>
					<td><?php _e( 'get league by name instead of ID (cannot be used together with attribute league_id)', 'leaguemanager' ) ?></td>
					<td><em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>season</td>
					<td><?php _e( 'display standings of specific season (default is last season)', 'leaguemanager' ) ?></td>
					<td><em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>logo</td>
					<td><?php _e( 'toggle dislay of Team Logos', 'leaguemanager' ) ?></td>
					<td><em>boolean</em></td>
					<td>true</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>template</td>
					<td><?php _e( 'specifies template to use', 'leaguemanager' ) ?></td>
					<td><?php _e( 'name of template file without extension, whereas the name has to be standings-X', 'leaguemanager' ) ?></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>group</td>
					<td><?php _e( 'specific group', 'leaguemanager' ) ?></td>
					<td><em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
			</tbody>
			</table>

			<!-- Shortcode for Matches table -->
			<p><?php _e( 'The matches table of a league is displayed with', 'leaguemanager' ) ?></p>
			<blockquote><p>[matches league_id=ID]</p></blockquote>
			<table class="widefat">
			<thead>
			<tr>
					<th scope="col"><?php _e( 'Parameter', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Description', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Possible Values', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Default', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Optional', 'leaguemanager' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr class="" valign="top">
					<td>league_id</td>
					<td><?php _e( 'ID of League', 'leaguemanager' ) ?></td>
					<td><em>integer</em></td>
					<td>&#160;</td>
					<td><?php _e( 'No', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>league_name</td>
					<td><?php _e( 'get league by name instead of ID (cannot be used together with attribute league_id)', 'leaguemanager' ) ?></td>
					<td><em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>season</td>
					<td><?php _e( 'display standings of specific season (default is last season)', 'leaguemanager' ) ?></td>
					<td><em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>team</td>
					<td><?php _e( 'display only matche of given Team ID', 'leaguemanager' ) ?></td>
					<td><em>integer</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>template</td>
					<td><?php _e( 'specifies template to use', 'leaguemanager' ) ?></td>
					<td><?php _e( 'name of template file without extension, whereas the name has to be matches-X', 'leaguemanager' ) ?></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>mode</td>
					<td><?php _e( 'control which matches to display', 'leaguemanager' ) ?></td>
					<td><em>all</em>, <em>home</em>, <em>racing</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>roster</td>
					<td><?php _e( 'Only works with Racing. Display race results for given racer. Can be either ID or name, but ID is recommended.', 'leaguemanager' ) ?></td>
					<td><em>integer</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>match_day</td>
					<td><?php _e( 'display matches of given match day. Alternatively use <em>next</em>, <em>last</em> or <em>current/latest</em>', 'leaguemanager' ) ?></td>
					<td><em>integer</em> or <em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>group</td>
					<td><?php _e( 'get matches of specific group', 'leaguemanager' ) ?></td>
					<td><em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>time</td>
					<td><?php _e( 'use this to get either upcoming (next) or previous (prev) matches', 'leaguemanager' ) ?></td>
					<td><em>next</em>, <em>prev</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>show_match_day_selection</td>
					<td><?php _e( 'force showing or hiding dropdown for match days', 'leaguemanager' ) ?></td>
					<td><em>true</em>, <em>false</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>show_team_selection</td>
					<td><?php _e( 'force showing or hiding dropdown for teams', 'leaguemanager' ) ?></td>
					<td><em>true</em>, <em>false</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
			</tbody>
			</table>

			<!-- Shortcode for Crosstable -->
			<p><?php _e( 'The crosstable of a league is displayed with', 'leaguemanager' ) ?></p>
			<blockquote><p>[crosstable league_id=ID]</p></blockquote>
			<table class="widefat">
			<thead>
			<tr>
					<th scope="col"><?php _e( 'Parameter', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Description', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Possible Values', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Default', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Optional', 'leaguemanager' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr class="" valign="top">
					<td>league_id</td>
					<td><?php _e( 'ID of League', 'leaguemanager' ) ?></td>
					<td><em>integer</em></td>
					<td>&#160;</td>
					<td><?php _e( 'No', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>league_name</td>
					<td><?php _e( 'get league by name instead of ID (cannot be used together with attribute league_id)', 'leaguemanager' ) ?></td>
					<td><em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>season</td>
					<td><?php _e( 'display standings of specific season (default is last season)', 'leaguemanager' ) ?></td>
					<td><em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>mode</td>
					<td><?php _e( 'embed table in page or display a link to open it in a thickbox popup window', 'leaguemanager' ) ?></td>
					<td><?php _e( '<em>embed</em> or <em>popup</em>', 'leaguemanager' ) ?></td>
					<td></td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>template</td>
					<td><?php _e( 'specifies template to use', 'leaguemanager' ) ?></td>
					<td><?php _e( 'name of template file without extension, whereas the name has to be crosstable-X', 'leaguemanager' ) ?></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
			</tbody>
			</table>

			<!-- Shortcode for single match -->
			<p><?php _e( 'A single match is displayed with', 'leaguemanager' ) ?></p>
			<blockquote><p>[match id=ID template=X]</p></blockquote>
			<table class="widefat">
			<thead>
			<tr>
					<th scope="col"><?php _e( 'Parameter', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Description', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Possible Values', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Default', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Optional', 'leaguemanager' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr class="" valign="top">
					<td>id</td>
					<td><?php _e( 'ID of match', 'leaguemanager' ) ?></td>
					<td><em>integer</em></td>
					<td>&#160;</td>
					<td><?php _e( 'No', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>template</td>
					<td><?php _e( 'specifies template to use', 'leaguemanager' ) ?></td>
					<td><?php _e( 'name of template file without extension, whereas the name has to be match-X', 'leaguemanager' ) ?></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
			</tbody>
			</table>

			<!-- Shortcode for teams -->
			<p><?php _e( 'A teams list of a league is displayed with', 'leaguemanager' ) ?></p>
			<blockquote><p>[teams league_id=ID]</p></blockquote>
			<table class="widefat">
			<thead>
			<tr>
					<th scope="col"><?php _e( 'Parameter', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Description', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Possible Values', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Default', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Optional', 'leaguemanager' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr class="" valign="top">
					<td>league_id</td>
					<td><?php _e( 'ID of League', 'leaguemanager' ) ?></td>
					<td><em>integer</em></td>
					<td>&#160;</td>
					<td><?php _e( 'No', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>season</td>
					<td><?php _e( 'display standings of specific season (default is last season)', 'leaguemanager' ) ?></td>
					<td><em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>template</td>
					<td><?php _e( 'specifies template to use', 'leaguemanager' ) ?></td>
					<td><?php _e( 'name of template file without extension, whereas the name has to be teams-X', 'leaguemanager' ) ?></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
			</tbody>
			</table>

			<!-- Shortcode for single team -->
			<p><?php _e( 'A single team is displayed with', 'leaguemanager' ) ?></p>
			<blockquote><p>[team id=ID template=X]</p></blockquote>
			<table class="widefat">
			<thead>
			<tr>
					<th scope="col"><?php _e( 'Parameter', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Description', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Possible Values', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Default', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Optional', 'leaguemanager' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr class="" valign="top">
					<td>id</td>
					<td><?php _e( 'ID of team', 'leaguemanager' ) ?></td>
					<td><em>integer</em></td>
					<td>&#160;</td>
					<td><?php _e( 'No', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>template</td>
					<td><?php _e( 'specifies template to use', 'leaguemanager' ) ?></td>
					<td><?php _e( 'name of template file without extension, whereas the name has to be team-X', 'leaguemanager' ) ?></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
			</tbody>
			</table>

			<!-- Shortcode for Championship -->
			<p><?php _e( 'A championship league is displayed with', 'leaguemanager' ) ?></p>
			<blockquote><p>[championship league_id=ID]</p></blockquote>
			<table class="widefat">
			<thead>
			<tr>
					<th scope="col"><?php _e( 'Parameter', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Description', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Possible Values', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Default', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Optional', 'leaguemanager' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr class="" valign="top">
					<td>league_id</td>
					<td><?php _e( 'ID of League', 'leaguemanager' ) ?></td>
					<td><em>integer</em></td>
					<td>&#160;</td>
					<td><?php _e( 'No', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>league_name</td>
					<td><?php _e( 'get league by name instead of ID (cannot be used together with attribute league_id)', 'leaguemanager' ) ?></td>
					<td><em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>season</td>
					<td><?php _e( 'display standings of specific season (default is last season)', 'leaguemanager' ) ?></td>
					<td><em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>template</td>
					<td><?php _e( 'specifies template to use', 'leaguemanager' ) ?></td>
					<td><?php _e( 'name of template file without extension, whereas the name has to be championship-X', 'leaguemanager' ) ?></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
			</tbody>
			</table>

			<!-- Shortcode for Archive -->
			<p><?php _e( 'An archive of a league is displayed with', 'leaguemanager' ) ?></p>
			<blockquote><p>[leaguearchive league_id=ID]</p></blockquote>
			<table class="widefat">
			<thead>
			<tr>
					<th scope="col"><?php _e( 'Parameter', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Description', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Possible Values', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Default', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Optional', 'leaguemanager' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr class="" valign="top">
					<td>league_id</td>
					<td><?php _e( 'ID of League', 'leaguemanager' ) ?></td>
					<td><em>integer</em></td>
					<td>&#160;</td>
					<td><?php _e( 'No', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>league_name</td>
					<td><?php _e( 'get league by name instead of ID (cannot be used together with attribute league_id)', 'leaguemanager' ) ?></td>
					<td><em>string</em></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>template</td>
					<td><?php _e( 'specifies template to use', 'leaguemanager' ) ?></td>
					<td><?php _e( 'name of template file without extension, whereas the name has to be leaguearchive-X', 'leaguemanager' ) ?></td>
					<td>&#160;</td>
					<td><?php _e( 'Yes', 'leaguemanager' ) ?></td>
				</tr>
			</tbody>
			</table>
		</div>

		<div id="templates">
			<a href="#top" class="alignright top-link"><?php _e( 'Top', 'leaguemanager' ) ?></a>
			<h2><?php _e( 'Templates', 'leaguemanager' ) ?></h2>
			<p><?php _e( 'Templates are special files that are used to display plugin data in the website frontend. They reside in the following directory', 'leaguemanager' ) ?></p>
			<blockquote><p>WP_PLUGIN_DIR/leaguemanager/templates/</p></blockquote>
			<p><?php _e( 'The following table lists all available default templates', 'leaguemanager' ) ?></p>
			<table class="widefat">
			<thead>
				<tr>
					<th scope="col"><?php _e( 'Template', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Description', 'leaguemanager' ) ?></th>
				</tr>
				</thead>
				<tbody>
				<tr class="" valign="top">
					<td>archive.php</td>
					<td><?php _e( 'Display an archive of LeagueManager.', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>championship.php</td>
					<td><?php _e( 'Championship display', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>crosstable.php</td>
					<td><?php _e( 'Crosstable', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>matches.php</td>
					<td><?php _e( 'Matches output. There exist multiple templates in the format matches-X.php. You can also design custom templates in this form and load them with the attribute template=X in the shortcode. If X is the sport type, this template is loaded automatically without the template attribute', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>match.php</td>
					<td><?php _e( 'Individual match. There exist multiple templates in the format match-X.php. You can also design custom templates in this form and load them with the attribute template=X in the shortcode', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>teams.php</td>
					<td><?php _e( 'List of teams', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="" valign="top">
					<td>team.php</td>
					<td><?php _e( 'Individual team', 'leaguemanager' ) ?></td>
				</tr>
				<tr class="alternate" valign="top">
					<td>standings.php</td>
					<td><?php _e( 'Standings table. There exist multiple templates in the format standings-X.php. You can also design custom templates in this form and load them with the attribute template=X in the shortcode', 'leaguemanager' ) ?></td>
				</tr>
			</tbody>
			</table>
			<h3><?php _e('Customization of template files', 'leaguemanager') ?></h3>
			<p><?php _e( 'If you want to modify existing templates copy it to', 'leaguemanager' ) ?></p>
			<blockquote><p>your_theme_dir/leaguemanager/</p></blockquote>
			<p><?php _e( 'The plugin will then first look in your theme directory. Further it is possible to design own templates, e.g. multiple standings templates. Assume you create a template called <strong>standings-sample1.php</strong>. To load this template use the following code.', 'leaguemanager' ) ?></p>
			<blockquote><p>[standings league_id=ID template=<strong>sample1</strong>]</p></blockquote>

			<h3><?php _e('Customization of sport files', 'leaguemanager') ?></h3>
			<p><?php _e( 'If you want to modify existing sports copy it to', 'leaguemanager' ) ?></p>
			<blockquote><p>your_theme_dir/sports/</p></blockquote>

		</div>
			
		<div id='import'>
			<a href="#top" class="alignright top-link"><?php _e( 'Top', 'projectmanager' ) ?></a>
			<h2><?php _e( 'Data Import', 'leaguemanager' ) ?></h2>
			<h3><?php _e( 'Teams', 'leaguemanager' ) ?></h3>
			<p><?php _e( 'Teams and Matches can be imported from tab or csv-separated files, which can be easily created using Excel', 'leaguemanager' ) ?></p>
			<p><?php _e( 'The file to import Teams has the following structure. Read from left-to-right and line-by-line.', 'leaguemanager' ) ?></p>
			<ul class="doc-import-structure">
				<li><?php _e('Season','leaguemanager') ?></li>
				<li><?php _e( 'Team Name', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Website', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Coach', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Stadium', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Home (0 or 1)', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Group', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Logo', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Played Matches', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Won Matches', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Tie Matches', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Lost Matches', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Secondary Points', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Points', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Roster', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Custom Fields', 'leaguemanager' ) ?> ...</li>
			</ul>
			<p><?php _e( "Only <strong>season</strong> and <strong>team name</strong> are <strong>mandatory</strong>. The logo files need to be uploaded separately to the upload folder set in the league preferences. Although the match statistics and points can be included they are not required and would be overwritten by new match results. The last column can be optionally the team roster, which are the project ID and category ID in the format <em>projectID_catID</em> for <a href='%s'>ProjectManager</a>.", 'leaguemanager' ) ?></p>
			<p><?php _e( 'After the team roster any number of custom fields depending on the sports type can be included. In this case all the above columns have to be present', 'leaguemanager' ) ?></p>

			<h4><?php _e( 'Baseball', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Runs For', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Runs Against', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Games Behind', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Shutouts', 'leaguemanager' ) ?></li>
			</ul>
			
			<h4><?php _e( 'Gymnastics', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Floor', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Pommelhorse', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Rings', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Vault', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Parallel bars', 'leaguemanager' ) ?></li>
				<li><?php _e( 'High bars', 'leaguemanager' ) ?></li>
			</ul>
			
			<h4><?php _e( 'Pool', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'For Score', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Against Score', 'leaguemanager' ) ?></li>
			</ul>
			
			<h4><?php _e( 'Rugby', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Gamepoints', 'leaguemanager' ) ?> (%d:%d)</li>
			</ul>
			
			<h4><?php _e( 'Tennis', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Straight Set Win', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Split Set Win', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Straight Set Lost', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Split Set Lost', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Games Allowed', 'leaguemanager' ) ?></li>
			</ul>
			
			<h4><?php _e( 'Volleyball', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Sets', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Ballpoints', 'leaguemanager' ) ?> (%d:%d)</li>
			</ul>
			
			
			<h3><?php _e( 'Matches', 'leaguemanager' ) ?></h3>
			<p><?php _e( 'The file to import matches has the following structure Read from left-to-right and line-by-line.', 'leaguemanager' ) ?></p>
			<ul class="doc-import-structure">
				<li><?php _e('Date','leaguemanager') ?></li>
				<li><?php _e('Season','leaguemanager') ?></li>
				<li><?php _e('Match Day','leaguemanager') ?></li>
				<li><?php _e('Home Team','leaguemanager') ?></li>
				<li><?php _e('Guest Team','leaguemanager') ?></li>
				<li><?php _e('Location','leaguemanager') ?></li>
				<li><?php _e('Begin Time','leaguemanager') ?> (%d:%d)</li>
				<li><?php _e('Group','leaguemanager') ?></li>
				<li><?php _e('Score','leaguemanager') ?> (%d:%d)</li>
				<li><?php _e('Custom Fields','leaguemanager') ?> ...</li>
			</ul>
			<p><?php _e( 'For matches all columns, except the custom fields have to be present. Any number of custom fields depending on the sports type can be included at the end', 'leaguemanager' ) ?></p>
			
			<h4><?php _e( 'Baseball', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Runs For', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Runs Against', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Shutouts', 'leaguemanager' ) ?> (%d:%d)</li>
			</ul>
			
			<h4><?php _e( 'Basketball', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Quarter 1', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Quarter 2', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Quarter 3', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Quarter 4', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Overtime', 'leaguemanager' ) ?> (%d:%d)</li>
			</ul>
			
			<h4><?php _e( 'Gymnastics', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Floor', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Pommelhorse', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Rings', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Vault', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Parallel bars', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'High bars', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Apparatus Points', 'leaguemanager' ) ?> (%d:%d)</li>
			</ul>
			
			<h4><?php _e( 'Hockey', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Third 1', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Third 2', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Third 3', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Overtime', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Penalty', 'leaguemanager' ) ?> (%d:%d)</li>
			</ul>
			
			<h4><?php _e( 'Irish Gaelic Football', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Goals', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Points', 'leaguemanager' ) ?> (%d:%d)</li>
			</ul>
			
			<h4><?php _e( 'Pool', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'For Score', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Against Score', 'leaguemanager' ) ?></li>
			</ul>
			
			<h4><?php _e( 'Racing', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Racing Type', 'leaguemanager' ) ?></li>
			</ul>
			
			<h4><?php _e( 'Rugby', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Tries', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Conversions', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Penalties', 'leaguemanager' ) ?> (%d:%d)</li>
			</ul>
			
			<h4><?php _e( 'Soccer', 'leaguemanager' ) ?> / <?php _e( 'Handball', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Halftime', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Overtime', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Penalty', 'leaguemanager' ) ?> (%d:%d)</li>
			</ul>
			
			<h4><?php _e( 'Tennis', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Set 1', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Set 2', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Set 3', 'leaguemanager' ) ?> (%d:%d)</li>
				<li>...</li>
			</ul>
			
			<h4><?php _e( 'Volleyball', 'leaguemanager' ) ?></h4>
			<ul class="doc-import-structure">
				<li><?php _e( 'Set 1', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Set 2', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Set 3', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Set 4', 'leaguemanager' ) ?> (%d:%d)</li>
				<li><?php _e( 'Set 5', 'leaguemanager' ) ?> (%d:%d)</li>
			</ul>
		</div>
		
		<div id='slideshows'>
			<a href="#top" class="alignright top-link"><?php _e( 'Top', 'projectmanager' ) ?></a>
			<h2><?php _e( 'Slideshows', 'leaguemanager' ) ?></h2>
			<p><?php printf(__( "Fancy slideshows of previous or upcoming matches can be easily created in combination with the <a href='%s' target='_blank'>Fancy Slideshows Plugin</a>. There are some limitations and specic requirements as listed below", 'leaguemanager' ), 'https://wordpress.org/plugins/sponsors-slideshow-widget/') ?></p>
			<ul style="list-style: disc; margin-left: 1em;">
				<li><?php _e( 'You should set a height for the slideshow as there is no main image to display. Otherwise each slide might have different heights and some content could be not displayed', 'leaguemanager' ) ?></li>
				<li><?php _e( 'You should set the slide overlay to show title & description. The title contains the match title and logos, while the description contains the date, location and score of the match', 'leaguemanager' ) ?></li>
				<li><?php _e( 'Slide overlay animations are not possible so you have to disable them.', 'leaguemanager' ) ?></li>
				<li><?php _e( 'The slide overlay style has to be set to <em>LeagueManager</em>', 'leaguemanager' ) ?></li>
			</ul>
		</div>
		
		<div id='roster_profiles'>
			<a href="#top" class="alignright top-link"><?php _e( 'Top', 'projectmanager' ) ?></a>
			<h2><?php _e( 'Team Roster & Profiles', 'leaguemanager' ) ?></h2>
			<p><?php printf(__( "Team Roster and additional Profiles can be generated using the <a href='%s' target='_blank'>ProjectManager Plugin</a>. This plugin has been specifically developed to manage athlete portraits. If you have ProjectManager installed, each team can be assigned a team roster, i.e. project and possibly category, in the team edit page. Team Profiles can be also extended using ProjectManager. Simply set the project and maybe category in the league settings page. Afterwards you can assign a team profile, i.e. dataset, to each team in the team edit page.", 'leaguemanager' ), 'https://wordpress.org/plugins/projectmanager') ?></p>
		</div>
		
		<div id="template_tags">
			<a href="#top" class="alignright top-link"><?php _e( 'Top', 'leaguemanager' ) ?></a>
			<h2><?php _e( 'Template Tags', 'leaguemanager' ) ?></h2>
			<p><?php _e( 'Template Tags are functions that can be used in your Wordpress Theme to display the plugin data. Here is a brief listing of available tags. For details see file functions.php', 'leaguemanager' ) ?><p>
			<dl class="leaguemanager">
				<dt><pre>&lt;?php leaguemanager_standings( $league_id, $args ) ?&gt;</pre></dt><dd><?php _e( 'display standings of given league with $league_id', 'leaguemanager' ) ?></dd>
				<dt><pre>&lt;?php leaguemanager_crosstable( $league_id, $args ) ?&gt;</pre></dt><dd><?php _e( 'display crosstable of given league with $league_id', 'leaguemanager' ) ?></dd>
				<dt><pre>&lt;?php leaguemanager_matches( $league_id, $args ) ?&gt;</pre></dt><dd><?php _e( 'display matches of given league with $league_id', 'leaguemanager' ) ?></dd>
				<dt><pre>&lt;?php leaguemanager_match( $match_id, $args ) ?&gt;</pre></dt><dd><?php _e( 'display match with given $match_id', 'leaguemanager' ) ?></dd>
				<dt><pre>&lt;?php leaguemanager_teams( $league_id, $args ) ?&gt;</pre></dt><dd><?php _e( 'display teams of given league with $league_id', 'leaguemanager' ) ?></dd>
				<dt><pre>&lt;?php leaguemanager_team( $team_id, $args ) ?&gt;</pre></dt><dd><?php _e( 'display team with given $team_id', 'leaguemanager' ) ?></dd>
				<dt><pre>&lt;?php leaguemanager_championship( $league_id, $args ) ?&gt;</pre></dt><dd><?php _e( 'display championship', 'leaguemanager' ) ?></dd>
				<dt><pre>&lt;?php leaguemanager_display_widget( $number, $instance ) ?&gt;</pre></dt><dd><?php _e( 'Display widget. <em>$number</em> is the widget number and <em>$instance</em> is an assoziative array of widget settings. See lib/widget.php function widget for details.', 'leaguemanager' ) ?></dd>
				<dt><pre>&lt;?php leaguemanager_display_next_match_box( $number, $instance ) ?&gt;</pre></dt><dd><?php _e( 'display box of next matches. <em>$number</em> is the widget number and <em>$instance</em> is an assoziative array of widget settings. See lib/widget.php function widget for details.', 'leaguemanager' ) ?></dd>
				<dt><pre>&lt;?php leaguemanager_display_prev_match_box( $number, $instance ) ?&gt;</pre></dt><dd><?php _e( 'display box of previous matches. <em>$number</em> is the widget number and <em>$instance</em> is an assoziative array of widget settings. See lib/widget.php function widget for details.', 'leaguemanager' ) ?></dd>
			</dl>
			<p><?php _e( 'The variable <em>$args</em> is always an assoziative array of additional arguments with keys being the same as the shortcode attributes.', 'leaguemanager' ) ?></p>
		</div>
		
		<div id="settings">
			<a href="#top" class="alignright top-link"><?php _e( 'Top', 'leaguemanager' ) ?></a>
			<h2><?php _e( 'League Settings', 'leaguemanager' ) ?></h2>

			<h3><?php _e( 'Sport', 'leaguemanager' ) ?></h3>
			<p><?php _e( "The sport type is important to enable certain rules. Gymnastics leagues have apparatus points which other leagues don't, Soccer has Halftime results, while Hocky is played in Thirds and Basketball in Quarters.", 'leaguemanager' ) ?></p>
			<?php do_action( 'leaguemanager_doc_sports' ) ?>

			<h3><?php _e( 'Point Rules', 'leaguemanager' ) ?></h3>
			<p><?php _e( 'The second important option is the point rule, which automatically sets the number of points teams get for won matches, draw matches or lost matches. Some league types have specific rules. See the sections below for details.', 'leaguemanager') ?></p>
				
			<h5><?php _e( 'One-Point-Rule', 'leaguemanager' ) ?></h5>
			<p><?php _e( 'The One-Point-Rule simply counts the number of won matches. This point system is used, e.g. in the MLB, NBA and NFL.', 'leaguemanager' ) ?></p>
				
			<h5><?php _e( 'Two-Point-Rule and Three-Point-Rule', 'leaguemanager' ) ?></h5>
			<p><?php _e( 'The Two- and Three-Point-Rules are the most common ones. Teams get two or three points for won matches respectively and one point for draw.', 'leaguemanager' ) ?></p>

			<h5><?php _e( '<em>Score</em> Point-Rule', 'leaguemanager' ) ?></h5>
			<p><?php _e( 'Teams get one point for each goal scored during a game.', 'leaguemanager' ) ?></p>

			<h5><?php _e( 'User defined', 'leaguemanager' ) ?></h5>
			<p><?php _e( 'User defined points for won, tie and lost matches. Overtime win/loss points does not work with all sport types!', 'leaguemanager' ) ?></p>

			<?php do_action( 'leaguemanager_doc_point_rules' ) ?>

			<h3><?php _e( 'Point Format', 'leaguemanager' ) ?></h3>
			<p><?php _e( 'The point format controls how the points are displayed. Most sports would choose some %d variation which treats the points as integer. Only if a user defined point rule with half points is used, a %f variation has to be used to display the points as floating number.', 'leaguemanager' ) ?></p>
		</div>
		
		<div id="customization">
			<a href="#top" class="alignright top-link"><?php _e( 'Top', 'leaguemanager' ) ?></a>
			<h2><?php _e( 'Customization', 'leaguemanager' ) ?></h2>
			<p><?php _e( 'The Plugin is built in a modular way with several Wordpress hooks to make customization as easy as possible. The filters are needed to implement rules for new sport types. I here provide a list of available hooks with a short description. For examples on usage see sports files in the sports subdirectory or lib/stats.php.', 'leaguemanager' ) ?></p>

			<h3><?php _e( 'List of Wordpress Filters', 'leaguemanager' ) ?></h3>
			<table class="widefat">
			<thead>
			<tr>
					<th scope="col"><?php _e( 'Filter', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Description', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Location', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Comments', 'leaguemanager' ) ?></th>
				</tr>
			</thead>
			<tbody>
			<tr class="" valign="top">
				<td>leaguemanager_sports</td>
				<td><?php _e( 'add a new sport type to the settings selection menu', 'leaguemanager' ) ?></td>
				<td>lib/core.php: function getLeagueTypes()</td>
				<td>&#160;</td>
			</tr>
			<tr class="alternate" valign="top">
				<td>rank_teams_<em>$sport</em></td>
				<td><?php _e( 'Change Team Ranking based on sport specific rules', 'leaguemanager' ) ?></td>
				<td>lib/core.php: function rankTeams()</td>
				<td>&#160;</td>
			</tr>
			<tr class="" valign="top">
				<td>leaguemanager_cards</td>
				<td><?php _e( 'manipulate card types (yellow, red, yellow-red)', 'leaguemanager' ) ?></td>
				<td>lib/core.php: function getCards()</td>
				<td>&#160;</td>
			</tr>
			<tr class="alternate" valign="top">
				<td>league_menu_<em>$sport</em> and league_menu_<em>$mode</em></td>
				<td><?php _e( 'manipulate league menu based on current sport type and mode', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function getMenu()</td>
				<td>&#160;</td>
			</tr>
			<tr class="" valign="top">
				<td>leaguemanager_modes</td>
				<td><?php _e( 'manipulate available modes', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function getModes()</td>
				<td>&#160;</td>
			</tr>
			<tr class="alternate" valign="top">
				<td>leaguemanager_point_rule_list</td>
				<td><?php _e( 'manipulate List of available point rules', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function getPointRules()</td>
				<td>&#160;</td>
			</tr>
			<tr class="" valign="top">
				<td>leaguemanager_point_rules</td>
				<td><?php _e( 'manipulate point rules', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function getPointRule()</td>
			</tr>
			<tr class="alternate" valign="top">
				<td>leaguemanager_point_formats</td>
				<td><?php _e( 'manipulate available point formats', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function getPointFormats()</td>
				<td>&#160;</td>
			</tr>
			<tr class="" valign="top">
				<td>team_points2_<em>$sport</em></td>
				<td><?php _e( 'calculate secondary points for team based on current sport type', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function saveStandings()</td>
				<td><?php _e( 'input naming structure name="custom[$team_id][points2][plus]" and name="custom[$team_id][points2][minus]" for manual saving of standing table.', 'leaguemanager' ) ?></td>
			</tr>
			<tr class="alternate" valign="top">
				<td>team_points_<em>$sport</em></td>
				<td><?php _e( 'manipulate team points based on current sport rules', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function calculatePoints()</td>
				<td>&#160;</td>
			</tr>
			<tr class="" valign="top">
				<td>leaguemanager_import_teams_<em>$sport</em></td>
				<td><?php _e( 'filter custom data for team', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function importTeams()</td>
				<td>&#160;</td>
			</tr>
			<tr class="alternate" valign="top">
				<td>leaguemanager_import_matches_<em>$sport</em></td>
				<td><?php _e( 'filter custom data for match', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function importMatches()</td>
				<td>&#160;</td>
			</tr>
			<tr class="" valign="top">
				<td>leaguemanager_export_teams_header_<em>$sport</em></td>
				<td><?php _e( 'append custom header data for team export', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function exportTeams()</td>
				<td>&#160;</td>
			</tr>
			<tr class="alternate" valign="top">
				<td>leaguemanager_export_teams_data_<em>$sport</em></td>
				<td><?php _e( 'append custom data for team export', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function exportTeams()</td>
				<td>&#160;</td>
			</tr>
			<tr class="" valign="top">
				<td>leaguemanager_export_matches_header_<em>$sport</em></td>
				<td><?php _e( 'append custom header data for match export', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function exportMatches()</td>
				<td>&#160;</td>
			</tr>
			<tr class="alternate" valign="top">
				<td>leaguemanager_export_matches_data_<em>$sport</em></td>
				<td><?php _e( 'append custom data for match export', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function exportMatches()</td>
				<td>&#160;</td>
			</tbody>
			</table>

			<h3><?php _e( 'List of Wordpress Actions', 'leaguemanager' ) ?></h3>
			<table class="widefat">
			<thead>
			<tr>
					<th scope="col"><?php _e( 'Action', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Description', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Location', 'leaguemanager' ) ?></th>
					<th scope="col"><?php _e( 'Comments', 'leaguemanager' ) ?></th>
				</tr>
			</thead>
			<tbody>
			<tr class="" valign="top">
				<td>leaguemanager_save_standings_<em>$sport</em></td>
				<td><?php _e( 'called when standings are saved for each team in automatic mode', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function saveStandings()</td>
				<td>&#160;</td>
			</tr>
			<tr class="alternate" valign="top">
				<td>leaguemanager_update_results_<em>$sport</em></td>
				<td><?php _e( 'called when results for each match are saved', 'leaguemanager' ) ?></td>
				<td>admin/admin.php: function updateResults()</td>
				<td>&#160;</td>
			</tr>
			<tr class="" valign="top">
				<td>leaguemanager_standings_header_<em>$sport</em></td>
				<td><?php _e( 'add additional columns to standings table header', 'leaguemanager' ) ?></td>
				<td>admin/show-league.php, templates/standings-extend.php</td>
				<td>&#160;</td>
			</tr>
			<tr class="alternate" valign="top">
				<td>leaguemanager_standings_columns_<em>$sport</em></td>
				<td><?php _e( 'add additional columns to standings table', 'leaguemanager' ) ?></td>
				<td>admin/show-league.php, templates/standings-extend.php</td>
				<td><?php _e( 'input naming structure name="custom[$team_id][points2][plus]" and name="custom[$team_id][points2][minus]" for secondary points. Otherwise name="custom[<em>name</em>]". Access via <em>$team->name</em>.</td> ', 'leaguemanager' ) ?></td>
			</tr>
			<tr class="" valign="top">
				<td>matchtable_header_<em>$sport</em></td>
				<td><?php _e( 'add additional columns to match table header', 'leaguemanager' ) ?></td>
				<td>admin/show-league.php</td>
				<td>&#160;</td>
			</tr>
			<tr class="alternate" valign="top">
				<td>matchtable_columns_<em>$sport</em></td>
				<td><?php _e( 'add additional columns to match table table', 'leaguemanager' ) ?></td>
				<td>admin/show-league.php</td>
				<td><?php _e ( 'input naming structure name="custom[$match_id][<em>name</em>]". Access via <em>$match->name</em>', 'leaguemanager' ) ?></td>
			</tr>
			<tr class="" valign="top">
				<td>leaguemanager_edit_match_<em>$sport</em></td>
				<td><?php _e( 'substitute match edit form with custom form', 'leaguemanager' ) ?></td>
				<td>admin/match.php</td>
				<td>&#160;</td>
			</tr>
			<tr class="alternate" valign="top">
				<td>league_settings, league_settings_<em>$sport</em>, league_settings_<em>$mode</em></td>
				<td><?php _e( 'add custom settings for league dependent on chosen sport or league mode', 'leaguemanager' ) ?></td>
				<td>admin/settings.php</td>
				<td><?php _e( 'name structure of input fields is name="settings[<em>name</em>]". access of values is <em>$league->name</em>', 'leaguemanager' ) ?></td>
			</tr>
			<tr class="" valign="top">
				<td>team_edit_form, team_edit_form_<em>$sport</em></td>
				<td><?php _e( 'add custom fields for team; used to add hidden input fields based on sport type', 'leaguemanager' ) ?></td>
				<td>admin/team.php</td>
				<td><?php _e( 'input name structure is name="custom[<em>name</em>]". access of values is <em>$team->name</em>', 'leaguemanager' ) ?></td>
			</tr>
			<tr class="alternate" valign="top">
				<td>leaguemanager_doc_sports</td>
				<td><?php _e( 'add documentation on specific sport type', 'leaguemanager' ) ?></td>
				<td>admin/documentation.php</td>
				<td>&#160;</td>
			</tr>
			<tr class="" valign="top">
				<td>leaguemanager_doc_point_rules</td>
				<td><?php _e( 'add documentation on point rules', 'leaguemanager' ) ?></td>
				<td>admin/documentation.php</td>
				<td>&#160;</td>
			</tr>
			<tr class="alternate" valign="top">
				<td>leaguemanager_widget_next_match, leaguemanager_widget_prev_match</td>
				<td><?php _e( 'call next or previous match, respectiveley', 'leaguemanager' ) ?></td>
				<td>lib/widget.php</td>
				<td><?php _e( 'remove action before hooking own function by remove_all_actions("leaguemanager_widget_next_match") or remove_all_action("leaguemanager_widget_prev_match")', 'leaguemanager' ) ?></td>
			</tr>
			<tr class="" valign="top">
				<td>leaguemanager_team_page</td>
				<td><?php _e( 'display infopage of single team', 'leaguemanager' ) ?></td>
				<td>templates/teams.php</td>
				<td>&#160;</td>
			</tbody>
			</table>
		</div>
		
		<div id="howto_intro">
			<a href="#top" class="alignright top-link"><?php _e( 'Top', 'leaguemanager' ) ?></a>
			<h2><?php _e( 'HowTo', 'leaguemanager' ) ?></h2>
			<p><?php printf( __( 'The main page of LeagueManager shows an <a href="%s" class="thickbox" rel="howto">overview of leagues</a> in the database together with a few statistics on number of seasons, teams and matches. At the beginning it is necessary to add at least one season for which the number of match days is also specified. Furthermore the <a href="%s" class="thickbox" rel="howto">league preferences</a> need to be set. One can choose from numerous sport types, which implement different extensions, point rules, point formats, team ranking and different other options. Afterwards <a href="%s" class="thickbox" rel="howto">teams</a> and <a href="%s" class="thickbox" rel="howto">matches</a> can be added to database. Data from LeagueManager can be inserted in a page or post using the cup <a href="%s" class="thickbox" rel="howto">TinyMCE Button</a> or directly shortcodes introduced above. Furthermore matches and standings can be displayed in the sidebar with a <a href="%s" class="thickbox" rel="howto">widget</a>. A page or post can be also marked as <a href="%s" class="thickbox" rel="howto">match report</a> with easy-to-use dropdown menus from which the match can be selected.', 'leaguemanager' ), LEAGUEMANAGER_URL.'/admin/doc/overview.png', LEAGUEMANAGER_URL.'/admin/doc/settings.png', LEAGUEMANAGER_URL.'/admin/doc/add_team.png', LEAGUEMANAGER_URL.'/admin/doc/add_matches.png', LEAGUEMANAGER_URL.'/admin/doc/page.png', LEAGUEMANAGER_URL.'/admin/doc/widget.png', LEAGUEMANAGER_URL.'/admin/doc/match_report.png' ) ?></p>

			<h3 id="championship"><?php _e( 'Championship', 'leaguemanager' ) ?></h3>
			<p><?php printf( __( 'The Championship mode is designed for soccer championships and may not work with other types. When changing the league mode, additional preferences could appear after saving. For championship mode two new settings will appear. <em>Groups</em> defines the group names which are available. <em>Teams Advance</em> is the number of teams which advance from the preliminary round to the final rounds. The number of final rounds is automatically calculated from the number of groups and the number of advancing teams. In championship mode, each team has be assigned to a group. The <a href="%s" class="thickbox" rel="howto-championship">preliminary rounds</a> are displayed on the bottom of the league overview page. Before adding matches a group needs to be selected, either from the group dropdown on the top of the page or in the preliminary section. <a href="%s" class="thickbox" rel="howto-championship">Final round matches</a> are added by using the <em>Actions</em> dropdown in the Finals section. Final round matches are added in a symbolic way before the championship starts. The plugin will automatically advance from the preliminary rounds to the final rounds as soon as all preliminary round matches in all groups are finished (have results). This is also the case for advancing from one final round to the next.', 'leaguemanager' ), LEAGUEMANAGER_URL.'/admin/doc/championship2.png', LEAGUEMANAGER_URL.'/admin/doc/championship1.png' ) ?></p>

			<h3 id="team_roster"><?php _e( 'Team Roster', 'leaguemanager' ) ?></h3>
			<p><?php printf( __( 'LeagueManager itself cannot manage team rosters, but has to be done together with the plugin <a href="%s" target="_blank">ProjectManager</a>. This plugin is specifically designed for team rosters. It allows the easy generation of custom input forms through the administration panel. Further datasets can be grouped into different categories using the wordpress category system. A template system allows a high degree of customization for displaying datasets in the output. It comes with two default templates to display datasets in a table list or show them as photo gallery, which is especially useful for team roster.', 'leaguemanager' ), "http://wordpress.org/extend/plugins/projectmanager/" ) ?></p>
			<p><?php printf( __( 'A project in ProjectManager can then be used as team roster and <a href="%s" class="thickbox" rel="howto-roster">linked to a specific team</a> in the team edit page. The team roster will be displayed in a list on an individual team page, e.g. by using the shortcode [team id=ID].', 'leaguemanager' ), LEAGUEMANAGER_URL.'/admin/doc/team_roster.png' ) ?></p>

			<h3 id="match_statistics"><?php _e( 'Match Statistics', 'leaguemanager' ) ?></h3>
			<p><?php printf( __( 'Match Statistics do not depend on ProjectManager and a team roster, but the combination makes it much more powerful and I recommend the combination. LeagueManager has a simple, but flexible match statistics module, which works similar to the formfields in ProjectManager. The first step is to activate match statistics in the league preferences and then <a href="%s" class="thickbox" rel="howto-statistics">add statistics a field</a>, e.g. Goals, by clicking on the Match Statistics link in the top menu on the league overview page. Each statistics needs a name and can have any number of fields, which are added dynamically. Each field can be either <em>text</em> or <em>roster</em>. Choose roster for a field which holds a player name, also if you do not use a team roster now, as it is more flexible.', 'leaguemanager' ), LEAGUEMANAGER_URL.'/admin/doc/stats.png') ?></p>
			<p><?php printf( __( 'After having set up the statistics fields you can return to the league overview page and <a href="%s" class="thickbox" rel="howto-statistics">add statistics for each match</a> by clicking the link in the matches table. Each field which was defined as roster will have a button next to the input field. Clicking on the button opens a popup window with a selection of players which are taken from ProjectManager. The statistics will be shown with each individual match, e.g. by using the shortcode [match id=ID].', 'leaguemanager' ), LEAGUEMANAGER_URL.'/admin/doc/match_statistics.png' ) ?></p>
			<p><?php printf( __( 'Each Statistics field will automatically create <a href="%s" class="thickbox" rel="howto-statistics">another formfield type in ProjectManager</a>. In this way match statistics from LeagueManager can be displayed in each player profile in ProjectManager. Any statistics which only counts the number of entries, e.g. Goals, Assists, Cards will be automatically calculated. ', 'leaguemanager' ), LEAGUEMANAGER_URL.'/admin/doc/goals_field.png') ?></p>
		</div>
		
		<div id="racing">
			<a href="#top" class="alignright top-link"><?php _e( 'Top', 'leaguemanager' ) ?></a>
			<h2><?php _e( 'Setup Racing Sports', 'leaguemanager' ) ?></h2>
			<p><?php printf(__('Racing mode has a special setup and strictly requires my other plugin %s to manage individual racer. After installing the plugin ProjectManager setup different cateogies for each racing team and setup individual profiles for each racer (Name is enough). Having setup the profiles for individual racer, add teams in LeagueManager and choose as team roster the respective project and category. Then add matches as normal only that for each racing event an individual title is used. The results are not required, I think, but the table for each team needs to be updated manually. By clicking on the results page you can insert results for each individual racer.', 'leaguemanager'), '<a href="https://wordpress.org/plugins/projectmanager/" target="_blank" title="Link to Projectmanager">ProjectManager</a>'); ?></p>
		</div>
	</div>
</div>

<?php endif; ?>
