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
set_config('connect_update_all_activities_message', json_encode($status));

$batchnum = 30;
$i = 1;
$params = array();

$con = _connect_get_instance();

$connects = array();

$connectmeetings = $DB->get_records( 'connectmeeting' );

foreach ($connectmeetings as $connectmeeting){
    $connects[] = $connectmeeting;
}

$connectslides = $DB->get_records( 'connectslide' );

foreach ($connectslides as $connectslide){
    $connects[] = $connectslide;
}

$connectquizs = $DB->get_records( 'connectquiz' );

foreach ($connectquizs as $connectquiz){
    $connects[] = $connectquiz;
}



$totalconnects = count($connects);
$currentconnectcount = 0;

foreach( $connects as $connect ){
	$params[] = array(
			'external_connect_id' => $connect->id,
			'external_course_id'  => $connect->course,
			'url'                 => $connect->url,
			'type'                => $connect->type,
			'start_time'          => $connect->start
	);
    $i++;
    $currentconnectcount++;

    if( $i > $batchnum ){
        $status = new stdClass();
        $status->percentage = ($currentconnectcount/$totalconnects) * 100;
        $status->date = time();

        $status->currentconnectcount = $currentconnectcount;
        $status->totalconnects = $totalconnects;

        set_config('connect_update_all_activities_message', json_encode($status));

        $con->connect_call( 'add-activity-record', $params );
        $params = array();
        $i=1;
    }
}

if( $i > 1 ){ // do final batch
    $con->connect_call( 'add-activity-record', $params );
}

$status = new stdClass();
$status->percentage = 100;
$status->date = time();
set_config('connect_update_all_activities_message', json_encode($status));

$result['message'] = 'Success'; // make language string
$json = json_encode($result);
header('Content-Type: application/json');
echo $json;
exit();
