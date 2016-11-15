<?php

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once( $CFG->dirroot.'/mod/connectmeeting/connectlib.php' );

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array('help' => false),
		array('h' => 'help'));

if ($unrecognized) {
	$unrecognized = implode("\n  ", $unrecognized);
	cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
	$help =
	"Update moodle users in Refined Services

	Options:
	-h, --help            Print out this help
	";

	echo $help;
	die;
}

raise_memory_limit(MEMORY_HUGE);

cli_heading('Update moodle users in Refined Services');
$start = getCurrentMilliTime();

$connect = _connect_get_instance();
// $users = $DB->get_records( 'user', array( 'updatedinrs' => 0, 'deleted' => 0 ) );
$users = $DB->get_records_sql("SELECT * FROM {user} WHERE ( updatedinrs=0 OR updatedinrs=-1 ) AND deleted=0 ORDER BY id ASC");

$batchnum = 30;
$i = 1;
$e=0;
$emailnum=5000;
$params1 = array();
$params2 = array();

foreach( $users as $user ){	
	$e++;
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
	
	if( $i > $batchnum ){	
		doBatch( $connect, $params1, $params2 );
		$i = 1;
		$params1 = array();
		$params2 = array();
	}
	if( $e >= $emailnum ){
		sendrsemail( $user->id );
		$e=0;	
	}
}

if( $i > 1 ){ // do final batch
	doBatch( $connect, $params1, $params2 );
}

sendrsemail();
cli_heading('Done');
$time = getCurrentMilliTime() - $start;
cli_heading( 'Execution time: '. $time.' ms' );

exit(0);

function doBatch( $connect, $params1, $params2 ){
	global $DB;
	
	$result = $connect->connect_call( 'updateconnectuser', $params1 );
	$result2 = $connect->connect_call( 'setconnectusercredentials', $params2 );
	
	$batchfail = $result ? 0 : 1;
	
	foreach( $params1 as $key => $param ){
		if( $batchfail ){
			cli_heading( date( 'h:i:s' )." - User Id ".$param['external_user_id']." BATCH FAILED" );
			$sql = "UPDATE {user} SET updatedinrs=-1 WHERE id=?";
			$DB->execute($sql, array($param['external_user_id']));
		}elseif( $result[$key] != 'Success' ){
			$value = $param['updatedinrs'] == 0 ? -1 : -2;
			cli_heading( date( 'h:i:s' )." - User Id ".$param['external_user_id']." FAILED" );
			$sql = "UPDATE {user} SET updatedinrs=$value WHERE id=?";
			$DB->execute($sql, array($param['external_user_id']));
		}else{
			cli_heading( date( 'h:i:s' )." - User Id ".$param['external_user_id']." Success" );
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

function sendrsemail( $userid = false ){
    global $CFG;
	$emails = array( 'training@refineddata.com' );

	$from = 'support@refineddata.com';
	$subject = $userid ? "RS User Update Script ( $CFG->wwwroot )" : "RS User Update Script Complete ( $CFG->wwwroot )";
	$message = $userid ? "RS User Update Script ON User ID $userid" : 'RS User Update Script COMPLETE!!!!!';
	
	$headers = 'From: '.$from.'' . "\r\n" .
			'Reply-To: '.$from.'' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
	
	foreach( $emails as $email ){
		$to      = $email;
		mail($to, $subject, $message, $headers);
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

function getCurrentMilliTime(){
	$microtime = microtime();
	$comps = explode(' ', $microtime);
	return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
}
