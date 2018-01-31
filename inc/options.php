<?php

class Disabler_Options_Class {
	var $options;

	function __construct() {
		$this->options = get_option( 'disabler_options' );

		add_action( 'admin_menu', array( $this, 'disabler_menu' ) );
		add_action( 'admin_init' , array( $this, 'disabler_settings' ) );
	}

	// Load the options page
	function disabler_menu() {
		$plugin_page = add_options_page( 'Disabler', 'Disabler', 'activate_plugins', 'disabler_options_page', array( $this, 'plugin_options' ) );
	}

	function sanitize( $posted_options ) {
		$default_options = array(
			'disabler_smartquotes' => '0',
			'disabler_capitalp' => '0',
			'disabler_autop' => '0',
			'disabler_selfping' => '0',
			'disabler_norss' => '0',
			'disabler_xmlrpc' => '0',
			'disabler_revisions' => '0',
			'disabler_autosave' => '0',
			'disabler_version' => '0',
			'disabler_nourl' => '0',
		);

		foreach ( $default_options as $key => $value ) {
			if ( ! isset( $posted_options[ $key ] ) ) {
				$posted_options[ $key ] = $value;
			}
		}

		return $posted_options;
	}

	function disabler_settings() {
		register_setting( 'ippy_dis_options', 'disabler_options', array( $this, 'sanitize' ) );

		add_settings_section( 'frontend_settings', __( 'Front End Settings', 'disabler' ), array( $this, 'callback_frontend' ), 'disabler_options_page' );
		add_settings_field( 'new_smartquotes', __( 'Disable Texturization -- smart quotes (a.k.a. curly quotes), em dash, en dash and ellipsis.', 'disabler' ), array( $this, 'smartquotes' ), 'disabler_options_page', 'frontend_settings', array( 'label_for' => 'new_smartquotes' ) );
		add_settings_field( 'new_capitalp', __( 'Disable auto-correction of WordPress capitalization.', 'disabler' ), array( $this, 'capitalp' ), 'disabler_options_page', 'frontend_settings', array( 'label_for' => 'new_capitalp' ) );
		add_settings_field( 'new_autop', __( 'Disable paragraphs (i.e. &lt;p&gt;  tags) from being automatically inserted in your posts.', 'disabler' ), array( $this, 'autop' ), 'disabler_options_page', 'frontend_settings', array( 'label_for' => 'new_autop' ) );

		add_settings_section( 'backend_settings', __( 'Back End Settings', 'disabler' ), array( $this, 'callback_backend' ), 'disabler_options_page' );
		add_settings_field( 'new_selfping', __( 'Disable self pings (i.e. trackbacks/pings from your own domain).', 'disabler' ), array( $this, 'selfping' ), 'disabler_options_page', 'backend_settings', array( 'label_for' => 'new_selfping' ) );
		add_settings_field( 'new_norss', __( 'Disable all RSS feeds.', 'disabler' ), array( $this, 'norss' ), 'disabler_options_page', 'backend_settings', array( 'label_for' => 'new_norss' ) );
		add_settings_field( 'new_xmlrpc', __( 'Disable XML-RPC.', 'disabler' ), array( $this, 'xmlrpc' ), 'disabler_options_page', 'backend_settings', array( 'label_for' => 'new_xmlrpc' ) );
		add_settings_field( 'new_autosave', __( 'Disable auto-saving of posts.', 'disabler' ), array( $this, 'autosave' ), 'disabler_options_page', 'backend_settings', array( 'label_for' => 'new_autosave' ) );
		add_settings_field( 'new_revisions', __( 'Disable post revisions.', 'disabler' ), array( $this, 'revisions' ), 'disabler_options_page', 'backend_settings', array( 'label_for' => 'new_revisions' ) );

		add_settings_section( 'privacy_settings', __( 'Privacy Settings', 'disabler' ), array( $this, 'callback_privacy' ), 'disabler_options_page' );
		add_settings_field( 'new_version', __( 'Disable WordPress from printing it\'s version in your headers (only seen via View Source).', 'disabler' ), array( $this, 'version' ), 'disabler_options_page', 'privacy_settings', array( 'label_for' => 'new_version' ) );
		add_settings_field( 'new_nourl', __( 'Disable WordPress from sending your URL information when checking for updates.', 'disabler' ), array( $this, 'nourl' ), 'disabler_options_page', 'privacy_settings', array( 'label_for' => 'new_nourl' ) );
	}

