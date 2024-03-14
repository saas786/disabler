<?php
/**
 * @see https://github.com/Automattic/vip-go-mu-plugins/blob/bd74c5fe57bce49ca6ddf065c5b40813b02232d1/vip-helpers/class-jetpack-ip-manager.php
 */

namespace HBP\Disabler\Tools\Jetpack;

class IPManager {

    public const OPTION_NAME = 'hbp_disabler_vip_jetpack_ips';

    public const ENDPOINT = 'https://jetpack.com/ips-v4.json';

    private const CACHE_GROUP = 'hbp_disabler_vip';

    private const CACHE_KEY = 'hbp_disabler_jetpack_ips_lock';

    public static function updateJetpackIPs(): array {
        $response = self::safeWPRemoteGet( self::ENDPOINT );

        if ( ! is_wp_error( $response ) ) {
            $code = wp_remote_retrieve_response_code( $response );

            if ( 200 === (int) $code ) {
                $body = wp_remote_retrieve_body( $response );
                $ips  = json_decode( $body, true );

                if ( is_array( $ips ) && ! empty( $ips ) ) {
                    $data = [
                        'ips' => $ips,
                        'exp' => time() + DAY_IN_SECONDS,
                    ];

                    update_option( self::OPTION_NAME, $data );

                    return $data;
                }
            }
        }

        return [];
    }

    public static function getJetpackIPs(): array {
        $data = get_option( self::OPTION_NAME, false );
        if ( ! is_array( $data ) ) {
            $data = self::updateJetpackIPs();
        }

        $ips = $data['ips'] ?? [];
        $exp = $data['exp'] ?? 0;

        if ( time() > $exp && wp_cache_add( self::CACHE_KEY, true, self::CACHE_GROUP, 300 ) ) {
            try {
                $fresh_data = self::updateJetpackIPs();
                if ( ! empty( $fresh_data ) ) {
                    $ips = $fresh_data['ips'];
                }
            } finally {
                wp_cache_delete( self::CACHE_KEY, self::CACHE_GROUP );
            }
        }

        return $ips;
    }

    /**
     * This is a convenience method for vip_safe_wp_remote_request() and behaves the same
     *
     * Note that like wp_remote_get(), this function does not cache.
     *
     * @see vip_safe_wp_remote_request()
     * @see wp_remote_get()
     * @see https://docs.wpvip.com/technical-references/code-quality-and-best-practices/retrieving-remote-data/ Fetching Remote Data
     * @see https://github.com/Automattic/vip-go-mu-plugins/blob/bd74c5fe57bce49ca6ddf065c5b40813b02232d1/vip-helpers/vip-utils.php#L920
     */
    public static function safeWPRemoteGet(
        $url,
        $fallback_value = '',
        $threshold = 3,
        $timeout = 1,
        $retry = 20,
        $args = []
    ) {
        // Same defaults as WP_HTTP::get() https://developer.wordpress.org/reference/classes/wp_http/get/
        $default_args = [ 'method' => 'GET' ];
        $parsed_args  = wp_parse_args( $args, $default_args );

        return self::safeWPRemoteRequest( $url, $fallback_value, $threshold, $timeout, $retry, $parsed_args );
    }

