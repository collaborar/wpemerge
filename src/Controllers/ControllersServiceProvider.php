<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Controllers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use WPEmerge\View\ViewService;

/**
 * Provide controller dependencies
 *
 * @codeCoverageIgnore
 */
class ControllersServiceProvider extends AbstractServiceProvider {

	public function provides( string $id ): bool {
		return $id === WordPressController::class;
	}

	public function register(): void {
		$this->getContainer()->addShared( WordPressController::class )->addArguments( [ ViewService::class ] );
	}
}
