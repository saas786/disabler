<?php
/**
 * @see https://github.com/Yoast/wordpress-seo/blob/bbfd0653cbb0926171c0987acfad2976be0b258e/src/integrations/admin/crawl-settings-integration.php#L119
 */

namespace HBP\Disabler\Optimize;

use HBP\Disabler\Admin\Options;
use HBP\Disabler\Contracts\Traits\Utils;
use Hybrid\Contracts\Bootable;
use Hybrid\Tools\WordPress\Traits\AccessiblePrivateMethods;

class Feeds implements Bootable {

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
        if ( Options::get( 'feeds_disable_feed_global' ) ) {
            add_action( 'feed_links_show_posts_feed', '__return_false' );
        }

        if ( Options::get( 'feeds_disable_feed_global_comments' ) ) {
            add_action( 'feed_links_show_comments_feed', '__return_false' );
        }

        add_action( 'wp', [ $this, 'maybeDisableFeeds' ] );
        add_action( 'wp', [ $this, 'maybeRedirectFeeds' ], -10000 );
    }

    /**
     * Disable feeds on selected cases.
     */
    public function maybeDisableFeeds() {
        if ( is_singular() && Options::get( 'feeds_disable_feed_post_comments' )
            || ( is_author() && Options::get( 'feeds_disable_feed_authors' ) )
            || ( is_category() && Options::get( 'feeds_disable_feed_categories' ) )
            || ( is_tag() && Options::get( 'feeds_disable_feed_tags' ) )
            || ( is_tax() && Options::get( 'feeds_disable_feed_custom_taxonomies' ) )
            || ( is_post_type_archive() && Options::get( 'feeds_disable_feed_post_types' ) )
            || ( is_search() && Options::get( 'feeds_disable_feed_search' ) ) ) {
            remove_action( 'wp_head', 'feed_links_extra', 3 );
        }
    }

    /**
     * Redirect feeds we don't want away.
     */
    public function maybeRedirectFeeds() {
        global $wp_query;

        if ( ! is_feed() ) {
            return;
        }

        if ( in_array( get_query_var( 'feed' ), [ 'atom', 'rdf' ], true )
            && Options::get( 'feeds_disable_atom_rdf_feeds' )
        ) {
            $this->redirectFeedOrDie( home_url(), 'We disable Atom/RDF feeds for performance reasons.' );
        }

        // Only if we're on the global feed, the query is _just_ `'feed' => 'feed'`, hence this check.
        if ( ( [ 'feed' => 'feed' ] === $wp_query->query
                || [ 'feed' => 'atom' ] === $wp_query->query
                || [ 'feed' => 'rdf' ] === $wp_query->query )
            && Options::get( 'feeds_disable_feed_global' ) ) {
            $this->redirectFeedOrDie( home_url(), 'We disable the RSS feed for performance reasons.' );
        }

        if ( is_comment_feed()
            && ! (
                is_singular()
                || is_attachment()
            )
            && Options::get( 'feeds_disable_feed_global_comments' )
        ) {
            $this->redirectFeedOrDie( home_url(), 'We disable comment feeds for performance reasons.' );
        } elseif ( is_comment_feed()
            && is_singular()
            && ( Options::get( 'feeds_disable_feed_post_comments' ) || Options::get( 'feeds_disable_feed_global_comments' ) ) ) {
            $url = get_permalink( get_queried_object() );
            $this->redirectFeedOrDie( $url, 'We disable post comment feeds for performance reasons.' );
        }

        if ( is_author() && Options::get( 'feeds_disable_feed_authors' ) ) {
            $author_id = (int) get_query_var( 'author' );
            $url       = get_author_posts_url( $author_id );
            $this->redirectFeedOrDie( $url, 'We disable author feeds for performance reasons.' );
        }

        if ( ( is_category() && Options::get( 'feeds_disable_feed_categories' ) )
            || ( is_tag() && Options::get( 'feeds_disable_feed_tags' ) )
            || ( is_tax() && Options::get( 'feeds_disable_feed_custom_taxonomies' ) ) ) {
            $term = get_queried_object();
            $url  = get_term_link( $term, $term->taxonomy );
            if ( is_wp_error( $url ) ) {
                $url = home_url();
            }

            $this->redirectFeedOrDie( $url, 'We disable taxonomy feeds for performance reasons.' );
        }

        if ( is_post_type_archive() && Options::get( 'feeds_disable_feed_post_types' ) ) {
            $url = get_post_type_archive_link( $this->getQueriedPostType() );
            $this->redirectFeedOrDie( $url, 'We disable post type feeds for performance reasons.' );
        }

        if ( is_search() && Options::get( 'feeds_disable_feed_search' ) ) {
            $url = trailingslashit( home_url() ) . '?s=' . get_search_query();
            $this->redirectFeedOrDie( $url, 'We disable search RSS feeds for performance reasons.' );
        }
    }

    /**
     * Redirect a feed result to somewhere else.
     *
     * @param string $url    The location we're redirecting to.
     * @param string $reason The reason we're redirecting.
     */
    private function redirectFeedOrDie( $url, $reason ) {
        if ( '404' === Options::get( 'feeds_rss_feed_redirect' ) ) {
            wp_die(
                sprintf(
                    // Translators: Placeholders for the homepage link.
                    esc_html__( 'No feed available, please visit our %1$shomepage%2$s!', 'hbp-disabler' ),
                    ' <a href="' . esc_url( home_url( '/' ) ) . '">',
                    '</a>'
                )
            );
        }

        header_remove( 'Content-Type' );
        header_remove( 'Last-Modified' );

        $this->cacheControlHeader( 7 * \DAY_IN_SECONDS );

        wp_safe_redirect( $url, 301, 'Yoast SEO: ' . $reason );
        exit;
    }

    /**
     * Sends a cache control header.
     *
     * @param int $expiration The expiration time.
     */
    public function cacheControlHeader( $expiration ) {
        header_remove( 'Expires' );

        // The cacheability of the current request. 'public' allows caching, 'private' would not allow caching by proxies like CloudFlare.
        $cacheability = 'public';
        $format       = '%1$s, max-age=%2$d, s-maxage=%2$d, stale-while-revalidate=120, stale-if-error=14400';

        if ( is_user_logged_in() ) {
            $expiration   = 0;
            $cacheability = 'private';
            $format       = '%1$s, max-age=%2$d';
        }

        header( sprintf( 'Cache-Control: ' . $format, $cacheability, $expiration ), true );
    }

    /**
     * Retrieves the queried post type.
     *
     * @return string The queried post type.
     */
    private function getQueriedPostType() {
        $post_type = get_query_var( 'post_type' );
        if ( is_array( $post_type ) ) {
            $post_type = reset( $post_type );
        }

        return $post_type;
    }

}
