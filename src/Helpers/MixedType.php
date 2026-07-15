<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Helpers;

class MixedType {
	/**
	 * Converts a value to an array containing this value unless it is an array.
	 * This will not convert objects like (array) casting does.
	 *
	 * @param  mixed $argument
	 * @return array
	 */
	public static function toArray( mixed $argument ): array {
		if ( ! is_array( $argument ) ) {
			$argument = [$argument];
		}

		return $argument;
	}

	/**
	 * Executes a value depending on what type it is and returns the result
	 * Callable: call; return result
	 * Instance: call method; return result
	 * Class:    instantiate; call method; return result
	 * Other:    return value without taking any action
	 *
	 * @noinspection PhpDocSignatureInspection
	 * @param  mixed    $entity
	 * @param  array    $arguments
	 * @param  string   $method
	 * @param  callable $instantiator
	 * @return mixed
	 */
	public static function value(
		mixed $entity,
		array $arguments = [],
		string $method = '__invoke',
		?callable $instantiator = null
	): mixed {
		if ( is_callable( $entity ) ) {
			return $entity( ...$arguments );
		}

		if ( is_object( $entity ) ) {
			return $entity->{$method}( ...$arguments );
		}

		if ( static::isClass( $entity ) ) {
			$instantiator = $instantiator ?? [static::class, 'instantiate'];
			return call_user_func( $instantiator, $entity )->{$method}( ...$arguments );
		}

		return $entity;
	}

	/**
	 * Check if a value is a valid class name
	 *
	 * @param  mixed   $class_name
	 * @return boolean
	 */
	public static function isClass( mixed $class_name ): bool {
		return ( is_string( $class_name ) && class_exists( $class_name ) );
	}

	/**
	 * Get the class "basename" of the given object / class.
	 *
	 * @param mixed $class_name
	 * @return string
	 */
	public static function classBasename( mixed $class_name ): string {
		$class_name = is_object( $class_name ) ? get_class( $class_name ) : $class_name;
		return basename( str_replace( '\\', '/', $class_name ) );
	}

	/**
	 * Create a new instance of the given class.
	 *
	 * @param  string $class_name
	 * @return object
	 */
	public static function instantiate( string $class_name ): object {
		return new $class_name();
	}

	/**
	 * Normalize a path's slashes according to the current OS.
	 * Solves mixed slashes that are sometimes returned by WordPress core functions.
	 *
	 * @param  string $path
	 * @param  string $slash
	 * @return string
	 */
	public static function normalizePath( string $path, string $slash = DIRECTORY_SEPARATOR ): string {
		return preg_replace( '~[' . preg_quote( '/\\', '~' ) . ']+~', $slash, $path );
	}

	/**
	 * Ensure path has a trailing slash.
	 *
	 * @param  string $path
	 * @param  string $slash
	 * @return string
	 */
	public static function addTrailingSlash( string $path, string $slash = DIRECTORY_SEPARATOR ): string {
		$path = static::normalizePath( $path, $slash );
		$path = preg_replace( '~' . preg_quote( $slash, '~' ) . '*$~', $slash, $path );
		return $path;
	}

	/**
	 * Ensure path does not have a trailing slash.
	 *
	 * @param  string $path
	 * @param  string $slash
	 * @return string
	 */
	public static function removeTrailingSlash( string $path, string $slash = DIRECTORY_SEPARATOR ): string {
		$path = static::normalizePath( $path, $slash );
		$path = preg_replace( '~' . preg_quote( $slash, '~' ) . '+$~', '', $path );
		return $path;
	}
}
