<?php
/**
 * Copyright 2016 Open mHealth
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OMHSchemaLibrary;


/**
 * @package OMH_Schema_Library
 * @version 0.1
 */
/*
Plugin Name: OMH Schema Library
Plugin URI: http://openmhealth.org/
Description: Pulls in the git repo of schemas and adds custom posts for every schema.
Author: Open mHealth
Version: 0.1
Author URI: http://openmhealth.org/
*/

define( 'OMH_SCHEMA_LIBRARY_VERSION', '0.1' );
define( 'USE_INCLUDED_ACF', false ); //can optionally store ACF code in this plugin directory

define( 'OMH_SCHEMA_LIBRARY__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'OMH_SCHEMA_LIBRARY__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OMH_SCHEMA_LIBRARY__PLUGIN_PAGE_NAME', 'omh-schema-library-plugin');

define( "SCHEMA_URL_PATH", "/schemas/"); //url where schema docs are found by web browser

define( "SHOW_CATEGORIES", false ); // in a later release this may become a configuration parameter

require_once( OMH_SCHEMA_LIBRARY__PLUGIN_DIR . "SchemaLibrary.php");
require_once( OMH_SCHEMA_LIBRARY__PLUGIN_DIR . "AdminOptions.php");

use OMHSchemaLibrary\SchemaLibrary;
use OMHSchemaLibrary\AdminOptions;

$adminOptions = new AdminOptions;

$schemaLibrary = new SchemaLibrary;
$schemaLibrary->init();

/**
* Show the page
* @param $name of page to show
* @param $data to show in the page
*/
function view( $name = 'config', $data ){

    $file = OMH_SCHEMA_LIBRARY__PLUGIN_DIR . 'views/'. $name . '.php';
    include( $file );

}

/**
* Remove the the variable from the url query params
* @param $url
* @param $varname to remove
* @return
*/
function remove_qsvar($url, $varname) {

    return preg_replace('/([?&])'.$varname.'=[^&]+(&|$)/','',$url);

}

/**
* Load the front-end js and css resources needed by the plugin
*/
function load_resources() {

    wp_register_style( 'omh_schema_library.css', OMH_SCHEMA_LIBRARY__PLUGIN_URL . '_inc/omh_schema_library.css', array(), OMH_SCHEMA_LIBRARY_VERSION );
    wp_enqueue_style( 'omh_schema_library.css');

    wp_register_script( 'omh_schema_library.js', OMH_SCHEMA_LIBRARY__PLUGIN_URL . '_inc/omh_schema_library.js', array('jquery'), OMH_SCHEMA_LIBRARY_VERSION );
    wp_enqueue_script( 'omh_schema_library.js' );

}

/**
* Add a page to the admin interface for managing the plugin
*/
function omh_schema_library_setup_menu(){

    add_menu_page( 'OMH Schema Library Plugin Page', 'Schema Library', 'manage_options', OMH_SCHEMA_LIBRARY__PLUGIN_PAGE_NAME, 'OMHSchemaLibrary\\omh_schema_library_config' );

}

/**
* Recall the options set by the user to control how the library is updated
* @return
*/
function get_config_options(){

    global $adminOptions;
    return $adminOptions->get();

}

/**
* Save the admin options to the database
*/
function save_config_options(){

    global $adminOptions;
    $oldOptions = $adminOptions->get();
    $updateOutput = $oldOptions['update_output'];

    $newOptions = $_GET;
    $newOptions['update_output'] = $updateOutput;
    return $adminOptions->save( $newOptions );

}

/**
* Save the output from the most recent update to the database
*/
function save_update_output( $output ){

    global $adminOptions;
    $options = [];
    $options['update_output'] = $output;
    return $adminOptions->save( $options );

}

/**
* Enable the Wordpress REST API to return acf data with schema responses
*/
function register_acf_rest_field() {

    \register_rest_field( 'schema',
        'acf',
        array(
            'get_callback'    => 'OMHSchemaLibrary\\rest_api_encode_acf',
            'update_callback' => null,
            'schema'          => null,
        )
    );

}

/**
* Callback for the Wordpress REST API to get a schema's acf data
* @return
*/
function rest_api_encode_acf( $post, $field_name, $request ) { 
    
    return \get_fields( $post['id'] );

}

/**
* Show the main config view and respond to any user input that happens there
*/
function omh_schema_library_config(){

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
        $data['update_output']['date'] = date(DATE_RFC2822);
        save_update_output( $data['update_output'] );
        reset_transient_options();
        $options = get_config_options();
        $data['output_old'] = false;
    }else{
        $data['update_output'] = $options['update_output'];
        $data['output_old'] = true;
    }

    $data['options'] = $options;


    view('config', $data);

}

/**
* Some options in the config view need to be reset after they are used, to prevent mistakes
*/
function reset_transient_options(){

    global $adminOptions;
    $options = get_config_options();
    $options['replace_all_authors'] = false;
    $adminOptions->save( $options );

}

/**
* Help sort the schema list
* @return the comparison result
*/
function compare_name( $a, $b ){

  return strnatcmp( $a['name'], $b['name'] );

}

/**
* Get a string that can be used in a href in the frontend for the schema
* @param $schema_id to use
* @return link string
*/
function convertSchemaIDtoHyperlink( $schema_id ){

  $parts = preg_split("/:/", $schema_id);
  
  $link = SCHEMA_URL_PATH . $parts[0] . "_" . $parts[1];
  return $link;

}

