<?php /** @noinspection ALL */

namespace WPEmerge\Support;

use ArrayAccess;

/**
 * A collection of tools dealing with arrays
 *
 * @credit (limited version of) illuminate/support
 * @codeCoverageIgnore
 */
class Arr {
	/**
	 * Determine whether the given value is array accessible.
	 *
	 * @param  mixed  $value
	 * @return bool
	 */
	public static function accessible( mixed $value ): bool
	{
		return \is_array($value) || $value instanceof ArrayAccess;
	}

	/**
	 * Add an element to an array using "dot" notation if it doesn't exist.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	public static function add( array $array, string $key, mixed $value ): array
	{
		if (static::get($array, $key) === null) {
			static::set($array, $key, $value);
		}

		return $array;
	}

	/**
	 * Collapse an array of arrays into a single array.
	 *
	 * @param  array  $array
	 * @return array
	 */
	public static function collapse( array $array ): array
	{
		$results = [];
		foreach ($array as $values) {
			if (! \is_array($values)) {
				continue;
			}
			$results = \array_merge($results, $values);
		}
		return $results;
	}

	/**
	 * Divide an array into two arrays. One with keys and the other with values.
	 *
	 * @param  array  $array
	 * @return array
	 */
	public static function divide( array $array ): array
	{
		return [\array_keys($array), \array_values($array)];
	}

	/**
	 * Flatten a multi-dimensional associative array with dots.
	 *
	 * @param  array   $array
	 * @param  string  $prepend
	 * @return array
	 */
	public static function dot( array $array, string $prepend = '' ): array
	{
		$results = [];

		foreach ($array as $key => $value) {
			if (\is_array($value) && ! empty($value)) {
				$results = \array_merge($results, static::dot($value, $prepend.$key.'.'));
			} else {
				$results[$prepend.$key] = $value;
			}
		}

		return $results;
	}

	/**
	 * Get all of the given array except for a specified array of items.
	 *
	 * @param  array  $array
	 * @param  array|string  $keys
	 * @return array
	 */
	public static function except( array $array, array|string $keys ): array
	{
		static::forget($array, $keys);

		return $array;
	}

	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param  \ArrayAccess|array  $array
	 * @param  string|int  $key
	 * @return bool
	 */
	public static function exists( ArrayAccess|array $array, string|int $key ): bool
	{
		if ($array instanceof ArrayAccess) {
			return $array->offsetExists($key);
		}

		return \array_key_exists($key, $array);
	}

	/**
	 * Get the first element in an array passing a given truth test.
	 *
	 * @param  array  $array
	 * @param  callable|null  $callback
	 * @param  mixed  $default
	 * @return mixed
	 */
	public static function first( array $array, ?callable $callback = null, mixed $default = null ): mixed
	{
		if ($callback === null) {
			if (empty($array)) {
				return $default;
			}

			foreach ($array as $item) {
				return $item;
			}
		}

		foreach ($array as $key => $value) {
			if (\call_user_func($callback, $value, $key)) {
				return $value;
			}
		}

		return $default;
	}

	/**
	 * Get the last element in an array passing a given truth test.
	 *
	 * @param  array  $array
	 * @param  callable|null  $callback
	 * @param  mixed  $default
	 * @return mixed
	 */
	public static function last( array $array, ?callable $callback = null, mixed $default = null ): mixed
	{
		if ($callback === null) {
			return empty($array) ? $default : \end($array);
		}

		return static::first(\array_reverse($array, true), $callback, $default);
	}

	/**
	 * Remove one or many array items from a given array using "dot" notation.
	 *
	 * @param  array  $array
	 * @param  array|string  $keys
	 * @return void
	 */
	public static function forget( array &$array, array|string $keys ): void
	{
		$original = &$array;

		$keys = (array) $keys;

		if (\count($keys) === 0) {
			return;
		}

		foreach ($keys as $key) {
			// if the exact key exists in the top-level, remove it
			if (static::exists($array, $key)) {
				unset($array[$key]);

				continue;
			}

			$parts = \explode('.', $key);

			// clean up before each pass
			$array = &$original;

			while (\count($parts) > 1) {
				$part = \array_shift($parts);

				if (isset($array[$part]) && \is_array($array[$part])) {
					$array = &$array[$part];
				} else {
					continue 2;
				}
			}

			unset($array[\array_shift($parts)]);
		}
	}

	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param  \ArrayAccess|array  $array
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public static function get( mixed $array, string $key = '', mixed $default = null ): mixed
	{
		if (! static::accessible($array)) {
			return $default;
		}

		if ($key === null) {
			return $array;
		}

		if (static::exists($array, $key)) {
			return $array[$key];
		}

		foreach (\explode('.', $key) as $segment) {
			if (static::accessible($array) && static::exists($array, $segment)) {
				$array = $array[$segment];
			} else {
				return $default;
			}
		}

		return $array;
	}

