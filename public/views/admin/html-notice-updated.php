<?php
/**
 * Admin View: Notice - Updated.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated hbp-disabler-message hbp-disabler-connect hbp-disabler-message--success">
    <a class="hbp-disabler-message-close notice-dismiss" href="<?php echo esc_url( $__data['dismiss_url'] ); ?>"><?php esc_html_e( 'Dismiss', 'hbp-disabler' ); ?></a>

    <p><?php esc_html_e( 'Disabler database update complete. Thank you for updating to the latest version!', 'hbp-disabler' ); ?></p>
</div>
