<?php
/**
 * Admin View: Notice - Install
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated disabler-message disabler-connect">
	<p><?php _e( '<strong>Welcome to Disabler</strong> &#8211; You&lsquo;re almost ready :)', 'disabler' ); ?></p>
	<!--<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=disabler-setup' ) ); ?>" class="button-primary"><?php _e( 'Run the Setup Wizard', 'disabler' ); ?></a> <a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'disabler-hide-notice', 'install' ), 'disabler_hide_notices_nonce', '_disabler_notice_nonce' ) ); ?>"><?php _e( 'Skip setup', 'disabler' ); ?></a></p>-->
</div>