	/**
	 * Check if an item or items exist in an array using "dot" notation.
	 *
	 * @param  \ArrayAccess|array  $array
	 * @param  string|array  $keys
	 * @return bool
	 */
	public static function has( ArrayAccess|array $array, string|array $keys ): bool
	{
		if ($keys === null) {
			return false;
		}

		$keys = (array) $keys;

		if (! $array) {
			return false;
		}

		if ($keys === []) {
			return false;
		}

		foreach ($keys as $key) {
			$subKeyArray = $array;

			if (static::exists($array, $key)) {
				continue;
			}

			foreach (\explode('.', $key) as $segment) {
				if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
					$subKeyArray = $subKeyArray[$segment];
				} else {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Determines if an array is associative.
	 *
	 * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
	 *
	 * @param  array  $array
	 * @return bool
	 */
	public static function isAssoc( array $array ): bool
	{
		$keys = \array_keys($array);

		return \array_keys($keys) !== $keys;
	}

	/**
	 * Get a subset of the items from the given array.
	 *
	 * @param  array  $array
	 * @param  array|string  $keys
	 * @return array
	 */
	public static function only( array $array, array|string $keys ): array
	{
		return \array_intersect_key($array, \array_flip((array) $keys));
	}

	/**
	 * Pluck an array of values from an array.
	 *
	 * @param  array  $array
	 * @param  string|array  $value
	 * @param  string|array|null  $key
	 * @return array
	 */
	public static function pluck( array $array, string|array $value, string|array|null $key = null ): array
	{
		$results = [];

		[$value, $key] = static::explodePluckParameters($value, $key);

		foreach ($array as $item) {
			$itemValue = static::data_get($item, $value);

			// If the key is "null", we will just append the value to the array and keep
			// looping. Otherwise we will key the array using the value of the key we
			// received from the developer. Then we'll return the final array form.
			if ($key === null) {
				$results[] = $itemValue;
			} else {
				$itemKey = static::data_get($item, $key);

				$results[$itemKey] = $itemValue;
			}
		}

		return $results;
	}

	/**
	 * Explode the "value" and "key" arguments passed to "pluck".
	 *
	 * @param  string|array  $value
	 * @param  string|array|null  $key
	 * @return array
	 */
	protected static function explodePluckParameters( string|array $value, string|array|null $key ): array
	{
		$value = \is_string($value) ? \explode('.', $value) : $value;

		$key = $key === null || \is_array($key) ? $key : \explode('.', $key);

		return [$value, $key];
	}

	/**
	 * Push an item onto the beginning of an array.
	 *
	 * @param  array  $array
	 * @param  mixed  $value
	 * @param  mixed  $key
	 * @return array
	 */
	public static function prepend( array $array, mixed $value, mixed $key = null ): array
	{
		if ($key === null) {
			\array_unshift($array, $value);
		} else {
			$array = [$key => $value] + $array;
		}

		return $array;
	}

	/**
	 * Get a value from the array, and remove it.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public static function pull( array &$array, string $key, mixed $default = null ): mixed
	{
		$value = static::get($array, $key, $default);

		static::forget($array, $key);

		return $value;
	}

	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	public static function set( array &$array, string $key, mixed $value ): array
	{
		if ($key === null) {
			return $array = $value;
		}

		$keys = \explode('.', $key);

		while (\count($keys) > 1) {
			$key = \array_shift($keys);

			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if (! isset($array[$key]) || ! \is_array($array[$key])) {
				$array[$key] = [];
			}

			$array = &$array[$key];
		}

		$array[\array_shift($keys)] = $value;

		return $array;
	}

	/**
	 * Shuffle the given array and return the result.
	 *
	 * @param  array  $array
	 * @return array
	 */
	public static function shuffle( array $array ): array
	{
		\shuffle($array);

		return $array;
	}

	/**
	 * Recursively sort an array by keys and values.
	 *
	 * @param  array  $array
	 * @return array
	 */
	public static function sortRecursive( array $array ): array
	{
		foreach ($array as &$value) {
			if (\is_array($value)) {
				$value = static::sortRecursive($value);
			}
		}

		if (static::isAssoc($array)) {
			\ksort($array);
		} else {
			\sort($array);
		}

		return $array;
	}

	/**
	 * Get an item from an array or object using "dot" notation.
	 *
	 * @param  mixed         $target
	 * @param  string|array  $key
	 * @param  mixed         $default
	 * @return mixed
	 */
	public static function data_get( mixed $target, string|array $key, mixed $default = null ): mixed
	{
		if ($key === null) {
			return $target;
		}
		$key = \is_array($key) ? $key : \explode('.', $key);
		while (($segment = \array_shift($key)) !== null) {
			if ($segment === '*') {
				if (! \is_array($target)) {
					return $default;
				}
				$result = static::pluck($target, $key);
				return \in_array('*', $key) ? static::collapse($result) : $result;
			}
			if (static::accessible($target) && static::exists($target, $segment)) {
				$target = $target[$segment];
			} elseif (\is_object($target) && isset($target->{$segment})) {
				$target = $target->{$segment};
			} else {
				return $default;
			}
		}
		return $target;
	}
}
