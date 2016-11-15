<?php
/**
 * @package    local
 * @subpackage refinedservices
 * @copyright  2014 Refined Data Solutions Inc.
 * @author     Elvis Li
 */

require_once('../../../config.php');
require_login();

$data = new stdClass();

$group = isset( $CFG->connect_update_all_connect_groups_message ) ? json_decode($CFG->connect_update_all_connect_groups_message):'';
$user = isset($CFG->connect_update_all_users_message) ? json_decode($CFG->connect_update_all_users_message):'';
$activity = isset($CFG->connect_update_all_activities_message) ? json_decode($CFG->connect_update_all_activities_message):'';
$nocheck = 0;


if( $group ){
    if( $group->percentage == 100 ){
        if( $group->date > ( 60*60*24 ) ){
            $data->group = "Success.  Completed at ".date('g:i:s a', $group->date);
        }else{
            $data->group = "Last update completed on ".date('F j, Y, g:i:s a', $group->date);
        }
        $data->grouppercentage = 100;
        $nocheck++;
    }else{
        if( $group->date < (time() - (60*15))){
            $data->group = "Sync stopped at ".number_format($group->percentage)."% Complete.  Last updated at ".date('g:i:s a', $group->date);
            $data->grouppercentage = $group->percentage;
            $nocheck++;
        }else{
            $data->group = number_format($group->percentage)."% Complete.  Last updated at ".date('g:i:s a', $group->date);
            $data->grouppercentage = $group->percentage;
        }
    }
}else{
    $nocheck++;
}

if( $user ){
    if( $user->percentage == 100 ){
        if( $user->date > ( 60*60*24 ) ){
            $data->user = "Success.  Completed at ".date('g:i:s a', $user->date);
        }else{
            $data->user = "Last update completed on ".date('F j, Y, g:i:s a', $user->date);
        }
        $data->userpercentage = 100;
        $nocheck++;
    }else{
        if( $user->date < (time() - (60*15))){
            $data->user = "Sync stopped at ".number_format($user->percentage)."% Complete. Last updated at ".date('g:i:s a', $user->date);
            $data->userpercentage = $user->percentage;
            $nocheck++;
        }else{
            $data->user = number_format($user->percentage)."% Complete. Last updated at ".date('g:i:s a', $user->date);
            $data->userpercentage = $user->percentage;
        }
    }
}else{
    $nocheck++;
}

if( $activity ){
    if( $activity->percentage == 100 ){
        if( $activity->date > ( 60*60*24 ) ){
            $data->activity = "Success.  Completed at ".date('g:i:s a', $activity->date);
        }else{
            $data->activity = "Last update completed on ".date('F j, Y, g:i:s a', $activity->date);
        }
        $data->activitypercentage = 100;
        $nocheck++;
    }else{
        if( $activity->date < (time() - (60*15))){
            $data->activity = number_format($activity->percentage)."% Complete.  Last updated at ".date('g:i:s a', $activity->date);
            $data->activitypercentage = $activity->percentage;
            $nocheck++;
        }else{
            $data->activity = number_format($activity->percentage)."% Complete.  Last updated at ".date('g:i:s a', $activity->date);
            $data->activitypercentage = $activity->percentage;
        }
    }
}else{
    $nocheck++;
}

if( $nocheck == 3 ){
    $data->nocheck = 1;
}

$json = json_encode($data);
header('Content-Type: application/json');
echo $json;
