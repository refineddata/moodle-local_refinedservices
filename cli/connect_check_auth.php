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
	"Check moodle users, if there auth info in RS is correct, if not, mark them as created by RS and generate random password

	Options:
	-h, --help            Print out this help
	";

	echo $help;
	die;
}

raise_memory_limit(MEMORY_HUGE);

cli_heading('Check moodle users, if there auth info in RS is correct, if not, mark them as created by RS and generate random password');
$start = getCurrentMilliTime();

$connect = _connect_get_instance();
// $users = $DB->get_records( 'user', array( 'updatedinrs' => 0, 'deleted' => 0 ) );
$users = $DB->get_records_sql("SELECT * FROM {user} WHERE ( updatedinrs=0 OR updatedinrs=-1 ) AND deleted=0 AND aclogin <> '' AND ackey <> '' ORDER BY id ASC");

$batchnum = 30;
$i = 1;
$e=0;
$emailnum=5000;
$params1 = array();

foreach( $users as $user ){	
	cli_heading($user->id);
	$e++;
	$i++;
	$params1[] = $user->id;
	
	if( $i > $batchnum ){	
		$params = array(
				'external_user_ids' => $params1,
				'only_if_no_auth'   => 1
		);
		$result = $connect->connect_call( 'set-user-as-created-by-rs', $params );
		
		$i = 1;
		$params1 = array();
	}
	if( $e >= $emailnum ){
		sendrsemail( $user->id );
		$e=0;	
	}
}

if( $i > 1 ){ // do final batch
	$params = array(
			'external_user_ids' => $params1,
			'only_if_no_auth'   => 1
	);
	$result = $connect->connect_call( 'set-user-as-created-by-rs', $params );
}

sendrsemail();
cli_heading('Done');
$time = getCurrentMilliTime() - $start;
cli_heading( 'Execution time: '. $time.' ms' );

exit(0);

function sendrsemail( $userid = false ){
	$emails = array(  );

	$from = 'support@refineddata.com';
	$subject = $userid ? "RS User Update Script" : 'RS User Update Script Complete';
	$message = $userid ? "RS User Update Script ON User ID $userid" : 'RS User Update Script COMPLETE!!!!!';
	
	$headers = 'From: '.$from.'' . "\r\n" .
			'Reply-To: '.$from.'' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
	
	foreach( $emails as $email ){
		$to      = $email;
		mail($to, $subject, $message, $headers);
	}	
}

function getCurrentMilliTime(){
	$microtime = microtime();
	$comps = explode(' ', $microtime);
	return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
}