<?php

namespace wpscholar\phpdotenv\Adapter;

/**
 * Class ServerAdapter
 */
class ServerAdapter implements AdapterInterface {

	/**
	 * Determines if the adapter is supported.
	 *
	 * @return bool
	 */
	public function isSupported() {
		return true;
	}

	/**
	 * Check if a variable exists.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has( $name ) {
		return array_key_exists( $name, $_SERVER );
	}

	/**
	 * Get an environment variable, if it exists.
	 *
	 * @param string $name
	 *
	 * @return \wpscholar\phpdotenv\Option\Option
	 */
	public function get( $name ) {
		if ( $this->has( $name ) ) {
			return $_SERVER[ $name ];
		}

		return null;
	}

	/**
	 * Set an environment variable.
	 *
	 * @param string $name
	 * @param mixed  $value
	 */
	public function set( $name, $value ) {
		$_SERVER[ $name ] = $value;
	}

	/**
	 * Clear an environment variable.
	 *
	 * @param string $name
	 */
	public function clear( $name ) {
		unset( $_SERVER[ $name ] );
	}

}
