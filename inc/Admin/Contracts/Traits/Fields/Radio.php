<?php

namespace HBP\Disabler\Admin\Contracts\Traits\Fields;

use HBP\Disabler\Admin\Options;

trait Radio {

    protected function renderFieldRadio( $field ): string {
        $id           = $field['id'] ?? null;
        $title        = $field['title'] ?? null;
        $type         = $field['type'] ?? null;
        $section      = $field['section'] ?? null;
        $description  = $field['description'] ?? null;
        $before_field = $field['before_field'] ?? null;
        $after_field  = $field['after_field'] ?? null;
        $setting_key  = $field['setting_key'] ?? $section . '_' . $id;
        $choices      = $field['choices'] ?? [];
        $class        = 'hbp-disabler-form-field ' . $setting_key . ' ' . ( $field['class'] ?? '' );
        $events       = $field['events'] ?? [];

        if ( ! $type || ! $section || ! $id ) {
            return '';
        }

        if ( is_callable( $choices ) ) {
            $choices = call_user_func( $choices );
        }

        $value = Options::get( $setting_key, $field['value'] ?? null );
        if ( is_callable( $value ) ) {
            $value = call_user_func( $value );
        }

        if ( is_callable( $events ) ) {
            $events = call_user_func( $events );
        }

        $name = $this->option_key . '[' . $setting_key . ']';

        $output  = '<fieldset>';
        $output .= sprintf( '<legend class="screen-reader-text"><span>%1$s</span></legend>', $title );

        foreach ( $choices as $key => $label ) {
            $output .= sprintf(
                '<label>%1$s <input type="radio" name="%2$s" id="%2$s" class="%3$s" value="%4$s" data-events=\'%5$s\' %6$s /> %7$s %8$s</label><br/>',
                wp_kses_post( $before_field ),
                esc_attr( $name ),
                esc_attr( $class ),
                esc_attr( $key ),
                wp_json_encode( $events ),
                // esc_attr( htmlspecialchars( wp_json_encode( $events ) ) ),
                checked( $value, $key, false ),
                esc_attr( $label ),
                wp_kses_post( $after_field )
            );
        }

        $output .= '</fieldset>';

        if ( ! empty( $description ) ) {
            $output .= wp_kses_post( sprintf( '<p class="description">%s</p>', $description ) );
        }

        return $output;
    }

    protected function sanitizeFieldRadio( $input ) {
        if ( is_array( $input ) ) {
            foreach ( $input as $key => $value ) {
                $input[ $key ] = sanitize_text_field( $value );
            }

            return $input;
        }

        return sanitize_text_field( $input );
    }

}
