<?php

namespace HBP\Disabler\Optimize;

use HBP\Disabler\Admin\Options;
use HBP\Disabler\Contracts\Traits\Utils;
use HBP\Disabler\Tools\Jetpack\IPManager;
use HBP\Disabler\Tools\Jetpack\IpUtils;
use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;

class XMLRPC implements Bootable {

    use AccessiblePrivateMethods;
    use Utils;

    /**
     * Boot.
     *
     * @return void
     */
    public function boot() {
        self::add_action( 'init', [ $this, 'initHooks' ], 0 );
    }

    private function initHooks(): void {
        if ( self::isRequestAllowed() || 'no' === Options::get( 'xmlrpc_disable_xmlrpc' ) ) {
            return;
        }

        self::add_filter( 'xmlrpc_enabled', [ $this, 'XMLRPCEnabled' ], \PHP_INT_MAX );
        self::add_filter( 'xmlrpc_methods', [ $this, 'disableMethods' ], \PHP_INT_MAX );
        self::add_filter( 'wp_headers', [ $this, 'removeHeaders' ], \PHP_INT_MAX );
        self::add_filter( 'template_redirect', [ $this, 'removeHeaders2' ], \PHP_INT_MAX );
        self::add_filter( 'bloginfo_url', [ $this, 'removePingbackURL' ], \PHP_INT_MAX, 2 );
        self::add_filter( 'hybrid/theme/head/link/pingback', [ $this, 'removePingbackURL2' ], \PHP_INT_MAX );
        self::add_action( 'xmlrpc_call', [ $this, 'disableCall' ] );

        $this->headCleanup();
    }

    /**
     * Disable XML-RPC methods that require authentication.
     *
     * @see https://developer.wordpress.org/reference/hooks/xmlrpc_enabled/
     */
    private function XMLRPCEnabled( $is_enabled ): bool {
        if ( 'completely' === Options::get( 'xmlrpc_disable_xmlrpc' ) ) {
            return false;
        }

        return $is_enabled;
    }

    /**
     * Prevent XML-RPC for responding to anything by simply making sure the
     * list of supported methods is empty.
     *
     * @see https://developer.wordpress.org/reference/hooks/xmlrpc_methods/
     */
    private function disableMethods( $methods ) {
        if ( 'completely' === Options::get( 'xmlrpc_disable_xmlrpc' ) ) {
            return [];
        }

        if ( 'selective' === Options::get( 'xmlrpc_disable_xmlrpc' ) ) {
            $selected_methods = Options::get( 'xmlrpc_xmlrpc_methods', [] );

            $custom_methods = self::prepareMultilineText( Options::get( 'xmlrpc_custom_xmlrpc_methods', '' ) );
            $custom_methods = array_merge( $selected_methods, $custom_methods );

            array_walk( $custom_methods, static function ( $method ) use ( &$methods ) {
                if ( array_key_exists( $method, $methods ) ) {
                    unset( $methods[ $method ] );
                }
            } );
        }

        return $methods;
    }

