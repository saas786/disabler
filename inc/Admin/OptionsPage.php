<?php
/**
 * Plugin settings screen.
 */

namespace HBP\Disabler\Admin;

use HBP\Disabler\Admin\Contracts\Traits\BootsTraits;
use HBP\Disabler\Admin\Contracts\Traits\Fields;
use HBP\Disabler\Admin\Contracts\Traits\TabbedSections;
use HBP\Disabler\Facades\Assets;
use function Hybrid\config;

/**
 * Sets up and handles the plugin settings screen.
 */
class OptionsPage {

    use BootsTraits;
    use Fields\Checkbox;
    use Fields\MultiCheckbox;
    use Fields\Radio;
    use Fields\Select;
    use Fields\Text;
    use Fields\Textarea;
    use TabbedSections;

    /**
     * Settings page name.
     *
     * @var string
     */
    public $settings_page = '';

    /**
     * Settings key/identifier.
     */
    public string $option_key = 'hbp_disabler_settings';

    /**
     * Boot.
     */
    public function boot(): void {
        static::bootTraits();

        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    /**
     * Sets up custom admin menus.
     *
     * @return void
     */
    public function admin_menu() {
        // Create the settings page.
        $this->settings_page = add_options_page(
            esc_html_x( 'Disabler', 'admin screen', 'hbp-disabler' ),
            esc_html_x( 'Disabler', 'admin screen', 'hbp-disabler' ),
            'manage_options',
            'hbp-disabler-settings',
            [ $this, 'settingsPage' ]
        );

        if ( $this->settings_page ) {
            add_action( 'admin_init', [ $this, 'register_settings' ] );
            add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ], 1 );
        }
    }

    /**
     * Registers the plugin settings.
     *
     * @return void
     */
    public function register_settings() {
        register_setting(
            $this->option_key,
            $this->option_key,
            [ $this, 'sanitizeSettings' ]
        );

        foreach ( config( 'admin.settings.sections' ) as $section ) {
            add_settings_section(
                $section['data']['id'],
                $section['data']['title'],
                $section['data']['callback'],
                $section['data']['page']
            );

            foreach ( $section['fields'] as $field ) {
                // No field type associated, skip, no GUI.
                if ( ! isset( $field['type'] ) ) {
                    continue;
                }

                if ( 'group' === $field['type'] ) {
                    $fields = $field['fields'];

                    if ( is_callable( $fields ) ) {
                        $fields = call_user_func( $fields );
                    }

                    foreach ( $fields as $groupField ) {
                        add_settings_field(
                            $groupField['id'],
                            $groupField['title'],
                            ( $groupField['callback'] ?? [
                                $this,
                                'outputField',
                            ] ),
                            $groupField['page'],
                            $groupField['section'],
                            array_merge( $groupField, [
                                'section'   => $groupField['section'],
                                'label_for' => sprintf( '%s_%s_%s', $groupField['page'], $groupField['section'], $groupField['id'] ),
                                'class'     => $groupField['container-class'] ?? ( $groupField['class'] ?? '' ),
                            ] )
                        );
                    }
                } else {
                    add_settings_field(
                        $field['id'],
                        $field['title'],
                        ( $field['callback'] ?? [
                            $this,
                            'outputField',
                        ] ),
                        $field['page'],
                        $field['section'],
                        array_merge( $field, [
                            'section'   => $field['section'],
                            'label_for' => sprintf( '%s_%s_%s', $field['page'], $field['section'], $field['id'] ),
                            'class'     => $field['container-class'] ?? ( $field['class'] ?? '' ),
                        ] )
                    );
                }
            }
        }
    }

    /**
     * Sanitizes the settings.
     *
     * @param  array $settings
     * @return array
     */
    public function sanitizeSettings( $settings ) {
        foreach ( config( 'admin.settings.sections' ) as $section ) {
            foreach ( $section['fields'] as $field ) {
                // No field type associated, skip.
                if ( ! isset( $field['type'] ) ) {
                    continue;
                }

                if ( 'group' === $field['type'] ) {
                    $fields = $field['fields'];

                    if ( is_callable( $fields ) ) {
                        $fields = call_user_func( $fields );
                    }

                    foreach ( $fields as $groupField ) {
                        $setting_key   = $groupField['setting_key'] ?? $groupField['section'] . '_' . $groupField['id'];
                        $setting_value = $settings[ $setting_key ] ?? '';

                        if ( $settings[ $setting_key ] ?? 'checkbox' === $groupField['type'] ) {
                            $settings[ $setting_key ] = $this->{'sanitizeField' . ucfirst( $groupField['type'] ) }( $setting_value );
                        }
                    }
                } else {
                    $setting_key   = $field['setting_key'] ?? $field['section'] . '_' . $field['id'];
                    $setting_value = $settings[ $setting_key ] ?? '';

                    if ( $settings[ $setting_key ] ?? 'checkbox' === $field['type'] ) {
                        $settings[ $setting_key ] = $this->{'sanitizeField' . ucfirst( $field['type'] ) }( $setting_value );
                    }
                }
            }
        }

        return $settings;
    }

    /**
     * Compile HTML needed for displaying the field.
     *
     * @param  array $field Field settings.
     * @return string HTML to be displayed
     */
    public function renderField( array $field ): string {
        if ( isset( $args['render'] ) ) {
            return call_user_func_array( $field['render'], [ $field ] );
        }

        return $this->{'renderField' . ucfirst( $field['type'] ) }( $field );
    }

    /**
     * Output the field.
     *
     * @param  array $field Field to be rendered.
     * @return void
     */
    public function outputField( $field ) {
        $method = 'outputField' . $field['id'];

        if ( method_exists( $this, $method ) ) {
            return call_user_func( [ $this, $method ], $field );
        }

        echo $this->renderField( $field ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Renders the settings page.
     *
     * @return void
     */
    public function settingsPage() {
        ?>
        <div class="wrap hbp-disabler-form-wrap">
            <h1><?php esc_html_e( 'Settings', 'hbp-disabler' ); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields( $this->option_key ); ?>
                <?php $this->renderTabbedSections( $this->settings_page ); ?>
                <?php submit_button( esc_attr__( 'Save Settings', 'hbp-disabler' ), 'primary' ); ?>
            </form>

        </div><!-- wrap -->
        <?php
    }

    public function enqueueAssets(): void {
        wp_enqueue_script(
            'hbp-disabler-wp-admin-settings',
            Assets::assetUrl( 'js/admin/settings.js' ),
            [],
            null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
            true
        );

        wp_enqueue_style(
            'hbp-disabler-wp-admin-settings',
            Assets::assetUrl( 'css/admin/settings.css' ),
            [],
            null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        );
    }

}
