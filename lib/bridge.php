<?php
/**
 * Bridge class for the WordPress plugin ProjectManager
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright Copyright 2008
*/

class LeagueManagerBridge extends LeagueManager
{
	/**
	 * ID of project to bridge
	 *
	 * @var int
	 */
	var $project_id;
	
	
	/**
	 * initialize bridge
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		return;
	}
	function LeagueManagerBridge()
	{
		$this->__construct();
	}
	
	
	/**
	 * set project ID
	 *
	 * @param int $project_id
	 * @return void
	 */
	function setProjectID( $project_id )
	{
		$this->project_id = intval($project_id);
	}

	
	/**
	 * get Team Roster
	 *
	 * @param array $roster array( 'id' => projectID, 'cat_id' => cat_id )
	 * @return array
	 */
	function getTeamRoster( $roster )
	{
		global $wpdb, $projectmanager;

		$cat_id = ( isset($roster['cat_id']) && $roster['cat_id'] != -1 ) ? $cat_id = $roster['cat_id'] : false;
		if ( !empty($roster['id']) && isset($projectmanager) ) {
			$projectmanager->init(intval($roster['id']));
			$projectmanager->setCatID($cat_id);

			/*
			$search = "`project_id` = ".intval($roster['id']);
			if ( $cat_id ) $search .= $projectmanager->getCategorySearchString();

			$datasets = $wpdb->get_results( "SELECT `id`, `name` FROM {$wpdb->projectmanager_dataset} WHERE $search" );
			$i = 0;
			foreach ( $datasets AS $dataset ) {
				$meta = $projectmanager->getDatasetMeta( $dataset->id );
				$meta_data = array();
				foreach ( $meta AS $data ) {
					$meta_data[sanitize_title($data->label)] = $data->value;
				}
				
				$datasets[$i] = (object) array_merge( (array) $dataset, (array) $meta_data );
				$i++;
			}
			*/
			$datasets = $projectmanager->getDatasets( array('limit' => false) );
			return $datasets;
		}

		return false;
	}
	
	
	/**
	 * get team profile
	 * 
	 * @param int $profile (Dataset ID containing team profile)
	 * @return object/boolean
	 */
	function getTeamProfile( $dataset_id )
	{
		global $projectmanager;
		
		if ( empty($dataset_id) || !isset($projectmanager) ) return false;
		
		$dataset = $projectmanager->getDataset($dataset_id);
		return $dataset;
	}
	
	/**
	 * get team roster
	 *
	 * @param mixed $selected
	 * @return HTML dropdown menu
	 */
	function getTeamRosterSelection( $roster, $selected, $id )
	{
		$out = "<select id='$id' name='$id' style='display: block; margin: 0.5em auto;'>";
		foreach ( $roster AS $team => $players ) {
			$out .= "<optgroup label='".$team."'>";
			foreach ( $players AS $id => $player ) {
				$player->name = stripslashes($player->name);
				$checked = ( $selected == $player->name ) ? ' selected="selected"' : '';
				$out .= "<option value='".$player->name."'".$selected.">".$player->name."</option>";
			}
			$out .= "</optgroup>";
		}
		$out .= "</select>";

		return $out;
	}
}
