<?php

namespace wpscholar\phpdotenv\Adapter;

/**
 * Class PutenvAdapter
 */
class PutenvAdapter implements AdapterInterface {

	/**
	 * Determines if the adapter is supported.
	 *
	 * @return bool
	 */
	public function isSupported() {
		return function_exists( 'getenv' ) && function_exists( 'putenv' );
	}

	/**
	 * Check if a variable exists.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has( $name ) {
		return ! empty( getenv( $name ) );
	}

	/**
	 * Get an environment variable, if it exists.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get( $name ) {
		$value = getenv( $name );
		if ( ! empty( $value ) ) {
			return $value;
		}

		return null;
	}

	/**
	 * Set an environment variable.
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function set( $name, $value = '' ) {
		putenv( "{$name}={$value}" );
	}

	/**
	 * Clear an environment variable.
	 *
	 * @param string $name
	 */
	public function clear( $name ) {
		putenv( $name );
	}

}
