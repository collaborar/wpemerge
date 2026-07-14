<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Application;

use Psr\Container\ContainerInterface;
use WPEmerge\Exceptions\ClassNotFoundException;

/**
 * Generic class instance factory.
 * Checks the container first, falls back to direct instantiation.
 */
class GenericFactory {
	/**
	 * Container.
	 *
	 * @var ContainerInterface
	 */
	protected ContainerInterface $container;

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 * @param ContainerInterface $container
	 */
	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	/**
	 * Make a class instance.
	 *
	 * @throws ClassNotFoundException
	 * @param  string $class
	 * @return object
	 */
	public function make( string $class ): object {
		if ( $this->container->has( $class ) ) {
			return $this->container->get( $class );
		}

		if ( ! class_exists( $class ) ) {
			throw new ClassNotFoundException( 'Class not found: ' . $class );
		}

		return new $class();
	}
}
