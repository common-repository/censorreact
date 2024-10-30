<?php 

//Package Name
/**
 *  
 * @package censorREACT
 * 
 */

/* 
Plugin Name: censorREACT
Plugin URI: https://intygrate.com/censorreact
Description: censorREACT protects your site, from inappropriate content, when filtering/verifying text and moderating images. censorREACT offers both pre-defined and customised settings to ensure protection for your business.
Version: 1.0.2
Author: Intygrate
Author URI: https://intygrate.com
License: GPLv2 or later
Text Domain: censorREACT
 
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
 
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

2020 © Intygrate. All Rights Reserved
*/

//If someone tries to access the files outside of wordpress kill the process
defined('ABSPATH') or die('You don\'t have permission to access this file');

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

define ('CENSORREACT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define ('CENSORREACT_PLUGIN_URL', plugin_dir_url(__FILE__)); 
define ('CENSORREACT_PLUGIN', plugin_basename(__FILE__)); 

//Get the classes from other files
use Censorreact\Base\Censorreact_Activate;
use Censorreact\Base\Censorreact_Deactivate;
use Censorreact\Base\Censorreact_Uninstall;

//Check that the include class exists
if (class_exists('Censorreact\\Censorreact_Init')) { 
    Censorreact\Censorreact_Init::censorreact_register_services();
}

//Use the function to use the class from the other files
function activate_censorreact() { 
    Censorreact_Activate::censorreact_activate();
}

//Require the activation file using the activation hook
register_activation_hook(__FILE__, 'activate_censorreact');

//Use the function to use the class from the other files
function deactivate_censorreact() {
    Censorreact_Deactivate::censorreact_deactivate();
}
 
//Require the deactivation file using the deactivation hook
register_deactivation_hook(__FILE__, 'deactivate_censorreact');

function uninstall_censorreact() {
    Censorreact_Uninstall::censorreact_uninstall();
}
 
//Require the deactivation file using the deactivation hook
register_uninstall_hook(__FILE__, 'uninstall_censorreact');

function censorreact_get_api_key() {
    //Access the wordpress database
    global $wpdb;
    //Get the table name
    $plugin_table_name = "censorreact_plugin";
    //Get the key from the database
    $db_key = $wpdb->get_results("SELECT `key` FROM `$plugin_table_name`");

    //If the key is set then get the key, otherwise set the variable to empty
    $api_key = '';
    if (isset($db_key[0]->key)) {
        $api_key = $db_key[0]->key;
    }

    return $api_key;
}

/**
 *  Start Error Processing  
 */

//Function to show errors to the user
function censorreact_error_notice() {

    //Access the wordpress database
    global $wpdb;
    //Get the table name
    $plugin_table_name = "censorreact_plugin"; 
    //Get the key from the database
    $get_key_valid = $wpdb->get_results("SELECT `key_is_valid`, '' FROM `$plugin_table_name` WHERE `id` = 1");

    $limit_reached = $wpdb->get_results("SELECT `limit_reached`, '' FROM `$plugin_table_name` WHERE `id` = 1");

    if(isset($_POST['intygrate-dismiss'])) {
        $_SESSION['intygrate-dismiss'] = 'dismiss';
    }
    
    if (censorreact_get_api_key() === '') {
        $_SESSION['alert-type'] = 'censorREACT-notice';
        $_SESSION['alert-message'] = 'Thank you for activating censorREACT! Register on the censorREACT settings page to get started.';
    } else if($get_key_valid[0]->key_is_valid === 'no' && censorreact_get_api_key() !== '') {
        //Set the error type and error message into the database
        $_SESSION['alert-type'] = 'error';
        $_SESSION['alert-message'] = 'Your key is invalid please get the latest on the settings page.'; 
    } else if ($limit_reached[0]->limit_reached === 'yes') {
        $_SESSION['alert-type'] = 'error';
        $_SESSION['alert-message'] ='Your last filtering failed due to reaching your monthly limit';
    }

    if(isset($_SESSION['intygrate-dismiss'])) {
        unset($_SESSION['alert-type']);
        unset($_SESSION['alert-message']);
    }

    if (isset($_SESSION['alert-message'])) {
        //If the error field is set the print out the error
        print("<div id='censorreact-myplugin-notice' class='censorreact-myplugin-notice " .  $_SESSION['alert-type'] . " notice'><img style='position: absolute;' class='censorreact-logo'  src='" . CENSORREACT_PLUGIN_URL . "assets/images/censorREACT-logo.png' alt='censorREACT logo'><p style='margin-left: 200px; padding: 20px;'> " . $_SESSION['alert-message'] . " </p><form id='censorreact-dismiss-form' method='POST'><input type='hidden' name='intygrate-dismiss' value'dismiss'><button type='submit' id='censorreact-intygrate-dismiss-btn'>x</button</form></div>");
    } 
}

/**
 * Start Filtering 
 */

function censorreact_api_text($content, $api_key) {

    global $wpdb;
    $plugin_table_name = "censorreact_plugin";
    $textAPI = 'https://api.censorreact.intygrate.com/v1/text';

    //Set the arguments to send through to the API
    $args = array(
        'method' => 'POST', 
        'body' => json_encode(array(
            'text' => $content, 
            'profile' => 'default' 
        )),
        'headers' => array(
            'Content-Type' => 'application/json',
            'x-api-key' => $api_key  
        )
    );   

    //Make the request for moderation with the API
    $response = wp_remote_request( $textAPI, $args );


    //Get the result from the API and json_decode it
    $decoded_body = json_decode($response['body'], true);

    error_log(print_r($decoded_body, true));
    
    if(isset($response['response']['code'])) {

        if ($response['response']['code'] === 429) {
            $wpdb->query("UPDATE `{$plugin_table_name}` SET `limit_reached` = 'yes' WHERE `id` = 1");
            return $content;
        } 
    } else {
        $wpdb->query("UPDATE `{$plugin_table_name}` SET `limit_reached` = 'no' WHERE `id` = 1");
    }

    //Check if the key is valid
    $invalid_key_message = $response['response']['message'];

    if ($invalid_key_message === 'Forbidden') {
        $_SESSION['alert-type'] = 'error';
        $_SESSION['alert-message'] ='YOUR KEY IS INVALID';
    } else {
        $wpdb->query("UPDATE `{$plugin_table_name}` SET `key_is_valid` = 'yes' WHERE `id` = 1");
    }

    //If the new data is empty then show the old data, if not then show the masked data
    if (!empty($decoded_body['data']['masked'])) {
        $content = $decoded_body['data']['masked'];
    }

    add_action('admin_notices', '_error_notice');
    
    return $content;
}

add_filter('wp_insert_post_data', 'censorreact_modify_post');

function censorreact_modify_post($content) {

    // skip processing of draft content
    if($content['post_title'] !== 'Auto Draft') {

        $api_key = censorreact_get_api_key();

        $content['post_title'] = censorreact_api_text($content['post_title'], $api_key);
        $content['post_content'] = censorreact_api_text($content['post_content'], $api_key);

        // if ($content['post_type'] === 'attachment') {
        // }
    }
    remove_filter('wp_insert_post_data', 'censorreact_modify_post');

    return $content;
} 


//Modify comment content
add_filter('preprocess_comment', 'censorreact_modify_comment', 10); 

//Moderate the Comment Content
function censorreact_modify_comment($content) {
    
    $api_key = censorreact_get_api_key();

    if(!is_user_logged_in()) {
        $content['comment_author'] = censorreact_api_text($content['comment_author'], $api_key);
    }

    
    $content['comment_content'] = censorreact_api_text($content['comment_content'], $api_key);

    remove_filter('preprocess_comment', 'censorreact_modify_comment');
    return $content;

}

add_filter('wp_handle_upload_prefilter', 'censorreact_images', 20, 2); 

function censorreact_images($file) {

    $imageAPI = 'https://api.censorreact.intygrate.com/v1/image';

    $get_file = file_get_contents($file['tmp_name']);
    $base64 = base64_encode($get_file);
    $base64 = 'data:' . $file['type'] . ';base64,' . $base64;
    $api_key = censorreact_get_api_key();

    $args = array(
        'method' => 'POST', 
        'body' => json_encode((object)array( 
            'ImageBytes' => $base64,
            'profile' => 'default'
        )),
        'headers' => array(
            'Content-Type' => 'application/json',
            'x-api-key' => $api_key  
        )
    );

    $response = wp_remote_request( $imageAPI, $args);

    $decoded_body = json_decode($response['body'], true);

    error_log(print_r($decoded_body, true));

    if (!empty($decoded_body['rejected']['blacklist'])) {
        $file['error'] = 'CensorREACT: ' . $decoded_body['reason'];

        // wp_delete_attachment( $attachmentid, true );
    }

    if(!$decoded_body) {
        $file['error'] = 'CensorREACT: ' . $response['body'];
    }

    
    remove_filter('wp_insert_attachment_data', 'censorreact_modify_images'); 

    return $file;
} 