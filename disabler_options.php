<?php
class Disabler_Options_Class {

	var $options;

	function __construct() {
		$this->options = get_option( 'disabler_options' );

		add_action( 'admin_menu',	array( $this, 'disabler_menu' ) );
		add_action( 'admin_init',	array( $this, 'disabler_settings' ) );
	}

	// Load the options page
	function disabler_menu() {
		add_filter( 'plugin_action_links_disabler/disabler.php', array( $this, 'settings_link' ) );
		add_options_page( 'Disabler', 'Disabler', 'activate_plugins', 'disabler_options_page', array( $this, 'plugin_options' ) );
	}

	function sanitize( $posted_options ) {
		$disabler_frontend_options	= array(
			'disabler_smartquotes'	=> '0',
			'disabler_capitalp'	=> '0',
			'disabler_autop'	=> '0'
		);
		$disabler_backend_options	= array(
			'disabler_selfping'	=> '0',
			'disabler_norss'	=> '0',
			'disabler_xmlrpc'	=> '0',
			'disabler_revisions'	=> '0',
			'disabler_gfonts'	=> '0',
			'disabler_update_nag'	=> '0',
			'disabler_autosave'	=> '0'
		);
		$disabler_privacy_options	= array(
			'disabler_version'	=> '0',
			'disabler_nourl'	=> '0'
		);
		$default_options = array_merge( $disabler_frontend_options, $disabler_backend_options, $disabler_privacy_options );

		$http_query = wp_parse_args( parse_url( wp_get_referer(), PHP_URL_QUERY ) );
		$active_tab = isset( $http_query['tab'] ) ? $http_query['tab'] : 'frontend_options';

		foreach ( $default_options as $key => $value ) {
			if ( ! isset( $posted_options[ $key ] ) && array_key_exists( $key, ${ 'disabler_' . $active_tab } ) ) {
				$posted_options[ $key ] = $value;
			}
			elseif ( ! isset( $posted_options[ $key ] ) && isset( $this->options[ $key ] ) ) {
				$posted_options[ $key ] = $this->options[ $key ];
			}
		}

		return $posted_options;
	}

	function disabler_settings() {
		register_setting( 'ippy_dis_options', 'disabler_options', array( $this, 'sanitize' ) );

		add_settings_section( 'frontend_settings', __( 'Front End Settings', 'ippy_dis' ), array( $this, 'callback_frontend' ), 'disabler_frontend_options_page' );
		add_settings_field( 'new_smartquotes', __( "Disable Texturization -- smart quotes (a.k.a. curly quotes), em dash, en dash and ellipsis.", 'ippy_dis' ), array( $this, 'smartquotes' ), 'disabler_frontend_options_page', 'frontend_settings', array( 'label_for' => 'new_smartquotes' ) );
		add_settings_field( 'new_capitalp', __( "Disable auto-correction of WordPress capitalization.", 'ippy_dis' ), array( $this, 'capitalp' ), 'disabler_frontend_options_page', 'frontend_settings', array( 'label_for' => 'new_capitalp' ) );
		add_settings_field( 'new_autop', __( "Disable paragraphs (i.e. &lt;p&gt;  tags) from being automatically inserted in your posts.", 'ippy_dis' ), array( $this, 'autop' ), 'disabler_frontend_options_page', 'frontend_settings', array( 'label_for' => 'new_autop' ) );

		add_settings_section( 'backend_settings', __( 'Back End Settings', 'ippy_dis' ), array( $this, 'callback_backend' ), 'disabler_backend_options_page' );
		add_settings_field( 'new_selfping', __( "Disable self pings (i.e. trackbacks/pings from your own domain).", 'ippy_dis' ), array( $this, 'selfping' ), 'disabler_backend_options_page', 'backend_settings', array( 'label_for' => 'new_selfping' ) );
		add_settings_field( 'new_norss', __( "Disable all RSS feeds.", 'ippy_dis' ), array( $this, 'norss' ), 'disabler_backend_options_page', 'backend_settings', array( 'label_for' => 'new_norss' ) );
		add_settings_field( 'new_xmlrpc', __( "Disable XML-RPC.", 'ippy_dis' ), array( $this, 'xmlrpc' ), 'disabler_backend_options_page', 'backend_settings', array( 'label_for' => 'new_xmlrpc' ) );
		add_settings_field( 'new_autosave', __( "Disable auto-saving of posts.", 'ippy_dis' ), array( $this, 'autosave' ), 'disabler_backend_options_page', 'backend_settings', array( 'label_for' => 'new_autosave' ) );
		add_settings_field( 'new_revisions', __( "Disable post revisions (existing revisions are NOT removed).", 'ippy_dis' ), array( $this, 'revisions' ), 'disabler_backend_options_page', 'backend_settings', array( 'label_for' => 'new_revisions' ) );
		add_settings_field( 'new_gfonts', __( "Disable Google Fonts in the Administration pages (only in /wp-admin).", 'ippy_dis' ), array( $this, 'gfonts' ), 'disabler_backend_options_page', 'backend_settings', array( 'label_for' => 'new_gfonts' ) );
		add_settings_field( 'new_updatenag', __( "Disable WordPress update notice (only hides the notice you can update from Dashboard > Updates).", 'ippy_dis' ), array( $this, 'update_nag' ), 'disabler_backend_options_page', 'backend_settings', array( 'label_for' => 'new_updatenag' ) );

		add_settings_section( 'privacy_settings', __( 'Privacy Settings', 'ippy_dis' ), array( $this, 'callback_privacy' ), 'disabler_privacy_options_page' );
		add_settings_field( 'new_version', __( "Disable WordPress from printing it's version in your headers (only seen via View Source).", 'ippy_dis' ), array( $this, 'version' ), 'disabler_privacy_options_page', 'privacy_settings', array( 'label_for' => 'new_version' ) );
		add_settings_field( 'new_nourl', __( "Disable WordPress from sending your URL information when checking for updates.", 'ippy_dis' ), array( $this, 'nourl' ), 'disabler_privacy_options_page', 'privacy_settings', array( 'label_for' => 'new_nourl' ) );
	}

