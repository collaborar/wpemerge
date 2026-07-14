<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\View;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use WPEmerge\Application\Application;
use WPEmerge\Application\Configuration;
use WPEmerge\Helpers\HandlerFactory;
use WPEmerge\Helpers\MixedType;
use WPEmerge\ServiceProviders\ExtendsConfigTrait;

/**
 * Provide view dependencies.
 *
 * @codeCoverageIgnore
 */
class ViewServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
	use ExtendsConfigTrait;

	public function provides( string $id ): bool {
		return in_array( $id, [
			ViewService::class,
			ViewEngineInterface::class,
			PhpViewEngine::class,
		], true );
	}

	public function boot(): void {
		$config    = $this->getContainer()->get( Configuration::class );
		$namespace = $config->get( 'namespace', 'App\\' );

		$this->extendConfig( 'views', [get_stylesheet_directory(), get_template_directory()] );
		$this->extendConfig( 'view_composers', [
			'namespace' => $namespace . 'ViewComposers\\',
		] );

		$app = $this->getContainer()->get( Application::class );
		$app->alias( 'views', ViewService::class );

		$app->alias( 'view', function () use ( $app ) {
			return call_user_func_array( [$app->views(), 'make'], func_get_args() );
		} );

		$app->alias( 'render', function () use ( $app ) {
			return call_user_func_array( [$app->views(), 'render'], func_get_args() );
		} );

		$app->alias( 'layoutContent', function () use ( $app ) {
			/** @var PhpViewEngine $engine */
			$engine = $app->resolve( PhpViewEngine::class );
			echo $engine->getLayoutContent();
		} );
	}

	public function register(): void {
		$c = $this->getContainer();

		$c->addShared( PhpViewEngine::class, function () use ( $c ) {
			$config = $c->get( Configuration::class );
			$views  = MixedType::toArray( $config->get( 'views', [] ) );
			$finder = new PhpViewFilesystemFinder( $views );

			$composeAction = function ( ViewInterface $view ) use ( $c ): ViewInterface {
				$c->get( ViewService::class )->compose( $view );
				return $view;
			};

			return new PhpViewEngine( $composeAction, $finder );
		} );

		$c->addShared( ViewEngineInterface::class, fn () => $c->get( PhpViewEngine::class ) );

		$c->addShared( ViewService::class )->addArguments( [
			Configuration::class,
			ViewEngineInterface::class,
			HandlerFactory::class,
		] );
	}
}
