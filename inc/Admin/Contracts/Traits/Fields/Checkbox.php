<?php

namespace HBP\Disabler\Admin\Contracts\Traits\Fields;

use HBP\Disabler\Admin\Options;

trait Checkbox {

    protected function renderFieldCheckbox( $field ): string {
        $id           = $field['id'] ?? null;
        $type         = $field['type'] ?? null;
        $section      = $field['section'] ?? null;
        $description  = $field['description'] ?? null;
        $before_field = $field['before_field'] ?? null;
        $after_field  = $field['after_field'] ?? null;
        $setting_key  = $field['setting_key'] ?? $section . '_' . $id;
        $class        = 'hbp-disabler-form-field ' . $setting_key . ' ' . ( $field['class'] ?? '' );
        $events       = $field['events'] ?? [];

        if ( ! $type || ! $section || ! $id ) {
            return '';
        }

        $value = Options::get( $setting_key, $field['value'] ?? null );
        if ( is_callable( $value ) ) {
            $value = call_user_func( $value );
        }

        if ( is_callable( $events ) ) {
            $events = call_user_func( $events );
        }

        $name = $this->option_key . '[' . $setting_key . ']';

        $output = sprintf(
            '<label>%1$s <input type="checkbox" name="%2$s" id="%2$s" class="%3$s" value="1" data-events=\'%4$s\' %5$s /> %6$s</label>',
            wp_kses_post( $before_field ),
            esc_attr( $name ),
            esc_attr( $class ),
            wp_json_encode( $events ),
            checked( $value, 1, false ),
            wp_kses_post( $after_field )
        );

        if ( ! empty( $description ) ) {
            $output .= wp_kses_post( sprintf( '<p class="description">%s</p>', $description ) );
        }

        return $output;
    }

    protected function sanitizeFieldCheckbox( $input ) {
        return absint( $input ) === 1 ? 1 : 0;
    }

}
