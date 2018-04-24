<?php

namespace Disabler;

/**
 * Represents the theme data.
 */
class Disabler_Tracking_Theme_Data implements Disabler_Collection {

	/**
	 * Returns the collection data.
	 *
	 * @return array The collection data.
	 */
	public function get() {
		$theme = wp_get_theme();

		return array(
			'theme' => array(
				'name'        => $theme->get( 'Name' ),
				'url'         => $theme->get( 'ThemeURI' ),
				'version'     => $theme->get( 'Version' ),
				'author'      => array(
					'name' => $theme->get( 'Author' ),
					'url'  => $theme->get( 'AuthorURI' ),
				),
				'parent_theme' => self::get_parent_theme( $theme ),
			),
		);
	}

	/**
	 * Returns the name of the parent theme.
	 *
	 * @param WP_Theme $theme The theme object.
	 *
	 * @return null|string The name of the parent theme or null.
	 */
	private static function get_parent_theme( \WP_Theme $theme ) {
		if ( is_child_theme() ) {
			return $theme->get( 'Template' );
		}

		return null;
	}
}