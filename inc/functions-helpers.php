<?php
/**
 * Helper functions for handling plugin options.
 */

namespace HBP\Disabler;

/**
 * Define a constant if it is not already defined.
 *
 * @param string $name  Constant name.
 * @param string $value Value.
 */
function maybe_define_constant( $name, $value ) {
    if ( ! defined( $name ) ) {
        define( $name, $value );
    }
}

/**
 * Get post types which support revisions.
 *
 * @return array
 */
function get_revision_post_types() {
    $revision_post_types = [];

    foreach ( get_post_types() as $type ) {
        $object = get_post_type_object( $type );
        if ( ! post_type_supports( $type, 'revisions' ) || null === $object ) {
            continue;
        }

        $name = property_exists( $object, 'labels' ) && property_exists( $object->labels, 'name' )
            ? $object->labels->name
            : $object->name;

        $revision_post_types[ $type ] = $name;
    }

    return $revision_post_types;
}
