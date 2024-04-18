<?php
/**
 * Admin View: Custom Notices.
 */

use function Hybrid\Tools\collect;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$data = collect( $__data );

?>
<div id="message" class="updated hbp-disabler-message">
    <a
        class="hbp-disabler-message-close notice-dismiss"
        href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'hbp-disabler-hide-notice', $data->get( 'notice' ) ), 'hbp_disabler_hide_notices_nonce', '_hbp_disabler_notice_nonce' ) ); ?>"
    >
        <?php
        _e( 'Dismiss', 'hbp-disabler' ); // phpcs:ignore: WordPress.Security.EscapeOutput.UnsafePrintingFunction
        ?>
    </a>
    <?php echo wp_kses_post( wpautop( $data->get( 'notice_html' ) ) ); ?>
</div>
