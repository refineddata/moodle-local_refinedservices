<?php

/**
 * @package    local
 * @subpackage refinedservices
 * @copyright  2014 Refined Data Solutions Inc.
 * @author     Elvis Li
 */

namespace RefinedServices;

class ServiceClient {

	protected $base_domain;
	protected $username;
	protected $password;
	private   $curl;
	private   $cookie;
	private   $type;
	private   $is_authorized;

	/**
	 * Constructor for ServiceClient
	 *
	 * @param string $type - Service Type (connect/report/account)
	 */
	public function __construct( $type, $courseid = null ) {
		global $USER, $CFG;

		if ( ! isset( $CFG->refinedservices_host ) || empty( $CFG->refinedservices_host )) {
			return false;
		}

		$this->type        = $type;
		$this->base_domain = $CFG->refinedservices_host . '/' . $type;
		if ( $type == 'connect' ) {
			$this->username = $CFG->connect_service_username;
			$this->password = $CFG->connect_service_password;
		} else if ( $type == 'reports' ) {
			$this->username = $CFG->report_service_username;
			$this->password = $CFG->report_service_password;
		} else {
			$this->username = '';
			$this->password = '';
		}

		$this->set_user_capability( $courseid );
		if ( $type == 'connect' ) {
			$this->cookie = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'curlcookie_' . md5( $CFG->wwwroot . $this->base_domain . $this->username . $this->password . $USER->id ) . '.txt';
		} else {
			$this->cookie = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'curlcookie_' . md5( $CFG->wwwroot . $this->base_domain . $this->username . $this->password . $USER->id . $this->role ) . '.txt';
		}

		$this->initCurl();

		if ( ! $this->getCookie() && ( $type != 'account' ) ) {
			try {
				$this->makeAuth();
			} catch ( \Exception $e ) {

			}
		}
	}

    public function writeError( $location, $do = 0 ){
        global $CFG;
        if( $do ){
            $_SESSION['doWriteError'] = 1;
        }
        if( isset( $_SESSION['doWriteError'] ) && $_SESSION['doWriteError'] ){
            error_log( 'LOCALRSERROR: '.$location );
        }
    }

	protected function set_user_capability( $courseid ) {
		global $USER;
		if ( is_siteadmin() ) {
			$this->role = 'admin';

			return true;
		}
		$privileges       = self::get_all_rtreporting_capabilities();
		$privilege_course = 99;
		if ( ! empty( $courseid ) && ( $courseid != 1 ) ) {
			$context = \context_course::instance( $courseid, MUST_EXIST );
			foreach ( $privileges as $key => $privilege ) {
				if ( has_capability( $privilege . '_course', $context ) && ( $key < $privilege_course ) ) {
					$privilege_course = $key;
				}
			}
		}
		$privilege_system = 99;
		$context          = \context_system::instance();
		foreach ( $privileges as $key => $privilege ) {
			if ( has_capability( $privilege . '_system', $context ) && ( $key < $privilege_system ) ) {
				$privilege_system = $key;
			}
		}

		if ( $privilege_course == 99 && $privilege_system == 99 ) {
			$this->role = 'user';

			return true;
		}
		if ( $privilege_course > $privilege_system ) {
			$privilege = $privilege_system;
		} else {
			$privilege = $privilege_course;
		}
		$this->role = 'privilege' . $privilege;

		return true;
	}

	protected static function get_all_rtreporting_capabilities() {
		global $DB;
		$privileges = array();
		$results    = $DB->get_records( 'capabilities' );
		foreach ( $results as $data ) {
			preg_match( '/local\/rtreporting\:privilege(.*)\_system/', $data->name, $matches );
			if ( ! empty( $matches ) && ! empty( $matches[1] ) ) {
				$privileges[ $matches[1] ] = 'local/rtreporting:privilege' . $matches[1];
			}
		}

		return $privileges;
	}

