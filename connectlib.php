<?php 
/**
 * @package    mod
 * @subpackage connect
 * @copyright  2014 Refined Data Solutions Inc.
 * @author     Elvis Li
 */

global $CFG;




// Used to ensure this file is set up prior to other libraries.
if (!defined('CONNECT_INTERNAL')) {
	define('CONNECT_INTERNAL', true);
}

// Number of seconds before resetting Connect CURL
define( 'CONNECT_INIT_BUFFER',           60 );

// Encryption key
define( 'CONNECT_ENCRYPTION_KEY',        'RefinedDataSolutions' );

// Encryption Algorithm
define( 'CONNECT_ENCRYPTION_ALGORITHM',  'cast-256' );

// Encryption Mode
define( 'CONNECT_ENCRYPTION_MODE',       'ecb' );

// Default Language for Invalid
define( 'CONNECT_DEFAULT_LANGUAGE',      'en' );

// Valid Language Codes
define( 'CONNECT_LANGUAGES',             'en,fr,de,ja,do,es' );

// Default Timezone for Invalid
define( 'CONNECT_DEFAULT_TIMEZONE',      '35' );

// Moodle-id Field Name
define( 'CONNECT_MOODLE_ID_FIELD_NAME',  'Moodle-ID' );

// Dummy Password when Unknown
define( 'CONNECT_DUMMY_PASSWORD',        'Null1234' );

// MoodleUsers Group
define( 'CONNECT_LMS_GROUP',             'MoodleUsers' );

// MoodleUsers Group Description
define( 'CONNECT_LMS_GROUP_DESC',        'Group for All LMS Users' );

// Group Description Prefix
define( 'CONNECT_COURSE_GROUP_PRE',      'LMS Course for ' );

define( 'CONNECT_LEGACYFILES_ACTIVE',    false);





require_once( $CFG->dirroot . '/local/refinedservices/ServiceClient.php' );
require_once( $CFG->dirroot . '/local/refinedservices/connectlib/access.php' );
require_once( $CFG->dirroot . '/local/refinedservices/connectlib/ftlib.php' );
require_once( $CFG->dirroot . '/local/refinedservices/connectlib/my.php' );
require_once( $CFG->dirroot . '/local/refinedservices/connectlib/sco.php' );
require_once( $CFG->dirroot . '/local/refinedservices/connectlib/user.php' );






function _connect_get_instance() {
    return new RefinedServices\ServiceClient( 'connect' );
}














// Delete all records from the connect cache
//
function reset_connect_cache() {
	global $CFG, $DB;

	$DB->delete_records( 'connect_cache', array() );
}


//Toggles between system timezone and the timezone passed in
//Typical use connect_tz_set( get_user_timezone() ) then date( ... ) then cpro_tz_set( 'reset' )
function connect_tz_set( $tz='reset' ){
	static $systz;
	static $nonstd = array( "(+ 0 WET) Western European Time" => "Europe/London",
			"(+ 1 CET) Central European Time" => "Europe/Paris",
			"(+ 2 EET) Eastern European Time" => "Europe/Athens",
			"(- 3.5 NST) Newfoundland Standard Time" => "America/St_Johns",
			"(- 4 AST) Atlantic Standard Time" => "Atlantic/Bermuda",
			"(- 5 EST) Eastern Standard Time" => "America/New_York",
			"(- 6 CST) Central Standard Time" => "America/Chicago",
			"(- 7 MST) Mountain Standard Time" => "America/Denver",
			"(- 8 PST) Pacific Standard Time" => "America/Los_Angeles",
			"(- 9 AKST) Alaska Standard Time" => "America/Anchorage",
			"(-11 HST) Hawaii Standard Time" => "Pacific/Honolulu" );

	if ( isset( $nonstd[ $tz ] ) ) $tz = $nonstd[ $tz ];

	if ( function_exists( 'date_default_timezone_get' ) ) {  // Only available for PHP V5.2 or greater
		if ( !isset( $systz ) ) {
			$systz = date_default_timezone_get();
		}
		if ( $tz == 'reset' || abs( $tz ) > 12 ) {
			return date_default_timezone_set( $systz );
		}
		if ( is_float( $tz ) ) {
			if ( ! $tz = timezone_name_from_abbr( "", $tz*3600, 0 ) ) {
				$tz = usertimezone();
			}
		}
		return date_default_timezone_set( $tz );
	}

	if ( !isset( $systz ) ) {
		$systz = getenv( "TZ" );
	}
	if( $tz == 'reset' || abs( $tz ) > 12 ) {
		return putenv( "TZ=$systz" );
	}
	return putenv( "TZ=" . usertimezone() );
}


