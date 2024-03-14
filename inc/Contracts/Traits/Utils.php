<?php

namespace HBP\Disabler\Contracts\Traits;

use Hybrid\Tools\Str;

trait Utils {

    /**
     * Prepare a multiline text by sanitizing and converting it into an array of sanitized strings.
     *
     * This method takes a multiline text, removes extra spaces and new lines, then splits it into an array of words,
     * and finally sanitizes each word using the provided or default sanitization function.
     *
     * @param  string $text              The multiline text to be prepared.
     * @param  string $sanitize_function (optional) The sanitization function to apply to each word.
     * @return array  An array of sanitized strings.
     */
    public static function prepareMultilineText( string $text, string $sanitize_function = 'sanitize_text_field' ): array {
        // Convert extra spaces and new lines to a single space.
        $text = Str::squish( $text );

        // Split the text into an array of words, trimming each word.
        $text = explode( ' ', $text );
        $text = array_map( 'trim', $text );

        // Sanitize each word using the provided or default sanitization function.
        if ( $sanitize_function ) {
            $text = array_map( $sanitize_function, $text );
        }

        return array_filter( $text );
    }

}
