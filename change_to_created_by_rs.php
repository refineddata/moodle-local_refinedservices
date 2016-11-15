<?php
require_once("../../config.php");

require_login();
$PAGE->set_url('/local/refinedservices/change_to_created_by_rs.php');
$context = context_course::instance(SITEID);
$PAGE->set_context($context);
$title   = get_string( 'change_to_rs_formtitle', 'local_refinedservices' );
$PAGE->set_title($title);

require_once("$CFG->libdir/formslib.php");

class change_rs_form extends moodleform {
	//Add elements to form
	public function definition() {
		global $CFG;

		$mform = $this->_form; // Don't forget the underscore!
		
		$mform->addElement('header', 'connect_credentials_header', get_string( 'connect_credentials_form_header', 'local_refinedservices' ) );

		$mform->addElement('text', 'external_user_ids', get_string('externalids', 'local_refinedservices')); // Add elements to your form
		$mform->setType('external_user_ids', PARAM_NOTAGS);
		$mform->setDefault('external_user_ids', '');
		
		$mform->addElement('submit', 'submitbutton', get_string("submit"));
		
	}
	//Custom validation should be added here
	function validation($data, $files) {
	}
}

//Instantiate simplehtml_form
$mform = new change_rs_form();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
	//Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
	require_once( "$CFG->dirroot/local/connect/lib.php" );
	
	$externalusers = explode( ',', $fromform->external_user_ids );
	
	$connect = _connect_get_instance();
	$params = array(
			'external_user_ids' => $externalusers
	);
	$result = $connect->connect_call( 'set-user-as-created-by-rs', $params );
	
	// back to the form with an error message
	$message = get_string( 'change_rs_form_success', 'local_refinedservices' );
	redirect( "$CFG->wwwroot/local/refinedservices/change_to_created_by_rs.php", $message, 6 );
} else {	
	if( !is_siteadmin() ){
		redirect( "$CFG->wwwroot" );
	}
	
	echo $OUTPUT->header();
	$mform->display();
}

?>