	function callback_frontend() {
		_e( 'These are settings are changes on the front end. These are the things that affect what your site looks like when other people visit. What THEY see.  While these are actually things that annoy <strong>you</strong>, it all comes back to being things on the forward facing part of your site.', 'ippy_dis' );
	}

	function callback_backend() {
		_e( "Back End settings affect how WordPress runs. Nothing here will <em>break</em> your install, but some turn off 'desired' functions.", 'ippy_dis' );
	}

	function callback_privacy() {
		_e( "These settings help obfuscate information about your blog to the world (inclyding to Wordpress.org). While they don't protect you from anything, they do make it a little harder for people to get information about you and your site.", 'ippy_dis' );
	}

	function smartquotes() {
		$checked = checked( isset( $this->options['disabler_smartquotes'] ) ? $this->options['disabler_smartquotes'] : '', '1', FALSE );
		echo '<input type="checkbox" id="new_smartquotes" name="disabler_options[disabler_smartquotes]" value="1" ' . $checked . ' />';
	}

	function capitalp() {
		$checked = checked( isset( $this->options['disabler_capitalp'] ) ? $this->options['disabler_capitalp'] : '', '1', FALSE );
		echo '<input type="checkbox" id="new_capitalp" name="disabler_options[disabler_capitalp]" value="1" ' . $checked . ' />';
	}

	function autop() {
		$checked = checked( isset( $this->options['disabler_autop'] ) ? $this->options['disabler_autop'] : '', '1', FALSE );
		echo '<input type="checkbox" id="new_autop" name="disabler_options[disabler_autop]" value="1" ' . $checked . ' />';
	}

	function selfping()
	{
		$checked = checked( isset( $this->options['disabler_selfping'] ) ? $this->options['disabler_selfping'] : '', '1', FALSE );
		echo '<input type="checkbox" id="new_selfping" name="disabler_options[disabler_selfping]" value="1" ' . $checked . ' />';
	}

	function norss() {
		$checked = checked( isset( $this->options['disabler_norss'] ) ? $this->options['disabler_norss'] : '', '1', FALSE );
		echo '<input type="checkbox" id="new_norss" name="disabler_options[disabler_norss]" value="1" ' . $checked . ' />';
	}

