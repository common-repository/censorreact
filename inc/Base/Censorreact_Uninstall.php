<?php

//Package Name
/**
 *  
 * @package censorREACT
 * 
 */

namespace Censorreact\Base;

//Uninstall Plugin  
//What happens on uninstall
class Censorreact_Uninstall 
{ 
    public static function censorreact_uninstall() {  
        //Clear the database of plugin data
        //Access the database via SQL
        global $wpdb; 
        $wpdb->query("DROP TABLE censorreact_plugin");
    } 
}