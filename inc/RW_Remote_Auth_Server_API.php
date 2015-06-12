<?php

/**
 * Class RW_Remote_Auth_Server_Options
 *
 * Contains code for API
 *
 */

class RW_Remote_Auth_Server_API {

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
		if( ! $request || !$request->cmd || !$request->data ) {
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
            // Check userdate and create the new user
			$data = array(
				'user_login' => $request->data->user_name,
				'user_pass' => urldecode( $request->data->user_password ),
				'user_nicename' => $request->data->user_name,

			);

			$wpdb->insert( $wpdb->users, $data   );
            RW_Remote_Auth_Server_API::send_response( true );
        }
       return $request;
    }

}