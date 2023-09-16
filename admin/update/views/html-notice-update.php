<?php

/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated disabler-message disabler-connect">
	<p><strong><?php _e( 'Disabler Data Update', 'disabler' ); ?></strong> &#8211; <?php _e( 'We need to update plugin\'s database to the latest version.', 'disabler' ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'disabler_do_update', 'true', admin_url( 'index.php' ) ) ); ?>" class="disabler-update-now button-primary"><?php _e( 'Run the updater', 'disabler' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery( '.disabler-update-now' ).click( 'click', function() {
		return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'disabler' ) ); ?>' ); // jshint ignore:line
	});
</script>
