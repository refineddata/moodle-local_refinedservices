<?php
/**
 * @package    local
 * @subpackage refinedservices
 * @copyright  2014 Refined Data Solutions Inc.
 * @author     Elvis Li
 */

define('CLI_SCRIPT', true);
require_once(__DIR__.'/../../../config.php');
require_once( "$CFG->dirroot/mod/connectmeeting/connectlib.php" );

$status = new stdClass();
$status->percentage = 0;
$status->date = time();
set_config('connect_update_all_connect_groups_message', json_encode($status));

$params = array();

$courses = $DB->get_records( 'course' );
$connect = _connect_get_instance();

$batchnum = 30;
$i=1;

$totalgroups = count($courses);
$currentgroupcount = 0;

foreach( $courses as $course ){
	$param = connect_update_group( $course->shortname, $course->id, true );
	if( $param ){
		$params[] = $param;
	}
    $i++;
    $currentgroupcount++;
    
    if( $i > $batchnum ){
            
        $status = new stdClass();
        $status->percentage = ($currentgroupcount/$totalgroups) * 100;
        $status->date = time();
        set_config('connect_update_all_connect_groups_message', json_encode($status));   

        $connect->connect_call( 'updategroup', $params );
        $i = 1;
        $params = array();
    }
}

if( $i > 1 ){ // do final batch
    $connect->connect_call( 'updategroup', $params );
}

$status = new stdClass();
$status->percentage = 100;
$status->date = time();
set_config('connect_update_all_connect_groups_message', json_encode($status));

$result['message'] = 'Success'; // make language string
$json = json_encode($result);
header('Content-Type: application/json');
echo $json;
exit();
