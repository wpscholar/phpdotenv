<?php

namespace wpscholar\phpdotenv\Adapter;

/**
 * Class EnvAdapter
 */
class EnvAdapter implements AdapterInterface {

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
		return array_key_exists( $name, $_ENV );
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
			return $_ENV[ $name ];
		}

		return null;
	}

	/**
	 * Set an environment variable.
	 *
	 * @param string $name
	 * @param mixed  $value
	 */
	public function set( $name, $value = null ) {
		$_ENV[ $name ] = $value;
	}

	/**
	 * Clear an environment variable.
	 *
	 * @param string $name
	 */
	public function clear( $name ) {
		unset( $_ENV[ $name ] );
	}

}
