<?php
/**
*
* Class representing the options that are set when configuring the library in the wp admin
*
*
*/

namespace OMHSchemaLibrary;

class AdminOptions {

  //options available for configuring the plugin behavior
  private $options = array(
    'git_repository' => '',
    'base_dir' => '',
    'sample_data_dir' => '',
    'git_branch' => '',
    'schema_host_url' => '',
    'default_author' => '',
    'default_author_url' => '',
    'git_enabled' => false,
    'replace_all_authors' => false,
    'update_output' => '',
    'replace_all_contributors' => false,
  );

  private $optionsLoaded = false;

  private $pluginOptionKey = 'options_omh_schema_library';

  /**
  * Get the stored options
  * @return
  */
  public function get(){

    if( !$this->optionsLoaded ){
      $this->load();
    }

    return $this->options;

  }

  /**
  * Load the stored options
  */
  public function load(){

    $omh_options = get_option( $this->pluginOptionKey );
    if ( $omh_options ) {
      foreach( $this->options as $name => $value){
        if ( array_key_exists( $name, $omh_options )){
          $this->options[ $name ] = $omh_options[ $name ];
        }
      }
    }

    $this->optionsLoaded = true;

  }

  /**
  * Save options in the database
  * @param new options to save
  */
  public function save( $newOptions ){

    foreach( $this->options as $name => $value ){
      if ( array_key_exists( $name, $newOptions ) ){
        $this->options[ $name ] = $newOptions[ $name ];
      }
    }

    update_option( $this->pluginOptionKey, $this->options );

  }
 
}