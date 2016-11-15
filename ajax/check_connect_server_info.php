<?php
/**
 * @package    local
 * @subpackage refinedservices
 * @copyright  2014 Refined Data Solutions Inc.
 * @author     Elvis Li
 */

require_once('../../../config.php');
require_once('../ServiceClient.php');
require_once($CFG->dirroot . '/mod/connectmeeting/connectlib.php');
require_once('../lib.php');
require_login();

$connect = _connect_get_instance();
$result = $connect->connect_call('check-connect-admin-user');

if( $result ){
	$data = array( 'message' => $result->message, 'auth' => $result->auth );
}else{
	$data = array( 'message' => 'Please create Refined Services Connect Account', 'auth' => false );
}
$json = json_encode($data);
header('Content-Type: application/json');
echo $json;
