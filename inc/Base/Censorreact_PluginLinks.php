<?php 

//Package Name
/**
 *  
 * @package censorREACT
 * 
 */

 namespace Censorreact\Base; 

//Activate Plugin 
class Censorreact_PluginLinks  
{ 
    public function register() {
        //Add the button to get to settings next to the activate/Deactivate
        add_filter("plugin_action_links_" . CENSORREACT_PLUGIN, array($this, 'censorreact_settings_link'));
    }
    
    public function censorreact_settings_link($links) {
        //Create new settings link (Copy this code and change it to create a new link)
        $settings_link = '<a href="admin.php?page=censorreact">Settings</a>';
        array_push($links, $settings_link); 
        
        return $links;
    } 
}