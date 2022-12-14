<?php 
/**
 * The admin-specific functionality of the plugin
 * @link       localhost/graphql
 * @since      1.0.0
 * @package iexport
 * @subpackage iexport/admin
 */
/**
 * The admin-specific functionality of the plugin.
 * 
 */
class Iexport_admin {
    private $plugin_name;
    private $version;
    
    protected $display_page;
    protected $options;
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version =  $version;
        require_once plugin_dir_path( dirname(__FILE__) ).'admin/partials/class-iexport-admin-display.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/class-iexport-admin-options.php';
        $this->display_page = new Iexport_admin_display();
        $this->options = new Iexport_admin_options();
    }
/**
 * Register the stylesheets for the admin area.
 */
    public function enqueue_styles(){
        wp_enqueue_style('admin-style', plugin_dir_url(__FILE__ ).'css/admin.css',array(),$this->version,'all');

    }

    /**
     * Register the Javascript for the admin area.
     * @since 1.0.0
     */
    public function enqueue_scripts(){

        wp_enqueue_script( 'admin-script', plugin_dir_url(__FILE__ ).'js/admin.js',array('jquery'), $this->version);
        wp_localize_script('admin-script','ajax_object',array('ajax_url' => admin_url('admin-ajax.php')));
        
    }
    
    public function display_page(){
      
        add_menu_page('iexport-page','Import/Export','manage_options','iexport-page',array($this->display_page,'settings_page'));
        add_submenu_page('iexport-page','import-page','Import','manage_options','import-page',array($this->display_page,'import_page'));
        add_submenu_page('iexport-page','export-page','Export','manage_options','export-page',array($this->display_page,'export_page'));
       
    }
    function settings_options(){
       
        register_setting('iexport-page','iexport_setting_name');
            
        add_settings_section('iexport_setting_section','iexport setting section',array($this->options,'iexport_setting_section_callback'),'iexport-page');

        add_settings_field('iexport_setting_field', 'iexport setting',array($this->options,'iexport_setting_field_callback'),'iexport-page','iexport_setting_section');
        
    }

     /*************** EXPORT function**************************/
  function export_p(){
    
    //check for selected values and create query
    $selected_ops = array();
    if(is_array(get_option( "iexport_setting_name" ) ) ){
        $selected_ops = get_option("iexport_setting_name")['clusters'];
        $args = array('post_type' =>  $selected_ops );
    }else{
        $args = array('post_type' => 'post');
    }
    //create query for post type 
                    
    $query = new WP_Query( $args);
    $bytes = file_put_contents(plugin_dir_path( dirname(__FILE__) )."iexport_posts.json",json_encode($query->posts));

    if($bytes){
    
        $res = '<a class = "doenload_link" href= "'. plugin_dir_url(dirname(__FILE__)).'iexport_posts.json' .'" download >download file </a> ';
    }

    echo $res;
wp_die();

} 
  /*************** IMPORT function **************************/

  function import_p(){

    $json = file_get_contents(plugin_dir_path(dirname(__FILE__)).'iexport_posts.json');
    $json_array = json_decode($json,true);
   
   foreach($json_array as $p =>$p_array){
     
       $p_array['ID'] = 0;
      $check = $this->wp_insert_post($p_array);
      
       echo 'check--'.$check;
   }
    
    } 

    /*******************wp_import_post  function ********** */
   
    function wp_my_insert_post( $postarr, $wp_error = false, $fire_after_hooks = true ) {
        global $wpdb;
     
        // Capture original pre-sanitized array for passing into filters.
        $unsanitized_postarr = $postarr;
     
        $user_id = get_current_user_id();
     
        $defaults = array(
            'post_author'           => $user_id,
            'post_content'          => '',
            'post_content_filtered' => '',
            'post_title'            => '',
            'post_excerpt'          => '',
            'post_status'           => 'draft',
            'post_type'             => 'post',
            'comment_status'        => '',
            'ping_status'           => '',
            'post_password'         => '',
            'to_ping'               => '',
            'pinged'                => '',
            'post_parent'           => 0,
            'menu_order'            => 0,
            'guid'                  => '',
            'import_id'             => 0,
            'context'               => '',
            'post_date'             => '',
            'post_date_gmt'         => '',
        );
     
        $postarr = wp_parse_args( $postarr, $defaults );
     
        unset( $postarr['filter'] );
     
        $postarr = sanitize_post( $postarr, 'db' );
     
        // Are we updating or creating?

        $post_ID = 0;
        $update  = false;
        $guid    = $postarr['guid'];
     
        if ( ! empty( $postarr['ID'] ) ) {

            $update = true;
     
            // Get the post ID and GUID.
            $post_ID     = $postarr['ID'];
            $post_before = get_post( $post_ID );
     
            if ( is_null( $post_before ) ) {
                if ( $wp_error ) {
                    return new WP_Error( 'invalid_post', __( 'Invalid post ID.' ) );
                }
                return 0;
            }
     
            $guid            = get_post_field( 'guid', $post_ID );
            $previous_status = get_post_field( 'post_status', $post_ID );
        } else {
             $previous_status = 'new';
             $post_before     = null;
        }
     
        $post_type = empty( $postarr['post_type'] ) ? 'post' : $postarr['post_type'];
     
        $post_title   = $postarr['post_title'];
        $post_content = $postarr['post_content'];
        $post_excerpt = $postarr['post_excerpt'];
     
        if ( isset( $postarr['post_name'] ) ) {
            $post_name = $postarr['post_name'];
        } elseif ( $update ) {
            // For an update, don't modify the post_name if it wasn't supplied as an argument.
            $post_name = $post_before->post_name;
        }
     
        $maybe_empty = 'attachment' !== $post_type
            && ! $post_content && ! $post_title && ! $post_excerpt
            && post_type_supports( $post_type, 'editor' )
            && post_type_supports( $post_type, 'title' )
            && post_type_supports( $post_type, 'excerpt' );
     
        /**
         * Filters whether the post should be considered "empty".
         *
         * The post is considered "empty" if both:
         * 1. The post type supports the title, editor, and excerpt fields
         * 2. The title, editor, and excerpt fields are all empty
         *
         * Returning a truthy value from the filter will effectively short-circuit
         * the new post being inserted and return 0. If $wp_error is true, a WP_Error
         * will be returned instead.
         *
         * @since 3.3.0
         *
         * @param bool  $maybe_empty Whether the post should be considered "empty".
         * @param array $postarr     Array of post data.
         */
        if ( apply_filters( 'wp_insert_post_empty_content', $maybe_empty, $postarr ) ) {
            if ( $wp_error ) {
                return new WP_Error( 'empty_content', __( 'Content, title, and excerpt are empty.' ) );
            } else {
                return 0;
            }
        }
     
        $post_status = empty( $postarr['post_status'] ) ? 'draft' : $postarr['post_status'];
     
        if ( 'attachment' === $post_type && ! in_array( $post_status, array( 'inherit', 'private', 'trash', 'auto-draft' ), true ) ) {
            $post_status = 'inherit';
        }
     
        if ( ! empty( $postarr['post_category'] ) ) {
            // Filter out empty terms.
            $post_category = array_filter( $postarr['post_category'] );
        }
     
        // Make sure we set a valid category.
        if ( empty( $post_category ) || 0 === count( $post_category ) || ! is_array( $post_category ) ) {
            // 'post' requires at least one category.
            if ( 'post' === $post_type && 'auto-draft' !== $post_status ) {
                $post_category = array( get_option( 'default_category' ) );
            } else {
                $post_category = array();
            }
        }
     
        /*
         * Don't allow contributors to set the post slug for pending review posts.
         *
         * For new posts check the primitive capability, for updates check the meta capability.
         */
        $post_type_object = get_post_type_object( $post_type );
     
        if ( ! $update && 'pending' === $post_status && ! current_user_can( $post_type_object->cap->publish_posts ) ) {
            $post_name = '';
        } elseif ( $update && 'pending' === $post_status && ! current_user_can( 'publish_post', $post_ID ) ) {
            $post_name = '';
        }
     
        /*
         * Create a valid post name. Drafts and pending posts are allowed to have
         * an empty post name.
         */
        if ( empty( $post_name ) ) {
            if ( ! in_array( $post_status, array( 'draft', 'pending', 'auto-draft' ), true ) ) {
                $post_name = sanitize_title( $post_title );
            } else {
                $post_name = '';
            }
        } else {
            // On updates, we need to check to see if it's using the old, fixed sanitization context.
            $check_name = sanitize_title( $post_name, '', 'old-save' );
     
            if ( $update && strtolower( urlencode( $post_name ) ) == $check_name && get_post_field( 'post_name', $post_ID ) == $check_name ) {
                $post_name = $check_name;
            } else { // new post, or slug has changed.
                $post_name = sanitize_title( $post_name );
            }
        }
     
        /*
         * Resolve the post date from any provided post date or post date GMT strings;
         * if none are provided, the date will be set to now.
         */
        $post_date = wp_resolve_post_date( $postarr['post_date'], $postarr['post_date_gmt'] );
        if ( ! $post_date ) {
            if ( $wp_error ) {
                return new WP_Error( 'invalid_date', __( 'Invalid date.' ) );
            } else {
                return 0;
            }
        }
     
        if ( empty( $postarr['post_date_gmt'] ) || '0000-00-00 00:00:00' === $postarr['post_date_gmt'] ) {
            if ( ! in_array( $post_status, get_post_stati( array( 'date_floating' => true ) ), true ) ) {
                $post_date_gmt = get_gmt_from_date( $post_date );
            } else {
                $post_date_gmt = '0000-00-00 00:00:00';
            }
        } else {
            $post_date_gmt = $postarr['post_date_gmt'];
        }
     
        if ( $update || '0000-00-00 00:00:00' === $post_date ) {
            $post_modified     = current_time( 'mysql' );
            $post_modified_gmt = current_time( 'mysql', 1 );
        } else {
            $post_modified     = $post_date;
            $post_modified_gmt = $post_date_gmt;
        }
     
        if ( 'attachment' !== $post_type ) {
            $now = gmdate( 'Y-m-d H:i:s' );
     
            if ( 'publish' === $post_status ) {
                if ( strtotime( $post_date_gmt ) - strtotime( $now ) >= MINUTE_IN_SECONDS ) {
                    $post_status = 'future';
                }
            } elseif ( 'future' === $post_status ) {
                if ( strtotime( $post_date_gmt ) - strtotime( $now ) < MINUTE_IN_SECONDS ) {
                    $post_status = 'publish';
                }
            }
        }
     
        // Comment status.
        if ( empty( $postarr['comment_status'] ) ) {
            if ( $update ) {
                $comment_status = 'closed';
            } else {
                $comment_status = get_default_comment_status( $post_type );
            }
        } else {
            $comment_status = $postarr['comment_status'];
        }
     
        // These variables are needed by compact() later.
        $post_content_filtered = $postarr['post_content_filtered'];
        $post_author           = isset( $postarr['post_author'] ) ? $postarr['post_author'] : $user_id;
        $ping_status           = empty( $postarr['ping_status'] ) ? get_default_comment_status( $post_type, 'pingback' ) : $postarr['ping_status'];
        $to_ping               = isset( $postarr['to_ping'] ) ? sanitize_trackback_urls( $postarr['to_ping'] ) : '';
        $pinged                = isset( $postarr['pinged'] ) ? $postarr['pinged'] : '';
        $import_id             = isset( $postarr['import_id'] ) ? $postarr['import_id'] : 0;
     
        /*
         * The 'wp_insert_post_parent' filter expects all variables to be present.
         * Previously, these variables would have already been extracted
         */
        if ( isset( $postarr['menu_order'] ) ) {
            $menu_order = (int) $postarr['menu_order'];
        } else {
            $menu_order = 0;
        }
     
        $post_password = isset( $postarr['post_password'] ) ? $postarr['post_password'] : '';
        if ( 'private' === $post_status ) {
            $post_password = '';
        }
     
        if ( isset( $postarr['post_parent'] ) ) {
            $post_parent = (int) $postarr['post_parent'];
        } else {
            $post_parent = 0;
        }
     
        $new_postarr = array_merge(
            array(
                'ID' => $post_ID,
            ),
            compact( array_diff( array_keys( $defaults ), array( 'context', 'filter' ) ) )
        );
     
        /**
         * Filters the post parent -- used to check for and prevent hierarchy loops.
         *
         * @since 3.1.0
         *
         * @param int   $post_parent Post parent ID.
         * @param int   $post_ID     Post ID.
         * @param array $new_postarr Array of parsed post data.
         * @param array $postarr     Array of sanitized, but otherwise unmodified post data.
         */
        $post_parent = apply_filters( 'wp_insert_post_parent', $post_parent, $post_ID, $new_postarr, $postarr );
     
        /*
         * If the post is being untrashed and it has a desired slug stored in post meta,
         * reassign it.
         */
        if ( 'trash' === $previous_status && 'trash' !== $post_status ) {
            $desired_post_slug = get_post_meta( $post_ID, '_wp_desired_post_slug', true );
     
            if ( $desired_post_slug ) {
                delete_post_meta( $post_ID, '_wp_desired_post_slug' );
                $post_name = $desired_post_slug;
            }
        }
     
        // If a trashed post has the desired slug, change it and let this post have it.
        if ( 'trash' !== $post_status && $post_name ) {
            /**
             * Filters whether or not to add a `__trashed` suffix to trashed posts that match the name of the updated post.
             *
             * @since 5.4.0
             *
             * @param bool   $add_trashed_suffix Whether to attempt to add the suffix.
             * @param string $post_name          The name of the post being updated.
             * @param int    $post_ID            Post ID.
             */
            $add_trashed_suffix = apply_filters( 'add_trashed_suffix_to_trashed_posts', true, $post_name, $post_ID );
     
            if ( $add_trashed_suffix ) {
                wp_add_trashed_suffix_to_post_name_for_trashed_posts( $post_name, $post_ID );
            }
        }
     
        // When trashing an existing post, change its slug to allow non-trashed posts to use it.
        if ( 'trash' === $post_status && 'trash' !== $previous_status && 'new' !== $previous_status ) {
            $post_name = wp_add_trashed_suffix_to_post_name_for_post( $post_ID );
        }
     
        $post_name = wp_unique_post_slug( $post_name, $post_ID, $post_status, $post_type, $post_parent );
     
        // Don't unslash.
        $post_mime_type = isset( $postarr['post_mime_type'] ) ? $postarr['post_mime_type'] : '';
     
        // Expected_slashed (everything!).
        $data = compact( 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_content_filtered', 'post_title', 'post_excerpt', 'post_status', 'post_type', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_parent', 'menu_order', 'post_mime_type', 'guid' );
     
        $emoji_fields = array( 'post_title', 'post_content', 'post_excerpt' );
     
        foreach ( $emoji_fields as $emoji_field ) {
            if ( isset( $data[ $emoji_field ] ) ) {
                $charset = $wpdb->get_col_charset( $wpdb->posts, $emoji_field );
     
                if ( 'utf8' === $charset ) {
                    $data[ $emoji_field ] = wp_encode_emoji( $data[ $emoji_field ] );
                }
            }
        }
     
        if ( 'attachment' === $post_type ) {
            /**
             * Filters attachment post data before it is updated in or added to the database.
             *
             * @since 3.9.0
             * @since 5.4.1 `$unsanitized_postarr` argument added.
             *
             * @param array $data                An array of slashed, sanitized, and processed attachment post data.
             * @param array $postarr             An array of slashed and sanitized attachment post data, but not processed.
             * @param array $unsanitized_postarr An array of slashed yet *unsanitized* and unprocessed attachment post data
             *                                   as originally passed to wp_insert_post().
             */
            $data = apply_filters( 'wp_insert_attachment_data', $data, $postarr, $unsanitized_postarr );
        } else {
            /**
             * Filters slashed post data just before it is inserted into the database.
             *
             * @since 2.7.0
             * @since 5.4.1 `$unsanitized_postarr` argument added.
             *
             * @param array $data                An array of slashed, sanitized, and processed post data.
             * @param array $postarr             An array of sanitized (and slashed) but otherwise unmodified post data.
             * @param array $unsanitized_postarr An array of slashed yet *unsanitized* and unprocessed post data as
             *                                   originally passed to wp_insert_post().
             */
            $data = apply_filters( 'wp_insert_post_data', $data, $postarr, $unsanitized_postarr );
        }
     
        $data  = wp_unslash( $data );
        $where = array( 'ID' => $post_ID );
     
        if ( $update ) {
            /**
             * Fires immediately before an existing post is updated in the database.
             *
             * @since 2.5.0
             *
             * @param int   $post_ID Post ID.
             * @param array $data    Array of unslashed post data.
             */
            do_action( 'pre_post_update', $post_ID, $data );
     
            if ( false === $wpdb->update( $wpdb->posts, $data, $where ) ) {
                if ( $wp_error ) {
                    if ( 'attachment' === $post_type ) {
                        $message = __( 'Could not update attachment in the database.' );
                    } else {
                        $message = __( 'Could not update post in the database.' );
                    }
     
                    return new WP_Error( 'db_update_error', $message, $wpdb->last_error );
                } else {
                    return 0;
                }
            }
        } else {
            // If there is a suggested ID, use it if not already present.
            if ( ! empty( $import_id ) ) {
                $import_id = (int) $import_id;
     
                if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE ID = %d", $import_id ) ) ) {
                    $data['ID'] = $import_id;
                }
            }
     
            if ( false === $wpdb->insert( $wpdb->posts, $data ) ) {
                if ( $wp_error ) {
                    if ( 'attachment' === $post_type ) {
                        $message = __( 'Could not insert attachment into the database.' );
                    } else {
                        $message = __( 'Could not insert post into the database.' );
                    }
     
                    return new WP_Error( 'db_insert_error', $message, $wpdb->last_error );
                } else {
                    return 0;
                }
            }
     
            $post_ID = (int) $wpdb->insert_id;
     
            // Use the newly generated $post_ID.
            $where = array( 'ID' => $post_ID );
        }
     
        if ( empty( $data['post_name'] ) && ! in_array( $data['post_status'], array( 'draft', 'pending', 'auto-draft' ), true ) ) {
            $data['post_name'] = wp_unique_post_slug( sanitize_title( $data['post_title'], $post_ID ), $post_ID, $data['post_status'], $post_type, $post_parent );
     
            $wpdb->update( $wpdb->posts, array( 'post_name' => $data['post_name'] ), $where );
            clean_post_cache( $post_ID );
        }
     
        if ( is_object_in_taxonomy( $post_type, 'category' ) ) {
            wp_set_post_categories( $post_ID, $post_category );
        }
     
        if ( isset( $postarr['tags_input'] ) && is_object_in_taxonomy( $post_type, 'post_tag' ) ) {
            wp_set_post_tags( $post_ID, $postarr['tags_input'] );
        }
     
        // Add default term for all associated custom taxonomies.
        if ( 'auto-draft' !== $post_status ) {
            foreach ( get_object_taxonomies( $post_type, 'object' ) as $taxonomy => $tax_object ) {
     
                if ( ! empty( $tax_object->default_term ) ) {
     
                    // Filter out empty terms.
                    if ( isset( $postarr['tax_input'][ $taxonomy ] ) && is_array( $postarr['tax_input'][ $taxonomy ] ) ) {
                        $postarr['tax_input'][ $taxonomy ] = array_filter( $postarr['tax_input'][ $taxonomy ] );
                    }
     
                    // Passed custom taxonomy list overwrites the existing list if not empty.
                    $terms = wp_get_object_terms( $post_ID, $taxonomy, array( 'fields' => 'ids' ) );
                    if ( ! empty( $terms ) && empty( $postarr['tax_input'][ $taxonomy ] ) ) {
                        $postarr['tax_input'][ $taxonomy ] = $terms;
                    }
     
                    if ( empty( $postarr['tax_input'][ $taxonomy ] ) ) {
                        $default_term_id = get_option( 'default_term_' . $taxonomy );
                        if ( ! empty( $default_term_id ) ) {
                            $postarr['tax_input'][ $taxonomy ] = array( (int) $default_term_id );
                        }
                    }
                }
            }
        }
     
        // New-style support for all custom taxonomies.
        if ( ! empty( $postarr['tax_input'] ) ) {
            foreach ( $postarr['tax_input'] as $taxonomy => $tags ) {
                $taxonomy_obj = get_taxonomy( $taxonomy );
     
                if ( ! $taxonomy_obj ) {
                    /* translators: %s: Taxonomy name. */
                    _doing_it_wrong( __FUNCTION__, sprintf( __( 'Invalid taxonomy: %s.' ), $taxonomy ), '4.4.0' );
                    continue;
                }
     
                // array = hierarchical, string = non-hierarchical.
                if ( is_array( $tags ) ) {
                    $tags = array_filter( $tags );
                }
     
                if ( current_user_can( $taxonomy_obj->cap->assign_terms ) ) {
                    wp_set_post_terms( $post_ID, $tags, $taxonomy );
                }
            }
        }
     
        if ( ! empty( $postarr['meta_input'] ) ) {
            foreach ( $postarr['meta_input'] as $field => $value ) {
                update_post_meta( $post_ID, $field, $value );
            }
        }
     
        $current_guid = get_post_field( 'guid', $post_ID );
     
        // Set GUID.
        if ( ! $update && '' === $current_guid ) {
            $wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $post_ID ) ), $where );
        }
     
        if ( 'attachment' === $postarr['post_type'] ) {
            if ( ! empty( $postarr['file'] ) ) {
                update_attached_file( $post_ID, $postarr['file'] );
            }
     
            if ( ! empty( $postarr['context'] ) ) {
                add_post_meta( $post_ID, '_wp_attachment_context', $postarr['context'], true );
            }
        }
     
        // Set or remove featured image.
        if ( isset( $postarr['_thumbnail_id'] ) ) {
            $thumbnail_support = current_theme_supports( 'post-thumbnails', $post_type ) && post_type_supports( $post_type, 'thumbnail' ) || 'revision' === $post_type;
     
            if ( ! $thumbnail_support && 'attachment' === $post_type && $post_mime_type ) {
                if ( wp_attachment_is( 'audio', $post_ID ) ) {
                    $thumbnail_support = post_type_supports( 'attachment:audio', 'thumbnail' ) || current_theme_supports( 'post-thumbnails', 'attachment:audio' );
                } elseif ( wp_attachment_is( 'video', $post_ID ) ) {
                    $thumbnail_support = post_type_supports( 'attachment:video', 'thumbnail' ) || current_theme_supports( 'post-thumbnails', 'attachment:video' );
                }
            }
     
            if ( $thumbnail_support ) {
                $thumbnail_id = (int) $postarr['_thumbnail_id'];
                if ( -1 === $thumbnail_id ) {
                    delete_post_thumbnail( $post_ID );
                } else {
                    set_post_thumbnail( $post_ID, $thumbnail_id );
                }
            }
        }
     
        clean_post_cache( $post_ID );
     
        $post = get_post( $post_ID );
     
        if ( ! empty( $postarr['page_template'] ) ) {
            $post->page_template = $postarr['page_template'];
            $page_templates      = wp_get_theme()->get_page_templates( $post );
     
            if ( 'default' !== $postarr['page_template'] && ! isset( $page_templates[ $postarr['page_template'] ] ) ) {
                if ( $wp_error ) {
                    return new WP_Error( 'invalid_page_template', __( 'Invalid page template.' ) );
                }
     
                update_post_meta( $post_ID, '_wp_page_template', 'default' );
            } else {
                update_post_meta( $post_ID, '_wp_page_template', $postarr['page_template'] );
            }
        }
     
        if ( 'attachment' !== $postarr['post_type'] ) {
            wp_transition_post_status( $data['post_status'], $previous_status, $post );
        } else {
            if ( $update ) {
                /**
                 * Fires once an existing attachment has been updated.
                 *
                 * @since 2.0.0
                 *
                 * @param int $post_ID Attachment ID.
                 */
                do_action( 'edit_attachment', $post_ID );
     
                $post_after = get_post( $post_ID );
     
                /**
                 * Fires once an existing attachment has been updated.
                 *
                 * @since 4.4.0
                 *
                 * @param int     $post_ID      Post ID.
                 * @param WP_Post $post_after   Post object following the update.
                 * @param WP_Post $post_before  Post object before the update.
                 */
                do_action( 'attachment_updated', $post_ID, $post_after, $post_before );
            } else {
     
                /**
                 * Fires once an attachment has been added.
                 *
                 * @since 2.0.0
                 *
                 * @param int $post_ID Attachment ID.
                 */
                do_action( 'add_attachment', $post_ID );
            }
     
            return $post_ID;
        }
     
        if ( $update ) {
            /**
             * Fires once an existing post has been updated.
             *
             * The dynamic portion of the hook name, `$post->post_type`, refers to
             * the post type slug.
             *
             * @since 5.1.0
             *
             * @param int     $post_ID Post ID.
             * @param WP_Post $post    Post object.
             */
            do_action( "edit_post_{$post->post_type}", $post_ID, $post );
     
            /**
             * Fires once an existing post has been updated.
             *
             * @since 1.2.0
             *
             * @param int     $post_ID Post ID.
             * @param WP_Post $post    Post object.
             */
            do_action( 'edit_post', $post_ID, $post );
     
            $post_after = get_post( $post_ID );
     
            /**
             * Fires once an existing post has been updated.
             *
             * @since 3.0.0
             *
             * @param int     $post_ID      Post ID.
             * @param WP_Post $post_after   Post object following the update.
             * @param WP_Post $post_before  Post object before the update.
             */
            do_action( 'post_updated', $post_ID, $post_after, $post_before );
        }
     
        /**
         * Fires once a post has been saved.
         *
         * The dynamic portion of the hook name, `$post->post_type`, refers to
         * the post type slug.
         *
         * @since 3.7.0
         *
         * @param int     $post_ID Post ID.
         * @param WP_Post $post    Post object.
         * @param bool    $update  Whether this is an existing post being updated.
         */
        do_action( "save_post_{$post->post_type}", $post_ID, $post, $update );
     
        /**
         * Fires once a post has been saved.
         *
         * @since 1.5.0
         *
         * @param int     $post_ID Post ID.
         * @param WP_Post $post    Post object.
         * @param bool    $update  Whether this is an existing post being updated.
         */
        do_action( 'save_post', $post_ID, $post, $update );
     
        /**
         * Fires once a post has been saved.
         *
         * @since 2.0.0
         *
         * @param int     $post_ID Post ID.
         * @param WP_Post $post    Post object.
         * @param bool    $update  Whether this is an existing post being updated.
         */
        do_action( 'wp_insert_post', $post_ID, $post, $update );
     
        if ( $fire_after_hooks ) {
            wp_after_insert_post( $post, $update, $post_before );
        }
     
        return $post_ID;
    }

    

}