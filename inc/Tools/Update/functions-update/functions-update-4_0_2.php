<?php

namespace HBP\Disabler\Tools\Update;

use Hybrid\Log\Facades\Log;

function update_4_0_2_options() {
    $options = get_option( 'hbp_disabler_settings' );

    if ( ! $options ) {
        Log::info(
            'No old Options found',
            [ 'source' => __NAMESPACE__ . '\update_4_0_2_options' ]
        );

        return;
    }

    Log::info(
        sprintf( 'Old Plugin Options: %s', print_r( $options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_4_0_2_options' ]
    );

    $new_options = [];

    if ( array_key_exists( 'backend_disable_autosave', $options ) ) {
        $new_options['editor_disable_autosave'] = absint( $options['backend_disable_autosave'] ) === 1 ? 'yes' : 'no';
    }

    if ( array_key_exists( 'frontend_disable_texturization', $options ) ) {
        $new_options['editor_disable_texturization'] = absint( $options['frontend_disable_texturization'] ) === 1
            ? 1
            : 0;
    }

    if ( array_key_exists( 'frontend_disable_capital_p', $options ) ) {
        $new_options['editor_disable_capital_p'] = absint( $options['frontend_disable_capital_p'] ) === 1 ? 1 : 0;
    }

    if ( array_key_exists( 'frontend_disable_autop', $options ) ) {
        $new_options['editor_disable_autop'] = absint( $options['frontend_disable_autop'] ) === 1 ? 1 : 0;
    }

    Log::info(
        sprintf( 'New plugin options: %s', print_r( $new_options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_4_0_2_options' ]
    );

    // Merged onto the existing row, not written over it.
    //
    // This routine renames four editor settings. Assigning $new_options
    // directly discarded everything else in the row -- and the row at this
    // point is what update_4_0_0_RC_2_options() wrote moments earlier in the
    // same upgrade, so a site upgrading from an older version arrived here
    // with its feeds, xmlrpc, revisions and privacy settings set and left
    // with only the four keys below.
    //
    // The overwrite also made the routine destructive on a second pass:
    // ActionScheduler can retry an update callback, and a second run found
    // none of the four legacy keys, built an empty array and wrote that over
    // the completed migration. With a merge, a second pass adds nothing and
    // writes the row back unchanged.
    //
    // The four consumed keys are removed by name rather than left in place,
    // so the row does not carry both spellings of the same setting forward.
    $options = array_merge( $options, $new_options );

    unset(
        $options['backend_disable_autosave'],
        $options['frontend_disable_texturization'],
        $options['frontend_disable_capital_p'],
        $options['frontend_disable_autop']
    );

    update_option( 'hbp_disabler_settings', $options );
}

function update_4_0_2_db_version() {
    PluginInstall::update_db_version( '4.0.2' );
}
