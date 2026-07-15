<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Flash;

use ArrayAccess;
use WPEmerge\Exceptions\ConfigurationException;
use WPEmerge\Helpers\MixedType;
use WPEmerge\Support\Arr;

/**
 * Provide a way to flash data into the session for the next request.
 */
class Flash {
	/**
	 * Keys for different request contexts.
	 */
	const CURRENT_KEY = 'current';
	const NEXT_KEY = 'next';

	/**
	 * Key to store flashed data in store with.
	 */
	protected string $store_key = '';

	/**
	 * Root store array or object implementing ArrayAccess.
	 *
	 * @var array|ArrayAccess|null
	 */
	protected $store = null;

	/**
	 * Flash store array.
	 */
	protected array $flashed = [];

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 * @param array|ArrayAccess $store
	 * @param string             $store_key
	 */
	public function __construct( mixed &$store = null, string $store_key = '__wpemergeFlash' ) {
		$this->store_key = $store_key;
		$this->setStore( $store );
	}

	/**
	 * Get whether a store object is valid.
	 *
	 * @param  mixed   $store
	 * @return boolean
	 */
	protected function isValidStore( mixed $store ): bool {
		return ( is_array( $store ) || $store instanceof ArrayAccess );
	}

	/**
	 * Throw an exception if store is not valid.
	 *
	 * @return void
	 */
	protected function validateStore(): void {
		if ( ! $this->isValidStore( $this->store ) ) {
			throw new ConfigurationException(
				'Attempted to use Flash without an active session. ' .
				'Did you miss to call session_start()?'
			);
		}
	}

	/**
	 * Get the store for flash messages.
	 *
	 * @return array|ArrayAccess
	 */
	public function getStore(): array|ArrayAccess|null {
		return $this->store;
	}

	/**
	 * Set the store for flash messages.
	 *
	 * @param  array|ArrayAccess $store
	 * @return void
	 */
	public function setStore( mixed &$store ): void {
		if ( ! $this->isValidStore( $store ) ) {
			return;
		}

		$this->store = &$store;

		if ( ! isset( $this->store[ $this->store_key ] ) ) {
			$this->store[ $this->store_key ] = [
				static::CURRENT_KEY => [],
				static::NEXT_KEY => [],
			];
		}

		$this->flashed = $store[ $this->store_key ];
	}

	/**
	 * Get whether the flash service is enabled.
	 *
	 * @return boolean
	 */
	public function enabled(): bool {
		return $this->isValidStore( $this->store );
	}

	/**
	 * Get the entire store or the values for a key for a request.
	 *
	 * @param  string $request_key
	 * @param  string|null $key
	 * @param  mixed $default
	 * @return mixed
	 */
	protected function getFromRequest( string $request_key, ?string $key = null, mixed $default = [] ): mixed {
		$this->validateStore();

		if ( $key === null ) {
			return Arr::get( $this->flashed, $request_key, $default );
		}

		return Arr::get( $this->flashed[ $request_key ], $key, $default );
	}

	/**
	 * Add values for a key for a request.
	 *
	 * @param  string $request_key
	 * @param  string $key
	 * @param  mixed $new_items
	 * @return void
	 */
	protected function addToRequest( string $request_key, string $key, mixed $new_items ): void {
		$this->validateStore();

		$new_items = MixedType::toArray( $new_items );
		$items = MixedType::toArray( $this->getFromRequest( $request_key, $key, [] ) );
		$this->flashed[ $request_key ][ $key ] = [...$items, ...$new_items];
	}

	/**
	 * Remove all values or values for a key from a request.
	 *
	 * @param  string $request_key
	 * @param  string|null $key
	 * @return void
	 */
	protected function clearFromRequest( string $request_key, ?string $key = null ): void {
		$this->validateStore();

		$keys = $key === null ? array_keys( $this->flashed[ $request_key ] ) : [$key];
		foreach ( $keys as $k ) {
			unset( $this->flashed[ $request_key ][ $k ] );
		}
	}

	/**
	 * Add values for a key for the next request.
	 *
	 * @param  string $key
	 * @param  mixed $new_items
	 * @return void
	 */
	public function add( string $key, mixed $new_items ): void {
		$this->addToRequest( static::NEXT_KEY, $key, $new_items );
	}

	/**
	 * Add values for a key for the current request.
	 *
	 * @param  string $key
	 * @param  mixed $new_items
	 * @return void
	 */
	public function addNow( string $key, mixed $new_items ): void {
		$this->addToRequest( static::CURRENT_KEY, $key, $new_items );
	}

	/**
	 * Get the entire store or the values for a key for the current request.
	 *
	 * @param  string|null $key
	 * @param  mixed $default
	 * @return mixed
	 */
	public function get( ?string $key = null, mixed $default = [] ): mixed {
		return $this->getFromRequest( static::CURRENT_KEY, $key, $default );
	}

	/**
	 * Get the entire store or the values for a key for the next request.
	 *
	 * @param  string|null $key
	 * @param  mixed $default
	 * @return mixed
	 */
	public function getNext( ?string $key = null, mixed $default = [] ): mixed {
		return $this->getFromRequest( static::NEXT_KEY, $key, $default );
	}

	/**
	 * Clear the entire store or the values for a key for the current request.
	 *
	 * @param  string|null $key
	 * @return void
	 */
	public function clear( ?string $key = null ): void {
		$this->clearFromRequest( static::CURRENT_KEY, $key );
	}

	/**
	 * Clear the entire store or the values for a key for the next request.
	 *
	 * @param  string|null $key
	 * @return void
	 */
	public function clearNext( ?string $key = null ): void {
		$this->clearFromRequest( static::NEXT_KEY, $key );
	}

	/**
	 * Shift current store and replace it with next store.
	 *
	 * @return void
	 */
	public function shift(): void {
		$this->validateStore();

		$this->flashed[ static::CURRENT_KEY ] = $this->flashed[ static::NEXT_KEY ];
		$this->flashed[ static::NEXT_KEY ] = [];
	}

	/**
	 * Save flashed data to store.
	 *
	 * @return void
	 */
	public function save(): void {
		$this->validateStore();

		$this->store[ $this->store_key ] = $this->flashed;
	}
}
