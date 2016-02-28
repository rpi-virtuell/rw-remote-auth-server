<?php

/**
 * Class RW_Remote_Auth_Server_Options
 *
 * Contains code for API
 *
 */

class RW_Remote_Auth_Server_API {

	public static $min_client_version = '0.2.0';
	public static $client_class = 'RW_Remote_Auth_Client';

	/**
     * Add API Endpoint
	 *
     * @since   0.1
     * @access  public
     * @static
	 * @return void
	 */
	static public function add_endpoint() {
        $endpoint = RW_Remote_Auth_Server_Options::get_endpoint();
        //var_dump($endpoint);exit;
        add_rewrite_rule( '^'. $endpoint .'/([^/]*)/?', 'index.php/?__rwrasapi=1&data=$1', 'top');
        flush_rewrite_rules();
	}

    /**
     *
     * @since   0.1
     * @access  public
     * @static
	 * @param $vars *
	 * @return array
	 */
	static public function add_query_vars( $vars ) {
		$vars[] = '__rwrasapi';
		$vars[] = 'data';
		return $vars;
	}

	/**	Sniff Requests
	 *	This is where we hijack all API requests
	 * 	If $_GET['__api'] is set, we kill WP and serve up pug bomb awesomeness
	 *	@return die if API request
     *
     * @since   0.1
     * @access  public
     * @static
     */
	static public function parse_request(){
		global $wp;
		if( isset( $wp->query_vars[ '__rwrasapi' ] ) ) {
			RW_Remote_Auth_Server_API::handle_request();
			exit;
		}
	}

	static public function log($content){
		if ( WP_DEBUG ) {
			file_put_contents( RW_Remote_Auth_Server::$plugin_dir.'/clients.log' , $content ."\n",FILE_APPEND );
		}
	}


	static public function is_validate_trustet_client($whitelisted = false){

		$trusted_client =  $error = false;
		$error_msg = 'Error: ';

		date_default_timezone_set('Europe/Berlin');

		//write to logfile;
		$str = '['.date('Y-m-d H:i:s').'] '.$_SERVER['REMOTE_ADDR'];

		self::log($str);

		$user_agent = explode(';',$_SERVER['HTTP_USER_AGENT']);
		if(count($user_agent) < 4){
			self::log( 'invalid user agent .'.$_SERVER['HTTP_USER_AGENT'] );
			$error_msg .= 'Invalid user agent. '.$_SERVER['HTTP_USER_AGENT'];
			$error = true;
		}else{
			list($version,$host,$ip,$apikey)= $user_agent;

			$client_arr = explode(' ',$version);
			if( count($client_arr) < 2){
				log ( ' invalid arguments in client version ' );
				$error_msg .= 'Invalid arguments in client version ';
				$error = true;
			}else{
				list($client_class,$release) = $client_arr;
				if(!version_compare($release,self::$min_client_version,'<=')){
					self::log ( 'too old client version ' . $release);
					$error_msg .= 'You use a deprecated client version. Please update your RW Remote Auth Client plugin. ' . $release;
					$error = true;
				}
			}
			$varstr = $version.'|'.$host.'|'.$ip.'|'.$apikey.'|'.$client_class.'|'.$release;
			self::log($varstr);
		}





		if( !$error && $ip  == $_SERVER['REMOTE_ADDR'] && self::$client_class = $client_class ){

			global $wpdb;

			$whitelist = get_option( 'rw_remote_auth_server_options_whitelist');
			$whitelist = str_replace(' ','' ,$whitelist);
			$whitelist = str_replace("\r",'' ,$whitelist);
			$whitelist = str_replace("\l",'|' ,$whitelist);
			$whitelist = str_replace("\n",'|' ,$whitelist);

			$whitelist_arr = explode("|",$whitelist);


			//is client from a trusted host or ip ?
			if(in_array($ip ,$whitelist_arr ) || in_array($host ,$whitelist_arr ) ){

				self::log('trusted host '.$host);
				if($whitelisted === true){

					return true;
				}

				//validate the clients api_key
				$host =  $wpdb->_real_escape($host);
				$dbkey = $wpdb->get_var("select post_excerpt from $wpdb->posts where post_title = '".$host."' and post_type='rw_authclientkey'");

				if(!empty($dbkey) && $dbkey == $apikey){
					self::log('trusted client '.$dbkey);
					return true;
				}else{
					self::log('invalid apikey');
					$error_msg .= 'Invalid Api Key. Go to setting page of your RW Remote Auth Client and update it with a valid Api Key!';
					$error = true;
				}

			}else{
				$error = true;
				$error_msg .= 'Your host ist not allowed so connect to this CAS - Server';
			}
		}else{
			$error_msg .= '';
		}
		return new WP_Error( 'rw_auth_server_error',$error_msg );

	}

	/** Handle Requests
	 *	This is where we send off for an intense pug bomb package
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  void
     * @todo    sanitize input data
	 */
	static protected function handle_request(){
		global $wp;


		$request = json_decode( stripslashes( $wp->query_vars[ 'data' ] ) );
		if( ! $request || !isset($request->cmd) || !isset($request->data) ) {
            RW_Remote_Auth_Server_API::send_response('Please send commands in json.');
        } else {
            apply_filters( 'rw_remote_auth_server_cmd_parser', $request );
        }
	}


