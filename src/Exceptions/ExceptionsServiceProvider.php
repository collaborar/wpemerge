<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Exceptions;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use WPEmerge\Application\Configuration;
use WPEmerge\Exceptions\Whoops\DebugDataProvider;
use WPEmerge\Responses\ResponseService;
use WPEmerge\ServiceProviders\ExtendsConfigTrait;

/**
 * Provide exceptions dependencies.
 *
 * @codeCoverageIgnore
 */
class ExceptionsServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
	use ExtendsConfigTrait;

	public function provides( string $id ): bool {
		return in_array( $id, [
			DebugDataProvider::class,
			PrettyPageHandler::class,
			Run::class,
			ErrorHandlerInterface::class,
		], true );
	}

	public function boot(): void {
		$debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$this->extendConfig( 'debug', [
			'enable'        => $debug,
			'pretty_errors' => $debug,
		] );
	}

	public function register(): void {
		$c = $this->getContainer();

		$c->addShared( DebugDataProvider::class )->addArguments( [ ContainerInterface::class ] );

		$c->addShared( PrettyPageHandler::class, function () use ( $c ) {
			$handler = new PrettyPageHandler();
			$handler->addResourcePath( implode( DIRECTORY_SEPARATOR, [WPEMERGE_DIR, 'src', 'Exceptions', 'Whoops'] ) );
			$handler->addDataTableCallback( 'WP Emerge: Route', function ( $inspector ) use ( $c ) {
				return $c->get( DebugDataProvider::class )->route( $inspector );
			} );
			return $handler;
		} );

		$c->addShared( Run::class, function () use ( $c ) {
			if ( ! class_exists( Run::class ) ) {
				return null;
			}
			$run = new Run();
			$run->allowQuit( false );
			$handler = $c->get( PrettyPageHandler::class );
			if ( $handler ) {
				$run->pushHandler( $handler );
			}
			return $run;
		} );

		$c->addShared( ErrorHandlerInterface::class, function () use ( $c ) {
			$debug  = $c->get( Configuration::class )->get( 'debug', [] );
			$whoops = ( $debug['pretty_errors'] ?? false ) ? $c->get( Run::class ) : null;
			return new ErrorHandler(
				$c->get( ResponseService::class ),
				$whoops,
				$debug['enable'] ?? false
			);
		} );
	}
}
