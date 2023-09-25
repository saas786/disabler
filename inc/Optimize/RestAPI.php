<?php

namespace HBP\Disabler\Optimize;

use HBP\Disabler\Admin\Options;
use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;

class RestAPI implements Bootable {

    use AccessiblePrivateMethods;

    /**
     * Boot.
     *
     * @return void
     */
    public function boot() {
        self::add_action( 'init', [ $this, 'initHooks' ], 0 );
    }

    private function initHooks(): void {
        // Disable REST API links in HTML <head>.
        if ( Options::get( 'restapi_disable_rest_api_links' ) ) {
            remove_action( 'wp_head', 'rest_output_link_wp_head' );
        }

        // Disable the REST API URL to the WP RSD endpoint.
        if ( Options::get( 'restapi_disable_rest_api_rsd_link' ) ) {
            remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
        }

        // Disable REST API link in HTTP headers.
        if ( Options::get( 'restapi_disable_rest_api_link_in_headers' ) ) {
            remove_action( 'template_redirect', 'rest_output_link_header', 11 );
        }

        if ( Options::get( 'restapi_disable_rest_api_for_visitors' ) ) {
            self::add_filter( 'rest_authentication_errors', [ $this, 'rest_authentication_errors' ], \PHP_INT_MAX );
        }
    }

    /**
     * Prevent unauthenticated requests to the REST API.
     *
     * Note: We intentionally run it late, to allow other authentication execute before this.
     *
     * @see https://developer.wordpress.org/rest-api/frequently-asked-questions/#require-authentication-for-all-requests
     * @see https://github.com/woocommerce/woocommerce/issues/26847
     * @see https://core.trac.wordpress.org/ticket/46586
     * @see https://github.com/WordPress/WordPress/blob/812b1e296c57c53c6a2bf23f2cbc62adf4c7cc23/wp-includes/rest-api.php#L1058
     * @return true|\WP_Error
     */
    private function rest_authentication_errors( $result ) {

        // If error is detected, pass it on as is.
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // When cookie authentication is performed,
        // and if there is a no nonce,
        // it returns true,
        // so we need to verify if user is actually logged in or not.
        // When internal requests are made to Rest API via Block Editor,
        // it should work.
        if ( true === $result && is_user_logged_in() ) {
            return true;
        }

        // No authentication has been performed yet.
        // Return an error if user is not logged in.
        if ( ! is_user_logged_in() ) {
            $message = apply_filters( 'hbp/disabler/rest_api_error', __( 'REST API restricted to authenticated users.', 'hbp-disabler' ) );

            return new \WP_Error(
                'rest_not_logged_in',
                $message,
                [ 'status' => 401 ]
            );
        }

        // Our custom authentication check should have no effect
        // on logged-in requests.
        return $result;
    }

}
