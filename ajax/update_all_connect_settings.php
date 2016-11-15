<?php
/**
 * @package    local
 * @subpackage refinedservices
 * @copyright  2014 Refined Data Solutions Inc.
 * @author     Elvis Li
 */

require_once('../../../config.php');
require_once('../ServiceClient.php');
require_once('../lib.php');
require_login();

$configvars = array(
    'connect_update', 'connect_updatedts', 'connect_instant_regrade', 
    'connect_icondisplay', 'connect_displayoncourse', 'connect_maxviews', 'connect_adobe_addin', 
    'connect_template', 'connect_autofolder', 'local_reminders', 
    'connect_emailaslogin', 'connect_unameaslogin', 
);

///////////////
// UPDATE IN RS
if(  isset( $CFG->connect_service_username ) && $CFG->connect_service_username && isset( $CFG->connect_service_password ) && $CFG->connect_service_password ){
	
    $params = array();
	foreach ($configvars as $var) {
        if( isset( $CFG->$var ) && $CFG->$var ){
    		$params[] = array('name' => $var, 'value' => $CFG->$var);
        }
	}
	require_once($CFG->dirroot . '/mod/connectmeeting/connectlib.php');
	$connect = _connect_get_instance();
	$connect->connect_call('setconfig', $params);
}

$data = array( 'message' => 'success' );
$json = json_encode($data);
header('Content-Type: application/json');
echo $json;
