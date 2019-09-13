<?php

namespace wpscholar\phpdotenv;

use M1\Env\Parser;
use wpscholar\phpdotenv\Adapter\AdapterRegistry;
use wpscholar\phpdotenv\Exception\ValidationException;

/**
 * Class Loader
 */
class Loader {

	/**
	 * Adapter registry.
	 *
	 * @var Adapter\AdapterRegistry
	 */
	protected $adapterRegistry;

	/**
	 * Whether or not .env file has been parsed.
	 *
	 * @var bool
	 */
	protected $parsed = false;

	/**
	 * Variable registry.
	 *
	 * @var VariableRegistry
	 */
	protected $variableRegistry;

	/**
	 * Create a new instance.
	 *
	 * @return self
	 */
	public static function create() {
		return new self();
	}

	/**
	 * DotEnv constructor.
	 */
	public function __construct() {
		$this->adapterRegistry = new AdapterRegistry();
		$this->variableRegistry = new VariableRegistry();
		$this->useAdapters( [ 'env', 'putenv', 'server' ] );
	}

	/**
	 * Apply configuration options.
	 *
	 * @param array $options
	 *
	 * @return $this
	 */
	public function config( $options = [] ) {

		// Setup adapters
		if ( isset( $options['adapters'] ) ) {
			$this->useAdapters( (array) $options['adapters'] );
		}

		// Set variable defaults
		if ( isset( $options['defaults'] ) ) {
			if ( ! is_array( $options['defaults'] ) ) {
				throw new \InvalidArgumentException( sprintf( "Invalid format for default variables. Must be an array; '%s' provided", gettype( $options['defaults'] ) ) );
			}
			$this->variableRegistry->populateDefaults( $options['defaults'] );
		}

		// Set required variables
		if ( isset( $options['required'] ) ) {
			if ( is_string( $options['required'] ) ) {
				$this->variableRegistry->makeRequired( $options['required'] );
			} elseif ( is_array( $options['required'] ) ) {
				$this->variableRegistry->populateRequired( $options['required'] );
			} else {
				throw new \InvalidArgumentException( sprintf( 'Invalid format for required variables: %s provided', gettype( $options['required'] ) ) );
			}
		}

		return $this;
	}

	/**
	 * Parse the .env file and set the variables on this class instance.
	 *
	 * @param string|array $filepaths
	 *
	 * @return $this
	 */
	public function parse( $filepaths ) {

		if ( ! empty( $filepaths ) ) {

			$filepaths = (array) $filepaths;

			// Check each file path sequentially until we find an env file.
			foreach ( $filepaths as $filepath ) {

				if ( ! file_exists( $filepath ) ) {
					continue;
				}

				if ( ! is_readable( $filepath ) || false === ( $contents = file_get_contents( $filepath ) ) ) {
					throw new \InvalidArgumentException( sprintf( "Environment file '%s' is not readable", $filepath ) );
				}

				if ( $contents ) {
					break;
				}
			}

			if ( ! isset( $contents ) ) {
				throw new \InvalidArgumentException( 'Unable to find .env file' );
			}

			// Parse file
			$env = new Parser( $contents );

			// Set variables
			$this->variableRegistry->populate( $env->getContent() );

		}

		$this->parsed = true;

		return $this;
	}

	/**
	 * Load variables into environment. Existing variables will not be overwritten.
	 *
	 * @return $this
	 * @throws Exception\ValidationException
	 *
	 */
	public function load() {

		$this->checkRequired();

		$adapters = $this->adapterRegistry->all();
		foreach ( $adapters as $adapter ) {
			$variables = $this->variableRegistry->all();
			foreach ( $variables as $name => $value ) {
				// By default, the load() method does not overwrite existing variables.
				if ( ! $adapter->has( $name ) ) {
					$adapter->set( $name, $value );
				}
			}
		}

		return $this;
	}

	/**
	 * Load variables into environment. Existing variables will not be overwritten.
	 *
	 * @return $this
	 * @throws Exception\ValidationException
	 *
	 */
	public function overload() {

		$this->checkRequired();

		$adapters = $this->adapterRegistry->all();
		foreach ( $adapters as $adapter ) {
			$variables = $this->variableRegistry->all();
			foreach ( $variables as $name => $value ) {
				// By default, the overload() method overwrites existing variables
				$adapter->set( $name, $value );
			}
		}

		return $this;
	}

	/**
	 * Check if a variable is set.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has( $name ) {
		return $this->variableRegistry->has( $name );
	}

	/**
	 * Get a variable value.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get( $name ) {
		return $this->variableRegistry->get( $name );
	}

	/**
	 * Set a variable value.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return $this
	 */
	public function set( $name, $value ) {
		$this->variableRegistry->set( $name, $value );

		return $this;
	}

	/**
	 * Clear a variable.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function clear( $name ) {
		$this->variableRegistry->clear( $name );

		return $this;
	}

	/**
	 * Get all variables.
	 *
	 * @return array
	 */
	public function all() {
		return $this->variableRegistry->all();
	}

	/**
	 * Mark one or more variables as required.
	 *
	 * @param string|array $name
	 *
	 * @return $this
	 */
	public function required( $name ) {
		$this->variableRegistry->populateRequired( (array) $name );

		return $this;
	}

	/**
	 * Checks if required variables are set.
	 *
	 * @return $this
	 * @throws Exception\ValidationException
	 *
	 */
	public function checkRequired() {

		if ( ! $this->parsed ) {
			throw new \LogicException( 'A .env file must be parsed before checking if required variables exist' );
		}

		$required = $this->variableRegistry->allRequiredButNotSet();
		if ( ! empty( $required ) ) {
			throw new ValidationException( sprintf( 'Required variables are not set: %s', implode( ', ', $required ) ) );
		}

		return $this;
	}

	/**
	 * Set default value in the event that a value is not set.
	 *
	 * @param string $name
	 * @param mixed $default
	 *
	 * @return $this
	 */
	public function setDefault( $name, $default ) {
		$this->variableRegistry->setDefault( $name, $default );

		return $this;
	}

	/**
	 * Bulk set default values.
	 *
	 * @param array $defaults
	 *
	 * @return $this
	 */
	public function setDefaults( array $defaults ) {
		$this->variableRegistry->populateDefaults( $defaults );

		return $this;
	}

	/**
	 * Validate a single variable.
	 *
	 * @param string $name
	 *
	 * @return Validator
	 */
	public function validate( $name ) {

		if ( ! $this->parsed ) {
			throw new \LogicException( 'Validation rules can only be run after a .env file has been parsed.' );
		}

		return new Validator( $name, $this->variableRegistry );
	}

	/**
	 * Get a specific adapter by name.
	 *
	 * @param string $name
	 *
	 * @return Adapter\AdapterInterface
	 */
	public function getAdapter( $name ) {
		return $this->adapterRegistry->get( $name );
	}

	/**
	 * Use adapters.
	 *
	 * @param array $adapters
	 */
	protected function useAdapters( array $adapters ) {
		foreach ( $adapters as $adapter ) {
			$this->adapterRegistry->activate( $adapter );
		}
	}

}
