<?php
/*
Plugin Name: Pivotal Tracker
Description: Pivotal Tracker API Plugin
Version: 1.01.420
Author: Sean Thompson
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/*
 *	Keeping Fools Out 
 */
if(!defined('ABSPATH')) exit;

/*
 *	Loading Required Files
 */ 

class PivotalApiIncludes{ 
	public function __construct(){
		include( plugin_dir_path( __File__ ) . 'pivotal_api_require.php');
		include( plugin_dir_path( __File__ ) . 'API/pivotal_tracker_api.php');
		include( plugin_dir_path( __File__ ) . 'Templates/portal_form_landing.php');
		include( plugin_dir_path( __File__ ) . 'Templates/portal_search.php');
		include( plugin_dir_path( __File__ ) . 'Templates/portal_story_export.php');
		include( plugin_dir_path( __File__ ) . 'Email/portal_email_update.php');
	}
}

new PivotalApiIncludes();


 
 