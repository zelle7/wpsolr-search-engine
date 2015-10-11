<?php

/**
 * Class OptionGotosolr
 *
 * Manage Gotosolr hosting options
 */
class OptionManagedSolrServer extends WpSolrExtensions {

	private $_options;

	private $_api_path;

	private $_managed_solr_service;

	private $_managed_solr_service_id;

	/*
	 * REST api paths
	 *
	 */
	const PATH_USERS_SIGNIN = '/users/signin';
	const PATH_LIST_ACCOUNTS = '/accounts';
	const PATH_LIST_INDEXES = '/accounts/%s/indexes';

	/*
	 * Constructor
	 *
	 * Subscribe to actions
	 */

	function __construct( $managed_solr_service_id ) {

		$this->_managed_solr_service_id = $managed_solr_service_id;

		//$this->set_service_option('token', '');

		$this->_options = self::get_option_data( self::OPTION_MANAGED_SOLR_SERVERS, null );

		$this->_managed_solr_service = $this->get_managed_solr_service();

		$this->_api_path = $this->_managed_solr_service['api_path'];

	}


	/*
	 * Generic REST calls
	 */
	public function call_rest_get( $path ) {

		$full_path = $this->_api_path . $path . '&access_token=' . $this->get_service_option( 'token' );

		// Pb with SSL certificate. Disabled.
		$options = array(
			'verify'  => false,
			'timeout' => 30,
		);

		// Json format.
		$headers = array(
			'Content-Type' => 'application/json'
		);

		$response = Requests::get(
			$full_path,
			$headers,
			$options
		);

		//var_dump( $full_path );
		//var_dump( $response->body );

		if ( 200 != $response->success ) {
			return (object) array( 'status' => (object) array( 'state' => 'ERROR', 'message' => $response->body ) );
		}

		return json_decode( $response->body );

	}

	public function call_rest_post( $path, $data = array() ) {

		$full_path = ( 'http' === substr( $path, 0, 4 ) ) ? $path : $this->_api_path . $path;

		// Pb with SSL certificate. Disabled.
		$options = array(
			'verify'  => false,
			'timeout' => 60,
		);

		// Json format.
		$headers = array( 'Content-Type' => 'application/json' );

		$response = Requests::post(
			$full_path,
			$headers,
			json_encode( $data ),
			$options
		);

		//var_dump( $response->body );
		if ( 200 != $response->success ) {
			return (object) array( 'status' => (object) array( 'state' => 'ERROR', 'message' => $response->body ) );
		}

		return json_decode( $response->body );

	}

	public static function is_response_ok( $response_object ) {

		return ( 'OK' === $response_object->status->state );

	}

	public static function get_response_results( $response_object ) {

		return $response_object->results[0];
	}

	public static function get_response_error_message( $response_object ) {

		return htmlentities( $response_object->status->message );
	}

	public static function get_response_result( $response_object, $field ) {

		return isset( $response_object->results ) && isset( $response_object->results[0] )
			? is_array( $response_object->results[0] ) ? $response_object->results[0][0]->$field : $response_object->results[0]->$field
			: null;
	}

	/*
	 * Api REST calls
	 */
	public function call_rest_signin( $email, $password ) {

		$response_object = $this->call_rest_post(
			self::PATH_USERS_SIGNIN,
			array(
				'email'    => $email,
				'password' => $password,
			)
		);

		return $response_object;
	}

	public function call_rest_create_solr_index() {

		$managed_solr_service = $this->get_managed_solr_service();

		$response_object = $this->call_rest_post(
			$managed_solr_service['order_solr_index_url']
		);

		return $response_object;
	}

	public function call_rest_list_accounts() {

		$response_object = $this->call_rest_get(
			sprintf( '%s?query=&orderBy=asc&start=1&limit=20', self::PATH_LIST_ACCOUNTS )
		);

		return $response_object;
	}

	public function call_rest_account_indexes( $account_uuid ) {

		$response_object = $this->call_rest_get(
			sprintf( '%s?query=&orderBy=asc&start=1&limit=20', sprintf( self::PATH_LIST_INDEXES, $account_uuid ) )
		);

		return $response_object;
	}

	/**
	 * Get a service option
	 *
	 * @return bool
	 */
	public function get_service_option( $option_name ) {

		$service_options = $this->get_service_options();

		return ( isset( $service_options ) && isset( $service_options[ $option_name ] ) ) ? $service_options[ $option_name ] : '';
	}

	/**
	 * Set a service option
	 *
	 * @return bool
	 */
	public function set_service_option( $option_name, $option_value ) {

		$options = isset( $this->_options ) ? $this->_options : array();

		$options[ $this->_managed_solr_service_id ][ $option_name ] = $option_value;

		// Save options
		$this->set_option_data( self::OPTION_MANAGED_SOLR_SERVERS, $options );

		// Refresh the options after save
		$this->_options = self::get_option_data( self::OPTION_MANAGED_SOLR_SERVERS, null );

	}

	/**
	 * Get the options stored for a managed Solr service
	 *
	 * @return option
	 */
	private function get_service_options() {

		return isset( $this->_options[ $this->_managed_solr_service_id ] ) ? $this->_options[ $this->_managed_solr_service_id ] : null;
	}

	/**
	 * Get all managed Solr services data
	 *
	 * @return array
	 */
	public static function get_managed_solr_services() {

		return array(
			'gotosolr'  => array(
				'menu_label'           => 'gotosolr.com',
				'home_page'            => 'http://www.gotosolr.com/en',
				'api_path'             => 'https://api.gotosolr.com/v1/partners/24b7729e-02dc-47d1-9c15-f1310098f93f',
				'order_solr_index_url' => 'https://api.gotosolr.com/v1/providers/8c25d2d6-54ae-4ff6-a478-e2c03f1e08a4/accounts/24b7729e-02dc-47d1-9c15-f1310098f93f/addons/f8622320-5a3b-48cf-a331-f52459c46573/order-solr-index/8037888b-501a-4200-9fb0-b4266434b161'
			),
			'reseller1' => array(
				'menu_label'           => 'local dev',
				'home_page'            => 'http://www.reseller1.com/en',
				'api_path'             => 'http://10.0.2.2:8082/v1/partners/2c93bcdc-e6cd-4251-b4f7-8130e398dc36',
				'order_solr_index_url' => 'http://10.0.2.2:8082/v1/providers/d26a384b-fa62-4bdb-a1dd-27d714a3f519/accounts/2c93bcdc-e6cd-4251-b4f7-8130e398dc36/addons/51e110f8-a8df-4791-8c09-f22ee81671e6/order-solr-index/6c9b24ed-c368-4d25-a836-15af7ed04447'
			)
		);

	}


	public function get_managed_solr_service() {
		$managed_services = $this->get_managed_solr_services();

		return $managed_services[ $this->_managed_solr_service_id ];
	}

	public function get_id() {

		return $this->_managed_solr_service_id;
	}

	public function get_label() {
		$managed_service = $this->get_managed_solr_service();

		return $managed_service['menu_label'];
	}
}