<?php

function aventurien_solo_create_tables() {
   	global $wpdb;

    $db_table_name = $wpdb->prefix . 'aventurien_solo_states';
	// create the ECPT metabox database table
	if($wpdb->get_var("show tables like '$db_table_name'") != $db_table_name) 
	{
		$sql = "CREATE TABLE " . $db_table_name . " (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `module` tinytext NOT NULL,
        `user` tinytext NOT NULL,
        `pid` smallint NOT NULL,
        `vars` text NOT NULL DEFAULT '[]',
		UNIQUE KEY id (id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

function aventurien_solo_db_get_id($module, $user) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'aventurien_solo_states';

    $id = $wpdb->get_var("SELECT id FROM $db_table_name WHERE module='$module' AND user='$user'");
    
    return $id;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// States
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function aventurien_solo_db_get_pid($module, $user) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'aventurien_solo_states';
    
    $pid = $wpdb->get_var("SELECT pid FROM $db_table_name WHERE module='$module' AND user='$user'");

    if (is_null($pid))
        return 1;

    return $pid;
}

function aventurien_solo_db_set_pid($module, $user, $pid) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'aventurien_solo_states';

    $wpdb->query('START TRANSACTION');

    $id = aventurien_solo_db_get_id($module, $user);

    if (!isset($id) || !$id)
    {
        $wpdb->insert($db_table_name, array('module' => $module, 'user' => $user, 'pid' => $pid));
    }
    else
    {
        $wpdb->update($db_table_name, array('pid' => $pid), array('id' => $id));
    }

    $wpdb->query('COMMIT');
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Variables
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function aventurien_solo_db_get_vars($module, $user) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'aventurien_solo_states';

    $json = $wpdb->get_var("SELECT vars FROM $db_table_name WHERE module='$module' AND user='$user'");
    
    return is_null($json) ? array() : json_decode($json, true);
}

function aventurien_solo_db_set_vars($module, $user, $vars) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'aventurien_solo_states';

    $wpdb->query('START TRANSACTION');

    $id = aventurien_solo_db_get_id($module, $user);
    $json = json_encode($vars);
    
    if ($id)
    {
        $wpdb->update($db_table_name, array('vars' => $json), array('id' => $id));
    }

    $wpdb->query('COMMIT');

}

?>