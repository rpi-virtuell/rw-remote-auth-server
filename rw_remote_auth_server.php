<?php

/**
* Plugin Name:      RW Remote Auth Server
* Plugin URI:       https://github.com/rpi-virtuell/rw_remote_auth_server
* Description:
* Author:           Frank Staude
* Version:          0.1
* Licence:          GPLv3
* Author URI:       http://staude.net
* Text Domain:      rw_remote_auth_server
* Domain Path:      /languages
* Last Change:      02.06.2015 13:20
*/

class RW_Remote_Auth_Server {
	/**
	 * Plugin version
	 *
	 * @var     string
	 * @since   0.1
	 * @access  public
	 */
	static public $version = "0.1";

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
	 * Plugin constructor.
	 *
	 * @since   0.1
	 * @access  public
	 * @uses    plugin_basename
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

		// Load the textdomain
		$this->load_plugin_textdomain();

		// Add Filter & Actions

		//add_action( '', array( '', '' ) );
		//add_filter( '', array( '', '' ), 10, 2 );

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
		load_plugin_textdomain( self::get_textdomain(), FALSE,false, apply_filters ( 'rw_remote_auth_server_translationpath', dirname( plugin_basename( __FILE__ )) .  self::get_textdomain_path() ) );
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
}