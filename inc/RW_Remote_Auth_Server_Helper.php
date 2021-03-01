<?php

/**
 * Class RW_Remote_Auth_Server_Helper
 *
 * @since   0.1.4
 */
class RW_Remote_Auth_Server_Helper {

	/**
	 *
	 * @since   0.1.4
	 * @param   $url
	 *
	 * @return  string
	 */
	static public function register_url( $url ) {
		if ( isset( $_REQUEST[ 'redirect_to' ] ) ) {
			$parseurl = parse_url(  $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
			$pairs = explode('&', $parseurl [ 'query'] );
			foreach ($pairs as $pair) {
				$keyVal = explode('=',$pair);
				$key = &$keyVal[0];
				$val = urldecode($keyVal[1]);
				if ( $key == 'redirect_to' ) {
					$parseurl2 = parse_url( $val );
					$pairs2 = explode('&', $parseurl2 [ 'query'] );
					foreach ($pairs2 as $pair2) {
						$keyVal2 = explode('=',$pair2);
						$key2 = &$keyVal2[0];
						$val2 = urldecode($keyVal2[1]);
						if ( $key2 == 'service' ) {
							$target = parse_url ($val2);
							//$url = $target['scheme'] . '://' . $target['host'] . '/register';
							$url = $url.'?ref_service='.$target['scheme'] . '://' . $target['host'] . '/register';
							
							
						}
					}
				}
			}
		}
		return $url;
	}


	/**
	 *
	 * @since   0.1.4
	 * @param   $url
	 *
	 * @return  mixed
	 */
	static public function lostpassword_url( $url ) {
		if ( isset( $_REQUEST[ 'redirect_to' ] ) ) {
			$url = add_query_arg( 'redirect_to', urlencode( $_REQUEST[ 'redirect_to'] ), $url );
		}
		return $url;
	}

	/**
	 *
	 * @since   0.1.4
	 * @param   $url
	 *
	 * @return  mixed
	 */
	static public function login_url( $url ) {
		if ( isset( $_POST[ 'redirect_to' ] ) ) {
			$url =  add_query_arg( 'redirect_to', ( $_POST[ 'redirect_to'] ), $url );
		} elseif ( isset( $_GET[ 'redirect_to' ] ) ) {
			$parseurl = parse_url(  $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
			$pairs = explode('&', $parseurl [ 'query'] );
			foreach ($pairs as $pair) {
				$keyVal = explode('=',$pair);
				$key = &$keyVal[0];
				$val = urldecode($keyVal[1]);
				if ( $key == 'redirect_to' ) {
					$parseurl2 = parse_url( $val );
					$pairs2 = explode('&', $parseurl2 [ 'query'] );
					foreach ($pairs2 as $pair2) {
						$keyVal2 = explode('=',$pair2);
						$key2 = &$keyVal2[0];
						if ( $key2 == 'service' ) {
							$url =  add_query_arg( 'redirect_to', ( $_REQUEST[ 'redirect_to'] ), site_url( '/wp-login.php' ) );
						}
					}
				}
			}
		}
		return $url;
	}


	/**
	 * add redirect_to param to site url
	 *
	 * @param $url
	 *
	 * @return string
	 */
	static public function site_url( $url ) {
		$parseurl = parse_url( $url );
		if ( isset( $parseurl [ 'query'] ) ) {
			$pairs = explode('&', $parseurl ['query']);
			foreach ($pairs as $pair) {
				$keyVal = explode('=', $pair);
				if(!isset($keyVal[1])){
					$keyVal[1]='';
				}
				$key = &$keyVal[0];
				$val = urldecode($keyVal[1]);
				if ($key == 'action' && ($val == 'rp')) {
					$url = add_query_arg('redirect_to', urlencode($_POST['redirect_to'] . "/wp-admin"), $url);
				}
			}
		}
		return $url;
	}

	/**
	 * adds hidden redirect_to field to reset password form
	 *
	 * @since   0.1.4
	 */
	static public function resetpass_form() {
		if ( isset( $_REQUEST[ 'redirect_to' ] ) ) {
			echo '<input type="hidden" name="redirect_to" value="' . urlencode(  $_REQUEST[ 'redirect_to' ] ) . '" />';
		}
	}

	/**
	 *
	 *
	 * @since   0.1.5
	 */
	static public function login_message( $message ) {
		if ( isset( $_REQUEST[ 'reauth' ] )  &&  $_REQUEST[ 'reauth' ] == 1 && isset( $_COOKIE[ RW_Remote_Auth_Server::$cookie_name ] ) && $_COOKIE[ RW_Remote_Auth_Server::$cookie_name ] == 1 ) {
			setcookie( RW_Remote_Auth_Server::$cookie_name, null, time()- ( 60 * 60 )  );
			$message .= '<p class="message">' .  __( 'You will receive a link to create a new password via email.', RW_Remote_Auth_Server::$textdomain ) . "</p>";
		}
		return $message;
	}

	/**
	 *
	 *
	 * @since   0.1.5
	 */
	static public function retrieve_password_message( $message ) {
		setcookie( RW_Remote_Auth_Server::$cookie_name, 1, time()+ ( 24 * 60 * 60 )  );
		return $message;
	}

	/**
	 *
	 * @since   0.1.5
	 */
	static public function pw_change_js() {
		if ( isset( $_POST['redirect_to'] ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'resetpass' ) {
			wp_enqueue_script( 'jQuery','https://login.reliwerk.de/wp-includes/js/jquery/jquery.js?ver=1.11.2' );

			wp_enqueue_script( 'rw_auth_pw_change', plugins_url( RW_Remote_Auth_Server::$plugin_dir_name . '/js/pw_change.js' ), array( 'jQuery') );
		}
		if ( isset( $_REQUEST['redirect_to'] ) ) {
			wp_enqueue_script( 'jQuery','https://login.reliwerk.de/wp-includes/js/jquery/jquery.js?ver=1.11.2' );
			wp_enqueue_script( 'cookie_js', plugins_url( RW_Remote_Auth_Server::$plugin_dir_name . '/js/js.cookie.js' ), array( 'jQuery') );
			wp_enqueue_script( 'rw_auth_backlink_change', plugins_url( RW_Remote_Auth_Server::$plugin_dir_name . '/js/backlink_change.js' ), array( 'jQuery', 'cookie_js') );
		}
	}

	/**
	 *
	 * @since   0.2.3
	 */
	static public function log_login( $login, $user) {
		$host = "local";
		if ( isset( $_POST['redirect_to'] ) ) {
			$host = $_POST['redirect_to'];
		}
		// write login and host to usermeta
		update_user_meta( $user->ID, 'rw_auth_server_lastlogin', time() );
		update_user_meta( $user->ID, 'rw_auth_server_host', $host );
		openlog("rpi-login", LOG_NDELAY, LOG_LOCAL2);
		syslog(LOG_ERR, $login. ";" . $host );
		closelog();

	}

	/**
	 *
	 * @since   0.2.3
	 */
	static public function manage_users_columns( $column_headers) {
		$column_headers['lastlogin'] = __( 'Last Login', RW_Remote_Auth_Server::$textdomain) ;
		$column_headers['host'] = __( 'Host', RW_Remote_Auth_Server::$textdomain) ;
		return $column_headers;
	}

	/**
	 *
	 * @since   0.2.3
	 */
	static public function manage_users_columns_data($value, $column_name, $user_id) {
		if ($column_name == 'lastlogin' ) {
			$return = '';
			$meta = get_user_meta( $user_id, 'rw_auth_server_lastlogin', true );
			if ( $meta != '') {
				$return = date ( "d.m.Y H:i:s" , $meta);
			}
			return  $return;
		}
		if ($column_name == 'host' ) {
			return get_user_meta( $user_id, 'rw_auth_server_host', true );
		}
		return $value;

	}

}