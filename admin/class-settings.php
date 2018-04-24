<?php
/**
 * Plugin settings screen.
 *
 */

namespace Disabler;

/**
 * Sets up and handles the plugin settings screen.
 *
 * @since  3.0.3
 * @access public
 */
final class Settings_Page {

	/**
	 * Settings page name.
	 *
	 * @since  3.0.3
	 * @access public
	 * @var    string
	 */
	public $settings_page = '';

	/**
	 * Sets up the needed actions for adding and saving the meta boxes.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	private function __construct() {

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

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
		}

		return $instance;
	}

	/**
	 * Sets up custom admin menus.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function admin_menu() {

		// Create the settings page.
		$this->settings_page = add_options_page(
			esc_html_x( 'Disabler', 'admin screen', 'disabler' ),
			esc_html_x( 'Disabler', 'admin screen', 'disabler' ),
			apply_filters( 'disabler_settings_capability', 'manage_options' ),
			'disabler-settings',
			array( $this, 'settings_page' )
		);

		if ( $this->settings_page ) {

			// Register settings.
			add_action( 'admin_init', array( $this, 'register_settings' ) );

		}

	}

	/**
	 * Registers the plugin settings.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	function register_settings() {

		// Register the setting.
		register_setting( 'disabler_settings', 'disabler_settings', array( $this, 'validate_settings' ) );

		/* === Settings Sections === */

		add_settings_section( 'front_end', esc_html__( 'Front End Settings', 'disabler' ), array( $this, 'section_front_end' ), $this->settings_page );
		add_settings_section( 'back_end', esc_html__( 'Back End Settings', 'disabler' ), array( $this, 'section_back_end' ), $this->settings_page );
		add_settings_section( 'privacy', esc_html__( 'Privacy Settings', 'disabler' ), array( $this, 'section_privacy' ), $this->settings_page );
		add_settings_section( 'tracking', esc_html__( 'Usage Tracking Settings', 'disabler' ), array( $this, 'section_tracking' ), $this->settings_page );

		/* === Settings Fields === */

		// Front End section fields
		add_settings_field( 'disable_texturization', esc_html__( 'Disable Texturization', 'disabler' ), array( $this, 'field_disable_texturization' ), $this->settings_page, 'front_end' );
		add_settings_field( 'disable_capital_p', esc_html__( 'Disable Capital P', 'disabler' ), array( $this, 'field_disable_capital_p' ), $this->settings_page, 'front_end' );
		add_settings_field( 'disable_autop', esc_html__( 'Disable paragraphs', 'disabler' ), array( $this, 'field_disable_autop' ), $this->settings_page, 'front_end' );

		// Back End section fields
		add_settings_field( 'disable_selfping', esc_html__( 'Disable self pings', 'disabler' ), array( $this, 'field_disable_selfping' ), $this->settings_page, 'back_end' );
		add_settings_field( 'disable_rss_feed', esc_html__( 'Disable RSS feeds', 'disabler' ), array( $this, 'field_disable_rss_feed' ), $this->settings_page, 'back_end' );
		add_settings_field( 'disable_xmlrpc', esc_html__( 'Disable XML-RPC', 'disabler' ), array( $this, 'field_disable_xmlrpc' ), $this->settings_page, 'back_end' );
		add_settings_field( 'disable_autosave', esc_html__( 'Disable auto-saving', 'disabler' ), array( $this, 'field_disable_autosave' ), $this->settings_page, 'back_end' );
		add_settings_field( 'disable_revisions', esc_html__( 'Disable revisions', 'disabler' ), array( $this, 'field_disable_revisions' ), $this->settings_page, 'back_end' );

		// Privacy section fields
		add_settings_field( 'hide_wp_version', esc_html__( 'Hide WordPress Version', 'disabler' ), array( $this, 'field_hide_wp_version' ), $this->settings_page, 'privacy' );
		add_settings_field( 'fake_user_agent_value', esc_html__( 'Fake User Agent', 'disabler' ), array( $this, 'field_fake_user_agent_value' ), $this->settings_page, 'privacy' );