	function callback_frontend() {
		_e( 'These are settings are changes on the front end. These are the things that affect what your site looks like when other people visit. What THEY see.  While these are actually things that annoy <strong>you</strong>, it all comes back to being things on the forward facing part of your site.', 'disabler' );
	}

	function callback_backend() {
		_e( 'Back End settings affect how WordPress runs. Nothing here will <em>break</em> your install, but some turn off \'desired\' functions.', 'disabler' );
	}

	function callback_privacy() {
		_e( 'These settings help obfuscate information about your blog to the world (inclyding to Wordpress.org). While they don\'t protect you from anything, they do make it a little harder for people to get information about you and your site.', 'disabler' );
	}

	function smartquotes() {
		$checked = checked( $this->options['disabler_smartquotes'], '1', false );
		echo '<input type="checkbox" id="new_smartquotes" name="disabler_options[disabler_smartquotes]" value="1" ' . $checked . ' />';
	}

	function capitalp() {
		$checked = checked( $this->options['disabler_capitalp'], '1', false );
		echo '<input type="checkbox" id="new_capitalp" name="disabler_options[disabler_capitalp]" value="1" ' . $checked . ' />';
	}

	function autop() {
		$checked = checked( $this->options['disabler_autop'], '1', false );
		echo '<input type="checkbox" id="new_autop" name="disabler_options[disabler_autop]" value="1" ' . $checked . ' />';
	}

	function selfping() {
		$checked = checked( $this->options['disabler_selfping'], '1', false );
		echo '<input type="checkbox" id="new_selfping" name="disabler_options[disabler_selfping]" value="1" ' . $checked . ' />';
	}

	function norss() {
		$checked = checked( $this->options['disabler_norss'], '1', false );
		echo '<input type="checkbox" id="new_norss" name="disabler_options[disabler_norss]" value="1" ' . $checked . ' />';
	}

	function xmlrpc() {
		$checked = checked( $this->options['disabler_xmlrpc'], '1', false );
		echo '<input type="checkbox" id="new_xmlrpc" name="disabler_options[disabler_xmlrpc]" value="1" ' . $checked . ' />';
	}

	function autosave() {
		$checked = checked( $this->options['disabler_autosave'], '1', false );
		echo '<input type="checkbox" id="new_autosave" name="disabler_options[disabler_autosave]" value="1" ' . $checked . ' />';
	}

	function revisions() {
		$checked = checked( $this->options['disabler_revisions'], '1', false );
		echo '<input type="checkbox" id="new_revisions" name="disabler_options[disabler_revisions]" value="1" ' . $checked . ' />';
	}

	function version() {
		$checked = checked( $this->options['disabler_version'], '1', false );
		echo '<input type="checkbox" id="new_version" name="disabler_options[disabler_version]" value="1" ' . $checked . ' />';
	}

	function nourl() {
		$checked = checked( $this->options['disabler_nourl'], '1', false );
		echo '<input type="checkbox" id="new_nourl" name="disabler_options[disabler_nourl]" value="1" ' . $checked . ' />';
	}

	function plugin_options() {
?>
		<div class="wrap">
			<h2><?php _e( 'Disabler', 'disabler' ); ?></h2>
			<p><?php _e( 'Here\'s where you can disable whatever you want.', 'disabler' ); ?></p>
			<form method="post" action="options.php">
			<?php settings_fields( 'ippy_dis_options' );
			do_settings_sections( 'disabler_options_page' );
			submit_button(); ?>
			</form>
		</div>
<?php
	}
}

$disabler_options_class = new Disabler_Options_Class();
