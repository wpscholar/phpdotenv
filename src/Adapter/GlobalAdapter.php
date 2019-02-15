<?php

namespace wpscholar\phpdotenv\Adapter;

/**
 * Class VarAdapter
 */
class GlobalAdapter implements AdapterInterface {

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
		global ${$name};

		return isset( ${$name} );
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
			global ${$name};

			return ${$name};
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
		global ${$name};
		${$name} = $value;
	}

	/**
	 * Clear an environment variable.
	 *
	 * @param string $name
	 */
	public function clear( $name ) {
		global ${$name};
		unset( ${$name} );
	}

}
