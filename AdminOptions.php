<?php namespace OMHSchemaLibrary;

class AdminOptions {

  private $options = array(
    'git_repository' => '',
    'git_repository_base_dir' => '',
    'git_branch' => '',
    'schema_host_url' => '',
    'git_enabled' => false,
  );

  private $optionsLoaded = false;

  private $pluginOptionKey = 'options_omh_schema_library';
  private static $pluginDataOptionKey = 'options_omh_schema_library_data';

  public function get(){

    if( !$this->optionsLoaded ){
      $this->load();
    }

    return $this->options;

  }

  public function load(){

    $omh_options = get_option( $this->pluginOptionKey );

    foreach( $this->options as $name => $value){
      if ( array_key_exists( $name, $omh_options )){
        $this->options[ $name ] = $omh_options[ $name ];
      }
    }

    $this->optionsLoaded = true;

  }

  public function save( $newOptions ){

    foreach( $this->options as $name => $value ){
      if ( array_key_exists( $name, $newOptions ) ){
        $this->options[ $name ] = $newOptions[ $name ];
      }
    }

    update_option( $this->pluginOptionKey, $this->options );

  }
 
  public static function saveSchemaData( $data ){
    update_option( AdminOptions::$pluginDataOptionKey, $data );
  }

  public static function getSchemaData(){
    return get_option( AdminOptions::$pluginDataOptionKey );
  }

}