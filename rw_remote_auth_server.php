<?php

/**
* Plugin Name:      RW Remote Auth Server
* Plugin URI:       https://github.com/rpi-virtuell/rw_remote_auth_server
* Description:
* Author:           Frank Staude
* Version:          0.1.9
* Licence:          GPLv3
* Author URI:       http://staude.net
* Text Domain:      rw_remote_auth_server
* Domain Path:      /languages
 * GitHub Plugin URI: https://github.com/rpi-virtuell/rw-remote-auth-server
 * GitHub Branch:     master
 * Last Change:       11.02.2016 17:52
*/

class RW_Remote_Auth_Server {
	/**
	 * Plugin version
	 *
	 * @var     string
	 * @since   0.1
	 * @access  public
	 */
	static public $version = "0.1.9";

	/**
	 * Singleton object holder
	 *
	 * @var     mixed
	 * @since   0.1
	 * @access  private
	 */
	static private $instance = NULL;

	/**
	 * @var     mixed
	 * @since   0.1
	 * @access  public
	 */
	static public $plugin_name = NULL;

	/**
	 * @var     mixed
	 * @since   0.1
	 * @access  public
	 */
	static public $textdomain = NULL;

	/**
	 * @var     mixed
	 * @since   0.1
	 * @access  public
	 */
	static public $plugin_base_name = NULL;

	/**
	 * @var     mixed
	 * @since   0.1
	 * @access  public
	 */
	static public $plugin_url = NULL;

	/**
	 * @var     string
	 * @since   0.1
	 * @access  public
	 */
	static public $plugin_filename = __FILE__;

	/**
	 * @var     string
	 * @since   0.1
	 * @access  public
	 */
	static public $plugin_version = '';

	/**
	 * @var     string
	 * @since   0.1
	 * @access  public
	 */
	static public $api_endpoint_default = '/rw_auth';

	static public $cookie_name = 'rw_auth_pw_change';

	static public $plugin_dir_name = '';

	/**
	 * Plugin constructor.
	 *
	 * @since   0.1
	 * @access  public
	 * @uses    plugin_basename
	 * @action  rw_remote_auth_server_init
	 */
	public function __construct () {
		// set the textdomain variable
		self::$textdomain = self::get_textdomain();

		// The Plugins Name
		self::$plugin_name = $this->get_plugin_header( 'Name' );

		// The Plugins Basename
		self::$plugin_base_name = plugin_basename( __FILE__ );

		// The Plugins Version
		self::$plugin_version = $this->get_plugin_header( 'Version' );

		self::$plugin_dir_name = dirname( plugin_basename( __FILE__ ) );

		// Load the textdomain
		$this->load_plugin_textdomain();

		// Add Filter & Actions

		add_action( 'admin_init',       array( 'RW_Remote_Auth_Server_Options', 'register_settings' ) );
		add_action( 'admin_menu',       array( 'RW_Remote_Auth_Server_Options', 'options_menu' ) );
		add_action( 'init',             array( 'RW_Remote_Auth_Server_API', 'add_endpoint'), 0 );
		add_action( 'parse_request',    array( 'RW_Remote_Auth_Server_API', 'parse_request'), 0 );
		add_action( 'rw_auth_check_server', array( 'RW_Remote_Auth_Server_Installation', 'check_server') );

		add_filter( 'plugin_action_links_' . self::$plugin_base_name, array( 'RW_Remote_Auth_Server_Options', 'plugin_settings_link') );
		add_filter( 'query_vars',       array( 'RW_Remote_Auth_Server_API', 'add_query_vars'), 0 );

        add_filter( 'rw_remote_auth_server_cmd_parser', array( 'RW_Remote_Auth_Server_API', 'cmd_user_exists' ) );
        add_filter( 'rw_remote_auth_server_cmd_parser', array( 'RW_Remote_Auth_Server_API', 'cmd_user_auth' ) );
        add_filter( 'rw_remote_auth_server_cmd_parser', array( 'RW_Remote_Auth_Server_API', 'cmd_user_create' ) );
		add_filter( 'rw_remote_auth_server_cmd_parser', array( 'RW_Remote_Auth_Server_API', 'cmd_user_password_change' ) );
		add_filter( 'rw_remote_auth_server_cmd_parser', array( 'RW_Remote_Auth_Server_API', 'cmd_user_get_password' ) );
		add_filter( 'rw_remote_auth_server_cmd_parser', array( 'RW_Remote_Auth_Server_API', 'cmd_ping' ) );

		add_filter( 'register_url', array( 'RW_Remote_Auth_Server_Helper', 'register_url' ) );
		add_filter( 'lostpassword_url', array( 'RW_Remote_Auth_Server_Helper', 'lostpassword_url' ) );
		add_filter( 'login_url', array( 'RW_Remote_Auth_Server_Helper', 'login_url' ) );
		add_filter( 'site_url', array( 'RW_Remote_Auth_Server_Helper', 'site_url' ) );

		add_action( 'resetpass_form', array( 'RW_Remote_Auth_Server_Helper', 'resetpass_form') );
		add_filter( 'login_message',  array( 'RW_Remote_Auth_Server_Helper', 'login_message') );
		add_filter( 'retrieve_password_message', array( 'RW_Remote_Auth_Server_Helper', 'retrieve_password_message') );
		add_action( 'login_init', array ( 'RW_Remote_Auth_Server_Helper', 'pw_change_js' ) );
		do_action( 'rw_remote_auth_server_init' );
	}

