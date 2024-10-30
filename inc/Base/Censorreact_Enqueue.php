<?php

//Package Name
/**
 *  
 * @package censorREACT
 * 
 */
namespace Censorreact\Base;
 
class Censorreact_Enqueue
{
    public function register() {
        //Use admin_enqueue_scripts for backend
        //Use wp_enqueue_scripts for frontend
        add_action('admin_enqueue_scripts', array($this, 'censorreact_enqueue'));
    }

    //Get external files
    function censorreact_enqueue() {
        //Enqueue your script
        //Use this for stylesheets 
        wp_enqueue_style('mypluginstyle', CENSORREACT_PLUGIN_URL . 'assets/css/styles.css'); 
        
        //Use this for JavaScript
        wp_enqueue_script('mypluginscript', CENSORREACT_PLUGIN_URL . 'assets/js/scripts.js');  
    }
}