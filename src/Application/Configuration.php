<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Application;

use WPEmerge\Support\Arr;

/**
 * Mutable configuration value object stored as a shared singleton in the container.
 * Replaces the raw PHP array previously stored at WPEMERGE_CONFIG_KEY.
 */
class Configuration {
	/**
	 * Configuration data.
	 *
	 * @var array<string, mixed>
	 */
	private array $data;

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $data
	 */
	public function __construct( array $data = [] ) {
		$this->data = $data;
	}

	/**
	 * Get a configuration value using dot notation.
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function get( string $key, mixed $default = null ): mixed {
		return Arr::get( $this->data, $key, $default );
	}

	/**
	 * Set a configuration value using dot notation.
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function set( string $key, mixed $value ): void {
		Arr::set( $this->data, $key, $value );
	}

	/**
	 * Get all configuration data.
	 *
	 * @return array<string, mixed>
	 */
	public function all(): array {
		return $this->data;
	}

	/**
	 * Extend a top-level config key with defaults.
	 * Mirrors the logic previously in ExtendsConfigTrait::replaceConfig().
	 *
	 * - If either value is not an array, the existing config value wins.
	 * - If both are indexed arrays, the existing config value wins.
	 * - If either is an associative array, array_replace is used with existing config having priority.
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return void
	 */
	public function extend( string $key, mixed $default ): void {
		$existing = Arr::get( $this->data, $key, $default );
		$this->data[ $key ] = $this->mergeWithDefault( $default, $existing );
	}

	/**
	 * Recursively merge config value over defaults.
	 *
	 * @param  mixed $default
	 * @param  mixed $config
	 * @return mixed
	 */
	private function mergeWithDefault( mixed $default, mixed $config ): mixed {
		if ( ! is_array( $default ) || ! is_array( $config ) ) {
			return $config;
		}

		$defaultIsIndexed = array_keys( $default ) === range( 0, count( $default ) - 1 );
		$configIsIndexed  = array_keys( $config ) === range( 0, count( $config ) - 1 );

		if ( $defaultIsIndexed && $configIsIndexed ) {
			return $config;
		}

		$result = $default;

		foreach ( $config as $k => $v ) {
			$result[ $k ] = $this->mergeWithDefault( Arr::get( $default, $k ), $v );
		}

		return $result;
	}
}
