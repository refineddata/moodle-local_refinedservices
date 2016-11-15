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
$vars['name'] = $SITE->shortname;
$vars['domain'] = $CFG->wwwroot;
$vars['plugintype'] = $plugintype;
$vars['email'] = $CFG->supportemail;
$vars['hostname'] = gethostname();
$vars['ip'] = $_SERVER['SERVER_ADDR'];

$admin = get_admin();
$registrant_info = array(
    'Main Admin Name' => "$admin->firstname $admin->lastname",
    'Main Admin Email' => $admin->email,
    'Main Admin Company' => $admin->institution,
    'Main Admin Address' => "$admin->address, $admin->city, $admin->state, $admin->country $admin->postcode"
);
if( isset( $USER ) ){
    $registrant_info['Registrant Name'] = "$USER->firstname $USER->lastname";
    $registrant_info['Registrant Email'] = "$USER->email";
    $registrant_info['Registrant Company'] = "$USER->institution";
    $registrant_info['Registrant Address'] = "$USER->address, $USER->city, $USER->state, $USER->country $USER->postcode";
}
$vars['registrant_info'] = json_encode( $registrant_info );

// var_dump($vars);die;

$client = new RefinedServices\ServiceClient('account');
if ( $plugintype == 'Connect' ) {
    $required = array( 'connect_protocol', 'connect_server', 'connect_account', 'connect_admin_login', 'connect_admin_password', 'connect_prefix' );
    
//     foreach ( $required as $key ) {
//         if ( empty( $CFG->$key ) ) {
//             $message = 'Connect parameters are not given. <br />';
//             $data = array('message' => $message, 'username' => '', 'password' => '', 'expired' => '');
//             $json = json_encode($data);
//             header('Content-Type: application/json');
//             echo $json;
//             exit;
//         }
//     }
    
    foreach ( $required as $key ) {
    	if ( !empty( $CFG->$key ) ) {
	        $vars[$key] = $CFG->$key;
    	}
    }
}
$data = $client->createAccount($vars);
refinedservices_update_config($data, $plugintype);
$json = json_encode($data);
header('Content-Type: application/json');
echo $json;
