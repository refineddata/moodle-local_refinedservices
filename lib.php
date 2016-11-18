<?php
/**
 * @package    local
 * @subpackage refinedservices
 * @copyright  2014 Refined Data Solutions Inc.
 * @author     Elvis Li
 */


function local_refinedservices_extend_navigation( global_navigation $nav ) {
	global $PAGE, $CFG;

	if ( ! $PAGE->requires->is_head_done() ) {
		$PAGE->requires->jquery();
		$PAGE->requires->jquery_plugin( 'refined-ui-css', 'local_refinedservices' );
		$PAGE->requires->js_init_code( 'window.wwwroot = "' . $CFG->wwwroot . '";' );

		// JavaScripts called in AMD using Require JS
		$PAGE->requires->js_call_amd('local_refinedservices/refinedtraining', 'init');

		if (file_exists($CFG->dirroot . '/local/core/version.php')){
			$PAGE->requires->js_call_amd('local_core/core', 'init');
		}

		$PAGE->requires->css( '/local/refinedservices/css/refinedservices.css' );
	}

	if ( file_exists( "$CFG->dirroot/local/core/lib.php" ) ) {
		require_once( "$CFG->dirroot/local/core/lib.php" );
		$issitesuperadmin = is_sitesuperadmin();
	} else {
		$issitesuperadmin = is_siteadmin();
	}
	$pageurlpath         = $PAGE->has_set_url() ? $PAGE->url->get_path() : '';
	$isuserprofile       = ( strpos( $pageurlpath, 'user/profile.php' ) !== false );
	$isusercourseprofile = ( strpos( $pageurlpath, 'user/view.php' ) !== false );

	// This tool is for site admin only and available from user profile pages
	if ( $issitesuperadmin and ( $isuserprofile or $isusercourseprofile ) ) {
		if ( $settingsnav = $PAGE->__get( 'settingsnav' ) ) {

			// Add the link to the bulk course completion page
			$url = new moodle_url( '/local/refinedservices/change_to_created_by_rs.php' );
			$nav->add( get_string( 'changed_by_rs', 'local_refinedservices' ), $url );
		}
	}
}

function refinedservices_update_config( $data, $plugintype, $updateifempty = false ) {
	global $CFG, $DB;
	if ( $data->password == '' && ! $updateifempty ) {
		return false;
	}
	$configs = array();
	if ( $plugintype == 'Connect' ) {
		$configs['connect_service_username'] = $data->username ? $data->username : '';
		$configs['connect_service_password'] = $data->password ? $data->password : '';
	} else if ( $plugintype == 'Reports' ) {
		$configs['report_service_username'] = $data->username;
		$configs['report_service_password'] = $data->password;
	}
	foreach ( $configs as $key => $value ) {
		$config = $DB->get_record( 'config', array( 'name' => $key ) );
		if ( empty( $config ) ) {
			$obj        = new stdClass();
			$obj->name  = $key;
			$obj->value = $value;
			$DB->insert_record( 'config', $obj );
		} else {
			if ( $CFG->$key != $value ) {
				$config->value = $value;
				$DB->update_record( 'config', $config );
			}
		}
	}
	purge_all_caches();

	return true;
}
