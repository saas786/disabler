<?php

namespace Disabler;

/**
 * Represents the server data.
 */
class Disabler_Tracking_Server_Data implements Disabler_Collection {

	/**
	 * Returns the collection data.
	 *
	 * @return array The collection data.
	 */
	public function get() {
		return array(
			'server' => self::get_server_data(),
		);
	}

	/**
	 * Returns the values with server details.
	 *
	 * @return array Array with the value.
	 */
	protected static function get_server_data() {
		$server_data = array();

		if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
			$server_data['software'] = $_SERVER['SERVER_SOFTWARE'];
		}

		// Validate if the server address is a valid IP-address.
		$ipaddress = filter_input( INPUT_SERVER, 'SERVER_ADDR', FILTER_VALIDATE_IP );

		if ( $ipaddress ) {
			$server_data['ip']       = $ipaddress;
			$server_data['Hostname'] = gethostbyaddr( $ipaddress );
		}

		if ( function_exists( 'phpversion' ) ) {
			$server_data['php_version'] = phpversion();
		} else if ( defined( 'PHP_VERSION' ) ) {
			$server_data['php_version'] = PHP_VERSION;
		}

		if ( function_exists( 'ini_get' ) ) {
			$server_data['php_post_max_size'] = size_format( disabler_let_to_num( ini_get( 'post_max_size' ) ) );
			$server_data['php_time_limit'] = ini_get( 'max_execution_time' );
			$server_data['php_max_input_vars'] = ini_get( 'max_input_vars' );
			$server_data['php_suhosin'] = extension_loaded( 'suhosin' ) ? 'Yes' : 'No';
		}

		global $wpdb;
		$server_data['mysql_version'] = $wpdb->db_version();

		$server_data['os']            = php_uname( 's r' );
		$server_data['php_max_upload_size'] = size_format( wp_max_upload_size() );
		$server_data['php_default_timezone'] = date_default_timezone_get();
		$server_data['php_soap']      = class_exists( 'SoapClient' ) ? 'Yes' : 'No';
		$server_data['php_fsockopen'] = function_exists( 'fsockopen' ) ? 'Yes' : 'No';
		$server_data['php_curl']      = self::get_curl_info();
		$server_data['php_extensions'] = self::get_php_extensions();

		return $server_data;
	}

	/**
	 * Returns details about the curl version.
	 *
	 * @return array|null The curl info. Or null when curl isn't available..
	 */
	protected static function get_curl_info() {
		if ( ! function_exists( 'curl_version' ) ) {
			return null;
		}

		$curl = curl_version();

		$ssl_support = true;
		if ( ! $curl['features'] && CURL_VERSION_SSL ) {
			$ssl_support = false;
		}

		return array(
			'version'     => $curl['version'],
			'ssl_support' => $ssl_support,
		);
	}

	/**
	 * Returns a list with php extensions.
	 *
	 * @return array Returns the state of the php extensions.
	 */
	protected static function get_php_extensions() {
		return array(
			'imagick' => extension_loaded( 'imagick' ),
			'filter'  => extension_loaded( 'filter' ),
			'bcmath'  => extension_loaded( 'bcmath' ),
			'modXml'  => extension_loaded( 'modXml' ),
			'pcre'    => extension_loaded( 'pcre' ),
			'xml'     => extension_loaded( 'xml' ),
		);
	}
}
