<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Application;

use Closure;

/**
 * Factory that makes closures which resolve values from the container.
 */
class ClosureFactory {
	/**
	 * Factory.
	 */
	protected GenericFactory $factory;

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 * @param GenericFactory $factory
	 */
	public function __construct( GenericFactory $factory ) {
		$this->factory = $factory;
	}

	/**
	 * Make a closure that resolves a value from the container.
	 *
	 * @param  string $key
	 * @return Closure
	 */
	public function value( string $key ): Closure {
		return function () use ( $key ) {
			return $this->factory->make( $key );
		};
	}

	/**
	 * Make a closure that resolves a class instance from the container and
	 * calls one of its methods.
	 * Useful if you need to pass a callable to an API without container
	 * support such as the REST API.
	 *
	 * @param  string $key
	 * @param  string $method
	 * @return Closure
	 */
	public function method( string $key, string $method ): Closure {
		return function ( ...$args ) use ( $key, $method ) {
			return $this->factory->make( $key )->{$method}( ...$args );
		};
	}
}
