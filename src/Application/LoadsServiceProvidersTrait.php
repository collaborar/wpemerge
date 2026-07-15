<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Application;

use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use WPEmerge\Controllers\ControllersServiceProvider;
use WPEmerge\Csrf\CsrfServiceProvider;
use WPEmerge\Exceptions\ConfigurationException;
use WPEmerge\Exceptions\ExceptionsServiceProvider;
use WPEmerge\Flash\FlashServiceProvider;
use WPEmerge\Input\OldInputServiceProvider;
use WPEmerge\Kernels\KernelsServiceProvider;
use WPEmerge\Middleware\MiddlewareServiceProvider;
use WPEmerge\Requests\RequestsServiceProvider;
use WPEmerge\Responses\ResponsesServiceProvider;
use WPEmerge\Routing\RoutingServiceProvider;
use WPEmerge\Support\Arr;
use WPEmerge\View\ViewServiceProvider;

/**
 * Load service providers.
 */
trait LoadsServiceProvidersTrait {
	/**
	 * Array of default service provider class names.
	 *
	 * @var class-string<AbstractServiceProvider>[]
	 */
	protected array $service_providers = [
		ApplicationServiceProvider::class,
		KernelsServiceProvider::class,
		ExceptionsServiceProvider::class,
		RequestsServiceProvider::class,
		ResponsesServiceProvider::class,
		RoutingServiceProvider::class,
		ViewServiceProvider::class,
		ControllersServiceProvider::class,
		MiddlewareServiceProvider::class,
		CsrfServiceProvider::class,
		FlashServiceProvider::class,
		OldInputServiceProvider::class,
	];

	/**
	 * Instantiated provider instances, keyed by class name.
	 * Retained so WordPress hooks added in boot() can be removed later.
	 *
	 * @var array<class-string, AbstractServiceProvider>
	 */
	protected array $provider_instances = [];

	/**
	 * Register all service providers with the League container.
	 *
	 * @codeCoverageIgnore
	 * @param  Container $container
	 * @return void
	 */
	protected function loadServiceProviders( Container $container ): void {
		$config     = $container->get( Configuration::class );
		$providers  = [
			...$this->service_providers,
			...$config->get( 'providers', [] ),
		];

		foreach ( $providers as $providerClass ) {
			if ( ! is_subclass_of( $providerClass, AbstractServiceProvider::class ) ) {
				throw new ConfigurationException(
					'The following class is not defined or does not extend ' .
					AbstractServiceProvider::class . ': ' . $providerClass
				);
			}

			$instance = new $providerClass();
			$this->provider_instances[ $providerClass ] = $instance;
			$container->addServiceProvider( $instance );
		}
	}

	/**
	 * Get a registered service provider instance by class name.
	 * Useful for removing WordPress hooks added during boot().
	 *
	 * @param  class-string $providerClass
	 * @return AbstractServiceProvider|null
	 */
	public function getProvider( string $providerClass ): ?AbstractServiceProvider {
		return $this->provider_instances[ $providerClass ] ?? null;
	}
}
