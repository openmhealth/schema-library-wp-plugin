<?php namespace OMHSchemaLibrary;

class SchemaLibrary {

  private static $schemaVersions;

  public static function registerSchemas()
  {
    $labels = array(
      'name' => __('Schemas', 'post type general name'),
      'singular_name' => __('Schema', 'post type singular name'),
      'add_new' => __('Add Schema', 'project item'),
      'add_new_item' => __('Add New Schema'),
      'edit_item' => __('Edit Schema'),
      'new_item' => __('New Schema'),
      'view_item' => __('View Schema'),
      'search_items' => __('Search Schema'),
      'not_found' =>  __('Nothing found'),
      'not_found_in_trash' => __('Nothing found in Trash'),
      'parent_item_colon' => ''
    );
   
    $args = array(
      'labels' => $labels,
      'public' => false,
      'publicly_queryable' => true,
      'show_ui' => true,
      'query_var' => true,
      'rewrite' => true,
      'capability_type' => 'post',
      'hierarchical' => true,
      'menu_position' => null,
      'supports' => array('title', 'editor', 'page-attributes', 'tags'),
      'rewrite' => array('slug' => 'developers/schema')
    );
    register_post_type( 'schema' , $args );
  }

  // Add Schema types to Schemas
  public static function createSchemaTypeTaxonomy() 
  {
    $labels = array(
      'name' => _x( 'Schema Types', 'taxonomy general name' ),
      'singular_name' => _x( 'Schema Type', 'taxonomy singular name' ),
      'search_items' =>  __( 'Search Schema Types' ),
      'popular_items' => __( 'Popular Schema Types' ),
      'all_items' => __( 'All Schema Types' ),
      'parent_item' => null,
      'parent_item_colon' => null,
      'edit_item' => __( 'Edit Schema Type' ),
      'update_item' => __( 'Update Schema Type' ),
      'add_new_item' => __( 'Add New Schema Type' ),
      'new_item_name' => __( 'New Schema Type Name' ),
      'separate_items_with_commas' => __( 'Separate Schema Types with commas' ),
      'add_or_remove_items' => __( 'Add or remove functions tags' ),
      'choose_from_most_used' => __( 'Choose from the most used Schema Types' ),
      'menu_name' => __( 'Schema Types' )
    );

    register_taxonomy('schema_type','schema', array(
      'hierarchical' => true,
      'labels' => $labels,
      'show_ui' => true,
      'show_admin_column' => true,
      'update_count_callback' => '_update_post_term_count',
      'query_var' => true,
      'rewrite' => array( 'slug' => 'schema_type' )
    ));
  }


  public static function addSchemaTaxonomyFilters() {
    global $typenow;
   
    // an array of all the taxonomies you want to display. Use the taxonomy name or slug
    $taxonomies = array('schema_type');
   
    // must set this to the post type you want the filter(s) displayed on
    if( $typenow == 'schema' ){
   
      foreach ($taxonomies as $tax_slug) {
        $tax_obj = get_taxonomy($tax_slug);
        $tax_name = $tax_obj->labels->name;
        $terms = get_terms($tax_slug);
        if(count($terms) > 0) {
          echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
          echo "<option value=''>Show All $tax_name</option>";
          foreach ($terms as $term) { 
            echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>'; 
          }
          echo "</select>";
        }
      }
    }
  }

  public function init(){
    add_action('init', 'OMHSchemaLibrary\\SchemaLibrary::registerSchemas');
    add_action('init', 'OMHSchemaLibrary\\SchemaLibrary::createSchemaTypeTaxonomy');
    add_action('restrict_manage_posts', 'OMHSchemaLibrary\\SchemaLibrary::addSchemaTaxonomyFilters');
    self::$schemaVersions = array();
  }