    /**
     * This is a sophisticated extended version of wp_remote_request(). It is designed to more gracefully handle failure than wpcom_vip_file_get_contents() does.
     *
     * Note that like wp_remote_request(), this function does not cache.
     *
     * @see wp_remote_request()
     * @see https://docs.wpvip.com/technical-references/code-quality-and-best-practices/retrieving-remote-data/ Fetching Remote Data
     * @see https://github.com/Automattic/vip-go-mu-plugins/blob/bd74c5fe57bce49ca6ddf065c5b40813b02232d1/vip-helpers/vip-utils.php#L920
     * @param string         $url            URL to request
     * @param string         $fallback_value Optional. Set a fallback value to be returned if the external request fails.
     * @param int            $threshold      Optional. The number of fails required before subsequent requests automatically return the fallback value. Defaults to 3, with a maximum of 10.
     * @param int            $timeout        Optional. Number of seconds before the request times out. Valid values 1-5; defaults to 1.
     * @param int            $retry          Optional. Number of seconds before resetting the fail counter and the number of seconds to delay making new requests after the fail threshold is reached. Defaults to 20, with a minimum of 10.
     * @param array Optional. Set other arguments to be passed to wp_remote_request().
     * @return string|\WP_Error|array Array of results. If fail counter is met, returns the $fallback_value, otherwise return WP_Error.
     */
    public static function safeWPRemoteRequest(
        $url,
        $fallback_value = '',
        $threshold = 3,
        $timeout = 1,
        $retry = 20,
        $args = []
    ) {
        global $blog_id;

        $default_args = [ 'method' => 'GET' ];
        $parsed_args  = wp_parse_args( $args, $default_args );

        $cache_group = "$blog_id:hbp_disabler_vip_safe_wp_remote_request";
        $cache_key   = 'disable_remote_request_' . md5( wp_parse_url( $url, PHP_URL_HOST ) . '_' . $parsed_args['method'] );

        // valid url
        if ( empty( $url ) || ! wp_parse_url( $url ) ) {
            return $fallback_value ?: new \WP_Error( 'invalid_url', $url );
        }

        // Ensure positive values
        $timeout   = abs( $timeout );
        $retry     = abs( $retry );
        $threshold = abs( $threshold );

        // Default max timeout is 5s.
        // For POST requests for through WP-CLI, this needs to be event higher to makes things like VIP Search commands works more consistently without tinkering.
        // For POST requests for admins, this needs to be a bit higher due to Elasticsearch and other things.
        $timeout         = (int) $timeout;
        $is_post_request = 0 === strcasecmp( 'POST', $parsed_args['method'] );

        if ( defined( 'WP_CLI' ) && WP_CLI && $is_post_request ) {
            if ( 30 < $timeout ) {
                _doing_it_wrong( __FUNCTION__, 'Remote POST request timeouts are capped at 30 seconds in WP-CLI for performance and stability reasons.', null );
                $timeout = 30;
            }
        } elseif ( is_admin() && $is_post_request ) {
            if ( 15 < $timeout ) {
                _doing_it_wrong( __FUNCTION__, 'Remote POST request timeouts are capped at 15 seconds for admin requests for performance and stability reasons.', null );
                $timeout = 15;
            }
        } elseif ( 5 < $timeout ) {
            _doing_it_wrong( __FUNCTION__, 'Remote request timeouts are capped at 5 seconds for performance and stability reasons.', null );
            $timeout = 5;
        }

        // retry time < 10 seconds will default to 10 seconds.
        $retry = (int) $retry < 10
            ? 10
            : (int) $retry;
        // more than 10 faulty hits seem to be to much
        $threshold = (int) $threshold > 10
            ? 10
            : (int) $threshold;

        $option = wp_cache_get( $cache_key, $cache_group );

        // check if the timeout was hit and obey the option and return the fallback value
        if ( false !== $option && time() - $option['time'] < $retry ) {
            if ( $option['hits'] >= $threshold ) {
                if (
                    ! defined( 'WPCOM_VIP_DISABLE_REMOTE_REQUEST_ERROR_REPORTING' )
                    || ! WPCOM_VIP_DISABLE_REMOTE_REQUEST_ERROR_REPORTING
                ) {
                    trigger_error( esc_html( "hbp_disabler_vip_safe_wp_remote_request: Blog ID {$blog_id}: Requesting $url with method {$parsed_args[ 'method' ]} has been throttled after {$option['hits']} attempts. Not reattempting until after $retry seconds" ), E_USER_WARNING );
                }

                return $fallback_value
                    ?: new \WP_Error( 'remote_request_disabled', 'Remote requests disabled: ' . maybe_serialize( $option ) );
            }
        }

        $start    = microtime( true );
        $response = wp_remote_request( $url, array_merge( $parsed_args, [ 'timeout' => $timeout ] ) );
        $end      = microtime( true );

        $elapsed = $end - $start > $timeout;
        if ( true === $elapsed ) {
            if ( false !== $option && $option['hits'] < $threshold ) {
                wp_cache_set( $cache_key, [
                    'time' => floor( $end ),
                    'hits' => $option['hits'] + 1,
                ], $cache_group, $retry ); // phpcs:ignore WordPressVIPMinimum.Performance.LowExpiryCacheTime.CacheTimeUndetermined
            } elseif ( false !== $option && $option['hits'] == $threshold ) {
                wp_cache_set( $cache_key, [
                    'time' => floor( $end ),
                    'hits' => $threshold,
                ], $cache_group, $retry ); // phpcs:ignore WordPressVIPMinimum.Performance.LowExpiryCacheTime.CacheTimeUndetermined
            } else {
                wp_cache_set( $cache_key, [
                    'time' => floor( $end ),
                    'hits' => 1,
                ], $cache_group, $retry ); // phpcs:ignore WordPressVIPMinimum.Performance.LowExpiryCacheTime.CacheTimeUndetermined
            }
        } elseif ( false !== $option && 0 < $option['hits'] && time() - $option['time'] < $retry ) {
            wp_cache_set( $cache_key, [
                'time' => $option['time'],
                'hits' => $option['hits'] - 1,
            ], $cache_group, $retry ); // phpcs:ignore WordPressVIPMinimum.Performance.LowExpiryCacheTime.CacheTimeUndetermined
        } else {
            wp_cache_delete( $cache_key, $cache_group );
        }

        if ( is_wp_error( $response ) ) {
            if (
                ! defined( 'WPCOM_VIP_DISABLE_REMOTE_REQUEST_ERROR_REPORTING' )
                || ! WPCOM_VIP_DISABLE_REMOTE_REQUEST_ERROR_REPORTING
            ) {
                trigger_error( esc_html( "hbp_disabler_vip_safe_wp_remote_request: Blog ID {$blog_id}: Requesting $url with method {$parsed_args[ 'method' ]} and a timeout of $timeout failed. Result: " . maybe_serialize( $response ) ), E_USER_WARNING );
            }

            do_action( 'hbp_disabler_wpcom_vip_remote_request_error', $url, $response );

            return $fallback_value ?: $response;
        }

        return $response;
    }

    /**
     * Get User IP.
     * Returns the IP address of the current visitor.
     *
     * @see https://github.com/awesomemotive/easy-digital-downloads/blob/675bd9306b4d1af3fc8c07bbeddfe2354e132584/includes/misc-functions.php#L224C24-L224C24
     * @return string
     */
    public static function getIP() {
        $ip = false;

        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            // Check ip from share internet.
            $ip = filter_var( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ), FILTER_VALIDATE_IP );
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {

            // To check ip is pass from proxy.
            // Can include more than 1 ip, first is the public one.

            // WPCS: sanitization ok.
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $ips = explode( ',', wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
            if ( is_array( $ips ) ) {
                $ip = filter_var( $ips[0], FILTER_VALIDATE_IP );
            }
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP );
        }

        $ip = false !== $ip ? $ip : '127.0.0.1';

        // Fix potential CSV returned from $_SERVER variables.
        $ip_array = explode( ',', $ip );
        $ip_array = array_map( 'trim', $ip_array );

        return apply_filters( 'hbp/disabler/get_ip', $ip_array[0] );
    }

}
