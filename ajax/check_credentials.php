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

$username = optional_param('username', null, PARAM_TEXT);
$password = optional_param('password', null, PARAM_TEXT);

$result = new stdClass();
if (empty($plugintype)) {
    $result->message = 'Missing plugintype';
    $result->username = '';
    $result->password = '';
    $json = json_encode($result);
    header('Content-Type: application/json');
    echo $json;
    exit(1);
}
$vars = array();
$client = new RefinedServices\ServiceClient('account');

if( $username && $password ){
	$vars['username'] = $username;
	$vars['password'] = $password;
	$vars['plugintype'] = $plugintype;
	$vars['domain'] = $CFG->wwwroot;
	$data = $client->checkExistingAccount($vars);
	if( !isset( $data->username ) ){// if no username returned, that means there acceptable
		$data->username = $username;
		$data->password = $password;
		refinedservices_update_config($data, $plugintype);
	}
}else{
	$result->message = 'Account not found';
    $result->username = '';
    $result->password = '';
    $json = json_encode($result);
	header('Content-Type: application/json');
	echo $json;
    exit();
}

$json = json_encode($data);
header('Content-Type: application/json');
echo $json;