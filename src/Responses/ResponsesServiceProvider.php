<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Responses;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use WPEmerge\Application\Application;
use WPEmerge\Requests\RequestInterface;
use WPEmerge\View\ViewService;

/**
 * Provide responses dependencies.
 *
 * @codeCoverageIgnore
 */
class ResponsesServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {

	public function provides( string $id ): bool {
		return $id === ResponseService::class;
	}

	public function boot(): void {
		// Aliases need to be registered eagerly (they are accessed statically before services are resolved)
		$app = $this->getContainer()->get( Application::class );

		$app->alias( 'responses', ResponseService::class );

		$app->alias('response', function ( ...$args ) use ( $app ) {
			return $app->responses()->response( ...$args );
		} );

		$app->alias('output', function ( ...$args ) use ( $app ) {
			return $app->responses()->output( ...$args );
		} );

		$app->alias('json', function ( ...$args ) use ( $app ) {
			return $app->responses()->json( ...$args );
		} );

		$app->alias('redirect', function ( ...$args ) use ( $app ) {
			return $app->responses()->redirect( ...$args );
		} );

		$app->alias('error', function ( ...$args ) use ( $app ) {
			return $app->responses()->error( ...$args );
		} );
	}

	public function register(): void {
		$this->getContainer()->addShared( ResponseService::class )->addArguments( [
			RequestInterface::class,
			ViewService::class,
		] );
	}
}
