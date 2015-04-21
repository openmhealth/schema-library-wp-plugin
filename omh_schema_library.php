<?php 
/**
 * @package OMH_Schema_Library
 * @version 0.1
 */
/*
Plugin Name: OMH Schema Library
Plugin URI: http://openmhealth.org/
Description: Pulls in the git repo of schemas and adds posts for any new ones it finds
Author: Jasper Speicher
Version: 0.1
Author URI: http://openmhealth.org/
*/

ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);

define( 'OMH_SCHEMA_LIBRARY_VERSION', '0.1' );

define( 'OMH_SCHEMA_LIBRARY__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'OMH_SCHEMA_LIBRARY__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OMH_SCHEMA_LIBRARY__PLUGIN_PAGE_NAME', 'omh-schema-library-plugin');

require_once( OMH_SCHEMA_LIBRARY__PLUGIN_DIR . "SchemaLibrary.php");
require_once( OMH_SCHEMA_LIBRARY__PLUGIN_DIR . "AdminOptions.php");

use OMHSchemaLibrary\SchemaLibrary;
use OMHSchemaLibrary\AdminOptions;

$adminOptions = new AdminOptions;

$schemaLibrary = new SchemaLibrary;
$schemaLibrary->init();

function view( $name = 'config', $data ){

    $file = OMH_SCHEMA_LIBRARY__PLUGIN_DIR . 'views/'. $name . '.php';
    include( $file );

}

function remove_qsvar($url, $varname) {

    return preg_replace('/([?&])'.$varname.'=[^&]+(&|$)/','',$url);

}

function load_resources() {

    wp_register_style( 'omh_schema_library.css', OMH_SCHEMA_LIBRARY__PLUGIN_URL . '_inc/omh_schema_library.css', array(), OMH_SCHEMA_LIBRARY_VERSION );
    wp_enqueue_style( 'omh_schema_library.css');

    wp_register_script( 'omh_schema_library.js', OMH_SCHEMA_LIBRARY__PLUGIN_URL . '_inc/omh_schema_library.js', array('jquery'), OMH_SCHEMA_LIBRARY_VERSION );
    wp_enqueue_script( 'omh_schema_library.js' );

}

function omh_schema_library_setup_menu(){

    add_menu_page( 'OMH Schema Library Plugin Page', 'Schema Library', 'manage_options', OMH_SCHEMA_LIBRARY__PLUGIN_PAGE_NAME, 'omh_schema_library_init' );

}

function get_config_options(){

    global $adminOptions;
    return $adminOptions->get();

}

function save_config_options(){

    global $adminOptions;
    $options = $_GET;
    return $adminOptions->save( $options );

}

function omh_schema_library_init(){

    global $schemaLibrary;

    $data = array(
        'plugin_full_url' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        'page_name' => OMH_SCHEMA_LIBRARY__PLUGIN_PAGE_NAME
    );

    if( array_key_exists( 'saveOptions', $_GET ) ) {
        save_config_options();
    }
    $options = get_config_options();

    if( array_key_exists( 'updateLibrary', $_GET ) ) {
        $data['update_output'] = $schemaLibrary->updateLibrary( $options );
    }

    $data['options'] = $options;

    view('config', $data);

}

load_resources();
add_action('admin_menu', 'omh_schema_library_setup_menu');

?>