    /**
     *  All built-in XML-RPC methods use the action xmlrpc_call, with a parameter
     *  equal to the method's name, e.g., wp.getUsersBlogs, wp.newPost, etc.
     *
     * @see https://developer.wordpress.org/reference/hooks/xmlrpc_call/
     */
    private function disableCall( $method ) {
        if ( 'completely' === Options::get( 'xmlrpc_disable_xmlrpc' ) ) {
            wp_die( 'XML-RPC is not supported', 'Not Allowed!', [ 'response' => 403 ] );
        }

        if ( 'selective' === Options::get( 'xmlrpc_disable_xmlrpc' ) ) {
            $custom_methods = self::prepareMultilineText( Options::get( 'xmlrpc_custom_xmlrpc_methods', '' ) );
            $custom_methods = array_merge( Options::get( 'xmlrpc_xmlrpc_methods', [] ), $custom_methods );

            if ( in_array( $method, $custom_methods ) ) {
                wp_die(
                    /* Translators: %1$s XML RPC method name. */
                    sprintf( esc_html__( 'XML-RPC\'s method %1$s is not supported', 'hbp-disabler' ), $method ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    'Not Allowed!',
                    [ 'response' => 403 ]
                );
            }
        }
    }

    /**
     * Remove X-Pingback in the HTTP header.
     */
    private function removeHeaders( $headers ) {
        $custom_headers = [];

        if ( 'completely' === Options::get( 'xmlrpc_disable_xmlrpc' ) ) {
            $custom_headers = [ 'X-Pingback' ];
        } elseif ( 'selective' === Options::get( 'xmlrpc_disable_xmlrpc' ) ) {
            $custom_headers = self::prepareMultilineText( Options::get( 'xmlrpc_custom_xmlrpc_headers', '' ) );
            $custom_headers = array_merge( Options::get( 'xmlrpc_disable_xmlrpc_headers', [] ), $custom_headers );
        }

        array_walk( $custom_headers, static function ( $header ) use ( &$headers ) {
            if ( array_key_exists( $header, $headers ) ) {
                unset( $headers[ $header ] );
            }
        } );

        return $headers;
    }

    /**
     * Remove X-Pingback in the HTTP header.
     */
    private function removeHeaders2() {
        if ( headers_sent() || ! function_exists( 'header_remove' ) ) {
            return;
        }

        $custom_headers = [];

        if ( 'completely' === Options::get( 'xmlrpc_disable_xmlrpc' ) ) {
            $custom_headers = [ 'X-Pingback' ];
        } elseif ( 'selective' === Options::get( 'xmlrpc_disable_xmlrpc' ) ) {
            $custom_headers = self::prepareMultilineText( Options::get( 'xmlrpc_custom_xmlrpc_headers', '' ) );
            $custom_headers = array_merge( Options::get( 'xmlrpc_disable_xmlrpc_headers', [] ), $custom_headers );
        }

        array_walk( $custom_headers, static function ( $header ) {
            header_remove( $header );
        } );
    }

    /**
     * Remove bloginfo('pingback_url') from <head>.
     *
     * Note: May cause pingbacks to be sent to the home URL.
     */
    private function removePingbackURL( $output, $show ) {
        if ( 'pingback_url' !== $show ) {
            return $output;
        }

        if (
            'completely' === Options::get( 'xmlrpc_disable_xmlrpc' )
            || (
                'selective' === Options::get( 'xmlrpc_disable_xmlrpc' )
                && Options::get( 'xmlrpc_remove_xmlrpc_pingback_link' )
            )
        ) {
            $output = '';
        }

        return $output;
    }

    /**
     * Remove bloginfo('pingback_url') from <head>.
     */
    private function removePingbackURL2( $link ) {
        if (
            'completely' === Options::get( 'xmlrpc_disable_xmlrpc' )
            || (
                'selective' === Options::get( 'xmlrpc_disable_xmlrpc' )
                && Options::get( 'xmlrpc_remove_xmlrpc_pingback_link' )
            )
        ) {
            $link = '';
        }

        return $link;
    }

    /**
     * Remove RSD EditURI (Really Simple Discovery) and WLW Manifest links.
     * `xmlrpc_rsd_apis` fires when adding APIs to the Really Simple Discovery (RSD) endpoint.
     *
     * @see https://developer.wordpress.org/reference/functions/rsd_link/
     * @see http://archipelago.phrasewise.com/rsd
     * @see https://cyber.harvard.edu/blogs/gems/tech/rsd.html
     */
    private function headCleanup() {
        if ( 'completely' === Options::get( 'xmlrpc_disable_xmlrpc' ) ) {
            remove_action( 'wp_head', 'rsd_link' );
            remove_action( 'wp_head', 'wlwmanifest_link' );
            remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
        }

        if ( 'selective' === Options::get( 'xmlrpc_disable_xmlrpc' ) ) {
            if ( Options::get( 'xmlrpc_xmlrpc_remove_rsd_link' ) ) {
                remove_action( 'wp_head', 'rsd_link' );
            }

            if ( Options::get( 'xmlrpc_xmlrpc_remove_wlwmanifest_link' ) ) {
                remove_action( 'wp_head', 'wlwmanifest_link' );
            }
        }
    }

    private static function isRequestAllowed() {
        if ( Options::get( 'xmlrpc_xmlrpc_whitelist_jetpack_ips' ) && self::isJetpackRequest() ) {
            return true;
        }

        $custom_ips = self::prepareMultilineText( Options::get( 'xmlrpc_custom_xmlrpc_whitelist_ips', '' ), '' );
        $custom_ips = array_map( 'strip_tags', $custom_ips );
        $custom_ips = array_map( 'htmlentities', $custom_ips );
        $custom_ips = array_filter( $custom_ips );

        if ( count( $custom_ips ) > 0 ) {
            $user_ip = IPManager::getIP();

            return IpUtils::checkIP( $user_ip, $custom_ips );
        }

        return false;
    }

    /**
     * Is the current request being made from Jetpack servers?
     *
     * NOTE - This checks the REMOTE_ADDR against known JP IPs. The IP can still be spoofed,
     * (but usually an attacker cannot receive the response), so it is important to treat it accordingly.
     *
     * @see https://github.com/Automattic/vip-go-mu-plugins/blob/bd74c5fe57bce49ca6ddf065c5b40813b02232d1/vip-helpers/vip-utils.php#L1508
     * @return bool Bool indicating if the current request came from JP servers
     */
    private static function isJetpackRequest() {
        // Filter by env.
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            return false;
        }

        $http_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Simple UA check to filter out most.
        if ( false === stripos( $http_user_agent, 'jetpack' ) ) {
            return false;
        }

        // If has a valid-looking UA, check the remote IP.
        $jetpack_ips = IPManager::getJetpackIPs();

        $user_ip = IPManager::getIP();

        return IpUtils::checkIP( $user_ip, $jetpack_ips );
    }

}
