<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

/**
 * Gravity Forms Pipe API library.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    Rocketgenius
 * @copyright Copyright (c) 2017, Rocketgenius
 */
class GF_Pipe_API {

	/**
	 * Pipe API Key.
	 *
	 * @since  1.0
	 * @var    string
	 * @access protected
	 */
	protected $api_key = '';

	/**
	 * Base Pipe API URL.
	 *
	 * @since  1.0
	 * @var    string
	 * @access protected
	 */
	protected $api_url = 'https://api.addpipe.com/';

	/**
	 * Initialize Pipe API library.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $api_key API key.
	 * @param string $api_url API URL.
	 */
	public function __construct( $api_key ) {

		$this->api_key = $api_key;

	}





	// # ACCOUNT -------------------------------------------------------------------------------------------------------

	/**
	 * Get account details.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_account() {

		return $this->make_request( 'account', array(), 'GET', 'account_details' );

	}





	// # ENVIRONMENTS --------------------------------------------------------------------------------------------------

	/**
	 * Get available environments.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_environments() {

		return $this->make_request( 'environment', array(), 'GET', 'environment_details' );

	}





	// # REQUEST METHODS -----------------------------------------------------------------------------------------------

	/**
	 * Make API request.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @param string $action     Request action.
	 * @param array  $options    Request options.
	 * @param string $method     HTTP method. Defaults to GET.
	 * @param string $return_key Array key from response to return. Defaults to null (return full response).
	 *
	 * @return array
	 */
	private function make_request( $action, $options = array(), $method = 'GET', $return_key = null ) {

		// Build request options string.
		$request_options = 'GET' === $method && ! empty( $options ) ? '?'. http_build_query( $options ) : null;

		// Build request URL.
		$request_url = $this->api_url . '/' .$action . $request_options;

		// Build request headers.
		$headers = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
			'X-PIPE-AUTH'  => $this->api_key,
		);

		// Build request arguments.
		$args = array(
			'body'      => 'GET' !== $method ? json_encode( $options ) : null,
			'headers'   => $headers,
			'method'    => $method,
		);

		// Execute request.
		$response      = wp_remote_request( $request_url, $args );
		$response_code = wp_remote_retrieve_response_code( $response );

		// If response is an error, throw an Exception.
		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}

		if ( ! empty( $response['body'] ) ) {
			$response = json_decode( $response['body'], true );
		} elseif ( (int) $response_code === 200 && rgar( $response, 'body' ) === '' ) {
			// If endpoint returns empty response with a 200, treat it as a success.
			$response = array( 'status' => 'OK' );
		}

		// If status is not OK, throw an Exception.
		if ( 'OK' !== $response['status'] ) {
			throw new Exception( $response['message'], $response['status_code'] );
		}

		// If a return key is defined and array item exists, return it.
		if ( ! empty( $return_key ) && isset( $response[ $return_key ] ) ) {
			return $response[ $return_key ];
		}

		return $response;

	}

}
