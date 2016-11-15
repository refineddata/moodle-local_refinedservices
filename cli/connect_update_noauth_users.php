<?php

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once( $CFG->dirroot.'/mod/connectmeeting/connectlib.php' );

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array('help' => false, 'batchsize' => false, 'numberofbatches' => false, 'reportonly' => false),
		array('h' => 'help', 'b' => 'batchsize', 'n' => 'numberofbatches', 'r' => 'reportonly'));

if ($unrecognized) {
	$unrecognized = implode("\n  ", $unrecognized);
	cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
	$help =
	"Update moodle users in Refined Services

	Options:
	-h, --help            Print out this help
	-b, --batchsize       Number of users to be updated in each call to RS
    -n, --numberofbatches Number of batch calls to do to RS
    -r, --reportonly      Only output auth report, do not do any actual changes";

	echo $help;
	die;
}

raise_memory_limit(MEMORY_HUGE);

cli_heading('Update moodle users in Refined Services that can not authenticate');
$start = getCurrentMilliTime();

$connect = _connect_get_instance();
// $users = $DB->get_records( 'user', array( 'updatedinrs' => 0, 'deleted' => 0 ) );
$users = $DB->get_records_sql("SELECT * FROM {user} WHERE deleted=0 AND updatedinrs=0 ORDER BY id DESC");

$batchnum = isset( $options['batchsize'] ) && is_numeric( $options['batchsize'] ) ? $options['batchsize'] : 30;
$numbatches = isset( $options['numberofbatches'] ) && is_numeric( $options['numberofbatches'] ) ? $options['numberofbatches'] : 1000;
$reportonly = isset( $options['reportonly'] ) && $options['reportonly'] ? 1 : 0;
$donebatches = 0;
$i = 1;
$byrsusers = array();

foreach( $users as $user ){		
	$i++;
	$byrsusers[] = $user->id;
	
	if( $i > $batchnum ){	
		$i = 1;
		$params = array(
				'external_user_ids' => $byrsusers,
				'only_if_no_auth'   => 1,
                'report_only'       => $reportonly
		);
		$result = $connect->connect_call( 'set-user-as-created-by-rs', $params );
        outputData( $byrsusers, $result );
		$byrsusers = array();
        $donebatches++;
        if( $donebatches >= $numbatches ) break;
	}
}

if( $i > 1 ){ // do final batch
	$params = array(
		'external_user_ids' => $byrsusers,
		'only_if_no_auth'   => 1,
        'report_only'       => $reportonly
	);
	$result = $connect->connect_call( 'set-user-as-created-by-rs', $params );
    outputData( $byrsusers, $result );
}

cli_heading('Done');
$time = getCurrentMilliTime() - $start;
cli_heading( 'Execution time: '. $time.' ms' );

exit(0);

function outputData( $byrsusers, $result ){
    global $DB;

    foreach( $byrsusers as $key => $value ){
        echo "$value - $result[$key]\n";

        $sql = "UPDATE {user} SET updatedinrs=1 WHERE id=?";
        $DB->execute($sql, array($value));
    }
}

function getCurrentMilliTime(){
	$microtime = microtime();
	$comps = explode(' ', $microtime);
	return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
}
