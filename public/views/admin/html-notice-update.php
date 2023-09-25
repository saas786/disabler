<?php
/**
 * Admin View: Notice - Update.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$update_url = wp_nonce_url(
    add_query_arg( 'do_update_hbp_disabler', 'true', admin_url( 'index.php' ) ),
    'hbp_disabler_db_update',
    'hbp_disabler_db_update_nonce'
);

?>

<div id="message" class="updated hbp-disabler-message hbp-disabler-connect">
    <p>
        <strong><?php esc_html_e( 'Disabler database update required', 'hbp-disabler' ); ?></strong>
    </p>
    <p>
        <?php
        esc_html_e( 'Disabler has been updated! To keep things running smoothly, we have to update your database to the newest version.', 'hbp-disabler' );
        echo '<br/>';
        esc_html_e( 'The database update process runs in the background and may take a little while, so please be patient.', 'hbp-disabler' )
        ?>
    </p>
    <p class="submit">
        <a href="<?php echo esc_url( $update_url ); ?>" class="hbp-disabler-update-now button-primary">
            <?php esc_html_e( 'Update Disabler Database', 'hbp-disabler' ); ?>
        </a>
    </p>
</div>
<script type="text/javascript">
    jQuery( '.hbp-disabler-update-now' ).click( 'click', function() {
        return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'hbp-disabler' ) ); ?>' ); // jshint ignore:line
    });
</script>