	protected function initCurl() {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $ch, CURLOPT_REFERER, $this->base_domain );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $this->cookie );
		curl_setopt( $ch, CURLOPT_COOKIEFILE, $this->cookie );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HEADER, true );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
		//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$this->curl = $ch;
	}

	/**
	 * make auth-request (defaults to stored)
	 *
	 * @param array
	 *
	 * @return ServiceClient
	 */
	public function makeAuth( $retry = true ) {

		if ( empty( $this->username ) || empty( $this->password ) ) {
			return false;
		}

		if ( file_exists( $this->cookie ) ) {
			unlink( $this->cookie );
		}
		$this->makeRequest( 'login', '', $retry );

		$this->initCurl();


		return $this;
	}

	public function setCookie( $cookie_string ) {
		global $USER;
		if ( empty( $this->username ) || empty( $this->password ) ) {
			return false;
		}
		$USER->refinedservices                = new \stdClass();
		$USER->refinedservices->cookie_string = new \stdClass();
		if ( $this->type == 'connect' ) {
			$USER->refinedservices->cookie_string->{$this->username} = $cookie_string;
		} else {
			$USER->refinedservices->cookie_string->{$this->username}                = new \stdClass();
			$USER->refinedservices->cookie_string->{$this->username}->{$this->role} = $cookie_string;
		}
		$this->is_authorized = true;
	}

	/**
	 *  read cookie string from current session, useful for external auth
	 *
	 * @return string
	 */
	public function getCookie() {
		if ( empty( $this->username ) || empty( $this->password ) ) {
			return false;
		}
		//return false;
		global $USER;
		if ( $this->type == 'connect' ) {
			if ( isset( $USER->refinedservices->cookie_string->{$this->username} ) ) {
				$this->is_authorized = true;

				return $USER->refinedservices->cookie_string->{$this->username};
			} else {
				$this->is_authorized = false;

				return false;
			}
		} else {
			if ( isset( $USER->refinedservices->cookie_string->{$this->username}->{$this->role} ) ) {
				$this->is_authorized = true;

				return $USER->refinedservices->cookie_string->{$this->username}->{$this->role};
			} else {
				$this->is_authorized = false;

				return false;
			}
		}
	}

	public function is_authorized() {
		return $this->is_authorized;
	}

	/**
	 *
	 *
	 */

	public function connect_call( $action, array $params = array(), $docache = false, $purgecache = false, $noredirect = false ) {
        $_SESSION['doWriteError'] = 0;
        if( $action == 'get-launch-url' ){
            $this->writeError(__FILE__.' - '.__LINE__.' Launch call ', 1);
        }

        global $CFG;

        if( isset( $CFG->refinedservices_disable_cache ) && $CFG->refinedservices_disable_cache ){
            $this->writeError(__FILE__.' - '.__LINE__);
            $docache = false;
            $purgecache = true;
        }

		if ( ! $this->username || ! $this->password ) { // no credentials, don't even try
            $this->writeError(__FILE__.' - '.__LINE__);
            return false; // previously would return error message to browser, we no longer want this, so just return false in all cases
            if( preg_match( '/^cli/', php_sapi_name() ) || $noredirect ){
                return false;
            }
			if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
				$redirecturl = $_SERVER['HTTP_REFERER'];
			} else {
				$redirecturl = "$CFG->wwwroot";
			}
			$redirectdelay = 30;
			$supportuser   = \core_user::get_support_user();
			$message       = get_string( 'connect_error_nonadmin', 'local_refinedservices', array(
					'contact' => $supportuser->email,
					'code'    => 'RS001'
				) );

			global $PAGE;

			if ( ! $PAGE->requires->is_head_done() ) {
				if ( defined( 'AJAX_SCRIPT' ) && AJAX_SCRIPT ) {
					$response        = new \stdClass();
					$response->error = $message;

					header( 'Content-Type: application/json' );
					die( json_encode( $response ) );
				} else {
					redirect( $redirecturl, $message, $redirectdelay );
				}
			} else {
				// headers sent, lets try javascript redirect
				echo \bootstrap_renderer::early_redirect_message( $redirecturl, $message, $redirectdelay );
				exit;
			}


			return false;
		}

		// Prepare Request
		global $USER, $DB;
		$rsversion    = $DB->get_field( 'config_plugins', 'value', array(
				'plugin' => 'local_refinedservices',
				'name'   => 'version '
			) );
		$user         = array(
			'external_user_id' => $USER->id,
			'version'          => $rsversion
		);
		$request_body = array(
			'settings' => $user,
			'params'   => $params
		);
		$jsonreq      = json_encode( $request_body );

        $this->writeError(__FILE__.' - '.__LINE__);

		//return $this->makeRequest( $action, $jsonreq );

		if ( $purgecache ) {
            $this->writeError(__FILE__.' - '.__LINE__);
			$cache = \cache::make( 'local_refinedservices', 'connectcall' );
			$cache->purge();
		}

		if ( $docache ) {
            $this->writeError(__FILE__.' - '.__LINE__);
			$cache = \cache::make( 'local_refinedservices', 'connectcall' );
			try {
				$cachereturn = $cache->get( "$action-$jsonreq" );
				if ( $cachereturn ) {
					return $cachereturn;
				}
			} catch ( \Exception $e ) {

			}

		}
        $this->writeError(__FILE__.' - '.__LINE__);
