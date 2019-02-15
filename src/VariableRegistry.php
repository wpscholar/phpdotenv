<?php

namespace wpscholar\phpdotenv;

/**
 * Class VariableRegistry
 */
class VariableRegistry {

	/**
	 * Variable names and default values.
	 *
	 * @var array
	 */
	protected $defaults = [];

	/**
	 * Variables names and whether they are required.
	 *
	 * @var array
	 */
	protected $required = [];

	/**
	 * Variable names and values.
	 *
	 * @var array
	 */
	protected $variables = [];

	/**
	 * Check if a variable exists.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has( $name ) {
		return array_key_exists( $name, $this->variables );
	}

	/**
	 * Get a variable value.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get( $name ) {
		return $this->has( $name ) ? $this->variables[ $name ] : $this->getDefault( $name );
	}

	/**
	 * Set a variable.
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set( $name, $value ) {
		$this->variables[ $name ] = $value;

		return $this;
	}

	/**
	 * Remove a variable from the registry.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function clear( $name ) {
		unset( $this->defaults[ $name ], $this->required[ $name ], $this->variables[ $name ] );

		return $this;
	}

	/**
	 * Get all variables; including defaults if a variable is not set.
	 *
	 * @return array
	 */
	public function all() {
		return array_merge( $this->defaults, $this->variables );
	}

	/**
	 * Set multiple variables at once.
	 *
	 * @param array $variables
	 *
	 * @return $this
	 */
	public function populate( array $variables ) {
		$this->variables = array_merge( $this->variables, $variables );

		return $this;
	}

	/**
	 * Check if a default value exists.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasDefault( $name ) {
		return array_key_exists( $name, $this->defaults );
	}

	/**
	 * Get the default value for a variable.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function getDefault( $name ) {
		return $this->hasDefault( $name ) ? $this->defaults[ $name ] : null;
	}

	/**
	 * Set a default value.
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function setDefault( $name, $value ) {
		$this->defaults[ $name ] = $value;

		return $this;
	}

	/**
	 * Clear a default value.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function clearDefault( $name ) {
		unset( $this->defaults[ $name ] );

		return $this;
	}

	/**
	 * Get all defaults.
	 *
	 * @return array
	 */
	public function allDefaults() {
		return $this->defaults;
	}

	/**
	 * Set multiple defaults at once.
	 *
	 * @param array $defaults
	 *
	 * @return $this
	 */
	public function populateDefaults( array $defaults ) {
		$this->defaults = array_merge( $this->defaults, $defaults );

		return $this;
	}

	/**
	 * Check if a variable is required.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isRequired( $name ) {
		return array_key_exists( $name, $this->required ) && (bool) $this->required[ $name ];
	}

	/**
	 * Make a variable required.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function makeRequired( $name ) {
		$this->required[ $name ] = true;

		return $this;
	}

	/**
	 * Make a variable not required.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function makeNotRequired( $name ) {
		if ( $this->has( $name ) ) {
			$this->required[ $name ] = false;
		}

		return $this;
	}

	/**
	 * Set multiple required fields at once.
	 *
	 * @param array $required
	 *
	 * @return $this
	 */
	public function populateRequired( array $required ) {
		if ( is_numeric( key( $required ) ) ) {
			$this->required = array_combine( $required, array_fill( 0, count( $required ), true ) );
		} else {
			$this->required = array_merge( $this->required, $required );
		}

		return $this;
	}

	/**
	 * Get all required variable names.
	 *
	 * @return array
	 */
	public function allRequired() {
		return array_keys( array_filter( $this->required ) );
	}

	/**
	 * Get names of all required variables that are not set.
	 *
	 * @return array
	 */
	public function allRequiredButNotSet() {
		$notSet = [];

		$required = $this->allRequired();
		foreach ( $required as $name ) {
			if ( ! $this->has( $name ) ) {
				$notSet[] = $name;
			}
		}

		return $notSet;
	}

	/**
	 * Reset registry to empty state.
	 *
	 * @return $this
	 */
	public function reset() {
		$this->defaults  = [];
		$this->required  = [];
		$this->variables = [];

		return $this;
	}

}