//encrypt string
//
function connect_encrypt( $data_input ){
	if (!function_exists('mcrypt_module_open')) {
		throw new Exception('Missing PHP Mcrypt library <a href="http://www.php.net/manual/en/mcrypt.installation.php">http://www.php.net/manual/en/mcrypt.installation.php</a>');
	}
	$key = CONNECT_ENCRYPTION_KEY;
	$td  = mcrypt_module_open( CONNECT_ENCRYPTION_ALGORITHM, '', CONNECT_ENCRYPTION_MODE, '');
	$iv  = mcrypt_create_iv( mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND );
	mcrypt_generic_init( $td, $key, $iv );
	$encrypted_data = mcrypt_generic( $td, $data_input );
	mcrypt_generic_deinit( $td );
	mcrypt_module_close( $td );
	$encoded_64 = base64_encode( $encrypted_data );
	return $encoded_64;
}


//decrypt string
//
function connect_decrypt( $encoded_64 ){
	$decoded_64 = base64_decode($encoded_64);
	$key = CONNECT_ENCRYPTION_KEY;
	$td  = mcrypt_module_open( CONNECT_ENCRYPTION_ALGORITHM, '', CONNECT_ENCRYPTION_MODE, '');
	$iv  = mcrypt_create_iv( mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND );
	mcrypt_generic_init( $td, $key, $iv );
	$decrypted_data = rtrim( mdecrypt_generic( $td, $decoded_64 ) );
	mcrypt_generic_deinit( $td );
	mcrypt_module_close( $td );
	return $decrypted_data;
}

if (!function_exists('strptime')) {
	function strptime($str, $fmt)
	{
		$rtn = array(
				'tm_sec'    => 0,
				'tm_min'    => 0,
				'tm_hour'   => 0,
				'tm_mday'   => 0,
				'tm_mon'    => 0,
				'tm_year'   => 0,
				'tm_wday'   => 0,
				'tm_yday'   => 0,
				'unparsed'  => ''
		);

		$rtn['tm_year'] = intval(substr($str,  0, 4)) - 1900;
		$rtn['tm_mon']  = intval(substr($str,  5, 2)) - 1;
		$rtn['tm_mday'] = intval(substr($str,  8, 2));
		$rtn['tm_hour'] = intval(substr($str, 11, 2));
		$rtn['tm_min']  = intval(substr($str, 14, 2));
		$rtn['tm_sec']  = intval(substr($str, 17, 2));

		return $rtn;
	}
}

function connect_fatal( $str ) {
	$str = isset( $str ) ? $str : 'A fatal error has occurred.';

	die( "<div class='box errorbox errorcontent'><b>$str</b></div>" );
}

/**
 * Try on demand migration of file from old course files
 * @param string $filepath old file path
 * @param int $cmid migrated course module if
 * @param int $courseid
 * @param string $component
 * @param string $filearea new file area
 * @param int $itemid migrated file item id
 * @return mixed, false if not found, stored_file instance if migrated to new area
 */
function connectlib_try_file_migration($filepath, $cmid, $courseid, $component, $filearea, $itemid) {
	$fs = get_file_storage();

	if (stripos($filepath, '/backupdata/') === 0 or stripos($filepath, '/moddata/') === 0) {
		// do not steal protected files!
		return false;
	}

	if (!$context = get_context_instance(CONTEXT_MODULE, $cmid)) {
		return false;
	}
	if (!$coursecontext = context_course::instance($courseid)) {
		return false;
	}

	$fullpath = rtrim("/$coursecontext->id/course/legacy/0".$filepath, '/');
	do {
		if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
			if ($file = $fs->get_file_by_hash(sha1("$fullpath/.")) and $file->is_directory()) {
				if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.htm"))) {
					break;
				}
				if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.html"))) {
					break;
				}
				if ($file = $fs->get_file_by_hash(sha1("$fullpath/Default.htm"))) {
					break;
				}
			}
			return false;
		}
	} while (false);

	// copy and keep the same path, name, etc.
	$file_record = array('contextid'=>$context->id, 'component'=>$component, 'filearea'=>$filearea, 'itemid'=>$itemid);
	try {
		return $fs->create_file_from_storedfile($file_record, $file);
	} catch (Exception $e) {
		// file may exist - highly unlikely, we do not want upgrades to stop here
		return false;
	}
}

