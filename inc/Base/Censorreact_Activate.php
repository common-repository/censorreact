<?php 

//Package Name
/**
 *  
 * @package censorREACT 
 * 
 */

 namespace Censorreact\Base; 

//Activate Plugin  
class Censorreact_Activate 
{ 
    public static function censorreact_activate() {  
        //Place code in here to change what happens when the plugin is activated
        //Create the table for the registation
        global $wpdb;

        //Set the plugin table name
        $plugin_table_name = "censorreact_plugin";

        //Create the new table for the key if it does not already exist
        $create_tbl_sql = "CREATE TABLE IF NOT EXISTS `" . $plugin_table_name . "`( `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, `email` VARCHAR(150), `confirmation_sent` VARCHAR(10), `user_confirmed` VARCHAR(10), `key` VARCHAR(150), `key_is_valid` VARCHAR(10), `limit_reached` VARCHAR(10))";
        
        require_once(ABSPATH . "wp-admin/includes/upgrade.php");
        dbDelta($create_tbl_sql);

        $has_Id = $wpdb->get_results("SELECT `id` FROM `$plugin_table_name`");

        if(empty($has_Id)) {
            $update_tbl_sql = "INSERT INTO `" . $plugin_table_name . "`(`id`, `email`, `confirmation_sent`, `user_confirmed`, `key`, `key_is_valid`, `limit_reached`) VALUES ('1', '', 'no', 'no', '', 'no', 'no' )";

            dbDelta($update_tbl_sql);
        }
    }
}