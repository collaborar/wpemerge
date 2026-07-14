<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Requests;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Provide request dependencies.
 *
 * @codeCoverageIgnore
 */
class RequestsServiceProvider extends AbstractServiceProvider {

	public function provides( string $id ): bool {
		return $id === RequestInterface::class;
	}

	public function register(): void {
		$this->getContainer()->addShared( RequestInterface::class, fn () => Request::fromGlobals() );
	}
}
