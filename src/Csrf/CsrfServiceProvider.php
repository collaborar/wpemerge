<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Csrf;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use WPEmerge\Application\Application;

/**
 * Provide CSRF dependencies.
 *
 * @codeCoverageIgnore
 */
class CsrfServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {

	public function provides( string $id ): bool {
		return in_array( $id, [ Csrf::class, CsrfMiddleware::class ], true );
	}

	public function boot(): void {
		$app = $this->getContainer()->get( Application::class );
		$app->alias( 'csrf', Csrf::class );
	}

	public function register(): void {
		$c = $this->getContainer();
		$c->addShared( Csrf::class );
		$c->addShared( CsrfMiddleware::class )->addArguments( [ Csrf::class ] );
	}
}
