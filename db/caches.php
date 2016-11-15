<?php

$definitions = array(
		'connectcall' => array(
				'mode' => cache_store::MODE_APPLICATION,
				'ttl' => isset($CFG->local_refinedservices_connect_cachetime) && $CFG->local_refinedservices_connect_cachetime ? $CFG->local_refinedservices_connect_cachetime : 1800
		),
		
		'reportcall' => array(
				'mode' => cache_store::MODE_APPLICATION,
				'ttl' => isset($CFG->local_refinedservices_report_cachetime) && $CFG->local_refinedservices_report_cachetime ? $CFG->local_refinedservices_report_cachetime : 1800
		)
);
?>