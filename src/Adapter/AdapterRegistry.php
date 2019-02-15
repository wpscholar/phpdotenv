<?php

namespace wpscholar\phpdotenv\Adapter;

/**
 * Class AdapterRegistry
 */
class AdapterRegistry {

	/**
	 * Available adapters. Keys are class names and values are a boolean representing whether the adapter is active.
	 *
	 * @var array
	 */
	protected $adapters = [];

	/**
	 * Active adapters. Keys are class names and values are instances.
	 *
	 * @var AdapterInterface[]
	 */
	protected $adapterInstances = [];

	/**
	 * AdapterRegistry constructor.
	 */
	public function __construct() {

		// Loop through and automatically add all adapter class names.
		$iterator = new \RecursiveDirectoryIterator( __DIR__ );
		foreach ( new \RecursiveIteratorIterator( $iterator ) as $file ) {
			/**
			 * @var $file \SplFileObject
			 */
			if ( $file->getExtension() === 'php' && strpos( $file->getFilename(), 'Adapter.php' ) ) {
				$this->adapters[ str_replace( '.php', '', $file->getFilename() ) ] = false;
			}
		}

	}

	/**
	 * Check if an adapter exists.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function exists( $name ) {
		return array_key_exists( $this->convertToClassName( $name ), $this->adapters );
	}

	/**
	 * Get an adapter instance.
	 *
	 * @param string $name
	 *
	 * @return AdapterInterface
	 */
	public function get( $name ) {

		// Check if adapter exists.
		if ( ! $this->exists( $name ) ) {
			throw new \InvalidArgumentException( 'Invalid adapter name' );
		}

		// Check if adapter is active
		if ( ! $this->isActive( $name ) ) {
			throw new \LogicException( 'Cannot fetch invalid adapter' );
		}

		// Get the class name
		$className = $this->convertToClassName( $name );

		// Get instance, if it already exists.
		if ( isset( $this->adapterInstances[ $className ] ) ) {
			return $this->adapterInstances[ $className ];
		}

		$fullyQualifiedClassName = __NAMESPACE__ . '\\' . $className;

		/**
		 * @var AdapterInterface $instance
		 */
		$instance = new $fullyQualifiedClassName();

		if ( ! $instance->isSupported() ) {
			throw new \LogicException( sprintf( 'Unsupported adapter: %s', $className ) );
		}

		// Add adapter instance to the registry
		$this->adapterInstances[ $className ] = $instance;

		return $instance;
	}

	/**
	 * Check if an adapter is active.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isActive( $name ) {
		return $this->adapters[ $this->convertToClassName( $name ) ];
	}

	/**
	 * Activate an adapter.
	 *
	 * @param string $name
	 */
	public function activate( $name ) {
		if ( $this->exists( $name ) ) {
			$this->adapters[ $this->convertToClassName( $name ) ] = true;
		}
	}

	/**
	 * Deactivate an adapter.
	 *
	 * @param string $name
	 */
	public function deactivate( $name ) {
		if ( $this->exists( $name ) ) {
			// Get class name
			$className = $this->convertToClassName( $name );
			// Deactivate adapter
			$this->adapters[ $className ] = false;
			// Delete instance, if it exists.
			unset( $this->adapterInstances[ $className ] );
		}
	}

	/**
	 * Disable all adapters.
	 */
	public function disableAll() {
		$this->adapters = array_combine( array_keys( $this->adapters ), array_fill( 0, count( $this->adapters ), false ) );
	}

	/**
	 * Get an array containing all the active and supported adapter interfaces.
	 *
	 * @return AdapterInterface[]
	 */
	public function all() {
		$instances = [];
		foreach ( $this->adapters as $className => $isActive ) {
			if ( $isActive ) {
				$instances[ $className ] = $this->get( $className );
			}
		}

		return $instances;
	}

	/**
	 * Convert a string to a class name.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	protected function convertToClassName( $name ) {
		return ucwords( str_replace( [ 'adapter', ' ' ], '', strtolower( $name ) ) ) . 'Adapter';
	}


}
