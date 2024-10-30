<?php

//Package Name
/**
 *  
 * @package censorREACT
 * 
 */

namespace Censorreact\Pages;

use \Censorreact\Base\Censorreact_BaseController;
 
class Censorreact_Admin extends Censorreact_BaseController
{
    public function register() {
        add_action('admin_menu', array($this, 'censorreact_add_admin_pages'));
    }

    public function censorreact_add_admin_pages() {
        //Bring in the admin page and set all names
        add_menu_page('censorREACT', 'censorREACT', 'manage_options', 'censorreact', array($this, 'censorreact_admin_index'), 'dashicons-admin-generic', 110);  
    } 

    public function censorreact_admin_index() { 
        //Require Admin page
        require_once $this->plugin_path . 'templates/admin-page.php';
    }
} 