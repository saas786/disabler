<?php

namespace Disabler;

/**
 * Singleton class that sets up and initializes the plugin.
 *
 * @since  3.0.3
 * @access public
 * @return void
 */
final class Plugin {

    /**
     * Tracks current plugin version throughout codebase.
     *
     * @since  3.0.3
     * @access public
     * @var    string
     */
    public $version = '3.0.3';

    /**
     * Plugin db version.
     *
     * @since  3.0.3
     * @access public
     * @var    string
     */
    public $db_version = '3.0.3';

    /**
     * Plugin asset version.
     *
     * @since  3.0.3
     * @access public
     * @var    string
     */
    public $asset_version = '1524555073';

    /**
     * Minimum required PHP version.
     *
     * @since  3.0.3
     * @access private
     * @var    string
     */
    private $php_version = '5.6';

    /**
     * Plugin file path.
     *
     * @since  3.0.3
     * @access private
     * @var    string
     */
    private $plugin_file = '';

	/**
	 * Directory path to the plugin folder.
	 *
	 * @since  3.0.3
	 * @access public
	 * @var    string
	 */
	public $dir = '';

	/**
	 * Directory URI to the plugin folder.
	 *
	 * @since  3.0.3
	 * @access public
	 * @var    string
	 */
	public $uri = '';

	/**
	 * Directory URI to the plugin assets folder.
	 *
	 * @since  3.0.3
	 * @access public
	 * @var    string
	 */
	public $assets = '';

	/**
	 * Directory path to the plugin logger folder.
	 *
	 * @since  3.0.3
	 * @access public
	 * @var    string
	 */
	public $logger_dir = '';

	/**
	 * Directory path to the plugin update folder.
	 *
	 * @since  3.0.3
	 * @access public
	 * @var    string
	 */
	public $update_dir = '';

	/**
	 * Returns the instance.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->constants();
			$instance->setup();
			$instance->includes();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since  3.0.3
	 * @access private
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Magic method to output a string if trying to use the object as a string.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function __toString() {
		return 'disabler';
	}

	/**
	 * Magic method to keep the object from being cloned.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Whoah, partner!', 'disabler' ), '3.0.3' );
	}

	/**
	 * Magic method to keep the object from being unserialized.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Whoah, partner!', 'disabler' ), '3.0.3' );
	}

	/**
	 * Magic method to prevent a fatal error when calling a method that doesn't exist.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function __call( $method = '', $args = array() ) {
		_doing_it_wrong( "Disabler_Plugin::{$method}", __( 'Method does not exist.', 'disabler' ), '3.0.3' );
		unset( $method, $args );
		return null;
	}

	/**
	 * Initial plugin setup.
	 *
	 * @since  3.0.3
	 * @access private
	 * @return void
	 */
	private function constants() {
		$upload_dir = wp_upload_dir();
		define( 'DISABLER_LOG_DIR', $upload_dir['basedir'] . '/disabler-logs/' );
	}

	/**
	 * Initial plugin setup.
	 *
	 * @since  3.0.3
	 * @access private
	 * @return void
	 */
	private function setup() {

        $this->plugin_file = DISABLER_PLUGIN_FILE;

		$this->dir         = trailingslashit( plugin_dir_path( $this->plugin_file ) );
		$this->uri         = trailingslashit( plugin_dir_url(  $this->plugin_file ) );
		$this->assets      = trailingslashit( $this->uri . 'assets' );

		$this->logger_dir  = trailingslashit( $this->dir . 'inc/logger' );
		$this->update_dir  = trailingslashit( $this->dir . 'admin/update' );
	}

	/**
	 * Loads include and admin files for the plugin.
	 *
	 * @since  3.0.3
	 * @access private
	 * @return void
	 */
	private function includes() {

		require_once( $this->logger_dir . 'abstracts/class-plugin-log-levels.php' );
		require_once( $this->logger_dir . 'interfaces/class-plugin-logger-interface.php' );
		require_once( $this->logger_dir . 'interfaces/class-plugin-log-handler-interface.php' );
		require_once( $this->logger_dir . 'abstracts/abstract-plugin-log-handler.php' );
		require_once( $this->logger_dir . 'class-plugin-log-handler-file.php' );
		require_once( $this->logger_dir . 'class-plugin-logger.php' );

		require_once( $this->dir . 'inc/functions-options.php' );
		require_once( $this->dir . 'inc/frontend/functions.php' );

		# Load logger files.
		require_once( $this->logger_dir . 'logger-init.php' );

		# Load install files.
		require_once( $this->update_dir . 'install-init.php' );

		# Load admin files.
		if ( $this->is_request( 'admin' ) ) {
			require_once( $this->dir . 'admin/class-settings.php' );
		}

		if ( $this->is_request( 'cron' ) && disabler_allow_usage_tracking() ) {
			include_once $this->dir . 'admin/tracking/class-disabler-tracker.php';
		}
	}

	/**
	 * Sets up initial actions.
	 *
	 * @since  3.0.3
	 * @access private
	 * @return void
	 */
	private function setup_actions() {

		// Internationalize the text strings used.
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

		// Register activation hook.
		register_activation_hook( $this->plugin_file, array( $this, 'activation' ) );
	}

	/**
	 * Loads the translation files.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function i18n() {

		load_plugin_textdomain( 'disabler', false, trailingslashit( dirname( plugin_basename( $this->plugin_file ) ) ) . 'lang' );
	}

	/**
	 * Method that runs only when the plugin is activated.
	 *
	 * @since  3.0.3
	 * @access public
	 * @global $wpdb
	 * @return void
	 */
	public function activation() {}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}
}

/**
 * Gets the instance of the `Disabler\Plugin` class.  This function is useful for quickly grabbing data
 * used throughout the plugin.
 *
 * @since  3.0.3
 * @access public
 * @return object
 */
function plugin() {
	return Plugin::get_instance();
}

// Let's do this thang!
plugin();
