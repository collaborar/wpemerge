<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Middleware;

use League\Container\ServiceProvider\AbstractServiceProvider;
use WPEmerge\Responses\ResponseService;

/**
 * Provide middleware dependencies.
 *
 * @codeCoverageIgnore
 */
class MiddlewareServiceProvider extends AbstractServiceProvider {

	public function provides( string $id ): bool {
		return in_array( $id, [
			UserLoggedInMiddleware::class,
			UserLoggedOutMiddleware::class,
			UserCanMiddleware::class,
		], true );
	}

	public function register(): void {
		$c = $this->getContainer();
		$c->addShared( UserLoggedInMiddleware::class )->addArguments( [ ResponseService::class ] );
		$c->addShared( UserLoggedOutMiddleware::class )->addArguments( [ ResponseService::class ] );
		$c->addShared( UserCanMiddleware::class )->addArguments( [ ResponseService::class ] );
	}
}
