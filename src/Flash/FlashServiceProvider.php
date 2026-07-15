<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Flash;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use WPEmerge\Application\Application;

/**
 * Provide flash dependencies.
 *
 * @codeCoverageIgnore
 */
class FlashServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {

	public function provides( string $id ): bool {
		return in_array( $id, [Flash::class, FlashMiddleware::class], true );
	}

	public function boot(): void {
		$app = $this->getContainer()->get( Application::class );
		$app->alias( 'flash', Flash::class );
	}

	public function register(): void {
		$c = $this->getContainer();

		$c->addShared( Flash::class, function () use ( $c ) {
			if ( $c->has( WPEMERGE_SESSION_KEY ) ) {
				$session = $c->get( WPEMERGE_SESSION_KEY );
			} elseif ( isset( $_SESSION ) ) {
				$session = &$_SESSION;
			} else {
				$session = null;
			}
			return new Flash( $session );
		} );

		$c->addShared( FlashMiddleware::class )->addArguments( [ Flash::class ] );
	}
}
