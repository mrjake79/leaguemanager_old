jQuery(document).ready(function($) {
	/* jQuery UI accordion list */	
	jQuery( ".jquery-ui-accordion" ).accordion({
		header: "h3.header",
		collapsible: true,
		heightStyle: "content"
	});
	
	/*
	 * Make sure that jQuery UI Tab content containers have correct IDs based on tablist links
	 */
	var i = 0;
	// get all tablist a elements
	jQuery('.jquery-ui-tabs>.tablist a').each(function() {
		// get href attribute of current link and remove leading #
		var tab_id = jQuery(this).attr('href');
		tab_id = tab_id.substring(1, tab_id.length);
		// get corresponding tab container
		var tab = jQuery('.jquery-ui-tabs .tab-content').eq(i);
		// set ID of tab container
		tab.attr('id', tab_id);
		
		// increment item count
		i = i + 1;
	});
	
	/*
	 * Acivate Tabs
	 */
	jQuery('.jquery-ui-tabs').tabs({
		collapsible: true,
	});
	jQuery(".jquery-ui-tabs>.tablist").css("display", "block");
	jQuery(".jquery-ui-tabs .tab-header").css("display", "none");
});

var Leaguemanager = new Object();

Leaguemanager.setMatchBox = function( requestURL, curr_index, operation, element, league_id, match_limit, widget_number, season, group, home_only, date_format ) {
	var ajax = new sack(requestURL);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( "action", "leaguemanager_get_match_box" );
	ajax.setVar( "widget_number", widget_number );
	ajax.setVar( "current", curr_index );
	ajax.setVar( "season", season );
	ajax.setVar( "group", group );
	ajax.setVar( "operation", operation );
	ajax.setVar( "element", element );
	ajax.setVar( "league_id", league_id );
	ajax.setVar( "match_limit", match_limit );
	ajax.setVar( "home_only", home_only );
	ajax.setVar( "date_format", date_format );
	ajax.onError = function() { alert('Ajax error'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}