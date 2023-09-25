<?php

namespace HBP\Disabler\Admin\Contracts\Traits\Fields;

use HBP\Disabler\Admin\Options;

trait Textarea {

    protected function renderFieldTextarea( $field ): string {
        $id           = $field['id'] ?? null;
        $type         = $field['type'] ?? null;
        $section      = $field['section'] ?? null;
        $description  = $field['description'] ?? null;
        $before_field = $field['before_field'] ?? null;
        $after_field  = $field['after_field'] ?? null;
        $setting_key  = $field['setting_key'] ?? $section . '_' . $id;
        $class        = 'hbp-disabler-form-field ' . $setting_key . ' ' . ( $field['class'] ?? '' );
        $placeholder  = $field['placeholder'] ?? null;
        $rows         = $field['rows'] ?? 10;
        $cols         = $field['cols'] ?? 50;

        if ( ! $type || ! $section || ! $id ) {
            return '';
        }

        $value = Options::get( $setting_key, $field['value'] ?? null );
        if ( is_callable( $value ) ) {
            $value = call_user_func( $value );
        }

        $name = $this->option_key . '[' . $setting_key . ']';

        $output = sprintf(
            '%1$s <textarea name="%2$s" id="%2$s" class="%3$s" placeholder="%4$s" rows="%5$d" cols="%6$d">%7$s</textarea> %8$s',
            wp_kses_post( $before_field ),
            esc_attr( $name ),
            esc_attr( $class ),
            esc_attr( $placeholder ),
            absint( $rows ),
            absint( $cols ),
            esc_textarea( $value ),
            wp_kses_post( $after_field )
        );

        if ( ! empty( $description ) ) {
            $output .= wp_kses_post( sprintf( '<p class="description">%s</p>', $description ) );
        }

        return $output;
    }

    protected function sanitizeFieldTextarea( $input ) {
        return sanitize_textarea_field( $input );
    }

}