//        $result = $this->makeRequest( $action, $jsonreq );
        try {
            $this->writeError(__FILE__.' - '.__LINE__);
            $result = $this->makeRequest( $action, $jsonreq );
            if( $docache ){
	            $cache->set( "$action-$jsonreq", $result );
            }
            return $result;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            switch ($error) {
                case '500':
                    $result = get_string('refinedserviceisdown','local_refinedservices');
                    break;
                case '505':
                    $result = get_string('adobeserverisdown','local_refinedservices');
                    break;
                case '506':
                    $result = get_string('jasperserverisdown','local_refinedservices');
                    break;
                case '401':
                    $result = get_string('unauthorizedreportservice','local_refinedservices');
                    break;
                case '402':
                    $result = get_string('unauthorizedconnectservice','local_refinedservices');
                    break;
                default:
                    $result = $error;
                    break;
            }die;
            $this->writeError(__FILE__.' - '.__LINE__);
            return $result;
        }
    }

    /**
     *
     *
     */

    public function report_call ( $action,  $params = null, $docache = false, $purgecache = false) {
        // Prepare Request
        global $USER, $CFG;

        $auth = array(
            'prefix' => $CFG->prefix,
            'role' => $this->role,
            'userid' => $USER->id
        );
        $request_body = array(
            'auth' => $auth,
            'params' => $params
        );

        $jsonreq = json_encode( $request_body );
        //return $this->makeRequest( $action, $jsonreq );

        if( $purgecache ){
        	$cache = \cache::make('local_refinedservices', 'reportcall');
        	$cache->purge();
        }

        if( $docache ){
        	$cache = \cache::make('local_refinedservices', 'reportcall');
                try{
                    $cachereturn = $cache->get( "$action-$jsonreq" );
                    if( $cachereturn ) return $cachereturn;
                } catch (\Exception $e) {
                }
        }

        try {
            $result = $this->makeRequest( $action, $jsonreq );
            if( $docache ){
            	$cache->set( "$action-$jsonreq", $result );
            }
            return $result;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            switch ($error) {
                case '500':
                    $result = get_string('refinedserviceisdown','local_refinedservices');
                    break;
                case '505':
                    $result = get_string('adobeserverisdown','local_refinedservices');
                    break;
                case '506':
                    $result = get_string('jasperserverisdown','local_refinedservices');
                    break;
                case '401':
                    $result = get_string('unauthorizedreportservice','local_refinedservices');
                    break;
                case '402':
                    $result = get_string('unauthorizedconnectservice','local_refinedservices');
                    break;
                default:
                    $result = $error;
                    break;
            }
            return $result;
        }
    }

    /**
     *
     *
     */

    public function account_call ( $action,  $params = null) {
        // Prepare Request
        global $USER, $CFG;

        $request_body = array(
            'params' => $params
        );

        $jsonreq = json_encode( $request_body );
        //return $this->makeRequest( $action, $jsonreq );
        try {
            $result = $this->makeRequest( $action, $jsonreq );
            return $result;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            switch ($error) {
                case '500':
                    $result = get_string('refinedserviceisdown','local_refinedservices');
                    break;
                case '505':
                    $result = get_string('adobeserverisdown','local_refinedservices');
                    break;
                case '506':
                    $result = get_string('jasperserverisdown','local_refinedservices');
                    break;
                case '401':
                    $result = get_string('unauthorizedreportservice','local_refinedservices');
                    break;
                case '402':
                    $result = get_string('unauthorizedconnectservice','local_refinedservices');
                    break;
                default:
                    $result = $error;
                    break;
            }
            $output = array();
            $output['message'] = $result;
            $output['username'] = '';
            $output['password'] = '';
            return $output;
        }
    }

    public function getAccount( $vars ) {
        $response = $this->account_call( 'getaccount', $vars );
        return $response;
    }

    public function checkExistingAccount( $vars ) {
    	$response = $this->account_call( 'check-existing-account', $vars );
    	return $response;
    }

    public function createAccount( $vars ) {
        $response = $this->account_call( 'createaccount', $vars );
        return $response;
    }

    /**
     * @param string $action
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    private function makeRequest( $action, $jsonreq = '', $retry = true, $noredirect = false ) {
    	global $CFG;

        $paths = array(
            '/var/www/html/refined_services/',
            '/var/www/html/refinedservices/'
        );

        $islocal = 0;
        $rslocalpath = '';
        /*
        foreach( $paths as $path ){
            if( file_exists( $path . 'app/controllers/ConnectController.php' ) ){
                $islocal = 1;
                $rslocalpath = $path;
                break;
            }
        }
        */

        if( $islocal && $rslocalpath ){
            // If RS is local
            if( $action == 'login'){
                return true;
            }
            require_once $rslocalpath . 'bootstrap/autoload.php';
            if( !isset( $this->app ) ){
                $this->app = require $rslocalpath . 'bootstrap/start.php';
            }
            $app = $this->app;

            $request = $app['request'];
            $client = (new \Stack\Builder)
                ->push('Illuminate\Cookie\Guard', $app['encrypter'])
                ->push('Illuminate\Cookie\Queue', $app['cookie'])
                ->push('Illuminate\Session\Middleware', $app['session'], null);
            $stack = $client->resolve($app);
            $stack->handle($request);

            $isAuthorized = $this->type == 'account' ? true : \Auth::check();

            if( !$isAuthorized ){
                $email = isset( $CFG->connect_service_username ) ? $CFG->connect_service_username : '';
                $password = isset( $CFG->connect_service_password ) ? $CFG->connect_service_password : '';

                if(!$email || !$password || !\Auth::attempt(array('username' => $email, 'password' => $password), true)){
                    $data = new \stdClass();
                    $data->status = 'RS002';
                }
            }

            if( !isset( $data ) ){
                $originalInput = \Input::all();

                \Input::replace( json_decode( $jsonreq, true ) );
                $base = explode( '/', $this->base_domain );
                $base = array_pop( $base );
                $request = \Request::create("/$base/$action", 'POST');
                \Config::set( 'moodlelocalcalluri', "$base/$action" );

                $data = json_decode(\Route::dispatch($request)->getContent());

                \Input::replace( $originalInput );
            }

            $url = $this->base_domain;
            $url .= '/' . $action;
        }else{
            $this->writeError(__FILE__.' - '.__LINE__);
            // Prepare Request
            $url = $this->base_domain;
            $url .= '/' . $action;
            //$this->_debugMessage( __METHOD__, '$action', $action );
            //$this->_debugMessage( __METHOD__, '$jsonreq', $jsonreq );
            //$this->_debugMessage( __METHOD__, '$url', $url );
            curl_setopt( $this->curl, CURLOPT_URL, $url );
            curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $jsonreq );
            $this->writeError(__FILE__.' - '.__LINE__);
            if ( !$this->getCookie() ) {
                $this->writeError(__FILE__.' - '.__LINE__);
                curl_setopt( $this->curl, CURLOPT_USERPWD, $this->username . ":" . $this->password );
                $result = curl_exec( $this->curl );
                $info = curl_getinfo( $this->curl );
                $this->writeError(__FILE__.' - '.__LINE__.' - INFO - '.json_encode($info));
                $this->writeError(__FILE__.' - '.__LINE__.' - RESULT - '.json_encode($result));
                $header = substr( $result, 0, $info['header_size'] );
                $cookieresult = $this->setCookieString( $header );
                $json = substr( $result, $info['header_size'] );
            } else {
                $this->writeError(__FILE__.' - '.__LINE__);
                curl_setopt( $this->curl, CURLOPT_HEADER, false );
                $json = curl_exec( $this->curl );
                $this->writeError(__FILE__.' - '.__LINE__.' - RESULT - '.$json);
            }
            // Prepare Response
            $this->writeError(__FILE__.' - '.__LINE__);
            $data = json_decode( $json );
            //         if (strstr($json, 'Invalid credentials' ) ) {
            //             if ($retry) return $this->makeRequest( $action, $jsonreq, false );
            //         }

            // admin log
        }
        $this->writeError(__FILE__.' - '.__LINE__);
        if( $action != 'login'){
//            var_dump( $data );die;
        }
        // For debuging, we can remove later

        if (!is_object($data) || ( !$islocal && strstr($json, 'Invalid credentials' ) ) || ( !$islocal && isset( $cookieresult ) && !$cookieresult ) ) {
            $this->writeError(__FILE__.' - '.__LINE__);
            if (!$retry) {
                $this->writeError(__FILE__.' - '.__LINE__);
                if ( $this->type == 'connect' ){
                    $this->writeError(__FILE__.' - '.__LINE__);
                    $data = new \stdClass();
                    $data->status = isset($cookieresult) && $cookieresult ? 'RS002' : 'RS004';
                } else if ( $this->type == 'reports' ){
                    throw new \Exception( 401 );
                }
            }

            if ($retry) {
                $this->writeError(__FILE__.' - '.__LINE__);
                $this->initCurl();
                $this->setCookie(null);
                if ($this->makeAuth(false)) {
                    $this->writeError(__FILE__.' - '.__LINE__);
                    // we where able to connect and $retry is true, so lets try this call again, pass $retry as false so it doesn't cause an infinate loop if it fails
                    return $this->makeRequest($action, $jsonreq, false);
                }
            }
        }

        $data->status = isset( $data->status ) ? $data->status : '';
        $data->body = isset( $data->body ) ? $data->body : '';
        $data->error = isset( $data->error ) ? $data->error : '';
        $this->admin_log( $url, $jsonreq, $data->status, json_encode($data->body), $data->error );

        // HTTP Status Codes
        // Success :      200 - OK,  204 - No Content, 205 ( our own code ) account is expired but not yet disabled
        // Client Error:  400 - Bad Request, 401 - Unauthorized of report service , 402 - Unauthorized of connect service , 404 - Not Found
        // Server Error:  500 - Refined Service is down (Internal Server Error),
        //                505 - Adobe server is down,
        //                506 - Jasper server is down,
        //                501 - Not Implemented, 502 - Bad Gateway, 503 - Service Unavailable, 504 - Gateway Timeout

        $expected_codes = array( 200, 204, 205 );

        if( $data->status == 500 ) $data->status = 'RS004';

        $this->writeError(__FILE__.' - '.__LINE__);

        if ( !in_array( $data->status, $expected_codes ) && !preg_match('/^RS\d{3}$/', $data->status ) && !preg_match('/^AC\d{3}$/', $data->status ) ) {
            if ( $CFG->debug >= 15 ) {
                $this->writeError(__FILE__.' - '.__LINE__);
                throw new \Exception( $data->status );
            } else {
                $this->writeError(__FILE__.' - '.__LINE__.' - '.$data->status);
                return false;
            }
        }
        if ( is_string( $data->body ) && strtolower( $data->body ) == 'true' )  $data->body = true;
        if ( is_string( $data->body ) && strtolower( $data->body ) == 'false' )  $data->body = false;

        $this->writeError(__FILE__.' - '.__LINE__.' - '.json_encode($data->body));

        if( $data->status == 205 && is_siteadmin() && !preg_match( '/^cli/', php_sapi_name() ) && !$noredirect ){ // account is expired, will soon be disabled, if admin, we warn them
        	if( !isset( $_SESSION['rsexpirewarning'] ) || ( $_SESSION['rsexpirewarning'] * 60*60*2 ) < time() ){// warn them every two hours
        		$_SESSION['rsexpirewarning'] = time();
        		$redirecturl = $CFG->wwwroot;
        		$message = get_string( 'account_expired_soon_disabled', 'local_refinedservices' );
        		global $PAGE;
        		if (!$PAGE->requires->is_head_done()) {
        			if (defined('AJAX_SCRIPT') && AJAX_SCRIPT) {
        				$response = new \stdClass();
        				$response->error = $message;
        				header('Content-Type: application/json');
        				die(json_encode($response));
        			} else {
        				redirect($redirecturl, $message, 15);
        			}
        		} else {
        			// headers sent, lets try javascript redirect
        			echo \bootstrap_renderer::early_redirect_message($redirecturl, $message, 15);
        			exit;
        		}
        	}
        }

        if( $data->status == 'RS003' ){
            $this->writeError(__FILE__.' - '.__LINE__);
        	$_SESSION['refined_noauth'] = 1;
        }elseif( isset( $_SESSION['refined_noauth'] ) ){
            $this->writeError(__FILE__.' - '.__LINE__);
        	$_SESSION['refined_noauth'] = 0;
        }

        $this->writeError(__FILE__.' - '.__LINE__);
        if( $this->type == 'connect' && $data->body == false && $data->error == 'no-user' && $retry ){ // logged in user not in RS, lets create them and try again
        	global $USER, $CFG;
            $this->writeError(__FILE__.' - '.__LINE__);
        	require_once("$CFG->dirroot/mod/connectmeeting/lib.php");
        	connect_update_user( $USER );
        	return $this->makeRequest( $action, $jsonreq, false );
        }

        if( $this->type == 'connect' && ( preg_match('/^RS\d{3}/', $data->status) || preg_match('/^AC\d{3}/', $data->status)) && (!preg_match( '/^cli/', php_sapi_name() )) && !$noredirect ) {
            if( (is_siteadmin() || has_capability('local/refinedservices:directacaccess', \context_system::instance()) )&& $data->error == 'no-auth' && $data->status != 'AC002' ){
            	//there is an account, but it couldn't connect, lets ask for there info
            	$redirecturl = "$CFG->wwwroot/local/refinedservices/connect_login_form.php";
            	$redirectdelay = 5;
                $message = get_string( 'require_connect_credentials', 'local_refinedservices' );
            }else{
                if (!empty($_SERVER['HTTP_REFERER'])) {
                    $redirecturl = $_SERVER['HTTP_REFERER'];
                }else{
                    $redirecturl = "$CFG->wwwroot";
                }
                $redirectdelay = 30;
                $supportuser = \core_user::get_support_user();
                $message = get_string( 'connect_error_nonadmin', 'local_refinedservices', array( 'contact' => $supportuser->email, 'code' => $data->status ) );
            }
        	global $PAGE;

            //if ( $CFG->debug >= 15 ) {
                if (!$PAGE->requires->is_head_done()) {
                    if (defined('AJAX_SCRIPT') && AJAX_SCRIPT) {
                        $response = new \stdClass();
                        if( (is_siteadmin() || has_capability('local/refinedservices:directacaccess', \context_system::instance()) ) && $data->error == 'no-auth' && $data->status != 'AC002' ){
                            $response->error = "Please add your Adobe Connect credentials here: <a href='$redirecturl'>$redirecturl</a>";
                        }else{
                            $response->error = $message;
                        }
                        header('Content-Type: application/json');
                        die(json_encode($response));
                    } else {
                        redirect($redirecturl, $message, $redirectdelay);
                    }
                } else {
                    // headers sent, lets try javascript redirect
                    echo \bootstrap_renderer::early_redirect_message($redirecturl, $message, $redirectdelay);
                    exit;
                }
            //}
        }

        $this->writeError(__FILE__.' - '.__LINE__.' - '.json_encode($data->body));
        return $data->body;
    }

    /**
     * parses cookie string from header of cURL response
     *
     * @param string $response
     * @throws Exception
     */
    private function setCookieString($response) {
        if (preg_match('/404 Not Found/', $response) || !preg_match('/laravel_session\=/', $response) ) {
            //throw new \Exception('500');
            return false;
        }
        $string = " " . $response;
        $ini = strpos($string, 'laravel_session=');
        $ini += strlen('laravel_session=');
        $len = strpos($string, '; httponly', $ini) - $ini;
        $this->setCookie(substr($string, $ini, $len));
        return true;
    }

    protected function admin_log( $requst_url, $requst_body, $response_status, $response_body, $response_error ) {
        global $CFG, $DB, $USER;
        if ($CFG->debug < 32767) {
            return;
        }
        if ( is_object( $response_body ) )
            $response_body = json_encode( $response_body );


     // for later, need to add an event and trigger it here


    }

    protected function _debugMessage( $function, $name, $data ) {

        $error = '** Refined Service Plugin * ' . $function . ' * ' . $name . ' : ';
        if ( is_object( $data ) || is_array( $data )) {
            $error .= json_encode( $data );
        } else {
            $error .= $data;
        }
        //error_log( '-------------------------------------------' );
        error_log( $error );
        error_log( '' );
    }

}
