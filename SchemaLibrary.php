<?php

/**
 * Main class for updating schemas and managing the interface in the admin section
 *
 */

namespace OMHSchemaLibrary;

class SchemaLibrary {

    private $schema_data = array();
    public static $schemaVersionVisibility = array();
    public static $field_definitions = false;
    public static $field_keys = [];

    /**
     * Set up the custom post types
     *
     */
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
            'show_in_rest' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'has_archive' => true,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'hierarchical' => true,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'page-attributes', 'tags'),
            'rewrite' => array('slug' => 'schemas')
        );
        register_post_type( 'schema' , $args );
    }

    /**
     * Add Schema types to Schemas
     *
     */
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
            'show_in_rest' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array( 'slug' => 'schema_type' )
        ));
    }

    /** 
     * Prepare markup for taxonomies that can help organize schemas
     *
     */
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
                        echo '<option value='. $term->slug, array_key_exists( $tax_slug, $_GET) && $_GET[ $tax_slug ] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>'; 
                    }
                    echo "</select>";
                }
            }
        }
    }

    /** 
     * Prepare structure of table for the admin section to help track status of schemas
     *
     * @param $columns to add our data to in the table
     * @return the columns
     */
    public static function admin_table_head( $columns ) {
        $columns['version_numbers']  = 'Versions';
        // $columns['release_dates']  = 'Release Dates';
        return $columns;
    }

    /** 
     * Prepare content for the admin section to help track status of schemas
     *
     * @param $column_name in the admin table
     * @param $post_id schema
     */
    public static function admin_table_content( $column_name, $post_id ) {

        if( $column_name == 'version_numbers' ) {
            $version_field_key = SchemaLibrary::acf_get_field_key( $post_id, '%schema_versions%' );
            $versions = get_field( $version_field_key, $post_id );
            $version_count = count( $versions );
            for ($i=0; $i < $version_count; $i++) {

                echo "<strong>".$versions[ $i ]['version']."</strong>";

                $date_field = $versions[ $i ]['released'];
                if ( $date_field != false && $date_field != '' ){
                    $date = \DateTime::createFromFormat('Ymd', $date_field);
                    echo  "&nbsp;&nbsp; " . $date->format('F d, Y');
                }else{
                    echo '&nbsp;&nbsp; <span class="omh-schema-library-alert-text">no release date</span>';
                }
                if ( $i < $version_count-1 ){
                    echo "<br/>\n";
                }
            }
        }
    }

    /** 
     * Add the data of a schema to an array in memory for use later in the update process
     *
     * @param $post_id of the schema
     * @param $key of field in the array
     * @param $value of the field
     */
    private function addSchemaData( $post_id, $key, $value ){
        if ( ! array_key_exists( $key, $this->schema_data ) ){
            $this->schema_data[ $key ] = array();
        }
        if ( ! array_key_exists( $post_id, $this->schema_data[ $key ] ) ){
            $this->schema_data[ $key ][ $post_id ] = array();
        }
        $this->schema_data[ $key ][ $post_id ][] = $value;
    }

    /** 
     * Get the data of a schema that has been stored by the class in memory for use later in the update process
     * @return
     */
    private function getSchemaData( $key ){
        return $this->schema_data[ $key ];
    }

    /** 
     * Copies existing data in a field to a new version so it is preserved by the update
     *
     * @param $existing_versions of the data
     * @param $new_versions of data
     * @param $field_name of acf field that holds the data
     * @param $key_field_name name of the key indexing the data that should be copied over
     */
    private function copyExistingFieldsByKeyField( $existing_versions, & $new_versions, $field_name, $key_field_name ){
        foreach ( $existing_versions as $existing_index => $existing_version_values ) {
          foreach ( $new_versions as $new_index => $new_version_values ) {
            if ( $new_version_values[ $key_field_name ] == $existing_version_values[ $key_field_name ] ){
              $new_versions[ $new_index ][ $field_name ] = $existing_version_values[ $field_name ];
            }
          }
        }
    }

    /** 
     * Get name (slug) of the post for a given organization and schema
     *
     * @param $organization_name
     * @param $schema_name
     *
     */
    private function getPostName( $organization_name, $schema_name ){
        return $organization_name . "_" . $schema_name;
    }

    /** 
     * Update the schema library using the data pulled down from git.
     *
     * Optionally pulls in data from git. Provides output to indicate what was done.
     * Stores and updates data in ACF fields. Groups sample data with the schema it belongs to.
     *
     * @param $options set by the user in the admin to change how the library is updated.
     *
     */
    public function updateLibrary( $options ){

        $schema_dir = OMH_SCHEMA_LIBRARY__PLUGIN_DIR . "schemas";

        $update_output = array(
            'git_result' => '',
            'update_result' => '',
        );


        if ( $options['git_enabled'] == 1 ){
                $remove_directory_command = "rm -rf " . $schema_dir;
                $git_command = "git clone " . $options['git_repository'] . ( $options['git_branch'] ? " -b " . $options['git_branch'] . " ": " " ) . $schema_dir;
                $update_output['git_result'] .= $git_command . "\n";
                $set_home_command = 'export HOME=/var/www';
                exec( $set_home_command . "; " . $remove_directory_command . "; " . $git_command . " 2>&1", $output );
                exec( "echo whoami: `whoami`", $output );
                exec( "echo groups: `groups`", $output );
                exec( "echo umask: `umask`", $output );
                exec( "cd " . $schema_dir . "; git reset --hard HEAD 2>&1", $output );
                $update_output['git_result'] .= "changing permissions on " . $schema_dir . "\n";
                exec( "chmod -R g+rw " . $schema_dir, $output );
                foreach( $output as $line ){
                    $update_output['git_result'] .= $line . "\n";
                }
        }

        $schema_json_path = $schema_dir . "/" . $options['base_dir'];

        $update_output['update_result'] .= "Checking directory: " . $schema_json_path . "\n\n";

        try {
            $objects = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $schema_json_path ), \RecursiveIteratorIterator::SELF_FIRST );
        } catch (\Exception $e) {
            $update_output['update_result'] .= "Update failed. Please check your configuration options and try again.\n" .  $e->getMessage() . "\n";
            return $update_output;
        }

        // used to keep track of changes so they can be messaged
        $old_versions_by_name = [];

        foreach( $objects as $name => $object ){

            if ( preg_match ( '/^.+\.json$/i' , $name ) ){

                $file_name = basename( $name );

                preg_match( "/.+\/([^\/]+)\/([^\/]+)-([[:alnum:]](?:\.[[:alnum:]]+)+)\.json/", $name, $matches );
                $organization_name = $matches[1];
                $schema_name = $matches[2];
                $version_name = $matches[3];

                if ( strpos($version_name, 'x') !== false ){
                    continue;
                }

                $post_updates = '';

                $post_name = $this->getPostName( $organization_name, $schema_name );

                // escape the backslashes so that wp (acf?) does nto remove them
                $file_content = str_replace("\\","&#92;", file_get_contents( $name ) ) ;

                $post = get_page_by_path( $post_name, OBJECT, 'schema'); //get_page_by_title( $id_without_version, 'OBJECT', 'schema' );

                $post_exists = ($post!=NULL) && property_exists( $post, 'ID' );

                $post_title = $post_exists ? $post->post_title :ucfirst( str_replace('-', ' ', $schema_name ) );

                // if the post exists, use its content. otherwise, use a default.
                $description = $post_exists ? $post->post_content : 'This schema page is a draft. Please enter a description for this schema and publish it.';

                // all new posts should be drafts so that they can be edited before they are seen
                $status = $post_exists ? $post->post_status : 'draft';

                $new_post = array(
                        'post_title'     => $post_title, // The title of your post.
                        'post_name'      => $post_name, // The slug of your post.
                        'post_status'    => $status, // Default 'draft'.
                        'post_type'      => 'schema', // Default 'post'.
                        'post_content'   => $description, // The full text of the post.
                );
                
                if ( $post_exists ){
                    // existing posts
                    $new_post['ID'] = $post->ID;
                    $post_id = wp_insert_post( $new_post );
                } else {
                    // new posts
                    $post_id = wp_insert_post( $new_post );
                    update_field( 'organization', $organization_name, $post_id );
                    update_field( 'author', $options['default_author'], $post_id );
                    update_field( 'author_url', $options['default_author_url'], $post_id );
                }

                if ( $options['replace_all_authors'] == 1 ) {
                    if ( $options['default_author'] != get_field('author', $post_id ) ){
                        $post_updates .=  'author,';
                    }
                    if ( $options['default_author_url'] != get_field('author_url', $post_id ) ){
                        $post_updates .=  'author url,';
                    }
                    update_field('author', $options['default_author'], $post_id );
                    update_field('author_url', $options['default_author_url'], $post_id );
                }

                // if the array of old values for this post is not initialized yet, create and fill it 
                if ( !array_key_exists( strval($post_id), $old_versions_by_name ) ){
                    $old_versions_by_name[ strval($post_id) ] = [];
                    $old_versions = get_field('schema_versions', $post_id );
                    foreach ( $old_versions as $old_version ) {
                        $old_versions_by_name[ strval($post_id) ][ $old_version['version'] ] = $old_version;
                    }
                }
                if( !array_key_exists( $version_name, $old_versions_by_name[ strval($post_id) ] ) ){
                    $post_updates .= 'v'.$version_name.',';
                }

                unset($value); // break the reference with the last element

                $this->addSchemaData( $post_id, 'schema_versions', array(
                    'version' => $version_name,
                    'schema_json' => $file_content
                ));

                $post_url = get_permalink( $post_id );
                $post_edit_url = get_edit_post_link( $post_id, '' );

                if( !$post_exists ){
                    $update_output['update_result'] .= "Created a new library entry for: " . $schema_name . " v. " . $version_name;
                    $update_output['update_result'] .= " <a href='" . $post_edit_url . "'>edit</a>\n";
                }
                if ( $post_updates != "" ) {
                    $post_updates = substr( $post_updates, 0, -1 );
                    $update_output['update_result'] .= "Updated library entry (" . $post_updates . ") for: " . $schema_name . " v. " . $version_name;
                    $update_output['update_result'] .= " <a href='" . $post_url . "'>view</a>\n";
                }

            }
        }

        $sample_data_json_path = $schema_dir . "/" . $options['sample_data_dir'];

        $update_output['update_result'] .= "\nChecking directory: " . $sample_data_json_path . "\n\n";

        try {
            $objects = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $sample_data_json_path ), \RecursiveIteratorIterator::SELF_FIRST );
        } catch (\Exception $e) {
            $update_output['update_result'] .= "Update failed. Please check your configuration options and try again.\n" .  $e->getMessage() . "\n";
            return $update_output;
        }

        foreach( $objects as $name => $object ){
            if ( preg_match ( '/^.+\.json$/i' , $name ) ){

                $file_name = basename( $name );
                $file_content = file_get_contents( $name );

                preg_match( "/\/([^\/]+)\/([^\/]+)\/(?:(\d(?:\.\d+)+)\/)?shouldPass\/(.+)\.json/", $name, $matches );

                if ( $matches ){

                    $organization_name = $matches[1];
                    $schema_name = $matches[2];
                    $version_name = $matches[3];
                    $sample_data_title = ucfirst( preg_replace("/-/", " ", $matches[4]) );

                    $post_name = $this->getPostName( $organization_name, $schema_name );

                    $post = get_page_by_path( $post_name, OBJECT, 'schema'); // get_page_by_title( $id_without_version, 'OBJECT', 'schema' );
                    $post_exists = ( $post != NULL ) && property_exists( $post, 'ID' );

                    $data_fields = json_decode( $file_content );
                    if ( is_array( $data_fields ) && array_key_exists('description', $data_fields ) ){
                        $description = $data_fields['description'];
                    }

                    if ( $post_exists ){
                        $post_id = $post->ID;
                        $this->addSchemaData( $post_id, 'sample_data', array(
                            'version' => $version_name,
                            'file_name' => $file_name,
                            'description' => $description,
                            'version' => $version_name,
                            'sample_data_json'=>$file_content,
                            'title' => $sample_data_title,
                            ) );
                    }

                }

            }
        }


        //Update json fields for each version
        foreach( $this->getSchemaData('schema_versions') as $post_id => $new_versions_for_post ){

            $existing_versions = get_field( 'schema_versions', $post_id );

            if( $existing_versions && count( $existing_versions ) > 0 ){
                $this->copyExistingFieldsByKeyField( $existing_versions, $new_versions_for_post, 'visibility', 'version' );
                $this->copyExistingFieldsByKeyField( $existing_versions, $new_versions_for_post, 'released', 'version' );
            }

            $field_key = $this->acf_get_field_key( $post_id, '%schema_versions%' );
            update_field( $field_key, $new_versions_for_post, $post_id );
        }

        //Update sample data fields
        foreach( $this->getSchemaData('sample_data') as $post_id => $new_data_for_post ){

            $existing_versions = get_field( 'sample_data', $post_id );
            $schema_name = get_the_title( $post_id );
            $post_edit_url = get_edit_post_link( $post_id, '' );

            foreach( $new_data_for_post as $index => $new_data_item ){
                $item_exists = false;
                foreach( $existing_versions as $index => $existing_item ){
                    if ( $existing_item['file_name'] == $new_data_item['file_name'] && $existing_item['version'] == $new_data_item['version'] ){
                        $item_exists = true;
                    }
                }
                if ( !$item_exists ){
                    $update_output['update_result'] .= "Sample data '" . $new_data_item['file_name'] . "' added to '" . $schema_name . ".' To make it visible, <a href='" . $post_edit_url . "'>edit</a> the schema.\n";
                }
            }

            if( $existing_versions && count( $existing_versions ) > 0 ){
                $this->copyExistingFieldsByKeyField( $existing_versions, $new_data_for_post, 'visibility', 'file_name' );
                $this->copyExistingFieldsByKeyField( $existing_versions, $new_data_for_post, 'title', 'file_name' );
            }

            $field_key = $this->acf_get_field_key( $post_id, '%sample_data%' );
            update_field( $field_key, $new_data_for_post, $post_id );

        }

        return $update_output;

    }

    /** 
     * Is the version visible?
     * 
     * @param $version of the schema.
     * @return
     */
    private static function get_version_visibility( $version ) {
        return is_array( $version['visibility'] ) && count( $version['visibility'] ) > 0 && $version['visibility'][0] == 'visible';
    }

    /** 
     * This function is used to track when a version is checked 'visible'.
     * 
     * @param $id of the post.
     */
    public static function set_schema_updated_field_status( $id ) {
        //store the visibility state of each version in a post
        //so that the next hook can compare old to new value
        // check if it's a schema
        if ( get_post_type($id) != 'schema' ){
            return;
        }
        $versions = \get_field( 'schema_versions', $id );
        for ($i=0; $i < count( $versions ); $i++) { 
            $key = $id . ':' . $i;
            SchemaLibrary::$schemaVersionVisibility[ $key ] = SchemaLibrary::get_version_visibility( $versions[$i] );
        }
    }

    /** 
     * Document when a schema version was released by checking the visibility box in the admin
     * 
     * @param $id of the post.
     */
    public static function set_schema_updated_field( $id ) {
        // check if it's a schema
        if ( get_post_type($id) != 'schema' ){
            return;
        }
        $versions = get_field( 'schema_versions', $id );
        for ($i=0; $i < count( $versions ); $i++) { 
            $key = $id . ':' . $i;
            $newVisibility = SchemaLibrary::get_version_visibility( $versions[$i] );
            if ( array_key_exists( $key, SchemaLibrary::$schemaVersionVisibility ) && ( $newVisibility != SchemaLibrary::$schemaVersionVisibility[ $key ] ) && $newVisibility ){
                if ( $versions[$i]['released']=='' ) {
                    $versions[$i]['released'] = date('Ymd');
                }
            }
            $versions[$i]['schema_json'] = html_entity_decode( $versions[$i]['schema_json'] );
        }
        $field_key = SchemaLibrary::acf_get_field_key( $id, '%schema_versions%' );
        update_field( $field_key, $versions, $id );
    }


    /** 
     * This function will recursively search a $field and its subfields for a field with name $name.
     * 
     * @param $field to check.
     * @param $name to find.
     * @return result
     */
    private static function search_field( $field, $name ){
        if ( $field['name'] == $name ){
            return $field['key'];
        } else {
            if ( array_key_exists( 'sub_fields', $field ) ) {
                foreach ( $field['sub_fields'] as $sub_field ) {
                    $result = SchemaLibrary::search_field( $sub_field, $name );
                    if ( $result ){
                        return $result;
                    }
                }
            }
        }
        return false;
    }

    /** 
     * Get field key for field name. ACF 4
     * Will return first matched acf field key for a give field name.
     * 
     * This function will return the field_key of a certain field.
     * 
     * @param $post_id int The post id to check.
     * @return 
     */
    private static function acf_get_field_key( $post_id, $field_match ) {

        $field_definitions = SchemaLibrary::$field_definitions;

        if( $field_definitions ){
            if ( array_key_exists( $field_match, SchemaLibrary::$field_keys ) ) {
                return SchemaLibrary::$field_keys[ $field_match ];
            }else{
                $name = substr($field_match, 1, -1);
                foreach ( $field_definitions['fields'] as $field ) {
                    $key = SchemaLibrary::search_field( $field, $name );
                    if ( $key ){
                        SchemaLibrary::$field_keys[ $field_match ] = $key; //save the result, avoid search later
                        return $key;
                    }
                }
            }
        } else {

            global $wpdb;

            $posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts where post_type=%s", 'acf' ) );
            var_dump($posts);
            $rows = $wpdb->get_results( $wpdb->prepare("SELECT meta_key,meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s AND meta_value LIKE %s", $posts[0]->ID, 'field_%', $field_match), ARRAY_A);
            var_dump($rows);
            $field_data = unserialize( $rows[0]["meta_value"] );//not sure why it is in there twice...
            debug_print_backtrace();
            return $field_data['key'];
        }
    }

    /** 
     * Initialize the class.
     * Add all necessary actions and filters
     */
    public function init(){
        add_action('init', 'OMHSchemaLibrary\\SchemaLibrary::registerSchemas');
        add_action('init', 'OMHSchemaLibrary\\SchemaLibrary::createSchemaTypeTaxonomy');
        add_action('restrict_manage_posts', 'OMHSchemaLibrary\\SchemaLibrary::addSchemaTaxonomyFilters');
        add_action('acf/save_post', 'OMHSchemaLibrary\\SchemaLibrary::set_schema_updated_field_status', 0);
        add_action('acf/save_post', 'OMHSchemaLibrary\\SchemaLibrary::set_schema_updated_field', 20);
        add_filter('manage_schema_posts_columns', 'OMHSchemaLibrary\\SchemaLibrary::admin_table_head');
        add_action('manage_schema_posts_custom_column', 'OMHSchemaLibrary\\SchemaLibrary::admin_table_content', 10, 2 );
    }

}

?>