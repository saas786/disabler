<?php

#namespace Disabler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Provides logging capabilities for debugging purposes.
 *
 * @class          Disabler_Logger
 * @version        2.0.0
 */
class Disabler_Logger implements Disabler_Logger_Interface {

	/**
	 * Stores registered log handlers.
	 *
	 * @var array
	 */
	protected $handlers;

	/**
	 * Minimum log level this handler will process.
	 *
	 * @var int Integer representation of minimum log level to handle.
	 */
	protected $threshold;

	/**
	 * Constructor for the logger.
	 *
	 * @param array $handlers Optional. Array of log handlers. If $handlers is not provided,
	 *     the filter 'disabler_register_log_handlers' will be used to define the handlers.
	 *     If $handlers is provided, the filter will not be applied and the handlers will be
	 *     used directly.
	 * @param string $threshold Optional. Define an explicit threshold. May be configured
	 *     via  Disabler_LOG_THRESHOLD. By default, all logs will be processed.
	 */
	public function __construct( $handlers = null, $threshold = null ) {
		if ( null === $handlers ) {
			$handlers = apply_filters( 'disabler_register_log_handlers', array() );
		}

		$register_handlers = array();

		if ( ! empty( $handlers ) && is_array( $handlers ) ) {
			foreach ( $handlers as $handler ) {
				$implements = class_implements( $handler );
				if ( is_object( $handler ) && is_array( $implements ) && in_array( 'Disabler_Log_Handler_Interface', $implements ) ) {
					$register_handlers[] = $handler;
				} else {
					disabler_doing_it_wrong(
						__METHOD__,
						sprintf(
							/* translators: 1: class name 2: Disabler_Log_Handler_Interface */
							__( 'The provided handler %1$s does not implement %2$s.', 'disabler' ),
							'<code>' . esc_html( is_object( $handler ) ? get_class( $handler ) : $handler ) . '</code>',
							'<code>Disabler_Log_Handler_Interface</code>'
						),
						'3.0'
					);
				}
			}
		}

		if ( null !== $threshold ) {
			$threshold = Disabler_Log_Levels::get_level_severity( $threshold );
		} elseif ( defined( 'Disabler_LOG_THRESHOLD' ) && Disabler_Log_Levels::is_valid_level( Disabler_LOG_THRESHOLD ) ) {
			$threshold = Disabler_Log_Levels::get_level_severity( Disabler_LOG_THRESHOLD );
		} else {
			$threshold = null;
		}

		$this->handlers  = $register_handlers;
		$this->threshold = $threshold;
	}

	/**
	 * Determine whether to handle or ignore log.
	 *
	 * @param string $level emergency|alert|critical|error|warning|notice|info|debug
	 * @return bool True if the log should be handled.
	 */
	protected function should_handle( $level ) {
		if ( null === $this->threshold ) {
			return true;
		}
		return $this->threshold <= Disabler_Log_Levels::get_level_severity( $level );
	}

	/**
	 * Add a log entry.
	 *
	 * This is not the preferred method for adding log messages. Please use log() or any one of
	 * the level methods (debug(), info(), etc.). This method may be deprecated in the future.
	 *
	 * @param string $handle
	 * @param string $message
	 * @param string $level
	 *
	 * @return bool
	 */
	public function add( $handle, $message, $level = Disabler_Log_Levels::NOTICE ) {
		$message = apply_filters( 'disabler_logger_add_message', $message, $handle );
		$this->log( $level, $message, array( 'source' => $handle, '_legacy' => true ) );
		disabler_do_deprecated_action( 'disabler_log_add', array( $handle, $message ), '3.0', 'This action has been deprecated with no alternative.' );
		return true;
	}

	/**
	 * Add a log entry.
	 *
	 * @param string $level One of the following:
	 *     'emergency': System is unusable.
	 *     'alert': Action must be taken immediately.
	 *     'critical': Critical conditions.
	 *     'error': Error conditions.
	 *     'warning': Warning conditions.
	 *     'notice': Normal but significant condition.
	 *     'info': Informational messages.
	 *     'debug': Debug-level messages.
	 * @param string $message Log message.
	 * @param array $context Optional. Additional information for log handlers.
	 */
	public function log( $level, $message, $context = array() ) {
		if ( ! Disabler_Log_Levels::is_valid_level( $level ) ) {
			/* translators: 1: Disabler_Logger::log 2: level */
			disabler_doing_it_wrong( __METHOD__, sprintf( __( '%1$s was called with an invalid level "%2$s".', 'disabler' ), '<code>Disabler_Logger::log</code>', $level ), '3.0' );
		}

		if ( $this->should_handle( $level ) ) {
			$timestamp = current_time( 'timestamp' );
			$message = apply_filters( 'disabler_logger_log_message', $message, $level, $context );

			foreach ( $this->handlers as $handler ) {
				$handler->handle( $timestamp, $level, $message, $context );
			}
		}
	}

	/**
	 * Adds an emergency level message.
	 *
	 * System is unusable.
	 *
	 * @see Disabler_Logger::log
	 */
	public function emergency( $message, $context = array() ) {
		$this->log( Disabler_Log_Levels::EMERGENCY, $message, $context );
	}

	/**
	 * Adds an alert level message.
	 *
	 * Action must be taken immediately.
	 * Example: Entire website down, database unavailable, etc.
	 *
	 * @see Disabler_Logger::log
	 */
	public function alert( $message, $context = array() ) {
		$this->log( Disabler_Log_Levels::ALERT, $message, $context );
	}

	/**
	 * Adds a critical level message.
	 *
	 * Critical conditions.
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @see Disabler_Logger::log
	 */
	public function critical( $message, $context = array() ) {
		$this->log( Disabler_Log_Levels::CRITICAL, $message, $context );
	}

	/**
	 * Adds an error level message.
	 *
	 * Runtime errors that do not require immediate action but should typically be logged
	 * and monitored.
	 *
	 * @see Disabler_Logger::log
	 */
	public function error( $message, $context = array() ) {
		$this->log( Disabler_Log_Levels::ERROR, $message, $context );
	}

	/**
	 * Adds a warning level message.
	 *
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things that are not
	 * necessarily wrong.
	 *
	 * @see Disabler_Logger::log
	 */
	public function warning( $message, $context = array() ) {
		$this->log( Disabler_Log_Levels::WARNING, $message, $context );
	}

	/**
	 * Adds a notice level message.
	 *
	 * Normal but significant events.
	 *
	 * @see Disabler_Logger::log
	 */
	public function notice( $message, $context = array() ) {
		$this->log( Disabler_Log_Levels::NOTICE, $message, $context );
	}

	/**
	 * Adds a info level message.
	 *
	 * Interesting events.
	 * Example: User logs in, SQL logs.
	 *
	 * @see Disabler_Logger::log
	 */
	public function info( $message, $context = array() ) {
		$this->log( Disabler_Log_Levels::INFO, $message, $context );
	}

	/**
	 * Adds a debug level message.
	 *
	 * Detailed debug information.
	 *
	 * @see Disabler_Logger::log
	 */
	public function debug( $message, $context = array() ) {
		$this->log( Disabler_Log_Levels::DEBUG, $message, $context );
	}

	/**
	 * Clear entries from chosen file.
	 *
	 * @deprecated 3.0.0
	 *
	 * @param string $handle
	 *
	 * @return bool
	 */
	public function clear( $handle ) {
		disabler_deprecated_function( 'Disabler_Logger::clear', '3.0', 'Disabler_Log_Handler_File::clear' );
		$handler = new Disabler_Log_Handler_File();
		return $handler->clear( $handle );
	}
}
