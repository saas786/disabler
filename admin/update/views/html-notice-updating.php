<?php
/**
 * Admin View: Notice - Updating
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated disabler-message disabler-connect">
	<p><strong><?php _e( 'Disabler Data Update', 'disabler' ); ?></strong> &#8211; <?php _e( 'Your database is being updated in the background.', 'disabler' ); ?> <a href="<?php echo esc_url( add_query_arg( 'disabler_force_update', 'true', admin_url( 'index.php' ) ) ); ?>"><?php _e( 'Taking a while? Click here to run it now.', 'disabler' ); ?></a></p>
</div>
