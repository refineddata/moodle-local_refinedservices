<?php

function xmldb_local_refinedservices_upgrade( $oldversion ) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    $result = true;

    if ( $oldversion < 2014041500 ) { //New version in version.php
        $table = new xmldb_table( 'log_admin_actions' );
        if ( $dbman->table_exists( $table ) ) {
            $record = $DB->get_record( 'log_admin_actions', array( 'name' => 'refinedservice' ) );
            if ( $record ) {
                $record->info5_label = 'Response Error';
                $DB->update_record( 'log_admin_actions', $record );
            }
        }
    }
    
    if ($oldversion < 2014082103) {
    	$table = new xmldb_table('user');
    	$field = new xmldb_field('updatedinrs');
    	$field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, 0, '');
    	if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
    }

    if ( $oldversion < 2016053101 ) {
        $record = $DB->get_record( 'config_plugins', array( 'plugin' => 'mod_connect', 'name' => 'version' ) );
        if (!empty($record)) {
            $DB->delete_records('config_plugins', array( 'plugin' => 'mod_connect' ));
        }

        $names = array('connect', 'connect_entries', 'connect_grading', 'connect_recurring');
        foreach ( $names as $name ) {
            $table = new xmldb_table($name);
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }

        $record = $DB->get_record( 'modules', array( 'name' => 'connect' ) );
        if (!empty($record)) {
            $DB->delete_records('modules', array( 'name' => 'connect' ));
        }
    }
    return $result;
}

