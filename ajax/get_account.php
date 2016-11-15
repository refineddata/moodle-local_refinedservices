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

$plugintype = optional_param('plugintype', null, PARAM_TEXT);
$result = array();
if (empty($plugintype)) {
    $result['message'] = get_string('failed', 'local_refinedservices');
    $result['username'] = '';
    $result['password'] = '';
    $json = json_encode($result);
    header('Content-Type: application/json');
    echo $json;
    exit();
}
$vars = array();
$client = new RefinedServices\ServiceClient('account');
if( $plugintype == 'Connect' && isset( $CFG->connect_service_username ) && $CFG->connect_service_username && isset( $CFG->connect_service_password ) && $CFG->connect_service_password ){
	$vars['username'] = $CFG->connect_service_username;
	$vars['password'] = $CFG->connect_service_password;
	$vars['plugintype'] = $plugintype;
// 	$vars['domain'] = $CFG->wwwroot;
	$data = $client->checkExistingAccount($vars);
	if( !empty( $data->username ) && !empty( $data->password ) ){
		refinedservices_update_config($data, $plugintype, true);
	}
}else{
	$vars['name'] = $SITE->shortname;
	$vars['domain'] = $CFG->wwwroot;
	$vars['plugintype'] = $plugintype;
	$data = $client->getAccount($vars);
}
$json = json_encode($data);
header('Content-Type: application/json');
echo $json;