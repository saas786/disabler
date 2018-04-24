<?php

add_filter( 'disabler_register_log_handlers', 'disabler_register_default_log_handler' );

/**
 * Registers the default log handler.
 */
function disabler_register_default_log_handler( $handlers ) {
	if ( defined( 'DISABLER_LOG_HANDLER' ) && class_exists( 'Disabler_Log_Handler' ) ) {
		$handler_class = Disabler_Log_Handler;
		$default_handler = new $handler_class();
	} else {
		$default_handler = new Disabler_Log_Handler_File();
	}

	array_push( $handlers, $default_handler );

	return $handlers;
}

/**
 * Get a shared logger instance.
 *
 * Use the disabler_logging_class filter to change the logging class. You may provide one of the following:
 *     - a class name which will be instantiated as `new $class` with no arguments
 *     - an instance which will be used directly as the logger
 * In either case, the class or instance *must* implement Disabler_Logger_Interface.
 *
 * @see Disabler_Logger_Interface
 *
 * @return Disabler_Logger
 */
function disabler_get_logger() {
	static $logger = null;

	if ( null === $logger ) {

		$class = apply_filters( 'disabler_logging_class', 'Disabler_Logger' );
		$implements = class_implements( $class );

		if ( is_array( $implements ) && in_array( 'Disabler_Logger_Interface', $implements ) ) {
			if ( is_object( $class ) ) {
				$logger = $class;
			} else {
				$logger = new $class;
			}
		} else {
			disabler_doing_it_wrong(
				__FUNCTION__,
				sprintf(
					__( 'The class <code>%s</code> provided by disabler_logging_class filter must implement <code>Disabler_Logger_Interface</code>.', 'disabler' ),
					esc_html( is_object( $class ) ? get_class( $class ) : $class )
				),
				'2.7'
			);
			$logger = new Disabler_Logger();
		}
	}
	
	return $logger;
}