		add_settings_field( 'allow_usage_tracking', esc_html__( 'Allow Usage Tracking', 'disabler' ), array( $this, 'field_allow_usage_tracking' ), $this->settings_page, 'tracking' );
	}

	/**
	 * Validates the plugin settings.
	 *
	 * @since  3.0.3
	 * @access public
	 * @param  array  $input
	 * @return array
	 */
	function validate_settings( $settings ) {

		// Validate true/false checkboxes.
		$settings['texturization_disabled'] = ! empty( $settings['texturization_disabled'] ) ? true : false;
		$settings['capital_p_disabled']     = ! empty( $settings['capital_p_disabled'] ) ? true : false;
		$settings['autop_disabled']         = ! empty( $settings['autop_disabled'] ) ? true : false;
		$settings['selfping_disabled']      = ! empty( $settings['selfping_disabled'] ) ? true : false;
		$settings['rss_feed_disabled']      = ! empty( $settings['rss_feed_disabled'] ) ? true : false;
		$settings['xmlrpc_disabled']        = ! empty( $settings['xmlrpc_disabled'] ) ? true : false;
		$settings['autosave_disabled']      = ! empty( $settings['autosave_disabled'] ) ? true : false;
		$settings['revisions_disabled']     = ! empty( $settings['revisions_disabled'] ) ? true : false;
		$settings['hide_wp_version']        = ! empty( $settings['hide_wp_version'] ) ? true : false;
		$settings['fake_user_agent_value']  = ! empty( $settings['fake_user_agent_value'] ) ? true : false;
		$settings['allow_usage_tracking']   = ! empty( $settings['allow_usage_tracking'] ) ? true : false;

		// Return the validated/sanitized settings.
		return $settings;
	}

	/**
	 * Front End section callback.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function section_front_end() { ?>

		<p class="description">
			<?php esc_html_e( 'These are settings are changes on the front end. These are the things that affect what your site looks like when other people visit. What THEY see. While these are actually things that annoy you, it all comes back to being things on the forward facing part of your site.', 'disabler' ); ?>
		</p>
	<?php }

	/**
	 * Back End section callback.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function section_back_end() { ?>

		<p class="description">
			<?php esc_html_e( 'Back End settings affect how WordPress runs. Nothing here will break your install, but some turn off \'desired\' functions.', 'disabler' ); ?>
		</p>
	<?php }

	/**
	 * Privacy section callback.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function section_privacy() { ?>

		<p class="description">
			<?php esc_html_e( 'These settings help obfuscate information about your blog to the world (inclyding to Wordpress.org). While they don\'t protect you from anything, they do make it a little harder for people to get information about you and your site.', 'disabler' ); ?>
		</p>
	<?php }

	/**
	 * User Tracking section callback.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function section_tracking() { ?>

		<p class="description">
			<?php esc_html_e( 'This setting will allow us to collect data about your usage of plugin. We will not be collecting any personally identifying data. We will be only collecting WordPress Info, Installed Plugins / Themes, Server Info etc.', 'disabler' ); ?>
		</p>
	<?php }

	/**
	 * Smartquotes / Texturization field callback.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function field_disable_texturization() { ?>

		<label>
			<input type="checkbox" name="disabler_settings[texturization_disabled]" value="true" <?php checked( disabler_texturization_disabled() ); ?> />
			<?php esc_html_e( '-- smart quotes (a.k.a. curly quotes), em dash, en dash and ellipsis.', 'disabler' ); ?>
		</label>
	<?php }

	/**
	 * Capital P in WordPress auto-correct
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function field_disable_capital_p() { ?>

		<label>
			<input type="checkbox" name="disabler_settings[capital_p_disabled]" value="true" <?php checked( disabler_capital_p_disabled() ); ?> />
			<?php esc_html_e( 'auto-correction of WordPress capitalization.', 'disabler' ); ?>
		</label>
	<?php }

	/**
	 * Remove the <p> from being automagically added in posts
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function field_disable_autop() { ?>

		<label>
			<input type="checkbox" name="disabler_settings[autop_disabled]" value="true" <?php checked( disabler_autop_disabled() ); ?> />
			<?php esc_html_e( '(i.e. <p> tags) from being automatically inserted in your posts.', 'disabler' ); ?>
		</label>
	<?php }

	/**
	 * Seflping
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function field_disable_selfping() { ?>

		<label>
			<input type="checkbox" name="disabler_settings[selfping_disabled]" value="true" <?php checked( disabler_selfping_disabled() ); ?> />
			<?php esc_html_e( '(i.e. trackbacks/pings from your own domain).', 'disabler' ); ?>
		</label>
	<?php }

	/**
	 * RSS Feed
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function field_disable_rss_feed() { ?>

		<label>
			<input type="checkbox" name="disabler_settings[rss_feed_disabled]" value="true" <?php checked( disabler_rss_feed_disabled() ); ?> />
			<?php esc_html_e( 'It will disable all RSS fields, like RSS, RSS2 etc.', 'disabler' ); ?>
		</label>
	<?php }

	/**
	 * xmlrpc
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function field_disable_xmlrpc() { ?>

		<label>
			<input type="checkbox" name="disabler_settings[xmlrpc_disabled]" value="true" <?php checked( disabler_xmlrpc_disabled() ); ?> />
			<?php esc_html_e( 'It will disable the XML-RPC API.', 'disabler' ); ?>
		</label>
	<?php }

	/**
	 * autosave
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function field_disable_autosave() { ?>

		<label>
			<input type="checkbox" name="disabler_settings[autosave_disabled]" value="true" <?php checked( disabler_autosave_disabled() ); ?> />
			<?php esc_html_e( 'It will disable autosave feature for posts etc.', 'disabler' ); ?>
		</label>
	<?php }

	/**
	 * Revisions
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function field_disable_revisions() { ?>

		<label>
			<input type="checkbox" name="disabler_settings[revisions_disabled]" value="true" <?php checked( disabler_revisions_disabled() ); ?> />
			<?php esc_html_e( 'It will disable revisions for posts etc.', 'disabler' ); ?>
		</label>
	<?php }

	/**
	 * hide WordPress version
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function field_hide_wp_version() { ?>

		<label>
			<input type="checkbox" name="disabler_settings[hide_wp_version]" value="true" <?php checked( disabler_hide_wp_version() ); ?> />
			<?php esc_html_e( 'It will prevent WordPress from printing it\'s version in your headers (only seen via View Source).', 'disabler' ); ?>
		</label>
	<?php }

	/**
	 * fake user agent value sent with an HTTP request
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function field_fake_user_agent_value() { ?>

		<label>
			<input type="checkbox" name="disabler_settings[fake_user_agent_value]" value="true" <?php checked( disabler_fake_user_agent_value() ); ?> />
			<?php esc_html_e( 'It will prevent WordPress from sending your URL information when checking for updates.', 'disabler' ); ?>
		</label>
	<?php }

	/**
	 * Allow usage tracking?
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function field_allow_usage_tracking() { ?>

		<label>
			<input type="checkbox" name="disabler_settings[allow_usage_tracking]" value="true" <?php checked( disabler_allow_usage_tracking() ); ?> />
			<?php esc_html_e( 'It will allows us to collect data about our plugin usage.', 'disabler' ); ?>
		</label>
	<?php }

	/**
	 * Renders the settings page.
	 *
	 * @since  3.0.3
	 * @access public
	 * @return void
	 */
	public function settings_page() {
		?>

		<div class="wrap">
			<h1><?php esc_html_e( 'Settings', 'disabler' ); ?></h1>

			<?php //settings_errors(); // not needed on options.php page ?>
			<p><?php esc_html_e( 'Here\'s where you can disable whatever you want.', 'disabler' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( 'disabler_settings' ); ?>
				<?php do_settings_sections( $this->settings_page ); ?>
				<?php submit_button( esc_attr__( 'Save Settings', 'disabler' ), 'primary' ); ?>
			</form>

		</div><!-- wrap -->

	<?php }
}

Settings_Page::get_instance();
