<?php

namespace wpscholar\phpdotenv;

use wpscholar\phpdotenv\Exception\ValidationException;

/**
 * Class Validator
 */
class Validator {

	/**
	 * The name of the variable to validate.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Registry where all variables exist.
	 *
	 * @var VariableRegistry
	 */
	protected $registry;

	/**
	 * Create a new instance.
	 *
	 * @param string           $name
	 * @param VariableRegistry $registry
	 *
	 * @return self
	 */
	public function create( $name, VariableRegistry $registry ) {
		return new self( $name, $registry );
	}

	/**
	 * Validator constructor.
	 *
	 * @param string           $name
	 * @param VariableRegistry $registry
	 */
	public function __construct( $name, VariableRegistry $registry ) {
		$this->name     = $name;
		$this->registry = $registry;
	}

	/**
	 * Apply an assertion.
	 *
	 * @throws \InvalidArgumentException
	 * @throws Exception\ValidationException
	 *
	 * @param string|callable $assertion
	 * @param array           $arguments
	 *
	 * @return $this
	 */
	public function apply( $assertion, $arguments = [] ) {
		if ( 'apply' === $assertion || false !== strpos( $assertion, 'assert' ) ) {
			throw new \InvalidArgumentException( 'Invalid assertion name' );
		}
		if ( is_callable( $assertion ) ) {
			return $this->assert( $assertion );
		}
		if ( method_exists( $this, $assertion ) ) {
			return $this->$assertion( ...$arguments );
		}
		throw new \InvalidArgumentException( 'Invalid assertion name' );
	}

	/**
	 * Assert that the variable is amongst the given choices.
	 *
	 * @throws Exception\ValidationException
	 *
	 * @param array $choices
	 *
	 * @return $this
	 */
	public function allowedValues( array $choices ) {
		return $this->assertCallback(
			function ( $value ) use ( $choices ) {
				return in_array( $value, $choices, true );
			},
			sprintf( '%s is not one of [%s]', $this->name, implode( ', ', $choices ) )
		);
	}

	/**
	 * Assert that the variable passes the callback.
	 *
	 * @throws Exception\ValidationException
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function assert( callable $callback ) {
		return $this->assertCallback( $callback, sprintf( '%s failed a custom validation assertion', $this->name ) );
	}

	/**
	 * Assert that the variable is a boolean.
	 *
	 * @throws Exception\ValidationException
	 *
	 * @return $this
	 */
	public function isBoolean() {
		return $this->assertCallback(
			function ( $value ) {
				return filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) !== null;
			},
			sprintf( '%s is not a boolean', $this->name )
		);
	}

	/**
	 * Assert that the variable is an integer.
	 *
	 * @throws Exception\ValidationException
	 *
	 * @return $this
	 */
	public function isInteger() {
		return $this->assertCallback(
			function ( $value ) {
				return is_int( $value );
			},
			sprintf( '%s is not an integer', $this->name )
		);
	}

	/**
	 * Assert that the variable is not empty.
	 *
	 * @throws Exception\ValidationException
	 *
	 * @return $this
	 */
	public function notEmpty() {
		return $this->assertCallback(
			function ( $value ) {
				return ! empty( $value );
			},
			sprintf( '%s is empty', $this->name )
		);
	}

	/**
	 * Assert that the callback returns true for each variable.
	 *
	 * @throws Exception\ValidationException
	 *
	 * @param callable $callback
	 * @param string   $message
	 *
	 * @return $this
	 */
	protected function assertCallback( callable $callback, $message ) {

		$value = $this->registry->get( $this->name );

		$isValid = $callback( $value );

		if ( ! $isValid ) {
			throw new ValidationException( $message );
		}

		return $this;
	}

}
