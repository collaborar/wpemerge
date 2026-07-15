<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Kernels;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use WPEmerge\Application\Application;
use WPEmerge\Application\Configuration;
use WPEmerge\Application\GenericFactory;
use WPEmerge\Exceptions\ErrorHandlerInterface;
use WPEmerge\Helpers\HandlerFactory;
use WPEmerge\Requests\RequestInterface;
use WPEmerge\Responses\ResponseService;
use WPEmerge\Routing\Router;
use WPEmerge\ServiceProviders\ExtendsConfigTrait;
use WPEmerge\View\ViewService;

/**
 * Provide kernel dependencies.
 *
 * @codeCoverageIgnore
 */
class KernelsServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
	use ExtendsConfigTrait;

	public function provides( string $id ): bool {
		return $id === HttpKernelInterface::class;
	}

	public function boot(): void {
		$this->extendConfig( 'middleware', [
			'flash'        => \WPEmerge\Flash\FlashMiddleware::class,
			'old_input'    => \WPEmerge\Input\OldInputMiddleware::class,
			'csrf'         => \WPEmerge\Csrf\CsrfMiddleware::class,
			'user.logged_in'  => \WPEmerge\Middleware\UserLoggedInMiddleware::class,
			'user.logged_out' => \WPEmerge\Middleware\UserLoggedOutMiddleware::class,
			'user.can'     => \WPEmerge\Middleware\UserCanMiddleware::class,
		] );

		$this->extendConfig( 'middleware_groups', [
			'wpemerge' => ['flash', 'old_input'],
			'global'   => [],
			'web'      => [],
			'ajax'     => [],
			'admin'    => [],
		] );

		$this->extendConfig( 'middleware_priority', [] );

		$app = $this->getContainer()->get( Application::class );
		$app->alias( 'run', function ( ...$args ) use ( $app ) {
			$kernel = $app->resolve( HttpKernelInterface::class );
			return $kernel->run( ...$args );
		} );
	}

	public function register(): void {
		$this->getContainer()->addShared( HttpKernelInterface::class, function () {
			$c = $this->getContainer();
			$config = $c->get( Configuration::class );

			$kernel = new HttpKernel(
				$c->get( Application::class ),
				$c->get( GenericFactory::class ),
				$c->get( HandlerFactory::class ),
				$c->get( ResponseService::class ),
				$c->get( RequestInterface::class ),
				$c->get( Router::class ),
				$c->get( ViewService::class ),
				$c->get( ErrorHandlerInterface::class )
			);

			$kernel->setMiddleware( $config->get( 'middleware', [] ) );
			$kernel->setMiddlewareGroups( $config->get( 'middleware_groups', [] ) );
			$kernel->setMiddlewarePriority( $config->get( 'middleware_priority', [] ) );

			return $kernel;
		} );
	}
}
