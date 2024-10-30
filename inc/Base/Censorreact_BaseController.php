<?php 

//Package Name
/**
 *  
 * @package censorREACT
 * 
 */

 namespace Censorreact\Base; 

 class Censorreact_BaseController 
 {
     public $plugin_path;

     //Set the plugin path so that it can be used with navigation around the files
     public function __construct() {
         $this->plugin_path = plugin_dir_path(dirname(__FILE__, 2));
     }
 }