  public function updateLibrary( $options ){

    $schema_dir = OMH_SCHEMA_LIBRARY__PLUGIN_DIR . "schemas";

    $update_output = array(
      'git_result' => '',
      'update_result' => '',
    );


    if ( $options['git_enabled']==1 ){
        $remove_directory_command = "rm -rf " . $schema_dir;
        $git_command = "git clone " . $options['git_repository'] . ( $options['git_branch'] ? " -b " . $options['git_branch'] . " --single-branch " : " " ) . $schema_dir;
        exec( $remove_directory_command . "; " . $git_command . " 2>&1", $output );
        exec( "cd " . $schema_dir . "; git reset --hard HEAD 2>&1", $output );
        foreach( $output as $line ){
            $update_output['git_result'] .= $line . "\n";
        }
    }

    $json_path = $schema_dir . "/" . $options['git_repository_base_dir'];

    $update_output['update_result'] .= "Checking directory: " . $json_path . "\n\n";

    try {
      $objects = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $json_path ), \RecursiveIteratorIterator::SELF_FIRST );
    } catch (\Exception $e) {
      $update_output['update_result'] .= "Update failed. Please check your configuration options and try again.\n" .  $e->getMessage() . "\n";
      return $update_output;
    }

    foreach( $objects as $name => $object ){

        if ( preg_match ( '/^.+\.json$/i' , $name ) ){

            $file_name = basename( $name );

            preg_match( "/(.+)-(\d(?:\.\d+)+)\.json/", $file_name, $matches );
            $schema_name = $matches[1];
            $version_name = $matches[2];

            $file_content = file_get_contents( $name );

            $post = get_page_by_title( $schema_name, 'OBJECT', 'schema' );

            $post_exists = ($post!=NULL) && property_exists( $post, 'ID' );

            $description = $post_exists ? $post->post_content : 'This schema page is a draft. Please enter a description for this schema and publish it.';

            $status = $post_exists ? $post->post_status : 'draft';

            $new_post = array(
                'post_title'     => $schema_name, // The title of your post.
                'post_status'    => $status, // Default 'draft'.
                'post_type'      => 'schema', // Default 'post'.
                'post_content'   => $description, // The full text of the post.
                //'post_parent'    => [ <post ID> ] // Sets the parent of the new post, if any. Default 0.
                //'menu_order'     => [ <order> ] // If new post is a page, sets the order in which it should appear in supported menus. Default 0.
                //'post_excerpt'   => [ <string> ] // For all your post excerpt needs.
                // 'post_date'      => [ Y-m-d H:i:s ] // The time post was made.
                // 'post_date_gmt'  => [ Y-m-d H:i:s ] // The time post was made, in GMT.
                // 'comment_status' => [ 'closed' | 'open' ] // Default is the option 'default_comment_status', or 'closed'.
                // 'post_category'  => [ array(<category id>, ...) ] // Default empty.
                // 'tags_input'     => [ '<tag>, <tag>, ...' | array ] // Default empty.
                // 'tax_input'      => [ array( <taxonomy> => <array | string>, <taxonomy_other> => <array | string> ) ] // For custom taxonomies. Default empty.
                // 'page_template'  => [ <string> ] // Requires name of template file, eg template.php. Default empty.
            );
            
            if ( $post_exists ){
                $new_post['ID'] = $post->ID;
            }

            $post_id = wp_insert_post( $new_post );

            $version_index = 0;
            if ( ! array_key_exists( $post_id, self::$schemaVersions ) ){
              self::$schemaVersions[ $post_id ] = array();
            }else{
              $version_index = count( self::$schemaVersions[ $post_id ] );
            }
            self::$schemaVersions[ $post_id ][ $version_index ] = array(
              'version' => $version_name,
              'schema_json' => $file_content,
              'visibility' => '', //change this based on whether it is there already
            );

            $post_url = get_permalink( $post_id );
            $post_edit_url = get_edit_post_link( $post_id, '' );

            $update_output['update_result'] .= $schema_name . " v. " . $version_name . ( $post_exists? " updated " : " created " ) . "<a href='" . $post_url . "'>view</a>|<a href='" . $post_edit_url . "'>edit</a>" . "\n";

        }
    }


      echo "<pre>";

    foreach( self::$schemaVersions as $post_id => $new_versions_for_post ){

      $field_key = $this->acf_get_field_key('schema_versions', $post_id );
      update_field( $field_key, $new_versions_for_post, $post_id );
      //break;
      //$existing_versions = get_field( 'schema_versions', $post_id );

      //echo "<pre>\n $post_id \n";

      //echo "EXISTING\n";
      //var_dump( $existing_versions );

      // if( $existing_versions && count( $existing_versions ) > 0 ){
      //   foreach ( $existing_versions as $existing_index => $existing_version_values ) {
      //     foreach ( $new_versions_for_post as $new_index => $new_version_values ) {
      //       if ( $new_version_values['version'] == $existing_version_values['version'] ){
      //         $new_version_values['visibility'] = $existing_version_values['visibility'];
      //       }
      //     }
      //   }
      // }

      //echo $post_id . " existing\n";
      //var_dump( get_field( 'schema_versions', $post_id ) );
      //echo "\n";
    }


    echo "</pre>";

    return $update_output;

  }

  /** 
   * Get field key for field name. ACF 4
   * Will return first matched acf field key for a give field name.
   * 
   * This function will return the field_key of a certain field.
   * 
   * @param $field_name String ACF Field name
   * @param $post_id int The post id to check.
   * @return 
   */
  private function acf_get_field_key( $field_name, $post_id ) {

    global $wpdb;

    $posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts where post_type=%s", 'acf' ) );
    $rows = $wpdb->get_results( $wpdb->prepare("SELECT meta_key,meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s AND meta_value LIKE %s", $posts[0]->ID, 'field_%', '%versions%'), ARRAY_A);
    $field_data = unserialize( $rows[0]["meta_value"] );//not sure why it is in there twice...

    return $field_data['key'];

  }

}



            //pull in contributors for this file

            // exec("cd " . $schema_dir . "; git config --get remote.origin.url", $git_repo_url );

            // $git_repo_url = $git_repo_url[0];

            // preg_match('/https:\/\/[^\/]+\/(.+).git/', $git_repo_url, $matches );
            // $git_path = $matches[1];

            // $trim_count = strlen( $schema_dir ) + 1;

            // $filepath_in_repo = substr( $name, $trim_count );

            // $git_data_url = "https://api.github.com/repos/" . $git_path . "/commits?path=" . $filepath_in_repo;

            // echo $git_data_url;

            // $curlSession = curl_init();
            // $agent = 'openmhealth/schemas';

            // //curl_setopt($curlSession, CURLOPT_USERPWD, $);
            // curl_setopt($curlSession, CURLOPT_USERAGENT, $agent);
            // curl_setopt($curlSession, CURLOPT_URL, $git_data_url);
            // curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
            // curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

            // $jsonData = curl_exec($curlSession);
            // curl_close($curlSession);
            // var_dump(json_decode($jsonData));


            //update_field('schema_json', $file_content, $post_id );
            //update_field('schema_json', [ 'schema_json' => $file_content, 'version' => '1.0', 'visible' => 0 ], $post_id );


?>