<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Input;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use WPEmerge\Application\Application;
use WPEmerge\Flash\Flash;

/**
 * Provide old input dependencies.
 *
 * @codeCoverageIgnore
 */
class OldInputServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {

	public function provides( string $id ): bool {
		return in_array( $id, [OldInput::class, OldInputMiddleware::class], true );
	}

	public function boot(): void {
		$app = $this->getContainer()->get( Application::class );
		$app->alias( 'oldInput', OldInput::class );
	}

	public function register(): void {
		$c = $this->getContainer();
		$c->addShared( OldInput::class )->addArguments( [ Flash::class ] );
		$c->addShared( OldInputMiddleware::class )->addArguments( [ OldInput::class ] );
	}
}
