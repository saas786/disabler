<?php
/**
 * Admin View: Notice - Updated.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated hbp-disabler-message hbp-disabler-connect hbp-disabler-message--success">
    <a class="hbp-disabler-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'hbp-disabler-hide-notice', 'update', remove_query_arg( 'do_update_語app_prefix代替' ) ), 'hbp_disabler_hide_notices_nonce', '_hbp_disabler_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'hbp-disabler' ); ?></a>

    <p><?php esc_html_e( 'Disabler database update complete. Thank you for updating to the latest version!', 'hbp-disabler' ); ?></p>
</div>
