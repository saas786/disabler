<?php
/**
 * Admin View: Notice - Updated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated disabler-message disabler-connect disabler-message--success">
	<a class="disabler-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'disabler-hide-notice', 'update', remove_query_arg( 'disabler_do_update' ) ), 'disabler_hide_notices_nonce', '_disabler_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'disabler' ); ?></a>

	<p><?php _e( 'Disabler data update complete. Thank you for updating to the latest version!', 'disabler' ); ?></p>
</div>
