//var Leaguemanager = new Object();
if(typeof Leaguemanager == "undefined") {
  var Leaguemanager = new Object();
}

Leaguemanager.reInit = function() {
	tb_init('a.thickbox, area.thickbox, input.thickbox');
}

Leaguemanager.insertLogoFromLibrary = function() {
  logo = document.getElementById('logo_library_url').value;

  var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
  ajax.execute = 1;
  ajax.method = 'POST';
  ajax.setVar( 'action', 'leaguemanager_insert_logo_from_library' );
  ajax.setVar( 'logo', logo );
  ajax.onError = function() { alert('Ajax error while getting seasons'); };
  ajax.onCompletion = function() { return true; };
  ajax.runAJAX();

  tb_remove();
}


Leaguemanager.addStatsField = function(requestUrl) {
  time = new Date();

  var ajax = new sack(requestUrl);
  ajax.execute = 1;
  ajax.method = 'POST';
  ajax.setVar( 'action', 'leaguemanager_add_stats_field' );
  ajax.onError = function() { alert('Ajax error while getting seasons'); };
  ajax.onCompletion = function() { return true; };
  ajax.runAJAX();
}

Leaguemanager.addStat = function(el_id, stat_id, match_id) {
  time = new Date();

  var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
  ajax.execute = 1;
  ajax.method = 'POST';
  ajax.setVar( 'action', 'leaguemanager_add_stat' );
  ajax.setVar( 'parent_id', el_id );
  ajax.setVar( 'stat_id', stat_id );
  ajax.setVar( 'match_id', match_id );
  ajax.onError = function() { alert('Ajax error while getting seasons'); };
  ajax.onCompletion = function() { return true; };
  ajax.runAJAX();
}

Leaguemanager.removeField = function(field_id, parent_id) {
	element_count = document.getElementById(parent_id).childNodes.length;
	if(element_count > 0) {
		target_element = document.getElementById(field_id);
		document.getElementById(parent_id).removeChild(target_element);
	}
	return false;
}

Leaguemanager.toggleTeamRosterGroups = function( roster ) {
	if ( '' == roster ) {
		jQuery('div#team_roster_groups').fadeOut('fast');
	} else {
		var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
		ajax.execute = 1;
		ajax.method = 'POST';
		ajax.setVar( 'action', 'leaguemanager_set_team_roster_groups' );
		ajax.setVar( 'roster', roster );
		ajax.onError = function() { alert('Ajax error while getting seasons'); };
		ajax.onCompletion = function() { return true; };
		ajax.runAJAX();
	}
}

Leaguemanager.getTeamFromDatabase = function() {
	var team_id = document.getElementById('team_db_select').value;

	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( 'action', 'leaguemanager_add_team_from_db' );
	ajax.setVar( 'team_id', team_id );
	ajax.onError = function() { alert('Ajax error while getting seasons'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();

	tb_remove();
}

Leaguemanager.getSeasonDropdown = function(league_id){
	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( 'action', 'leaguemanager_get_season_dropdown' );
	ajax.setVar( 'league_id', league_id );
	ajax.onError = function() { alert('Ajax error while getting seasons'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}

Leaguemanager.getMatchDropdown = function(league_id, season) {
	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( 'action', 'leaguemanager_get_match_dropdown' );
	ajax.setVar( 'league_id', league_id );
	ajax.setVar( 'season', season );
	ajax.onError = function() { alert('Ajax error while getting seasons'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}

Leaguemanager.saveStandings = function(ranking) {
	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( "action", "leaguemanager_save_team_standings" );
	ajax.setVar( "ranking", ranking );
	ajax.onError = function() { alert('Ajax error on saving standings'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}

Leaguemanager.saveAddPoints = function(team_id) {
	Leaguemanager.isLoading('loading_' + team_id);
	var points = document.getElementById('add_points_' + team_id).value;

	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( "action", "leaguemanager_save_add_points" );
	ajax.setVar( "team_id", team_id );
	ajax.setVar( "points", points );
	ajax.onError = function() { alert('Ajax error on saving standings'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}

Leaguemanager.isLoading = function(id) {
	document.getElementById(id).style.display = 'inline';
	document.getElementById(id).innerHTML="<img src='"+LeagueManagerAjaxL10n.pluginUrl+"/images/loading.gif' />";
}

Leaguemanager.doneLoading = function(id) {
	document.getElementById(id).style.display = 'none';
}

Leaguemanager.setMatchDayPopUp = function(match_day, i, max_matches, mode) {
	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( "action", "leaguemanager_set_match_day_popup" );
	ajax.setVar( "match_day", match_day );
	ajax.setVar( "i", i);
	ajax.setVar( "mode", mode);
	ajax.setVar( "max_matches", max_matches );
	ajax.onError = function() { alert('Ajax error on saving standings'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}

Leaguemanager.setMatchDate = function(match_date, i, max_matches, mode) {
	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( "action", "leaguemanager_set_match_date" );
	ajax.setVar( "match_date", match_date );
	ajax.setVar( "i", i);
	ajax.setVar( "mode", mode);
	ajax.setVar( "max_matches", max_matches );
	ajax.onError = function() { alert('Ajax error on saving standings'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}

Leaguemanager.insertHomeStadium = function(team_id, i) {
	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( "action", "leaguemanager_insert_home_stadium" );
	ajax.setVar( "team_id", team_id );
	ajax.setVar( "i", i);
	ajax.onError = function() { alert('Ajax error on saving standings'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}