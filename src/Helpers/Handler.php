<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Helpers;

use Closure;
use WPEmerge\Application\GenericFactory;
use WPEmerge\Exceptions\ClassNotFoundException;
use WPEmerge\Exceptions\ConfigurationException;
use WPEmerge\Support\Arr;

/**
 * Represent a generic handler - a Closure or a class method to be resolved from the service container
 */
class Handler {
	/**
	 * Injection Factory.
	 */
	protected GenericFactory $factory;

	/**
	 * Parsed handler
	 */
	protected array|Closure $handler;

	/**
	 * Constructor
	 *
	 * @param GenericFactory       $factory
	 * @param string|array|Closure $raw_handler
	 * @param string               $default_method
	 * @param string               $namespace
	 */
	public function __construct( GenericFactory $factory, string|array|Closure $raw_handler, string $default_method = '', string $namespace = '' ) {
		$this->factory = $factory;

		$handler = $this->parse( $raw_handler, $default_method, $namespace );

		if ( $handler === null ) {
			throw new ConfigurationException( 'No or invalid handler provided.' );
		}

		$this->handler = $handler;
	}

	/**
	 * Parse a raw handler to a Closure or a [class, method, namespace] array
	 *
	 * @param  string|array|Closure $raw_handler
	 * @param  string               $default_method
	 * @param  string               $namespace
	 * @return array|Closure|null
	 */
	protected function parse( string|array|Closure $raw_handler, string $default_method, string $namespace ): array|Closure|null {
		if ( $raw_handler instanceof Closure ) {
			return $raw_handler;
		}

		if ( is_array( $raw_handler ) ) {
			return $this->parseFromArray( $raw_handler, $default_method, $namespace );
		}

		return $this->parseFromString( $raw_handler, $default_method, $namespace );
	}

	/**
	 * Parse a [Class::class, 'method'] array handler to a [class, method, namespace] array
	 *
	 * @param  array      $raw_handler
	 * @param  string     $default_method
	 * @param  string     $namespace
	 * @return array|null
	 */
	protected function parseFromArray( array $raw_handler, string $default_method, string $namespace ): ?array {
		$class = Arr::get( $raw_handler, 0, '' );
		$class = preg_replace( '/^\\\\+/', '', $class );
		$method = Arr::get( $raw_handler, 1, $default_method );

		if ( empty( $class ) ) {
			return null;
		}

		if ( empty( $method ) ) {
			return null;
		}

		return [
			'class' => $class,
			'method' => $method,
			'namespace' => $namespace,
		];
	}

	/**
	 * Parse a 'Controller@method' or 'Controller::method' string handler to a [class, method, namespace] array
	 *
	 * @param  string     $raw_handler
	 * @param  string     $default_method
	 * @param  string     $namespace
	 * @return array|null
	 */
	protected function parseFromString( string $raw_handler, string $default_method, string $namespace ): ?array {
		return $this->parseFromArray( preg_split( '/@|::/', $raw_handler, 2 ), $default_method, $namespace );
	}

	/**
	 * Get the parsed handler
	 *
	 * @return array|Closure
	 */
	public function get(): array|Closure {
		return $this->handler;
	}

	/**
	 * Make an instance of the handler.
	 *
	 * @return object
	 */
	public function make(): object {
		$handler = $this->get();

		if ( $handler instanceof Closure ) {
			return $handler;
		}

		$namespace = $handler['namespace'];
		$class = $handler['class'];

		try {
			$instance = $this->factory->make( $class );
		} catch ( ClassNotFoundException $e ) {
			try {
				$instance = $this->factory->make( $namespace . $class );
			} catch ( ClassNotFoundException $e ) {
				throw new ClassNotFoundException( 'Class not found - tried: ' . $class . ', ' . $namespace . $class );
			}
		}

		return $instance;
	}

	/**
	 * Execute the parsed handler with any provided arguments and return the result.
	 *
	 * @param  mixed ,...$arguments
	 * @return mixed
	 */
	public function execute( mixed ...$arguments ): mixed {
		$instance = $this->make();

		if ( $instance instanceof Closure ) {
			return $instance( ...$arguments );
		}

		return $instance->{$this->get()['method']}( ...$arguments );
	}
}