/**
* Generate markup for the list of schemas.
* This function is registered both as a Wordpress action 'omh_schema_library_archive_schema'
* and as a Wordpress shortcode 'schema_list'
* @param $shortcode_attributes passed in by the user (unused)
*/
function schema_list_view( $shortcode_attributes ){


    $schema_data = array();
    $schemas_added = array();

    //list terms in a given taxonomy
    if ( SHOW_CATEGORIES ){
        $taxonomy = 'schema_type';
        $tax_terms = get_terms($taxonomy);
        foreach ($tax_terms as $tax_term) {
          $data = array();
          $data['url'] = esc_attr( home_url() . get_term_link( $tax_term, $taxonomy ) );
          $data['name'] = $tax_term->name;
          $args = array(
              'post_type' => 'schema',
              'schema_type' => $tax_term->slug,
              'posts_per_page'=>-1
          );
          $query = new WP_Query( $args );
          $data['count'] = count( $query->posts );
          $schema_data[] = $data;
          foreach ( $query->posts as $schema ) {
            $schemas_added[] = $schema->ID;
          }
        }
    }

    $args = array(
        'post_type' => 'schema',
        'posts_per_page'=>-1,
    );

    $query = new \WP_Query( $args );
    foreach ( $query->posts as $schema ) {
      if ( array_search( $schema->ID, $schemas_added ) === false ){
        $data = array();
        $data['url'] = esc_attr( get_permalink( $schema->ID ) );
        $data['name'] = esc_attr( get_the_title( $schema->ID ) );
        $data['slug'] = esc_attr( $schema->ID );
        $data['count'] = 1;
        $data['deprecated'] = get_field( "deprecated", $schema->ID );
        $schema_data[] = $data;
      }
    }

    usort( $schema_data, 'OMHSchemaLibrary\\compare_name');

    include('views/schema-list-view.php');
    
}

/**
* Generate markup for a single schema.
* This function is registered as a Wordpress action 'omh_schema_library_single_schema'
*/
function single_schema_view( ){

    // the_post();

    $schema_id = str_replace('_', ':', get_post_field( 'post_name', get_post() ) );

    $schema_path = explode('_', get_post_field( 'post_name', get_post() ) );
    $schema_namespace = $schema_path[0];
    $schema_file_base = $schema_path[1];

    $options = get_config_options();
    $schema_url = rtrim( $options['schema_host_url'], '/');
    $versions = get_field('schema_versions');

    $schema_object = json_decode( trim( htmlspecialchars_decode( $versions[ count( $versions ) - 1 ]['schema_json'] ) ), true );

    if ( $schema_object ){

        $deprecation = array_key_exists( 'deprecation', $schema_object )? $schema_object['deprecation']: false;

        if( $deprecation ){
            $deprecation_date = date_format( date_create( $deprecation['date'] ), 'F dS, Y' );
            $supersededBy_link = convertSchemaIDtoHyperlink( $deprecation['supersededBy'] );
        }

        include('views/single-schema-view.php');

    }
    else return 'No schema versions found for this schema.';
}

/**
* Path in plugin where acf can be stored
*/
function my_acf_settings_path( $path ) {
 
    // update path
    $path = OMH_SCHEMA_LIBRARY__PLUGIN_DIR.'/_inc/acf/';

    // return
    return $path;

}

/**
* Path in plugin where acf settings can be stored
*/
function my_acf_settings_dir( $dir ) {
 
    // update path
    $dir = OMH_SCHEMA_LIBRARY__PLUGIN_URL.'/_inc/acf/';

    // return
    return $dir;

}


/**
* Add actions and shortcodes that link the plugin to the rest of Wordpress
*/
function initialize_plugin(){
    
    add_shortcode('schema_list', 'OMHSchemaLibrary\\schema_list_view');
    add_action('omh_schema_library_archive_schema', 'OMHSchemaLibrary\\schema_list_view');
    add_action('omh_schema_library_single_schema', 'OMHSchemaLibrary\\single_schema_view');

    add_action( 'rest_api_init', 'OMHSchemaLibrary\\register_acf_rest_field' );
}

/**
* Set up Advanced Custom Fields.
* Optionally include ACF from the local plugin directory.
* Register fields used by the plugin.
*/
function activate_acf(){

    /* Checks to see if “is_plugin_active” function exists and if not load the php file that includes that function */
    if ( ! function_exists('is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php');
    }

    /* Checks to see if the acf pro plugin is activated */
    if ( USE_INCLUDED_ACF && !is_plugin_active('advanced-custom-fields-pro/acf.php') ) {

        // 1. customize ACF path
        add_filter('acf/settings/path', 'OMHSchemaLibrary\\my_acf_settings_path');

        // 2. customize ACF dir
        add_filter('acf/settings/dir', 'OMHSchemaLibrary\\my_acf_settings_dir');
         
        // 3. Hide ACF field group menu item
        add_filter('acf/settings/show_admin', '__return_false');

        // 4. Include ACF
        include_once( '_inc/acf/acf.php' );

    }

    if ( !is_plugin_active('acf-hidden-master/acf-hidden.php') ) {
        include_once( '_inc/acf-hidden-master/acf-hidden.php' );
    }

    if ( is_plugin_active('advanced-custom-fields-pro/acf.php') ){
        include "_inc/acf_fields.php";

        SchemaLibrary::$field_definitions = get_acf_fields();

        \acf_add_local_field_group( SchemaLibrary::$field_definitions );
    }

}

// these actions must run when this file is loaded
add_action('init',       'OMHSchemaLibrary\\initialize_plugin' );
add_action('admin_init', 'OMHSchemaLibrary\\load_resources');
add_action('admin_menu', 'OMHSchemaLibrary\\omh_schema_library_setup_menu');

activate_acf();

?>