	function xmlrpc() {
		$checked = checked( isset( $this->options['disabler_xmlrpc'] ) ? $this->options['disabler_xmlrpc'] : '', '1', FALSE );
		echo '<input type="checkbox" id="new_xmlrpc" name="disabler_options[disabler_xmlrpc]" value="1" ' . $checked . ' />';
	}

	function autosave() {
		$checked = checked( isset( $this->options['disabler_autosave'] ) ? $this->options['disabler_autosave'] : '', '1', FALSE );
		echo '<input type="checkbox" id="new_autosave" name="disabler_options[disabler_autosave]" value="1" ' . $checked . ' />';
	}

	function revisions() {
		$checked = checked( isset( $this->options['disabler_revisions'] ) ? $this->options['disabler_revisions'] : '' , '1', FALSE );
		echo '<input type="checkbox" id="new_revisions" name="disabler_options[disabler_revisions]" value="1" ' . $checked . ' />';
	}

	function gfonts() {
		$checked = checked( isset( $this->options['disabler_gfonts'] ) ? $this->options['disabler_gfonts'] : '', '1', FALSE );
		echo '<input type="checkbox" id="new_gfonts" name="disabler_options[disabler_gfonts]" value="1" ' . $checked . ' />';
	}

	function update_nag() {
		$checked = checked( isset( $this->options['disabler_update_nag'] ) ? $this->options['disabler_update_nag'] : '', '1', FALSE );
		echo '<input type="checkbox" id="new_updatenag" name="disabler_options[disabler_update_nag]" value="1" ' . $checked . ' />';
	}

	function version() {
		$checked = checked( isset( $this->options['disabler_version'] ) ? $this->options['disabler_version'] : '', '1', FALSE );
		echo '<input type="checkbox" id="new_version" name="disabler_options[disabler_version]" value="1" ' . $checked . ' />';
	}

	function nourl() {
		$checked = checked( isset( $this->options['disabler_nourl'] ) ? $this->options['disabler_nourl'] : '', '1', FALSE );
		echo '<input type="checkbox" id="new_nourl" name="disabler_options[disabler_nourl]" value="1" ' . $checked . ' />';
	}

	function settings_link( $links ) {
		array_unshift( $links, '<a href="' . admin_url( 'options-general.php?page=disabler_options_page' ) . '">' . __( 'Settings', 'ippy_dis' ) . '</a>' );
		return $links;
	}

	function plugin_options() {
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'frontend_options';
?>
		<div class="wrap">
			<h2><?php _e( "Disabler", 'ippy_dis' ); ?></h2>
			<p><?php _e( "Here's where you can disable whatever you want.", 'ippy_dis' ); ?></p>
			<h2 class="nav-tab-wrapper">
				<a href="<?php echo admin_url( 'options-general.php?page=disabler_options_page&tab=frontend_options' ); ?>" class="nav-tab<?php echo 'frontend_options' == $active_tab ? ' nav-tab-active' : ''; ?>">Frontend</a>
				<a href="<?php echo admin_url( 'options-general.php?page=disabler_options_page&tab=backend_options' ); ?>" class="nav-tab<?php echo 'backend_options' == $active_tab ? ' nav-tab-active' : ''; ?>">Backend</a>
				<a href="<?php echo admin_url( 'options-general.php?page=disabler_options_page&tab=privacy_options' ); ?>" class="nav-tab<?php echo 'privacy_options' == $active_tab ? ' nav-tab-active' : ''; ?>">Privacy</a>
			</h2>
			<form method="post" action="options.php">
				<?php
					settings_fields( 'ippy_dis_options' );
					if ( 'backend_options' == $active_tab ) {
						do_settings_sections( 'disabler_backend_options_page' );
					}
					elseif ( 'privacy_options' == $active_tab ) {
						do_settings_sections( 'disabler_privacy_options_page' );
					}
					else {
						do_settings_sections( 'disabler_frontend_options_page' );
					}
					submit_button();
				?>
			</form>
		</div>
<?php
	}
}

$disabler_options_class = new Disabler_Options_Class();
