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
use League\Container\Container;
use League\Container\ReflectionContainer;
use Psr\Container\ContainerInterface;
use WPEmerge\Exceptions\ConfigurationException;
use WPEmerge\Kernels\HttpKernelInterface;
use WPEmerge\Requests\Request;
use WPEmerge\Responses\ResponseService;
use WPEmerge\Support\Arr;

/**
 * The core WP Emerge component representing an application.
 */
class Application {
	use HasAliasesTrait;
	use LoadsServiceProvidersTrait;
	use HasContainerTrait;

	/**
	 * Flag whether to intercept and render configuration exceptions.
	 *
	 * @var bool
	 */
	protected bool $render_config_exceptions = true;

	/**
	 * Flag whether the application has been bootstrapped.
	 *
	 * @var bool
	 */
	protected bool $bootstrapped = false;

	/**
	 * Make a new application instance.
	 *
	 * @codeCoverageIgnore
	 * @return static
	 */
	public static function make(): static {
		return new static( new Container() );
	}

	/**
	 * Constructor.
	 *
	 * @param Container $container
	 * @param bool      $render_config_exceptions
	 */
	public function __construct( Container $container, bool $render_config_exceptions = true ) {
		$this->setContainer( $container );
		$this->render_config_exceptions = $render_config_exceptions;

		// Enable auto-wiring via ReflectionContainer (cache reflections for performance).
		$container->delegate( new ReflectionContainer( true ) );

		// Self-register so the container can resolve the application and ContainerInterface.
		$container->addShared( static::class, fn () => $this );
		$container->addShared( Application::class, fn () => $this );
		$container->addShared( ContainerInterface::class, fn () => $container );
	}

	/**
	 * Get whether the application has been bootstrapped.
	 *
	 * @return bool
	 */
	public function isBootstrapped(): bool {
		return $this->bootstrapped;
	}

	/**
	 * Bootstrap the application.
	 *
	 * @param  array $config
	 * @param  bool  $run
	 * @return void
	 */
	public function bootstrap( array $config = [], bool $run = true ): void {
		if ( $this->isBootstrapped() ) {
			throw new ConfigurationException( static::class . ' already bootstrapped.' );
		}

		$this->bootstrapped = true;

		$container = $this->container();
		$this->loadConfig( $container, $config );
		$this->loadServiceProviders( $container );

		$this->renderConfigExceptions( function () use ( $run ) {
			$this->loadRoutes();

			if ( $run ) {
				$kernel = $this->resolve( HttpKernelInterface::class );
				$kernel->bootstrap();
			}
		} );
	}

	/**
	 * Register the Configuration singleton in the container.
	 *
	 * @codeCoverageIgnore
	 * @param  Container $container
	 * @param  array     $config
	 * @return void
	 */
	protected function loadConfig( Container $container, array $config ): void {
		$configuration = new Configuration( $config );
		$container->addShared( Configuration::class, fn () => $configuration );
	}

	/**
	 * Load route definition files depending on the current request.
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	protected function loadRoutes(): void {
		if ( wp_doing_ajax() ) {
			$this->loadRoutesGroup( 'ajax' );
			return;
		}

		if ( is_admin() ) {
			$this->loadRoutesGroup( 'admin' );
			return;
		}

		$this->loadRoutesGroup( 'web' );
	}

	/**
	 * Load a route group applying default attributes, if any.
	 *
	 * @codeCoverageIgnore
	 * @param  string $group
	 * @return void
	 */
	protected function loadRoutesGroup( string $group ): void {
		$config = $this->resolve( Configuration::class );
		$file   = $config->get( 'routes.' . $group . '.definitions', '' );
		$attributes = $config->get( 'routes.' . $group . '.attributes', [] );

		if ( empty( $file ) ) {
			return;
		}

		$middleware = Arr::get( $attributes, 'middleware', [] );

		if ( ! in_array( $group, $middleware, true ) ) {
			$middleware = array_merge( [ $group ], $middleware );
		}

		$attributes['middleware'] = $middleware;

		$blueprint = $this->resolve( \WPEmerge\Routing\RouteBlueprint::class );
		$blueprint->attributes( $attributes )->group( $file );
	}

	/**
	 * Catch any configuration exceptions and short-circuit to an error page.
	 *
	 * @codeCoverageIgnore
	 * @param  Closure $action
	 * @return void
	 */
	public function renderConfigExceptions( Closure $action ): void {
		try {
			$action();
		} catch ( ConfigurationException $exception ) {
			if ( ! $this->render_config_exceptions ) {
				throw $exception;
			}

			$request         = Request::fromGlobals();
			$error_handler   = $this->resolve( \WPEmerge\Exceptions\ErrorHandlerInterface::class );

			add_filter( 'wpemerge.pretty_errors.apply_admin_styles', '__return_false' );

			$response_service = $this->resolve( ResponseService::class );
			$response_service->respond( $error_handler->getResponse( $request, $exception ) );

			wp_die();
		}
	}
}
