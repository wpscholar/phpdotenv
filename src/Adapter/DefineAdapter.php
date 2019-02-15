<?php

namespace wpscholar\phpdotenv\Adapter;

/**
 * Class DefineAdapter
 */
class DefineAdapter implements AdapterInterface {

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
		return defined( $name );
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
			return constant( $name );
		}

		return null;
	}

	/**
	 * Set an environment variable.
	 *
	 * @param string                $name
	 * @param string|int|float|bool $value
	 */
	public function set( $name, $value = null ) {
		// Only set if not already in order to prevent fatal errors.
		if ( ! $this->has( $name ) ) {
			define( $name, $value );
		}
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
