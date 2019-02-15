<?php

namespace wpscholar\phpdotenv\Adapter;

/**
 * Interface AdapterInterface
 */
interface AdapterInterface {

	/**
	 * Determines if the adapter is supported.
	 *
	 * @return bool
	 */
	public function isSupported();

	/**
	 * Check if a variable exists.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has( $name );

	/**
	 * Get an environment variable, if it exists.
	 *
	 * @param string $name
	 *
	 * @return \wpscholar\phpdotenv\Option\Option
	 */
	public function get( $name );

	/**
	 * Set an environment variable.
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function set( $name, $value );

	/**
	 * Clear an environment variable.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function clear( $name );

}
