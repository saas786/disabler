<?php

namespace HBP\Disabler\Tools\Update;

use Hybrid\Log\Facades\Log;

function update_4_0_0_RC_2_options() {
    $options = get_option( 'disabler_settings' );

    if ( ! $options ) {
        Log::info(
            'No old Options found',
            [ 'source' => __NAMESPACE__ . '\update_4_0_0_RC_2_options' ]
        );

        return;
    }

    Log::info(
        sprintf( 'Old Plugin Options: %s', print_r( $options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_4_0_0_RC_2_options' ]
    );

    $new_options = [];

    if ( array_key_exists( 'texturization_disabled', $options ) ) {
        $new_options['frontend_disable_texturization'] = absint( $options['texturization_disabled'] ) === 1 ? 1 : 0;
    }

    if ( array_key_exists( 'capital_p_disabled', $options ) ) {
        $new_options['frontend_disable_capital_p'] = absint( $options['capital_p_disabled'] ) === 1 ? 1 : 0;
    }

    if ( array_key_exists( 'autop_disabled', $options ) ) {
        $new_options['frontend_disable_autop'] = absint( $options['autop_disabled'] ) === 1 ? 1 : 0;
    }

    if ( array_key_exists( 'selfping_disabled', $options ) ) {
        $new_options['backend_disable_self_ping'] = absint( $options['selfping_disabled'] ) === 1 ? 1 : 0;
    }

    if ( array_key_exists( 'rss_feed_disabled', $options ) ) {
        $feeds_disabled = absint( $options['rss_feed_disabled'] ) === 1 ? 1 : 0;

        $new_options['feeds_disable_feed_global']            = $feeds_disabled;
        $new_options['feeds_disable_feed_global_comments']   = $feeds_disabled;
        $new_options['feeds_disable_feed_post_comments']     = $feeds_disabled;
        $new_options['feeds_disable_feed_authors']           = $feeds_disabled;
        $new_options['feeds_disable_feed_post_types']        = $feeds_disabled;
        $new_options['feeds_disable_feed_categories']        = $feeds_disabled;
        $new_options['feeds_disable_feed_tags']              = $feeds_disabled;
        $new_options['feeds_disable_feed_custom_taxonomies'] = $feeds_disabled;
        $new_options['feeds_disable_feed_search']            = $feeds_disabled;
        $new_options['feeds_disable_atom_rdf_feeds']         = $feeds_disabled;
    }

    if ( array_key_exists( 'xmlrpc_disabled', $options ) && absint( $options['xmlrpc_disabled'] ) === 1 ) {
        $new_options['xmlrpc_disable_xmlrpc']               = 'completely';
        $new_options['xmlrpc_xmlrpc_whitelist_jetpack_ips'] = 1;
    }

    if ( array_key_exists( 'autosave_disabled', $options ) ) {
        $new_options['backend_disable_autosave'] = absint( $options['autosave_disabled'] ) === 1 ? 1 : 0;
    }

    if ( array_key_exists( 'revisions_disabled', $options ) && absint( $options['revisions_disabled'] ) === 1 ) {
        $new_options['revisions_disable_revisions'] = [ 'all' ];
    }

    if ( array_key_exists( 'hide_wp_version', $options ) ) {
        $new_options['privacy_disable_wp_generator'] = absint( $options['hide_wp_version'] ) === 1 ? 1 : 0;
    }

    if ( array_key_exists( 'fake_user_agent_value', $options ) ) {
        $new_options['privacy_fake_user_agent_value'] = absint( $options['fake_user_agent_value'] ) === 1 ? 1 : 0;
    }

    if ( array_key_exists( 'allow_usage_tracking', $options ) ) {
        $new_options['tracking_allow_usage_tracking'] = absint( $options['allow_usage_tracking'] ) === 1 ? 1 : 0;
    }

    Log::info(
        sprintf( 'New plugin options: %s', print_r( $new_options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_4_0_0_RC_2_options' ]
    );

    update_option( 'hbp_disabler_settings', $new_options );

    delete_option( 'disabler_admin_notices' );
    delete_option( 'disabler_db_version' );
    delete_option( 'disabler_version' );
    delete_option( 'disabler_settings' );
}

function update_4_0_0_RC_2_db_version() {
    PluginInstall::update_db_version( '4.0.0-RC.2' );
}
