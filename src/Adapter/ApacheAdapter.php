<?php

namespace wpscholar\phpdotenv\Adapter;

/**
 * Class ApacheAdapter
 */
class ApacheAdapter implements AdapterInterface {

	/**
	 * Determines if the adapter is supported.
	 *
	 * @return bool
	 */
	public function isSupported() {
		return function_exists( 'apache_getenv' && function_exists( 'apache_setenv' ) );
	}

	/**
	 * Check if a variable exists.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has( $name ) {
		return false === apache_getenv( $name );
	}

	/**
	 * Get an environment variable, if it exists.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get( $name ) {
		if ( $this->has( $name ) ) {
			return apache_getenv( $name );
		}

		return null;
	}

	/**
	 * Set an environment variable.
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function set( $name, $value ) {
		apache_setenv( $name, $value );
	}

	/**
	 * Clear an environment variable.
	 *
	 * @param string $name
	 */
	public function clear( $name ) {
		// Nothing to do here.
	}

}