	/**
	 * Creates an Instance of this Class
	 *
	 * @since   0.1
	 * @access  public
	 * @return  RW_Remote_Auth_Server
	 */
	public static function get_instance() {

		if ( NULL === self::$instance )
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Load the localization
	 *
	 * @since	0.1
	 * @access	public
	 * @uses	load_plugin_textdomain, plugin_basename
	 * @filters rw_remote_auth_server_translationpath path to translations files
	 * @return	void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( self::get_textdomain(), false, apply_filters ( 'rw_remote_auth_server_translationpath', dirname( plugin_basename( __FILE__ )) .  self::get_textdomain_path() ) );
	}

	/**
	 * Get a value of the plugin header
	 *
	 * @since   0.1
	 * @access	protected
	 * @param	string $value
	 * @uses	get_plugin_data, ABSPATH
	 * @return	string The plugin header value
	 */
	protected function get_plugin_header( $value = 'TextDomain' ) {

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php');
		}

		$plugin_data = get_plugin_data( __FILE__ );
		$plugin_value = $plugin_data[ $value ];

		return $plugin_value;
	}

	/**
	 * get the textdomain
	 *
	 * @since   0.1
	 * @static
	 * @access	public
	 * @return	string textdomain
	 */
	public static function get_textdomain() {
		if( is_null( self::$textdomain ) )
			self::$textdomain = self::get_plugin_data( 'TextDomain' );

		return self::$textdomain;
	}

	/**
	 * get the textdomain path
	 *
	 * @since   0.1
	 * @static
	 * @access	public
	 * @return	string Domain Path
	 */
	public static function get_textdomain_path() {
		return self::get_plugin_data( 'DomainPath' );
	}

	/**
	 * return plugin comment data
	 *
	 * @since   0.1
	 * @uses    get_plugin_data
	 * @access  public
	 * @param   $value string, default = 'Version'
	 *		Name, PluginURI, Version, Description, Author, AuthorURI, TextDomain, DomainPath, Network, Title
	 * @return  string
	 */
	public static function get_plugin_data( $value = 'Version' ) {

		if ( ! function_exists( 'get_plugin_data' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		$plugin_data  = get_plugin_data ( __FILE__ );
		$plugin_value = $plugin_data[ $value ];

		return $plugin_value;
	}
}


if ( class_exists( 'RW_Remote_Auth_Server' ) ) {

	add_action( 'plugins_loaded', array( 'RW_Remote_Auth_Server', 'get_instance' ) );

	require_once 'inc/RW_Remote_Auth_Server_Autoloader.php';
	RW_Remote_Auth_Server_Autoloader::register();

	register_activation_hook( __FILE__, array( 'RW_Remote_Auth_Server_Installation', 'on_activate' ) );
	register_uninstall_hook(  __FILE__,	array( 'RW_Remote_Auth_Server_Installation', 'on_uninstall' ) );
    register_deactivation_hook( __FILE__, array( 'RW_Remote_Auth_Server_Installation', 'on_deactivation' ) );
}
