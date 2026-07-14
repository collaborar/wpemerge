<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Middleware;

/**
 * Provide middleware definitions.
 */
interface HasMiddlewareDefinitionsInterface {
	/**
	 * Register middleware.
	 *
	 * @codeCoverageIgnore
	 * @param  array<string, string> $middleware
	 * @return void
	 */
	public function setMiddleware( array $middleware ): void;

	/**
	 * Register middleware groups.
	 *
	 * @codeCoverageIgnore
	 * @param  array<string, string[]> $middleware_groups
	 * @return void
	 */
	public function setMiddlewareGroups( array $middleware_groups ): void;

	/**
	 * Filter array of middleware into a unique set.
	 *
	 * @param  array[]  $middleware
	 * @return string[]
	 */
	public function uniqueMiddleware( array $middleware ): array;

	/**
	 * Expand array of middleware into an array of fully qualified class names.
	 *
	 * @param  string[] $middleware
	 * @return array[]
	 */
	public function expandMiddleware( array $middleware ): array;

	/**
	 * Expand a middleware group into an array of fully qualified class names.
	 *
	 * @param  string  $group
	 * @return array[]
	 */
	public function expandMiddlewareGroup( string $group ): array;

	/**
	 * Expand middleware into an array of fully qualified class names and any companion arguments.
	 *
	 * @param  string  $middleware
	 * @return array[]
	 */
	public function expandMiddlewareMolecule( string $middleware ): array;

	/**
	 * Expand a single middleware a fully qualified class name.
	 *
	 * @param  string $middleware
	 * @return string
	 */
	public function expandMiddlewareAtom( string $middleware ): string;
}
