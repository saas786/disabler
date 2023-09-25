<?php

namespace HBP\Disabler\Admin\Contracts\Traits\Fields;

use HBP\Disabler\Admin\Options;

trait Text {

    protected function renderFieldText( $field ): string {
        $id           = $field['id'] ?? null;
        $type         = $field['type'] ?? null;
        $section      = $field['section'] ?? null;
        $description  = $field['description'] ?? null;
        $before_field = $field['before_field'] ?? null;
        $after_field  = $field['after_field'] ?? null;
        $setting_key  = $field['setting_key'] ?? $section . '_' . $id;
        $class        = 'hbp-disabler-form-field ' . $setting_key . ' ' . ( $field['class'] ?? '' );
        $placeholder  = $field['placeholder'] ?? null;

        if ( ! $type || ! $section || ! $id ) {
            return '';
        }

        $value = Options::get( $setting_key, $field['value'] ?? null );
        if ( is_callable( $value ) ) {
            $value = call_user_func( $value );
        }

        $name = $this->option_key . '[' . $setting_key . ']';

        $output = sprintf(
            '%1$s <input type="text" name="%2$s" id="%2$s" class="%3$s" placeholder="%4$s" value="%5$s" /> %6$s',
            wp_kses_post( $before_field ),
            esc_attr( $name ),
            esc_attr( $class ),
            esc_attr( $placeholder ),
            esc_attr( $value ),
            wp_kses_post( $after_field )
        );

        if ( ! empty( $description ) ) {
            $output .= wp_kses_post( sprintf( '<p class="description">%s</p>', $description ) );
        }

        return $output;
    }

    protected function sanitizeFieldText( $input ) {
        return sanitize_text_field( $input );
    }

}
