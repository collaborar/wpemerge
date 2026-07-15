<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Application;

use League\Container\Container;

/**
 * Holds a League Container instance.
 */
trait HasContainerTrait {
	/**
	 * IoC container.
	 */
	protected ?Container $container = null;

	/**
	 * Get the IoC container instance.
	 *
	 * @codeCoverageIgnore
	 * @return Container|null
	 */
	public function container(): ?Container {
		return $this->container;
	}

	/**
	 * Set the IoC container instance.
	 *
	 * @codeCoverageIgnore
	 * @param  Container $container
	 * @return void
	 */
	public function setContainer( Container $container ): void {
		$this->container = $container;
	}

	/**
	 * Resolve a dependency from the IoC container.
	 *
	 * @param  string     $key
	 * @return mixed|null
	 */
	public function resolve( string $key ): mixed {
		if ( ! $this->container()->has( $key ) ) {
			return null;
		}

		return $this->container()->get( $key );
	}
}
