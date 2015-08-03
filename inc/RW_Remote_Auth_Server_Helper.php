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
							$url = $target['scheme'] . '://' . $target['host'] . '/register';
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
	 * @return  string
	 */
	static public function home_url( $url ) {

		if ( isset( $_REQUEST[ 'redirect_to' ] ) ) {
			if ( isset( $_GET[ 'redirect_to' ] ) ) {
				$parseurl = parse_url( $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
				$pairs = explode( '&', $parseurl ['query'] );
				foreach ( $pairs as $pair ) {
					$keyVal = explode( '=', $pair );
					$key    = &$keyVal[0];
					$val    = urldecode( $keyVal[1] );
					if ( $key == 'redirect_to' ) {
						$parseurl2 = parse_url( $val );
						$pairs2    = explode( '&', $parseurl2 ['query'] );
						foreach ( $pairs2 as $pair2 ) {
							$keyVal2 = explode( '=', $pair2 );
							$key2    = &$keyVal2[0];
							$val2    = urldecode( $keyVal2[1] );
							if ( $key2 == 'service' ) {
								$target = parse_url( $val2 );
								$url    = $target['scheme'] . '://' . $target['host'];
							}
						}
					}
				}
			}
			if ( isset( $_POST[ 'redirect_to' ] ) ) {
				$parseurl = parse_url( urldecode( $_POST[ 'redirect_to' ] ) );
				$pairs = explode( '&', $parseurl ['query'] );
				foreach ( $pairs as $pair ) {
					$keyVal = explode( '=', $pair );
					$key    = &$keyVal[0];
					$val    = urldecode( $keyVal[1] );
					if ( $key == 'service' ) {
						$target = parse_url( $val );
						if ( !isset(  $_POST[ 'log' ] ) ) {
							$url    = $target['scheme'] . '://' . $target['host'];
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
		$pairs = explode('&', $parseurl [ 'query'] );
		foreach ($pairs as $pair) {
			$keyVal = explode('=',$pair);
			$key = &$keyVal[0];
			$val = urldecode($keyVal[1]);
			if ( $key == 'action' && ( $val == 'rp'  ) ) {
				$url =  add_query_arg( 'redirect_to', urlencode( $_POST[ 'redirect_to'] ), $url );
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

}