<?php
/**
 * Admin View: Notice - Updating.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$pending_actions_url = class_exists( 'WC_API' ) ? add_query_arg( array(
    'page'   => 'wc-status',
    'tab'    => 'action-scheduler',
    'status' => 'pending',
    's'      => 'hbp_disabler_run_update',
), admin_url( 'admin.php' ) ) : add_query_arg( array(
    'page'   => 'action-scheduler',
    'status' => 'pending',
    's'      => 'hbp_disabler_run_update',
), admin_url( 'tools.php' ) );

$cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
$cron_cta      = $cron_disabled
    ? __( 'You can manually run queued updates here.', 'hbp-disabler' )
    : __( 'View progress &rarr;', 'hbp-disabler' );

?>

<div id="message" class="updated hbp-disabler-message hbp-disabler-connect">
    <p>
        <strong><?php esc_html_e( 'Disabler database update', 'hbp-disabler' ); ?></strong><br>
        <?php esc_html_e( 'Disabler is updating the database in the background. The database update process may take a little while, so please be patient.', 'hbp-disabler' ); ?>
        <?php
        if ( $cron_disabled ) {
            echo '<br>' . esc_html__( 'Note: WP CRON has been disabled on your install which may prevent this update from completing.', 'hbp-disabler' );
        }
        ?>
        &nbsp;<a href="<?php echo esc_url( $pending_actions_url ); ?>"><?php echo esc_html( $cron_cta ); ?></a>
    </p>
</div>
