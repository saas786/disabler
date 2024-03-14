<?php

namespace HBP\Disabler\Tools\Update;

use Hybrid\Log\Facades\Log;

function update_3_1_0_alpha_1_options() {
    $options = get_option( 'disabler_settings' );

    Log::info(
        sprintf( 'Old Plugin Options: %s', print_r( $options, true ) ),
        [ 'source' => __NAMESPACE__ . '\update_3_1_0_alpha_1_options' ]
    );

    update_option( 'hbp_disabler_settings', $options );

    delete_option( 'disabler_admin_notices' );
    // delete_option( 'disabler_settings' );
    // delete_option( 'disabler_db_version' );
}

function update_3_1_0_alpha_1_db_version() {
    PluginInstall::update_db_version( '3.1.0-alpha.1' );
}
