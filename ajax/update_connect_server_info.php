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

$vars = array();
$vars['connect_protocol'] = optional_param('protocol', null, PARAM_TEXT);
$vars['connect_server'] = optional_param('server', null, PARAM_TEXT);
$vars['connect_account'] = optional_param('account', null, PARAM_TEXT);
$vars['connect_admin_login'] = optional_param('admin_login', null, PARAM_TEXT);
$vars['connect_admin_password'] = optional_param('admin_password', null, PARAM_TEXT);
$vars['connect_prefix'] = optional_param('prefix', null, PARAM_TEXT);
$vars['connect_emailaslogin'] = optional_param('connect_emailaslogin', 0, PARAM_INT);
$vars['connect_unameaslogin'] = optional_param('connect_unameaslogin', null, PARAM_INT);

///////////////////
// UPDATE IN MOODLE
foreach ($vars as $key => $value) {
	$config = $DB->get_record('config', array('name' => $key));
	if (empty($config)) {
		$obj = new stdClass();
		$obj->name = $key;
		$obj->value = $value;
		$DB->insert_record('config', $obj);
	} else {
		if ($CFG->$key != $value) {
			$config->value = $value;
			$DB->update_record('config', $config);
		}
	}
}
purge_all_caches();

///////////////
// UPDATE IN RS
if(  isset( $CFG->connect_service_username ) && $CFG->connect_service_username && isset( $CFG->connect_service_password ) && $CFG->connect_service_password ){
	$params = array();
	foreach ($vars as $name => $value) {
		$params[] = array('name' => $name, 'value' => $value);
	}
	require_once($CFG->dirroot . '/mod/connectmeeting/connectlib.php');
	$connect = _connect_get_instance();
	$connect->connect_call('setconfig', $params);
}

// $data = array( 'message' => 'Success' );

// $json = json_encode($data);
// header('Content-Type: application/json');
// echo $json;

$result = $connect->connect_call('check-connect-admin-user');

$data = array( 'message' => $result->message, 'auth' => $result->auth );
$json = json_encode($data);
header('Content-Type: application/json');
echo $json;