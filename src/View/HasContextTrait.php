<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\View;

use WPEmerge\Support\Arr;

trait HasContextTrait {
	/**
	 * Context.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * Get context values.
	 *
	 * @param  string|null $key
	 * @param  mixed|null  $default
	 * @return mixed
	 */
	public function getContext( ?string $key = null, mixed $default = null ): mixed {
		if ( $key === null ) {
			return $this->context;
		}

		return Arr::get( $this->context, $key, $default );
	}

	/**
	 * Add context values.
	 *
	 * @param  string|array<string, mixed> $key
	 * @param  mixed                       $value
	 * @return static                      $this
	 */
	public function with( string|array $key, mixed $value = null ): static {
		if ( is_array( $key ) ) {
			$this->context = array_merge( $this->getContext(), $key );
		} else {
			$this->context[ $key ] = $value;
		}
		return $this;
	}
}
