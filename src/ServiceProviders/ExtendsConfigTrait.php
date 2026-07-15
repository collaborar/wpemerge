<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\ServiceProviders;

use WPEmerge\Application\Configuration;

/**
 * Convenience trait for service providers to extend the shared Configuration singleton.
 * Must be used inside a class that provides getContainer() (i.e. AbstractServiceProvider).
 */
trait ExtendsConfigTrait {
	/**
	 * Extend a top-level config key with default values.
	 * User-supplied values always take priority over defaults.
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return void
	 */
	protected function extendConfig( string $key, mixed $default ): void {
		$this->getContainer()->get( Configuration::class )->extend( $key, $default );
	}
}
