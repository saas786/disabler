<?php
/**
 * Admin View: Custom Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated disabler-message">
	<a class="disabler-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'disabler-hide-notice', $notice ), 'disabler_hide_notices_nonce', '_disabler_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'disabler' ); ?></a>
	<?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
</div>