<?php

function xmldb_local_refinedservices_install() {
    global $CFG, $DB;
    require_once($CFG->libdir.'/db/upgradelib.php'); // Core Upgrade-related functions
    $dbman = $DB->get_manager();
    $status = true;
    $table = new xmldb_table('log_admin_actions');
    if ($dbman->table_exists($table)) {
        $record_exists = $DB->record_exists('log_admin_actions', array('name' => 'refinedservice'));

        if (!$record_exists) {
            $record = new stdClass();
            $record->name = 'refinedservice';
            $record->label = 'Refined Services';
            $record->info1_label = 'Request Url';
            $record->info2_label = 'Request Body';
            $record->info3_label = 'Response Status';
            $record->info4_label = 'Response Body';
            $record->info5_label = 'Response Error';
            $record->adminview = 1;
            $record->active = 1;
            $DB->insert_record('log_admin_actions', $record);
        }
        
    }
    
    
    // add updateinrs field to user table
    $table = new xmldb_table('user');
    $field = new xmldb_field('updatedinrs');
    $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, 0, '');
    if (!$dbman->field_exists($table, $field)) $dbman->add_field($table, $field);
    
    
    return $status;
}
?>