<?php
if ( file_exists( $CFG->dirroot . '/local/core/lib.php' ) ) {
	require_once( $CFG->dirroot . '/local/core/lib.php' );
	$access_rs_settings = is_sitesuperadmin();
} else {
	$access_rs_settings = is_siteadmin();
}

$connect_exists = file_exists( "$CFG->dirroot/local/connect/lib.php" ) ? 1 : 0;
$report_exists  = 0;//file_exists( "$CFG->dirroot/local/rtreporting/lib.php" ) ? 1 : 0;


if ( $access_rs_settings ) {
	// do check, if is a dev server, force RS host to be dev RS
	$devhosts = array(
		'Gluster-Node-1-Dev',
		'qa-local',
		'dev-b.refineddata.com',
		'dev-c.refineddata.com'
	);
	$host     = gethostname();
	$devrs    = 'http://services.dev.refineddata.com';

	if ( preg_match( '/settings.php/', $_SERVER['PHP_SELF'] ) ) {
		if ( in_array( $host, $devhosts ) && $CFG->refinedservices_host != $devrs ) {
			$config = $DB->get_record( 'config', array( 'name' => 'refinedservices_host' ) );
			if ( empty( $config ) ) {
				$obj        = new stdClass();
				$obj->name  = 'refinedservices_host';
				$obj->value = $devrs;
				$DB->insert_record( 'config', $obj );
			} else {
				$config->value = $devrs;
				$DB->update_record( 'config', $config );
			}
			purge_all_caches();
			if ( ! $PAGE->requires->is_head_done() ) {
				redirect( "$CFG->wwwroot/admin/settings.php?section=local_refinedservices", '', 0 );
			} else {
				echo \bootstrap_renderer::early_redirect_message( "$CFG->wwwroot/admin/settings.php?section=local_refinedservices", '', 0 );
				exit;
			}
		}
	}

	/**
	 * @package    local
	 * @subpackage refinedservices
	 * @copyright  2014 Refined Data Solutions Inc.
	 * @author     Elvis Li
	 */
	defined( 'MOODLE_INTERNAL' ) || die;

	$connect_service_account_comment = '<div class="label label-success" id="connect_service_account_comment" 
	                                    data-plugintype="Connect"
	                                    >connect_service_account_comment</div>';

	$connect_service_account_button = '<button id="connect_service_account_button" 
	                                    data-plugintype="Connect">Create Connect Account</button>
										<button id="connect_service_credentials_button" 
	                                    data-plugintype="Connect">Update Connect Credentials</button>';


	$connect_service_update_buttons = '
    <div id="connect_service_update_users"><button>Sync Users with Adobe Connect</button> <span id="update_users_message"></span>
        <div id="progress-wrap-users" class="rsprogress-wrap rsprogress">
            <div id="progress-bar-users" class="rsprogress-bar rsprogress"></div>
        </div>
    </div>
	<div id="connect_service_update_courses"><button>Sync Courses with Adobe Connect</button>  <span id="update_courses_message"></span>
        <div id="progress-wrap-courses" class="rsprogress-wrap rsprogress">
            <div id="progress-bar-courses" class="rsprogress-bar rsprogress"></div>
        </div>
    </div>
	<div id="connect_service_update_activities"><button>Sync Connect Activities with Adobe Connect</button>  <span id="update_activities_message"></span>
        <div id="progress-wrap-activities" class="rsprogress-wrap rsprogress">
            <div id="progress-bar-activities" class="rsprogress-bar rsprogress"></div>
        </div>
    </div>';

	$connect_service_credentials_button  = '<button id="connect_service_credentials">Update Adobe Connect Settings</button>';
	$connect_service_credentials_comment = '<div class="label" id="connect_service_credentials_comment">connect_service_credentials_comment</div>';
	// settings
	if ( $hassiteconfig ) {

		$settings = new admin_settingpage( 'local_refinedservices', get_string( 'settings', 'local_refinedservices' ) );
		$ADMIN->add( 'localplugins', $settings );

		// Refined Services Host
		$settings->add( new admin_setting_configtext( 'refinedservices_host', get_string( 'refinedservices_host', 'local_refinedservices' ),
			get_string( 'configrefinedservices_host', 'local_refinedservices' ), 'http://services.refineddata.com', PARAM_RAW, 64 ) );

		if ( $connect_exists ) {

			// Connect Service Header
			$setting = new admin_setting_heading( 'connect_serivce_account_heading', get_string( 'connect_service_account', 'local_refinedservices' ),
				$connect_service_account_comment . $connect_service_account_button . $connect_service_update_buttons );
			$settings->add( $setting );

			// Connect Service Username
			$settings->add( new admin_setting_configtext( 'connect_service_username', get_string( 'connect_service_username', 'local_refinedservices' ),
				get_string( 'configconnect_service_username', 'local_refinedservices' ), '', PARAM_RAW, 64 ) );

			// Connect Service Password
			$settings->add( new admin_setting_configtext( 'connect_service_password', get_string( 'connect_service_password', 'local_refinedservices' ),
				get_string( 'configconnect_service_password', 'local_refinedservices' ), '', PARAM_RAW, 64 ) );


			// Adobe Info Here
			$setting = new admin_setting_heading( 'connect_serivce_credentials_heading', get_string( 'connect_service_credentials', 'local_refinedservices' ),
				$connect_service_credentials_comment . $connect_service_credentials_button );
			$settings->add( $setting );

			$setting = new admin_setting_configtext( 'connect_protocol', get_string( 'connect_protocol', 'local_connect' ),
				get_string( 'config_connect_protocol', 'local_connect' ), 'https://', PARAM_URL );
			$settings->add( $setting );

			$setting = new admin_setting_configtext( 'connect_server', get_string( 'connect_server', 'local_connect' ),
				get_string( 'config_connect_server', 'local_connect' ), '', PARAM_HOST );
			$settings->add( $setting );

			$setting = new admin_setting_configtext( 'connect_account', get_string( 'connect_account', 'local_connect' ),
				get_string( 'config_connect_account', 'local_connect' ), '', PARAM_INT );
			$settings->add( $setting );

			$setting = new admin_setting_configtext( 'connect_admin_login', get_string( 'connect_admin_login', 'local_connect' ),
				get_string( 'config_connect_admin_login', 'local_connect' ), '', PARAM_RAW_TRIMMED );
			$settings->add( $setting );

			$setting = new admin_setting_configtext( 'connect_admin_password', get_string( 'connect_admin_password', 'local_connect' ),
				get_string( 'config_connect_admin_password', 'local_connect' ), '', PARAM_RAW_TRIMMED );
			$settings->add( $setting );

			$setting = new admin_setting_configtext( 'connect_prefix', get_string( 'connect_prefix', 'local_connect' ),
				get_string( 'config_connect_prefix', 'local_connect' ), $CFG->dbname . '-', PARAM_ALPHANUMEXT );
			$settings->add( $setting );

			$settings->add( new admin_setting_configcheckbox( 'connect_emailaslogin', get_string( 'emailaslogin', 'filter_connect' ), get_string( 'emailaslogin_hint', 'filter_connect' ), '1' ) );
			$settings->add( new admin_setting_configcheckbox( 'connect_unameaslogin', get_string( 'unameaslogin', 'filter_connect' ), get_string( 'unameaslogin_hint', 'filter_connect' ), '0' ) );

		}

	}


	// dashboard related settings
	$temp = new admin_settingpage( 'local_refinedservicescachesettings', get_string( 'cachesettings', 'local_refinedservices' ) );

	$temp->add( new admin_setting_configtext( 'local_refinedservices_connect_cachetime', get_string( 'connect_cachetime', 'local_refinedservices' ),
		null, 1800, PARAM_INT ) );

	$temp->add( new admin_setting_configtext( 'local_refinedservices_report_cachetime', get_string( 'report_cachetime', 'local_refinedservices' ),
		null, 1800, PARAM_INT ) );

	$temp->add( new admin_setting_configcheckbox( 'refinedservices_disable_cache', get_string( 'refinedservices_disable_cache', 'local_refinedservices' ), '', 0 ) );

	$ADMIN->add( 'localplugins', $temp );
}
	
