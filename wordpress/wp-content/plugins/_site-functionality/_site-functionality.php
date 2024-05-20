<?php


/**
 * Plugin Name: _ Custom Site Functionality
 * Plugin URI: 
 * Description: Site specific functionality
 * Version: 1.0
 * Author: Wim Huurman 
 * Author URI: 
 * License: GPL2+

 */
define('SMG_FILE', __FILE__); // this file
define('SMG_DIR', dirname(SMG_FILE)); // our directory
define('SMG_INC', SMG_DIR . '/includes'); // our directory
define('SMG_URL', plugin_dir_url(SMG_FILE)); // our directory

include SMG_INC . '/smg_load_class.php';

$classes = array(

    'SMG_Api_Content_Blocks',
    'SMG_Api_Smg',
    'SMG_Api',
    'SMG_Force_Login_Bypass',
    /*
    'SMG_Brand',
    'SMG_Event',
    'SMG_Event_Save',
    'SMG_Event_Admin',
    
    'SMG_Historic_Event',
    'SMG_Image_Upload',
    'SMG_Neighborhood_Partner',

    'SMG_Preview_Link',
    'SMG_Settings',
    'SMG_Admin_Styles_Scripts',
    'SMG_Settings_Page',
    'SMG_Page',
*/
    //'SMG_Person',

    // 'SMG_TicketmasterAPI',

    //'SMG_Menu_Image',

);

$smg_basic = new SMG_Load_Class();

$smg_basic->set_prefix('SMG_');

$smg_basic->set_dirs(array(SMG_INC));

$smg_basic->set_load_classes($classes);


function debug_log($message)
{

    $backtrace = debug_backtrace();
    $message = $backtrace[0]['args'];
    if (is_array($message) || is_object($message)) {
        $message = json_encode($message);
    }
    $file = explode("/", $backtrace[0]['file']);
    error_log(end($file) .  " on line " . $backtrace[0]['line'] . ": " . $message);
}
