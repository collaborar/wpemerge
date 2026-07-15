<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Helpers;

use Closure;
use WPEmerge\Application\GenericFactory;

/**
 * Handler factory.
 */
class HandlerFactory {
	/**
	 * Injection Factory.
	 */
	protected GenericFactory $factory;

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 * @param GenericFactory $factory
	 */
	public function __construct( GenericFactory $factory ) {
		$this->factory = $factory;
	}

	/**
	 * Make a Handler.
	 *
	 * @codeCoverageIgnore
	 * @param  string|Closure $raw_handler
	 * @param  string         $default_method
	 * @param  string         $namespace
	 * @return Handler
	 */
	public function make( string|Closure $raw_handler, string $default_method = '', string $namespace = '' ): Handler {
		return new Handler( $this->factory, $raw_handler, $default_method, $namespace );
	}
}
