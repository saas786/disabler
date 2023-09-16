<?php

namespace Disabler;

/**
 * Collects the data from the added collection objects.
 */
class Disabler_Collector {

	/** @var Disabler_Collection[] */
	protected static $collections = array();

	/**
	 * Adds a collection object to the collections.
	 *
	 * @param Disabler_Collection $collection The collection object to add.
	 */
	public function add_collection( Disabler_Collection $collection ) {
		self::$collections[] = $collection;
	}

	/**
	 * Collects the data from the collection objects.
	 *
	 * @return array The collected data.
	 */
	public function collect() {
		$data = array();

		foreach ( self::$collections as $collection ) {
			$data = array_merge( $data, $collection->get() );
		}

		return $data;
	}

	/**
	 * Returns the collected data as a JSON encoded string.
	 *
	 * @return false|string The encode string.
	 */
	public function get_as_json() {
		return wp_json_encode( self::collect() );
	}
}
