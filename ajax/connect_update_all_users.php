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
set_config('connect_update_all_users_message', json_encode($status));

$connect = _connect_get_instance();

$users = $DB->get_records_sql("SELECT * FROM {user} WHERE ( updatedinrs=0 OR updatedinrs=-1 ) AND deleted=0 ORDER BY id ASC");

$batchnum = 30; 
$i = 1;
$params1 = array();
$params2 = array();

$totalusers = count($users);
$currentusercount = 0;

foreach( $users as $user ){  
    $param1 = connect_update_user( $user, false, true );
    $param1['updatedinrs'] = $user->updatedinrs;
    $param1['auth'] = 'manuel';
    if( $param1 ){
        $params1[] = $param1;
    }   
    
    if( isset( $user->aclogin ) && $user->aclogin && isset( $user->ackey ) && $user->ackey ){   
        $params2[] = array(
                'external_user_id' => $user->id,
                'username'         => $user->aclogin,
                'password'         => connect_decrypt_userpass($user->ackey)
        );
    }   
    $i++;
    $currentusercount++;
    
    if( $i > $batchnum ){
        
        $status = new stdClass();
        $status->percentage = ($currentusercount/$totalusers) * 100;
        $status->date = time();
        set_config('connect_update_all_users_message', json_encode($status));   

        doBatch( $connect, $params1, $params2 );
        $i = 1;
        $params1 = array();
        $params2 = array();
    }
}

if( $i > 1 ){ // do final batch
    doBatch( $connect, $params1, $params2 );
}

$status = new stdClass();
$status->percentage = 100;
$status->date = time();
set_config('connect_update_all_users_message', json_encode($status)); 

$result['message'] = 'Success'; // make language string
$json = json_encode($result);
header('Content-Type: application/json');
echo $json;
exit();


function doBatch( $connect, $params1, $params2 ){
    global $DB;

    $result = $connect->connect_call( 'updateconnectuser', $params1 );
    $result2 = $connect->connect_call( 'setconnectusercredentials', $params2 );
    
    $batchfail = $result ? 0 : 1;
    
    foreach( $params1 as $key => $param ){
        if( $batchfail ){
            $sql = "UPDATE {user} SET updatedinrs=-1 WHERE id=?";
            $DB->execute($sql, array($param['external_user_id']));
        }elseif( $result[$key] != 'Success' ){
            $value = $param['updatedinrs'] == 0 ? -1 : -2;
            $sql = "UPDATE {user} SET updatedinrs=$value WHERE id=?";
            $DB->execute($sql, array($param['external_user_id']));
        }else{
            $sql = "UPDATE {user} SET updatedinrs=1 WHERE id=?";
            $DB->execute($sql, array($param['external_user_id']));
        }
    }

    $byrsusers = array();
    foreach( $params2 as $key => $param ){
        if( $result2[$key] == 'no-auth' ){
            $byrsusers[] = $param['external_user_id'];
        }
    }

    if( !empty( $byrsusers ) ){
        $params = array(
                'external_user_ids' => $byrsusers
        );
        $result = $connect->connect_call( 'set-user-as-created-by-rs', $params );
    }
}

function connect_decrypt_userpass($encoded_64) {
	if (!$encoded_64) return '';

	$c_key = 'RefinedDataSolutions';
	$c_algo = 'cast-256';
	$c_mode = 'ecb';

	$decoded_64 = base64_decode($encoded_64);
	$key = $c_key;
	$td = mcrypt_module_open($c_algo, '', $c_mode, '');
	$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	mcrypt_generic_init($td, $key, $iv);
	$decrypted_data = rtrim(mdecrypt_generic($td, $decoded_64));
	mcrypt_generic_deinit($td);
	mcrypt_module_close($td);
	return $decrypted_data;
}