    /**
     *
     * @param $msg
     * @param string $data
     */
	static protected function send_response($msg, $data = ''){
		$response[ 'message' ] = $msg;
		if( $data ) {
			$response['data'] = $data;
        }
		header('content-type: application/json; charset=utf-8');
		echo json_encode( $response )."\n";
		exit;
	}

    /**
     *
     * @hook    rw_remote_auth_server_cmd_parser
     * @param   $request
     * @return  mixed
     */
    static public function cmd_user_exists( $request ) {
        if ( 'user_exists' == $request->cmd ) {
            $answer = username_exists( $request->data->user_name ) ? true : false;
            RW_Remote_Auth_Server_API::send_response( $answer );
        }
        return $request;
    }

	/**
     *
     * @hook    rw_remote_auth_server_cmd_parser
     * @param   $request
     * @return  mixed
     */
    static public function cmd_user_get_details( $request ) {
		if ( 'user_get_details' == $request->cmd ){

			$valid = self::is_validate_trustet_client();
			if(is_wp_error($valid)){
				$error_string = $valid->get_error_message();
				RW_Remote_Auth_Server_API::send_response( 'user', array('error'=>$error_string) );
				return $request;

			}

			self::log('cmd_user_get_details: '.$request->data->user_name);

			if(isset($request->data->user_name)) {
				$user = false;
				if (false !== strpos($request->data->user_name, '@')) {
					$user = get_user_by('email', $request->data->user_name);
				}
				if (!$user) {
					$user = get_user_by('login', $request->data->user_name);
				}
				if ($user) {
					$user_details['exists'] = true;
					$user_details['user_login'] = $user->user_login;
					$user_details['user_email'] = $user->user_email;
					$user_details['user_password'] = $user->user_pass;
					$user_details['user_nicename'] = $user->user_nicename;

					self::log('return: '.json_encode($user_details) );

					RW_Remote_Auth_Server_API::send_response('user', $user_details);
				} else {
					self::log('user nicht gefunden');
					RW_Remote_Auth_Server_API::send_response('user',array('exits'=>false));
				}
			}else{
				self::log('user_name nicht übergeben');
				RW_Remote_Auth_Server_API::send_response('user', array('error'=>'user_name nicht übergeben') );
			}

        }
        return $request;
    }
	
    /**
     *
     * @hook    rw_remote_auth_server_cmd_parser
     * @param   $request
     * @return  mixed
     */
    static public function cmd_user_auth( $request ) {
        if ( 'user_auth' == $request->cmd ) {
            // check username and password for auth from remotesystem
            // like xmlrpc request from bloging app


        }
        return $request;
    }

    /**
     *
     * @hook    rw_remote_auth_server_cmd_parser
     * @param   $request
     * @return  mixed
     */
    static public function cmd_user_create( $request ) {
	    global $wpdb;

        if ( 'user_create' == $request->cmd ) {
	        // only if user not exists.
	        if ( ! get_user_by( 'login' ,$request->data->user_name ) ) {
		        // Check userdate and create the new user
		        $data = array(
			        'user_login'    => $request->data->user_name,
			        'user_pass'     => urldecode( $request->data->user_password ),
			        'user_nicename' => $request->data->user_name,
			        'user_email'    => $request->data->user_email,

		        );

		        $wpdb->insert( $wpdb->users, $data );
		        RW_Remote_Auth_Server_API::send_response( true );
	        }
        }
       return $request;
    }

	/**
	 * @param $request
	 */
	static public function cmd_user_password_change( $request ) {
		global $wpdb;

		//@todo uncomment this in next release
		/*
		$valid = self::is_validate_trustet_client();
		if(is_wp_error($valid)){
			$error_string = $valid->get_error_message();
			RW_Remote_Auth_Server_API::send_response(false);
			return $request;

		}
		*/

		if ( 'user_change_password' == $request->cmd ) {
			// Check userdate and create the new user
			$user = get_user_by( 'slug', $request->data->user_name );
			if ( $user->user_pass == urldecode( $request->data->user_old_password ) ) {
				$wpdb->update (
					$wpdb->users,
					array(
						'user_pass' => urldecode( $request->data->user_new_password ),
					),
					array(
						'ID' => $user->ID
					)
				);
				RW_Remote_Auth_Server_API::send_response( true );
			} else {
				RW_Remote_Auth_Server_API::send_response( false );
			}
		}
		return $request;
	}

	/**
	 * @param $request
	 *
	 * @return mixed
	 */
	static public function cmd_user_get_password( $request ) {

		//@todo uncomment this in next release
		/*
		$valid = self::is_validate_trustet_client();
		if(is_wp_error($valid)){
			$error_string = $valid->get_error_message();
			RW_Remote_Auth_Server_API::send_response(false);
			return $request;

		}
		*/
		if ( 'user_get_password' == $request->cmd ) {
			// Check userdate and create the new user
			$user = get_user_by( 'slug', $request->data->user_name );
			if ( $user !== false ) {
				RW_Remote_Auth_Server_API::send_response( json_encode( array( 'password' => $user->user_pass, 'email' => $user->user_email ) ) );
			} else {
				RW_Remote_Auth_Server_API::send_response( false );
			}
		}
		return $request;
	}

	/**
	 * Implements a ping command, to check if rw_auth server is responding
	 *
	 * @since 0.1.3
	 * @param $request
	 *
	 * @return mixed
	 */
	static public function cmd_ping( $request ) {
		if ( 'ping' == $request->cmd ) {
			RW_Remote_Auth_Server_API::send_response( json_encode( array( 'answer' => 'pong' ) ) );
		}
		return $request;
	}


}