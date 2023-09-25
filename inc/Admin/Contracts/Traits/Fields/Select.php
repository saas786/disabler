<?php

namespace HBP\Disabler\Admin\Contracts\Traits\Fields;

use HBP\Disabler\Admin\Options;

trait Select {

    protected function renderFieldSelect( $field ): string {
        $id           = $field['id'] ?? null;
        $type         = $field['type'] ?? null;
        $section      = $field['section'] ?? null;
        $description  = $field['description'] ?? null;
        $before_field = $field['before_field'] ?? null;
        $after_field  = $field['after_field'] ?? null;
        $setting_key  = $field['setting_key'] ?? $section . '_' . $id;
        $class        = 'hbp-disabler-form-field ' . $setting_key . ' ' . ( $field['class'] ?? '' );
        $choices      = $field['choices'] ?? [];
        $multiple     = $field['multiple'] ?? false;
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

        $select_options = '';
        foreach ( $choices as $key => $label ) {
            $selected        = is_array( $value )
                ? selected( true, in_array( $key, $value ), false )
                : selected( $value, $key, false );
            $select_options .= sprintf( '<option value="%s"%s>%s</option>', $key, $selected, $label );
        }

        $output = sprintf(
            '%1$s <select name="%2$s" id="%2$s" class="%3$s" data-events=\'%4$s\' %5$s>%6$s</select> %7$s',
            wp_kses_post( $before_field ),
            $multiple ? esc_attr( $name . '[]' ) : esc_attr( $name ),
            esc_attr( $class ),
            wp_json_encode( $events ),
            $multiple ? esc_attr( 'multiple="multiple"' ) : '',
            $select_options,
            wp_kses_post( $after_field )
        );

        if ( ! empty( $description ) ) {
            $output .= wp_kses_post( sprintf( '<p class="description">%s</p>', $description ) );
        }

        return $output;
    }

    protected function sanitizeFieldSelect( $input ) {
        if ( is_array( $input ) ) {
            foreach ( $input as $key => $value ) {
                $input[ $key ] = sanitize_text_field( $value );
            }

            return $input;
        }

        return sanitize_text_field( $input );
    }

}
