<?php

/**
 * Class RW_Remote_Auth_Server_Options
 *
 * Manage APY-Keys for  RW_Remote_Auth_Clients
 *
 * Author: Joachim Happel
 * Date: 26.02.2016
 * Time: 11:09
 *
 * @since 0.2.0
 */
class RW_Remote_Auth_Server_Clients
{

    public static $post_type = 'rw_authclientkey';
    public static $post_type_slug = false;
        /**
     *
     * @useaction init
     */
    public static function init(){

        /* MUST be called on the init action/callback (will not work properly if not called at right time/place */

        add_action('init', array('RW_Remote_Auth_Server_Clients' , 'create_apikey_cpt' ) );

    }

    /**
    * get clients domain
    */
    static public function detect_server_client(){

        $str = $_SERVER['HTTP_USER_AGENT'];
        $str .= "|";
        $str .= $_SERVER['REMOTE_ADDR'];
        $str .= "\n";
            file_put_contents( RW_Remote_Auth_Server::$plugin_dir.'/clients.log', $str,FILE_APPEND );
        //die();
    }

    /**
     * Create custom Post Type to stor API Keys
     */
     static public function create_apikey_cpt(){
        $args = array(
            'labels' => array(
                'name' => __('Server Clients'),
                'singular_name' => __('Server Client')
            ),
            'public' => true, /* shows in admin on left menu etc */
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'menu_position' => 76,
            'supports' => array( 'title','excerpt' ),

            //'rewrite' => array('slug' => 'rw_authclientkey'), /* rewrite not usefull in this case */
        );

        register_post_type( self::$post_type, $args);

    }

    /**
     *  run on deactivation (however it doesn't seem to delete custom posts )
     */
    static public function uninstall_apikey_cpt() {

        global $wp_post_types;

        $post_type = self::$post_type;
        $slug = self::$post_type_slug;

        if (isset($wp_post_types[$post_type])) {

            unset($wp_post_types[$post_type]);

            $slug = (!$slug ) ? 'edit.php?post_type=' . $post_type : $slug;
            remove_menu_page($slug);
        }

    }

}