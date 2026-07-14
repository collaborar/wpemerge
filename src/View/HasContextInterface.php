<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\View;

interface HasContextInterface {
	/**
	 * Get context values.
	 *
	 * @param  string|null $key
	 * @param  mixed|null  $default
	 * @return mixed
	 */
	public function getContext( ?string $key = null, mixed $default = null ): mixed;

	/**
	 * Add context values.
	 *
	 * @param  string|array<string, mixed> $key
	 * @param  mixed                       $value
	 * @return static                      $this
	 */
	public function with( string|array $key, mixed $value = null ): static;
}
