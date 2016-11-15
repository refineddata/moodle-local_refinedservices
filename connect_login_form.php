<?php
require_once("../../config.php");

require_login();
$PAGE->set_url('/local/refinedservices/connect_login_form.php');
$context = context_course::instance(SITEID);
$PAGE->set_context($context);
$title   = get_string( 'connect_credentials_form_title', 'local_refinedservices' );
$PAGE->set_title($title);

require_once("$CFG->libdir/formslib.php");

class connect_login_form extends moodleform {
	//Add elements to form
	public function definition() {
		global $CFG;

		$mform = $this->_form; // Don't forget the underscore!
		
		$mform->addElement('header', 'connect_credentials_header', get_string( 'connect_credentials_form_header', 'local_refinedservices' ) );

		$mform->addElement('text', 'connect_username', get_string('username')); // Add elements to your form
		$mform->setType('connect_username', PARAM_NOTAGS);
		$mform->setDefault('connect_username', '');
		
		$mform->addElement('password', 'connect_password', get_string('password')); // Add elements to your form
		$mform->setType('connect_password', PARAM_NOTAGS);
		$mform->setDefault('connect_password', '');
		
		$mform->addElement('submit', 'submitbutton', get_string("submit"));
		
	}
	//Custom validation should be added here
	function validation($data, $files) {
	}
}

//Instantiate simplehtml_form
$mform = new connect_login_form();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
	//Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
	require_once( "$CFG->dirroot/mod/connectmeeting/connectlib.php" );
	
	$connect = _connect_get_instance();
	$params = array(
			'external_user_id' => $USER->id,
			'username'         => $fromform->connect_username,
			'password'         => $fromform->connect_password
	);
	$result = $connect->connect_call( 'setconnectusercredentials', array($params) );
	
	if( $result[0] == 'Success' ){
		// redirect to wherever we came from
		$message = get_string( 'connect_credentials_form_success', 'local_refinedservices' ); //TEMP - should use language string
		if (!empty($SESSION->fromurl)) {
			$link = $SESSION->fromurl;
			unset($SESSION->fromurl);
		} else {
			$link = $CFG->wwwroot .'/';
		}
		redirect( $link, $message, 6 );
	}else{
		// back to the form with an error message
		$message = get_string( 'connect_credentials_form_fail', 'local_refinedservices' );
		redirect( "$CFG->wwwroot/local/refinedservices/connect_login_form.php", $message, 6 );
	}
} else {
	if (!empty($_SERVER['HTTP_REFERER']) && empty($SESSION->fromurl)) {
		$SESSION->fromurl  = $_SERVER['HTTP_REFERER'];
	}
	
	echo $OUTPUT->header();
	$mform->display();
}

?>