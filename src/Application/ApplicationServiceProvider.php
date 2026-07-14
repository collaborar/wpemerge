<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Application;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Psr\Container\ContainerInterface;
use WPEmerge\Helpers\HandlerFactory;
use WPEmerge\Helpers\MixedType;
use WPEmerge\ServiceProviders\ExtendsConfigTrait;

/**
 * Provide application dependencies.
 *
 * @codeCoverageIgnore
 */
class ApplicationServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
	use ExtendsConfigTrait;

	public function provides( string $id ): bool {
		return in_array( $id, [
			GenericFactory::class,
			ClosureFactory::class,
			HandlerFactory::class,
			\WP_Filesystem_Base::class,
		], true );
	}

	public function boot(): void {
		$this->extendConfig( 'providers', [] );
		$this->extendConfig( 'namespace', 'App\\' );

		$uploadDir = wp_upload_dir();
		$cacheDir  = MixedType::addTrailingSlash( $uploadDir['basedir'] ) . 'wpemerge' . DIRECTORY_SEPARATOR . 'cache';
		$this->extendConfig( 'cache', [ 'path' => $cacheDir ] );

		$config        = $this->getContainer()->get( Configuration::class );
		$resolvedCache = $config->get( 'cache.path' );
		wp_mkdir_p( $resolvedCache );

		$app = $this->getContainer()->get( Application::class );
		$app->alias( 'app', Application::class );
		$app->alias( 'closure', ClosureFactory::class );
	}

	public function register(): void {
		$c = $this->getContainer();

		$c->addShared( GenericFactory::class )->addArguments( [ ContainerInterface::class ] );

		$c->addShared( ClosureFactory::class )->addArguments( [ GenericFactory::class ] );

		$c->addShared( HandlerFactory::class )->addArguments( [ GenericFactory::class ] );

		$c->addShared( \WP_Filesystem_Base::class, function () {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
			return $GLOBALS['wp_filesystem'];
		} );
	}
}
