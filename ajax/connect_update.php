<?php
/**
 * @package    local
 * @subpackage refinedservices
 * @copyright  2014 Refined Data Solutions Inc.
 * @author     Elvis Li
 */

require_once('../../../config.php');
require_once( "$CFG->dirroot/mod/connectmeeting/connectlib.php" );
require_login();

$action = optional_param('action', '', PARAM_ALPHA);

$message = '';

if( !is_siteadmin() ){
	$result['message'] = 'You must be an admin to do this'; // make language string
	$json = json_encode($result);
	header('Content-Type: application/json');
	echo $json;
	exit();
}

$script = '';
if( $action == 'activities' ){
    $info = isset( $CFG->connect_update_all_activities_message ) ? json_decode($CFG->connect_update_all_activities_message):'';
    $script = 'connect_update_all_activities.php';
}elseif( $action == 'coursegroups' ){
    $info = isset( $CFG->connect_update_all_connect_groups_message ) ? json_decode($CFG->connect_update_all_connect_groups_message):'';
    $script = 'connect_update_all_course_groups.php';
}elseif( $action == 'users'){
    $info = isset( $CFG->connect_update_all_users_message ) ? json_decode($CFG->connect_update_all_users_message):'';
    $script = 'connect_update_all_users.php';
}

if( $info && isset( $info->date ) && $info->date > (time() - (60*15)) ){
    $script = '';
}

if( $script ){
    $script = __DIR__.'/'.$script;
    shell_exec("php $script > /dev/null 2>&1 &");
    $message = 'Updating, Please wait...'.$script;
}

if( !$message ){
    $message = 'Please provide a valid action';
}

$result['message'] = $message; // make language string
$json = json_encode($result);
header('Content-Type: application/json');
echo $json;
exit();
